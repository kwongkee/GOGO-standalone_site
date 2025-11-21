<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

use think\Route;

Route::get('/', 'index/index/index'); // 定义GET请求路由规则

//==========独立网站界面===========start
//Route::get('index/website_manage', 'index/Index/website_manage');#企业管理
Route::rule('admin', 'index/index/website_manage');
//    ->pattern(['company_id' => '\d+']);

Route::any('index/get_enterprise_info', 'index/Index/get_enterprise_info');#获取企业信息
Route::any('index/save_domainname', 'index/Index/save_domainname');#保存企业二级域名
#企业网站-后台
Route::any('index/website_official', 'index/Index/website_official');#企业网站
Route::any('index/menu_manage', 'index/Index/menu_manage');#企业菜单
Route::any('index/save_website_basic', 'index/Index/save_website_basic');#企业网站-保存网站配置
Route::any('index/save_website_basic2', 'index/Index/save_website_basic2');#企业网站-保存页头页脚
Route::any('index/save_website_rotate', 'index/Index/save_website_rotate');#企业网站-保存轮播图
Route::any('index/del_website_rotate', 'index/Index/del_website_rotate');#企业网站-删除轮播图
Route::any('index/save_website_menu', 'index/Index/save_website_menu');#企业网站-保存菜单
Route::any('index/del_website_menu', 'index/Index/del_website_menu');#企业网站-删除菜单
Route::any('index/save_website_index', 'index/Index/save_website_index');#企业网站-保存频道
Route::any('index/del_website_index', 'index/Index/del_website_index');#企业网站-删除频道
Route::any('index/get_nextNavbar', 'index/Index/get_nextNavbar');#企业网站-获取下级菜单
Route::any('index/save_website_discovery', 'index/Index/save_website_discovery');#企业网站-保存发现轮播图
Route::any('index/del_website_discovery', 'index/Index/del_website_discovery');#企业网站-删除发现轮播图
#企业网店-后台
Route::any('index/website_shop', 'index/Index/website_shop');#企业网店
Route::any('index/website_basic', 'index/Index/website_basic');#企业网店-店铺信息
Route::any('index/warehouse_manage', 'index/Index/warehouse_manage');#企业网店-仓库管理
Route::any('index/save_warehouse', 'index/Index/save_warehouse');#企业网店-保存仓库
Route::any('index/del_warehouse', 'index/Index/del_warehouse');#企业网店-删除仓库
Route::any('index/shop_head_menu', 'index/Index/shop_head_menu');#企业网店-页头菜单管理
Route::any('index/save_shop_menu', 'index/Index/save_shop_menu');#企业网店-保存页头菜单
Route::any('index/del_shop_menu', 'index/Index/del_shop_menu');#企业网店-删除页头菜单
Route::any('index/shop_scroll_info', 'index/Index/shop_scroll_info');#企业网店-滚动信息管理
Route::any('index/shop_foot_menu', 'index/Index/shop_foot_menu');#企业网店-页脚菜单管理
Route::any('index/save_shop_foot_menu', 'index/Index/save_shop_foot_menu');#企业网店-保存页脚菜单
Route::any('index/del_shop_foot_menu', 'index/Index/del_shop_foot_menu');#企业网店-删除页脚菜单
Route::any('index/shop_social', 'index/Index/shop_social');#企业网店-社交媒体管理
Route::any('index/save_shop_social', 'index/Index/save_shop_social');#企业网店-保存社交媒体
Route::any('index/del_shop_social', 'index/Index/del_shop_social');#企业网店-删除社交媒体
Route::any('index/shop_contact', 'index/Index/shop_contact');#企业网店-联系信息配置
Route::any('index/shop_qualification', 'index/Index/shop_qualification');#企业网店-资质信息管理
Route::any('index/save_shop_qualification', 'index/Index/save_shop_qualification');#企业网店-保存资质信息
Route::any('index/del_shop_qualification', 'index/Index/del_shop_qualification');#企业网店-删除资质信息
Route::any('index/shop_copyright', 'index/Index/shop_copyright');#企业网店-版权信息
Route::any('index/shop_public', 'index/Index/shop_public');#企业网店-公示信息
Route::any('index/shop_rotate', 'index/Index/shop_rotate');#企业网店-轮播图管理
Route::any('index/save_shop_rotate', 'index/Index/save_shop_rotate');#企业网店-保存轮播图管理
Route::any('index/del_shop_rotate', 'index/Index/del_shop_rotate');#企业网店-删除轮播图管理
Route::any('index/shop_guide', 'index/Index/shop_guide');#企业网店-导流模块管理
Route::any('index/save_shop_guide', 'index/Index/save_shop_guide');#企业网店-保存导流模块管理
Route::any('index/del_shop_guide', 'index/Index/del_shop_guide');#企业网店-删除导流模块管理
Route::any('index/shop_guide_content_list', 'index/Index/shop_guide_content_list');#企业网店-模块内容管理
Route::any('index/save_shop_guide_content', 'index/Index/save_shop_guide_content');#企业网店-模块内容管理
Route::any('index/del_shop_guide_content', 'index/Index/del_shop_guide_content');#企业网店-模块内容管理
Route::any('index/shop_guide_content_list2', 'index/Index/shop_guide_content_list2');#企业网店-模块内容管理2
Route::any('index/save_shop_guide_content2', 'index/Index/save_shop_guide_content2');#企业网店-模块内容管理2
Route::any('index/del_shop_guide_content2', 'index/Index/del_shop_guide_content2');#企业网店-模块内容管理2
Route::any('index/shop_recommend', 'index/Index/shop_recommend');#企业网店-首页推荐管理
Route::any('index/save_shop_recommend', 'index/Index/save_shop_recommend');#企业网店-保存首页推荐
Route::any('index/del_shop_recommend', 'index/Index/del_shop_recommend');#企业网店-删除首页推荐
Route::any('index/shop_recommend2', 'index/Index/shop_recommend2');#企业网店-推荐资讯管理
Route::any('index/save_shop_recommend2', 'index/Index/save_shop_recommend2');#企业网店-保存推荐资讯
Route::any('index/del_shop_recommend2', 'index/Index/del_shop_recommend2');#企业网店-删除推荐资讯
Route::any('index/search_result', 'index/Index/search_result');#企业网店-搜索结果管理
Route::any('index/save_product', 'index/Index/save_product');#企业网店-供应管理-新增商品
Route::any('index/get_nextcate', 'index/Index/get_nextcate');#企业网店-供应管理-获取商品分类（下级）
Route::any('index/product_manage', 'index/Index/product_manage');#企业网店-供应管理-管理商品
Route::any('index/del_product', 'index/Index/del_product');#企业网店-供应管理-删除商品
Route::any('index/quicky_selgoods', 'index/Index/quicky_selgoods');#企业网店-供应管理-快速选品
Route::any('index/product_series', 'index/Index/product_series');#企业网店-供应管理-产品系列
Route::any('index/save_product_series', 'index/Index/save_product_series');#企业网店-供应管理-保存产品系列
Route::any('index/del_product_series', 'index/Index/del_product_series');#企业网店-供应管理-删除产品系列
Route::any('index/procurement_manage', 'index/Index/procurement_manage');#企业网店-供应管理-采购管理
Route::any('index/save_procurement', 'index/Index/save_procurement');#企业网店-供应管理-保存采购
Route::any('index/get_goods', 'index/Index/get_goods');#企业网店-供应管理-获取商店所有商品
Route::any('index/del_procurement', 'index/Index/del_procurement');#企业网店-供应管理-删除采购
Route::any('index/save_supplier', 'index/Index/save_supplier');#企业网店-供应管理-保存供应企业
Route::any('index/transfer_manage', 'index/Index/transfer_manage');#企业网店-供应管理-调拔管理
Route::any('index/save_transfer', 'index/Index/save_transfer');#企业网店-供应管理-保存调拔
Route::any('index/del_transfer', 'index/Index/del_transfer');#企业网店-供应管理-删除调拔
Route::any('index/connect_product', 'index/Index/connect_product');#企业网店-供应管理-赠送关联
Route::any('index/save_inventory', 'index/Index/save_inventory');#企业网店-库存管理-出入库存
Route::any('index/get_country_info2', 'index/Index/get_country_info2');#企业网店-获取国地信息
Route::any('index/getarea', 'index/Index/getarea');#企业网店-获取区域信息
Route::any('index/inventory_manage', 'index/Index/inventory_manage');#企业网店-库存管理-管理库存
Route::any('index/save_shelf', 'index/Index/save_shelf');#企业网店-上架管理-新增上架
Route::any('index/get_name', 'index/Index/get_name');#企业网店-上架管理-获取通用信息
Route::any('index/description_manage', 'index/Index/description_manage');#企业网店-上架管理-规则类别管理
Route::any('index/save_description', 'index/Index/save_description');#企业网店-上架管理-保存规则类别
Route::any('index/del_description', 'index/Index/del_description');#企业网店-上架管理-删除规则类别
Route::any('index/billinfo', 'index/Index/billinfo');#企业网店-账单信息
Route::any('index/keywords_manage', 'index/Index/keywords_manage');#企业网店-上架管理-规则分类管理
Route::any('index/save_keywords2', 'index/Index/save_keywords2');#企业网店-上架管理-保存规则分类
Route::any('index/del_keywords', 'index/Index/del_keywords');#企业网店-上架管理-删除规则分类
Route::any('index/rule_manage', 'index/Index/rule_manage');#企业网店-上架管理-规则管理
Route::any('index/save_rule', 'index/Index/save_rule');#企业网店-上架管理-保存规则
Route::any('index/del_rule', 'index/Index/del_rule');#企业网店-上架管理-删除规则
Route::any('index/get_keywords', 'index/Index/get_keywords');#企业网店-上架管理-获取关键词
Route::any('index/get_goods_info', 'index/Index/get_goods_info');#企业网店-上架管理-获取商品信息
Route::any('index/get_goods_param', 'index/Index/get_goods_param');#企业网店-上架管理-获取最近商品参数信息
Route::any('index/spec_arrange', 'index/Index/spec_arrange');#企业网店-上架管理-整理规格
Route::any('index/shelf_manage', 'index/Index/shelf_manage');#企业网店-上架管理-管理上架
Route::any('index/del_shelf', 'index/Index/del_shelf');#企业网店-上架管理-下架商品
Route::any('index/select_shelf', 'index/Index/select_shelf');#企业网店-上架管理-选择上架
Route::any('index/sure_shelf', 'index/Index/sure_shelf');#企业网店-上架管理-确认上架（废弃了）
Route::any('index/save_next_merchant', 'index/Index/save_next_merchant');#企业网店-交易管理-配置下游
Route::any('index/member_manage', 'index/Index/member_manage');#企业网店-买家管理-信息管理
Route::any('index/save_member', 'index/Index/save_member');#企业网店-买家管理-保存买家
Route::any('index/del_member', 'index/Index/del_member');#企业网店-买家管理-删除买家
Route::any('index/merge_member', 'index/Index/merge_member');#企业网店-买家管理-合并买家
Route::any('index/grade_manage', 'index/Index/grade_manage');#企业网店-买家管理-等级管理
Route::any('index/save_grade', 'index/Index/save_grade');#企业网店-买家管理-保存等级
Route::any('index/del_grade', 'index/Index/del_grade');#企业网店-买家管理-删除等级
Route::any('index/tag_manage', 'index/Index/tag_manage');#企业网店-买家管理-标签管理
Route::any('index/save_tag', 'index/Index/save_tag');#企业网店-买家管理-保存标签
Route::any('index/del_tag', 'index/Index/del_tag');#企业网店-买家管理-删除标签
Route::any('index/member_grade_manage', 'index/Index/member_grade_manage');#企业网店-买家管理-分级管理
Route::any('index/save_member_grade', 'index/Index/save_member_grade');#企业网店-买家管理-保存分级
Route::any('index/del_member_grade', 'index/Index/del_member_grade');#企业网店-买家管理-删除分级
Route::any('index/member_tag_manage', 'index/Index/member_tag_manage');#企业网店-买家管理-标识管理
Route::any('index/save_member_tag', 'index/Index/save_member_tag');#企业网店-买家管理-保存标识
Route::any('index/del_member_tag', 'index/Index/del_member_tag');#企业网店-买家管理-删除标识
Route::any('index/online_merge_member', 'index/Index/online_merge_member');#企业网店-买家管理-买家在线同意
Route::any('index/sale_panel_manage', 'index/Index/sale_panel_manage');#企业网店-营销管理-营销面板
Route::any('index/adv_manage', 'index/Index/adv_manage');#企业网店-营销管理-宣传活动
Route::any('index/save_adv', 'index/Index/save_adv');#企业网店-营销管理-保存宣传活动
Route::any('index/del_adv', 'index/Index/del_adv');#企业网店-营销管理-删除宣传活动
Route::any('index/artical_manage', 'index/Index/artical_manage');#企业网店-营销管理-文章管理
Route::any('index/save_artical', 'index/Index/save_artical');#企业网店-营销管理-保存文章
Route::any('index/del_artical', 'index/Index/del_artical');#企业网店-营销管理-删除文章
Route::any('index/market_manage', 'index/Index/market_manage');#企业网店-营销管理-市场管理
Route::any('index/save_market', 'index/Index/save_market');#企业网店-营销管理-保存市场
Route::any('index/del_market', 'index/Index/del_market');#企业网店-营销管理-删除市场（待做）
Route::any('index/directory_manage', 'index/Index/directory_manage');#企业网店-营销管理-市场目录管理
Route::any('index/save_directory', 'index/Index/save_directory');#企业网店-营销管理-保存市场目录
Route::any('index/del_directory', 'index/Index/del_directory');#企业网店-营销管理-删除市场目录
Route::any('index/sorder_manage', 'index/Index/sorder_manage');#企业网店-交易管理-选购管理
Route::any('index/sorder_detail', 'index/Index/sorder_detail');#企业网店-交易管理-选购详情
Route::any('index/porder_manage', 'index/Index/porder_manage');#企业网店-交易管理-订购管理
Route::any('index/cancel_porder', 'index/Index/cancel_porder');#企业网店-交易管理-拒绝订购
Route::any('index/porder_detail', 'index/Index/porder_detail');#企业网店-交易管理-订购详情
Route::any('index/value_introduce', 'index/Index/value_introduce');#企业网店-交易管理-属性详情
Route::any('index/porder_edit', 'index/Index/porder_edit');#企业网店-交易管理-订购单修改
Route::any('index/order_manage', 'index/Index/order_manage');#企业网店-交易管理-支付单管理
Route::any('index/cancel_order', 'index/Index/cancel_order');#企业网店-交易管理-取消退订
Route::any('index/procure_manage', 'index/Index/procure_manage');#企业网店-物流管理-采购管理
Route::any('index/discount_manage', 'index/Index/discount_manage');#企业网店-交易管理-折扣管理
Route::any('index/save_discount', 'index/Index/save_discount');#企业网店-交易管理-保存折扣
Route::any('index/del_discount', 'index/Index/del_discount');#企业网店-交易管理-删除折扣
Route::any('index/first_logistics_manage', 'index/Index/first_logistics_manage');#企业网店-物流管理-打包贴单
Route::any('index/consolidation_manage', 'index/Index/consolidation_manage');#企业网店-物流管理-国内集货
Route::any('index/waybill_info', 'index/Index/waybill_info');#企业网店-物流管理-集货详情
Route::any('index/parcel_bill', 'index/Index/parcel_bill');#企业网店-物流管理-包裹账单
Route::any('index/parcel_billinfo', 'index/Index/parcel_billinfo');#企业网店-物流管理-账单详情
Route::any('index/express_info', 'index/Index/express_info');#企业网店-物流管理-物流信息
Route::any('index/packing_list', 'index/Index/packing_list');#企业网店-物流管理-装箱单
Route::any('index/transport_process1', 'index/Index/transport_process1');#企业网店-物流管理-出口申报等（开发中）
Route::any('index/transport_process2', 'index/Index/transport_process2');#企业网店-物流管理-出口申报等（开发中）
Route::any('index/transport_process3', 'index/Index/transport_process3');#企业网店-物流管理-出口申报等（开发中）
Route::any('index/transport_process4', 'index/Index/transport_process4');#企业网店-物流管理-出口申报等（开发中）
Route::any('index/payment_manage', 'index/Index/payment_manage');#企业网店-支付管理-支付管理
Route::any('index/save_payment', 'index/Index/save_payment');#企业网店-支付管理-添加支付
Route::any('index/del_payment', 'index/Index/del_payment');#企业网店-支付管理-删除支付

