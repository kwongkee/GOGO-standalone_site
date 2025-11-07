<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\Db;

// 应用公共文件
function dd($res){
    print_r($res);die;
}

//请求
function httpRequest($url,$data,$head=[])
{
    $ch=curl_init();
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
    curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
    curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);
    curl_setopt($ch,CURLOPT_POST,1);
    curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
    curl_setopt($ch, CURLOPT_HTTPHEADER,$head);
    $output=curl_exec($ch);
    curl_close($ch);
    return $output;
}

function httpRequest2($url,$data,$head=[])
{
    $ch=curl_init();
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
//    curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
//    curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $head);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    $output=curl_exec($ch);
    curl_close($ch);
    return $output;
}

function makeJsonRequest($url, $jsonPayload) {
    // 将请求数据转换为JSON字符串
//    $jsonPayload = json_encode($requestData);
    header("Access-Control-Allow-Origin:*");

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => $jsonPayload,
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json;charset=utf-8",
            "content-type: application/json",
            "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36"
        ],
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
        echo "cURL Error #:" . $err;
    } else {
        return $response;
    }
}


function http_post($url, $data_string)
{
    $request = new HttpRequest();
    $request->setUrl($url);
    $request->setMethod(HTTP_METH_POST);

    $request->setHeaders([
        'Content-Type' => 'application/json;charset=utf-8',
        'content-type' => 'application/json'
    ]);

    $request->setBody($data_string);

    try {
        $response = $request->send();

        echo $response->getBody();
    } catch (HttpException $ex) {
        echo $ex;
    }
}


function send($url, $jsonObject, $encoding) {
    $body = "";

    // 创建httpclient对象
    $client = new \GuzzleHttp\Client();

    // 创建post方式请求对象
    $response = $client->post($url, [
        'headers' => [
            'Content-Type' => 'application/json',
            'User-Agent' => 'Mozilla/4.0 (compatible; MSIE 5.0; Windows NT; DigExt)'
        ],
        'body' => $jsonObject->toString(),
        'verify' => false // 可选：忽略 SSL 证书验证
    ]);

    // 获取响应内容
    $body = $response->getBody()->getContents();

    return $body;
}

//格兰德签名
function sign($privateKey='', $requestData) {
    #私钥
    $privateKey = "MIICdwIBADANBgkqhkiG9w0BAQEFAASCAmEwggJdAgEAAoGBAMNsKfXOWJzAhN7Wv9E9FAitlNfYVXSzj+FU5gYLKPrfn2Q2BGbHngujWqJugvUophiLhYQ1ILlXoQfes9BmdaLREs5oMlrdBRspUFMVAKlaLZPSJSH+fKFTUDovsLkegNMCAxYPRD9i8Ix/d/HEMRPweKGeWnQmVhgfdP00X4pBAgMBAAECgYEAwRvMZvrN87AGXJHHIMODXYPx1k0PPPAHWLplR0mV6Do8LnF2bf4JviRg9qvPuuS9JPoSKO+684uVM8qs0128FTqqY+3PkXx13dnZOHWSNVdcTvTrCTIjqf0lFKqukWYrBmT2bH58C1qpnlgCqSL3Ob/zeFB1I+L25RRXsEYAoAECQQDpDbE4pBDUV0GbDitEKDTgaRr7fSy9OipWU7hIBsk7bZBJKyUQCECLO59Jdw5sRQhuP20ejkif28AC7Az88frBAkEA1qnznH6WMztRhLYuwxUfORwUvmiPNioD15c6fLHe0q40OfkKv0fY4hVeKlOIY/MYIkR+aJ6MEKHNhb2qONbvgQJBALE5LVSCRx4CgKxV2QcKgDNLGi62oMgBLGLbZV64clyT084gVh3b+KEopNesBrbExEV6TBOZZZbS+DAAq1vK88ECQHecKQ6pBj4zrj95V+MBkO08dV6HWkz+6jcln8Q9RAA2awlmeBOPEA0hhN+mvdeba3Yknh7jQP4/egosXX3gXYECQC7/jqFqj1dr9lCwFc7P9YWCO+f4mm/DHWVO+7YuVIDaSCnc0dehKQVZAUOmTQYMdZpGPU1pGTrGsIUNvzwoRIg=";
    $privateKey = "-----BEGIN RSA PRIVATE KEY-----\n".
        wordwrap($privateKey, 64, "\n", true) .
        "\n-----END RSA PRIVATE KEY-----";

    $signStr = null;
    try {
        $signature = openssl_get_privatekey($privateKey);
        // 计算请求数据的摘要
        $digest = hash('sha256', $requestData, true);

        // 使用私钥进行签名
        if (openssl_sign($digest, $signatureBytes, $signature, OPENSSL_ALGO_SHA256)) {
            // 将签名结果进行 Base64 编码
            $signStr = base64_encode($signatureBytes);
        } else {
            echo "签名失败";
        }
//            // 使用私钥进行签名
//            openssl_sign($digest, $signatureBytes, $signature);
//            // 将签名结果进行 Base64 编码
//            $signStr = base64_encode($signatureBytes);
    } catch (Exception $e) {
        echo $e->getMessage();
    }
    return $signStr;
}

