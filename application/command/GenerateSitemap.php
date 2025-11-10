<?php
namespace app\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Db;

class GenerateSitemap extends Command
{
    protected $config = [
        'type'     => 'mysql',
        'hostname' => 'rm-wz9mt4j79jrdh0p3z.mysql.rds.aliyuncs.com',
        'database' => 'lrw',
        'username' => 'gogo198',
        'password' => 'Gogo@198',
        'hostport' => '3306',
        'prefix'   => '',
    ];

    protected $limit = 40000;  // 每文件 < 50,000 条，留余量
    protected $baseUrl = 'https://dtc.gogo198.net';

    protected function configure()
    {
        $this->setName('sitemap')->setDescription('Generate sitemap.xml (split large tables)');
    }

    protected function execute(Input $input, Output $output)
    {
        $output->writeln('开始生成 sitemap...');

        // 1. 获取总数
        $total = Db::connect($this->config)->name('goods')->count();
        $pages = ceil($total / $this->limit);
        $sitemaps = [];

        // 2. 分页生成
        for ($page = 1; $page <= $pages; $page++) {
            $offset = ($page - 1) * $this->limit;
            $products = Db::connect($this->config)
                ->name('goods')
                ->field('goods_id,created_at')
                ->limit($offset, $this->limit)
                ->select();

            $xml = $this->buildSitemapXml($products);
            $filename = "sitemap_{$page}.xml";
            $filepath = public_path() . $filename;
            file_put_contents($filepath, $xml);
            $sitemaps[] = $this->baseUrl . '/' . $filename;

            $output->writeln("生成 {$filename} ({$products->count()} 条)");
        }

        // 3. 生成 sitemapindex.xml
        $this->generateSitemapIndex($sitemaps);

        $output->writeln("完成！共生成 {$pages} 个 sitemap 文件");
        $output->writeln("提交：{$this->baseUrl}/sitemapindex.xml 到 Google");
    }

    private function buildSitemapXml($products)
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;

        // 首页
        $xml .= "  <url><loc>{$this->baseUrl}/</loc><priority>1.0</priority></url>" . PHP_EOL;

        foreach ($products as $p) {
            $xml .= "  <url>" . PHP_EOL;
            $xml .= "    <loc>{$this->baseUrl}/product/{$p['goods_id']}</loc>" . PHP_EOL;
            $xml .= "    <lastmod>" . date('c', strtotime($p['created_at'])) . "</lastmod>" . PHP_EOL;
            $xml .= "    <changefreq>weekly</changefreq>" . PHP_EOL;
            $xml .= "    <priority>0.8</priority>" . PHP_EOL;
            $xml .= "  </url>" . PHP_EOL;
        }

        $xml .= '</urlset>';
        return $xml;
    }

    private function generateSitemapIndex($sitemaps)
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;
        foreach ($sitemaps as $url) {
            $xml .= "  <sitemap><loc>{$url}</loc><lastmod>" . date('c') . "</lastmod></sitemap>" . PHP_EOL;
        }
        $xml .= '</sitemapindex>';
        file_put_contents(public_path() . 'sitemapindex.xml', $xml);
    }
}