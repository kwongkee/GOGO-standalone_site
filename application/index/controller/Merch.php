<?php
namespace app\index\controller;

use think\Request;
use think\Db;
use think\Controller;
use think\Log;

// 开启 GZIP
if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') && !ob_get_length()) {
    ob_start('ob_gzhandler');
} else {
    ob_start();
}

class Merch
{
    public $websites;
    public $source_link;
    public $config = [
        //数据库类型
        'type'     => 'mysql',
        //服务器地址
        'hostname' => 'rm-wz9mt4j79jrdh0p3z.mysql.rds.aliyuncs.com',
        //数据库名
        'database' => 'lrw',
        //用户名
        'username' => 'gogo198',
        //密码
        'password' => 'Gogo@198',
        //端口
        'hostport' => '3306',
        //表前缀
        'prefix'   => '',
    ];

    public function __construct(Request $request){
        $dat = input();
        $this->source_link = '//dtc.gogo198.net';

        #判断有无企业id
        $cid = isset($dat['company_id'])?intval($dat['company_id']):0;
        $company_type = isset($dat['company_type'])?intval($dat['company_type']):0;

//        if(empty($cid)){
//            $cid = cookie::get('cid');
//        }

        if(empty($cid)){
            echo '<h1>商家站点ID不能为空，正在跳转至淘中国</h1><script>setTimeout(function(){ window.location.href="//www.gogo198.cn"; },1000);</script>';
        }

        #获取商户的企业配置的基本信息
        $this->websites['cid'] = $cid;
        $domain = $_SERVER['HTTP_HOST'];
        $this->websites['domain'] = 'https://'.$domain;
        $this->websites['rand'] = rand(11111,99999);
        $this->websites['info'] = Db::name('website_basic')->where(['company_id'=>$cid,'company_type'=>$company_type])->find();

        if(empty($this->websites['info'])){
            echo '<h1>请先配置电商网店信息后再访问</h1>';exit;
        }

        #获取公示信息
        $this->websites['info']['publicity_info'] = json_decode($this->websites['info']['publicity_info'],true);

        #获取搜索结果展示版式
        if(!empty($this->websites['info']['search_format'])){
            $this->websites['info']['search_format'] = json_decode($this->websites['info']['search_format'],true);
        }else{
            $this->websites['info']['search_format'] = [5,5];
        }

        #左侧弹框
        $this->websites['leftFrame'] = Db::connect($this->config)->name('frame_body')->where(['pid'=>0,'type'=>2])->order('displayorder asc')->select();

        #获取商户的企业配置的头部菜单
        $this->websites['menu'] = Db::name('website_navbar')->where(['company_id'=>$cid,'company_type'=>$company_type,'pid'=>0])->select();
        foreach($this->websites['menu'] as $k=>$v){
            $this->websites['menu'][$k]['children'] = Db::name('website_navbar')->where(['company_id'=>$cid,'company_type'=>$company_type,'pid'=>$v['id']])->select();
            foreach($this->websites['menu'][$k]['children'] as $k2=>$v2){
                $this->websites['menu'][$k]['children'][$k2]['children'] = Db::name('website_navbar')->where(['company_id'=>$cid,'company_type'=>$company_type,'pid'=>$v2['id']])->select();
            }
        }

        #获取页脚功能菜单
        $this->websites['footer_menu'] = Db::name('website_footer')->where(['company_id'=>$cid,'company_type'=>$company_type,'pid'=>0])->select();
        foreach($this->websites['footer_menu'] as $k=>$v){
            $this->websites['footer_menu'][$k]['children'] = Db::name('website_footer')->where(['company_id'=>$cid,'company_type'=>$company_type,'pid'=>$v['id']])->select();
        }

        #获取社媒
        $this->websites['website_contact'] = Db::name('website_contact')->where(['company_id'=>$cid,'company_type'=>$company_type])->select();

        #获取资质
        $this->websites['website_qualification'] = Db::name('merchsite_qualification')->where(['company_id'=>$cid,'company_type'=>$company_type])->select();

        #客服信息
        $this->websites['customer'] = Db::name('merchsite_customer_group')->where(['company_id'=>$cid])->find();
        
        $current_url = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        // 清理参数，保留主要路径
        $canonical = preg_replace('/(\?.*)$/', '', $current_url);
        if (substr($canonical, -1) !== '/') $canonical .= '/';
        
        $this->websites['website_canonical'] = '<link rel="canonical" href="' . $canonical . '">';
        $this->websites['website_og'] = '
            <meta property="og:title" content="'.$this->websites['info']['name'].'">
            <meta property="og:description" content="'.$this->websites['info']['desc'].'">
            <meta property="og:image" content="https://dtc.gogo198.net'.$this->websites['info']['logo'].'">
            <meta property="og:url" content="'.$current_url.'">
            <meta property="og:type" content="website">
        ';
    }

    public function list() {
        $keyword = input('get.keyword');
        $where = ['name|description' => ['like', "%{$keyword}%"]]; // 修复：多字段安全搜索
        $products = Db::name('product')->where($where)->select();
        return json($products);
    }

    public function groupByArray($array, $key)
    {
        $result = [];
        foreach ($array as $item) {
            $groupKey = $item[$key];
            if (!isset($result[$groupKey])) {
                $result[$groupKey] = [];
            }
            $result[$groupKey][] = $item;
        }
        return $result;
    }

    #商家商城首页
    public function merch_shop_index(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);

        #获取滚动信息
        $this->websites['rotate_info'] = Db::name('merchsite_rotate')->where(['company_id'=>$company_id,'company_type'=>$company_type])->find();
        if(!empty($this->websites['rotate_info']['content_id'])){
            $this->websites['rotate_info']['content_id'] = explode(',',$this->websites['rotate_info']['content_id']);
            foreach($this->websites['rotate_info']['content_id'] as $k=>$v){
                if($v==1){
                    #新闻内容
                    $news = Db::name('website_crossborder_news')->where(['time'=>date('Y-m-d')])->orderRaw('rand()')->limit(50)->select();
                    if(count($news)==0){
                        $news = Db::name('website_crossborder_news')->where(['status'=>1])->orderRaw('rand()')->limit(50)->select();
                    }
                    $this->websites['rotate_info']['content'][$k] = $news;
                }
                elseif($v==2){
                    #时间内容
                    $citys2 = Db::name('website_world_time')->where(['is_show'=>0])->order('displayorder asc')->group('contryCn')->select();
                    $citys = $this->groupByArray($citys2, 'contryCn');
                    $this->websites['rotate_info']['content'][$k] = $citys;
                }
                elseif($v==3){
                    #汇率内容
                    $rate = Db::name('website_exchange_rate')->whereRaw('id != 158 ')->select();

                    #其他币种
                    $currency = Db::name('centralize_currency')->whereRaw('code_zhname <> "人民币元"')->select();

                    $this->websites['rotate_info']['content'][$k] = ['rate'=>$rate,'currency'=>$currency];
                }
            }
        }

        #获取轮播图
        $this->websites['rotate'] = Db::name('website_rotate')->where(['company_id'=>$company_id,'company_type'=>$company_type])->select();

        #获取首页推荐A
        $this->websites['recommendA'] = Db::name('website_discovery_list')->where(['company_id'=>$company_id,'company_type'=>$company_type])->select();

