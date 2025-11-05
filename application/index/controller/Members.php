<?php
namespace app\index\controller;

use think\Request;
use think\Db;
use think\Controller;
use app\index\model\Parceltask;
use think\Log;

class Members
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
        'type'     => 'mysql',
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
        'prefix'   => '',
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
        $this->website_contact = Db::name('website_contact')->where(['system_id' => 1])->select();

        #日志记录
//        platform_log($request);
    }

    #会员中心
    public function member_center(Request $request){
        $dat = input();
        $mid = isset($dat['mid'])?intval($dat['mid']):0;
        $key = isset($dat['key'])?trim($dat['key']):'';
        if($mid>0){
            $user = Db::name('website_user')->where(['id'=>$mid])->find();
            session('account',$user);
        }
        if(empty(session('account'))){
            header('Location:/?s=index/customer_login');exit;
        }

        $menu = Db::name('website_member_menu')->where(['pid'=>0,'status'=>0,'auth_type'=>0])->select();
        foreach($menu as $k=>$v){
            $menu[$k]['children'] = Db::name('website_member_menu')->where(['pid'=>$v['id']])->select();
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

        $account = Db::name('website_user')->where(['id'=>session('account.id')])->find();

        if(count(explode('_',$account['nickname'])) > 1){
            $account['nickname'] = explode('_',$account['nickname'])[1];
        }
        return view('',compact('website','menu','account'));
    }

    public function system_manage(Request $request){
        $dat = input();
        $pid = isset($dat['pid'])?intval($dat['pid']):0;

        $menu_list = Db::name('website_member_menu')->where(['pid'=>$pid])->select();

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

        return view('',compact('website','menu_list'));
    }

    #系统管理-二级
    public function system_manage2(Request $request){
        $dat = input();
        $pid = isset($dat['pid'])?intval($dat['pid']):0;

        $menu_list = Db::name('website_member_menu')->where(['pid'=>$pid])->select();

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

        return view('',compact('website','menu_list'));
    }

    #账户信息
    public function person_basic(Request $request){
        $dat = input();
        if(isset($dat['pa'])){

            $res = Db::name('website_user')->where(['id'=>session('account.id')])->update(['nickname'=>trim($dat['nickname'])]);
            if($res){
                return json(['code'=>0,'msg'=>'保存成功']);
            }
        }else{
            #栏目
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
            $website['website_contact'] = $this->website_contact;

            $account = Db::name('website_user')->where(['id'=>session('account.id')])->find();

            return view('', compact(  'website','account'));
        }
    }

    #关联账户列表
    public function connect_account_list(Request $request){
        $dat = input();

        if(isset($dat['pa'])){
            $app_id = intval($dat['app_id']);
            $bind = intval($dat['bind']);
            if($app_id==1){
                #微信
                if($bind==0){
                    #绑定
                    $account = Db::name('website_user')->where(['id'=>session('account.id')])->find();
                    if(!empty($account['unionid'])){
                        $mc_fans = Db::name('mc_mapping_fans')->where(['unionid'=>session('account.unionid')])->find();
                        Db::name('website_user')->where(['id'=>session('account.id')])->update(['openid'=>$mc_fans['openid']]);
                        return json(['code'=>0,'msg'=>'绑定成功']);
                    }
                }elseif($bind==1){
                    #解绑
                    $res = Db::name('website_user')->where(['id'=>session('account.id')])->update(['openid'=>'']);
                    return json(['code'=>0,'msg'=>'解绑成功']);
                }
            }
            elseif($app_id==2){
                #微信小程序
                if($bind==0){
                    #绑定
                    $account = Db::name('website_user')->where(['id'=>session('account.id')])->find();
                    if(!empty($account['unionid'])){
                        if(!empty($account['sns_openid'])){
                            $mc_fans = Db::name('mc_mapping_fans')->where(['unionid'=>session('account.unionid')])->find();
                            Db::name('website_user')->where(['id'=>session('account.id')])->update(['openid'=>$mc_fans['openid']]);
                            return json(['code'=>0,'msg'=>'绑定成功']);
                        }
                    }
                }elseif($bind==1){
                    #解绑
                    $res = Db::name('website_user')->where(['id'=>session('account.id')])->update(['sns_openid'=>'']);
                    return json(['code'=>0,'msg'=>'解绑成功']);
                }
            }
        }
        else{
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

            $list = Db::name('website_authlogin_apps')->select();
            $account = Db::name('website_user')->where(['id'=>session('account.id')])->find();
            foreach($list as $k=>$v){
                if($v['id']==1){
                    #微信
                    $list[$k]['is_bind']=0;
                    if(empty($account['openid'])){
                        #解绑
                        $list[$k]['bind1_colorClass'] = 'r_grey';
                        $list[$k]['bind2_colorClass'] = 'r_blue';
                    }else{
                        #绑定
                        $list[$k]['is_bind']=1;
                        $list[$k]['bind2_colorClass'] = 'r_grey';
                        $list[$k]['bind1_colorClass'] = 'r_blue';
                    }
                }elseif($v['id']==2){
                    #微信小程序
                    $list[$k]['is_bind']=0;
                    if(empty($account['sns_openid'])){
                        #解绑
                        $list[$k]['bind1_colorClass'] = 'r_grey';
                        $list[$k]['bind2_colorClass'] = 'r_blue';
                    }else{
                        #绑定
                        $list[$k]['is_bind']=1;
                        $list[$k]['bind2_colorClass'] = 'r_grey';
                        $list[$k]['bind1_colorClass'] = 'r_blue';
                    }
                }
            }

            return view('', compact('website','list','account'));
        }
    }

    #关联企业列表
    public function connect_enterprise_list(Request $request){
        $dat = input();
        $pid = isset($dat['pid'])?intval($dat['pid']):0;

        if(isset($dat['pa'])){
            $id = intval($dat['id']);
            $res = Db::name('website_user_company')->where(['id'=>$id])->update(['user_id'=>0]);
            if($res){
                return json(['code'=>0,'msg'=>'解绑成功']);
            }
        }
        else{
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

            $list = Db::name('website_user_company')->where(['user_id'=>session('account.id')])->select();
            $account = Db::name('website_user')->where(['id'=>session('account.id')])->find();

            return view('', compact('website','list','account','pid'));
        }
    }

    #国内认证
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
            $website['website_contact'] = $this->website_contact;

            #个人信息
            $account = Db::name('website_user')->where('id',session('account')['id'])->find();

            return view('',compact('website','account'));
        }
    }

    #关联企业
    public function connect_enterprise(Request $request){
        $dat = input();

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

                $company = trim($dat['company']);
                $realname = trim($dat['realname']);
                $idcard = trim($dat['idcard']);
                $mobile = trim($dat['phone']);
                $type = trim($dat['type']);
                $type2 = trim($dat['type2']);

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

            $account = Db::name('website_user')->where('id',session('account')['id'])->find();
            if($account['is_verify']==0){
                #未认证时跳转到认证信息页
                header('Location: /?s=members/auth_info');
            }
            session('account',$account);
            return view('',compact('website','account'));
        }
    }

    #联系信息
    public function contact_info(Request $request){
        $dat = input();

        if($request->isAjax()){
            $phone_code = trim($dat['phone_code']);
            $email_code = trim($dat['email_code']);
            $upd_phone = trim($dat['upd_phone']);
            $upd_email = trim($dat['upd_email']);
            if(empty($dat['phone']) || empty($dat['email'])){
                return json(['code'=>-1,'msg'=>'请输入信息']);
            }
            if($upd_phone==1){
                if($phone_code!=session('phone_verify_code')){
                    return json(['code'=>-1,'msg'=>'手机验证码错误']);
                }
            }
            if($upd_email==1){
                if($email_code!=session('email_verify_code')){
                    return json(['code'=>-1,'msg'=>'邮箱验证码错误']);
                }
            }

            #对碰群组是否已有该邮箱
            Db::name('decision_group_member')->where(['user_id'=>0,'email'=>trim($dat['email'])])->update(['user_id'=>session('account.id')]);

            $res = Db::name('website_user')->where('id',session('account.id'))->update([
                'phone'=>trim($dat['phone']),
                'email'=>trim($dat['email']),
            ]);

            if($res){
                $account = Db::name('website_user')->where('id',session('account.id'))->find();
                session('account',$account);
                return json(['code'=>0,'msg'=>'保存成功！']);
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

            $account = Db::name('website_user')->where('id',session('account')['id'])->find();
            #国家地区号码
            $country_code = Db::name('centralize_diycountry_content')->where(['pid'=>5])->select();

            return view('',compact('website','account','country_code'));
        }
    }

    #收货信息
    public function receive_list(Request $request){
        $dat = input();

        if($request->isAjax()){

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

            $list = Db::name('centralize_user_address')->where(['type'=>0,'user_id'=>session('account.id')])->select();

            return view('',compact('website','list'));
        }
    }

    public function save_receive(Request $request){
        $dat = input();
        $id = isset($dat['id'])?intval($dat['id']):0;

        if($request->isAjax()){
            if (!empty(session('account'))) {
                $name = trim($dat['user_name1']) . ' ' . trim($dat['user_name2']) . ' ' . trim($dat['user_name3']);

                $address2 = [];
                if(isset($dat['address2'])){
                    foreach($dat['address2'] as $k=>$v){
                        array_push($address2, $v);
                    }
                }

                $post = [];
                if(isset($dat['postal_code'])){
                    foreach($dat['postal_code'] as $k=>$v){
                        array_push($post, $v);
                    }
                }

                $province = '0';
                if (isset($data['province'])) {
                    $province = $dat['province'];
                }

                $city = '0';
                if (isset($dat['city'])) {
                    $city = $dat['city'];
                }

                $area = '0';
                if (isset($dat['area'])) {
                    $area = $dat['area'];
                }
                $area2 = '0';
                if (isset($dat['area2'])) {
                    $area2 = $dat['area2'];
                }
                $area3 = '0';
                if (isset($dat['area3'])) {
                    $area3 = $dat['area3'];
                }
                $area4 = '0';
                if (isset($dat['area4'])) {
                    $area4 = $dat['area4'];
                }

                if ($province == '自定义') {
                    $total_area = [];
                    if(isset($dat['diycountry'])){
                        foreach($dat['diycountry'] as $k=>$v){
                            if(!empty($v)) {
                                array_push($total_area, $v);
                            }
                        }
                    }

                    if (!empty($total_area)) {
                        $pid = 0;
                        foreach ($total_area as $k => $v) {
                            $pid = Db::name('centralize_adminstrative_area')->insertGetId([
                                'country_id' => $dat['country'],
                                'pid' => $pid,
                                'code_name' => trim($v)
                            ]);
                            if ($k == 0) {
                                $province = $pid;
                            } elseif ($k == 1) {
                                $city = $pid;
                            } elseif ($k == 2) {
                                $area = $pid;
                            } elseif ($k == 3) {
                                $area2 = $pid;
                            } elseif ($k == 4) {
                                $area3 = $pid;
                            } elseif ($k == 5) {
                                $area4 = $pid;
                            }
                        }
                    }
                }
                else {
                    if ($city == '自定义') {
                        $total_area = [];
                        if(isset($dat['diycountry'])){
                            foreach($dat['diycountry'] as $k=>$v){
                                if(!empty($v)){
                                    array_push($total_area, $v);
                                }
                            }
                        }
                        if (!empty($total_area)) {
                            $pid = $province;
                            foreach ($total_area as $k => $v) {
                                $pid = Db::name('centralize_adminstrative_area')->insertGetId([
                                    'country_id' => $dat['country'],
                                    'pid' => $pid,
                                    'code_name' => trim($v)
                                ]);
                                if ($k == 0) {
                                    $city = $pid;
                                } elseif ($k == 1) {
                                    $area = $pid;
                                } elseif ($k == 2) {
                                    $area2 = $pid;
                                } elseif ($k == 3) {
                                    $area3 = $pid;
                                } elseif ($k == 4) {
                                    $area4 = $pid;
                                }
                            }
                        }
                    }
                    else {
                        if ($area == '自定义') {
                            $total_area = [];
                            if(isset($dat['diycountry'])){
                                foreach($dat['diycountry'] as $k=>$v){
                                    if(!empty($v)) {
                                        array_push($total_area, $v);
                                    }
                                }
                            }

                            if (!empty($total_area)) {
                                $pid = $city;
                                foreach ($total_area as $k => $v) {
                                    $pid = Db::name('centralize_adminstrative_area')->insertGetId([
                                        'country_id' => $dat['country'],
                                        'pid' => $pid,
                                        'code_name' => trim($v)
                                    ]);
                                    if ($k == 0) {
                                        $area = $pid;
                                    } elseif ($k == 1) {
                                        $area2 = $pid;
                                    } elseif ($k == 2) {
                                        $area3 = $pid;
                                    } elseif ($k == 3) {
                                        $area4 = $pid;
                                    }
                                }
                            }
                        }
                        else {
                            if ($area2 == '自定义') {
                                $total_area = [];
                                if(isset($dat['diycountry'])){
                                    foreach($dat['diycountry'] as $k=>$v){
                                        if(!empty($v)) {
                                            array_push($total_area, $v);
                                        }
                                    }
                                }
                                if (!empty($total_area)) {
                                    $pid = $area;
                                    foreach ($total_area as $k => $v) {
                                        $pid = Db::name('centralize_adminstrative_area')->insertGetId([
                                            'country_id' => $dat['country'],
                                            'pid' => $pid,
                                            'code_name' => trim($v)
                                        ]);
                                        if ($k == 0) {
                                            $area2 = $pid;
                                        } elseif ($k == 2) {
                                            $area3 = $pid;
                                        } elseif ($k == 3) {
                                            $area4 = $pid;
                                        }
                                    }
                                }
                            }
                            else {
                                if ($area3 == '自定义') {
                                    $total_area = [];
                                    if(isset($dat['diycountry'])){
                                        foreach($dat['diycountry'] as $k=>$v){
                                            if(!empty($v)) {
                                                array_push($total_area, $v);
                                            }
                                        }
                                    }

                                    if (!empty($total_area)) {
                                        $pid = $area;
                                        foreach ($total_area as $k => $v) {
                                            $pid = Db::name('centralize_adminstrative_area')->insertGetId([
                                                'country_id' => $dat['country'],
                                                'pid' => $pid,
                                                'code_name' => trim($v)
                                            ]);
                                            if ($k == 0) {
                                                $area3 = $pid;
                                            } elseif ($k == 1) {
                                                $area4 = $pid;
                                            }
                                        }
                                    }
                                }
                                else {
                                    if ($area4 == '自定义') {
                                        $total_area = [];
                                        if(isset($dat['diycountry'])){
                                            foreach($dat['diycountry'] as $k=>$v){
                                                if(!empty($v)) {
                                                    array_push($total_area, $v);
                                                }
                                            }
                                        }

                                        if (!empty($total_area)) {
                                            $pid = $area;
                                            foreach ($total_area as $k => $v) {
                                                $pid = Db::name('centralize_adminstrative_area')->insertGetId([
                                                    'country_id' => $dat['country'],
                                                    'pid' => $pid,
                                                    'code_name' => trim($v)
                                                ]);
                                                if ($k == 0) {
                                                    $area4 = $pid;
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                if ($dat['is_default'] == 1) {
                    Db::name('centralize_user_address')->where(['user_id'=> session('account.id'),'is_default'=>1])->update(['is_default' => 0]);
                }

                if($id>0){
                    $res = Db::name('centralize_user_address')->where(['user_id'=>session('account.id'),'id'=>$id])->update([
                        'user_name' => $name,
                        'mobile' => trim($dat['mobile']),
                        'mobile2' => trim($dat['mobile2']),
                        'email' => $dat['email'],
                        'address1' => $dat['address1'],
                        'address2' => json_encode($address2, true),
                        'is_default' => $dat['is_default'],
                    ]);
                }else{
                    $res = Db::name('centralize_user_address')->insert([
                        'user_id' => session('account.id'),
                        'country_id' => $dat['country'],
                        'province' => $province,
                        'city' => $city,
                        'area' => $area,
                        'area2' => $area2,
                        'area3' => $area3,
                        'area4' => $area4,
                        'user_name' => $name,
                        'area_mobile' => trim($dat['area_mobile']),
                        'mobile' => trim($dat['mobile']),
                        'mobile2' => trim($dat['mobile2']),
                        'email' => $dat['email'],
                        'postal_code' => json_encode($post, true),
                        'address1' => $dat['address1'],
                        'createtime' => time(),
                        'address2' => json_encode($address2, true),
                        'is_default' => $dat['is_default'],
                    ]);
                }


                if ($res) {
                    return json(['code' => 0, 'msg' => '保存成功']);
                }
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

            $country = Db::name('centralize_diycountry_content')->where(['pid'=>5])->select();

            $address = ['country_id'=>'','province'=>'','city'=>'','area'=>'','area2'=>'','area3'=>'','area4'=>'','area_mobile'=>'','mobile'=>'','mobile2'=>'','address1'=>'','address2'=>[],'email'=>'','postal_code'=>'','user_name'=>['','',''],'is_default'=>0];
            if($id>0) {
                $address = Db::name('centralize_user_address')->where(['id' => $id, 'user_id' => session('account.id')])->find();

                #收货国地--start
                $address['detail_area'] = Db::name('centralize_diycountry_content')->where(['id'=>$address['country_id']])->find()['param2'];
                $province = Db::name('centralize_adminstrative_area')->where(['id'=>$address['province']])->find()['code_name'];
                $address['detail_area'] .= ' '.$province;
                if(!empty($address['city'])){
                    $city = Db::name('centralize_adminstrative_area')->where(['id'=>$address['city']])->find()['code_name'];
                    $address['detail_area'] .= ' '.$city;
                }
                if(!empty($address['area'])){
                    $area = Db::name('centralize_adminstrative_area')->where(['id'=>$address['area']])->find()['code_name'];
                    $address['detail_area'] .= ' '.$area;
                }
                if(!empty($address['area2'])){
                    $area2 = Db::name('centralize_adminstrative_area')->where(['id'=>$address['area2']])->find()['code_name'];
                    $address['detail_area'] .= ' '.$area2;
                }
                if(!empty($address['area3'])){
                    $area3 = Db::name('centralize_adminstrative_area')->where(['id'=>$address['area3']])->find()['code_name'];
                    $address['detail_area'] .= ' '.$area3;
                }
                if(!empty($v['area4'])){
                    $area4 = Db::name('centralize_adminstrative_area')->where(['id'=>$address['area4']])->find()['code_name'];
                    $address['detail_area'] .= ' '.$area4;
                }
                #收货国地--end

                #收货人姓名
                $address['user_name'] = explode(' ',$address['user_name']);
                if(count($address['user_name'])==2){
                    $uname2 = $address['user_name'][1];
                    $address['user_name'][1] = '';
                    $address['user_name'][2] = $uname2;
                }

                #邮政编码
                $address['postal_code'] = implode("",json_decode($address['postal_code'],true));

                #更多收货地址
                if(!empty($address['address2'])){
                    $address['address2'] = json_decode($address['address2'],true);
                }
            }

            return view('',compact('website','address','id','country'));
        }
    }

    public function del_receive(Request $request)
    {
        $dat = input();
        $id = isset($dat['id']) ? intval($dat['id']) : 0;

        $res = Db::name('centralize_user_address')->where(['id'=>$id,'user_id'=>session('account.id')])->delete();
        if($res){
            return json(['code'=>0,'msg'=>'删除成功']);
        }
    }

    public function getphonenum(Request $request){
        $dat = input();
        if($dat['pa']==1){
            // $area = Db::name('centralize_adminstrative_area')->where(['id'=>$data['id']])->find();
            $phone = Db::name('centralize_diycountry_content')->where(['id'=>$dat['id']])->find();
            $post = Db::name('centralize_diycountry_content')->where(['pid'=>4,'param1'=>$phone['param5']])->find();
            $post_temp = '';
            if(!empty($post)){
                $post_temp = $post['param3'];
            }
            $province = Db::name('centralize_adminstrative_area')->where(['country_id'=>$dat['id'],'pid'=>0])->select();
            return json(['code'=>0,'phone'=>$phone['param8'],'post'=>$post_temp,'province'=>$province]);
        }
        elseif($dat['pa']==2){
            $city = Db::name('centralize_adminstrative_area')->where(['pid'=>$dat['id']])->select();
            return json(['code'=>0,'area'=>$city]);
        }
    }

    #发货信息
    public function send_list(Request $request){
        $dat = input();

        if($request->isAjax()){

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

            $list = Db::name('centralize_user_address')->where(['type'=>1,'user_id'=>session('account.id')])->select();

            return view('',compact('website','list'));
        }
    }

    public function save_send(Request $request){
        $dat = input();
        $id = isset($dat['id'])?intval($dat['id']):0;

        if($request->isAjax()){
            if (!empty(session('account'))) {
                $name = trim($dat['user_name1']) . ' ' . trim($dat['user_name2']) . ' ' . trim($dat['user_name3']);

                $address2 = [];
                if(isset($dat['address2'])){
                    foreach($dat['address2'] as $k=>$v){
                        array_push($address2, $v);
                    }
                }

                $post = [];
                if(isset($dat['postal_code'])){
                    foreach($dat['postal_code'] as $k=>$v){
                        array_push($post, $v);
                    }
                }

                $province = '0';
                if (isset($data['province'])) {
                    $province = $dat['province'];
                }

                $city = '0';
                if (isset($dat['city'])) {
                    $city = $dat['city'];
                }

                $area = '0';
                if (isset($dat['area'])) {
                    $area = $dat['area'];
                }
                $area2 = '0';
                if (isset($dat['area2'])) {
                    $area2 = $dat['area2'];
                }
                $area3 = '0';
                if (isset($dat['area3'])) {
                    $area3 = $dat['area3'];
                }
                $area4 = '0';
                if (isset($dat['area4'])) {
                    $area4 = $dat['area4'];
                }

                if ($province == '自定义') {
                    $total_area = [];
                    if(isset($dat['diycountry'])){
                        foreach($dat['diycountry'] as $k=>$v){
                            if(!empty($v)) {
                                array_push($total_area, $v);
                            }
                        }
                    }

                    if (!empty($total_area)) {
                        $pid = 0;
                        foreach ($total_area as $k => $v) {
                            $pid = Db::name('centralize_adminstrative_area')->insertGetId([
                                'country_id' => $dat['country'],
                                'pid' => $pid,
                                'code_name' => trim($v)
                            ]);
                            if ($k == 0) {
                                $province = $pid;
                            } elseif ($k == 1) {
                                $city = $pid;
                            } elseif ($k == 2) {
                                $area = $pid;
                            } elseif ($k == 3) {
                                $area2 = $pid;
                            } elseif ($k == 4) {
                                $area3 = $pid;
                            } elseif ($k == 5) {
                                $area4 = $pid;
                            }
                        }
                    }
                }
                else {
                    if ($city == '自定义') {
                        $total_area = [];
                        if(isset($dat['diycountry'])){
                            foreach($dat['diycountry'] as $k=>$v){
                                if(!empty($v)){
                                    array_push($total_area, $v);
                                }
                            }
                        }
                        if (!empty($total_area)) {
                            $pid = $province;
                            foreach ($total_area as $k => $v) {
                                $pid = Db::name('centralize_adminstrative_area')->insertGetId([
                                    'country_id' => $dat['country'],
                                    'pid' => $pid,
                                    'code_name' => trim($v)
                                ]);
                                if ($k == 0) {
                                    $city = $pid;
                                } elseif ($k == 1) {
                                    $area = $pid;
                                } elseif ($k == 2) {
                                    $area2 = $pid;
                                } elseif ($k == 3) {
                                    $area3 = $pid;
                                } elseif ($k == 4) {
                                    $area4 = $pid;
                                }
                            }
                        }
                    }
                    else {
                        if ($area == '自定义') {
                            $total_area = [];
                            if(isset($dat['diycountry'])){
                                foreach($dat['diycountry'] as $k=>$v){
                                    if(!empty($v)) {
                                        array_push($total_area, $v);
                                    }
                                }
                            }

                            if (!empty($total_area)) {
                                $pid = $city;
                                foreach ($total_area as $k => $v) {
                                    $pid = Db::name('centralize_adminstrative_area')->insertGetId([
                                        'country_id' => $dat['country'],
                                        'pid' => $pid,
                                        'code_name' => trim($v)
                                    ]);
                                    if ($k == 0) {
                                        $area = $pid;
                                    } elseif ($k == 1) {
                                        $area2 = $pid;
                                    } elseif ($k == 2) {
                                        $area3 = $pid;
                                    } elseif ($k == 3) {
                                        $area4 = $pid;
                                    }
                                }
                            }
                        }
                        else {
                            if ($area2 == '自定义') {
                                $total_area = [];
                                if(isset($dat['diycountry'])){
                                    foreach($dat['diycountry'] as $k=>$v){
                                        if(!empty($v)) {
                                            array_push($total_area, $v);
                                        }
                                    }
                                }
                                if (!empty($total_area)) {
                                    $pid = $area;
                                    foreach ($total_area as $k => $v) {
                                        $pid = Db::name('centralize_adminstrative_area')->insertGetId([
                                            'country_id' => $dat['country'],
                                            'pid' => $pid,
                                            'code_name' => trim($v)
                                        ]);
                                        if ($k == 0) {
                                            $area2 = $pid;
                                        } elseif ($k == 2) {
                                            $area3 = $pid;
                                        } elseif ($k == 3) {
                                            $area4 = $pid;
                                        }
                                    }
                                }
                            }
                            else {
                                if ($area3 == '自定义') {
                                    $total_area = [];
                                    if(isset($dat['diycountry'])){
                                        foreach($dat['diycountry'] as $k=>$v){
                                            if(!empty($v)) {
                                                array_push($total_area, $v);
                                            }
                                        }
                                    }

                                    if (!empty($total_area)) {
                                        $pid = $area;
                                        foreach ($total_area as $k => $v) {
                                            $pid = Db::name('centralize_adminstrative_area')->insertGetId([
                                                'country_id' => $dat['country'],
                                                'pid' => $pid,
                                                'code_name' => trim($v)
                                            ]);
                                            if ($k == 0) {
                                                $area3 = $pid;
                                            } elseif ($k == 1) {
                                                $area4 = $pid;
                                            }
                                        }
                                    }
                                }
                                else {
                                    if ($area4 == '自定义') {
                                        $total_area = [];
                                        if(isset($dat['diycountry'])){
                                            foreach($dat['diycountry'] as $k=>$v){
                                                if(!empty($v)) {
                                                    array_push($total_area, $v);
                                                }
                                            }
                                        }

                                        if (!empty($total_area)) {
                                            $pid = $area;
                                            foreach ($total_area as $k => $v) {
                                                $pid = Db::name('centralize_adminstrative_area')->insertGetId([
                                                    'country_id' => $dat['country'],
                                                    'pid' => $pid,
                                                    'code_name' => trim($v)
                                                ]);
                                                if ($k == 0) {
                                                    $area4 = $pid;
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                if ($dat['is_default'] == 1) {
                    Db::name('centralize_user_address')->where(['user_id'=> session('account.id'),'is_default'=>1])->update(['is_default' => 0]);
                }

                if($id>0){
                    $res = Db::name('centralize_user_address')->where(['user_id'=>session('account.id'),'id'=>$id])->update([
                        'user_name' => $name,
                        'mobile' => trim($dat['mobile']),
                        'mobile2' => trim($dat['mobile2']),
                        'email' => $dat['email'],
                        'address1' => $dat['address1'],
                        'address2' => json_encode($address2, true),
                        'is_default' => $dat['is_default'],
                    ]);
                }else{
                    $res = Db::name('centralize_user_address')->insert([
                        'type'=>1,
                        'user_id' => session('account.id'),
                        'country_id' => $dat['country'],
                        'province' => $province,
                        'city' => $city,
                        'area' => $area,
                        'area2' => $area2,
                        'area3' => $area3,
                        'area4' => $area4,
                        'user_name' => $name,
                        'area_mobile' => trim($dat['area_mobile']),
                        'mobile' => trim($dat['mobile']),
                        'mobile2' => trim($dat['mobile2']),
                        'email' => $dat['email'],
                        'postal_code' => json_encode($post, true),
                        'address1' => $dat['address1'],
                        'createtime' => time(),
                        'address2' => json_encode($address2, true),
                        'is_default' => $dat['is_default'],
                    ]);
                }


                if ($res) {
                    return json(['code' => 0, 'msg' => '保存成功']);
                }
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

            $country = Db::name('centralize_diycountry_content')->where(['pid'=>5])->select();

            $address = ['country_id'=>'','province'=>'','city'=>'','area'=>'','area2'=>'','area3'=>'','area4'=>'','area_mobile'=>'','mobile'=>'','mobile2'=>'','address1'=>'','address2'=>[],'email'=>'','postal_code'=>'','user_name'=>['','',''],'is_default'=>0];
            if($id>0) {
                $address = Db::name('centralize_user_address')->where(['id' => $id, 'user_id' => session('account.id')])->find();

                #收货国地--start
                $address['detail_area'] = Db::name('centralize_diycountry_content')->where(['id'=>$address['country_id']])->find()['param2'];
                $province = Db::name('centralize_adminstrative_area')->where(['id'=>$address['province']])->find()['code_name'];
                $address['detail_area'] .= ' '.$province;
                if(!empty($address['city'])){
                    $city = Db::name('centralize_adminstrative_area')->where(['id'=>$address['city']])->find()['code_name'];
                    $address['detail_area'] .= ' '.$city;
                }
                if(!empty($address['area'])){
                    $area = Db::name('centralize_adminstrative_area')->where(['id'=>$address['area']])->find()['code_name'];
                    $address['detail_area'] .= ' '.$area;
                }
                if(!empty($address['area2'])){
                    $area2 = Db::name('centralize_adminstrative_area')->where(['id'=>$address['area2']])->find()['code_name'];
                    $address['detail_area'] .= ' '.$area2;
                }
                if(!empty($address['area3'])){
                    $area3 = Db::name('centralize_adminstrative_area')->where(['id'=>$address['area3']])->find()['code_name'];
                    $address['detail_area'] .= ' '.$area3;
                }
                if(!empty($v['area4'])){
                    $area4 = Db::name('centralize_adminstrative_area')->where(['id'=>$address['area4']])->find()['code_name'];
                    $address['detail_area'] .= ' '.$area4;
                }
                #收货国地--end

                #收货人姓名
                $address['user_name'] = explode(' ',$address['user_name']);
                if(count($address['user_name'])==2){
                    $uname2 = $address['user_name'][1];
                    $address['user_name'][1] = '';
                    $address['user_name'][2] = $uname2;
                }

                #邮政编码
                $address['postal_code'] = implode("",json_decode($address['postal_code'],true));

                #更多收货地址
                if(!empty($address['address2'])){
                    $address['address2'] = json_decode($address['address2'],true);
                }
            }

            return view('',compact('website','address','id','country'));
        }
    }

    #生成该网站的二维码
    public function get_website_qrcode(Request $request){
        $dat = input();
        $folder = $_SERVER['DOCUMENT_ROOT'].'/qrcode/share_qrcode/';
        $img = generate_code('website_qrcode','https://www.gogo198.net/?s=members/member_center',$folder);
        return json(['code'=>0,'img'=>$img]);
    }

    #转移到其他页面
    public function transfer_website(Request $request){
        $dat = input();

        $url = isset($dat['url'])?trim($dat['url']):'';
        $have_child = isset($dat['have_child'])?intval($dat['have_child']):0;

        $list = [];
        if($have_child==1){
            $pid = intval($dat['pid']);

            $list = Db::name('website_member_menu')->where(['pid'=>$pid])->select();
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

        return view('',compact('website','url','have_child','list'));
    }

    #优惠卡券列表
    public function coupon_list(Request $request){
        $dat = input();
        echo '<h2>正在开发中...</h2>';exit;
    }

    #预付账单列表
    public function prepaid_list(Request $request){
        $dat = input();
        echo '<h2>正在开发中...</h2>';exit;
    }

    #我确认的（列表）
    public function sure_list(Request $request){
        $dat = input();

//        $list = [];

        #群组确认
        $list = Db::name('decision_group_member')
            ->alias('a')
            ->join('decision_group b','a.group_id=b.id')
            ->where(['a.user_id'=>session('account.id'),'a.status'=>1])
            ->field(['a.*','b.name as group_name','b.createtime'])
            ->select();

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

        return view('',compact('website','list'));
    }

    #开发中
    public function processing(){
        echo '<h2>正在开发中...</h2>';exit;
    }
}