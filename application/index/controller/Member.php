<?php
namespace app\index\controller;

use think\Request;
use think\Db;
use think\Controller;
use think\Log;

header("Access-Control-Allow-Origin: *");

#决策应用
class Member
{
    public $website_name = '';
    public $website_keywords = '';
    public $website_description = '';
    public $website_ico = '';
    public $website_sico = '';
    public $website_tel = '';
    public $website_email = '';
    public $website_copyright = '';
    public $website_color = '';
    public $website_color_inner = '';
    public $website_colorword = '';
    public $website_inpic = '';
    public $website_contact = [];
    public $apps = [];
    public $config = [
        //数据库类型
        'type' => 'mysql',
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
        'prefix' => '',
    ];
    public $medical_config = [
        //数据库类型
        'type' => 'mysql',
        //服务器地址
        'hostname' => 'rm-wz9mt4j79jrdh0p3z.mysql.rds.aliyuncs.com',
        //数据库名
        'database' => 'medical',
        //用户名
        'username' => 'gogo198',
        //密码
        'password' => 'Gogo@198',
        //端口
        'hostport' => '3306',
        //表前缀
        'prefix' => '',
    ];

    #查询当前网址在系统中的配置
    public function __construct(Request $request)
    {
        $dat = input();

        $website = Db::name('website_basic')->where(['id' => 1])->find();

        #监测有无设置语言
        if (session('lang') == null) {
            session('lang', 'zh');
        }

        $website['name'] = json_decode($website['name'], true);
        $website['keywords'] = json_decode($website['keywords'], true);
        $website['desc'] = json_decode($website['desc'], true);
        $website['copyright'] = json_decode($website['copyright'], true);
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
        $this->website_inpic = $website['inpic'];
        $this->website_contact = Db::name('website_contact')->where(['system_id' => 1, 'company_id' => 0])->select();
        $this->apps = Db::name('website_list')->select();
        #日志记录
//        platform_log($request);
    }

    public function get_tips(Request $request)
    {
        $data = input();
        // dd($data);
        $where = array();

        if ($data['controller_names'] == 'mainbody' && $data['function_name'] == 'addMainBody') {
            $data['controller_names'] = 'main';
            $data['function_name'] = 'add';
        }

        if ($data['controller_names'] == 'logistics' && $data['function_name'] == 'index') {
            $data['controller_names'] = 'logistic';
            $data['function_name'] = 'index';
        }

        if ($data['controller_names'] == 'elists' && $data['function_name'] == 'index') {
            $data['controller_names'] = 'elist';
            $data['function_name'] = 'index';
        }

        if ($data['controller_names'] == 'ccgoods' && $data['function_name'] == 'add_bol') {
            $data['controller_names'] = 'ccgoodsdecl';
            $data['function_name'] = 'add_bol';
        }

        if ($data['controller_names'] == 'goodsmanagement' && $data['function_name'] == 'shelfIndex') {
            $data['controller_names'] = 'goods';
            $data['function_name'] = 'shelf_index';
        }

        $where['controller_name'] = $data['controller_names'];
        $where['function_name'] = $data['function_name'];
        if ($data['value_name'] != '') {
            $where['value'] = $data['value_name'];
            return Db::name('decl_user_systemtips')->where($where)->find();
        } else {
            return Db::name('decl_user_systemtips')->where($where)->select();
        }

    }
    #菜单栏目
    public function menu(){
        $menu = Db::name('website_navbar')->where(['system_id'=>4,'pid'=>0])->select();
        foreach($menu as $k=>$v){
            $menu[$k]['name'] = json_decode($v['name'],true)[session('lang')];
            $menu[$k]['childMenu'] = $this->getDownMenu($v['id']);
        }
        return $menu;
    }

    #下级菜单
    public function getDownMenu($id){
        $cmenu = Db::name('website_navbar')->where(['pid'=>$id])->select();
        foreach($cmenu as $k=>$v){
            $cmenu[$k]['name'] = json_decode($v['name'],true)[session('lang')];
            $cmenu[$k]['childMenu'] = Db::name('website_navbar')->where(['pid'=>$v['id']])->select();
            foreach($cmenu[$k]['childMenu'] as $k2=>$v2){
                $cmenu[$k]['childMenu'][$k2]['name'] = json_decode($v2['name'],true)[session('lang')];
                $cmenu[$k]['childMenu'][$k2]['childMenu'] = Db::name('website_navbar')->where(['pid'=>$v2['id']])->select();
                foreach($cmenu[$k]['childMenu'][$k2]['childMenu'] as $k3=>$v3){
                    $cmenu[$k]['childMenu'][$k2]['childMenu'][$k3]['name'] = json_decode($v3['name'],true)[session('lang')];
                }
            }
        }
        return $cmenu;
    }
    #菜单栏目
    public function menu_footer(){
        $menu = Db::connect($this->config)->name('footer_body')->where(['system_id'=>4,'company_id'=>0,'pid'=>0])->select();
        foreach($menu as $k=>$v){
            $menu[$k]['childMenu'] = $this->getDownMenuFooter($v['id']);
        }
        return $menu;
    }

    #下级菜单
    public function getDownMenuFooter($id){
        $cmenu = Db::connect($this->config)->name('footer_body')->where(['pid'=>$id])->select();
        return $cmenu;
    }

    #获取菜单权限
    public function get_menu(Request $request)
    {
        $dat = input();

        $level_id = isset($dat['level_id']) ? intval($dat['level_id']) : 0;

        #获取当前账户的权限
//        $now_auth = Db::name('centralize_manage_person')->where(['gogo_id' => session('account.id')])->find();
//        $level_list = '';
//        if ($now_auth['pid'] == 0) {
//            #管理员,查询总后台角色表
////            $level_list = Db::name('centralize_backstage_role')->where(['id'=>$now_auth['role_id']])->field('authList')->find()['authList'];
//            $level_list = Db::name('website_user_company')->where(['id' => $now_auth['company_id']])->find()['authList'];
//        } else {
//            #员工,查询企业角色表
//            $level_list = Db::name('centralize_manage_level')->where(['id' => $now_auth['role_id']])->field('authList')->find()['authList'];
//        }
        #普通用户权限
        $level_list = '198198,224,225,226,227,228,229,231,232,233,234,235,236,237,238,239,240,241,242,244,246,249';
        $is_merchant = Db::name('centralize_manage_person')->where(['gogo_id'=>session('account.id')])->find();
        if(!empty($is_merchant)){
            $level_list .= ',243,247,245';
        }

        $levelData = Db::name('centralize_manage_level')->where(['id' => $level_id])->field('authList')->find();
        if ($level_id) {
            $level_menus = "," . $levelData['authList'];
        } else {
            $level_menus = "";
        }

        $list = Db::name('centralize_manage_menu')->where(['status' => 0, 'auth_type' => 4])->order('sort asc')->select();
        $newList = [];
        foreach ($list as $item) {
            if (strpos($level_list, $item['id'] . ",") || strpos($level_list, "," . $item['id'] . ",") || strpos($level_list, "," . $item['id'])) {
                if (empty($level_menus)) {
                    #新增角色时默认选中
                    array_push($newList, ['id' => $item['id'], 'pId' => $item['pid'], 'title' => $item['title'], 'checked' => 1]);
                } else {
                    array_push($newList, ['id' => $item['id'], 'pId' => $item['pid'], 'title' => $item['title'], 'checked' => strpos($level_menus, "," . $item['id'] . ",") !== false ? true : false]);
                }

                foreach ($list as $value) {
                    if ($item['id'] == $value['pid'] && !$value['pid'] != 0) {
                        if (empty($level_menus)) {
                            #新增角色时默认选中
                            array_push($newList, ['id' => $value['id'], 'pId' => $value['pid'], 'title' => $value['title'], 'checked' => 1]);
                        } else {
                            array_push($newList, ['id' => $value['id'], 'pId' => $value['pid'], 'title' => $value['title'], 'checked' => strpos($level_menus, "," . $value['id'] . ",") !== false ? true : false]);
                        }
                    }
                }
            }
        }


        $data['menuList'] = $newList;
        return json(['code' => 0, 'data' => $data]);
    }

