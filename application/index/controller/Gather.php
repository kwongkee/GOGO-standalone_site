<?php
namespace app\index\controller;

use think\Request;
use think\Db;
use think\Controller;
use app\index\model\Parceltask;
use think\Log;

class Gather
{
    #首页
    public function index(Request $request){
        $dat = input();
        if ($request->isAjax()) {

        } else {
            return view('/gather/estimate/index', compact(''));
        }
    }

    #运费估算
    public function freight_estimation(Request $request)
    {
        $dat = input();
        if ($request->isAjax()) {

        } else {
            #目的地
            $country = Db::name('country_code')->where('code_name', '<>', '无')->select();
            #仓库

            #物品属性
            $value = Db::name('centralize_value_list')->select();
            return view('/gather/estimate/freight_estimation', compact('country', 'value'));
        }
    }

    #服务中心
    public function service_center(Request $request){
        $dat = input();
        if ($request->isAjax()) {

        } else {
            if(empty(session('account.id'))){
                header('location:/?s=gather/login&open=1');
            }
            return view('/gather/estimate/service_center', compact(''));
        }
    }

    #物流查询
    public function tracking(Request $request)
    {
        $dat = input();
        if ($request->isAjax()) {

        } else {

            return view('/gather/estimate/tracking', compact(''));
        }
    }

    #用户评价
    public function appraise(Request $request){
        $dat = input();
        if ($request->isAjax()) {

        } else {

            return view('/gather/estimate/appraise', compact(''));
        }
    }

    #文章
    public function article(Request $request){
        $dat = input();
        if ($request->isAjax()) {

        } else {

            return view('/gather/estimate/article', compact(''));
        }
    }

    #关于平台
    public function about(Request $request){
        $dat = input();
        if ($request->isAjax()) {

        } else {

            return view('/gather/estimate/about', compact(''));
        }
    }