        #获取首页推荐B
        $this->websites['recommendB'] = Db::name('merchsite_recommend_b')->where(['company_id'=>$company_id])->select();
        foreach($this->websites['recommendB'] as $k=>$v){
            $this->websites['recommendB'][$k]['children'] = [];
            if($v['go_other']==2){
                #商品详情，按倒序获取该企业商品
                $this->websites['recommendB'][$k]['children'] = Db::connect($this->config)->name('goods')->where(['shop_id'=>$company_id,'goods_status'=>1])->order('goods_id desc')->select();
                foreach($this->websites['recommendB'][$k]['children'] as $k2=>$v2){
                    $sku_prices = Db::connect($this->config)->name('goods_sku')->where(['sku_id'=>$v2['sku_id']])->find()['sku_prices'];
                    $sku_prices = json_decode($sku_prices,true);
                    if($v2['goods_price']==0){
                        $this->websites['recommendB'][$k]['children'][$k2]['goods_price'] = number_format(end($sku_prices['price']),2);
                    }
                    $this->websites['recommendB'][$k]['children'][$k2]['currency'] = Db::name('centralize_currency')->where(['id'=>$sku_prices['currency'][0]])->find()['currency_symbol_standard'];
                }
            }
        }

        #获取导流区
        $this->websites['guide'] = Db::connect($this->config)->name('merchsite_guide_body')->where(['company_id'=>$company_id,'company_type'=>$company_type])->select();

        foreach($this->websites['guide'] as $k=>$v){
            #导流样式
            $this->websites['guide'][$k]['format_info'] = Db::connect($this->config)->name('merchsite_guide_format')->where(['id'=>$v['content_id']])->find();
            #获取导流内容
            if($this->websites['guide'][$k]['format_info']['type']==1 || $this->websites['guide'][$k]['format_info']['type']==3 || $this->websites['guide'][$k]['format_info']['type']==5){
                #店铺展示/触发搜索/图文展示版式
                $this->websites['guide'][$k]['children'] = Db::connect($this->config)->name('guide_content')->where(['pid'=>$v['id'],'company_id'=>$company_id])->select();

                #商家上架信息==start
                $shelf_info = Db::connect($this->config)->name('goods_shelf')->whereRaw('cid='.$company_id.' and type=1 and guide_id='.$v['id'].' and keywords <> ""')->select();
                dd($shelf_info);
                if(!empty($shelf_info)){
                    foreach($shelf_info as $k2=>$v2) {
                        $arr1 = explode('、', $v2['keywords']);
                        $arr2 = explode('、', $v['gkeywords']);

                        $intersection = array_intersect($arr1, $arr2);

                        if (!empty($intersection)) {
                            #有当前导流关键字的商品
                            $goods = Db::connect($this->config)->name('goods')->where(['goods_id'=>$v2['gid'],'goods_status'=>1])->find();

                            if(!empty($goods)) {
                                $keywords_content = [
                                    'name' => $goods['goods_name'],
                                    'back_type' => 2,
                                    'back_content' => str_replace('//dtc.gogo198.net', '', $goods['goods_image']),
                                    'back_content2' => str_replace('//dtc.gogo198.net', '', $goods['goods_image']),
                                    'go_other' => 2,
                                    'other_goods' => $goods['goods_id'],
                                ];
                                $this->websites['guide'][$k]['children'] = array_merge($this->websites['guide'][$k]['children'], [$keywords_content]);
                            }
                        }
                    }
                }
                #商家上架信息==end

                foreach($this->websites['guide'][$k]['children'] as $k2=>$v2){
                    if($v2['go_other']==2){
                        #商品
                        $this->websites['guide'][$k]['children'][$k2]['info'] = Db::connect($this->config)->name('goods')->where(['goods_id'=>$v2['other_goods'],'goods_status'=>1])->find();

                        $sku_prices = Db::connect($this->config)->name('goods_sku')->where(['sku_id'=>$this->websites['guide'][$k]['children'][$k2]['info']['sku_id']])->find()['sku_prices'];
                        $sku_prices = json_decode($sku_prices,true);

                        if($this->websites['guide'][$k]['children'][$k2]['info']['goods_price']==0){
                            $this->websites['guide'][$k]['children'][$k2]['info']['goods_price'] = number_format(end($sku_prices['price']),2);
                        }
                        $this->websites['guide'][$k]['children'][$k2]['info']['currency'] = Db::name('centralize_currency')->where(['id'=>$sku_prices['currency'][0]])->find()['currency_symbol_standard'];
                    }
                }

                shuffle($this->websites['guide'][$k]['children']);

                if($this->websites['guide'][$k]['format_info']['type']==3){
                    //触发搜索
                    if(is_mobile2()){
                        $this->websites['guide'][$k]['children'] = array_chunk($this->websites['guide'][$k]['children'],1);
                    }else{
                        $this->websites['guide'][$k]['children'] = array_chunk($this->websites['guide'][$k]['children'],6);
                    }

                    foreach($this->websites['guide'][$k]['children'] as $k2=>$v2){
                        if(is_mobile2()){
                            $this->websites['guide'][$k]['children'][$k2] = array_chunk($v2,1);
                        }else{
                            $this->websites['guide'][$k]['children'][$k2] = array_chunk($v2,3);
                        }
                    }
                }
            }
            elseif($this->websites['guide'][$k]['format_info']['type']==2){
                #卡片导航版式
                $this->websites['guide'][$k]['children'] = Db::connect($this->config)->name('guide_content')->where(['pid'=>$v['id'],'top_id'=>0,'company_id'=>$company_id])->select();

                foreach($this->websites['guide'][$k]['children'] as $k2=>$v2){
                    $this->websites['guide'][$k]['children'][$k2]['children'] = Db::connect($this->config)->name('guide_content')->where(['pid'=>$v['id'],'top_id'=>$v2['id'],'company_id'=>$company_id])->select();
                }
            }
            elseif($this->websites['guide'][$k]['format_info']['type']==4){
                #杂志导航版式
                $this->websites['guide'][$k]['big_children'] = Db::connect($this->config)->name('guide_content')->where(['top_id'=>0,'pid'=>$v['id'],'company_id'=>$company_id,'is_show'=>0])->select();
                foreach($this->websites['guide'][$k]['big_children'] as $k2=>$v2){
                    $this->websites['guide'][$k]['big_children'][$k2]['sml_children'] = Db::connect($this->config)->name('guide_content')->where(['pid'=>$v['id'],'top_id'=>$v2['id'],'company_id'=>$company_id])->select();

                    if(is_mobile2()){
                        $this->websites['guide'][$k]['big_children'][$k2]['sml_children'] = array_chunk($this->websites['guide'][$k]['big_children'][$k2]['sml_children'],4);
                    }else{
                        $this->websites['guide'][$k]['big_children'][$k2]['sml_children'] = array_chunk($this->websites['guide'][$k]['big_children'][$k2]['sml_children'],4);
                    }
                    foreach($this->websites['guide'][$k]['big_children'][$k2]['sml_children'] as $k3=>$v3){
                        foreach($v3 as $k4=>$v4) {
                            $color = Db::name('centralize_diycountry_content')->where(['pid' => 12])->orderRaw('rand()')->find();

                            $this->websites['guide'][$k]['big_children'][$k2]['sml_children'][$k3][$k4]['rand_background'] = sprintf("#%02x%02x%02x", $color['param1'], $color['param2'], $color['param3']);
                        }
                    }
                }
            }
        }

        $data['websites'] = $this->websites;
//        dd($data['websites']['rotate_info']['content'][1]);
        $data['source_link'] = '//dtc.gogo198.net';
        $data['page_type'] = 1;