//application请求
function api_request($url,$postData){
    // $curl = curl_init();
    // curl_setopt_array($curl, array(
    //     CURLOPT_URL => $url,
    //     CURLOPT_RETURNTRANSFER => true,
    //     CURLOPT_ENCODING => "",
    //     CURLOPT_MAXREDIRS => 10,
    //     CURLOPT_TIMEOUT => 30,
    //     CURLOPT_CUSTOMREQUEST => "POST",
    //     CURLOPT_POSTFIELDS => http_build_query($postData),
    //     CURLOPT_HTTPHEADER => array(
    //         "Cache-Control: no-cache",
    //         "Content-Type: application/json",
    //     ),
    // ));

    // $response = curl_exec($curl);
    // curl_close($curl);
    $ch = curl_init();  // 初始化 CURL
    curl_setopt($ch, CURLOPT_URL, $url);  // 设置请求的 URL
    curl_setopt($ch, CURLOPT_POST, true);  // 设置为 POST 请求
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));  // 设置 POST 请求的表单数据
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);  // 返回结果而不是直接输出
    
    $response = curl_exec($ch);  // 执行请求并获取响应
    curl_close($ch);  // 关闭 CURL

    return $response;
}

#验证手机号码
function verifyTel($tel)
{
    if(preg_match("/^1[34578]\d{9}$/",$tel)){
        return true;
    }
    return false;
}

function is_mobile2()
{
//    return false;//20240228改
    if (isset($_SERVER['HTTP_VIA']) && stristr($_SERVER['HTTP_VIA'], "wap")) {
        return true;
    } elseif (isset($_SERVER['HTTP_ACCEPT']) && strpos(strtoupper($_SERVER['HTTP_ACCEPT']), "VND.WAP.WML")) {
        return true;
    } elseif (isset($_SERVER['HTTP_X_WAP_PROFILE']) || isset($_SERVER['HTTP_PROFILE'])) {
        return true;
    } elseif (isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/(blackberry|configuration\/cldc|hp |hp-|htc |htc_|htc-|iemobile|kindle|midp|mmp|motorola|mobile|nokia|opera mini|opera |Googlebot-Mobile|YahooSeeker\/M1A1-R2D2|android|iphone|ipod|mobi|palm|palmos|pocket|portalmmm|ppc;|smartphone|sonyericsson|sqh|spv|symbian|treo|up.browser|up.link|vodafone|windows ce|xda |xda_)/i', $_SERVER['HTTP_USER_AGENT'])) {
        return true;
    } else {
        return false;
    }
}

//生成二维码
function generate_code($name,$url,$folder){
    //链接生成二维码
    $errorCorrectionLevel = 'L';//错误等级，忽略
    $matrixPointSize = 4;
    require_once $_SERVER['DOCUMENT_ROOT'].'/../extend/lib/phpqrcode.php';
    $path = $folder; //储存的地方
    if (!is_dir($path)) {
        mkdirs($path); //创建文件夹
    }
    $infourl = $url;
    $filename =  $path.$name.'.png'; //图片文件
    QRcode::png($infourl, $filename, $errorCorrectionLevel, $matrixPointSize, 2); //生成图片

//    $filename = str_replace('/www/wwwroot/gogo','https://shop.gogo198.cn',$filename);
    $logo = 'http://shop.gogo198.cn/collect_website/public/logo.png';//准备好的logo图片
    $QR = $filename;//已经生成的原始二维码图
    if ($logo !== FALSE) {

        $QR = imagecreatefromstring(file_get_contents($QR));
        $logo = imagecreatefromstring(file_get_contents($logo));
        $QR_width = imagesx($QR);//二维码图片宽度
        $QR_height = imagesy($QR);//二维码图片高度
        $logo_width = imagesx($logo);//logo图片宽度
        $logo_height = imagesy($logo);//logo图片高度
        $logo_qr_width = $QR_width / 5; //logo图片在二维码图片中宽度大小
        $scale = $logo_width/$logo_qr_width;
        $logo_qr_height = $logo_height/$scale; //logo图片在二维码图片中高度大小
        $from_width = ($QR_width - $logo_qr_width) / 2;
        //重新组合图片并调整大小
        imagecopyresampled($QR, $logo, $from_width, $from_width, 0, 0, $logo_qr_width,
            $logo_qr_height, $logo_width, $logo_height);
    }

    imagepng($QR,$filename); // 保存最终生成的二维码到本地

    //直接输出图片到浏览器
    Header("Content-type: image/png");

    $qrcode = str_replace('/www/wwwroot/default/company/dedcms/new1_web/public','https://www.gogo198.net',$filename);
    return $qrcode;
}