#企业群组-后台
Route::any('index/website_group', 'index/Index/website_group');#企业服务-客服群组管理
Route::any('index/save_customer_direction', 'index/Index/save_customer_direction');#企业服务-保存显示位置
Route::any('index/save_customer_group', 'index/Index/save_customer_group');#企业服务-保存群组
Route::any('index/comlang_manage', 'index/Index/comlang_manage');#企业服务-场景用语
Route::any('index/save_comlang', 'index/Index/save_comlang');#企业服务-保存场景用语
Route::any('index/del_comlang', 'index/Index/del_comlang');#企业服务-删除场景用语
Route::any('index/comlang_manage2', 'index/Index/comlang_manage2');#企业服务-常用语
Route::any('index/save_comlang2', 'index/Index/save_comlang2');#企业服务-保存常用语
Route::any('index/del_comlang2', 'index/Index/del_comlang2');#企业服务-删除常用语
Route::any('index/group_member_role', 'index/Index/group_member_role');#企业服务-角色管理
Route::any('index/save_group_member_role', 'index/Index/save_group_member_role');#企业服务-保存角色
Route::any('index/del_group_member_role', 'index/Index/del_group_member_role');#企业服务-删除角色
Route::any('index/group_member', 'index/Index/group_member');#企业服务-组员管理
Route::any('index/save_group_member', 'index/Index/save_group_member');#企业服务-保存组员
Route::any('index/del_group_member', 'index/Index/del_group_member');#企业服务-删除组员

