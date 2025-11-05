<?php
namespace app\index\model;

use think\Db;
class Parceltask
{
    /**
     * 包裹任务
     */
    public static function distribute_task($data,$taskid)
    {
        if($data['task_id']==16){
            return self::yuding($data,$taskid);
        }elseif($data['task_id']==19){
            return self::yubao($data,$taskid);
        }elseif($data['task_id']==22){
            return self::qianshou($data,$taskid);
        }elseif($data['task_id']==30){
            if($data['method2']==1){
                return self::qihuo($data,$taskid);
            }elseif($data['method2']==2){
                return self::zhuanyun($data,$taskid);
            }
        }elseif($data['task_id']==28){
            if($data['task_method']==1){
                return self::hebing($data,$taskid);
            }elseif ($data['task_method']==2){
                return self::fenchai($data,$taskid);
            }
        }
    }

    #通知管理员
    public static function notice($task_name,$id=0,$data=[]){
        $data = Db::name('centralize_system_notice')->where(['uid'=>0,'system_type'=>1])->find();
        $url = '';
        if($task_name=='仓库预订'){
            $url = 'https://shop.gogo198.cn/app/index.php?i=3&c=entry&do=gather&p=index&m=sz_yi&op=gather_info&id='.$id;
        }else{
            $url = 'https://gadmin.gogo198.cn';
        }

        if($data['notice_type']==1){
            #微信
            $post = json_encode([
                'call'=>'confirmCollectionNotice',
                'find' =>"用户[".session('account')['custom_id']."]提交了[".$task_name."]事项操作，请打开查看！",
                'keyword1' => "用户[".session('account')['custom_id']."]提交了[".$task_name."]事项操作，请打开查看！",
                'keyword2' => '已提交待操作',
                'keyword3' => date('Y-m-d H:i:s',time()),
                'remark' => '点击查看详情',
                'url' => $url,
                'openid' => $data['account'],
                'temp_id' => 'SVVs5OeD3FfsGwW0PEfYlZWetjScIT8kDxht5tlI1V8'
            ]);

            httpRequest('https://shop.gogo198.cn/api/sendwechattemplatenotice.php', $post);
        }elseif($data['notice_type']==3){
            $title = "管理员您好，用户[".session('account')['custom_id'].']提交了['.$task_name.']事项操作，请进入集运总后台进行操作！';
            $post_data = json_encode(['email'=>$data['account'],'title'=>$title,'content'=>$url],true);
            $res = httpRequest('https://admin.gogo198.cn/collect_website/public/?s=api/sendemail/index',$post_data,array(
                'Content-Type: application/json; charset=utf-8',
                'Content-Length:' . strlen($post_data),
                'Cache-Control: no-cache',
                'Pragma: no-cache'
            ));
        }
    }