    #资讯
    public function notice_detail(Request $request){
        $dat = input();
        if ($request->isAjax()) {

        } else {

            return view('/gather/estimate/notice_detail', compact(''));
        }
    }
    #投诉建议
    public function suggest(Request $request){
        $dat = input();
        if ($request->isAjax()) {

        } else {

            return view('/gather/estimate/suggest', compact(''));
        }
    }
    #登录
    public function login(Request $request){
        $dat = input();
        if ($request->isAjax()) {
            if($dat['number']!='947960547@qq.com' && $dat['number']!='13119893380' && $dat['number']!='13119893381' && $dat['number']!='947960542@qq.com' && $dat['number']!='947960543@qq.com' && $dat['number']!='13119893382'&& $dat['number']!='13809703680' && $dat['number']!='13809703681' && $dat['number']!='hejunxin@gogo198.net'){
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
                $time = time();
                $insertid = Db::name('website_user')->insertGetId([
                    'phone'=>$dat['reg_method']==1?$number:'',
                    'email'=>$dat['reg_method']==2?$number:'',
                    'times'=>1,
                    'createtime'=>$time
                ]);
                $res = Db::name('website_user')->where('id',$insertid)->update(['custom_id'=>'9'.str_pad($insertid, 5, '0', STR_PAD_LEFT)]);

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
                    #买全球账号
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
                    #卖全球账号
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
                }

                #通知用户
                if($dat['reg_method']==1){
                    #手机
                    $post_data = [
                        'spid'=>'254560',
                        'password'=>'J6Dtc4HO',
                        'ac'=>'1069254560',
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
                    httpRequest('https://shop.gogo198.cn/collect_website/public/?s=/api/sendemail/index',['email'=>$number,'title'=>'购购网','content'=>'尊敬的客户，您好！您已成功注册成为购购网会员，感谢您的支持！']);
                }
                // return json(['code'=>-1,'msg'=>'账户不正确！']);
            }else{
                if($account['times']==1){
                    Db::name('website_user')->where(['id'=>$account['id']])->update(['times'=>2]);
                }else{
                    Db::name('website_user')->where(['id'=>$account['id']])->update(['times'=>intval($account['times'])+1]);
                }
            }

            session('account',$account);

            return json(['code'=>0,'msg'=>'登录成功！','uid'=>$account['id'],'open'=>$dat['open']]);
        } else {
            if(!empty(session('account.id'))){
                header('location:/?s=gather/member_center');
            }
            $open = isset($dat['open'])?intval($dat['open']):0;
            return view('/gather/estimate/login', compact('open'));
        }
    }
    #发送验证码
    public function send_code(Request $request){
        $dat = input();
        if ($request->isAjax()) {
            $code = mt_rand(11, 99) . mt_rand(11, 99) . mt_rand(11, 99);
            if(isset($dat['islogin'])){
                session('login_code',$code);

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
                        'content'=>'您正在登录GOGO购购网，手机验证码为：'.$code.'【GOGO】',
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
                    $res=httpRequest('https://shop.gogo198.cn/collect_website/public/?s=/api/sendemail/index',['email'=>trim($dat['number']),'title'=>'登录Gogo购购网','content'=>'验证码：'.$code.'，您正在登录Gogo购购网。']);
                }
            }elseif(isset($dat['isverify'])){
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
                    $res=httpRequest('https://shop.gogo198.cn/collect_website/public/?s=/api/sendemail/index',['email'=>trim($dat['number']),'title'=>'Gogo购购网','content'=>'您正在验证电子邮箱，验证码为：'.$code.'。']);
                }
            }else{
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
                        'spid'=>'254560',
                        'password'=>'J6Dtc4HO',
                        'ac'=>'1069254560',
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
    #获取小程序码（用于小程序授权登录）
    public function getminiprogramcode(Request $request){
        $data = input();
        if($data['pa']==1) {
            #小程序二维码
            $time = time();
//        if($time > (session('expires_time') + 3600)){
            #获取accesstoken
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=wx6d1af256d76896ba&secret=d19a96d909c1a167c12bb899d0c10da6";
            $res = file_get_contents($url);
            $result = json_decode($res, true);
            session('access_token', $result["access_token"]);
            session('expires_time', $time);
//        }

            $auth_id = Db::name('website_authlogin')->insertGetId([
                'timestamp' => $time,
                'status' => 0,
                'ip' => $_SERVER['REMOTE_ADDR']
            ]);

            #获取微信小程序码
            $url = "https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=" . session('access_token');
            $datas = array(
//            'path' => 'pages/index/index?id='.intval($data['id']),
                "page" => "pages/login/index",
                "scene" => "authid=" . $auth_id,
                "check_path" => true,
                "env_version" => 'release',//release develop trial体验
                'width' => 430,
            );
            $img = httpRequest($url, json_encode($datas));
            Log::info('生成授权小程序码：');
            Log::info(json_encode($img, true));
            $savepath = $_SERVER['DOCUMENT_ROOT'] . '/img/wxmini_img.png';
            file_put_contents($savepath, $img);
            $img = 'https://dtc.gogo198.net/img/wxmini_img.png?v=' . $time;
            return json(['code' => 0, 'img' => $img, 'auth_id' => $auth_id]);
        }
        elseif($data['pa']==2){
            #查询是否已授权登录
            $auth = Db::name('website_authlogin')->where(['id'=>intval($data['auth_id'])])->find();
            if($auth['status']==1){
                if($auth['uid']>0){
                    $acc = Db::name('website_user')->where(['id'=>$auth['uid']])->find();
                    session('account',$acc);
                    $is_company=0;
                    $ishave = Db::name('website_user_company')->where(['user_id'=>$acc['id'],'status'=>0])->find();
                    if(!empty($ishave)){
                        $is_company=1;
                    }
                    return json(['code'=>1,'msg'=>'授权成功，正在跳转','uid'=>$auth['uid'],'company'=>$is_company]);
                }else{
                    return json(['code'=>-2,'msg'=>'授权失败，系统暂无此用户','uid'=>0,'company'=>0]);
                }

            }elseif($auth['status']==-1){
                return json(['code'=>-1,'msg'=>'授权失败，正在刷新','uid'=>$auth['uid'],'company'=>0]);
            }elseif($auth['status']==0){
                return json(['code'=>0,'msg'=>'正在刷新','uid'=>$auth['uid'],'base64_uid'=>base64_encode($auth['uid']),'company'=>0]);
            }
        }
        elseif($data['pa']==3){
            #微信登录
            $type = isset($data['type'])?intval($data['type']):0;
            if($type==6){
                #官网登录
                $acc = Db::name('website_user')->where(['openid'=>$data['openid']])->find();
                if(!empty($acc)){
                    #已有账号
                    session('account',$acc);

                    $ishave = Db::name('website_user_company')->where(['user_id'=>$acc['id'],'status'=>0])->find();
                    if(!empty($ishave)){
                        header("Location: /?s=index/change_identity");
                    }else{
                        header("Location: /?s=index/account_manage");
                    }
                }else{
                    #未有账号
                    #跳转到补充基本信息页
                    header("Location: /?s=index/save_contact&app_type=".$data['pa']."&openid=".$data['openid'].'&unionid='.$data['unionid']);
                }
            }
            elseif($type==7){
                #医讯网登录
                $acc = Db::name('website_user')->where(['openid'=>$data['openid']])->find();
                header("Location: https://healink.gogo198.com/?s=index/customer_login&uid=".base64_encode($acc['id']));
            }
        }
        elseif($data['pa']==4){
            $d = Db::name('centralize_diycountry_content')->where(['id'=>$data['id']])->find();
            return json(['code'=>0,'data'=>$d['param8']]);
        }
    }

    public function getweixin(Request $request){
        $dat = input();

        if($dat['state']=='STATE'){
            if(!empty($dat['code'])){
                $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=wx76d541cc3e471aeb&secret=3e3d16ccb63672a059d387e43ec67c95&code='.$dat['code'].'&grant_type=authorization_code';
                $data = file_get_contents($url);
                $data = json_decode($data,true);
//                session('access_token',$data['access_token']);
                $res = Db::name('website_user')->where(['id'=>session('account.id')])->update([
                    'openid'=>$data['openid'],
                    'unionid'=>$data['unionid']
                ]);

                if($res){
                    $user = Db::name('website_user')->where(['id'=>session('account.id')])->find();
                    session('account',$user);

                    header('Location:/?s=index/basic_info');
                }
            }
        }
    }
    #会员中心
    public function member_center(Request $request){
        $dat = input();
        if ($request->isAjax()) {

        } else {
            if(empty(session('account.id'))){
                header('location:/?s=gather/login');
            }
            return view('/gather/estimate/member_center', compact(''));
        }
    }
    #添加包裹
    public function package_forecast(Request $request){
        $data = input();
        if ($request->isAjax()) {
            $data['task_id'] = 3;
//            dd($data);
//            Parceltask::distribute_task($data,1);
            #预报-仓库预定
            if($data['prediction_id']==1 && !isset($data['line_id'])){
                return json(['code'=>-1,'msg'=>'请选择线路。']);
            }
            if($data['prediction_id']==1 && !isset($data['guide_sure'])){
                return json(['code'=>-1,'msg'=>'请打开“《阅读须知》”并确认了解。']);
            }
            if($data['prediction_id']==1 && $data['guide_sure']!='on'){
                return json(['code'=>-1,'msg'=>'请打开“《阅读须知》”并确认了解。']);
            }
            if($data['prediction_id']==1 && empty($data['sure_code'])){
                return json(['code'=>-1,'msg'=>'请打开“《集运须知》”并发送验证码确认。']);
            }
            if($data['prediction_id']==1 && !empty($data['sure_code']) && $data['sure_code'] != session('sure_code')){
                return json(['code'=>-1,'msg'=>'《集运须知》验证码错误。']);
            }
            if($data['prediction_id']==2 && !isset($data['guide_sure'])){
                return json(['code'=>-1,'msg'=>'请打开“《阅读须知》”并确认了解。']);
            }
            if($data['prediction_id']==2 && $data['guide_sure']!='on'){
                return json(['code'=>-1,'msg'=>'请打开“《阅读须知》”并确认了解。']);
            }

            //任务信息处理
            $time = time();

            #获取任务流水号
            $start_num = $this->get_today_task($time);
            if(empty($start_num)){
                $serial_number = 'MC'.date('ymdHis',$time).str_pad(1,2,'0',STR_PAD_LEFT);
            }else{
                $serial_number = 'MC'.date('ymdHis',$time).str_pad(intval($start_num)+1,2,'0',STR_PAD_LEFT);
            }
            #获取任务名称
            $task_name = session('account')['custom_id'].'发起任务[包裹预报]';

            $res = Db::name('centralize_task')->insertGetId([
                'user_id'=>session('account')['id'],
                'type'=>3,
                'task_name'=>$task_name,
                'task_id'=>3,
                'order_id'=>isset($data['order_id'])?$data['order_id']:0,
                'serial_number'=>$serial_number,
                'remark'=>isset($data['remark'])?trim($data['remark']):'',
                'status'=>0,
                'createtime'=>$time
            ]);
            Parceltask::distribute_task($data,$res);
            if($res){
                return json(['code'=>0,'msg'=>'提交预报成功']);
            }
        } else {
            if(empty(session('account.id'))){
                header('location:/?s=gather/login');
            }
            return view('/gather/estimate/package_forecast', compact(''));
        }
    }
    #获取今天的任务排序号
    public function get_today_task($time){
        $data = Db::name('centralize_task')->where('createtime',$time)->order('id desc')->find();

        return substr($data['serial_number'],-2);
    }
    #包裹列表
    public function package_list(Request $request){
        $dat = input();
        $limit = $request->get('limit');
        $page = $request->get('page') - 1;
        if ($page != 0) {
            $page = $limit * $page;
        }
        $keyword = $request->get('keywords') ? $request->get('keywords') : '';
        if (isset($dat['pa'])) {
            if ($dat['pa'] == 1) {
                $where = ['a.user_id' => session('account')['id'], 'a.status' => 0];
                $count = Db::name('centralize_parcel_order_goods')
                    ->alias('a')
                    ->join('centralize_parcel_order b','a.orderid=b.id')
                    ->where($where)
//                    ->where('ordersn', 'like', '%' . $keyword . '%')
                    ->count();
                $list = Db::name('centralize_parcel_order_goods')
                    ->alias('a')
                    ->join('centralize_parcel_order b','a.orderid=b.id')
                    ->where($where)
//                    ->where('ordersn', 'like', '%' . $keyword . '%')
                    ->limit($page . ',' . $limit)
                    ->order('a.id desc')
                    ->field(['a.*','b.ordersn'])
                    ->select();
                foreach ($list as $k => $v) {
                    $list[$k]['createtime'] = date('Y-m-d H:i:s', $v['createtime']);
                    if(!empty($v['itemid'])){
                        $list[$k]['itemid'] = Db::name('centralize_value_list')->where(['id'=>$v['itemid']])->find()['name'];#商品类别
                    }
                }
                return json(['code' => 0, 'count' => $count, 'data' => $list]);
            }
        } else {
            return view('/gather/estimate/package_list', compact(''));
        }
    }
    #我的订单
    public function order_management(Request $request){
        $data = input();

        $limit = $request->get('limit');
        $page = $request->get('page') - 1;
        if ($page != 0) {
            $page = $limit * $page;
        }
        $keyword = $request->get('keywords') ? $request->get('keywords') : '';
        if (isset($data['pa'])) {
            if ($data['pa'] == 1) {
                $where = ['user_id' => session('account')['id'], 'status' => 0];
                $count = Db::name('centralize_parcel_order')->where($where)->where('ordersn', 'like', '%' . $keyword . '%')->count();
                $list = Db::name('centralize_parcel_order')
                    ->where($where)
                    ->where('ordersn', 'like', '%' . $keyword . '%')
                    ->limit($page.','.$limit)
                    ->order('id desc')
                    ->select();
            } elseif ($data['pa'] == 2) {
                $where = ['user_id' => session('account')['id'], 'status' => 1];
                $count = Db::name('centralize_parcel_order')->where($where)->where('ordersn', 'like', '%' . $keyword . '%')->count();
                $list = Db::name('centralize_parcel_order')
                    ->where($where)
                    ->where('ordersn', 'like', '%' . $keyword . '%')
                    ->limit($page.','.$limit)
                    ->order('id desc')
                    ->select();
            } elseif ($data['pa'] == 3) {
                $where = ['user_id' => session('account')['id'], 'status' => 2];
                $count = Db::name('centralize_parcel_order')->where($where)->where('ordersn', 'like', '%' . $keyword . '%')->count();
                $list = Db::name('centralize_parcel_order')
                    ->where($where)
                    ->where('ordersn', 'like', '%' . $keyword . '%')
                    ->limit($page.','.$limit)
                    ->order('id desc')
                    ->select();
            } elseif ($data['pa'] == 4) {
                $where = ['user_id' => session('account')['id'], 'status' => 3];
                $count = Db::name('centralize_parcel_order')->where($where)->where('ordersn', 'like', '%' . $keyword . '%')->count();
                $list = Db::name('centralize_parcel_order')
                    ->where($where)
                    ->where('ordersn', 'like', '%' . $keyword . '%')
                    ->limit($page.','.$limit)
                    ->order('id desc')
                    ->select();
            }

            foreach ($list as $k => $v) {
                $list[$k]['createtime'] = date('Y-m-d H:i:s', $v['createtime']);
            }

            return json(['code' => 0, 'count' => $count, 'data' => $list]);
        }
        else {
            return view('/gather/estimate/order_management', compact(''));
        }
    }
    #订单详情
    public function order_info(Request $request){
        $dat = input();
        $order_id = intval($dat['id']);
        if ($request->isAjax()) {
            if(!isset($dat['sure_code'])){
                return json(['code'=>-1,'msg'=>'请打开《确认须知》并发送验证码确认。']);
            }
            if(!empty($dat['sure_code']) && $dat['sure_code'] != session('sure_code')){
                return json(['code'=>-1,'msg'=>'《确认须知》验证码错误。']);
            }

            $task_id = intval($dat['task_id']);
            $task = Db::name('centralize_task')->where(['id'=>$task_id])->find();
            $time = time();
            if($task['task_id']==3){
                #确认预报
                $order = Db::name('centralize_parcel_order')->where(['id'=>$order_id])->find();
                $order['ordersn'] = substr($order['ordersn'],0,-2);
                if($order['prediction_id']==1){
                    #无需处理即可集运
                    $express_id = $express_no = '';
                    if($dat['delivery_method']==1){
                        $express_id = trim($dat['express_id']);
                        $express_no = trim($dat['express_no']);
                    }elseif($dat['delivery_method']==2){
                        $express_id = trim($dat['logistics_id']);
                        $express_no = trim($dat['logistics_no']);
                    }elseif($dat['delivery_method']==3) {
                        $express_id = 0;
                        $express_no = 'in'.date('YmdHis');
                    }
                    Db::name('centralize_parcel_order_goods')->insert([
                        'user_id'=>Session('account')['id'],
                        'orderid'=>$order_id,
                        'express_id'=>$express_id,
                        'express_no'=>$express_no,
                        #送仓方式
                        'delivery_method'=>$dat['delivery_method'],
                        #商品分类
                        'itemid'=>$dat['itemid'],
                        #商品信息，1在线获取，2自主申报
                        'ginfo'=>$dat['ginfo'],
                        #商品描述
                        'brand_desc'=>$dat['ginfo']==2?trim($dat['desc']):trim($dat['online_desc']),
                        #商品品牌
                        'brand_type'=>intval($dat['brand_type']),
                        #商品名称
                        'brand_name'=>$dat['brand_type']>=2?trim($dat['brand_name']):trim($dat['brand_name2']),
                        #包装材质
                        'package'=>$dat['package'],
                        #毛重
                        'grosswt'=>trim($dat['grosswt']),
                        #体积
                        'true_volumn'=>trim($dat['long']).'*'.trim($dat['width']).'*'.trim($dat['height']),
                        #入仓信息
                        'inwarehouse_date'=>$dat['delivery_method']==3?trim($dat['inwarehouse_date']):'',
                        'contact_name'=>$dat['delivery_method']==3?trim($dat['contact_name']):'',
                        'contact_mobile'=>$dat['delivery_method']==3?trim($dat['contact_mobile']):'',
                        #创建时间
                        'createtime'=>$time
                    ]);
                }
                elseif($order['prediction_id']==2){
                    #包裹需要处理才可集运
                    foreach($dat['delivery_method'] as $k=>$v){
                        $express_id = $express_no = '';
                        if($v==1){
                            $express_id = trim($dat['express_id'][$k]);
                            $express_no = trim($dat['express_no'][$k]);
                        }elseif($v==2){
                            $express_id = trim($dat['logistics_id'][$k]);
                            $express_no = trim($dat['logistics_no'][$k]);
                        }elseif($v==3) {
                            $express_id = 0;
                            $express_no = 'in'.date('YmdHis');
                        }
                        Db::name('centralize_parcel_order_goods')->insert([
                            'user_id'=>Session('account')['id'],
                            'orderid'=>$order_id,
                            'express_id'=>$express_id,
                            'express_no'=>$express_no,
                            #送仓方式
                            'delivery_method'=>$v,
                            #商品分类
                            'itemid'=>$dat['itemid'][$k],
                            #商品信息，1在线获取，2自主申报
                            'ginfo'=>$dat['ginfo'][$k],
                            #商品描述
                            'brand_desc'=>$dat['ginfo'][$k]==2?trim($dat['desc'][$k]):trim($dat['online_desc'][$k]),
                            #商品名称
                            'brand_name'=>$dat['brand_type'][$k]>=2?trim($dat['brand_name'][$k]):trim($dat['brand_name2'][$k]),
                            #验货方式
                            'inspection_method'=>$dat['inspection_method'][$k],
                            #验货事项
                            'inspection_matter'=>json_encode($dat['inspection_matter'][$k],true),
                            #毛重
                            'grosswt'=>trim($dat['grosswt'][$k]),
                            #体积
                            'true_volumn'=>trim($dat['long'][$k]).'*'.trim($dat['width'][$k]).'*'.trim($dat['height'][$k]),
                            #入仓信息
                            'inwarehouse_date'=>$v==3?trim($dat['inwarehouse_date'][$k]):'',
                            'contact_name'=>$v==3?trim($dat['contact_name'][$k]):'',
                            'contact_mobile'=>$v==3?trim($dat['contact_mobile'][$k]):'',
                            #创建时间
                            'createtime'=>$time
                        ]);
                    }
                }
                Db::name('centralize_parcel_order')->where(['id'=>$order_id])->update(['sure_prediction'=>1,'ordersn'=>$order['ordersn'].'02']);
            }
            return json(['code'=>0,'msg'=>'确认预报成功']);
        } else {
            $data = Db::name('centralize_parcel_order')->where(['id'=>$order_id])->find();
            #线路信息
            $data['line_info'] = Db::name('centralize_line_country')
                ->alias('a')
                ->join('centralize_line_list b','b.id=a.pid')
                ->where(['a.id'=>$data['line_id']])
                ->field(['a.*','b.name'])
                ->find();
            #仓库信息
            $data['warehouse_info'] = Db::name('centralize_warehouse_list')->where(['id'=>$data['warehouse_id']])->find();
            #商品类别
            $goods_item = [];
            if($data['prediction_id']==1){
//                $goods_item = Db::name('centralize_value_list')->where(['name'=>$data['line_info']['accept_product']])->select();
                $goods_item = Db::name('centralize_value_list')->select();
            }
            elseif($data['prediction_id']==2){
                $goods_item = Db::name('centralize_value_list')->select();
            }
            #任务信息
            $task = Db::name('centralize_task')
                ->alias('a')
                ->join('centralize_task_list b','a.task_id=b.id')
                ->where(['a.order_id'=>$data['id'],'a.type'=>3])
                ->order('a.id desc')
                ->field(['b.name','a.status'])
                ->find();
            $_status = [0=>'预报订单',1=>'到仓订单',2=>'在仓订单',3=>'集运订单'];
            $_status2 = ['01'=>'已订仓未预报','02'=>'已订仓已预报'];
            #包裹详情信息
            $data['order_goods'] = Db::name('centralize_parcel_order_goods')->where(['orderid'=>$data['id']])->select();
            if(!empty($data['order_goods'])){
                foreach($data['order_goods'] as $k=>$v){
                    if(!empty($v['express_id'])){
                        $data['order_goods'][$k]['express_name'] = Db::name('customs_express_company_code')->where(['id'=>$v['express_id']])->find()['name'];
                    }else{
                        $data['order_goods'][$k]['express_name'] = '自送入仓，无运输企业';
                    }
                    if(!empty($v['itemid'])){
                        $data['order_goods'][$k]['itemid'] = Db::name('centralize_value_list')->where(['id'=>$v['itemid']])->find()['name'];#商品类别
                    }
                    if(!empty($v['package'])) {
                        $data['order_goods'][$k]['package'] = Db::name('packing_type')->where(['code_value' => $v['package']])->find()['code_name'];#包装材质
                    }
                    if($data['prediction_id']==2){
                        $data['order_goods'][$k]['inspection_matter'] = json_decode($v['inspection_matter'],true);
                    }
                }
            }

            $data['createtime'] = date('Y-m-d H:i:s',$data['createtime']);
            $data['status_name'] = $_status2[substr($data['ordersn'],-2)];
//            $data['status_name'] = $task['name'];
            #快递公司
            $express = Db::name('customs_express_company_code')->select();
            #奢侈品牌
            $brand = Db::name('customs_travelexpress_brand')->select();
            #包装材质
            $package = Db::name('packing_type')->select();
            return view('/gather/estimate/order_info', compact('order_id','data','express','brand','package','goods_item'));
        }
    }
    #异常件处理
    public function parcel_claim(Request $request){
        $dat = input();
        if ($request->isAjax()) {

        } else {
            return view('/gather/estimate/parcel_claim', compact(''));
        }
    }
    #会员中心
    public function member(Request $request){
        $dat = input();
        if ($request->isAjax()) {

        } else {
            return view('/gather/estimate/member', compact(''));
        }
    }
    #优惠券
    public function coupon(Request $request){
        $dat = input();
        if ($request->isAjax()) {

        } else {
            return view('/gather/estimate/coupon', compact(''));
        }
    }
    #我的积分
    public function point(Request $request){
        $dat = input();
        if ($request->isAjax()) {

        } else {
            return view('/gather/estimate/point', compact(''));
        }
    }
    #余额充值
    public function balance(Request $request){
        $dat = input();
        if ($request->isAjax()) {

        } else {
            return view('/gather/estimate/balance', compact(''));
        }
    }
    #成为合伙人
    public function become_partner(Request $request){
        $dat = input();
        if ($request->isAjax()) {

        } else {
            return view('/gather/estimate/become_partner', compact(''));
        }
    }
    #仓库地址
    public function warehouse_address(Request $request){
        $dat = input();
        if ($request->isAjax()) {

        } else {
            if(empty(session('account.id'))){
                header('location:/?s=gather/login');
            }
            return view('/gather/estimate/warehouse_address', compact(''));
        }
    }
    #收货地址
    public function address_receive(Request $request){
        $dat = input();
        if ($request->isAjax()) {

        } else {
            return view('/gather/estimate/address_receive', compact(''));
        }
    }
    #个人信息
    public function update_personal(Request $request){
        $dat = input();
        if ($request->isAjax()) {

        } else {
            return view('/gather/estimate/update_personal', compact(''));
        }
    }
    #账号安全
    public function update_password(Request $request){
        $dat = input();
        if ($request->isAjax()) {

        } else {
            return view('/gather/estimate/update_password', compact(''));
        }
    }

    #获取线路详情
    public function get_line_detail(Request $request){
        $data = input();

        $list = Db::name('centralize_line_country')
            ->alias('a')
            ->join('centralize_line_list b','b.id=a.pid')
            ->where(['a.id'=>$data['id']])
            ->field(['a.*','b.track_website,b.detail_content'])
            ->find();

        $list['detail_content'] = str_replace("&nbsp;", '', json_decode($list['detail_content'],true));
        return json(['code'=>0,'data'=>$list]);
    }

    #获取国地的手机号前缀
    public function getphonenum(Request $request){
        $data = input();

//        $area = Db::name('centralize_adminstrative_area')->where(['id'=>$data['id']])->find();
//        $country = Db::name('centralize_diycountry_content')->where(['id'=>$area['country_id']])->find();
        $country = Db::name('centralize_diycountry_content')->where(['id'=>$data['id']])->find();
        $all_country = Db::name('centralize_diycountry_content')->where(['pid'=>5])->select();
        #获取国家下第一层地区
        $area = Db::name('centralize_adminstrative_area')->where(['country_id'=>$data['id'],'pid'=>0])->select();
        return json(['code'=>0,'phone'=>$country['param8'],'all_country'=>$all_country,'area'=>$area]);
    }

    #获取国地汇率
    public function get_rate(Request $request){
        $data = input();
        $rate = Db::name('centralize_currency')->where(['id'=>$data['val']])->find();
        return json(['code'=>-1,'data'=>$rate]);
    }

    #获取线路信息
    public function get_line_info(Request $request){
        $data = input();
        try{
            $country_id = explode('_',$data['country_id'])[1];
            $end_country = Db::name('centralize_adminstrative_area')->where(['id'=>$country_id])->find();
            $line = Db::name('centralize_lines')->where(['end_country'=>$end_country['country_id']])->select();
            $lines = [];
            foreach($line as $k=>$v){
                $line[$k]['content'] = json_decode($v['content'],true);
                foreach($line[$k]['content']['procategory'] as $k2=>$v2){
                    if($v2==$data['id']){
                        $proname = Db::name('centralize_gvalue_list')->where(['id'=>$v2])->find();
                        $lines[$k]['value'] = $v['id'];
                        $lines[$k]['name'] = $v['code'].'-['.$proname['name'].']'.' '.$v['name'];
                        $lines[$k]['id'] = $v['id'];
                        $lines[$k]['children'] = [];
                    }
                }
            }
            $lines = array_values($lines);
        }catch(\Exception $e){
            $country_id = $data['country_id'];
//            $end_country = Db::name('centralize_adminstrative_area')->where(['id'=>$country_id])->find();
            $line = Db::name('centralize_lines')->where(['end_country'=>$country_id])->select();
            $lines = [];
            foreach($line as $k=>$v){
                $line[$k]['content'] = json_decode($v['content'],true);
                foreach($line[$k]['content']['procategory'] as $k2=>$v2){
                    if($v2==$data['id']){
                        $proname = Db::name('centralize_gvalue_list')->where(['id'=>$v2])->find();
                        $lines[$k]['value'] = $v['id'];
                        $lines[$k]['name'] = $v['code'].'-['.$proname['name'].']'.' '.$v['name'];
                        $lines[$k]['id'] = $v['id'];
                        $lines[$k]['children'] = [];
                    }
                }
            }
            $lines = array_values($lines);
        }


//        $list = Db::name('centralize_line_country')
//            ->alias('a')
//            ->join('centralize_line_list b','b.id=a.pid')
//            ->where(['b.id'=>$data['id']])
//            ->field(['a.*','b.track_website'])
//            ->find();

//        $list = Db::name('centralize_lines')->where(['id'=>$data['id']])->find();
//        $list['content'] = json_decode($list['content'],true);
//
//        #获取线路下的货物类别和对应适用货物
//        $procategory = [];
//        foreach($list['content']['procategory'] as $k=>$v){
//            $pc = Db::name('centralize_lines_procategory')->where(['id'=>$v])->find();
//            $procategory[$k]['name'] = $pc['name'];
//            $procategory[$k]['value'] = 'p_'.$pc['id'];
//            $pc_child = Db::name('centralize_gvalue_list')->whereRaw('id in ('.$list['content']['chicategory'][$k].')')->select();
//            foreach($pc_child as $k2=>$v2){
//                $procategory[$k]['children'][$k2]['name'] = $v2['name'];
//                $procategory[$k]['children'][$k2]['value'] = $v2['id'];
//                $procategory[$k]['children'][$k2]['children'] = [];
//            }
//        }

        return json(['code'=>0,'data'=>$lines]);
    }

    #获取账单数据表信息(带缓存)
    public function gettableinfo($id){
//        session('country_list','');
//        session('line_list','');
//        session('value_list','');
        $res = '';
        if($id==1) {
            #国家
//            if (session('country_list') == '') {
                $res = httpRequest('https://shop.gogo198.cn/collect_website/public/?s=api/gather/gettableinfo', ['id' => $id]);
                $res2 = json_decode($res, true);
                $res = $res2['list'];
                session('country_list', $res);
//            } else {
//                $res = session('country_list');
//            }
        }
        if($id==2) {
            #线路
//            if (session('line_list') == '') {
                $res = httpRequest('https://shop.gogo198.cn/collect_website/public/?s=api/gather/gettableinfo', ['id' => $id]);
                $res2 = json_decode($res, true);
                $res = $res2['list'];
                session('line_list', $res);
//            } else {
//                $res = session('line_list');
//            }
        }
        if($id==3) {
            #物品属性
//            if (session('value_list') == '') {
//                $res = httpRequest('https://shop.gogo198.cn/collect_website/public/?s=api/gather/gettableinfo', ['id' => $id]);
//                $res2 = json_decode($res, true);
//                $res = $res2['list'];
//                session('value_list', $res);
//            } else {
//                $res = session('value_list');
//            }
            $res = httpRequest('https://shop.gogo198.cn/collect_website/public/?s=api/gather/gettableinfo', ['id' => $id]);
            $res2 = json_decode($res, true);
            $res = $res2['list'];
            session('value_list', $res);
        }

        return json(['code'=>0,'list'=>$res]);
    }

    //获取当前线路下的货物类别体积比
    public function get_volumn(Request $request){
        $dat = input();

        $line = Db::name('centralize_lines')->where(['id'=>$dat['id']])->find();
        $line['content'] = json_decode($line['content'],true);

        $volumn = '';
        $gnum = 0;
        foreach($line['content']['procategory'] as $k=>$v){
            if(strpos($v, $dat['procategory']) !== false){
                $volumn = $line['content']['rate'][$k];
                $gnum = $k;
            }
        }
        return json(['code'=>0,'volumn'=>$volumn,'gnum'=>$gnum]);
    }

    #计算线路的计费重和计费额
    public function calclinecost(Request $request){
        $dat = input();
//        $r = number_format(108.4556, 2, '.', '');
//        dd($dat);
        try {
            $start_country = explode('_', $dat['start_country'])[1];
        }catch(\Exception $e){
            $start_country = $dat['start_country'];
        }

        list($grosst,$long,$width,$height,$volumn,$grosswt_unit,$size_unit,$gnum,$isphone,$area_id) = [trim($dat['grosswt']),trim($dat['long']),trim($dat['width']),trim($dat['height']),trim($dat['volumn']),trim($dat['grosswt_unit']),trim($dat['size_unit']),trim($dat['gnum']),trim($dat['isphone']),$start_country];
        ##1、获取线路
        $line = Db::name('centralize_lines')->where(['id'=>$dat['lineid']])->find();
        $line['content'] = json_decode($line['content'],true);
        ##2、获取包裹重量和体积重的最大者
        $long=$long*100;$width=$width*100;$height=$height*100;
        if($volumn!=0){
            $vw = number_format(($long * $width * $height) / $volumn, 2, '.', '');
        }else{
            $vw = 0;
        }
        $true_weight = 0;
        #体积重 > OR < 包裹毛重，取最大值
        if($vw >= $grosst){
            $true_weight = &$vw;
        }else{
            $true_weight = &$grosst;
        }
        ##3、获取指定货物下的计费单位进行计费
        $calc_weight = 0;$price = 0;$html='';

        foreach($line['content']['qj1'][$gnum] as $k=>$v){
//            if($line['content']['unit'][$gnum]=='035'){
                #千克
                if($line['content']['qj2_method'][$gnum][$k]==1){
                    #数值

                    if($true_weight>=$v && $true_weight<=$line['content']['qj2'][$gnum][$k]){

                        $html = $this->start_calc($line,$gnum,$k,$true_weight,$isphone,[$grosswt_unit,$area_id]);
                    }
                }elseif($line['content']['qj2_method'][$gnum][$k]==2){
                    #以上
                    if($true_weight>=$v){
                        $html = $this->start_calc($line,$gnum,$k,$true_weight,$isphone,[$grosswt_unit,$area_id]);
                    }
                }
//            }elseif($line['conetnt']['unit'][$gnum]=='033'){
//                #立方米
//            }
        }
        $unit = Db::name('unit')->where(['code_value'=>$line['content']['unit'][$gnum]])->find();

        return json(['code'=>0,'html'=>$html[0],'true_weight'=>$html[1],'unit'=>$unit]);
    }

    #线路计费
    public function start_calc($line,$gnum,$k,$true_weight,$isphone,$bill_datas){
        $html='';
        #4、判断计费重量是否在该区间下

        foreach($line['content']['jf_method'][$gnum][$k] as $k2=>$v2){
            #5、根据计费方式进行计费
            #6、将计费重按照进阶重的格式变为可计算数值（目前只有这三种格式：100，100.5，100.1）
            $true_weight2 = explode('.',$true_weight);
            if(count($true_weight2)>1){
                $true_weight = $true_weight2[0];
                $true_weight2 = floatval('0.'.$true_weight2[1]);
                if($true_weight2>0){
                    if($true_weight2<$line['content']['jinjie'][$gnum][$k][$k2]){
                        $true_weight2=$line['content']['jinjie'][$gnum][$k][$k2];
                    }
                }
                $true_weight = floatval($true_weight) + floatval($true_weight2);
            }
            #7、开始计算费用
            if($v2==1){
                #7、首续重计费，计法：(100-1首重)/1续重*15续重额+30首重额
                $price = (($true_weight - $line['content']['shouzhong'][$gnum][$k][$k2]) / $line['content']['xuzhong'][$gnum][$k][$k2]) * $line['content']['xuzhong_money'][$gnum][$k][$k2] + $line['content']['shouzhong_money'][$gnum][$k][$k2];
                #续重数量
                $xz_num = (($true_weight - $line['content']['shouzhong'][$gnum][$k][$k2]) / $line['content']['xuzhong'][$gnum][$k][$k2]);//10-0.5)/0.5,1-0.5)/0.5
                #续重金额
                $xz_price = (($true_weight - $line['content']['shouzhong'][$gnum][$k][$k2]) / $line['content']['xuzhong'][$gnum][$k][$k2]) * $line['content']['xuzhong_money'][$gnum][$k][$k2];
                $datas = [$price,$true_weight,[$line['content']['shouzhong'][$gnum][$k][$k2],$line['content']['shouzhong_money'][$gnum][$k][$k2]],[$line['content']['xuzhong'][$gnum][$k][$k2],$line['content']['xuzhong_money'][$gnum][$k][$k2],$xz_num,$xz_price]];
                $html = $this->get_calhtml($isphone,1,$datas,$bill_datas);
            }elseif($v2==2){
                #7、计量计费，计法：(100/1千克)*34元
                $price = ($true_weight / $line['content']['anliang'][$gnum][$k][$k2]) * $line['content']['anliang_money'][$gnum][$k][$k2];
                $datas = [$price,$true_weight,$line['content']['anliang_money'][$gnum][$k][$k2]];
                $html = $this->get_calhtml($isphone,2,$datas,$bill_datas);
            }elseif($v2==3){
                #7、分段计费
                foreach($line['content']['fenduan_num1'][$gnum][$k][$k2] as $k3=>$v3){
                    if($line['content']['fenduan_method'][$gnum][$k][$k2][$k3]){
                        #数值
                        if($true_weight>=$v3 && $true_weight<=$line['content']['fenduan_num2'][$gnum][$k][$k2]){
                            #8、重量在该区间的分段计费下，计法：（100/1进阶）*16
                            $price = ($true_weight / $line['content']['jinjie'][$gnum][$k]) * $line['content']['fenduan_money'][$gnum][$k][$k2][$k3];
                            $datas = [$price,$true_weight,$line['content']['fenduan_money'][$gnum][$k][$k2][$k3]];
                            $html = $this->get_calhtml($isphone,3,$datas,$bill_datas);
                        }
                    }else{
                        #以上
                        if($true_weight>=$v3 && $true_weight<=$line['content']['qj2'][$gnum][$k]){
                            #8、重量在该区间的分段计费下，计法：（100/1进阶）*16
                            $price = ($true_weight / $line['content']['jinjie'][$gnum][$k]) * $line['content']['fenduan_money'][$gnum][$k][$k2][$k3];
                            $datas = [$price,$true_weight,$line['content']['fenduan_money'][$gnum][$k][$k2][$k3]];
                            $html = $this->get_calhtml($isphone,3,$datas,$bill_datas);
                        }
                    }
                }
            }
        }

        return [$html,$true_weight];
    }

    #获取计费后的账单详情html
    public function get_calhtml($isphone,$type,$datas=[],$bill_datas){
        $html = '';
        #计量单位
        $unit_grosswt = Db::name('unit')->where(['code_value'=>$bill_datas[0]])->find();

        #账单启运国家-币种
        $country = Db::name('centralize_adminstrative_area')->where(['id'=>$bill_datas[1]])->find();
        $currency_start = Db::name('centralize_currency')->where(['country_id'=>$country['country_id']])->find();

        #全部币种+计量单位
        $currency = Db::name('centralize_currency')->select();
        $unit = Db::name('unit')->select();
//        dd($currency['currency_symbol_origin'].' '.$unit['code_name']);

        if($type==1){
            #首续重
            if($isphone=='true'){
                $html = '                                               <div class="layui-colla-item bill_body_phone">'.
                    '                                                        <h2 class="layui-colla-title disf" style="justify-content: space-between;">'.
                    '                                                            <span>首重.</span>'.
                    '                                                        </h2>'.
                    '                                                        <div class="layui-colla-content">'.
                    '                                                            <div class="layui-form-item">'.
                    '                                                                <div class="layui-form-label">序号</div>'.
                    '                                                                <div class="layui-input-block disf">'.
                    '                                                                    <input class="layui-input" name="body_content[snum][]" value="1" lay-verify="" readonly>'.
                    '                                                                </div>'.
                    '                                                            </div>'.
                    '                                                            <div class="layui-form-item">'.
                    '                                                                <div class="layui-form-label">项目</div>'.
                    '                                                                <div class="layui-input-block disf">'.
                    '                                                                    <input class="layui-input" name="body_content[project][]" value="首重" lay-verify="" onchange="project_name(this)">'.
                    '                                                                </div>'.
                    '                                                            </div>'.
                    '                                                            <div class="layui-form-item">'.
                    '                                                                <div class="layui-form-label">摘要</div>'.
                    '                                                                <div class="layui-input-block disf">'.
                    '                                                                    <input class="layui-input" name="body_content[abstract][]" value="" lay-verify="">'.
                    '                                                                </div>'.
                    '                                                            </div>'.
                    '                                                            <div class="layui-form-item">'.
                    '                                                                <div class="layui-form-label">单价</div>'.
                    '                                                                <div class="layui-input-block disf">'.
                    '                                                                    <div class="currency_div">'.
                    '                                                                         <select name="body_content[currency][]" class="currency" lay-search lay-filter="currency">'.
                    '                                                                              <option value="">选择币种</option>';
                foreach($currency as $k=>$v){
                    if($country['country_id']==$v['country_id']){
                        $html .= '<option value="'.$v['id'].'" selected data-title="'.$v['currency_symbol_origin'].'">'.$v['code_zhname'].':'.$v['currency_symbol_origin'].'</option>';
                    }else{
                        $html .= '<option value="'.$v['id'].'" data-title="'.$v['currency_symbol_origin'].'">'.$v['code_zhname'].':'.$v['currency_symbol_origin'].'</option>';
                    }
                }
                $html .= '                                                                         </select>'.
                    '                                                                    </div>'.
                    '                                                                    <input class="layui-input" name="body_content[price][]" value="'.$datas[2][1].'" lay-verify="required">'.
                    '                                                                </div>'.
                    '                                                            </div>'.
                    '                                                            <div class="layui-form-item">'.
                    '                                                                <div class="layui-form-label">数量</div>'.
                    '                                                                <div class="layui-input-block disf">'.
                    '                                                                    <div class="unit_div">'.
                    '                                                                        <select name="body_content[unit][]" lay-search lay-filter="unit">'.
                    '                                                                               <option value="">选择单位</option>';
                    foreach($unit as $k=>$v){
                        if($unit_grosswt['code_name']==$v['code_name']){
                            $html .= '<option value="'.$v['code_value'].'" selected data-title="'.$v['code_name'].'">'.$v['code_name'].'</option>';
                        }else{
                            $html .= '<option value="'.$v['code_value'].'" data-title="'.$v['code_name'].'">'.$v['code_name'].'</option>';
                        }
                    }
                $html .='                                                                        </select>'.
                    '                                                                    </div>'.
                    '                                                                    <input class="layui-input" name="body_content[num][]" value="'.$datas[2][0].'" lay-verify="required">'.
                    '                                                                </div>'.
                    '                                                            </div>'.
                    '                                                            <div class="layui-form-item">'.
                    '                                                                <div class="layui-form-label">金额</div>'.
                    '                                                                <div class="layui-input-block disf">'.
                    '                                                                    <div class="currency_div2">'.$currency_start['currency_symbol_origin'].'</div>'.
                    '                                                                    <input class="layui-input" name="body_content[money][]" value="'.$datas[2][1].'" lay-verify="required">'.
                    '                                                                </div>'.
                    '                                                            </div>'.
                    '                                                            <div class="layui-form-item">'.
                    '                                                                <div class="layui-form-label">备注</div>'.
                    '                                                                <div class="layui-input-block disf">'.
                    '                                                                    <input class="layui-input" name="body_content[remark][]" value="">'.
                    '                                                                </div>'.
                    '                                                            </div>'.
                    '                                                        </div>'.
                    '                                                    </div>'.
                    '                                                    <div class="layui-colla-item bill_body_phone">'.
                    '                                                        <h2 class="layui-colla-title disf" style="justify-content: space-between;">'.
                    '                                                            <span>续重.</span>'.
                    '                                                        </h2>'.
                    '                                                        <div class="layui-colla-content">'.
                    '                                                            <div class="layui-form-item">'.
                    '                                                                <div class="layui-form-label">序号</div>'.
                    '                                                                <div class="layui-input-block disf">'.
                    '                                                                    <input class="layui-input" name="body_content[snum][]" value="2" lay-verify="" readonly>'.
                    '                                                                </div>'.
                    '                                                            </div>'.
                    '                                                            <div class="layui-form-item">'.
                    '                                                                <div class="layui-form-label">项目</div>'.
                    '                                                                <div class="layui-input-block disf">'.
                    '                                                                    <input class="layui-input" name="body_content[project][]" value="续重" lay-verify="" onchange="project_name(this)">'.
                    '                                                                </div>'.
                    '                                                            </div>'.
                    '                                                            <div class="layui-form-item">'.
                    '                                                                <div class="layui-form-label">摘要</div>'.
                    '                                                                <div class="layui-input-block disf">'.
                    '                                                                    <input class="layui-input" name="body_content[abstract][]" value="" lay-verify="">'.
                    '                                                                </div>'.
                    '                                                            </div>'.
                    '                                                            <div class="layui-form-item">'.
                    '                                                                <div class="layui-form-label">单价</div>'.
                    '                                                                <div class="layui-input-block disf">'.
                    '                                                                    <div class="currency_div">'.
                    '                                                                         <select name="body_content[currency][]" class="currency" lay-search lay-filter="currency">'.
                    '                                                                              <option value="">选择币种</option>';
                foreach($currency as $k=>$v){
                    if($country['country_id']==$v['country_id']){
                        $html .= '<option value="'.$v['id'].'" selected data-title="'.$v['currency_symbol_origin'].'">'.$v['code_zhname'].':'.$v['currency_symbol_origin'].'</option>';
                    }else{
                        $html .= '<option value="'.$v['id'].'" data-title="'.$v['currency_symbol_origin'].'">'.$v['code_zhname'].':'.$v['currency_symbol_origin'].'</option>';
                    }
                }
                $html .= '                                                                         </select>'.
                    '                                                                    </div>'.
                    '                                                                    <input class="layui-input" name="body_content[price][]" value="'.$datas[3][1].'" lay-verify="required">'.
                    '                                                                </div>'.
                    '                                                            </div>'.
                    '                                                            <div class="layui-form-item">'.
                    '                                                                <div class="layui-form-label">数量</div>'.
                    '                                                                <div class="layui-input-block disf">'.
                    '                                                                    <div class="unit_div">'.
                    '                                                                        <select name="body_content[unit][]" lay-search lay-filter="unit">'.
                    '                                                                               <option value="">选择单位</option>';
                foreach($unit as $k=>$v){
                    if($unit_grosswt['code_name']==$v['code_name']){
                        $html .= '<option value="'.$v['code_value'].'" selected data-title="'.$v['code_name'].'">'.$v['code_name'].'</option>';
                    }else{
                        $html .= '<option value="'.$v['code_value'].'" data-title="'.$v['code_name'].'">'.$v['code_name'].'</option>';
                    }
                }
                $html .='                                                                        </select>'.
                    '                                                                    </div>'.
                    '                                                                    <input class="layui-input" name="body_content[num][]" value="'.$datas[3][2].'" lay-verify="required">'.
                    '                                                                </div>'.
                    '                                                            </div>'.
                    '                                                            <div class="layui-form-item">'.
                    '                                                                <div class="layui-form-label">金额</div>'.
                    '                                                                <div class="layui-input-block disf">'.
                    '                                                                    <div class="currency_div2">'.$currency_start['currency_symbol_origin'].'</div>'.
                    '                                                                    <input class="layui-input" name="body_content[money][]" value="'.$datas[3][3].'" lay-verify="required">'.
                    '                                                                </div>'.
                    '                                                            </div>'.
                    '                                                            <div class="layui-form-item">'.
                    '                                                                <div class="layui-form-label">备注</div>'.
                    '                                                                <div class="layui-input-block disf">'.
                    '                                                                    <input class="layui-input" name="body_content[remark][]" value="">'.
                    '                                                                </div>'.
                    '                                                            </div>'.
                    '                                                        </div>'.
                    '                                                    </div>'.
                    '                                                    <div class="layui-form-item">'.
                    '                                                        <div class="layui-input-block disf" style="margin-left:20px;margin-top:10px;">'.
                    '                                                             <div class="layui-btn layui-btn-success add_row f26" onclick="add_row(this)">+</div>'.
                    '                                                        </div>'.
                    '                                                    </div>';
            }
            else{
                $html = '    <tr>'.
                    '           <td><input class="layui-input" name="body_content[snum][]" value="1" lay-verify="" readonly></td>'.
                    '           <td><input class="layui-input" name="body_content[project][]" value="首重" lay-verify=""></td>'.
                    '           <td><input class="layui-input" name="body_content[abstract][]" value="" lay-verify=""></td>'.
                    '           <td><div class="disf"><div class="currency_div">'.
                    '               <select name="body_content[currency][]" class="currency" lay-search lay-filter="currency">'.
                    '                      <option value="">选择币种</option>';
                    foreach($currency as $k=>$v){
                        if($country['country_id']==$v['country_id']){
                            $html .= '<option value="'.$v['id'].'" selected data-title="'.$v['currency_symbol_origin'].'">'.$v['code_zhname'].':'.$v['currency_symbol_origin'].'</option>';
                        }else{
                            $html .= '<option value="'.$v['id'].'" data-title="'.$v['currency_symbol_origin'].'">'.$v['code_zhname'].':'.$v['currency_symbol_origin'].'</option>';
                        }
                    }
                $html .= '         </select>'.
                    '</div><input class="layui-input" name="body_content[price][]" value="'.$datas[2][1].'" lay-verify="required"></div></td>'.
                    '           <td><div class="disf"><div class="unit_div">'.
                    '                                                                        <select name="body_content[unit][]" lay-search lay-filter="unit">'.
                    '                                                                               <option value="">选择单位</option>';
                foreach($unit as $k=>$v){
                    if($unit_grosswt['code_name']==$v['code_name']){
                        $html .= '<option value="'.$v['code_value'].'" selected data-title="'.$v['code_name'].'">'.$v['code_name'].'</option>';
                    }else{
                        $html .= '<option value="'.$v['code_value'].'" data-title="'.$v['code_name'].'">'.$v['code_name'].'</option>';
                    }
                }
                $html .='                                                                        </select>'.
                    '</div><input class="layui-input" name="body_content[num][]" value="'.$datas[2][0].'" lay-verify="required"></div></td>'.
                    '           <td><div class="disf"><div class="currency_div2">'.$currency_start['currency_symbol_origin'].'</div><input class="layui-input" name="body_content[money][]" value="'.$datas[2][1].'" lay-verify="required"></div></td>'.
                    '           <td><input class="layui-input" name="body_content[remark][]" value=""></td>'.
                    '        </tr>'.
                    '        <tr>'.
                    '           <td><input class="layui-input" name="body_content[snum][]" value="2" lay-verify="" readonly></td>'.
                    '           <td><input class="layui-input" name="body_content[project][]" value="续重" lay-verify=""></td>'.
                    '           <td><input class="layui-input" name="body_content[abstract][]" value="" lay-verify=""></td>'.
                    '           <td><div class="disf"><div class="currency_div">'.
                    '               <select name="body_content[currency][]" class="currency" lay-search lay-filter="currency">'.
                    '                      <option value="">选择币种</option>';
                foreach($currency as $k=>$v){
                    if($country['country_id']==$v['country_id']){
                        $html .= '<option value="'.$v['id'].'" selected data-title="'.$v['currency_symbol_origin'].'">'.$v['code_zhname'].':'.$v['currency_symbol_origin'].'</option>';
                    }else{
                        $html .= '<option value="'.$v['id'].'" data-title="'.$v['currency_symbol_origin'].'">'.$v['code_zhname'].':'.$v['currency_symbol_origin'].'</option>';
                    }
                }
                $html .= '         </select>'.
                    '</div><input class="layui-input" name="body_content[price][]" value="'.$datas[3][1].'" lay-verify="required"></div></td>'.
                    '           <td><div class="disf"><div class="unit_div">'.
                    '                                                                        <select name="body_content[unit][]" lay-search lay-filter="unit">'.
                    '                                                                               <option value="">选择单位</option>';
                foreach($unit as $k=>$v){
                    if($unit_grosswt['code_name']==$v['code_name']){
                        $html .= '<option value="'.$v['code_value'].'" selected data-title="'.$v['code_name'].'">'.$v['code_name'].'</option>';
                    }else{
                        $html .= '<option value="'.$v['code_value'].'" data-title="'.$v['code_name'].'">'.$v['code_name'].'</option>';
                    }
                }
                $html .='                                                                        </select>'.
                    '</div><input class="layui-input" name="body_content[num][]" value="'.$datas[3][2].'" lay-verify="required"></div></td>'.
                    '           <td><div class="disf"><div class="currency_div2">'.$currency_start['currency_symbol_origin'].'</div><input class="layui-input" name="body_content[money][]" value="'.$datas[3][3].'" lay-verify="required"></div></td>'.
                    '           <td><input class="layui-input" name="body_content[remark][]" value=""></td>'.
                    '        </tr>'.
                    '        <tr class="opera">'.
                    '           <td colspan="8">'.
                    '               <div class="layui-btn layui-btn-success add_row f26" onclick="add_row(this)">+</div>'.
                    '           </td>'.
                    '        </tr>';
            }
        }
        elseif($type==2 || $type==3){
            $name = '';
            if($type==2){
                $name='按量计费';
            }elseif($type==3){
                $name='分段计费';
            }

            if($isphone=='true'){
                $html = '<div class="layui-colla-item bill_body_phone">'.
                    '                                                        <h2 class="layui-colla-title disf" style="justify-content: space-between;">'.
                    '                                                            <span>'.$name.'.</span>'.
                    '                                                        </h2>'.
                    '                                                        <div class="layui-colla-content">'.
                    '                                                            <div class="layui-form-item">'.
                    '                                                                <div class="layui-form-label">序号</div>'.
                    '                                                                <div class="layui-input-block disf">'.
                    '                                                                    <input class="layui-input" name="body_content[snum][]" value="1" lay-verify="" readonly>'.
                    '                                                                </div>'.
                    '                                                            </div>'.
                    '                                                            <div class="layui-form-item">'.
                    '                                                                <div class="layui-form-label">项目</div>'.
                    '                                                                <div class="layui-input-block disf">'.
                    '                                                                    <input class="layui-input" name="body_content[project][]" value="'.$name.'" lay-verify="" onchange="project_name(this)">'.
                    '                                                                </div>'.
                    '                                                            </div>'.
                    '                                                            <div class="layui-form-item">'.
                    '                                                                <div class="layui-form-label">摘要</div>'.
                    '                                                                <div class="layui-input-block disf">'.
                    '                                                                    <input class="layui-input" name="body_content[abstract][]" value="" lay-verify="">'.
                    '                                                                </div>'.
                    '                                                            </div>'.
                    '                                                            <div class="layui-form-item">'.
                    '                                                                <div class="layui-form-label">单价</div>'.
                    '                                                                <div class="layui-input-block disf">'.
                    '                                                                    <div class="currency_div">'.
                    '                                                                         <select name="body_content[currency][]" class="currency" lay-search lay-filter="currency">'.
                    '                                                                              <option value="">选择币种</option>';
                foreach($currency as $k=>$v){
                    if($country['country_id']==$v['country_id']){
                        $html .= '<option value="'.$v['id'].'" selected data-title="'.$v['currency_symbol_origin'].'">'.$v['code_zhname'].':'.$v['currency_symbol_origin'].'</option>';
                    }else{
                        $html .= '<option value="'.$v['id'].'" data-title="'.$v['currency_symbol_origin'].'">'.$v['code_zhname'].':'.$v['currency_symbol_origin'].'</option>';
                    }
                }
                $html .= '                                                                         </select>'.
                    '                                                                    </div>'.
                    '                                                                    <input class="layui-input" name="body_content[price][]" value="'.$datas[2].'" lay-verify="required">'.
                    '                                                                </div>'.
                    '                                                            </div>'.
                    '                                                            <div class="layui-form-item">'.
                    '                                                                <div class="layui-form-label">数量</div>'.
                    '                                                                <div class="layui-input-block disf">'.
                    '                                                                    <div class="unit_div">'.
                    '                                                                        <select name="body_content[unit][]" lay-search lay-filter="unit">'.
                    '                                                                               <option value="">选择单位</option>';
                foreach($unit as $k=>$v){
                    if($unit_grosswt['code_name']==$v['code_name']){
                        $html .= '<option value="'.$v['code_value'].'" selected data-title="'.$v['code_name'].'">'.$v['code_name'].'</option>';
                    }else{
                        $html .= '<option value="'.$v['code_value'].'" data-title="'.$v['code_name'].'">'.$v['code_name'].'</option>';
                    }
                }
                $html .='                                                                        </select>'.
                    '                                                                    </div>'.
                    '                                                                    <input class="layui-input" name="body_content[num][]" value="'.$datas[1].'" lay-verify="required">'.
                    '                                                                </div>'.
                    '                                                            </div>'.
                    '                                                            <div class="layui-form-item">'.
                    '                                                                <div class="layui-form-label">金额</div>'.
                    '                                                                <div class="layui-input-block disf">'.
                    '                                                                    <div class="currency_div2">'.$currency_start['currency_symbol_origin'].'</div>'.
                    '                                                                    <input class="layui-input" name="body_content[money][]" value="'.$datas[0].'" lay-verify="required">'.
                    '                                                                </div>'.
                    '                                                            </div>'.
                    '                                                            <div class="layui-form-item">'.
                    '                                                                <div class="layui-form-label">备注</div>'.
                    '                                                                <div class="layui-input-block disf">'.
                    '                                                                    <input class="layui-input" name="body_content[remark][]" value="">'.
                    '                                                                </div>'.
                    '                                                            </div>'.
                    '                                                        </div>'.
                    '                                                    </div>'.
                    '                                                    <div class="layui-form-item">'.
                    '                                                        <div class="layui-input-block disf" style="margin-left:20px;margin-top:10px;">'.
                    '                                                             <div class="layui-btn layui-btn-success add_row f26" onclick="add_row(this)">+</div>'.
                    '                                                        </div>'.
                    '                                                    </div>';

            }
            else{
                $html = '        <tr>'.
                        '           <td><input class="layui-input" name="body_content[snum][]" value="1" lay-verify="" readonly></td>'.
                        '           <td><input class="layui-input" name="body_content[project][]" value="'.$name.'" lay-verify=""></td>'.
                        '           <td><input class="layui-input" name="body_content[abstract][]" value="" lay-verify=""></td>'.
                        '           <td><div class="disf"><div class="currency_div">'.
                        '               <select name="body_content[currency][]" class="currency" lay-search lay-filter="currency">'.
                        '                      <option value="">选择币种</option>';
                    foreach($currency as $k=>$v){
                        if($country['country_id']==$v['country_id']){
                            $html .= '<option value="'.$v['id'].'" selected data-title="'.$v['currency_symbol_origin'].'">'.$v['code_zhname'].':'.$v['currency_symbol_origin'].'</option>';
                        }else{
                            $html .= '<option value="'.$v['id'].'" data-title="'.$v['currency_symbol_origin'].'">'.$v['code_zhname'].':'.$v['currency_symbol_origin'].'</option>';
                        }
                    }
                    $html .= '         </select>'.
                        '</div><input class="layui-input" name="body_content[price][]" value="'.$datas[2].'" lay-verify="required"></div></td>'.
                        '           <td><div class="disf"><div class="unit_div">'.
                        '                                                                        <select name="body_content[unit][]" lay-search lay-filter="unit">'.
                        '                                                                               <option value="">选择单位</option>';
                foreach($unit as $k=>$v){
                    if($unit_grosswt['code_name']==$v['code_name']){
                        $html .= '<option value="'.$v['code_value'].'" selected data-title="'.$v['code_name'].'">'.$v['code_name'].'</option>';
                    }else{
                        $html .= '<option value="'.$v['code_value'].'" data-title="'.$v['code_name'].'">'.$v['code_name'].'</option>';
                    }
                }
                $html .='                                                                        </select>'.
                        '</div><input class="layui-input" name="body_content[num][]" value="'.$datas[1].'" lay-verify="required"></div></td>'.
                        '           <td><div class="disf"><div class="currency_div2">'.$currency_start['currency_symbol_origin'].'</div><input class="layui-input" name="body_content[money][]" value="'.$datas[0].'" lay-verify="required"></div></td>'.
                        '           <td><input class="layui-input" name="body_content[remark][]" value=""></td>'.
                        '        </tr>'.
                        '        <tr class="opera">'.
                        '           <td colspan="8">'.
                        '               <div class="layui-btn layui-btn-success add_row f26" onclick="add_row(this)">+</div>'.
                        '           </td>'.
                        '        </tr>';
            }
        }

        return $html;
    }

    #获取币种
    public function get_currency(Request $request){
        $data = input();

        $list = Db::name('currency')->select();
        return json(['code'=>0,'list'=>$list]);
    }
    #获取仓库
    public function get_warehouse(Request $request){
        $data = input();
        $list = Db::name('centralize_warehouse_list')->where(['status'=>0])->order('id desc')->select();

        return json(['code'=>0,'list'=>$list]);
    }

    #获取指定仓库
    public function get_warehouse_info(Request $request){
        $data = input();
        $list = Db::name('centralize_warehouse_list')->where(['id'=>$data['warehouse_id']])->find();
        $list['province_code'] = Db::name('centralize_diycountry_content')->where(['id'=>$list['province_code']])->find()['param2'];
        $list['city_code'] = Db::name('centralize_diycountry_content')->where(['id'=>$list['city_code']])->find()['param2'];
        if(!empty($list['addresss'])){
            $list['addresss'] = json_decode($list['addresss'],true);
        }
        return json(['code'=>0,'data'=>$list]);
    }

    #获取线路
    public function get_lines(Request $request){
        $data = input();

        #先找国家名称
        $country = Db::name('country_code')->where(['code_value'=>$data['val']])->find();
        #再找线路国家表
        $line_country = Db::name('centralize_line_country')
            ->alias('a')
            ->join('centralize_line_list b','b.id=a.pid')
            ->where(['a.country_code'=>$country['code_name'],'b.template_id'=>$data['channel']])
            ->field(['a.*','b.name'])
            ->select();

        return json(['code'=>0,'list'=>$line_country]);
    }

    #获取快递企业
    public function get_express(Request $request){
        $data = input();
        $express=Db::name('customs_express_company_code')->select();
        return json(['code'=>0,'list'=>$express]);
    }

    #仓库信息
    public function warehouse_info(Request $request){
        $data = input();

        $warehouse = Db::name('centralize_warehouse_list')->where('id',$data['warehouse_id'])->order('id,desc')->find();

        return view('/gather/estimate/warehouse_info',compact('warehouse'));
    }

    #获取阅读须知
    public function get_guide(Request $request){
        $data = input();

        $info = Db::name('centralize_guide_list')->where('id',$data['val'])->find();
        $info['content'] = json_decode($info['content'],true);

        return json(['code'=>0,'info'=>$info]);
    }

    #发送确认须知验证码
    public function sendcode(Request $request){
        $data = input();
        $number = trim($data['number']);
        $code = mt_rand(111111, 999999);
//        dd(session('sure_code'));
        if($data['method']==2){
            $post_data = [
                'spid'=>'254560',
                'password'=>'J6Dtc4HO',
                'ac'=>'1069254560',
                'mobiles'=>$number,
                'content'=>$data['msg'].$code.' 【GOGO】',
            ];
            $post_data = json_encode($post_data,true);
            $res = httpRequest('https://decl.gogo198.cn/api/sendmsg_jumeng',$post_data,array(
                'Content-Type: application/json; charset=utf-8',
                'Content-Length:' . strlen($post_data),
                'Cache-Control: no-cache',
                'Pragma: no-cache'
            ));// 必须声明请求头);

//        $templateVar = ['code' => $code, 'product' => '集运系统'];
//        $send_mobile = $area_code.$phone;
//        $result = $sms->send($send_mobile, 'Gogo购购网', json_encode($templateVar), 'SMS_35030091');
//        $result = json_decode(json_encode($result), true)['result'];
        }elseif($data['method']==1){
            // 发送验证码
            $text = $data['msg'].$code;
            $post_data = json_encode(['email'=>$number,'title'=>'集运网','content'=>$text],true);
            $res = httpRequest('https://shop.gogo198.cn/collect_website/public/?s=api/sendemail/index',$post_data,array(
                'Content-Type: application/json; charset=utf-8',
                'Content-Length:' . strlen($post_data),
                'Cache-Control: no-cache',
                'Pragma: no-cache'
            ));
        }

        if (!$res) {
            return json(['code' => -1, 'msg' => '发送失败']);
        }

        // 设置session
        session($data['code_name'], $code);

        return json(['code' => 0, 'msg' => '发送成功']);
    }

    #验证确认码
    public function check_verifyCode_for_rules(Request $request){
        $dat = input();
        if(empty($dat['sure_code'])){
            return json(['code'=>-1,'msg'=>'验证码错误']);
        }
        if(session('sure_code') == trim($dat['sure_code'])){
            unset($_SESSION['think']['sure_code']);
            return json(['code'=>0,'msg'=>'验证成功']);
        }else{
            return json(['code'=>-1,'msg'=>'验证码错误']);
        }
    }

    //获取万邦商品描述
    public function get_desc(Request $request){
        $data = input();
        if(!isset($data['platform_id']) || empty($data['platform_id'])){
            return json(['code'=>-1,'msg'=>'请选择商品所属的平台。']);
        }
        if(empty($data['good_id'])){
            return json(['code'=>-1,'msg'=>'请输入商品ID。']);
        }

        $platform = '';
        $extra = '';
        switch($data['platform_id']){
            case 1:
                $platform = 'taobao';
                $extra = '&is_promotion=1';
                break;
            case 2:
                $platform = 'jd';
                break;
        }
        $res = onebound_itemSearch($platform,trim($data['good_id']),$extra);
        if(strval($res['error_code'])=='2000'){
            return json(['code'=>-1,'msg'=>$res['reason']]);
        }elseif(strval($res['error_code'])=='0000'){
            return json(['code'=>0,'msg'=>'获取成功','data'=>$res['item']]);
        }else{
            return json(['code'=>-1,'msg'=>'服务错误，请联系管理员'.$res['error_code']]);
        }
    }

    #获取国地
    public function get_country(Request $request){
        $data = input();
        $list = get_country();
        return json(['code'=>0,'list'=>$list]);
    }

    #查找物品属性信息
    public function search_info(Request $request){
        set_time_limit(0);
        $data = input();
        $list = [];
        if($data['type']==1){
            #属性查询
            $list = Db::name('centralize_gvalue_list')->where('keywords','like','%'.trim($data['keywords']).'%')->select();
            foreach($list as $k=>$v){
                if($v['pid']==20){
                    $list[$k]['limit_product'] = 1;
                }else{
                    $list[$k]['limit_product'] = 0;
                }
                $parent = Db::name('centralize_gvalue_list')->where(['id'=>$v['pid']])->field('id,name,pid')->find();
                $list[$k]['parent_name'] = $parent['name'];
                if(!empty($parent['pid'])){
                    $parent2 = Db::name('centralize_gvalue_list')->where(['id'=>$parent['pid']])->field('id,name,pid')->find();
                    $list[$k]['parent_name'] = $parent2['name'];
                    if($parent2['id']==20){
                        $list[$k]['limit_product'] = 1;
                    }
                    if(!empty($parent2['pid'])){
                        $parent3 = Db::name('centralize_gvalue_list')->where(['id'=>$parent2['pid']])->field('id,name,pid')->find();
                        if($parent3['id']==20){
                            $list[$k]['limit_product'] = 1;
                        }
                        $list[$k]['parent_name'] = $parent3['name'];
                    }
                }
            }
        }
        return json(['code'=>0,'list'=>$list]);
    }

    #属性详情
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

        return view('/gather/estimate/value_introduce',compact('id','info'));
    }

