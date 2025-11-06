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
//独立网站界面===========start
Route::get('index/website_manage', 'index/Index/website_manage');#企业管理
Route::any('index/get_enterprise_info', 'index/Index/get_enterprise_info');#获取企业信息
Route::any('index/save_domainname', 'index/Index/save_domainname');#保存企业二级域名
#企业网站
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
#企业网店
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
Route::any('index/shop_shop_guide_content2', 'index/Index/save_shop_guide_content2');#企业网店-模块内容管理2
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
Route::any('index/keywords_manage', 'index/Index/keywords_manage');#企业网店-上架管理-规则分类管理
Route::any('index/save_keywords2', 'index/Index/save_keywords2');#企业网店-上架管理-保存规则分类
Route::any('index/del_keywords', 'index/Index/del_keywords');#企业网店-上架管理-删除规则分类
Route::any('index/rule_manage', 'index/Index/rule_manage');#企业网店-上架管理-规则管理
Route::any('index/save_rule', 'index/Index/save_rule');#企业网店-上架管理-保存规则
Route::any('index/del_rule', 'index/Index/del_rule');#企业网店-上架管理-删除规则
Route::any('index/get_keywords', 'index/Index/get_keywords');#企业网店-上架管理-获取关键词
Route::any('index/get_goods_info', 'index/Index/get_goods_info');#企业网店-上架管理-获取商品信息
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

#企业群组
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
#企业服务
Route::any('index/website_ai', 'index/Index/website_ai');#企业服务-服务管理
Route::any('index/knowledge_list', 'index/Index/knowledge_list');#企业服务-知识管理
Route::any('index/save_knowledge', 'index/Index/save_knowledge');#企业网店-保存知识
Route::any('index/del_knowledge', 'index/Index/del_knowledge');#企业网店-删除知识
Route::any('index/active_knowledge', 'index/Index/active_knowledge');#企业网店-上架知识至平台
Route::any('index/hot_product', 'index/Index/hot_product');#企业网店-添加热门商品
Route::any('index/chat_history', 'index/Index/chat_history');#企业网店-商家聊天历史列表
Route::any('index/chat_histories', 'index/Index/chat_histories');#企业网店-商家与客户聊天历史
Route::any('index/chat_association_info', 'index/Index/chat_association_info');#企业网店-当前对话的关联信息

//**商家官网界面**====START
Route::get('index/merch_website_index', 'index/index/merch_website_index'); // 定义GET请求路由规则
//**商家官网界面**====END

//**商家商城界面**====START
Route::get('merch/merch_shop_index', 'index/Merch/merch_shop_index'); // 定义GET请求路由规则
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

//独立网站界面===========end
Route::get('index/detail', 'index/Index/detail');
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
Route::any('index/getLanguage', 'index/Index/getLanguage');
Route::any('index/chooseMarket', 'index/Index/chooseMarket');#选市场
Route::any('index/customers', 'index/Index/customers');#找客户
Route::any('index/background_email', 'index/Index/background_email');#全球客户背景调查-邮箱
Route::any('index/background_site', 'index/Index/background_site');#全球客户背景调查-网站
Route::any('index/background_company', 'index/Index/background_company');#全球客户背景调查-企业
Route::any('index/background_searchworld', 'index/Index/background_searchworld');#全球客户背景调查-查注册信息
Route::any('index/background_overseasreport', 'index/Index/background_overseasreport');#全球客户背景调查-信用报告
Route::any('index/KYBreport', 'index/Index/KYBreport');#全球客户背景调查-KYBreport（隐藏）
Route::any('index/searchengine', 'index/Index/searchengine');#全球客户背景调查-搜索引擎获客
Route::any('index/domainsearch', 'index/Index/domainsearch');#全球客户背景调查-域名获客
Route::any('index/findcustomers', 'index/Index/findcustomers');#全球客户背景调查-海关数据
Route::any('index/enterprise', 'index/Index/enterprise');#全球客户背景调查-社交媒体获客
Route::any('index/goto_gld', 'index/Index/goto_gld');#跳转到格兰德
Route::any('index/get_quotetext', 'index/Index/get_quotetext');#获得收费文本信息
Route::any('index/save_quotetext', 'index/Index/save_quotetext');#保存商户收费文本的修改信息
Route::any('index/quote_chat', 'index/Index/quote_chat');#聊天工具
Route::any('index/rule_list', 'index/Index/rule_list');#平台规则-列表
Route::any('index/version_list', 'index/Index/version_list');#平台规则-版本列表
Route::any('index/rule', 'index/Index/rule');#平台规则-内容
Route::any('index/more_imgtxt', 'index/Index/more_imgtxt');#瀑布流图文
Route::any('index/txt_detail', 'index/Index/txt_detail');#图文详情
Route::any('index/msg_detail', 'index/Index/msg_detail');#图文详情