#企业服务-后台
Route::any('index/website_ai', 'index/Index/website_ai');#企业服务-服务管理
Route::any('index/knowledge_list', 'index/Index/knowledge_list');#企业服务-知识管理
Route::any('index/save_knowledge', 'index/Index/save_knowledge');#企业网店-保存知识
Route::any('index/del_knowledge', 'index/Index/del_knowledge');#企业网店-删除知识
Route::any('index/active_knowledge', 'index/Index/active_knowledge');#企业网店-上架知识至平台
Route::any('index/hot_product', 'index/Index/hot_product');#企业网店-添加热门商品
Route::any('index/chat_history', 'index/Index/chat_history');#企业网店-商家聊天历史列表
Route::any('index/chat_histories', 'index/Index/chat_histories');#企业网店-商家与客户聊天历史
Route::any('index/chat_association_info', 'index/Index/chat_association_info');#企业网店-当前对话的关联信息

//**商家商城前端界面**====START
//Route::get('merch/merch_shop_index', 'index/Merch/merch_shop_index'); // 定义GET请求路由规则
Route::rule('shops', 'index/Merch/merch_shop_index');
//    ->pattern(['company_id' => '\d+']);
Route::any('merch/rate_detail', 'index/Merch/rate_detail'); // 定义GET请求路由规则
Route::any('merch/detail', 'index/Merch/detail'); // 菜单详情
Route::any('merch/goods_list', 'index/Merch/goods_list'); // 商品结果页
Route::any('merch/taozg', 'index/Merch/taozg'); // 淘中国
Route::any('merch/advice', 'index/Merch/advice'); // 建议
Route::any('merch/social_detail', 'index/Merch/social_detail'); // 社媒
Route::any('merch/qualific', 'index/Merch/qualific'); // 资质
Route::any('merch/rule_list', 'index/Merch/rule_list'); // 规则列表
Route::any('merch/version_list', 'index/Merch/version_list'); // 版本列表
Route::any('merch/rule_detail', 'index/Merch/rule_detail'); // 规则详情
Route::any('merch/getFrame', 'index/Merch/getFrame'); // 规则详情
//**商家商城界面**====END

