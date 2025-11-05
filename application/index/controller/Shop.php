<?php
namespace app\index\controller;

use think\Request;
use think\Db;
use think\Controller;
use app\index\model\Parceltask;
use think\Log;

class Shop
{
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

    public function __construct(Request $request){
        #日志记录
//        platform_log($request);
    }

    #分流人员操作，选择买手
    public function audit(Request $request){
        $dat = input();
        $pa = isset($dat['pa'])?$dat['pa']:1;

        if(isset($dat['req'])){
            $limit = $request->get('limit');
            $page = $request->get('page') - 1;

            if ($page != 0) {
                $page = $limit * $page;
            }

            if($pa==1){
                #待分流
                $count = Db::name('website_order_list')->where(['buyer_id'=>0])->count();
                $rows = Db::name('website_order_list')->where(['buyer_id'=>0])->limit($page . ',' . $limit)->order('id','desc')->select();
            }elseif($pa==2){
                #已分流
                $count = Db::name('website_order_list')->whereRaw('buyer_id<>0')->count();
                $rows = Db::name('website_order_list')->whereRaw('buyer_id<>0')->limit($page . ',' . $limit)->order('id','desc')->select();
            }
            foreach ($rows as $k => $v) {
                $rows[$k]['createtime'] = date('Y-m-d H:i:s',$v['createtime']);
            }

            return json(['code' => 0, 'count' => $count, 'data' => $rows]);
        }

        return view('',compact('pa'));
    }