#账户管理
Route::any('index/save_contact', 'index/Index/save_contact');#授权登录时，补充联系方式（邮箱+手机号）
Route::any('index/save_basicinfo', 'index/Index/save_basicinfo');#主动注册的第二次进来就要补充资料
Route::any('index/account_manage', 'index/Index/account_manage');
Route::any('index/merchant_reg', 'index/Index/merchant_reg');
Route::any('index/customer_reg', 'index/Index/customer_reg');
Route::any('index/customer_login', 'index/Index/customer_login');
Route::any('index/login_log', 'index/Index/login_log');#记录授权登录信息
Route::any('index/authlogin_result', 'index/Index/authlogin_result');#授权登录结果页
Route::any('index/account_center', 'index/Index/account_center');
Route::any('index/basic_info', 'index/Index/basic_info');
Route::any('index/service_manage', 'index/Index/service_manage');
Route::any('index/buyer_service', 'index/Index/buyer_service');
Route::any('index/seller_service', 'index/Index/seller_service');
Route::any('index/distri_service', 'index/Index/distri_service');
Route::any('index/merchant_reg', 'index/Index/merchant_reg');//商户认证
Route::any('index/send_code', 'index/Index/send_code');
Route::any('index/send_code2', 'index/Index/send_code2');#实名认证
Route::any('index/connect_app', 'index/Index/connect_app');
Route::any('index/distri_service', 'index/Index/distri_service');#分销服务
Route::any('index/apply_distr', 'index/Index/apply_distr');#分销服务-申请分销
Route::any('index/approve_distr', 'index/Index/approve_distr');#分销服务-审批分销
Route::any('index/trade_manage', 'index/Index/trade_manage');#交易管理

Route::any('index/tradeflow', 'index/Index/tradeflow');#交易管理-交易流水
Route::any('index/tradeflow_buyer', 'index/Index/tradeflow_buyer');
Route::any('index/tb_order_detail', 'index/Index/tb_order_detail');#订单详情
Route::any('index/prescription_detail', 'index/Index/prescription_detail');#处方详情
Route::any('index/tradeflow_seller', 'index/Index/tradeflow_seller');
Route::any('index/tradeflow_distr', 'index/Index/tradeflow_distr');

Route::any('index/settlement', 'index/Index/settlement');#交易管理-结算管理
Route::any('index/trade_sure', 'index/Index/trade_sure');
Route::any('index/collect', 'index/Index/collect');
Route::any('index/account_list', 'index/Index/account_list');#账户配置
Route::any('index/save_account', 'index/Index/save_account');#添加账户
Route::any('index/del_account', 'index/Index/del_account');#删除账户
Route::any('index/save_collect', 'index/Index/save_collect');#收款发起
Route::any('index/collect_manage', 'index/Index/collect_manage');#收款管理
Route::any('index/check_collect', 'index/Index/check_collect');#收款管理-收款待确认
Route::any('index/collect_list', 'index/Index/collect_list');#收款管理-收款进度列表
Route::any('index/collect_status', 'index/Index/collect_status');#收款管理-收款状态列表
Route::any('index/collect_detail', 'index/Index/collect_detail');#收款管理-收款详情
Route::any('index/withdraw_list', 'index/Index/withdraw_list');#收款管理-提现管理
Route::any('index/save_withdraw', 'index/Index/save_withdraw');#收款管理-马上提现
Route::any('index/withdraw_detail', 'index/Index/withdraw_detail');#提现详情