    #仓库预订任务
    public static function yuding($data,$taskid){
        $time = time();

        $content = [];

        #订仓类型(直接转运T/集货转运G) + 会员编号后5位 + 订仓年度YYYY + 流水编号（本年度该订仓类型下的累计数）001-999
        $start = strtotime(date('Y-01-01 00:00:00'));$end = strtotime(date('Y-12-31 23:59:59'));
        $order_num = Db::name('centralize_parcel_order')->where(['prediction_id'=>$data['prediction_id'],'user_id'=>session('account.id')])->whereBetween('createtime',[$start,$end],'AND')->count();
        $order_num = str_pad($order_num+1,3,'0',STR_PAD_LEFT);
        $ordersn = substr(session('account.custom_id'),-5) . date('Y') . $order_num;
        #生成订仓号-end

        if($data['prediction_id']==1){
            $line = Db::name('centralize_line_country')
                ->alias('a')
                ->join('centralize_line_list b','b.id=a.pid')
                ->where(['b.id'=>$data['line_id']])
                ->field('b.code,b.channel_id')
                ->find();
            $line['code'] = substr($line['code'],-2);
            #单个包裹
            $content = [
                'user_id'    => session('account')['id'],
                'agent_id'   => session('account')['id'],
                #9+YY+（集货仓码）00+（入仓日期MMDD）+线路编码00+集运日期DD+终端（O、C、B）+（流程编码）00

                'ordersn'    => 'T' .$ordersn ,
                'prediction_id'=> $data['prediction_id'],
                'country'=> $data['country'],
                'channel'=> $line['channel_id'],
                'line_id'=> $data['line_id'],
                'task_id'    => $taskid,
                'is_otherplace'=>1,
                'createtime' => $time
            ];
        }elseif($data['prediction_id']==2){
            $warehouse = Db::name('centralize_warehouse_list')->where(['id'=>$data['warehouse_id']])->field('warehouse_code')->find();
            #多个包裹
            $content = [
                'user_id'    => session('account')['id'],
                'agent_id'   => session('account')['id'],
                'ordersn'    => 'G'.$ordersn,
                'warehouse_id'=> $data['warehouse_id'],
                'prediction_id'=> $data['prediction_id'],
                'task_id'    => $taskid,
                'is_otherplace'=>1,
                'createtime' => $time
            ];
        }
        $orderid = Db::name('centralize_parcel_order')->insertGetId($content);

        Db::name('centralize_task')->where(['user_id'=>session('account')['id'], 'type'=>3,'id'=>$taskid])->update(['order_id'=>$orderid]);

        #通知管理员
//        if($data['prediction_id']==1) {
            self::notice('仓库预订', $orderid, $data);
//        }
        return json(['code'=>0]);
    }

