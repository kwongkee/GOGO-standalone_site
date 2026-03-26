//================order_info.html====================
//获取商品描述
function get_desc(t,typ){
    var $ = layui.$
        , layer = layui.layer;
    layer.load();
    if(typ==1){
        let platform_id = $('#platform_id').val();
        let good_id = $('#good_id').val();
        $('.online_desc').hide();
        $('.online_desc').find('.layui-input-inline').html('');
        $.ajax({
            url: "/?s=gather/get_desc",
            method: 'post',
            data: {'platform_id':platform_id,'good_id':good_id},
            dataType: 'JSON',
            success: function (res) {
                layer.closeAll('loading');
                layer.msg(res.msg, {time: 2000}, function () {
                    if (res.code == 0) {
                        let html = '<textarea name="online_desc" id="online_desc" placeholder="请输入商品描述" class="layui-textarea">商品标题：'+res.data.title+'；商品品牌：'+res.data.nick+'</textarea>\n'+
                            '           <a class="other_link" target="_blank" href="'+res.data.detail_url+'" style="color:#ff2222;text-decoration: underline;">跳转到商品详情页</a>';
                        $('.online_desc').find('.layui-input-inline').html(html);
                        $('.online_desc').show();
                    }
                });
            },
            error: function (data) {
                layer.msg('系统错误', {time: 2000});
            }
        });
    }else{
        let platform_id = $(t).parent().parent().parent().find('#platform_id').val();
        let good_id = $(t).parent().parent().parent().find('#good_id').val();
        $(t).parent().parent().parent().find('.online_desc').hide();
        $(t).parent().parent().parent().find('.online_desc').find('.layui-input-inline').html('');
        $.ajax({
            url: "/?s=gather/get_desc",
            method: 'post',
            data: {'platform_id':platform_id,'good_id':good_id},
            dataType: 'JSON',
            success: function (res) {
                layer.closeAll('loading');
                layer.msg(res.msg, {time: 2000}, function () {
                    if (res.code == 0) {
                        let html = '<textarea name="online_desc[]" id="online_desc" placeholder="请输入商品描述" class="layui-textarea">商品标题：'+res.data.title+'；商品品牌：'+res.data.nick+'</textarea>\n'+
                            '           <a class="other_link" target="_blank" href="'+res.data.detail_url+'" style="color:#ff2222;text-decoration: underline;">跳转到商品详情页</a>';
                        $(t).parent().parent().parent().find('.online_desc').find('.layui-input-inline').html(html);
                        $(t).parent().parent().parent().find('.online_desc').show();
                    }
                });
            },
            error: function (data) {
                layer.msg('系统错误', {time: 2000});
            }
        });
    }
}

//查看仓库信息
function warehouse_info(){
    var $ = layui.$
        , layer = layui.layer;
    let area = ['80%','80%'];
    if(IsPhone()){
        area = ['95%','90%'];
    }
    layer.open({
        skin:'grey_div',
        type: 1,
        title: "仓库信息",
        content: $('.warehouse_infoDIV'),
        area:area,
        cancel: function(){
            $('.warehouse_infoDIV').hide()
        }
    });
}

//查看预报详情
function view_order() {
    var $ = layui.$
        , layer = layui.layer;

    layer.open({
        type: 1,
        title: "预报信息",
        content: $('.order_detail'),
        area:['80%','80%'],
        cancel: function(){
            $('.order_detail').hide();
        }
    });
}

//查看线路信息
function line_info(id,channel_id){
    var $ = layui.$
        , layer = layui.layer;
    let area = ['80%','80%'];
    if(IsPhone()){
        area = ['100%','95%'];
    }
    var index = layer.open({
        skin:'grey_div',
        type: 2,
        title: "线路信息",
        content: 'https://www.gogo198.com/?s=gather/line_detail&id='+id+'&channel='+channel_id+'&watch=1',
        area:area,
        cancel: function(){
            // $('.line_info').hide();
        }
    });
}