//**商家网站前端界面（部分）**====START
Route::get('index/detail', 'index/Index/detail');
Route::get('web', 'index/index/merch_website_index');
//    ->pattern(['company_id' => '\d+']);
Route::any('index/change_date', 'index/Index/change_date');
Route::get('index/contact_detail', 'index/Index/contact_detail');
Route::any('index/advice', 'index/Index/advice');
Route::any('index/change_language', 'index/Index/change_language');
Route::any('index/friendly_link', 'index/Index/friendly_link');
Route::any('index/enterprise_news', 'index/Index/enterprise_news');#购购动态
Route::any('index/enterprise_news_detail', 'index/Index/enterprise_news_detail');#购购动态详情
Route::any('index/news_detail', 'index/Index/news_detail');
Route::any('index/all_news', 'index/Index/all_news');
Route::any('index/cross_news', 'index/Index/cross_news');#跨境新闻
Route::any('index/cross_news_detail', 'index/Index/cross_news_detail');#跨境新闻详情
Route::any('index/qualific', 'index/Index/qualific');

Route::any('index/rule_list', 'index/Index/rule_list');#平台规则-列表
Route::any('index/version_list', 'index/Index/version_list');#平台规则-版本列表
Route::any('index/rule', 'index/Index/rule');#平台规则-内容
Route::any('index/more_imgtxt', 'index/Index/more_imgtxt');#瀑布流图文
Route::any('index/txt_detail', 'index/Index/txt_detail');#图文详情
Route::any('index/msg_detail', 'index/Index/msg_detail');#图文详情
//**商家网站前端界面（部分）**====end