    #包裹预报任务
    public static function yubao($data,$taskid){
        $time = time();
        $order_id = $data['id'];
        #确认预报
        $order = Db::name('centralize_parcel_order')->where(['id'=>$order_id])->find();
        #包裹需要处理才可集运、包裹无需处理即可集运
        foreach($data['delivery_logistics'] as $k=>$v){
            $express_id = $express_no = $inwarehouse_date = $contact_name = $contact_mobile = '';
            if($v==1){
                #物流送仓
                if($data['delivery_method'][$k]==1){
                    $express_id = trim($data['express_id'][$k]);
                    $express_no = trim($data['express_no'][$k]);
                }elseif($data['delivery_method'][$k]==2){
                    $express_id = trim($data['logistics_id'][$k]);
                    $express_no = trim($data['logistics_no'][$k]);
                }elseif($data['delivery_method'][$k]==3) {
                    $express_id = 0;
                    $express_no = 'IN'.date('YmdHis');
                    $inwarehouse_date = trim($data['inwarehouse_date'][$k]);
                    $contact_name = trim($data['contact_name'][$k]);
                    $contact_mobile = trim($data['contact_mobile'][$k]);
                }
            }elseif($v==2){
                #上门提货，待做。。
            }

            $insert_data = [
                'user_id'=>Session('account')['id'],
                'orderid'=>$order_id,
                'express_id'=>$express_id,
                'express_no'=>$express_no,
                #入仓信息
                'delivery_logistics'=>$v,
                'delivery_method'=>$v==1?$data['delivery_method'][$k]:'',
                'inwarehouse_date'=>$inwarehouse_date,
                'contact_name'=>$contact_name,
                'contact_mobile'=>$contact_mobile,
                #状态
                'status2'=>0,#直接转运或集货转运都要先签收入库
                #创建时间
                'createtime'=>$time
            ];
            if($order['prediction_id']==1) {
                #原包签收
                #包裹材质（裸装、包装）
                $insert_data['package'] = $data['package'][$k];
                #包装名称
                $insert_data['package_name'] = $data['package'][$k]=='包装'?trim($data['package_name'][$k]):'';
                #毛重
                $insert_data['grosswt'] = trim($data['grosswt'][$k]);
                #体积
                $insert_data['volumn'] = trim($data['long'][$k]).'*'.trim($data['width'][$k]).'*'.trim($data['height'][$k]);
                #包裹线路类型
                $insert_data['line_type'] = trim($data['package_line'][$k]);
            }
            elseif($order['prediction_id']==2){
                #验货签收
                #验货方式
                $insert_data['inspection_method'] = $data['inspection_method'][$k];
                #视频时间
                $insert_data['inspection_date'] = $data['inspection_method'][$k]==2?trim($data['inspection_date'][$k]):'';
                #视频平台
                $insert_data['video_platform'] = $data['inspection_method'][$k]==2?trim($data['video_platform'][$k]):'';
                #其他平台名称
                $insert_data['video_platform_name'] = '';
                if($data['inspection_method'][$k]==2 && $data['video_platform'][$k]==4){
                    $insert_data['video_platform_name'] = trim($data['video_platform_name'][$k]);
                }
            }
            #插入包裹表
            $package_id = Db::name('centralize_parcel_order_package')->insertGetId($insert_data);

            if($order['prediction_id']==1){
                #原包签收
                #插入包裹货物表
                foreach($data['valueid'][$k] as $k2=>$v2){
                    $brand_name = '';
                    if($data['goods_brand'][$k][$k2]==2){
                        #仿牌
                        $brand_name=$data['goods_brand_name2'][$k][$k2];
                    }
                    elseif($data['goods_brand'][$k][$k2]==3){
                        #普通品牌
                        $brand_name=$data['goods_brand_name'][$k][$k2];
                    }
                    elseif($data['goods_brand'][$k][$k2]==4){
                        #奢侈品牌
                        $brand_name=$data['goods_brand_name3'][$k][$k2];
                    }
                    Db::name('centralize_parcel_order_goods')->insert([
                        'user_id'=>Session('account')['id'],
                        'orderid'=>$order_id,
                        'package_id'=>$package_id,
                        #物品属性
                        'valueid'=>$data['valueid'][$k][$k2],
                        #物品描述
                        'good_desc'=>$data['goods_select_desc'][$k][$k2]==-1?trim($data['goods_desc'][$k][$k2]):$data['goods_select_desc'][$k][$k2],
                        #物品数量
                        'good_num'=>trim($data['goods_num'][$k][$k2]),
                        #物品单位
                        'good_unit'=>$data['goods_unit'][$k][$k2],
                        #物品币种
                        'good_currency'=>$data['goods_currency'][$k][$k2],
                        #物品金额
                        'good_price'=>trim($data['goods_price'][$k][$k2]),
                        #物品金额（等值美元）
                        'goods_usdprice'=>trim($data['goods_usdprice'][$k][$k2]),
                        #物品包装
                        'good_package'=>$data['goods_package'][$k][$k2],
                        #物品品牌类型
                        'brand_type'=>$data['goods_brand'][$k][$k2],
                        'brand_name'=>$brand_name,
                        #物品备注
                        'good_remark'=>trim($data['goods_remark'][$k][$k2]),
                        #创建时间
                        'createtime'=>$time
                    ]);

                    #插入物品属性指定id下的标签
                    if(!empty($data['goods_desc'][$k][$k2])){
                        $gvalue = Db::name('centralize_gvalue_list')->where(['id'=>$v2])->field('keywords')->find();
                        $gvalue['keywords'] = $gvalue['keywords'].'、'.trim($data['goods_desc'][$k][$k2]);
                        Db::name('centralize_gvalue_list')->where(['id'=>$v2])->update(['keywords'=>$gvalue['keywords']]);
                    }
                }
            }

            Db::name('centralize_order_fee_log')->insert([
                'type'=>1,
                'ordersn'=>'G'.date('YmdHis'),
                'user_id'=>session('account.id'),
                'orderid'=>$order['id'],
                #包裹id
                'good_id'=>$package_id,
                'express_no'=>$express_no,
                'service_status'=>1,
                'order_status'=>0,
                'createtime'=>$time
            ]);
        }

        Db::name('centralize_parcel_order')->where(['id'=>$order_id])->update(['sure_prediction'=>1]);

        self::notice('包裹预报', $order_id, $data);
        return json(['code'=>0]);
    }

