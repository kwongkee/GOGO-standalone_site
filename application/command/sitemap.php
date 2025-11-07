<?php

public function execute()
{
    $products = Db::name('product')->field('id,update_time')->select();
    $xml = '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
    foreach ($products as $p) {
        $xml .= "<url><loc>https://dte.gogo198.net/product/{$p['id']}</loc><lastmod>" . date('c', $p['update_time']) . "</lastmod></url>";
    }
    $xml .= '</urlset>';
    file_put_contents(root_path('public') . 'sitemap.xml', $xml);
    $this->output->writeln('Sitemap generated!');
}