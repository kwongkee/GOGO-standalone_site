<?php
namespace app\index\controller;

use think\Request;
use think\Db;
use think\Controller;
use app\index\model\Parceltask;
use app\index\model\WebsiteBasic;
use think\Log;
use think\Cache;

class Index extends Controller
{
    public $website_name='';
    public $website_keywords='';
    public $website_description='';
    public $website_ico='';
    public $website_sico='';
    public $website_tel='';
    public $website_email='';
    public $website_copyright='';
    public $website_color='';
    public $website_color_inner='';
    public $website_colorword='';
    public $website_coloradorn='';
    public $website_colorhead='';
    public $website_inpic='';
    public $website_contact=[];
    public $website_canonical='';
    public $website_og='';
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
    public $company_id = 0;
    public $company_type = 0;
    // protected $website = [];
    
    public function initialize(){
        parent::initialize();
    }
    
    #查询当前网址在系统中的配置
    public function __construct(Request $request){
        parent::__construct();
        
        $dat = input();
        $this->company_id = isset($dat['company_id'])?intval($dat['company_id']):0;
        $this->company_type = isset($dat['company_type'])?intval($dat['company_type']):0;
        if(!isset($_SERVER['HTTPS'])){ header("Location: https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); exit; }
        
        #监测有无设置语言
        if(session('lang') == null){
            session('lang','zh');
        }

        if($this->company_id==0){
            $website = Db::name('website_basic')->where(['id'=>9])->find();

            $this->website_contact = Db::name('website_contact')->where(['system_id'=>9])->select();
        }
        else{
            $website = Db::name('website_basic')->where(['company_id'=>$this->company_id,'company_type'=>$this->company_type])->find();
            $this->website_contact = [];
            session('company_id',$this->company_id);
            session('company_type',$this->company_type);
        }
        if(!empty($website)){
            $website['name'] = json_decode($website['name'],true);
            $website['keywords'] = json_decode($website['keywords'],true);
            $website['desc'] = json_decode($website['desc'],true);
            $website['copyright'] = json_decode($website['copyright'],true);
        }

        $this->website_name = $website['name'][session('lang')];
        $this->website_keywords = $website['keywords'][session('lang')];
        $this->website_description = $website['desc'][session('lang')];
        $this->website_ico = $website['logo'];
        $this->website_sico = $website['slogo'];
        $this->website_tel = $website['mobile'];
        $this->website_email = $website['email'];
        $this->website_copyright = $website['copyright'][session('lang')];
        $this->website_color = $website['color'];
        $this->website_color_inner = $website['color_inner'];
        $this->website_colorword = $website['color_word'];
        $this->website_coloradorn = $website['color_adorn'];
        $this->website_colorhead = $website['color_head'];
        $this->website_inpic = $website['inpic'];

        $current_url = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        // 清理参数，保留主要路径
        $canonical = preg_replace('/(\?.*)$/', '', $current_url);
        if (substr($canonical, -1) !== '/') $canonical .= '/';
        
        $this->website_canonical = '<link rel="canonical" href="' . $canonical . '">';
        $this->website_og = '
            <meta property="og:title" content="'.$this->website_name.'">
            <meta property="og:description" content="'.$this->website_description.'">
            <meta property="og:image" content="https://shop.gogo198.cn/'.$this->website_ico.'">
            <meta property="og:url" content="'.$current_url.'">
            <meta property="og:type" content="website">
        ';
        #日志记录
//        platform_log($request);
    }
    
    public function _empty(){
        return redirect("https://dtc.gogo198.net/", 301);
    }

    public function search() {
        $keyword = input('get.keyword');
        $where = ['title' => ['like', "%{$keyword}%"]]; // 修复：参数绑定
        $list = Db::name('product')->where($where)->select();
        $this->assign('list', $list);
        return $this->fetch();
    }

    #网站菜单栏目
    public function menu(){
        $menu = Db::name('website_navbar')->where(['system_id'=>9,'pid'=>0])->order('displayorder,id asc')->select();
        foreach($menu as $k=>$v){
            $menu[$k]['name'] = json_decode($v['name'],true)[session('lang')];
            $menu[$k]['childMenu'] = $this->getDownMenu($v['id']);
        }
        return $menu;
    }
    
    #下级菜单
    public function getDownMenu($id){
        $cmenu = Db::name('website_navbar')->where(['pid'=>$id])->order('displayorder,id asc')->select();
        foreach($cmenu as $k=>$v){
            $cmenu[$k]['name'] = json_decode($v['name'],true)[session('lang')];
            $cmenu[$k]['childMenu'] = Db::name('website_navbar')->where(['pid'=>$v['id']])->order('displayorder,id asc')->select();
            foreach($cmenu[$k]['childMenu'] as $k2=>$v2){
                $cmenu[$k]['childMenu'][$k2]['name'] = json_decode($v2['name'],true)[session('lang')];
                $cmenu[$k]['childMenu'][$k2]['childMenu'] = Db::name('website_navbar')->where(['pid'=>$v2['id']])->order('displayorder,id asc')->select();
                foreach($cmenu[$k]['childMenu'][$k2]['childMenu'] as $k3=>$v3){
                    $cmenu[$k]['childMenu'][$k2]['childMenu'][$k3]['name'] = json_decode($v3['name'],true)[session('lang')];
                }
            }
        }
        return $cmenu;
    }
    
    #随机码
    public function random_str(){
        return rand(11111,99999);
    }
    
    #面包削
    public function bread($id){
        #找上级
        $navbar_menu = '';
        $now_level = Db::name('website_navbar')->where('id',$id)->find();
        $now_level['name'] = json_decode($now_level['name'],true);
        $now_level2 = [];$now_level3 = [];$now_level4 = [];$now_level5 = [];$now_level6 = [];
        if(!empty($now_level['pid'])){
            $now_level2 = Db::name('website_navbar')->where('id',$now_level['pid'])->find();
            $now_level2['name'] = json_decode($now_level2['name'],true);
            if(!empty($now_level2['pid'])){
                $now_level3 = Db::name('website_navbar')->where('id',$now_level2['pid'])->find();
                $now_level3['name'] = json_decode($now_level3['name'],true);
                if(!empty($now_level3['pid'])){
                    $now_level4 = Db::name('website_navbar')->where('id',$now_level3['pid'])->find();
                    $now_level4['name'] = json_decode($now_level4['name'],true);
                    if(!empty($now_level4['pid'])){
                        $now_level5 = Db::name('website_navbar')->where('id',$now_level4['pid'])->find();
                        $now_level5['name'] = json_decode($now_level5['name'],true);
                        if(!empty($now_level5['pid'])){
                            $now_level6 = Db::name('website_navbar')->where('id',$now_level6['pid'])->find();
                            $now_level6['name'] = json_decode($now_level6['name'],true);
                        }
                    }
                }
            }
        }
        $count = 0;
        if(!empty($now_level)){
            $count += 1;
            $navbar_menu .= '<a href="?s=index/detail&company_id='.$this->company_id.'&company_type='.$this->company_type.'&id='.$now_level['id'].'">'.$now_level['name'][session('lang')].'</a>&nbsp;<span style="color:#d1a575;">\</span>&nbsp;';
        }
        if(!empty($now_level2)){
            $count += 1;
            $navbar_menu = '<a href="?s=index/detail&company_id='.$this->company_id.'&company_type='.$this->company_type.'&id='.$now_level2['id'].'">'.$now_level2['name'][session('lang')].'</a>&nbsp;<span style="color:#d1a575;">\</span>&nbsp;'.$navbar_menu;
        }
        if($count<2){
            if(!empty($now_level3)){
                $count += 1;
                $navbar_menu = '<a href="?s=index/detail&company_id='.$this->company_id.'&company_type='.$this->company_type.'&id='.$now_level3['id'].'">'.$now_level3['name'][session('lang')].'</a>&nbsp;<span style="color:#d1a575;">\</span>&nbsp;'.$navbar_menu;
            }
        }
        if($count<2){
            if(!empty($now_level4)){
                $count += 1;
                $navbar_menu = '<a href="?s=index/detail&company_id='.$this->company_id.'&company_type='.$this->company_type.'&id='.$now_level4['id'].'">'.$now_level4['name'][session('lang')].'</a>&nbsp;<span style="color:#d1a575;">\</span>&nbsp;'.$navbar_menu;
            }    
        }
        if($count<2){
            if(!empty($now_level5)){
                $count += 1;
                $navbar_menu = '<a href="?s=index/detail&company_id='.$this->company_id.'&company_type='.$this->company_type.'&id='.$now_level5['id'].'">'.$now_level5['name'][session('lang')].'</a>&nbsp;<span style="color:#d1a575;">\</span>&nbsp;'.$navbar_menu;
            }
        }
        if($count<2){
            if(!empty($now_level6)){
                $count += 1;
                $navbar_menu = '<a href="?s=index/detail&company_id='.$this->company_id.'&company_type='.$this->company_type.'&id='.$now_level6['id'].'">'.$now_level6['name'][session('lang')].'</a>&nbsp;<span style="color:#d1a575;">\</span>&nbsp;'.$navbar_menu;
            }
        }
        return substr($navbar_menu,0,-49);
        return rtrim($navbar_menu,'<span style="color:#d1a575;">\</span>&nbsp;');
    }
    
    #底部社交链接
    public function get_footer_link(){
        $link['facebook'] = Db::name('website_navbar')->where('id',43)->find()['url'];
        $link['linkedin'] = Db::name('website_navbar')->where('id',45)->find()['url'];
        $link['instagram'] = Db::name('website_navbar')->where('id',44)->find()['url'];
        $link['ziwoyou'] = Db::name('website_navbar')->where('id',53)->find()['url'];
        $link['sell_global'] = Db::name('website_navbar')->where('id',54)->find()['url'];
        $link['buy_global'] = Db::name('website_navbar')->where('id',55)->find()['url'];
        
        $link['qualific'] = Db::name('website_qualifications')->order('displayorder','asc')->select();
        foreach($link['qualific'] as $k=>$v){
            $link['qualific'][$k]['name'] = json_decode($v['name'],true)[session('lang')];
        }
        return $link;
    }
    
    #首页
    public function index(Request $request)
    {
        // session('account',[]);
//        dd(base64_encode('https://rte.gogo198.cn/uploads/knowledge_files/20250521/huasa.jpg'));
        $data = input();
        $company_id = $this->company_id;
        
        $cacheKey = 'website_basic_' . $company_id;
        $ishave_website = Cache::get($cacheKey);
        if ($ishave_website === false) {
            $ishave_website = Db::name('website_basic')->where(['company_id' => $company_id])->count();
            Cache::set($cacheKey, $ishave_website, 3600); // Cache for 1 hour
        }
        if (!$ishave_website) {
            return $this->error('Website not configured');
        }
        
        #授权登录跳转
        if(isset($data['authid'])){
            $ip = $_SERVER['REMOTE_ADDR'];
            $device = $_SERVER['HTTP_USER_AGENT'];
            $info = Db::name('website_login_log')->where(['ip'=>$ip,'device'=>$device,'status'=>1,'id'=>intval($data['authid'])])->find();
            if($info){
                $account = Db::name('website_user')->where(['email'=>$info['account']])->find();
                session('account',$account);
            }

            header("Location: /");
        }

        #栏目
        $menu = $this->menu();
        
        #轮播图
        $rotate = Db::name('website_rotate')->where(['system_id'=>9])->select();
        foreach($rotate as $k=>$v){
            $rotate[$k]['link'] = $this->getAppLink($v['go_other'],$v,'lunbo');
        }

        $website['title'] = $this->website_name;
        $website['keywords'] = $this->website_keywords;
        $website['description'] = $this->website_description;
        $website['ico'] = $this->website_ico;
        $website['sico'] = $this->website_sico;
        $website['tel'] = $this->website_tel;
        $website['email'] = $this->website_email;
        $website['copyright'] = $this->website_copyright;
        $website['color'] = $this->website_color;
        $website['color_word'] = $this->website_colorword;
        $website['color_adorn'] = $this->website_coloradorn;
        $website['color_head'] = $this->website_colorhead;
        $website['website_contact'] = $this->website_contact;
        $website['website_canonical'] = $this->website_canonical;
        $website['website_og'] = $this->website_og;

        #首页板块
        $services = Db::name('website_index')->where(['system_id'=>9])->order('displayorder asc')->select();

        foreach($services as $k=>$v){
            if($v['format_type']==2){
                #内容切换框
                $services[$k]['content'] = json_decode($v['content'],true);

                #按键内容
                $services[$k]['btn_content'] = json_decode($v['btn_content'],true);
                foreach($services[$k]['btn_content'] as $k2=>$v2){
                    $services[$k]['btn_content'][$k2]['id'] = $v['id'];
                    $services[$k]['btn_content'][$k2]['link'] = $this->getAppLink($v2['go_other'],$services[$k]['btn_content'][$k2],'btn');
                }
            }
            elseif($v['format_type']==4){
                #标题和描述折叠框
                $services[$k]['fq_content'] = json_decode($v['fq_content'],true);
            }
            elseif($v['format_type']==5){
                #一问一答图文轮播
                $services[$k]['fq_category_content'] = json_decode($v['fq_category_content'],true);
                foreach($services[$k]['fq_category_content'] as $k2=>$v2){
                    $services[$k]['fq_category_content'][$k2]['fq_list'] = Db::name('website_image_txt')->where('id in ('.$v2['fq_ids'].')')->field('id,name,createtime')->order('id desc')->select();
                    foreach($services[$k]['fq_category_content'][$k2]['fq_list'] as $k3=>$v3){
                        $services[$k]['fq_category_content'][$k2]['fq_list'][$k3]['name'] = json_decode($v3['name'],true)['zh'];
                        $services[$k]['fq_category_content'][$k2]['fq_list'][$k3]['createtime'] = date('Y-m-d',$v3['createtime']);
                    }
                }

//                $services[$k]['fq_list'] = Db::name('website_image_txt')->where('id in ('.$v['fq_ids'].')')->field('id,name,createtime')->order('id desc')->select();
//                foreach($services[$k]['fq_list'] as $k2=>$v2){
//                    $services[$k]['fq_list'][$k2]['name'] = json_decode($v2['name'],true)['zh'];
//                    $services[$k]['fq_list'][$k2]['createtime'] = date('Y-m-d',$v2['createtime']);
//                }

            }
            elseif($v['format_type']==6){
                $services[$k]['card1_content'] = json_decode($v['card1_content'],true);
            }
            elseif($v['format_type']==7){
                $services[$k]['card2_content'] = json_decode($v['card2_content'],true);
            }
            elseif($v['format_type']==8){
                $services[$k]['card3_content'] = json_decode($v['card3_content'],true);
            }

            if($v['format']==1){
                $services[$k]['info'] = Db::name('website_navbar')->where('id',$v['navbar_id'])->field('id,name,desc,format,color,go_other,other_link,other_navbar,thumb')->find();
                $services[$k]['info']['name'] = json_decode($services[$k]['info']['name'],true)[session('lang')];
                $services[$k]['info']['desc'] = isset($services[$k]['info']['desc'])?json_decode($services[$k]['info']['desc'],true)[session('lang')]:'';
                // $services[$k]['info']['desc'] = explode('、',$services[$k]['info']['desc']);
                $services[$k]['info']['children'] = Db::name('website_navbar')->where('pid',$services[$k]['info']['id'])->field('id,name,desc,thumb,format,color,go_other,other_link,other_navbar')->select();
                // dd($services[$k]['info']['children']);
                foreach($services[$k]['info']['children'] as $k2=>$v2){
                    $services[$k]['info']['children'][$k2]['name'] = json_decode($services[$k]['info']['children'][$k2]['name'],true)[session('lang')];
                    $services[$k]['info']['children'][$k2]['desc'] = json_decode($services[$k]['info']['children'][$k2]['desc'],true)[session('lang')];
                    $services[$k]['info']['children'][$k2]['desc'] = explode('、',$services[$k]['info']['children'][$k2]['desc']);
                    $services[$k]['info']['children'][$k2]['children'] = Db::name('website_navbar')->where('pid',$v2['id'])->field('id,name,desc,thumb,format,color,go_other,other_link,other_navbar')->select();
                    foreach($services[$k]['info']['children'][$k2]['children'] as $k3=>$v3){
                        $services[$k]['info']['children'][$k2]['children'][$k3]['name'] = json_decode($services[$k]['info']['children'][$k2]['children'][$k3]['name'],true)[session('lang')];
                        $services[$k]['info']['children'][$k2]['children'][$k3]['desc'] = json_decode($services[$k]['info']['children'][$k2]['children'][$k3]['desc'],true)[session('lang')];
                        // $services[$k]['info']['children'][$k2]['children'][$k3]['desc'] = explode('、',$services[$k]['info']['children'][$k2]['children'][$k3]['desc']);
                    }
                }
            }
            elseif($v['format']==2){
                $services[$k]['info'] = Db::name('website_navbar')->where('id',$v['navbar_id'])->field('id,name,desc,thumb,format,color,go_other,other_link,other_navbar,thumb')->find();
                $services[$k]['info']['name'] = json_decode($services[$k]['info']['name'],true)[session('lang')];
                $services[$k]['info']['desc'] = json_decode($services[$k]['info']['desc'],true)[session('lang')];
                $services[$k]['info']['children'] = Db::name('website_navbar')->where('pid',$services[$k]['info']['id'])->field('id,name,desc,thumb,format,color,go_other,other_link,other_navbar')->select();
                foreach($services[$k]['info']['children'] as $k2=>$v2){
                    $services[$k]['info']['children'][$k2]['name'] = json_decode($services[$k]['info']['children'][$k2]['name'],true)[session('lang')];
                    $services[$k]['info']['children'][$k2]['desc'] = json_decode($services[$k]['info']['children'][$k2]['desc'],true)[session('lang')];
                    $services[$k]['info']['children'][$k2]['children'] = Db::name('website_navbar')->where('pid',$v2['id'])->field('id,name,desc,thumb,format,color,go_other,other_link,other_navbar')->select();
                    foreach($services[$k]['info']['children'][$k2]['children'] as $k3=>$v3){
                        $services[$k]['info']['children'][$k2]['children'][$k3]['name'] = json_decode($services[$k]['info']['children'][$k2]['children'][$k3]['name'],true)[session('lang')];
                        $services[$k]['info']['children'][$k2]['children'][$k3]['desc'] = json_decode($services[$k]['info']['children'][$k2]['children'][$k3]['desc'],true)[session('lang')];
                    }
                }
            }
            elseif($v['format']==0){
                if($v['navbar_id']=='A1'){
                    $services[$k]['info'] = [];
                    $services[$k]['info']['name'] = '';
                    $services[$k]['info']['desc'] = '';
                    $services[$k]['info']['children'] = [];
                }elseif($v['navbar_id']=='A2'){
                    $services[$k]['info'] = [];
                    $services[$k]['info']['name'] = '常见问题';
                    $services[$k]['info']['desc'] = '';
                    $services[$k]['info']['children'] = [];
                }
            }
        }
//        dd($services);
        #关于购购
        $services2['cross_about'] = Db::name('website_navbar')->where('id',2)->field('id,name,desc,thumb,format,color,go_other,other_link,other_navbar')->find();
        $services2['cross_about']['name'] = json_decode($services2['cross_about']['name'],true)[session('lang')];
        $services2['cross_about']['desc'] = json_decode($services2['cross_about']['desc'],true)[session('lang')];
        if(session('lang')=='en'){
            $services2['cross_about']['desc'] = explode('.',$services2['cross_about']['desc']);    
        }else{
            $services2['cross_about']['desc'] = explode('。',$services2['cross_about']['desc']);   
        }
        
        $services2['cross_about']['children'] = Db::name('website_navbar')->where('pid',$services2['cross_about']['id'])->field('id,name,desc,thumb,go_other,other_link,other_navbar')->select();
        foreach($services2['cross_about']['children'] as $k=>$v){
            $services2['cross_about']['children'][$k]['name'] = json_decode($services2['cross_about']['children'][$k]['name'],true)[session('lang')];
            $services2['cross_about']['children'][$k]['desc'] = json_decode($services2['cross_about']['children'][$k]['desc'],true)[session('lang')];
        }
      
        #底部社交链接
        $link = $this->get_footer_link();

        #发现轮播图
        $discovery_rotate = Db::name('website_discovery_list')->where(['system_id'=>9,'company_id'=>0])->select();

        #新闻列表
        // $timestamp = strtotime("yesterday");
        // $news = Db::name('website_crossborder_news')->where(['time'=>date('Y-m-d',$timestamp),'status'=>1])->order('id','desc')->limit(50)->select();
        // if(empty($news)){
        //     $news = Db::name('website_crossborder_news')->where(['status'=>1])->order('id','desc')->limit(50)->select();
        // }
        
        $newsCacheKey = 'crossborder_news_latest';
        $news = Cache::get($newsCacheKey);
        if ($news === false) {
            $yesterday = date('Y-m-d', strtotime('-1 day'));
            $news = Db::name('website_crossborder_news')->whereRaw("time = '".$yesterday."' and status=1")->order('id desc')->limit(50)->select();
            if (empty($news)) {
                $news = Db::name('website_crossborder_news')->whereRaw("status = 1")->order('id desc')->limit(50)->select();
            }
            Cache::set($newsCacheKey, $news, 86400); // Cache for 1 day
        }

        #分享
        $data['url'] = 'https://'.$_SERVER['HTTP_HOST'].$_SERVER["REQUEST_URI"];
        $data['desc'] = $website['description'];
        $data['name'] = $website['title'];
        $data['url_this'] = 'https://'.$_SERVER['HTTP_HOST'].$_SERVER["REQUEST_URI"];
        $data['thumb'] = 'https://shop.gogo198.cn/collect_website/public/uploads/centralize/website_index/64a5282e9bdbf.png';
        $signPackage = weixin_share($data);

        return view('/index/index',compact('menu','rotate','website','services','services2','link','news','signPackage','discovery_rotate','company_id'));
    }

    #站点管理
    public function website_manage(Request $request){
        $data = input();
        $cid = isset($data['cid'])?base64_decode($data['cid']):0;
        $mid = isset($data['mid'])?base64_decode($data['mid']):0;

        if($mid>0){
            $account = Db::name('website_user')->where(['id'=>$mid])->find();
            session('account',$account);
        }

        $company = Db::name('website_user_company')->where(['user_id'=>session('account.id'),'status'=>0])->select();
        $mid = session('account.id');

        #独立站菜单
        $menuList = Db::name('centralize_manage_menu')->where(['auth_type'=>5])->select();

        $company_id = $this->company_id;
        $website['website_canonical'] = $this->website_canonical;
        $website['website_og'] = $this->website_og;

        return view('/index/website_manage',compact('company','mid','cid','menuList','website','company_id'));
    }

    #获取企业信息
    public function get_enterprise_info(Request $request){
        $data = input();

        $company = Db::name('website_user_company')->where(['id'=>intval($data['company_id'])])->find();

        $list = [];
        #获取权限
        if(!empty($company['webList'])){
            $company['webList'] = explode(',',$company['webList']);
            $list = Db::name('centralize_manage_menu')
                ->whereIn('id', $company['webList'])
                ->select();
        }

        if(empty($company['domain_name'])){
            $company['domain_name'] = 0;
        }
        else{
            $company['domain_name2'] = explode('.',$company['domain_name'])[1];
        }

        return json(['code'=>0,'list'=>$company,'list2'=>$list,'msg'=>'正在刷新...']);
    }

    #保存企业二级域名
    public function save_domainname(Request $request){
        $dat = input();

        $company_id = intval($dat['company_id']);
        $site_domain = trim($dat['site_domain']);
        $site_type = trim($dat['site_type']);

        $new_site_domain = 'www.'.$site_domain.$site_type;

        $ishave = Db::name('website_user_company')->where(['domain_name'=>$new_site_domain])->find();
        if(!empty($ishave)){
            return json(['code'=>-1,'msg'=>'二级域名【"'.$new_site_domain.'"】已存在。']);
        }


        $res = Db::name('website_user_company')->where(['id'=>$company_id])->update([
            'domain_name' => $new_site_domain
        ]);

        if($res){
            return json(['code'=>0,'msg'=>'保存成功']);
        }
    }

    #企业网站==============================================start
    #商家网站首页
    public function merch_website_index(Request $request)
    {
        $data = input();
        $company_id = intval($data['company_id']);
        $company_type = intval($data['company_type']); #企业类型，0商家商店，1商家网站

        $website['title'] = $this->website_name;
        $website['keywords'] = $this->website_keywords;
        $website['description'] = $this->website_description;
        $website['ico'] = $this->website_ico;
        $website['sico'] = $this->website_sico;
        $website['tel'] = $this->website_tel;
        $website['email'] = $this->website_email;
        $website['copyright'] = $this->website_copyright;
        $website['color'] = $this->website_color;
        $website['color_word'] = $this->website_colorword;
        $website['color_adorn'] = $this->website_coloradorn;
        $website['color_head'] = $this->website_colorhead;
        $website['website_contact'] = $this->website_contact;
        $website['website_canonical'] = $this->website_canonical;
        $website['website_og'] = $this->website_og;

        $ishave = Db::name('website_basic')->where(['company_id'=>$company_id,'company_type'=>$company_type])->find();
        if(empty($ishave)){
            $title = '资讯网站';
            $msg = '请先配置资讯网站信息后再访问';
            $setting_url = '/?s=index/website_official&company_id='.$company_id.'&company_type='.$company_type;

            return view('/index/setting',compact('setting_url','msg','title','website','company_id'));
        }

        #查询有无商城
        $ishave_website = Db::name('website_basic')->where(['company_id'=>$this->company_id,'company_type'=>0])->find();

        #授权登录跳转
        if(isset($data['authid'])){
            $ip = $_SERVER['REMOTE_ADDR'];
            $device = $_SERVER['HTTP_USER_AGENT'];
            $info = Db::name('website_login_log')->where(['ip'=>$ip,'device'=>$device,'status'=>1,'id'=>intval($data['authid'])])->find();
            if($info){
                $account = Db::name('website_user')->where(['email'=>$info['account']])->find();
                session('account',$account);
            }

            header("Location: /?s=index/merch_website_index&company_id=".$company_id);
        }

        #栏目
        $menu = $this->company_menu($company_id,$company_type);

        #轮播图
        $rotate = Db::name('website_rotate')->where(['company_id'=>$company_id,'company_type'=>$company_type])->select();
        foreach($rotate as $k=>$v){
            $rotate[$k]['link'] = $this->getAppLink($v['go_other'],$v,'lunbo');
        }

        #首页板块
        $services = Db::name('website_index')->where(['company_id'=>$company_id,'company_type'=>$company_type])->order('displayorder asc')->select();

        foreach($services as $k=>$v){
            if($v['format_type']==2){
                #内容切换框
                $services[$k]['content'] = json_decode($v['content'],true);

                #按键内容
                $services[$k]['btn_content'] = json_decode($v['btn_content'],true);
                foreach($services[$k]['btn_content'] as $k2=>$v2){
                    $services[$k]['btn_content'][$k2]['id'] = $v['id'];
                    $services[$k]['btn_content'][$k2]['link'] = $this->getAppLink($v2['go_other'],$services[$k]['btn_content'][$k2],'btn');
                }
            }
            elseif($v['format_type']==4){
                #标题和描述折叠框
                $services[$k]['fq_content'] = json_decode($v['fq_content'],true);
            }
            elseif($v['format_type']==5){
                #一问一答图文轮播
                $services[$k]['fq_category_content'] = json_decode($v['fq_category_content'],true);
                foreach($services[$k]['fq_category_content'] as $k2=>$v2){
                    $services[$k]['fq_category_content'][$k2]['fq_list'] = Db::name('website_image_txt')->where('id in ('.$v2['fq_ids'].')')->field('id,name,createtime')->order('id desc')->select();
                    foreach($services[$k]['fq_category_content'][$k2]['fq_list'] as $k3=>$v3){
                        $services[$k]['fq_category_content'][$k2]['fq_list'][$k3]['name'] = json_decode($v3['name'],true)['zh'];
                        $services[$k]['fq_category_content'][$k2]['fq_list'][$k3]['createtime'] = date('Y-m-d',$v3['createtime']);
                    }
                }

//                $services[$k]['fq_list'] = Db::name('website_image_txt')->where('id in ('.$v['fq_ids'].')')->field('id,name,createtime')->order('id desc')->select();
//                foreach($services[$k]['fq_list'] as $k2=>$v2){
//                    $services[$k]['fq_list'][$k2]['name'] = json_decode($v2['name'],true)['zh'];
//                    $services[$k]['fq_list'][$k2]['createtime'] = date('Y-m-d',$v2['createtime']);
//                }

            }
            elseif($v['format_type']==6){
                $services[$k]['card1_content'] = json_decode($v['card1_content'],true);
            }
            elseif($v['format_type']==7){
                $services[$k]['card2_content'] = json_decode($v['card2_content'],true);
            }
            elseif($v['format_type']==8){
                $services[$k]['card3_content'] = json_decode($v['card3_content'],true);
            }

            if($v['format']==1){
                $services[$k]['info'] = Db::name('website_navbar')->where('id',$v['navbar_id'])->field('id,name,desc,format,color,go_other,other_link,other_navbar,thumb')->find();
                $services[$k]['info']['name'] = json_decode($services[$k]['info']['name'],true)[session('lang')];
                $services[$k]['info']['desc'] = isset($services[$k]['info']['desc'])?json_decode($services[$k]['info']['desc'],true)[session('lang')]:'';
                // $services[$k]['info']['desc'] = explode('、',$services[$k]['info']['desc']);
                $services[$k]['info']['children'] = Db::name('website_navbar')->where('pid',$services[$k]['info']['id'])->field('id,name,desc,thumb,format,color,go_other,other_link,other_navbar')->select();
                // dd($services[$k]['info']['children']);
                foreach($services[$k]['info']['children'] as $k2=>$v2){
                    $services[$k]['info']['children'][$k2]['name'] = json_decode($services[$k]['info']['children'][$k2]['name'],true)[session('lang')];
                    $services[$k]['info']['children'][$k2]['desc'] = json_decode($services[$k]['info']['children'][$k2]['desc'],true)[session('lang')];
                    $services[$k]['info']['children'][$k2]['desc'] = explode('、',$services[$k]['info']['children'][$k2]['desc']);
                    $services[$k]['info']['children'][$k2]['children'] = Db::name('website_navbar')->where('pid',$v2['id'])->field('id,name,desc,thumb,format,color,go_other,other_link,other_navbar')->select();
                    foreach($services[$k]['info']['children'][$k2]['children'] as $k3=>$v3){
                        $services[$k]['info']['children'][$k2]['children'][$k3]['name'] = json_decode($services[$k]['info']['children'][$k2]['children'][$k3]['name'],true)[session('lang')];
                        $services[$k]['info']['children'][$k2]['children'][$k3]['desc'] = json_decode($services[$k]['info']['children'][$k2]['children'][$k3]['desc'],true)[session('lang')];
                        // $services[$k]['info']['children'][$k2]['children'][$k3]['desc'] = explode('、',$services[$k]['info']['children'][$k2]['children'][$k3]['desc']);
                    }
                }
            }
            elseif($v['format']==2){
                $services[$k]['info'] = Db::name('website_navbar')->where('id',$v['navbar_id'])->field('id,name,desc,thumb,format,color,go_other,other_link,other_navbar,thumb')->find();
                $services[$k]['info']['name'] = json_decode($services[$k]['info']['name'],true)[session('lang')];
                $services[$k]['info']['desc'] = json_decode($services[$k]['info']['desc'],true)[session('lang')];
                $services[$k]['info']['children'] = Db::name('website_navbar')->where('pid',$services[$k]['info']['id'])->field('id,name,desc,thumb,format,color,go_other,other_link,other_navbar')->select();
                foreach($services[$k]['info']['children'] as $k2=>$v2){
                    $services[$k]['info']['children'][$k2]['name'] = json_decode($services[$k]['info']['children'][$k2]['name'],true)[session('lang')];
                    $services[$k]['info']['children'][$k2]['desc'] = json_decode($services[$k]['info']['children'][$k2]['desc'],true)[session('lang')];
                    $services[$k]['info']['children'][$k2]['children'] = Db::name('website_navbar')->where('pid',$v2['id'])->field('id,name,desc,thumb,format,color,go_other,other_link,other_navbar')->select();
                    foreach($services[$k]['info']['children'][$k2]['children'] as $k3=>$v3){
                        $services[$k]['info']['children'][$k2]['children'][$k3]['name'] = json_decode($services[$k]['info']['children'][$k2]['children'][$k3]['name'],true)[session('lang')];
                        $services[$k]['info']['children'][$k2]['children'][$k3]['desc'] = json_decode($services[$k]['info']['children'][$k2]['children'][$k3]['desc'],true)[session('lang')];
                    }
                }
            }
            elseif($v['format']==0){
                if($v['navbar_id']=='A1'){
                    $services[$k]['info'] = [];
                    $services[$k]['info']['name'] = '';
                    $services[$k]['info']['desc'] = '';
                    $services[$k]['info']['children'] = [];
                }elseif($v['navbar_id']=='A2'){
                    $services[$k]['info'] = [];
                    $services[$k]['info']['name'] = '常见问题';
                    $services[$k]['info']['desc'] = '';
                    $services[$k]['info']['children'] = [];
                }
            }
        }

        #底部社交链接
//        $link = $this->get_footer_link();

        #发现轮播图
        $discovery_rotate = Db::name('website_discovery_list')->where(['company_id'=>$company_id,'company_type'=>$company_type])->select();

        #新闻列表
        $timestamp = strtotime("yesterday");
        $news = Db::name('website_crossborder_news')->where(['time'=>date('Y-m-d',$timestamp),'status'=>1])->order('id','desc')->limit(50)->select();
        if(empty($news)){
            $news = Db::name('website_crossborder_news')->where(['status'=>1])->order('id','desc')->limit(50)->select();
        }

        #分享
        $data['url'] = 'https://'.$_SERVER['HTTP_HOST'].$_SERVER["REQUEST_URI"];
        $data['desc'] = $website['description'];
        $data['name'] = $website['title'];
        $data['url_this'] = 'https://'.$_SERVER['HTTP_HOST'].$_SERVER["REQUEST_URI"];
        $data['thumb'] = 'https://shop.gogo198.cn/collect_website/public/uploads/centralize/website_index/64a5282e9bdbf.png';
        $signPackage = weixin_share($data);
        
        return view('/index/merch_index',compact('menu','rotate','website','services','services2','link','news','signPackage','discovery_rotate','company_id','company_type','ishave_website'));
    }

    #企业网站管理
    public function website_official(Request $request){
        $dat = input();
//        $typ = intval($dat['typ']);//企业类型，0商城，1网站
        $typ = 1;
        $company_id = intval($dat['company_id']);
        if (empty($company_id) || !is_numeric($company_id)) {
            return $this->error('无效的公司ID');
        }
        
        $company = Db::name('website_user_company')->where(['user_id'=>session('account.id'),'status'=>0])->select();
        $tab = isset($dat['tab'])?trim($dat['tab']):'website-config';

        $company_info = Db::name('website_user_company')->where(['id'=>$company_id])->find();
        if(empty($company_info['domain_name'])){
            $company_info['domain_name2'] = 'xxx';
        }
        else{
            $company_info['domain_name2'] = explode('.',$company_info['domain_name'])[1];
        }

        #企业网站-基本&页头页脚配置
        $model = new WebsiteBasic();
        $website_basic = $model->getByCompanyId($company_id, $typ);
        // $website_basic = Db::name('website_basic')->where(['company_id'=>$company_id,'company_type'=>$typ])->find();
        

        #企业网站-轮播图
        $rotate = Db::name('website_rotate')->where(['company_id'=>$company_id,'company_type'=>1])->select();
        foreach($rotate as $k=>$v){
            $rotate[$k]['title'] = json_decode($v['title'],true)['zh'];
        }

        #企业网站-网站频道
        $website_index = Db::name('website_index')->where(['company_id'=>$company_id,'company_type'=>$typ])->order('displayorder,id asc')->select();
        foreach($website_index as $k=>$v){
            if($v['navbar_id']=='A1'){
                $website_index[$k]['name'] = '发现轮播+信息切换框';
            }elseif($v['navbar_id']=='A2'){
                $website_index[$k]['name'] = '常见问题';
            }else{
                $name = Db::name('website_navbar')->where(['id'=>intval($v['navbar_id'])])->field('name')->find()['name'];
                $website_index[$k]['name'] = json_decode($name,true)['zh'];
            }
        }

        #企业网站-发现轮播
        $website_discovery = Db::name('website_discovery_list')->where(['company_id'=>$company_id,'company_type'=>$typ])->select();
        foreach($website_discovery as $k=>$v){
            $website_discovery[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);
        }

        #企业网站-会员管理
        $user = Db::name('website_user')->where(['company_id'=>$company_id])->select();
        
        $website['website_canonical'] = $this->website_canonical;
        $website['website_og'] = $this->website_og;

        return view('index/website/website_official',compact('company','company_info','user','company_id','rotate','website_basic','tab','website_index','website_discovery','website'));
    }

    #企业菜单
    public function menu_manage(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);

        $list = Db::name('website_navbar')->where(['company_id'=>$company_id,'company_type'=>$company_type])->order('displayorder,id asc')->select();
        if($company_type==1){
            #网站
            foreach($list as $k=>$v){
                $list[$k]['name'] = json_decode($v['name'],true)['zh'];
            }
        }

        return json(['code' => 0, 'msg' => '', 'count' => count($list), 'data' => $list]);
    }

    #保存网站配置
    public function save_website_basic(Request $request){
        $dat = input();

        if($request->isAjax()){
            $company_id = intval($dat['company_id']);
            $company_type = intval($dat['company_type']);

            $ishave = Db::name('website_basic')->where(['company_id'=>$company_id,'company_type'=>$company_type])->find();
            if(!empty($ishave)){
                #修改
                Db::name('website_basic')->where(['company_id'=>$company_id,'company_type'=>$company_type])->update([
                    'name'=>json_encode(['zh'=>trim($dat['name']['zh'])]),
                    'desc'=>json_encode(['zh'=>trim($dat['desc']['zh'])]),
                    'keywords'=>json_encode(['zh'=>trim($dat['keywords']['zh'])]),
                    'mobile'=>trim($dat['mobile']),
                    'email'=>trim($dat['email']),
                    'slogo'=>isset($dat['slogo_file'][0])?$dat['slogo_file'][0]:'',
                    'logo'=>isset($dat['logo_file'][0])?$dat['logo_file'][0]:'',
                ]);
            }else{
                #新增
                Db::name('website_basic')->insert([
                    'company_id'=>$company_id,
                    'company_type'=>$company_type,
                    'name'=>json_encode(['zh'=>trim($dat['name']['zh'])]),
                    'desc'=>json_encode(['zh'=>trim($dat['desc']['zh'])]),
                    'keywords'=>json_encode(['zh'=>trim($dat['keywords']['zh'])]),
                    'mobile'=>trim($dat['mobile']),
                    'email'=>trim($dat['email']),
                    'slogo'=>isset($dat['slogo_file'][0])?$dat['slogo_file'][0]:'',
                    'logo'=>isset($dat['logo_file'][0])?$dat['logo_file'][0]:'',
                ]);
            }

            return json(['code'=>0,'msg'=>'保存成功']);
        }
    }

    #保存页头页脚
    public function save_website_basic2(Request $request){
        $dat = input();

        if($request->isAjax()){
            $company_id = intval($dat['company_id']);
            $company_type = intval($dat['company_type']);

            $ishave = Db::name('website_basic')->where(['company_id'=>$company_id,'company_type'=>$company_type])->find();

            if(!empty($ishave)){
                #修改
                Db::name('website_basic')->where(['company_id'=>$company_id,'company_type'=>$company_type])->update([
                    'color'=>$dat['color'],
                    'color_inner'=>$dat['color_inner'],
                    'color_word'=>$dat['color_word'],
                    'color_head'=>$dat['color_head'],
                    'color_adorn'=>$dat['color_adorn'],
                    'copyright'=>isset($dat['copyright_zh'])?json_encode(['zh'=>$dat['copyright_zh']],true):'',
                ]);
            }else{
                #新增
                Db::name('website_basic')->insert([
                    'company_id'=>$company_id,
                    'company_type'=>$company_type,
                    'color'=>$dat['color'],
                    'color_inner'=>$dat['color_inner'],
                    'color_word'=>$dat['color_word'],
                    'color_head'=>$dat['color_head'],
                    'color_adorn'=>$dat['color_adorn'],
                    'copyright'=>isset($dat['copyright_zh'])?json_encode(['zh'=>$dat['copyright_zh']],true):'',
                ]);
            }

            return json(['code'=>0,'msg'=>'保存成功']);
        }
    }

    #保存轮播图
    public function save_website_rotate(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);#0商城，1官网
        $rotate_id = isset($dat['id'])?intval($dat['id']):0;

        if($request->isAjax()){

            if($rotate_id>0){
                //修改
                Db::name('website_rotate')->where(['id'=>$rotate_id])->update([
                    'type'=>0,
                    'title'=>json_encode($dat['title'],true),
                    'thumb'=>isset($dat['thumb'][0])?$dat['thumb'][0]:'',
                    'go_other'=>$dat['go_other'],
                    'other_link'=>$dat['go_other']==1?trim($dat['other_link']):'',
                    'other_navbar'=>$dat['go_other']==2?trim($dat['other_navbar']):'',
                    'other_pic'=>$dat['go_other']==3?trim($dat['other_pic']):'',
                    'other_msg'=>$dat['go_other']==4?trim($dat['other_msg']):'',
                    'other_keywords'=>$dat['go_other']==5?trim($dat['other_keywords']):'',
                ]);
            }
            else{
                //新增
                Db::name('website_rotate')->insert([
                    'company_id'=>$company_id,
                    'company_type'=>$company_type,
                    'type'=>0,
                    'title'=>json_encode($dat['title'],true),
                    'thumb'=>isset($dat['thumb'][0])?$dat['thumb'][0]:'',
                    'go_other'=>$dat['go_other'],
                    'other_link'=>$dat['go_other']==1?trim($dat['other_link']):'',
                    'other_navbar'=>$dat['go_other']==2?trim($dat['other_navbar']):'',
                    'other_pic'=>$dat['go_other']==3?trim($dat['other_pic']):'',
                    'other_msg'=>$dat['go_other']==4?trim($dat['other_msg']):'',
                    'other_keywords'=>$dat['go_other']==5?trim($dat['other_keywords']):'',
                ]);
            }
            return json(['code'=>0,'msg'=>'保存成功']);
        }
        else{
            $data = ['title'=>['zh'=>''],'thumb'=>'','go_other'=>'','other_link'=>'','other_navbar'=>'',];
            if($rotate_id>0){
                $data = Db::name('website_rotate')->where(['id'=>$rotate_id])->find();
                $data['title'] = json_decode($data['title'],true);
            }
            return view('index/website/save_website_rotate',compact('company_id','company_type','data','rotate_id'));
        }
    }

    #删除轮播图
    public function del_website_rotate(Request $request){
        $dat = input();
        $res = Db::name('website_rotate')->where('id',$dat['id'])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功！']);
        }
    }

    #保存菜单
    public function save_website_menu(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);#0商城，1官网
        $menu_id = isset($dat['id'])?intval($dat['id']):0;
        $pid = isset($dat['pid'])?intval($dat['pid']):0;

        if($request->isAjax()){
            $content_id = 0;

            if($menu_id>0){
                //修改
                $res = Db::name('website_navbar')->where('id',$dat['id'])->update([
                    'displayorder'=>intval($dat['displayorder2']),
                    'name'=>json_encode(['zh'=>trim($dat['name']['zh'])]),
                    'content'=>isset($dat['content_zh'])?json_encode(['zh'=>$dat['content_zh']]):'',
                    'type'=>intval($dat['type']),
                    'content_id'=>$content_id,
                    'thumb'=>isset($dat['thumb'][0])?$dat['thumb'][0]:'',
                    'avatar_location'=>$dat['format']==1?$dat['avatar_location']:$dat['avatar_location2'],
                    'format'=>$dat['format'],
                    'color'=>$dat['format']==2?$dat['color']:'',
                    'color_word'=>$dat['format']==2?json_encode(['zh'=>trim($dat['color_word_zh'])]):'',
                    'word_color'=>$dat['format']==2?$dat['word_color']:'',
                    'desc'=>json_encode(['zh'=>trim($dat['desc']['zh'])]),
                    'url'=>$dat['url'],
                    'go_other'=>$dat['go_other'],
                    'other_link'=>$dat['go_other']==1?trim($dat['other_link']):'',
                    'other_navbar'=>$dat['go_other']==2?trim($dat['other_navbar']):'',
                    'seo_type'=>$dat['seo_type'],
                    'seo_content'=>$dat['seo_type']==2?json_encode($dat['seo_content'],true):'',
                ]);
            }
            else{
                //新增
                $res = Db::name('website_navbar')->insert([
                    'company_id'=>$company_id,
                    'company_type'=>$company_type,
                    'displayorder'=>intval($dat['displayorder2']),
                    'name'=>json_encode(['zh'=>trim($dat['name']['zh'])]),
                    'content'=>isset($dat['content_zh'])?json_encode(['zh'=>$dat['content_zh']]):'',
                    'type'=>intval($dat['type']),
                    'content_id'=>$content_id,
                    'thumb'=>isset($dat['thumb'][0])?$dat['thumb'][0]:'',
                    'avatar_location'=>$dat['avatar_location'],
                    'format'=>$dat['format'],
                    'color'=>$dat['format']==2?$dat['color']:'',
                    'color_word'=>$dat['format']==2?json_encode(['zh'=>trim($dat['color_word_zh'])]):'',
                    'word_color'=>$dat['format']==2?$dat['word_color']:'',
                    'desc'=>json_encode(['zh'=>trim($dat['desc']['zh'])]),
                    'url'=>$dat['url'],
                    'go_other'=>$dat['go_other'],
                    'other_link'=>$dat['go_other']==1?trim($dat['other_link']):'',
                    'other_navbar'=>$dat['go_other']==2?trim($dat['other_navbar']):'',
                    'seo_type'=>$dat['seo_type'],
                    'seo_content'=>$dat['seo_type']==2?json_encode($dat['seo_content'],true):'',
                    'pid'=>$pid
                ]);
            }

            return json(['code' => 0, 'msg' => '保存成功']);
        }
        else{
            $data = ['displayorder'=>'','name'=>['zh'=>''],'content'=>['zh'=>''],'url'=>'','type'=>1,'content_id'=>1,'desc'=>['zh'=>''],'seo_type'=>1,'seo_content'=>['title'=>['zh'=>''],'keywords'=>['zh'=>''],'desc'=>['zh'=>'']],'format'=>'','avatar_location'=>2,'color'=>'','word_color'=>'','color_word'=>['zh'=>''],'go_other'=>0,'other_navbar'=>'','other_link'=>''];

            if($menu_id>0){
                $data = Db::name('website_navbar')->where('id',$menu_id)->find();
                $data['content'] = json_decode($data['content'],true);
                $data['name'] = json_decode($data['name'],true);
                $data['desc'] = json_decode($data['desc'],true);
                if($data['format']==2){
                    $data['color_word'] = json_decode($data['color_word'],true);
                }else{
                    $data['color_word'] = ['zh'=>''];
                }
                $dat['pid'] = $data['pid'];
                if($data['seo_type']==2){
                    $data['seo_content'] = json_decode($data['seo_content'],true);
                }else{
                    $data['seo_content'] = ['title'=>['zh'=>''],'keywords'=>['zh'=>''],'desc'=>['zh'=>'']];
                }
            }
            $list = $this->company_menu($company_id,$company_type);

            return view('index/website/save_website_menu',compact('company_id','company_type','data','menu_id','list','pid'));
        }
    }

    #保存频道
    public function save_website_index(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);#0商城，1官网
        $id = isset($dat['id'])?intval($dat['id']):0;

        if($request->isAjax()){
            $format = 0;
            $connect_menus = explode(',',$dat['select'])[0];

            if(empty($connect_menus)){
                return json(['code'=>-1, 'msg'=>'请选择板块']);
            }

            if (is_numeric($connect_menus)) {
                $have_child = Db::name('website_navbar')->where('pid', $connect_menus)->find();

                if (empty($have_child)) {
                    return json(['code' => -1, 'msg' => '请选择有下级菜单的板块！']);
                } else {
                    $format = 2;
                    $have_child = Db::name('website_navbar')->where('pid', $have_child['id'])->find();
                    if (!empty($have_child)) {
                        $format = 1;
                    }
                }
            }

            $content = [];#切换框内容
            $btn_content = [];#按键自定义内容
            $fq_content = [];#标题+描述折叠框内容
            $fq_category_content = [];#一问一答图文内容
            $card1_content = [];#卡片1样式内容
            $card2_content = [];#卡片2样式内容
            $card3_content = [];#卡片3样式内容
            if($dat['format_type']==2){
                foreach($dat['ficon'] as $k=>$v){
                    array_push($content,['fnavbar'=>intval($dat['fnavbar'][$k]),'ficon'=>trim($v),'ftitle'=>trim($dat['ftitle'][$k]),'fdesc'=>trim($dat['fdesc'][$k])]);
                }

                foreach($dat['btn_title'] as $k=>$v){
                    array_push($btn_content,['btn_navbar'=>intval($dat['btn_navbar'][$k]),'btn_title'=>trim($v),'go_other'=>intval($dat['go_other'][$k]),'other_link'=>trim($dat['other_link'][$k]),'other_navbar'=>intval($dat['other_navbar'][$k]),'other_pic'=>intval($dat['other_pic'][$k]),'other_msg'=>intval($dat['other_msg'][$k]),'other_keywords'=>trim($dat['other_keywords'][$k])]);
                }
            }elseif($dat['format_type']==4){
                foreach($dat['fq_title'] as $k=>$v){
                    array_push($fq_content,['fq_title'=>trim($v),'fq_desc'=>trim($dat['fq_desc'][$k])]);
                }
            }elseif($dat['format_type']==5){
                foreach($dat['fq_ctitle'] as $k=>$v){
                    array_push($fq_category_content,['fq_ctitle'=>trim($v),'fq_ids'=>trim($dat['fq_ids'][$k])]);
                }
            }elseif($dat['format_type']==6){
                foreach($dat['card1_title'] as $k=>$v){
                    array_push($card1_content,['card1_title'=>trim($v),'card1_desc'=>trim($dat['card1_desc'][$k]),'card1_img'=>trim($dat['card1_img'][$k])]);
                }
            }elseif($dat['format_type']==7){
                foreach($dat['card2_title'] as $k=>$v){
                    array_push($card2_content,['card2_title'=>trim($v),'card2_icon'=>trim($dat['card2_icon'][$k])]);
                }
            }elseif($dat['format_type']==8){
                foreach($dat['card3_title'] as $k=>$v){
                    array_push($card3_content,['card3_title'=>trim($v),'card3_desc'=>trim($dat['card3_desc'][$k]),'card3_img'=>trim($dat['card3_img'][$k])]);
                }
            }

            if($id>0){
                Db::name('website_index')->where('id',$id)->update([
                    'navbar_id'=>$connect_menus,
                    'displayorder'=>intval($dat['displayorder2']),
                    'format'=>$format,
                    'format_type'=>intval($dat['format_type']),
                    'content'=>$dat['format_type']==2?json_encode($content,true):'',
                    'btn_content'=>$dat['format_type']==2?json_encode($btn_content,true):'',
                    'fq_content'=>$dat['format_type']==4?json_encode($fq_content,true):'',
                    'bg_type'=>$dat['bg_type'],
                    'bg_color'=>$dat['bg_type']==1?trim($dat['bg_color']):'',
                    'bg_img'=>$dat['bg_type']==2?trim($dat['bg_img'][0]):'',
                    'fq_category_content'=>$dat['format_type']==5?json_encode($fq_category_content,true):'',
                    'card1_content'=>$dat['format_type']==6?json_encode($card1_content,true):'',
                    'card2_content'=>$dat['format_type']==7?json_encode($card2_content,true):'',
                    'card3_content'=>$dat['format_type']==8?json_encode($card3_content,true):'',
                ]);
            }else{
                Db::name('website_index')->insert([
                    'company_id'=>$company_id,
                    'company_type'=>$company_type,
                    'navbar_id'=>$connect_menus,
                    'displayorder'=>intval($dat['displayorder2']),
                    'format'=>$format,
                    'format_type'=>intval($dat['format_type']),
                    'content'=>$dat['format_type']==2?json_encode($content,true):'',
                    'btn_content'=>$dat['format_type']==2?json_encode($btn_content,true):'',
                    'fq_content'=>$dat['format_type']==4?json_encode($fq_content,true):'',
                    'bg_type'=>$dat['bg_type'],
                    'bg_color'=>$dat['bg_type']==1?trim($dat['bg_color']):'',
                    'bg_img'=>$dat['bg_type']==2?trim($dat['bg_img'][0]):'',
                    'fq_category_content'=>$dat['format_type']==5?json_encode($fq_category_content,true):'',
                    'card1_content'=>$dat['format_type']==6?json_encode($card1_content,true):'',
                    'card2_content'=>$dat['format_type']==7?json_encode($card2_content,true):'',
                    'card3_content'=>$dat['format_type']==8?json_encode($card3_content,true):'',
                ]);
            }

            return json(['code'=>0,'msg'=>'保存成功！']);
        }
        else{
            $data = ['navbar_id'=>'','displayorder'=>'','format_type'=>0,'bg_type'=>1,'bg_color'=>'','bg_img'=>'','fq_ids'=>''];
            $cmenu = [];

            if($id>0){
                $data = Db::name('website_index')->where('id',$id)->find();

                $cmenu = Db::name('website_navbar')->where(['pid'=>$data['navbar_id']])->field('id,name')->select();
                foreach($cmenu as $k=>$v){
                    $cmenu[$k]['name'] = json_decode($v['name'],true)['zh'];
                }
            }

            # 一问一答图文内容
            $fq_list = Db::name('website_image_txt')->select();
            foreach($fq_list as $k=>$v){
                $fq_list[$k]['name'] = json_decode($v['name'],true)['zh'];
                $fq_list[$k]['value'] = $fq_list[$k]['id'];
                $fq_list[$k]['children'] = [];
            }
            $fq_list = json_encode($fq_list,true);

            $list = $this->company_menu($company_id,$company_type,2);
            foreach($list as $k=>$v){
                $list[$k]['value'] = $v['id'];
                $list[$k]['children'] = [];
                if($id>0){
                    $list[$k]['disabled'] = true;
                }
            }
            #针对系统添加内容
            array_push($list,['id'=>'A1','name'=>'发现轮播+信息切换框','value'=>'A1','children'=>[],'disabled'=>$id>0?true:false]);
            array_push($list,['id'=>'A2','name'=>'常见问题','value'=>'A2','children'=>[],'disabled'=>$id>0?true:false]);
            $list = json_encode($list,true);

            if($id>0){
                if($data['format_type']==2){
                    // 左右图文切换框
                    $data['content'] = json_decode($data['content'],true);
                    foreach($data['content'] as $k=>$v){
                        $navbar_name = Db::name('website_navbar')->where(['id'=>$v['fnavbar']])->field('name')->find()['name'];
                        $data['content'][$k]['fnavbar_name'] = json_decode($navbar_name,true)['zh'];
                    }
                    #按键内容
                    $data['btn_content'] = json_decode($data['btn_content'],true);
                    foreach($data['btn_content'] as $k=>$v){
                        $navbar_name = Db::name('website_navbar')->where(['id'=>$v['btn_navbar']])->field('name')->find()['name'];
                        $data['btn_content'][$k]['btn_navbar_name'] = json_decode($navbar_name,true)['zh'];
                    }
                }
                elseif($data['format_type']==4){
                    // 标题+描述折叠框
                    $data['fq_content'] = json_decode($data['fq_content'],true);
                }
                elseif($data['format_type']==5){
                    // 一问一答图文内容
                    $data['fq_category_content'] = json_decode($data['fq_category_content'],true);
                }
                elseif($data['format_type']==6){
                    $data['card1_content'] = json_decode($data['card1_content'],true);
                }
                elseif($data['format_type']==7){
                    $data['card2_content'] = json_decode($data['card2_content'],true);
                }
                elseif($data['format_type']==8){
                    $data['card3_content'] = json_decode($data['card3_content'],true);
                }
            }

            $btn_list = $this->company_menu($company_id,$company_type,2);

            #图文链接
            $pic_list = Db::name('website_image_txt')->select();
            foreach($pic_list as $k=>$v){
                $pic_list[$k]['name'] = json_decode($v['name'],'true')['zh'];
            }
            #消息链接
            $msg_list = Db::name('website_message_manage')->select();

            return view('index/website/save_website_index',compact('data','id','list','system_id','cmenu','fq_list','btn_list','pic_list','msg_list','company_id','company_type'));
        }
    }

    #删除频道
    public function del_website_index(Request $request){
        $dat = input();
        $res = Db::name('website_index')->where('id',$dat['id'])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功！']);
        }
    }

    #获取下级菜单
    public function get_nextNavbar(Request $request){
        $dat = input();

        $id = intval($dat['id']);

        $cmenu = [];
        if($id>0){
            $cmenu = Db::name('website_navbar')->where(['pid'=>$id])->field('id,name')->select();
            foreach($cmenu as $k=>$v){
                $cmenu[$k]['name'] = json_decode($v['name'],true)['zh'];
            }
        }


        return json(['code'=>0,'list'=>$cmenu]);
    }

    #保存发现轮播图
    public function save_website_discovery(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);#0商城，1官网
        $id = isset($dat['id'])?intval($dat['id']):0;

        if($request->isAjax()){

            if($id>0){
                //修改
                Db::name('website_discovery_list')->where(['id'=>$id])->update([
                    'descs'=>trim($dat['descs']),
                    'thumb'=>isset($dat['thumb'][0])?$dat['thumb'][0]:'',
                    'go_other'=>$dat['go_other'],
                    'other_link'=>$dat['go_other']==1?trim($dat['other_link']):'',
                    'other_navbar'=>$dat['go_other']==2?trim($dat['other_navbar']):'',
                    'other_pic'=>$dat['go_other']==3?trim($dat['other_pic']):'',
                    'other_msg'=>$dat['go_other']==4?trim($dat['other_msg']):'',
                    'other_keywords'=>$dat['go_other']==5?trim($dat['other_keywords']):'',
                ]);
            }
            else{
                //新增
                Db::name('website_discovery_list')->insert([
                    'company_id'=>$company_id,
                    'company_type'=>$company_type,
                    'descs'=>trim($dat['descs']),
                    'thumb'=>isset($dat['thumb'][0])?$dat['thumb'][0]:'',
                    'go_other'=>$dat['go_other'],
                    'other_link'=>$dat['go_other']==1?trim($dat['other_link']):'',
                    'other_navbar'=>$dat['go_other']==2?trim($dat['other_navbar']):'',
                    'other_pic'=>$dat['go_other']==3?trim($dat['other_pic']):'',
                    'other_msg'=>$dat['go_other']==4?trim($dat['other_msg']):'',
                    'other_keywords'=>$dat['go_other']==5?trim($dat['other_keywords']):'',
                    'createtime'=>time()
                ]);
            }
            return json(['code'=>0,'msg'=>'保存成功']);
        }
        else{
            $data = ['descs'=>'','thumb'=>'','go_other'=>'','other_link'=>'','other_navbar'=>'',];
            if($id>0){
                $data = Db::name('website_discovery_list')->where(['id'=>$id])->find();
            }
            return view('index/website/save_website_discovery',compact('company_id','company_type','data','id'));
        }
    }

    #删除发现轮播图
    public function del_website_discovery(Request $request){
        $dat = input();
        $res = Db::name('website_discovery_list')->where('id',$dat['id'])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功！']);
        }
    }

    #菜单栏目
    public function company_menu($company_id,$company_type,$type=0){
        $menu = Db::name('website_navbar')->where(['company_id'=>$company_id,'company_type'=>$company_type,'pid'=>0])->order('displayorder,id asc')->select();
        foreach($menu as $k=>$v){
            $menu[$k]['name'] = json_decode($v['name'],true)['zh'];
            if($type==2){
                #不获取下级
                $menu[$k]['childMenu'] = [];
            }else{
                $menu[$k]['childMenu'] = $this->getDownCompanyMenu($v['id']);
            }
        }
        return $menu;
    }

    #下级菜单
    public function getDownCompanyMenu($id){
        $cmenu = Db::name('website_navbar')->where(['pid'=>$id])->order('displayorder,id asc')->select();
        foreach($cmenu as $k=>$v){
            $cmenu[$k]['name'] = json_decode($v['name'],true)['zh'];
            $cmenu[$k]['childMenu'] = Db::name('website_navbar')->where(['pid'=>$v['id']])->order('displayorder,id asc')->select();
            foreach($cmenu[$k]['childMenu'] as $k2=>$v2){
                $cmenu[$k]['childMenu'][$k2]['name'] = json_decode($v2['name'],true)['zh'];
                $cmenu[$k]['childMenu'][$k2]['childMenu'] = Db::name('website_navbar')->where(['pid'=>$v2['id']])->order('displayorder,id asc')->select();
                foreach($cmenu[$k]['childMenu'][$k2]['childMenu'] as $k3=>$v3){
                    $cmenu[$k]['childMenu'][$k2]['childMenu'][$k3]['name'] = json_decode($v3['name'],true)['zh'];
                }
            }
        }
        return $cmenu;
    }

    #删除菜单
    public function del_website_menu(Request $request){
        $id = $request->get('id');
        if ($request->get('id') === '') {
            return json(['code' => -1, 'msg' => '错误！']);
        }
        $have_child = Db::name('website_navbar')->where('pid',$id)->find();
        if ($have_child['id']) {
            return json(['code' => -1, 'msg' => '存在子级菜单,不允许删除!']);
        }

        $have_home = Db::name('website_index')->where('navbar_id',$id)->find();
        if ($have_home['id']) {
            return json(['code' => -1, 'msg' => '请先删除网站频道相应菜单!']);
        }
        Db::name('website_navbar')->where('id',$id)->delete();
        return json(['code' => 0, 'msg' => '已删除']);
    }
    #企业网站==============================================end

    #企业网店==============================================start
    public function website_shop(Request $request){
        $dat = input();

        $company = Db::name('website_user_company')->where(['user_id'=>session('account.id'),'status'=>0])->select();

//        $typ = intval($dat['typ']);//企业类型，0商城，1网站
        $typ = 0;
        $company_id = intval($dat['company_id']);
        $tab = isset($dat['tab'])?trim($dat['tab']):'website_basic';

        $company_info = Db::name('website_user_company')->where(['id'=>$company_id])->find();
        if(empty($company_info['domain_name'])){
            $company_info['domain_name2'] = 'xxx';
        }
        else{
            $company_info['domain_name2'] = explode('.',$company_info['domain_name'])[1];
        }

        #企业网站-基本&页头页脚配置
        $model = new WebsiteBasic();
        $website_basic = $model->getByCompanyId($company_id, $typ);

        #企业网站-轮播图
        $rotate = Db::name('website_rotate')->where(['company_id'=>$company_id,'company_type'=>$typ])->select();
        foreach($rotate as $k=>$v){
            $rotate[$k]['title'] = json_decode($v['title'],true)['zh'];
        }

        #企业网站-发现轮播
        $website_discovery = Db::name('website_discovery_list')->where(['company_id'=>$company_id,'company_type'=>$typ])->select();
        foreach($website_discovery as $k=>$v){
            $website_discovery[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);
        }
        
        $website['website_canonical'] = $this->website_canonical;
        $website['website_og'] = $this->website_og;

        return view('index/shop_backend/website_shop',compact('company','company_info','company_id','rotate','website_basic','tab','website','website_discovery'));
    }

    #店铺基本信息
    public function website_basic(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);#0商城，1官网

        if($request->isAjax()){
            if(!isset($dat['slogo_file'])){
                return json(['code'=>-1,'msg'=>'请上传网站Logo']);
            }
            if(!isset($dat['logo_file'])){
                return json(['code'=>-1,'msg'=>'请上传浏览器标志']);
            }

            $data = Db::name('website_basic')->where(['company_id'=>$company_id,'company_type'=>$company_type])->find();
            $res = '';

//            $paypal = [];
//            if($dat['pay_method']==2){
//                if(!empty($dat['paypal']['client_id']) && !empty($dat['paypal']['client_secret'])){
//                    $paypal['client_id'] = trim($dat['paypal']['client_id']);
//                    $paypal['client_secret'] = trim($dat['paypal']['client_secret']);
//                    $paypal['is_through'] = intval($dat['paypal']['is_through']);
//                    $paypal = json_encode($paypal,true);
//                }
//            }

            if(!empty($data)){
                $res = Db::name('website_basic')->where(['company_id'=>$company_id,'company_type'=>$company_type])->update([
                    'name'=>trim($dat['name']),
                    'desc'=>trim($dat['desc']),
                    'keywords'=>trim($dat['keywords']),
                    'slogo'=>$dat['slogo_file'][0],
                    'logo'=>$dat['logo_file'][0],
                    'color'=>$dat['color'],
                    'color_inner'=>$dat['color_inner'],
                    'color_word'=>$dat['color_word'],
                    'color_adorn'=>$dat['color_adorn'],
                    'font_family'=>$dat['font_family'],
//                    'is_website'=>$dat['is_website'],
//                    'pay_method'=>$dat['pay_method'],
//                    'paypal'=>$paypal,
//                    'cash_on_delivery'=>$dat['cash_on_delivery'],#货到付款
//                    'down_payment'=>$dat['down_payment'],#预付定金
//                    'prepaid_method'=>$dat['prepaid_method'],#预付方式
//                    'prepaid_percent'=>$dat['prepaid_percent'],#按比例
//                    'prepaid_currency'=>$dat['prepaid_currency'],#按定额-币种
//                    'prepaid_amount'=>$dat['prepaid_amount']#按定额-金额
                ]);
            }
            else{
                $res = Db::name('website_basic')->insert([
                    'company_id'=>$company_id,
                    'company_type'=>$company_type,
                    'name'=>trim($dat['name']),
                    'desc'=>trim($dat['desc']),
                    'keywords'=>trim($dat['keywords']),
                    'slogo'=>$dat['slogo_file'][0],
                    'logo'=>$dat['logo_file'][0],
                    'color'=>$dat['color'],
                    'color_inner'=>$dat['color_inner'],
                    'color_word'=>$dat['color_word'],
                    'color_adorn'=>$dat['color_adorn'],
                    'font_family'=>$dat['font_family'],
//                    'is_website'=>$dat['is_website'],
//                    'pay_method'=>$dat['pay_method'],
//                    'paypal'=>$paypal,
//                    'cash_on_delivery'=>$dat['cash_on_delivery'],#货到付款
//                    'down_payment'=>$dat['down_payment'],#预付定金
//                    'prepaid_method'=>$dat['prepaid_method'],#预付方式
//                    'prepaid_percent'=>$dat['prepaid_percent'],#按比例
//                    'prepaid_currency'=>$dat['prepaid_currency'],#按定额-币种
//                    'prepaid_amount'=>$dat['prepaid_amount']#按定额-金额
                ]);
            }

            if($res){
                return json(['code' => 0, 'msg' => '保存成功！']);
            }
            else{
                return json(['code' => -1, 'msg' => '暂无修改！']);
            }
        }else{
            $data = Db::name('website_basic')->where(['company_id'=>$company_id,'company_type'=>$company_type])->find();

            if(empty($data)){
                $data = ['slogo'=>'','logo'=>'','name'=>'','desc'=>'','keywords'=>'','color'=>'','color_inner'=>'#ffffff','color_word'=>'#ffffff','color_adorn'=>'','is_website'=>0,'font_family'=>"Microsoft JhengHei, 微軟正黑體, Arial, sans-serif",'pay_method'=>0,'cash_on_delivery'=>1,'down_payment'=>1,'prepaid_method'=>1,'prepaid_percent'=>'','prepaid_currency'=>'','prepaid_amount'=>'','paypal'=>['client_id'=>'','client_secret'=>'','is_through'=>0]];
            }
            else{
                if(empty($data['paypal'])){
                    $data['paypal'] = ['client_id'=>'','client_secret'=>'','is_through'=>0];
                }
                else{
                    $data['paypal'] = json_decode($data['paypal'],true);
                }
            }

            return view('index/shop_backend/website_basic',compact('company_id','company_type','data'));
        }
    }

    #仓库管理
    public function warehouse_manage(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);#0商城，1官网

        #企业商城-仓库地址
        $list = Db::name('centralize_warehouse_list')->where(['uid'=>$company_id])->order('id desc')->select();

        foreach ($list as &$item) {
            $item['createtime'] = date('Y-m-d H:i',$item['createtime']);
        }

        return view('index/shop_backend/warehouse_manage',compact('company_type','company_id','list'));
    }

    #保存我的仓库
    public function save_warehouse(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);
        $id = isset($dat['id'])?intval($dat['id']):0;

        if($request->isAjax()){
            if($id>0){
                Db::name('centralize_warehouse_list')->where(['id'=>$id])->update([
                    'company_name'=>trim($dat['company_name']),
                    'country_code'=>intval($dat['country_code']),
                    'postal_code'=>trim($dat['postal_code']),
                    'address1'=>trim($dat['address1']),
                    'name'=>trim($dat['name']),
                    'email'=>trim($dat['email']),
                    'area_code'=>explode(' ',$dat['mobile'])[0],
                    'mobile'=>explode(' ',$dat['mobile'])[1],
                ]);
            }else{
                Db::name('centralize_warehouse_list')->insert([
                    'uid'=>intval($dat['company_id']),
                    'warehouse_name'=>trim($dat['warehouse_name']),
                    'country_code'=>intval($dat['country_code']),
                    'postal_code'=>trim($dat['postal_code']),
                    'address1'=>trim($dat['address1']),
                    'name'=>trim($dat['name']),
                    'email'=>trim($dat['email']),
                    'area_code'=>explode(' ',$dat['mobile'])[0],
                    'mobile'=>explode(' ',$dat['mobile'])[1],
                    'createtime'=>time()
                ]);
            }

            return json(['code'=>0,'msg'=>'保存成功']);
        }else{
            $data = ['warehouse_name'=>'','country_code'=>0,'postal_code'=>'','pre_address'=>'','address1'=>'','name'=>'','email'=>'','area_code'=>'','mobile'=>''];
            if($id>0){
                $data = Db::name('centralize_warehouse_list')->where(['id'=>$id])->find();
                $country = Db::name('centralize_diycountry_content')->where(['id'=>$data['country_code']])->find();
                $pre_address = Db::name('all_country_area_postcode')->where(['postal_code'=>$data['postal_code'],'country_code'=>$country['param5']])->find();
                $data['pre_address'] = $pre_address['admin_name1'].' '.$pre_address['admin_name2'].' '.$pre_address['admin_name3'];
            }

            #国家和手机号码前缀
            $list['country'] = Db::name('centralize_diycountry_content')->where(['pid'=>5])->select();

            return view('index/shop_backend/save_warehouse',compact('company_id','company_type','id','list','data'));
        }
    }

    #删除仓库
    public function del_warehouse(Request $request){
        $dat = input();

        $res = Db::name('centralize_warehouse_list')->where(['id'=>intval($dat['id'])])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功']);
        }
    }

    #页头菜单管理
    public function shop_head_menu(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);#0商城，1官网
        $pid = isset($dat['pid'])?intval($dat['pid']):0;

        if($request->isAjax()){
            $list = Db::name('website_navbar')->where(['company_id'=>$company_id,'company_type'=>$company_type])->select();
            return json(['code' => 0, 'msg' => '', 'count' => count($list), 'data' => $list]);
        }
        else{
            return view('index/shop_backend/shop_head_menu',compact('company_id','company_type'));
        }
    }

    #保存菜单栏目
    public function save_shop_menu(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);#0商城，1官网
        $id = isset($dat['id'])?intval($dat['id']):0;
        $pid = isset($dat['pid'])?intval($dat['pid']):0;
        $system_id = 0;

        if ($request->isAJAX()) {
            $content_id = 0;
            if($id>0){
                $res = Db::name('website_navbar')->where('id',$dat['id'])->update([
                    'name'=>trim($dat['name']),
                    'content'=>isset($dat['content'])?json_encode($dat['content'],true):'',
                    'type'=>intval($dat['type']),
                    'content_id'=>$content_id,
                    'thumb'=>isset($dat['thumb'][0])?$dat['thumb'][0]:'',
                    'avatar_location'=>$dat['format']==1?$dat['avatar_location']:$dat['avatar_location2'],
                    'format'=>$dat['format'],
                    'color'=>$dat['format']==2?$dat['color']:'',
                    'color_word'=>$dat['format']==2?trim($dat['color_word']):'',
                    'word_color'=>$dat['format']==2?$dat['word_color']:'',
                    'desc'=>trim($dat['desc']),
                    'url'=>$dat['url'],
                    'go_other'=>$dat['go_other'],
                    'other_link'=>$dat['go_other']==1?trim($dat['other_link']):'',
                    'other_navbar'=>$dat['go_other']==2?trim($dat['other_navbar']):'',
                    'seo_type'=>$dat['seo_type'],
                    'seo_content'=>$dat['seo_type']==2?json_encode($dat['seo_content'],true):'',
                ]);
            }
            else{
                $res = Db::name('website_navbar')->insert([
                    'company_id'=>$company_id,
                    'company_type'=>$company_type,
                    'name'=>trim($dat['name']),
                    'content'=>isset($dat['content'])?json_encode($dat['content'],true):'',
                    'type'=>intval($dat['type']),
                    'content_id'=>$content_id,
                    'thumb'=>isset($dat['thumb'][0])?$dat['thumb'][0]:'',
                    'avatar_location'=>$dat['avatar_location'],
                    'format'=>$dat['format'],
                    'color'=>$dat['format']==2?$dat['color']:'',
                    'color_word'=>$dat['format']==2?trim($dat['color_word']):'',
                    'word_color'=>$dat['format']==2?$dat['word_color']:'',
                    'desc'=>trim($dat['desc']),
                    'url'=>$dat['url'],
                    'go_other'=>$dat['go_other'],
                    'other_link'=>$dat['go_other']==1?trim($dat['other_link']):'',
                    'other_navbar'=>$dat['go_other']==2?trim($dat['other_navbar']):'',
                    'seo_type'=>$dat['seo_type'],
                    'seo_content'=>$dat['seo_type']==2?json_encode($dat['seo_content'],true):'',
                    'pid'=>$pid
                ]);
            }

            if($res){
                return json(['code' => 0, 'msg' => '保存成功']);
            }
            else{
                return json(['code' => -1, 'msg' => '暂无修改']);
            }
        }else {
            if ($id>0) {
                $data = Db::name('website_navbar')->where('id', $id)->find();
                $data['content'] = json_decode($data['content'], true);
                $pid = $data['pid'];

                if ($data['seo_type'] == 2) {
                    $data['seo_content'] = json_decode($data['seo_content'], true);
                } else {
                    $data['seo_content'] = ['title' => '', 'keywords' => '', 'desc' => ''];
                }
            } else {
                $data = ['name' => '', 'content' => '', 'url' => '', 'type' => 0, 'content_id' => 1, 'desc' => '', 'seo_type' => 1, 'seo_content' => ['title' => '', 'keywords' => '', 'desc' => ''], 'format' => '', 'avatar_location' => 2, 'color' => '', 'word_color' => '', 'color_word' => '', 'go_other' => 0, 'other_navbar' => '', 'other_link' => ''];
            }
            $list = $this->shop_menu($company_id,$company_type);

            return view('index/shop_backend/save_shop_menu',compact('id','pid','data','list','company_id','company_type'));
        }
    }

    #商城菜单栏目
    public function shop_menu($company_id,$company_type){
        $menu = Db::name('website_navbar')->where(['company_id'=>$company_id,'company_type'=>$company_type,'pid'=>0])->field('id,name')->select();
        foreach($menu as $k=>$v){
            $menu[$k]['childMenu'] = $this->getShopDownMenu($v['id']);
        }
        return $menu;
    }

    #下级菜单
    public function getShopDownMenu($id){
        $cmenu = Db::name('website_navbar')->where(['pid'=>$id])->field('id,name')->select();
        if(!empty($cmenu)){
            foreach($cmenu as $k=>$v){
                $cmenu[$k]['childMenu'] = Db::name('website_navbar')->where(['pid'=>$v['id']])->field('id,name')->select();
                if(!empty($cmenu[$k]['childMenu'])) {
                    foreach ($cmenu[$k]['childMenu'] as $k2 => $v2) {
                        $cmenu[$k]['childMenu'][$k2]['childMenu'] = Db::name('website_navbar')->where(['pid' => $v2['id']])->field('id,name')->select();
                        if(!empty($cmenu[$k]['childMenu'][$k2]['childMenu'])) {
                            foreach ($cmenu[$k]['childMenu'][$k2]['childMenu'] as $k3 => $v3) {

                            }
                        }
                    }
                }
            }
        }

        return $cmenu;
    }

    #删除菜单栏目
    public function del_shop_menu(Request $request){
        $id = $request->get('id');
        if ($request->get('id') === '') {
            return json(['code' => -1, 'msg' => '菜单id不能空！']);
        }
        #删除下级
        $res = Db::name('website_navbar')->where('pid',$id)->find();
        if(!empty($res)){
            return json(['code'=>-1,'msg'=>'存在子级菜单,不允许删除!']);
        }
        #删除当前菜单
        Db::name('website_navbar')->where('id',$id)->delete();

        return json(['code' => 0, 'msg' => '已删除']);
    }

    #滚动信息管理
    public function shop_scroll_info(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);#0商城，1官网

        if ($request->isAJAX()) {
            $res = '';
            $data = Db::name('merchsite_rotate')->where(['company_id'=>$company_id,'company_type'=>$company_type])->find();
            if(empty($data)){
                $res = Db::name('merchsite_rotate')->insert([
                    'company_id'=>$company_id,
                    'company_type'=>$company_type,
                    'location'=>intval($dat['location']),
                    'content_id'=>trim($dat['content_id']),
                ]);
            }
            else{
                $res = Db::name('merchsite_rotate')->where(['company_id'=>$company_id,'company_type'=>$company_type])->update([
                    'location'=>intval($dat['location']),
                    'content_id'=>trim($dat['content_id']),
                ]);
            }

            if($res){
                return json(['code'=>0,'msg'=>'保存成功']);
            }else{
                return json(['code'=>-1,'msg'=>'暂无修改']);
            }
        }
        else{
            $data = Db::name('merchsite_rotate')->where(['company_id'=>$company_id,'company_type'=>$company_type])->find();
            if(empty($data)){
                $data = ['location'=>1,'content_id'=>''];
            }

            $list = Db::name('merchsite_rotateapi')->select();
            $list = json_encode($list,true);

            return view('index/shop_backend/shop_scroll_info',compact('data','list','company_id','company_type'));
        }
    }

    #页脚菜单管理
    public function shop_foot_menu(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);#0商城，1官网
        $pid = isset($dat['pid'])?intval($dat['pid']):0;

        if ($request->isAJAX()) {
            $list = Db::name('website_footer')->where(['company_id'=>$company_id,'company_type'=>$company_type])->select();
            return json(['code' => 0, 'msg' => '', 'count' => count($list), 'data' => $list]);
        }else{

            return view('index/shop_backend/shop_foot_menu',compact('company_id','company_type'));
        }
    }

    #保存页脚菜单
    public function save_shop_foot_menu(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);#0商城，1官网
        $id = isset($dat['id'])?intval($dat['id']):0;
        $pid = isset($dat['pid'])?intval($dat['pid']):0;

        if($request->isAjax()){
            $content_id = 0;
            if($id>0){
                $res = Db::name('website_footer')->where('id',$dat['id'])->update([
                    'name'=>trim($dat['name']),
                    'content'=>isset($dat['content'])?json_encode($dat['content'],true):'',
                    'type'=>intval($dat['type']),
                    'content_id'=>$content_id,
                    'thumb'=>isset($dat['thumb'][0])?$dat['thumb'][0]:'',
                    'avatar_location'=>$dat['format']==1?$dat['avatar_location']:$dat['avatar_location2'],
                    'format'=>$dat['format'],
                    'color'=>$dat['format']==2?$dat['color']:'',
                    'color_word'=>$dat['format']==2?trim($dat['color_word']):'',
                    'word_color'=>$dat['format']==2?$dat['word_color']:'',
                    'desc'=>trim($dat['desc']),
                    'url'=>$dat['url'],
                    'go_other'=>$dat['go_other'],
                    'other_link'=>$dat['go_other']==1?trim($dat['other_link']):'',
                    'other_navbar'=>$dat['go_other']==2?trim($dat['other_navbar']):'',
                    'seo_type'=>$dat['seo_type'],
                    'seo_content'=>$dat['seo_type']==2?json_encode($dat['seo_content'],true):'',
                ]);
            }
            else{
                $res = Db::name('website_footer')->insert([
                    'company_id'=>$company_id,
                    'company_type'=>$company_type,
                    'name'=>trim($dat['name']),
                    'content'=>isset($dat['content'])?json_encode($dat['content'],true):'',
                    'type'=>intval($dat['type']),
                    'content_id'=>$content_id,
                    'thumb'=>isset($dat['thumb'][0])?$dat['thumb'][0]:'',
                    'avatar_location'=>$dat['avatar_location'],
                    'format'=>$dat['format'],
                    'color'=>$dat['format']==2?$dat['color']:'',
                    'color_word'=>$dat['format']==2?trim($dat['color_word']):'',
                    'word_color'=>$dat['format']==2?$dat['word_color']:'',
                    'desc'=>trim($dat['desc']),
                    'url'=>$dat['url'],
                    'go_other'=>$dat['go_other'],
                    'other_link'=>$dat['go_other']==1?trim($dat['other_link']):'',
                    'other_navbar'=>$dat['go_other']==2?trim($dat['other_navbar']):'',
                    'seo_type'=>$dat['seo_type'],
                    'seo_content'=>$dat['seo_type']==2?json_encode($dat['seo_content'],true):'',
                    'pid'=>$pid
                ]);
            }

            if($res){
                return json(['code' => 0, 'msg' => '保存成功']);
            }
            else{
                return json(['code' => -1, 'msg' => '暂无修改']);
            }
        }else{
            if ($id>0) {
                $data = Db::name('website_footer')->where('id', $id)->find();
                $data['content'] = json_decode($data['content'], true);
                $pid = $data['pid'];

                if ($data['seo_type'] == 2) {
                    $data['seo_content'] = json_decode($data['seo_content'], true);
                } else {
                    $data['seo_content'] = ['title' => '', 'keywords' => '', 'desc' => ''];
                }
            } else {
                $data = ['name' => '', 'content' => '', 'url' => '', 'type' => 0, 'content_id' => 1, 'desc' => '', 'seo_type' => 1, 'seo_content' => ['title' => '', 'keywords' => '', 'desc' => ''], 'format' => '', 'avatar_location' => 2, 'color' => '', 'word_color' => '', 'color_word' => '', 'go_other' => 0, 'other_navbar' => '', 'other_link' => ''];
            }
            $list = $this->footer_menu($company_id,$company_type);

            return view('index/shop_backend/save_shop_foot_menu',compact('id','pid','data','list','company_id','company_type'));
        }
    }

    #菜单栏目
    public function footer_menu($company_id,$company_type){
        $menu = Db::name('website_footer')->where(['company_id'=>$company_id,'company_type'=>$company_type,'pid'=>0])->field('id,name')->select();
        foreach($menu as $k=>$v){
            $menu[$k]['childMenu'] = $this->getFooterDownMenu($v['id']);
        }
        return $menu;
    }

    #下级菜单
    public function getFooterDownMenu($id){
        $cmenu = Db::name('website_footer')->where(['pid'=>$id])->field('id,name')->select();
        foreach($cmenu as $k=>$v){
            $cmenu[$k]['childMenu'] = Db::name('website_footer')->where(['pid'=>$v['id']])->field('id,name')->select();
            foreach($cmenu[$k]['childMenu'] as $k2=>$v2){
                $cmenu[$k]['childMenu'][$k2]['childMenu'] = Db::name('website_footer')->where(['pid'=>$v2['id']])->field('id,name')->select();
                foreach($cmenu[$k]['childMenu'][$k2]['childMenu'] as $k3=>$v3){

                }
            }
        }
        return $cmenu;
    }

    #删除页脚菜单
    public function del_shop_foot_menu(Request $request){
        $id = $request->get('id');
        if ($request->get('id') === '') {
            return json(['code' => -1, 'msg' => '菜单id不能空！']);
        }
        #删除下级
        $res = Db::name('website_footer')->where('pid',$id)->find();
        if($res){
            return json(['code'=>-1,'msg'=>'存在子级菜单,不允许删除!']);
        }
        #删除当前菜单
        Db::name('website_footer')->where('id',$id)->delete();

        return json(['code' => 0, 'msg' => '已删除']);
    }

    #社交媒体管理
    public function shop_social(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);#0商城，1官网

        $list = Db::name('website_contact')->where(['company_id'=>$company_id,'company_type'=>$company_type])->select();
        foreach($list as $k=>$v){
            $list[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);
        }

        return view('index/shop_backend/shop_social',compact('company_id','company_type','list'));
    }

    #保存社交媒体
    public function save_shop_social(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);#0商城，1官网
        $id = isset($dat['id'])?$dat['id']:0;

        if($request->isAjax()){
            if($id>0){
                $res = Db::name('website_contact')->where('id',$dat['id'])->update([
                    'name'=>trim($dat['name']),
                    'ico'=>$dat['ico'][0],
                    'type'=>$dat['type'],
                    'link'=>$dat['type']==1?trim($dat['link']):'',
                    'img'=>$dat['type']==2?$dat['img'][0]:'',
                ]);
            }
            else{
                $res = Db::name('website_contact')->insert([
                    'company_type'=>$company_type,
                    'company_id'=>$company_id,
                    'name'=>trim($dat['name']),
                    'ico'=>$dat['ico'][0],
                    'type'=>$dat['type'],
                    'link'=>$dat['type']==1?trim($dat['link']):'',
                    'img'=>$dat['type']==2?$dat['img'][0]:'',
                    'createtime'=>time()
                ]);
            }

            if($res){
                return json(['code' => 0, 'msg' => '保存成功']);
            }
            else{
                return json(['code' => -1, 'msg' => '暂无修改']);
            }

        }else{
            $data = ['name'=>'','type'=>'','link'=>'','img'=>'','ico'=>''];
            if($id>0){
                $data = Db::name('website_contact')->where('id',$id)->find();
            }

            return view('index/shop_backend/save_shop_social',compact('id','data','company_id','company_type'));
        }
    }

    #删除社交媒体
    public function del_shop_social(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;

        $res = Db::name('website_contact')->where('id',$dat['id'])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功']);
        }
    }

    #联系信息配置
    public function shop_contact(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);#0商城，1官网

        if($request->isAjax()){
            $data = Db::name('website_basic')->where(['company_id'=>$company_id,'company_type'=>$company_type])->find();
            $res = '';
            if(!empty($data)){
                $res = Db::name('website_basic')->where(['company_id'=>$company_id,'company_type'=>$company_type])->update([
                    'mobile'=>trim($dat['mobile']),
                    'email'=>trim($dat['email']),
                    'address'=>trim($dat['address']),
                ]);
            }
            else{
                $res = Db::name('website_basic')->insert([
                    'company_id'=>$company_id,
                    'company_type'=>$company_type,
                    'mobile'=>trim($dat['mobile']),
                    'email'=>trim($dat['email']),
                    'address'=>trim($dat['address']),
                ]);
            }

            if($res){
                return json(['code' => 0, 'msg' => '保存成功']);
            }
            else{
                return json(['code' => -1, 'msg' => '暂无修改']);
            }
        }else{
            $data = Db::name('website_basic')->where(['company_id'=>$company_id,'company_type'=>$company_type])->find();
            if(empty($data)){
                $data = ['mobile'=>'','email'=>'','address'=>''];
            }

            return view('index/shop_backend/shop_contact',compact('id','data','company_id','company_type'));
        }
    }

    #资质信息管理
    public function shop_qualification(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);#0商城，1官网

        $list = Db::name('merchsite_qualification')->where(['company_id'=>$company_id,'company_type'=>$company_type])->select();
        foreach($list as $k=>$v){
            $list[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);
        }

        return view('index/shop_backend/shop_qualification',compact('company_id','company_type','list'));
    }

    #保存资质信息
    public function save_shop_qualification(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);#0商城，1官网

        if($request->isAjax()){
            if(!isset($dat['img'])){
                return json(['code'=>-1,'msg'=>'请上传资质图片']);
            }

            if($id>0){
                $res = Db::name('merchsite_qualification')->where('id',$dat['id'])->update([
                    'name'=>trim($dat['name']),
                    'img'=>isset($dat['img'][0])?$dat['img'][0]:'',
                ]);
            }
            else{
                $res = Db::name('merchsite_qualification')->insert([
                    'company_id'=>$company_id,
                    'company_type'=>$company_type,
                    'name'=>trim($dat['name']),
                    'img'=>isset($dat['img'][0])?$dat['img'][0]:'',
                    'createtime'=>time()
                ]);
            }

            if($res){
                return json(['code' => 0, 'msg' => '保存成功']);
            }
            else{
                return json(['code' => -1, 'msg' => '暂无修改']);
            }

        }else{
            $data = ['name'=>'','img'=>''];
            if($id>0){
                $data = Db::name('merchsite_qualification')->where('id',$id)->find();
            }

            return view('index/shop_backend/save_shop_qualification',compact('id','data','company_id','company_type'));
        }
    }

    #删除资质信息
    public function del_shop_qualification(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;

        $res = Db::name('merchsite_qualification')->where('id',$dat['id'])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功']);
        }
    }

    #版权信息
    public function shop_copyright(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);#0商城，1官网

        if($request->isAjax()){
            $data = Db::name('website_basic')->where(['company_id'=>$company_id,'company_type'=>$company_type])->find();
            $res = '';
            if(!empty($data)){
                $res = Db::name('website_basic')->where(['company_id'=>$company_id,'company_type'=>$company_type])->update([
                    'copyright'=>trim($dat['copyright']),
                ]);
            }
            else{
                $res = Db::name('website_basic')->insert([
                    'company_id'=>$company_id,
                    'company_type'=>$company_type,
                    'copyright'=>trim($dat['copyright']),
                ]);
            }

            if($res){
                return json(['code' => 0, 'msg' => '保存成功']);
            }
            else{
                return json(['code' => -1, 'msg' => '暂无修改']);
            }
        }else{
            $data = Db::name('website_basic')->where(['company_id'=>$company_id,'company_type'=>$company_type])->find();
            if(empty($data)){
                $data = ['copyright'=>''];
            }
            return view('index/shop_backend/shop_copyright',compact('company_id','company_type','data'));
        }
    }

    #公示信息
    public function shop_public(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);#0商城，1官网

        if($request->isAjax()){
            $publicity_info = [];
            foreach($dat['name'] as $k=>$v){
                array_push($publicity_info,['name'=>trim($v),'link'=>trim($dat['link'][$k])]);
            }

            $data = Db::name('website_basic')->where(['company_id'=>$company_id,'company_type'=>$company_type])->find();
            $res = '';
            if(!empty($data)){
                $res = Db::name('website_basic')->where(['company_id'=>$company_id,'company_type'=>$company_type])->update([
                    'publicity_info'=>json_encode($publicity_info,true),
                ]);
            }
            else{
                $res = Db::name('website_basic')->insert([
                    'company_id'=>$company_id,
                    'company_type'=>$company_type,
                    'publicity_info'=>json_encode($publicity_info,true),
                ]);
            }

            if($res){
                return json(['code' => 0, 'msg' => '保存成功']);
            }
            else{
                return json(['code' => -1, 'msg' => '暂无修改']);
            }
        }else{
            $data = Db::name('website_basic')->where(['company_id'=>$company_id,'company_type'=>$company_type])->find();
            if(empty($data)){
                $data['publicity_info'] = [['name'=>'','link'=>'']];
            }
            else{
                $data['publicity_info'] = json_decode($data['publicity_info'],true);
            }
            return view('index/shop_backend/shop_public',compact('company_id','company_type','data'));
        }
    }

    #轮播图管理
    public function shop_rotate(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);#0商城，1官网

        #企业商城-轮播图
        $list = Db::name('website_rotate')->where(['company_id'=>$company_id,'company_type'=>$company_type])->select();
        $go_method_name = ['无','分享插件','商品详情','网站菜单','网址链接','搜索结果','我要咨询','去找客服'];
        foreach ($list as &$item) {
            $item['go_method'] = $go_method_name[$item['go_other']];
        }

        return view('index/shop_backend/shop_rotate',compact('company_type','company_id','list'));
    }

    #保存轮播图管理
    public function save_shop_rotate(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);#0商城，1官网

        if($request->isAjax()){
            if(!isset($dat['thumb'])){
                return json(['code'=>-1,'msg'=>'请上传轮播图']);
            }

            if($id>0){
                $res = Db::name('website_rotate')->where('id',$dat['id'])->update([
                    'title'=>trim($dat['title']),
                    'thumb'=>isset($dat['thumb'][0])?$dat['thumb'][0]:'',
                    'go_other'=>intval($dat['go_other']),
                    'other_goods'=>$dat['go_other']==2?intval($dat['other_goods']):0,
                    'other_link'=>$dat['go_other']==4?trim($dat['other_link']):'',
                    'other_navbar'=>$dat['go_other']==3?intval($dat['other_navbar']):0,
                    'other_keywords'=>$dat['go_other']==5?trim($dat['other_keywords']):'',
                ]);
            }
            else{
                $res = Db::name('website_rotate')->insert([
                    'company_id'=>$company_id,
                    'company_type'=>$company_type,
                    'title'=>trim($dat['title']),
                    'thumb'=>isset($dat['thumb'][0])?$dat['thumb'][0]:'',
                    'go_other'=>intval($dat['go_other']),
                    'other_goods'=>$dat['go_other']==2?intval($dat['other_goods']):0,
                    'other_link'=>$dat['go_other']==4?trim($dat['other_link']):'',
                    'other_navbar'=>$dat['go_other']==3?intval($dat['other_navbar']):0,
                    'other_keywords'=>$dat['go_other']==5?trim($dat['other_keywords']):'',
                ]);
            }

            if($res){
                if($dat['go_other']==5 && !empty(trim($dat['other_keywords']))) {
                    $this->save_keywords($this->config,explode('、',trim($dat['other_keywords'])));
//                    if(!empty($dat['other_keywords'])){
//                        #新增时自动获取商品(队列服务)；*这里待改，应该是插入关键字表，记录在关键字中，而不是轮播图id
//                        $options = array('http' => array('timeout' => 7500));
//                        $context = stream_context_create($options);
//                        file_get_contents('https://decl.gogo198.cn/api/v2/get_content_goods?type=4&id=' . $id, false, $context);
//                    }
                }
                return json(['code' => 0, 'msg' => '保存成功']);
            }
            else{
                return json(['code' => -1, 'msg' => '暂无修改']);
            }

        }else{
            $data = ['title'=>'','thumb'=>'','go_other'=>'','other_goods'=>0,'other_link'=>'','other_navbar'=>'','other_pic'=>'','other_msg'=>'','other_keywords'=>''];
            if($id>0){
                $data = Db::name('website_rotate')->where('id',$id)->find();
            }

            #导流方式
            #1、菜单
            $type['list'] = $this->shop_menu($company_id,$company_type);

            #2、商品
            $type['goods'] = Db::connect($this->config)->name('goods')->where(['shop_id'=>$company_id,'goods_status'=>1])->select();

            return view('index/shop_backend/save_shop_rotate',compact('id','data','company_id','company_type','type'));
        }
    }

    //记录在关键字表
    public function save_keywords($config=[],$keywords=[]){
        foreach($keywords as $k=>$v){
            $ishave = Db::connect($config)->name('goods_keywords')->where(['keywords'=>$v])->find();
            if(empty($ishave)){
                Db::connect($config)->name('goods_keywords')->where(['keywords'=>$v])->insert([
                    'keywords'=>trim($v)
                ]);
            }
        }
    }

    #删除轮播图
    public function del_shop_rotate(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;

        $res = Db::name('website_rotate')->where('id',$dat['id'])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功']);
        }
    }

    #导流模块管理
    public function shop_guide(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);#0商城，1官网

        #企业商城-轮播图
        $list = Db::connect($this->config)->name('merchsite_guide_body')->where(['company_id'=>$company_id,'company_type'=>$company_type])->select();
        foreach ($list as &$item) {
            $item['content_name'] = Db::connect($this->config)->name('merchsite_guide_format')->where(['id'=>$item['content_id']])->find()['name'];
        }

        return view('index/shop_backend/shop_guide',compact('company_type','company_id','list'));
    }

    #保存导流模块
    public function save_shop_guide(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);#0商城，1官网
        $id = isset($dat['id'])?$dat['id']:0;

        if($request->isAjax()){
            if($id>0){
                $res = Db::connect($this->config)->name('merchsite_guide_body')->where('id',$dat['id'])->update([
                    'title'=>trim($dat['title']),
                    'content_id'=>intval($dat['content_id']),
                    'gkeywords'=>trim($dat['gkeywords'])
                ]);
            }
            else{
                $res = Db::connect($this->config)->name('merchsite_guide_body')->insert([
                    'company_id'=>$company_id,
                    'company_type'=>$company_type,
                    'title'=>trim($dat['title']),
                    'content_id'=>intval($dat['content_id']),
                    'gkeywords'=>trim($dat['gkeywords'])
                ]);
            }

            if($res){
                return json(['code' => 0, 'msg' => '保存成功']);
            }
            else{
                return json(['code' => -1, 'msg' => '暂无修改']);
            }
        }else{
            $data = ['title'=>'','content_id'=>0,'gkeywords'=>''];
            if($id>0){
                $data = Db::connect($this->config)->name('merchsite_guide_body')->where('id',$id)->find();
            }

            #模块版式
            $type['format'] = Db::connect($this->config)->name('merchsite_guide_format')->where(['isshow'=>0])->select();

            return view('index/shop_backend/save_shop_guide',compact('company_type','company_id','data','id','type'));
        }
    }

    #删除导流模块
    public function del_shop_guide(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;

        $res = Db::connect($this->config)->name('merchsite_guide_body')->where('id',$dat['id'])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功']);
        }
    }

    #导流模块内容管理
    public function shop_guide_content_list(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);#0商城，1官网
        $pid = isset($dat['pid'])?intval($dat['pid']):0;
        $guide_id = isset($dat['guide_id'])?intval($dat['guide_id']):0;
        $top_id = isset($dat['top_id'])?intval($dat['top_id']):0;

        if(isset($dat['pa'])){
            $limit = $request->get('limit');
            $page = $request->get('page') - 1;
            if ($page != 0) {
                $page = $limit * $page;
            }
            $where =['company_id'=>$company_id,'top_id'=>$top_id,'pid'=>$guide_id];
            $keyword = isset($dat['keywords']) ? trim($dat['keywords']) : '';

            $count = Db::connect($this->config)->name('guide_content')->where($where)->where('name', 'like', '%'.$keyword.'%')->count();
            $rows = DB::connect($this->config)->name('guide_content')->where($where)
                ->where('name', 'like', '%'.$keyword.'%')
                ->limit($page . ',' . $limit)
                ->order('id desc')
                ->select();

            $go_method_name = ['无','分享插件','商品详情','网站菜单','网址链接','搜索结果','我要咨询','去找客服'];
            foreach ($rows as &$item) {
                $item['go_method'] = $go_method_name[$item['go_other']];
            }

            return json(['code'=>0,'count'=>$count,'data'=>$rows]);
        }else{
            #模块信息-版式
            $guide_info = Db::connect($this->config)->name('merchsite_guide_body')->where(['id'=>$guide_id])->find();

            return view('index/shop_backend/shop_guide_content_list',compact('company_type','company_id','pid','guide_info','guide_id','top_id'));
        }
    }

    #保存导流模块-内容
    public function save_shop_guide_content(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);#0商城，1官网
        $id = isset($dat['id'])?$dat['id']:0;
        $top_id = isset($dat['top_id'])?$dat['top_id']:0;
        $guide_id = isset($dat['guide_id'])?intval($dat['guide_id']):0;
        $guide_info = Db::connect($this->config)->name('merchsite_guide_body')->where(['id'=>$guide_id])->find();

        if($request->isAjax()){
            if($id>0){
                $res = Db::connect($this->config)->name('guide_content')->where('id',$dat['id'])->update([
                    'name'=>trim($dat['name']),
                    'back_content'=>isset($dat['back_content'])?$dat['back_content'][0]:'',
                    'back_content2'=>isset($dat['back_content'])?$dat['back_content'][0]:'',
                    'go_other'=>intval($dat['go_other']),
                    'other_goods'=>$dat['go_other']==2?intval($dat['other_goods']):0,
                    'link'=>$dat['go_other']==4?trim($dat['link']):'',
                    'other_navbar'=>$dat['go_other']==3?intval($dat['other_navbar']):0,
                    'gkeywords'=>$dat['go_other']==5?trim($dat['gkeywords']):'',
                    'desc'=>isset($dat['desc'])?trim($dat['desc']):'',
                ]);
            }
            else{
                $res = Db::connect($this->config)->name('guide_content')->insert([
                    'system_id'=>0,
                    'company_id'=>$company_id,
                    'pid'=>$guide_id,
                    'top_id'=>$top_id,
                    'name'=>trim($dat['name']),
                    'back_type'=>2,
                    'back_content'=>isset($dat['back_content'])?$dat['back_content'][0]:'',
                    'back_content2'=>isset($dat['back_content'])?$dat['back_content'][0]:'',
                    'go_other'=>intval($dat['go_other']),
                    'other_goods'=>$dat['go_other']==2?intval($dat['other_goods']):0,
                    'link'=>$dat['go_other']==4?trim($dat['link']):'',
                    'other_navbar'=>$dat['go_other']==3?intval($dat['other_navbar']):0,
                    'gkeywords'=>$dat['go_other']==5?trim($dat['gkeywords']):'',
                    'desc'=>isset($dat['desc'])?trim($dat['desc']):'',
                ]);
            }

            if($res){
                if($dat['go_other']==5 && !empty(trim($dat['gkeywords']))) {
                    $this->save_keywords($this->config,explode('、',trim($dat['gkeywords'])));
                }
                return json(['code' => 0, 'msg' => '保存成功']);
            }
            else{
                return json(['code' => -1, 'msg' => '暂无修改']);
            }

        }else{
            $data = ['name'=>'','type'=>0,'img_name'=>'','desc'=>'','back_type'=>0,'back_content'=>'','gkeywords'=>'','go_other'=>0,'link'=>'','other_navbar'=>'','other_goods'=>''];
            if($id>0){
                $data = Db::connect($this->config)->name('guide_content')->where('id',$id)->find();
            }

            #导流方式
            #1、菜单
            $type['list'] = $this->shop_menu($company_id,$company_type);
            #2、商品
            $type['goods'] = Db::connect($this->config)->name('goods')->where(['shop_id'=>$company_id,'goods_status'=>1])->select();


            return view('index/shop_backend/save_shop_guide_content',compact('id','data','company_id','company_type','type','guide_info','guide_id','top_id'));
        }
    }

    #删除导流模块-内容
    public function del_shop_guide_content(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        $company_id = intval($dat['company_id']);

        $res = Db::connect($this->config)->name('guide_content')->where(['id'=>$dat['id'],'company_id'=>$company_id])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功']);
        }
    }

    #杂志模式-内容管理
    public function shop_guide_content_list2(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);#0商城，1官网
        $pid = isset($dat['pid'])?intval($dat['pid']):0;
        $guide_id = isset($dat['guide_id'])?intval($dat['guide_id']):0;
        $top_id = isset($dat['top_id'])?intval($dat['top_id']):0;

        if(isset($dat['pa'])){
            $limit = $request->get('limit');
            $page = $request->get('page') - 1;
            if ($page != 0) {
                $page = $limit * $page;
            }
            $where =['company_id'=>$company_id,'top_id'=>$top_id];
            $keyword = isset($dat['keywords']) ? trim($dat['keywords']) : '';

            $count = Db::connect($this->config)->name('guide_content')->where($where)->where('name', 'like', '%'.$keyword.'%')->count();
            $rows = DB::connect($this->config)->name('guide_content')->where($where)
                ->where('name', 'like', '%'.$keyword.'%')
                ->limit($page . ',' . $limit)
                ->order('id desc')
                ->select();

            $go_method_name = ['无','分享插件','商品详情','网站菜单','网址链接','搜索结果','我要咨询','去找客服'];
            foreach ($rows as &$item) {
                $item['go_method'] = $go_method_name[$item['go_other']];
            }

            return json(['code'=>0,'count'=>$count,'data'=>$rows]);
        }else{
            #模块信息-版式
            $guide_info = Db::connect($this->config)->name('merchsite_guide_body')->where(['id'=>$guide_id])->find();

            return view('index/shop_backend/shop_guide_content_list2',compact('company_type','company_id','pid','guide_info','guide_id','top_id'));
        }
    }

    #保存导流模块-内容
    public function save_shop_guide_content2(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);#0商城，1官网
        $id = isset($dat['id'])?$dat['id']:0;
        $top_id = isset($dat['top_id'])?$dat['top_id']:0;
        $guide_id = isset($dat['guide_id'])?intval($dat['guide_id']):0;
        $guide_info = Db::connect($this->config)->name('merchsite_guide_body')->where(['id'=>$guide_id])->find();

        if($request->isAjax()){
            if($id>0){
                $res = Db::connect($this->config)->name('guide_content')->where('id',$dat['id'])->update([
                    'name'=>trim($dat['name']),
                    'back_content'=>isset($dat['back_content'])?$dat['back_content'][0]:'',
                    'back_content2'=>isset($dat['back_content'])?$dat['back_content'][0]:'',
                    'go_other'=>intval($dat['go_other']),
                    'other_goods'=>$dat['go_other']==2?intval($dat['other_goods']):0,
                    'link'=>$dat['go_other']==4?trim($dat['link']):'',
                    'other_navbar'=>$dat['go_other']==3?intval($dat['other_navbar']):0,
                    'gkeywords'=>$dat['go_other']==5?trim($dat['gkeywords']):'',
                    'desc'=>isset($dat['desc'])?trim($dat['desc']):'',
                ]);
            }
            else{
                $res = Db::connect($this->config)->name('guide_content')->insert([
                    'system_id'=>0,
                    'company_id'=>$company_id,
                    'pid'=>$guide_id,
                    'top_id'=>$top_id,
                    'name'=>trim($dat['name']),
                    'back_type'=>2,
                    'back_content'=>isset($dat['back_content'])?$dat['back_content'][0]:'',
                    'back_content2'=>isset($dat['back_content'])?$dat['back_content'][0]:'',
                    'go_other'=>intval($dat['go_other']),
                    'other_goods'=>$dat['go_other']==2?intval($dat['other_goods']):0,
                    'link'=>$dat['go_other']==4?trim($dat['link']):'',
                    'other_navbar'=>$dat['go_other']==3?intval($dat['other_navbar']):0,
                    'gkeywords'=>$dat['go_other']==5?trim($dat['gkeywords']):'',
                    'desc'=>isset($dat['desc'])?trim($dat['desc']):'',
                ]);
            }

            if($res){
                if($dat['go_other']==5 && !empty(trim($dat['gkeywords']))) {
                    $this->save_keywords($this->config,explode('、',trim($dat['gkeywords'])));
                }
                return json(['code' => 0, 'msg' => '保存成功']);
            }
            else{
                return json(['code' => -1, 'msg' => '暂无修改']);
            }

        }else{
            $data = ['name'=>'','type'=>0,'img_name'=>'','desc'=>'','back_type'=>0,'back_content'=>'','gkeywords'=>'','go_other'=>0,'link'=>'','other_navbar'=>'','other_goods'=>''];
            if($id>0){
                $data = Db::connect($this->config)->name('guide_content')->where('id',$id)->find();
            }

            #导流方式
            #1、菜单
            $type['list'] = $this->shop_menu($company_id,$company_type);
            #2、商品
            $type['goods'] = Db::connect($this->config)->name('goods')->where(['shop_id'=>$company_id,'goods_status'=>1])->select();


            return view('index/shop_backend/save_shop_guide_content2',compact('id','data','company_id','company_type','type','guide_info','guide_id','top_id'));
        }
    }

    #首页推荐管理
    public function shop_recommend(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);#0商城，1官网

        if(isset($dat['pa'])){
            $limit = $request->get('limit');
            $page = $request->get('page') - 1;
            if ($page != 0) {
                $page = $limit * $page;
            }
            $where =['company_id'=>$company_id,'company_type'=>$company_type];
            $keyword = isset($dat['keywords']) ? trim($dat['keywords']) : '';

            $count = Db::name('website_discovery_list')->where($where)->where('descs', 'like', '%'.$keyword.'%')->count();
            $rows = DB::name('website_discovery_list')->where($where)
                ->where('descs', 'like', '%'.$keyword.'%')
                ->limit($page . ',' . $limit)
                ->order('id desc')
                ->select();

            $go_method_name = ['无','分享插件','商品详情','网站菜单','网址链接','搜索结果','我要咨询','去找客服'];
            foreach ($rows as &$item) {
                $item['go_method'] = $go_method_name[$item['go_other']];
            }

            return json(['code'=>0,'count'=>$count,'data'=>$rows]);
        }else{
            $where =['company_id'=>$company_id,'company_type'=>$company_type];
            $list = Db::name('website_discovery_list')->where($where)->select();
            $go_method_name = ['无','分享插件','商品详情','网站菜单','网址链接','搜索结果','我要咨询','去找客服'];
            foreach ($list as $k=>$item) {
                $list[$k]['go_method'] = $go_method_name[$item['go_other']];
            }

            return view('index/shop_backend/shop_recommend',compact('company_id','company_type','list'));
        }
    }

    #保存推荐区发现轮播
    public function save_shop_recommend(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);#0商城，1官网

        if($request->isAjax()){
            if(!isset($dat['thumb'])){
                return json(['code'=>-1,'msg'=>'请上传轮播图']);
            }

            if($id>0){
                $res = Db::name('website_discovery_list')->where('id',$dat['id'])->update([
                    'descs'=>trim($dat['descs']),
                    'thumb'=>isset($dat['thumb'][0])?$dat['thumb'][0]:'',
                    'go_other'=>intval($dat['go_other']),
                    'other_goods'=>$dat['go_other']==2?intval($dat['other_goods']):0,
                    'other_link'=>$dat['go_other']==4?trim($dat['other_link']):'',
                    'other_navbar'=>$dat['go_other']==3?intval($dat['other_navbar']):0,
                    'other_keywords'=>$dat['go_other']==5?trim($dat['other_keywords']):'',
                ]);
            }
            else{
                $res = Db::name('website_discovery_list')->insert([
                    'system_id'=>0,
                    'company_id'=>$company_id,
                    'company_type'=>$company_type,
                    'descs'=>trim($dat['descs']),
                    'thumb'=>isset($dat['thumb'][0])?$dat['thumb'][0]:'',
                    'go_other'=>intval($dat['go_other']),
                    'other_goods'=>$dat['go_other']==2?intval($dat['other_goods']):0,
                    'other_link'=>$dat['go_other']==4?trim($dat['other_link']):'',
                    'other_navbar'=>$dat['go_other']==3?intval($dat['other_navbar']):0,
                    'other_keywords'=>$dat['go_other']==5?trim($dat['other_keywords']):'',
                    'createtime'=>time()
                ]);
            }

            if($res){
                if($dat['go_other']==5 && !empty(trim($dat['other_keywords']))) {
                    $this->save_keywords($this->config,explode('、',trim($dat['other_keywords'])));
                }
                return json(['code' => 0, 'msg' => '保存成功']);
            }
            else{
                return json(['code' => -1, 'msg' => '暂无修改']);
            }

        }else{
            $data = ['descs'=>'','thumb'=>'','go_other'=>'','other_goods'=>0,'other_link'=>'','other_navbar'=>'','other_pic'=>'','other_msg'=>'','other_keywords'=>''];
            if($id>0){
                $data = Db::name('website_discovery_list')->where('id',$id)->find();
            }

            #导流方式
            #1、菜单
            $type['list'] = $this->shop_menu($company_id,$company_type);
            #2、商品
            $type['goods'] = Db::connect($this->config)->name('goods')->where(['shop_id'=>$company_id,'goods_status'=>1])->select();

            return view('index/shop_backend/save_shop_recommend',compact('id','data','company_id','company_type','type'));
        }
    }

    #删除推荐区发现轮播
    public function del_shop_recommend(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;

        $res = Db::name('website_discovery_list')->where('id',$dat['id'])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功']);
        }
    }

    #推荐资讯管理
    public function shop_recommend2(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);#0商城，1官网

        if(isset($dat['pa'])){
            $limit = $request->get('limit');
            $page = $request->get('page') - 1;
            if ($page != 0) {
                $page = $limit * $page;
            }
            $where =['company_id'=>$company_id];
            $keyword = isset($dat['keywords']) ? trim($dat['keywords']) : '';

            $count = Db::name('merchsite_recommend_b')->where($where)->where('name', 'like', '%'.$keyword.'%')->count();
            $rows = DB::name('merchsite_recommend_b')->where($where)
                ->where('name', 'like', '%'.$keyword.'%')
                ->limit($page . ',' . $limit)
                ->order('id desc')
                ->select();

            $go_method_name = ['无','分享插件','商品详情','网站菜单','网址链接','搜索结果','我要咨询','去找客服'];
            foreach ($rows as &$item) {
                $item['go_method'] = $go_method_name[$item['go_other']];
            }

            return json(['code'=>0,'count'=>$count,'data'=>$rows]);
        }else{
            $where =['company_id'=>$company_id];
            $list = Db::name('merchsite_recommend_b')->where($where)->select();
            $go_method_name = ['无','分享插件','商品详情','网站菜单','网址链接','搜索结果','我要咨询','去找客服'];
            foreach ($list as $k=>$item) {
                $list[$k]['go_method'] = $go_method_name[$item['go_other']];
            }

            return view('index/shop_backend/shop_recommend2',compact('company_id','company_type','list'));
        }
    }

    #保存推荐区资讯
    public function save_shop_recommend2(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);#0商城，1官网

        if($request->isAjax()){
            if($id>0){
                $res = Db::name('merchsite_recommend_b')->where('id',$dat['id'])->update([
                    'name'=>trim($dat['name']),
                    'go_other'=>intval($dat['go_other']),
                    'other_link'=>$dat['go_other']==4?trim($dat['other_link']):0,
                    'other_navbar'=>$dat['go_other']==3?intval($dat['other_navbar']):0,
                    'other_keywords'=>$dat['go_other']==5?trim($dat['other_keywords']):0,

                ]);
            }
            else{
                $res = Db::name('merchsite_recommend_b')->insert([
                    'company_id'=>$company_id,
                    'name'=>trim($dat['name']),
                    'go_other'=>intval($dat['go_other']),
                    'other_link'=>$dat['go_other']==4?trim($dat['other_link']):0,
                    'other_navbar'=>$dat['go_other']==3?intval($dat['other_navbar']):0,
                    'other_keywords'=>$dat['go_other']==5?trim($dat['other_keywords']):0,
                ]);
            }

            if($res){
                return json(['code' => 0, 'msg' => '保存成功']);
            }
            else{
                return json(['code' => -1, 'msg' => '暂无修改']);
            }

        }else{
            $data = ['name'=>'','thumb'=>'','go_other'=>'','other_goods'=>0,'other_link'=>'','other_navbar'=>'','other_pic'=>'','other_msg'=>'','other_keywords'=>''];
            if($id>0){
                $data = Db::name('merchsite_recommend_b')->where('id',$id)->find();
            }

            #导流方式
            #1、菜单
//            $type['list'] = $this->shop_menu($company_id,$company_type);
            #2、商品
//            $type['goods'] = Db::connect($this->config)->name('goods')->where(['shop_id'=>$company_id,'goods_status'=>1])->select();

            return view('index/shop_backend/save_shop_recommend2',compact('id','data','company_id','company_type','type'));
        }
    }

    #删除推荐区资讯
    public function del_shop_recommend2(Request $request){
        $dat = input();
        $id = isset($dat['id'])?$dat['id']:0;

        $res = Db::name('merchsite_recommend_b')->where('id',$dat['id'])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功']);
        }
    }

    #搜索结果管理
    public function search_result(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);#0商城，1官网

        if($request->isAjax()){
            $data = Db::name('website_basic')->where(['company_id'=>$company_id,'company_type'=>$company_type])->find();
            $res = '';
            if(!empty($data)){
                $res = Db::name('website_basic')->where(['company_id'=>$company_id,'company_type'=>$company_type])->update([
                    'search_format'=>json_encode($dat['search_format'],true),
                ]);
            }
            else{
                $res = Db::name('website_basic')->insert([
                    'company_id'=>$company_id,
                    'company_type'=>$company_type,
                    'search_format'=>json_encode($dat['search_format'],true),
                ]);
            }

            if($res){
                return json(['code' => 0, 'msg' => '保存成功！']);
            }
            else{
                return json(['code' => -1, 'msg' => '暂无修改！']);
            }
        }else{
            $data = Db::name('website_basic')->where(['company_id'=>$company_id,'company_type'=>$company_type])->find();
            if(empty($data['search_format'])){
                $data = ['search_format'=>[5,5]];
            }
            else{
                $data['search_format'] = json_decode($data['search_format'],true);
            }

            return view('index/shop_backend/search_result',compact('data','company_id','company_type'));
        }
    }

    #新增商品
    public function save_product(Request $request){
        $dat = input();
        $id = isset($dat['id'])?intval($dat['id']):0;
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);#0商城，1官网
        $type = isset($dat['type'])?intval($dat['type']):0;

        if($request->isAjax()){
            $created_at = date('Y-m-d H:i:s');

            if($type==1){
                #赠品
                if($dat['add_method']==2){
                    #选择商品作为赠品
                    Db::connect($this->config)->name('goods_merchant')->where(['id'=>$dat['select_goods']])->update(['type'=>$type]);
                    return json(['code'=>0,'msg'=>'保存成功','id'=>$dat['select_goods']]);
                }
            }


            #商品分类===============================start
            $cat_id1 = 0;$cat_id2 = 0;$cat_id3 = 0;$cat_id = 0;
            if($dat['cat_id1']==-1){
                #添加商品分类
                if(empty($dat['diy_catname1'])){
                    return json(['code'=>-1,'msg'=>'请输入自定义商品分类']);
                }
                $cate_name = explode('、',$dat['diy_catname1']);

                foreach($cate_name as $k=>$v){
                    $ishave = Db::connect($this->config)->name('category')->where(['cat_name'=>$v])->find();

                    if(empty($ishave)){
                        if(empty($cat_id1)){
                            $cat_id = Db::connect($this->config)->name('category')->insertGetId([
                                'cat_name'=>trim($v),
                                'type_id'=>1,
                                'parent_id'=>0,
                                'cat_level'=>1,
                                'is_show'=>1,
                                'created_at'=>$created_at
                            ]);
                            $cat_id1 = $cat_id;
                        }
                        elseif(empty($cat_id2)){
                            $cat_id = Db::connect($this->config)->name('category')->insertGetId([
                                'cat_name'=>trim($v),
                                'type_id'=>1,
                                'parent_id'=>$cat_id1,
                                'cat_level'=>2,
                                'is_show'=>1,
                                'created_at'=>$created_at
                            ]);
                            $cat_id2 = $cat_id;
                        }
                        elseif(empty($cat_id3)){
                            $cat_id = Db::connect($this->config)->name('category')->insertGetId([
                                'cat_name'=>trim($v),
                                'type_id'=>1,
                                'parent_id'=>$cat_id2,
                                'cat_level'=>3,
                                'is_show'=>1,
                                'created_at'=>$created_at
                            ]);
                            $cat_id3 = $cat_id;
                        }
                    }
                    else{
                        if(empty($cat_id1)){
                            $cat_id = $ishave['cat_id'];
                            $cat_id1 = $ishave['cat_id'];
                        }
                        elseif(empty($cat_id2)){
                            $cat_id = $ishave['cat_id'];
                            $cat_id2 = $ishave['cat_id'];
                        }
                        elseif(empty($cat_id3)){
                            $cat_id = $ishave['cat_id'];
                            $cat_id3 = $ishave['cat_id'];
                        }
                    }
                }
            }
            else{
                $cat_id1 = intval($dat['cat_id1']);
                $cat_id2 = intval($dat['cat_id2']);
                $cat_id3 = intval($dat['cat_id3']);
                if(empty($cat_id1)){
                    return json(['code'=>-1,'msg'=>'请选择类别']);
                }
                if(!empty($cat_id3)){
                    $cat_id = $cat_id3;
                }
                elseif(!empty($cat_id2)){
                    $cat_id = $cat_id2;
                }
                elseif(!empty($cat_id1)){
                    $cat_id = $cat_id1;
                }
            }
            #商品分类===============================end

            #商品品牌===============================start
            if($dat['brand_type']==1){
                #有牌
                if($dat['brand_type2']==0){
                    #自有品牌
                    if(empty(trim($dat['brand_name']))){
                        return json(['code'=>-1,'msg'=>'请输入品牌名称']);
                    }
                }
            }
            #商品品牌===============================end

            #商品主图===============================start
            if(empty($dat['goods_image'])){
                return json(['code'=>-1,'msg'=>'请上传一张商品主图']);
            }
            #商品主图===============================end

            #商品副图===============================start
            if(empty($dat['images_arr'])){
                return json(['code'=>-1,'msg'=>'请上传商品副图']);
            }
            #商品副图===============================end

            $goods_merchant_id = $id;
            if($id>0){
                $goods = Db::connect($this->config)->name('goods_merchant')->where(['id'=>$id])->find();

                Db::connect($this->config)->name('goods_merchant')->where(['id'=>$id])->update([
                    'goods_name'=>trim($dat['goods_name']),
                    'type'=>$type,
                    'cat_id'=>$cat_id,
                    'cat_id1'=>$cat_id1,
                    'cat_id2'=>$cat_id2,
                    'cat_id3'=>$cat_id3,
                    'brand_type'=>$dat['brand_type'],
                    'brand_type2'=>$dat['brand_type']==1?$dat['brand_type2']:'',
                    'brand_id'=>$dat['brand_type2']==1?$dat['brand_id']:0,
                    'brand_name'=>$dat['brand_type2']==0?trim($dat['brand_name']):'',
                    'goods_image'=>$dat['goods_image'],
                    'goods_video'=>isset($dat['video'])?$dat['video']:'',
                ]);

                #主图
                Db::connect($this->config)->name('goods_image_merchant')->where(['is_default'=>1,'sort'=>1,'cid'=>$company_id,'goods_id'=>$id])->update([
                    'path'=>$dat['goods_image'],
                ]);

                #副图
                if(!empty($dat['images_arr'])){
                    foreach($dat['images_arr'] as $k=>$v){
                        $ishave = Db::connect($this->config)->name('goods_image_merchant')->where(['cid'=>$company_id,'goods_id'=>$id,'is_default'=>0,'path'=>$v])->find();
                        if(empty($ishave)){
                            Db::connect($this->config)->name('goods_image_merchant')->insert([
                                'cid'=>$company_id,
                                'goods_id'=>$id,
                                'path'=>$v,
                                'is_default'=>0,
                                'sort'=>$k+2,
                                'created_at'=>$created_at,
                                'updated_at'=>$created_at
                            ]);
                        }
                    }
                }

                #同步平台商品表
                if($goods['shelf_id']>0){
                    Db::connect($this->config)->name('goods')->where(['goods_id'=>$goods['shelf_id']])->update([
                        'goods_name'=>trim($dat['goods_name']),
                        'type'=>$type,
                        'cat_id'=>$cat_id,
                        'cat_id1'=>$cat_id1,
                        'cat_id2'=>$cat_id2,
                        'cat_id3'=>$cat_id3,
                        'brand_type'=>$dat['brand_type'],
                        'brand_type2'=>$dat['brand_type']==1?$dat['brand_type2']:'',
                        'brand_id'=>$dat['brand_type2']==1?$dat['brand_id']:0,
                        'brand_name'=>$dat['brand_type2']==0?trim($dat['brand_name']):'',
                        'goods_image'=>$dat['goods_image'],
                        'goods_video'=>isset($dat['video'])?$dat['video']:'',
                    ]);

                    #主图
                    Db::connect($this->config)->name('goods_image')->where(['is_default'=>1,'sort'=>1,'goods_id'=>$goods['shelf_id']])->update([
                        'path'=>$dat['goods_image'],
                    ]);

                    #副图
                    if(!empty($dat['images_arr'])){
                        Db::connect($this->config)->name('goods_image')->where(['goods_id'=>$goods['shelf_id'],'is_default'=>0])->delete();
                        foreach($dat['images_arr'] as $k=>$v){
                            Db::connect($this->config)->name('goods_image')->insert([
                                'goods_id'=>$goods['shelf_id'],
                                'path'=>$v,
                                'is_default'=>0,
                                'sort'=>$k+2,
                                'created_at'=>$created_at,
                                'updated_at'=>$created_at
                            ]);
                        }
                    }
                }
            }
            else{
                $goods_merchant_id = Db::connect($this->config)->name('goods_merchant')->insertGetId([
                    'cid'=>$company_id,
                    'goods_name'=>trim($dat['goods_name']),
                    'type'=>$type,
                    'cat_id'=>$cat_id,
                    'cat_id1'=>$cat_id1,
                    'cat_id2'=>$cat_id2,
                    'cat_id3'=>$cat_id3,
                    'brand_type'=>$dat['brand_type'],
                    'brand_type2'=>$dat['brand_type']==1?$dat['brand_type2']:'',
                    'brand_id'=>$dat['brand_type2']==1?$dat['brand_id']:0,
                    'brand_name'=>$dat['brand_type2']==0?trim($dat['brand_name']):'',
                    'goods_image'=>$dat['goods_image'],
                    'goods_video'=>isset($dat['video'])?$dat['video']:'',
                    'created_at'=>$created_at,
                ]);

                #主图
                Db::connect($this->config)->name('goods_image_merchant')->insert([
                    'cid'=>$company_id,
                    'goods_id'=>$goods_merchant_id,
                    'path'=>$dat['goods_image'],
                    'is_default'=>1,
                    'sort'=>1,
                    'created_at'=>$created_at,
                    'updated_at'=>$created_at
                ]);

                #副图
                if(!empty($dat['images_arr'])){
                    foreach($dat['images_arr'] as $k=>$v){
                        Db::connect($this->config)->name('goods_image_merchant')->insert([
                            'cid'=>$company_id,
                            'goods_id'=>$goods_merchant_id,
                            'path'=>$v,
                            'is_default'=>0,
                            'sort'=>$k+2,
                            'created_at'=>$created_at,
                            'updated_at'=>$created_at
                        ]);
                    }
                }
            }

            return json(['code'=>0,'msg'=>'保存成功','id'=>$goods_merchant_id]);
        }
        else{

            $data = ['cate_id'=>0,'cat_info'=>['cat_ids'=>[],'cat_names'=>''],'brand_type'=>0,'brand_type2'=>'','brand_id'=>0,'brand_name'=>'','goods_name'=>'','goods_image'=>'','goods_video'=>'','images_arr'=>[]];
            if($id>0){
                $data = Db::connect($this->config)->name('goods_merchant')->where(['id'=>$id,'cid'=>$company_id])->find();
                $data['images_arr'] = Db::connect($this->config)->name('goods_image_merchant')->where(['goods_id'=>$id,'cid'=>$company_id,'is_default'=>0])->select();

                $cat_level3 = Db::connect($this->config)->name('category')->where(['cat_id'=>$data['cat_id']])->find();
                $cat_level2 = [];$cat_level1 = [];
                if($cat_level3['parent_id']>0){
                    $cat_level2 = Db::connect($this->config)->name('category')->where(['cat_id'=>$cat_level3['parent_id']])->find();
                    if($cat_level2['parent_id']>0){
                        $cat_level1 = Db::connect($this->config)->name('category')->where(['cat_id'=>$cat_level2['parent_id']])->find();
                    }
                }

                $data['cat_info'] = ['cat_ids'=>[],'cat_names'=>[]];
                if(!empty($cat_level1)){
                    $data['cat_info']['cat_ids'][0] = $cat_level1['cat_id'];
                    $data['cat_info']['cat_names'][0] = $cat_level1['cat_name'];
                }

                if(!empty($cat_level2)){
                    $data['cat_info']['cat_ids'][1] = $cat_level2['cat_id'];
                    $data['cat_info']['cat_names'][1] = $cat_level2['cat_name'];
                }

                if(!empty($cat_level3)){
                    $data['cat_info']['cat_ids'][2] = $cat_level3['cat_id'];
                    $data['cat_info']['cat_names'][2] = $cat_level3['cat_name'];
                }
            }

            $sale_cate = Db::connect($this->config)->name('category')->where(['type_id'=>1,'parent_id'=>0])->select();

            $brand = Db::name('centralize_diycountry_content')->where(['pid'=>8])->select();

            $goods_list = Db::connect($this->config)->name('goods_merchant')->where(['type'=>0,'cid'=>$company_id])->order('id desc')->select();

            return view('index/shop_backend/save_product',compact('id','company_id','company_type','data','brand','sale_cate','type','goods_list'));
        }
    }

    #获取商品分类（下级）
    public function get_nextcate(Request $request){
        $dat = input();
        $list = Db::connect($this->config)->name('category')->where(['parent_id'=>$dat['parent_id']])->select();

        #获取该类别的属性
        $value = Db::connect($this->config)->name('ssl_value')->where(['cate_id'=>$dat['parent_id'],'pid'=>0])->select();

        #该类别下的品牌
        $brands = Db::connect($this->config)->name('category')->where(['cat_id'=>$dat['parent_id']])->field(['brand_ids'])->find();
        $brand_list = [
            [
                'brand_id' => 0,
                'brand_name' => '-- 请选择品牌 --'
            ],
            [
                'brand_id' => -1,
                'brand_name' => '自定义品牌'
            ]
        ];
        if (!empty($brands->brand_ids)) {
            $brands->brand_ids = explode(',',rtrim($brands->brand_ids,','));
            foreach ($brands->brand_ids as $item) {
                $brand_name = Db::connect($this->config)->name('brand')->where(['brand_id'=>$item])->find()['brand_name'];
                $brand_list[] = [
                    'brand_id' => $item,
                    'brand_name' => $brand_name
                ];
            }
        }

        return json(['code'=>0,'data'=>$list,'value'=>$value,'brand'=>$brand_list]);
    }

    #管理商品
    public function product_manage(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);#0商城，1官网
        $type = isset($dat['type'])?intval($dat['type']):0;

        if(isset($dat['pa'])){
            $limit = $request->get('limit');
            $page = $request->get('page') - 1;
            if ($page != 0) {
                $page = $limit * $page;
            }
            $keyword = isset($dat['keywords']) ? trim($dat['keywords']) : '';
            $where =['cid'=>$company_id,'type'=>$type];

            $count = Db::connect($this->config)->name('goods_merchant')->where($where)->where('goods_name', 'like', '%'.$keyword.'%')->count();
            $rows = Db::connect($this->config)->name('goods_merchant')->where($where)
                ->where('goods_name', 'like', '%'.$keyword.'%')
                ->limit($page.','.$limit)
                ->order('id desc')
                ->select();

            $_status = ['-1'=>'已下架','0'=>'待审核','1'=>'已上架'];
            foreach ($rows as &$item) {
                $item['status_name'] = $_status[$item['goods_status']];
                if($item['goods_audit']==0 && $item['goods_status']==1){
                    $item['status_name'] .= '待审核';
                }
            }

            return json(['code'=>0,'count'=>$count,'data'=>$rows]);
        }
        else{

            return view('index/shop_backend/product_manage',compact('company_type','company_id','type'));
        }
    }

    #赠送关联
    public function connect_product(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);#0商城，1官网
        $type = isset($dat['type'])?intval($dat['type']):0;
        $id = intval($dat['id']);

        if($request->isAjax()){
            $data = Db::connect($this->config)->name('goods_connect_gift')->where(['company_id'=>$company_id,'gift_id'=>$id])->find();
            if(!empty($data)){
                Db::connect($this->config)->name('goods_connect_gift')->where(['company_id'=>$company_id,'gift_id'=>$id])->update([
                    'goods_id'=>$dat['goods_id'],
                    'reduction_money'=>floatval($dat['reduction_money']),
                    'num'=>intval($dat['num']),
                    'remark'=>trim($dat['remark']),
                    'type'=>intval($dat['type']),
                    'end_time'=>strtotime($dat['end_time']),
                ]);
            }else{
                Db::connect($this->config)->name('goods_connect_gift')->insert([
                    'goods_id'=>$dat['goods_id'],
                    'company_id'=>$company_id,
                    'gift_id'=>$id,
                    'reduction_money'=>floatval($dat['reduction_money']),
                    'num'=>intval($dat['num']),
                    'remark'=>trim($dat['remark']),
                    'type'=>intval($dat['type']),
                    'end_time'=>strtotime($dat['end_time']),
                ]);
            }
            return json(['code'=>0,'msg'=>'保存成功']);
        }else{
            #关联商品
            $goods_list = Db::connect($this->config)->name('goods')->where(['type'=>0,'shop_id'=>$company_id])->order('goods_id desc')->select();

            $merchant_goods_info = Db::connect($this->config)->name('goods_merchant')->where(['id'=>$id])->find();
            $gift_info = [];
            $currency_symbol = '';
            if(!empty($merchant_goods_info['shelf_id'])){
                #赠品信息
                $gift_info = Db::connect($this->config)->name('goods')->where(['type'=>1,'shop_id'=>$company_id,'goods_id'=>$merchant_goods_info['shelf_id']])->find();
                $currency_symbol = Db::name('centralize_currency')->where(['id'=>$gift_info['goods_currency']])->field('currency_symbol_standard')->find()['currency_symbol_standard'];
            }
            $data = Db::connect($this->config)->name('goods_connect_gift')->where(['company_id'=>$company_id,'gift_id'=>$id])->find();
            if(empty($data)){
                $data = ['goods_id'=>0,'gift_id'=>$id,'reduction_money'=>0,'num'=>0,'remark'=>'','type'=>0,'end_time'=>''];
            }else{
                $data['end_time'] = date('Y-m-d H:i:s',$data['end_time']);
            }


            return view('index/shop_backend/connect_product',compact('id','company_id','company_type','type','goods_list','gift_info','data','currency_symbol'));
        }
    }

    #删除商品
    public function del_product(Request $request){
        $dat = input();
        $id = intval($dat['id']);

        Db::connect($this->config)->name('goods_image_merchant')->where(['goods_id'=>$id])->delete();
        $res = Db::connect($this->config)->name('goods_merchant')->where(['id'=>$id])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功']);
        }
    }

    #快速选品
    public function quicky_selgoods(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);#0商城，1官网

        return view('index/shop_backend/quicky_selgoods',compact('company_id','company_type'));
    }

    #商品系列
    public function product_series(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);#0商城，1官网

        if(isset($dat['pa'])){
            $limit = $request->get('limit');
            $page = $request->get('page') - 1;
            if ($page != 0) {
                $page = $limit * $page;
            }
            $keyword = isset($dat['keywords']) ? trim($dat['keywords']) : '';
            $where =['company_id'=>$company_id,'company_type'=>$company_type];

            $count = Db::name('website_goods_series')->where($where)->where('title', 'like', '%'.$keyword.'%')->count();
            $rows = Db::name('website_goods_series')->where($where)
                ->where('title', 'like', '%'.$keyword.'%')
                ->limit($page.','.$limit)
                ->order('id desc')
                ->select();


            foreach ($rows as &$item) {
                $item['goods_num'] = count(explode(',',$item['goods_id']));
                if($item['type']==1){
                    if($item['condition']==0){
                        $item['condition'] = '商品须匹配：所有条件';
                    }elseif($item['condition']==1){
                        $item['condition'] = '商品须匹配：任一条件';
                    }
                }else{
                    $item['condition']='';
                }
                $item['createtime'] = date('Y-m-d H:i',$item['createtime']);
            }

            return json(['code'=>0,'count'=>$count,'data'=>$rows]);
        }else{
            return view('index/shop_backend/product_series',compact('company_id','company_type'));
        }
    }

    #保存商品系列
    public function save_product_series(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);#0商城，1官网
        $id = isset($dat['id'])?intval($dat['id']):0;

        if($request->isAjax()){
            if($id>0){
                #修改
                Db::name('website_goods_series')->where(['id'=>$id,'company_id'=>$company_id])->update([
                    'title'=>trim($dat['title']),
                    'desc'=>json_encode($dat['desc'],true),
                    'image'=>isset($dat['image'])?$dat['image'][0]:'',
                    'type'=>$dat['type'],
                    'condition'=>$dat['type']==1?$dat['condition']:0,
                    'condition_list'=>$dat['type']==1?json_encode($dat['condition_list'],true):'',
                    'goods_id'=>$dat['type']==0?$dat['goods_id']:'',
                    'page_title'=>trim($dat['page_title']),
                    'page_desc'=>trim($dat['page_desc']),
                    'page_url'=>trim($dat['page_url'])
                ]);
            }else{
                #新增
                Db::name('website_goods_series')->insert([
                    'company_id'=>$company_id,
                    'company_type'=>$company_type,
                    'title'=>trim($dat['title']),
                    'desc'=>json_encode($dat['desc'],true),
                    'image'=>isset($dat['image'])?$dat['image'][0]:'',
                    'type'=>$dat['type'],
                    'condition'=>$dat['type']==1?$dat['condition']:0,
                    'condition_list'=>$dat['type']==1?json_encode($dat['condition_list'],true):'',
                    'goods_id'=>$dat['type']==0?$dat['goods_id']:'',
                    'page_title'=>trim($dat['page_title']),
                    'page_desc'=>trim($dat['page_desc']),
                    'page_url'=>trim($dat['page_url']),
                    'createtime'=>time(),
                ]);
            }

            return json(['code'=>0,'msg'=>'保存系列成功']);
        }else{
            $data = ['title'=>'','desc'=>'','type'=>0,'condition'=>0,'condition_list'=>'','goods_id'=>'','image'=>'','page_title'=>'','page_desc'=>'','page_url'=>''];
            if($id>0){
                $data = Db::name('website_goods_series')->where(['id'=>$id,'company_id'=>$company_id,'company_type'=>$company_type])->find();
                $data['desc'] = json_decode($data['desc'],true);
                $data['condition_list'] = json_decode($data['condition_list'],true);

            }

            #列出所有该企业的商品
            $goods = Db::connect($this->config)->name('goods')->where(['shop_id'=>$company_id,'goods_status'=>1])->select();
            $goods = json_encode($goods,true);

            return view('index/shop_backend/save_product_series',compact('company_id','company_type','id','data','goods'));
        }
    }

    #删除商品系列
    public function del_product_series(Request $request){
        $dat = input();
        $id = intval($dat['id']);

        $res = Db::name('website_goods_series')->where(['id'=>$id])->delete();

        if($res){
            return json(['code'=>0,'msg'=>'删除成功']);
        }
    }

    #出入库存
    public function save_inventory(Request $request){
        $dat = input();
        $id = isset($dat['id'])?intval($dat['id']):0;
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);#0商城，1官网
        $type2 = isset($dat['type'])?intval($dat['type']):0;#0商品，1赠品
        $is_shelf_link = isset($dat['is_shelf_link'])?intval($dat['is_shelf_link']):0;#0未到上架环节，1已到上架环节

        if($request->isAjax()){
            $goods = Db::connect($this->config)->name('goods_merchant')->where(['id' => $dat['goods_id']])->find();
            if($goods['have_specs']==1){
                #有规格
                $goods_sku = Db::connect($this->config)->name('goods_sku_merchant')->where(['goods_id' => $dat['goods_id']])->select();
                foreach($goods_sku as $k=>$v){
                    $goods_sku[$k]['sku_prices'] = json_decode($v['sku_prices'],true);
                    $goods_sku[$k]['sku_prices']['goods_number'] = intval($dat['goods_number'][$k]);
                    Db::connect($this->config)->name('goods_sku_merchant')->where(['sku_id' => $v['sku_id']])->update([
                        'sku_prices'=>json_encode($goods_sku[$k]['sku_prices'],true),
                        'goods_number'=>intval($dat['goods_number'][$k]),
                    ]);

                    #同步平台商品表
                    if($goods['shelf_id']>0){
                        $origin_sku = Db::connect($this->config)->name('goods_sku_merchant')->where(['sku_id' => $v['sku_id']])->find();
                        Db::connect($this->config)->name('goods_sku')->where(['goods_id' => $goods['shelf_id'],'spec_vids'=>$origin_sku['spec_vids']])->update([
                            'sku_prices'=>json_encode($goods_sku[$k]['sku_prices'],true),
                            'goods_number'=>intval($dat['goods_number'][$k]),
                        ]);
                    }
                }
            }
            elseif($goods['have_specs']==2){
                #无规格
                $goods_sku = Db::connect($this->config)->name('goods_sku_merchant')->where(['goods_id' => $dat['goods_id']])->find();
                $goods_sku['sku_prices'] = json_decode($goods_sku['sku_prices'],true);
                $goods_sku['sku_prices']['goods_number'] = intval($dat['goods_number']);
                Db::connect($this->config)->name('goods_sku_merchant')->where(['goods_id' => $dat['goods_id']])->update([
                    'sku_prices'=>json_encode($goods_sku['sku_prices'],true),
                    'goods_number'=>intval($dat['goods_number']),
                ]);

                #同步平台商品表
                if($goods['shelf_id']>0){
                    Db::connect($this->config)->name('goods_sku')->where(['goods_id' => $goods['shelf_id']])->update([
                        'sku_prices'=>json_encode($goods_sku['sku_prices'],true),
                        'goods_number'=>intval($dat['goods_number']),
                    ]);
                }
            }

            if(empty($dat['areas'])){
                return json(['code'=>-1,'msg'=>'请选择发货区域']);
            }
            #更改发货国地与详细地址
            Db::connect($this->config)->name('goods_merchant')->where(['id' => $dat['goods_id']])->update([
                'shipping_country'=>$dat['shipping_country'],
                'goods_currency'=>$dat['goods_currency'],
                'areas'=>json_encode($dat['areas'],true)
            ]);

            #同步平台商品表
            if($goods['shelf_id']>0){
                Db::connect($this->config)->name('goods')->where(['goods_id' => $goods['shelf_id']])->update([
                    'shipping_country'=>$dat['shipping_country'],
                    'goods_currency'=>$dat['goods_currency'],
                    'areas'=>json_encode($dat['areas'],true)
                ]);
            }

            return json(['code'=>0,'msg'=>'保存成功','id'=>$dat['goods_id']]);
        }
        else{
            $data = ['type_id'=>0,'id'=>0,'shipping_country'=>0,'areas'=>[],'goods_currency'=>'','reason_id'=>0,'warehouse_id'=>0,'have_specs'=>0];
            if($id>0){
                $data = Db::connect($this->config)->name('goods_merchant')->where(['id'=>$id,'type'=>$type2])->find();
                $data['type_id'] = 0;
                $data['reason_id'] = 0;
                $data['warehouse_id'] = 0;
                if(isset($data['areas'])){
                    $data['areas'] = json_decode($data['areas'],true);
                    if(!empty($data['areas'])){
                        foreach($data['areas'] as $k=>$v){
                            $data['areas2'][$k] = Db::name('centralize_adminstrative_area')->where(['id'=>$v])->find();
                        }
                    }
                }else{
                    $data['areas'] = [];
                }


                $data['currency'] = Db::name('centralize_currency')->where(['id'=>$data['goods_currency']])->find();

                if($data['have_specs']==2){
                    #无规格
                    $data['nospecs'] = json_decode($data['nospecs'],true);
//                    $data['nospecs']['sku_prices'] = json_decode($data['nospecs']['sku_prices'],true);

                    $data['sku_info'] = Db::connect($this->config)->name('goods_sku_merchant')->where(['goods_id'=>$id])->find();
                    $data['sku_info']['sku_prices'] = json_decode($data['sku_info']['sku_prices'],true);
                }
                elseif($data['have_specs']==1){
                    #有规格
                    $data['havespecs'] = Db::connect($this->config)->name('goods_sku_merchant')->where(['goods_id'=>$id])->select();
                    foreach($data['havespecs'] as $k=>$v){
                        $data['havespecs'][$k]['spec_names'] = str_replace(' ','<br/>',$v['spec_names']);
                        $data['havespecs'][$k]['sku_prices'] = json_decode($v['sku_prices'],true);
                    }
                }
            }

            #商品
            $type['goods'] = Db::connect($this->config)->name('goods_merchant')->whereRaw('cid='.$company_id.' and type='.$type2.' and shipping_country is null')->select();

            #发货城市
            ##国家
            $type['country'] = Db::name('centralize_diycountry_content')->where(['pid'=>5])->field(['id','param2'])->select();

            #仓库
            $type['warehouse'] = Db::name('centralize_warehouse_list')->where(['uid'=>$company_id])->select();

            return view('index/shop_backend/save_inventory',compact('id','company_id','company_type','data','type','type2','is_shelf_link'));
        }
    }

    #获取国地信息
    public function get_country_info2(Request $request){
        $dat = input();
        $id = intval($dat['id']);

        #国家信息
        $country = Db::name('centralize_diycountry_content')->where(['id'=>$id])->find();

        #获取国家币种
        $data['currency'] = Db::name('centralize_currency')->where(['country_id'=>$id])->find();

        #获取邮政编码格式
        $data['rule'] = Db::name('centralize_diycountry_content')->where(['pid'=>4,'param1'=>$country['param5']])->find();

        #获取当前国家的行政区域
        $data['addr'] = Db::name('centralize_adminstrative_area')->where(['country_id'=>$id,'pid'=>0])->field('id,code_name')->select();

        return json(['code'=>0,'data'=>$data]);
    }

    #获取区域信息
    public function getarea(Request $request){
        $dat = input();
        $list = [];
        if($dat['type']==1){
            #获取国家下的一级区域
            $list = Db::name('centralize_adminstrative_area')->where(['country_id'=>$dat['val'],'pid'=>0])->select();
        }
        elseif($dat['type']==2){
            #获取国家下的二级区域
            $list = Db::name('centralize_adminstrative_area')->where(['pid'=>$dat['val']])->select();
        }
        elseif($dat['type']==3){
            #获取洲下的国家
            $list = Db::name('centralize_diycountry_content')->where(['state_id'=>$dat['val']])->field(['id','param2'])->select();
        }
        elseif($dat['type']==4){
            #获取国家下的邮编
            $regex = '[0-9]';
            $list = Db::query("SELECT * FROM ims_centralize_adminstrative_area WHERE country_id = ? AND code_name REGEXP ? AND code_name NOT REGEXP '[()]'", [
                $dat['val'],
                $regex
            ]);
        }

        return json(['code'=>0,'msg'=>'获取成功','data'=>$list]);
    }

    #管理库存
    public function inventory_manage(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);#0商城，1官网
        $type = isset($dat['type'])?intval($dat['type']):0;

        if(isset($dat['pa'])){
            $limit = $request->get('limit');
            $page = $request->get('page') - 1;
            if ($page != 0) {
                $page = $limit * $page;
            }
//            $keyword = isset($dat['keywords']) ? trim($dat['keywords']) : '';
            $where =['cid'=>$company_id,'type'=>$type];
            //->whereRaw('shipping_country!=""')
            $count = Db::connect($this->config)->name('goods_merchant')->where($where)->count();
            $rows = DB::connect($this->config)->name('goods_merchant')
                ->where($where)
//                ->whereRaw('shipping_country!=""')
                ->limit($page . ',' . $limit)
                ->order('id desc')
                ->select();

            foreach ($rows as &$item) {
//                if($item['type_id']==0){
//                    $item['type_name'] = '新增入库';
//                }elseif($item['type_id']==1){
//                    $item['type_name'] = '新增出库';
//                }
//
//                if($item['reason_id']==0){
//                    $item['reason_name'] = '入库拟售';
//                }elseif($item['reason_id']==1){
//                    $item['reason_name'] = '停售退库';
//                }
            }

            return json(['code'=>0,'count'=>$count,'data'=>$rows]);
        }else{

            return view('index/shop_backend/inventory_manage',compact('company_id','company_type','type'));
        }
    }

    #新增上架
    public function save_shelf(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);#0商城，1官网
        $id = isset($dat['id'])?intval($dat['id']):0;
        $page_type = isset($dat['page_type'])?intval($dat['page_type']):1;
        $type2 = isset($dat['type'])?intval($dat['type']):0;

        if($request->isAjax()){
//            dd($dat);
            Db::startTrans();
            try {
                $page_type = isset($dat['page_type'])?$dat['page_type']:0;
                if(empty($dat['goods_id'])){
                    return json(['code'=>-1,'msg'=>'请选择商品']);
                }
                #修改商品商户表
                $goods = Db::connect($this->config)->name('goods_merchant')->where(['id'=>$dat['goods_id'],'type'=>$type2])->find();
                #查找最低价钱的规格
                $low_goods_sku = Db::connect($this->config)->name('goods_sku_merchant')->where(['goods_id'=>$dat['goods_id']])->order('goods_price','asc')->limit(1)->find();
                Db::connect($this->config)->name('goods_merchant')->where(['id'=>$dat['goods_id']])->update([
                    'currency_type'=>$dat['currency_type'],
                    'goods_currency'=>$dat['currency_type']==1?$dat['other_currency']:$goods['goods_currency'],
                    'goods_price'=>$low_goods_sku['goods_price'],
                    'market_price'=>$low_goods_sku['market_price'],
                    'cost_price'=>$low_goods_sku['cost_price'],
                    'goods_number'=>$low_goods_sku['goods_number'],
                    'have_specs'=>$page_type==1?$dat['have_specs']:$goods['have_specs'],
                ]);
                $goods = Db::connect($this->config)->name('goods_merchant')->where(['id'=>$dat['goods_id']])->find();
    
                if($page_type==1){
                    #商品价格
                    if($dat['currency_type']==1){
                        #外币结算
                        if(empty($dat['other_currency'])){
                            return json(['code'=>-1,'msg'=>'请选择其他货币']);
                        }
                    }
    
                    #插入库存
                    $sku_id = 0;
                    $min_goods_price = 0;
                    if($dat['have_specs']==2){
                        #无规格
                        $goodsSkuInsert = [
                            'goods_id' => $dat['goods_id'],
                            'goods_sn' => trim($dat['nospecs']['goods_sn']),
                            'goods_barcode' => trim($dat['nospecs']['goods_barcode']),
                            'goods_stockcode' => trim($dat['nospecs']['goods_stockcode']),
                            'market_price' => trim($dat['nospecs']['market_price']),
                            'cost_price' => trim($dat['nospecs']['cost_price']),
                            'shelf_number' => trim($dat['nospecs']['shelf_number']),
                            'warn_type' => trim($dat['nospecs']['warn_type']),
                            'warn_number' => trim($dat['nospecs']['warn_number']),
                            'goods_price' => end($dat['nospecs']['price']),
                            'goods_number' => 0,
                            'is_spu' => 1, // 无规格商品 是SPU商品
                            'sku_prices' => json_encode([
                                'goods_number'=>0,
                                'start_num'=>$dat['nospecs']['start_num'],
                                'unit'=>[$dat['nospecs']['unit'][0]],
                                'select_end'=>$dat['nospecs']['select_end'],
                                'end_num'=>$dat['nospecs']['end_num'],
                                'currency'=>[$goods['goods_currency']],
                                'price'=>$dat['nospecs']['price'],
                            ],true)#该规格的区间价格
                        ];
    
                        #最低价
                        $min_goods_price = end($dat['nospecs']['price']);
    
                        if(isset($dat['is_edit'])){
                            $sku_id = $dat['sku_id'];
                            Db::connect($this->config)->name('goods_sku_merchant')->where(['goods_id'=>$dat['goods_id']])->update($goodsSkuInsert);
    
                            #同步平台商品表
                            if($goods['shelf_id']>0){
                                $goodsSkuInsert['goods_id'] = $goods['shelf_id'];
                                Db::connect($this->config)->name('goods_sku')->where(['goods_id'=>$goods['shelf_id']])->update($goodsSkuInsert);
                            }
                        }
                        else{
                            $sku_id = Db::connect($this->config)->name('goods_sku_merchant')->insertGetId($goodsSkuInsert);
                        }
                    }
                    elseif($dat['have_specs']==1){
                        #有规格
                        $spec_ids_arr = [];#大分类
                        $insert_specs_arr = [];#小分类
    
                        foreach($dat['havespecs']['option_name'] as $k=>$v){
                            $options = explode('<br/>',$v);
                            $specs_vids_arr = [];
                            foreach($options as $k2=>$v2){
                                $options_2 = explode(':',$v2);
    
                                #归口大分类
                                if(!in_array($options_2[0], $spec_ids_arr)) {
                                    array_push($spec_ids_arr, $options_2[0]);
                                }
                                if(isset($options_2[1])){
                                    #归口小分类
                                    if(!in_array($options_2[1], $specs_vids_arr)) {
                                        array_push($specs_vids_arr, $options_2[1]);
                                    }
                                }
                            }
                            array_push($insert_specs_arr, $specs_vids_arr);
                        }
    
                        $date = date('Y-m-d H:i:s',time());
                        $spec_ids = '';
                        foreach($spec_ids_arr as $k=>$v){
                            $have_attr = Db::connect($this->config)->name('attribute')->where(['attr_name'=>$v])->find();
                            if(!empty($have_attr)){
                                $spec_ids .= $have_attr['attr_id'].'|';
                            }
                            else{
                                $have_attr = Db::connect($this->config)->name('attribute')->insertGetId([
                                    'attr_name'=>trim($v),
                                    'created_at'=>$date,
                                    'updated_at'=>$date,
                                ]);
                                $spec_ids .= $have_attr.'|';
                            }
                        }
                        $spec_ids = rtrim($spec_ids,'|');
    //                    dd($spec_ids);
    
                        $insert_specs = [];
                        $spec_ids_arr2 = explode('|',$spec_ids);
                        foreach($insert_specs_arr as $k=>$v){
                            $spec_vids = '';
    
                            foreach($v as $k2=>$v2){
                                $os = explode('-@-',$v2);
    
                                if(count($os)>1){
                                    $have_value = Db::connect($this->config)->name('attr_value')->where(['attr_vname'=>trim($os[0])])->find();
                                    if(!empty($have_value)){
                                        $spec_vids .= $have_value['attr_vid'];
                                    }
                                    else{
                                        $have_value = Db::connect($this->config)->name('attr_value')->insertGetId([
                                            'attr_id'=>$spec_ids_arr2[$k2],
                                            'attr_vname'=>trim($os[0]),
                                            'created_at'=>$date,
                                            'updated_at'=>$date,
                                        ]);
                                        $spec_vids .= $have_value['attr_vid'];
                                    }
                                    $have_value2 = Db::connect($this->config)->name('attr_value')->where(['attr_vname'=>trim($os[1])])->find();
                                    if(!empty($have_value2)){
                                        $spec_vids .= '-'.$have_value2['attr_vid'].'|';
                                    }
                                    else{
                                        $have_value2 = Db::connect($this->config)->name('attr_value')->insertGetId([
                                            'attr_id'=>$spec_ids_arr2[$k2],
                                            'attr_vname'=>trim($os[1]),
                                            'created_at'=>$date,
                                            'updated_at'=>$date,
                                        ]);
    
                                        $spec_vids .= '-'.$have_value2.'|';
                                    }
                                }
                                else{
                                    $have_value = Db::connect($this->config)->name('attr_value')->where(['attr_vname'=>$v2])->find();
                                    if(!empty($have_value)){
                                        $spec_vids .= $have_value['attr_vid'].'|';
                                    }
                                    else{
                                        $have_value = Db::connect($this->config)->name('attr_value')->insertGetId([
                                            'attr_id'=>$spec_ids_arr2[$k2],
                                            'attr_vname'=>trim($v2),
                                            'created_at'=>$date,
                                            'updated_at'=>$date,
                                        ]);
                                        $spec_vids .= $have_value.'|';
                                    }
                                }
                            }
    
                            array_push($insert_specs,rtrim($spec_vids,'|'));
                        }
    
                        // if(!isset($dat['havespecs']['sku_id'])) {
                        //     #规格已重新刷新，删除之前的商品规格
                        //     Db::connect($this->config)->name('goods_sku_merchant')->where(['goods_id' => $dat['id']])->delete();
    
                        //     #同步平台商品表
                        //     if($goods['shelf_id']>0){
                        //         Db::connect($this->config)->name('goods_sku')->where(['goods_id' => $goods['shelf_id']])->delete();
                        //     }
                        // }
    
                        #最低价
                        $min_goods_price = end($dat['havespecs']['price'][0]);
                        
                        // dd($dat);
                        
                        #插入数据表
                        foreach($dat['havespecs']['option_name'] as $k=>$v){
                            if(isset($dat['havespecs']['sku_id'][$k])){
                                #以往规格调整
                                if($dat['havespecs']['sku_id'][$k] > 0){
                                    #修改当前规格
                                    $sku_id2 = $dat['havespecs']['sku_id'][$k];
                                    $old_sku = Db::connect($this->config)->name('goods_sku_merchant')->where(['sku_id'=>$dat['havespecs']['sku_id'][$k],'goods_id'=>$dat['goods_id']])->find();
                                    $old_sku['sku_prices'] = json_decode($old_sku['sku_prices'],true);
                                    
                                    $goodsSkuUpdate = [
                                        'goods_sn'=>$dat['havespecs']['goods_sn'][$k],
                                        'goods_barcode'=>$dat['havespecs']['goods_barcode'][$k],
                                        'goods_stockcode'=>$dat['havespecs']['goods_stockcode'][$k],
                                        'market_price'=>$dat['havespecs']['market_price'][$k],
                                        'cost_price'=>$dat['havespecs']['cost_price'][$k],
                                        'shelf_number'=>$dat['havespecs']['shelf_number'][$k],
                                        'warn_type'=>$dat['havespecs']['warn_type'][$k],
                                        'warn_number'=>$dat['havespecs']['warn_number'][$k],
                                        'goods_price' => end($dat['havespecs']['price'][$k]),
                                        'sku_prices' => json_encode([
                                            'goods_number'=>$old_sku['sku_prices']['goods_number'],
                                            'start_num'=>$dat['havespecs']['start_num'][$k],
                                            'unit'=>[$dat['havespecs']['unit'][$k][0]],
                                            'select_end'=>$dat['havespecs']['select_end'][$k],
                                            'end_num'=>$dat['havespecs']['end_num'][$k],
                                            'currency'=>[$goods['goods_currency']],
                                            'price'=>$dat['havespecs']['price'][$k],
                                        ],true)#该规格的区间价格
                                    ];
                                    
                                    Db::connect($this->config)->name('goods_sku_merchant')->where(['sku_id'=>$dat['havespecs']['sku_id'][$k],'goods_id'=>$dat['goods_id']])->update($goodsSkuUpdate);
                                    
                                    #同步平台商品表
                                    if($goods['shelf_id']>0) {
                                        Db::connect($this->config)->name('goods_sku')->where(['goods_id'=>$goods['shelf_id'],'spec_vids'=>$old_sku['spec_vids']])->update($goodsSkuUpdate);
                                    }
                                }
                            }
                            else{
                                #新增规格调整
                                $goodsSkuInsert = [
                                    'goods_id'=>$dat['goods_id'],
                                    'spec_ids'=>$spec_ids,
                                    'spec_vids'=>$insert_specs[$k],
                                    'sku_specs'=>str_replace("|","*",$insert_specs[$k]),
                                    'spec_names'=>str_replace('<br/>',' ',$v),
                                    'goods_sn'=>$dat['havespecs']['goods_sn'][$k],
                                    'goods_barcode'=>$dat['havespecs']['goods_barcode'][$k],
                                    'goods_stockcode'=>$dat['havespecs']['goods_stockcode'][$k],
                                    'market_price'=>$dat['havespecs']['market_price'][$k],
                                    'cost_price'=>$dat['havespecs']['cost_price'][$k],
                                    'shelf_number'=>$dat['havespecs']['shelf_number'][$k],
                                    'warn_type'=>$dat['havespecs']['warn_type'][$k],
                                    'warn_number'=>$dat['havespecs']['warn_number'][$k],
                                    'goods_price' => end($dat['havespecs']['price'][$k]),
                                    'goods_number' => 0,
                                    'is_spu' => 1, // 无规格商品 是SPU商品
                                    'sku_prices' => json_encode([
                                        'goods_number'=>0,
                                        'start_num'=>$dat['havespecs']['start_num'][$k],
                                        'unit'=>[$dat['havespecs']['unit'][$k][0]],
                                        'select_end'=>$dat['havespecs']['select_end'][$k],
                                        'end_num'=>$dat['havespecs']['end_num'][$k],
                                        'currency'=>[$goods['goods_currency']],
                                        'price'=>$dat['havespecs']['price'][$k],
                                    ],true)#该规格的区间价格
                                ];
        
                                #整理规格
                                $sku_id2 = 0;
                                if(isset($dat['is_edit'])){
                                    #修改
                                    if(!isset($dat['havespecs']['sku_id'])){
                                        #插入
                                        $sku_id2 = Db::connect($this->config)->name('goods_sku_merchant')->insertGetId($goodsSkuInsert);
        
                                        #同步平台商品表
                                        if($goods['shelf_id']>0){
                                            $goodsSkuInsert['goods_id'] = $goods['shelf_id'];
                                            Db::connect($this->config)->name('goods_sku')->insertGetId($goodsSkuInsert);
                                        }
                                    }
                                    else{
                                        if(isset($dat['havespecs']['sku_id'][$k])){
                                            #修改
                                            $sku_id2 = Db::connect($this->config)->name('goods_sku_merchant')->where(['sku_id'=>$dat['havespecs']['sku_id'][$k]])->update($goodsSkuInsert);
        
                                            #同步平台商品表
                                            if($goods['shelf_id']>0) {
                                                $goodsSkuInsert['goods_id'] = $goods['shelf_id'];
                                                $origin_sku = Db::connect($this->config)->name('goods_sku_merchant')->where(['sku_id'=>$dat['havespecs']['sku_id'][$k]])->find();
                                                Db::connect($this->config)->name('goods_sku')->where(['goods_id'=>$goods['shelf_id'],'spec_vids'=>$origin_sku['spec_vids']])->update($goodsSkuInsert);
                                            }
                                        }
                                        else{
                                            #新的规格插入表
                                            $sku_id2 = Db::connect($this->config)->name('goods_sku_merchant')->insertGetId($goodsSkuInsert);
                                            #同步平台商品表
                                            if($goods['shelf_id']>0) {
                                                $goodsSkuInsert['goods_id'] = $goods['shelf_id'];
                                                Db::connect($this->config)->name('goods_sku')->insertGetId($goodsSkuInsert);
                                            }
                                        }
                                    }
                                }
                                else{
                                    #插入
                                    $sku_id2 = Db::connect($this->config)->name('goods_sku_merchant')->insertGetId($goodsSkuInsert);
                                }
                            }
    
                            if($sku_id==0){
                                $sku_id = $sku_id2;
                            }
                        }
                    }
    
                    
                    #修改商品商户表
                    Db::connect($this->config)->name('goods_merchant')->where(['id'=>$dat['goods_id']])->update([
                        'sku_id'=>$sku_id,
                        'goods_price'=>$min_goods_price,
                        'nospecs'=>$dat['have_specs']==2?json_encode(['goods_number'=>$goods['goods_number'], 'start_num'=>$dat['nospecs']['start_num'], 'unit'=>[$dat['nospecs']['unit'][0]], 'select_end'=>$dat['nospecs']['select_end'], 'end_num'=>$dat['nospecs']['end_num'], 'currency'=>[$goods['goods_currency']], 'price'=>$dat['nospecs']['price']],true):''
                    ]);
    
                    #同步平台商品表
                    if($goods['shelf_id']>0){
                        Db::connect($this->config)->name('goods')->where(['goods_id'=>$goods['shelf_id']])->update([
                            'goods_currency'=>intval($dat['currency_type'])==1?$dat['other_currency']:$goods['goods_currency'],
                            'goods_price'=>$min_goods_price,
                            'have_specs'=>$dat['have_specs'],
                            'sku_id'=>$sku_id,
                            'nospecs'=>$dat['have_specs']==2?json_encode(['goods_number'=>$goods['goods_number'], 'start_num'=>$dat['nospecs']['start_num'], 'unit'=>[$dat['nospecs']['unit'][0]], 'select_end'=>$dat['nospecs']['select_end'], 'end_num'=>$dat['nospecs']['end_num'], 'currency'=>[$goods['goods_currency']], 'price'=>$dat['nospecs']['price']],true):''
                        ]);
                    }
                }
                elseif($page_type==2){
                    #物流支撑
                    #修改商品商户表
                    if($dat['service_type']==1){
                        #（发货国）国内配送
                        Db::connect($this->config)->name('goods_merchant')->where(['id'=>$dat['goods_id']])->update([
                            'service_type'=>$dat['service_type'],
                            'domestic_logistics'=>json_encode($dat['domestic_logistics'],true)
                        ]);
    
                        #同步平台商品表
                        if($goods['shelf_id']>0){
                            Db::connect($this->config)->name('goods')->where(['goods_id'=>$goods['shelf_id']])->update([
                                'service_type'=>$dat['service_type'],
                                'domestic_logistics'=>json_encode($dat['domestic_logistics'],true)
                            ]);
                        }
                    }
                    elseif($dat['service_type']==2){
                        #（发货国）跨境配送
                        if($dat['gather_method']==1){
                            #平台集运
                            Db::connect($this->config)->name('goods_merchant')->where(['id'=>$dat['goods_id']])->update([
                                'service_type'=>$dat['service_type'],
                                'gather_method'=>$dat['gather_method']
                            ]);
    
                            #同步平台商品表
                            if($goods['shelf_id']>0){
                                Db::connect($this->config)->name('goods')->where(['goods_id'=>$goods['shelf_id']])->update([
                                    'service_type'=>$dat['service_type'],
                                    'gather_method'=>$dat['gather_method']
                                ]);
                            }
                        }
                        elseif($dat['gather_method']==2){
                            #自主集运
                            if($dat['support_export']==1){
                                #支持集运到其他国家
                                Db::connect($this->config)->name('goods_merchant')->where(['id'=>$dat['goods_id']])->update([
                                    'service_type'=>$dat['service_type'],
                                    'gather_method'=>$dat['gather_method'],
                                    'support_export'=>$dat['support_export'],
                                    'gather_countrys'=>json_encode([
                                        'gather_zhou'=>$dat['gather_zhou'],
                                        'gather_country'=>$dat['gather_country'],
                                        'gather_postal'=>$dat['gather_postal'],
                                    ],true)
                                ]);
    
                                #同步平台商品表
                                if($goods['shelf_id']>0){
                                    Db::connect($this->config)->name('goods')->where(['goods_id'=>$goods['shelf_id']])->update([
                                        'service_type'=>$dat['service_type'],
                                        'gather_method'=>$dat['gather_method'],
                                        'support_export'=>$dat['support_export'],
                                        'gather_countrys'=>json_encode([
                                            'gather_zhou'=>$dat['gather_zhou'],
                                            'gather_country'=>$dat['gather_country'],
                                            'gather_postal'=>$dat['gather_postal'],
                                        ],true)
                                    ]);
                                }
                            }
                        }
    
                    }
                }
                elseif($page_type==3){
                    #其它费用
    
                    $goodsInfo['otherfees_content'] = json_encode(['fees_name'=>$dat['fees_name'],'fees_desc'=>$dat['fees_desc'],'fees_condition'=>$dat['fees_condition'],'fees_trigger'=>$dat['fees_trigger'],'fees_trigger2'=>$dat['fees_trigger2'],'fees_trigger2_equal'=>$dat['fees_trigger2_equal'],'fees_trigger2_num'=>$dat['fees_trigger2_num'],'fees_options'=>$dat['fees_options'],'fees_trigger2_area'=>$dat['fees_trigger2_area'],'fees_trigger2_area1'=>$dat['fees_trigger2_area1'],'other_fees_area'=>$dat['other_fees_area'],'fees_standard'=>$dat['fees_standard'],'fees_standard_currency'=>$dat['fees_standard_currency'],'fees_standard_price'=>$dat['fees_standard_price'],'fees_standard_unit'=>$dat['fees_standard_unit'],'fees_standard_ratio'=>$dat['fees_standard_ratio'],'fees_standard_ratio_price'=>$dat['fees_standard_ratio_price'],'fees_standard_ratio_ratio'=>$dat['fees_standard_ratio_ratio']],true);
    
                    Db::connect($this->config)->name('goods_merchant')->where(['id'=>$dat['goods_id']])->update([
                        'otherfees_content'=>$goodsInfo['otherfees_content'],
                    ]);
    
                    #同步平台商品表
                    if($goods['shelf_id']>0){
                        Db::connect($this->config)->name('goods')->where(['goods_id'=>$goods['shelf_id']])->update([
                            'otherfees_content'=>$goodsInfo['otherfees_content'],
                        ]);
                    }
                }
                elseif($page_type==4){
                    #价格说明
    
                    #优惠减免
                    $goodsInfo['reduction_content'] = json_encode(['preferential_blong'=>$dat['reduction']['preferential_blong'],'type'=>$dat['reduction']['type'],'strict'=>$dat['reduction']['strict'],'currency1'=>$goods['goods_currency'],'price1'=>$dat['reduction']['price1'],'currency2'=>$goods['goods_currency'],'price2'=>$dat['reduction']['price2']],true);
                    #优惠随赠
                    $goodsInfo['gift_content'] = json_encode(['preferential_blong'=>$dat['gift']['preferential_blong'],'type'=>$dat['gift']['type'],'operaer'=>$dat['gift']['operaer'],'points_type'=>$dat['gift']['points_type'],'points_currency'=>$goods['goods_currency'],'points_money'=>$dat['gift']['points_money'],'points_send'=>$dat['gift']['points_send'],'coupon_currency'=>$goods['goods_currency'],'coupon_money'=>$dat['gift']['coupon_money'],'coupon_num'=>$dat['gift']['coupon_num'],'accgift_type'=>$dat['gift']['accgift_type'],'accgift_content'=>$dat['gift']['accgift_content'],'accgift_num'=>$dat['gift']['accgift_num'],'strict'=>$dat['gift']['strict']],true);
                    #价格未含
                    $goodsInfo['noinclude_content'] = json_encode(['name'=>$dat['noinclude']['name'],'desc'=>$dat['noinclude']['desc'],'currency'=>$goods['goods_currency'],'price'=>$dat['noinclude']['price']],true);
                    #潜在收费
                    $goodsInfo['potential_content'] = json_encode(['currency'=>$dat['potential']['currency'],'name'=>$dat['potential']['name'],'desc'=>$dat['potential']['desc'],'currency2'=>$goods['goods_currency'],'price'=>$dat['potential']['price']],true);
                    Db::connect($this->config)->name('goods_merchant')->where(['id'=>$dat['goods_id']])->update([
                        'reduction_content'=>$goodsInfo['reduction_content'],
                        'gift_content'=>$goodsInfo['gift_content'],
                        'noinclude_content'=>$goodsInfo['noinclude_content'],
                        'potential_content'=>$goodsInfo['potential_content'],
                    ]);
    
                    #同步平台商品表
                    if($goods['shelf_id']>0) {
                        Db::connect($this->config)->name('goods')->where(['goods_id' => $goods['shelf_id']])->update([
                            'reduction_content'=>$goodsInfo['reduction_content'],
                            'gift_content'=>$goodsInfo['gift_content'],
                            'noinclude_content'=>$goodsInfo['noinclude_content'],
                            'potential_content'=>$goodsInfo['potential_content'],
                        ]);
                    }
                }
                elseif($page_type==5){
                    #商品促销
                    Db::connect($this->config)->name('goods_merchant')->where(['id'=>$dat['goods_id']])->update([
                        'activity_info'=>$dat['activity_id'],
                        'other_keywords'=>trim($dat['other_keywords']),
                    ]);
    
                    #同步平台商品表
                    if($goods['shelf_id']>0) {
                        Db::connect($this->config)->name('goods')->where(['goods_id' => $goods['shelf_id']])->update([
                            'activity_info'=>$dat['activity_id'],
                            'other_keywords'=>trim($dat['other_keywords']),
                        ]);
                    }
                }
                elseif($page_type==6){
                    #商品详情
    
                    #制造企业
                    $manufacture = '';
                    if(isset($dat['manufacture'])){
                        $insert_arr = [];
                        if(isset($dat['manufacture']['company_name'])){
                            if(!empty($dat['manufacture']['company_name'])){
                                $insert_arr['company_name'] = trim($dat['manufacture']['company_name']);
                            }
                        }
                        if(isset($dat['manufacture']['country'])){
                            if(!empty($dat['manufacture']['country'])){
                                $insert_arr['country'] = $dat['manufacture']['country'];
                                $insert_arr['address'] = trim($dat['manufacture']['address']);
                            }
                        }
                        if(isset($dat['manufacture']['area1'])){
                            if(!empty($dat['manufacture']['area1'])){
                                $insert_arr['area1'] = $dat['manufacture']['area1'];
                            }
                        }
                        if(isset($dat['manufacture']['area2'])){
                            if(!empty($dat['manufacture']['area2'])){
                                $insert_arr['area2'] = $dat['manufacture']['area2'];
                            }
                        }
                        if(isset($dat['manufacture']['area3'])){
                            if(!empty($dat['manufacture']['area3'])){
                                $insert_arr['area3'] = $dat['manufacture']['area3'];
                            }
                        }
                        if(isset($dat['manufacture']['area4'])){
                            if(!empty($dat['manufacture']['area4'])){
                                $insert_arr['area4'] = $dat['manufacture']['area4'];
                            }
                        }
                        if(isset($dat['manufacture']['area5'])){
                            if(!empty($dat['manufacture']['area5'])){
                                $insert_arr['area5'] = $dat['manufacture']['area5'];
                            }
                        }
                        if(isset($dat['manufacture']['area6'])){
                            if(!empty($dat['manufacture']['area6'])){
                                $insert_arr['area6'] = $dat['manufacture']['area6'];
                            }
                        }
                        if(isset($dat['manufacture']['connect_info'])){
                            if(!empty($dat['manufacture']['connect_info'])){
                                $insert_arr['connect_type'] = $dat['manufacture']['connect_type'];
                                $insert_arr['connect_info'] = trim($dat['manufacture']['connect_info']);
                            }
                        }
                        if(isset($dat['manufacture']['product_license'])){
                            if(!empty($dat['manufacture']['product_license'])){
                                $insert_arr['product_license'] = trim($dat['manufacture']['product_license']);
                            }
                        }
                        if(isset($dat['manufacture']['product_standard'])){
                            if(!empty($dat['manufacture']['product_standard'])){
                                $insert_arr['product_standard'] = trim($dat['manufacture']['product_standard']);
                            }
                        }
    
                        if(!empty($insert_arr)){
                            $manufacture = json_encode($insert_arr,true);
                        }
                    }
    
                    #销售企业
                    $sales = '';
                    if(isset($dat['sales'])){
                        $insert_arr = [];
                        if(isset($dat['sales']['company_name'])){
                            if(!empty($dat['sales']['company_name'])){
                                $insert_arr['company_name'] = trim($dat['sales']['company_name']);
                            }
                        }
                        if(isset($dat['sales']['country'])){
                            if(!empty($dat['sales']['country'])){
                                $insert_arr['country'] = $dat['sales']['country'];
                                $insert_arr['address'] = trim($dat['sales']['address']);
                            }
                        }
                        if(isset($dat['sales']['area1'])){
                            if(!empty($dat['sales']['area1'])){
                                $insert_arr['area1'] = $dat['sales']['area1'];
                            }
                        }
                        if(isset($dat['sales']['area2'])){
                            if(!empty($dat['sales']['area2'])){
                                $insert_arr['area2'] = $dat['sales']['area2'];
                            }
                        }
                        if(isset($dat['sales']['area3'])){
                            if(!empty($dat['sales']['area3'])){
                                $insert_arr['area3'] = $dat['sales']['area3'];
                            }
                        }
                        if(isset($dat['sales']['area4'])){
                            if(!empty($dat['sales']['area4'])){
                                $insert_arr['area4'] = $dat['sales']['area4'];
                            }
                        }
                        if(isset($dat['sales']['area5'])){
                            if(!empty($dat['sales']['area5'])){
                                $insert_arr['area5'] = $dat['sales']['area5'];
                            }
                        }
                        if(isset($dat['sales']['area6'])){
                            if(!empty($dat['sales']['area6'])){
                                $insert_arr['area6'] = $dat['sales']['area6'];
                            }
                        }
                        if(isset($dat['sales']['connect_info'])){
                            if(!empty($dat['sales']['connect_info'])){
                                $insert_arr['connect_type'] = $dat['sales']['connect_type'];
                                $insert_arr['connect_info'] = trim($dat['sales']['connect_info']);
                            }
                        }
                        if(isset($dat['sales']['product_license'])){
                            if(!empty($dat['sales']['product_license'])){
                                $insert_arr['product_license'] = trim($dat['sales']['product_license']);
                            }
                        }
    
                        if(!empty($insert_arr)){
                            $sales = json_encode($insert_arr,true);
                        }
                    }
    
                    #外贸企业
                    $foreign = '';
                    if(isset($dat['foreign'])){
                        $insert_arr = [];
                        if(isset($dat['foreign']['company_name'])){
                            if(!empty($dat['foreign']['company_name'])){
                                $insert_arr['company_name'] = trim($dat['foreign']['company_name']);
                            }
                        }
                        if(isset($dat['foreign']['country'])){
                            if(!empty($dat['foreign']['country'])){
                                $insert_arr['country'] = $dat['foreign']['country'];
                                $insert_arr['address'] = trim($dat['foreign']['address']);
                            }
                        }
                        if(isset($dat['foreign']['area1'])){
                            if(!empty($dat['foreign']['area1'])){
                                $insert_arr['area1'] = $dat['foreign']['area1'];
                            }
                        }
                        if(isset($dat['foreign']['area2'])){
                            if(!empty($dat['foreign']['area2'])){
                                $insert_arr['area2'] = $dat['foreign']['area2'];
                            }
                        }
                        if(isset($dat['foreign']['area3'])){
                            if(!empty($dat['foreign']['area3'])){
                                $insert_arr['area3'] = $dat['foreign']['area3'];
                            }
                        }
                        if(isset($dat['foreign']['area4'])){
                            if(!empty($dat['foreign']['area4'])){
                                $insert_arr['area4'] = $dat['foreign']['area4'];
                            }
                        }
                        if(isset($dat['foreign']['area5'])){
                            if(!empty($dat['foreign']['area5'])){
                                $insert_arr['area5'] = $dat['foreign']['area5'];
                            }
                        }
                        if(isset($dat['foreign']['area6'])){
                            if(!empty($dat['foreign']['area6'])){
                                $insert_arr['area6'] = $dat['foreign']['area6'];
                            }
                        }
                        if(isset($dat['foreign']['connect_info'])){
                            if(!empty($dat['foreign']['connect_info'])){
                                $insert_arr['connect_type'] = $dat['foreign']['connect_type'];
                                $insert_arr['connect_info'] = trim($dat['foreign']['connect_info']);
                            }
                        }
                        if(isset($dat['foreign']['product_license'])){
                            if(!empty($dat['foreign']['product_license'])){
                                $insert_arr['product_type'] = trim($dat['foreign']['product_type']);
                                $insert_arr['product_license'] = trim($dat['foreign']['product_license']);
                            }
                        }
    
                        if(!empty($insert_arr)){
                            $foreign = json_encode($insert_arr,true);
                        }
                    }
    
                    #有效期限
                    $effective = '';
                    if(isset($dat['effective'])){
                        $insert_arr = [];
                        if(isset($dat['effective']['type'])){
                            $insert_arr['type'] = $dat['effective']['type'];
                            $insert_arr['type2'] = $dat['effective']['type2'];
                            $insert_arr['fixed_day'] = trim($dat['effective']['fixed_day']);
                            $insert_arr['interval_day'] = trim($dat['effective']['interval_day']);
                        }
    
                        if(isset($dat['effective']['valid_period'])){
                            if(!empty($dat['effective']['valid_period'])){
                                $insert_arr['valid_period'] = trim($dat['effective']['valid_period']);
                                $insert_arr['valid_unit'] = $dat['effective']['valid_unit'];
                            }
                        }
    
                        if(!empty($insert_arr)){
                            $effective = json_encode($insert_arr,true);
                        }
                    }
    
                    #贮存条件
                    $store = '';
                    if(isset($dat['store'])){
                        $insert_arr = [];
                        if(isset($dat['store']['temperature_condition'])){
                            if(!empty($dat['store']['temperature_condition'])){
                                $insert_arr['temperature_condition'] = $dat['store']['temperature_condition'];
                            }
                        }
                        if(isset($dat['store']['humidity_condition'])){
                            if(!empty($dat['store']['humidity_condition'])){
                                $insert_arr['humidity_condition'] = $dat['store']['humidity_condition'];
                                $insert_arr['humidity_x'] = trim($dat['store']['humidity_x']);
                                $insert_arr['humidity_y'] = trim($dat['store']['humidity_y']);
                            }
                        }
                        if(isset($dat['store']['light_condition'])){
                            if(!empty($dat['store']['light_condition'])){
                                $insert_arr['light_condition'] = $dat['store']['light_condition'];
                            }
                        }
                        if(isset($dat['store']['packing_condition'])){
                            if(!empty($dat['store']['packing_condition'])){
                                $insert_arr['packing_condition'] = $dat['store']['packing_condition'];
                            }
                        }
                        if(isset($dat['store']['store_condition'])){
                            if(!empty($dat['store']['store_condition'])){
                                $insert_arr['store_condition'] = $dat['store']['store_condition'];
                            }
                        }
                        if(isset($dat['store']['special_condition'])){
                            if(!empty($dat['store']['special_condition'])){
                                $insert_arr['special_condition'] = $dat['store']['special_condition'];
                            }
                        }
    
                        if(!empty($insert_arr)){
                            $store = json_encode($insert_arr,true);
                        }
                    }
    
                    #产品包装
                    $packing = '';
                    if(isset($dat['packing'])){
                        $insert_arr = [];
                        if($dat['packing']['type']=='有包装'){
                            if(isset($dat['packing']['method'])){
                                if(!empty($dat['packing']['method'])){
                                    $insert_arr['type'] = $dat['packing']['type'];
                                    $insert_arr['no_pack'] = $dat['packing']['no_pack'];
                                    $insert_arr['method'] = $dat['packing']['method'];
                                    $insert_arr['packing_container'] = $dat['packing']['packing_container'];
                                    $insert_arr['packing_material'] = $dat['packing']['packing_material'];
                                }
                            }
                        }
                        elseif($dat['packing']['type']=='无包装'){
                            $insert_arr['type'] = $dat['packing']['type'];
                            $insert_arr['no_pack'] = $dat['packing']['no_pack'];
                            $insert_arr['method'] = $dat['packing']['method'];
                        }
    
    
                        if(!empty($insert_arr)){
                            $packing = json_encode($insert_arr,true);
                        }
                    }
    
                    #自定义参数
                    $spec_info = [];
                    if(!empty($dat['spec_name'][0])){
                        foreach($dat['spec_name'] as $k=>$v){
    
                            array_push($spec_info,['spec_name'=>trim($v),'spec_desc'=>trim($dat['spec_desc'][$k])]);
                        }
                        $spec_info = json_encode($spec_info,true);
                    }else{
                        $spec_info = '';
                    }
    
                    if(empty($manufacture) && empty($sales) && empty($foreign) && empty($effective) && empty($store) && empty($packing) && empty($spec_info) && !isset($dat['pc_desc'])){
                        return json(['code'=>0,'msg'=>'暂无修改','data'=>['goods_id'=>$dat['goods_id']]]);
                    }
    
                    Db::connect($this->config)->name('goods_merchant')->where(['id'=>$dat['goods_id']])->update([
                        'manufacture'=>$manufacture,
                        'sales'=>$sales,
                        'foreign'=>$foreign,
                        'effective'=>$effective,
                        'store'=>$store,
                        'packing'=>$packing,
                        'spec_info'=>$spec_info,
                        'pc_desc'=>isset($dat['pc_desc'])?json_encode($dat['pc_desc'],true):'',
                    ]);
    
                    #同步平台商品表
                    if($goods['shelf_id']>0) {
                        Db::connect($this->config)->name('goods')->where(['goods_id' => $goods['shelf_id']])->update([
                            'manufacture'=>$manufacture,
                            'sales'=>$sales,
                            'foreign'=>$foreign,
                            'effective'=>$effective,
                            'store'=>$store,
                            'packing'=>$packing,
                            'spec_info'=>$spec_info,
                            'pc_desc'=>isset($dat['pc_desc'])?json_encode($dat['pc_desc'],true):'',
                        ]);
                    }
                }
                elseif($page_type==7){
                    #卖家说明
                    Db::connect($this->config)->name('goods_merchant')->where(['id'=>$dat['goods_id']])->update([
                        'rule_id'=>$dat['rule_id'],
                    ]);
    
                    #同步平台商品表
                    if($goods['shelf_id']>0) {
                        Db::connect($this->config)->name('goods')->where(['goods_id' => $goods['shelf_id']])->update([
                            'rule_id'=>$dat['rule_id'],
                        ]);
                    }
                }
                
                Db::commit();
                return json(['code'=>0,'msg'=>'保存成功','data'=>['goods_id'=>$dat['goods_id']]]);
            } catch (\Exception $e) {
                Db::rollback();
                Log::error('保存失败: ' . $e->getMessage());
                return json(['code' => 0, 'msg' => '保存失败', 'error'=>$e->getMessage()]);
            }
        }
        else{
            $data = ['mode'=>1];
            if($id>0){
                $type['now_goods'] = Db::connect($this->config)->name('goods_merchant')->where(['id'=>$id,'type'=>$type2])->find();

                if($type['now_goods']['have_specs']==1){
                    #获取规格型号
                    $type['now_goods']['havespecs'] = Db::connect($this->config)->name('goods_sku_merchant')->where(['goods_id'=>$id])->select();
                    foreach($type['now_goods']['havespecs'] as $k=>$v){
//                        $type['now_goods']['havespecs'][$k]['spec_names'] = str_replace(' ','<br/>',$v['spec_names']);
                        $type['now_goods']['havespecs'][$k]['spec_names'] = $v['spec_names'];
                        $type['now_goods']['havespecs'][$k]['spec_names2'] = $v['spec_names'];
                        $type['now_goods']['havespecs'][$k]['sku_prices'] = json_decode($v['sku_prices'],true);
                    }
                }else{
                    $type['now_goods']['havespecs'] = [];
                }
                if(!empty($type['now_goods']['domestic_logistics'])){
                    $type['now_goods']['domestic_logistics'] = json_decode($type['now_goods']['domestic_logistics'],true);
                }
            }

            #商品
            $type['goods'] = Db::connect($this->config)->name('goods_merchant')->where(['cid'=>$company_id])->order('id desc')->select();

            #单位
            $type['unit'] = Db::name('unit')->select();

            #洲
            $type['zhou'] = Db::name('centralize_diycountry_content')->where(['pid'=>9])->field(['id','param1'])->select();

            //减免规则
            $type['reduction_rule'] = Db::connect($this->config)->name('ssl_reduction_rule')->select();
            foreach($type['reduction_rule'] as $k=>$v){
                $type['reduction_rule'][$k]['content'] = json_decode($v['content'],true);
            }

            //收款单位
            $type['currency'] = Db::name('centralize_currency')->select();

            //活动
            $type['activity'] = Db::connect($this->config)->name('ssl_activity')->select();
            $type['activity'] = json_encode($type['activity'],true);

            //卖家说明
            $type['rule'] = Db::connect($this->config)->name('description_rule')->where(['cid'=>$company_id])->select();

            //国家
            $type['country'] = Db::name('centralize_diycountry_content')->where(['pid'=>5])->field(['id','param2'])->select();

            //产品包装
            $type['packing'] = Db::name('packing_category')->where(['pid'=>0,'type'=>0])->select();

            return view('index/shop_backend/save_shelf',compact('id','company_id','company_type','data','type','page_type','type2'));
        }
    }

    #规则管理
    public function description_manage(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);

        if(isset($dat['pa'])){
            $limit = $request->get('limit');
            $page = $request->get('page') - 1;
            if ($page != 0) {
                $page = $limit * $page;
            }
            $where =['cid'=>$company_id];
            $keyword = $request->get('keywords') ? $request->get('keywords') : '';

            $count = Db::connect($this->config)->name('description_type')->where($where)->where('name', 'like', '%'.$keyword.'%')->count();
            $rows = DB::connect($this->config)->name('description_type')->where($where)
                ->where('name', 'like', '%'.$keyword.'%')
                ->limit($page . ',' . $limit)
                ->order('id desc')
                ->select();

            foreach ($rows as &$v) {

            }

            return json(['code'=>0,'count'=>$count,'data'=>$rows]);
        }else{
            return view('index/shop_backend/description_manage',compact('company_id','company_type'));
        }
    }

    #保存规则
    public function save_description(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);
        $id = isset($dat['id'])?intval($dat['id']):0;

        if($request->isAjax()){
            if($id>0){
                Db::connect($this->config)->name('description_type')->where(['id'=>$id])->update([
                    'name'=>trim($dat['name'])
                ]);
            }else{
                Db::connect($this->config)->name('description_type')->insert([
                    'cid'=>$company_id,
                    'name'=>trim($dat['name'])
                ]);
            }
            return json(['code'=>0,'msg'=>'保存成功']);
        }else{
            $data = ['name'=>''];
            if($id>0){
                $data = Db::connect($this->config)->name('description_type')->where(['id'=>$id])->find();
            }

            return view('index/shop_backend/save_description',compact('company_id','company_type','id','data'));
        }
    }

    #删除规则
    public function del_description(Request $request){
        $dat = input();
        $id = intval($dat['id']);

        $ishave = Db::name('description_keywords')->where(['type_id'=>$id])->find();
        if(!empty($ishave)){
            return json(['code'=>-1,'msg'=>'删除失败，下级数据还存在']);
        }else{
            $res = Db::name('description_type')->where(['id'=>$id])->delete();
            if($res){
                return json(['code'=>0,'msg'=>'删除成功']);
            }
        }
    }

    #账单信息
    public function billinfo(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);
        
        if($request->isAjax()){
            $billinfo = json_encode([
                'company_name'=>trim($dat['company_name']),
                'country_id'=>intval($dat['country_id']),
                'postal_code'=>trim($dat['postal_code']),
                'address'=>trim($dat['address']),
            ]);
            $res = Db::name('website_basic')->where(['company_id'=>$company_id,'company_type'=>$company_type])->update([
                'billinfo'=>$billinfo
            ]);
            
            return json(['code'=>0,'msg'=>'保存成功']);
        }else{
            $info = Db::name('website_basic')->where(['company_id'=>$company_id,'company_type'=>$company_type])->field('billinfo')->find();
            if(!empty($info['billinfo'])){
                $info['billinfo'] = json_decode($info['billinfo'],true);
            }else{
                $info['billinfo'] = ['company_name'=>'','country_id'=>162,'address'=>'','postal_code'=>''];
            }
            
            $country = Db::name('centralize_diycountry_content')->where(['pid'=>5])->field('id,param2')->select();
            
            return view('index/shop_backend/billinfo',compact('company_id','company_type','info','country'));
        }
    }

    #规则管理-二级分类管理
    public function keywords_manage(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);
        $type_id = intval($dat['type_id']);

        if(isset($dat['pa'])){
            $limit = $request->get('limit');
            $page = $request->get('page') - 1;
            if ($page != 0) {
                $page = $limit * $page;
            }
            $where =['cid'=>$company_id,'type_id'=>$type_id];
            $keyword = $request->get('keywords') ? $request->get('keywords') : '';

            $count = Db::connect($this->config)->name('description_keywords')->where($where)->where('name', 'like', '%'.$keyword.'%')->count();
            $rows = DB::connect($this->config)->name('description_keywords')->where($where)
                ->where('name', 'like', '%'.$keyword.'%')
                ->limit($page . ',' . $limit)
                ->order('id desc')
                ->select();

            foreach ($rows as &$v) {

            }

            return json(['code'=>0,'count'=>$count,'data'=>$rows]);
        }else{
            return view('index/shop_backend/keywords_manage',compact('company_id','company_type','type_id'));
        }
    }

    #规则管理-保存二级分类
    public function save_keywords2(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);
        $type_id = intval($dat['type_id']);
        $id = isset($dat['id'])?intval($dat['id']):0;

        if($request->isAjax()){
            if($id>0){
                Db::connect($this->config)->name('description_keywords')->where(['id'=>$id])->update([
                    'type_id'=>intval($dat['type_id']),
                    'name'=>trim($dat['name'])
                ]);
            }else{
                Db::connect($this->config)->name('description_keywords')->insert([
                    'cid'=>$company_id,
                    'type_id'=>intval($dat['type_id']),
                    'name'=>trim($dat['name'])
                ]);
            }
            return json(['code'=>0,'msg'=>'保存成功']);
        }else{
            $data = ['name'=>''];
            if($id>0){
                $data = Db::connect($this->config)->name('description_keywords')->where(['id'=>$id])->find();
            }

            $type = Db::connect($this->config)->name('description_type')->where(['cid'=>$company_id])->select();

            return view('index/shop_backend/save_keywords',compact('company_id','company_type','id','data','type_id','type'));
        }
    }

    #规则管理-删除二级分类
    public function del_keywords(Request $request){
        $dat = input();
        $id = intval($dat['id']);

        $ishave = Db::name('description_rule')->where(['key_id'=>$id])->find();
        if(!empty($ishave)){
            return json(['code'=>-1,'msg'=>'删除失败，下级数据还存在']);
        }else{
            $res = Db::name('description_keywords')->where(['id'=>$id])->delete();
            if($res){
                return json(['code'=>0,'msg'=>'删除成功']);
            }
        }
    }

    #规则管理-规则管理
    public function rule_manage(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);
        $type_id = intval($dat['type_id']);
        $key_id = intval($dat['key_id']);

        if(isset($dat['pa'])){
            $limit = $request->get('limit');
            $page = $request->get('page') - 1;
            if ($page != 0) {
                $page = $limit * $page;
            }
            $where =['cid'=>$company_id,'type_id'=>$type_id,'key_id'=>$key_id];
            $keyword = $request->get('keywords') ? $request->get('keywords') : '';

            $count = Db::connect($this->config)->name('description_rule')->where($where)->where('rule_name', 'like', '%'.$keyword.'%')->count();
            $rows = DB::connect($this->config)->name('description_rule')->where($where)
                ->where('rule_name', 'like', '%'.$keyword.'%')
                ->limit($page . ',' . $limit)
                ->order('id desc')
                ->select();

            foreach ($rows as &$v) {
                $v['createtime'] = date('Y/m/d',$v['createtime']);
            }

            return json(['code'=>0,'count'=>$count,'data'=>$rows]);
        }else{
            return view('index/shop_backend/rule_manage',compact('company_id','company_type','type_id','key_id'));
        }
    }

    #规则管理-保存规则
    public function save_rule(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);
        $key_id = intval($dat['key_id']);
        $id = isset($dat['id'])?intval($dat['id']):0;

        if($request->isAjax()){
            if(isset($dat['editorValue'])){
                return json(['code'=>-1,'msg'=>'提交过快，富文本未保存']);
            }
            $time = time();
            $keyword = $key_id = $type_id = '';
            if($dat['method']==1){
                $key_id=$dat['key_id1'];
                $type_id=$dat['type_id1'];
                $keyword = Db::connect($this->config)->name('description_keywords')->where(['id'=>$key_id])->find()['name'];
            }elseif($dat['method']==2){
                if(empty($dat['add_type_name'])){
                    $type_id = $dat['type_id1'];
                }else{
                    #1、插入类别
                    $type_id = Db::connect($this->config)->name('description_type')->insertGetId(['cid'=>$company_id,'name'=>trim($dat['add_type_name'])]);
                }
                #2、插入关键字
                $keyword = trim($dat['add_keyword']);
                $key_id = Db::connect($this->config)->name('description_keywords')->insertGetId(['cid'=>$company_id,'type_id'=>$type_id,'name'=>$keyword]);
            }
            #3、根据规则文本类型判断
            $content = [];
            if($dat['type']==1){
                foreach($dat['parag_num'] as $k=>$v){
                    if(isset($dat['parag_num'][$k])) {
                        #标题判断
                        if(!empty($dat['title'][$k])){
                            $dat['is_title'][$k]=1;
                        }else{
                            $dat['is_title'][$k]=-1;
                        }
                        array_push($content,[
                            'parag_num'=>trim($v),
                            'pnum'=>$dat['pnum'][$k],
                            'is_title'=>$dat['is_title'][$k],
                            'title'=>trim($dat['title'][$k]),
                            'content'=>trim($dat['content'][$k]),
                        ]);
                    }
                }
                $content = json_encode($content,true);
            }elseif ($dat['type']==2){
                $content = json_encode($dat['content2'],true);
            }
            if($id>0){
                #检测有无修改
                if($dat['is_preamble']==0){
                    $is_no_update = Db::connect($this->config)->name('description_rule')->where(['id'=>$id,'content'=>$content,'rule_name'=>trim($dat['rule_name'])])->find();
                }elseif($dat['is_preamble']==1){
                    $is_no_update = Db::connect($this->config)->name('description_rule')->where(['id'=>$id,'content'=>$content,'preamble_con'=>json_encode($dat['preamble_con'],true),'rule_name'=>trim($dat['rule_name'])])->find();
                }
                if(!empty($is_no_update)){
                    return json(['code'=>-1,'msg'=>'生成新版本失败，该版本内容无任何修改']);
                }
            }
            #4、版本序号
            $start = strtotime(date('Y-m-d',$time).' 00:00:00');
            $end = strtotime(date('Y-m-d',$time).' 23:59:59');
            $rule_num = Db::connect($this->config)->name('description_rule')->where(['type_id'=>$type_id,'key_id'=>$key_id])->whereBetween('createtime',[$start,$end],'AND')->count();
            if(empty($rule_num)){
                $serial_number = str_pad(1,2,'0',STR_PAD_LEFT);
            }else{
                $serial_number = str_pad(intval($rule_num)+1,2,'0',STR_PAD_LEFT);
            }
            #5、插入数据表
            Db::connect($this->config)->name('description_rule')->insert([
                'cid'=>$company_id,
                'type_id'=>$type_id,
                'key_id'=>$key_id,
                'pid'=>$id,
                'rule_name'=>trim($dat['rule_name']),
                'is_preamble'=>$dat['is_preamble'],
                'position_display'=>$dat['is_preamble']==1?$dat['position_display']:0,
                'preamble_con'=>$dat['is_preamble']==1?json_encode($dat['preamble_con'],true):'',
                'type'=>$dat['type'],
                'title'=>$dat['type']==1?json_encode($dat['title'],true):'',
                'content'=>$content,
                'version'=>'G/R/'.$keyword.'/'.date('Ymd',$time).'/'.$serial_number,
                'createtime'=>$time,
                'effecttime'=>$dat['effecttime']
            ]);
            return json(['code'=>0,'msg'=>'保存成功']);
        }else{
            $data = ['id'=>0,'type_id'=>0,'key_id'=>$key_id,'type'=>1,'title'=>[],'content'=>['parag_num'=>'','is_title'=>'1','title'=>'','content'=>'','pnum'=>''],'effecttime'=>date('Y/m/d'),'is_preamble'=>0,'position_display'=>1,'preamble_con'=>'','rule_name'=>''];
            if($key_id>0){
                $data['type_id'] = Db::connect($this->config)->name('description_keywords')->where(['id'=>$key_id])->find()['type_id'];
            }
            #类别表
            $type = Db::connect($this->config)->name('description_type')->where(['cid'=>$company_id])->select();
            #关键字表
            $keywords = Db::connect($this->config)->name('description_keywords')->where(['cid'=>$company_id])->select();
            if($id>0){
                $data = Db::connect($this->config)->name('description_rule')->where(['cid'=>$company_id,'id'=>$id])->find();
                $data['content'] = json_decode($data['content'],true);
                if($data['is_preamble']==1){
                    $data['preamble_con'] = json_decode($data['preamble_con'],true);
                }
                if($data['type']==1){
                    $num = 0;
                    foreach($data['content'] as $k=>$v){
                        $big_parag_num2 = explode('.',$v['parag_num']);
                        if(count($big_parag_num2)==2){
                            $num+=1;
                        }
                    }
                    $data['big_parag_num'] = $num;
                }
                #关键字表
                $keywords = Db::connect($this->config)->name('description_keywords')->where(['type_id'=>$data['type_id']])->select();
            }

            return view('index/shop_backend/save_rule',compact('company_id','company_type','id','data','key_id','type','keywords'));
        }
    }

    #卖家说明-获取关键字
    public function get_keywords(Request $request){
        $dat = input();
        $shop_id = intval($dat['company_id']);
        $list = Db::connect($this->config)->name('description_keywords')->where(['type_id'=>$dat['type_id'],'cid'=>$shop_id])->select();

        return json(['code'=>0,'data'=>$list]);
    }

    #规则管理-删除规则
    public function del_rule(Request $request){
        $dat = input();
        $id = intval($dat['id']);

        $res = Db::name('description_rule')->where(['id'=>$id])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功']);
        }
    }

    #获取通用信息
    public function get_name(Request $request){
        $dat = input();
        $val = trim($dat['val']);
        $type = intval($dat['type']);

        $list = [];
        if($val != ''){
            if($type==3){
                #商品型号分类（大类）
                $list = Db::connect($this->config)->name('attribute')->whereRaw('attr_name like "%'.$val.'%"')->field(['attr_id','attr_name'])->limit(100)->select();
            }
            elseif($type==4){
                #商品型号属性（二级、三级）
                $list = Db::connect($this->config)->name('attr_value')->whereRaw('attr_vname like "%'.$val.'%"')->field(['attr_vid','attr_vname'])->limit(100)->select();
            }
            elseif($type==5){
                #获取当前国家下的区域
                $list = Db::name('centralize_adminstrative_area')->where(['country_id'=>$val,'pid'=>0])->select();
            }
            elseif($type==6){
                #获取当前区域下的区域
                $list = Db::name('centralize_adminstrative_area')->where(['pid'=>$val])->select();
            }
            elseif($type==7){
                #获取包装方式下的容器和材料
                $list['container'] = Db::name('packing_category')->where(['pid'=>$val,'type'=>1])->select();
                $list['material'] = Db::name('packing_category')->where(['pid'=>$val,'type'=>2])->select();
            }

            if(!empty($list)){
                return json(['code'=>0,'list'=>$list]);
            }else{
                return json(['code'=>-1,'list'=>$list]);
            }
        }else{
            return json(['code'=>-1,'list'=>$list]);
        }
    }

    #获取商品信息
    public function get_goods_info(Request $request){
        $dat = input();
        $id = intval($dat['id']);

        $list['goods'] = Db::connect($this->config)->name('goods_merchant')->where(['id'=>$id])->find();
        $list['goods']['currency_info'] = Db::name('centralize_currency')->where(['id'=>$list['goods']['goods_currency']])->field(['currency_symbol_standard'])->find()['currency_symbol_standard'];
        $list['country_name'] = Db::name('centralize_diycountry_content')->where(['id'=>$list['goods']['shipping_country']])->field(['param2'])->find()['param2'];
        #商品型号======
        if($list['goods']['have_specs']==2){
            #无规格
            $list['goods']['nospecs'] = json_decode($list['goods']['nospecs'],true);
            $list['goods']['nospecs']['sku_info'] = Db::connect($this->config)->name('goods_sku_merchant')->where(['goods_id'=>$id])->find();
//            $list['goods']['nospecs']['sku_prices'] = json_decode($list['goods']['nospecs']['sku_prices'],true);
        }
        elseif($list['goods']['have_specs']==1){
            #有规格
            $list['goods']['havespecs'] = Db::connect($this->config)->name('goods_sku_merchant')->where(['goods_id'=>$id])->select();
            foreach($list['goods']['havespecs'] as $k=>$v){
//                $list['goods']['havespecs'][$k]['spec_names'] = str_replace(' ','<br/>',$v['spec_names']);
                $list['goods']['havespecs'][$k]['spec_names'] =$v['spec_names'];
                $list['goods']['havespecs'][$k]['spec_names2'] = $v['spec_names'];
                $list['goods']['havespecs'][$k]['sku_prices'] = json_decode($v['sku_prices'],true);
            }
        }

        #物流支撑======
        if($list['goods']['service_type']==1){
            #国内配送
            $list['goods']['domestic_logistics'] = json_decode($list['goods']['domestic_logistics'],true);
            $list['goods']['domestic_logistics']['areas'] = [];
            foreach($list['goods']['domestic_logistics']['area1'] as $k=>$v){
                $area1 = Db::name('centralize_adminstrative_area')->where(['id'=>$v])->find();
                $area2 = [];$area3 = [];$area4 = [];$area5 = [];$area6 = [];
                if(isset($list['goods']['domestic_logistics']['area2'][$k])){
                    $area2 = Db::name('centralize_adminstrative_area')->where(['id'=>$list['goods']['domestic_logistics']['area2'][$k]])->find();
                }
                if(isset($list['goods']['domestic_logistics']['area3'][$k])){
                    $area3 = Db::name('centralize_adminstrative_area')->where(['id'=>$list['goods']['domestic_logistics']['area3'][$k]])->find();
                }
                if(isset($list['goods']['domestic_logistics']['area4'][$k])){
                    $area4 = Db::name('centralize_adminstrative_area')->where(['id'=>$list['goods']['domestic_logistics']['area4'][$k]])->find();
                }
                if(isset($list['goods']['domestic_logistics']['area5'][$k])){
                    $area5 = Db::name('centralize_adminstrative_area')->where(['id'=>$list['goods']['domestic_logistics']['area5'][$k]])->find();
                }
                if(isset($list['goods']['domestic_logistics']['area6'][$k])){
                    $area6 = Db::name('centralize_adminstrative_area')->where(['id'=>$list['goods']['domestic_logistics']['area6'][$k]])->find();
                }
                array_push($list['goods']['domestic_logistics']['areas'],['area1'=>$area1,'area2'=>$area2,'area3'=>$area3,'area4'=>$area4,'area5'=>$area5,'area6'=>$area6]);
            }
        }
        elseif($list['goods']['service_type']==2){
            #跨境集运
            if($list['goods']['gather_method']==2){
                #自主集运
                if($list['goods']['support_export']==1){
                    #支持集运到其他国家
                    $list['goods']['gather_countrys'] = json_decode($list['goods']['gather_countrys'],true);
                    $list['goods']['gather_countrys']['areas'] = [];
                    foreach($list['goods']['gather_countrys']['gather_zhou'] as $k=>$v){
                        $area1 = Db::name('centralize_diycountry_content')->where(['id'=>$v])->find();
                        $area2 = [];$area3 = [];
                        if(isset($list['goods']['gather_countrys']['gather_country'][$k])){
                            $area2 = Db::name('centralize_diycountry_content')->where(['id'=>$list['goods']['gather_countrys']['gather_country'][$k]])->find();
                        }
                        if(isset($list['goods']['gather_countrys']['gather_postal'][$k])){
                            $area3 = Db::name('centralize_adminstrative_area')->whereRaw('id in ('.$list['goods']['gather_countrys']['gather_postal'][$k].')')->select();
                        }
                        array_push($list['goods']['gather_countrys']['areas'],['area1'=>$area1,'area2'=>$area2,'area3'=>$area3]);
                    }
                }
            }
        }

        #其它费用=====
        if(!empty($list['goods']['otherfees_content'])){
            $list['goods']['otherfees_content'] = json_decode($list['goods']['otherfees_content'],true);
//            #查找商品下所有型号
//            if($list['goods']['have_specs']==1){
//                #有规格型号
//                $list['goods']['all_sku'] = Db::connect($this->config)->name('goods_sku_merchant')->where(['goods_id'=>$list['goods']['id']])->select();
//                $list['goods']['all_sku'] = json_encode($list['goods']['all_sku'],true);
//            }else{
//                #无规格型号
//                $list['goods']['all_sku'] = [];
//            }

//            $dat['fees_trigger2_area1'],$dat['other_fees_area']
            $list['goods']['otherfees_content']['areas'] = [];

            foreach($list['goods']['otherfees_content']['other_fees_area']['area1'] as $k=>$v){
                $area1 = [];
                if(!empty($v) && $v!='-1'){
                    $area1 = Db::name('centralize_adminstrative_area')->where(['id'=>$v])->find();
                }
                $area2 = [];$area3 = [];$area4 = [];$area5 = [];$area6 = [];
                if(isset($list['goods']['otherfees_content']['other_fees_area']['area2'][$k])){
                    $area2 = Db::name('centralize_adminstrative_area')->where(['id'=>$list['goods']['otherfees_content']['other_fees_area']['area2'][$k]])->find();
                }
                if(isset($list['goods']['otherfees_content']['other_fees_area']['area3'][$k])){
                    $area3 = Db::name('centralize_adminstrative_area')->where(['id'=>$list['goods']['otherfees_content']['other_fees_area']['area3'][$k]])->find();
                }
                if(isset($list['goods']['otherfees_content']['other_fees_area']['area4'][$k])){
                    $area4 = Db::name('centralize_adminstrative_area')->where(['id'=>$list['goods']['otherfees_content']['other_fees_area']['area4'][$k]])->find();
                }
                if(isset($list['goods']['otherfees_content']['other_fees_area']['area5'][$k])){
                    $area5 = Db::name('centralize_adminstrative_area')->where(['id'=>$list['goods']['otherfees_content']['other_fees_area']['area5'][$k]])->find();
                }
                if(isset($list['goods']['otherfees_content']['other_fees_area']['area6'][$k])){
                    $area6 = Db::name('centralize_adminstrative_area')->where(['id'=>$list['goods']['otherfees_content']['other_fees_area']['area6'][$k]])->find();
                }
                array_push($list['goods']['otherfees_content']['areas'],['area1'=>$area1,'area2'=>$area2,'area3'=>$area3,'area4'=>$area4,'area5'=>$area5,'area6'=>$area6,'detail_area'=>$list['goods']['otherfees_content']['other_fees_area']['detail_area'][$k]]);
            }
        }

        #价格说明======
        if(!empty($list['goods']['reduction_content'])){
            $list['goods']['reduction_content'] = json_decode($list['goods']['reduction_content'],true);
        }
        if(!empty($list['goods']['gift_content'])) {
            $list['goods']['gift_content'] = json_decode($list['goods']['gift_content'], true);
        }
        if(!empty($list['goods']['noinclude_content'])) {
            $list['goods']['noinclude_content'] = json_decode($list['goods']['noinclude_content'], true);
        }
        if(!empty($list['goods']['potential_content'])) {
            $list['goods']['potential_content'] = json_decode($list['goods']['potential_content'], true);
        }

        if(!empty($list['goods']['activity_info'])){
            #商品促销======
            $list['goods']['activity_info'] = explode(",",$list['goods']['activity_info']);
        }

        #商品详情======
        ##制造企业
        if(!empty($list['goods']['manufacture'])){
            $list['goods']['manufacture'] = json_decode($list['goods']['manufacture'],true);
            if(isset($list['goods']['manufacture']['area1'])){
                $list['goods']['manufacture']['area1_name'] = Db::name('centralize_adminstrative_area')->where(['id'=>$list['goods']['manufacture']['area1']])->find()['code_name'];
            }
            if(isset($list['goods']['manufacture']['area2'])){
                $list['goods']['manufacture']['area2_name'] = Db::name('centralize_adminstrative_area')->where(['id'=>$list['goods']['manufacture']['area2']])->find()['code_name'];
            }
            if(isset($list['goods']['manufacture']['area3'])){
                $list['goods']['manufacture']['area3_name'] = Db::name('centralize_adminstrative_area')->where(['id'=>$list['goods']['manufacture']['area3']])->find()['code_name'];
            }
            if(isset($list['goods']['manufacture']['area4'])){
                $list['goods']['manufacture']['area4_name'] = Db::name('centralize_adminstrative_area')->where(['id'=>$list['goods']['manufacture']['area4']])->find()['code_name'];
            }
            if(isset($list['goods']['manufacture']['area5'])){
                $list['goods']['manufacture']['area5_name'] = Db::name('centralize_adminstrative_area')->where(['id'=>$list['goods']['manufacture']['area5']])->find()['code_name'];
            }
            if(isset($list['goods']['manufacture']['area6'])){
                $list['goods']['manufacture']['area6_name'] = Db::name('centralize_adminstrative_area')->where(['id'=>$list['goods']['manufacture']['area6']])->find()['code_name'];
            }
        }
        ##销售企业
        if(!empty($list['goods']['sales'])){
            $list['goods']['sales'] = json_decode($list['goods']['sales'],true);
            if(isset($list['goods']['sales']['area1'])){
                $list['goods']['sales']['area1_name'] = Db::name('centralize_adminstrative_area')->where(['id'=>$list['goods']['sales']['area1']])->find()['code_name'];
            }
            if(isset($list['goods']['sales']['area2'])){
                $list['goods']['sales']['area2_name'] = Db::name('centralize_adminstrative_area')->where(['id'=>$list['goods']['sales']['area2']])->find()['code_name'];
            }
            if(isset($list['goods']['sales']['area3'])){
                $list['goods']['sales']['area3_name'] = Db::name('centralize_adminstrative_area')->where(['id'=>$list['goods']['sales']['area3']])->find()['code_name'];
            }
            if(isset($list['goods']['sales']['area4'])){
                $list['goods']['sales']['area4_name'] = Db::name('centralize_adminstrative_area')->where(['id'=>$list['goods']['sales']['area4']])->find()['code_name'];
            }
            if(isset($list['goods']['sales']['area5'])){
                $list['goods']['sales']['area5_name'] = Db::name('centralize_adminstrative_area')->where(['id'=>$list['goods']['sales']['area5']])->find()['code_name'];
            }
            if(isset($list['goods']['sales']['area6'])){
                $list['goods']['sales']['area6_name'] = Db::name('centralize_adminstrative_area')->where(['id'=>$list['goods']['sales']['area6']])->find()['code_name'];
            }
        }
        ##外贸企业
        if(!empty($list['goods']['foreign'])){
            $list['goods']['foreign'] = json_decode($list['goods']['foreign'],true);
            if(isset($list['goods']['foreign']['area1'])){
                $list['goods']['foreign']['area1_name'] = Db::name('centralize_adminstrative_area')->where(['id'=>$list['goods']['foreign']['area1']])->find()['code_name'];
            }
            if(isset($list['goods']['foreign']['area2'])){
                $list['goods']['foreign']['area2_name'] = Db::name('centralize_adminstrative_area')->where(['id'=>$list['goods']['foreign']['area2']])->find()['code_name'];
            }
            if(isset($list['goods']['foreign']['area3'])){
                $list['goods']['foreign']['area3_name'] = Db::name('centralize_adminstrative_area')->where(['id'=>$list['goods']['foreign']['area3']])->find()['code_name'];
            }
            if(isset($list['goods']['foreign']['area4'])){
                $list['goods']['foreign']['area4_name'] = Db::name('centralize_adminstrative_area')->where(['id'=>$list['goods']['foreign']['area4']])->find()['code_name'];
            }
            if(isset($list['goods']['foreign']['area5'])){
                $list['goods']['foreign']['area5_name'] = Db::name('centralize_adminstrative_area')->where(['id'=>$list['goods']['foreign']['area5']])->find()['code_name'];
            }
            if(isset($list['goods']['foreign']['area6'])){
                $list['goods']['foreign']['area6_name'] = Db::name('centralize_adminstrative_area')->where(['id'=>$list['goods']['foreign']['area6']])->find()['code_name'];
            }
        }
        ##有效期限
        if(!empty($list['goods']['effective'])){
            $list['goods']['effective'] = json_decode($list['goods']['effective'],true);
        }
        ##贮存条件
        if(!empty($list['goods']['store'])){
            $list['goods']['store'] = json_decode($list['goods']['store'],true);
        }
        ##产品包装
        if(!empty($list['goods']['packing'])){
            $list['goods']['packing'] = json_decode($list['goods']['packing'],true);
            $list['container'] = Db::name('packing_category')->where(['pid'=>$list['goods']['packing']['method'],'type'=>1])->select();
            $list['material'] = Db::name('packing_category')->where(['pid'=>$list['goods']['packing']['method'],'type'=>2])->select();
        }
        ##商品参数
        if(!empty($list['goods']['spec_info'])){
            $list['goods']['spec_info'] = json_decode($list['goods']['spec_info'],true);
        }
        ##商品描述
        $list['goods']['pc_desc'] = json_decode($list['goods']['pc_desc'],true);


        #外币信息
        $list['currency'] = Db::name('centralize_currency')->whereRaw('id != '.$list['goods']['goods_currency'])->field(['id','currency_symbol_standard'])->select();

        #获取当前国家的行政区域
        $list['addr'] = Db::name('centralize_adminstrative_area')->where(['country_id'=>$list['goods']['shipping_country'],'pid'=>0])->field('id,code_name')->select();

        return json(['code'=>0,'data'=>$list]);
    }

    #获取最近的商品参数信息
    public function get_goods_param(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);

        //获取上一条参数数据
        $info = Db::connect($this->config)->name('goods_merchant')->where(['cid'=>$company_id])->order('id desc')->field('id,spec_info')->limit(1,1)->select();
        if(empty($info)){
            return json(['code'=>-1,'msg'=>'最新无新增商品参数']);
        }else{
            $infos = json_decode($info[0]['spec_info'],true);
            return json(['code'=>0,'msg'=>'获取成功','datas'=>$infos]);
        }
    }

    #整理规格
    public function spec_arrange(Request $request){
        $dat = input();

        $option1_arr = [];#大分类
        $option2_arr = [];#二分类
        $option3_arr = [];#三分类
        if(isset($dat['txt_arr'])){
            foreach($dat['txt_arr'] as $k=>$v){
                $options = explode('<br>',$v);

                $specs_vids_arr = [];
                foreach($options as $k2=>$v2){
                    $options_1 = explode(':',$v2);

                    #归口大分类
                    if(!in_array($options_1[0], $option1_arr)) {
                        array_push($option1_arr, $options_1[0]);
                    }

                    $options_2 = explode("-@-",$options_1[1]);

                    if(count($options_2)>1){
                        #归口三级分类
                        if(!in_array($options_2[1], $option3_arr)) {
                            array_push($option3_arr, $options_1[0].':'.$options_2[0].'-@-'.$options_2[1]);
                        }
                    }
                    else{
                        #归口二级分类
                        if(!in_array($options_2[0], $option2_arr)) {
                            array_push($option2_arr, $options_1[0].':'.$options_2[0]);
                        }
                    }
                }
            }
        }


        return json(['option1_arr'=>array_unique($option1_arr),'option2_arr'=>array_values(array_unique($option2_arr)),'option3_arr'=>array_values(array_unique($option3_arr))]);
    }

    #上架管理
    public function shelf_manage(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);#0商城，1官网
        $type = isset($dat['type'])?intval($dat['type']):0;

        if(isset($dat['pa'])){
            $limit = $request->get('limit');
            $page = $request->get('page') - 1;
            if ($page != 0) {
                $page = $limit * $page;
            }
            $keyword = isset($dat['keywords']) ? trim($dat['keywords']) : '';
            $where =['cid'=>$company_id,'type'=>$type];
            $count = Db::connect($this->config)->name('goods_merchant')->where($where)->whereRaw('goods_name like "%'.$keyword.'%"')->whereRaw('have_specs!=""')->count();
            $rows = DB::connect($this->config)->name('goods_merchant')
                ->where($where)
                ->whereRaw('goods_name like "%'.$keyword.'%"')
                ->whereRaw('have_specs!=""')
                ->limit($page . ',' . $limit)
                ->order('id desc')
                ->select();

            foreach ($rows as &$item) {
                if($item['goods_status']==0){
                    $item['status_name'] = '待提交审核';
                }elseif($item['goods_status']==1){
                    $item['status_name'] = '已上架';
                }
                elseif($item['goods_status']==-1){
                    $item['status_name'] = '已下架';
                }
                elseif($item['goods_status']==-1 && !empty($item['goods_reasons'])){
                    $item['status_name'] = '拒绝上架（原因：'.$item['goods_reasons'].'）';
                }
            }

            return json(['code'=>0,'count'=>$count,'data'=>$rows]);
        }else{
            return view('index/shop_backend/shelf_manage',compact('company_id','company_type','type'));
        }
    }

    #下架商品
    public function del_shelf(Request $request){
        $dat = input();

        $id = intval($dat['id']);
        $res = Db::connect($this->config)->name('goods_merchant')->where(['id'=>$id])->update(['goods_status'=>-1,'goods_reasons'=>'']);
        if($res){
            $data=Db::connect($this->config)->name('goods_merchant')->where(['id'=>$id])->find();
            Db::connect($this->config)->name('goods')->where(['goods_id'=>$data['shelf_id']])->update(['goods_status'=>-1,'goods_reasons'=>'']);

            #通知平台
            $system = Db::name('centralize_system_notice')->where(['uid'=>0])->find();
            $post = json_encode([
                'call'=>'confirmCollectionNotice',
                'find' =>"商户已把商品[".$data['goods_name']."]已下架！",
                'keyword1' => "商户已把商品[".$data['goods_name']."]已下架！",
                'keyword2' => '已下架',
                'keyword3' => date('Y-m-d H:i:s',time()),
                'remark' => '点击查看详情',
                'url' => 'https://gadmin.gogo198.cn',
                'openid' => $system['account'],
                'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
            ]);
            httpRequest('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);

            return json(['code'=>0,'msg'=>'下架成功！']);
        }
    }

    #管理上架
    public function select_shelf(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);#0商城，1官网
        $gid = intval($dat['id']);
        $type = isset($dat['type'])?intval($dat['type']):0;

        if($request->isAjax()){
            if($dat['pa']==1){
                #检测库存
                $g = Db::connect($this->config)->name('goods_merchant')->where(['id'=>$gid,'type'=>$type])->find();

                if(empty($g['goods_number'])){
                    return json(['code'=>-1,'msg'=>'跳转补充库存信息中...']);
                }

                return json(['code'=>0,'msg'=>'']);
            }
            elseif($dat['pa']==2){
                #管理上架
                $res = '';
                $g = Db::connect($this->config)->name('goods_merchant')->where(['id'=>$gid,'type'=>$type])->find();
                $shelf_id=0;
                if(empty($g['shelf_id'])){
                    #同步平台商品库
                    $shelf_id=$this->sync_goods($g,$company_id,$gid);
                }else{
                    $shelf_id=$g['shelf_id'];
                }

                #==自主导流
                if(isset($dat['guide']['my_guide'])){
                    foreach($dat['guide']['my_guide'] as $k=>$v){
                        $ishave = Db::connect($this->config)->name('goods_shelf')->where(['gid_merch'=>$gid,'type'=>1,'guide_id'=>$v])->find();
                        if(empty($ishave)){
                            $res=Db::connect($this->config)->name('goods_shelf')->insert([
                                'cid'=>$company_id,
                                'gid_merch'=>$gid,
                                'gid'=>$shelf_id,
                                'type'=>1,
                                'guide_id'=>$v,
                                'keywords'=>trim($dat['keywords']['my_guide'][$k])
                            ]);
                        }
                        else{
                            $res=Db::connect($this->config)->name('goods_shelf')->where(['cid'=>$company_id,'gid_merch'=>$gid,'type'=>1,'guide_id'=>$v])->update([
                                'keywords'=>trim($dat['keywords']['my_guide'][$k])
                            ]);
                        }
                    }
                }

                #==平台导流
                foreach($dat['guide']['platform_guide'] as $k=>$v){
                    $ishave = Db::connect($this->config)->name('goods_shelf')->where(['gid_merch'=>$gid,'type'=>2,'guide_id'=>$v])->find();
                    if(empty($ishave)){
                        $res=Db::connect($this->config)->name('goods_shelf')->insert([
                            'cid'=>$company_id,
                            'gid_merch'=>$gid,
                            'gid'=>$shelf_id,
                            'type'=>2,
                            'guide_id'=>$v,
                            'keywords'=>trim($dat['keywords']['platform_guide'][$k])
                        ]);
                    }
                    else{
                        $res=Db::connect($this->config)->name('goods_shelf')->where(['cid'=>$company_id,'gid_merch'=>$gid,'type'=>2,'guide_id'=>$v])->update([
                            'keywords'=>trim($dat['keywords']['platform_guide'][$k])
                        ]);
                    }
                }

                #==自主商城
                if(isset($dat['guide']['my_shop'])) {
                    foreach ($dat['guide']['my_shop'] as $k => $v) {
                        $ishave = Db::connect($this->config)->name('goods_shelf')->where(['gid_merch' => $gid, 'type' => 3, 'guide_id' => $v])->find();
                        if (empty($ishave)) {
                            $res = Db::connect($this->config)->name('goods_shelf')->insert([
                                'cid' => $company_id,
                                'gid_merch' => $gid,
                                'gid' => $shelf_id,
                                'type' => 3,
                                'guide_id' => $v,
                                'keywords' => trim($dat['keywords']['my_shop'][$k])
                            ]);
                        } else {
                            $res = Db::connect($this->config)->name('goods_shelf')->where(['cid' => $company_id, 'gid_merch' => $gid, 'type' => 3, 'guide_id' => $v])->update([
                                'keywords' => trim($dat['keywords']['my_shop'][$k])
                            ]);
                        }
                    }
                }

                #==平台商城
                foreach($dat['guide']['platform_shop'] as $k=>$v){
                    $ishave = Db::connect($this->config)->name('goods_shelf')->where(['gid_merch'=>$gid,'type'=>4,'guide_id'=>$v])->find();
                    if(empty($ishave)){
                        $res=Db::connect($this->config)->name('goods_shelf')->insert([
                            'cid'=>$company_id,
                            'gid_merch'=>$gid,
                            'gid'=>$shelf_id,
                            'type'=>4,
                            'guide_id'=>$v,
                            'keywords'=>trim($dat['keywords']['platform_shop'][$k])
                        ]);
                    }
                    else{
                        $res=Db::connect($this->config)->name('goods_shelf')->where(['cid'=>$company_id,'gid_merch'=>$gid,'type'=>4,'guide_id'=>$v])->update([
                            'keywords'=>trim($dat['keywords']['platform_shop'][$k])
                        ]);
                    }
                }

                return json(['code'=>0,'msg'=>'提交成功']);
            }
        }
        else{

            $list = [];
            #商品
            $list['goods'] = Db::connect($this->config)->name('goods_merchant')->where(['id'=>$gid,'type'=>$type])->find();

            #自主导流
            $list['my_guide'] = Db::name('website_navbar')->where(['company_id'=>$company_id,'company_type'=>0])->order('id','asc')->select();
            foreach($list['my_guide'] as $k=>$v){
                $list['my_guide'][$k]['my_keywords']=Db::connect($this->config)->name('goods_shelf')->where(['gid_merch'=>$gid,'type'=>1,'guide_id'=>$v['id']])->find()['keywords'];
            }
            #平台导流
            $list['platform_guide'] = Db::name('website_navbar')->where(['company_id'=>0,'system_id'=>1])->order('id','asc')->select();
            foreach($list['platform_guide'] as $k=>$v){
                $list['platform_guide'][$k]['name'] = json_decode($v['name'],true);
                $list['platform_guide'][$k]['my_keywords']=Db::connect($this->config)->name('goods_shelf')->where(['gid_merch'=>$gid,'type'=>2,'guide_id'=>$v['id']])->find()['keywords'];
            }

            #自主商城
            $list['my_shop'] = Db::connect($this->config)->name('merchsite_guide_body')->where(['company_id'=>$company_id,'company_type'=>$company_type])->order('id','asc')->select();
            foreach($list['my_shop'] as $k=>$v){
                $list['my_shop'][$k]['my_keywords']=Db::connect($this->config)->name('goods_shelf')->where(['gid_merch'=>$gid,'type'=>3,'guide_id'=>$v['id']])->find()['keywords'];
            }
            #平台商城
            $list['platform_shop'] = Db::connect($this->config)->name('guide_body')->where(['company_id'=>0,'system_id'=>3])->order('displayorders','asc')->select();
            foreach($list['platform_shop'] as $k=>$v){
                $list['platform_shop'][$k]['my_keywords']=Db::connect($this->config)->name('goods_shelf')->where(['gid_merch'=>$gid,'type'=>4,'guide_id'=>$v['id']])->find()['keywords'];
            }

            return view('index/shop_backend/select_shelf',compact('company_id','company_type','list','gid','type'));
        }
    }

    #采购管理
    public function procurement_manage(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);#0商城，1官网

        if(isset($dat['pa'])){
            $limit = $request->get('limit');
            $page = $request->get('page') - 1;
            if ($page != 0) {
                $page = $limit * $page;
            }
            $keyword = isset($dat['keywords']) ? trim($dat['keywords']) : '';
            $where =['company_id'=>$company_id,'company_type'=>$company_type];

            $count = Db::name('website_procurement')->where($where)->count();
            $rows = Db::name('website_procurement')->where($where)
//                ->where('title', 'like', '%'.$keyword.'%')
                ->limit($page.','.$limit)
                ->order('id desc')
                ->select();


            foreach ($rows as &$item) {
                $item['supplier_name'] = Db::name('website_supplier')->where(['id'=>$item['supplier_id']])->field('company_name')->find()['company_name'];
                $item['warehouse_name'] = Db::name('centralize_warehouse_list')->where(['uid'=>$item['company_id'],'id'=>$item['warehouse_id']])->field('warehouse_name')->find()['warehouse_name'];

                $currency = Db::name('centralize_currency')->where(['id'=>$item['supplier_currency']])->field('currency_symbol_standard')->find()['currency_symbol_standard'];
                #计算此订购单所有（币种）费用
                $item['total_price'] = $currency.' '.$item['total_price'];

                $item['createtime'] = date('Y-m-d H:i',$item['createtime']);
            }

            return json(['code'=>0,'count'=>$count,'data'=>$rows]);
        }else{
            return view('index/shop_backend/procurement_manage',compact('company_id','company_type'));
        }
    }

    #保存采购订单
    public function save_procurement(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);#0商城，1官网
        $id = isset($dat['id'])?intval($dat['id']):0;

        if($request->isAjax()){
            $goods_info = [];

            if(!empty($dat['goods_code'])){
                foreach($dat['goods_code'] as $k=>$v){
                    array_push($goods_info,[
                        'goods_id'=>intval($dat['goods_id'][$k]),
                        'sku_id'=>intval($dat['sku_id'][$k]),
                        'goods_code'=>trim($v),
                        'goods_quantity'=>intval($dat['goods_quantity'][$k]),
                        'goods_price'=>intval($dat['goods_price'][$k]),
                        'goods_tax'=>intval($dat['goods_tax'][$k]),
                        'goods_line_total'=>number_format(intval($dat['goods_line_total'][$k]),2,'.',''),
                    ]);
                }
            }else{
                return json(['code'=>-1,'msg'=>'请先选择系统中已有商品']);
            }

            if($id>0){
                Db::name('website_procurement')->where(['id'=>$id])->update([
                    'supplier_id'=>intval($dat['supplier_id']),
                    'warehouse_id'=>intval($dat['warehouse_id']),
                    'payment_method'=>intval($dat['payment_method']),
                    'supplier_currency'=>intval($dat['supplier_currency']),
                    'delivery_time'=>trim($dat['delivery_time']),
                    'delivery_id'=>intval($dat['delivery_id']),
                    'delivery_method'=>trim($dat['delivery_method']),
                    'delivery_no'=>trim($dat['delivery_no']),
                    'delivery_remark'=>trim($dat['delivery_remark']),
                    'goods_info'=>json_encode($goods_info,true),
                    'project_info'=>json_encode($dat['project'],true),
                    'goods_total_price'=>$dat['goods_total_price'],
                    'project_total_price'=>$dat['project_total_price'],
                    'total_price'=>$dat['total_price']
                ]);
            }else{
                Db::name('website_procurement')->insert([
                    'company_id'=>$company_id,
                    'company_type'=>$company_type,
                    'supplier_id'=>intval($dat['supplier_id']),
                    'warehouse_id'=>intval($dat['warehouse_id']),
                    'payment_method'=>intval($dat['payment_method']),
                    'supplier_currency'=>intval($dat['supplier_currency']),
                    'delivery_time'=>trim($dat['delivery_time']),
                    'delivery_id'=>intval($dat['delivery_id']),
                    'delivery_method'=>trim($dat['delivery_method']),
                    'delivery_no'=>trim($dat['delivery_no']),
                    'delivery_remark'=>trim($dat['delivery_remark']),
                    'goods_info'=>json_encode($goods_info,true),
                    'project_info'=>json_encode($dat['project'],true),
                    'goods_total_price'=>$dat['goods_total_price'],
                    'project_total_price'=>$dat['project_total_price'],
                    'total_price'=>$dat['total_price'],
                    'createtime'=>time()
                ]);

                #插入企业&仓库的库存表
                foreach($goods_info as $k=>$v){
                    #为每个商品及其规格修改库存信息
                    $goods_infos = Db::connect($this->config)->name('goods_merchant')->where(['id'=>$v['goods_id']])->field(['goods_number','have_specs'])->find();
                    $sku_infos = Db::connect($this->config)->name('goods_sku_merchant')->where(['goods_id'=>$v['goods_id'],'sku_id'=>$v['sku_id']])->find();
                    $origin_goods_number = $goods_infos['goods_number'];#原商品库存
                    $sku_infos['sku_prices'] = json_decode($sku_infos['sku_prices'],true);
                    $origin_sku_number = $sku_infos['sku_prices']['goods_number'];#原规格库存

                    $goods_number = intval($origin_goods_number) + intval($v['goods_quantity']);#最新商品库存
                    $sku_infos['sku_prices']['goods_number'] = intval($origin_sku_number) + intval($v['goods_quantity']);#最新商品规格库存
                    if($goods_infos['have_specs']==1){
                        #有规格
                        Db::connect($this->config)->name('goods_merchant')->where(['id'=>$v['goods_id']])->update(['goods_number'=>$goods_number]);
                    }elseif($goods_infos['have_specs']==2){
                        #无规格
                        $goods_infos['nospecs'] = json_decode($goods_infos['nospecs'],true);
                        $goods_infos['nospecs']['goods_number'] = $goods_number;
                        Db::connect($this->config)->name('goods_merchant')->where(['id'=>$v['goods_id']])->update(['goods_number'=>$goods_number,'nospecs'=>json_encode($goods_infos['nospecs'],true)]);
                    }
                    Db::connect($this->config)->name('goods_sku_merchant')->where(['goods_id'=>$v['goods_id'],'sku_id'=>$v['sku_id']])->update(['sku_prices'=>json_encode($sku_infos['sku_prices'],true),'goods_number'=>$sku_infos['sku_prices']['goods_number']]);

                    #记录在X企业Y仓库的A商品下的B规格库存数量
                    $warehouse_infos = Db::name('website_warehouse_goodsnum')->where(['company_id'=>$company_id,'goods_id'=>$v['goods_id'],'sku_id'=>$v['sku_id']])->find();
                    if(empty($warehouse_infos)){
                        Db::name('website_warehouse_goodsnum')->insert([
                            'company_id'=>$company_id,
                            'warehouse_id'=>intval($dat['warehouse_id']),
                            'goods_id'=>$v['goods_id'],
                            'sku_id'=>$v['sku_id'],
                            'num'=>$v['goods_quantity']
                        ]);
                    }else{
                        Db::name('website_warehouse_goodsnum')->where(['company_id'=>$company_id,'goods_id'=>$v['goods_id'],'sku_id'=>$v['sku_id']])->update([
                           'num'=>$warehouse_infos['num'] + $v['goods_quantity']
                        ]);
                    }
                }
            }

            return json(['code'=>0,'msg'=>'保存成功']);
        }else{
            $data = ['supplier_id'=>0,'warehouse_id'=>0,'payment_method'=>'','supplier_currency'=>5,'delivery_time'=>'','delivery_id'=>'','delivery_method'=>'','delivery_no'=>'','delivery_remark'=>'','goods_info'=>'','project_info'=>'','currency_symbol'=>'CNY','goods_total_price'=>'0.00','project_total_price'=>'0.00','total_price'=>'0.00'];
            if($id>0){
                $data = Db::name('website_procurement')->where(['id'=>$id])->find();
                $data['goods_info'] = json_decode($data['goods_info'],true);
                $data['project_info'] = json_decode($data['project_info'],true);

                #商品信息
                foreach($data['goods_info'] as $k=>$v){
                    $goods_info = Db::connect($this->config)->name('goods_merchant')->where(['id'=>$v['goods_id']])->field('goods_name')->find();
                    $data['goods_info'][$k]['goods_name'] = $goods_info['goods_name'];

                    $sku_info = Db::connect($this->config)->name('goods_sku_merchant')->where(['sku_id'=>$v['sku_id']])->field('spec_names')->find();
                    $data['goods_info'][$k]['sku_name'] = $sku_info['spec_names'];
                }

                #采购单币种
                $data['currency_symbol'] = Db::name('centralize_currency')->where(['id'=>$data['supplier_currency']])->field('currency_symbol_standard')->find()['currency_symbol_standard'];
            }

            #供应企业
            $list['supplier'] = Db::name('website_supplier')->where(['company_id'=>$company_id])->select();

            #仓库信息
            $list['warehouse'] = Db::name('centralize_warehouse_list')->where(['uid'=>$company_id])->select();

            #付款条件
            $list['payment_method'] = [['id'=>0,'name'=>'无'],['id'=>1,'name'=>'货到付款'],['id'=>2,'name'=>'收货后付款'],['id'=>3,'name'=>'预付款'],['id'=>4,'name'=>'Net 7'],['id'=>5,'name'=>'Net 15'],['id'=>6,'name'=>'Net 30'],['id'=>7,'name'=>'Net 45'],['id'=>8,'name'=>'Net 60']];

            #币种信息
            $list['currency'] = Db::name('centralize_currency')->select();

            #物流企业
            $list['express'] = Db::name('centralize_diycountry_content')->where(['pid'=>6])->select();

            #物流方式
            $list['express_method'] = Db::name('centralize_lines_transport_method')->select();

            return view('index/shop_backend/save_procurement',compact('company_id','company_type','id','list','data'));
        }
    }

    #获取商店所有商品
    public function get_goods(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $goods = Db::connect($this->config)->name('goods_merchant')->where(['cid'=>$company_id])->field(['id as goods_id','goods_name','cid','goods_number','goods_image'])->order('id desc')->select();
        foreach($goods as $k=>$v){
            $goods[$k]['variants'] = Db::connect($this->config)->name('goods_sku_merchant')->where(['goods_id'=>$v['goods_id']])->field(['sku_id','spec_names','sku_prices'])->select();
            foreach($goods[$k]['variants'] as $k2=>$v2){
                if(empty($v2['spec_names'])){
                    $goods[$k]['variants'][$k2]['spec_names'] = $v['goods_name'];
                }
                $goods[$k]['variants'][$k2]['sku_prices'] = json_decode($v2['sku_prices'],true);
                $goods[$k]['variants'][$k2]['goods_number'] = $goods[$k]['variants'][$k2]['sku_prices']['goods_number'];
            }
        }

        return json(['code'=>0,'data'=>$goods]);
    }

    #删除采购订单
    public function del_procurement(Request $request){
        $dat = input();

        $id = intval($dat['id']);
        $res = Db::name('website_procurement')->where(['id'=>$id])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功']);
        }
    }

    #保存供应企业
    public function save_supplier(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);
        $id = isset($dat['id'])?intval($dat['id']):0;

        if($request->isAjax()){
            if($id>0){
                Db::name('website_supplier')->where(['id'=>$id])->update([
                    'company_name'=>trim($dat['company_name']),
                    'country_id'=>intval($dat['country_id']),
                    'postal_code'=>trim($dat['postal_code']),
                    'address'=>trim($dat['address']),
                    'name'=>trim($dat['name']),
                    'email'=>trim($dat['email']),
                    'tel'=>trim($dat['tel']),
                ]);
            }else{
                Db::name('website_supplier')->insert([
                    'company_id'=>intval($dat['company_id']),
                    'company_type'=>intval($dat['company_type']),
                    'company_name'=>trim($dat['company_name']),
                    'country_id'=>intval($dat['country_id']),
                    'postal_code'=>trim($dat['postal_code']),
                    'address'=>trim($dat['address']),
                    'name'=>trim($dat['name']),
                    'email'=>trim($dat['email']),
                    'tel'=>trim($dat['tel']),
                    'createtime'=>time()
                ]);
            }

            return json(['code'=>0,'msg'=>'保存成功']);
        }else{
            $data = ['company_name'=>'','country_id'=>0,'postal_code'=>'','pre_address'=>'','address'=>'','name'=>'','email'=>'','tel'=>''];
            if($id>0){
                $data = Db::name('website_supplier')->where(['id'=>$id])->find();
                $pre_address = Db::name('all_country_area_postcode')->where(['postal_code'=>$data['postal_code'],'country_id'=>$data['country_id']])->find();
                $data['pre_address'] = $pre_address['admin_name1'].' '.$pre_address['admin_name2'].' '.$pre_address['admin_name3'];
            }

            #国家和手机号码前缀
            $list['country'] = Db::name('centralize_diycountry_content')->where(['pid'=>5])->select();

            return view('index/shop_backend/save_supplier',compact('company_id','company_type','id','list','data'));
        }
    }

    #调拨管理
    public function transfer_manage(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);#0商城，1官网

        if(isset($dat['pa'])){
            $limit = $request->get('limit');
            $page = $request->get('page') - 1;
            if ($page != 0) {
                $page = $limit * $page;
            }
            $keyword = isset($dat['keywords']) ? trim($dat['keywords']) : '';
            $where =['company_id'=>$company_id,'company_type'=>$company_type];

            $count = Db::name('website_warehouse_transfer')->where($where)->count();
            $rows = Db::name('website_warehouse_transfer')->where($where)
//                ->where('title', 'like', '%'.$keyword.'%')
                ->limit($page.','.$limit)
                ->order('id desc')
                ->select();


            foreach ($rows as &$item) {
                $item['in_name'] = Db::name('centralize_warehouse_list')->where(['id'=>$item['in_id']])->field('warehouse_name')->find()['warehouse_name'];
                $item['out_name'] = Db::name('centralize_warehouse_list')->where(['id'=>$item['out_id']])->field('warehouse_name')->find()['warehouse_name'];

                $item['createtime'] = date('Y-m-d H:i',$item['createtime']);
            }

            return json(['code'=>0,'count'=>$count,'data'=>$rows]);
        }else{
            return view('index/shop_backend/transfer_manage',compact('company_id','company_type'));
        }
    }

    #保存调拨
    public function save_transfer(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);
        $id = isset($dat['id'])?intval($dat['id']):0;

        if($request->isAjax()){
            if($dat['in_id']==$dat['out_id']){
                return json(['code'=>-1,'msg'=>'调出仓库不能与调入仓库相同。']);
            }

            $goods_info = [];
            if(!empty($dat['goods_quantity'])){
                foreach($dat['goods_quantity'] as $k=>$v){
                    #判断是否大于现有库存
                    $out_warehouse_info = Db::name('website_warehouse_goodsnum')->where(['company_id'=>$company_id,'warehouse_id'=>$dat['out_id'],'goods_id'=>$dat['goods_id'][$k],'sku_id'=>$dat['sku_id'][$k]])->find();
                    if(empty($out_warehouse_info)){
                        return json(['code'=>-1,'msg'=>'调出失败，库存不足']);
                    }else{
                        if($out_warehouse_info['num']<intval($v)){
                            return json(['code'=>-1,'msg'=>'调出库存不能大于现有库存']);
                        }
                    }

                    array_push($goods_info,[
                        'goods_id'=>intval($dat['goods_id'][$k]),
                        'sku_id'=>intval($dat['sku_id'][$k]),
                        'goods_quantity'=>intval($v),
                    ]);
                }
            }else{
                return json(['code'=>-1,'msg'=>'请先选择系统中已有商品']);
            }

            if($id>0){
                Db::name('website_warehouse_transfer')->where(['id'=>$id])->update([
                    'in_id'=>intval($dat['in_id']),
                    'out_id'=>intval($dat['out_id']),
                    'goods_info'=>json_encode($goods_info,true),
                    'remark'=>trim($dat['remark']),
                ]);
            }else{
                Db::name('website_warehouse_transfer')->insert([
                    'company_id'=>intval($dat['company_id']),
                    'company_type'=>intval($dat['company_type']),
                    'in_id'=>intval($dat['in_id']),
                    'out_id'=>intval($dat['out_id']),
                    'goods_info'=>json_encode($goods_info,true),
                    'remark'=>trim($dat['remark']),
                    'createtime'=>time()
                ]);

                #自动调出仓库，做记录
                foreach($dat['goods_quantity'] as $k=>$v) {
                    #调出仓库
                    $out_warehouse_info = Db::name('website_warehouse_goodsnum')->where(['company_id' => $company_id, 'warehouse_id' => $dat['out_id'], 'goods_id' => $dat['goods_id'][$k], 'sku_id' => $dat['sku_id'][$k]])->find();
                    Db::name('website_warehouse_goodsnum')->where(['company_id' => $company_id, 'warehouse_id' => $dat['out_id'], 'goods_id' => $dat['goods_id'][$k], 'sku_id' => $dat['sku_id'][$k]])->update(['num'=>$out_warehouse_info['num'] - intval($v)]);

                    #调入仓库
                    $in_warehouse_info = Db::name('website_warehouse_goodsnum')->where(['company_id' => $company_id, 'warehouse_id' => $dat['in_id'], 'goods_id' => $dat['goods_id'][$k], 'sku_id' => $dat['sku_id'][$k]])->find();
                    if(empty($in_warehouse_info)){
                        Db::name('website_warehouse_goodsnum')->insert([
                            'company_id'=>$company_id,
                            'warehouse_id'=>$dat['in_id'],
                            'goods_id'=>$dat['goods_id'][$k],
                            'sku_id'=>$dat['sku_id'][$k],
                            'num'=>intval($v)
                        ]);
                    }else{
                        Db::name('website_warehouse_goodsnum')->where(['company_id' => $company_id, 'warehouse_id' => $dat['in_id'], 'goods_id' => $dat['goods_id'][$k], 'sku_id' => $dat['sku_id'][$k]])->update(['num'=>$in_warehouse_info['num'] + intval($v)]);
                    }
                }

            }

            return json(['code'=>0,'msg'=>'保存成功']);
        }else{
            $data = ['in_id'=>0,'out_id'=>0,'goods_info'=>'','remark'=>''];
            if($id>0){
                $data = Db::name('website_warehouse_transfer')->where(['id'=>$id])->find();
                $data['goods_info'] = json_decode($data['goods_info'],true);

                #商品信息
                foreach($data['goods_info'] as $k=>$v){
                    $goods_info = Db::connect($this->config)->name('goods_merchant')->where(['id'=>$v['goods_id']])->field('goods_name')->find();
                    $data['goods_info'][$k]['goods_name'] = $goods_info['goods_name'];

                    $sku_info = Db::connect($this->config)->name('goods_sku_merchant')->where(['sku_id'=>$v['sku_id']])->field('spec_names')->find();
                    $data['goods_info'][$k]['sku_name'] = $sku_info['spec_names'];
                }

            }

            #仓库
            $list['warehouse'] = Db::name('centralize_warehouse_list')->where(['uid'=>$company_id])->select();

            return view('index/shop_backend/save_transfer',compact('company_id','company_type','id','list','data'));
        }
    }

    #删除调拨
    public function del_transfer(Request $request){
        $dat = input();

        $id = intval($dat['id']);
        $res = Db::name('website_warehouse_transfer')->where(['id'=>$id])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功']);
        }
    }

    #同步商品
    public function sync_goods($data,$shop_id,$id){
        $image = Db::connect($this->config)->name('goods_image_merchant')->where(['goods_id'=>$id])->select();
        #上架商品信息
        $shelf_id = Db::connect($this->config)->name('goods')->insertGetId([
            'goods_name'=>$data['goods_name'],
            'type'=>$data['type'],
            'shop_id'=>$shop_id,
            'cat_id'=>$data['cat_id'],
            'cat_id1'=>$data['cat_id1'],
            'cat_id2'=>$data['cat_id2'],
            'cat_id3'=>$data['cat_id3'],
            'service_type'=>$data['service_type'],
            'domestic_logistics'=>$data['domestic_logistics'],
            'gather_method'=>$data['gather_method'],
            'support_export'=>$data['support_export'],
            'gather_countrys'=>$data['gather_countrys'],
            'rule_id'=>$data['rule_id'],
            'brand_type'=>$data['brand_type'],
            'brand_type2'=>$data['brand_type2'],
            'brand_id'=>$data['brand_id'],
            'brand_name'=>$data['brand_name'],
            'have_specs'=>$data['have_specs'],
            'nospecs'=>$data['nospecs'],
            'otherfees_content'=>$data['otherfees_content'],
            'reduction_content'=>$data['reduction_content'],
            'gift_content'=>$data['gift_content'],
            'noinclude_content'=>$data['noinclude_content'],
            'potential_content'=>$data['potential_content'],
            'activity_info'=>$data['activity_info'],
            'other_keywords'=>$data['other_keywords'],
            'manufacture'=>$data['manufacture'],#默认项目=start
            'sales'=>$data['sales'],
            'foreign'=>$data['foreign'],
            'effective'=>$data['effective'],
            'store'=>$data['store'],
            'packing'=>$data['packing'],#默认项目=end
            'spec_info'=>$data['spec_info'],
            'pc_desc'=>$data['pc_desc'],
            'created_at'=>$data['created_at'],
            'sku_open'=>$data['sku_open'],
            'sku_id'=>0,//已做
            'goods_status'=>0,#平台审核上架
            'shipping_country'=>$data['shipping_country'],
            'goods_currency'=>$data['goods_currency'],
            'areas'=>$data['areas'],
            'goods_price'=>$data['goods_price'],
            'market_price'=>$data['market_price'],
            'cost_price'=>$data['cost_price'],
            'goods_number'=>$data['goods_number'],
            'goods_image'=>'//dtc.gogo198.net'.$data['goods_image'],
            'goods_video'=>!empty($data['goods_video'])?'//dtc.gogo198.net'.$data['goods_video']:'',
        ]);
        $sku_id = 0;
        if($data['have_specs']==1){
            #有规格
            $sku = Db::connect($this->config)->name('goods_sku_merchant')->where(['goods_id'=>$id])->select();

            $date = date('Y-m-d H:i:s',time());
            foreach($sku as $k=>$v){
                $skuid=Db::connect($this->config)->name('goods_sku')->insertGetId([
                    'goods_id'=>$shelf_id,
                    'spec_ids'=>$v['spec_ids'],
                    'spec_vids'=>$v['spec_vids'],
                    'spec_names'=>$v['spec_names'],
                    'sku_specs'=>$v['sku_specs'],
                    'sku_prices'=>$v['sku_prices'],
                    'goods_price'=>$v['goods_price'],
                    'shelf_number'=>$v['shelf_number'],
                    'market_price'=>$v['market_price'],
                    'cost_price'=>$v['cost_price'],
                    'goods_number'=>$v['goods_number'],
                    'goods_sn'=>$v['goods_sn'],
                    'goods_barcode'=>$v['goods_barcode'],
                    'warn_type'=>$v['warn_type'],
                    'warn_number'=>$v['warn_number'],
                    'goods_stockcode'=>$v['goods_stockcode'],
                ]);
                if($sku_id==0){
                    $sku_id = $skuid;
                }

                $spec_ids = explode('|',$v['spec_ids']);
                $spec_vids = explode('|',$v['spec_vids']);
                foreach($spec_vids as $k2=>$v2){
                    $sv2 = explode('-',$v2);
                    if(count($sv2)>1){
                        foreach($sv2 as $k3=>$v3){
                            $aval = Db::connect($this->config)->name('attr_value')->where(['attr_vid'=>$v3])->find();
                            if(!empty($aval)){
                                $ishave = Db::connect($this->config)->name('goods_spec')->where(['attr_id'=>$spec_ids[$k2],'goods_id'=>$shelf_id,'attr_vid'=>$v3,'attr_value'=>$aval['attr_vname']])->find();
                                if(empty($ishave)){
                                    Db::connect($this->config)->name('goods_spec')->insert([
                                        'goods_id'=>$shelf_id,
                                        'attr_id'=>$spec_ids[$k2],
                                        'attr_vid'=>$v3,
                                        'attr_value'=>$aval['attr_vname'],
                                        'created_at'=>$date,
                                        'updated_at'=>$date,
                                    ]);
                                }
                            }
                        }
                    }else{
                        $aval = Db::connect($this->config)->name('attr_value')->where(['attr_vid'=>$v2])->find();
                        if(!empty($aval)){
                            $ishave = Db::connect($this->config)->name('goods_spec')->where(['attr_id'=>$spec_ids[$k2],'goods_id'=>$shelf_id,'attr_vid'=>$v2,'attr_value'=>$aval['attr_vname']])->find();
                            if(empty($ishave)) {
                                Db::connect($this->config)->name('goods_spec')->insert([
                                    'goods_id' => $shelf_id,
                                    'attr_id' => $spec_ids[$k2],
                                    'attr_vid' => $v2,
                                    'attr_value' => $aval['attr_vname'],
                                    'created_at' => $date,
                                    'updated_at' => $date,
                                ]);
                            }
                        }
                    }
                }
            }
        }
        elseif($data['have_specs']==2){
            #无规格
            $sku = Db::connect($this->config)->name('goods_sku_merchant')->where(['goods_id'=>$id])->find();
            $skuid=Db::connect($this->config)->name('goods_sku')->insertGetId([
                'goods_id'=>$shelf_id,
                'spec_ids'=>$sku['spec_ids'],
                'spec_vids'=>$sku['spec_vids'],
                'spec_names'=>$sku['spec_names'],
                'sku_specs'=>$sku['sku_specs'],
                'sku_prices'=>$sku['sku_prices'],
                'goods_price'=>$sku['goods_price'],
                'shelf_number'=>$sku['shelf_number'],
                'market_price'=>$sku['market_price'],
                'cost_price'=>$sku['cost_price'],
                'goods_number'=>$sku['goods_number'],
                'goods_sn'=>$sku['goods_sn'],
                'goods_barcode'=>$sku['goods_barcode'],
                'warn_type'=>$sku['warn_type'],
                'warn_number'=>$sku['warn_number'],
                'goods_stockcode'=>$sku['goods_stockcode'],
            ]);
            if($sku_id==0){
                $sku_id = $skuid;
            }
        }

        #记录在商家商品表
        Db::connect($this->config)->name('goods_merchant')->where(['id'=>$id])->update([
            'shelf_id'=>$shelf_id,
            'goods_status'=>1,
        ]);

        #记录在平台商品表
        Db::connect($this->config)->name('goods')->where(['goods_id'=>$shelf_id])->update([
            'sku_id'=>$sku_id,
        ]);


        #上架图片
        foreach($image as $k=>$v){
            Db::connect($this->config)->name('goods_image')->insert([
                'goods_id'=>$shelf_id,
                'path'=>'//dtc.gogo198.net'.$v['path'],
                'is_default'=>$v['is_default'],
                'sort'=>$v['sort'],
                'created_at'=>$v['created_at'],
                'updated_at'=>$v['updated_at'],
            ]);
        }

        #通知平台
        $system = Db::name('centralize_system_notice')->where(['uid'=>0])->find();
        $post = json_encode([
            'call'=>'confirmCollectionNotice',
            'find' =>"有新的商品提交审核，请打开查看！",
            'keyword1' => "有新的商品提交审核，请打开查看！",
            'keyword2' => '待审核',
            'keyword3' => date('Y-m-d H:i:s',time()),
            'remark' => '点击查看详情',
            'url' => 'https://gadmin.gogo198.cn',
            'openid' => $system['account'],
            'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
        ]);
        httpRequest('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);

        return $shelf_id;
    }

    #买家管理
    public function member_manage(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);#0商城，1官网

        if(isset($dat['pa'])){
            $limit = $request->get('limit');
            $page = $request->get('page') - 1;
            if ($page != 0) {
                $page = $limit * $page;
            }
            $keyword = isset($dat['keywords']) ? trim($dat['keywords']) : '';
            $where =['company_id'=>$company_id];

            $count = Db::name('website_user')->where($where)->count();
            $rows = Db::name('website_user')->where($where)
//                ->where('title', 'like', '%'.$keyword.'%')
                ->limit($page.','.$limit)
                ->order('id desc')
                ->select();


            foreach ($rows as &$item) {

                $item['createtime'] = date('Y-m-d H:i',$item['createtime']);
            }

            return json(['code'=>0,'count'=>$count,'data'=>$rows]);
        }else{
            return view('index/shop_backend/member_manage',compact('company_id','company_type'));
        }
    }

    #保存买家
    public function save_member(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);
        $id = isset($dat['id'])?intval($dat['id']):0;

        if($request->isAjax()){
            if($id>0){
                Db::name('website_user')->where(['id'=>$id])->update([
                    'realname'=>trim($dat['realname']),
                    'nickname'=>trim($dat['nickname']),
                    'email'=>trim($dat['email']),
                    'area_code'=>intval($dat['area_code']),
                    'phone'=>trim($dat['phone']),
                ]);
            }else{
                foreach($dat['realname'] as $k=>$v){
                    if(!empty($v) && !empty($dat['nickname'][$k]) && !empty($dat['email'][$k])){
                        $ishave = Db::name('website_user')->where(['realname'=>trim($v),'nickname'=>trim($dat['nickname'][$k]),'email'=>trim($dat['email'][$k]),'area_code'=>intval($dat['area_code'][$k]),'phone'=>trim($dat['phone'][$k])])->find();
                        if(empty($ishave)){
                            $time = time();
                            $insertid = Db::name('website_user')->insertGetId([
                                'company_id'=>$company_id,
                                'realname'=>$v,
                                'nickname'=>trim($dat['nickname'][$k]),
                                'email'=>trim($dat['email'][$k]),
                                'area_code'=>intval($dat['area_code'][$k]),
                                'phone'=>trim($dat['phone'][$k]),
                                'createtime'=>$time
                            ]);

                            $custom_id = 'G'.str_pad($insertid, 5, '0', STR_PAD_LEFT);
//                            $nickname = 'GoFriend_'.$custom_id;
                            $res = Db::name('website_user')->where('id',$insertid)->update(['custom_id'=>$custom_id]);

                            if($res){
                                #赋予账号
                                $account = Db::name('website_user')->where('id',$insertid)->find();
                                #集运网账号
                                Db::name('centralize_user')->insert([
                                    'name'=>$account['nickname'],
                                    'realname'=>$account['realname'],
                                    'email'=>$account['email'],
                                    'pwd'=>md5('888888'),
                                    'mobile'=>$account['phone'],
                                    'status'=>0,
                                    'agentid'=>$account['agent_id'],
                                    'gogo_id'=>$account['custom_id'],
                                    'createtime'=>$time,
                                ]);
                                #买全球账号（旧的）
                                Db::name('sz_yi_member')->insert([
                                    'uniacid'=>3,
                                    'realname'=>$account['realname'],
                                    'nickname'=>$account['nickname'],
                                    'mobile'=>$account['phone']!=''?$account['phone']:$account['email'],
                                    'pwd'=>md5('888888'),
                                    'id_card'=>$account['idcard'],
                                    'gogo_id'=>$account['custom_id'],
                                    'createtime'=>$time,
                                ]);
                                #卖全球账号（旧的）
                                Db::name('sz_yi_member')->insert([
                                    'uniacid'=>18,
                                    'realname'=>$account['realname'],
                                    'nickname'=>$account['nickname'],
                                    'mobile'=>$account['phone']!=''?$account['phone']:$account['email'],
                                    'pwd'=>md5('888888'),
                                    'id_card'=>$account['idcard'],
                                    'gogo_id'=>$account['custom_id'],
                                    'createtime'=>$time,
                                ]);
                                #商城账号(新的)
                                Db::connect($this->config)->name('user')->insert([
                                    'role_id'=>0,
                                    'gogo_id'=>$custom_id,
                                    'user_name'=>$account['realname'],
                                    'nickname'=>$account['nickname'],
                                    'password'=>'$2y$10$Nbq/GtGDT6wjbs6e7WhJ0Ox2EaWQ0ANcpayPi9bFLQQ6B3rEEeHx2',//6个8
                                    'mobile'=>$account['phone'],
                                    'email'=>$account['email'],
                                    'status'=>1,
                                    'shopping_status'=>1,
                                    'comment_status'=>1,
                                    'created_at'=>date('Y-m-d H:i:s',$time)
                                ]);
                            }
                        }
                    }
                }
            }

            return json(['code'=>0,'msg'=>'保存成功']);
        }else{
            $data = ['realname'=>'','nickname'=>'','email'=>'','area_code'=>'162','phone'=>''];
            if($id>0){
                $data = Db::name('website_user')->where(['id'=>$id])->find();
            }

            $list = Db::name('centralize_diycountry_content')->where(['pid'=>5])->field(['id','param2','param8'])->select();
            return view('index/shop_backend/save_member',compact('company_id','company_type','id','data','list'));
        }
    }

    #删除买家
    public function del_member(Request $request){
        $dat = input();
        $id = intval($dat['id']);
        $account = Db::name('website_user')->where(['id'=>$id])->find();
        if($account){
            Db::name('centralize_user')->where(['gogo_id'=>$account['custom_id']])->delete();
            Db::name('sz_yi_member')->where(['gogo_id'=>$account['custom_id']])->delete();
            Db::connect($this->config)->name('user')->where(['gogo_id'=>$account['custom_id']])->delete();
            Db::name('website_user')->where(['id'=>$id])->delete();
        }
        return json(['code'=>0,'msg'=>'删除成功']);
    }

    #合并买家
    public function merge_member(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);

        if($request->isAjax()){
            $buyer_id = explode(',',$dat['buyer_id']);
            if(count($buyer_id)<2){
                return json(['code'=>-1,'msg'=>'至少要选择两位买家，才能合并。']);
            }
            $member = Db::name('website_user')->where(['id'=>$buyer_id[0]])->find();
            foreach($buyer_id as $k=>$v){
                if($k>0){
                    $user = Db::name('website_user')->where(['id'=>$v])->find();
                    $merge_id = Db::name('website_merge_member_log')->insertGetId([
                        'company_id'=>$company_id,
                        'to_id'=>$buyer_id[$k],
                        'member_id'=>$v,
                        'status'=>0,
                        'createtime'=>time()
                    ]);

                    $company = Db::name('website_user_company')->where(['id'=>$company_id])->find();
                    //邮箱通知会员
                    $res=httpRequest('https://shop.gogo198.cn/collect_website/public/?s=/api/sendemail/index',['email'=>$user['email'],'title'=>$company['company'].'邀请你合并指定买家','content'=>'<p>你好，您的账号【'.$user['realname'].'】经商家合并操作，现将合并至其他买家【'.$member['realname'].'】，请点击：<a href="https://dtc.gogo198.net/?s=index/online_merge_member&merge_id='.intval($merge_id).'" style="text-decoration: underline;">立即处理</a> <br/><p>我们很乐意倾听用户的意见！如果您有任何意见或问题，请电邮至：</p><p>We are very willing to listen to the opinions of users! If you have any opinions or problems, please email to:</p><p>198@gogo198.net</p><br/><p>谢谢 Thank you!</p><br/><p>购购网 | Gogo</p>']);
                }
            }
            return json(['code'=>0,'msg'=>'保存成功']);
        }else{
            $list = Db::name('website_user')->where(['company_id'=>$company_id])->select();
            $list = json_encode($list,true);
            return view('index/shop_backend/merge_member',compact('company_id','company_type','list'));
        }
    }

    #在线同意合并买家
    public function online_merge_member(Request $request){
        $dat = input();
        $id = intval($dat['merge_id']);

        if($request->isAjax()){
            $status = intval($dat['status']);
            if($status==1){
                #同意
                $data = Db::name('website_merge_member_log')->where(['id'=>$id])->find();
                $info['to_user'] = Db::name('website_user')->where(['id'=>$data['to_id']])->find();
                $info['now_user'] = Db::name('website_user')->where(['id'=>$data['member_id']])->find();

                #查找当前用户id的订购订单、集运订单、地址、支付订单为对象用户
                Db::name('website_order_list')->where(['user_id'=>$info['now_user']['id']])->update(['user_id'=>$info['to_user']['id']]);
                Db::name('centralize_order_fee_log')->where(['user_id'=>$info['now_user']['id']])->update(['user_id'=>$info['to_user']['id']]);
                Db::name('centralize_parcel_order')->where(['user_id'=>$info['now_user']['id']])->update(['user_id'=>$info['to_user']['id']]);
                Db::name('centralize_parcel_order_transfer')->where(['user_id'=>$info['now_user']['id']])->update(['user_id'=>$info['to_user']['id']]);
                Db::name('centralize_parcel_order_spin')->where(['user_id'=>$info['now_user']['id']])->update(['user_id'=>$info['to_user']['id']]);
                Db::name('centralize_parcel_order_goods')->where(['user_id'=>$info['now_user']['id']])->update(['user_id'=>$info['to_user']['id']]);
                Db::name('centralize_parcel_merge_order')->where(['user_id'=>$info['now_user']['id']])->update(['user_id'=>$info['to_user']['id']]);
                Db::name('centralize_user_address')->where(['user_id'=>$info['now_user']['id']])->update(['user_id'=>$info['to_user']['id']]);
                Db::name('centralize_warehouse_list')->where(['uid'=>$info['now_user']['id']])->update(['uid'=>$info['to_user']['id']]);

                #删除当前用户id
                Db::name('centralize_user')->where(['gogo_id'=>$info['now_user']['custom_id']])->delete();
                Db::name('sz_yi_member')->where(['gogo_id'=>$info['now_user']['custom_id']])->delete();
                Db::connect($this->config)->name('user')->where(['gogo_id'=>$info['now_user']['custom_id']])->delete();
                Db::name('website_user')->where(['id'=>$data['member_id']])->delete();

                Db::name('website_merge_member_log')->where(['id'=>$id])->update([
                    'status'=>$status
                ]);
            }elseif($status==-1){
                #拒绝
                Db::name('website_merge_member_log')->where(['id'=>$id])->update([
                   'status'=>$status
                ]);
            }

            return json(['code'=>0,'msg'=>'操作成功']);
        }else{
            $data = Db::name('website_merge_member_log')->where(['id'=>$id])->find();

            $info['company'] = Db::name('website_user_company')->where(['id'=>$data['company_id']])->find();
            $info['to_user'] = Db::name('website_user')->where(['id'=>$data['to_id']])->find();
            $info['now_user'] = Db::name('website_user')->where(['id'=>$data['member_id']])->find();
            return view('index/shop_backend/online_merge_member',compact('data','id','info'));
        }
    }

    #等级管理
    public function grade_manage(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);#0商城，1官网

        if(isset($dat['pa'])){
            $limit = $request->get('limit');
            $page = $request->get('page') - 1;
            if ($page != 0) {
                $page = $limit * $page;
            }
            $keyword = isset($dat['keywords']) ? trim($dat['keywords']) : '';
            $where =['company_id'=>$company_id,'company_type'=>$company_type];

            $count = Db::name('website_grade_manage')->where($where)->count();
            $rows = Db::name('website_grade_manage')->where($where)
//                ->where('title', 'like', '%'.$keyword.'%')
                ->limit($page.','.$limit)
                ->order('id desc')
                ->select();


            foreach ($rows as &$item) {
                $item['createtime'] = date('Y-m-d H:i',$item['createtime']);
            }

            return json(['code'=>0,'count'=>$count,'data'=>$rows]);
        }else{
            return view('index/shop_backend/grade_manage',compact('company_id','company_type'));
        }
    }

    #保存等级
    public function save_grade(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);
        $id = isset($dat['id'])?intval($dat['id']):0;

        if($request->isAjax()){
            if($id>0){
                Db::name('website_grade_manage')->where(['id'=>$id])->update([
                    'name'=>trim($dat['name']),
                    'desc'=>trim($dat['desc']),
                    'coupon_id'=>intval($dat['coupon_id']),
                    'gift_id'=>intval($dat['gift_id']),
                    'other_gift'=>trim($dat['other_gift'])
                ]);
            }else{
                Db::name('website_grade_manage')->insert([
                    'company_id'=>$company_id,
                    'company_type'=>$company_type,
                    'name'=>trim($dat['name']),
                    'desc'=>trim($dat['desc']),
                    'coupon_id'=>intval($dat['coupon_id']),
                    'gift_id'=>intval($dat['gift_id']),
                    'other_gift'=>trim($dat['other_gift']),
                    'createtime'=>time(),
                ]);
            }

            return json(['code'=>0,'msg'=>'保存成功']);
        }else{
            $data = ['name'=>'','desc'=>'','coupon_id'=>'','gift_id'=>'','other_gift'=>''];
            if($id>0){
                $data = Db::name('website_grade_manage')->where(['id'=>$id])->find();
            }

            $list['coupon_info'] = Db::name('centralize_coupon_list')->select();
//            'goods_status'=>0
            $list['gift_info'] = Db::connect($this->config)->name('goods')->where(['shop_id'=>$company_id,'type'=>1])->select();
            return view('index/shop_backend/save_grade',compact('company_id','company_type','id','data','list'));
        }
    }

    #删除等级
    public function del_grade(Request $request){
        $dat = input();
        $id = intval($dat['id']);
        Db::name('website_grade_manage')->where(['id'=>$id])->delete();

        return json(['code'=>0,'msg'=>'删除成功']);
    }

    #标签管理
    public function tag_manage(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);#0商城，1官网

        if(isset($dat['pa'])){
            $limit = $request->get('limit');
            $page = $request->get('page') - 1;
            if ($page != 0) {
                $page = $limit * $page;
            }
            $keyword = isset($dat['keywords']) ? trim($dat['keywords']) : '';
            $where =['company_id'=>$company_id,'company_type'=>$company_type];

            $count = Db::name('website_tag_manage')->where($where)->count();
            $rows = Db::name('website_tag_manage')->where($where)
//                ->where('title', 'like', '%'.$keyword.'%')
                ->limit($page.','.$limit)
                ->order('id desc')
                ->select();


            foreach ($rows as &$item) {
                $item['createtime'] = date('Y-m-d H:i',$item['createtime']);
            }

            return json(['code'=>0,'count'=>$count,'data'=>$rows]);
        }else{
            return view('index/shop_backend/tag_manage',compact('company_id','company_type'));
        }
    }

    #保存标签
    public function save_tag(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);
        $id = isset($dat['id'])?intval($dat['id']):0;

        if($request->isAjax()){
            if($id>0){
                Db::name('website_tag_manage')->where(['id'=>$id])->update([
                    'name'=>trim($dat['name']),
                    'desc'=>trim($dat['desc']),
                    'coupon_id'=>intval($dat['coupon_id']),
                    'gift_id'=>intval($dat['gift_id']),
                    'other_gift'=>trim($dat['other_gift'])
                ]);
            }else{
                Db::name('website_tag_manage')->insert([
                    'company_id'=>$company_id,
                    'company_type'=>$company_type,
                    'name'=>trim($dat['name']),
                    'desc'=>trim($dat['desc']),
                    'coupon_id'=>intval($dat['coupon_id']),
                    'gift_id'=>intval($dat['gift_id']),
                    'other_gift'=>trim($dat['other_gift']),
                    'createtime'=>time(),
                ]);
            }

            return json(['code'=>0,'msg'=>'保存成功']);
        }else{
            $data = ['name'=>'','desc'=>'','coupon_id'=>'','gift_id'=>'','other_gift'=>''];
            if($id>0){
                $data = Db::name('website_tag_manage')->where(['id'=>$id])->find();
            }

            $list['coupon_info'] = Db::name('centralize_coupon_list')->select();
//            'goods_status'=>0
            $list['gift_info'] = Db::connect($this->config)->name('goods')->where(['shop_id'=>$company_id,'type'=>1])->select();
            return view('index/shop_backend/save_tag',compact('company_id','company_type','id','data','list'));
        }
    }

    #删除标签
    public function del_tag(Request $request){
        $dat = input();
        $id = intval($dat['id']);
        Db::name('website_tag_manage')->where(['id'=>$id])->delete();

        return json(['code'=>0,'msg'=>'删除成功']);
    }

    #分级管理
    public function member_grade_manage(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);#0商城，1官网

        if(isset($dat['pa'])){
            $limit = $request->get('limit');
            $page = $request->get('page') - 1;
            if ($page != 0) {
                $page = $limit * $page;
            }
            $keyword = isset($dat['keywords']) ? trim($dat['keywords']) : '';
            $where =['company_id'=>$company_id];

            $count = Db::name('website_user')->whereRaw('company_id='.$company_id.' and (company_level_id<>0 or company_level_id <> null )')->count();
            $rows = Db::name('website_user')->whereRaw('company_id='.$company_id.' and (company_level_id<>0 or company_level_id <> null )')
//                ->where('title', 'like', '%'.$keyword.'%')
                ->limit($page.','.$limit)
                ->order('id desc')
                ->select();


            foreach ($rows as &$item) {
                $item['levelname'] = Db::name('website_grade_manage')->where(['id'=>$item['company_level_id']])->field('name')->find()['name'];
                $item['createtime'] = date('Y-m-d H:i',$item['createtime']);
            }

            return json(['code'=>0,'count'=>$count,'data'=>$rows]);
        }else{
            return view('index/shop_backend/member_grade_manage',compact('company_id','company_type'));
        }
    }

    #保存分级
    public function save_member_grade(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);
        $id = isset($dat['id'])?intval($dat['id']):0;

        if($request->isAjax()){
            if($id>0){
                Db::name('website_user')->where(['id'=>$id])->update([
                    'company_level_id'=>intval($dat['company_level_id']),
                ]);
            }else{
                Db::name('website_user')->where(['id'=>$dat['user_id']])->update([
                    'company_level_id'=>intval($dat['company_level_id']),
                ]);
            }
            return json(['code'=>0,'msg'=>'保存成功']);
        }else{
            $data = ['company_level_id'=>''];
            if($id>0){
                $data = Db::name('website_user')->where(['id'=>$id])->find();
            }

            $list['user'] = Db::name('website_user')->where(['company_id'=>$company_id])->select();
            $list['grade'] = Db::name('website_grade_manage')->where(['company_id'=>$company_id])->select();
            return view('index/shop_backend/save_member_grade',compact('company_id','company_type','id','data','list'));
        }
    }

    #删除分级
    public function del_member_grade(Request $request){
        $dat = input();
        $id = intval($dat['id']);
        Db::name('website_user')->where(['id'=>$id])->update(['company_level_id'=>0]);

        return json(['code'=>0,'msg'=>'删除成功']);
    }

    #标识管理
    public function member_tag_manage(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);#0商城，1官网

        if(isset($dat['pa'])){
            $limit = $request->get('limit');
            $page = $request->get('page') - 1;
            if ($page != 0) {
                $page = $limit * $page;
            }
            $keyword = isset($dat['keywords']) ? trim($dat['keywords']) : '';
            $where =['company_id'=>$company_id];

            $count = Db::name('website_user')->whereRaw('company_id='.$company_id.' and (company_tag_id<>0 or company_tag_id <> null )')->count();
            $rows = Db::name('website_user')->whereRaw('company_id='.$company_id.' and (company_tag_id<>0 or company_tag_id <> null )')
//                ->where('title', 'like', '%'.$keyword.'%')
                ->limit($page.','.$limit)
                ->order('id desc')
                ->select();


            foreach ($rows as &$item) {
                $item['tagname'] = Db::name('website_tag_manage')->where(['id'=>$item['company_tag_id']])->field('name')->find()['name'];
                $item['createtime'] = date('Y-m-d H:i',$item['createtime']);
            }

            return json(['code'=>0,'count'=>$count,'data'=>$rows]);
        }else{
            return view('index/shop_backend/member_tag_manage',compact('company_id','company_type'));
        }
    }

    #保存标识
    public function save_member_tag(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);
        $id = isset($dat['id'])?intval($dat['id']):0;

        if($request->isAjax()){
            if($id>0){
                Db::name('website_user')->where(['id'=>$id])->update([
                    'company_tag_id'=>intval($dat['company_tag_id']),
                ]);
            }else{
                Db::name('website_user')->where(['id'=>$dat['user_id']])->update([
                    'company_tag_id'=>intval($dat['company_tag_id']),
                ]);
            }
            return json(['code'=>0,'msg'=>'保存成功']);
        }else{
            $data = ['company_tag_id'=>''];
            if($id>0){
                $data = Db::name('website_user')->where(['id'=>$id])->find();
            }

            $list['user'] = Db::name('website_user')->where(['company_id'=>$company_id])->select();
            $list['tag'] = Db::name('website_tag_manage')->where(['company_id'=>$company_id])->select();
            return view('index/shop_backend/save_member_tag',compact('company_id','company_type','id','data','list'));
        }
    }

    #删除标识
    public function del_member_tag(Request $request){
        $dat = input();
        $id = intval($dat['id']);
        Db::name('website_user')->where(['id'=>$id])->update(['company_tag_id'=>0]);

        return json(['code'=>0,'msg'=>'删除成功']);
    }

    #营销面板
    public function sale_panel_manage(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);#0商城，1官网

        if($request->isAjax()){

        }else{
            return view('index/shop_backend/sale_panel_manage',compact('company_id','company_type'));
        }
    }

    #宣传活动
    public function adv_manage(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);#0商城，1官网

        if(isset($dat['pa'])){
            $limit = $request->get('limit');
            $page = $request->get('page') - 1;
            if ($page != 0) {
                $page = $limit * $page;
            }
            $keyword = isset($dat['keywords']) ? trim($dat['keywords']) : '';

            $count = Db::name('website_activity')->where(['company_id'=>$company_id,'company_type'=>$company_type])->count();
            $rows = DB::name('website_activity')
                ->where(['company_id'=>$company_id,'company_type'=>$company_type])
//                ->where('ordersn', 'like', '%'.$keyword.'%')
                ->limit($page . ',' . $limit)
                ->order('id desc')
                ->select();

            foreach ($rows as &$item) {
                $item['createtime'] = date('Y-m-d H:i:s', $item['createtime']);
            }

            return json(['code'=>0,'count'=>$count,'data'=>$rows]);
        }else{
            return view('index/shop_backend/adv_manage',compact('company_id','company_type'));
        }
    }

    #保存活动
    public function save_adv(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);#0商城，1官网
        $id = isset($dat['id'])?intval($dat['id']):0;

        if($request->isAjax()){
            if($id>0){
                Db::name('website_activity')->where(['id'=>$id])->update([
                    'name'=>trim($dat['name']),
                    'utm_campaign'=>trim($dat['utm_campaign']),
                    'utm_source'=>trim($dat['utm_source']),
                    'utm_medium'=>trim($dat['utm_medium']),
                    'utm_term'=>trim($dat['utm_term']),
                    'utm_content'=>trim($dat['utm_content']),
                    'channel'=>trim($dat['channel']),
                    'type'=>trim($dat['type'])
                ]);
            }else{
                Db::name('website_activity')->insert([
                    'company_id'=>$dat['company_id'],
                    'company_type'=>$dat['company_type'],
                    'name'=>trim($dat['name']),
                    'utm_campaign'=>trim($dat['utm_campaign']),
                    'utm_source'=>trim($dat['utm_source']),
                    'utm_medium'=>trim($dat['utm_medium']),
                    'utm_term'=>trim($dat['utm_term']),
                    'utm_content'=>trim($dat['utm_content']),
                    'channel'=>trim($dat['channel']),
                    'type'=>trim($dat['type']),
                    'createtime'=>time()
                ]);
            }
            return json(['code'=>0,'msg'=>'保存成功']);
        }else{
            $data = ['name'=>'','utm_campaign'=>'','utm_source'=>'','utm_medium'=>'','utm_term'=>'','utm_content'=>'','channel'=>'','type'=>''];
            if($id>0){
                $data = Db::name('website_activity')->where(['id'=>$id])->find();
            }
            return view('index/shop_backend/save_adv',compact('company_id','company_type','data','id'));
        }
    }

    #删除活动
    public function del_adv(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);#0商城，1官网
        $id = isset($dat['id'])?intval($dat['id']):0;

        $res = Db::name('website_activity')->where(['id'=>$id])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除活动成功']);
        }
    }

    #博客文章管理
    public function artical_manage(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);#0商城，1官网

        if(isset($dat['pa'])){
            $limit = $request->get('limit');
            $page = $request->get('page') - 1;
            if ($page != 0) {
                $page = $limit * $page;
            }
            $keyword = isset($dat['keywords']) ? trim($dat['keywords']) : '';

            $count = Db::name('website_enterprise_news_company')->where(['company_id'=>$company_id,'company_type'=>$company_type])->count();
            $rows = DB::name('website_enterprise_news_company')
                ->where(['company_id'=>$company_id,'company_type'=>$company_type])
//                ->where('ordersn', 'like', '%'.$keyword.'%')
                ->limit($page . ',' . $limit)
                ->order('id desc')
                ->select();

            foreach ($rows as &$item) {
                $item['createtime'] = date('Y-m-d H:i:s', $item['createtime']);
            }

            return json(['code'=>0,'count'=>$count,'data'=>$rows]);
        }else{
            return view('index/shop_backend/artical_manage',compact('company_id','company_type'));
        }
    }

    #保存文章
    public function save_artical(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);#0商城，1官网
        $id = isset($dat['id'])?intval($dat['id']):0;

        if($request->isAjax()){
            if($id>0){
                Db::name('website_enterprise_news_company')->where('id',$id)->update([
                    'name'=>trim($dat['name']),
                    'type'=>intval($dat['type']),
                    'origin_link'=>$dat['type']==1?trim($dat['origin_link']):'',
                    'content'=>$dat['type']==1?json_encode($dat['content'],true):'',
                    'social_id'=>$dat['type']==2?intval($dat['social_id']):'',
                    'social_link'=>$dat['type']==2?trim($dat['social_link']):'',
                    'seo_content'=>json_encode($dat['seo_content'],true),
                    'release_date'=>trim($dat['release_date']),
                ]);
            }else{
                $res=Db::name('website_enterprise_news_company')->insert([
                    'company_id'=>$company_id,
                    'company_type'=>$company_type,
                    'name'=>trim($dat['name']),
                    'type'=>intval($dat['type']),
                    'origin_link'=>$dat['type']==1?trim($dat['origin_link']):'',
                    'content'=>$dat['type']==1?json_encode($dat['content'],true):'',
                    'createtime'=>$dat['type']==1?time():'',
                    'social_id'=>$dat['type']==2?intval($dat['social_id']):'',
                    'social_link'=>$dat['type']==2?trim($dat['social_link']):'',
                    'seo_content'=>json_encode($dat['seo_content'],true),
                    'release_date'=>trim($dat['release_date']),
                ]);
            }

            return json(['code'=>0,'msg'=>'保存成功！']);
        }else{
            $data = ['name'=>'','type'=>1,'content'=>'','origin_link'=>'','social_id'=>'','social_link'=>'','seo_content'=>['title'=>'','keywords'=>'','desc'=>''],'release_date'=>''];
            if($id>0){
                $data = Db::name('website_enterprise_news_company')->where('id',$id)->find();

                if(!empty($data['content'])){
                    $data['content'] = json_decode($data['content'],true);
                }
                $data['seo_content'] = json_decode($data['seo_content'],true);
            }
            $social = Db::name('website_contact')->where(['company_id'=>$company_id,'company_type'=>$company_type])->select();
            return view('index/shop_backend/save_artical',compact('company_id','company_type','data','id','social'));
        }
    }

    #删除文章
    public function del_artical(Request $request){
        $dat = input();
        $id = isset($dat['id'])?intval($dat['id']):0;

        $res = Db::name('website_enterprise_news_company')->where(['id'=>$id])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功']);
        }
    }

    #市场管理
    public function market_manage(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);#0商城，1官网

        if(isset($dat['pa'])){
            $limit = $request->get('limit');
            $page = $request->get('page') - 1;
            if ($page != 0) {
                $page = $limit * $page;
            }
            $keyword = isset($dat['keywords']) ? trim($dat['keywords']) : '';

            $count = Db::name('website_market')->where(['company_id'=>$company_id,'company_type'=>$company_type])->count();
            $rows = DB::name('website_market')
                ->where(['company_id'=>$company_id,'company_type'=>$company_type])
//                ->where('ordersn', 'like', '%'.$keyword.'%')
                ->limit($page . ',' . $limit)
                ->order('id desc')
                ->select();

            foreach ($rows as $key=>$item) {
                $country_info = '';
                $country = explode(',',$item['country_ids']);

                foreach($country as $k=>$v){
                    $country_name = Db::name('centralize_diycountry_content')->where(['id'=>$v,'pid'=>5])->field('param2')->find()['param2'];
                    $country_info .= ','.$country_name;
                }
                $rows[$key]['country_info'] = ltrim($country_info,',');

                $rows[$key]['createtime'] = date('Y-m-d H:i:s', $item['createtime']);
            }

            return json(['code'=>0,'count'=>$count,'data'=>$rows]);
        }else{
            return view('index/shop_backend/market_manage',compact('company_id','company_type'));
        }
    }

    #保存市场
    public function save_market(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);#0商城，1官网
        $id = isset($dat['id'])?intval($dat['id']):0;

        if($request->isAjax()){
            if($id>0){
                Db::name('website_market')->where(['id'=>$id])->update([
                    'name'=>trim($dat['name']),
                    'country_ids'=>trim($dat['country_ids']),
                    'currency_id'=>intval($dat['currency_id']),
                    'tax_info'=>json_encode($dat['tax_info'],true),
                ]);
            }else{
                Db::name('website_market')->insert([
                    'company_id'=>$company_id,
                    'company_type'=>$company_type,
                    'name'=>trim($dat['name']),
                    'country_ids'=>trim($dat['country_ids']),
                    'currency_id'=>intval($dat['currency_id']),
                    'tax_info'=>json_encode($dat['tax_info'],true),
                    'createtime'=>time()
                ]);
            }
            return json(['code'=>0,'msg'=>'保存成功']);
        }else{
            $data = ['name'=>'','country_ids'=>'','currency_id'=>5,'tax_info'=>['sale_tax'=>'','customs_duties_import_tax'=>0,'tax_display'=>0,'duty_display'=>0]];
            if($id>0){
                $data = Db::name('website_market')->where(['id'=>$id])->find();
                $data['tax_info'] = json_decode($data['tax_info'],true);
            }

            $country_info = Db::name('centralize_diycountry_content')->where(['pid'=>5])->field(['id','param2'])->select();
            $country_info = json_encode($country_info,true);

            $currency_info = Db::name('centralize_currency')->select();

            return view('index/shop_backend/save_market',compact('company_id','company_type','id','data','country_info','currency_info'));
        }
    }

    #市场目录管理
    public function directory_manage(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);#0商城，1官网

        if(isset($dat['pa'])){
            $limit = $request->get('limit');
            $page = $request->get('page') - 1;
            if ($page != 0) {
                $page = $limit * $page;
            }
            $keyword = isset($dat['keywords']) ? trim($dat['keywords']) : '';

            $count = Db::name('website_market_directory')->where(['company_id'=>$company_id,'company_type'=>$company_type])->count();
            $rows = DB::name('website_market_directory')
                ->where(['company_id'=>$company_id,'company_type'=>$company_type])
//                ->where('ordersn', 'like', '%'.$keyword.'%')
                ->limit($page . ',' . $limit)
                ->order('id desc')
                ->select();

            foreach ($rows as $key=>$item) {
                $country_info = '';
                $country = explode(',',$item['market_ids']);

                foreach($country as $k=>$v){
                    $country_name = Db::name('website_market')->where(['id'=>$v])->field('name')->find()['name'];
                    $country_info .= ','.$country_name;
                }
                $rows[$key]['market_info'] = ltrim($country_info,',');

                $rows[$key]['createtime'] = date('Y-m-d H:i:s', $item['createtime']);
            }

            return json(['code'=>0,'count'=>$count,'data'=>$rows]);
        }else{
            return view('index/shop_backend/directory_manage',compact('company_id','company_type'));
        }
    }

    #保存市场目录
    public function save_directory(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);#0商城，1官网
        $id = isset($dat['id'])?intval($dat['id']):0;

        if($request->isAjax()){
            if($id>0){
                Db::name('website_market_directory')->where(['id'=>$id])->update([
                    'name'=>trim($dat['name']),
                    'country_ids'=>trim($dat['country_ids']),
                    'currency_id'=>intval($dat['currency_id']),
                    'type'=>intval($dat['type']),
                    'number'=>floatval($dat['number']),
                ]);

                foreach($dat['gid'] as $k=>$v){
                    $ishave = Db::name('website_market_directory_goods')->where(['directory_id'=>$id,'gid'=>intval($v),'sku_id'=>intval($dat['sku_id'][$k])])->find();
                    if(empty($ishave)){
                        Db::name('website_market_directory_goods')->insert([
                            'directory_id'=>$id,
                            'gid'=>intval($v),
                            'sku_id'=>intval($dat['sku_id'][$k]),
                            'price'=>floatval($dat['price'][$k])
                        ]);
                    }else{
                        Db::name('website_market_directory_goods')->where(['directory_id'=>$id,'gid'=>intval($v),'sku_id'=>intval($dat['sku_id'][$k])])->update([
                            'price'=>floatval($dat['price'][$k])
                        ]);
                    }
                }
            }else{
                $insid = Db::name('website_market_directory')->insertGetId([
                    'company_id'=>$company_id,
                    'company_type'=>$company_type,
                    'name'=>trim($dat['name']),
                    'market_ids'=>trim($dat['market_ids']),
                    'currency_id'=>intval($dat['currency_id']),
                    'type'=>intval($dat['type']),
                    'number'=>floatval($dat['number']),
                    'createtime'=>time()
                ]);

                foreach($dat['gid'] as $k=>$v){
                    Db::name('website_market_directory_goods')->insert([
                        'directory_id'=>$insid,
                        'gid'=>intval($v),
                        'sku_id'=>intval($dat['sku_id'][$k]),
                        'price'=>floatval($dat['price'][$k])
                    ]);
                }
            }
            return json(['code'=>0,'msg'=>'保存成功']);
        }else{
            $data = ['name'=>'','market_ids'=>'','currency_id'=>5,'type'=>0,'number'=>''];
            if($id>0){
                $data = Db::name('website_market_directory')->where(['id'=>$id])->find();
            }

            $market_info = Db::name('website_market')->where(['company_id'=>$company_id,'company_type'=>$company_type])->field(['id','name'])->select();
            $market_info = json_encode($market_info,true);

            $currency_info = Db::name('centralize_currency')->select();

            $goods = Db::connect($this->config)->name('goods')->where(['shop_id'=>$company_id])->field(['goods_id','goods_name','goods_image'])->order('goods_id desc')->select();
            foreach($goods as $k=>$v){
                $goods[$k]['sku_info'] = Db::connect($this->config)->name('goods_sku')->where(['goods_id'=>$v['goods_id']])->field(['sku_id','spec_names','sku_prices'])->select();
                foreach($goods[$k]['sku_info'] as $k2=>$v2){
                    $goods[$k]['sku_info'][$k2]['sku_prices'] = json_decode($v2['sku_prices'],true);
                    $goods[$k]['sku_info'][$k2]['price'] = $goods[$k]['sku_info'][$k2]['sku_prices']['price'][0];//可能要改
                }
            }

            return view('index/shop_backend/save_directory',compact('company_id','company_type','id','data','market_info','currency_info','goods'));
        }
    }

    #选购管理
    public function sorder_manage(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);#0商城，1官网
        $uid = isset($dat['uid'])?intval($dat['uid']):0;#买家id

        if(isset($dat['pa'])){
            $limit = $request->get('limit');
            $page = $request->get('page') - 1;
            if ($page != 0) {
                $page = $limit * $page;
            }
            $keyword = isset($dat['keywords']) ? trim($dat['keywords']) : '';

            #先获取企业的买家
            if($uid>0){
                $customIds = [$uid];
            }else {
                $customIds = Db::name('website_user')
                    ->where(['company_id' => $company_id])
                    ->column('id');
            }

            $count = Db::connect($this->config)->name('cart')->where('user_id','in',$customIds)->where(['is_buy'=>0,'is_show'=>0])->count();
            $rows = DB::connect($this->config)->name('cart')
                ->where('user_id','in',$customIds)
                ->where(['is_buy'=>0,'is_show'=>0])
//                ->where('ordersn', 'like', '%'.$keyword.'%')
                ->limit($page . ',' . $limit)
                ->order('cart_id desc')
                ->select();

            foreach ($rows as &$item) {
                $item['status_name'] = '';
                if($item['status']==0){
                    $item['status_name'] = '已选购';
                }elseif($item['status']==-1){
                    $item['status_name'] = '已拒绝';
                }elseif($item['status']==-2){
                    $item['status_name'] = '已退回';
                }elseif($item['status']==1){
                    $item['status_name'] = '已接受';
                }

                $wuser = Db::name('website_user')->where(['id'=>$item['user_id']])->field(['realname','nickname'])->find();
                $item['buyer_name'] = !empty($wuser['realname'])?$wuser['realname']:$wuser['nickname'];
                $item['createtime'] = date('Y-m-d H:i:s', $item['created_at']);
            }

            return json(['code'=>0,'count'=>$count,'data'=>$rows]);
        }else{
            return view('index/shop_backend/sorder_manage',compact('company_id','company_type','uid'));
        }
    }

    #选购详情
    public function sorder_detail(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);#0商城，1官网
        $id = intval($dat['id']);

        if($request->isAjax()){
            if($id>0){
                Db::connect($this->config)->name('cart')->where(['cart_id'=>$id])->update([
                   'status'=>intval($dat['status'])
                ]);
            }

            return json(['code'=>0,'msg'=>'保存成功']);
        }else{
            #选购信息
            $cart = Db::connect($this->config)->name('cart')->where(['cart_id'=>$id])->find();
            if(!empty($cart['file'])){
                $cart['file'] = json_decode($cart['file'],true);
            }
            if(!empty($cart['otherfee_content'])){
                $cart['otherfee_content'] = json_decode($cart['otherfee_content'],true);
            }
            if(!empty($cart['prefe_gift'])){
                $cart['prefe_gift'] = json_decode($cart['prefe_gift'],true);
            }
            if(!empty($cart['prefe_reduction'])){
                $cart['prefe_reduction'] = json_decode($cart['prefe_reduction'],true);
            }

            $cart['services'] = json_decode($cart['services'],true);

            #商品信息
            $cart['goods_info'] = Db::connect($this->config)->name('goods')->where(['goods_id'=>$cart['goods_id']])->find();

            #商铺信息
            if(!empty($cart['goods_info']['shop_id'])){
                $cart['shop_info'] = Db::name('website_user_company')->where(['id'=>$cart['goods_info']['shop_id']])->find();
            }
            else{
                $cart['goods_info']['other_shop'] = json_decode($cart['goods_info']['other_shop'],true);
            }

            #增值服务
            if(!empty($cart['services'])){
                $cart['services_money'] = 0;
                foreach($cart['services'] as $k2=>$v2){
                    $services = Db::connect($this->config)->name('goods_services')->where(['id'=>$v2['service_id']])->find();

                    if($services['type']==1){
                        #递增模式
                        $cart['services'][$k2]['photoRequest'] = explode('@@@',rtrim($v2['photoRequest'],'@@@'));
                        if($v2['photonum']>$services['num']){
                            $cart['services_money'] += $services['price'] + (($v2['photonum'] - 1) * $services['interval_price']);
                        }else{
                            $cart['services_money'] += $services['price'];
                        }
                    }else{
                        $cart['services_money'] += $services['price'];
                    }
                    $cart['services'][$k2]['info'] = $services;
                }
            }

            #规格信息
            $sku_info = Db::connect($this->config)->name('cart_sku')->where(['cart_id'=>$cart['cart_id'],'is_buy'=>0])->select();
            foreach($sku_info as $k2=>$v2){
                $sku_info[$k2]['info'] = Db::connect($this->config)->name('goods_sku')->where(['sku_id'=>$v2['sku_id']])->find();
                $sku_info[$k2]['info']['sku_prices'] = json_decode($sku_info[$k2]['info']['sku_prices'],true);
                #判断区间价格

                if(count($sku_info[$k2]['info']['sku_prices']['price'])>1){
                    foreach($sku_info[$k2]['info']['sku_prices']['start_num'] as $k3=>$v3){
                        if($sku_info[$k2]['info']['sku_prices']['select_end'][$k3]==1){
                            #数值
                            if($v2['goods_num']>=$v3 and $v2['goods_num']<=$sku_info[$k2]['info']['sku_prices']['end_num'][$k3]){
                                $sku_info[$k2]['info']['sku_prices']['target_key'] = $k3;
                                $sku_info[$k2]['info']['sku_prices']['target_price'] = $sku_info[$k2]['info']['sku_prices']['price'][$k3];
                                break;
                            }
                        }
                        elseif($sku_info[$k2]['info']['sku_prices']['select_end'][$k3]==2){
                            #以上
                            if($v2['goods_num']>=$v3){
                                $sku_info[$k2]['info']['sku_prices']['target_key'] = $k3;
                                $sku_info[$k2]['info']['sku_prices']['target_price'] = $sku_info[$k2]['info']['sku_prices']['price'][$k3];
                                break;
                            }
                        }
                    }
                }

                #单位

                $sku_info[$k2]['info']['sku_prices']['unit'][0] = Db::name('unit')->where(['code_value'=>$sku_info[$k2]['info']['sku_prices']['unit'][0]])->find()['code_name'];
                #币种
                $sku_info[$k2]['info']['sku_prices']['currency'][0] = Db::name('centralize_currency')->where(['id'=>$sku_info[$k2]['info']['sku_prices']['currency'][0]])->find()['currency_symbol_standard'];
            }
            $cart['sku_info'] = $sku_info;
//            dd($cart);
            return view('index/shop_backend/sorder_detail',compact('company_id','company_type','id','cart'));
        }
    }

    #订购管理
    public function porder_manage(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);#0商城，1官网
        $uid = isset($dat['uid'])?intval($dat['uid']):0;#买家id

        if(isset($dat['pa'])){
            $limit = $request->get('limit');
            $page = $request->get('page') - 1;
            if ($page != 0) {
                $page = $limit * $page;
            }
            $keyword = isset($dat['keywords']) ? trim($dat['keywords']) : '';
            #获取企业管理员下的买手id
            $buyer_info = Db::name('website_user_company')
                ->alias('a')
                ->join('website_buyer b','b.uid=a.user_id')
                ->where(['a.id'=>$company_id])
                ->field(['b.*'])
                ->find();

            #先获取企业的买家
            if($uid>0){
                $customIds = [$uid];
            }else{
                $customIds = Db::name('website_user')
                    ->where(['company_id' => $company_id])
                    ->column('id');
            }

            $where =['buyer_id'=>$buyer_info['id']];
            $count = Db::name('website_order_list')->where($where)->where('user_id','in',$customIds)->where('ordersn', 'like', '%'.$keyword.'%')->whereRaw('pay_id=0 and ( status=-2 or status=-3 or status=-9 or status=-10 or status=-11 or status=-12 )')->count();
            $rows = DB::name('website_order_list')
                ->where($where)
                ->where('user_id','in',$customIds)
                ->where('ordersn', 'like', '%'.$keyword.'%')
                ->whereRaw('pay_id=0 and ( status=-2 or status=-3 or status=-9 or status=-10 or status=-11 or status=-12 )')
                ->limit($page . ',' . $limit)
                ->order('id desc')
                ->select();

            foreach ($rows as &$item) {
                $item['status_name'] = $this->get_statusname($item['status']);
                $item['createtime'] = date('Y-m-d H:i:s', $item['createtime']);
            }

            return json(['code'=>0,'count'=>$count,'data'=>$rows]);
        }else{
            return view('index/shop_backend/porder_manage',compact('company_id','company_type','uid'));
        }
    }

    public function get_statusname($status){
        if($status==-2){
            return '待确认';
        }elseif($status==-3){
            return '申请取消订购';
        }elseif($status==-4){
            return '已取消';
        }elseif($status==-5){
            return '申请退货';
        }elseif($status==-6){
            return '已退货';
        }elseif($status==-7){
            return '申请换货';
        }elseif($status==-8){
            return '已换货';
        }elseif($status==-9){
            return '有货（无修改）';
        }elseif($status==-10){
            return '有货（有修改）';
        }elseif($status==-11){
            return '无货';
        }elseif($status==-12){
            return '拒绝订购';
        }elseif($status==0){
            return '待付款';
        }elseif($status==1){
            return '待采购';
        }elseif($status==2){
            return '已发货';
        }elseif($status==3){
            return '待验货';
        }elseif($status==4){
            return '待入库';
        }elseif($status==5){
            return '待集货';
        }elseif($status==6){
            return '待转运';
        }elseif($status==7){
            return '待签收';
        }elseif($status==8){
            return '待评价';
        }elseif($status==9){
            return '已完成';
        }
    }

    #拒绝订购
    public function cancel_porder(Request $request){
        $dat = input();

        $id = intval($dat['id']);
        $res = Db::name('website_order_list')->where(['id'=>$id])->update(['status'=>-12]);
        if($res){
            #通知管理员（待做）

            return json(['code'=>0,'msg'=>'拒绝成功！']);
        }
    }

    #订购信息
    public function porder_detail(Request $request){
        $dat = input();
        $id = intval($dat['id']);
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);#0商城，1官网

        $order = Db::name('website_order_list')->where(['id'=>$id])->find();
        $order['content'] = json_decode($order['content'],true);

        if(isset($dat['pa'])){
            $time = time();
            $user = Db::name('website_user')->where(['id'=>$order['user_id']])->find();
            $system = Db::name('centralize_system_notice')->where(['uid'=>0])->find();
            $manage_openid = $system['account'];

            if(isset($dat['status'])){
                if($dat['status']==1){
                    #有货（无修改），通知总后台
                    Db::name('website_order_list')->where(['id'=>$order['id']])->update([
                        'status'=>-9,
                    ]);
                    common_notice([
                        'openid'=>$manage_openid,
                        'phone'=>'',
                        'email'=>''
                    ],[
                        'msg'=>'清单['.$order['ordersn'].']已确认有货[无修改]，点击链接查看：https://www.gogo198.net/?s=shop/audit',
                        'opera'=>'确认有货（无修改）',
                        'url'=>'https://www.gogo198.net/?s=shop/audit'
                    ]);
                }
                elseif($dat['status']==-4){
                    #无货，通知总后台
                    Db::name('website_order_list')->where(['id'=>$order['id']])->update([
                        'status'=>-11,
                    ]);
                    common_notice([
                        'openid'=>$manage_openid,
                        'phone'=>'',
                        'email'=>''
                    ],[
                        'msg'=>'清单['.$order['ordersn'].']已确认无货，点击链接查看：https://www.gogo198.net/?s=shop/audit',
                        'opera'=>'确认无货',
                        'url'=>'https://www.gogo198.net/?s=shop/audit'
                    ]);
                }
                elseif($dat['status']==-9){
                    //有货（已修改），通知总后台
                    Db::name('website_order_list')->where(['id'=>$order['id']])->update([
                        'status'=>-10,
                    ]);
                    common_notice([
                        'openid'=>$manage_openid,
                        'phone'=>'',
                        'email'=>''
                    ],[
                        'msg'=>'清单['.$order['ordersn'].']已确认有货[有修改]，点击链接查看：https://www.gogo198.net/?s=shop/audit',
                        'opera'=>'确认有货（有修改）',
                        'url'=>'https://www.gogo198.net/?s=shop/audit'
                    ]);
                }
                elseif($dat['status']==2){
                    #库存有货，立即发货
                    Db::name('website_order_list')->where(['id'=>$order['id']])->update([
                        'accept_type'=>2,
                    ]);
                }
            }
            elseif(isset($dat['accept_type'])){
                if($dat['accept_type']==1){
                    #接受
                    if(isset($dat['have_under'])){
                        if($dat['have_under']==0){
                            if(empty($dat['company_id']) || $dat['company_id']==-1){
                                return json(['code'=>-1,'msg'=>'请选择或配置下游信息']);
                            }

                            $res = Db::name('website_order_list')->where(['id'=>$id])->update(['accept_type'=>1,'company_id'=>$dat['company_id']]);
                        }
                    }else{
                        $res = Db::name('website_order_list')->where(['id'=>$id])->update(['accept_type'=>1]);
                    }
                    if($res){
                        return json(['code'=>0,'msg'=>'接受成功！']);
                    }
                }
                elseif($dat['accept_type']==2){
                    #退订
                    $res = Db::name('website_order_list')->where(['id'=>$id])->update(['status'=>-3]);
                    if($res){
                        $order = Db::name('website_order_list')->where(['id'=>$id])->find();
                        #通知管理员
                        common_notice([
                            'openid'=>$manage_openid,
                            'phone'=>'',
                            'email'=>''
                        ],[
                            'msg'=>'订单['.$order['ordersn'].']已申请取消退订，点击链接查看：https://www.gogo198.net/?s=shop/audit',
                            'opera'=>'买手取消退订',
                            'url'=>'https://www.gogo198.net/?s=shop/audit'
                        ]);
                        return json(['code'=>0,'msg'=>'退订成功！']);
                    }
                }
            }
            elseif(isset($dat['delivery_type'])){
                #已采购，保存发货信息

                if($dat['delivery_type']==1){
                    #直邮发货
                    if(empty($dat['direct_ship']['express_id']) || empty($dat['direct_ship']['express_no']) || empty($dat['direct_ship']['address']) || empty($dat['direct_ship']['postal']) || empty($dat['direct_ship']['user_name']) || empty($dat['direct_ship']['area_mobile']) || empty($dat['direct_ship']['mobile'])){
                        return json(['code'=>-1,'msg'=>'请输入发货信息']);
                    }

                    $res = Db::name('website_order_list')->where(['id'=>$dat['id']])->update([
                        'status'=>2,
                        'accept_type'=>4,
                        'direct_ship'=>json_encode($dat['direct_ship'],true)
                    ]);

                    #6、通知买家
//                    common_notice($user,[
//                        'msg'=>'订单['.$order['ordersn'].']状态变更为[已预报]，点击链接查看：https://gather.gogo198.cn/',
//                        'opera'=>'包裹已预报',
//                        'url'=>'https://gather.gogo198.cn'
//                    ]);

                    return json(['code'=>0,'msg'=>'保存成功']);
                }
                elseif($dat['delivery_type']==2){
                    #集运发货
                    if(empty($dat['warehouse_id'])){return json(['code'=>-1,'msg'=>'请选择集货仓库']);}
                    if($dat['delivery_logistics']==1){
                        if(empty($dat['delivery_method'])){return json(['code'=>-1,'msg'=>'请选择送仓物流']);}
                        if($dat['delivery_method']==1 || $dat['delivery_method']==2){
                            if(empty($dat['express_id']) || empty($dat['express_no'])){return json(['code'=>-1,'msg'=>'请填写物流信息']);}
                        }
                        elseif($dat['delivery_method']==3){
                            if(empty($dat['inwarehouse_date']) || empty($dat['contact_name']) || empty($dat['contact_mobile'])){return json(['code'=>-1,'msg'=>'请填写入仓信息']);}
                        }
                    }

                    #1、仓库预定
                    $prediction_id = 2;#1直接转运T，2集货转运G
                    $start = strtotime(date('Y-01-01 00:00:00'));$end = strtotime(date('Y-12-31 23:59:59'));
                    $order_num = Db::name('centralize_parcel_order')->where(['prediction_id'=>$prediction_id,'user_id'=>$user['id']])->whereBetween('createtime',[$start,$end],'AND')->count();
                    $order_num = str_pad($order_num+1,3,'0',STR_PAD_LEFT);
                    $ordersn = substr($user['custom_id'],-5) . date('Y') . $order_num;
                    #多个包裹
                    $content = [
                        'user_id'    => $user['id'],
                        'agent_id'   => $user['agent_id'],
                        'ordersn'    => 'G'.$ordersn,
                        'warehouse_id'=> intval($dat['warehouse_id']),#默认仓库地址
                        'prediction_id'=> $prediction_id,
                        'task_id'    => 0,
                        'sure_prediction'=>1,
                        'createtime' => $time
                    ];
                    $orderid = Db::name('centralize_parcel_order')->insertGetId($content);

                    //1.1、任务信息处理（废弃）
                    #获取任务流水号
//                $start_num = $this->get_today_task($time);
//                if(empty($start_num)){
//                    $serial_number = 'MC'.date('ymdHis',$time).str_pad(1,2,'0',STR_PAD_LEFT);
//                }else{
//                    $serial_number = 'MC'.date('ymdHis',$time).str_pad(intval($start_num)+1,2,'0',STR_PAD_LEFT);
//                }
//                #获取任务名称
//                $task_name = $user['custom_id'].'发起任务[仓库预报]';
//                Db::name('centralize_task')->insertGetId([
//                    'user_id'=>$user['id'],
//                    'type'=>3,
//                    'task_name'=>$task_name,
//                    'task_id'=>19,
//                    'order_id'=>$orderid,
//                    'serial_number'=>$serial_number,
//                    'remark'=>'',
//                    'status'=>1,
//                    'createtime'=>$time
//                ]);

                    #2、包裹预报
                    $insert_data = [
                        'user_id'=>$user['id'],
                        'orderid'=>$orderid,
                        'gogo_oid'=>$order['id'],
                        'express_id'=>intval($dat['express_id']),
                        'express_no'=>trim($dat['express_no']),
                        #入仓信息
                        'delivery_logistics'=>intval($dat['delivery_logistics']),#1物流到仓，2上门提货
                        'delivery_method'=>intval($dat['delivery_method']),#1快递，2物流，3自送入仓
                        #物品信息
                        'inspection_method'=>1,#1拍照验货，2在线视频
                        #包装材质
                        'package'=>trim($dat['package']),
                        'package_name'=>trim($dat['package_name']),
                        #包裹毛重
                        'grosswt'=>trim($dat['grosswt']),
                        #包裹体积
                        'volumn'=>trim($dat['long']).'*'.trim($dat['width']).'*'.trim($dat['height']),
                        #状态
                        'status2'=>0,#直接转运或集货转运都要先签收入库
                        #创建时间
                        'createtime'=>$time
                    ];
                    $package_id = Db::name('centralize_parcel_order_package')->insertGetId($insert_data);

                    #3、预报商品
                    $brand_name = '';
//                if($dat['goods_brand']==2){
//                    #仿牌
//                    $brand_name=$dat['goods_brand_name2'];
//                }
//                elseif($dat['goods_brand']==3){
//                    #普通品牌
//                    $brand_name=$dat['goods_brand_name'];
//                }
//                elseif($dat['goods_brand']==4){
//                    #奢侈品牌
//                    $brand_name=$dat['goods_brand_name3'];
//                }
                    foreach($dat['goods_desc'] as $k=>$v){
                        #商品规格信息
                        $sku_info = Db::connect($this->config)->name('goods_sku')->where(['sku_id'=>$dat['goods_sku'][$k]])->find();
                        if(!empty($sku_info)){
                            $sku_info['sku_prices'] = json_decode($sku_info['sku_prices'],true);
                            $dat['goods_currency'][$k] = $sku_info['sku_prices']['currency'][0];
                            $usd_to_cny = Db::name('centralize_currency')->where(['id'=>60])->field('to_cny_rate')->find()['to_cny_rate'];

                            #区间价格
                            if(count($sku_info['sku_prices']['start_num'])==1){
                                #单个区间价格
                                $dat['goods_price'][$k] = sprintf('%.2f',$dat['goods_num'][$k]*$sku_info['sku_prices']['price'][0]);
                            }else{
                                #多个区间价格
                                foreach($sku_info['sku_prices']['start_num'] as $k2=>$v2){
                                    if($v2<=$dat['goods_num'][$k] && $dat['goods_num'][$k]<=$sku_info['sku_prices']['end_num'][$k2]){
                                        $dat['goods_price'][$k] = sprintf('%.2f',$dat['goods_num']*$sku_info['sku_prices']['price'][$k2]);
                                    }
                                }
                            }
                            $dat['goods_usdprice'][$k] = sprintf('%.2f',$dat['goods_price'][$k] / $usd_to_cny);
                        }

                        Db::name('centralize_parcel_order_goods')->insert([
                            'user_id'=>$user['id'],
                            'orderid'=>$orderid,
                            'package_id'=>$package_id,
                            #物品属性
                            'valueid'=>isset($dat['valueid'][$k])?$dat['valueid'][$k]:'',
                            #物品描述
                            'good_desc'=>trim($v),
                            #物品数量
                            'good_num'=>isset($dat['goods_num'][$k])?$dat['goods_num'][$k]:'',
                            #物品单位
                            'good_unit'=>isset($dat['goods_unit'][$k])?$dat['goods_unit'][$k]:'',
                            #物品币种
                            'good_currency'=>isset($dat['goods_currency'][$k])?$dat['goods_currency'][$k]:'',
                            #物品金额
                            'good_price'=>isset($dat['goods_price'][$k])?$dat['goods_price'][$k]:'',
                            #物品金额（等值美元）
                            'goods_usdprice'=>isset($dat['goods_usdprice'][$k])?$dat['goods_usdprice'][$k]:'',
                            #物品包装
                            'good_package'=>isset($dat['goods_package'])?$dat['goods_package']:'',
                            #物品品牌类型
                            'brand_type'=>isset($dat['goods_brand'][$k])?$dat['goods_brand'][$k]:'',
                            'brand_name'=>$brand_name,
                            #物品备注
                            'good_remark'=>isset($dat['goods_remark'][$k])?$dat['goods_remark'][$k]:'',
                            #创建时间
                            'createtime'=>$time
                        ]);
                    }

                    #插入物品属性指定id下的标签
//                if(!empty($dat['goods_desc'])){
//                    $gvalue = Db::name('centralize_gvalue_list')->where(['id'=>$dat['valueid']])->field('keywords')->find();
//                    $gvalue['keywords'] = $gvalue['keywords'].'、'.trim($dat['goods_desc']);
//                    Db::name('centralize_gvalue_list')->where(['id'=>$dat['valueid']])->update(['keywords'=>$gvalue['keywords']]);
//                }

                    #4、包裹订单
                    Db::name('centralize_order_fee_log')->insert([
                        'type'=>1,#1国内订单，2集运订单
                        'ordersn'=>'G'.date('YmdHis'),
                        'user_id'=>$user['id'],
                        'orderid'=>$orderid,
                        #包裹id
                        'good_id'=>$package_id,
                        'express_no'=>trim($dat['express_no']),
                        'service_status'=>1,
                        'order_status'=>0,
                        'createtime'=>$time
                    ]);

                    #5、修改购购网订单表
                    Db::name('website_order_list')->where(['id'=>$id])->update(['status'=>2,'accept_type'=>3]);

                    #6、通知买家
                    common_notice($user,[
                        'page'=>'',
                        'msg'=>'订单['.$order['ordersn'].']状态变更为[已预报]，点击链接查看：https://gather.gogo198.cn/?s=gather/package_info&id='.$package_id.'&process1=19&process2=21&process3=undefined',
                        'opera'=>'包裹已预报',
                        'url'=>'https://gather.gogo198.cn/?s=gather/package_info&id='.$package_id.'&process1=19&process2=21&process3=undefined'
                    ]);

                    return json(['code'=>0,'msg'=>'已生成发货预报并通知客户']);
                }
            }

            return json(['code'=>0,'msg'=>'保存成功']);
        }else{

            if(!empty($order['edit_address'])){
                $address = json_decode($order['edit_address'],true);
            }
            else {
                #收货信息
                if(isset($order['content']['address_id'])){
                    $address = Db::name('centralize_user_address')->where(['id' => $order['content']['address_id']])->find();
                    $address['postal_code'] = json_decode($address['postal_code'], true);
                    $address['postal'] = '';
                    foreach ($address['postal_code'] as $k => $v) {
                        $address['postal'] .= $v;
                    }
                    $country = Db::name('centralize_diycountry_content')->where(['id' => $address['country_id']])->find();#国
                    $province = '';
                    if (!empty($address['province'])) {
                        $province = Db::name('centralize_adminstrative_area')->where(['id' => $address['province']])->find()['code_name'];#省
                    }
                    $city = '';
                    if (!empty($address['city'])) {
                        $city = Db::name('centralize_adminstrative_area')->where(['id' => $address['city']])->find()['code_name'];#市
                    }
                    $area_info = '';
                    $area_info2 = '';
                    $area_info3 = '';
                    $area_info4 = '';
                    if (!empty($address['area'])) {
                        $area_info = Db::name('centralize_adminstrative_area')->where(['id' => $address['area']])->find()['code_name'];#区1
                    }
                    if (!empty($address['area2'])) {
                        $area_info2 = Db::name('centralize_adminstrative_area')->where(['id' => $address['area2']])->find()['code_name'];#区2
                    }
                    if (!empty($address['area3'])) {
                        $area_info3 = Db::name('centralize_adminstrative_area')->where(['id' => $address['area3']])->find()['code_name'];#区3
                    }
                    if (!empty($address['area4'])) {
                        $area_info4 = Db::name('centralize_adminstrative_area')->where(['id' => $address['area4']])->find()['code_name'];#区4
                    }

                    $address['address2'] = json_decode($address['address2'], true);
                    $address2 = '';
                    if (!empty($address['address2'])) {
                        foreach ($address['address2'] as $k => $v) {
                            $address2 .= $v;
                        }
                    }

                    $address['address'] = $country['param2'] . $province . $city . $area_info . $area_info2 . $area_info3 . $area_info4 . $address['address1'] . $address2;
                }else{
                    $address['postal'] = '';
                    $address['address2'] = '';
                    $address['address'] = '';
                }

            }
            $order['status_name'] = $this->get_statusname($order['status']);

            $express = Db::name('centralize_diycountry_content')->where(['pid'=>6])->select();
            $unit = Db::name('unit')->select();
            $currency = Db::name('centralize_currency')->select();
            $package = Db::name('packing_type')->select();
//            $value = json_encode($this->menu2(2),true);
            $brand = Db::name('centralize_diycountry_content')->where(['pid'=>8])->select();
            #订购清单信息
            foreach($order['content']['goods_info'] as $k=>$v){
                $order['content']['goods_info'][$k]['goods_info'] = Db::connect($this->config)->name('goods')->where(['goods_id'=>$v['good_id']])->find();
                foreach($v['sku_info'] as $k2=>$v2){
                    $order['content']['goods_info'][$k]['sku_info'][$k2]['sku_info'] =  Db::connect($this->config)->name('goods_sku')->where(['sku_id'=>$v2['sku_id']])->find();
                }
            }
            $goods = $order['content']['goods_info'];

            $list = [];
            if($order['status']==1 && $order['accept_type']==0){
                #接受采购,判断有无下级功能
                $ishave = getUnFun(194,$company_id);
                $list['have_under'] = $ishave;
                if($ishave==0){
                    #获取下游
                    $list['under_merchant'] = Db::name('website_user_company')->whereRaw('FIND_IN_SET('.$company_id.',pids)')->where(['status'=>0])->select();
                }
            }

            if($order['accept_type']==2){
                #库存有货，订单发货
                $list['express_company'] = Db::name('centralize_diycountry_content')->where(['pid'=>6])->select();
                #仓库
                $list['warehouse'] = Db::name('centralize_warehouse_list')->where(['uid'=>0,'status'=>0])->select();
                $warehouse = Db::name('centralize_warehouse_list')->where(['uid'=>$company_id,'status'=>0])->select();
                $list['warehouse'] = array_merge($list['warehouse'],$warehouse);
                #查找下级仓库和平台仓库
                $next_child = Db::name('website_user_company')->whereRaw('FIND_IN_SET('.$company_id.',pids)')->select();
                foreach($next_child as $k=>$v){
                    $warehouse = Db::name('centralize_warehouse_list')->where(['uid'=>$v['id'],'status'=>0])->select();
                    if(!empty($warehouse)){
                        $list['warehouse'] = array_merge($list['warehouse'],$warehouse);
                    }
                }

                #单位
                $list['unit'] = Db::name('unit')->select();
                #物品属性
                $list['value'] = json_encode($this->valuemenu2(2),true);
            }

            return view('index/shop_backend/porder_detail',compact('order','address','goods','id','express','unit','currency','package','value','brand','company_id','company_type','list'));
        }
    }

    #菜单栏目-xmselect树形结构
    public function valuemenu2($typ=0){
        $menu = Db::name('centralize_gvalue_list')->where(['pid'=>0])->field(['id','name','country','channel','desc','keywords'])->select();
        foreach($menu as $k=>$v){
            $menu[$k]['name'] = $v['name'];
            $menu[$k]['value'] = $v['id'];
            $menu[$k]['children'] = $this->getDownValueMenu2($v['id']);
        }
        return $menu;
    }

    #下级菜单
    public function getDownValueMenu2($id){
        $cmenu = Db::name('centralize_gvalue_list')->where(['pid'=>$id])->field(['id','name','country','channel','desc','keywords'])->select();
        foreach($cmenu as $k=>$v){
            $cmenu[$k]['name'] = $v['name'];
            $cmenu[$k]['value'] = $v['id'];
            $cmenu[$k]['children'] = Db::name('centralize_gvalue_list')->where(['pid'=>$v['id']])->field(['id','name','country','channel','desc','keywords'])->select();
            foreach($cmenu[$k]['children'] as $k2=>$v2){
                $cmenu[$k]['children'][$k2]['name'] = $v2['name'];
                $cmenu[$k]['children'][$k2]['value'] = $v2['id'];
                $cmenu[$k]['children'][$k2]['children'] = Db::name('centralize_gvalue_list')->where(['pid'=>$v2['id']])->field(['id','name','country','channel','desc','keywords'])->select();
                foreach($cmenu[$k]['children'][$k2]['children'] as $k3=>$v3){
                    $cmenu[$k]['children'][$k2]['children'][$k3]['name'] = $v3['name'];
                    $cmenu[$k]['children'][$k2]['children'][$k3]['value'] = $v3['id'];
                    $cmenu[$k]['children'][$k2]['children'][$k3]['children'] = Db::name('centralize_gvalue_list')->where(['pid'=>$v3['id']])->field(['id','name','country','channel','desc','keywords'])->select();
                    foreach($cmenu[$k]['children'][$k2]['children'][$k3]['children'] as $k4=>$v4){
                        $cmenu[$k]['children'][$k2]['children'][$k3]['children'][$k4]['name'] = $v4['name'];
                        $cmenu[$k]['children'][$k2]['children'][$k3]['children'][$k4]['value'] = $v4['id'];
                    }
                }
            }
        }
        return $cmenu;
    }

    #查看物品属性信息详情
    public function value_introduce(Request $request){
        $data = input();
        $id = $data['id'];

        $info = Db::name('centralize_gvalue_list')->where(['id'=>$id])->find();
        $info['country'] = explode(',',$info['country']);
        foreach($info['country'] as $k=>$v){
            $info['country'][$k] = Db::name('centralize_diycountry_content')->where(['id'=>$v])->find();
        }
        $info['channel'] = explode(',',$info['channel']);
        $channel = '';
        foreach($info['channel'] as $k=>$v){
            if($v==1){
                $channel .= '国际快递，';
            }elseif($v==2){
                $channel .= '国际邮政，';
            }elseif($v==3){
                $channel .= '国际专线，';
            }
        }
        $info['channel'] = rtrim($channel,',');

        return view('index/shop_backend/value_introduce',compact('id','info','website'));
    }

    #订购地址修改
    public function porder_addr(Request $request){
        $dat = input();
        $id = intval($dat['id']);
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);#0商城，1官网

        if(isset($dat['pa'])){
            Db::name('website_order_list')->where(['id'=>$id])->update([
                'edit_address'=>json_encode(['address'=>$dat['address'],'postal'=>$dat['postal'],'user_name'=>$dat['user_name'],'area_mobile'=>$dat['area_mobile'],'mobile'=>$dat['mobile'],'mobile2'=>$dat['mobile2'],'email'=>$dat['email']],true)
            ]);
            return json(['code'=>0,'msg'=>'修改成功']);
        }else{
            $order = Db::name('website_order_list')->where(['id'=>$id])->find();
            $order['content'] = json_decode($order['content'],true);
            if(!empty($order['edit_address'])){
                $address = json_decode($order['edit_address'],true);
            }
            else{
                #收货地址
                $address = Db::name('centralize_user_address')->where(['id'=>$order['content']['address_id']])->find();
                $address['postal_code'] = json_decode($address['postal_code'],true);
                $address['postal'] = '';
                foreach($address['postal_code'] as $k=>$v){
                    $address['postal'] .= $v;
                }
                $country = Db::name('centralize_diycountry_content')->where(['id'=>$address['country_id']])->find();#国
                $province = '';
                if(!empty($address['province'])){
                    $province = Db::name('centralize_adminstrative_area')->where(['id'=>$address['province']])->find()['code_name'];#省
                }
                $city = '';
                if(!empty($address['city'])){
                    $city = Db::name('centralize_adminstrative_area')->where(['id'=>$address['city']])->find()['code_name'];#市
                }
                $area_info = '';$area_info2 = '';$area_info3 = '';$area_info4 = '';
                if(!empty($address['area'])){
                    $area_info = Db::name('centralize_adminstrative_area')->where(['id'=>$address['area']])->find()['code_name'];#区1
                }
                if(!empty($address['area2'])) {
                    $area_info2 = Db::name('centralize_adminstrative_area')->where(['id' => $address['area2']])->find()['code_name'];#区2
                }
                if(!empty($address['area3'])) {
                    $area_info3 = Db::name('centralize_adminstrative_area')->where(['id' => $address['area3']])->find()['code_name'];#区3
                }
                if(!empty($address['area4'])) {
                    $area_info4 = Db::name('centralize_adminstrative_area')->where(['id' => $address['area4']])->find()['code_name'];#区4
                }

                $address['address2'] = json_decode($address['address2'],true);
                $address2 = '';
                if(!empty($address['address2'])){
                    foreach($address['address2'] as $k=>$v){
                        $address2 .= $v;
                    }
                }

                $address['address'] = $country['param2'].$province.$city.$area_info.$area_info2.$area_info3.$area_info4.$address['address1'].$address2;
            }

            return view('index/shop_backend/porder_addr',compact('id','address','company_id','company_type'));
        }
    }

    #订购内容修改
    public function porder_edit(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);#0商城，1官网

        if(isset($dat['pa'])){
//            dd($dat);
            $id = intval($dat['id']);
            $gid = intval($dat['gid']);
            $gkey = intval($dat['gkey']);
            $skey = intval($dat['skey']);
            $sku_id = intval($dat['sku_id']);
            $cart_id = intval($dat['cart_id']);

            $edit_type = intval($dat['edit_type']);

            $order = Db::name('website_order_list')->where(['id'=>$id])->find();
            $order['content'] = json_decode($order['content'],true);
//            dd($order['content']);
            Db::startTrans();
            try {
                if($edit_type==1){
                    #商品规格参数

                    #旧的规格与商品价值
                    $order['content']['goods_info'][$gkey]['sku_info'][$skey]['odd_skuid']=$order['content']['goods_info'][$gkey]['sku_info'][$skey]['sku_id'];
                    $order['content']['goods_info'][$gkey]['sku_info'][$skey]['odd_price']=$order['content']['goods_info'][$gkey]['sku_info'][$skey]['price'];

                    #新的规格与商品价值
                    $new_skuid = intval($dat['new_skuid']);
                    $order['content']['goods_info'][$gkey]['sku_info'][$skey]['sku_id']=$new_skuid;

                    #新的规格参数
                    $new_skuinfo = Db::connect($this->config)->name('goods_sku')->where(['sku_id'=>$new_skuid])->find();
                    $new_skuinfo['sku_prices'] = json_decode($new_skuinfo['sku_prices'],true);
                    #商品信息
                    $goods = Db::connect($this->config)->name('goods')->where(['goods_id'=>$order['content']['goods_info'][$gkey]['good_id']])->find();
                    $goods_num = $order['content']['goods_info'][$gkey]['sku_info'][$skey]['goods_num'];
                    #计算新的规格参数下的商品单价*购买数量
                    if($goods['shop_id']>0){
                        #自营店铺
                        foreach($new_skuinfo['sku_prices']['start_num'] as $k=>$v){
                            if($new_skuinfo['sku_prices']['select_end'][$k]==1){
                                #区间
                                if($v<=$goods_num && $goods_num<=$new_skuinfo['sku_prices']['end_num']){
                                    $order['content']['goods_info'][$gkey]['sku_info'][$skey]['price'] = number_format($new_skuinfo['sku_prices']['price'][$k] * $goods_num,2);
                                }
                            }
                            elseif($new_skuinfo['sku_prices']['select_end'][$k]==2){
                                #以上
                                if($v<=$goods_num){
                                    $order['content']['goods_info'][$gkey]['sku_info'][$skey]['price'] = number_format($new_skuinfo['sku_prices']['price'][$k] * $goods_num,2);
                                }
                            }
                        }
                    }
                    elseif($goods['shop_id']==0){
                        #接口店铺
                        $order['content']['goods_info'][$gkey]['sku_info'][$skey]['price'] = number_format($new_skuinfo['sku_prices']['price'][0] * $goods_num,2);
                    }

                    #修改买家的选购清单
                    Db::connect($this->config)->name('cart_sku')->where(['sku_id'=>$sku_id,'cart_id'=>$cart_id])->update([
                        'sku_id'=>$new_skuid,
                        'attr_id'=>str_replace('|','_',$new_skuinfo['spec_vids']),
                        'spec_id'=>str_replace('|','_',$new_skuinfo['spec_ids']),
                        'price'=>$order['content']['goods_info'][$gkey]['sku_info'][$skey]['price']
                    ]);

                }
                elseif($edit_type==2){
                    #费用项目价格

                    #减免金额
                    if(isset($dat['new_reduction'])){
                        if($dat['new_reduction']==''){
                            $dat['new_reduction'] = 0;
                        }
                        $order['content']['goods_info'][$gkey]['odd_reduction_money'] = $order['content']['goods_info'][$gkey]['reduction_money'];
                        $order['content']['goods_info'][$gkey]['reduction_money'] = floatval($dat['new_reduction']);
                    }

                    #优惠随赠
                    if(isset($dat['new_gift'])){
                        if($dat['new_gift']==''){
                            $dat['new_gift'] = 0;
                        }
                        $order['content']['goods_info'][$gkey]['odd_gift_money'] = $order['content']['goods_info'][$gkey]['gift_money'];
                        $order['content']['goods_info'][$gkey]['gift_money'] = floatval($dat['new_gift']);
                    }

                    #其他费用
                    if(isset($dat['new_otherfee_total'])){
                        if($dat['new_otherfee_total']==''){
                            $dat['new_otherfee_total'] = 0;
                        }
                        $order['content']['goods_info'][$gkey]['odd_otherfee_total'] = $order['content']['goods_info'][$gkey]['otherfee_total'];
                        $order['content']['goods_info'][$gkey]['otherfee_total'] = floatval($dat['new_otherfee_total']);
                    }

                    #服务费用
                    if(isset($dat['new_services_money'])){
                        if($dat['new_services_money']==''){
                            $dat['new_services_money'] = 0;
                        }

                        $order['content']['goods_info'][$gkey]['services'] = json_decode($order['content']['goods_info'][$gkey]['services'],true);
                        $services_money = 0;
                        foreach($order['content']['goods_info'][$gkey]['services'] as $k2=>$v2){
                            $services = Db::connect($this->config)->name('goods_services')->where(['id'=>$v2['service_id']])->find();
                            if($v2['service_id']==1){
                                if($v2['photonum']>1){
                                    $services_money += $services['price'] + (($v2['photonum'] - 1) * $services['interval_price']);
                                }
                            }else{
                                $services_money += $services['price'];
                            }
                        }

                        $order['content']['goods_info'][$gkey]['odd_services_money'] = $services_money;
                        #新增“修改的服务金额”字段
                        $order['content']['goods_info'][$gkey]['services_money'] = floatval($dat['new_services_money']);
                        $order['content']['goods_info'][$gkey]['services'] = json_encode($order['content']['goods_info'][$gkey]['services'],true);
                    }
                }
                elseif($edit_type==3){
                    #商品数量
                    $new_num = intval($dat['new_num']);
                    if($new_num==0 || $new_num==''){
                        return json(['code'=>-1,'msg'=>'请输入新的商品数量']);
                    }
                    if($new_num==$order['content']['goods_info'][$gkey]['sku_info'][$skey]['goods_num']){
                        return json(['code'=>-1,'msg'=>'新的商品数量不能与之相同']);
                    }
                    $order['content']['goods_info'][$gkey]['sku_info'][$skey]['odd_goods_num']=$order['content']['goods_info'][$gkey]['sku_info'][$skey]['goods_num'];
                    $order['content']['goods_info'][$gkey]['sku_info'][$skey]['goods_num']=$new_num;

                    #当前规格
                    $new_skuinfo = Db::connect($this->config)->name('goods_sku')->where(['sku_id'=>$order['content']['goods_info'][$gkey]['sku_info'][$skey]['sku_id']])->find();
                    $new_skuinfo['sku_prices'] = json_decode($new_skuinfo['sku_prices'],true);
                    #商品信息
                    $goods = Db::connect($this->config)->name('goods')->where(['goods_id'=>$order['content']['goods_info'][$gkey]['good_id']])->find();
                    $goods_num = $order['content']['goods_info'][$gkey]['sku_info'][$skey]['goods_num'];
                    #计算新的规格参数下的商品单价*购买数量
                    if($goods['shop_id']>0){
                        #自营店铺
                        foreach($new_skuinfo['sku_prices']['start_num'] as $k=>$v){
                            if($new_skuinfo['sku_prices']['select_end'][$k]==1){
                                #区间
                                if($v<=$goods_num && $goods_num<=$new_skuinfo['sku_prices']['end_num']){
                                    $order['content']['goods_info'][$gkey]['sku_info'][$skey]['price'] = number_format($new_skuinfo['sku_prices']['price'][$k] * $goods_num,2);
                                }
                            }
                            elseif($new_skuinfo['sku_prices']['select_end'][$k]==2){
                                #以上
                                if($v<=$goods_num){
                                    $order['content']['goods_info'][$gkey]['sku_info'][$skey]['price'] = number_format($new_skuinfo['sku_prices']['price'][$k] * $goods_num,2);
                                }
                            }
                        }
                    }
                    elseif($goods['shop_id']==0){
                        #接口店铺
                        $order['content']['goods_info'][$gkey]['sku_info'][$skey]['price'] = number_format($new_skuinfo['sku_prices']['price'][0] * $goods_num,2);
                    }

                    #修改买家的选购清单
                    Db::connect($this->config)->name('cart_sku')->where(['sku_id'=>$sku_id,'cart_id'=>$cart_id])->update([
                        'goods_num'=>$order['content']['goods_info'][$gkey]['sku_info'][$skey]['goods_num'],
                        'price'=>$order['content']['goods_info'][$gkey]['sku_info'][$skey]['price'],
                    ]);
                }

                #重新计算订购清单价格
                $true_price = 0;
                foreach($order['content']['goods_info'] as $k=>$v){
                    #是否拿已修改的更多服务金额
                    if(isset($order['content']['goods_info'][$k]['services_money'])){
                        $services_money = $order['content']['goods_info'][$k]['services_money'];
                    }else{
                        $order['content']['goods_info'][$k]['services'] = json_decode($order['content']['goods_info'][$k]['services'],true);
                        $services_money = 0;
                        foreach($order['content']['goods_info'][$k]['services'] as $k2=>$v2){
                            $services = Db::connect($this->config)->name('goods_services')->where(['id'=>$v2['service_id']])->find();
                            if($v2['service_id']==1){
                                if($v2['photonum']>1){
                                    $services_money += $services['price'] + (($v2['photonum'] - 1) * $services['interval_price']);
                                }
                            }else{
                                $services_money += $services['price'];
                            }
                        }
                        $order['content']['goods_info'][$k]['services'] = json_encode($order['content']['goods_info'][$k]['services'],true);
                    }


                    $price = 0;
                    foreach($v['sku_info'] as $k2=>$v2){
                        $price += floatval($v2['price']);
                    }
                    $true_price += number_format($price + floatval($v['otherfee_total']) + $services_money - floatval($v['reduction_money']) - floatval($v['gift_money']),2);
                }

                //修改购物清单价格
                $odd_money = $order['true_money'];
                Db::name('website_order_list')->where(['id'=>$id])->update([
                    'odd_money'=>$odd_money,
                    'true_money'=>$true_price,
                    'content'=>json_encode($order['content'],true),
                ]);
                Db::commit();
                return json(['code' => 0, 'msg' => '修改成功']);
            } catch (\Exception $e) {
                Db::rollback();
                return json(['code' => -1, 'msg' => '操作失败：'.$e->getMessage()]);
            }
        }else{

            $is_manage = intval($dat['is_manage']);
            $arr = explode(',',$dat['arr']);
            $id = intval($arr[0]);
            $gid = intval($arr[1]);
            $gkey = intval($arr[2]);
            $skey = intval($arr[3])-1;
            $sku_id = intval($arr[4]);
            $cart_id = intval($arr[5]);

            $order = Db::name('website_order_list')->where(['id'=>$id])->find();
            $order['content'] = json_decode($order['content'],true);
//            foreach($order['content']['goods_info'] as $k=>$v){
//                $order['content']['goods_info'][$k]['services'] = json_encode($order['content']['goods_info'][$k]['services'],true);
//            }
//            Db::name('website_order_list')->where(['id'=>$id])->update([
//                'content'=>json_encode($order['content'],true)
//            ]);
//            dd($order['content']);

            $sku_data = $order['content']['goods_info'][$gkey]['sku_info'][$skey];
            $goods_data = $order['content']['goods_info'][$gkey];

            #商品规格
            $origin_skuinfo = Db::connect($this->config)->name('goods_sku')->where(['sku_id'=>$sku_data['sku_id']])->find()['spec_names'];
            $other_skuinfo = [];
            if(!empty($origin_skuinfo)){
                $other_skuinfo = Db::connect($this->config)->name('goods_sku')->where(['goods_id'=>$goods_data['good_id']])->select();
            }
//            dd($other_skuinfo);
            #费用项目
            #是否拿已修改的更多服务金额
            if(isset($goods_data['services_money'])){
                $services_money = $goods_data['services_money'];
            }else{
                $goods_data['services'] = json_decode($goods_data['services'],true);
                $services_money = 0;
                foreach($goods_data['services'] as $k2=>$v2){
                    $services = Db::connect($this->config)->name('goods_services')->where(['id'=>$v2['service_id']])->find();
                    if($v2['service_id']==1){
                        $goods_data['services'][$k2]['photoRequest'] = explode('@@@',rtrim($v2['photoRequest'],'@@@'));
                        if($v2['photonum']>1){
                            $services_money += $services['price'] + (($v2['photonum'] - 1) * $services['interval_price']);
                        }
                    }else{
                        $services_money += $services['price'];
                    }
                }
            }

            $origin_services = ['reduction_money'=>$goods_data['reduction_money'],'gift_money'=>$goods_data['gift_money'],'otherfee_total'=>$goods_data['otherfee_total'],'otherfee_currency'=>$goods_data['otherfee_currency'],'services_money'=>$services_money];

            return view('index/shop_backend/porder_edit',compact('id','gid','gkey','skey','sku_id','cart_id','sku_data','origin_skuinfo','other_skuinfo','origin_services','is_manage','company_id','company_type'));
        }
    }

    #下游配置
    public function save_next_merchant(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);#0商城，1官网

        if($request->isAjax()){
            if($dat['pa']==1){
                #配置下游
                $company = trim($dat['company_info']);

                #查询是否存在
                $ishave = Db::name('website_user_company')->where('company="'.$company.'" and id <> '.$company_id)->find();
                if(empty($ishave)){
                    return json(['code'=>-1,'msg'=>'配置失败，该下游企业不存在']);
                }else{
                    #判断该企业是否已是当前企业的下级
                    if(!empty($ishave['pids'])){
                        if(in_array($company_id, explode(',',$ishave['pids']))) {
                            return json(['code'=>-1,'msg'=>'配置失败，该企业是贵司的下级']);
                        }
                    }

                    #判断该企业是否已是当前企业的上级
                    $my_company = Db::name('website_user_company')->where(['id'=>$company_id])->find();
                    if(!empty($my_company['pids'])){
                        if(in_array($ishave['id'], explode(',',$my_company['pids']))) {
                            return json(['code'=>-1,'msg'=>'配置失败，该企业是贵司的上级']);
                        }
                    }

                    $ishave2 = Db::name('website_company_confirm')->where(['parent_cid'=>$company_id,'child_cid'=>$ishave['id']])->find();
                    if(empty($ishave2)){
                        Db::name('website_company_confirm')->insert([
                            'parent_cid'=>$company_id,
                            'child_cid'=>$ishave['id'],
                            'status'=>0,
                            'createtime'=>time()
                        ]);

                        return json(['code'=>0,'msg'=>'配置成功，请等待确认']);
                    }else{
                        return json(['code'=>-1,'msg'=>'配置失败，该企业已被贵司操作过']);
                    }
                }
            }
            elseif($dat['pa']==2){
                #模糊查询下游
                $val = trim($dat['val']);

                $my_company = Db::name('website_user_company')->where(['id'=>$company_id])->find();
                $company = [];
                if(empty($my_company['pids'])){
                    #当前企业上级为空
                    $company = Db::name('website_user_company')->whereRaw('id<>'.$company_id.' and status=0 and company like "%'.$val.'%"')->select();
                }else{
                    #当前企业有上级（自己的企业、已有下级和已有上级不显示，企业状态为“正常”）
                    $ids = rtrim($my_company['pids'],',');
                    $ids = explode(',',$ids);

                    $company = Db::name('website_user_company')->whereRaw('id<>'.$company_id.' and status=0 and company like "%'.$val.'%"')->where('id','not in',$ids)->select();
                }

                if(empty($company)){
                    $website_user = Db::name('website_user')->where(['custom_id'=>$val])->find();
                    if(!empty($website_user)){
                        $company = Db::name('website_user_company')->whereRaw('user_id='.$website_user['id'].' and id<>'.$company_id.' and status=0')->select();
                    }
                }

                return json(['code'=>0,'list'=>$company]);
            }
        }else{
//            $my_company = Db::name('website_user_company')->where(['id'=>session('manage_person.company_id')])->find();
//            $company = [];
//            if(empty($my_company['pids'])){
//                $company = Db::name('website_user_company')->whereRaw('id<>'.session('manage_person.company_id').' and status=0')->select();
//            }else{
//                $ids = rtrim($my_company['pids'],',');
//                $ids = explode(',',$ids);
//
//                $company = Db::name('website_user_company')->whereRaw('id<>'.session('manage_person.company_id').' and status=0')->where('id','not in',$ids)->select();
//            }

            return view('index/shop_backend/save_next_merchant',compact('company_id','company_type'));
        }
    }

    #支付单管理
    public function order_manage(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);#0商城，1官网
        $uid = isset($dat['uid'])?intval($dat['uid']):0;#买家id

        if(isset($dat['pa'])){
            $limit = $request->get('limit');
            $page = $request->get('page') - 1;
            if ($page != 0) {
                $page = $limit * $page;
            }
            $keyword = isset($dat['keywords']) ? trim($dat['keywords']) : '';
            #获取企业管理员下的买手id
            $buyer_info = Db::name('website_user_company')
                ->alias('a')
                ->join('website_buyer b','b.uid=a.user_id')
                ->where(['a.id'=>$company_id])
                ->field(['b.*'])
                ->find();

            if($uid>0){
                $customIds = [$uid];
            }else{
                $customIds = Db::name('website_user')
                    ->where(['company_id' => $company_id])
                    ->column('id');
            }

            $where =['buyer_id'=>$buyer_info['id']];
            $count = Db::name('website_order_list')->where($where)->where('user_id','in',$customIds)->where('ordersn', 'like', '%'.$keyword.'%')->whereRaw('accept_type=0 and pay_id<>0 and ( status=1 or status=-3 or status=-9 or status=-10 or status=-11 )')->count();
            $rows = DB::name('website_order_list')
                ->where($where)
                ->where('user_id','in',$customIds)
                ->where('ordersn', 'like', '%'.$keyword.'%')
                ->whereRaw('accept_type=0 and pay_id<>0 and ( status=1 or status=-3 or status=-9 or status=-10 or status=-11 )')
                ->limit($page . ',' . $limit)
                ->order('id desc')
                ->select();

            foreach ($rows as &$item) {
                $item['status_name'] = $this->get_statusname($item['status']);
                $item['createtime'] = date('Y-m-d H:i:s', $item['createtime']);
            }

            return json(['code'=>0,'count'=>$count,'data'=>$rows]);
        }else{

            return view('index/shop_backend/order_manage',compact('company_id','company_type','uid'));
        }
    }

    #订单退订
    public function cancel_order(Request $request){
        $dat = input();

        $id = intval($dat['id']);
        $res = Db::name('website_order_list')->where(['id'=>$id])->update(['status'=>-3]);
        if($res){
            $order = Db::name('website_order_list')->where(['id'=>$id])->find();
            #通知管理员
            $system = Db::name('centralize_system_notice')->where(['uid'=>0])->find();
            $manage_openid = $system['account'];
            common_notice([
                'openid'=>$manage_openid,
                'phone'=>'',
                'email'=>''
            ],[
                'msg'=>'订单['.$order['ordersn'].']已申请取消退订，点击链接查看：https://www.gogo198.net/?s=shop/audit',
                'opera'=>'买手取消退订',
                'url'=>'https://www.gogo198.net/?s=shop/audit'
            ]);
            return json(['code'=>0,'msg'=>'退订成功！']);
        }
    }

    #采购管理
    public function procure_manage(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);#0商城，1官网

        if(isset($dat['pa'])){
            $limit = $request->get('limit');
            $page = $request->get('page') - 1;
            if ($page != 0) {
                $page = $limit * $page;
            }
            $keyword = isset($dat['keywords']) ? trim($dat['keywords']) : '';
            #获取企业管理员下的买手id
            $buyer_info = Db::name('website_user_company')
                ->alias('a')
                ->join('website_buyer b','b.uid=a.user_id')
                ->where(['a.id'=>$company_id])
                ->field(['b.*'])
                ->find();
            if(empty($buyer_info)){
                $buyer_info['id'] = $company_id;
            }
            $where =['buyer_id'=>$buyer_info['id']];
            $count = Db::name('website_order_list')->where('ordersn', 'like', '%'.$keyword.'%')->whereRaw('accept_type=1 and pay_id<>0 and ( status=1 or status=-3 or status=-9 or status=-10 or status=-11) and ( buyer_id='.$buyer_info['id'].' or company_id='.$company_id.' )')->count();
            $rows = DB::name('website_order_list')
//                ->where($where)
                ->where('ordersn', 'like', '%'.$keyword.'%')
                ->whereRaw('accept_type=1 and pay_id<>0 and ( status=1 or status=-3 or status=-9 or status=-10 or status=-11 ) and ( buyer_id='.$buyer_info['id'].' or company_id='.$company_id.' )')
                ->limit($page . ',' . $limit)
                ->order('id desc')
                ->select();

            foreach ($rows as &$item) {
                $item['status_name'] = $this->get_statusname($item['status']);
                $item['createtime'] = date('Y-m-d H:i:s', $item['createtime']);
            }

            return json(['code'=>0,'count'=>$count,'data'=>$rows]);
        }else{

            return view('index/shop_backend/procure_manage',compact('company_id','company_type'));
        }
    }

    #折扣管理
    public function discount_manage(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);#0商城，1官网

        if(isset($dat['pa'])){
            $limit = $request->get('limit');
            $page = $request->get('page') - 1;
            if ($page != 0) {
                $page = $limit * $page;
            }
            $keyword = isset($dat['keywords']) ? trim($dat['keywords']) : '';

            $where =['company_id'=>$company_id,'company_type'=>$company_type];
            $count = Db::name('website_discount')->where($where)->count();
            $rows = DB::name('website_discount')
                ->where($where)
//                ->where('ordersn', 'like', '%'.$keyword.'%')
                ->limit($page . ',' . $limit)
                ->order('id desc')
                ->select();

            foreach ($rows as &$item) {
                $item['starttime'] = date('Y-m-d H:i:s',$item['starttime']);
                $item['endtime'] = date('Y-m-d H:i:s',$item['endtime']);
                $item['title'] = !empty($item['discount_code'])?$item['discount_code']:$item['name'];
                $item['typename'] = '';
                if($item['type']==1){
                    $item['typename'] = '产品折扣金额';
                }elseif($item['type']==2){
                    $item['typename'] = '买X送Y';
                }elseif($item['type']==3){
                    $item['typename'] = '订单金额';
                }elseif($item['type']==4){
                    $item['typename'] = '免运费';
                }
                $item['createtime'] = date('Y-m-d H:i:s', $item['createtime']);
            }

            return json(['code'=>0,'count'=>$count,'data'=>$rows]);
        }else{

            return view('index/shop_backend/discount_manage',compact('company_id','company_type'));
        }
    }

    #保存折扣
    public function save_discount(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);
        $id = isset($dat['id'])?intval($dat['id']):0;

        if($request->isAjax()){

            if($dat['method']==1 && empty($dat['discount_code'])){
                return json(['code'=>-1,'msg'=>'请输入折扣代码']);
            }else if($dat['method']==2 && empty($dat['name'])){
                return json(['code'=>-1,'msg'=>'请输入折扣标题']);
            }

            if(intval($dat['type'])==4){
                if($dat['country_type']==1 && empty($dat['country_ids'])){
                    return json(['code'=>-1,'msg'=>'请选择国家地区']);
                }
                if(empty($dat['low_freight_price'])){
                    return json(['code'=>-1,'msg'=>'请输入最低免运费金额']);
                }
            }else{
                if(empty($dat['discount_num'])){
                    return json(['code'=>-1,'msg'=>'请输入折扣额']);
                }
            }

            $pre_order_times = 0;
            $goods_info = [];
            $series_id = '';
            if(intval($dat['discount_object'])==0  && intval($dat['type'])==1){
                #适用于商品系列&商品折扣金额
                if(empty($dat['series_id'])){
                    return json(['code'=>-1,'msg'=>'请选择商品系列']);
                }else{
                    $series_id = trim($dat['series_id']);
                }
            }
            elseif(intval($dat['discount_object'])==1 && intval($dat['type'])==1){
                if(!isset($dat['goods_id'])){
                    return json(['code'=>-1,'msg'=>'请选择商品']);
                }else{
                    foreach($dat['goods_id'] as $k=>$v){
                        array_push($goods_info,[
                            'goods_id'=>$v,
                            'sku_id'=>$dat['sku_id'][$k]
                        ]);
                    }
                }
                if(isset($dat['pre_order_times'])){
                    if($dat['pre_order_times']=='on'){
                        $pre_order_times = 1;
                    }
                }
            }

            $onetimes = 0;
            if(isset($dat['onetimes'])){
                if($dat['onetimes']=='on'){
                    $onetimes = 1;
                }
            }

            $goods_discount = 0;
            $order_discount = 0;
            $freight_discount = 0;
            if(isset($dat['goods_discount'])){
                if($dat['goods_discount'] == 'on'){
                    $goods_discount = 1;
                }
            }
            if(isset($dat['order_discount'])){
                if($dat['order_discount'] == 'on'){
                    $order_discount = 1;
                }
            }
            if(isset($dat['freight_discount'])){
                if($dat['freight_discount'] == 'on'){
                    $freight_discount = 1;
                }
            }

            if(empty($dat['starttime'])){
                return json(['code'=>-1,'msg'=>'请选择开始时间']);
            }
            if(empty($dat['endtime'])){
                return json(['code'=>-1,'msg'=>'请选择结束时间']);
            }
            if($dat['endtime']<=$dat['starttime']){
                return json(['code'=>-1,'msg'=>'结束时间需大于开始时间']);
            }

            $country_ids = '';
            if($dat['country_type']==1 && intval($dat['type'])==4){
                #选择特定国家&免运费
                $country_ids = $dat['country_ids'];
            }

            if($id>0){
                Db::name('website_discount')->where(['id'=>$id])->update([
                    'method'=>intval($dat['method']),
                    'discount_code'=>$dat['method']==1?trim($dat['discount_code']):'',
                    'name'=>$dat['method']==2?trim($dat['name']):'',
                    'country_type'=>intval($dat['type'])==4?intval($dat['country_type']):'',
                    'country_ids'=>$country_ids,
                    'low_freight_currency'=>intval($dat['type'])==4?intval($dat['low_freight_currency']):"",
                    'low_freight_price'=>intval($dat['type'])==4?floatval($dat['low_freight_price']):'',
                    'discount_method'=>intval($dat['discount_method']),
                    'discount_num'=>floatval($dat['discount_num']),
                    'discount_object'=>intval($dat['type'])==1?intval($dat['discount_object']):'',
                    'series_id'=>$series_id,
                    'goods_info'=>json_encode($goods_info,true),
                    'pre_order_times'=>$pre_order_times,
                    'sale_type'=>intval($dat['sale_type']),
                    'customer_tag_ids'=>intval($dat['sale_type'])==1?trim($dat['customer_tag_ids']):'',
                    'customer_ids'=>intval($dat['sale_type'])==2?trim($dat['customer_ids']):'',
                    'buy_requirement'=>intval($dat['buy_requirement']),
                    'low_currency'=>intval($dat['buy_requirement'])==1?intval($dat['low_currency']):'',
                    'low_price'=>intval($dat['buy_requirement'])==1?floatval($dat['low_price']):'',
                    'low_num'=>intval($dat['buy_requirement'])==2?intval($dat['low_num']):'',
                    'canusetimes'=>intval($dat['canusetimes']),
                    'onetimes'=>$onetimes,
                    'goods_discount'=>$goods_discount,
                    'order_discount'=>$order_discount,
                    'freight_discount'=>$freight_discount,
                    'starttime'=>strtotime($dat['starttime']),
                    'endtime'=>strtotime($dat['endtime']),
                ]);
            }else{
                Db::name('website_discount')->insert([
                    'company_id'=>$company_id,
                    'company_type'=>$company_type,
                    'type'=>intval($dat['type']),
                    'method'=>intval($dat['method']),
                    'discount_code'=>$dat['method']==1?trim($dat['discount_code']):'',
                    'name'=>$dat['method']==2?trim($dat['name']):'',
                    'country_type'=>intval($dat['type'])==4?intval($dat['country_type']):'',
                    'country_ids'=>$country_ids,
                    'low_freight_currency'=>intval($dat['type'])==4?intval($dat['low_freight_currency']):"",
                    'low_freight_price'=>intval($dat['type'])==4?floatval($dat['low_freight_price']):'',
                    'discount_method'=>intval($dat['discount_method']),
                    'discount_num'=>floatval($dat['discount_num']),
                    'discount_object'=>intval($dat['type'])==1?intval($dat['discount_object']):'',
                    'series_id'=>$series_id,
                    'goods_info'=>json_encode($goods_info,true),
                    'pre_order_times'=>$pre_order_times,
                    'sale_type'=>intval($dat['sale_type']),
                    'customer_tag_ids'=>intval($dat['sale_type'])==1?trim($dat['customer_tag_ids']):'',
                    'customer_ids'=>intval($dat['sale_type'])==2?trim($dat['customer_ids']):'',
                    'buy_requirement'=>intval($dat['buy_requirement']),
                    'low_currency'=>intval($dat['buy_requirement'])==1?intval($dat['low_currency']):'',
                    'low_price'=>intval($dat['buy_requirement'])==1?floatval($dat['low_price']):'',
                    'low_num'=>intval($dat['buy_requirement'])==2?intval($dat['low_num']):'',
                    'canusetimes'=>intval($dat['canusetimes']),
                    'onetimes'=>$onetimes,
                    'goods_discount'=>$goods_discount,
                    'order_discount'=>$order_discount,
                    'freight_discount'=>$freight_discount,
                    'starttime'=>strtotime($dat['starttime']),
                    'endtime'=>strtotime($dat['endtime']),
                    'createtime'=>time()
                ]);
            }
            return json(['code'=>0,'msg'=>'保存成功']);
        }else{
            $data = ['type'=>1,'method'=>1,'discount_code'=>'','name'=>'','discount_method'=>0,'discount_num'=>'','discount_object'=>0,'series_id'=>'','goods_info'=>'','pre_order_times'=>1,'sale_type'=>0,'customer_tag_ids'=>'','customer_ids'=>'','buy_requirement'=>0,'low_currency'=>5,'low_price'=>'','low_num'=>'','canusetimes'=>'','onetimes'=>1,'goods_discount'=>0,'order_discount'=>'','freight_discount'=>'','starttime'=>'','endtime'=>'','country_type'=>0,'country_ids'=>'','low_freight_currency'=>5,'low_freight_price'=>''];
            if($id>0){
                $data = Db::name('website_discount')->where(['id'=>$id])->find();

                if(!empty($data['goods_info']) && $data['goods_info']!='[]'){
                    #商品信息
                    $data['goods_info'] = json_decode($data['goods_info'],true);
                    foreach($data['goods_info'] as $k=>$v){
                        $goods_info = Db::connect($this->config)->name('goods_merchant')->where(['id'=>$v['goods_id']])->field(['goods_name','goods_number'])->find();
                        $data['goods_info'][$k]['goods_name'] = $goods_info['goods_name'];
                        $data['goods_info'][$k]['goods_number'] = $goods_info['goods_number'];

                        $sku_info = Db::connect($this->config)->name('goods_sku_merchant')->where(['sku_id'=>$v['sku_id']])->field('spec_names')->find();
                        $data['goods_info'][$k]['sku_name'] = $sku_info['spec_names'];
                    }
                }

                $data['starttime'] = date('Y-m-d H:i:s',$data['starttime']);
                $data['endtime'] = date('Y-m-d H:i:s',$data['endtime']);
            }

            #商品系列
            $list['series'] = Db::name('website_goods_series')->where(['company_id'=>$company_id,'company_type'=>$company_type])->select();
            $list['series'] = json_encode($list['series'],true);

            #买家标识
            $list['tag'] = Db::name('website_tag_manage')->where(['company_id'=>$company_id,'company_type'=>$company_type])->select();
            $list['tag'] = json_encode($list['tag'],true);

            #企业买家
            $list['customer'] = Db::name('website_user')->where(['company_id'=>$company_id])->select();
            foreach($list['customer'] as $k=>$v){
                $name = !empty($v['realname'])?$v['realname']:$v['nickname'];
                $area_code = '';
                if(empty($v['email'])){
                    $area_code = Db::name('centralize_diycountry_content')->where(['id'=>$v['area_code']])->field('param8')->find()['param8'];
                }
                $contact = !empty($v['email'])?$v['email']:$area_code.$v['phone'];
                $list['customer'][$k]['name'] = $name.'（'.$contact.'）';
            }
            $list['customer'] = json_encode($list['customer'],true);

            #币种
            $list['currency'] = Db::name('centralize_currency')->field(['currency_symbol_standard','id','code_zhname'])->select();

            #国家地区
            $list['country'] = Db::name('centralize_diycountry_content')->where(['pid'=>5])->field(['id','param2'])->select();
            $list['country'] = json_encode($list['country'],true);

            return view('index/shop_backend/save_discount',compact('company_id','company_type','id','data','list'));
        }
    }

    #删除折扣
    public function del_discount(Request $request){
        $dat = input();
        $id = intval($dat['id']);

        $res = Db::name('website_discount')->where(['id'=>$id])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功']);
        }
    }

    #打包贴单
    public function first_logistics_manage(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);#0商城，1官网

        if(isset($dat['pa'])){
            $limit = $request->get('limit');
            $page = $request->get('page') - 1;
            if ($page != 0) {
                $page = $limit * $page;
            }
            $keyword = isset($dat['keywords']) ? trim($dat['keywords']) : '';
            #获取企业管理员下的买手id
            $buyer_info = Db::name('website_user_company')
                ->alias('a')
                ->join('website_buyer b','b.uid=a.user_id')
                ->where(['a.id'=>$company_id])
                ->field(['b.*'])
                ->find();

            if(empty($buyer_info)){
                $buyer_info['id'] = $company_id;
            }
//            $where =['buyer_id'=>$buyer_info['id'],'accept_type'=>2];
            $count = Db::name('website_order_list')->whereRaw('accept_type=2 and ( buyer_id='.$buyer_info['id'].' or company_id='.$company_id.' )')->where('ordersn', 'like', '%'.$keyword.'%')->count();
            $rows = DB::name('website_order_list')
                ->whereRaw('accept_type=2 and ( buyer_id='.$buyer_info['id'].' or company_id='.$company_id.' )')
//                ->where($where)
                ->where('ordersn', 'like', '%'.$keyword.'%')
                ->limit($page . ',' . $limit)
                ->order('id desc')
                ->select();

            foreach ($rows as &$item) {
                $item['status_name'] = $this->get_statusname($item['status']);
                $item['createtime'] = date('Y-m-d H:i:s', $item['createtime']);
            }

            return json(['code'=>0,'count'=>$count,'data'=>$rows]);
        }else{

            return view('index/shop_backend/first_logistics_manage',compact('company_id','company_type'));
        }
    }

    #国内集货
    public function consolidation_manage(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);#0商城，1官网
        $process_ids['process1'] = isset($dat['process1'])?intval($dat['process1']):19;
        $uid = isset($dat['uid'])?intval($dat['uid']):0;

        if(isset($dat['pa'])){
            $limit = $request->get('limit');
            $page = $request->get('page') - 1;
            if ($page != 0) {
                $page = $limit * $page;
            }
            $keyword = isset($dat['keywords']) ? trim($dat['keywords']) : '';

            if($uid>0){
                $customIds = [$uid];
            }else{
                $customIds = Db::name('website_user')
                    ->where(['company_id' => $company_id])
                    ->column('id');
            }

            $count = Db::name('centralize_parcel_order')
                ->alias('a')
                ->join('centralize_warehouse_list b','b.id=a.warehouse_id')
                ->where(['b.uid'=>$company_id])
//                ->where('a.user_id','in',$customIds)
                ->whereRaw('a.ordersn like "%'.$keyword.'%"')
                ->count();
            $list = Db::name('centralize_parcel_order')
                ->alias('a')
                ->join('centralize_warehouse_list b','b.id=a.warehouse_id')
                ->where(['b.uid'=>$company_id])
//                ->where('a.user_id','in',$customIds)
                ->whereRaw('a.ordersn like "%'.$keyword.'%"')
                ->limit($page . ',' . $limit)
                ->order('a.id asc')
                ->field('a.*')
                ->select();

            if($process_ids['process1']!=16){
                #非管理订仓
                $where = [];
                if($process_ids['process1']==19){
                    #管理预报
                    $where=[0];
                }elseif($process_ids['process1']==22){
                    #签收入库->确认信息(包裹签收、常恒/恒温入库)
                    $where=[1,2,31,32,33,34,35,36,37,38];
                }elseif($process_ids['process1']==25){
                    #仓库集货（包裹合并、分拆、附加、剔除、国内转运、定点弃货、公益弃货、就地弃货）
                    $where=[43,44,45,46,39,40,41,42,51,52,53,54,55,56,57,58,6,7,8,9,10,16,17,18,19,21,22,23,24,25,26,27,28,29,30];
                }elseif($process_ids['process1']==31){
                    #跨境集运
                    $where=[];
                }
                $list2 = [];
                foreach ($list as $k => $v) {
                    $list[$k]['createtime'] = date('Y-m-d H:i:s', $v['createtime']);
                    $list[$k]['orderid'] = 0;

                    #找到该订仓单下面的包裹
                    $list3 = Db::name('centralize_parcel_order_package')->where(['orderid'=>$v['id']])->whereIn('status2',$where)->select();
                    if(empty($list3)){
                        unset($list[$k]);
                    }
                    foreach($list3 as $k2=>$v2){
                        $list3[$k2]['ordersn']='';
                        $list3[$k2]['createtime'] = date('Y-m-d H:i:s', $v2['createtime']);
                        $process_num = Db::name('centralize_process_list')->where(['id'=>$process_ids['process1']])->field('step')->find()['step'];
                        $process_num = explode('Step',$process_num)[1];
                        $num=$k2+1;
                        $list3[$k2]['ordersn'] = '--'.$process_num.$num;
                    }
                    $list2 = array_merge($list2,$list3);
                }
                $list = array_merge($list,$list2);

                foreach($list as $k=>$v){
                    $list[$k]['oid'] = $v['id'];
                    if($v['orderid']!=0){
                        $list[$k]['id'] = 'p'.strval($k+1);#包裹id
                    }else{
                        $list[$k]['process_name'] = Db::name('centralize_process_list')->where(['id'=>$process_ids['process1']])->field('content')->find()['content'];
                    }
                }
            }

            return json(['code'=>0,'count'=>$count,'data'=>$list]);
        }else{

            $process = Db::name('centralize_process_list')->where(['pid'=>0,'display'=>0,'system_id'=>2])->order('displayorders asc')->select();

            return view('index/shop_backend/consolidation_manage',compact('company_id','company_type','process','process_ids','uid'));
        }
    }

    #集货详情
    public function waybill_info(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);#0商城，1官网
        $process_ids['process1'] = isset($dat['process1'])?intval($dat['process1']):19;

        if($request->isAjax()){
            #获取包裹信息
            $parcel = Db::name('centralize_parcel_order_package')->where(['id'=>$dat['goods_id']])->find();
            $order = Db::name('centralize_parcel_order')->where(['id'=>$parcel['orderid']])->find();
            $status2_name = Db::name('centralize_parcel_status')->where(['status_id'=>$parcel['status2']])->find();
            $status_name = Db::name('centralize_parcel_operation_status')->where(['status_id'=>$status2_name['pid']])->find();

            $res = '';
            #修改包裹状态
            if($parcel['status2']==0 || $parcel['status2']==1){
                #已预报待确认预报->已确认预报待确认信息
                foreach($dat['package'] as $k=>$v) {
                    if ($order['prediction_id'] == 1) {
                        #原包签收
                        $res = Db::name('centralize_parcel_order_package')->where(['id' => $dat['goods_id']])->update([
                            'package'=>$v,
                            'package_name'=>$dat['package'][$k]=='包装'?trim($dat['package_name'][$k]):'',
                            'grosswt'=>trim($dat['grosswt'][$k]),
                            'volumn'=>trim($dat['long'][$k]).'*'.trim($dat['width'][$k]).'*'.trim($dat['height'][$k]),
                            'condition'=>trim($dat['condition'][$k]),
                            'condition_file'=>isset($dat['condition_file'])?json_encode($dat['condition_file']):'',
                            'status2'=>1,
                        ]);
                        if(isset($dat['valueid'][$k])) {
                            #修改包裹货物表
                            foreach ($dat['valueid'][$k] as $k2 => $v2) {
                                $brand_name = '';
                                if ($dat['goods_brand'][$k][$k2] == 2) {
                                    #仿牌
                                    $brand_name = $dat['goods_brand_name2'][$k][$k2];
                                } elseif ($dat['goods_brand'][$k][$k2] == 3) {
                                    #普通品牌
                                    $brand_name = $dat['goods_brand_name'][$k][$k2];
                                } elseif ($dat['goods_brand'][$k][$k2] == 4) {
                                    #奢侈品牌
                                    $brand_name = $dat['goods_brand_name3'][$k][$k2];
                                }
                                $insdata = [
                                    #物品属性
                                    'valueid' => $dat['valueid'][$k][$k2],
                                    #物品描述
                                    'good_desc' => $dat['goods_select_desc'][$k][$k2] == -1 ? trim($dat['goods_desc'][$k][$k2]) : $dat['goods_select_desc'][$k][$k2],
                                    #物品数量
                                    'good_num' => trim($dat['goods_num'][$k][$k2]),
                                    #物品单位
                                    'good_unit' => $dat['goods_unit'][$k][$k2],
                                    #物品币种
                                    'good_currency' => $dat['goods_currency'][$k][$k2],
                                    #物品金额
                                    'good_price' => trim($dat['goods_price'][$k][$k2]),
                                    #物品包装
                                    'good_package' => $dat['goods_package'][$k][$k2],
                                    #物品品牌类型
                                    'brand_type' => $dat['goods_brand'][$k][$k2],
                                    'brand_name' => $brand_name,
                                    #物品备注
                                    'good_remark' => trim($dat['goods_remark'][$k][$k2]),
                                ];
                                if ($dat['good_id'][$k][$k2] != 0) {
                                    Db::name('centralize_parcel_order_goods')->where(['id' => $dat['good_id'][$k][$k2]])->update($insdata);
                                } else {
                                    $insdata['user_id'] = $parcel['user_id'];
                                    $insdata['orderid'] = $parcel['orderid'];
                                    $insdata['package_id'] = $parcel['id'];
                                    $insdata['createtime'] = time();
                                    Db::name('centralize_parcel_order_goods')->insert($insdata);
                                }

                                #插入物品属性指定id下的标签
                                if (!empty($dat['goods_desc'][$k][$k2])) {
                                    $gvalue = Db::name('centralize_gvalue_list')->where(['id' => $v2])->field('keywords')->find();
                                    $gvalue['keywords'] = $gvalue['keywords'] . '、' . trim($dat['goods_desc'][$k][$k2]);
                                    Db::name('centralize_gvalue_list')->where(['id' => $v2])->update(['keywords' => $gvalue['keywords']]);
                                }
                            }
                        }
                    }
                    elseif ($order['prediction_id'] == 2) {
                        #验货签收
                        $res = Db::name('centralize_parcel_order_package')->where(['id' => $dat['goods_id']])->update([
                            'package'=>$v,
                            'package_name'=>$dat['package'][$k]=='包装'?trim($dat['package_name'][$k]):'',
                            'grosswt'=>trim($dat['grosswt'][$k]),
                            'volumn'=>trim($dat['long'][$k]).'*'.trim($dat['width'][$k]).'*'.trim($dat['height'][$k]),
                            'condition'=>trim($dat['condition'][$k]),
                            'condition_file'=>isset($dat['condition_file'])?json_encode($dat['condition_file']):'',
                            'status2' => 1,
                            'pic_file' => isset($dat['pic_file'])?json_encode($dat['pic_file'], true):'',//拍照文件
                            'video_code' => isset($dat['video_code'])?trim($dat['video_code']):'',
                        ]);
                        if(isset($data['valueid'][$k])) {
                            #修改包裹货物表
                            foreach ($dat['valueid'][$k] as $k2 => $v2) {
                                $brand_name = '';
                                if ($dat['goods_brand'][$k][$k2] == 2) {
                                    #仿牌
                                    $brand_name = $dat['goods_brand_name2'][$k][$k2];
                                } elseif ($dat['goods_brand'][$k][$k2] == 3) {
                                    #普通品牌
                                    $brand_name = $dat['goods_brand_name'][$k][$k2];
                                } elseif ($dat['goods_brand'][$k][$k2] == 4) {
                                    #奢侈品牌
                                    $brand_name = $dat['goods_brand_name3'][$k][$k2];
                                }
                                $insdata = [
                                    #物品属性
                                    'valueid' => $dat['valueid'][$k][$k2],
                                    #物品描述
                                    'good_desc' => $dat['goods_select_desc'][$k][$k2] == -1 ? trim($dat['goods_desc'][$k][$k2]) : $dat['goods_select_desc'][$k][$k2],
                                    #物品数量
                                    'good_num' => trim($dat['goods_num'][$k][$k2]),
                                    #物品单位
                                    'good_unit' => $dat['goods_unit'][$k][$k2],
                                    #物品币种
                                    'good_currency' => $dat['goods_currency'][$k][$k2],
                                    #物品金额
                                    'good_price' => trim($dat['goods_price'][$k][$k2]),
                                    #物品包装
                                    'good_package' => $dat['goods_package'][$k][$k2],
                                    #物品品牌类型
                                    'brand_type' => $dat['goods_brand'][$k][$k2],
                                    'brand_name' => $brand_name,
                                    #物品备注
                                    'good_remark' => trim($dat['goods_remark'][$k][$k2]),
                                ];
                                if ($dat['good_id'][$k][$k2] != 0) {
                                    Db::name('centralize_parcel_order_goods')->where(['id' => $dat['good_id'][$k][$k2]])->update($insdata);
                                } else {
                                    $insdata['user_id'] = $parcel['user_id'];
                                    $insdata['orderid'] = $parcel['orderid'];
                                    $insdata['package_id'] = $parcel['id'];
                                    $insdata['createtime'] = time();
                                    Db::name('centralize_parcel_order_goods')->insert($insdata);
                                }

                                #插入物品属性指定id下的标签
                                if (!empty($dat['goods_desc'][$k][$k2])) {
                                    $gvalue = Db::name('centralize_gvalue_list')->where(['id' => $v2])->field('keywords')->find();
                                    $gvalue['keywords'] = $gvalue['keywords'] . '、' . trim($dat['goods_desc'][$k][$k2]);
                                    Db::name('centralize_gvalue_list')->where(['id' => $v2])->update(['keywords' => $gvalue['keywords']]);
                                }
                            }
                        }
                    }
                }
                #待支付订单列表
                Db::name('centralize_order_fee_log')->where(['good_id'=>$dat['goods_id']])->update([
                    'service_status'=>1,#包裹操作状态
                    'order_status'=>1,#包裹状态
//                    'service_price'=>$data['service_price'],
                ]);

                if($parcel['gogo_oid']>0){
                    #商城订单
                    Db::name('website_order_list')->where(['id'=>$parcel['gogo_oid']])->update(['status'=>3]);
                }
            }
            elseif($parcel['status2']==2){
                #已确认预报待确认签收
                if ($order['prediction_id'] == 1) {
                    foreach($dat['package'] as $k=>$v) {
                        $res = Db::name('centralize_parcel_order_package')->where(['id' => $dat['goods_id']])->update([
                            'package'=>$v,
                            'package_name'=>$dat['package'][$k]=='包装'?trim($dat['package_name'][$k]):'',
                            'grosswt'=>trim($dat['grosswt'][$k]),
                            'volumn'=>trim($dat['long'][$k]).'*'.trim($dat['width'][$k]).'*'.trim($dat['height'][$k]),
                            'condition'=>trim($dat['condition'][$k]),
                            'condition_file'=>isset($dat['condition_file'])?json_encode($dat['condition_file']):'',
                            'status2'=>2,
                        ]);
                        if(isset($dat['valueid'][$k])) {
                            #修改包裹货物表
                            foreach ($dat['valueid'][$k] as $k2 => $v2) {
                                $brand_name = '';
                                if ($dat['goods_brand'][$k][$k2] == 2) {
                                    #仿牌
                                    $brand_name = $dat['goods_brand_name2'][$k][$k2];
                                } elseif ($dat['goods_brand'][$k][$k2] == 3) {
                                    #普通品牌
                                    $brand_name = $dat['goods_brand_name'][$k][$k2];
                                } elseif ($dat['goods_brand'][$k][$k2] == 4) {
                                    #奢侈品牌
                                    $brand_name = $dat['goods_brand_name3'][$k][$k2];
                                }
                                $insdata = [
                                    #物品属性
                                    'valueid' => $dat['valueid'][$k][$k2],
                                    #物品描述
                                    'good_desc' => $dat['goods_select_desc'][$k][$k2] == -1 ? trim($dat['goods_desc'][$k][$k2]) : $dat['goods_select_desc'][$k][$k2],
                                    #物品数量
                                    'good_num' => trim($dat['goods_num'][$k][$k2]),
                                    #物品单位
                                    'good_unit' => $dat['goods_unit'][$k][$k2],
                                    #物品币种
                                    'good_currency' => $dat['goods_currency'][$k][$k2],
                                    #物品金额
                                    'good_price' => trim($dat['goods_price'][$k][$k2]),
                                    #物品包装
                                    'good_package' => $dat['goods_package'][$k][$k2],
                                    #物品品牌类型
                                    'brand_type' => $dat['goods_brand'][$k][$k2],
                                    'brand_name' => $brand_name,
                                    #物品备注
                                    'good_remark' => trim($dat['goods_remark'][$k][$k2]),
                                ];
                                if ($dat['good_id'][$k][$k2] != 0) {
                                    Db::name('centralize_parcel_order_goods')->where(['id' => $dat['good_id'][$k][$k2]])->update($insdata);
                                } else {
                                    $insdata['user_id'] = $parcel['user_id'];
                                    $insdata['orderid'] = $parcel['orderid'];
                                    $insdata['package_id'] = $parcel['id'];
                                    $insdata['createtime'] = time();
                                    Db::name('centralize_parcel_order_goods')->insert($insdata);
                                }

                                #插入物品属性指定id下的标签
                                if (!empty($dat['goods_desc'][$k][$k2])) {
                                    $gvalue = Db::name('centralize_gvalue_list')->where(['id' => $v2])->field('keywords')->find();
                                    $gvalue['keywords'] = $gvalue['keywords'] . '、' . trim($dat['goods_desc'][$k][$k2]);
                                    Db::name('centralize_gvalue_list')->where(['id' => $v2])->update(['keywords' => $gvalue['keywords']]);
                                }
                            }
                        }
                    }
                }elseif ($order['prediction_id'] == 2){
                    $res = Db::name('centralize_parcel_order_package')->where(['id' => $dat['goods_id']])->update([
                        'pic_file' => isset($dat['pic_file'])?json_encode($dat['pic_file'], true):'',//拍照文件
                        'video_code' => isset($dat['video_code'])?trim($dat['video_code']):'',
                        'status2'=>2,
                    ]);
                }
            }
            elseif($parcel['status2']==6 || $parcel['status2']==16 || $parcel['status2']==21 || $parcel['status2']==26 || $parcel['status2']==31 || $parcel['status2']==35 || $parcel['status2']==39 || $parcel['status2']==43 || $parcel['status2']==47 || $parcel['status2']==51 || $parcel['status2']==55 || $parcel['status2']==59){
                #确认入库暂存、合并、转移、分拆、附加、剔除、弃货、国内转运、跨境转运
                if($dat['opera_status']==1){
                    #修改包裹事项状态
                    if($parcel['status2']==59){
                        #出库申报，修改运单为已确认状态
                        $res = Db::name('centralize_waybill_list')->where(['parcel_id'=>$parcel['id']])->update(['status'=>2]);
                    }else{
                        if($parcel['status2']==51 || $parcel['status2']==55){
                            #附加、剔除

                            #对运单的包裹进行操作
                            $res = Db::name('centralize_parcel_order_package')->where(['id'=>$parcel['id']])->update(['status2'=>intval($parcel['status2'])+1]);

                            $service_price = 0;
                            $project = [];
                            foreach($dat['project_price'] as $k=>$v){
                                $service_price += $v;
                                $project[$k]['project_name'] = trim($dat['project_name'][$k]);
                                $project[$k]['project_currency'] = $dat['project_currency'][$k];
                                $project[$k]['project_price'] = trim($dat['project_price'][$k]);
                            }
                            $order_fee_log = Db::name('centralize_order_fee_log')->where(['express_no'=>$parcel['express_no'],'order_status'=>$parcel['status2']])->find();
                            if($parcel['status2']==51){
                                Db::name('centralize_parcel_add_eli')->where(['id'=>$order_fee_log['attach_id']])->update(['content'=>json_encode($project,true)]);
                            }elseif($parcel['status2']==55){
                                Db::name('centralize_parcel_add_eli')->where(['id'=>$order_fee_log['reject_id']])->update(['content'=>json_encode($project,true)]);
                            }

                            Db::name('centralize_order_fee_log')->where(['express_no'=>$parcel['express_no'],'order_status'=>$parcel['status2']])->update([
                                'order_status'=>intval($parcel['status2'])+1,#包裹状态
                                'service_price'=>$service_price,
                            ]);
                        }
                        else{
                            #对运单的包裹进行操作
                            $res = Db::name('centralize_parcel_order_package')->where(['id'=>$parcel['id']])->update(['status2'=>intval($parcel['status2'])+1]);
                            if(empty($dat['opera_price'])){$dat['opera_price'] = 0;}
                            Db::name('centralize_order_fee_log')->where(['express_no'=>$parcel['express_no'],'order_status'=>$parcel['status2']])->update([
                                'order_status'=>intval($parcel['status2'])+1,#包裹状态
                                'service_price'=>$dat['opera_price'],
                            ]);
                        }
                    }
                }
            }
            elseif($parcel['status2']==8 || $parcel['status2']==18 || $parcel['status2']==23 || $parcel['status2']==28 || $parcel['status2']==33 || $parcel['status2']==37 || $parcel['status2']==41 || $parcel['status2']==45 || $parcel['status2']==49 || $parcel['status2']==53 || $parcel['status2']==57){
                #已入库待暂存、
                if($dat['opera_status']==1) {
                    #对运单的包裹进行操作
                    if($parcel['status2']==33 || $parcel['status2']==37){
                        #执行入库
                        $shelf_code='';
                        if($order['prediction_id']==2){
                            #验货签收（判断重号）
                            $shelf_code = $dat['shelf_code'][0].str_pad($dat['shelf_code'][1],2,'0',STR_PAD_LEFT).$dat['shelf_code'][2].str_pad($dat['shelf_code'][3],2,'0',STR_PAD_LEFT);
                        }
                        elseif($order['prediction_id']==1){
                            #原包签收（判断重号）（允许空白）编号自定义
                            $shelf_code = trim($dat['shelf_code']);
                        }
                        $ishave = Db::name('centralize_parcel_order_package')->where(['shelf_code'=>$shelf_code])->find();
                        if(isset($ishave->id)){
                            return json(['code'=>-1,'msg'=>'该货架编号重复，请输入新的货架编号']);
                        }
                        Db::name('centralize_parcel_order_package')->where(['id'=>$parcel['id']])->update(['shelf_code'=>$shelf_code]);

                        if($parcel['gogo_oid']>0){
                            #商城订单
                            Db::name('website_order_list')->where(['id'=>$parcel['gogo_oid']])->update(['status'=>5]);
                        }
                    }
                    $res = Db::name('centralize_parcel_order_package')->where(['id'=>$parcel['id']])->update(['status2'=>intval($parcel['status2']) + 1]);

                    if($parcel['status2']==8 || $parcel['status2']==23 || $parcel['status2']==28){
                        #国内转运、弃货物流信息
                        Db::name('centralize_order_fee_log')->where(['express_no'=>$parcel['express_no'],'order_status'=>intval($parcel['status2'])])->update(['express_content' => json_encode(['express_id'=>$dat['express_id'],'express_no'=>$dat['express_no']])]);
                    }

                    $order_fee_log = Db::name('centralize_order_fee_log')->where(['good_id'=>$parcel['id'],'order_status'=>intval($parcel['status2'])])->find();
                    Db::name('centralize_order_fee_log')->where(['express_no'=>$parcel['express_no'],'order_status'=>intval($parcel['status2'])])->update(['order_status' => intval($parcel['status2']) +1]);
                    if($parcel['status2']==41){
                        #执行分拆
                        $this->spin_off($order_fee_log['spin_id']);
                    }
                    elseif($parcel['status2']==45){
                        #执行合并
                        $this->merge_parcel($order_fee_log['id']);
                    }
                    elseif($parcel['status2']==49){
                        #执行转移
                        $this->transfer_parcel_goods($order_fee_log['id']);
                    }
                }
            }
            if($res){
//                $time = time();
//                $workorder_number = 'MB'.date('ymdHis',$time);
//                $parcel = Db::name('centralize_parcel_order_package')->where(['id'=>$dat['goods_id']])->find();
//                $status2_name = Db::name('centralize_parcel_status')->where(['status_id'=>$parcel['status2']])->find();
//                $task_name = '服务商（ID：'.session('manage_person')['id'].'，'.session('manage_person')['name'].'）在'.date('Y-m-d H:i:s',$time).'时间，新增工单事项['.$status2_name['status_name'].']';
//                $pid = Db::name('centralize_task')->where(['order_id'=>$order['id'],'package_id'=>$parcel['id']])->order('id desc')->find();
//                if(empty($pid)){
//                    #包裹预报前没有记录包裹id
//                    $pid = Db::name('centralize_task')->where(['order_id'=>$order['id']])->order('id desc')->find();
//                }
//                $res = Db::name('centralize_workorder')->insert([
//                    'user_id'=>session('manage_person')['id'],
//                    'pid'=>$pid['id'],
//                    'type'=>2,
//                    'event_name'=>$task_name,
//                    'event_id'=>$parcel['status2'],
//                    'workorder_number'=>$workorder_number,
//                    'remark'=>isset($dat['remark'])?trim($dat['remark']):'',
//                    'createtime'=>$time
//                ]);
//                centralize_notice($workorder_number);
                return json(['code'=>0,'msg'=>'操作成功']);
            }
        }
        else{

            $package = Db::name('centralize_parcel_order_package')
                ->alias('a')
                ->join('centralize_parcel_order b','b.id=a.orderid')
                ->where(['a.id'=>$dat['id']])
                ->field('a.*,b.prediction_id,b.warehouse_id')
                ->find();

            #跨境集运
            if($package['status2']==64 || $package['status2']==65 || $package['status2']==66 || $package['status2']==67 || $package['status2']==68 || $package['status2']==69 || $package['status2']==70){
                $waybill = Db::name('centralize_waybill_list')->where(['parcel_id'=>$package['id']])->find();
                $package['waybill_info'] = $waybill;
                #判断仓库的拥有者是否自己
                $warehouse = Db::name('centralize_warehouse_list')->where(['id'=>$package['warehouse_id']])->find();
                if($warehouse['uid'] == $company_id){
                    $package['warehouse_isme']=1;
                }else{
                    $package['warehouse_isme']=0;
                }
            }

            if(!empty($package['condition_file'])){
                $package['condition_file'] = json_decode($package['condition_file'],true);
            }
            if(!empty($package['pic_file'])){
                $package['pic_file'] = json_decode($package['pic_file'],true);
            }
            if(!empty($package['express_id'])){
                $param = Db::name('centralize_diycountry_content')->where(['id'=>$package['express_id']])->find();
                $package['express_name'] = $param['param3'];
            }else{
                $package['express_name'] = '自送入仓，无运输企业';
            }
            #包裹体积
            if(!empty($package['volumn'])){
                $package['volumn'] = explode('*',$package['volumn']);
            }
            $goods = Db::name('centralize_parcel_order_goods')->where(['package_id'=>$dat['id']])->select();
            if(!empty($goods)){
                foreach($goods as $k=>$v){
                    #商品类别
//                if(!empty($v['itemid'])){
//                    $goods[$k]['itemid'] = Db::name('centralize_hscode_list')->where(['id'=>$v['itemid']])->find()['name'];
//                }
                    #物品属性
                    if(!empty($v['valueid'])){
                        $gval = Db::name('centralize_gvalue_list')->where(['id'=>$v['valueid']])->field('name')->find();
                        $goods[$k]['valueid2'] = $gval['name'];
                        if($k==0){
                            #修改包裹时查找最上级id
                            $top_id = Db::name('centralize_gvalue_list')->where('id',$v['valueid'])->field('pid')->find()['pid'];
                            $top_id2 = Db::name('centralize_gvalue_list')->where('id',$top_id)->field('pid')->find()['pid'];
                            if($top_id2==0){
                                $goods[$k]['top_id'] = $top_id;
                            }else{
                                $top_id = Db::name('centralize_gvalue_list')->where('id',$top_id2)->field('pid')->find()['pid'];
                                try {
                                    $top_id2 = Db::name('centralize_gvalue_list')->where('id', $top_id)->field('pid')->find()['pid'];
                                }catch (\Exception $e) {
                                    $top_id2 = 0;
                                }
                                if($top_id2==0){
                                    $goods[$k]['top_id'] = $top_id;
                                }else{
                                    $top_id = Db::name('centralize_gvalue_list')->where('id',$top_id2)->field('pid')->find()['pid'];
                                    $top_id2 = Db::name('centralize_gvalue_list')->where('id',$top_id)->field('pid')->find()['pid'];
                                    if($top_id2==0) {
                                        $goods[$k]['top_id'] = $top_id;
                                    }
                                }
                            }
                        }
                    }
                    #物品材质
                    if(!empty($v['good_package'])) {
                        $goods[$k]['good_package'] = Db::name('packing_type')->where(['code_value' => $v['good_package']])->find()['code_name'];
                    }
                    #物品单位
                    if(!empty($v['good_unit'])){
                        $goods[$k]['good_unit'] = Db::name('unit')->where(['code_value'=>$v['good_unit']])->find()['code_name'];
                    }
                    #物品币种
                    if(!empty($v['good_currency'])){
                        $goods[$k]['good_currency'] = Db::name('centralize_currency')->where(['id'=>$v['good_currency']])->find()['currency_symbol_standard'];
                    }
                    #物品品牌类型
                }
            }else{
                $goods = [['top_id'=>'','id'=>0,'good_desc'=>'','good_num'=>'','good_unit'=>'','good_currency'=>'CNY','good_price'=>'','goods_usdprice'=>'','good_package'=>'','brand_type'=>'','brand_name'=>'','good_remark'=>'','valueid'=>'','status2'=>0]];
            }

            $order_fee_log = Db::name('centralize_order_fee_log')->where(['orderid'=>$package['orderid'],'express_no'=>$package['express_no']])->order('id desc')->find();

            #合并货物
            if($order_fee_log['service_status']==11){
                $order_fee_log['parcel_ids'] = explode(',',$order_fee_log['parcel_ids']);
                foreach($order_fee_log['parcel_ids'] as $k=>$v){
                    $order_fee_log['content'][$k] = Db::name('centralize_parcel_order_goods')->where(['package_id'=>$v])->select();
                    foreach($order_fee_log['content'][$k] as $k2=>$v2){
                        $order_fee_log['content'][$k][$k2]['good_unit'] = Db::name('unit')->where(['code_value'=>$order_fee_log['content'][$k][$k2]['good_unit']])->find()['code_name'];
                        $order_fee_log['content'][$k][$k2]['ordersn'] = Db::name('centralize_parcel_order')->where(['id'=>$order_fee_log['content'][$k][$k2]['orderid']])->find()['ordersn'];
                        $order_fee_log['content'][$k][$k2]['express_no'] = Db::name('centralize_parcel_order_package')->where(['id'=>$order_fee_log['content'][$k][$k2]['package_id']])->find()->express_no;
                    }
                }
            }

            #分拆货物
            if($order_fee_log['service_status']==10){
                #分拆信息
                $order_fee_log['spin_content'] = Db::name('centralize_parcel_order_spin')->where(['id'=>$order_fee_log['spin_id']])->find();
                $order_fee_log['spin_content']['spin_info'] = json_decode($order_fee_log['spin_content']['spin_info'],true);
                foreach($order_fee_log['spin_content']['spin_info'] as $k=>$v){
                    $ginfo = Db::name('centralize_parcel_order_goods')->where(['id'=>$v['gid']])->find();
                    if(empty($ginfo)){
                        $ginfo = Db::name('centralize_parcel_order_package')
                            ->alias('a')
                            ->join('centralize_parcel_order_goods b','b.package_id=a.id')
                            ->where(['a.express_no'=>$order_fee_log['spin_content']['new_expressno']])
                            ->field('b.*')
                            ->find();
                    }
                    $ginfo['good_unit'] = Db::name('unit')->where(['code_value'=>$ginfo['good_unit']])->field('code_name')->find()['code_name'];
                    $order_fee_log['spin_content']['spin_info'][$k]['ginfo'] = $ginfo;
                }
            }
            #物品转移
            if($order_fee_log['service_status']==12){
                #转移信息
                $order_fee_log['transfer_content'] = Db::name('centralize_parcel_order_transfer')->where(['id'=>$order_fee_log['transfer_id']])->find();
                $order_fee_log['transfer_content']['target_order'] = Db::name('centralize_parcel_order')->where(['id'=>$order_fee_log['transfer_content']['targetid']])->find()['ordersn'];
                $order_fee_log['transfer_content']['spin_info'] = json_decode($order_fee_log['transfer_content']['spin_info'],true);
                foreach($order_fee_log['transfer_content']['spin_info'] as $k=>$v){
                    $ginfo = Db::name('centralize_parcel_order_goods')->where(['id'=>$v['gid']])->find();
                    $ginfo['unit'] = Db::name('unit')->where(['code_value'=>$ginfo['unit']])->field('code_name')->find()['code_name'];
                    $order_fee_log['transfer_content']['spin_info'][$k]['ginfo'] = $ginfo;
                    $order_fee_log['transfer_content']['spin_info'][$k]['parcel_no'] = Db::name('centralize_parcel_order')->where(['id'=>$order_fee_log['transfer_content']['orderid']])->field('ordersn')->find()['ordersn'];
                }
            }
            #包裹附加
            if($order_fee_log['service_status']==13){
                $order_fee_log['attach_content'] = Db::name('centralize_parcel_add_eli')->where(['id'=>$order_fee_log['attach_id']])->find();
                $order_fee_log['attach_content']['content'] = json_decode($order_fee_log['attach_content']['content'],true);
            }
            #包裹剔除
            if($order_fee_log['service_status']==14){
                $order_fee_log['attach_content'] = Db::name('centralize_parcel_add_eli')->where(['id'=>$order_fee_log['reject_id']])->find();
                $order_fee_log['attach_content']['content'] = json_decode($order_fee_log['attach_content']['content'],true);
            }
            #包裹弃货
            if($order_fee_log['service_status']==6 || $order_fee_log['service_status']==7){
                $order_fee_log['qihuo_content'] = json_decode($order_fee_log['qihuo_content'],true);
            }
            #国内转运
            if($order_fee_log['service_status']==3){
                $order_fee_log['zhuanyun_content'] = json_decode($order_fee_log['zhuanyun_content'],true);
            }

            #包裹操作状态
            $status2_name = Db::name('centralize_parcel_status')->where(['status_id'=>$package['status2']])->find();
            $status_name = Db::name('centralize_parcel_operation_status')->where(['status_id'=>$status2_name['pid']])->find();
            $package['status_name'] = '【'.$status_name['status_name'].'】'.$status2_name['status_name'];

            $currency = Db::name('centralize_currency')->select();
            $express = Db::name('centralize_diycountry_content')->where(['pid'=>6])->select();
            #单位
            $unit = Db::name('unit')->select();
            #包装材质
            $package_type = Db::name('packing_type')->select();
            #奢侈品牌
            $brand = Db::name('centralize_diycountry_content')->where(['pid'=>8])->select();
            #属性
//            $value = $this->menu2(2);
            $value = json_encode($this->menu_gahter2(2),true);
            $process = Db::name('centralize_process_list')->where(['pid'=>0,'display'=>0])->order('displayorders asc')->select();

            return view('index/shop_backend/waybill_info',compact('goods','package','order_fee_log','currency','express','unit','package_type','brand','value','process','process_ids','company_id','company_type'));
        }
    }

    #账单信息
    public function parcel_bill(Request $request){
        $dat = input();

        $order = Db::name('centralize_order_fee_log')->where(['orderid'=>$dat['id']])->order('id desc')->select();
        foreach($order as $k=>$v){
            $order[$k]['status_name'] = $this->get_orderstatusName($v['service_status'],0);
            $order[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);
        }
        return view('index/shop_backend/parcel_bill',compact('order'));
    }

    //订单状态
    private function get_orderstatusName($sta,$id){
        $status_name = '';
        if($sta==1){
            $status_name = '外观签收';
        }elseif($sta==2){
            $status_name = '拍照查验';
        }elseif($sta==3){
            $status_name = '视频查验';
        }elseif($sta==4){
            $status_name = '直播查验';
        }elseif($sta==5){
            $status_name = '包裹拒收';
        }elseif($sta==6){
            $status_name = '国内转运';
        }elseif($sta==7){
            $status_name = '退回启运';
        }elseif($sta==8){
            $status_name = '就地弃货';
        }elseif($sta==9){
            $status_name = '定点弃货';
        }elseif($sta==10){
            $status_name = '公益弃货';
        }elseif($sta==11){
            $status_name = '常恒入库';
        }elseif($sta==12){
            $status_name = '恒温入库';
        }elseif($sta==13){
            $status_name = '包裹分拆';
        }elseif($sta==14){
            $status_name = '包裹合并';
        }elseif($sta==15){
            $status_name = '物品转移';
        }elseif($sta==16){
            $status_name = '包裹附加';
        }elseif($sta==17){
            $status_name = '包裹剔除';
        }elseif($sta==18){
            $status_name = '国内转运';
        }elseif($sta==19){
            $status_name = '跨境集运';
        }
        return $status_name;
    }

    #账单详情
    public function parcel_billinfo(Request $request){
        $dat = input();

        $order_feelog = Db::name('centralize_order_fee_log')->where(['id'=>$dat['id']])->find();
        $order_feelog['createtime'] = date('Y-m-d H:i',$order_feelog['createtime']);

        $paylog = Db::name('centralize_pay_log')->where(['orderid'=>$order_feelog['id']])->find();

        switch($paylog['paytype']){
            case 1:
                $paylog['paytype_name'] = '余额支付';break;
        }

        switch($paylog['status']){
            case 0:
                $paylog['status_name'] = '未支付';break;
            case 1:
                $paylog['status_name'] = '已付清';break;
            case 2:
                $paylog['status_name'] = '未挂账';break;
            case 3:
                $paylog['status_name'] = '未付清';break;
            case 4:
                $paylog['status_name'] = '已挂账';break;
        }

        $info = Db::name('centralize_parcel_order')
            ->alias('a')
            ->join('centralize_warehouse_list b','a.warehouse_id=b.id')
            ->where(['a.id'=>$order_feelog['orderid']])
            ->field('a.*,b.warehouse_name')
            ->find();

        //包裹状态
        if($order_feelog['service_status']==1 || $order_feelog['service_status']==2 || $order_feelog['service_status']==3 || $order_feelog['service_status']==4){
            $order_feelog['status_name'] = '';
            switch($order_feelog['service_status']){
                case 1:
                    $order_feelog['status_name'] = '外观查验';break;
                case 2:
                    $order_feelog['status_name'] = '拍照查验';
                    $info['pic_file'] = json_decode($info['pic_file'],true);
                    break;
                case 3:
                    $order_feelog['status_name'] = '视频查验';
                    $info['pic_file'] = json_decode($info['pic_file'],true);
                    break;
                case 4:
                    $order_feelog['status_name'] = '直播查验';break;
            }

            if($order_feelog['order_status']==0){
                $order_feelog['status_name'] .= '[待签收]';
                if($info['face_signid']>0){
                    $name = Db::name('centralize_inspection_status')->where('id',$info['face_signid'])->field('name')->find()['name'];
                    $order_feelog['status_name'] .= '['.$name.']';
                }
            }elseif($order_feelog['order_status']==1){
                $order_feelog['status_name'] .= '[已提交待查验]';
            }elseif($order_feelog['order_status']==2){
                $order_feelog['status_name'] .= '[已查验待付款]';
            }elseif($order_feelog['order_status']==3){
                $order_feelog['status_name'] .= '[已签收]';
            }
        }
        elseif($order_feelog['service_status']==5){
            $order_feelog['status_name'] = '拒收';
            if($order_feelog['order_status']==5){
                $order_feelog['status_name'] .= '[已提交待确认]';
            }elseif($order_feelog['order_status']==6){
                $order_feelog['status_name'] .= '[已确认待付款]';
            }elseif($order_feelog['order_status']==7){
                $order_feelog['status_name'] .= '[已拒收]';
            }
        }
        elseif($order_feelog['service_status']==6 || $order_feelog['service_status']==7) {
            #转运
            $order_feelog['status_name'] = '';
            switch($order_feelog['service_status']){
                case 6:
                    $order_feelog['status_name'] = '国内转运';break;
                case 7:
                    $order_feelog['status_name'] = '退回启运';break;
            }

            if($order_feelog['order_status']==8){
                $order_feelog['status_name'] .= '[已提交待确认]';
            }elseif($order_feelog['order_status']==9){
                $order_feelog['status_name'] .= '[已确认待付款]';
            }elseif($order_feelog['order_status']==10){
                $order_feelog['status_name'] .= '[已付款待寄出]';
            }elseif($order_feelog['order_status']==11){
                $order_feelog['status_name'] .= '[已寄出待签收]';
            }elseif($order_feelog['order_status']==12){
                $order_feelog['status_name'] .= '[已签收]';
            }
            $order_feelog['zhuanyun_content'] = json_decode($order_feelog['zhuanyun_content'],true);
            if($order_feelog['order_status']==11 || $order_feelog['order_status']==12){
                $order_feelog['express_content'] = json_decode($order_feelog['express_content'],true);
                $order_feelog['express_content'][0] = Db::name('customs_express_company_code')->where(['id'=>$order_feelog['express_content'][0]])->field('name')->find()['name'];
            }
        }
        elseif($order_feelog['service_status']==8 || $order_feelog['service_status']==9 || $order_feelog['service_status']==10) {
            #弃货
            $order_feelog['status_name'] = '';
            switch($order_feelog['service_status']){
                case 8:
                    $order_feelog['status_name'] = '就地弃货';break;
                case 9:
                    $order_feelog['status_name'] = '定点弃货';
                    $order_feelog['qihuo_content'] = json_decode($order_feelog['qihuo_content'],true);
                    break;
                case 10:
                    $order_feelog['status_name'] = '公益弃货';
                    $order_feelog['qihuo_content'] = json_decode($order_feelog['qihuo_content'],true);
                    break;
            }

            if($order_feelog['order_status']==14){
                $order_feelog['status_name'] .= '[已提交待确认]';
            }elseif($order_feelog['order_status']==15){
                $order_feelog['status_name'] .= '[已确认待付款]';
            }elseif($order_feelog['order_status']==16){
                $order_feelog['status_name'] .= '[已付款待寄出]';
            }elseif($order_feelog['order_status']==17){
                $order_feelog['status_name'] .= '[已寄出待签收]';
            }elseif($order_feelog['order_status']==18){
                $order_feelog['status_name'] .= '[已弃货]';
            }

            if(($order_feelog['order_status']==17 || $order_feelog['order_status']==18) && $info['status']!=6){
                $order_feelog['express_content'] = json_decode($order_feelog['express_content'],true);
                $order_feelog['express_content'][0] = Db::name('customs_express_company_code')->where(['id'=>$order_feelog['express_content'][0]])->field('name')->find()['name'];
            }
        }
        elseif($order_feelog['service_status']==11 || $order_feelog['service_status']==12) {
            #入库
            $order_feelog['status_name'] = '';
            switch($order_feelog['service_status']){
                case 11:
                    $order_feelog['status_name'] = '常恒入库';break;
                case 12:
                    $order_feelog['status_name'] = '恒温入库';break;
            }


            if($order_feelog['order_status']==19){
                $order_feelog['status_name'] .= '[已提交待确认]';
            }elseif($order_feelog['order_status']==20){
                $order_feelog['status_name'] .= '[已确认待付款]';
            }elseif($order_feelog['order_status']==21){
                $order_feelog['status_name'] .= '[已付款待入库]';
            }elseif($order_feelog['order_status']==22){
                $order_feelog['status_name'] .= '[已入库]';

            }
        }
        elseif($order_feelog['service_status']==13){
            #分拆

            $order_feelog['status_name'] = '包裹分拆';
            switch($order_feelog['order_status']){
                case 24:
                    $order_feelog['status_name'] .= '[已提交待确认]';break;
                case 25:
                    $order_feelog['status_name'] .= '[已确认待付款]';break;
                case 26:
                    $order_feelog['status_name'] .= '[已付款待分拆]';break;
                case 27:
                    $order_feelog['status_name'] .= '[已分拆]';break;
            }


            #分拆信息
            $order_feelog['spin_content'] = Db::name('centralize_parcel_order_spin')->where(['id'=>$order_feelog['spin_id']])->find();
            $order_feelog['spin_content']['spin_info'] = json_decode($order_feelog['spin_content']['spin_info'],true);
            foreach($order_feelog['spin_content']['spin_info'] as $k=>$v){
                $ginfo = Db::name('centralize_parcel_order_goods')->where(['id'=>$v['gid']])->find();
                $ginfo->unit = Db::name('unit')->where(['code_value'=>$ginfo['unit']])->field('code_name')->find()['code_name'];
                $order_feelog['spin_content']['spin_info'][$k]['parcel_no'] = Db::name('centralize_parcel_order')->where(['id'=>$order_feelog['spin_content']['orderid']])->field('ordersn')->find()['ordersn'];
            }
        }
        elseif($order_feelog['service_status']==14){
            #包裹合并

            $order_feelog['status_name'] = '包裹合并';
            switch($order_feelog['order_status']){
                case 29:
                    $order_feelog['status_name'] .= '[已提交待确认]';break;
                case 30:
                    $order_feelog['status_name'] .= '[已确认待付款]';break;
                case 31:
                    $order_feelog['status_name'] .= '[已付款待合并]';break;
                case 32:
                    $order_feelog['status_name'] .= '[已合并]';break;
            }


            #合并信息
            $order_feelog['parcel_ids'] = explode(',',$order_feelog['parcel_ids']);
            #xx包裹下的xx物品
            $merge_parcel = [];
            $ii=0;
            foreach($order_feelog['parcel_ids'] as $k=>$v){
                if($k>0){
                    $merge_parcel[$ii]['ordersn'] = Db::name('centralize_parcel_order')->where(['id'=>$v])->field('ordersn')->find()['ordersn'];
                    $merge_parcel[$ii]['goods'] = Db::name('centralize_parcel_order_goods')->where(['orderid'=>$v])->select();
                    $ii++;
                }
            }
            foreach($merge_parcel as $k=>$v){
                foreach($v['goods'] as $kk=>$vv){
                    $cate_item = Db::name('jd_goods_category')->whereRaw('find_in_set(id,?)',[$vv['cateid']])->select();
                    $merge_parcel[$k]['goods'][$kk]['cate_item'] = '';
                    foreach($cate_item as $kkk=>$vvv){
                        $merge_parcel[$k]['goods'][$kk]['cate_item'] .= $vvv['catName'].',';
                    }
                    $merge_parcel[$k]['goods'][$kk]['cate_item'] = rtrim($merge_parcel[$k]['goods'][$kk]['cate_item'],',');

                    //属性
                    $good_item = Db::name('centralize_goods_value')->whereRaw('find_in_set(id,?)',[$vv['itemid']])->select();
                    $merge_parcel[$k]['goods'][$kk]['good_item'] = '';
                    foreach($good_item as $kkk=>$vvv){
                        $merge_parcel[$k]['goods'][$kk]['good_item'] .= $vvv['title'].',';
                    }
                    $merge_parcel[$k]['goods'][$kk]['good_item'] = rtrim($merge_parcel[$k]['goods'][$kk]['good_item'],',');

                    //单位
                    $merge_parcel[$k]['goods'][$kk]['unit'] = Db::name('unit')->where('code_value',$vv['unit'])->find()['code_name'];
                }
            }
            $order_feelog['merge_parcel'] = $merge_parcel;
        }
        elseif($order_feelog['service_status']==15){
            #包裹转移
            $order_feelog['status_name'] = '包裹转移';
            switch($order_feelog['order_status']){
                case 33:
                    $order_feelog['status_name'] .= '[已提交待确认]';break;
                case 34:
                    $order_feelog['status_name'] .= '[已确认待付款]';break;
                case 35:
                    $order_feelog['status_name'] .= '[已付款待转移]';break;
                case 36:
                    $order_feelog['status_name'] .= '[已转移]';break;
            }

            #转移信息
            $order_feelog['transfer_content'] = Db::name('centralize_parcel_order_transfer')->where(['id'=>$order_feelog['transfer_id']])->find();
            $order_feelog['transfer_content']['spin_info'] = json_decode($order_feelog['transfer_content']['spin_info'],true);
            foreach($order_feelog['transfer_content']['spin_info'] as $k=>$v){
                $ginfo = Db::name('centralize_parcel_order_goods')->where(['id'=>$v['gid']])->find();
                $ginfo['unit'] = Db::name('unit')->where(['code_value'=>$ginfo['unit']])->field('code_name')->find()['code_name'];
                $order_feelog['transfer_content']['spin_info'][$k]['parcel_no'] = Db::name('centralize_parcel_order')->where(['id'=>$order_feelog['transfer_content']['orderid']])->field('ordersn')->find()['ordersn'];
            }
        }
        elseif($order_feelog['service_status']==16){
            #附加
            $order_feelog['status_name'] = '包裹附加';
            switch($order_feelog['order_status']){
                case 38:
                    $order_feelog['status_name'] .= '[已提交待确认]';break;
                case 39:
                    $order_feelog['status_name'] .= '[已确认待付款]';break;
                case 40:
                    $order_feelog['status_name'] .= '[已付款待附加]';break;
                case 41:
                    $order_feelog['status_name'] .= '[已附加]';break;
            }
        }
        elseif($order_feelog['service_status']==17){
            #剔除
            $order_feelog['status_name'] = '包裹剔除';
            switch($order_feelog['order_status']){
                case 43:
                    $order_feelog['status_name'] .= '[已提交待确认]';break;
                case 44:
                    $order_feelog['status_name'] .= '[已确认待付款]';break;
                case 45:
                    $order_feelog['status_name'] .= '[已付款待剔除]';break;
                case 46:
                    $order_feelog['status_name'] .= '[已剔除]';break;
            }
        }
        elseif($order_feelog['service_status']==18) {
            $order_feelog['status_name'] = '国内转运';

            if($order_feelog['order_status']==48){
                $order_feelog['status_name'] .= '[已提交待确认]';
            }elseif($order_feelog['order_status']==49){
                $order_feelog['status_name'] .= '[已确认待付款]';
            }elseif($order_feelog['order_status']==50){
                $order_feelog['status_name'] .= '[已付款待寄出]';
            }elseif($order_feelog['order_status']==51){
                $order_feelog['status_name'] .= '[已寄出待签收]';
            }elseif($order_feelog['order_status']==52){
                $order_feelog['status_name'] .= '[已签收]';
            }
            $order_feelog['zhuanyun_content'] = json_decode($order_feelog['zhuanyun_content'],true);
            if($order_feelog['order_status']==51 || $order_feelog['order_status']==52){
                $order_feelog['express_content'] = json_decode($order_feelog['express_content'],true);
                $order_feelog['express_content'][0] = Db::name('customs_express_company_code')->where(['id'=>$order_feelog['express_content'][0]])->field('name')->find()['name'];
            }
        }
        elseif($order_feelog['service_status']==19) {
            $order_feelog['status_name'] = '跨境集运';

            if($order_feelog['order_status']==54){
                $order_feelog['status_name'] .= '[已提交待确认]';
            }elseif($order_feelog['order_status']==55){
                $order_feelog['status_name'] .= '[已确认待付款]';
            }elseif($order_feelog['order_status']==56){
                $order_feelog['status_name'] .= '[已付款拟集运]';
            }

            #收件信息
            $order_feelog['address_info'] = Db::name('centralize_user_address')->where(['id'=>$order_feelog['address_id']])->find();
            $order_feelog['address_info']['country_id'] = Db::name('country_code')->where(['code_value'=>$order_feelog['address_info']['country_id']])->field('code_name')->find()['code_name'];
            #线路信息
            $order_feelog['produce_info'] = Db::name('centralize_manage_produce')->where(['id'=>$order_feelog['produce_id']])->find();
        }

        return view('index/shop_backend/parcel_billinfo',compact('order_feelog','info','paylog'));
    }

    #物流信息
    public function express_info(Request $request){
        $dat = input();

        $id = $dat['id'];
        $data = Db::name('centralize_waybill_list')->where(['id'=>$dat['id']])->find();
        $merchant = Db::name('centralize_manage_person')->where(['id'=>$data['merchant_id']])->find();
        $data['merchant_phone'] = $merchant['tel'];
        $data['merchant_name'] = $merchant['name'];
        $data['send_postal'] = json_decode($data['send_postal'],true);
        $data['receive_postal'] = json_decode($data['receive_postal'],true);
        $data['send_address'] = json_decode($data['send_address'],true);
        $data['receive_address'] = json_decode($data['receive_address'],true);
        $data['merchant_warehouse'] = json_decode($data['merchant_warehouse'],true);
        $data['goods_list'] = json_decode($data['goods_list'],true);
        $data['goods_volumn'] = json_decode($data['goods_volumn'],true);
        $data['logistics_channel'] = json_decode($data['logistics_channel'],true);
        $data['logistics_waybill'] = json_decode($data['logistics_waybill'],true);

        if($data['status']>=3) {
            $data['sure_fee_list'] = json_decode($data['sure_fee_list'], true);
            #查找物流运单
            $all_express = Db::name('centralize_waybill_express_no')->where(['waybill_id' => $dat['id']])->select();
            if (!empty($all_express)) {
                $new_express['express'] = [];
                $new_express['express_no'] = [];
                foreach ($all_express as $k => $v) {
                    array_push($new_express['express'], $v['express_id']);
                    array_push($new_express['express_no'], $v['express_no']);
                }
                $data['logistics_waybill']['express'] = array_merge($data['logistics_waybill']['express'], $new_express['express']);
                $data['logistics_waybill']['express_no'] = array_merge($data['logistics_waybill']['express_no'], $new_express['express_no']);
            }

            foreach ($data['goods_list']['valueid'] as $k => $v) {#[1,2],[1,2]
                $value = explode(',', $v);
                foreach ($value as $k2 => $v3) {
                    if (!empty($v3)) {
                        $data['goods_list']['value_select'][$k][$k2] = Db::name('centralize_product_value')->where(['id' => $v3])->find()['name'];
                    }
                }
            }
        }
        $postal = Db::name('centralize_diycountry_content')->where(['pid'=>4])->select();
        $country = Db::name('centralize_diycountry_content')->where(['pid'=>5])->select();
        $merchant = Db::name('centralize_manage_person')->select();
        $unit = Db::name('unit')->select();
        $category = Db::name('centralize_hscode_list')->select();
        $express = Db::name('centralize_diycountry_content')->where(['pid'=>6])->select();
        #奢侈品牌
        $brand = Db::name('customs_travelexpress_brand')->select();
        #属性
        $value = json_encode($this->valuemenu2(2),true);
        #国家区域
        $area = Db::name('centralize_diycountry_content')->where(['pid'=>7])->select();
        $currency = Db::name('currency')->select();

        return view('index/shop_backend/express_info',compact('data','id','postal','country','merchant','unit','express','category','brand','value','area','currency'));
    }

    #执行分拆
    public function spin_off($spinid){
        #开始分拆
        $time = time();
        $spin_info = Db::name('centralize_parcel_order_spin')->where(['id'=>$spinid])->find();
        Db::startTrans();
        try {
            //1-插入新的包裹
            $spin_info['spin_info'] = json_decode($spin_info['spin_info'],true);
            $package_id = 0;
            foreach($spin_info['spin_info'] as $k=>$v){
                $order_goods = Db::name('centralize_parcel_order_goods')->where(['id'=>$v['gid']])->find();
                $parcel = Db::name('centralize_parcel_order_package')->where(['id'=>$order_goods['package_id']])->find();
                if($k==0){
                    #这里设置第一个才插入新的包裹，后续不新增
                    $package_id = Db::name('centralize_parcel_order_package')->insertGetId([
                        'user_id'=>$spin_info['user_id'],
                        'orderid'=>$order_goods['orderid'],
                        'express_id'=>$parcel['express_id'],
                        'express_no'=>$spin_info['new_expressno'],
                        'inspection_method'=>$parcel['inspection_method'],
                        'inspection_matter'=>$parcel['inspection_matter'],
                        'grosswt'=>$parcel['grosswt'],
                        'package'=>$parcel['package'],
                        'volumn'=>$parcel['volumn'],
                        'pic_file'=>$parcel['pic_file'],
                        'delivery_method'=>$parcel['delivery_method'],
                        'status2'=>42,
                        'createtime'=>$time
                    ]);
                }

                Db::name('centralize_parcel_order_goods')->insert([
                    'user_id'=>$spin_info['user_id'],
                    'orderid'=>$order_goods['orderid'],
                    'package_id'=>$package_id,
                    'valueid'=>$order_goods['valueid'],
                    'good_desc'=>$order_goods['good_desc'],
                    'good_num'=>$v['num'],
                    'good_unit'=>$order_goods['good_unit'],
                    'good_currency'=>$order_goods['good_currency'],
                    'good_price'=>$order_goods['good_price'],
                    'good_package'=>$order_goods['good_package'],
                    'brand_type'=>$order_goods['brand_type'],
                    'brand_name'=>$order_goods['brand_name'],
                    'good_remark'=>$order_goods['good_remark'],
                    'createtime'=>$time,
                ]);
            }

            //2、修改原始订单商品表货物信息
            $spin_info['origin_info'] = json_decode($spin_info['origin_info'],true);
            foreach($spin_info['origin_info'] as $k=>$v){
                $order_goods = Db::name('centralize_parcel_order_goods')->where(['id'=>$v['gid']])->find();
                if(empty($v['num'])){
                    Db::name('centralize_parcel_order_goods')->where(['id'=>$v['gid']])->delete();
                }else{
                    Db::name('centralize_parcel_order_goods')->where(['id'=>$v['gid']])->update([
                        'good_num'=>$v['num'],
                    ]);
                }
            }

            Db::commit();
            return 1;
        } catch (\Exception $e) {
            Db::rollBack();
            return -1;
//            return json(['code' => -1, 'msg' => '操作失败：'.$e->getMessage()]);
        }
    }

    #执行合并(N个订单有N个运单，N个运单包裹有N个货物)
    public function merge_parcel($orderid){
        $order_feelog = Db::name('centralize_order_fee_log')->where(['id'=>$orderid])->order('id desc')->find();
        Db::startTrans();
        try {
            $parcel_ids = explode(',',$order_feelog['parcel_ids']);
            #第一个合并目标包裹的信息
            $parcel = Db::name('centralize_parcel_order_package')->where(['id'=>$parcel_ids[0]])->find();
            foreach($parcel_ids as $k=>$v){
                if($k>0){
                    Db::name('centralize_parcel_order_goods')->where(['package_id'=>$v])->update(['orderid'=>$parcel['orderid'],'package_id'=>$parcel['id']]);
                    Db::name('centralize_parcel_order_package')->where(['id'=>$v])->update(['user_id'=>0,'orderid'=>0]);#原包裹信息为空
                }
            }
            Db::commit();
            return 1;
        } catch (\Exception $e) {
            Db::rollBack();
            return -1;
        }
    }

    #执行转移
    public function transfer_parcel_goods($id){
        #开始转移
        $time = time();

        #目标包裹
        $order_feelog = Db::name('centralize_order_fee_log')->where(['id'=>$id])->order('id desc')->find();

        #需要转移的包裹
        $spin_info = Db::name('centralize_parcel_order_transfer')->where(['id'=>$order_feelog['transfer_id']])->find();

        Db::startTrans();
        try {
            //1-插入目标订单商品
            $spin_info['spin_info'] = json_decode($spin_info['spin_info'],true);
            foreach($spin_info['spin_info'] as $k=>$v){
                $order_goods = Db::name('centralize_parcel_order_goods')->where(['id'=>$v['gid']])->find();

                Db::name('centralize_parcel_order_goods')->insert([
                    'user_id'=>$order_feelog['user_id'],
                    'orderid'=>$spin_info['targetid'],
                    'express_id'=>$order_goods['express_id'],
                    'express_no'=>$order_goods['express_no'],
                    'name'=>$order_goods['name'],
                    'money'=>$order_goods['money'],
                    'inspection_method'=>$order_goods['inspection_method'],
                    'inspection_matter'=>$order_goods['inspection_matter'],
                    'ginfo'=>$order_goods['ginfo'],
                    'brand_type'=>$order_goods['brand_type'],
                    'brand_name'=>$order_goods['brand_name'],
                    'brand_desc'=>$order_goods['brand_desc'],
                    'cateid'=>$order_goods['cateid'],
                    'itemid'=>$order_goods['itemid'],
                    'valueid'=>$order_goods['valueid'],
                    'num'=>$v['num'],
                    'netwt'=>$order_goods['netwt'],
                    'grosswt'=>$order_goods['grosswt'],
                    'unit'=>$order_goods['unit'],
                    'package'=>$order_goods['package'],
                    'true_volumn'=>$order_goods['true_volumn'],
                    'delivery_method'=>$order_goods['delivery_method'],
                    'inwarehouse_date'=>$order_goods['inwarehouse_date'],
                    'contact_name'=>$order_goods['contact_name'],
                    'contact_mobile'=>$order_goods['contact_mobile'],
                    'pic_file'=>$order_goods['pic_file'],
                    'remark'=>$order_goods['remark'],
                    'status'=>$order_goods['status'],
                    'status2'=>intval($order_goods['status2']),#已转移状态
                    'createtime'=>$time,
                ]);
            }

            //2、修改原始订单商品表货物信息
            $spin_info['origin_info'] = json_decode($spin_info['origin_info'],true);
            foreach($spin_info['origin_info'] as $k=>$v){
                $order_goods = Db::name('centralize_parcel_order_goods')->where(['id'=>$v['gid']])->find();

                if(empty($v['num'])){
                    Db::name('centralize_parcel_order_goods')->where(['id'=>$v['gid']])->delete();
                }else{
                    Db::name('centralize_parcel_order_goods')->where(['id'=>$v['gid']])->update(['num'=>$v['num']]);
                }
            }

            Db::commit();
            return 1;
        } catch (\Exception $e) {
            Db::rollBack();
            return -1;
        }
    }

    #装箱单
    public function packing_list(Request $request){
        $dat = input();

        if(isset($dat['pa'])){
            if($dat['pa']==1){
                #获取所属订单信息
                $order_type = intval($dat['order_type']);
                $template_id = intval($dat['template_id']);

                $return_data = [];
                if($order_type==1){
                    #商城订单
                    $order = Db::name('website_order_list')->where(['ordersn'=>trim($dat['ordersn'])])->find();
                    if(empty($order)){
                        return json(['code'=>0,'msg'=>'获取失败，系统无此订单','data'=>[]]);
                    }
                    $order['content'] = json_decode($order['content'],true);
                    foreach($order['content']['goods_info'] as $k=>$v){
                        #查询商品是哪个商家
                        $goods_info = Db::connect($this->config)->name('goods')->where(['goods_id'=>$v['good_id']])->find();

                        $return_data['send_company'] = '佛山市钜铭商务资讯服务有限公司';
                        $return_data['sender'] = 'Gogo';
                        $return_data['send_address'] = '佛山市南海区桂城创客邦D611';
                        $return_data['send_number'] = '86329911';
                        $return_data['send_date'] = '';
                        $return_data['consignee'] = '';
                        $return_data['consignee_country'] = '';
                        $return_data['marking_code'] = '';
                        $return_data['from'] = '';
                        $return_data['transportNumber'] = '';

                        if($goods_info['shop_id']>0){
                            #有企业的商品
                            $company = Db::name('website_user_company')->where(['id'=>$goods_info['shop_id']])->find();
                            $return_data['send_company'] = $company['company'];
                            $return_data['sender'] = $company['realname'];
                            $return_data['send_address'] = '';
                            $return_data['send_number'] = $company['mobile'];
                        }

                        foreach($v['sku_info'] as $k2=>$v2){
                            $sku_info = Db::connect($this->config)->name('goods_sku')->where(['sku_id'=>$v2['sku_id']])->find();

                            #不同的规格是不同的商品
                            $return_data['goods'][$k2]['name'] = $goods_info['goods_name'];
                            $return_data['goods'][$k2]['picture'] = $goods_info['goods_image'];
                            $return_data['goods'][$k2]['brand'] = '';
                            if($goods_info['brand_type']==1){
                                #有牌
                                if(!empty($goods_info['brand_name'])){
                                    $return_data['goods'][$k2]['brand'] = $goods_info['brand_name'];
                                }

                                if(!empty($goods_info['brand_id'])){
                                    $return_data['goods'][$k2]['brand'] = Db::name('centralize_diycountry_content')->where(['id'=>$goods_info['brand_id']])->find()['param1'];
                                }
                            }
                            $return_data['goods'][$k2]['price'] = 0;
                            $sku_info['sku_prices'] = json_decode($sku_info['sku_prices'],true);
                            #币种
                            $currency = $sku_info['sku_prices']['currency'][0];
                            $return_data['goods'][$k2]['currency'] = Db::name('centralize_currency')->where(['id'=>$currency])->find()['currency_symbol_standard'];
                            #单价
                            foreach($sku_info['sku_prices']['select_end'] as $k3=>$v3){
                                if($v3==1){
                                    #数值
                                    if($sku_info['sku_prices']['start_num'][$k3] <= $v2['goods_num'] && $sku_info['sku_prices']['end_num'][$k3] >= $v2['goods_num']){
                                        $return_data['goods'][$k2]['price'] = $sku_info['sku_prices']['price'][$k3];
                                    }
                                }
                                elseif($v3==2){
                                    #以上
                                    if($sku_info['sku_prices']['start_num'][$k3] <= $v2['goods_num']){
                                        $return_data['goods'][$k2]['price'] = $sku_info['sku_prices']['price'][$k3];
                                    }
                                }
                            }
                            $return_data['goods'][$k2]['package'] = $v2['goods_num'];
                            $return_data['goods'][$k2]['option_name'] = $sku_info['spec_names'];
                        }
                    }
                }
                elseif($order_type==2){
                    #集运订单(centralize_parcel_order、centralize_parcel_order_goods、centralize_parcel_order_package、
                    $order = Db::name('centralize_parcel_order')->where(['ordersn'=>trim($dat['ordersn'])])->find();
                    if(empty($order)){
                        return json(['code'=>0,'msg'=>'获取失败，系统无此订单','data'=>[]]);
                    }

                    $return_data['send_company'] = '佛山市钜铭商务资讯服务有限公司';
                    $return_data['sender'] = 'Gogo';
                    $return_data['send_address'] = '佛山市南海区桂城创客邦D611';
                    $return_data['send_number'] = '86329911';
                    $return_data['send_date'] = '';
                    $return_data['consignee'] = '';
                    $return_data['consignee_country'] = Db::name('centralize_diycountry_content')->where(['id'=>$order['country']])->find()['param2'];
                    $return_data['marking_code'] = '';
                    $return_data['from'] = '';
                    $return_data['transportNumber'] = '';

                    $order_goods = Db::name('centralize_parcel_order_goods')->where(['orderid'=>$order['id']])->select();
                    foreach($order_goods as $k=>$v){
                        $order_package = Db::name('centralize_parcel_order_package')->where(['id'=>$v['package_id']])->find();
                        if(empty($return_data['transportNumber'])){
                            $return_data['transportNumber'] = $order_package['express_no'];
                        }

                        $return_data['goods'][$k]['name'] = $v['good_desc'];
                        $return_data['goods'][$k]['picture'] = '';
                        $return_data['goods'][$k]['brand'] = $v['brand_name'];
                        #币种
                        $currency = $v['good_currency'];
                        $return_data['goods'][$k]['currency'] = Db::name('centralize_currency')->where(['id'=>$currency])->find()['currency_symbol_standard'];
                        $return_data['goods'][$k]['price'] = $v['good_price'];
                        $return_data['goods'][$k]['package'] = $v['good_num'];
                        $return_data['goods'][$k]['total_price'] = round($v['good_price'] * $v['good_num'],2);
                        $return_data['goods'][$k]['option_name'] = $v['good_desc'];
                        $return_data['goods'][$k]['grosswt'] = $order_package['grosswt'];
                        $return_data['goods'][$k]['size'] = $order_package['volumn'];
                    }
                }

                return json(['code'=>0,'msg'=>'获取成功','data'=>$return_data]);
            }
        }
        else{
            $data = Db::name('order_table_temp')->select();
            foreach($data as $k=>$v){
                if($v['type']==1){
                    $data[$k]['name'] = '装箱单【'.$v['name'].'】';
                }
                elseif($v['type']==2){
                    $data[$k]['name'] = '商业发票【'.$v['name'].'】';
                }
                elseif($v['type']==3){
                    $data[$k]['name'] = '装箱单&商业发票【'.$v['name'].'】';
                }
            }

            return view('index/shop_backend/packing_list',compact('data'));
        }
    }

    //跨境转运
    #出口申报
    public function transport_process1(Request $request){
        echo '<h1>Coming Soon~</h1>';
    }
    #跨境运输
    public function transport_process2(Request $request){
        echo '<h1>Coming Soon~</h1>';
    }
    #进口申报
    public function transport_process3(Request $request){
        echo '<h1>Coming Soon~</h1>';
    }
    #本地转运
    public function transport_process4(Request $request){
        echo '<h1>Coming Soon~</h1>';
    }

    #支付管理
    public function payment_manage(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);#0商城，1官网

        if($request->isAjax()){
            $data = Db::name('website_basic')->where('company_id',$company_id)->find();
            $res = '';

            $paypal = [];
            if($dat['pay_method']==2){
                if(!empty($dat['paypal']['client_id']) && !empty($dat['paypal']['client_secret'])){
                    $paypal['client_id'] = trim($dat['paypal']['client_id']);
                    $paypal['client_secret'] = trim($dat['paypal']['client_secret']);
                    $paypal['is_through'] = intval($dat['paypal']['is_through']);
                    $paypal = json_encode($paypal,true);
                }
            }

            if(!empty($data)){
                $res = Db::name('website_basic')->where('company_id',$company_id)->update([
                    'pay_method'=>$dat['pay_method'],
                    'paypal'=>$paypal,
                    'cash_on_delivery'=>$dat['cash_on_delivery'],#货到付款
                    'down_payment'=>$dat['down_payment'],#预付定金
                    'prepaid_method'=>$dat['prepaid_method'],#预付方式
                    'prepaid_percent'=>$dat['prepaid_percent'],#按比例
                    'prepaid_currency'=>$dat['prepaid_currency'],#按定额-币种
                    'prepaid_amount'=>$dat['prepaid_amount']#按定额-金额
                ]);
            }
            else{
                $res = Db::name('website_basic')->insert([
                    'company_id'=>$company_id,
                    'company_type'=>$company_type,
                    'pay_method'=>$dat['pay_method'],
                    'paypal'=>$paypal,
                    'cash_on_delivery'=>$dat['cash_on_delivery'],#货到付款
                    'down_payment'=>$dat['down_payment'],#预付定金
                    'prepaid_method'=>$dat['prepaid_method'],#预付方式
                    'prepaid_percent'=>$dat['prepaid_percent'],#按比例
                    'prepaid_currency'=>$dat['prepaid_currency'],#按定额-币种
                    'prepaid_amount'=>$dat['prepaid_amount']#按定额-金额
                ]);
            }

            if($res){
                return json(['code' => 0, 'msg' => '保存成功！']);
            }
            else{
                return json(['code' => -1, 'msg' => '暂无修改！']);
            }

        }else{
            $data = Db::name('website_basic')->where(['company_id'=>$company_id,'company_type'=>$company_type])->find();

            if(empty($data)){
                $data = ['slogo'=>'','logo'=>'','name'=>'','desc'=>'','keywords'=>'','color'=>'','color_inner'=>'#ffffff','color_word'=>'#ffffff','is_website'=>0,'font_family'=>"Microsoft JhengHei, 微軟正黑體, Arial, sans-serif",'pay_method'=>0,'cash_on_delivery'=>1,'down_payment'=>1,'prepaid_method'=>1,'prepaid_percent'=>'','prepaid_currency'=>'','prepaid_amount'=>'','paypal'=>['client_id'=>'','client_secret'=>'','is_through'=>0]];
            }
            else{
                if(empty($data['paypal'])){
                    $data['paypal'] = ['client_id'=>'','client_secret'=>'','is_through'=>0];
                }
                else{
                    $data['paypal'] = json_decode($data['paypal'],true);
                }
            }

            $currency = Db::name('centralize_currency')->select();

            return view('index/shop_backend/payment_manage',compact('company_id','company_type','data','currency'));
        }
    }
    #企业网店==============================================end

    #企业群组==============================================start
    public function website_group(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $company_type = 0;#0商城，1官网

        $company = Db::name('website_user_company')->where(['user_id'=>session('account.id'),'status'=>0])->select();

//        $typ = intval($dat['typ']);//企业类型，0商城，1网站
        $typ = 0;
        $company_id = intval($dat['company_id']);
        $tab = isset($dat['tab'])?trim($dat['tab']):'show_direction';

        #客服群组-显示位置
        $group = Db::name('merchsite_customer_group')->where('company_id',$company_id)->find();
        if(empty($group)){
            $group = ['direction'=>'','name'=>'','staff_ids'=>'','worktime'=>'周一至周六 08:00-12:00 13:30-18:00'];
        }else{
            if(empty($group['worktime'])){
                $group['worktime'] = '周一至周六 08:00-12:00 13:30-18:00';
            }
        }

        $staffs = Db::name('centralize_manage_person')->where(['company_id'=>$company_id])->select();
        $staffs = json_encode($staffs,true);

        $website['title'] = $this->website_name;
        $website['keywords'] = $this->website_keywords;
        $website['description'] = $this->website_description;
        $website['ico'] = $this->website_ico;
        $website['sico'] = $this->website_sico;
        $website['tel'] = $this->website_tel;
        $website['email'] = $this->website_email;
        $website['copyright'] = $this->website_copyright;
        $website['color'] = $this->website_color;
        $website['color_word'] = $this->website_colorword;
        $website['color_adorn'] = $this->website_coloradorn;
        $website['color_head'] = $this->website_colorhead;
        $website['website_contact'] = $this->website_contact;
        $website['website_canonical'] = $this->website_canonical;
        $website['website_og'] = $this->website_og;

        return view('index/group/website_group',compact('company','company_id','company_type','tab','group','staffs','website'));
    }

    #在线客服-位置配置
    public function save_customer_direction(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);#0商城，1官网

        if($request->isAjax()){

            $data = Db::name('merchsite_customer_group')->where('company_id',$company_id)->find();
            if(!empty($data)){
                $res = Db::name('merchsite_customer_group')->where('company_id',$company_id)->update([
                    'direction'=>intval($dat['direction']),
                ]);
            }
            else{
                $res = Db::name('merchsite_customer_group')->insert([
                    'company_id'=>$company_id,
                    'direction'=>intval($dat['direction']),
                ]);
            }

            if($res){
                return json(['code' => 0, 'msg' => '保存成功']);
            }
            else{
                return json(['code' => -1, 'msg' => '暂无修改']);
            }

        }else{


            return view('',compact('data','website'));
        }
    }

    #在线客服-群组配置
    public function save_customer_group(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);#0商城，1官网

        if($request->isAjax()){
            $data = Db::name('merchsite_customer_group')->where('company_id',$company_id)->find();
            if(!empty($data)){
                $res = Db::name('merchsite_customer_group')->where('company_id',$company_id)->update([
                    'name'=>trim($dat['name']),
                    'staff_ids'=>trim($dat['staff_ids']),
                    'worktime'=>trim($dat['worktime']),
                ]);
            }
            else{
                $res = Db::name('merchsite_customer_group')->insert([
                    'company_id'=>$company_id,
                    'name'=>trim($dat['name']),
                    'staff_ids'=>trim($dat['staff_ids']),
                    'worktime'=>trim($dat['worktime']),
                ]);
            }

            if($res){
                return json(['code' => 0, 'msg' => '保存成功']);
            }
            else{
                return json(['code' => -1, 'msg' => '暂无修改']);
            }

        }else{
            $data = Db::name('merchsite_customer_group')->where('company_id',$company_id)->find();

            return view('',compact('data','website','staffs'));
        }
    }

    #在线客服-场景配置
    public function comlang_manage(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);#0商城，1官网
        $pid = isset($dat['pid'])?intval($dat['pid']):0;

        if(isset($dat['pa'])){
            $limit = $request->get('limit');
            $page = $request->get('page') - 1;
            if ($page != 0) {
                $page = $limit * $page;
            }
            $where =['pid'=>0,'company_id'=>$company_id];

            $count = Db::name('website_chatlanguage')->where($where)->count();
            $rows = DB::name('website_chatlanguage')
                ->where($where)
                ->limit($page . ',' . $limit)
                ->select();


            foreach($rows as $k=>&$v){

            }

            return json(['code'=>0,'count'=>$count,'data'=>$rows]);
        }else{
            return view('index/group/comlang_manage',compact('company_id','company_type'));
        }
    }

    #在线客服-保存场景
    public function save_comlang(Request $request){
        $dat = input();
        $id = isset($dat['id'])?intval($dat['id']):0;
        $pid = isset($dat['pid'])?intval($dat['pid']):0;
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);#0商城，1官网

        if($request->isAjax()){
            $res = '';
            if($id>0){
                $res = Db::name('website_chatlanguage')->where(['id'=>$id])->update([
                    'name'=>trim($dat['name'])
                ]);
            }else{
                $res = Db::name('website_chatlanguage')->insert([
                    'pid'=>$pid,
                    'company_id'=>$company_id,
                    'name'=>trim($dat['name'])
                ]);
            }

            if($res){
                return json(['code'=>0,'msg'=>'保存成功']);
            }
        }else{
            $data = ['name'=>''];

            if($id>0){
                $data = Db::name('website_chatlanguage')->where(['id'=>$id])->find();
            }

            return view('index/group/save_comlang',compact('data','id','pid','company_id','company_type'));
        }
    }

    #在线客服-删除场景
    public function del_comlang(Request $request){
        $dat = input();
        $id = isset($dat['id'])?intval($dat['id']):0;

        $res = Db::name('website_chatlanguage')->where(['id'=>$id])->delete();
        if($res){
            Db::name('website_chatlanguage')->where(['pid'=>$id])->delete();
            return json(['code'=>0,'msg'=>'删除成功']);
        }
    }

    #在线客服-常用语
    public function comlang_manage2(Request $request){
        $dat = input();
        $pid = isset($dat['pid'])?intval($dat['pid']):0;
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);#0商城，1官网

        if(isset($dat['pa'])){
            $limit = $request->get('limit');
            $page = $request->get('page') - 1;
            if ($page != 0) {
                $page = $limit * $page;
            }
            $where =['pid'=>$pid,'company_id'=>$company_id];

            $count = Db::name('website_chatlanguage')->whereRaw('pid<>0 and company_id='.$company_id)->count();
            $rows = DB::name('website_chatlanguage')
                ->whereRaw('pid<>0 and company_id='.$company_id)
                ->limit($page . ',' . $limit)
                ->select();


            foreach($rows as $k=>$v){
                $rows[$k]['parent_name'] = DB::name('website_chatlanguage')->where(['id'=>$v['pid']])->find()['name'];
            }

            return json(['code'=>0,'count'=>$count,'data'=>$rows]);
        }
        else{
            return view('index/group/comlang_manage2',compact('pid','company_id','company_type'));
        }
    }

    #在线客服-保存常用语
    public function save_comlang2(Request $request){
        $dat = input();
        $id = isset($dat['id'])?intval($dat['id']):0;
        $pid = isset($dat['pid'])?intval($dat['pid']):0;
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);#0商城，1官网

        if($request->isAjax()){
            $res = '';
            if($id>0){
                $res = Db::name('website_chatlanguage')->where(['id'=>$id])->update([
                    'pid'=>$pid,
                    'name'=>trim($dat['name'])
                ]);
            }else{
                $res = Db::name('website_chatlanguage')->insert([
                    'pid'=>$pid,
                    'company_id'=>$company_id,
                    'name'=>trim($dat['name'])
                ]);
            }

            if($res){
                return json(['code'=>0,'msg'=>'保存成功']);
            }
        }else{
            $data = ['name'=>'','pid'=>0];

            if($id>0){
                $data = Db::name('website_chatlanguage')->where(['id'=>$id])->find();
            }

            $parent = Db::name('website_chatlanguage')->where(['pid'=>0,'company_id'=>$company_id])->select();
            return view('index/group/save_comlang2',compact('data','id','pid','company_id','company_type','parent'));
        }
    }

    #在线客服-删除常用语
    public function del_comlang2(Request $request){
        $dat = input();
        $id = isset($dat['id'])?intval($dat['id']):0;

        $res = Db::name('website_chatlanguage')->where(['id'=>$id])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功']);
        }
    }

    #在线客服-角色管理
    public function group_member_role(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);#0商城，1官网

        if(isset($dat['pa'])){
            $limit = $request->get('limit');
            $page = $request->get('page') - 1;
            if ($page != 0) {
                $page = $limit * $page;
            }
            $keywords = isset($dat['keywords'])?trim($dat['keywords']):'';
            $count = Db::name('centralize_manage_level')
                ->where(['company_id' => $company_id])
                ->whereRaw('name like "%'.$keywords.'%"')
                ->count();
            $list = Db::name('centralize_manage_level')
                ->where(['company_id' => $company_id])
                ->whereRaw('name like "%'.$keywords.'%"')
                ->limit($page . ',' . $limit)
                ->order('id asc')
                ->select();

            foreach($list as $k=>&$v){
                $v['createtime'] = date('Y-m-d H:i:s', $v['createtime']);
            }
            return json(['code' => 0, 'msg'=>'','count' => $count, 'data' => $list]);
        }
        else{

            return view('index/group/group_member_role',compact('company_id','company_type'));
        }
    }

    #在线客服-保存角色
    public function save_group_member_role(Request $request){
        $dat = input();
        $id = isset($dat['id'])?intval($dat['id']):0;
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);#0商城，1官网

        if($request->isAjax()){
            if($id>0){
                $res = Db::name('centralize_manage_level')->where(['id'=>$id])->update([
                    'name'=>trim($dat['name']),
                    'desc'=>trim($dat['desc']),
                    'authList'=>$dat['authList'],
                    'distribution_auth'=>intval($dat['distribution_auth']),
                    'data'=>json_encode($dat['data'],true),
                    'connect'=>json_encode($dat['connect'],true),
                    'commission_type'=>intval($dat['commission_type']),
                ]);
            }else{
                $res = Db::name('centralize_manage_level')->insert([
                    'company_id'=>$company_id,
                    'name'=>trim($dat['name']),
                    'desc'=>trim($dat['desc']),
                    'authList'=>$dat['authList'],
                    'distribution_auth'=>intval($dat['distribution_auth']),
                    'data'=>json_encode($dat['data'],true),
                    'connect'=>json_encode($dat['connect'],true),
                    'commission_type'=>intval($dat['commission_type']),
                    'createtime'=>time()
                ]);
            }

            if($res){
                return json(['code'=>0,'msg'=>'保存成功！']);
            }
            else{
                return json(['code'=>-1,'msg'=>'暂无修改']);
            }
        }else{
            $info = ['name'=>'','desc'=>'','authList'=>'','distribution_auth'=>'','data'=>['data_auth'=>'','view_up'=>'','view_down'=>''],'connect'=>['connect_auth'=>'','connect_up'=>'','connect_down'=>''],'commission_type'=>''];
            if($id>0){
                $info = Db::name('centralize_manage_level')->where(['id'=>$id,'company_id'=>$company_id])->find();
                $info['authList'] = json_decode($info['authList'],true);
                $info['data'] = json_decode($info['data'],true);
                $info['connect'] = json_decode($info['connect'],true);
            }

            #获取功能权限列表
//            $authList = Db::name('centralize_manage_menu')->where(['status'=>0])->order('id asc')->select();

            return view('index/group/save_group_member_role',compact('id','info','authList','company_id','company_type'));
        }
    }

    #在线客服-删除角色
    public function del_group_member_role(Request $request){
        $dat = input();
        $role_id = intval($dat['id']);
        $company_id = intval($dat['company_id']);

        $count = Db::name('centralize_manage_person')->where(['role_id'=>$role_id,'company_id'=>$company_id])->count();
        if($count>=1){
            return json(['code'=>-1,'msg'=>'删除失败，企业存在此角色员工！']);
        }

        $res = Db::name('centralize_manage_level')->where(['id'=>$role_id])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功！']);
        }
    }

    #在线客服-组员管理
    public function group_member(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);#0商城，1官网

        if(isset($dat['pa'])){
            $limit = $request->get('limit');
            $page = $request->get('page') - 1;
            if ($page != 0) {
                $page = $limit * $page;
            }
            $where =['company_id'=>$company_id];
            $keyword = isset($dat['keywords']) ? trim($dat['keywords']) : '';

            $count = Db::name('centralize_manage_person')->where($where)->where('name', 'like', '%'.$keyword.'%')->count();
            $rows = DB::name('centralize_manage_person')->where($where)
                ->where('name', 'like', '%'.$keyword.'%')
                ->limit($page . ',' . $limit)
                ->order('id asc')
                ->select();

            foreach ($rows as &$item) {
                $item['role_name'] = Db::name('centralize_manage_level')->where(['id'=>$item['role_id']])->field('name')->find()['name'];
                if($item['status']==0){
                    $item['status_name'] = '待认证';
                }elseif($item['status']==1) {
                    $item['status_name'] = '已认证';
                }
                $item['createtime'] = date('Y-m-d H:i:s', $item['createtime']);
            }

            return json(['code'=>0,'count'=>$count,'data'=>$rows]);
        }else{

            return view('index/group/group_member',compact('company_id','company_type'));
        }
    }

    #在线客服-保存组员管理
    public function save_group_member(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']);#0商城，1官网
        $id = isset($dat['id'])?intval($dat['id']):0;

        if($request->isAjax()){
            $tel='';$email='';
            $manage_person = Db::name('centralize_manage_person')->where(['gogo_id'=>session('account.id')])->find();

            if(intval($dat['type'])==1){
                if (!preg_match('/^((13[0-9])|(14[5,7])|(15[0-3,5-9])|(17[0,3,5-8])|(18[0-9])|166|198|199|(147))\\d{8}$/',trim($dat['tel']))) {
                    return json(['msg' => '手机号码格式不对', 'code' => -1]);
                }
                $tel = trim($dat['tel']);
            }elseif(intval($dat['type'])==2){
                if (!filter_var(trim($dat['email']), FILTER_VALIDATE_EMAIL)) {
                    return json(['code' => -1, 'msg' => '电子邮箱格式不正确']);
                }
                $email = trim($dat['email']);
            }

            if($id>0){
                $res = Db::name('centralize_manage_person')->where(['id'=>$id])->update([
                    'name'=>trim($dat['name']),
                    'type'=>intval($dat['type']),
                    'country_code'=>$dat['country_code'],
                    'tel'=>$tel,
                    'email'=>$email,
                    'role_id'=>intval($dat['role_id']),
                ]);
            }
            else{
                #查询当前企业是否已添加该邮箱或手机号有无
                if($dat['type']==1){
                    #手机验证
                    $is_have = Db::name('centralize_manage_person')->where(['tel'=>trim($dat['tel']),'company_id'=>$company_id])->find();
                }elseif($dat['type']==2){
                    #邮箱验证
                    $is_have = Db::name('centralize_manage_person')->where(['email'=>trim($dat['email']),'company_id'=>$company_id])->find();
                }

                if(!empty($is_have['id'])){
                    return json(['code'=>-1,'msg'=>'当前企业下的人员手机号或邮箱号已存在，请勿重复添加！']);
                }

                $time = time();

                #先查询用户表有无此用户
                $isHaveUser = '';
                if(!empty($tel)){
                    $isHaveUser = Db::name('website_user')->where(['phone'=>$tel])->find();
                }elseif(!empty($email)){
                    $isHaveUser = Db::name('website_user')->where(['email'=>$email])->find();
                }

                if(empty($isHaveUser)){
                    #生成各系统用户
                    $post_data = json_encode(['realname'=>$dat['name'],'phone'=>$tel,'email'=>$email,'area_code'=>$dat['country_code']],true);
                    $gogo_id = httpRequest2('https://rte.gogo198.cn/?s=api/generate_member',$post_data,array(
                        'Content-Type: application/json; charset=utf-8',
                        'Content-Length:' . strlen($post_data),
                        'Cache-Control: no-cache',
                        'Pragma: no-cache'
                    ));// 必须声明请求头);
                }else{
                    $gogo_id = $isHaveUser['id'];
                }

                $res = Db::name('centralize_manage_person')->insertGetId([
                    'name'=>trim($dat['name']),
                    'type'=>intval($dat['type']),
                    'company_id'=>$company_id,
                    'country_code'=>$dat['country_code'],
                    'tel'=>$tel,
                    'email'=>$email,
                    'role_id'=>intval($dat['role_id']),
//                    'stru_id'=>intval($dat['stru_id']),
//                    'duty'=>intval($dat['duty']),
                    'agent_id'=>'',
                    'status'=>0,
                    'pid'=>$manage_person['id'],
                    'enterprise_id'=>'',
                    'gogo_id'=>$gogo_id,
                    'createtime'=>$time,
                ]);

                /*查询当前企业的管理员角色*/
                $type = Db::name('centralize_manage_person')->where(['company_id'=>$company_id,'pid'=>0])->field('role_id')->find()['role_id'];

                $company = Db::name('website_user_company')->where(['company_id'=>$company_id])->find();
                //通知
                if($dat['type']==1){
                    #1.1、手机,发送链接去手机打开注册
                    $post_data = [
                        'country_code'=>$dat['country_code'],
                        'mobiles'=>trim($dat['tel']),
                        'content'=>'您好！'.$manage_person['name'].'已为您注册['.$company['company_name'].']的企业人员。请点击链接进行认证：https://rte.gogo198.cn/?s=main/staff_reg&id='.base64_encode($res).' 【GOGO】',
                    ];

                    $post_data = json_encode($post_data,true);
                    $res = httpRequest2('https://decl.gogo198.cn/api/sendmsg_jumeng',$post_data,array(
                        'Content-Type: application/json; charset=utf-8',
                        'Content-Length:' . strlen($post_data),
                        'Cache-Control: no-cache',
                        'Pragma: no-cache'
                    ));// 必须声明请求头);
                }elseif($dat['type']==2){
                    #1.2、邮箱,发送链接去邮箱打开注册
//                    $data['email'] = '947960547@qq.com';
                    $post_data = json_encode(['email'=>trim($dat['email']),'title'=>'邀请您成为'.$company['company_name'].'的企业成员','content'=>'
您好！'.$manage_person['name'].'已为您注册['.$company['company_name'].']的成员。请点击链接进行认证：https://rte.gogo198.cn/?s=main/staff_reg&id='.base64_encode($res)],true);
                    $res = httpRequest2('https://shop.gogo198.cn/foll/public/?s=api/sendemail/index',$post_data,array(
                        'Content-Type: application/json; charset=utf-8',
                        'Content-Length:' . strlen($post_data),
                        'Cache-Control: no-cache',
                        'Pragma: no-cache'
                    ));
                }
            }

            if($res){
                return json(['code'=>0,'msg'=>'保存成功']);
            }
            else{
                return json(['code'=>-1,'msg'=>'暂无修改']);
            }
        }
        else{
            $manage_person = Db::name('centralize_manage_person')->where(['gogo_id'=>session('account.id')])->find();
            $info = ['name'=>'','tel'=>'','agent_type'=>'','type'=>1,'email'=>'','role_id'=>0,'status'=>0,'pid'=>$manage_person['id'],'agent_id'=>'','stru_id'=>'','duty'=>''];
            if($id>0){
                $info = Db::name('centralize_manage_person')->where(['id'=>$id])->find();
            }

            #获取角色
            $role = Db::name('centralize_manage_level')->where(['company_id'=>$company_id])->select();

            #获取架构（废弃）
//            $stru = Db::name('centralize_manage_structure')->where(['uid'=>$manage_info['id']])->get();
//            $stru = objectToArrays($stru);
            $stru = [];

            #手机号码区号
            $country_code = Db::name('centralize_diycountry_content')->where(['pid'=>5])->select();

            return view('index/group/save_group_member',compact('id','info','uid','role','manage_agent','stru','country_code','company_id','company_type'));
        }
    }

    #在线客服-删除组员管理
    public function del_group_member(Request $request){
        $dat = input();
        $id = intval($dat['id']);
        $res = Db::name('centralize_manage_person')->where(['id'=>$id])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功！']);
        }
    }
    #企业群组==============================================end
    
    #企业服务==============================================start
    public function website_ai(Request $request){
        $dat = input();

        $company = Db::name('website_user_company')->where(['user_id'=>session('account.id'),'status'=>0])->select();

//        $typ = intval($dat['typ']);//企业类型，0商城，1网站
        $typ = 0;
        $company_id = intval($dat['company_id']);
        $tab = isset($dat['tab'])?trim($dat['tab']):'knowledge';

        $website['title'] = $this->website_name;
        $website['keywords'] = $this->website_keywords;
        $website['description'] = $this->website_description;
        $website['ico'] = $this->website_ico;
        $website['sico'] = $this->website_sico;
        $website['tel'] = $this->website_tel;
        $website['email'] = $this->website_email;
        $website['copyright'] = $this->website_copyright;
        $website['color'] = $this->website_color;
        $website['color_word'] = $this->website_colorword;
        $website['color_adorn'] = $this->website_coloradorn;
        $website['color_head'] = $this->website_colorhead;
        $website['website_contact'] = $this->website_contact;
        $website['website_canonical'] = $this->website_canonical;
        $website['website_og'] = $this->website_og;

        return view('index/ai/website_ai',compact('company','company_id','tab','website'));
    }
    
    #知识库
    public function knowledge_list(Request $request){
        $dat = input();
        $company_id =  intval($dat['company_id']);

        if(isset($dat['pa'])){
            $limit = $request->get('limit');
            $page = $request->get('page') - 1;
            if ($page != 0) {
                $page = $limit * $page;
            }
            $where =['cid'=>$company_id];
//            $keyword = isset($dat['keywords']) ? trim($dat['keywords']) : '';
//            ->where('name', 'like', '%'.$keyword.'%')
            $count = Db::name('knowledge_list')->where($where)->count();
            $rows = DB::name('knowledge_list')->where($where)
                ->limit($page . ',' . $limit)
                ->order('id asc')
                ->select();

            foreach ($rows as $k=>$item) {
                #知识条状态
                if($item['status']==0){
                    $rows[$k]['status_name'] = '未提交';
                }
                elseif($item['status']==1){
                    $rows[$k]['status_name'] = '已提交';
                }

                #知识条类型
                if($item['type']==1){
                    $rows[$k]['type_name'] = '商品资讯';
                }
                elseif($item['type']==2){
                    $rows[$k]['type_name'] = '政策资讯';
                }
                elseif($item['type']==3){
                    $rows[$k]['type_name'] = '历史对话';
                }

                $rows[$k]['createtime'] = date('Y-m-d H:i:s',$item['createtime']);
            }

            return json(['code'=>0,'count'=>$count,'data'=>$rows]);
        }else{
            
            return view('',compact('company_id'));
        }
    }
    
    #保存知识
    public function save_knowledge(Request $request){
        $data = input();
        $company_id = intval($data['company_id']);
        
        $id = isset($data['id'])?intval($data['id']):0;

        if ($request->isAJAX()) {
            $file_path = '';
            if(empty($data['type'])){
                return json(['code'=>-1,'msg'=>'请选择知识类型']);
            }
            if($data['type']==1 || $data['type']==2){
                #商品和政策
                if(isset($data['file_path'])){
                    $file_path = json_encode($data['file_path'],true);
                }else{
                    return json(['code'=>-1,'msg'=>'请上传文档']);
                }
            }
            $knowledge_id = 0;
            if($data['type']==1 || $data['type']==3){
                #商品和历史对话
                if($data['type']==1){
                    $knowledge_id = intval($data['goods_id']);
                }
                elseif($data['type']==3){
                    $knowledge_id = intval($data['dialogue_id']);
                }
            }

            if($id>0){
                $res = Db::name('knowledge_list')->where(['id'=>$id])->update([
                    'cid'=>$company_id,
                    'type'=>intval($data['type']),
                    'knowledge_id'=>$knowledge_id,
                    'file_path'=>$file_path,
                    'status'=>0
                ]);
            }
            else{
                $res = Db::name('knowledge_list')->insert([
                    'cid'=>$company_id,
                    'type'=>intval($data['type']),
                    'knowledge_id'=>$knowledge_id,
                    'file_path'=>$file_path,
                    'status'=>0,
                    'createtime'=>time()
                ]);
            }

            if($res){
                return json(['code'=>0,'msg'=>'操作成功']);
            }else{
                return json(['code'=>0,'msg'=>'暂无修改']);
            }

        }else{
            $info = ['type'=>0,'file_path'=>[],'knowledge_id'=>''];
            if($id>0){
                $info = Db::name('knowledge_list')->where(['id'=>$id])->find();
                $info['file_path'] = json_decode($info['file_path'],true);
            }

            #商品
            $list['goods'] = Db::connect($this->config)->name('goods')->where(['shop_id'=>$company_id])->field('goods_id,goods_name')->select();
            #历史对话（迟点要改的）
            $list['dialogue'] = Db::name('website_chatlist')->where(['company_id'=>$company_id,'content_type'=>1])->field('id,content')->select();
            foreach($list['dialogue'] as $k=>$v){
                $list['dialogue'][$k]['content'] = json_decode($v['content'],true);
            }
            $list['dialogue'] = json_encode($list['dialogue'],true);

            return view('index/ai/save_knowledge',compact('info','company_id','id','list'));
        }
    }
    
    #上架知识
    public function active_knowledge(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $res = Db::name('knowledge_list')->where(['cid'=>$company_id,'status'=>0])->update(['status'=>1]);
        if($res){
            return json(['code'=>0,'msg'=>'上传成功']);
        }else{
            return json(['code'=>-1,'msg'=>'暂无数据上传']);
        }
    }

    #删除知识条
    public function del_knowledge(Request $request){
        $data = input();
        if($data['id']>0){
            $res = Db::name('knowledge_list')->where(['id'=>intval($data['id'])])->delete();
            if($res){
                return json(['code'=>0,'msg'=>'删除成功']);
            }else{
                return json(['code'=>-1,'msg'=>'数据不存在或已删除']);
            }
        }
    }
    
    #标注商家将已上架的商品添加到热门商品表中-merchant_hotproduct
    public function hot_product(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);

        if ($request->isAJAX()) {
            if(empty($dat['goods_id'])){
                return json(['code'=>-1,'msg'=>'暂无数据添加']);
            }

            $res = '';
            $ishave = Db::name('merchant_hotproduct')->where(['cid'=>$company_id])->find();
            if($ishave){
                $res = Db::name('merchant_hotproduct')->where(['cid'=>$company_id])->update(['goods_id'=>$dat['goods_id']]);
            }else{
                $res = Db::name('merchant_hotproduct')->insert([
                    'cid'=>$company_id,
                    'goods_id'=>$dat['goods_id']
                ]);
            }

            if($res){
                return json(['code'=>0,'msg'=>'操作成功']);
            }else{
                return json(['code'=>-1,'msg'=>'暂无修改']);
            }
        }
        else{
            #商家商品
            $goods = Db::connect($this->config)->name('goods')->where(['shop_id'=>$company_id,'goods_status'=>1])->field('goods_id,goods_name')->select();
            $goods = json_encode($goods,true);
            #商家热门商品记录
            $info = Db::name('merchant_hotproduct')->where(['cid'=>$company_id])->find();

            return view('index/ai/hot_product',compact('goods','info','company_id'));
        }
    }

    #商家客服与所有用户的聊天历史
    public function chat_history(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);

        if (isset($dat['pa'])) {
            $limit = $request->get('limit');
            $page = $request->get('page') - 1;
            if ($page != 0) {
                $page = $limit * $page;
            }
            $where =['company_id'=>$company_id,'who_send'=>2];
//            $keyword = isset($dat['keywords']) ? trim($dat['keywords']) : '';
//            ->where('name', 'like', '%'.$keyword.'%')
            $count = Db::name('website_chatlist')->where($where)->group('uid')->distinct(true)->count();

            $subQuery = Db::name('website_chatlist')
                ->field(['uid', 'MAX(id) AS max_id'])
                ->where($where)
                ->group('uid')
                ->buildSql();

            $rows = Db::table($subQuery . ' t1')
                ->join('website_chatlist t2', 't1.uid = t2.uid AND t1.max_id = t2.id')
                ->field('t2.*') // 选择完整字段
                ->limit($page . ',' . $limit)
                ->order('t2.id DESC') // 按 id 降序排序
                ->select();

            foreach ($rows as $k=>$item) {
                #用户信息
                $user = Db::name('website_user')->where(['id'=>$item['uid']])->find();
                $rows[$k]['nickname'] = $user['nickname'];

                #最近聊天对话
                $rows[$k]['content'] = json_decode($item['content'],true);

                #最近对话时间
                $rows[$k]['createtime'] = date('Y-m-d H:i:s',$item['createtime']);
            }

            return json(['code'=>0,'count'=>$count,'data'=>$rows]);
        }else{
            return view('index/ai/chat_history',compact('company_id'));
        }
    }

    #商家与当前用户的聊天历史
    public function chat_histories(Request $request){
        $dat = input();
        $company_id = intval($dat['company_id']);
        $chat_id = intval($dat['chat_id']);
        $identify = 1;//0用户，1商家

        if($request->isAjax()){
            $chat_info = Db::name('website_chatlist')->where(['id'=>$chat_id])->find();

            $chatlist = Db::name('website_chatlist')->whereRaw('company_id='.$chat_info['company_id'].' and uid='.$chat_info['uid'])->order('id asc')->select();
            foreach($chatlist as $k=>$v){
                $chatlist[$k]['content'] = json_decode($v['content'],true);
                $chatlist[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);
                if($v['is_read']==0){
                    $chatlist[$k]['is_read'] = '未读';
                }elseif($v['is_read']==1){
                    $chatlist[$k]['is_read'] = '已读';
                }
                if(!empty($v['quote_text'])){
                    $chatlist[$k]['quote_text'] = explode('@@@',$v['quote_text']);
                }
                $chatlist[$k]['associations'] = json_decode($v['associations'],true);

                #订单
                $chatlist[$k]['associations2']['orders'] = [];
                if(isset($chatlist[$k]['associations']['orders'])){
                    if(count($chatlist[$k]['associations']['orders'])>0){
                        foreach($chatlist[$k]['associations']['orders'] as $k2=>$v2){
                            $order = Db::name('website_order_list')->where(['id'=>$v2])->find();
                            $order['currency'] = Db::name('centralize_currency')->where(['id'=>$order['currency']])->field('currency_symbol_standard')->find()['currency_symbol_standard'];

                            $chatlist[$k]['associations2']['orders'][$k2]['id'] = $order['id'];
                            $chatlist[$k]['associations2']['orders'][$k2]['ordersn'] = $order['ordersn'];
                            $chatlist[$k]['associations2']['orders'][$k2]['currency'] = $order['currency'];
                            if($order['final_money']>0){
                                $chatlist[$k]['associations2']['orders'][$k2]['realmoney'] = $order['final_money'];
                            }
                            else{
                                $chatlist[$k]['associations2']['orders'][$k2]['realmoney'] = $order['true_money'];
                            }
                        }
                    }
                }



                #商品
                $chatlist[$k]['associations2']['products'] = [];
                if(isset($chatlist[$k]['associations']['products'])){
                    if(count($chatlist[$k]['associations']['products'])>0) {
                        foreach ($chatlist[$k]['associations']['products'] as $k2 => $v2) {
                            $products = Db::connect($this->config)->name('goods')->where(['goods_id' => $v2])->field('goods_name,goods_image,goods_id,goods_currency')->find();
                            $goods_sku = Db::connect($this->config)->name('goods_sku')->where(['goods_id' => $v2])->field('goods_price')->limit(1)->find();

                            $chatlist[$k]['associations2']['products'][$k2]['goods_id'] = $products['goods_id'];
                            $chatlist[$k]['associations2']['products'][$k2]['goods_name'] = $products['goods_name'];
                            $chatlist[$k]['associations2']['products'][$k2]['goods_price'] = $goods_sku['goods_price'];
                            $chatlist[$k]['associations2']['products'][$k2]['goods_currency'] = Db::name('centralize_currency')->where(['id' => $products['goods_currency']])->field('currency_symbol_standard')->find()['currency_symbol_standard'];
                        }
                    }
                }

                #文件
//            $chatlist[$k]['associations2']['files'] = [];
//            if(count($chatlist[$k]['associations']['files'])>0) {
//                foreach ($chatlist[$k]['associations']['files'] as $k2 => $v2) {
//                    {"orders":[],"products":["37920"],"files":[{"id":"6","name":"member_center.txt","path":"/uploads/chat_files/20250721/687da47de2a0d.txt"}]}
//                    $files = Db::name('website_chatfiles')->where(['id'=>$v2['id']])->find();
//
//                    $chatlist[$k]['associations2']['files'][$k2]['id'] = $;
//                }
//            }
            }

            $name = [];

            if($identify==0){
                #普通用户聊天界面
//            $company = Db::name('website_user_company')->where(['id'=>$cid])->find();
//            $name = Db::name('website_user')->where(['id'=>$company['user_id']])->find();
//            $name['name'] = $company['company'];
                $name = Db::name('website_user')->where(['id'=>$chat_info['uid']])->find();
                $name['name'] = $name['custom_id'];
            }
            elseif($identify==1){
                #企业聊天界面
                $name = Db::name('website_user')->where(['id'=>$chat_info['uid']])->field('custom_id,realname,nickname,email,phone,area_code')->find();
                $name['name'] = !empty($name['nickname'])?$name['nickname']:$name['realname'];
            }

            $name['area_code'] = Db::name('centralize_diycountry_content')->where(['id'=>$name['area_code']])->field('param8')->find()['param8'];

            #用户订单
            $order_log = Db::name('website_order_list')->where(['user_id'=>$chat_info['uid']])->field('id,ordersn,currency,true_money,final_money,createtime')->order('id desc')->limit(3)->select();
            foreach($order_log as $k2=>$v2){
                $order_log[$k2]['currency'] = Db::name('centralize_currency')->where(['id'=>$v2['currency']])->field('currency_symbol_standard')->find()['currency_symbol_standard'];
                $order_log[$k2]['createtime'] = date('Y-m-d H:i',$v2['createtime']);
            }

            #用户浏览记录
            $browsing_log = Db::name('user_behavior_record')->whereRaw('uid='.$chat_info['uid'].' and ( goods_id != 0 or goods_id != null)')->order('id desc')->limit(3)->select();
            foreach($browsing_log as $k2=>$v2){
                $browsing_log[$k2]['goods_name'] = Db::connect($this->config)->name('goods')->where(['goods_id'=>$v2['goods_id']])->field('goods_name')->find()['goods_name'];
                $browsing_log[$k2]['createtime'] = date('Y-m-d H:i',$v2['createtime']);
            }


            #获取该用户的所有订单
            $all_order = Db::name('website_order_list')->where(['user_id'=>$chat_info['uid']])->select();
            foreach($all_order as $k=>$v){
                $all_order[$k]['currency'] = Db::name('centralize_currency')->where(['id'=>$v['currency']])->field('currency_symbol_standard')->find()['currency_symbol_standard'];

                $all_order[$k]['realmoney'] = 0;
                if($v['final_money']>0){
                    $all_order[$k]['realmoney'] = $v['final_money'];
                }
                else{
                    $all_order[$k]['realmoney'] = $v['true_money'];
                }

                $all_order[$k]['createtime'] = date('Y-m-d H:i:s',$v['createtime']);
            }

            #获取该店铺的所有商品
            $all_goods = Db::connect($this->config)->name('goods')->where(['shop_id'=>$chat_info['company_id'],'goods_status'=>1])->field('goods_name,goods_image,goods_id,goods_currency')->select();
            foreach($all_goods as $k=>$v){
                $goods_sku = Db::connect($this->config)->name('goods_sku')->where(['goods_id'=>$v['goods_id']])->field('goods_price')->limit(1)->find();
                $all_goods[$k]['goods_price'] = $goods_sku['goods_price'];

                $all_goods[$k]['goods_currency'] = Db::name('centralize_currency')->where(['id'=>$v['goods_currency']])->field('currency_symbol_standard')->find()['currency_symbol_standard'];
            }

            $user_info = ['info'=>$name,'order_log'=>$order_log,'browsing_log'=>$browsing_log,'uid'=>$chat_info['uid'],'cid'=>$chat_info['company_id'],'all_order'=>$all_order,'all_goods'=>$all_goods];

            return json(['code'=>0,'list'=>$chatlist,'userinfo'=>$user_info]);
        }else{
            return view('index/ai/chat_histories',compact('company_id','chat_id','identify'));
        }
    }

    #获取当前对话关联信息
    public function chat_association_info(Request $request){
        $dat = input();
        $chat_id = intval($dat['chat_id']);

        $chatinfo = Db::name('website_chatlist')->where(['id'=>$chat_id])->find();
        $chatinfo['associations'] = json_decode($chatinfo['associations'],true);

        #订单
        $chatinfo['associations2']['orders'] = [];
        if(count($chatinfo['associations']['orders'])>0){
            foreach($chatinfo['associations']['orders'] as $k2=>$v2){
                $order = Db::name('website_order_list')->where(['id'=>$v2])->find();
                $order['currency'] = Db::name('centralize_currency')->where(['id'=>$order['currency']])->field('currency_symbol_standard')->find()['currency_symbol_standard'];

                $chatinfo['associations2']['orders'][$k2]['id'] = $order['id'];
                $chatinfo['associations2']['orders'][$k2]['ordersn'] = $order['ordersn'];
                $chatinfo['associations2']['orders'][$k2]['currency'] = $order['currency'];
                if($order['final_money']>0){
                    $chatinfo['associations2']['orders'][$k2]['realmoney'] = $order['final_money'];
                }
                else{
                    $chatinfo['associations2']['orders'][$k2]['realmoney'] = $order['true_money'];
                }
                $chatinfo['associations2']['orders'][$k2]['createtime'] = date('Y-m-d H:i:s',$order['createtime']);
                $chatinfo['associations2']['orders'][$k2]['price_date'] = $chatinfo['associations2']['orders'][$k2]['currency'].' '.$chatinfo['associations2']['orders'][$k2]['realmoney'].' • '.$chatinfo['associations2']['orders'][$k2]['createtime'];
            }
        }


        #商品
        $chatinfo['associations2']['products'] = [];
        if(count($chatinfo['associations']['products'])>0) {
            foreach ($chatinfo['associations']['products'] as $k2 => $v2) {
                $products = Db::connect($this->config)->name('goods')->where(['goods_id' => $v2])->field('goods_name,goods_image,goods_id,goods_currency')->find();
                $goods_sku = Db::connect($this->config)->name('goods_sku')->where(['goods_id' => $v2])->field('goods_price')->limit(1)->find();

                $chatinfo['associations2']['products'][$k2]['goods_id'] = $products['goods_id'];
                $chatinfo['associations2']['products'][$k2]['goods_name'] = $products['goods_name'];
                $chatinfo['associations2']['products'][$k2]['goods_image'] = $products['goods_image'];
                $chatinfo['associations2']['products'][$k2]['goods_price'] = $goods_sku['goods_price'];
                $chatinfo['associations2']['products'][$k2]['goods_currency'] = Db::name('centralize_currency')->where(['id' => $products['goods_currency']])->field('currency_symbol_standard')->find()['currency_symbol_standard'];
            }
        }

        #文件
        if(count($chatinfo['associations']['files'])>0){
            foreach($chatinfo['associations']['files'] as $k=>$v){
                $file_info = Db::name('website_chatfiles')->where(['id'=>$v['id']])->find();
                $chatinfo['associations']['files'][$k]['type'] = $file_info['file_type'];
            }
        }
//        {"orders":[],"products":["37920"],"files":[{"id":"6","name":"member_center.txt","path":"/uploads/chat_files/20250721/687da47de2a0d.txt"}]}
        return json(['code'=>0,'list'=>$chatinfo]);
    }
    #企业服务==============================================end

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
//            $link = Db::connect($this->config)->name('guide_frame')->where(['id'=>$data['other_navbar']])->find();
//            return $link['link'];
            if(!empty($type)){
                return '/?s=index/detail&company_id='.$this->company_id.'&company_type='.$this->company_type.'&id='.$data['other_navbar'];
            }
            return '';
        }elseif($go==3){
            #图文链接
            return '/?s=index/txt_detail&id='.$data['other_pic'].'&type='.$type.'&oid='.$data['id'];
        }elseif($go==4){
            #消息链接
            return '/?s=index/msg_detail&id='.$data['other_msg'].'&type='.$type.'&oid='.$data['id'];
        }elseif($go==5){
            #店铺链接
            return '';
//            return '/shop_detail?id='.isset($data['other_shop'])?$data['other_shop']:'';
        }elseif($go==6){
            #政策链接
            return '/?s=index/rule&pid='.$data['pid'].'&id='.$data['id'];
        }
    }

    #详情
    public function detail(){
        $dat = input();
        
        $id = $dat['id'];
        $company_id = intval($dat['company_id']);
        $company_type = intval($dat['company_type']); #企业类型，0商家商店，1商家网站

        #微信小程序跳转-start
        $miniprogram=0;
        if(session('miniprogram') == ''){
            $miniprogram = isset($dat['miniprogram'])?intval($dat['miniprogram']):0;
            session('miniprogram',$miniprogram);
        }
        if($miniprogram==1 || session('miniprogram')==1){
            if(isset($dat['uid'])){
                $account = Db::name('website_user')->where(['id'=>$dat['uid']])->find();
                session('account',$account);
            }
        }
        #微信小程序跳转-end

        if(isset($dat['isrotate'])){
            $isrotate = intval($dat['isrotate']);
        }else{
            $isrotate = 0;
        }

        $page_name = '';
        #内页详情
        if($isrotate){
            #轮播图内页
            if($isrotate==1){
                $data = Db::name('website_rotate')->where(['id'=>$dat['id']])->find();
                if(empty($data['like_num'])){
                    $news['like_num'] = rand(100,999);
                    Db::name('website_rotate')->where(['id'=>$dat['id']])->update(['like_num'=>$news['like_num']]);
                }else{
                    $news['like_num'] = $data['like_num'];
                }
                $news['id'] = $data['id'];
                $page_name = json_decode($data['title'],true)['zh'];
            }elseif($isrotate==2){
                $data = Db::name('website_rotate_inner')->where(['id'=>$dat['id']])->find();
                if(empty($data['like_num'])){
                    $news['like_num'] = rand(100,999);
                    Db::name('website_rotate_inner')->where(['id'=>$dat['id']])->update(['like_num'=>$news['like_num']]);
                }else{
                    $news['like_num'] = $data['like_num'];
                }
                $news['id'] = $data['id'];
            }

            #制作面包削
            $navbar_menu = '';
            $data['avatar_location'] = '';
            $next_menu = '';
            $rotate = [];
        }
        else{
            #栏目详情页
            $data = Db::name('website_navbar')->where(['id'=>$dat['id']])->find();
            if($data['go_other']==1){
                header('Location:'.$data['other_link']);
            }
            #瀑布流图文
            if($data['go_other']==3){
                header('Location:/?s=index/more_imgtxt');
            }
            if(empty($data['like_num'])){
                $news['like_num'] = rand(100,999);
                Db::name('website_navbar')->where(['id'=>$dat['id']])->update(['like_num'=>$news['like_num']]);
            }else{
                $news['like_num'] = $data['like_num'];
            }
            $news['id'] = $data['id'];
            $page_name = json_decode($data['name'],true)['zh'];
            if($data['format']==2){
                $data['color_word'] = json_decode($data['color_word'],true)[session('lang')];
            }
            #制作面包削
            $navbar_menu = $this->bread($id);
            #下级菜单
            $next_menu = Db::name('website_navbar')->where('pid',$id)->select();
            // dd($next_menu);
            foreach($next_menu as $k=>$v){
                $next_menu[$k]['name'] = json_decode($v['name'],true)[session('lang')];
                $next_menu[$k]['color_word'] = json_decode($v['color_word'],true)[session('lang')];
                // $next_menu[$k]['children'] = Db::name('website_navbar')->where('pid',$v['id'])->select();
                // foreach($next_menu[$k]['children'] as $k2=>$v2){
                //     $next_menu[$k]['children'][$k2]['name'] = json_decode($v2['name'],true)[session('lang')];
                //     $next_menu[$k]['children'][$k2]['color_word'] = json_decode($v2['color_word'],true)[session('lang')];
                // }
            }
            #内页轮播图
            $rotate = Db::name('website_rotate_inner')->where('navbar_id',$dat['id'])->select();
            if(empty($rotate)){
                $rotate = Db::name('website_rotate_inner')->orderRaw('rand()')->limit(5)->select();
            }
        }

        // === 生成 Article Schema（内容页）===
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $data['name'],
            'image' => !empty($data['thumb']) ? 'https://dtc.gogo198.net' . $data['thumb'] : 'https://shop.gogo198.cn/' . $this->website_ico,
            'author' => ['@type' => 'Organization', 'name' => $this->website_name],
            'publisher' => [
                '@type' => 'Organization',
                'name' => $this->website_name,
                'logo' => ['@type' => 'ImageObject', 'url' => 'https://shop.gogo198.cn/' . $this->website_sico]
            ],
            'datePublished' => date('c', $data['create_time'] ?? time()),
            'dateModified' => date('c', $data['update_time'] ?? time()),
            'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => 'https://'.$_SERVER['HTTP_HOST'].$_SERVER["REQUEST_URI"]],
            'description' => strip_tags(mb_substr($data['content'] ?? '', 0, 200, 'utf-8')) . '...'
        ];
        
        // 递归清理空值
        $schema = $this->filterEmpty($schema);
        $article_schema = json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        // -----------------------------------------

        #栏目
        #$menu = $this->menu();
        $menu =  $this->company_menu($company_id,$company_type);
        
        $data['content'] = str_replace('src="https://shop.gogo198.cn','src="',json_decode($data['content'],true)[session('lang')]);
        $data['content'] = str_replace('src="','src="https://admin.gogo198.cn',$data['content']);
        $data['content'] = str_replace('src="https://admin.gogo198.cnhttps','src="https',$data['content']);
        $data['content'] = str_replace('src="https://admin.gogo198.cnhttp','src="http',$data['content']);
        $data['url_this'] = 'https://'.$_SERVER['HTTP_HOST'].$_SERVER["REQUEST_URI"];
        
        #网站信息
        if($data['seo_type']==2){
            $data['seo_content'] = json_decode($data['seo_content'],true);
            $website['title'] = $data['seo_content']['title'][session('lang')];
            $website['keywords'] = $data['seo_content']['keywords'][session('lang')];
            $website['description'] = $data['seo_content']['desc'][session('lang')];
        }else{
            $website['title'] = $page_name . ' - ' . $this->website_name;
            $website['keywords'] = $this->website_keywords;
            $website['description'] = $this->website_description;    
        }
        
        $data['name'] = $website['title'];
        $desc = json_decode($data['desc'],true)[session('lang')];
        $data['desc'] = $website['description'];
        
        $website['ico'] = $this->website_ico;
        $website['sico'] = $this->website_sico;
        $website['tel'] = $this->website_tel;
        $website['email'] = $this->website_email;
        $website['copyright'] = $this->website_copyright;
        $website['color'] = $this->website_color;
        $website['color_word'] = $this->website_colorword;
        $website['color_adorn'] = $this->website_coloradorn;
        $website['color_head'] = $this->website_colorhead;
        $website['inpic'] = $this->website_inpic;
        $website['color_inner'] = $this->website_color_inner;
        $website['website_contact'] = $this->website_contact;
        $website['website_canonical'] = $this->website_canonical;
        $website['website_og'] = $this->website_og;
        
        #随机码
        $rand = $this->random_str();
        
        #底部社交链接
        $link = $this->get_footer_link();

        #分享
        $data['url'] = 'https://'.$_SERVER['HTTP_HOST'].$_SERVER["REQUEST_URI"];
        $data['desc'] = $desc;
        $signPackage = weixin_share($data);

        #所有评论
        $type=1;
        $all_comment = Db::name('website_crossborder_news_chat')->where(['news_id'=>$dat['id'],'type'=>$type])->order('id','desc')->select();
        $news['comment_num'] = Db::name('website_crossborder_news_chat')->where(['news_id'=>$news['id'],'type'=>$type])->count();
        $news['share_num'] = intval($data['share_num']);
        
        return view('',compact('menu','website','data','rand','id','navbar_menu','next_menu','link','rotate','isrotate','news','signPackage','all_comment','type','news','company_id','company_type','website','article_schema'));
    }
    
    /**
     * 递归过滤空值（防止 JSON 出错）
     */
    protected function filterEmpty($array)
    {
        foreach ($array as $k => &$v) {
            if (is_array($v)) {
                $v = $this->filterEmpty($v);
            }
        }
        return array_filter($array, function ($v) {
            return $v !== '' && $v !== null && !(is_array($v) && empty($v));
        });
    }

    #图文详情
    public function txt_detail(Request $request){
        $dat = input();
        $oid = isset($dat['oid'])?intval($dat['oid']):0;
        $foid = isset($dat['foid'])?intval($dat['foid']):0;
        $page_id = isset($dat['id'])?intval($dat['id']):0;
        $company_id = isset($dat['company_id'])?intval($dat['company_id']):0;
        $company_type = isset($dat['company_type'])?intval($dat['company_type']):0;

        $news = Db::name('website_image_txt')->where(['id'=>intval($dat['id'])])->find();

        if(!empty($news['content'])){
            $news['content'] = json_decode($news['content'],true)['zh'];
        }
        if(!empty($news['color_word'])){
            $news['color_word'] = json_decode($news['color_word'],true)['zh'];
        }

        $stype = trim($dat['type']);
        $news['share_num'] = 0;
        $news['like_num'] = 0;
        $type=11;

        $news['share_num'] = intval($news['share_num']);

        if(empty($news['like_num'])){
            $news['like_num'] = rand(100,999);
            Db::name('website_image_txt')->where(['id'=>intval($dat['id'])])->update(['like_num'=>$news['like_num']]);
        }


        $id = intval($dat['oid']);
        $news['id'] = $id;
        $news['name'] = json_decode($news['name'],true)['zh'];
        $news['desc'] = json_decode($news['desc'],true)['zh'];

        #分享
        $data['url'] = 'https://'.$_SERVER['HTTP_HOST'].$_SERVER["REQUEST_URI"];
        $data['name'] = $news['name'];
        $data['desc'] = $news['desc'];
        $data['url_this'] = 'https://'.$_SERVER['HTTP_HOST'].$_SERVER["REQUEST_URI"];
        $data['thumb'] = 'https://shop.gogo198.cn/collect_website/public/uploads/centralize/website_index/64a5282e9bdbf.png';
        $signPackage = weixin_share($data);
        $news['link'] = '';

        #栏目
        $menu = $this->menu();

        #网站信息
        if($news['seo_type']==2){
            $news['seo_content'] = json_decode($news['seo_content'],true);
            $website['title'] = $news['seo_content']['title'][session('lang')];
            $website['keywords'] = $news['seo_content']['keywords'][session('lang')];
            $website['description'] = $news['seo_content']['desc'][session('lang')];
        }else{
            $website['title'] = $this->website_name;
            $website['keywords'] = $this->website_keywords;
            $website['description'] = $this->website_description;
        }

        $data['name'] = $website['title'];
        $desc = json_decode($data['desc'],true)[session('lang')];
        $data['desc'] = $website['description'];

        $website['ico'] = $this->website_ico;
        $website['sico'] = $this->website_sico;
        $website['tel'] = $this->website_tel;
        $website['email'] = $this->website_email;
        $website['copyright'] = $this->website_copyright;
        $website['color'] = $this->website_color;
        $website['color_word'] = $this->website_colorword;
        $website['color_adorn'] = $this->website_coloradorn;
        $website['color_head'] = $this->website_colorhead;
        $website['inpic'] = $this->website_inpic;
        $website['color_inner'] = $this->website_color_inner;
        $website['website_contact'] = $this->website_contact;

        #随机码
        $rand = $this->random_str();

        #底部社交链接
        $link = $this->get_footer_link();

        #所有评论
        $all_comment = Db::name('website_crossborder_news_chat')->where(['news_id'=>$dat['id'],'type'=>$type])->order('id','desc')->select();
        $news['comment_num'] = Db::name('website_crossborder_news_chat')->where(['news_id'=>$news['id'],'type'=>$type])->count();
        $news['share_num'] = intval($news['share_num']);
        $origin_page = '/?index/txt_detail&id='.$page_id.'&type='.$stype.'&oid='.$id.'&foid='.$foid;

        #制作面包削
        $navbar_menu = $news['name'];
        return view('',compact('website','news','id','data','signPackage','all_comment','type','rand','link','origin_page','menu','desc','navbar_menu','company_id','company_type'));
    }

    #消息详情
    public function msg_detail(Request $request){
        $dat = input();
        $id = $dat['id'];
        $foid = isset($dat['foid'])?intval($dat['foid']):0;
        $isframe = isset($dat['isframe'])?intval($dat['isframe']):0;
        $company_id = isset($dat['company_id'])?intval($dat['company_id']):0;
        $company_type = isset($dat['company_type'])?intval($dat['company_type']):0;

        $type=8;
        $news = Db::name('website_message_manage')->where(['id'=>$dat['id']])->find();
        $news['share_num'] = intval($news['share_num']);
        if(empty($news['like_num'])){
            $news['like_num'] = rand(100,999);
            Db::name('website_message_manage')->where(['id'=>$dat['id']])->update(['like_num'=>$news['like_num']]);
        }
        $news['comment_num'] = Db::name('website_crossborder_news_chat')->where(['type'=>$type,'news_id'=>$news['id']])->count();
        $id = $news['id'];
        #分享
        $data['url'] = 'https://'.$_SERVER['HTTP_HOST'].$_SERVER["REQUEST_URI"];
        $data['name'] = $news['name'];
        $data['desc'] = $news['desc'];
        $data['url_this'] = 'https://'.$_SERVER['HTTP_HOST'].$_SERVER["REQUEST_URI"];
        $data['thumb'] = 'https://shop.gogo198.cn/collect_website/public/uploads/centralize/website_index/64a5282e9bdbf.png';
        $signPackage = weixin_share($data);

        #获取配置信息
        $website['ico'] = $this->website_ico;
        $website['sico'] = $this->website_sico;
        $website['tel'] = $this->website_tel;
        $website['email'] = $this->website_email;
        $website['copyright'] = $this->website_copyright;
        $website['color'] = $this->website_color;
        $website['color_word'] = $this->website_colorword;
        $website['color_adorn'] = $this->website_coloradorn;
        $website['color_head'] = $this->website_colorhead;
        $website['inpic'] = $this->website_inpic;
        $website['color_inner'] = $this->website_color_inner;
        $website['website_contact'] = $this->website_contact;
        $website['name'] = $news['name'];

        $website['title'] = $news['name'];
        $website['keywords'] = $this->website_keywords;
        $website['description'] = $this->website_description;

        #栏目
        $menu = $this->menu();

        #随机码
        $rand = $this->random_str();

        #底部社交链接
        $link = $this->get_footer_link();

        #上一个消息
        $prev_news = Db::name('website_message_manage')->where('id','<',$id)->order('id desc')->limit(1)->find();
        #下一个消息
        $next_news = Db::name('website_message_manage')->where('id','>',$id)->order('id asc')->limit(1)->find();

        #制作面包削
        $navbar_menu = $news['name'];

        #所有评论
        $all_comment = Db::name('website_crossborder_news_chat')->where(['news_id'=>$dat['id'],'type'=>$type])->order('id desc')->select();
        $news['comment_num'] = Db::name('website_crossborder_news_chat')->where(['news_id'=>$news['id'],'type'=>$type])->count();
        $news['share_num'] = intval($news['share_num']);
        $origin_page = '/?index/msg_detail&id='.$dat['id'].'&type='.$type.'&oid='.$dat['id'].'&company_id='.$company_id.'&company_type='.$company_type;

        return view('',compact('website','news','id','type','prev_news','next_news','all_comment','data','signPackage','rand','menu','link','navbar_menu','origin_page','company_id','company_type'));
    }

    #瀑布流图文
    public function more_imgtxt(){
        $dat = input();

        header('Content-Type: text/html; charset=utf-8');

        $website['ico'] = $this->website_ico;
        $website['sico'] = $this->website_sico;
        $website['tel'] = $this->website_tel;
        $website['email'] = $this->website_email;
        $website['copyright'] = $this->website_copyright;
        $website['color'] = $this->website_color;
        $website['color_word'] = $this->website_colorword;
        $website['color_adorn'] = $this->website_coloradorn;
        $website['color_head'] = $this->website_colorhead;
        $website['inpic'] = $this->website_inpic;
        $website['color_inner'] = $this->website_color_inner;
        $website['website_contact'] = $this->website_contact;
        $website['title'] = $this->website_name;
        $website['keywords'] = $this->website_keywords;
        $website['description'] = $this->website_description;    
            
        #栏目
        $menu = $this->menu();
        
        #随机码
        $rand = $this->random_str();
        
        #底部社交链接
        $link = $this->get_footer_link();

        #分享
        $data['url'] = 'https://'.$_SERVER['HTTP_HOST'].$_SERVER["REQUEST_URI"];
        $data['desc'] = $website['description'];
        $data['name'] = $website['title'];
        $data['url_this'] = 'https://'.$_SERVER['HTTP_HOST'].$_SERVER["REQUEST_URI"];
        $signPackage = weixin_share($data);
        #新的商城
        $config = [
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
                    
        $list = Db::name('website_more_image_txt')->select();
        foreach($list as $k=>$v){
            $list[$k]['pic_list'] = json_decode($v['pic_list'],true);
        }
        
        $navbar_menu = '';
        
        return view('',compact('menu','website','data','rand','link','signPackage','list','navbar_menu'));
    }

    #联系方式详情
    public function contact_detail(){
        $dat = input();
        
        #栏目
        $menu = $this->menu();
        
        $website['title'] = $this->website_name;
        $website['keywords'] = $this->website_keywords;
        $website['description'] = $this->website_description;
        $website['ico'] = $this->website_ico;
        $website['sico'] = $this->website_sico;
        $website['tel'] = $this->website_tel;
        $website['email'] = $this->website_email;
        $website['copyright'] = $this->website_copyright;
        $website['color'] = $this->website_color;
        $website['color_word'] = $this->website_colorword;
        $website['color_adorn'] = $this->website_coloradorn;
        $website['color_head'] = $this->website_colorhead;
        $website['inpic'] = $this->website_inpic;
        $website['color_inner'] = $this->website_color_inner;
        $website['website_contact'] = $this->website_contact;
        
        #底部社交链接
        $link = $this->get_footer_link();
        
        #社交图片
        $img = Db::name('website_contact')->where(['id'=>$dat['id']])->find();
        #分享
        $data['url'] = 'https://'.$_SERVER['HTTP_HOST'].$_SERVER["REQUEST_URI"];
        $data['desc'] = $website['description'];
        $data['name'] = $website['title'];
        $data['url_this'] = 'https://'.$_SERVER['HTTP_HOST'].$_SERVER["REQUEST_URI"];
        $signPackage = weixin_share($data);
        return view('',compact('menu','website','link','img','signPackage'));
    }
    
    #友情链接
    public function friendly_link(){
        $dat = input();
        
        #栏目
        $menu = $this->menu();
        #底部社交链接
        $link = $this->get_footer_link();
        #友情链接分类
        $linkcate_list = Db::name('website_linkcategory')->where(['show'=>2,'company_id'=>0])->order('id','desc')->select();
        foreach($linkcate_list as $k=>$v){
            $linkcate_list[$k]['name'] = json_decode($v['name'],true)[session('lang')];
            $linkcate_list[$k]['children'] = Db::name('website_link')->where('cate_id',$v['id'])->select();
            foreach($linkcate_list[$k]['children'] as $k2=>$v2){
                $linkcate_list[$k]['children'][$k2]['name'] = json_decode($v2['name'],true)[session('lang')];
            }
        }
        
        #网站信息
        $website['title'] = $this->website_name;
        $website['keywords'] = $this->website_keywords;
        $website['description'] = $this->website_description;    
        $website['ico'] = $this->website_ico;
        $website['sico'] = $this->website_sico;
        $website['tel'] = $this->website_tel;
        $website['email'] = $this->website_email;
        $website['copyright'] = $this->website_copyright;
        $website['color'] = $this->website_color;
        $website['color_inner'] = $this->website_color_inner;
        $website['color_word'] = $this->website_colorword;
        $website['color_adorn'] = $this->website_coloradorn;
        $website['color_head'] = $this->website_colorhead;
        $website['inpic'] = $this->website_inpic;
        $website['website_contact'] = $this->website_contact;
        #分享
        $data['url'] = 'https://'.$_SERVER['HTTP_HOST'].$_SERVER["REQUEST_URI"];
        $data['desc'] = $website['description'];
        $data['name'] = $website['title'];
        $data['url_this'] = 'https://'.$_SERVER['HTTP_HOST'].$_SERVER["REQUEST_URI"];
        $signPackage = weixin_share($data);
        return view('',compact('menu','link','linkcate_list','website','signPackage'));
    }

    #跨境政策新闻-start
    #所有新闻
    public function all_news(Request $request){
        $dat = input();
        $id = $dat['id'];

        #栏目
        $menu = $this->menu();
        #网站信息
        $website['title'] = $this->website_name;
        $website['keywords'] = $this->website_keywords;
        $website['description'] = $this->website_description;
        $website['ico'] = $this->website_ico;
        $website['sico'] = $this->website_sico;
        $website['tel'] = $this->website_tel;
        $website['email'] = $this->website_email;
        $website['copyright'] = $this->website_copyright;
        $website['color'] = $this->website_color;
        $website['color_word'] = $this->website_colorword;
        $website['color_adorn'] = $this->website_coloradorn;
        $website['color_head'] = $this->website_colorhead;
        $website['inpic'] = $this->website_inpic;
        $website['color_inner'] = $this->website_color_inner;
        $website['website_contact'] = $this->website_contact;
        #随机码
        $rand = 0;
        #底部社交链接
        $link = $this->get_footer_link();
        $news = Db::name('policy_list')->where(['from_source'=>0])->order('release_date','desc')->select();
        foreach($news as $k=>$v){
            $news[$k]['issuing_authority'] = json_decode($v['issuing_authority'],true)['zh'];
            $news[$k]['document_number'] = json_decode($v['document_number'],true)['zh'];
            $news[$k]['name'] = json_decode($v['name'],true)['zh'];
        }

//        if(empty($news)){
//            $news = Db::name('website_crossborder_news')->order('id','desc')->select();
//        }

        #制作面包削
        $navbar_menu = $this->bread($id);
        #下级菜单
        $next_menu = Db::name('website_navbar')->where('pid',$id)->select();
        foreach($next_menu as $k=>$v){
            $next_menu[$k]['name'] = json_decode($v['name'],true)[session('lang')];
            $next_menu[$k]['color_word'] = json_decode($v['color_word'],true)[session('lang')];
        }
        #内页轮播图
        $rotate = Db::name('website_rotate_inner')->where('navbar_id',$dat['id'])->select();
        if(empty($rotate)){
            $rotate = Db::name('website_rotate_inner')->orderRaw('rand()')->limit(5)->select();
        }

        #分享
        $data['url'] = 'https://'.$_SERVER['HTTP_HOST'].$_SERVER["REQUEST_URI"];
        $data['desc'] = $website['description'];
        $data['name'] = $website['title'];
        $data['url_this'] = 'https://'.$_SERVER['HTTP_HOST'].$_SERVER["REQUEST_URI"];
        $signPackage = weixin_share($data);

        return view('',compact('menu','website','rand','id','navbar_menu','next_menu','link','rotate','news','signPackage'));
    }

    #新闻列表
    public function news_detail_backup(){
        $dat = input();
        
        #栏目
        $menu = $this->menu();
        #底部社交链接
        $link = $this->get_footer_link();
        #友情链接分类
        $linkcate_list = Db::name('website_linkcategory')->where('show',2)->order('id','desc')->select();
        foreach($linkcate_list as $k=>$v){
            $linkcate_list[$k]['name'] = json_decode($v['name'],true)[session('lang')];
            $linkcate_list[$k]['children'] = Db::name('website_link')->where('cate_id',$v['id'])->select();
            foreach($linkcate_list[$k]['children'] as $k2=>$v2){
                $linkcate_list[$k]['children'][$k2]['name'] = json_decode($v2['name'],true)[session('lang')];
            }
        }
        
        $website['ico'] = $this->website_ico;
        $website['sico'] = $this->website_sico;
        $website['tel'] = $this->website_tel;
        $website['email'] = $this->website_email;
        $website['copyright'] = $this->website_copyright;
        $website['color'] = $this->website_color;
        $website['color_word'] = $this->website_colorword;
        $website['color_adorn'] = $this->website_coloradorn;
        $website['color_head'] = $this->website_colorhead;
        $website['inpic'] = $this->website_inpic;
        $website['color_inner'] = $this->website_color_inner;
        $website['website_contact'] = $this->website_contact;
        
        $news = Db::name('policy_list')->where('id',$dat['id'])->find();
        #网站信息
        if($news['seo_type']==2){
            $news['seo_content'] = json_decode($news['seo_content'],true);
            $website['title'] = $news['seo_content']['title'][session('lang')];
            $website['keywords'] = $news['seo_content']['keywords'][session('lang')];
            $website['description'] = $news['seo_content']['desc'][session('lang')];
        }else{
            $website['title'] = $this->website_name;
            $website['keywords'] = $this->website_keywords;
            $website['description'] = $this->website_description;    
        }  
        $news['issuing_authority'] = json_decode($news['issuing_authority'],true)[session('lang')];
        $news['document_number'] = json_decode($news['document_number'],true)[session('lang')];
        $news['name'] = json_decode($news['name'],true)[session('lang')];
        $news['effect'] = json_decode($news['effect'],true)[session('lang')];
        $news['effect_statement'] = json_decode($news['effect_statement'],true)[session('lang')];
        $news['content'] = json_decode($news['content'],true)[session('lang')];
        $news['desc'] = json_decode($news['desc'],true)[session('lang')];
        if($news['format']==2){
            $news['color_word'] = json_decode($news['color_word'],true)[session('lang')];
        }
        
        #分享
        $data['url'] = $news['url'];
        $data['name'] = $news['name'];
        $data['desc'] = $news['desc'];
        $data['url_this'] = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER["REQUEST_URI"];
        $data['thumb'] = $news['avatar'];
        
        #随机码
        $rand = $this->random_str();
        $id=0;
        return view('',compact('news','menu','link','website','id','rand','data'));
    }

    public function news_detail(Request $request){
        $dat = input();
        $news = Db::name('policy_list')->where(['id'=>$dat['id']])->find();
        $news['share_num'] = intval($news['share_num']);
        if(empty($news['like_num'])){
            $news['like_num'] = rand(100,999);
            Db::name('policy_list')->where(['id'=>$dat['id']])->update(['like_num'=>$news['like_num']]);
        }
        $news['name'] = json_decode($news['name'],true)['zh'];
        $news['content'] = json_decode($news['content'],true)['zh'];
        $news['issuing_authority'] = json_decode($news['issuing_authority'],true)['zh'];
        $news['document_number'] = json_decode($news['document_number'],true)['zh'];
        $news['effect'] = json_decode($news['effect'],true)['zh'];
        if($news['origin_type']==2){
            $news['file'] = json_decode($news['file'],true)[0];
        }
        $id = $news['id'];
        #分享
        $data['url'] = 'https://'.$_SERVER['HTTP_HOST'].$_SERVER["REQUEST_URI"];
        $data['name'] = $news['name'];
        $data['desc'] = $news['issuing_authority'];
        $data['url_this'] = 'https://'.$_SERVER['HTTP_HOST'].$_SERVER["REQUEST_URI"];
        $data['thumb'] = 'https://shop.gogo198.cn/collect_website/public/uploads/centralize/website_index/64a5282e9bdbf.png';
        $signPackage = weixin_share($data);
        $website['title'] = $news['name'];
        $website['keywords'] = $news['name'];
        $website['description'] = $news['name'];
        #栏目
        $menu = $this->menu();
        #底部社交链接
        $link = $this->get_footer_link();
        #友情链接分类
        $linkcate_list = Db::name('website_linkcategory')->where('show',2)->order('id','desc')->select();
        foreach($linkcate_list as $k=>$v){
            $linkcate_list[$k]['name'] = json_decode($v['name'],true)[session('lang')];
            $linkcate_list[$k]['children'] = Db::name('website_link')->where('cate_id',$v['id'])->select();
            foreach($linkcate_list[$k]['children'] as $k2=>$v2){
                $linkcate_list[$k]['children'][$k2]['name'] = json_decode($v2['name'],true)[session('lang')];
            }
        }
        #网站信息
        $website['ico'] = $this->website_ico;
        $website['sico'] = $this->website_sico;
        $website['tel'] = $this->website_tel;
        $website['email'] = $this->website_email;
        $website['copyright'] = $this->website_copyright;
        $website['color'] = $this->website_color;
        $website['color_word'] = $this->website_colorword;
        $website['color_adorn'] = $this->website_coloradorn;
        $website['color_head'] = $this->website_colorhead;
        $website['inpic'] = $this->website_inpic;
        $website['color_inner'] = $this->website_color_inner;
        $website['website_contact'] = $this->website_contact;
        #随机码
        $rand = 0;
        #网页导航
        $navbar_menu = $this->bread(50);
        $navbar_menu .= '&nbsp;<span style="color:#d1a575;">\</span>&nbsp;<a href="?s=index/news_detail&id='.$id.'">'.$news['name'].'</a>&nbsp;';
        #上一个新闻
        $prev_news = Db::name('policy_list')->where('release_date','<',$news['release_date'])->order('release_date','desc')->limit(1)->find();
        #下一个新闻
        $next_news = Db::name('policy_list')->where('release_date','>',$news['release_date'])->order('release_date','asc')->limit(1)->find();
        #所有评论
        $type=4;
        $all_comment = Db::name('website_crossborder_news_chat')->where(['news_id'=>$dat['id'],'type'=>$type])->order('id','desc')->select();
        $news['comment_num'] = Db::name('website_crossborder_news_chat')->where(['news_id'=>$news['id'],'type'=>$type])->count();
        return view('',compact('menu','link','linkcate_list','website','news','id','rand','data','navbar_menu','prev_news','next_news','signPackage','all_comment','type'));
    }
    #跨境政策新闻-end

    #购购动态新闻-start
    public function enterprise_news(Request $request){
        $dat = input();
        $id = $dat['id'];
        if($request->isAjax()) {
            $limit = $request->get('limit');
            $page = $request->get('page') - 1;

            if ($page != 0) {
                $page = $limit * $page;
            }

            $count = Db::name('website_enterprise_news')->count();
            $rows = DB::name('website_enterprise_news')
                ->limit($page . ',' . $limit)
                ->order('release_date desc')
                ->select();

            foreach ($rows as $k => $v) {
               if(!empty($v['createtime'])){
                   $rows[$k]['createtime'] = date('Y-m-d H:i', $v['createtime']);
               }else{
                   $rows[$k]['createtime'] = '-';
               }
            }

            return json(['code' => 0, 'count' => $count, 'data' => $rows]);
        }else{
            #栏目
            $menu = $this->menu();
            $website['title'] = $this->website_name;
            $website['keywords'] = $this->website_keywords;
            $website['description'] = $this->website_description;
            $website['ico'] = $this->website_ico;
            $website['sico'] = $this->website_sico;
            $website['tel'] = $this->website_tel;
            $website['email'] = $this->website_email;
            $website['copyright'] = $this->website_copyright;
            $website['color'] = $this->website_color;
            $website['color_inner'] = $this->website_color_inner;
            $website['color_word'] = $this->website_colorword;
            $website['color_adorn'] = $this->website_coloradorn;
            $website['color_head'] = $this->website_colorhead;
            $website['website_contact'] = $this->website_contact;
            #底部社交链接
            $link = $this->get_footer_link();
            #内页轮播图
            $rotate = Db::name('website_rotate_inner')->where('navbar_id',$id)->select();
            if(empty($rotate)){
                $rotate = Db::name('website_rotate_inner')->orderRaw('rand()')->limit(5)->select();
            }
            $news = DB::name('website_enterprise_news')->order('release_date desc')->select();
            #制作面包削
            $navbar_menu = $this->bread($id);
            #分享
            $data['url'] = 'https://'.$_SERVER['HTTP_HOST'].$_SERVER["REQUEST_URI"];
            $data['desc'] = $website['description'];
            $data['name'] = $website['title'];
            $data['url_this'] = 'https://'.$_SERVER['HTTP_HOST'].$_SERVER["REQUEST_URI"];
            $signPackage = weixin_share($data);
            return view('',compact('menu','link','website','rotate','news','navbar_menu','signPackage'));
        }
    }

    public function enterprise_news_detail(Request $request){
        $dat = input();
        $id = intval($dat['id']);

        $data = Db::name('website_enterprise_news')->where(['id'=>$id])->find();
//        $data['createtime'] = date('Y-m-d H:i',$data['createtime']);
        if(empty($data['like_num'])){
            $news['like_num'] = rand(100,999);
            Db::name('website_enterprise_news')->where(['id'=>$id])->update(['like_num'=>$news['like_num']]);
        }else{
            $news['like_num'] = $data['like_num'];
        }
        $news['id'] = $data['id'];
        $data['content'] = json_decode($data['content'],true);
        $data['seo_content'] = json_decode($data['seo_content'],true);

        $data['content'] = str_replace('src="/uploads','src="https://shop.gogo198.cn/uploads',$data['content']);

        if(!empty($data['seo_content']['title'])){
            $website['title'] = $data['seo_content']['title'];
            $website['keywords'] = $data['seo_content']['keywords'];
            $website['description'] = $data['seo_content']['desc'];
        }else{
            $website['title'] = $this->website_name;
            $website['keywords'] = $this->website_keywords;
            $website['description'] = $this->website_description;
        }
        #栏目
        $menu = $this->menu();
        $website['ico'] = $this->website_ico;
        $website['sico'] = $this->website_sico;
        $website['tel'] = $this->website_tel;
        $website['email'] = $this->website_email;
        $website['copyright'] = $this->website_copyright;
        $website['color'] = $this->website_color;
        $website['color_word'] = $this->website_colorword;
        $website['color_adorn'] = $this->website_coloradorn;
        $website['color_head'] = $this->website_colorhead;
        $website['color_inner'] = $this->website_color_inner;
        $website['website_contact'] = $this->website_contact;
        #底部社交链接
        $link = $this->get_footer_link();
        $rand='';
        #分享
        $data['url'] = 'https://'.$_SERVER['HTTP_HOST'].$_SERVER["REQUEST_URI"];
        $data['desc'] = $website['description'];
        $data['name'] = $website['title'];
        $data['url_this'] = 'https://'.$_SERVER['HTTP_HOST'].$_SERVER["REQUEST_URI"];
        $data['thumb'] = 'https://shop.gogo198.cn/collect_website/public/uploads/centralize/website_index/64a5282e9bdbf.png';
        $signPackage = weixin_share($data);

        #网页导航
        $navbar_menu = $this->bread(42);
        $navbar_menu .= '&nbsp;<span style="color:#d1a575;">\</span>&nbsp;<a href="?s=index/enterprise_news_detail&id='.$id.'">'.$data['name'].'</a>&nbsp;';
        #上一个新闻
        $prev_news = Db::name('website_enterprise_news')->where('release_date','<',$data['release_date'])->order('release_date','desc')->limit(1)->find();
        #下一个新闻
        $next_news = Db::name('website_enterprise_news')->where('release_date','>',$data['release_date'])->order('release_date','asc')->limit(1)->find();
        #所有评论
        $type=2;
        $all_comment = Db::name('website_crossborder_news_chat')->where(['news_id'=>$dat['id'],'type'=>$type])->order('id','desc')->select();
        $news['comment_num'] = Db::name('website_crossborder_news_chat')->where(['news_id'=>$data['id'],'type'=>$type])->count();
        $news['share_num'] = intval($data['share_num']);
        return view('',compact('menu','link','website','data','id','rand','navbar_menu','prev_news','next_news','signPackage','all_comment','type','news'));
    }
    #购购动态新闻-end

    #跨境新闻-start
    public function cross_news(Request $request){
        $dat = input();
        $id = $dat['id'];

        #栏目
        $menu = $this->menu();
        #网站信息
        $website['title'] = $this->website_name;
        $website['keywords'] = $this->website_keywords;
        $website['description'] = $this->website_description;
        $website['ico'] = $this->website_ico;
        $website['sico'] = $this->website_sico;
        $website['tel'] = $this->website_tel;
        $website['email'] = $this->website_email;
        $website['copyright'] = $this->website_copyright;
        $website['color'] = $this->website_color;
        $website['color_word'] = $this->website_colorword;
        $website['color_adorn'] = $this->website_coloradorn;
        $website['color_head'] = $this->website_colorhead;
        $website['inpic'] = $this->website_inpic;
        $website['color_inner'] = $this->website_color_inner;
        $website['website_contact'] = $this->website_contact;
        #随机码
        $rand = 0;
        #底部社交链接
        $link = $this->get_footer_link();
        $news = Db::name('website_crossborder_news')->where(['time'=>date('Y-m-d')])->order('id','desc')->select();
//        if(empty($news)){
//            $news = Db::name('website_crossborder_news')->order('id','desc')->select();
//        }

        #制作面包削
        $navbar_menu = $this->bread($id);
        #下级菜单
        $next_menu = Db::name('website_navbar')->where('pid',$id)->select();
        foreach($next_menu as $k=>$v){
            $next_menu[$k]['name'] = json_decode($v['name'],true)[session('lang')];
            $next_menu[$k]['color_word'] = json_decode($v['color_word'],true)[session('lang')];
        }
        #内页轮播图
        $rotate = Db::name('website_rotate_inner')->where('navbar_id',$dat['id'])->select();
        if(empty($rotate)){
            $rotate = Db::name('website_rotate_inner')->orderRaw('rand()')->limit(5)->select();
        }
        #分享
        $data['url'] = 'https://'.$_SERVER['HTTP_HOST'].$_SERVER["REQUEST_URI"];
        $data['desc'] = $website['description'];
        $data['name'] = $website['title'];
        $data['url_this'] = 'https://'.$_SERVER['HTTP_HOST'].$_SERVER["REQUEST_URI"];
        $signPackage = weixin_share($data);
        return view('',compact('menu','website','rand','id','navbar_menu','next_menu','link','rotate','news','signPackage'));
    }

    #更改日期
    public function change_date(Request $request){
        $dat = input();
        $time = time();
        $news_type = isset($dat['news_type'])?intval($dat['news_type']):0;
        $date = '';
        $news = [];
        if($dat['typ']==1) {
            $date = date('Y-m-d',strtotime($dat['current_day'])-86400);
        }elseif($dat['typ']==2){
            $date = date('Y-m-d',strtotime($dat['current_day'])+86400);
        }
        if(empty(session('account'))){
            #判断距离今天是否有7天相隔
            $prev_seven = strtotime(date('Y-m-d',$time-604800));
            $next_seven = strtotime(date('Y-m-d',$time+604800));
            if(!(strtotime($date) >= $prev_seven && strtotime($date) <= $next_seven)){
                return json(['code'=>-2,'msgg'=>'查阅超出时间限定，如需更多请<a style="color:#ff2222;text-decoration: underline;" href="/?s=index/customer_login&open=4">注册成为会员</a>']);
            }
        }
        $table_name = '';
        $where = [];
        if($news_type==1){
            $table_name = 'policy_list';
            $where = ['release_date'=>date('Y/m/d',strtotime($date))];
        }elseif($news_type==0){
            $table_name = 'website_crossborder_news';
            $where = ['time'=>$date];
        }
        $news = Db::name($table_name)->where($where)->select();
        if($news_type==1){
            foreach($news as $k=>$v){
                $news[$k]['issuing_authority'] = json_decode($v['issuing_authority'],true)['zh'];
                $news[$k]['document_number'] = json_decode($v['document_number'],true)['zh'];
                $news[$k]['name'] = json_decode($v['name'],true)['zh'];
            }
        }
        if(!empty($news)){
            return json(['code'=>0,'date'=>$date,'data'=>$news]);
        }else{
            return json(['code'=>-1,'date'=>$date,'data'=>$news]);
        }
    }

    public function cross_news_detail(Request $request){
        $dat = input();
        $company_id = isset($dat['company_id'])?intval($dat['company_id']):0;
        $company_type = isset($dat['company_type'])?intval($dat['company_type']):0;

        if($request->isAjax()){
            if($dat['type']==1){
                #导航栏/菜单
                if($dat['pa']==1){
                    $news = Db::name('website_navbar')->where(['id'=>$dat['id']])->find();
                    $res = Db::name('website_navbar')->where(['id'=>$dat['id']])->update(['like_num'=>$news['like_num']+1]);
                    if($res){
                        return json(['code'=>0,'msg'=>'点赞成功！']);
                    }
                }elseif($dat['pa']==2){
                    $time = time();
                    $res = Db::name('website_crossborder_news_chat')->insert(['news_id'=>$dat['id'],'text'=>trim($dat['val']),'createtime'=>$time,'type'=>$dat['type'],'ip'=>$_SERVER['REMOTE_ADDR']]);
                    if($res){
                        return json(['code'=>0,'msg'=>'评论成功！','time'=>date('Y-m-d H:i',$time),'ip'=>$_SERVER['REMOTE_ADDR']]);
                    }
                }elseif($dat['pa']==3){
                    $news = Db::name('website_navbar')->where(['id'=>$dat['id']])->find();
                    $res = Db::name('website_navbar')->where(['id'=>$dat['id']])->update(['share_num'=>intval($news['share_num'])+1]);
                    if($res){
                        return json(['code'=>0,'msg'=>'分享成功！']);
                    }
                }
            }
            elseif($dat['type']==2){
                #动态新闻
                if($dat['pa']==1){
                    $news = Db::name('website_enterprise_news')->where(['id'=>$dat['id']])->find();
                    $res = Db::name('website_enterprise_news')->where(['id'=>$dat['id']])->update(['like_num'=>$news['like_num']+1]);
                    if($res){
                        return json(['code'=>0,'msg'=>'点赞成功！']);
                    }
                }elseif($dat['pa']==2){
                    $time = time();
                    $res = Db::name('website_crossborder_news_chat')->insert(['news_id'=>$dat['id'],'text'=>trim($dat['val']),'createtime'=>$time,'type'=>$dat['type'],'ip'=>$_SERVER['REMOTE_ADDR']]);
                    if($res){
                        return json(['code'=>0,'msg'=>'评论成功！','time'=>date('Y-m-d H:i',$time),'ip'=>$_SERVER['REMOTE_ADDR']]);
                    }
                }elseif($dat['pa']==3){
                    $news = Db::name('website_enterprise_news')->where(['id'=>$dat['id']])->find();
                    $res = Db::name('website_enterprise_news')->where(['id'=>$dat['id']])->update(['share_num'=>intval($news['share_num'])+1]);
                    if($res){
                        return json(['code'=>0,'msg'=>'分享成功！']);
                    }
                }
            }
            elseif($dat['type']==3){
                #跨境新闻
                if($dat['pa']==1){
                    $news = Db::name('website_crossborder_news')->where(['id'=>$dat['id']])->find();
                    $res = Db::name('website_crossborder_news')->where(['id'=>$dat['id']])->update(['like_num'=>$news['like_num']+1]);
                    if($res){
                        return json(['code'=>0,'msg'=>'点赞成功！']);
                    }
                }elseif($dat['pa']==2){
                    $time = time();
                    $res = Db::name('website_crossborder_news_chat')->insert(['news_id'=>$dat['id'],'text'=>trim($dat['val']),'createtime'=>$time,'type'=>$dat['type'],'ip'=>$_SERVER['REMOTE_ADDR']]);
                    if($res){
                        return json(['code'=>0,'msg'=>'评论成功！','time'=>date('Y-m-d H:i',$time),'ip'=>$_SERVER['REMOTE_ADDR']]);
                    }
                }elseif($dat['pa']==3){
                    $news = Db::name('website_crossborder_news')->where(['id'=>$dat['id']])->find();
                    $res = Db::name('website_crossborder_news')->where(['id'=>$dat['id']])->update(['share_num'=>intval($news['share_num'])+1]);
                    if($res){
                        return json(['code'=>0,'msg'=>'分享成功！']);
                    }
                }
            }
            elseif($dat['type']==4){
                #政策新闻
                if($dat['pa']==1){
                    $news = Db::name('policy_list')->where(['id'=>$dat['id']])->find();
                    $res = Db::name('policy_list')->where(['id'=>$dat['id']])->update(['like_num'=>$news['like_num']+1]);
                    if($res){
                        return json(['code'=>0,'msg'=>'点赞成功！']);
                    }
                }elseif($dat['pa']==2){
                    $time = time();
                    $res = Db::name('website_crossborder_news_chat')->insert(['news_id'=>$dat['id'],'text'=>trim($dat['val']),'createtime'=>$time,'type'=>$dat['type'],'ip'=>$_SERVER['REMOTE_ADDR']]);
                    if($res){
                        return json(['code'=>0,'msg'=>'评论成功！','time'=>date('Y-m-d H:i',$time),'ip'=>$_SERVER['REMOTE_ADDR']]);
                    }
                }elseif($dat['pa']==3){
                    $news = Db::name('policy_list')->where(['id'=>$dat['id']])->find();
                    $res = Db::name('policy_list')->where(['id'=>$dat['id']])->update(['share_num'=>intval($news['share_num'])+1]);
                    if($res){
                        return json(['code'=>0,'msg'=>'分享成功！']);
                    }
                }
            }
        }else{
            $news = Db::name('website_crossborder_news')->where(['id'=>$dat['id']])->find();
            $news['share_num'] = intval($news['share_num']);
            if(empty($news['like_num'])){
                $news['like_num'] = rand(100,999);
                Db::name('website_crossborder_news')->where(['id'=>$dat['id']])->update(['like_num'=>$news['like_num']]);
            }
            $news['comment_num'] = Db::name('website_crossborder_news_chat')->where(['news_id'=>$news['id']])->count();
            $id = $news['id'];

            #分享
            $data['url'] = 'https://'.$_SERVER['HTTP_HOST'].$_SERVER["REQUEST_URI"];
            $data['name'] = $news['title'];
            $data['desc'] = $news['descs'];
            $data['url_this'] = 'https://'.$_SERVER['HTTP_HOST'].$_SERVER["REQUEST_URI"];
            $data['thumb'] = 'https://shop.gogo198.cn/collect_website/public/uploads/centralize/website_index/64a5282e9bdbf.png';
            $signPackage = weixin_share($data);
            $website['title'] = $news['title'];
            $website['keywords'] = $news['title'];
            $website['description'] = $news['title'];
            #栏目
            $menu = $this->menu();
            #底部社交链接
            $link = $this->get_footer_link();
            #友情链接分类
            $linkcate_list = Db::name('website_linkcategory')->where('show',2)->order('id','desc')->select();
            foreach($linkcate_list as $k=>$v){
                $linkcate_list[$k]['name'] = json_decode($v['name'],true)[session('lang')];
                $linkcate_list[$k]['children'] = Db::name('website_link')->where('cate_id',$v['id'])->select();
                foreach($linkcate_list[$k]['children'] as $k2=>$v2){
                    $linkcate_list[$k]['children'][$k2]['name'] = json_decode($v2['name'],true)[session('lang')];
                }
            }
            #网站信息
            $website['ico'] = $this->website_ico;
            $website['sico'] = $this->website_sico;
            $website['tel'] = $this->website_tel;
            $website['email'] = $this->website_email;
            $website['copyright'] = $this->website_copyright;
            $website['color'] = $this->website_color;
            $website['color_word'] = $this->website_colorword;
            $website['color_adorn'] = $this->website_coloradorn;
            $website['color_head'] = $this->website_colorhead;
            $website['inpic'] = $this->website_inpic;
            $website['color_inner'] = $this->website_color_inner;
            $website['website_contact'] = $this->website_contact;
            #随机码
            $rand = 0;
            #网页导航
            $navbar_menu = '';
//            if($news['pid']!=0){
//                $navbar_menu = $this->bread($news['pid']);
//            }
//            $navbar_menu .= '&nbsp;<span style="color:#d1a575;">\</span>&nbsp;<a href="?s=index/cross_news_detail&id='.$id.'">'.$news['title'].'</a>&nbsp;';
            $navbar_menu .= '&nbsp;<a href="?s=index/cross_news_detail&id='.$id.'">'.$news['title'].'</a>&nbsp;';
            #上一个新闻
            $prev_news = Db::name('website_crossborder_news')->where('id','<',$dat['id'])->order('id','desc')->limit(1)->find();
            #下一个新闻
            $next_news = Db::name('website_crossborder_news')->where('id','>',$dat['id'])->order('id','asc')->limit(1)->find();
            #所有评论
            $type=3;
            $all_comment = Db::name('website_crossborder_news_chat')->where(['news_id'=>$dat['id'],'type'=>$type])->order('id','desc')->select();
            return view('',compact('menu','link','linkcate_list','website','news','id','rand','data','navbar_menu','prev_news','next_news','signPackage','all_comment','type','company_id','company_type'));
        }
    }
    #跨境新闻-end

    #企业资质内页
    public function qualific(){
        $dat = input();
        $id = intval($dat['id']);
        $data = Db::name('website_qualifications')->where('id',$id)->find();
        
        #栏目
        $menu = $this->menu();
        $website['title'] = $this->website_name;
        $website['keywords'] = $this->website_keywords;
        $website['description'] = $this->website_description;
        $website['ico'] = $this->website_ico;
        $website['sico'] = $this->website_sico;
        $website['tel'] = $this->website_tel;
        $website['email'] = $this->website_email;
        $website['copyright'] = $this->website_copyright;
        $website['color'] = $this->website_color;
        $website['color_word'] = $this->website_colorword;
        $website['color_adorn'] = $this->website_coloradorn;
        $website['color_head'] = $this->website_colorhead;
        $website['website_contact'] = $this->website_contact;
        #底部社交链接
        $link = $this->get_footer_link();
        #分享
        $data['url'] = 'https://'.$_SERVER['HTTP_HOST'].$_SERVER["REQUEST_URI"];
        $data['desc'] = $website['description'];
        $data['name'] = $website['title'];
        $data['url_this'] = 'https://'.$_SERVER['HTTP_HOST'].$_SERVER["REQUEST_URI"];
        $signPackage = weixin_share($data);
        return view('',compact('menu','data','id','link','website','signPackage'));
    }

    #切换账号
    public function change_account(Request $request){
        $dat = input();
        $account = session('account');
        $company_id = isset($dat['company_id'])?intval($dat['company_id']):0;
        $company_type = isset($dat['company_type'])?intval($dat['company_type']):0;
        session('company',null);
        session('account',null);

        sleep(1);

        if(!empty($account['auth0_info'])){
            header('Location: https://gogo198.us.auth0.com/v2/logout?client_id=3LuZWceTu0CTzV5z4VBXfDWMaEE3yIVF&returnTo=https://www.gogo198.net'.urlencode('/?s=api/protected_resource&redirect_url=//dtc.gogo198.net/?s=index/customer_login&company_id='.$company_id.'&company_type='.$company_type));exit;
        }

        header('Location:/?s=index/customer_login&company_id='.$company_id.'&company_type='.$company_type);exit;
    }
    
    #账户登录
    public function customer_login(Request $request){
        $dat = input();
        
        $uid = isset($dat['uid'])?intval(base64_decode($dat['uid'])):0;
        $member_id = isset($dat['member_id'])?intval($dat['member_id']):0;
        $company_id = isset($dat['company_id'])?intval($dat['company_id']):0;
        $company_type = isset($dat['company_type'])?intval($dat['company_type']):0;

        if(!empty($member_id)){
            #小程序进来
            $uid = $member_id;
        }
        $is_merch = isset($dat['is_merch'])?intval($dat['is_merch']):0;
        $type = isset($dat['type'])?intval($dat['type']):0;

        #直接点击链接登录/注册
        $email = isset($dat['email'])?trim($dat['email']):'';
        $code = isset($dat['code'])?trim($dat['code']):'';

        if(!isset($dat['is_email'])){
            session('login_code',$code);
        }


        if($uid>0){
            $account = Db::name('website_user')->where('id',$uid)->find();
            session('account',$account);
            sleep(1);
            header('Location: /');
        }

        if($request->isAjax()){
            if(empty(trim($dat['number']))){
                return json(['code'=>-1,'msg'=>'请输入账号！']);
            }
            if($dat['number']!='947960547@qq.com' && $dat['number']!='13119893380' && $dat['number']!='13202629133' && $dat['number']!='13119893381' && $dat['number']!='947960542@qq.com' && $dat['number']!='yushanfang@qq.com' && $dat['number']!='13119893382'&& $dat['number']!='13809703680' && $dat['number']!='13809703681' && $dat['number']!='hejunxin@gogo198.net' && $dat['number']!='198@gogo198.net' && $dat['number']!='pinkeast@126.com' && $dat['number']!='admin@gogo198.net' && $dat['number']!='13129043380@qq.com' && $dat['number']!='3888189426@qq.com' && $dat['number']!='yushanfang@gogo198.net'){
                if(empty(trim($dat['code']))){
                    return json(['code'=>-1,'msg'=>'验证码不正确！']);
                }
                if(empty(session('login_code'))){
                    return json(['code'=>-1,'msg'=>'验证码不正确！']);
                }
                if(session('login_code')!=trim($dat['code'])){
                    return json(['code'=>-1,'msg'=>'验证码不正确！']);
                }
            }
            $number = trim($dat['number']);
            if($dat['reg_method']==1){
                $account = Db::name('website_user')->where('phone',$number)->find();
            }elseif($dat['reg_method']==2){
                $account = Db::name('website_user')->where('email',$number)->find();
            }

            if(empty($account)){
                #无感注册
                $postData = ['phone'=>$dat['reg_method']==1?$number:'','email'=>$dat['reg_method']==2?$number:'','area_code'=>$dat['reg_method']==1?$dat['country_code']:162];

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, "https://shop.gogo198.cn/collect_website/public/?s=api/func/generate_member&company_id=".$company_id); // 目标URL
                curl_setopt($ch, CURLOPT_POST, 1); // 设置为POST请求
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postData); // POST数据
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // 将响应结果作为字符串返回
                $account_id = curl_exec($ch);
                if (curl_errno($ch)) {
                    echo 'Error:' . curl_error($ch);die;
                }
                curl_close($ch);

                $account = Db::name('website_user')->where('id',$account_id)->find();

                #通知用户
                if($dat['reg_method']==1){
                    #手机
                    $post_data = [
                        'mobiles'=>$number,
                        'content'=>'尊敬的客户，您好！您已成功注册成为购购网会员，感谢您的支持！【GOGO】',
                    ];
                    $post_data = json_encode($post_data,true);
                    httpRequest('https://decl.gogo198.cn/api/sendmsg_jumeng',$post_data,array(
                        'Content-Type: application/json; charset=utf-8',
                        'Content-Length:' . strlen($post_data),
                        'Cache-Control: no-cache',
                        'Pragma: no-cache'
                    ));
                }elseif($dat['reg_method']==2){
                    #邮箱
                    httpRequest('https://shop.gogo198.cn/collect_website/public/?s=/api/sendemail/index',['email'=>$number,'title'=>'注册成功','content'=>'<p>注册成功电邮内容：</p><p>尊敬的GoFriend：</p><p>欢迎使用购购网服务，感谢您注册购购网账户。</p><p>Welcome to use the Gogo198 Service. Thank you for registering an Gogo account.</p><br/><p>以下是您的用户名，请保留此电子邮件，日后您可能需要参考它。</p><p>The following is your username. Please keep this email as you may need to refer to it in the future.</p><br/><p>用户名 Username：'.$account['nickname'].'</p><br/><p>现在您可以登录您的账户</p><p>Now you can log in to your account：</p><p>https://www.gogo198.net</p><br/><p>我们很乐意倾听用户的意见！如果您有任何意见或问题，请电邮至：</p><p>We are very willing to listen to the opinions of users! If you have any opinions or problems, please email to:</p><p>198@gogo198.net</p><br/><p>谢谢 Thank you!</p><br/><p>购购网 | Gogo</p>']);
                }
                // return json(['code'=>-1,'msg'=>'账户不正确！']);
            }

            #记录当前账户设备和ip，下次进来可以免登录
            $ip = $_SERVER['REMOTE_ADDR'];
            $device = $_SERVER['HTTP_USER_AGENT'];
            Db::name('decision_login')->insert([
                'uid'=>$account['id'],
                'ip'=>$ip,
                'device'=>$device,
                'createtime'=>time()
            ]);

            session('account',$account);

            #系统判断其是否有关联企业，如果有，应该显示：（个人中心、XY企业）
            $company = Db::name('website_user_company')->where(['user_id'=>$account['id'],'status'=>0])->find();
            $ishave_company=0;
            if(!empty($company)){
                $ishave_company=1;
            }
            return json(['code'=>0,'msg'=>'登录成功！','uid'=>$account['id'],'base64_uid'=>base64_encode($account['id']),'account'=>$account,'company'=>$ishave_company,'company_id'=>$company_id,'company_type'=>$company_type]);
            
        }
        else{
            #栏目
            $menu = $this->menu();
            $website['title'] = $this->website_name;
            $website['keywords'] = $this->website_keywords;
            $website['description'] = $this->website_description;
            $website['ico'] = $this->website_ico;
            $website['sico'] = $this->website_sico;
            $website['tel'] = $this->website_tel;
            $website['email'] = $this->website_email;
            $website['copyright'] = $this->website_copyright;
            $website['color'] = $this->website_color;
            $website['color_word'] = $this->website_colorword;
            $website['color_adorn'] = $this->website_coloradorn;
            $website['color_head'] = $this->website_colorhead;
            $website['website_contact'] = $this->website_contact;
            $website['color_inner'] = $this->website_color_inner;
            #底部社交链接
            $link = $this->get_footer_link();
            #分享
            $data['url'] = 'https://'.$_SERVER['HTTP_HOST'].$_SERVER["REQUEST_URI"];
            $data['desc'] = $website['description'];
            $data['name'] = $website['title'];
            $data['url_this'] = 'https://'.$_SERVER['HTTP_HOST'].$_SERVER["REQUEST_URI"];
            $signPackage = weixin_share($data);

            $open = isset($dat['open'])?intval($dat['open']):0;
            $param = isset($dat['param'])?json_decode($dat['param'],true):['','','','','',''];#其它页面的携带参数
            $param2 = isset($dat['param2'])?base64_decode($dat['param2']):'';#其它页面的携带参数
            #解决网页弹框警告问题
            $param2 = str_replace('*','',$param2);
            $param2 = str_replace('-','',$param2);
            $param2 = str_replace('>','',$param2);
            $param2 = str_replace('\'','',$param2);
            $param2 = str_replace('"','',$param2);

            $inquiry_id = isset($dat['inquiry_id'])?intval($dat['inquiry_id']):0;

//            if(empty(session('account.id'))){
//                $ip = $_SERVER['REMOTE_ADDR'];
//                $device = $_SERVER['HTTP_USER_AGENT'];
//                $have_been_login = Db::name('decision_login')->where(['ip'=>$ip,'device'=>$device])->find();
//                if(!empty($have_been_login)){
//                    $account = Db::name('website_user')->where(['id'=>$have_been_login['uid']])->find();
//                    session('account',$account);
//
//                    if($open==4){
//                        if(!empty($param2)){
//                            header("Location: ".$param2);
//                        }else{
//                            header("Location: /?s=member/member_center");
//                        }
//                    }else{
//                        header("Location: /?s=member/member_center");
//                    }
//                }
//            }

            #国家地区号码
            $country_code = Db::name('centralize_diycountry_content')->where(['pid'=>5])->select();

            #授权应用
            $authlogin_apps = Db::name('website_authlogin_apps')->where(['isshow'=>0])->select();

            #授权应用（最新）
            $autologin_apps2 = Db::name('website_login_apps')->order('id asc')->select();

            $token = generateRandomString();

            $login_content = Db::name('website_login_content')->where(['system_id'=>9])->find();
            $login_content['content'] = json_decode($login_content['content'],true);

            return view('',compact('menu','data','id','link','website','open','inquiry_id','param','param2','signPackage','country_code','authlogin_apps','autologin_apps2','code','email','token','login_content','company_id','company_type'));
        }
    }

    #登录记录
    public function login_log(Request $request){
        $dat = input();
        $ip = $_SERVER['REMOTE_ADDR'];
        $device = $_SERVER['HTTP_USER_AGENT'];
        $link = $_SERVER['HTTP_HOST'];

        $insertId = Db::name('website_login_log')->insertGetId([
            'app_id'=>intval($dat['app_id']),
            'account'=>trim($dat['account']),
            'ip'=>$ip,
            'device'=>$device,
            'link'=>'//'.$link,
            'status'=>0,
            'createtime'=>time()
        ]);

        $app = Db::name('website_login_apps')->where(['id'=>intval($dat['app_id'])])->find();

        return json(['code'=>0,'msg'=>'正在跳转','insertId'=>$insertId,'app'=>$app]);
    }

    #授权登录结果页
    public function authlogin_result(Request $request){
        $dat = input();
        $ip = $_SERVER['REMOTE_ADDR'];
        $device = $_SERVER['HTTP_USER_AGENT'];

        $info = Db::name('website_login_log')->where(['ip'=>$ip,'device'=>$device])->order('id desc')->find();

        $app_info = Db::name('website_login_apps')->where(['id'=>$info['app_id']])->find();

        #栏目
        $menu = $this->menu();
        $website['title'] = $this->website_name;
        $website['keywords'] = $this->website_keywords;
        $website['description'] = $this->website_description;
        $website['ico'] = $this->website_ico;
        $website['sico'] = $this->website_sico;
        $website['tel'] = $this->website_tel;
        $website['email'] = $this->website_email;
        $website['copyright'] = $this->website_copyright;
        $website['color'] = $this->website_color;
        $website['color_word'] = $this->website_colorword;
        $website['color_adorn'] = $this->website_coloradorn;
        $website['color_head'] = $this->website_colorhead;
        $website['website_contact'] = $this->website_contact;
        $website['color_inner'] = $this->website_color_inner;

        return view('',compact('info','website','app_info'));
    }
    
    #验证码发送
    public function send_code(Request $request){
        $dat = input();
//        $res = '';
//        $xml = simplexml_load_string($res);
//        dd($xml->report->status);

        if($request->isAjax()){
            $code = mt_rand(11, 99) . mt_rand(11, 99) . mt_rand(11, 99);
//            $code = 666666;
            if(isset($dat['islogin'])){
                session('login_code',$code);
            
                if($dat['code_type']==1){
                    #手机号码
                    $tel = trim($dat['number']);
                    if(!verifyTel($tel)){
                        return json(['code'=>-1,'msg'=>'手机格式错误！']);
                    }
                    $country_code = intval($dat['country_code']);
                    Db::name('send_msg_list')->insert(['phone'=>$tel,'code'=>$code,'createtime'=>time(),'ip'=>$_SERVER['REMOTE_ADDR'],'type'=>1]);
                    $post_data = [
                        'country_code'=>$country_code,
                        'mobiles'=>$tel,
                        'content'=>'您正在登录GOGO购购网，手机验证码为：'.$code.'【GOGO】',
                        'code'=>$code,
                        'type'=>'login'
                    ];
                    $post_data = json_encode($post_data,true);
                    $res = httpRequest('https://decl.gogo198.cn/api/sendmsg_jumeng',$post_data,array(
                        'Content-Type: application/json; charset=utf-8',
                        'Content-Length:' . strlen($post_data),
                        'Cache-Control: no-cache',
                        'Pragma: no-cache'
                    ));
                    $res = json_decode($res,true);
                    if($res['code']==-1){
                        session('login_code',$res['verify_code']);
                    }
                    elseif($res['code']==2){
                        #查看信息状态
                        $post_data = json_encode([
                            'taskid'=>$res['taskid'],
                            'mobile'=>$tel
                        ],true);
                        $res = httpRequest('https://decl.gogo198.cn/api/getstatus_jumeng',$post_data,array(
                            'Content-Type: application/json; charset=utf-8',
                            'Content-Length:' . strlen($post_data),
                            'Cache-Control: no-cache',
                            'Pragma: no-cache'
                        ));
                        $res = json_decode($res,true);
                        return json(['code'=>$res['code'],'msg'=>$res['msg']]);
                    }
                    return json(['code'=>$res['code'],'msg'=>$res['msg']]);
                }elseif($dat['code_type']==2){
                    #邮箱
                    $res=httpRequest('https://shop.gogo198.cn/collect_website/public/?s=/api/sendemail/index',['email'=>trim($dat['number']),'title'=>'一次性代码','content'=>'<p>你好，您的一次性代码为
Hello, Your one-time code is:</p><br/><p>'.$code.'</p><br/><p>或直接点击：<a href="https://www.gogo198.net/?s=index/customer_login&email='.trim($dat['number']).'&code='.$code.'&open='.intval($dat['open']).'&param2='.$dat['param2'].'&is_email=1" style="text-decoration: underline;">立即登录/注册</a> ,完成网站的登录/注册</p><br/><p>请在登录时输入这个代码，以验证是您本人在登录。请注意，出于安全原因，这个代码将在 20 分钟后过期。</p><p>Please enter this code when logging in to verify that it is you logging in. Please note that for security reasons, this code will expire after 20 minutes.</p><br/><p>我们很乐意倾听用户的意见！如果您有任何意见或问题，请电邮至：</p><p>We are very willing to listen to the opinions of users! If you have any opinions or problems, please email to:</p><p>198@gogo198.net</p><br/><p>谢谢 Thank you!</p><br/><p>购购网 | Gogo</p>']);
                }
            }
            elseif(isset($dat['isverify'])){
                session('verify_code',$code);
                if($dat['code_type']==1){
                    #手机号码
                    $tel = trim($dat['number']);
                    if(!verifyTel($tel)){
                        return json(['code'=>-1,'msg'=>'手机格式错误！']);
                    }

                    $country_code = intval($dat['country_code']);
                    $post_data = [
                        'country_code'=>$country_code,
                        'mobiles'=>$tel,
                        'content'=>'您正在验证手机号码，验证码为：'.$code.'【GOGO】',
                    ];
                    $post_data = json_encode($post_data,true);
                    $res = httpRequest('https://decl.gogo198.cn/api/sendmsg_jumeng',$post_data,array(
                        'Content-Type: application/json; charset=utf-8',
                        'Content-Length:' . strlen($post_data),
                        'Cache-Control: no-cache',
                        'Pragma: no-cache'
                    ));

                    if(isset($dat['basic_verify'])){
                        session('phone_verify_code',$code);
                    }
                }elseif($dat['code_type']==2){
                    #邮箱
                    $res=httpRequest('https://shop.gogo198.cn/collect_website/public/?s=/api/sendemail/index',['email'=>trim($dat['number']),'title'=>'Gogo购购网','content'=>'您正在验证电子邮箱，验证码为：'.$code.'。']);
                    if(isset($dat['basic_verify'])){
                        session('email_verify_code',$code);
                    }
                }
            }
            elseif(isset($dat['isverify_express'])){
                session('verify_exp_code',$code);
//                dd($code);
                if($dat['code_type']==1){
                    #手机号码
                    $tel = '';
                    if($dat['id']>0){
                        $info = Db::name('centralize_waybill_list')
                        ->alias('a')
                        ->join('website_user b','b.id=a.merchant_id')
                        ->where(['a.id'=>$dat['id']])
                        ->field(['b.phone'])
                        ->find();
                        
                        $tel = $info['phone'];
                        // dd(session('verify_exp_code'));
                    }else{
                        $tel = session('account.phone');
                    }
                    // if(!verifyTel($tel)){
                    //     return json(['code'=>-1,'msg'=>'手机格式错误！']);
                    // }

//                    $res=1;
                    $post_data = [
                        'mobiles'=>$tel,
                        'content'=>'您正在验证手机号码，验证码为：'.$code.'【GOGO】',
                    ];
                    $post_data = json_encode($post_data,true);
                    $res = httpRequest('https://decl.gogo198.cn/api/sendmsg_jumeng',$post_data,array(
                        'Content-Type: application/json; charset=utf-8',
                        'Content-Length:' . strlen($post_data),
                        'Cache-Control: no-cache',
                        'Pragma: no-cache'
                    ));
                }
            }
            elseif(isset($dat['save_basic'])){
                #授权登录时，需补充的信息
                if($dat['code_type']==1){
                    #手机号码
                    session('phone_verify_code',$code);

                    $tel = trim($dat['number']);
                    if(!verifyTel($tel)){
                        return json(['code'=>-1,'msg'=>'手机格式错误！']);
                    }

                    $country_code = intval($dat['country_code']);
                    $post_data = [
                        'country_code'=>$country_code,
                        'mobiles'=>$tel,
                        'content'=>'您正在验证手机号码，验证码为：'.$code.'【GOGO】',
                    ];
                    $post_data = json_encode($post_data,true);
                    $res = httpRequest('https://decl.gogo198.cn/api/sendmsg_jumeng',$post_data,array(
                        'Content-Type: application/json; charset=utf-8',
                        'Content-Length:' . strlen($post_data),
                        'Cache-Control: no-cache',
                        'Pragma: no-cache'
                    ));
                }elseif($dat['code_type']==2){
                    #邮箱
                    session('email_verify_code',$code);
                    $res=httpRequest('https://shop.gogo198.cn/collect_website/public/?s=/api/sendemail/index',['email'=>trim($dat['number']),'title'=>'Gogo购购网','content'=>'您正在验证电子邮箱，验证码为：'.$code.'']);
                }
            }
            else{
                session('reg_code',$code);
            
                if($dat['code_type']==1){
                    #手机号码
                    $tel = trim($dat['number']);
                    if(!verifyTel($tel)){
                        return json(['code'=>-1,'msg'=>'手机格式错误！']);
                    }
                    $ishave = Db::name('website_user')->where('phone',$tel)->find();
                    if(!empty($ishave)){
                        return json(['code'=>-1,'msg'=>'该手机号已注册账号！']);
                    }
                    
                    $post_data = [
                        'mobiles'=>$tel,
                        'content'=>'您正在注册成为GOGO购购网用户，手机验证码为：'.$code.'【GOGO】',
                    ];
                    $post_data = json_encode($post_data,true);
                    $res = httpRequest('https://decl.gogo198.cn/api/sendmsg_jumeng',$post_data,array(
                        'Content-Type: application/json; charset=utf-8',
                        'Content-Length:' . strlen($post_data),
                        'Cache-Control: no-cache',
                        'Pragma: no-cache'
                    ));
                }elseif($dat['code_type']==2){
                    #邮箱
                    $ishave = Db::name('website_user')->where('email',$dat['number'])->find();
                    if(!empty($ishave)){
                        return json(['code'=>-1,'msg'=>'该邮箱地址已注册账号！']);
                    }
                    $res=httpRequest('https://shop.gogo198.cn/collect_website/public/?s=/api/sendemail/index',['email'=>trim($dat['number']),'title'=>'注册成为Gogo购购网用户','content'=>'验证码：'.$code.'，您正在注册成为Gogo购购网用户，感谢您的支持！']);
                }
            }
            
            
            if($res){
                return json(['code'=>0,'msg'=>'发送成功！']);
            }else{
                return json(['code'=>-1,'msg'=>'发送失败，请联系管理员！']);
            }
        }
    }

    #保存账号基础信息
    public function save_contact(Request $request){
        $dat = input();

        if($request->isAjax()){
            $data = explode(',',$dat['data']);
            $email_code = trim($data[4]);
            $phone_code = trim($data[3]);
            if(session('email_verify_code')!=$email_code){
                return json(['code'=>-1,'msg'=>'邮箱验证码有误']);
            }

            if(session('phone_verify_code')!=$phone_code){
                return json(['code'=>-1,'msg'=>'手机验证码有误']);
            }
            $country_code = trim($data[0]);
            $phone = trim($data[1]);
            $email = trim($data[2]);
            $openid = trim($data[5]);
            $unionid = trim($data[6]);
            $app_type = trim($data[7]);

            #无感注册
            $postData = ['phone'=>$phone,'email'=>$email,'area_code'=>$country_code];
            if($app_type==3){
                #微信授权
                $postData = array_merge($postData,['openid'=>$openid,'unionid'=>$unionid]);
            }

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://shop.gogo198.cn/collect_website/public/?s=api/func/generate_member"); // 目标URL
            curl_setopt($ch, CURLOPT_POST, 1); // 设置为POST请求
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData); // POST数据
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // 将响应结果作为字符串返回
            $account_id = curl_exec($ch);
            if (curl_errno($ch)) {
                echo 'Error:' . curl_error($ch);die;
            }
            curl_close($ch);

            $account = Db::name('website_user')->where('id',$account_id)->find();

            #通知用户
            if(!empty($account['email'])){
                #邮箱
                httpRequest('https://shop.gogo198.cn/collect_website/public/?s=/api/sendemail/index',['email'=>$email,'title'=>'注册成功','content'=>'<p>注册成功电邮内容：</p><p>尊敬的GoFriend：</p><p>欢迎使用购购网服务，感谢您注册购购网账户。</p><p>Welcome to use the Gogo198 Service. Thank you for registering an Gogo account.</p><br/><p>以下是您的用户名，请保留此电子邮件，日后您可能需要参考它。</p><p>The following is your username. Please keep this email as you may need to refer to it in the future.</p><br/><p>用户名 Username：'.$account['nickname'].'</p><br/><p>现在您可以登录您的账户</p><p>Now you can log in to your account：</p><p>https://healink.gogo198.com</p><br/><p>我们很乐意倾听用户的意见！如果您有任何意见或问题，请电邮至：</p><p>We are very willing to listen to the opinions of users! If you have any opinions or problems, please email to:</p><p>198@gogo198.net</p><br/><p>谢谢 Thank you!</p><br/><p>购购网 | Gogo</p>']);
            }elseif(!empty($account['phone'])){
                #手机
                $post_data = [
                    'country_code'=>$country_code,
                    'mobiles'=>$phone,
                    'content'=>'尊敬的客户，您好！您已成功注册成为购购网会员，感谢您的支持！【GOGO】',
                ];
                $post_data = json_encode($post_data,true);
                httpRequest('https://decl.gogo198.cn/api/sendmsg_jumeng',$post_data,array(
                    'Content-Type: application/json; charset=utf-8',
                    'Content-Length:' . strlen($post_data),
                    'Cache-Control: no-cache',
                    'Pragma: no-cache'
                ));
            }

            session('account',$account);

            #系统判断其是否有关联企业，如果有，应该显示：（个人中心、XY企业）
            $company = Db::name('website_user_company')->where(['user_id'=>$account['id'],'status'=>0])->find();
            $ishave_company=0;
            if(!empty($company)){
                $ishave_company=1;
            }
            return json(['code'=>0,'msg'=>'登录成功！','uid'=>$account['id'],'account'=>$account,'company'=>$ishave_company]);
        }else{
            #栏目
            $menu = $this->menu();
            $website['title'] = $this->website_name;
            $website['keywords'] = $this->website_keywords;
            $website['description'] = $this->website_description;
            $website['ico'] = $this->website_ico;
            $website['sico'] = $this->website_sico;
            $website['tel'] = $this->website_tel;
            $website['email'] = $this->website_email;
            $website['copyright'] = $this->website_copyright;
            $website['color'] = $this->website_color;
            $website['color_word'] = $this->website_colorword;
            $website['color_adorn'] = $this->website_coloradorn;
            $website['color_head'] = $this->website_colorhead;
            $website['website_contact'] = $this->website_contact;
            #底部社交链接
            $link = $this->get_footer_link();
            #分享
            $data['url'] = 'https://'.$_SERVER['HTTP_HOST'].$_SERVER["REQUEST_URI"];
            $data['desc'] = $website['description'];
            $data['name'] = $website['title'];
            $data['url_this'] = 'https://'.$_SERVER['HTTP_HOST'].$_SERVER["REQUEST_URI"];
            $signPackage = weixin_share($data);

            $openid = trim($dat['openid']);
            $unionid = trim($dat['unionid']);
            $app_type = intval($dat['app_type']);

            #国家地区号码
            $country_code = Db::name('centralize_diycountry_content')->where(['pid'=>5])->select();
            return view('',compact('openid','unionid','app_type','country_code'));
        }
    }

    #实名认证-手机/邮箱验证码发送
    public function send_code2(Request $request){
        $dat = input();
        
        if($request->isAjax()){
            $code = mt_rand(11, 99) . mt_rand(11, 99) . mt_rand(11, 99);
            if(isset($dat['islogin'])){
                session('verify_code',$code);
            
                if($dat['code_type']==1){
                    #手机号码
                    $tel = trim($dat['number']);
                    if(!verifyTel($tel)){
                        return json(['code'=>-1,'msg'=>'手机格式错误！']);
                    }
                    
                    $post_data = [
                        'spid'=>'254560',
                        'password'=>'J6Dtc4HO',
                        'ac'=>'1069254560',
                        'mobiles'=>$tel,
                        'content'=>'您正在GOGO实名认证，手机验证码为：'.$code.'【GOGO】',
                    ];
                    $post_data = json_encode($post_data,true);
                    $res = httpRequest('https://decl.gogo198.cn/api/sendmsg_jumeng',$post_data,array(
                        'Content-Type: application/json; charset=utf-8',
                        'Content-Length:' . strlen($post_data),
                        'Cache-Control: no-cache',
                        'Pragma: no-cache'
                    ));
                }elseif($dat['code_type']==2){
                    #邮箱
                    $res=httpRequest('https://shop.gogo198.cn/collect_website/public/?s=/api/sendemail/index',['email'=>trim($dat['number']),'title'=>'Gogo实名认证','content'=>'验证码：'.$code.'，您正在GOGO实名认证。']);
                }
            }
            
            if($res){
                return json(['code'=>0,'msg'=>'发送成功！']);
            }else{
                return json(['code'=>-1,'msg'=>'发送失败，请联系管理员！']);
            }
        }
    }
    
    #认证信息
    public function auth_info(Request $request){
        $dat = input();
        if($request->isAjax()){
            $idcard = trim($dat['idcard']);
            $realname = trim($dat['realname']);
            $phone = trim($dat['phone']);

            $res = Db::name('website_user')->where(['id'=>session('account')['id']])->update([
                'idcard'=>$idcard,
                'realname'=>$realname,
                'phone'=>$phone,
                'is_verify'=>0
            ]);
            if($res){
                return json(['code'=>0,'data'=>[$phone,$idcard,$realname]]);
            }
        }else{
            #栏目
            $menu = $this->menu();
            $website['title'] = $this->website_name;
            $website['keywords'] = $this->website_keywords;
            $website['description'] = $this->website_description;
            $website['ico'] = $this->website_ico;
            $website['sico'] = $this->website_sico;
            $website['tel'] = $this->website_tel;
            $website['email'] = $this->website_email;
            $website['copyright'] = $this->website_copyright;
            $website['color'] = $this->website_color;
            $website['color_word'] = $this->website_colorword;
            $website['color_adorn'] = $this->website_coloradorn;
            $website['color_head'] = $this->website_colorhead;
            $website['website_contact'] = $this->website_contact;
            #底部社交链接
            $link = $this->get_footer_link();
            #个人信息
            $account = Db::name('website_user')->where('id',session('account')['id'])->find();
            #分享
            $data['url'] = 'https://'.$_SERVER['HTTP_HOST'].$_SERVER["REQUEST_URI"];
            $data['desc'] = $website['description'];
            $data['name'] = $website['title'];
            $data['url_this'] = 'https://'.$_SERVER['HTTP_HOST'].$_SERVER["REQUEST_URI"];
            $signPackage = weixin_share($data);
            return view('',compact('menu','data','id','link','website','account','signPackage'));
        }
    }

    #关联企业管理
    public function connect_info(Request $request){
        $dat = input();

        #获取企业
        $enterprise = Db::name('website_user_company')->where(['user_id'=>session('account.id'),'status'=>0])->select();

        #栏目
        $menu = $this->menu();
        $website['title'] = $this->website_name;
        $website['keywords'] = $this->website_keywords;
        $website['description'] = $this->website_description;
        $website['ico'] = $this->website_ico;
        $website['sico'] = $this->website_sico;
        $website['tel'] = $this->website_tel;
        $website['email'] = $this->website_email;
        $website['copyright'] = $this->website_copyright;
        $website['color'] = $this->website_color;
        $website['color_word'] = $this->website_colorword;
        $website['color_adorn'] = $this->website_coloradorn;
        $website['color_head'] = $this->website_colorhead;
        $website['website_contact'] = $this->website_contact;
        #底部社交链接
        $link = $this->get_footer_link();
        #分享
        $data['url'] = 'https://'.$_SERVER['HTTP_HOST'].$_SERVER["REQUEST_URI"];
        $data['desc'] = $website['description'];
        $data['name'] = $website['title'];
        $data['url_this'] = 'https://'.$_SERVER['HTTP_HOST'].$_SERVER["REQUEST_URI"];
        $signPackage = weixin_share($data);
        return view('',compact('menu','link','website','signPackage','enterprise'));
    }
    
    #商户注册
    public function merchant_reg(Request $request){
        $dat = input();
        // $is_merch = Db::name('website_user')->where('id',session('account')['id'])->find();
        // session('account',$is_merch);
        if($request->isAjax()){
            if($dat['reg_method']==2){
                #境外企业
                $files = [];
                foreach($dat['filename'] as $k=>$v){
                    if(empty($v)){
                        return json(['code'=>-1,'msg'=>'请输入文件名称']);
                    }else{
                        $files = array_merge($files,[['files'=>$dat['file'][$k],'filenames'=>trim($v)]]);
                    }
                }
                $files = json_encode($files,true);

                $type = $dat['type'];
                $type2 = $type==2?$dat['type2']:0;
                $company = trim($dat['company_name']);

                if(empty($company) || empty($dat['filename'])){
                    return json(['code'=>-1,'msg'=>'请输入关联信息']);
                }

                #判断企业是否已被自己关联和自己是“超级管理员”还是“员工”
                $mehave_company = Db::name('website_user_company')->where(['company'=>$company,'user_id'=>session('account')['id']])->find();
                if(!empty($mehave_company['id'])){
                    if($mehave_company['status']==0){
                        return json(['code'=>-1,'msg'=>'关联失败，您已认证此企业']);
                    }
                    elseif($mehave_company['status']==1){
                        return json(['code'=>-1,'msg'=>'关联失败，您已注销此企业']);
                    }
                }
                $ishave_company = Db::name('website_user_company')->whereRaw('company="'.$company.'" and user_id<>'.session('account')['id'])->find();
                $is_manager = 1;#员工
                if(empty($ishave_company['id'])){
                    $is_manager = 0;#管理员
                }

                $company_id = 0;
                if(empty($mehave_company['id'])){
                    #插入认证信息
                    $company_id = Db::name('website_user_company')->insertGetId([
                        'role'=>$is_manager,
                        'user_id'=>session('account.id'),
                        'reg_method'=>$dat['reg_method'],
                        'company'=>$company,
                        'reg_file'=>$files,
                        'type'=>$type,
                        'type2'=>$type==2?$type2:0,
                        'status'=>-1,
                        'createtime'=>time(),
                    ]);
                }else{
                    #修改认证信息
                    Db::name('website_user_company')->where(['id'=>$mehave_company['id']])->update([
                        'reg_method'=>$dat['reg_method'],
                        'company'=>$company,
                        'reg_file'=>$files,
                        'type'=>$type,
                        'type2'=>$type==2?$type2:0,
                        'status'=>-1,
                    ]);
                    $company_id = $mehave_company['id'];
                }
                
                #通知O端
                $system = Db::name('centralize_system_notice')->where(['uid'=>0])->find();
                if($system['notice_type']==1){
                    #微信
                    $post = json_encode([
                        'call'=>'confirmCollectionNotice',
                        'first' =>'用户['.session('account.custom_id').']提交境外商户认证',
                        'keyword1' => '用户['.session('account.custom_id').']提交境外商户认证',
                        'keyword2' => '已提交待认证',
                        'keyword3' => date('Y-m-d H:i:s',time()),
                        'remark' => '点击查看详情',
                        'url' => 'https://gadmin.gogo198.cn/',
                        'openid' => $system['account'],
                        'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
                    ]);

                    httpRequest('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);
                }
                elseif($system['notice_type']==3){
                    #邮箱通知
                    httpRequest('https://shop.gogo198.cn/collect_website/public/?s=/api/sendemail/index',['email'=>$system['account'],'title'=>'用户['.session('account.custom_id').']提交境外商户认证','content'=>'请登录总后台，进入商户管理进行审批：https://gadmin.gogo198.cn/']);
                }
                
                return json(['code'=>0,'msg'=>'提交成功，请等待管理人员审批，感谢支持！']);
            }
            elseif($dat['reg_method']==1){
                 #境内企业
//                 $user = Db::name('website_user')->where('id',session('account')['id'])->find();
//                 $dat['data'] = explode(',',$dat['data']);
//                 if(empty($user['phone'])){
//                     if(session('verify_code')!=trim($dat['data'][5])){
//                         return json(['code'=>-1,'msg'=>'验证码不正确！']);
//                     }
//                 }
                    $data = explode(',',$dat['data']);
                    $company = trim($data[0]);
                    $realname = trim($data[1]);
                    $idcard = trim($data[2]);
                    $mobile = trim($data[3]);
                    $email = trim($data[4]);
                    $verify_code = trim($data[5]);
                    $type = trim($data[6]);
                    $type2 = trim($data[7]);

                    if(empty($company) || empty($realname) || empty($idcard) || empty($mobile)){
                        return json(['code'=>-1,'msg'=>'请输入关联信息']);
                    }

                    #判断企业是否已被自己关联和自己是“超级管理员”还是“员工”
                    $mehave_company = Db::name('website_user_company')->where(['company'=>$company,'user_id'=>session('account')['id']])->find();
                    if(!empty($mehave_company['id'])){
                        if($mehave_company['status']==0){
                            return json(['code'=>-1,'msg'=>'关联失败，您已认证此企业名']);
                        }
                        elseif($mehave_company['status']==1){
                            return json(['code'=>-1,'msg'=>'关联失败，您已注销此企业']);
                        }
                    }
                    $ishave_company = Db::name('website_user_company')->whereRaw('company="'.$company.'" and user_id<>'.session('account')['id'])->find();
                    $is_manager = 1;#员工
                    if(empty($ishave_company['id'])){
                        $is_manager = 0;#管理员
                    }

                    $company_id = 0;
                    if(empty($mehave_company['id'])){
                        #插入认证信息
                        if($mehave_company['status']==-1){
                            Db::name('website_user_company')->where(['id'=>$mehave_company['id']])->update([
                                'reg_method'=>$dat['reg_method'],
                                'realname'=>$realname,
                                'mobile'=>$mobile,
                                'company'=>$company,
                                'idcard'=>$idcard,
                                'type'=>$type,
                                'type2'=>$type==2?$type2:0,
                                'status'=>-1,
                            ]);
                            $company_id = $mehave_company['id'];
                        }else{
                            $company_id = Db::name('website_user_company')->insertGetId([
                                'role'=>$is_manager,
                                'user_id'=>session('account.id'),
                                'reg_method'=>$dat['reg_method'],
                                'realname'=>$realname,
                                'mobile'=>$mobile,
                                'company'=>$company,
                                'idcard'=>$idcard,
                                'type'=>$type,
                                'type2'=>$type==2?$type2:0,
                                'status'=>-1,
                                'createtime'=>time(),
                            ]);
                        }
                    }else{
                        #修改认证信息
                        Db::name('website_user_company')->where(['id'=>$mehave_company['id']])->update([
                            'reg_method'=>$dat['reg_method'],
                            'realname'=>$realname,
                            'mobile'=>$mobile,
                            'company'=>$company,
                            'idcard'=>$idcard,
                            'type'=>$type,
                            'type2'=>$type==2?$type2:0,
                            'status'=>-1,
                        ]);
                        $company_id = $mehave_company['id'];
                    }

                 return json(['code'=>0,'data'=>[$mobile,$realname,$idcard,$company_id],'msg'=>'关联成功，请等待管理员审核']);
            }
        }else{
            #栏目
            $menu = $this->menu();
            $website['title'] = $this->website_name;
            $website['keywords'] = $this->website_keywords;
            $website['description'] = $this->website_description;
            $website['ico'] = $this->website_ico;
            $website['sico'] = $this->website_sico;
            $website['tel'] = $this->website_tel;
            $website['email'] = $this->website_email;
            $website['copyright'] = $this->website_copyright;
            $website['color'] = $this->website_color;
            $website['color_word'] = $this->website_colorword;
            $website['color_adorn'] = $this->website_coloradorn;
            $website['color_head'] = $this->website_colorhead;
            $website['website_contact'] = $this->website_contact;
            #底部社交链接
            $link = $this->get_footer_link();
            #分享
            $data['url'] = 'https://'.$_SERVER['HTTP_HOST'].$_SERVER["REQUEST_URI"];
            $data['desc'] = $website['description'];
            $data['name'] = $website['title'];
            $data['url_this'] = 'https://'.$_SERVER['HTTP_HOST'].$_SERVER["REQUEST_URI"];
            $signPackage = weixin_share($data);
            $account = Db::name('website_user')->where('id',session('account')['id'])->find();
            if($account['is_verify']==0){
                #未认证时跳转到认证信息页
                header('Location: /?s=index/auth_info');
            }
            session('account',$account);
            $open = isset($dat['open'])?$dat['open']:0;
            return view('',compact('menu','link','website','open','signPackage','account'));
        }
    }

    public function equipmentSystem()
    {
        $agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        if (stristr($agent, 'iPad')) {
            $fb_fs = "iPad";
        } else if (preg_match('/Android (([0-9_.]{1,3})+)/i', $agent, $version)) {
            $fb_fs = "手机(Android " . $version[1] . ")";
        } else if (stristr($agent, 'Linux')) {
            $fb_fs = "电脑(Linux)";
        } else if (preg_match('/iPhone OS (([0-9_.]{1,3})+)/i', $agent, $version)) {
            $fb_fs = "手机(iPhone " . $version[1] . ")";
        } else if (preg_match('/Mac OS X (([0-9_.]{1,5})+)/i', $agent, $version)) {
            $fb_fs = "电脑(OS X " . $version[1] . ")";
        } else if (preg_match('/unix/i', $agent)) {
            $fb_fs = "Unix";
        } else if (preg_match('/windows/i', $agent)) {
            $fb_fs = "电脑(Windows)";
        } else {
            $fb_fs = "Unknown";
        }
        return $fb_fs;
    }
  
    #平台规则-start
    public function rule_list(Request $request){
        $dat = input();
        if($request->isAjax()){
            $limit = $request->get('limit');
            $page = $request->get('page') - 1;

            if ($page != 0) {
                $page = $limit * $page;
            }
            $count = Db::name('website_platform_keywords')->order('id desc')->count();
            $rows = DB::name('website_platform_keywords')
                ->alias('a')
                ->join('website_platform_type b','b.id = a.type_id')
                ->limit($page.','.$limit)
                ->order('id desc')
                ->field('a.*,b.name as type_name')
                ->select();
            foreach($rows as $k=>$v){
                $rows[$k]['name'] = $v['type_name'].'['.$v['name'].']';
            }
            return json(['code'=>0,'count'=>$count,'data'=>$rows]);
        }else{
            #栏目
            $menu = $this->menu();
            $website['title'] = $this->website_name;
            $website['keywords'] = $this->website_keywords;
            $website['description'] = $this->website_description;
            $website['ico'] = $this->website_ico;
            $website['sico'] = $this->website_sico;
            $website['tel'] = $this->website_tel;
            $website['email'] = $this->website_email;
            $website['copyright'] = $this->website_copyright;
            $website['color'] = $this->website_color;
            $website['color_inner'] = $this->website_color_inner;
            $website['color_word'] = $this->website_colorword;
            $website['color_adorn'] = $this->website_coloradorn;
            $website['color_head'] = $this->website_colorhead;
            $website['website_contact'] = $this->website_contact;
            #底部社交链接
            $link = $this->get_footer_link();
            #分享
            $data['url'] = 'https://'.$_SERVER['HTTP_HOST'].$_SERVER["REQUEST_URI"];
            $data['desc'] = $website['description'];
            $data['name'] = $website['title'];
            $data['url_this'] = 'https://'.$_SERVER['HTTP_HOST'].$_SERVER["REQUEST_URI"];
            $signPackage = weixin_share($data);
            $navbar_menu = '<a href="?s=index/rule_list">平台规则</a>&nbsp;<span style="color:#d1a575;">\</span>&nbsp;';
            $list = Db::name('website_platform_type')->select();
            foreach($list as $k=>$v){
                $list[$k]['children'] = Db::name('website_platform_keywords')->where(['type_id'=>$v['id']])->select();
                foreach($list[$k]['children'] as $k2=>$v2){
                    $list[$k]['children'][$k2]['children'] = Db::name('website_platform_rule')->where(['type_id'=>$v['id'],'key_id'=>$v2['id'],'pid'=>0])->select();
//                    foreach($list[$k]['children'][$k2]['children'] as $k3=>$v3){
//                        $list[$k]['children'][$k2]['children'][$k3]['children'] = Db::name('website_platform_rule')->where(['type_id'=>$v['id'],'key_id'=>$v2['id'],'pid'=>$v3['id']])->whereOr('id','=',$v3['id'])->order('createtime desc')->select();
//                    }
                }
            }
            return view('/index/rule/rule_list',compact('menu','link','website','navbar_menu','list','signPackage'));
        }
    }

    public function version_list(Request $request){
        $dat = input();
        $pid = isset($dat['pid'])?intval($dat['pid']):0;
        if($request->isAjax()){
            $limit = $request->get('limit');
            $page = $request->get('page') - 1;

            if ($page != 0) {
                $page = $limit * $page;
            }
            $count = Db::name('website_platform_rule')->where(['pid'=>$pid,'status'=>0])->whereOr('id','=',$pid)->order('createtime desc')->count();
            $rows = DB::name('website_platform_rule')
                ->where(['pid'=>$pid,'status'=>0])
                ->whereOr('id','=',$pid)
                ->limit($page.','.$limit)
                ->order('createtime desc')
                ->select();
            foreach($rows as $k=>$v){
                $rows[$k]['createtime'] = date('Y-m-d H:i',$v['createtime']);
            }
            return json(['code'=>0,'count'=>$count,'data'=>$rows]);
        }else{
            #栏目
            $menu = $this->menu();
            $website['title'] = $this->website_name;
            $website['keywords'] = $this->website_keywords;
            $website['description'] = $this->website_description;
            $website['ico'] = $this->website_ico;
            $website['sico'] = $this->website_sico;
            $website['tel'] = $this->website_tel;
            $website['email'] = $this->website_email;
            $website['copyright'] = $this->website_copyright;
            $website['color'] = $this->website_color;
            $website['color_inner'] = $this->website_color_inner;
            $website['color_word'] = $this->website_colorword;
            $website['color_adorn'] = $this->website_coloradorn;
            $website['color_head'] = $this->website_colorhead;
            $website['website_contact'] = $this->website_contact;
            #底部社交链接
            $link = $this->get_footer_link();
            #分享
            $data['url'] = 'https://'.$_SERVER['HTTP_HOST'].$_SERVER["REQUEST_URI"];
            $data['desc'] = $website['description'];
            $data['name'] = $website['title'];
            $data['url_this'] = 'https://'.$_SERVER['HTTP_HOST'].$_SERVER["REQUEST_URI"];
            $signPackage = weixin_share($data);
//            $rule_name = DB::name('website_platform_keywords')
//                ->alias('a')
//                ->join('website_platform_type b','b.id = a.type_id')
//                ->where(['a.id'=>$key_id,'b.id'=>$type_id])
//                ->field('a.*,b.name as type_name')
//                ->find();

//            $rule_name['name'] = $rule_name['type_name'].'['.$rule_name['name'].']';
            $history = Db::name('website_platform_rule')->where(['id'=>$pid])->find();
            $navbar_menu = '<a href="?s=index/rule_list">平台规则</a>&nbsp;<span style="color:#d1a575;">\</span>&nbsp;<a href="?s=index/version_list&pid='.$pid.'">'.$history['rule_name'].'</a>';
            return view('/index/rule/version_list',compact('menu','link','website','navbar_menu','key_id','type_id','pid','signPackage'));
        }
    }

    public function rule(Request $request){
        $dat = input();
        $pid = isset($dat['pid'])?intval($dat['pid']):0;
        $watch = isset($dat['watch'])?$dat['watch']:0;
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
            #栏目
            $menu = $this->menu();
            $website['title'] = $this->website_name;
            $website['keywords'] = $this->website_keywords;
            $website['description'] = $this->website_description;
            $website['ico'] = $this->website_ico;
            $website['sico'] = $this->website_sico;
            $website['tel'] = $this->website_tel;
            $website['email'] = $this->website_email;
            $website['copyright'] = $this->website_copyright;
            $website['color'] = $this->website_color;
            $website['color_inner'] = $this->website_color_inner;
            $website['color_word'] = $this->website_colorword;
            $website['color_adorn'] = $this->website_coloradorn;
            $website['color_head'] = $this->website_colorhead;
            $website['website_contact'] = $this->website_contact;
            #底部社交链接
            $link = $this->get_footer_link();
            #分享
            $data['url'] = 'https://'.$_SERVER['HTTP_HOST'].$_SERVER["REQUEST_URI"];
            $data['desc'] = $website['description'];
            $data['name'] = $website['title'];
            $data['url_this'] = 'https://'.$_SERVER['HTTP_HOST'].$_SERVER["REQUEST_URI"];
            $signPackage = weixin_share($data);
            #规则内容
            $data = Db::name('website_platform_rule')->where(['id'=>$dat['id']])->find();
            #序言
            if($data['is_preamble']==1){
                $data['preamble_con'] = json_decode($data['preamble_con'],true);
            }
            $data['content'] = json_decode($data['content'],true);
            #整理树形结构代码
            if($data['type']==1){
                $first = [];
                $second = [];
                foreach($data['content'] as $k=>$v){
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
                $data['content2'] = $first;
            }
            #面包削
//            $key_name = Db::name('website_platform_keywords')->where(['id'=>$data['key_id']])->find()['name'];
//            $rule_name = DB::name('website_platform_keywords')
//                ->alias('a')
//                ->join('website_platform_type b','b.id = a.type_id')
//                ->where(['a.id'=>$data['key_id'],'b.id'=>$data['type_id']])
//                ->field('a.*,b.name as type_name')
//                ->find();
//            $rule_name['name'] = $rule_name['type_name'].'['.$rule_name['name'].']';
            $history = Db::name('website_platform_rule')->where(['id'=>$pid])->find();
            $navbar_menu = '<a href="?s=index/rule_list">平台规则</a>&nbsp;<span style="color:#d1a575;">\</span>&nbsp;<a href="?s=index/version_list&pid='.$pid.'">'.$history['rule_name'].'</a>&nbsp;<span style="color:#d1a575;">\</span>&nbsp;<a href="?s=index/rule&pid='.$pid.'&id='.$data['id'].'">'.$data['version'].'</a>';
//            $navbar_menu = '<a href="?s=index/rule_list">平台规则</a>&nbsp;<span style="color:#d1a575;">\</span>&nbsp;<a href="?s=index/rule&pid='.$pid.'&id='.$data['id'].'">'.$history['rule_name'].'['.$data['version'].']</a>';
            // dd($data['content2']);
            return view('/index/rule/rule',compact('menu','link','website','navbar_menu','data','pid','signPackage','watch'));
        }
    }
    #平台规则-end

    #智能客服体验
    public function intelligent(Request $request){
        header('Access-Control-Allow-Origin: https://boss.gogo198.cn');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Methods: GET, POST');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Access-Control-Expose-Headers: X-Custom-Header');
        
        $dat = input();

        if(empty(session('account'))){
            $param = base64_encode('/?s=index/intelligent');
            header('Location:/?s=index/customer_login&open=4&param2='.$param);exit;
        }
        #判断是否商户
        $is_merch = Db::name('website_user_company')->where(['user_id'=>session('account.id')])->order('id asc')->find();

        #身份
        $identify = empty($is_merch)?0:1;

        #栏目
        $menu = $this->menu();
        $website['title'] = $this->website_name;
        $website['keywords'] = $this->website_keywords;
        $website['description'] = $this->website_description;
        $website['ico'] = $this->website_ico;
        $website['sico'] = $this->website_sico;
        $website['tel'] = $this->website_tel;
        $website['email'] = $this->website_email;
        $website['copyright'] = $this->website_copyright;
        $website['color'] = $this->website_color;
        $website['color_word'] = $this->website_colorword;
        $website['color_adorn'] = $this->website_coloradorn;
        $website['color_head'] = $this->website_colorhead;
        $website['website_contact'] = $this->website_contact;
        #底部社交链接
        $link = $this->get_footer_link();
        #分享
        $data['url'] = 'https://'.$_SERVER['HTTP_HOST'].$_SERVER["REQUEST_URI"];
        $data['desc'] = $website['description'];
        $data['name'] = $website['title'];
        $data['url_this'] = 'https://'.$_SERVER['HTTP_HOST'].$_SERVER["REQUEST_URI"];
        $signPackage = weixin_share($data);

        $account = Db::name('website_user')->where(['id'=>session('account.id')])->find();
        $account['level_name'] = Db::name('member_level')->where(['id'=>$account['level_id']])->field('name')->find()['name'];
        $account['company'] = $is_merch['company'];
        $mid = session('account.id');

        return view('/index/intelligent', compact('menu', 'link', 'website', 'data', 'signPackage','account','identify','mid'));
    }

    #多语文档
    public function knowledge_list2(Request $request){
        $dat = input();

        if(isset($dat['pa'])){
            $limit = $request->get('limit');
            $page = $request->get('page') - 1;
            if ($page != 0) {
                $page = $limit * $page;
            }
            $where =['uid'=>session('account.id')];
//            $keyword = isset($dat['keywords']) ? trim($dat['keywords']) : '';
//            ->where('name', 'like', '%'.$keyword.'%')
            $count = Db::name('experience_knowledge_list')->where($where)->count();
            $rows = DB::name('experience_knowledge_list')->where($where)
                ->limit($page . ',' . $limit)
                ->order('id asc')
                ->select();

            foreach ($rows as $k=>$item) {
                #知识条状态
                if($item['status']==0){
                    $rows[$k]['status_name'] = '未提交';
                }
                elseif($item['status']==1 && $item['is_add_dataset']==0){
                    $rows[$k]['status_name'] = '正在构建索引中';
                }
                elseif($item['status']==1 && $item['is_add_dataset']==1){
                    $rows[$k]['status_name'] = '确认已构建索引';
                }

                #知识条类型
                if($item['type']==1){
                    $rows[$k]['type_name'] = '商品资讯';
                }
                elseif($item['type']==2){
                    $rows[$k]['type_name'] = '政策资讯';
                }
                elseif($item['type']==3){
                    $rows[$k]['type_name'] = '历史对话';
                }

                $rows[$k]['createtime'] = date('Y-m-d H:i:s',$item['createtime']);
            }

            return json(['code'=>0,'count'=>$count,'data'=>$rows]);
        }else{
            $website['title'] = $this->website_name;
            $website['keywords'] = $this->website_keywords;
            $website['description'] = $this->website_description;
            $website['ico'] = $this->website_ico;
            $website['sico'] = $this->website_sico;
            $website['tel'] = $this->website_tel;
            $website['email'] = $this->website_email;
            $website['copyright'] = $this->website_copyright;
            $website['color'] = $this->website_color;
            $website['color_word'] = $this->website_colorword;
            $website['color_adorn'] = $this->website_coloradorn;
            $website['color_head'] = $this->website_colorhead;
            $website['website_contact'] = $this->website_contact;

            return view('',compact('website'));
        }
    }

    #多语文档编辑
    public function save_knowledge2(Request $request){
        $data = input();
        $company_id =  session('manage_person.company_id');
        $id = isset($data['id'])?intval($data['id']):0;

        if ($request->isAJAX()) {
            $file_path = '';
            if(empty($data['type'])){
                return json(['code'=>-1,'msg'=>'请选择知识类型']);
            }
            if($data['type']==1 || $data['type']==2){
                #商品和政策
                if(isset($data['file_path'])){
                    $file_path = json_encode($data['file_path'],true);
                }else{
                    return json(['code'=>-1,'msg'=>'请上传文档']);
                }
            }
            $knowledge_id = 0;
            if($data['type']==1 || $data['type']==3){
                #商品和历史对话
                if($data['type']==1){
                    $knowledge_id = intval($data['goods_id']);
                }
                elseif($data['type']==3){
                    $knowledge_id = intval($data['dialogue_id']);
                }
            }

            if($id>0){
                $res = Db::name('experience_knowledge_list')->where(['id'=>$id])->update([
                    'uid'=>session('account.id'),
                    'type'=>intval($data['type']),
                    'knowledge_id'=>$knowledge_id,
                    'file_path'=>$file_path,
                    'status'=>0
                ]);
            }
            else{
                $res = Db::name('experience_knowledge_list')->insertGetId([
                    'uid'=>session('account.id'),
                    'type'=>intval($data['type']),
                    'knowledge_id'=>$knowledge_id,
                    'file_path'=>$file_path,
                    'status'=>1,
                    'createtime'=>time()
                ]);

                if($res){
//                    $post_data = ['id'=>$res];
//
//                    httpRequest('https://shop.gogo198.cn/collect_website/public/?s=api/getgoods/now_sync_file_to_local',$post_data,array(
//                        'Content-Type: application/json; charset=utf-8',
//                        'Content-Length:' . strlen($post_data),
//                        'Cache-Control: no-cache',
//                        'Pragma: no-cache'
//                    ));
                    httpRequest('https://shop.gogo198.cn/collect_website/public/?s=api/getgoods/now_sync_file_to_local&id='.$res,[]);
                }
            }

            if($res){
                return json(['code'=>0,'msg'=>'操作成功']);
            }else{
                return json(['code'=>-1,'msg'=>'暂无修改']);
            }

        }else{
            $info = ['type'=>0,'file_path'=>[],'knowledge_id'=>''];
            if($id>0){
                $info = Db::name('experience_knowledge_list')->where(['id'=>$id])->find();
                $info['file_path'] = json_decode($info['file_path'],true);
            }

            $website['title'] = $this->website_name;
            $website['keywords'] = $this->website_keywords;
            $website['description'] = $this->website_description;
            $website['ico'] = $this->website_ico;
            $website['sico'] = $this->website_sico;
            $website['tel'] = $this->website_tel;
            $website['email'] = $this->website_email;
            $website['copyright'] = $this->website_copyright;
            $website['color'] = $this->website_color;
            $website['color_word'] = $this->website_colorword;
            $website['color_adorn'] = $this->website_coloradorn;
            $website['color_head'] = $this->website_colorhead;
            $website['website_contact'] = $this->website_contact;

            return view('',compact('info','website','id'));
        }
    }

    #建议
    public function advice(Request $request){
        $dat = input();

        if($request->isAjax()){
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
                'pid'=>intval($dat['id']),
                'name'=>trim($dat['name']),
                'email'=>trim($dat['email']),
                'tel'=>trim($dat['mobile']),
                'remark'=>$dat['content'],
                'createtime'=>time(),
            ]);
            if($res){
                return json(['code'=>0,'msg'=>'提交成功！']);
            }
        }
    }
    
    #上传文件
    public function upload_file(Request $request){
        header('Access-Control-Allow-Origin:*');
        date_default_timezone_set("Asia/chongqing");
        error_reporting(E_ERROR);
        header("Content-Type: text/html; charset=utf-8");
        set_time_limit(0);
        $dat = input();

        $folder = isset($dat['folder'])?trim($dat['folder']):'merch_file';
        $type = isset($dat['type'])?trim($dat['type']):'merch_file';

        $path = ROOT_PATH . 'public' . DS . 'uploads' . DS . $folder . DS . $type;
        // $this->mkdirs($path);
        $file = request()->file('file');

        if( $file )
        {
            $info = $file->rule('uniqid')->move($path);
            if( $info )
            {
                return json(["error" => 1, "message" => "上传成功", "file_path" => '/uploads/'.$folder.'/'.$type.'/'.$info->getSaveName() ]);
            }else{
                return json(["error" => 0, "message" => "上传失败", "path" => "" ]);
            }

        }else{
            return json(["error" => 0, "message" => "请先上传文件！"]);
        }
    }

    #上传自定义文件
    public function upload_diy_file(Request $request){
        set_time_limit(0);

        $data = input();
        if(trim($data['upName'])==''){
            return json(['code' => 0, 'msg' => '请输入文件名称后上传文件！']);
        }
        $path = ROOT_PATH.'public'.'/uploads/'.$data['folder'];
        try {
            $file = request()->file('file');

            $filename = $_FILES['file']['name']; // 假设这是上传文件的名称
            $ext = pathinfo($filename, PATHINFO_EXTENSION);
            $return_name = trim($data['upName']).'.'.$ext;
            $filename = trim($data['upName']).'.'.$ext;

            $info = $file->rule('uniqid')->move($path,$filename);
            $files = 'uploads/'.$data['folder'].'/'.$filename;
        } catch (\Exception $e) {
            return json(['code' => 0, 'msg' => '文件上传失败！']);
        }

        return json(['code' => 1, 'msg' => '文件上传成功！' ,'file_path' => $files, 'filename' => $return_name]);
    }
    
    public function sitemap() {
        $urls = [];
        $base = 'https://' . $_SERVER['HTTP_HOST'];
    
        // 首页
        $urls[] = ['loc' => $base . '/', 'priority' => '1.0', 'changefreq' => 'daily'];
    
        // 调试：输出数据库配置
        $db_config = config('database');
        $debug = "DEBUG: DB=" . $db_config['database'] . ", Host=" . $db_config['hostname'];
    
        try {
            $menus = Db::name('website_navbar')->where('status', 1)->select();
            if (empty($menus)) {
                $menus = Db::name('website_navbar')->limit(5)->select(); // 强制取
            }
            foreach ($menus as $m) {
                $urls[] = [
                    'loc' => $base . '/?s=index/detail&id=' . $m['id'],
                    'priority' => '0.8',
                    'changefreq' => 'weekly'
                ];
            }
            $debug .= ", Menus=" . count($menus);
        } catch (\Exception $e) {
            $debug .= ", ERROR=" . $e->getMessage();
        }
    
        // 输出调试 + XML
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $xml .= "<!-- $debug -->" . PHP_EOL;
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;
        foreach ($urls as $u) {
            $xml .= "  <url><loc>" . htmlspecialchars($u['loc']) . "</loc><priority>{$u['priority']}</priority><changefreq>{$u['changefreq']}</changefreq></url>" . PHP_EOL;
        }
        $xml .= '</urlset>';
    
        return response($xml, 200, ['Content-Type' => 'text/xml']);
    }
    
    public function robots() {
        $host = $_SERVER['HTTP_HOST'];
        $txt = "User-agent: *\n";
        $txt .= "Allow: /\n";
        $txt .= "Disallow: /index.php\n";
        $txt .= "Disallow: /admin/\n";
        $txt .= "Sitemap: https://$host/sitemap.xml";
        return response($txt, 200, ['Content-Type' => 'text/plain']);
    }

    //判断文件夹是否存在，没有则新建。
    // public function mkdirs($dir, $mode = 0777)
    // {
    //     dd($dir);
    //     if (is_dir($dir) || @mkdir($dir, $mode)) {
    //         return true;
    //     }
    //     if (!mkdirs(dirname($dir), $mode)) {
    //         return false;
    //     }
    //     return @mkdir($dir, $mode);
    // }
}