//複製全部
function xs_all(this1){
    var name = $(this1).attr('data-name');
    var tel = $(this1).attr('data-tel');
    var post = $(this1).attr('data-post');
    var address = $(this1).attr('data-address');
    var address2 = '';
    if($('.addrs').length>0) {
        for (let i = 0; i < $('.addrs').length; i++) {
            let num = i + 2;
            address2 += $(this1).attr('data-address'+num)+'\r';
        }
    }
    // var address2 = $(this1).attr('data-address2');
    if(typeof(address2)=='undefined'){
        document.getElementById("cs").innerHTML=name+'<br>'+tel+'<br>'+post+'<br>'+address;
    }else{
        document.getElementById("cs").innerHTML=name+'<br>'+tel+'<br>'+post+'<br>'+address+'<br>'+address2;
    }

    //開始複製
    var oInput = document.createElement('textarea');
    // oInput.value = document.getElementById("cs").innerText;
    if(typeof(address2)=='undefined'){
        oInput.value = name+'\n'+tel+'\n'+post+'\n'+address;
    }else{
        oInput.value = name+'\n'+tel+'\n'+post+'\n'+address+'\n'+address2;
    }
    document.body.appendChild(oInput);
    oInput.select(); // 选择对象
    document.execCommand("Copy"); // 执行浏览器复制命令
    oInput.remove();
    layer.msg("复制成功");
}

//删除包裹
function del_parcel(t){
    var $ = layui.$
        , layer = layui.layer
        , form = layui.form;
    let yubao_num = parseInt($('#yubao_num').val()) - 1;
    $('#yubao_num').val(yubao_num);

    $(t).parent().parent().remove();
    form.render(null, 'component-form-group');
    layui.element.render();
}
//================order_info.html====================

function IsPhone() {
    var info = navigator.userAgent;
    var isPhone = /Mobi|Android|iPhone/i.test(info);
    return isPhone;
}
//自定义多选框--start20230915
function select_option(name,id,nowElement){
    var layer = layui.layer
        ,form  = layui.form
        ,$ = layui.jquery;

    //查看是否有相同值
    let val = $('#'+nowElement).find('.xm-select-default').attr('value');
    if($('#'+nowElement).find('.xm-select-default').val() == ''){
        if(typeof(val)=='undefined') {
            $('#'+nowElement).find('.xm-select-default').attr('value',id);
            valueid.append([id]);
        }else if(val.includes(id) == false){
            let v = val+','+id;
            $('#'+nowElement).find('.xm-select-default').attr('value',v);
            valueid.append([id]);
        }
        valueid.closed();
    }else{
        layer.msg('物品属性仅支持单选');
    }
}
function openValue(id,type){
    var layer = layui.layer
        ,form  = layui.form
        ,$ = layui.jquery;
    if(type==1){
        //打开属性描述
        let area = [];
        if(IsPhone()){
            area = ['90%','90%'];
        }else{
            area = ['50%','50%'];
        }
        layer.open({
            type: 2,
            title: '查看详情',
            area:area,
            content: "/?s=gather/value_introduce&id="+id+"&type="+type
        });
    }
}
//自定义多选框--end20230915

//自定义输入物品描述+物品数量，显示在物品栏
function goods_input_desc(t){
    var layer = layui.layer
        ,form  = layui.form
        ,$ = layui.jquery;

    let val = $(t).val();
    $(t).parent().parent().parent().parent().find('.layui-colla-title h4 .goods_desc').html(val+' ');
}
function goods_input_num(t){
    var layer = layui.layer
        ,form  = layui.form
        ,$ = layui.jquery;
    let val2 = $(t).parent().parent().find('.xmvalue .xm-select-demo xm-select .xm-select-default').val();//二级类别
    let val = $(t).val();
    if(val>0 && val2!=''){
        get_condition(val,2,val2);
    }

    $(t).parent().parent().parent().parent().find('.layui-colla-title h4 .goods_num').html(val);
}

//转换为$
function get_money(t){
    var layer = layui.layer
        ,form  = layui.form
        ,$ = layui.jquery;
    let money = $(t).val();
    let currency = $(t).parent().find('.layui-form-select .layui-select-title').find('input').val();
    if(currency!='' && money>0){
        currency = currency.split('：')[1];
        if(typeof(currency)=='undefined'){
            currency = $(t).parent().find('.layui-form-select .layui-select-title').eq(1).find('input').val();
            currency = currency.split('：')[1];
        }

        if(currency!='USD' && money>0){
            if(currency!='CNY'){
                //转换成CNY汇率，再转换USD
                get_cny(currency,money,t);
            }else{
                //转换成USD汇率
                get_usd(currency,money,t);
            }
        }else if(currency=='USD'){
            $(t).parent().find('.equal_usd').hide();
            get_condition(money,1);

            all_money(t);
        }
    }
}