Route::any('index/customer_login', 'index/Index/customer_login');#登录
Route::any('index/send_code', 'index/Index/send_code');
Route::any('index/change_account', 'index/Index/change_account');#切换账号
//==========独立网站界面===========end

#账户管理
Route::any('index/save_contact', 'index/Index/save_contact');#授权登录时，补充联系方式（邮箱+手机号）
Route::any('index/merchant_reg', 'index/Index/merchant_reg');
Route::any('index/login_log', 'index/Index/login_log');#记录授权登录信息
Route::any('index/authlogin_result', 'index/Index/authlogin_result');#授权登录结果页
Route::any('index/merchant_reg', 'index/Index/merchant_reg');//商户认证
Route::any('index/send_code2', 'index/Index/send_code2');#实名认证
Route::any('index/auth_info', 'index/Index/auth_info');#认证信息
Route::any('index/connect_info', 'index/Index/connect_info');#关联管理
Route::any('index/intelligent', 'index/Index/intelligent');#智能服务体验-2025-08-21
Route::any('index/knowledge_list', 'index/Index/knowledge_list');#智能服务体验-多语文档列表-2025-08-21
Route::any('index/save_knowledge', 'index/Index/save_knowledge');#智能服务体验-多语文档编辑-2025-08-21
//账户管理end

