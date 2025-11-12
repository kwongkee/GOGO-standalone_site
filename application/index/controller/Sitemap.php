<?php
namespace app\index\controller;
use think\Controller;
use think\Db;

class Sitemap extends Controller
{
    public function index()
    {
        // 获取所有文章（假设表名是 article 或 news）
        $articles = Db::name('website_navbar')->where('status',0)->select();
        // 如果表名不同，改为：Db::name('news') 或 Db::name('content')
        foreach($articles as $k=>$v){
            $articles[$k]['name'] = json_decode($v['name'],true)['zh'];
        }
        
        $this->assign('articles', $articles);
        $this->view->engine->layout(false);
        header('Content-Type: application/xml');
        return $this->fetch();
    }
}