        return view('index/shop_frontend/merch_shop_index',compact('data','company_id','company_type'));
    }

    #税率页
    public function rate_detail(Request $request){
        $dat = input();
        $id = isset($dat['id'])?intval($dat['id']):0;
        $isframe = isset($dat['isframe'])?intval($dat['isframe']):0;
        $price = isset($dat['price'])?intval($dat['price']):1;
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);

        if($request->isAjax()){

            if($dat['pa']==1){
                $key = 'feea63fb96c064f252418348bf775fa9';
                $from = Db::name('website_exchange_rate')->where(['id'=>$dat['from_currency']])->find();
                $to = Db::name('website_exchange_rate')->where(['id'=>$dat['to_currency']])->find();
                $url = 'http://api.tanshuapi.com/api/exchange/v1/index?key='.$key.'&from='.$from['symbol'].'&to='.$to['symbol'].'&money='.intval($dat['from_money']);
                $list = json_decode(file_get_contents($url),true);

                if($list['code']==1){
                    return json(['code'=>0,'msg'=>'查询成功','data'=>$list['data']]);
                }else{
                    return json(['code'=>-1,'msg'=>'查询失败']);
                }
            }
        }else{
            $rate = Db::name('website_exchange_rate')->where(['id'=>$id])->find();

            #币种
            $currency = Db::name('website_exchange_rate')->whereRaw('id != 158 ')->select();

            $origin_page = '/?s=merch/merch_shop_index&company_id='.$company_id.'&company_type='.$company_type;
            $data['websites'] = $this->websites;
            $data['source_link'] = '//dtc.gogo198.net';

            return view('index/shop_frontend/rate_detail',compact('data','id','rate','currency','isframe','price','origin_page','company_id','company_type'));
        }
    }

    #详情页
    public function detail(){
        $dat = input();
        $is_footer = isset($dat['is_footer'])?intval($dat['is_footer']):0;
        $id = isset($dat['id'])?intval($dat['id']):0;
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);

        $data = [];
        if($is_footer==1){
            #页脚菜单
            $data = Db::name('website_footer')->where(['id'=>$id])->find();
        }
        else{
            #页头菜单
            $data = Db::name('website_navbar')->where(['id'=>$id])->find();
        }

        #内页seo优化
        if($data['seo_type']==2){
            $data['seo_content'] = json_decode($data['seo_content'],true);
            $this->websites['info']['name'] = $data['seo_content']['title'];
            $this->websites['info']['keywords'] = $data['seo_content']['keywords'];
            $this->websites['info']['desc'] = $data['seo_content']['desc'];
        }

        $data['content'] = json_decode($data['content'],true);
        $data['websites'] = $this->websites;
        $data['source_link'] = $this->source_link;

        $origin_page = '/?s=merch/detail?id='.$id.'&company_id='.$company_id.'&company_type='.$company_type;

        return view('index/shop_frontend/detail',compact('data','origin_page','id','company_id','company_type'));
    }

    #商品结果页
    public function goods_list(Request $request){
        header('Content-Type: text/html; charset=utf-8');
        $data = input();
        $company_id = intval($data['company_id']);
        $company_type = intval($data['company_type']);
        $frame_id = isset($data['frame_id'])?intval($data['frame_id']):0;
        $hotsearchId = isset($data['hotsearchId'])?intval($data['hotsearchId']):0;#导页id、节日id、轮播id、发现id
        $searchTitle = isset($data['searchTitle'])?trim($data['searchTitle']):'';#板块名称

        #当前页面的链接
        $origin_page = '/?s=merch/goods_list&frame_id='.$frame_id.'&hotsearchId='.$hotsearchId.'&searchTitle='.$searchTitle.'&company_id='.$company_id.'&company_type='.$company_type;

        if(isset($data['pa2'])){

        }
        else{
            $currency_sel = isset($data['currency_sel'])?trim($data['currency_sel']):158;
            $catename = isset($data['cate_name'])?trim($data['cate_name']):'';#关键字搜索
            $id = $frame_id;//1导页数据、2节日、3首页轮播、4发现好货
            $sort_info = isset($data['sort_info'])?trim($data['sort_info']):0;

            #获取商户配置的展示版式
            $calc_limit = $this->websites['info']['search_format'][0] * $this->websites['info']['search_format'][1];#N行N个

            #分页数据====================
            $goods_count = isset($data['goods_count'])?intval($data['goods_count']):0;
            $limit = $calc_limit;
            $page = isset($data['page'])?(intval($data['page']) - 1) * $limit:0;#【0*10,10】【1*10,10】【2*10,10】
            #分页数据====================

            #高级条件====START
            $origin_condition = isset($data['g_condition'])?trim($data['g_condition']):'';
            $g_condition = isset($data['g_condition'])?base64_decode($origin_condition):'';
            $g_o = $g_condition;
            if(!empty($g_condition)){
                $g_condition = explode('@@@',rtrim($g_condition,'@@@'));
            }
            $origin_field_condition = isset($data['field_condition'])?$data['field_condition']:'';
            $field_condition = isset($data['field_condition'])?base64_decode(str_replace(' ','',$origin_field_condition)):'';
            $field_condition = json_decode($field_condition,true);

            //二级字段条件
            $condition_arr2 = isset($data['condition_arr2'])?trim($data['condition_arr2']):'';

            #高级条件======END

            $result = '没找到相关的商品';
            $minprice = 0;
            $maxprice = 0;
            if($hotsearchId>0){
                #查找该板块的关键字
                $keywords = [];
                if($id==1){
                    #导页
                    $get_keywords = Db::connect($this->config)->name('guide_content')->where(['id'=>$hotsearchId,'company_id'=>$this->websites['cid']])->find();

                    #1.1、获取当前导流板块关键字
                    $datas = Db::connect($this->config)->name('merchsite_guide_body')->where(['id'=>$get_keywords['pid']])->find();
                    $shelf_keywords = '';

                    #1.2、获取当前导流板块的商户上架关键字
                    $goods_shelf = Db::connect($this->config)->name('goods_shelf')->where(['type'=>1,'guide_id'=>$get_keywords['pid']])->select();

                    #1.3、对比导流板块关键字是否与商家上架关键字有匹配
                    foreach($goods_shelf as $k=>$v){
                        $arr1 = explode('、',$datas['gkeywords']);
                        $arr2 = explode('、',$v['keywords']);
                        $intersection = array_intersect($arr1, $arr2);
                        if(!empty($intersection)){
                            foreach($intersection as $k2=>$v2){
                                #1.4、判断关键字表有无此关键字，无则插入并设置为已爬完
                                $ishave = Db::connect($this->config)->name('goods_keywords')->where(['keywords'=>$v2])->find();

                                $keywordsId = 0;
                                if(empty($ishave)){
                                    $keywordsId = Db::connect($this->config)->name('goods_keywords')->insertGetId([
                                        'keywords'=>trim($v2),
                                        'get_times'=>0,
                                        'is_done'=>1,
                                        'is_merch'=>1
                                    ]);
                                }else{
                                    $keywordsId = $ishave['id'];
                                }
                                Db::connect($this->config)->name('goods')->where(['goods_id'=>$v['gid']])->update(['keywords_id'=>$keywordsId]);
                                $shelf_keywords .= $v2.'、';
                            }
                        }
                    }

                    if(empty($shelf_keywords)){
                        $keywords = explode('、',$get_keywords['gkeywords']);
                    }else{
                        $keywords = explode('、',$get_keywords['gkeywords'].'、'.rtrim($shelf_keywords,'、'));
                    }
                }
                elseif($id==2){
                    #节日
                    $get_keywords = Db::name('website_festival')->where(['id'=>$hotsearchId])->find();

                    $keywords = explode('、',$get_keywords['keywords']);
                }
                elseif($id==3){
                    #轮播
                    $get_keywords = Db::name('website_rotate')->where(['id'=>$hotsearchId,'company_id'=>$this->websites['cid']])->find();

                    $keywords = explode('、',$get_keywords['other_keywords']);
                }
                elseif($id==4){
                    #发现好货
                    $get_keywords = Db::name('website_discovery_list')->where(['id'=>$hotsearchId,'company_id'=>$this->websites['cid']])->find();

                    $keywords = explode('、',$get_keywords['other_keywords']);
                }

                $keywords_id = [];
//                foreach($keywords as $k=>$v){
//                    $this_keyword = Db::connect($this->config)->name('goods_keywords')->where(['keywords'=>$v])->find();
//
//                    array_push($keywords_id,$this_keyword['id']);
//                }

                #找当前企业的商品信息
                $query = Db::connect($this->config)->name('goods')->where(['shop_id'=>$this->websites['cid'],'goods_status'=>1]);

                foreach ($keywords as $keyword) {
                    $query->where('goods_name', 'like', '%' . $keyword . '%');
                }

                $cateinfo1 = $query->select();

                $list = [];
                $condition = [];
                if(isset($cateinfo1[0]['goods_id'])){
//                    $cateinfo1['goods_id']>0
                    #获取高级条件
                    $list_info = $this->getTotalWhere($catename,$g_condition,$field_condition,$condition_arr2,['goods_status'=>['=',1],'shop_id'=>['=',$this->websites['cid']]],$keywords_id,$sort_info,['page'=>$page,'limit'=>$limit]);

                    $list = $list_info[0];
                    $goods_count = $list_info[1];
                    $minprice = $list_info[2];
                    $maxprice = $list_info[3];

                    $list2 = $cateinfo1;
//                    $list2 = Db::connect($this->config)->name('goods')->where(['shop_id'=>$this->websites['cid'],'goods_status'=>1])->whereIn('keywords_id',$keywords_id)->select();

                    $condition = $this->get_condition($id, $list2, 1,['value_show'=>0,'brand_show'=>0]);
//                    }
                }
                else{
                    $result = '暂无商品';
                }

                #当前页面的链接
                $origin_page = '/?s=index/customer_login&company_id='.$company_id.'&company_type='.$company_type.'&open=4&param2='.base64_encode('/?s=merch/goods_list&frame_id='.$data['frame_id'].'&hotsearchId='.$hotsearchId.'&searchTitle='.$searchTitle.'&company_id='.$company_id.'&company_type='.$company_type);
            }
            else{
                $searchTitle = $catename;
                #先查询有无此商品名称
                #后再查询分类名称
                $cateinfo1 = Db::connect($this->config)->name('goods')->where(['shop_id'=>$this->websites['cid'],'goods_status'=>1])->where('goods_name', 'like', '%'.$catename.'%')->find();

                $list = [];
                $condition = [];
                if($cateinfo1['goods_id']>0){
                    #获取高级条件

                    $list_info = $this->getTotalWhere($catename,$g_condition,$field_condition,$condition_arr2,['goods_name'=>['like','%'.$catename.'%'],'goods_status'=>['=',1],'shop_id'=>['=',$this->websites['cid']]],[],$sort_info,['page'=>$page,'limit'=>$limit]);

                    $list = $list_info[0];
                    $goods_count = $list_info[1];
                    $minprice = $list_info[2];
                    $maxprice = $list_info[3];
                    #获取原“商品名称/分类名称”的条件
                    $list2 = Db::connect($this->config)->name('goods')->where(['goods_name'=>['like','%'.$catename.'%'],'goods_status'=>['=',1],'shop_id'=>['=',$this->websites['cid']]])->select();

                    $condition = $this->get_condition($id,$list2,1,['value_show'=>0,'brand_show'=>0]);
                }
                else{
                    $cateinfo1 = Db::connect($this->config)->name('category')->where(['cat_name'=>$catename])->find();

                    #获取高级条件
                    $list_info = $this->getTotalWhere($catename,$g_condition,$field_condition,$condition_arr2,[['cat_id', '=', $cateinfo1['cat_id']],['goods_status','=',1],['shop_id','=',$this->websites['cid']]],$sort_info,['page'=>$page,'limit'=>$limit]);
                    $list = $list_info[0];
                    $goods_count = $list_info[1];
                    $minprice = $list_info[2];
                    $maxprice = $list_info[3];

                    #获取原“商品名称/分类名称”的条件
                    $list2 = Db::connect($this->config)->name('goods')->where(['cat_id'=>$cateinfo1['cat_id'],'shop_id'=>$this->websites['cid']])->select();

                    $condition = $this->get_condition($id,$list2,1,['value_show'=>1,'brand_show'=>1]);
                }

                #当前页面的链接
                $origin_page = '/?s=index/customer_login&company_id='.$company_id.'&company_type='.$company_type.'&open=4&param2='.base64_encode('/?s=merch/goods_list&cate_name='.$catename.'&company_id='.$company_id.'&company_type='.$company_type);
            }
//            dd($keywords_id);

            #币种转换
            if(!empty($list)){
                foreach($list as $k=>$v){
                    if($currency_sel==158){
                        $list[$k]['goods_currency'] = Db::name('centralize_currency')->where(['id'=>$v['goods_currency']])->find()['currency_symbol_standard'];
                    }
                    else{
                        $currency_info = Db::name('website_exchange_rate')->where(['id'=>$currency_sel])->find();

                        $list[$k]['goods_currency'] = $currency_info['symbol'];
                        $list[$k]['goods_price'] = sprintf('%.2f',$currency_info['rate'] * $v['goods_price']);
                    }
                }
            }
            if(isset($data['pa'])){
                return json(['code'=>0,'data'=>$list]);
            }

            #获取配置信息
            $data = ['websites'=>$this->websites,'source_link'=>$this->source_link];


            #币种
            $currency = Db::name('website_exchange_rate')->select();

            #价格排序参数
            $sort = 0;
            if(!empty($sort_info)){
                $sort = explode('_',$sort_info)[1];
            }

            #二级字段
            $two_fields = Db::connect($this->config)->name('merchsite_search_column_two')->where(['company_id'=>$this->websites['cid']])->select();

            $data['websites'] = $this->websites;

            return view('index/shop_frontend/goods_list',compact('condition','list','data','origin_field_condition','field_condition','origin_condition','g_condition','catename','id','g_o','sort_info','sort','hotsearchId','goods_count','limit','currency','currency_sel','minprice','maxprice','result','searchTitle','origin_page','two_fields','condition_arr2','company_id','company_type','websites'));
        }
    }

    #列表页根据高级条件搜索
    public function getTotalWhere($catename,$g_condition,$field_condition,$condition_arr2,$where,$whereIn,$sort_info='',$limit=['page'=>0,'limit'=>10]){
        #条件存在时，打包搜索
        $opt_arr = [];
        $whereOr = [];
        if(!empty($g_condition)){
            #商品字段条件
            foreach($g_condition as $k=>$v){
                $now_val = explode('_',$v);
                if($now_val[0]=='cate'){
                    $whereOr = array_merge($whereOr,['cat_id'=>['=',$now_val[1]]]);
//                    $where = array_merge($where,[['cat_id','=',$now_val[1],'or']]);
                }
                elseif($now_val[0]=='opt'){
                    $now_val = explode('|',$now_val[1]);
                    $opt_arr = array_merge($opt_arr,[['attr_id'=>$now_val[0],'attr_vid'=>$now_val[1]]]);
                }
                elseif($now_val[0]=='brand'){
                    $whereOr = array_merge($whereOr,['brand_id'=>['=',$now_val[1]]]);
//                    $where = array_merge($where,[['brand_id','=',$now_val[1],'or']]);
                }
            }
        }

        if(!empty($field_condition)){
            #自定字段条件
            $field_condition2 = [];
            foreach($field_condition as $k=>$v){
                if(empty($field_condition2)){
                    $field_condition2 = array_merge($field_condition2,[$v]);
                }else{
                    foreach($field_condition2 as $k2=>$v2){
                        if($v2['id']==$v['id']){
                            $field_condition2[$k2]['val'] .=  '_'.$v['val'];
                            break;
                        }else{
                            $field_condition2 = array_merge($field_condition2,[$v]);
                            break;
                        }
                    }
                }
            }

            foreach($field_condition2 as $k=>$v){
                $column_condition = Db::connect($this->config)->name('search_column')->where(['id'=>$v['id']])->find();

                if($column_condition['stype']==1){
                    #价幅
                    $num = explode('_',$v['val']);
                    if($num[1]>0 && !empty($column_condition['field'])){
                        $where = array_merge($where,[$column_condition['field']=>['between',[$num[0],$num[1]]]]);
//                        $where = array_merge($where,[[$column_condition['field'],'>=',$num[0],'and']]);
//                        $where = array_merge($where,[[$column_condition['field'],'<=',$num[1],'and']]);
                    }
                }
                elseif($column_condition['stype']==2){
                    #下拉选择 todo 下拉选择可能要选表中参数
                    if(!empty($column_condition['field'])){
                        $where = array_merge($where,[$column_condition['field']=>['=',$v['val']]]);
//                        $where = array_merge($where,[[$column_condition['field'],'=',$v['val'],'and']]);
                    }
                }
                elseif($column_condition['stype']==3){
                    #单选参数 todo 有些参数是1/2/3的，请求只会给0/1
                    if(!empty($column_condition['field'])){
                        $where = array_merge($where,[$column_condition['field']=>['=',$v['val']]]);
//                        $where = array_merge($where,[[$column_condition['field'],'=',$v['val'],'and']]);
                    }
                }
                elseif($column_condition['stype']==4){
                    #发货地区
                    if(!empty($column_condition['field'])){
                        $area = Db::name('centralize_adminstrative_area')->where(['code_name'=>$v['val']])->find();
                        $where = array_merge($where,[$column_condition['field']=>['=',$area['id']]]);
//                        $where = array_merge($where,[[$column_condition['field'],'=',$area->id,'and']]);
                    }
                }
            }
        }

        #二级字段
        if(!empty($condition_arr2)){
            $condition_arr2 = explode('、',$condition_arr2);
            foreach($condition_arr2 as $k=>$v){
                if(!empty($v)){
                    $value = Db::connect($this->config)->name('merchsite_search_column_two')->where(['id'=>$v])->find();
                    $where = array_merge($where,[$value['field']=>['=',1]]);
//                    $where = array_merge($where,[[$value['field'],'=',1,'and']]);
                }
            }
        }

        $minprice = 0;$maxprice = 0;
        if(empty($whereIn)){
            #总数量
            $count_query = Db::connect($this->config)->name('goods')->where($where);

            #价钱——最低值/最高值
            $minprice_query = Db::connect($this->config)->name('goods')->where($where);
            $maxprice_query = Db::connect($this->config)->name('goods')->where($where);

            if(!empty($whereOr)){
                $count_query->whereOr($whereOr);
                $minprice_query->whereOr($whereOr);
                $maxprice_query->whereOr($whereOr);
            }

            $count = $count_query->count();
            $minprice = $minprice_query->min('goods_price');
            $maxprice = $maxprice_query->max('goods_price');
        }else{
            #总数量
            $count_query = Db::connect($this->config)->name('goods')->where($where)->whereIn('keywords_id',$whereIn);

            #价钱——最低值/最高值
            $minprice_query = Db::connect($this->config)->name('goods')->where($where)->whereIn('keywords_id',$whereIn);
            $maxprice_query = Db::connect($this->config)->name('goods')->where($where)->whereIn('keywords_id',$whereIn);

            if(!empty($whereOr)){
                $count_query->whereOr($whereOr);
                $minprice_query->whereOr($whereOr);
                $maxprice_query->whereOr($whereOr);
            }

            $count = $count_query->count();
            $minprice = $minprice_query->min('goods_price');
            $maxprice = $maxprice_query->max('goods_price');
        }


        #按“x”字段排序商品
        if($sort_info!=0 && !empty($sort_info)){
            $sort_info = explode('_',$sort_info);
            $sort_field = Db::connect($this->config)->name('search_column')->where(['id'=>$sort_info[0]])->find();
            if($sort_info[1]==1){
                #升序
                if(empty($whereIn)) {
                    $list = Db::connect($this->config)->name('goods')->where($where)->whereOr($whereOr)->limit($limit['page'],$limit['limit'])->order($sort_field['field'].' asc')->select();
                }else{
                    $list = Db::connect($this->config)->name('goods')->where($where)->whereOr($whereOr)->whereIn('keywords_id', $whereIn)->limit($limit['page'],$limit['limit'])->order($sort_field['field'].' asc')->select();
                }
            }
            elseif($sort_info[1]==2){
                #降序
                if(empty($whereIn)) {
                    $list = Db::connect($this->config)->name('goods')->where($where)->whereOr($whereOr)->limit($limit['page'],$limit['limit'])->order($sort_field['field'].' desc')->select();
                }else{
                    $list = Db::connect($this->config)->name('goods')->where($where)->whereOr($whereOr)->whereIn('keywords_id', $whereIn)->limit($limit['page'],$limit['limit'])->order($sort_field['field'].' desc')->select();
                }
            }
        }else{
            #无排序（最新>最旧）
            if(empty($whereIn)) {
                $list = Db::connect($this->config)->name('goods')->where($where)->whereOr($whereOr)->limit($limit['page'],$limit['limit'])->select();
            }else{
                $list = Db::connect($this->config)->name('goods')->where($where)->whereOr($whereOr)->whereIn('keywords_id',$whereIn)->limit($limit['page'],$limit['limit'])->select();
            }
        }

        $list2 = [];#出现过任何规格相同的商品id，将要保留在数组
        if(!empty($opt_arr) && !empty($list)){
            foreach($list as $k=>$v){
                foreach($opt_arr as $k2=>$v2){
                    $ishave = Db::connect($this->config)->name('goods_spec')->where(['attr_id'=>$v2['attr_id'],'attr_vid'=>$v2['attr_vid'],'goods_id'=>$v['goods_id']])->find();
                    if(!empty($ishave->spec_id)){
                        if (!in_array($list[$k], $list2)) {
                            $list2 = array_merge($list2,[$list[$k]]);
                        }
                    }
                }
            }
            $list = $list2;
            $count = count($list);
        }

        #处理商家商品价格问题
        foreach($list as $key=>$item){
            if($item['goods_price']==0){
                $sku_info = Db::connect($this->config)->name('goods_sku')->where(['sku_id'=>$item['sku_id']])->find();

                $sku_info['sku_prices'] = json_decode($sku_info['sku_prices'],true);
                $list[$key]['goods_price'] = number_format(end($sku_info['sku_prices']['price']),2);
            }
        }
        return [$list,$count,$minprice,$maxprice];
    }

    #浮框&列表页获取条件数组
    #$id//1导页数据、2节日、3首页轮播、4发现好货
    public function get_condition($id,$list,$type,$show=['value_show'=>0,'brand_show'=>0]){
        #条件============START
        $condition = [
            'category'=>[],
            'options'=>[],
            'brand'=>[],
        ];
        $cate_id = 0;
        foreach($list as $k=>$v){
            #1、获取当前分类的同级分类作为条件
            if($cate_id==0){
                if($v['cat_id']>0){
                    $cate_id = $v['cat_id'];
                    #1.1、查找同级分类
                    $last_cat = Db::connect($this->config)->name('category')->where(['cat_id'=>$v['cat_id']])->find();
                    $category = Db::connect($this->config)->name('category')->where(['parent_id'=>$last_cat['parent_id']])->select();
                    $condition['category'] = $category;
                }
            }

            if($show['value_show']==1){
                #2、获取所有商品的所有规格/属性
                if($v['have_specs']==1){
                    $sku = Db::connect($this->config)->name('goods_sku')->where(['goods_id'=>$v['goods_id']])->select();

                    foreach($sku as $k2=>$v2){
                        if(empty($condition['options'])){
                            $condition['options'] = array_merge($condition['options'],[[
                                'spec_ids'=>$v2['spec_ids'],
                                'children2'=>[$v2['spec_vids']],
                            ]]);
                        }else{
                            foreach($condition['options'] as $k3=>$v3){
                                if($v3['spec_ids']==$v2['spec_ids']){
                                    $condition['options'][$k3]['children2'] = array_merge($condition['options'][$k3]['children2'],[$v2['spec_vids']]);
                                }
                            }
                        }
                    }
                }
            }

            if($show['brand_show']==1) {
                #5、获取品牌
                if ($v['brand_type'] == 1) {
                    $brand = Db::name('centralize_diycountry_content')->where(['pid' => 8, 'id' => $v['brand_id']])->find();
                    $condition['brand'] = array_merge($condition['brand'], [['type' => 1, 'name' => $brand['param1'], 'id' => $brand['id']]]);
                }
            }
        }

        if($show['value_show']==1){
            #3、去除重复规格、品牌
            foreach ($condition['options'] as $k => $v) {
                $condition['options'][$k]['children2'] = array_values(array_unique($v['children2']));
                $condition['options'][$k]['spec_name'] = Db::connect($this->config)->name('attribute')->where(['attr_id' => $v['spec_ids']])->find()['attr_name'];
                foreach ($condition['options'][$k]['children2'] as $k2 => $v2) {
                    $condition['options'][$k]['children'][$k2]['value_id'] = $v2;
                    $condition['options'][$k]['children'][$k2]['value_name'] = Db::connect($this->config)->name('attr_value')->where(['attr_vid' => $v2])->find()['attr_vname'];
                }
                unset($condition['options'][$k]['children2']);
            }
        }
        if($show['brand_show']==1) {
            $condition['brand'] = $this->hasDuplicateField($condition['brand'], 'name');
        }

        #4、查询框架自定条件
//        $data = Db::table('search_list')->where(['id'=>$id])->first();
        $data = Db::connect($this->config)->name('search_list')->where(['id'=>1])->find();

        if(!empty($data['content'])){
            $data['column_content'] = Db::connect($this->config)->name('search_column')->whereRaw('find_in_set(id,?)',[$data['content']])->whereRaw('type <> 1')->select();

            foreach($data['column_content'] as $k=>$v){
                $data['column_content'][$k]['content'] = json_decode($v['content'],true);
                if(!empty($data['column_content'][$k]['content'])){
                    $data['column_content'][$k]['content'] = explode('、',$data['column_content'][$k]['content']);
                }

                if($v['stype']==4){
                    #获取国家的省
                    if(!empty(session('province_list'))){
                        $condition['province'] = session('province_list');
                    }else{
                        $res = httpRequest('https://shop.gogo198.cn/collect_website/public/?s=api/gather/gettableinfo', ['id' => 4,'country_id'=>162]);
                        $res2 = json_decode($res, true);
                        $res = json_decode($res2['list'],true);
                        session('province_list', $res);
//                    $request->session()->put('country_list', $res);
                        $condition['province'] = $res;
                    }
                }
            }
        }
        $condition['column_content'] = $data['column_content'];
        #条件============END

        return $condition;
    }

    #去除重复字段
    public function hasDuplicateField($arr, $field) {
        $values = [];
        foreach ($arr as $row) {
            $values[] = $row[$field];
        }

        $uniqueValues = array_unique($values);
        return $uniqueValues;
    }

    #淘中国列表
    public function taozg(Request $request){
        $data = input();

        if(isset($data['pa'])){
            if($data['pa']==1){
                $id = isset($data['id'])?intval($data['id']):0;#框架id
                $catename = trim($data['cate_name']);#关键字搜索

                #先查询有无此商品名称
                #后再查询分类名称
                $cateinfo1 = Db::connect($this->config)->name('goods')->where([['goods_name', 'like', '%'.$catename.'%'],['goods_status','=',1]])->find();

                if($cateinfo1['goods_id']>0){
                    $list = Db::connect($this->config)->name('goods')->where([['goods_name', 'like', '%'.$catename.'%'],['goods_status','=',1]])->select();

                    if(!empty($list)){
                        $condition = $this->get_condition($id,$list,1);
                        return json(['code'=>-1,'id'=>0,'msg'=>'搜索成功','data'=>$list,'condition'=>$condition]);
                    }else{
                        $list = $this->get_goods($catename);
                        $new_list = $this->save_goods($list['data']);
                        if($list['code']==0){
                            $condition = $this->get_condition($id,$new_list,1);
                            return json(['code'=>-1,'id'=>0,'msg'=>'搜索成功','data'=>$new_list,'condition'=>$condition]);
                        }elseif($list['code']==-1){
                            return json(['code'=>-2,'id'=>0,'msg'=>'暂无信息','data'=>'','condition'=>'']);
                        }
                    }
                }
                else{
                    $cateinfo1 = Db::connect($this->config)->name('category')->where(['cat_name'=>$catename])->find();

                    if(!empty($cateinfo1)){
                        $list = Db::connect($this->config)->name('goods')->where(['cat_id'=>$cateinfo1['cat_id'],'goods_status'=>1])->select();

                        if(!empty($list)){
                            $condition = $this->get_condition($id,$list,1);
                            return json(['code'=>-1,'id'=>0,'msg'=>'搜索成功','data'=>$list,'condition'=>$condition]);
                        }else{
                            $list = $this->get_goods($catename);
                            $new_list = $this->save_goods($list['data']);
                            if($list['code']==0){
                                $condition = $this->get_condition($id,$new_list,1);
                                return json(['code'=>-1,'id'=>0,'msg'=>'搜索成功','data'=>$new_list,'condition'=>$condition]);
                            }elseif($list['code']==-1){
                                return json(['code'=>-2,'id'=>0,'msg'=>'暂无信息','data'=>'','condition'=>'']);
                            }
                        }
                    }else{
                        return json(['code'=>-2,'id'=>0,'msg'=>'暂无信息','data'=>'','condition'=>'']);
                    }

                }
            }
            elseif($data['pa']==2){
                #获取城市
                $res = httpRequest('https://shop.gogo198.cn/collect_website/public/?s=api/gather/gettableinfo', ['id' => 5,'province_id'=>$data['province_id']]);
                $res2 = json_decode($res, true);
                $res = json_decode($res2['list'],true);
//                    session('city_list', $res);
//                    $request->session()->put('country_list', $res);
                if(!empty($res)){
                    return json(['code'=>0,'data'=>$res]);
                }else{
                    return json(['code'=>-1,'data'=>'']);
                }
            }
        }else{
            $cate_name = isset($data['cate_name'])?$data['cate_name']:'';
            $company_id = intval($data['company_id']);
            $company_type = intval($data['company_type']);

            return view('index/shop_frontend/tao_zg',compact('cate_name','company_id','company_type'));
        }
    }

    #我要咨询
    public function advice(Request $request){
        $dat = $request->except(['_token']);

        if($dat['code_origin'] != trim($dat['code_input'])){
            return json(['code'=>-1,'msg'=>'验证码不正确']);
        }

        if(empty($dat['name']) || empty($dat['email']) || empty($dat['mobile']) || empty($dat['content'])){
            return json(['code'=>-1,'msg'=>'请输入信息']);
        }

        if(!preg_match("/^1[34578]\d{9}$/", trim($dat['mobile']))){
            return json(['code'=>-1,'msg'=>'请输入正确的手机号码']);
        }

        if(!preg_match('/([\w\-]+\@[\w\-]+\.[\w\-]+)/',trim($dat['email']))){
            return json(['code'=>-1,'msg'=>'请输入正确的邮箱号码']);
        }

        $res = Db::name('website_message')->insert([
            'name'=>trim($dat['name']),
            'email'=>trim($dat['email']),
            'tel'=>trim($dat['mobile']),
            'remark'=>$dat['content'],
            'createtime'=>time(),
        ]);
        if($res){
            return json(['code'=>0,'msg'=>'提交成功！']);
        }else{
            return json(['code'=>-1,'msg'=>'提交失败！']);
        }
    }

    #关注我们详情
    public function social_detail(Request $request){
        $dat = input();
        $id = isset($dat['id'])?intval($dat['id']):0;
        $isframe = isset($dat['isframe'])?intval($dat['isframe']):0;
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);

        $origin_page = '/?s=merch/social_detail?id='.$id.'&company_id='.$company_id.'&company_type='.$company_type;

        if($request->isAjax()){

        }else{
            #获取配置信息
            $data = ['websites'=>$this->websites,'source_link'=>$this->source_link];

            $info = Db::name('website_contact')->where(['company_id'=>$this->websites['cid'],'id'=>$id])->find();

            return view('index/shop_frontend/social_detail',compact('data','id','info','isframe','origin_page','company_id','company_type'));
        }
    }

    #资质详情
    public function qualific(Request $request){
        $dat = input();
        $id = isset($dat['id'])?intval($dat['id']):0;
        $isframe = isset($dat['isframe'])?intval($dat['isframe']):0;
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);
        $origin_page = '/?s=merch/qualific?id='.$id.'&company_id='.$company_id.'&company_type='.$company_type;

        if($request->isAjax()){

        }else{
            #获取配置信息
            $data = ['websites'=>$this->websites,'source_link'=>$this->source_link];

            $info = Db::name('merchsite_qualification')->where(['company_id'=>$this->websites['cid'],'id'=>$id])->find();

            return view('index/shop_frontend/qualific',compact('data','id','info','isframe','origin_page','company_id','company_type'));
        }
    }

    #平台规则列表
    public function rule_list(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);

        if($request->isAjax()){
            $limit = $dat['limit'];
            $page = $dat['page'] - 1;

            if ($page != 0) {
                $page = $limit * $page;
            }
            $count = Db::name('website_platform_keywords')->order('id desc')->count();
            $rows = DB::name('website_platform_keywords as a')
                ->join('website_platform_type as b','b.id','=','a.type_id')
                ->limit($page.','.$limit)
                ->order('id desc')
                ->field(['a.*','b.name as type_name'])
                ->select();

            foreach($rows as $k=>$v){
                $rows[$k]['name'] = $v['type_name'].'['.$v['name'].']';
            }
            return json(['code'=>0,'count'=>$count,'data'=>$rows]);
        }else{
            $list = Db::name('website_platform_type')->select();

            foreach($list as $k=>$v){
                $list[$k]['children'] = Db::name('website_platform_keywords')->where(['type_id'=>$v['id']])->select();

                foreach($list[$k]['children'] as $k2=>$v2){
                    $list[$k]['children'][$k2]['children'] = Db::name('website_platform_rule')->where(['type_id'=>$v['id'],'key_id'=>$v2['id'],'pid'=>0])->select();
                }
            }

            $origin_page = '/?s=merch/rule_list'.'&company_id='.$company_id.'&company_type='.$company_type;;

            #获取配置信息
            $data['websites'] = $this->websites;
            $data['source_link'] = $this->source_link;

            return view('index/shop_frontend/rule_list',compact('data','list','page_info','origin_page','company_id','company_type'));
        }
    }

    #规则版本列表
    public function version_list(Request $request){
        $dat = input();
        $pid = isset($dat['pid'])?intval($dat['pid']):0;
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);

        if(isset($dat['pa'])){
            $limit = $dat['limit'];
            $page = $dat['page'] - 1;

            if ($page != 0) {
                $page = $limit * $page;
            }
            $count = Db::name('website_platform_rule')->whereRaw('(pid='.$pid.' and status=0) or id='.$pid)->count();
            $rows = DB::name('website_platform_rule')
                ->whereRaw('(pid='.$pid.' and status=0) or id='.$pid)
                ->limit($page,$limit)
                ->order('createtime desc')
                ->select();

            foreach($rows as $k=>$v){
                $rows[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);
            }
            return json(['code'=>0,'count'=>$count,'data'=>$rows]);
        }else{
            $history = Db::name('website_platform_rule')->where(['id'=>$pid])->find();

            #获取配置信息
            $data['websites'] = $this->websites;
            $data['source_link'] = $this->source_link;

            $origin_page = '/?s=merch/version_list?pid='.$pid.'&company_id='.$company_id.'&company_type='.$company_type;;

            return view('index/shop_frontend/version_list',compact('data','pid','page_info','history','origin_page','company_id','company_type'));
        }
    }

    #平台规则详情
    public function rule_detail(Request $request){
        $dat = input();
        $foid = isset($dat['foid'])?intval($dat['foid']):0;
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);

        if($request->isAjax()){
            $data = Db::name('website_platform_rule')->where(['id'=>$dat['id']])->find();

            $data['content'] = json_decode($data['content'],true);
            $content = [];
            $first = [];
            $second = [];
            foreach($data['content'] as $k=>$v){
                if($v['pnum']==$dat['parag_num']){
                    array_push($first,[
                        'title'=>$v['title'],
                        'parag_num'=>$v['parag_num'],
                        'pnum'=>$v['pnum'],
                        'content'=>$v['content'],
                        'children'=>[],
                    ]);
                }else{
                    array_push($second,[
                        'title'=>$v['title'],
                        'parag_num'=>$v['parag_num'],
                        'pnum'=>$v['pnum'],
                        'content'=>$v['content'],
                        'children'=>[],
                    ]);
                }
            }

            #最多嵌套3层
            foreach($first as $k=>$v){
                foreach($second as $k2=>$v2){
                    if($v['parag_num']==$v2['pnum']){
                        #1.1.
                        array_push($first[$k]['children'],$v2);
                    }else{
                        foreach($first[$k]['children'] as $k3=>$v3){
                            if($v3['parag_num']==$v2['pnum']){
                                #1.1.1.
                                array_push($first[$k]['children'][$k3]['children'],[
                                    'title'=>$v2['title'],
                                    'parag_num'=>$v2['parag_num'],
                                    'pnum'=>$v2['pnum'],
                                    'content'=>$v2['content'],
                                    'children'=>[],
                                ]);
                            }
                        }
                    }
                }
            }
            $content = $first;
            return json(['code'=>0,'data'=>$content]);
        }else{
            #获取配置信息
            $data['websites'] = $this->websites;
            $data['source_link'] = $this->source_link;

            #规则内容
            $rule = Db::name('website_platform_rule')->where(['id'=>$dat['id']])->find();

            #分享
            $rule['url'] = 'https://'.$_SERVER['HTTP_HOST'].$_SERVER["REQUEST_URI"];
            $rule['desc'] = $rule['version'];
            $rule['name'] = $rule['rule_name'];
            $rule['url_this'] = 'https://'.$_SERVER['HTTP_HOST'].$_SERVER["REQUEST_URI"];
            $signPackage = weixin_share($rule);

            #序言
            if($rule['is_preamble']==1){
                $rule['preamble_con'] = json_decode($rule['preamble_con'],true);
            }
            $rule['content'] = json_decode($rule['content'],true);
            #整理树形结构代码
            if($rule['type']==1){
                $first = [];
                $second = [];
                foreach($rule['content'] as $k=>$v){
                    if($v['pnum']==0){
                        array_push($first,[
                            'title'=>$v['title'],
                            'parag_num'=>$v['parag_num'],
                            'pnum'=>$v['pnum'],
                            'content'=>$v['content'],
                            'children'=>[],
                        ]);
                    }else{
                        array_push($second,[
                            'title'=>$v['title'],
                            'parag_num'=>$v['parag_num'],
                            'pnum'=>$v['pnum'],
                            'content'=>$v['content'],
                            'children'=>[],
                        ]);
                    }
                }

                #最多嵌套3层
                foreach($first as $k=>$v){
                    foreach($second as $k2=>$v2){
                        if($v['parag_num']==$v2['pnum']){
                            #1.1.
                            array_push($first[$k]['children'],$v2);
                        }else{
                            foreach($first[$k]['children'] as $k3=>$v3){
                                if($v3['parag_num']==$v2['pnum']){
                                    #1.1.1.
                                    array_push($first[$k]['children'][$k3]['children'],[
                                        'title'=>$v2['title'],
                                        'parag_num'=>$v2['parag_num'],
                                        'pnum'=>$v2['pnum'],
                                        'content'=>$v2['content'],
                                        'children'=>[],
                                    ]);
                                }
                            }
                        }
                    }
                }
                $rule['content2'] = $first;
            }

            $origin_page = '/?s=merch/rule_detail&id='.$dat['id'].'&foid='.$foid.'&company_id='.$company_id.'&company_type='.$company_type;

            #是否有配置跳转其他应用
            $footerInfo = Db::connect($this->config)->name('footer_body')->where(['id'=>$foid])->find();

            if(isset($footerInfo['have_link'])){
                $footerInfo['link'] = $this->getAppLink(2,['other_navbar'=>$footerInfo['content_id'],'company_id'=>$company_id,'company_type'=>$company_type]);
            }

            return view('index/shop_frontend/rule_detail',compact('rule','data','signPackage','page_info','footerInfo','origin_page','company_id','company_type'));
        }
    }

    #获取弹框内容
    public function getFrame(Request $request){
        $data = input();
        $id = intval($data['id']);
        $type = intval($data['type']);
        $company_id = intval($data['company_id']);
        $company_type = intval($data['company_type']);

        if($type==0){
            $list = Db::connect($this->config)->name('frame_body')->where(['pid'=>$id])->select();

            return json(['code'=>0,'list'=>$list]);
        }
        elseif($type==99){
            #提示框图片
            $adv = Db::connect($this->config)->name('frame_adv')->where(['id'=>$id])->find();

            return json(['code'=>0,'adv'=>$adv]);
        }
        else{
            $list = Db::connect($this->config)->name('frame_body')->where(['type'=>$type,'pid'=>0,'id'=>$id])->order('displayorder asc')->select();

            foreach($list as $k=>$v){
                if($v['id']==$id){
                    $list[$k]['children'] = Db::connect($this->config)->name('frame_body')->where(['pid'=>$id])->select();

                    foreach($list[$k]['children'] as $k2=>$v2){
                        $list[$k]['children'][$k2]['link'] = $this->getAppLink(2,['other_navbar'=>$v2['app_id'],'company_id'=>$company_id,'company_type'=>$company_type]);
                        if(strpos($list[$k]['children'][$k2]['link'],'?') !== false){
                            $list[$k]['children'][$k2]['link'] .= '&isframe=1';
                        }else{
                            $list[$k]['children'][$k2]['link'] .= '?isframe=1';
                        }
                    }
                }

                if($v['id']==11){
                    #社交平台（获取后台配置的社交平台数据）
                    $list[$k]['children'] = Db::name('website_contact')->where(['system_id'=>3])->select();

                    foreach($list[$k]['children'] as $k2=>$v2){
                        if($v2['type']==2){
                            $list[$k]['children'][$k2]['link'] = '/?s=merch/social_detail&id='.$v2['id'].'&isframe=1&company_id='.$company_id.'&company_type='.$company_type;
                        }
                    }
                }

                if($v['id']==21) {
                    #搜索中心（获取后台配置的搜索管理数据）
                    $list[$k]['children'] = Db::connect($this->config)->name('search_list')->select();

                    foreach($list[$k]['children'] as $k2=>$v2){
                        $list[$k]['children'][$k2]['link'] = '/?s=merch/search_list&id='.$v2['id'].'&isframe=1&company_id='.$company_id.'&company_type='.$company_type;
                    }
                }
            }
            $adv = Db::connect($this->config)->name('frame_adv')->where(['type'=>$type])->find();

            return json(['code'=>0,'list'=>$list,'adv'=>$adv]);
        }
    }

    public function getAppLink($go=0,$data=[],$type=''){
        if($go==1){
            #第三方链接
            if(isset($data['other_link'])){
                return $data['other_link'];
            }
            elseif(isset($data['origin_link'])){
                return $data['origin_link'];
            }
            else{
                return $data['link'];
            }
        }elseif($go==2){
            #菜单（应用）链接
            $link = Db::connect($this->config)->name('guide_frame')->where(['id'=>$data['other_navbar']])->find();

            return $link['link'];
        }elseif($go==3){
            #图文链接
            return '/?s=merch/txt_detail&id='.$data['other_pic'].'&type='.$type.'&oid='.$data['id'].'&company_id='.$data['company_id'].'&company_type='.$data['company_type'];
        }elseif($go==4){
            #消息链接
            return '/?s=merch/msg_detail&id='.$data['other_msg'].'&type='.$type.'&oid='.$data['id'].'&company_id='.$data['company_id'].'&company_type='.$data['company_type'];
        }elseif($go==5){
            #店铺链接
            return '/?s=merch/shop_detail&id='.isset($data['other_shop'])??$data['other_shop'];
        }elseif($go==6){
            #政策链接
            return '/?s=merch/policy_detail&id='.$data['other_privacy'].'&type='.$type.'&oid='.$data['id'].'&company_id='.$data['company_id'].'&company_type='.$data['company_type'];
        }
    }
}