//物流官网start
Route::any('gather/article', 'index/Gather/article');
Route::any('gather/notice_detail', 'index/Gather/notice_detail');
Route::any('gather/suggest', 'index/Gather/suggest');
Route::any('gather/send_code', 'index/Gather/send_code');
Route::any('gather/getminiprogramcode', 'index/Gather/getminiprogramcode');#计算线路的计费重和计费额
Route::any('gather/getweixin', 'index/Gather/getweixin');#获取微信账号信息
//物流官网end

//上传方法start
Route::any('index/upload_file', 'index/Index/upload_file');
Route::any('index/upload_diy_file', 'index/Index/upload_diy_file');
//上传方法end

//每日列出日志清单给老板
Route::any('log', 'index/Loggin/index');
Route::any('log_detail', 'index/Loggin/log_detail');
Route::any('notice_boss', 'index/Loggin/notice_boss');

//SEO优化
// Route::get('sitemap.xml', 'index/Index/sitemap');
Route::get('robots.txt', 'index/Index/robots');
Route::get('sitemap.xml', 'index/sitemap/index');

//api
Route::any('api/chatgpt', 'api/Chatgpt/index');
Route::any('api/logout', 'api/Account/index');

#auth0
Route::any('api/goto_login', 'api/Authlogin/goto_login');
Route::any('api/authorization_callback', 'api/Authlogin/authorization_callback');
Route::any('api/token_callback', 'api/Authlogin/token_callback');
Route::any('api/protected_resource', 'api/Authlogin/protected_resource');
Route::any('api/userinfo_callback', 'api/Authlogin/userinfo_callback');
Route::any('api/auto_login', 'api/Authlogin/auto_login');
Route::any('api/facebook_callback', 'api/Authlogin/facebook_callback');

// 捕获所有未定义路由 → 301
Route::miss(function() {
    return redirect('https://dtc.gogo198.net/', 301);
});
