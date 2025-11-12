<?php

namespace app\api\controller;

use think\Controller;
use think\Db;
use think\Request;

header('Access-Control-Allow-Origin: *'); //设置http://www.baidu.com允许跨域访问
header('Access-Control-Allow-Headers: X-Requested-With,X_Requested_With'); //设置允许的跨域header

class Authlogin extends Controller
{
    #去auth0授权登入
    public function goto_login(Request $request){
        $data = input();

        header('Location: https://gogo198.us.auth0.com/authorize?response_type=code&client_id=3LuZWceTu0CTzV5z4VBXfDWMaEE3yIVF&scope=openid%20profile%20email&redirect_uri=https://www.gogo198.net/?s=api/authorization_callback&web_origin=https://www.gogo198.net'.urlencode('/?auth_id='.intval($data['id'])));
    }

    #用户授权回调
    public function authorization_callback(Request $request)
    {
        $data = input();

        $code = isset($data['code'])?trim($data['code']):'';
        $state = isset($data['state'])?trim($data['state']):'';
//        $token = trim($data['token']);
//        if($state==$token){}

        $token = get_auth0_token(['code'=>$code,'callback'=>'https://www.gogo198.net/?s=api/authorization_callback']);
        $token = json_decode($token,true);

        if(isset($token['token_type'])){
            if($token['token_type']=='Bearer'){
                $res = get_auth0_api(['accessToken'=>$token['access_token']]);

                if(!empty($res['account'])){
                    //                    session('account',$res['account']);

                    #查看当前设备信息和ip在表中是否有相同
                    $ip = $_SERVER['REMOTE_ADDR'];
                    $device = $_SERVER['HTTP_USER_AGENT'];
                    $last_login = Db::name('website_login_log')->where(['account'=>$res['account']['email'],'ip'=>$ip,'device'=>$device,'status'=>0])->order('id desc')->find();
                    Db::name('website_login_log')->where(['id'=>$last_login['id'],'ip'=>$ip,'device'=>$device])->update(['status'=>1]);

                    #跳转授权登录结果页
                    header('Location: //www.gogo198.net/?s=index/authlogin_result');
                }

//                if(isset($data['web_origin'])){
//                    header('Location: '.$data['web_origin']);
//                }else{
//                    header('Location: https://www.gogo198.net');
//                }
            }
        }else{
            header("Location: /");
        }
    }

    #获取token回调
    public function token_callback(Request $request){
        $data = input();

    }

    #auth0注销回调
    public function protected_resource(Request $request){
        $data = input();
        if(isset($data['redirect_url'])){
            header('Location: '.$data['redirect_url']);
        }else{
            header('Location: /?s=index/customer_login');
        }
    }

    #auth0登录成功后，用户信息回调
    public function userinfo_callback(Request $request){
        $data = input();
        if($data['email']!='j+smith@example.com'){
            $account = Db::name('website_user')->where(['email'=>$data['email']])->find();
            if(empty($account)){
                $postData = [
                    'phone'=>isset($data['phone_number'])?$data['phone_number']:'',
                    'email'=>isset($data['email'])?$data['email']:'',
                    'realname'=>isset($data['username'])?$data['username']:'',
                    'auth0_info'=>json_encode($data,true)
                ];

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

                if($account_id>0 && isset($data['email'])){
                    $account = Db::name('website_user')->where('id',$account_id)->find();
                    httpRequest('https://shop.gogo198.cn/collect_website/public/?s=/api/sendemail/index',['email'=>$data['email'],'title'=>'注册成功','content'=>'<p>注册成功电邮内容：</p><p>尊敬的GoFriend：</p><p>欢迎使用购购网服务，感谢您注册购购网账户。</p><p>Welcome to use the Gogo198 Service. Thank you for registering an Gogo account.</p><br/><p>以下是您的用户名，请保留此电子邮件，日后您可能需要参考它。</p><p>The following is your username. Please keep this email as you may need to refer to it in the future.</p><br/><p>用户名 Username：'.$account['nickname'].'</p><br/><p>现在您可以登录您的账户</p><p>Now you can log in to your account：</p><p>https://www.gogo198.net</p><br/><p>我们很乐意倾听用户的意见！如果您有任何意见或问题，请电邮至：</p><p>We are very willing to listen to the opinions of users! If you have any opinions or problems, please email to:</p><p>198@gogo198.net</p><br/><p>谢谢 Thank you!</p><br/><p>购购网 | Gogo</p>']);
                }
            }
        }


//        Db::name('website_user')->where(['id'=>1])->update(['reg_file'=>json_encode($data,true)]);

    }

    #facebook回调
    public function facebook_callback(Request $request){
        $data = input();

        Db::name('website_user')->where(['id'=>1])->update(['reg_file'=>json_encode($data,true)]);
        dd($data);
    }

    #auth0登录成功，通过access_token获取返回值（废弃）
    public function auto_login(Request $request){
        $data = input();

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://gogo198.us.auth0.com/userinfo',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => [
                "authorization: Bearer ".$data['accessToken'],
                "content-type: application/json"
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            Db::name('website_user')->where(['id'=>1])->update(['auth0_info'=>json_encode($response,true)]);
            header('Location: https://www.gogo198.net?id=1');
        }
    }
}