/**
 * 万邦-按关键字搜索淘宝/1688的商品
 */
function onebound_itemSearch($platform,$num_iid,$extra=''){
//    https://api-gw.onebound.cn/taobao/item_get_desc/?key=t3809703680&&num_iid=652874751412&cache=no&&lang=zh-CN&secret=20220324
    $url = "https://api-gw.onebound.cn/".$platform."/item_get/?key=t3809703680&secret=20220324&api_name=item_get&cache=no&&lang=zh-CN&num_iid=".$num_iid.$extra;
//    $res = onebound_post($url,'','GET');
    $res = file_get_contents($url);
    return json_decode($res,true);

}

//微信js分享功能
function weixin_share($data){
    $time = time();
    $appid = 'wx76d541cc3e471aeb';
    $secret = '3e3d16ccb63672a059d387e43ec67c95';
    if($time > (session('expires_time') + 3600)) {
        #获取access_token
//        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appid."&secret=".$secret;
//        $res = file_get_contents($url);
        $url = "https://api.weixin.qq.com/cgi-bin/stable_token";
        $res = httpRequest($url, json_encode(['grant_type'=>'client_credential','appid'=>$appid,'secret'=>$secret],true));
        $result = json_decode($res, true);
        session('access_token', $result["access_token"]);
        session('expires_time', $time);
    }
    if($time > (session('expires_tocket_time') + 3600)) {
        $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=" . session('access_token') . "&type=jsapi";
        $res = file_get_contents($url);
        $result = json_decode($res, true);
        session('ticket', $result);
        session('expires_tocket_time', $time);
    }
    if(isset(session('ticket')['ticket'])){
        $jsapiTicket = session('ticket')['ticket'];
    }else{
        $jsapiTicket = '';
    }
    $timestamp = $time;
    $url = $data['url_this'];
    $nonceStr = createNonceStr();
    $string =  "jsapi_ticket=".$jsapiTicket."&noncestr=".$nonceStr."&timestamp=".$timestamp."&url=".$url;
    $signature = sha1($string);
    
    $signPackage = array(
        "appId" => $appid,
        "nonceStr" => $nonceStr,
        "timestamp" => $timestamp,
        "url" => $url,
        "signature" => $signature,
        "rawString" => $string,
        "desc" => $data['desc'],
        "name" => $data['name']
    );
    return $signPackage;
}

function createNonceStr($length = 16)
{
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    $str = "";
    for ($i = 0; $i < $length; $i++) {
        $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
    }
    return $str;
}

//获取各大州下的国地
function get_country(){
    $list2 = Db::name('centralize_diycountry_content')->where(['pid'=>9])->select();
    $list = [];
    foreach($list2 as $k=>$v){
        $list[$k]['name'] = $v['param1'];
        $list[$k]['value'] = $v['id'];
        $list[$k]['children'] = Db::name('centralize_diycountry_content')->where(['state_id'=>$v['id']])->select();
        foreach($list[$k]['children'] as $k2=>$v2){
            $list[$k]['children'][$k2]['name'] = $v2['param2'];
            $list[$k]['children'][$k2]['value'] = $v2['id'];
            $list[$k]['children'][$k2]['children'] = '';
        }
    }
    return json_encode($list,true);
}

