<?php
namespace app\index\controller;

use think\Request;
use think\Db;
use think\Controller;
use think\Log;

class Merch
{
    public $websites;
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
        $this->websites['domain'] = 'https://'.$domain.'/?cid='.$cid;
        $this->websites['rand'] = rand(11111,99999);
        $this->websites['info'] = Db::name('website_basic')->where(['company_id'=>$cid,'company_type'=>$company_type])->find();

        #获取公示信息
        $this->websites['info']['publicity_info'] = json_decode($this->websites['info']['publicity_info'],true);

        #获取搜索结果展示版式
        if(!empty($this->websites['info']['search_format'])){
            $this->websites['info']['search_format'] = json_decode($this->websites['info']['search_format'],true);
        }else{
            $this->websites['info']['search_format'] = [5,5];
        }

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
                    $citys = Db::name('website_world_time')->where(['is_show'=>0])->order('displayorder asc')->group('contryCn')->select();
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
                #商家上架信息==end

                foreach($this->websites['guide'][$k]['children'] as $k2=>$v2){
                    if($v2['go_other']==2){
                        #商品
                        $this->websites['guide'][$k]['children'][$k2]['info'] = Db::connect($this->config)->name('goods')->where(['goods_id'=>$v2['other_goods'],'goods_status'=>1])->find();

                        $sku_prices = Db::connect($this->config)->name('goods_sku')->where(['sku_id'=>$this->websites['guide'][$k]['children'][$k2]['info']['sku_id']])->find()['sku_prices'];

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

        $data['source_link'] = '//dtc.gogo198.net';
        $data['page_type'] = 1;
//        dd($data);
        return view('index/shop_frontend/merch_shop_index',compact('data','company_id','company_type'));
    }
}