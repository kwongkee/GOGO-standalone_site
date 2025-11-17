<?php
namespace app\index\controller;

use think\Request;
use think\Db;
use think\Controller;
use app\index\model\Parceltask;
use think\Log;

class Gather
{
    #文章
    public function article(Request $request){
        $dat = input();
        if ($request->isAjax()) {

        } else {

            return view('/gather/estimate/article', compact(''));
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
}