Route::any('index/bussiness_manage', 'index/Index/bussiness_manage');#业务中心
Route::any('index/bussiness_person', 'index/Index/bussiness_person');#业务中心
Route::any('index/cross_gather', 'index/Index/cross_gather');#业务中心
Route::any('index/cross_buy', 'index/Index/cross_buy');#业务中心
Route::any('index/bussiness_merch', 'index/Index/bussiness_merch');#业务中心
Route::any('index/chat', 'index/Index/chat');#聊天界面

Route::any('index/order_sure', 'index/Index/order_sure');
Route::any('index/bill_sure', 'index/Index/bill_sure');
Route::any('index/view_bill', 'index/Index/view_bill');

Route::any('index/inquiry_list', 'index/Index/inquiry_list');#询价中心-询价表单管理
Route::any('index/inquiry_template_manage', 'index/Index/inquiry_template_manage');#询价中心-询价模板管理
Route::any('index/inquiry_template', 'index/Index/inquiry_template');#询价中心-询价模板列表
Route::any('index/create_inquiry_template', 'index/Index/create_inquiry_template');#询价中心-创建询价模板
Route::any('index/inquiry_info', 'index/Index/inquiry_info');#询价中心-询价详情
Route::any('index/save_inquiry', 'index/Index/save_inquiry');#询价中心-发起询价
Route::any('index/save_page_inquiry', 'index/Index/save_page_inquiry');#询价中心-发起网页模板询价
Route::any('index/thanks', 'index/Index/thanks');#询价中心-发起询价-感谢使用
Route::any('index/inquiry_buss', 'index/Index/inquiry_buss');#询价中心-业务列表
Route::any('index/inquiry_direction', 'index/Index/inquiry_direction');#询价中心-询价方向（模板/表单）
Route::any('index/inquiry_quote', 'index/Index/inquiry_quote');#询价中心-报价列表
Route::any('index/quote_detail', 'index/Index/quote_detail');#询价中心-报价详情
Route::any('index/account_reg', 'index/Index/account_reg');#询价中心-会员认证
Route::any('index/quote_direction', 'index/Index/quote_direction');#报价中心
Route::any('index/share_order', 'index/Index/share_order');#报价中心-分享下单
Route::any('index/quote_list', 'index/Index/quote_list');#报价中心-报价表单
Route::any('index/share_inquiry', 'index/Index/share_inquiry');#报价中心-分享询价
Route::any('index/save_quote', 'index/Index/save_quote');#报价中心-询价详情
Route::any('index/quote_info', 'index/Index/quote_info');#报价中心-询价详情
Route::any('index/save_template', 'index/Index/save_template');#报价中心-添加模板
Route::any('index/select_template', 'index/Index/select_template');#报价中心-选择模板
Route::any('index/upload_template', 'index/Index/upload_template');#报价中心-上架报价
Route::any('index/save_upload', 'index/Index/save_upload');#报价中心-保存上架报价
Route::any('index/upload_list', 'index/Index/upload_list');#报价中心-上架报价列表
Route::any('index/get_country_lines', 'index/Index/get_country_lines');#报价中心-获取当前国线路
Route::any('index/warehouse_reservation', 'index/Index/warehouse_reservation');#仓库预订
Route::any('index/manage_reservation', 'index/Index/manage_reservation');#仓库预订-管理订仓
Route::any('index/add_reservation', 'index/Index/add_reservation');#仓库预订-新增订仓
Route::any('index/edit_booking', 'index/Index/edit_booking');#仓库预订-修改订仓
Route::any('index/package_forecast', 'index/Index/package_forecast');#包裹预报
Route::any('index/add_forecast', 'index/Index/add_forecast');#包裹预报-新增预报
Route::any('index/order_info', 'index/Index/order_info');#包裹预报-新增预报-进入预报
Route::any('index/package_info', 'index/Index/package_info');#包裹预报-新增预报-修改预报
Route::any('index/manage_forecast', 'index/Index/manage_forecast');#包裹预报-管理预报
Route::any('index/share_orders', 'index/Index/share_orders');#包裹预报-分享预报单
Route::any('index/generate_distribute_code', 'index/Index/generate_distribute_code');#小程序-我的分销码
Route::any('index/del_operation', 'index/Index/del_operation');#删除操作
Route::any('index/distr_recon', 'index/Index/distr_recon');#分销对账列表
Route::any('index/save_recon', 'index/Index/save_recon');#添加对账
Route::any('index/del_recon', 'index/Index/del_recon');#删除对账
Route::any('index/share_recon', 'index/Index/share_recon');#分享对账
Route::any('index/view_recon', 'index/Index/view_recon');#查看对账单
Route::any('index/save_newregion', 'index/Index/save_newregion');#新增国地
Route::any('index/get_region', 'index/Index/get_region');#获取该国下的区域
Route::any('index/save_overdue', 'index/Index/save_overdue');#保存账单逾期设置
Route::any('index/bill_center', 'index/Index/bill_center');#账单中心
Route::any('index/outstanding_list', 'index/Index/outstanding_list');#未结账单
Route::any('index/notpay_list', 'index/Index/notpay_list');#未结账单-未支付
Route::any('index/notcollect_list', 'index/Index/notcollect_list');#未结账单-未收款
Route::any('index/finish_list', 'index/Index/finish_list');#已结账单
Route::any('index/member_level', 'index/Index/member_level');#会员等级
Route::any('index/info_center', 'index/Index/info_center');#信息管理
Route::any('index/connect_manage', 'index/Index/connect_manage');#关联管理
Route::any('index/sure_mange', 'index/Index/sure_mange');#确认管理
Route::any('index/consume_center', 'index/Index/consume_center');#消费管理
Route::any('index/bill_manage', 'index/Index/bill_manage');#账单管理
Route::any('index/aftersales_manage', 'index/Index/aftersales_manage');#售后管理
Route::any('index/distr_up', 'index/Index/distr_up');#对接上级
Route::any('index/distr_down', 'index/Index/distr_down');#管理下级
Route::any('index/distr_settlement', 'index/Index/distr_settlement');#分销结算
Route::any('index/finance_center', 'index/Index/finance_center');#财务管理
Route::any('index/change_identity', 'index/Index/change_identity');#切换身份
Route::any('index/merchant_manage', 'index/Index/merchant_manage');#商家中心
Route::any('index/change_account', 'index/Index/change_account');#切换账号
Route::any('index/logout_account', 'index/Index/logout_account');#注销账号
Route::any('index/auth_info', 'index/Index/auth_info');#认证信息
Route::any('index/connect_info', 'index/Index/connect_info');#关联管理
Route::any('index/intelligent', 'index/Index/intelligent');#智能服务体验-2025-08-21
Route::any('index/knowledge_list', 'index/Index/knowledge_list');#智能服务体验-多语文档列表-2025-08-21
Route::any('index/save_knowledge', 'index/Index/save_knowledge');#智能服务体验-多语文档编辑-2025-08-21

