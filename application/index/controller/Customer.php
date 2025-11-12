<?php
namespace app\index\controller;

use think\Request;
use think\Db;
use think\Controller;
use think\Log;

class Customer
{
    public $website_name='';
    public $website_keywords='';
    public $website_description='';
    public $website_ico='';
    public $website_sico='';
    public $website_tel='';
    public $website_email='';
    public $website_copyright='';
    public $website_color='';
    public $website_color_inner='';
    public $website_colorword='';
    public $website_inpic='';
    public $website_contact=[];
    public $apps = [];
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
        $this->website_contact = Db::name('website_contact')->where(['system_id' => 4, 'company_id' => 0])->select();
        $this->apps = Db::name('website_list')->select();
    }

    public function customer_online(Request $request){
        $dat = input();
        $pid = isset($dat['pid'])?intval($dat['pid']):0;
        $id = isset($dat['id'])?intval($dat['id']):0;

        $height = '';
        if (isMobile()) {
            $height = '55%';
        }else{
            $height = '45%';
        }

        $control_height = isset($dat['control_height']) ? $dat['control_height'] : $height;

        if($request->isAjax()){
            $user = Db::name('website_user')->where(['id'=>session('account.id')])->find();
            if($dat['pa']==1){
                #插入数据表

                $content = json_encode($dat['content'],true);
                $merchant_master = 0;
                #判断当前页面是否商品页面
                $origin_page = trim($dat['origin_page']);

                Db::name('website_chatlist')->insert([
                    'pid'=>$dat['pid'],
                    'uid'=>$user['id'],
                    'merchant_master'=>$merchant_master,
                    'who_send'=>$dat['who_send'],
                    'is_read'=>0,
                    'content_type'=>$dat['content_type'],
                    'content'=>$content,
                    'quote_text'=>isset($dat['quote_text'])?trim($dat['quote_text']):'',
                    'origin_page'=>$origin_page,
                    'createtime'=>time()
                ]);

                #通知客服运营人员
                $postData = json_encode(['is_notice'=>1],true);
                httpRequest('https://shop.gogo198.cn/collect_website/public/?s=/api/getgoods/contact_customer',$postData,array(
                    'Content-Type: application/json; charset=utf-8',
                    'Content-Length:' . strlen($postData),
                    'Cache-Control: no-cache',
                    'Pragma: no-cache'
                ));

                return json(['code'=>0,'msg'=>'已发送']);
            }
            elseif($dat['pa']==2){
                #获取历史消息，以日为数组
                $list = Db::name('website_chatlist')->where(['uid'=>$user['id']])->order('id','asc')->field(['id','createtime'])->select();
                if(!empty($list)){
                    $pid = $list[0]['id'];
                }else{
                    $pid = 0;
                }

                $chat_group = [];
                $who_send = 2;
                if(!empty($list)){
                    #聊天记录以时间为数组
                    $group = [];
                    foreach($list as $k=>$v){
                        $time = date('Y-m-d',$v['createtime']);
                        if(empty($group)){
                            $group = array_merge($group,[$time]);
                        }else{
                            if(!in_array($time,$group)){
                                $group = array_merge($group,[$time]);
                            }
                        }
                    }
                    sort($group);
                    #根据时间查找聊天记录
                    foreach($group as $k=>$v){
                        $starttime = strtotime($v.' 00:00:00');
                        $endtime = strtotime($v.' 23:59:59');
                        $chat_group[$k]['time'] = date('Y年m月d日',$starttime);

                        $chat_group[$k]['info'] = Db::name('website_chatlist')->where(['uid' => $user['id']])->whereBetween('createtime', [$starttime, $endtime])->order('createtime', 'asc')->select();
                    }
                    #整理数组
                    foreach($chat_group as $k=>$v){
                        foreach($v['info'] as $kk=>$vv){
                            if($vv['who_send']==1 && !empty($vv['kefu_id'])){
                                #总后台客服
//                                $user_name = Db::name('foll_user')->where(['id'=>$vv['kefu_id']])->find()['id'];
                                $chat_group[$k]['info'][$kk]['kefu_name'] = '客服';
                            }
                            $chat_group[$k]['info'][$kk]['content'] = json_decode($vv['content'],true);
                            $chat_group[$k]['info'][$kk]['createtime'] = date('H:i',$vv['createtime']);
                        }
                    }

                    #全部设置成已看记录
                    Db::name('website_chatlist')->where(['pid' => $pid, 'who_send' => 1,'is_read'=>0])->update(['is_read' => 1]);
                }

                return json(['code'=>0,'data'=>$chat_group,'pid'=>$pid]);
            }
            elseif($dat['pa']==3){
                #撤回消息（查看当前聊天有无被对方看过）
                $id = intval($dat['id']);
                $nowchat = Db::name('website_chatlist')->where(['id'=>$id])->find();
                if($nowchat['is_read']==0){
                    Db::name('website_chatlist')->where(['id'=>$id])->update(['is_withdraw'=>1]);
                }
                return json(['code'=>0,'msg'=>'撤回成功']);
            }
        }else{
            $who_send = 2;
            $menu = Db::name('centralize_manage_menu')->where(['id'=>intval($dat['pid'])])->find();
            $list = Db::name('centralize_manage_menu')->where(['pid'=>$menu['pid']])->select();

            #讨论内容
            $info['taolun'] = '';
            if($pid==237){
                $info['taolun'] = '订单';
                $info['detail'] = Db::name('website_order_list')->where(['id'=>$id])->find()['ordersn'];
                $info['url'] = '//www.gogo198.cn/cart/cart_detail?id='.$id;
            }elseif($pid==238){
                $info['taolun'] = '商品';
                $info['detail'] = Db::connect($this->config)->name('goods')->where(['goods_id'=>$id])->find()['goods_name'];
                $info['url'] = '//www.gogo198.cn/goods-'.$id.'.html';
            }elseif($pid==239){
                $info['taolun'] = '议题';
                $info['detail'] = Db::name('decision_topics')->where(['id'=>$id])->find()['name'];
                $info['url'] = '/?s=merchant/topics_detail&id='.$id.'&is_edit='.base64_encode('1');
            }else{
                $info['taolun'] = '其他';
                $info['detail'] = '';
                $info['url'] = '';
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

            return view('',compact('who_send','control_height','list','id','pid','info','website'));
        }
    }

    public function discuss_online(Request $request){
        $dat = input();
        $id = isset($dat['id'])?intval($dat['id']):0;
        $pid = isset($dat['pid'])?intval($dat['pid']):239;

        $height = '';
        if (isMobile()) {
            $height = '55%';
        }else{
            $height = '45%';
        }

        $control_height = isset($dat['control_height']) ? $dat['control_height'] : $height;

        if($request->isAjax()){
            $user = Db::name('website_user')->where(['id'=>session('account.id')])->find();
            if($dat['pa']==1){
                #插入数据表

                $content = json_encode($dat['content'],true);
                $merchant_master = 0;
                #判断当前页面是否商品页面
                $origin_page = trim($dat['origin_page']);

                Db::name('website_decision_chatlist')->insert([
                    'topics_id'=>$dat['id'],
                    'uid'=>$user['id'],
                    'merchant_master'=>$merchant_master,
                    'who_send'=>$dat['who_send'],
                    'is_read'=>0,
                    'content_type'=>$dat['content_type'],
                    'content'=>$content,
                    'quote_text'=>isset($dat['quote_text'])?trim($dat['quote_text']):'',
                    'origin_page'=>$origin_page,
                    'createtime'=>time()
                ]);

                return json(['code'=>0,'msg'=>'已发送']);
            }
            elseif($dat['pa']==2){
                #获取历史消息，以日为数组
                $list = Db::name('website_decision_chatlist')->where(['topics_id'=>$id])->order('id','asc')->field(['id','createtime'])->select();

                if(!empty($list)){
                    $pid = $list[0]['id'];
                }else{
                    $pid = 0;
                }

                $chat_group = [];
                $who_send = 2;
                if(!empty($list)){
                    #聊天记录以时间为数组
                    $group = [];
                    foreach($list as $k=>$v){
                        $time = date('Y-m-d',$v['createtime']);
                        if(empty($group)){
                            $group = array_merge($group,[$time]);
                        }else{
                            if(!in_array($time,$group)){
                                $group = array_merge($group,[$time]);
                            }
                        }
                    }
                    sort($group);
                    #根据时间查找聊天记录
                    foreach($group as $k=>$v){
                        $starttime = strtotime($v.' 00:00:00');
                        $endtime = strtotime($v.' 23:59:59');
                        $chat_group[$k]['time'] = date('Y年m月d日',$starttime);

                        $chat_group[$k]['info'] = Db::name('website_decision_chatlist')->where(['topics_id' => $id])->whereBetween('createtime', [$starttime, $endtime])->order('createtime', 'asc')->select();
                    }
                    #整理数组
                    foreach($chat_group as $k=>$v){
                        foreach($v['info'] as $kk=>$vv){
                            if($vv['who_send']==2 && $vv['uid']!=$user['id']){
                                #其他人
                                $chat_group[$k]['info'][$kk]['who_send']=1;

//                                $chat_group[$k]['info'][$kk]['kefu_name'] = substr($user['realname'], -2);
//                                $chat_group[$k]['info'][$kk]['kefu_name'] = Db::name('website_user')->where(['id'=>$vv['uid']])->find()['realname'];
                                $group_id = Db::name('decision_topics')->where(['id'=>$id])->field('group_id')->find()['group_id'];
                                $chat_group[$k]['info'][$kk]['kefu_name'] = Db::name('decision_group_member')->where(['user_id'=>$vv['uid'],'group_id'=>$group_id])->find()['title'];
                            }
                            $chat_group[$k]['info'][$kk]['content'] = json_decode($vv['content'],true);
                            $chat_group[$k]['info'][$kk]['createtime'] = date('H:i',$vv['createtime']);
                        }
                    }

                    #全部设置成已看记录
                    Db::name('website_decision_chatlist')->where(['topics_id' => $id, 'is_read'=>0])->whereRaw('uid <> '.$user['id'])->update(['is_read' => 1]);

                }

                return json(['code'=>0,'data'=>$chat_group,'pid'=>$pid]);
            }
            elseif($dat['pa']==3){
                #撤回消息（查看当前聊天有无被对方看过）
                $id = intval($dat['id']);
                $nowchat = Db::name('website_decision_chatlist')->where(['id'=>$id])->find();
                if($nowchat['is_read']==0){
                    Db::name('website_decision_chatlist')->where(['id'=>$id])->update(['is_withdraw'=>1]);
                }
                return json(['code'=>0,'msg'=>'撤回成功']);
            }
        }else{
            $who_send = 2;
            $menu = Db::name('centralize_manage_menu')->where(['id'=>$pid])->find();
            $list = Db::name('centralize_manage_menu')->where(['pid'=>$menu['pid']])->select();
            #讨论内容
            $info['taolun'] = '议题';
            $info['detail'] = Db::name('decision_topics')->where(['id'=>$id])->find()['name'];
            $info['url'] = '/?s=member/topics_detail&id='.$id.'&is_edit='.base64_encode('1');

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

            return view('',compact('who_send','control_height','list','id','pid','info','website'));
        }
    }

    public function upload_files(Request $request){
        $data = input();

        $file = request()->file('file');

        // 准备要上传的文件
        $file_name = $_FILES["file"]['name']; // 获取文件名
        $file_size = $_FILES["file"]['size']; // 获取文件大小
        try {

            $file_data = array(
                "name" => $file_name,
                "type" => $_FILES["file"]['type'],
                "tmp_name" => $_FILES['file']['tmp_name'],
                "error" => 0,
                "size" => $file_size,
            );
            $post_data = json_encode(['folder' => $data['folder'], 'type' => $data['type'], 'file' => $file_data],true);
            $res = httpRequest('https://shop.gogo198.cn/collect_website/public/?s=api/uploadfile/index', $post_data,array(
                'Content-Type: application/json; charset=utf-8',
                'Content-Length:' . strlen($post_data),
                'Cache-Control: no-cache',
                'Pragma: no-cache'
            ));
            $res = json_decode($res,true);
            if($res['code']==1){

                return json(["code" => 1, "message" => "上传成功", "file_path" => $res['file_path']]);
            }else{
                return json(["code" => 0, "message" => "上传失败", "path" => "" ]);
            }
        }catch(\Exception $e){
            dd($e);
        }
    }
}