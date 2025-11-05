<?php
namespace app\index\controller;

use think\Request;
use think\Db;
use think\Controller;
use think\Log;

class Loggin
{
    public function index(Request $request){
        $data = input();
        if(isset($data['pa'])) {
            #获取昨天的起止时间
            $yesterday_timestamp = strtotime("-1 day");
            $yesterday_startDate = strtotime(date("Y-m-d 00:00:00", $yesterday_timestamp));
            $yesterday_endDate = strtotime(date("Y-m-d 23:59:59", $yesterday_timestamp));

            $limit = $request->get('limit');
            $page = $request->get('page') - 1;

            if ($page != 0) {
                $page = $limit * $page;
            }

            $count = Db::name('system_log')->whereRaw('createtime>='.$yesterday_startDate.' and createtime<='.$yesterday_endDate)->field('ip')->group('ip')->count();
            #查找昨天的IP
            $ip_info = Db::name('system_log')->whereRaw('createtime>='.$yesterday_startDate.' and createtime<='.$yesterday_endDate)->field('ip,count(*) as count')->group('ip')->orderRaw('count desc')->limit($page . ',' . $limit)->select();
            foreach ($ip_info as $k => $v) {
                $ip_info[$k]['times'] = Db::name('system_log')->where(['ip' => $v['ip']])->count();
            }

            return json(['code' => 0, 'count' => $count, 'data' => $ip_info]);
        }else{
            $yesterday = date('Y-m-d',strtotime("-1 day"));
            return view('',compact('yesterday'));
        }
    }

    public function log_detail(Request $request){
        $data = input();
        $ip = trim($data['ip']);

        if(isset($data['pa'])) {
            #获取昨天的起止时间
            $yesterday_timestamp = strtotime("-1 day");
            $yesterday_startDate = strtotime(date("Y-m-d 00:00:00", $yesterday_timestamp));
            $yesterday_endDate = strtotime(date("Y-m-d 23:59:59", $yesterday_timestamp));

            $limit = $request->get('limit');
            $page = $request->get('page') - 1;

            if ($page != 0) {
                $page = $limit * $page;
            }
            $count = Db::name('system_log')->where('ip',$ip)->whereRaw('(createtime>='.$yesterday_startDate.' and createtime<='.$yesterday_endDate.')')->count();

            #查找昨天的IP
            $ip_info = Db::name('system_log')->where('ip',$ip)->whereRaw('(createtime>='.$yesterday_startDate.' and createtime<='.$yesterday_endDate.')')->limit($page . ',' . $limit)->orderRaw('createtime asc')->select();
            foreach ($ip_info as $k => $v) {
                $info = explode('@@',$v['content']);
                $ip_info[$k]['user'] = $info[0];
                $ip_info[$k]['device'] = $info[2];
                $ip_info[$k]['time'] = $info[3];
//                $ip_info[$k]['url'] = '<a href="'.$info[4].'" target="_blank" class="layui-btn layui-btn-normal layui-btn-xs">打开链接</a>';
                $ip_info[$k]['url'] = $info[4];
            }

            return json(['code' => 0, 'count' => $count, 'data' => $ip_info]);
        }else{
            $yesterday = date('Y-m-d',strtotime("-1 day"));

            return view('',compact('yesterday','ip'));
        }
    }

    public function notice_boss(Request $request){
        $yesterday = date('Y-m-d',strtotime("-1 day"));
        $post = json_encode([
            'call'=>'confirmCollectionNotice',
            'first' =>"日志记录[".$yesterday."]",
            'keyword1' => "日志记录[".$yesterday."]",
            'keyword2' => "已整理昨天日志数据",
            'keyword3' => date('Y-m-d H:i:s',time()),
            'remark' => '点击查看详情',
            'url' => 'https://www.gogo198.net/?s=log',
            'openid' => 'ov3-bt8keSKg_8z9Wwi-zG1hRhwg',//ov3-bt8keSKg_8z9Wwi-zG1hRhwg ov3-bt5vIxepEjWc51zRQNQbFSaQ
            'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
        ]);

        $res = httpRequest('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);
        if($res){
            echo 1;
        }else{
            echo -1;
        }
    }
}