    #包裹签收任务
    public static function qianshou($data,$taskid){
        $time = time();
        $service_status=0;
        $order_status=0;
        if($data['method']==8){
            #31-34
            $order_status=31;
            $service_status=8;
            Db::name('centralize_parcel_order_package')->where(['id'=>$data['parcel_ids']])->update([
                'status2'=>$order_status,
                'remark'=>trim($data['remark']),
            ]);
        }elseif($data['method']==9){
            #35-38
            $order_status=35;
            $service_status=9;
            Db::name('centralize_parcel_order_package')->where(['id'=>$data['parcel_ids']])->update([
                'status2'=>$order_status,
                'remark'=>trim($data['remark']),
            ]);
        }
        $order = Db::name('centralize_parcel_order_package')->where(['id'=>$data['parcel_ids'],'user_id'=>session('account.id')])->find();
        Db::name('centralize_order_fee_log')->insert([
            'ordersn'=>'G'.date('YmdHis'),
            'type'=>1,
            'user_id'=>session('account.id'),
            'orderid'=>$order['orderid'],
            'good_id'=>$data['parcel_ids'],
            'express_no' => $order['express_no'],
            'service_status'=>$service_status,
            'order_status'=>$order_status,
            'remark'=>trim($data['remark']),
            'service_price'=>0,
            'createtime'=>$time
        ]);
        self::notice('包裹签收', $order['orderid'], $data);
        return json(['code'=>0]);
    }

    #包裹弃货任务
    public static function qihuo($data,$taskid){
        $user_id = session('account.id');
        $parcel = Db::name('centralize_parcel_order_package')->where(['id'=>$data['parcel_ids']])->find();
        $time = time();
        $status = 0;
        $content = '';
        switch($data['method']){
            case 5:
                $status = 16;break;
            case 6:
                $content = json_encode(['name'=>trim($data['name']),'mobile'=>trim($data['mobile']),'address'=>trim($data['address'])],true);
                $status = 21;break;
            case 7:
                $content = json_encode(['name'=>trim($data['name']),'mobile'=>trim($data['mobile']),'address'=>trim($data['address'])],true);
                $status = 26;break;
        }

        Db::name('centralize_parcel_order_package')->where(['id'=>$data['parcel_ids']])->update([
            'status2'=>$status,
            'remark'=>trim($data['remark']),
        ]);

        Db::name('centralize_order_fee_log')->insert([
            'ordersn' => 'G' . date('YmdHis'),
            'type' => 1,
            'user_id' => $user_id,
            'orderid' => $parcel['orderid'],
            'good_id' => $data['parcel_ids'],
            'express_no'=>$parcel['express_no'],
            'service_status' => $data['method'],
            'order_status' => $status,
            'qihuo_content' => $content,
            'service_price' => 0,
            'createtime' => $time
        ]);
        self::notice('包裹弃置', $parcel['orderid'], $data);
    }

    #包裹转运任务
    public static function zhuanyun($data,$taskid){
        $user_id = session('account.id');
        $parcel = Db::name('centralize_parcel_order_package')->where(['id'=>$data['parcel_ids']])->find();
        $time = time();
        $content = json_encode(['name'=>trim($data['name']),'mobile'=>trim($data['mobile']),'address'=>trim($data['address'])],true);
        $status = 6;

        $res = Db::name('centralize_parcel_order_package')->where(['id'=>$data['parcel_ids']])->update([
            'status2'=>$status,
            'remark'=>trim($data['remark']),
        ]);

        #国内转运
        Db::name('centralize_order_fee_log')->insert([
            'ordersn' => 'G' . date('YmdHis'),
            'type' => 1,
            'user_id' => $user_id,
            'orderid' => $parcel['orderid'],
            'good_id' => $data['parcel_ids'],
            'express_no'=>$parcel['express_no'],
            'service_status' => 3,
            'order_status' => $status,
            'zhuanyun_content' => $content,
            'remark' => trim($data['remark']),
            'service_price' => 0,
            'createtime' => $time
        ]);
        self::notice('包裹国内转运', $parcel['orderid'], $data);
    }