    #中转
    public function index()
    {
        $dat = input();
        $type = isset($dat['type']) ? intval($dat['type']) : 0;
        #判断是否登录
        if (empty(session('account.id'))) {
            header('Location:/?s=index/member_login&type=' . $type);
        } else {
            #跳转会员中心
            header('Location:/?s=member/member_center&type=' . $type);
        }
    }

    #会员中心
    public function member_center(Request $request)
    {
        $dat = input();
//        session(null);
        $mid = isset($dat['mid'])?intval($dat['mid']):0;
        $key = isset($dat['key'])?trim($dat['key']):'';
        if($mid>0){
            $user = Db::name('website_user')->where(['id'=>$mid])->find();
            session('account',$user);
        }
        if(empty(session('account'))){
            $url_this = '//www.gogo198.net'.$_SERVER["REQUEST_URI"];
            header('Location:/?s=index/customer_login&open=4&param2='.base64_encode($url_this));exit;
        }
        $tz_url = isset($dat['tz_url'])?base64_decode($dat['tz_url']):'/?s=member/chat_list&pid=225';
        $footer = isset($dat['footer'])?intval($dat['footer']):2;
//        $menu_pid = 0;
//        $is_pc = 1;
//        if (isset($dat['is_pc'])) {
//            $is_pc = 0;
//            $menu_pid = 241;
//        } else {
//            if (isMobile()) {
                $menu_pid = 241;
                $is_pc = 0;
//            }
//        }

        #企业id
        $cid = isset($dat['cid']) ? base64_decode($dat['cid']) : 0;

//        #获取该角色权限
        $func = [];
        $level['authList'] = '224,225,226,227,228,229,231,232,233,234,235,236,237,238,239,240,241,242,244,246,249,251,252,253,254';
        $is_merchant = Db::name('centralize_manage_person')->where(['gogo_id'=>session('account.id')])->find();
        if(!empty($is_merchant)){
            $company = Db::name('website_user_company')->where(['id'=>$is_merchant['company_id']])->find();
            if(strpos($company['authList'],'243') !== false){
                $level['authList'] .= ',243';
            }
//            if(strpos($company['authList'],'245') !== false){
//                $level['authList'] .= ',245';
//            }
//            if(strpos($company['authList'],'247') !== false){
//                $level['authList'] .= ',247';
//            }
            if(strpos($company['authList'],'245') !== false){
                $other_func = Db::name('centralize_manage_menu')->where(['id'=>245])->find();
                $func = array_merge($func,[$other_func]);
            }
            if(strpos($company['authList'],'247') !== false){
                $other_func = Db::name('centralize_manage_menu')->where(['id'=>247])->find();
                $func = array_merge($func,[$other_func]);
            }
        }

        #商户端菜单
        $menu = Db::name('centralize_manage_menu')->where(['pid' => $menu_pid, 'status' => 0, 'auth_type' => 4])->order('sort asc')->select();
        foreach ($menu as $k => $v) {
            $menu[$k]['children'] = Db::name('centralize_manage_menu')->where(['pid' => $v['id']])->order('sort asc')->select();
            $menu[$k]['url'] = str_replace('/?s=merchant','/?s=member',$v['url']);

            if(!empty($menu[$k]['children'])){
                foreach($menu[$k]['children'] as $k2=>$v2){
                    if($v2['id']!=247 && $v2['id']!=245) {
                        $menu[$k]['children'][$k2]['url'] = str_replace('/?s=merchant', '/?s=member', $v2['url']);
                    }
                    $menu[$k]['children'][$k2]['children'] = Db::name('centralize_manage_menu')->where(['pid' => $v2['id'],'is_open'=>0])->order('sort asc')->select();
                    if(!empty($menu[$k]['children'][$k2]['children'])){
                        foreach($menu[$k]['children'][$k2]['children'] as $k3=>$v3){
                            $menu[$k]['children'][$k2]['children'][$k3]['url'] = str_replace('/?s=merchant', '/?s=member', $v3['url']);
                        }
                    }
                }
            }
        }

        #搜索配置
        $search = Db::connect($this->config)->name('search_setting')->where(['system_id' => 4, 'company_id' => 0])->find();

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
        $website['color_inner'] = $this->website_color_inner;
        $website['website_contact'] = $this->website_contact;
        $website['apps'] = $this->apps;

        #当前账户的企业
        $company = [];
//        if (session('manage_person.pid') == 0) {
//            #管理员
//            $company = Db::name('website_user_company')->where(['status' => 0, 'user_id' => session('manage_person.gogo_id')])->select();
//        } else {
//            #员工
//
//            #先找到自己的所有账号
//            $manage_person = Db::name('centralize_manage_person')->where(['gogo_id' => session('account.id')])->select();
//            foreach ($manage_person as $k => $v) {
//                $company[$k] = Db::name('website_user_company')->where(['status' => 0, 'id' => $v['company_id']])->find();
//            }
//        }

//        if ($is_pc == 1) {
//            return view('/member/member_center2', compact('website', 'menu', 'level', 'manage_info', 'search', 'company', 'cid','tz_url','footer','func'));
//        } else {
            return view('/member/member_center', compact('website', 'menu', 'level', 'manage_info', 'search', 'company', 'cid','tz_url','footer','func'));
//        }
    }

    public function system_manage(Request $request){
        $dat = input();
        $pid = isset($dat['pid'])?intval($dat['pid']):0;
        $group_id = isset($dat['group_id'])?intval($dat['group_id']):0;

        $menu_list = Db::name('centralize_manage_menu')->where(['pid'=>$pid])->select();
        foreach($menu_list as $k=>$v){
            $menu_list[$k]['url'] = str_replace('/?s=merchant','/?s=member',$v['url']);
        }
        #获取企业权限
        $level_list = '198198,224,225,226,227,228,229,231,232,233,234,235,236,237,238,239,240,241,242,243,244,246,249';
//        $level_list = Db::name('website_user_company')->where(['id'=>session('manage_person.company_id')])->field('authList')->find()['authList'];

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
        $website['color_inner'] = $this->website_color_inner;
        $website['website_contact'] = $this->website_contact;
        $website['apps'] = $this->apps;

        return view('',compact('website','menu_list','level_list','group_id'));
    }