//获取当前包裹已添加的物品总价值
function all_money(t) {
    var layer = layui.layer
        ,form  = layui.form
        ,$ = layui.jquery;
    let goods_price = $(t).parent().parent().parent().parent().parent().find('.goods_price');
    let totalprice = 0;
    for(let i=0;i<goods_price.length;i++){
        totalprice+=parseFloat(goods_price[i].value);
    }
    let country_id = $('#country_id').val();
    if(country_id != ''){
        get_condition(country_id,3,totalprice);
    }
}

//其他国币种请求转换为CNY(请求window服务器)
function get_cny(currency,money,t){
    // decl.gogo198.cn/api/kvb/othercurrency_get_cnyrate
    $.getJSON("/?s=api/get_rate&form_currency=" + currency + "&to_currency=USD" + "&money=" + money, function(res) {
        layer.msg(res.msg, {time: 2000}, function () {
            if (res.code == 0) {
                $(t).parent().parent().find('.equal_usd .equal_usd_symbol').html('<P>' + currency + money + '=USD' + res.price+'</P>'+'<p>数据仅供参考，具体请打开&nbsp;<a href="https://www.xe.com" target="_blank" style="text-decoration: underline;color: #1E9FFF;font-weight:bold;">XE汇率</a>&nbsp;查询</p>');
                $(t).parent().parent().find('.equal_usd input').val(res.price);
                $(t).parent().parent().find('.equal_usd').css({'display': 'flex', 'align-items': 'center'});
                get_condition(res.price, 1);//先判断申报货值限制
                all_money(t);//后判断目的国地涉税额，提示覆盖上面的申报货值限制
            }else if(res.code == -1){
                $(t).parent().parent().find('.equal_usd .equal_usd_symbol').html('<P>系统查找不到' + currency + money + '=USD？的等值金额</P>'+'<p>具体请打开&nbsp;<a href="https://www.xe.com" target="_blank" style="text-decoration: underline;color: #1E9FFF;font-weight:bold;">XE汇率</a>&nbsp;查询</p>');
                $(t).parent().parent().find('.equal_usd input').val(0);
                $(t).parent().parent().find('.equal_usd').css({'display': 'flex', 'align-items': 'center'});
            }
        });
    });

    // $.ajax({
    //     url: "http://python.gogo198.com/index.php",
    //     method: 'get',
    //     data: {form_currency:currency,money:money,to_currency:'USD'},
    //     dataType: 'JSON',
    //     crossDomain: true,
    //     // headers:{"Access-Control-Allow-Origin":"*"},
    //     success: function (res) {
    //         layer.msg('查询等值美元成功！', {time: 2000}, function () {
    //             // if (res.code == 0) {
    //             // }
    //
    //             $(t).parent().find('.equal_usd .equal_usd_symbol').text('=$' + res);
    //             $(t).parent().find('.equal_usd input').val(res);
    //             $(t).parent().find('.equal_usd').css({'display': 'flex', 'align-items': 'center'});
    //             get_condition(res, 1);
    //             all_money(t);
    //         });
    //     },
    //     error: function (data) {
    //         layer.msg('系统错误', {time: 2000});
    //     }
    // });
}

//cny请求转换为usd
function get_usd(currency,money,t){
    let country_id = $('#country_id').val();
    if(country_id != '') {
        $.ajax({
            url: "/?s=api/get_rate",
            method: 'get',
            data: {form_currency: currency, money: money, to_currency:'USD'},
            async: true,
            dataType: 'jsonp',
            jsonp: 'cs',
            jsonpCallback: 'cs',
            success: function (res) {
                layer.msg(res.msg, {time: 2000}, function () {
                    if (res.code == 0) {
                        $(t).parent().parent().find('.equal_usd .equal_usd_symbol').html('<P>' + currency + money + '=USD' + res.data.price+'</P>'+'<p>数据仅供参考，具体请打开&nbsp;<a href="https://www.xe.com" target="_blank" style="text-decoration: underline;color: #1E9FFF;font-weight:bold;">XE汇率</a>&nbsp;查询</p>');
                        $(t).parent().parent().find('.equal_usd input').val(res.data.price);
                        $(t).parent().parent().find('.equal_usd').css({'display': 'flex', 'align-items': 'center'});
                        get_condition(res.data.price, 1);
                        all_money(t);
                    }
                });
            },
            error: function (data) {
                layer.msg('系统错误', {time: 2000});
            }
        });
    }
}