function isMobile() {
    // 获取用户代理字符串
    $user_agent = $_SERVER['HTTP_USER_AGENT'];

    // 定义移动设备正则表达式
    $mobile_regex = "/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i";

    // 使用preg_match函数检查用户代理字符串是否匹配移动设备正则表达式
    if (preg_match($mobile_regex, $user_agent)) {
        return true; // 是移动设备
    } else {
        return false; // 不是移动设备
    }
}

//B端商家获取是否有下级功能
function getUnFun($fun_id,$company_id){
    $is_have = Db::name('website_user_company')->where(['id'=>$company_id])->whereRaw('FIND_IN_SET('.$fun_id.',authList)')->find();
    return empty($is_have)?0:1;
}

//微信小程序、公众号、邮箱、手机通知
function common_notice($data,$msg){
    if(!empty($data['sns_openid'])){
        #小程序
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=wx6d1af256d76896ba&secret=d19a96d909c1a167c12bb899d0c10da6";
        $res = file_get_contents($url);
        $result = json_decode($res, true);

        $post2 = json_encode([
            'template_id'=>'GRa2BGkGrqU8g7IgMAVh6vx2iDD08uJSdK316TINQ7s',
            'page'=>$msg['page'],
            'touser' =>$data['sns_openid'],
            'data'=>['thing1'=>['value'=>$msg['taskname']],'phrase2'=>['value'=>$msg['opera']],'time4'=>['value'=>date('Y年m月d日 H:i')]],
            'miniprogram_state'=>'formal',//developer为开发版；trial为体验版；formal为正式版
            'lang'=>'zh_CN',
        ]);
        $resu = httpRequest('https://api.weixin.qq.com/cgi-bin/message/subscribe/send?access_token='.$result['access_token'], $post2,['Content-Type:application/json'],1);
    }elseif(!empty($data['openid'])){
        #微信
        $post = json_encode([
            'call'=>'confirmCollectionNotice',
            'find' =>$msg['msg']."请打开查看！",
            'keyword1' => $msg['msg']."请打开查看！",
            'keyword2' => $msg['opera'],
            'keyword3' => date('Y-m-d H:i:s',time()),
            'remark' => '点击查看详情',
            'url' => $msg['url'],
            'openid' => $data['openid'],
            'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
        ]);

        httpRequest('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);
    }elseif(!empty($data['email'])){
        $title = $msg['msg']."请打开查看！";
        $post_data = json_encode(['email'=>$data['email'],'title'=>$title,'content'=>$msg['url']],true);
        $res = httpRequest('https://admin.gogo198.cn/collect_website/public/?s=api/sendemail/index',$post_data,array(
            'Content-Type: application/json; charset=utf-8',
            'Content-Length:' . strlen($post_data),
            'Cache-Control: no-cache',
            'Pragma: no-cache'
        ));
    }elseif(!empty($data['phone'])){
        $post_data = [
            'spid'=>'254560',
            'password'=>'J6Dtc4HO',
            'ac'=>'1069254560',
            'mobiles'=>$data['phone'],
            'content'=>$msg['msg'].'请打开链接（'.$msg['url'].'）查看！【GOGO】',
        ];
        $post_data = json_encode($post_data,true);
        httpRequest('https://decl.gogo198.cn/api/sendmsg_jumeng',$post_data,array(
            'Content-Type: application/json; charset=utf-8',
            'Content-Length:' . strlen($post_data),
            'Cache-Control: no-cache',
            'Pragma: no-cache'
        ));
    }
}
//客服通知：公众号、邮箱、手机通知
function common_notice2($data,$msg){
    if(!empty($data['openid'])){
        #微信
        $post = json_encode([
            'call'=>'workorderToMember',
            'thing20' => "你有一条消息处理，请打开查看！",
            'time48' => date('Y年m月d日 H:i:s',time()),
            'url' => $msg['url'],
            'openid' => $data['openid'],
            'temp_id' => 'HLTkX1DshQnHoJpHLaGTjQkygsZVyFDIn7luT6hcjOY'
        ]);

        $res = httpRequest('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);

        #记录平台通知
        platform_notice(['type'=>1,'msg'=>$msg['msg'],'url'=>$msg['url'],'uid'=>$data['id'],'email'=>'']);
    }elseif(!empty($data['email'])){
        $post_data = json_encode(['email'=>$data['email'],'title'=>$msg['title'],'content'=>$msg['msg']],true);
        $res = httpRequest('https://shop.gogo198.cn/collect_website/public/?s=api/sendemail/index',$post_data,array(
            'Content-Type: application/json; charset=utf-8',
            'Content-Length:' . strlen($post_data),
            'Cache-Control: no-cache',
            'Pragma: no-cache'
        ));

        #记录平台通知
        platform_notice(['type'=>2,'msg'=>$msg['msg'],'url'=>$msg['url'],'uid'=>$data['id'],'email'=>$data['email']]);
    }elseif(!empty($data['phone'])){
        $post_data = [
            'spid'=>'254560',
            'password'=>'J6Dtc4HO',
            'ac'=>'1069254560',
            'mobiles'=>$data['phone'],
            'content'=>$msg['msg'].'请打开链接（'.$msg['url'].'）查看！【GOGO】',
        ];
        $post_data = json_encode($post_data,true);
        httpRequest('https://decl.gogo198.cn/api/sendmsg_jumeng',$post_data,array(
            'Content-Type: application/json; charset=utf-8',
            'Content-Length:' . strlen($post_data),
            'Cache-Control: no-cache',
            'Pragma: no-cache'
        ));
    }
}
//记录通知
function platform_notice($data){
    Db::name('platform_notice_list')->insert([
        'type'=>$data['type'],
        'uid'=>$data['uid'],
        'email'=>$data['email'],
        'msg'=>$data['msg'],
        'url'=>$data['url'],
        'createtime'=>time()
    ]);
}
//议题结果
function topics_result($data){
    #1、查找该议题的参与组员
    $group_member = Db::name('decision_group_member')->where(['group_id'=>$data['group_id'],'status'=>1])->select();
    $group_member_num = count($group_member);
    if($group_member_num>0){
        #2、查找组员的选择
        $option = [];
        foreach($group_member as $k=>$v){
            $member_option = Db::name('decision_topics_selected')->where(['topics_id'=>$data['id'],'uid'=>$v['user_id']])->field('option_id')->find()['option_id'];
            if(!empty($member_option)){
                array_push($option,$member_option);
            }
        }

        if(!empty($option)) {
            #3、判断选择中出现最多的值和次数
            // 计算每个元素出现的次数
            $counts = array_count_values($option);
            // 找到出现次数最多的元素的值
            $maxCount = max($counts);#最多的值一共出现n次
//            $mostFrequentValue = array_search($maxCount, $counts);#最多的值为X

            #4、用出现最多的次数÷组内人数，得出是否等于通过结果的方式
            #1一致决议=100%，2多数决议≥60%，3半数决议≥50%，4少数决议<50%
            $result = $maxCount / $group_member_num;
            if ($data['pass_method'] == 1) {
                if ($result == 1) {
                    return 1;
                }
            } elseif ($data['pass_method'] == 2) {
                if ($result >= 0.6) {
                    return 1;
                }
            } elseif ($data['pass_method'] == 3) {
                if ($result >= 0.5) {
                    return 1;
                }
            } elseif ($data['pass_method'] == 4) {
                if ($result < 0.5) {
                    return 1;
                }
            }
        }
    }

    return 0;
}

/**
 * 创建国内结算订单
 * @param $id
 * @param $type 1=账单
 * @param $account 通知的账户
 */
function create_collection($id=0,$type=0,$account){
    if($type==1){
        //立即收款+付款依据（订单）+逾期天数（总后台配置）+逾期费用（总后台配置）
        $recon = Db::name('centralize_distr_recon')->where(['id'=>$id])->find();#账单
        $time = time();
        $ordersn = 'GP' . date('YmdH', $time) . str_pad(mt_rand(1, 999999), 6, '0',
                STR_PAD_LEFT) . substr(microtime(), 2, 6);
//        $payset = Db::name('website_business_payset')->where(['id'=>1])->find();
        $pay_term = $recon['pay_term'];//逾期天数
        $overdue = ( $pay_term * 86400 ) + $time;//逾期时间 付款期限*86400+发起时间

        //服务条款
        $gather = Db::name('centralize_parcel_order')->where(['id'=>$recon['gather_id']])->find();#集运订单
        $agent = Db::name('website_user')->where(['id'=>$recon['agent_id']])->find();#分销商
        $recon['body_content'] = json_decode($recon['body_content'],true);
        $totalmoney = $recon['money'];
        foreach($recon['body_content']['snum'] as $k=>$v){
//            if($recon['body_content']['currency'][$k]!=5){
//                $rate = Db::name('centralize_currency')->where(['id'=>$recon['body_content']['currency'][$k]])->find()['to_cny_rate'];
//                $calc_price = number_format($recon['body_content']['money'][$k]*$rate,2);
//                $totalmoney += $calc_price;
//                $service_info[$k] = $recon['body_content']['boxno'][$k].','.$recon['body_content']['true_grosswt'][$k].'（KG）'.','.'-';
//            }else{
                $service_info[$k] = $recon['body_content']['boxno'][$k].','.$recon['body_content']['true_grosswt'][$k].'（KG）'.','.'-';
//                $totalmoney += $recon['body_content']['money'][$k];
//            }
        }
        $service_info = json_encode($service_info,true);
        $pay_fee = $recon['overdue_rate']/100;//逾期费用
        $data = [
            'uniacid'=>3,
            'openid'=>$account['openid'],//接收的openid有可能是手机号或正常的openid
            'send_openid'=>$agent['openid'],
            'ordersn'=>$ordersn,
            'trade_price'=>$totalmoney,
            'trade_type'=>3,#服务
            'payer_name'=>$account['realname']==''?$account['phone']:$account['realname'],
            'payer_tel'=>$account['phone'],
            'pay_term'=>$pay_term,
            'pay_fee'=>$pay_fee,
            'overdue'=>$overdue,
            'trans_form'=>1,#立即收款
            'createtime'=>$time,
            'basic'=>2,#付款依据：订单
            'orderno'=>$gather['ordersn'],
            'orderurl'=>'https://www.gogo198.com/?s=gather/package_manage&miniprogram=1&manage=2&process1=19&process2=21&process3=21&oid='.$gather['id'].'&uid='.$account['id'],
//            'orderdemo'=>'https://www.gogo198.com/?s=gather/package_manage&manage=2&process1=19&process2=21&process3=21&oid='.$gather['id'].'&uid='.$account['id'],
            'service_info'=>$service_info
        ];

        $insertid = Db::name('customs_collection')->insertGetId($data);
        $msg_template['first'] = '你好！您有一笔来自［'.$agent['realname'].'］的［立即收款］付款请求，请点击消息支付，为确保您的资金安全，如对此支付请求有疑问，请暂缓支付并致电Gogo客服电话07578632991咨询或反馈，感谢您使用Gogo服务。';
        $data['basic'] = '订单号：'.$gather['ordersn'];
        $post = json_encode([
            'call'=>'collectionNotice',
            'first' =>$msg_template['first'],
            'keyword1' => $data['ordersn'],
            'keyword2' => date('Y-m-d H:i:s',$time),
            'keyword3' => 'CNY '.$data['trade_price'],
            'keyword4' => $data['basic'],
            'keyword5'=> date('Y-m-d H:i:s',$overdue),
            'remark' => 'Gogo在线收款服务为商户提供合规安全的即时、预约及定期收款通知与在线支付服务，如需了解，可回复”8“了解及与客服联系。',
            'url' => 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=member&p=custompayment&m=sz_yi&oid='.$insertid,
            'openid' => $account['openid'],
            'temp_id' => 'YU8Nczq9tyT8CNUyu9Lnyi0VcASZ4VBkEzTnB2adal4'
        ]);
        httpRequest('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);
        Db::name('customs_collection')->where(['id'=>$insertid])->update(['is_send'=>1]);
    }
}

/**
 * 创建集运订单
 * @param int $id
 * @param int $type 1=账单
 * @param $account 通知的账户
 */
function create_gatherorder($id=0,$type=0,$account){
    if($type==1){
//        dd($account);
        #1、账单信息
        $recon = Db::name('centralize_distr_recon')->where(['id'=>$id])->find();
        $recon['head_content'] = json_decode($recon['head_content'],true);
        $recon['body_content'] = json_decode($recon['body_content'],true);
        $line_id = 0;
        $country_id = 0;
        list($grosswt,$name,$mobile,$volumn,$line_type,$valueid) = ['','','','','',''];
        if($recon['pid']==1){
            #集运业务=1
//            $line_id = explode('area_',$recon['head_content'][8])[1];
            $line_id = $recon['head_content'][9];

            $country_id = $recon['head_content'][4];

//            $country_id = Db::name('centralize_adminstrative_area')->where(['id'=>$cid])->find()['country_id'];
            $name = $recon['head_content'][6];$mobile=$recon['head_content'][7];
            $grosswt = $recon['body_content']['total_grosswt'];
//            $volumn = $recon['head_content'][11][0].'*'.$recon['head_content'][11][1].'*'.$recon['head_content'][11][2];
            $line_type = '';#包裹货物类别
            $valueid = $recon['head_content'][8];
//            $valueid = explode('_',$recon['head_content'][8])[1];
        }


        $time = time();
        #订仓类型(直接转运T/集货转运G) + 会员编号后5位 + 订仓年度YYYY + 流水编号（本年度该订仓类型下的累计数）001-999
        $start = strtotime(date('Y-01-01 00:00:00'));$end = strtotime(date('Y-12-31 23:59:59'));
        $order_num = Db::name('centralize_parcel_order')->where(['prediction_id'=>1,'user_id'=>$account['id']])->whereBetween('createtime',[$start,$end],'AND')->count();
        $order_num = str_pad($order_num+1,3,'0',STR_PAD_LEFT);
        $ordersn = substr($account['custom_id'],-5) . date('Y') . $order_num;
        #生成订仓号-end

        #2、生成订仓单
//        $line = Db::name('centralize_line_country')
//            ->alias('a')
//            ->join('centralize_line_list b','b.id=a.pid')
//            ->where(['b.id'=>$line_id])
//            ->field('b.code,b.channel_id')
//            ->find();
        $line = Db::name('centralize_lines')->where(['id'=>$line_id])->find();
        $line['code'] = substr($line['code'],-2);

        #单个包裹
        $content = [
            'user_id'    => $account['id'],
            'agent_id'   => $account['agent_id'],
            #9+YY+（集货仓码）00+（入仓日期MMDD）+线路编码00+集运日期DD+终端（O、C、B）+（流程编码）00

            'ordersn'    => 'T' .$ordersn ,
            'prediction_id'=> 1,
            'country'=> $country_id,
            'channel'=> $line['channel_id'],
            'line_id'=> $line_id,
            'task_id'    => '',
            'sure_prediction'=>1,
            'createtime' => $time
        ];
        $orderid = Db::name('centralize_parcel_order')->insertGetId($content);
        #3、修改账单的集运订单为该id
        Db::name('centralize_distr_recon')->where(['id'=>$id])->update(['gather_id'=>$orderid]);

        foreach($recon['body_content']['snum'] as $k=>$v){
            #4、生成预报单
            $expressno = 'IN'.date('YmdHis');
            $insert_data = [
                'user_id'=>$account['id'],
                'orderid'=>$orderid,
                'express_id'=>0,
                'express_no'=>$expressno,
                #入仓信息
                'delivery_logistics'=>1,#物流到仓
                'delivery_method'=>3,
                'inwarehouse_date'=>date('Y年m月d日',$time),
                'contact_name'=>$name,
                'contact_mobile'=>$mobile,
                'grosswt'=>$recon['body_content']['true_grosswt'][$k],
                'volumn'=>$recon['body_content']['long'][$k].'*'.$recon['body_content']['width'][$k].'*'.$recon['body_content']['height'][$k],
                'line_type'=>$line_type,
                #状态
                'status2'=>0,#直接转运或集货转运都要先签收入库
                #创建时间
                'createtime'=>$time
            ];
            $package_id = Db::name('centralize_parcel_order_package')->insertGetId($insert_data);

            #5、生成货物表
            Db::name('centralize_parcel_order_goods')->insert([
                'user_id'=>$account['id'],
                'orderid'=>$orderid,
                'package_id'=>$package_id,
                #物品属性
                'valueid'=>$valueid,
                #物品描述
                'good_desc'=>'',
                #物品数量
                'good_num'=>'',
                #物品单位
                'good_unit'=>'',
                #物品币种
                'good_currency'=>'',
                #物品金额
                'good_price'=>'',
                #物品金额（等值美元）
                'goods_usdprice'=>'',
                #物品包装
                'good_package'=>'',
                #物品品牌类型
                'brand_type'=>'',
                'brand_name'=>'',
                #物品备注
                'good_remark'=>'',
                #创建时间
                'createtime'=>$time
            ]);

            #6、生成订单表
            Db::name('centralize_order_fee_log')->insert([
                'type'=>1,
                'ordersn'=>'G'.date('YmdHis'),
                'user_id'=>$account['id'],
                'orderid'=>$orderid,
                #包裹id
                'good_id'=>$package_id,
                'express_no'=>$expressno,
                'service_status'=>1,
                'order_status'=>0,
                'createtime'=>$time
            ]);
        }
    }
}

//注销账户
function sure_logout($uid){
    #1、先判断当前账户是否有账单/订单未付
    #1.1、商品订单
    $goods_order = Db::name('website_order_list')->where(['user_id'=>$uid])->select();
    foreach($goods_order as $k=>$v){
        #1.1.1、当前订单是否完成（待做）
        if(!empty($v['pay_id'])){
            $not_pay = Db::name('customs_collection')->where(['id'=>$v['pay_id'],'status'=>0])->find();
            if(!empty($not_pay)){
                return json(['code'=>-1,'msg'=>'您有账单未付，请完成后再操作']);
            }
        }
    }
    #1.2、集运订单
    $gather_order = Db::name('centralize_order_fee_log')->where(['user_id'=>$uid])->select();
    foreach($gather_order as $k=>$v){
        #1.2.1、当前订单是否完成（待做）
        if(!empty($v['orderid'])){
            $not_pay = Db::name('customs_collection')->where(['id'=>$v['orderid'],'status'=>0])->find();
            if(!empty($not_pay)){
                return json(['code'=>-1,'msg'=>'您有账单未付，请完成后再操作']);
            }
        }
    }


    #查询是否关联企业
    $company = Db::name('website_user_company')->where(['user_id'=>$uid,'status'=>0])->find();
    if(!empty($company)){
        #企业账户
        if(session('company.role')==1){
            return json(['code'=>-1,'msg'=>'请联系企业超级管理员注销关联']);
        }
    }else{
        #个人账户（确认注销）
        $user = Db::name('website_user')->where(['id'=>$uid])->find();
        Db::name('website_user')->where(['id'=>$uid])->delete();
        Db::name('centralize_user')->where(['gogo_id'=>$uid])->delete();
        Db::name('sz_yi_member')->where(['gogo_id'=>$uid])->delete();
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
        Db::connect($config)->name('user')->where(['gogo_id'=>$user['custom_id']])->delete();

        return json(['code'=>0,'msg'=>'注销成功，正在跳转...']);
    }
}

function platform_log($request){
    #日志记录
    $time = time();
    $content = '访客@@';
    if(session('account')!=''){
        $content = '用户【'.session('account')['custom_id'].'】@@';
    }

    // 获取协议类型
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
    // 获取主机名(包括域名和端口)
    $host = $_SERVER['HTTP_HOST'];
    // 获取资源路径
    $uri = $_SERVER['REQUEST_URI'];
    // 组合完整的URL
    $url = $protocol . '://' . $host . $uri;

    $userAgent = '';
    if (isset($_SERVER['HTTP_USER_AGENT'])) {
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
    } else {
        // 处理未定义的情况，例如设置默认值或记录错误
        $userAgent = '未知';
    }

    $content .= $request->ip().'@@'.$userAgent.'@@'.date('Y-m-d H:i:s',$time).'@@'.$url;

    Db::name('system_log')->insert([
        'type'=>6,
        'ip'=>$request->ip(),
        'content'=>$content,
        'createtime'=>$time
    ]);
}

#auth0接口====================================================start
#生成随机token
function generateRandomString($length = 35) {
    $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $index = rand(0, strlen($characters) - 1);
        $randomString .= $characters[$index];
    }
    return $randomString;
}

#获取token
function get_auth0_token($data){
    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => "https://gogo198.us.auth0.com/oauth/token",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => "grant_type=authorization_code&client_id=3LuZWceTu0CTzV5z4VBXfDWMaEE3yIVF&client_secret=vhgWu6iyAbbR2UHtuROT2_iPzgIjCWnlaQsANC6hu7NjAOUlzbZnAiO1KS0VG_LP&code=".$data['code']."&redirect_uri=".$data['callback'],
        CURLOPT_HTTPHEADER => [
            "content-type: application/x-www-form-urlencoded"
        ],
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
        echo "cURL Error #:" . $err;
    } else {
        return $response;
    }
}

//进行auth0的api调用
function get_auth0_api($data){
    $curl = curl_init();
//    CURLOPT_URL => 'https://www.gogo198.net/?s=api/auto_login',
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
//        Db::name('website_user')->where(['id'=>1])->update(['auth0_info'=>json_encode($response,true)]);
        $response = json_decode($response,true);

        $account = Db::name('website_user')->where(['email'=>$response['email']])->find();
        if(!empty($account)){
            return ['account'=>$account,'response'=>$response];
        }else{
            return ['account'=>[],'response'=>$response];
        }

//        echo $response;
    }
}
#auth0接口====================================================end