    #系统管理-二级
    public function system_manage2(Request $request){
        $dat = input();
        $pid = isset($dat['pid'])?intval($dat['pid']):0;
        $group_id = isset($dat['group_id'])?intval($dat['group_id']):0;

        $menu_list = Db::name('centralize_manage_menu')->where(['pid'=>$pid])->select();
        foreach($menu_list as $k=>$v){
            $menu_list[$k]['url'] = str_replace('/?s=merchant','/?s=member',$v['url']);
        }
        #获取企业权限
        $level_list = '198198,224,225,226,227,228,229,231,232,233,234,235,236,237,238,239,240,241,242,243,244,246,249';
//        $level_list = Db::name('website_user_company')->where(['id'=>session('manage_person.company_id')])->field('authList')->find()['authList'];

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
        $website['color_inner'] = $this->website_color_inner;
        $website['website_contact'] = $this->website_contact;
        $website['apps'] = $this->apps;

        return view('',compact('website','menu_list','level_list','group_id'));
    }

    #决策管理=================================================================START
    #决策列表
    public function business_list(Request $request){
        $dat = input();
        $pid = isset($dat['pid'])?intval($dat['pid']):0;

        if(isset($dat['pa'])){
            $limit = $request->get('limit');
            $page = $request->get('page') - 1;
            if ($page != 0) {
                $page = $limit * $page;
            }
            $keyword = isset($dat['keywords']) ? trim($dat['keywords']) : '';

            if($pid==237){
                #订单咨询
//                $company = Db::name('website_user_company')->where(['id'=>session('manage_person.company_id')])->find();
                $count = Db::name('website_order_list')->where(['user_id'=>session('account.id')])->count();
                $rows = Db::name('website_order_list')->where(['user_id'=>session('account.id')])
                    ->where('ordersn', 'like', '%'.$keyword.'%')
                    ->limit($page . ',' . $limit)
                    ->order('id asc')
                    ->select();
            }
            elseif($pid==238){
                #商品咨询
//                $company = Db::name('website_user_company')->where(['id'=>session('manage_person.company_id')])->find();
                $user = Db::name('website_user')->where(['id'=>session('account.id')])->find();
                $shopping_user = Db::connect($this->config)->name('user')->where(['gogo_id'=>$user['custom_id']])->find();
                $count = Db::connect($this->config)->name('goods_history')->where(['user_id'=>$shopping_user['user_id']])->count();
                $rows = Db::connect($this->config)->name('goods_history')
                    ->alias('a')
                    ->join('goods b','b.goods_id=a.goods_id')
                    ->where(['a.user_id'=>$shopping_user['user_id']])
                    ->where('b.goods_name', 'like', '%'.$keyword.'%')
                    ->limit($page . ',' . $limit)
                    ->order('a.history_id asc')
                    ->field(['b.goods_id','b.goods_name'])
                    ->select();
            }
            elseif($pid==239){
                #决策咨询
                $all_group = Db::name('decision_group_member')->where(['user_id'=>session('account.id'),'status'=>1])->field('group_id')->select();
                $group_id = '';
                $group_id_arr = [];
                foreach($all_group as $k=>$v){
                    $group_id .= $v['group_id'].',';
                    array_push($group_id_arr,$v['group_id']);
                }
                $group_id = rtrim($group_id,',');

//                ->whereRaw('find_in_set(group_id,?)',[$group_id])
                $count = Db::name('decision_topics')->whereIn('group_id',$group_id_arr)->where('name', 'like', '%'.$keyword.'%')->count();
                $rows = DB::name('decision_topics')->whereIn('group_id',$group_id_arr)
                    ->where('name', 'like', '%'.$keyword.'%')
                    ->limit($page . ',' . $limit)
                    ->order('id desc')
                    ->select();
            }
            else {
                $count = 3;
                $rows = [['id' => 1, 'ordersn' => '123456'], ['id' => 2, 'ordersn' => '456789'], ['id' => 3, 'ordersn' => '123789']];
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
            $website['color_inner'] = $this->website_color_inner;
            $website['website_contact'] = $this->website_contact;
            $website['apps'] = $this->apps;

            return view('',compact('website','pid'));
        }
    }
    #群组列表
    public function group_list(Request $request){
        $dat = input();
        if(isset($dat['pa'])){
            $limit = $request->get('limit');
            $page = $request->get('page') - 1;
            if ($page != 0) {
                $page = $limit * $page;
            }
            $where =['a.user_id'=>session('account.id'),'a.status'=>1];
            $keyword = isset($dat['keywords']) ? trim($dat['keywords']) : '';

            $count = Db::name('decision_group_member')
                ->alias('a')
                ->join('decision_group b','b.id=a.group_id')
                ->where($where)
                ->where('b.name', 'like', '%'.$keyword.'%')
                ->count();
            $rows = DB::name('decision_group_member')
                ->alias('a')
                ->join('decision_group b','b.id=a.group_id')
                ->where($where)
                ->where('b.name', 'like', '%'.$keyword.'%')
                ->limit($page . ',' . $limit)
                ->order('b.id desc')
                ->field(['b.*'])
                ->select();

            foreach ($rows as &$item) {
//                $item['createtime'] = date('Y-m-d H:i:s', $item['createtime']);
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
            $website['color_inner'] = $this->website_color_inner;
            $website['website_contact'] = $this->website_contact;
            $website['apps'] = $this->apps;

            return view('',compact('website'));
        }
    }
    #保存群组
    public function save_group(Request $request){
        $dat = input();
        $id = isset($dat['id'])?intval($dat['id']):0;

        if($request->isAjax()){
            if($id>0){
                Db::name('decision_group')->where(['id'=>$id])->update(['name'=>trim($dat['name'])]);
            }else{
                $insert_id = Db::name('decision_group')->insertGetId([
//                    'company_id'=>session('manage_person.company_id'),
                    'user_id'=>session('account.id'),
                    'name'=>trim($dat['name']),
                    'createtime'=>time()
                ]);

                $insert_data = [];
                foreach($dat['type'] as $k=>$v){
                    if($v==1){
                        if(empty($dat['user_id'][$k])){
                            return json(['code'=>-1,'msg'=>'选择组员不能为空']);
                        }
                    }
                    elseif($v==2){
                        if(empty($dat['email'][$k])){
                            return json(['code'=>-1,'msg'=>'组员邮箱不能为空']);
                        }
                    }
                    $insert_data = array_merge($insert_data,[['type'=>$v,'user_id'=>$dat['user_id'][$k],'email'=>trim($dat['email'][$k]),'title'=>trim($dat['title'][$k])]]);
                }

                foreach($insert_data as $k=>$v){
                    Db::name('decision_group_member')->insert([
                        'group_id'=>$insert_id,
                        'type'=>$v['type'],
                        'user_id'=>$v['user_id'],
                        'email'=>$v['email'],
                        'title'=>$v['title'],
                        'status'=>0
                    ]);

                    if($v['type']==1){
                        $data = Db::name('website_user')->where(['id'=>$v['user_id']])->find();
                        $name = '';
                        if(!empty($data['realname'])){
                            $name = $data['realname'];
                        }elseif(!empty($data['nickname'])){
                            $name = $data['nickname'];
                        }elseif(!empty($data['email'])){
                            $name = $data['email'];
                        }
                        common_notice2($data,[
                            'title'=>session('account.realname').'邀请你加入群组',
                            'msg'=>'<p>'.$name.'，你好，你的好友'.session('account.realname').'现正式邀请您加入Ta组建的“'.trim($dat['name']).'”互动聊天、商议事务。</p><br/><p>若你，认识'.session('account.realname').'及同意入群聊天，请点击以下链接加入：</p><p>'.'https://www.gogo198.net/?s=member/member_center&tz_url='.base64_encode('https://www.gogo198.net/?s=member/join_group&group_id='.base64_encode($insert_id)).'&footer=1'.'</p><br/><p>若你，不认识'.session('account.realname').'，或认识但不愿意加入有关群组，或对此邮件有疑问，可随时邮件联系我们，或不予理会此邮件，或直接与邀请人联系处理。</p><br/><p>购购网 | Gogo</p>',
                            'opera'=>'待入组',
                            'url'=>'https://www.gogo198.net/?s=member/member_center&tz_url='.base64_encode('https://www.gogo198.net/?s=member/join_group&group_id='.base64_encode($insert_id)).'&footer=1'
                        ]);
//                        kefu_auth_setting($data);
                    }elseif($v['type']==2){
                        #邮箱通知
                        common_notice2(['id'=>'','openid'=>'','phone'=>'','email'=>trim($v['email'])],[
                            'title'=>session('account.realname').'邀请你加入群组',
                            'msg'=>'<p>'.$v['email'].'，你好，你的好友'.session('account.realname').'现正式邀请您加入Ta组建的“'.trim($dat['name']).'”互动聊天、商议事务。</p><br/><p>若你，认识'.session('account.realname').'及同意入群聊天，请点击以下链接加入：</p><p>'.'https://www.gogo198.net/?s=member/member_center&tz_url='.base64_encode('https://www.gogo198.net/?s=member/join_group&group_id='.base64_encode($insert_id)).'&footer=1'.'</p><br/><p>若你，不认识'.session('account.realname').'，或认识但不愿意加入有关群组，或对此邮件有疑问，可随时邮件联系我们，或不予理会此邮件，或直接与邀请人联系处理。</p><br/><p>购购网 | Gogo</p>','opera'=>'待入组',
                            'url'=>'https://www.gogo198.net/?s=member/member_center&tz_url='.base64_encode('https://www.gogo198.net/?s=member/join_group&group_id='.base64_encode($insert_id)).'&footer=1'
                        ]);
                    }
                }

            }
            return json(['code'=>0,'msg'=>'操作成功']);
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
            $website['color_inner'] = $this->website_color_inner;
            $website['website_contact'] = $this->website_contact;
            $website['apps'] = $this->apps;

            $data = ['name'=>''];

            if($id>0){
                $data = Db::name('decision_group')->where(['id'=>$id])->find();
            }

            return view('',compact('id','website','data'));
        }
    }
    #发起议题
    public function save_topics(Request $request){
        $dat = input();
        $id = isset($dat['id'])?intval($dat['id']):0;
        $method = isset($dat['method'])?$dat['method']:0;

        if($request->isAjax()){
            if($dat['pa']==1){
                if($id>0){
                    if($method==3){
                        #修改议题
                        $starttime = strtotime($dat['starttime']);
                        $endtime = strtotime($dat['endtime']);
                        if($endtime<=$starttime){
                            return json(['code'=>-1,'msg'=>'结束时间不能小于开始时间']);
                        }

                        Db::name('decision_topics')->where(['id'=>$id])->update([
//                            'name'=>trim($dat['name']),
                            'content'=>trim($dat['content']),
                            'files'=>isset($dat['files'])?json_encode($dat['files'],true):'',
                            'pass_method'=>intval($dat['pass_method']),
//                            'starttime'=>$starttime,
                            'endtime'=>$endtime,
                            'is_pdf'=>0
                        ]);

                        foreach($dat['options_name'] as $k=>$v){
                            if(!empty(trim($v))){
                                if(isset($dat['options_id'][$k])){
                                    Db::name('decision_topics_option')->where(['topics_id'=>$id,'id'=>$dat['options_id'][$k]])->update([
                                        'name'=>trim($v),
                                        'content'=>trim($dat['options_remark'][$k])
                                    ]);
                                }else{
                                    Db::name('decision_topics_option')->insert([
                                        'topics_id'=>$id,
                                        'name'=>trim($v),
                                        'content'=>trim($dat['options_remark'][$k])
                                    ]);
                                }
                            }
                        }

                    }
                    elseif($method==4){
                        #修改时效
                        $starttime = strtotime($dat['starttime']);
                        $endtime = strtotime($dat['endtime']);
                        if($endtime<=$starttime){
                            return json(['code'=>-1,'msg'=>'结束时间不能小于开始时间']);
                        }

                        Db::name('decision_topics')->where(['id'=>$id])->update([
//                            'starttime'=>$starttime,
                            'endtime'=>$endtime,
                            'is_pdf'=>0
                        ]);
                    }
                }
                else{
                    $starttime = strtotime($dat['starttime']);
                    $endtime = strtotime($dat['endtime']);
                    if($endtime<=$starttime){
                        return json(['code'=>-1,'msg'=>'结束时间不能小于开始时间']);
                    }

                    #议题编号，这个组当天的顺序号
                    $starttime = strtotime(date('Y-m-d 00:00:00',time()));
                    $endtime = strtotime(date('Y-m-d 23:59:59',time()));
                    $ordersn = Db::name('decision_topics')->whereRaw('group_id='.$dat['group_id'].' and (createtime >= '.$starttime.' and createtime <= '.$endtime.')')->count();
                    $ordersn = sprintf('%03d',$ordersn+1);
//                    dd('BO'.intval($dat['group_id']).date('Ymd').$ordersn);
                    $topics_id = Db::name('decision_topics')->insertGetId([
//                        'company_id'=>session('manage_person.company_id'),
                        'user_id'=>session('account.id'),
                        'ordersn'=>'BO'.intval($dat['group_id']).date('Ymd').$ordersn,
                        'name'=>trim($dat['name']),
                        'group_id'=>intval($dat['group_id']),
                        'cc_member2'=>rtrim($dat['cc_member2']),
                        'content'=>trim($dat['content']),
                        'files'=>isset($dat['files'])?json_encode($dat['files'],true):'',
                        'pass_method'=>intval($dat['pass_method']),
                        'starttime'=>$starttime,
                        'endtime'=>$endtime,
                        'createtime'=>time()
                    ]);

                    foreach($dat['options_name'] as $k=>$v){
                        if(!empty(trim($v))){
                            Db::name('decision_topics_option')->insert([
                                'topics_id'=>$topics_id,
                                'name'=>trim($v),
                                'content'=>trim($dat['options_remark'][$k])
                            ]);
                        }
                    }

                    #通知组员,'a.status'=>1
                    $gm = Db::name('decision_group_member')
                        ->alias('a')
                        ->join('website_user b','b.id=a.user_id')
                        ->where(['a.group_id'=>intval($dat['group_id'])])
                        ->field(['b.*'])
                        ->select();
                    foreach($gm as $k=>$v){
//                        common_notice($v,['msg'=>session('manage_person.name').'邀请您参与决策['.trim($dat['name']).']','opera'=>'待参与','url'=>'https://www.gogo198.net/?s=merchant/topics_detail&id='.$topics_id.'&is_edit='.base64_encode('1')]);
//                        common_notice($v,['msg'=>session('manage_person.name').'邀请您参与决策['.trim($dat['name']).']','opera'=>'待参与','url'=>'https://www.gogo198.net/?s=member/topics_detail&id='.$topics_id.'&is_edit='.base64_encode('1')]);
                        common_notice2($v,['title'=>session('account.realname').'邀请您查看决策['.trim($dat['name']).']','msg'=>session('account.realname').'邀请您参与决策['.trim($dat['name']).']','opera'=>'待参与','url'=>'https://www.gogo198.net/?s=member/member_center&tz_url='.base64_encode('https://www.gogo198.net/?s=member/topics_list&id='.$topics_id.'&is_edit='.base64_encode('1')).'&footer=2']);
                    }

                    #通知抄送人员
                    $cc_member2 = explode('、',rtrim($dat['cc_member2']));
                    foreach($cc_member2 as $k=>$v){
//                        common_notice(['id'=>'','email'=>$v,'openid'=>'','phone'=>''],['msg'=>session('manage_person.name').'邀请您查看决策['.trim($dat['name']).']','opera'=>'待查看','url'=>'https://www.gogo198.net/?s=member/topics_detail&id='.$topics_id.'&is_edit='.base64_encode('0')]);
                        common_notice2(['id'=>'','email'=>$v,'openid'=>'','phone'=>''],['title'=>session('account.realname').'邀请您查看决策['.trim($dat['name']).']','opera'=>'待查看','msg'=>'请点击链接查看：https://www.gogo198.net/?s=member/member_center&tz_url='.base64_encode('https://www.gogo198.net/?s=member/topics_list&id='.$topics_id.'&is_edit='.base64_encode('0')).'&footer=2']);
                    }
                }
            }

            return json(['code'=>0,'msg'=>'操作成功']);
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
            $website['color_inner'] = $this->website_color_inner;
            $website['website_contact'] = $this->website_contact;
            $website['apps'] = $this->apps;

            #群组
            $group = Db::name('decision_group')->where(['user_id'=>session('account.id')])->select();

            $data = ['name'=>'','group_id'=>0,'cc_member2'=>'','content'=>'','files'=>[],'pass_method'=>1,'starttime'=>date('Y-m-d H:i',time()),'endtime'=>date('Y-m-d H:i',time()+3600)];
            if($id>0){
                $data = Db::name('decision_topics')->where(['id'=>$id])->find();
                if(!empty($data['files'])){
                    $data['files'] = json_decode($data['files'],true);
                }
                $data['starttime'] = date('Y-m-d H:i',$data['starttime']);
                $data['endtime'] = date('Y-m-d H:i',$data['endtime']);

                $data['options'] = Db::name('decision_topics_option')->where(['topics_id'=>$id])->select();
            }

            return view('',compact('website','id','group','data','method'));
        }
    }
    #删除选项
    public function del_options(Request $request){
        $dat = input();
        $res=Db::name('decision_topics_option')->where(['topics_id'=>intval($dat['topics_id']),'name'=>trim($dat['name'])])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功']);
        }
    }
    #管理议题
    public function topics_manage(Request $request){
        $dat = input();
        $pa = isset($dat['pa'])?$dat['pa']:0;
        $group_id = isset($dat['group_id'])?intval($dat['group_id']):0;

        if(isset($dat['pa2'])){
            $limit = $request->get('limit');
            $page = $request->get('page') - 1;
            if ($page != 0) {
                $page = $limit * $page;
            }

            $keyword = isset($dat['keywords']) ? trim($dat['keywords']) : '';

            if($dat['pa2']==1){
                #我发起的
                $where =['user_id'=>session('account.id')];
                $count = Db::name('decision_topics')->where($where)->where('name', 'like', '%'.$keyword.'%')->count();
                $rows = DB::name('decision_topics')->where($where)
                    ->where('name', 'like', '%'.$keyword.'%')
                    ->limit($page . ',' . $limit)
                    ->order('id desc')
                    ->select();
            }
            elseif($dat['pa2']==2){
                #我参与的
                if($group_id>0){
                    $all_group = Db::name('decision_group_member')->where(['user_id'=>session('account.id'),'status'=>1,'group_id'=>$group_id])->field('group_id')->select();
                }else{
                    $all_group = Db::name('decision_group_member')->where(['user_id'=>session('account.id'),'status'=>1])->field('group_id')->select();
                }
//                $group_id = '';
                $group_id_arr = [];
                foreach($all_group as $k=>$v){
//                    $group_id .= $v['group_id'].',';
                    array_push($group_id_arr,$v['group_id']);
                }
//                $group_id = rtrim($group_id,',');

//                ->whereRaw('find_in_set(group_id,?)',[$group_id])
                $count = Db::name('decision_topics')->whereIn('group_id',$group_id_arr)->where('name', 'like', '%'.$keyword.'%')->count();
                $rows = DB::name('decision_topics')->whereIn('group_id',$group_id_arr)
                    ->where('name', 'like', '%'.$keyword.'%')
                    ->limit($page . ',' . $limit)
                    ->order('id desc')
                    ->select();
            }

            foreach ($rows as &$item) {
                if($item['status']==0){
                    $item['status_name'] = '进行中';
                }
                elseif($item['status']==-1){
                    $item['status_name'] = '不通过';
                }
                elseif($item['status']==1){
                    $item['status_name'] = '通过';
                }
//                $item['createtime'] = date('Y-m-d H:i:s', $item['createtime']);
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
            $website['color_inner'] = $this->website_color_inner;
            $website['website_contact'] = $this->website_contact;
            $website['apps'] = $this->apps;

            return view('',compact('website','pa','group_id'));
        }
    }
    #管理决策
    public function topics_manage2(Request $request){
        $dat = input();

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
        $website['color_inner'] = $this->website_color_inner;
        $website['website_contact'] = $this->website_contact;
        $website['apps'] = $this->apps;

        #议题id
        $topics_id = intval($dat['id']);
        #1、参与决策0/更改决策1
        $topics = Db::name('decision_topics')->where(['id'=>$topics_id])->find();
        #判断当前人是否在该群组
        $is_ingroup = Db::name('decision_group_member')->where(['group_id'=>$topics['group_id'],'user_id'=>session('account.id')])->find();
        if(empty($is_ingroup)){
            $info['is_ending'] = 0;
        }else{
            if(time()>$topics['endtime']){
                #已结束
                $info['is_ending'] = 0;
            }else{
                #决策进行中
                $info['is_ending'] = 1;
                $is_join = Db::name('decision_topics_selected')->where(['topics_id'=>$topics_id,'uid'=>session('account.id')])->find();
                $info['is_join'] = empty($is_join)?0:1;
            }
        }


        #2、查看该议题是否“我发起的”
        $is_me = Db::name('decision_topics')->where(['user_id'=>session('account.id')])->find();
        if(!empty($is_me)){
            #3、更改议题
            $is_empty = Db::name('decision_topics_selected')->where(['topics_id'=>$topics_id])->find();
            $info['is_empty'] = empty($is_empty)?1:0;
            #4、更改时效
            $info['is_date'] = 1;
            #5、分享决策
            $info['is_share_topics'] = 1;
            #6、分享决议
            if(time()>$is_me['endtime']){
                #已结束
                $info['is_share_chat'] = 1;
            }else{
                $info['is_share_chat'] = 0;
            }
        }else{
            #3、更改议题
            $info['is_empty'] = 0;
            #4、更改时效
            $info['is_date'] = 0;
            #5、分享决策
            $info['is_share_topics'] = 0;
            #6、分享决议
            $info['is_share_chat'] = 0;
        }

        return view('',compact('topics_id','website','info'));
    }
    #参与决策
    public function topics_detail(Request $request){
        $dat = input();
//        session(null);
        if(empty(session('account'))){
            $url_this = '//www.gogo198.net'.$_SERVER["REQUEST_URI"];
            header('Location:/?s=index/member_login&open=4&param2='.base64_encode($url_this));exit;
        }

        $id = isset($dat['id'])?intval($dat['id']):0;
        $is_edit = isset($dat['is_edit'])?base64_decode($dat['is_edit']):0;

        if($request->isAjax()){
            $ishave = Db::name('decision_topics_selected')->where(['topics_id'=>intval($dat['id']),'uid'=>session('account.id')])->find();
            if(!empty($ishave)){
                Db::name('decision_topics_selected')->where(['topics_id'=>intval($dat['id']),'uid'=>session('account.id')])->update(['option_id'=>intval($dat['option_id'])]);
            }else{
                Db::name('decision_topics_selected')->insert([
                    'topics_id'=>intval($dat['id']),
                    'option_id'=>intval($dat['option_id']),
                    'uid'=>session('account.id'),
                    'createtime'=>time()
                ]);
            }
            return json(['code'=>0,'msg'=>'操作成功']);
        }else{
//            $arr = [];
//            $counts = array_count_values($arr);
//            // 找到出现次数最多的元素的值
//            $maxCount = max($counts);
//            $mostFrequentValue = array_search($maxCount, $counts);
//            dd($maxCount);
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
            $website['color_inner'] = $this->website_color_inner;
            $website['website_contact'] = $this->website_contact;
            $website['apps'] = $this->apps;

            #议题
            $data = Db::name('decision_topics')->where(['id'=>$id])->find();
            #查看有无入组
            $is_join = Db::name('decision_group_member')->where(['group_id'=>$data['group_id'],'user_id'=>session('account.id')])->find();

            $data['files'] = json_decode($data['files'],true);
            $data['options'] = Db::name('decision_topics_option')->where(['topics_id'=>$id])->select();

            #已选选项
            $data['selected'] = Db::name('decision_topics_selected')->where(['topics_id'=>$id,'uid'=>session('account.id')])->find();

            #议题状态
            #5、判断议题是否“已结束”或“进行中”
            $data['is_ending'] = 0;
            if (time() > $data['endtime']) {
                #已结束
                $data['is_ending'] = 1;
                $data['is_pass'] = topics_result($data);
            }

            $data['starttime'] = date('Y-m-d H:i',$data['starttime']);
            $data['endtime'] = date('Y-m-d H:i',$data['endtime']);

            return view('',compact('id','website','data','is_edit','is_join'));
        }
    }
    #议题列表和当前发起议题id排在最上面
    public function topics_list(Request $request){
        $dat = input();
        $url_this = '//www.gogo198.net'.$_SERVER["REQUEST_URI"];

        if(empty(session('account'))){
            header('Location:/?s=index/member_login&open=4&param2='.base64_encode($url_this));exit;
        }

        #判断组员有无微信公众号openid，无就跳转到补充“基本信息页”
        $member = Db::name('website_user')->where(['id'=>session('account.id')])->find();
        if(empty($member['openid'])){
            header('Location: /?s=member/save_basic&param2='.base64_encode($url_this));
        }

        $id = isset($dat['id'])?intval($dat['id']):0;
        $is_edit = isset($dat['is_edit'])?base64_decode($dat['is_edit']):0;

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
        $website['color_inner'] = $this->website_color_inner;
        $website['website_contact'] = $this->website_contact;
        $website['apps'] = $this->apps;

        $is_join = ['status'=>1,'group_id'=>0];
        if($is_edit==0){
            #抄送人员，查找该议题结果
            $topics_list = Db::name('decision_topics')->where(['id' => $id])->select();

            foreach ($topics_list as $k => $v) {
                #3、查看该议题有多少人投票的
                $total_people = Db::name('decision_group_member')->where(['group_id' => $v['group_id'], 'status' => 1])->count();

                #4、判断议题是否已全员投票,0未全员，1已全员
                $topics_list[$k]['is_quanyuan'] = 0;
                $selected_num = Db::name('decision_topics_selected')->where(['topics_id' => $v['id']])->count();
                if ($selected_num == $total_people) {
                    #已全员
                    $topics_list[$k]['is_quanyuan'] = 1;
                }

                #5、判断议题是否“已结束”或“进行中”
                $topics_list[$k]['is_ending'] = 0;
                if (time() > $v['endtime']) {
                    #已结束
                    $topics_list[$k]['is_ending'] = 1;
                }

                if ($topics_list[$k]['is_quanyuan'] == 1) {
                    #已全员投票
                    #6、判断议题是否符合要求，0不符合，1符合
                    $topics_list[$k]['is_pass'] = topics_result($v);
                }
            }
        }
        else if($is_edit==1) {
            #1、查找本人参与过的议题
            $topics_list = Db::name('decision_group_member')
                ->alias('a')
                ->join('decision_topics b', 'b.group_id=a.group_id')
                ->where(['a.user_id' => session('account.id')])
                ->field(['b.*'])
                ->order('b.id desc')
                ->select();

            $origin_key = 0;
            foreach ($topics_list as $k => $v) {
                #2、如有议题id时当前议题排第一
                if ($id > 0) {
                    if ($v['id'] == $id) {
                        $origin_key = $k;
                    }
                }

                #3、查看该议题有多少人投票的
                $total_people = Db::name('decision_group_member')->where(['group_id' => $v['group_id'], 'status' => 1])->count();

                #4、判断议题是否已全员投票,0未全员，1已全员
                $topics_list[$k]['is_quanyuan'] = 0;
                $selected_num = Db::name('decision_topics_selected')->where(['topics_id' => $v['id']])->count();
                #4.1、该议题是否已发生投票
                $topics_list[$k]['already_resolution'] = $selected_num;
                if ($selected_num == $total_people) {
                    #已全员
                    $topics_list[$k]['is_quanyuan'] = 1;
                }

                #5、判断议题是否“已结束”或“进行中”
                $topics_list[$k]['is_ending'] = 0;
                if (time() > $v['endtime']) {
                    #已结束
                    $topics_list[$k]['is_ending'] = 1;
                }

                if ($topics_list[$k]['is_quanyuan'] == 1) {
                    #已全员投票
                    #6、判断议题是否符合要求，0不符合，1符合
                    $topics_list[$k]['is_pass'] = topics_result($v);
                }

                #7、查看自己有无决议
                $is_resolution = Db::name('decision_topics_selected')->where(['uid'=>session('account.id'),'topics_id'=>$v['id']])->find();
                $topics_list[$k]['is_resolution'] = 0;
                if(!empty($is_resolution)){
                    $topics_list[$k]['is_resolution'] = 1;
                }
            }

            if ($origin_key > 0) {
                #如有议题id时当前议题排第一，第一的替换为本议题原位置
                $topics_data = $topics_list[0];
                $topics_list[0] = $topics_list[$origin_key];
                $topics_list[$origin_key] = $topics_data;
            }

            if($id>0){
                #查看有无入组
                $is_join = Db::name('decision_topics')
                    ->alias('a')
                    ->join('decision_group_member b','b.group_id=a.group_id')
                    ->where(['b.user_id'=>session('account.id'),'a.id'=>$id])
                    ->field('b.*')
                    ->find();
            }
        }

        return view('',compact('id','website','data','is_edit','topics_list','is_join'));
    }
    #我发起的
    public function send_topics_list(Request $request){
        $dat = input();

        if(empty(session('account'))){
            $url_this = '//www.gogo198.net'.$_SERVER["REQUEST_URI"];
            header('Location:/?s=index/member_login&open=4&param2='.base64_encode($url_this));exit;
        }

        #1、查找本人发起的议题
        $topics_list = Db::name('decision_topics')
            ->where(['user_id' => session('account.id')])
            ->order('id desc')
            ->select();

        foreach($topics_list as $k=>$v){
            #3、查看该议题有多少人投票的
            $total_people = Db::name('decision_group_member')->where(['group_id' => $v['group_id'], 'status' => 1])->count();

            #4、判断议题是否已全员投票,0未全员，1已全员
            $topics_list[$k]['is_quanyuan'] = 0;
            $selected_num = Db::name('decision_topics_selected')->where(['topics_id' => $v['id']])->count();
            #4.1、该议题是否已发生投票
            $topics_list[$k]['already_resolution'] = $selected_num;
            if ($selected_num == $total_people) {
                #已全员
                $topics_list[$k]['is_quanyuan'] = 1;
            }

            #5、判断议题是否“已结束”或“进行中”
            $topics_list[$k]['is_ending'] = 0;
            if (time() > $v['endtime']) {
                #已结束
                $topics_list[$k]['is_ending'] = 1;
            }

            if ($topics_list[$k]['is_quanyuan'] == 1) {
                #已全员投票
                #6、判断议题是否符合要求，0不符合，1符合
                $topics_list[$k]['is_pass'] = topics_result($v);
            }
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
        $website['color_inner'] = $this->website_color_inner;
        $website['website_contact'] = $this->website_contact;
        $website['apps'] = $this->apps;

        return view('',compact('id','website','topics_list'));
    }
    #群组列表
    public function join_group(Request $request){
        $dat = input();
//        session(null);
        $page_id = isset($dat['page_id'])?intval($dat['page_id']):0;
        $url_this = '//www.gogo198.net'.$_SERVER["REQUEST_URI"];

        if(empty(session('account'))){
            header('Location:/?s=index/customer_login&open=4&param2='.base64_encode($url_this));exit;
        }

        $group_id = isset($dat['group_id'])?base64_decode($dat['group_id']):0;

        if($request->isAjax()){
            $res = Db::name('decision_group_member')->where(['group_id'=>intval($dat['group_id']),'user_id'=>session('account.id')])->update(['status'=>intval($dat['status'])]);
            if($res){
                return json(['code'=>0,'msg'=>'操作成功']);
            }
        }else{
            $menu = $this->menu();$menu_footer = $this->menu_footer();
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
            $website['color_inner'] = $this->website_color_inner;
            $website['website_contact'] = $this->website_contact;
            $website['apps'] = $this->apps;

            #记录发送“邮箱”方式时，记录组员id
            if(!empty($group_id)){
                $is_group_member = Db::name('decision_group_member')->where(['group_id'=>$group_id,'user_id'=>session('account.id')])->find();
                if(empty($is_group_member)){
                    if(!empty(session('account.email'))){
                        $is_group_member = Db::name('decision_group_member')->where(['group_id'=>$group_id,'email'=>session('account.email')])->find();
                        if(empty($is_group_member['user_id'])){
                            Db::name('decision_group_member')->where(['id'=>$is_group_member['id']])->update(['user_id'=>session('account.id')]);
                            Db::name('platform_notice_list')->where(['email'=>session('account.email')])->update(['uid'=>session('account.id')]);
                        }
                    }
                }
            }

            #判断组员有无微信公众号openid，无就跳转到补充“基本信息页”
            $member = Db::name('website_user')->where(['id'=>session('account.id')])->find();
            if(empty($member['openid']) || empty($member['email'])){
                header('Location: /?s=member/save_basic&param2='.base64_encode($url_this));
            }


            #1、查找当前用户的所有群组
            $group = Db::name('decision_group_member')
                ->alias('a')
                ->join('decision_group b','b.id=a.group_id')
                ->join('website_user c','c.id=b.user_id')
                ->where(['a.user_id'=>session('account.id')])
                ->field(['b.*','a.status','c.nickname as grouper_name'])
                ->select();

            $origin_key=0;
            foreach($group as $k=>$v){
                #2、如有群组id时当前群组排第一
                if ($group_id > 0) {
                    if ($v['id'] == $group_id) {
                        $origin_key = $k;
                    }
                }

                #3、查看该群组有无议题
                $group[$k]['topics_id'] = Db::name('decision_topics')->where(['group_id'=>$v['id']])->field('id')->find()['id'];
            }
            if ($origin_key > 0) {
                #如有议题id时当前议题排第一，第一的替换为本议题原位置
                $group_data = $group[0];
                $group[0] = $group[$origin_key];
                $group[$origin_key] = $group_data;
            }

            return view('',compact('website','group','menu','menu_footer','page_id','is_group_member'));
        }
    }
    #获取名称
    public function get_name(Request $request){
        $dat = input();
        $val = trim($dat['val']);
        $type = intval($dat['type']);

        $list = [];
        if($val != ''){
            if($type==1){
                #通用名称
                $list1 = Db::connect($this->medical_config)->name('goods')->whereRaw('name like "%'.$val.'%"')->field('name,en_name,py_name')->select();
                $list2 = Db::connect($this->medical_config)->name('goods_temp')->whereRaw('name like "%'.$val.'%"')->field('name,en_name')->select();
                $array = array_merge($list1,$list2);
                foreach ($array as $item) {
                    if (!in_array($item, $list)) {
                        $list[] = $item;
                    }
                }
            }elseif($type==2){
                #英文名称
                $list1 = Db::connect($this->medical_config)->name('goods')->whereRaw('en_name like "%'.$val.'%"')->field('name,en_name')->select();
                $list2 = Db::connect($this->medical_config)->name('goods_temp')->whereRaw('en_name like "%'.$val.'%"')->field('name,en_name')->select();
                $array = array_merge($list1,$list2);
                foreach ($array as $item) {
                    if (!in_array($item, $list)) {
                        $list[] = $item;
                    }
                }
            }
            elseif($type==3){
                #通过“手机”或“邮箱号”搜索组员
                $list = Db::name('website_user')->whereRaw('phone like "%'.$val.'%" or email like "%'.$val.'%"')->select();
                foreach($list as $k=>$v){
                    $name = '';
                    if(!empty($v['realname'])){
                        $name = $v['realname'];
                    }elseif(!empty($v['nickname'])){
                        $name = $v['nickname'];
                    }

                    if(!empty($v['phone'])){
                        $name .= '-'.$v['phone'];
                    }elseif(!empty($v['email'])){
                        $name .= '-'.$v['email'];
                    }
                    $list[$k]['name'] = $name;
                }
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
    #我的基本信息页
    public function save_basic(Request $request){
        $dat = input();
        $param2 = isset($dat['param2'])?base64_decode($dat['param2']):'';
        if($request->isAjax()){
            $info = ['realname'=>trim($dat['realname']),'nickname'=>trim($dat['nickname'])];

            if(isset($dat['area_code'])){
                $info = array_merge($info,['area_code'=>$dat['area_code']]);
            }
            if(isset($dat['phone'])){
                $info = array_merge($info,['phone'=>trim($dat['phone'])]);
            }
            if(isset($dat['email'])){
                $info = array_merge($info,['email'=>trim($dat['email'])]);
                #对碰群组是否已有该邮箱
                Db::name('decision_group_member')->where(['user_id'=>0,'email'=>trim($dat['email'])])->update(['user_id'=>session('account.id')]);
            }

            Db::name('website_user')->where(['id'=>session('account.id')])->update($info);
            return json(['code'=>0,'msg'=>'提交成功']);
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
            $website['color_inner'] = $this->website_color_inner;
            $website['website_contact'] = $this->website_contact;
            $website['apps'] = $this->apps;

            $info = Db::name('website_user')->where(['id'=>session('account.id')])->find();
            return view('',compact('website','info','param2'));
        }
    }
    #查看用户有无关注公众号
    public function check_follow(Request $request){
        $dat = input();

        $user = Db::name('website_user')->where(['id'=>session('account.id')])->find();
        if(!empty($user['openid'])){
            session('account',$user);
            return json(['code'=>0,'msg'=>'已关注公号，正在跳转...']);
        }else{
            return json(['code'=>-1,'msg'=>'未关注公号，等待操作...']);
        }
    }
    #聊天列表
    public function chat_list(Request $request){
        $dat = input();
        if(isset($dat['pid2'])){
            $dat['pid'] = $dat['pid2'];
        }
        $list = Db::name('centralize_manage_menu')->where(['pid'=>intval($dat['pid']),'status'=>0])->select();
        foreach($list as $k=>$v){
            if($v['id']==237){
                #订单
                $list[$k]['list'] = Db::name('website_order_list')->where(['user_id'=>session('account.id')])->order('id desc')->select();
            }
            elseif($v['id']==238){
                #商品
                $shopping_user = Db::connect($this->config)->name('user')->where(['gogo_id'=>session('account.custom_id')])->find();
                $list[$k]['list'] = Db::connect($this->config)->name('goods_history')
                    ->alias('a')
                    ->join('goods b','b.goods_id=a.goods_id')
                    ->where(['a.user_id'=>$shopping_user['user_id']])
                    ->order('a.history_id desc')
                    ->field(['b.goods_id','b.goods_name'])
                    ->select();
            }
            elseif($v['id']==239){
                #决策
                $all_group = Db::name('decision_group_member')->where(['user_id'=>session('account.id'),'status'=>1])->field('group_id')->select();
//                $group_id = '';
                $group_id_arr = [];
                foreach($all_group as $k2=>$v2){
//                    $group_id .= $v2['group_id'].',';
                    array_push($group_id_arr,$v2['group_id']);
                }
//                $group_id = rtrim($group_id,',');
                $list[$k]['list'] = DB::name('decision_topics')->whereIn('group_id',$group_id_arr)
                    ->order('id desc')
                    ->select();
            }
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
        $website['color_inner'] = $this->website_color_inner;
        $website['website_contact'] = $this->website_contact;
        $website['apps'] = $this->apps;

        return view('',compact('website','list'));
    }
    #分享议题
    public function share_topics(Request $request){
        $dat = input();
        $id = $dat['id'];
        $folder = $_SERVER['DOCUMENT_ROOT'].'/qrcode/topics_code/';
        $img = generate_code('topics_'.$id,'https://www.gogo198.net/?s=member/member_center&tz_url='.base64_encode('https://www.gogo198.net/?s=member/topics_detail&id='.$id.'&is_edit='.base64_encode(0)).'&footer=2',$folder);
        return json(['code'=>0,'img'=>$img]);
    }

    #建议咨询
    public function advice_list(Request $request){
        $dat = input();

        $list = Db::name('website_message')->where(['uid'=>session('account.id')])->order('id desc')->select();

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
        $website['color_inner'] = $this->website_color_inner;
        $website['website_contact'] = $this->website_contact;
        $website['apps'] = $this->apps;

        return view('',compact('website','list'));
    }

    public function save_advice(Request $request){
        $dat = input();
        $id = isset($dat['id'])?intval($dat['id']):0;

        if(isset($dat['pa'])){
            $res = Db::name('website_message')->insert([
                'uid'=>session('account.id'),
                'name'=>session('account.nickname'),
                'tel'=>session('account.phone'),
                'email'=>session('account.email'),
                'remark'=>trim($dat['remark']),
                'createtime'=>time()
            ]);

            if($res){
                return json(['code'=>0,'msg'=>'提交成功']);
            }
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
            $website['color_inner'] = $this->website_color_inner;
            $website['website_contact'] = $this->website_contact;
            $website['apps'] = $this->apps;

            $data = ['remark'=>''];
            if($id>0){
                $data = Db::name('website_message')->where(['id'=>$id])->find();
            }

            return view('',compact('website','data','id'));
        }
    }

    #社媒账户
    public function social_list(Request $request){
        $dat = input();

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
        $website['color_inner'] = $this->website_color_inner;
        $website['website_contact'] = $this->website_contact;
        $website['apps'] = $this->apps;

        return view('',compact('website','info','param2'));
    }
    #决策管理=================================================================END
}