//账户管理end

//运单管理start
Route::any('index/express', 'index/Index/express');
Route::any('index/express_list', 'index/Index/express_list');
Route::any('index/express_info', 'index/Index/express_info');
Route::any('index/express_fee', 'index/Index/express_fee');
Route::any('index/goods_info', 'index/Index/goods_info');
Route::any('index/express_manage', 'index/Index/express_manage');
Route::any('index/save_expressno', 'index/Index/save_expressno');
Route::any('index/del_expressno', 'index/Index/del_expressno');
Route::any('index/express_share', 'index/Index/express_share');
Route::any('index/express_share_ginfo', 'index/Index/express_share_ginfo');
Route::any('index/notfound', 'index/Index/notfound');
//运单管理end

//新的会员中心start2024-09-12
Route::any('members/member_center', 'index/Members/member_center');
Route::any('members/system_manage', 'index/Members/system_manage');
Route::any('members/system_manage2', 'index/Members/system_manage2');
Route::any('members/person_basic', 'index/members/person_basic');#账户信息
Route::any('members/connect_account_list', 'index/Members/connect_account_list');#关联账户列表
Route::any('members/connect_enterprise_list', 'index/Members/connect_enterprise_list');#关联企业列表
Route::any('members/auth_info', 'index/Members/auth_info');#国内人认证
Route::any('members/connect_enterprise', 'index/Members/connect_enterprise');#关联企业
Route::any('members/contact_info', 'index/Members/contact_info');#联系信息
Route::any('members/receive_list', 'index/Members/receive_list');#收货信息
Route::any('members/save_receive', 'index/Members/save_receive');#收货信息-save
Route::any('members/del_receive', 'index/Members/del_receive');#收货信息-del
Route::any('members/getphonenum', 'index/Members/getphonenum');#获取手机号码
Route::any('members/send_list', 'index/Members/send_list');#发货信息
Route::any('members/save_send', 'index/Members/save_send');#发货信息-save
Route::any('members/processing', 'index/Members/processing');#开发中
Route::any('members/transfer_website', 'index/Members/transfer_website');#跳转其他页面
Route::any('members/coupon_list', 'index/Members/coupon_list');#优惠卡券列表
Route::any('members/prepaid_list', 'index/Members/prepaid_list');#预付账单列表
Route::any('members/sure_list', 'index/Members/sure_list');#我确认的列表
Route::any('members/get_website_qrcode', 'index/Members/get_website_qrcode');#获取网站二维码
//新的会员中心end