    public function audit_detail(Request $request){
        $dat = input();
        $id = intval($dat['id']);

        $order = Db::name('website_order_list')->where(['id'=>$id])->find();
        $order['content'] = json_decode($order['content'],true);

        if(isset($dat['pa'])){

            $time = time();
            $user = Db::name('website_user')->where(['id'=>$order['user_id']])->find();
            if($dat['send']==1){
                #同意、拒绝分流
                if($dat['shunt_type']==1){
                    #同意分流
                    if(empty($dat['buyer_id'])){
                        return json(['code'=>-1,'msg'=>'请选择买手信息']);
                    }


                    $buyer = Db::name('website_buyer')->where(['id'=>intval($dat['buyer_id'])])->find();
                    Db::name('website_order_list')->where(['id'=>$id])->update([
                        'buyer_id'=>intval($dat['buyer_id'])
                    ]);

                    if($buyer['type']==1){
                        #接口买手

                        $productList = [];
                        if(!empty($order['content']['goods_info'])){
                            foreach($order['content']['goods_info'] as $k=>$v){
                                $goods = Db::connect($this->config)->name('goods')->where(['goods_id'=>$v['good_id']])->find();
                                foreach($v['sku_info'] as $k2=>$v2){
                                    $sku_goods = Db::connect($this->config)->name('goods_sku')->where(['sku_id'=>$v2['sku_id']])->find();
                                    $sku_goods['sku_prices'] = json_decode($sku_goods['sku_prices'],true);

                                    array_push($productList,[
                                        'platform'=>$goods['other_platform'],
                                        'productCount'=>$v2['goods_num'],
                                        'productLink'=>$goods['other_goods_link'],
                                        'productName'=>$goods['goods_name'],
                                        'productPrice'=>$sku_goods['sku_prices']['price'][0],#订购产品单价
                                        'skuCode'=>$sku_goods['goods_sn'],
                                        'spuCode'=>$goods['other_spuCode'],
                                        'productImage'=>$goods['goods_image'],
                                        'orderRemark'=>'买家：'.$user['custom_id']
                                    ]);
                                }
                            }
                        }

                        $res = $this->create_order(json_encode(['ordersn'=>$order['ordersn'],'createtime'=>$time*1000,'productList'=>json_encode($productList,true)],true));
                        if($res['code']==0){
                            Db::name('website_order_list')->where(['id'=>$order['id']])->update([
                                'other_ordersn'=>$res['data']['shopOrderNo'],
                                'issend'=>1
                            ]);

                            return json(['code'=>0,'msg'=>'已分流']);
                        }
                    }elseif($buyer['type']==2){
                        #人工买手
                        $buyer = Db::name('website_user')->where(['id'=>$buyer['uid']])->find();
                        common_notice([
                            'openid'=>$buyer['openid'],
                            'phone'=>$buyer['phone'],
                            'email'=>$buyer['email']
                        ],[
                            'msg'=>'订购清单['.$order['ordersn'].']待确认，点击链接查看：https://www.gogo198.net/?s=shop/shunt&gogo_id='.$buyer['custom_id'],
                            'opera'=>'待确认',
                            'url'=>'https://www.gogo198.net/?s=shop/shunt&gogo_id='.$buyer['custom_id']
                        ]);

                        return json(['code'=>0,'msg'=>'已分流']);
                    }
                }
                elseif($dat['shunt_type']==2){
                    #拒绝分流
                    Db::name('website_order_list')->where(['id'=>$id])->update(['status'=>-4]);
                    common_notice([
                        'openid'=>$user['openid'],
                        'phone'=>$user['phone'],
                        'email'=>$user['email']
                    ],[
                        'msg'=>'订购清单['.$order['ordersn'].']状态变更为[已取消]，点击链接查看：https://www.gogo198.cn/bill_detail?id='.$order['id'],
                        'opera'=>'拒绝订购，已取消',
                        'url'=>'https://shopping.gogo198.cn/bill_detail?id='.$order['id']
                    ]);

                    return json(['code'=>0,'msg'=>'已拒绝']);
                }
            }
            elseif($dat['send']==2){
                #撤销分流
                Db::name('website_order_list')->where(['id'=>$id])->update([
                    'buyer_id'=>0,
                    'status'=>-2
                ]);

                return json(['code'=>0,'msg'=>'已撤回分流']);
            }
            elseif($dat['send']==3){
                #确认有无货，通知买家
                if($order['status']==-9){
                    #有货，无修改
                    Db::name('website_order_list')->where(['id'=>$order['id']])->update([
                        'status'=>0,
                        'issend'=>1
                    ]);

                    common_notice([
                        'openid'=>$user['openid'],
                        'phone'=>$user['phone'],
                        'email'=>$user['email']
                    ],[
                        'msg'=>'订购清单['.$order['ordersn'].']确认有货，点击链接查看：https://www.gogo198.cn/cart.html?selected=1',
                        'opera'=>'确认有货，请勾选支付',
                        'url'=>'https://www.gogo198.cn/cart.html?selected=1'
                    ]);
                    return json(['code'=>0,'msg'=>'已通知买家']);
                }
                elseif($order['status']==-10){
                    #有货，有修改
                    Db::name('website_order_list')->where(['id'=>$order['id']])->update([
                        'status'=>0,
                        'issend'=>1
                    ]);

                    common_notice([
                        'openid'=>$user['openid'],
                        'phone'=>$user['phone'],
                        'email'=>$user['email']
                    ],[
                        'msg'=>'订购清单['.$order['ordersn'].']确认有货，点击链接查看：https://www.gogo198.cn/cart.html?selected=1',
                        'opera'=>'确认有货，请勾选支付',
                        'url'=>'https://www.gogo198.cn/cart.html?selected=1'
                    ]);
                    return json(['code'=>0,'msg'=>'已通知买家']);
                }
                elseif($order['status']==-11){
                    #无货
                    Db::name('website_order_list')->where(['id'=>$order['id']])->update([
                        'status'=>-4,
                        'issend'=>1
                    ]);

                    common_notice([
                        'openid'=>$user['openid'],
                        'phone'=>$user['phone'],
                        'email'=>$user['email']
                    ],[
                        'msg'=>'订购清单['.$order['ordersn'].']确认无货，点击链接查看：https://www.gogo198.cn/cart.html?selected=1',
                        'opera'=>'确认无货，已取消',
                        'url'=>'https://www.gogo198.cn/cart.html?selected=1'
                    ]);
                    return json(['code'=>0,'msg'=>'已通知买家']);
                }
            }
        }
        else{

            #收货信息
//            $address = Db::name('centralize_user_address')->where(['id'=>$order['content']['address_id']])->find();
//            $address['postal_code'] = json_decode($address['postal_code'],true);
//            $address['postal'] = '';
//            foreach($address['postal_code'] as $k=>$v){
//                $address['postal'] .= $v;
//            }
//            $country = Db::name('centralize_diycountry_content')->where(['id'=>$address['country_id']])->find();#国
//            $province = '';
//            if(!empty($address['province'])){
//                $province = Db::name('centralize_adminstrative_area')->where(['id'=>$address['province']])->find()['code_name'];#省
//            }
//            $city = '';
//            if(!empty($address['city'])){
//                $city = Db::name('centralize_adminstrative_area')->where(['id'=>$address['city']])->find()['code_name'];#市
//            }
//            $area_info = '';$area_info2 = '';$area_info3 = '';$area_info4 = '';
//            if(!empty($address['area'])){
//                $area_info = Db::name('centralize_adminstrative_area')->where(['id'=>$address['area']])->find()['code_name'];#区1
//            }
//            if(!empty($address['area2'])) {
//                $area_info2 = Db::name('centralize_adminstrative_area')->where(['id' => $address['area2']])->find()['code_name'];#区2
//            }
//            if(!empty($address['area3'])) {
//                $area_info3 = Db::name('centralize_adminstrative_area')->where(['id' => $address['area3']])->find()['code_name'];#区3
//            }
//            if(!empty($address['area4'])) {
//                $area_info4 = Db::name('centralize_adminstrative_area')->where(['id' => $address['area4']])->find()['code_name'];#区4
//            }
//            $address['address2'] = json_decode($address['address2'], true);
//            $address2 = '';
//            if (!empty($address['address2'])) {
//                foreach ($address['address2'] as $k => $v) {
//                    $address2 .= $v;
//                }
//            }
//            $address['address'] = $country['param2'].$province.$city.$area_info.$area_info2.$area_info3.$area_info4.$address['address1'].$address2;

            #账单状态
            $order['status_name'] = $this->get_statusname($order['status']);

            #买手信息
            $buyer = Db::name('website_buyer')->where(['is_verify'=>1])->select();
            foreach($buyer as $k=>$v){
                if($v['type']==1){
                    $buyer[$k]['typename'] = '接口买家';
                }
                elseif($v['type']==2){
                    $buyer[$k]['typename'] = '自营买家';
                }
                elseif($v['type']==3){
                    $buyer[$k]['typename'] = '合作买家';
                }
            }

            #订购清单信息
            foreach($order['content']['goods_info'] as $k=>$v){
                    $order['content']['goods_info'][$k]['goods_info'] = Db::connect($this->config)->name('goods')->where(['goods_id'=>$v['good_id']])->find();
                foreach($v['sku_info'] as $k2=>$v2){
                    $order['content']['goods_info'][$k]['sku_info'][$k2]['sku_info'] =  Db::connect($this->config)->name('goods_sku')->where(['sku_id'=>$v2['sku_id']])->find();
                }
            }
            $goods = $order['content']['goods_info'];
//            dd($address);
            return view('',compact('order','id','buyer','goods'));
        }
    }