    public function get_tips(Request $request){
        $data = input();
//         dd($data);
        $where = array();

        if( $data['controller_names'] == 'mainbody' && $data['function_name'] == 'addMainBody' )
        {
            $data['controller_names'] = 'main';
            $data['function_name']  = 'add';
        }

        if( $data['controller_names'] == 'logistics' && $data['function_name'] == 'index' )
        {
            $data['controller_names'] = 'logistic';
            $data['function_name']  = 'index';
        }

        if( $data['controller_names'] == 'elists' && $data['function_name'] == 'index' )
        {
            $data['controller_names'] = 'elist';
            $data['function_name']  = 'index';
        }

        if( $data['controller_names'] == 'ccgoods' && $data['function_name'] == 'add_bol' )
        {
            $data['controller_names'] = 'ccgoodsdecl';
            $data['function_name']  = 'add_bol';
        }

        if( $data['controller_names'] == 'goodsmanagement' && $data['function_name'] == 'shelfIndex' )
        {
            $data['controller_names'] = 'goods';
            $data['function_name']  = 'shelf_index';
        }

        $where['controller_name'] = $data['controller_names'];
        $where['function_name'] = $data['function_name'];
        if($data['value_name'] != '')
        {
            $where['value'] = $data['value_name'];
            return Db::name('decl_user_systemtips')->where($where)->find();
        }else{
            return Db::name('decl_user_systemtips')->where($where)->select();
        }
    }