//决策应用start2024-09-12
Route::any('member/get_name', 'index/Member/get_name');#发货信息
Route::any('member/member_center','index/Member/member_center');#普通会员主界面
Route::any('member/system_manage','index/Member/system_manage');#系统管理页面1
Route::any('member/system_manage2','index/Member/system_manage2');#系统管理页面2
Route::any('member/join_group','index/Member/join_group');#查看群组
Route::any('member/business_list','index/Member/business_list');#决策管理列表
Route::any('member/group_list','index/Member/group_list');#群组列表
Route::any('member/save_group','index/Member/save_group');#组建群组
Route::any('member/topics_manage','index/Member/topics_manage');#管理决策
Route::any('member/topics_manage2','index/Member/topics_manage2');#管理决策2
Route::any('member/save_topics','index/Member/save_topics');#发起/修改议题
Route::any('member/del_options','index/Member/del_options');#删除选项
Route::any('member/topics_detail','index/Member/topics_detail');#决策详情
Route::any('member/topics_list','index/Member/topics_list');#我参与的议题
Route::any('member/send_topics_list','index/Member/send_topics_list');#我发起的议题
Route::any('member/chat_list','index/Member/chat_list');#我的聊天列表
Route::any('member/save_basic','index/Member/save_basic');#我的基本信息页
Route::any('member/check_follow','index/Member/check_follow');#查看用户有无关注公众号
Route::any('member/share_topics','index/Member/share_topics');#分享议题
Route::any('member/advice_list','index/Member/advice_list');#建议咨询列表
Route::any('member/save_advice','index/Member/save_advice');#保存建议咨询
Route::any('member/social_list','index/Member/social_list');#社媒账户列表
//决策应用end

//在线客服start
Route::any('customer/customer_online', 'index/Customer/customer_online');
Route::any('customer/discuss_online', 'index/Customer/discuss_online');
Route::any('customer/upload_files', 'index/Customer/upload_files');
//在线客服end

