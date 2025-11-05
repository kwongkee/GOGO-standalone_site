<?php

namespace app\api\controller;

use think\Controller;
use think\Db;
use think\Request;

class Chatgpt extends Controller
{
    public function index(Request $request)
    {
        // header("Access-Control-Allow-Origin:*");
        $dat = input();
        $prompt = trim($dat['prompt']);
        $url = 'http://47.254.123.154:3002/api/chat-process';
        $res = api_request($url,['prompt'=>$prompt,"options"=>'','temperature'=>0.8,'top_p'=>1,'systemMessage'=>"You are ChatGPT, a large language model trained by OpenAI. Follow the user's instructions carefully. Respond using markdown."]);
        // $res = json_decode($res,true);
        dd($res);
        if($res['status']=='Fail'){
            #错误
            return json(['msg'=>$res['message'],'data'=>$res['data']]);
        }else{
            #返回
            return json(['msg'=>$res['message'],'data'=>$res['data']]);
        }
    }
}