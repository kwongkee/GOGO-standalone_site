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

//查看线路信息
function line_info(){
    var $ = layui.$
        , layer = layui.layer;
    let area = ['80%','80%'];
    if(IsPhone()){
        area = ['95%','90%'];
    }
    var index = layer.open({
        skin:'grey_div',
        type: 1,
        title: "线路信息",
        content: $('.line_info'),
        area:area,
        cancel: function(){
            $('.line_info').hide();
        }
    });
}

//单个包裹集运
function single_parcel(){
    var $ = layui.$
        , layer = layui.layer
        , form = layui.form;
    let html = '   <div class="parcel_list">\n' +
        '               <fieldset class="layui-elem-field warehouse_field" style="margin-top:10px;">\n' +
        '                        <legend>仓库预订：</legend>\n' +
        '                        <div class="layui-field-box">\n' +
        '                            <div class="layui-form-item">\n' +
        '                                <label class="layui-form-label">目的国地</label>\n' +
        '                                <div class="layui-input-inline layui-select-fscon">\n'+
        '                                     <div id="country" class="xm-select-demo"></div>\n'+
        '                                </div>\n' +
        '                            </div>\n' +
        '                                     <div class="layui-form-item">\n'+
        '                                          <label class="layui-form-label">集运线路</label>\n'+
        '                                          <div class="layui-input-inline layui-select-fscon">\n'+
        '                                              <div id="line_id" class="xm-select-demo"></div>\n'+
        '                                          </div>\n'+
        '                                     </div>\n'+
        '                        </div>\n' +
        '                    </fieldset>\n' +
        '          </div>';

    return html;
}

//多个包裹集运
function multipart_parcel(){
    var $ = layui.$
        , layer = layui.layer;
    $('.know').css('display','none');
    let html = '   <div class="parcel_list">\n' +
        '               <fieldset class="layui-elem-field warehouse_field" style="margin-top:10px;">\n' +
        '                        <legend>仓库预订：</legend>\n' +
        '                        <div class="layui-field-box">\n' +
        '                            <div class="layui-form-item">\n' +
        '                                <label class="layui-form-label">选择仓库</label>\n' +
        '                                <div class="layui-input-inline layui-select-fscon" style="display:flex;align-items:center;">\n'+
        '                                     <select name="warehouse_id" id="warehouse_id" lay-filter="warehouse_id" lay-verify="required">\n'+
        '                                         <option value="">请选择仓库</option>\n';
    $.ajax({
        url: "/?s=gather/get_warehouse",
        method: 'post',
        data: {},
        dataType: 'JSON',
        async: false,
        success: function (res) {
            if (res.code == 0) {
                if(res.list.length!=0){
                    for(let i=0;i<res.list.length;i++){
                        html += '<option value="'+res.list[i].id+'">'+res.list[i].warehouse_name+'</option>\n';
                    }
                }
            }
        },
        error: function (data) {
            layer.msg('系统错误', {time: 2000});
        }
    });
    html += '                             </select>\n'+
        '                                         <div class="layui-btn layui-btn-primary layui-btn-md" onclick="view_warehouse()">查看</div>\n'+
        '                                </div>\n' +
        '                            </div>\n' +
        '                        </div>\n' +
        '                    </fieldset>\n' +
        '          </div>\n';

    return html;
}

//获取提示
function getTipConfig(controller_name,function_name,value) {
    var layer = layui.layer,$ = layui.$;
    $.ajax({
        url:"/?s=gather/get_tips",
        method:'get',
        data:{controller_names: controller_name, function_name:function_name, value_name:value},
        dataType:'JSON',
        success:function(res){

            if( value != '' )
            {
                var type_texts = res.type == 1 ? '提示' : '指引';
                var index = layer.open({
                    type: 1,
                    id: "right_show",
                    anim: -1,
                    title: res.name + type_texts,
                    closeBtn: 1,
                    offset: "r",
                    shade: .1,
                    shadeClose: 1,
                    skin: "layui-anim layui-anim-rl layui-layer-adminRight",
                    area: ['50%','100%'],
                    success: function(layero, index){
                        $('#right_show').html('<div style="padding: 20px;">'+res.content+'<div style="text-align: center;padding-top: 20px;"><button class="layui-btn layui-btn-danger" onclick="closes('+ "'"+index+"'" +')">我已知晓</button></div></div>');
                    }
                });
            }else{
                for (var i=0;i<res.length;i++)
                {
                    var classname = res[i].type == 1 ? 'fa-exclamation-circle' : 'fa-question-circle';
                    $("#showtip_"+res[i].value).addClass(classname);
                    $("#showtip_"+res[i].value).show();
                    // 强制弹出
                    if(res[i].is_force == 2)
                    {
                        var type_text = res[i].type == 1 ? '提示' : '指引';
                        layer.open({
                            title: res[i].name + type_text
                            ,type: 1
                            ,shade: .1
                            ,anim: -1
                            ,area: ['50%', '100%']
                            ,skin: 'layui-anim layui-anim-upbit'
                            ,content: '<div style="padding: 20px;">'+res[i].content+'</div>'
                        });
                    }
                }
            }
        },
        error:function (data) {
            layer.msg('系统错误',{time:2000});
        }
    });
}