    #买手操作，通知用户
    public function shunt(Request $request){
        $dat = input();

        $gogo_id = intval($dat['gogo_id']);

        if(empty(session('account'))){
            $account = Db::name('website_user')->where(['custom_id'=>$gogo_id])->find();
            session('account',$account);
        }

        if(isset($dat['req'])){
            $limit = $request->get('limit');
            $page = $request->get('page') - 1;

            if ($page != 0) {
                $page = $limit * $page;
            }

            $buyer = Db::name('website_buyer')->where(['uid'=>session('account')['id']])->find();
            #已分流
            $count = Db::name('website_order_list')->whereRaw('buyer_id='.$buyer['id'])->count();
            $rows = Db::name('website_order_list')->whereRaw('buyer_id='.$buyer['id'])->limit($page . ',' . $limit)->order('id','desc')->select();

            foreach ($rows as $k => $v) {
                $rows[$k]['createtime'] = date('Y-m-d H:i:s',$v['createtime']);
                $rows[$k]['status_name'] = $this->get_statusname($v['status']);
            }

            return json(['code' => 0, 'count' => $count, 'data' => $rows]);
        }
        return view('',compact('gogo_id'));
    }

    public function shunt_detail(Request $request){
        $dat = input();
        $id = intval($dat['id']);

        $order = Db::name('website_order_list')->where(['id'=>$id])->find();
        $order['content'] = json_decode($order['content'],true);

        if(isset($dat['pa'])){
            $time = time();
            $user = Db::name('website_user')->where(['id'=>$order['user_id']])->find();

            $system = Db::name('centralize_system_notice')->where(['uid'=>0])->find();
            $manage_openid = $system['account'];
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
                    'msg'=>'订购清单['.$order['ordersn'].']已确认有货[无修改]，点击链接查看：https://www.gogo198.net/?s=shop/audit',
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
                    'msg'=>'订购清单['.$order['ordersn'].']已确认无货，点击链接查看：https://www.gogo198.net/?s=shop/audit',
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
                    'msg'=>'订购清单['.$order['ordersn'].']已确认有货[有修改]，点击链接查看：https://www.gogo198.net/?s=shop/audit',
                    'opera'=>'确认有货（有修改）',
                    'url'=>'https://www.gogo198.net/?s=shop/audit'
                ]);
            }
            elseif($dat['status']==3){
                #已采购

                #1、仓库预定
                $prediction_id = 2;
                $start = strtotime(date('Y-01-01 00:00:00'));$end = strtotime(date('Y-12-31 23:59:59'));
                $order_num = Db::name('centralize_parcel_order')->where(['prediction_id'=>$prediction_id,'user_id'=>$user['id']])->whereBetween('createtime',[$start,$end],'AND')->count();
                $order_num = str_pad($order_num+1,3,'0',STR_PAD_LEFT);
                $ordersn = substr($user['custom_id'],-5) . date('Y') . $order_num;
                #多个包裹
                $content = [
                    'user_id'    => $user['id'],
                    'agent_id'   => $user['agent_id'],
                    'ordersn'    => 'G'.$ordersn,
                    'warehouse_id'=> 16,#默认仓库地址
                    'prediction_id'=> $prediction_id,
                    'task_id'    => 0,
                    'sure_prediction'=>1,
                    'createtime' => $time
                ];
                $orderid = Db::name('centralize_parcel_order')->insertGetId($content);

                //1.1、任务信息处理
                $time = time();
                #获取任务流水号
                $start_num = $this->get_today_task($time);
                if(empty($start_num)){
                    $serial_number = 'MC'.date('ymdHis',$time).str_pad(1,2,'0',STR_PAD_LEFT);
                }else{
                    $serial_number = 'MC'.date('ymdHis',$time).str_pad(intval($start_num)+1,2,'0',STR_PAD_LEFT);
                }
                #获取任务名称
                $task_name = $user['custom_id'].'发起任务[仓库预报]';
                Db::name('centralize_task')->insertGetId([
                    'user_id'=>$user['id'],
                    'type'=>3,
                    'task_name'=>$task_name,
                    'task_id'=>19,
                    'order_id'=>$orderid,
                    'serial_number'=>$serial_number,
                    'remark'=>'',
                    'status'=>1,
                    'createtime'=>$time
                ]);

                #2、包裹预报
                $insert_data = [
                    'user_id'=>$user['id'],
                    'orderid'=>$orderid,
                    'gogo_oid'=>$order['id'],
                    'express_id'=>$dat['express_id'],
                    'express_no'=>$dat['express_no'],
                    #入仓信息
                    'delivery_logistics'=>1,
                    'delivery_method'=>1,
                    #物品信息
                    'inspection_method'=>1,
                    #包装材质
                    'package'=>$dat['package'],
                    'package_name'=>$dat['package']=='包装'?trim($dat['package_name']):'',
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
                if($dat['goods_brand']==2){
                    #仿牌
                    $brand_name=$dat['goods_brand_name2'];
                }
                elseif($dat['goods_brand']==3){
                    #普通品牌
                    $brand_name=$dat['goods_brand_name'];
                }
                elseif($dat['goods_brand']==4){
                    #奢侈品牌
                    $brand_name=$dat['goods_brand_name3'];
                }
                Db::name('centralize_parcel_order_goods')->insert([
                    'user_id'=>$user['id'],
                    'orderid'=>$orderid,
                    'package_id'=>$package_id,
                    #物品属性
                    'valueid'=>$dat['valueid'],
                    #物品描述
                    'good_desc'=>$dat['goods_select_desc']==-1?trim($dat['goods_desc']):$dat['goods_select_desc'],
                    #物品数量
                    'good_num'=>trim($dat['goods_num']),
                    #物品单位
                    'good_unit'=>$dat['goods_unit'],
                    #物品币种
                    'good_currency'=>$dat['goods_currency'],
                    #物品金额
                    'good_price'=>trim($dat['goods_price']),
                    #物品金额（等值美元）
                    'goods_usdprice'=>trim($dat['goods_usdprice']),
                    #物品包装
                    'good_package'=>$dat['goods_package'],
                    #物品品牌类型
                    'brand_type'=>$dat['goods_brand'],
                    'brand_name'=>$brand_name,
                    #物品备注
                    'good_remark'=>trim($dat['goods_remark']),
                    #创建时间
                    'createtime'=>$time
                ]);
                #插入物品属性指定id下的标签
                if(!empty($dat['goods_desc'])){
                    $gvalue = Db::name('centralize_gvalue_list')->where(['id'=>$dat['valueid']])->field('keywords')->find();
                    $gvalue['keywords'] = $gvalue['keywords'].'、'.trim($dat['goods_desc']);
                    Db::name('centralize_gvalue_list')->where(['id'=>$dat['valueid']])->update(['keywords'=>$gvalue['keywords']]);
                }

                #4、包裹订单
                Db::name('centralize_order_fee_log')->insert([
                    'type'=>1,
                    'ordersn'=>'G'.date('YmdHis'),
                    'user_id'=>$user['id'],
                    'orderid'=>$orderid,
                    #包裹id
                    'good_id'=>$package_id,
                    'express_no'=>$dat['express_id'],
                    'service_status'=>1,
                    'order_status'=>0,
                    'createtime'=>$time
                ]);

                #5、修改购购网订单表
                Db::name('website_order_list')->where(['id'=>$id])->update(['status'=>2]);

                #6、通知用户
                common_notice([
                    'openid'=>$user['openid'],
                    'phone'=>$user['phone'],
                    'email'=>$user['email']
                ],[
                    'msg'=>'订单['.$order['ordersn'].']状态变更为[已预报]，点击链接查看：https://www.gogo198.com/',
                    'opera'=>'包裹已预报',
                    'url'=>'https://www.gogo198.com'
                ]);
//                $warehouse = Db::name('centralize_warehouse_list')->where(['id'=>16])->find();
//                $merchant = Db::name('website_user')->where(['id'=>$warehouse['uid']])->find();


                return json(['code'=>0,'msg'=>'已保存预报信息并通知客户']);
            }

            return json(['code'=>0,'msg'=>'保存成功']);
        }
        else{
            #收货地址===========================================================
//            if(!empty($order['edit_address'])){
//                $address = json_decode($order['edit_address'],true);
//            }
//            else {
//                #收货信息
//                $address = Db::name('centralize_user_address')->where(['id' => $order['content']['address_id']])->find();
//                $address['postal_code'] = json_decode($address['postal_code'], true);
//                $address['postal'] = '';
//                foreach ($address['postal_code'] as $k => $v) {
//                    $address['postal'] .= $v;
//                }
//                $country = Db::name('centralize_diycountry_content')->where(['id' => $address['country_id']])->find();#国
//                $province = '';
//                if (!empty($address['province'])) {
//                    $province = Db::name('centralize_adminstrative_area')->where(['id' => $address['province']])->find()['code_name'];#省
//                }
//                $city = '';
//                if (!empty($address['city'])) {
//                    $city = Db::name('centralize_adminstrative_area')->where(['id' => $address['city']])->find()['code_name'];#市
//                }
//                $area_info = '';
//                $area_info2 = '';
//                $area_info3 = '';
//                $area_info4 = '';
//                if (!empty($address['area'])) {
//                    $area_info = Db::name('centralize_adminstrative_area')->where(['id' => $address['area']])->find()['code_name'];#区1
//                }
//                if (!empty($address['area2'])) {
//                    $area_info2 = Db::name('centralize_adminstrative_area')->where(['id' => $address['area2']])->find()['code_name'];#区2
//                }
//                if (!empty($address['area3'])) {
//                    $area_info3 = Db::name('centralize_adminstrative_area')->where(['id' => $address['area3']])->find()['code_name'];#区3
//                }
//                if (!empty($address['area4'])) {
//                    $area_info4 = Db::name('centralize_adminstrative_area')->where(['id' => $address['area4']])->find()['code_name'];#区4
//                }
//
//                $address['address2'] = json_decode($address['address2'], true);
//                $address2 = '';
//                if (!empty($address['address2'])) {
//                    foreach ($address['address2'] as $k => $v) {
//                        $address2 .= $v;
//                    }
//                }
//
//                $address['address'] = $country['param2'] . $province . $city . $area_info . $area_info2 . $area_info3 . $area_info4 . $address['address1'] . $address2;
//            }
            #收货地址===========================================================

            $order['status_name'] = $this->get_statusname($order['status']);

            $express = Db::name('centralize_diycountry_content')->where(['pid'=>6])->select();
            $unit = Db::name('unit')->select();
            $currency = Db::name('centralize_currency')->select();
            $package = Db::name('packing_type')->select();
            $value = json_encode($this->menu2(2),true);
            $brand = Db::name('centralize_diycountry_content')->where(['pid'=>8])->select();
            #订购清单信息
            foreach($order['content']['goods_info'] as $k=>$v){
                $order['content']['goods_info'][$k]['goods_info'] = Db::connect($this->config)->name('goods')->where(['goods_id'=>$v['good_id']])->find();
                foreach($v['sku_info'] as $k2=>$v2){
                    $order['content']['goods_info'][$k]['sku_info'][$k2]['sku_info'] =  Db::connect($this->config)->name('goods_sku')->where(['sku_id'=>$v2['sku_id']])->find();
                }
            }
            $goods = $order['content']['goods_info'];

            return view('',compact('order','goods','id','express','unit','currency','package','value','brand'));
        }
    }

    #修改商品规格参数+费用项目价格+商品数量
    public function shunt_edit(Request $request){
        $dat = input();

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

            return view('',compact('id','gid','gkey','skey','sku_id','cart_id','sku_data','origin_skuinfo','other_skuinfo','origin_services','is_manage'));
        }
    }

    #修改地址
    public function shunt_addr(Request $request){
        $dat = input();
        $id = intval($dat['id']);

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


            return view('',compact('id','address'));
        }
    }

    #获取今天的任务排序号
    public function get_today_task($time){
        $data = Db::name('centralize_task')->where('createtime',$time)->order('id desc')->find();

        return substr($data['serial_number'],-2);
    }

    #菜单栏目-xmselect树形结构
    public function menu2($typ=0){
        $menu = Db::name('centralize_gvalue_list')->where(['pid'=>0])->field('id,name,country,channel,desc,keywords,pid')->select();
        foreach($menu as $k=>$v){
            $menu[$k]['name'] = $v['name'];
            $menu[$k]['value'] = $v['id'];
            if($typ==1){
                $menu[$k]['children'] = $this->getDownMenu3($v['id']);
            }else{
                $menu[$k]['children'] = $this->getDownMenu2($v['id']);
//                dd($menu);
            }
        }
        return $menu;
    }

    #下级菜单
    public function getDownMenu2($id){
        $cmenu = Db::name('centralize_gvalue_list')->where(['pid'=>$id])->field('id,name,country,channel,desc,keywords,pid')->select();
        foreach($cmenu as $k=>$v){
            $cmenu[$k]['name'] = $v['name'];
            $cmenu[$k]['value'] = $v['id'];
            $cmenu[$k]['top_id'] = $id;
            $cmenu[$k]['children'] = Db::name('centralize_gvalue_list')->where(['pid'=>$v['id']])->field('id,name,country,channel,desc,keywords,pid')->select();
            foreach($cmenu[$k]['children'] as $k2=>$v2){
                $cmenu[$k]['children'][$k2]['name'] = $v2['name'];
                $cmenu[$k]['children'][$k2]['value'] = $v2['id'];
                $cmenu[$k]['children'][$k2]['top_id'] = $id;
                $cmenu[$k]['children'][$k2]['children'] = Db::name('centralize_gvalue_list')->where(['pid'=>$v2['id']])->field('id,name,country,channel,desc,keywords,pid')->select();
                foreach($cmenu[$k]['children'][$k2]['children'] as $k3=>$v3){
                    $cmenu[$k]['children'][$k2]['children'][$k3]['name'] = $v3['name'];
                    $cmenu[$k]['children'][$k2]['children'][$k3]['value'] = $v3['id'];
                    $cmenu[$k]['children'][$k2]['children'][$k3]['top_id'] = $id;
                    $cmenu[$k]['children'][$k2]['children'][$k3]['children'] = Db::name('centralize_gvalue_list')->where(['pid'=>$v3['id']])->field('id,name,country,channel,desc,keywords,pid')->select();
                    foreach($cmenu[$k]['children'][$k2]['children'][$k3]['children'] as $k4=>$v4){
                        $cmenu[$k]['children'][$k2]['children'][$k3]['children'][$k4]['name'] = $v4['name'];
                        $cmenu[$k]['children'][$k2]['children'][$k3]['children'][$k4]['value'] = $v4['id'];
                        $cmenu[$k]['children'][$k2]['children'][$k3]['children'][$k4]['top_id'] = $id;
                    }
                }
            }
        }
        return $cmenu;
    }

    #不要最下一层的菜单
    public function getDownMenu3($id){
        $cmenu = Db::name('website_navbar')->where(['pid'=>$id])->field('id,name')->select();
        foreach($cmenu as $k=>$v){
            $cmenu[$k]['name'] = json_decode($v['name'],true)['zh'];
            $cmenu[$k]['value'] = $v['id'];
//            $cmenu[$k]['children'] = Db::name('website_navbar')->where(['pid'=>$v['id']])->field('id,name')->select();
//            if(empty($cmenu[$k]['children'])){
//                unset($cmenu[$k]);
//            }
//            else{
//                foreach($cmenu[$k]['children'] as $k2=>$v2){
//                    $cmenu[$k]['children'][$k2]['name'] = json_decode($v2['name'],true)['zh'];
//                    $cmenu[$k]['children'][$k2]['value'] = $v2['id'];
//                    $cmenu[$k]['children'][$k2]['children'] = Db::name('website_navbar')->where(['pid'=>$v2['id']])->field('id,name')->select();
//                    if(empty($cmenu[$k]['children'][$k2]['children'])){
//                        unset($cmenu[$k]['children'][$k2]);
//                    }else{
//                        foreach($cmenu[$k]['children'][$k2]['children'] as $k3=>$v3){
//                            $cmenu[$k]['children'][$k2]['children'][$k3]['name'] = json_decode($v3['name'],true)['zh'];
//                            $cmenu[$k]['children'][$k2]['children'][$k3]['value'] = $v3['id'];
//                            $cmenu[$k]['children'][$k2]['children'][$k3]['children'] = Db::name('website_navbar')->where(['pid'=>$v3['id']])->field('id,name')->select();
//                            if(empty($cmenu[$k]['children'][$k2]['children'][$k3]['children'])){
//                                unset($cmenu[$k]['children'][$k2]['children'][$k3]);
//                            }else{
//                                foreach($cmenu[$k]['children'][$k2]['children'][$k3]['children'] as $k4=>$v4){
//                                    $cmenu[$k]['children'][$k2]['children'][$k3]['children'][$k4]['name'] = json_decode($v4['name'],true)['zh'];
//                                    $cmenu[$k]['children'][$k2]['children'][$k3]['children'][$k4]['value'] = $v4['id'];
//                                }
//                            }
//                        }
//                    }
//                }
//            }
        }
        return $cmenu;
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

    #生成订单
    public function create_order($data){
        $res = httpRequest2('https://shop.gogo198.cn/collect_website/public/?s=/api/getgoods/create_order',$data,['Content-Type: application/json;charset=utf-8']);
        $res = json_decode($res,true);
        return $res;
    }

    #成为买手
    public function become_buyer(Request $request){
        $dat = input();
        $id = intval($dat['id']);

        if($request->isAjax()){
            $buyer = Db::name('website_buyer')->where(['id'=>$id])->find();
            $user = Db::name('website_user')->where(['email'=>$buyer['email']])->find();
            if(empty($user)){
                $time = time();
                $insertid = Db::name('website_user')->insertGetId([
                    'email'=>$buyer['email'],
                    'times'=>1,
                    'createtime'=>$time
                ]);

                $custom_id = 'G'.str_pad($insertid, 5, '0', STR_PAD_LEFT);
                $nickname = 'GoFriend_'.$custom_id;
                $res = Db::name('website_user')->where('id',$insertid)->update(['custom_id'=>$custom_id,'nickname'=>$nickname]);

                if($res){
                    #赋予账号
                    $user = Db::name('website_user')->where('id',$insertid)->find();
                    #集运网账号
                    Db::name('centralize_user')->insert([
                        'name'=>$user['nickname'],
                        'realname'=>$user['realname'],
                        'email'=>$user['email'],
                        'pwd'=>md5('888888'),
                        'mobile'=>$user['phone'],
                        'status'=>0,
                        'agentid'=>$user['agent_id'],
                        'gogo_id'=>$user['custom_id'],
                        'createtime'=>$time,
                    ]);
                    #买全球账号（旧的）
                    Db::name('sz_yi_member')->insert([
                        'uniacid'=>3,
                        'realname'=>$user['realname'],
                        'nickname'=>$user['nickname'],
                        'mobile'=>$user['phone']!=''?$user['phone']:$user['email'],
                        'pwd'=>md5('888888'),
                        'id_card'=>$user['idcard'],
                        'gogo_id'=>$user['custom_id'],
                        'createtime'=>$time,
                    ]);
                    #卖全球账号（旧的）
                    Db::name('sz_yi_member')->insert([
                        'uniacid'=>18,
                        'realname'=>$user['realname'],
                        'nickname'=>$user['nickname'],
                        'mobile'=>$user['phone']!=''?$user['phone']:$user['email'],
                        'pwd'=>md5('888888'),
                        'id_card'=>$user['idcard'],
                        'gogo_id'=>$user['custom_id'],
                        'createtime'=>$time,
                    ]);
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
                    Db::connect($config)->name('user')->insert([
                        'role_id'=>0,
                        'gogo_id'=>$custom_id,
                        'user_name'=>$user['realname'],
                        'nickname'=>$user['nickname'],
                        'password'=>'$2y$10$Nbq/GtGDT6wjbs6e7WhJ0Ox2EaWQ0ANcpayPi9bFLQQ6B3rEEeHx2',//6个8
                        'mobile'=>$user['phone'],
                        'email'=>$user['email'],
                        'status'=>1,
                        'shopping_status'=>1,
                        'comment_status'=>1,
                        'created_at'=>date('Y-m-d H:i:s',$time)
                    ]);
                }
            }

            $res = Db::name('website_buyer')->where(['id'=>$id])->update(['is_verify'=>1,'uid'=>$user['id']]);
            if($res){
                httpRequest('https://shop.gogo198.cn/collect_website/public/?s=/api/sendemail/index',['email'=>$buyer['email'],'title'=>'购购网','content'=>'尊敬的客户，您好！您已成功注册成为购购网会员并成为合作买手，感谢您的支持！']);
                return json(['code'=>0,'msg'=>'确认成功']);
            }
        }else{
            $info = Db::name('website_buyer')->where(['id'=>$id])->find();

            return view('',compact('id','info'));
        }
    }
}