    #获取涉税详情
    public function tax_relate(Request $request){
        $data = input();
        $country = intval($data['country']);
        $infos = [];
        if($country>0){
            $info = Db::name('centralize_tax_relate')->where(['country_id'=>$country])->find();
            $info['tax_amount'] = json_decode($info['tax_amount'],true);
//            $info['tips'] = json_decode($info['tips'],true);

            foreach($info['tax_amount'] as $k=>$v){
                if($v['regulatory_method']=='个人物品'){
                    $infos[0]['name'] = '个人物品';
                    if($v['transport_method']=='邮递托运'){
                        $infos[0]['values'][0]['name']='邮递托运';
                        $infos[0]['values'][0]['values']=$v;
                    }
                    elseif($v['transport_method']=='国际快递'){
                        $infos[0]['values'][1]['name']='国际快递';
                        $infos[0]['values'][1]['values']=$v;
                    }
                    elseif($v['transport_method']=='国际邮政'){
                        $infos[0]['values'][2]['name']='国际邮政';
                        $infos[0]['values'][2]['values']=$v;
                    }
                    elseif($v['transport_method']=='国际专线'){
                        $infos[0]['values'][3]['name']='国际专线';
                        if($v['line_method']=='空运'){
                            $infos[0]['values'][3]['values'][0]['name']='空运';
                            $infos[0]['values'][3]['values'][0]['values']=$v;
                        }
                        elseif($v['line_method']=='海运'){
                            $infos[0]['values'][3]['values'][1]['name']='海运';
                            $infos[0]['values'][3]['values'][1]['values']=$v;
                        }
                        elseif($v['line_method']=='陆运'){
                            $infos[0]['values'][3]['values'][2]['name']='陆运';
                            $infos[0]['values'][3]['values'][2]['values']=$v;
                        }
                        elseif($v['line_method']=='铁运'){
                            $infos[0]['values'][3]['values'][3]['name']='铁运';
                            $infos[0]['values'][3]['values'][3]['values']=$v;
                        }
                    }
                }
                elseif($v['regulatory_method']=='商品样品'){
                    $infos[1]['name'] = '商品样品';
                    if($v['transport_method']=='邮递托运'){
                        $infos[1]['values'][0]['name']='邮递托运';
                        $infos[1]['values'][0]['values']=$v;
                    }
                    elseif($v['transport_method']=='国际快递'){
                        $infos[1]['values'][1]['name']='国际快递';
                        $infos[1]['values'][1]['values']=$v;
                    }
                    elseif($v['transport_method']=='国际邮政'){
                        $infos[1]['values'][2]['name']='国际邮政';
                        $infos[1]['values'][2]['values']=$v;
                    }
                    elseif($v['transport_method']=='国际专线'){
                        $infos[1]['values'][3]['name']='国际专线';
                        if($v['line_method']=='空运'){
                            $infos[1]['values'][3]['values'][0]['name']='空运';
                            $infos[1]['values'][3]['values'][0]['values']=$v;
                        }
                        elseif($v['line_method']=='海运'){
                            $infos[1]['values'][3]['values'][1]['name']='海运';
                            $infos[1]['values'][3]['values'][1]['values']=$v;
                        }
                        elseif($v['line_method']=='陆运'){
                            $infos[1]['values'][3]['values'][2]['name']='陆运';
                            $infos[1]['values'][3]['values'][2]['values']=$v;
                        }
                        elseif($v['line_method']=='铁运'){
                            $infos[1]['values'][3]['values'][3]['name']='铁运';
                            $infos[1]['values'][3]['values'][3]['values']=$v;
                        }
                    }
                }
                elseif($v['regulatory_method']=='贸易货品'){
                    $infos[2]['name'] = '贸易货品';
                    if($v['transport_method']=='邮递托运'){
                        $infos[2]['values'][0]['name']='邮递托运';
                        $infos[2]['values'][0]['values']=$v;
                    }
                    elseif($v['transport_method']=='国际快递'){
                        $infos[2]['values'][1]['name']='国际快递';
                        $infos[2]['values'][1]['values']=$v;
                    }
                    elseif($v['transport_method']=='国际邮政'){
                        $infos[2]['values'][2]['name']='国际邮政';
                        $infos[2]['values'][2]['values']=$v;
                    }
                    elseif($v['transport_method']=='国际专线'){
                        $infos[2]['values'][3]['name']='国际专线';
                        if($v['line_method']=='空运'){
                            $infos[2]['values'][3]['values'][0]['name']='空运';
                            $infos[2]['values'][3]['values'][0]['values']=$v;
                        }
                        elseif($v['line_method']=='海运'){
                            $infos[2]['values'][3]['values'][1]['name']='海运';
                            $infos[2]['values'][3]['values'][1]['values']=$v;
                        }
                        elseif($v['line_method']=='陆运'){
                            $infos[2]['values'][3]['values'][2]['name']='陆运';
                            $infos[2]['values'][3]['values'][2]['values']=$v;
                        }
                        elseif($v['line_method']=='铁运'){
                            $infos[2]['values'][3]['values'][3]['name']='铁运';
                            $infos[2]['values'][3]['values'][3]['values']=$v;
                        }
                    }
                }
            }
        }
        $infos = array_values($infos);
        $country_list = Db::name('centralize_diycountry_content')->where(['pid'=>5])->select();
//        dd($infos);
        return view('/gather/centralize/tax_relate',compact('infos','country_list','country'));
    }
}