//物流官网start
Route::any('gather', 'index/Gather/index');
Route::any('gather/freight_estimation', 'index/Gather/freight_estimation');
Route::any('gather/service_center', 'index/Gather/service_center');
Route::any('gather/tracking', 'index/Gather/tracking');
Route::any('gather/appraise', 'index/Gather/appraise');
Route::any('gather/article', 'index/Gather/article');
Route::any('gather/about', 'index/Gather/about');
Route::any('gather/notice_detail', 'index/Gather/notice_detail');
Route::any('gather/suggest', 'index/Gather/suggest');
Route::any('gather/login', 'index/Gather/login');
Route::any('gather/send_code', 'index/Gather/send_code');
Route::any('gather/member_center', 'index/Gather/member_center');
Route::any('gather/package_forecast', 'index/Gather/package_forecast');#提交预报任务
Route::any('gather/package_list', 'index/Gather/package_list');
Route::any('gather/order_management', 'index/Gather/order_management');
Route::any('gather/order_info', 'index/Gather/order_info');
Route::any('gather/parcel_claim', 'index/Gather/parcel_claim');
Route::any('gather/member', 'index/Gather/member');
Route::any('gather/coupon', 'index/Gather/coupon');
Route::any('gather/point', 'index/Gather/point');
Route::any('gather/balance', 'index/Gather/balance');
Route::any('gather/become_partner', 'index/Gather/become_partner');
Route::any('gather/warehouse_address', 'index/Gather/warehouse_address');
Route::any('gather/address_receive', 'index/Gather/address_receive');
Route::any('gather/update_personal', 'index/Gather/update_personal');
Route::any('gather/update_password', 'index/Gather/update_password');
Route::any('gather/get_warehouse', 'index/Gather/get_warehouse');#获取仓库
Route::any('gather/sendcode', 'index/Gather/sendcode');#阅读须知-发送验证码
Route::any('gather/check_verifyCode_for_rules', 'index/Gather/check_verifyCode_for_rules');#阅读须知-发送验证码-提交验证
Route::any('gather/get_desc', 'index/Gather/get_desc');#获取万邦商品描述
Route::any('gather/get_guide', 'index/Gather/get_guide');#获取阅读须知
Route::any('gather/warehouse_info', 'index/Gather/warehouse_info');#获取仓库信息
Route::any('gather/get_warehouse_info', 'index/Gather/get_warehouse_info');#获取仓库信息
Route::any('gather/get_express', 'index/Gather/get_express');#获取快递企业
Route::any('gather/get_lines', 'index/Gather/get_lines');#获取线路
Route::any('gather/get_country', 'index/Gather/get_country');#获取各大州下的国地
Route::any('gather/get_currency', 'index/Gather/get_currency');#获取币种
Route::any('gather/get_line_detail', 'index/Gather/get_line_detail');#获取线路详情
Route::any('gather/get_line_info', 'index/Gather/get_line_info');#获取线路信息
Route::any('gather/get_tips', 'index/Gather/get_tips');#获取提示
Route::any('gather/search_info', 'index/Gather/search_info');#查询属性内物品名称
Route::any('gather/value_introduce', 'index/Gather/value_introduce');#查询属性内物品名称
Route::any('gather/tax_relate', 'index/Gather/tax_relate');#涉税
Route::any('gather/gettableinfo', 'index/Gather/gettableinfo');#获取账单数据表信息
Route::any('gather/calclinecost', 'index/Gather/calclinecost');#计算线路的计费重和计费额
Route::any('gather/getminiprogramcode', 'index/Gather/getminiprogramcode');#计算线路的计费重和计费额
Route::any('gather/getweixin', 'index/Gather/getweixin');#获取微信账号信息
Route::any('gather/get_volumn', 'index/Gather/get_volumn');#获取线路下的货物类别体积比
Route::any('index/line_info', 'index/Index/line_info');#线路详情
Route::any('gather/getphonenum', 'index/Gather/getphonenum');#获取国地的手机号前缀
Route::any('gather/get_rate', 'index/Gather/get_rate');#获取当前币种汇率

//物流官网end
Route::any('index/upload_file', 'index/Index/upload_file');
Route::any('index/upload_diy_file', 'index/Index/upload_diy_file');

//每日列出日志清单给老板
Route::any('log', 'index/Loggin/index');
Route::any('log_detail', 'index/Loggin/log_detail');
Route::any('notice_boss', 'index/Loggin/notice_boss');

//商城信息
Route::any('shop/audit','index/Shop/audit');
Route::any('shop/audit_detail','index/Shop/audit_detail');
Route::any('shop/shunt','index/Shop/shunt');
Route::any('shop/shunt_detail','index/Shop/shunt_detail');
Route::any('shop/shunt_edit','index/Shop/shunt_edit');
Route::any('shop/shunt_addr','index/Shop/shunt_addr');#修改地址
Route::any('shop/become_buyer','index/Shop/become_buyer');

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