    #包裹合并任务
    public static function hebing($data,$taskid){
        if($data['method']==1){
            #以首个运单为合并目标
            $arr_first = Db::name('centralize_parcel_order_package')->where(['id'=>$data['arr'][0]])->find();
            $parcel = Db::name('centralize_parcel_order_package')->where(['express_no'=>$arr_first['express_no']])->select();
            foreach($parcel as $k=>$v){
                Db::name('centralize_parcel_order_package')->where(['express_no'=>$v['express_no']])->update([
                    'status2'=>43,
                    'remark'=>trim($data['remark']),
                ]);
            }

            Db::name('centralize_order_fee_log')->insert([
                'ordersn'=>'G'.date('YmdHis'),
                'type'=>1,
                'user_id'=>session('account.id'),
                'orderid'=>$arr_first['orderid'],
                'good_id'=>$arr_first['id'],
                'express_no' => $arr_first['express_no'],
                'service_status'=>11,
                'order_status'=>43,
                'parcel_ids'=>$data['parcel_ids'],
                'remark'=>trim($data['remark']),
                'service_price'=>0,
                'createtime'=>time()
            ]);
            self::notice('包裹合并', $arr_first['orderid'], $data);
        }
    }

    #包裹分拆任务
    public static function fenchai($data,$taskid){
        $user_id = session('account.id');
        $parcel = Db::name('centralize_parcel_order_package')->where(['id'=>$data['transfer_id'],'user_id'=>$user_id])->find();
        $time = time();
        dd($data);
        //1、判断原始表物品数量是否为空
        $all_empty=0;//0全为空，1不全为空
        foreach($data['num'] as $k=>$v){
            if(!empty($v)){
                $all_empty=1;
            }
        }
        if(empty($all_empty)){
            return ['code'=>-1,'msg'=>'原有货物列表物品数量不能全为空，分拆失败'];
        }
        //1.1、判断分拆列表是否为空
        if(!isset($dat['spin_gid2'])){
            return ['code'=>-1,'msg'=>'分拆货物列表物品数量不能全为空，分拆失败'];
        }

        //2、记录分拆货物列表和原始货物列表
        $insert_data = [
            'user_id'=>$user_id,
            'orderid'=>$data['transfer_id'],#包裹id
            'createtime'=>$time
        ];
        $spin_info = [];
        foreach($data['spin_gid2'] as $k=>$v){
            $spin_info = array_merge($spin_info,[[
                'gid'=>$v,
                'num'=>$data['spin_num2'][$k]
            ]]);
        }
        #修改包裹为分拆状态
        Db::name('centralize_parcel_order_package')->where(['id'=>$parcel['id']])->update([
            'status2'=>39,
            'remark'=>trim($data['remark']),
        ]);

        $origin_info = [];
        foreach($data['gid'] as $k=>$v){
            $origin_info = array_merge($origin_info,[[
                'gid'=>$v,
                'num'=>$data['num'][$k]
            ]]);
        }
        $insert_data = array_merge($insert_data,[
            'spin_info'=>json_encode($spin_info,true),
            'origin_info'=>json_encode($origin_info,true),
            'new_expressno'=>trim($data['new_expressno']),
        ]);

        $res = Db::name('centralize_parcel_order_spin')->insertGetId($insert_data);
        #仓库费用
        //$warehouse_info = $this->get_warehouse_info($data['warehouse_id']);
        Db::name('centralize_order_fee_log')->insert([
            'ordersn' => 'G' . date('YmdHis'),
            'type' => 1,
            'user_id' => $user_id,
            'orderid' => $parcel['orderid'],
            'good_id' => $parcel['id'],#包裹id
            'express_no'=>$parcel['express_no'],
            'service_status' => 10,
            'order_status' => 39,
            'spin_id' => $res,
            'service_price' => 0,
            'createtime' => $time
        ]);
        self::notice('包裹分拆', $parcel['orderid'], $data);
        return ['code'=>0,'msg'=>'提交成功'];
    }
}