//判断USD值是否超出或低于总后台配置的申报货值条件
function get_condition(val1,type,val2=''){
    var layer = layui.layer
        ,form  = layui.form
        ,$ = layui.jquery;
    $.ajax({
        url: "/?s=api/get_condition",
        method: 'post',
        data: {type:type,val:val1,val2:val2},
        dataType: 'JSON',
        crossDomain: true,
        // headers:{"Access-Control-Allow-Origin":"*"},
        success: function (res) {
            // layer.msg(res.msg, {time: 2000}, function () {
                if (res.code == -1) {
                    //触发了条件
                    tips_box(res);
                }

                if(type==3){
                    tips_box(res);
                }
            // });
        },
        error: function (data) {
            layer.msg('系统错误', {time: 2000});
        }
    });
}

//公用提示框
function tips_box(res){
    var layer = layui.layer
        ,form  = layui.form
        ,$ = layui.jquery;

    let operation_btn = [];
    for(let i=0;i<res.data[0].operation_name.length;i++){
        operation_btn.push(res.data[0].operation_name[i]);
    }
    let idx = layer.confirm(res.data[0].text_tips, {
        title: "操作提示",
        icon: 0,
        closeBtn: 0,
        btn: operation_btn
        ,btn3: function(index, layero){
            //按钮【按钮三】的回调
            if(res.data[0].operation_select[2]==2){
                if(typeof(res.data[0].system_urls_value)=='undefined'){
                    window.location.href=res.data[0].system_urls[2];//目的国涉税那里配置
                }else{
                    window.location.href=res.data[0].system_urls_value[2];
                }
            }else if(res.data[0].operation_select[2]==1){
                window.location.href=res.data[0].operation_url[2];
            }else{
                layer.close(idx);
            }
        }
    }, function(index, layero){
        //按钮【按钮一】的回调
        if(res.data[0].operation_select[0]==2){
            if(typeof(res.data[0].system_urls_value)=='undefined'){
                window.location.href=res.data[0].system_urls[0];
            }else{
                window.location.href=res.data[0].system_urls_value[0];
            }
        }else if(res.data[0].operation_select[0]==1){
            window.location.href=res.data[0].operation_url[0];
        }else{
            layer.close(idx);
        }
    }, function(index){
        //按钮【按钮二】的回调
        if(res.data[0].operation_select[1]==2){
            if(typeof(res.data[0].system_urls_value)=='undefined'){
                window.location.href=res.data[0].system_urls[1];
            }else{
                window.location.href=res.data[0].system_urls_value[1];
            }
        }else if(res.data[0].operation_select[1]==1){
            window.location.href=res.data[0].operation_url[1];
        }else{
            layer.close(idx);
        }
    });
}

//修改包裹信息,package_info
function edit_info(typ,t){
    var layer = layui.layer
        ,form  = layui.form
        ,$ = layui.jquery;
    $(t).hide();
    if(typ==1){
        $(t).parent().parent().find('.layui-field-box .need_edit').show();
        $(t).parent().parent().find('.layui-field-box .noneed_edit').hide();
        $('.sure_editBtn').show();
    }
    else if(typ==2){
        $(t).parent().parent().find('.layui-field-box .need_edit').show();
        $(t).parent().parent().find('.layui-field-box .noneed_edit').hide();
        $('.sure_editBtn').show();
    }
}

//复制单个文本信息
function copy_that(t,data){
    var layer = layui.layer
        ,form  = layui.form
        ,$ = layui.jquery;
    document.getElementById("cs").innerHTML=data;
    //開始複製
    var oInput = document.createElement('input');
    oInput.value = document.getElementById("cs").innerText;
    document.body.appendChild(oInput);
    oInput.select(); // 选择对象
    document.execCommand("Copy"); // 执行浏览器复制命令
    oInput.remove();
    layer.msg("复制成功");
}

//是否涉税
function tax_relate(country=''){
    var layer = layui.layer
        ,form  = layui.form
        ,$ = layui.jquery;
    if(country==''){
        country=$('select[name=country]').val();
    }
    if(country==''){
        layer.msg('请先选择目的国地');return false;
    }
    area = ['80%','80%'];
    if(IsPhone()){
        area = ['95%','90%'];
    }
    layer.open({
        skin:'grey_div',
        type: 2,
        title: '涉税详情',
        area:area,
        content: "/?s=gather/tax_relate&country="+country
    });
}