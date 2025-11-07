<?php
namespace app\command;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Db;

class GenerateSitemap extends Command
{
    protected function configure()
    {
        $this->setName('sitemap')->setDescription('Generate sitemap.xml');
    }

    protected function execute(Input $input, Output $output)
    {
        $products = Db::name('product')->field('id,update_time')->select();
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;

        // 首页
        $xml .= '    <url>' . PHP_EOL;
        $xml .= '        <loc>https://dtc.gogo198.net/</loc>' . PHP_EOL;  // 注意：你修正的域名
        $xml .= '        <priority>1.0</priority>' . PHP_EOL;
        $xml .= '    </url>' . PHP_EOL;

        // 产品页面
        foreach ($products as $p) {
            $xml .= '    <url>' . PHP_EOL;
            $xml .= '        <loc>https://dtc.gogo198.net/product/' . $p['id'] . '</loc>' . PHP_EOL;
            $xml .= '        <lastmod>' . date('c', $p['update_time']) . '</lastmod>' . PHP_EOL;
            $xml .= '        <changefreq>daily</changefreq>' . PHP_EOL;
            $xml .= '        <priority>0.8</priority>' . PHP_EOL;
            $xml .= '    </url>' . PHP_EOL;
        }

        $xml .= '</urlset>';
        file_put_contents(root_path('public') . 'sitemap.xml', $xml);
        $output->writeln('Sitemap generated: public/sitemap.xml');
    }
}