function showInfo (controller_name,method_name,value) {
    getTipConfig(controller_name,method_name,value);
    // getTipConfig2(controller_name,method_name,value);
}

function closes (index) {
    layer.close(index);
}

function IsPhone() {
    var info = navigator.userAgent;
    var isPhone = /Mobi|Android|iPhone/i.test(info);
    return isPhone;
}

//填写或查看包裹信息
function parcel_info(){
    var $ = layui.$
        , layer = layui.layer;
    let area = ['80%','80%'];
    if(IsPhone()){
        area = ['100%','100%'];
    }
    var index2 = layer.open({
        skin:'grey_div',
        type: 1,
        title: "新增预报",
        content: $('.parcel_info'),
        area:area,
        cancel:function(){
            $('.parcel_info').hide();
        }
    });
    $('.close_win').click(function(){
        $('.parcel_info').hide();
        layer.close(index2);
    });
}

//展示线路
function show_line(val,channel){
    return ['                <div class="layui-form-item">\n' +
    '                        <label class="layui-form-label">线路列表</label>\n' +
    '                        <div class="layui-input-inline layui-select-fscon">\n' +
    '                            <div class="layui-btn layui-btn-primary layui-btn-md" onclick="line_list()">查看列表</div>\n' +
    // '                            <div onclick="know()" class="know">《集运须知》</div>\n'+
    '                        </div>\n' +
    '                    </div>',val,channel];
}

//放入线路列表
function put_line_list(val,channel) {
    var $ = layui.$
        , layer = layui.layer;
    var html2 = '';
    //获取指定国地的所有线路-start
    $.ajax({
        url: "/?s=gather/get_lines",
        method: 'post',
        data: {'val':val,'channel':channel},
        dataType: 'JSON',
        async: false,
        success: function (res) {
            html2 += '<table class="layui-table line_table" lay-even="true" style="table-layout: fixed;word-break: break-all;">\n'+
                '<thead>\n'+
                '      <th>线路名称</th>\n'+
                '      <th>运输方式</th>\n'+
                '      <th>签收时效(日)</th>\n'+
                '      <th>接受货物属性</th>\n'+
                '      <th>操作</th>\n'+
                '</thead>\n'+
                '<tbody>\n';
            if (res.code == 0) {
                if(res.list.length!=0){
                    for(let i=0;i<res.list.length;i++){
                        html2 += '<tr>\n'+
                            '<td>'+res.list[i].name+'</td>\n'+
                            '<td>'+res.list[i].transport_method+'</td>\n'+
                            '<td>'+res.list[i].sign_time+'</td>\n'+
                            '<td>'+res.list[i].accept_product+'</td>\n'+
                            '<td><input type="radio" name="line_id" value="'+res.list[i].line_id+'" title="选择"></td>\n'+
                            '</tr>';
                    }
                    $('.know').css('display','inline-block');
                }else{
                    html2 += '<tr>\n'+
                        '<td colspan="4">暂无合适线路</td>';
                    '</tr>';
                    $('.know').css('display','none');
                }
            }
            html2 += '</tbody>\n'+
                '</table>\n'+
                '<div class="layui-btn layui-btn-success layui-btn-md close_win" style="margin-top:10px;"><i class="layui-icon">&#xe605;</i> 保存</div>';
        },
        error: function (data) {
            layer.msg('系统错误', {time: 2000});
        }
    });
    $('.line_list').html(html2);
    //获取指定国地的所有线路-end
}

//线路列表
function line_list(){
    var $ = layui.$
        , layer = layui.layer;

    var index = layer.open({
        type: 1,
        title: "线路列表",
        content: $('.line_list'),
        area:['80%','80%']
    });
    $('.close_win').click(function(){
        layer.close(index);
    });
}

function formrender(){
    var $ = layui.$
        ,form = layui.form;

    form.render(null, 'component-form-group');
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

//删除物品
function del_goods(t){
    var $ = layui.$
        , layer = layui.layer
        , form = layui.form;

    let idx = layer.confirm('确认删除该物品吗？',function(index){
        $(t).parent().parent().parent().remove();
        layer.close(idx);
    });
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
    $.getJSON("https://www.gogo198.com/?s=api/get_rate&form_currency=" + currency + "&to_currency=USD" + "&money=" + money, function(res) {
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
}

//cny请求转换为usd
function get_usd(currency,money,t){
    let country_id = $('#country_id').val();
    if(country_id != '') {
        $.ajax({
            url: "https://gather.gogo198.cn/api/kvb/cny_get_usdrate",
            method: 'get',
            data: {currency: currency, money: money},
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
        url: "https://www.gogo198.com/?s=api/get_condition",
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

//是否涉税
function tax_relate(country=''){
    var layer = layui.layer
        ,form  = layui.form
        ,$ = layui.jquery;
    if(country==''){
        country=$('input[name=country]').val();
    }
    if(country=='' || country=='undefined' || typeof(country)=='undefined'){
        layer.msg('请先选择目的国地');return false;
    }
    // console.log(country);return false;
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