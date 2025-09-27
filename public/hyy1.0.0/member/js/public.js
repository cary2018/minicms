layui.use(['jquery','layer','form','table','laydate'], function () {
    var $ = layui.jquery;
    var layer = layui.layer;
    var form = layui.form;
    var table = layui.table;
    var iconPickerFa = layui.iconPickerFa;
    var laydate = layui.laydate;

    //日期
    laydate.render({
        elem: '#date',
        type: 'datetime',
    });
    laydate.render({
        elem: '#date2',
        type: 'datetime',
    });

    //监听头部工具栏事件（表头事件） 添加 + 获取选中行id + 批量删除 + 重载数据表格
    table.on('toolbar(DataDemos)', function(obj){
        let checkStatus = table.checkStatus(obj.config.id); //idTest 即为基础参数 id 对应的值
        let data = checkStatus.data;
        let fileName = $('#FieldName').attr('alt');
        if(!fileName){
            fileName = 'id';
        }
        let arr_id = new Array();
        switch(obj.event){
            case 'add':
                openAdd()
                break;
            case 'adds':
                openAdds()
                break;
            case 'importSelect':
                for(let i = 0;i<data.length;i++){
                    arr_id.push(data[i][fileName]); //ar_id 是数据表唯一id
                }
                importSelect(arr_id);
                break;
            case 'delete':
                for(let i = 0;i<data.length;i++){
                    arr_id.push(data[i][fileName]); //ar_id 是数据表唯一id
                }
                layer.confirm('确定删除吗?', function(index){
                    DelData(arr_id);  //执行批量删除
                    layer.close(index);
                });
                break;
            case 'ChangeData':
                let fieldName = $(this).attr('field');
                if(!fieldName){
                    fieldName = 'id';
                }
                let actionUrl = $(this).attr('action');
                if(!actionUrl){
                    actionUrl = 'Change';
                }
                let confirm = $(this).attr('title');
                if(!confirm){
                    confirm = '确定操作吗?';
                }
                let arr = new Array();
                for(let i = 0;i<data.length;i++){
                    arr.push(data[i][fieldName]); //ar_id 是数据表唯一id
                }
                layer.confirm(confirm, function(index){
                    ChangeData(arr,actionUrl);  //执行批量删除
                    layer.close(index);
                });
                break;
            case 'laySearch':
                $("#inputSearch").slideToggle("fast");
                break;
        }
    });

    //监听单元格编辑事件(修改字段数据)
    table.on('edit(DataDemos)', function(obj){
        //异步发送，把数据提交给php
        var wait = 1000;
        let aid = $('#FieldName').attr('alt');
        if(!aid){
            aid = 'id';
        }
        let FieldUrl = $('#FieldUrl').attr('alt');
        if(!FieldUrl){
            FieldUrl = "updateField";
        }
        $.ajax({
            url: FieldUrl,
            type: "POST",
            data: {value:obj.value,field:obj.field,id:obj.data[aid]},
            beforeSend: function(){
                layer.load(1,{shade:[0.3,'#000']});
            },
            success: function (datas) {
                //关闭等待弹出层
                layer.closeAll();
                //权限不足
                let ob = typeof datas;
                if(ob == 'object'){
                    if(data.code == 0){
                        layer.msg(data.msg,{icon:5,time:wait,shade:0.3});
                    }
                }
                let obj = JSON.parse(datas);
                if(obj.code == 200){
                    layer.msg(obj.msg,{icon:1,time:wait,shade:0.3});
                }else{
                    layer.msg(obj.msg,{icon:2,time:wait,shade:0.3});
                    table.reload("DataDemos");  //重载数据表格
                }
            },
            error: function () {
                layer.msg("上传失败！",{icon:2,time:wait,shade:0.3});
                $("#imgWait").hide();
                layer.closeAll('loading'); //关闭加载层
            }
        });
    });
    function importSelect(ids=''){
        let JumpUrl = $('#JumpUrl').attr('alt');
        let JumpParam = $('#JumpParam').attr('alt');
        if(!JumpUrl){
            JumpUrl = 'selectImport';
        }
        if(JumpParam){
            JumpUrl=JumpUrl+'?id='+JumpParam;
        }
        if(ids){
            JumpUrl=JumpUrl+'&ids='+ids;
        }
        layer.open({
            type:2,
            title:'导入',
            shadeClose: true,
            content:JumpUrl,
            area:[$(window).width()*0.9+'px',$(window).height() - 50+'px']
        })
    }
    var url='saveAt';
    //打开添加页面（弹出当前元素）
    function openAdd(){
        parent.layer.open({
            type:1,
            title:'添加',
            maxmin:true,
            shadeClose: true,
            content:$('#saveOrUpdate'),
            area:[$(window).width()*0.9+'px',$(window).height() - 50+'px'],
            success:function(index){
                //清空表单数据
                //$('#DataForm')[0].reset();
                $('#show_img').attr('src','/admin_file/images/all1.png');
                let ActionUrl = $('#ActionUrl').attr('alt');
                if(ActionUrl){
                    url = ActionUrl;
                }else{
                    url="saveAt";
                }
            }
        })
    }
    //打开添加页面（iframe）
    var commonObj = {};
    function openAdds(){
        let AddUrl = $('#AddUrl').attr('alt');
        if(!AddUrl){
            AddUrl = 'add';
        }
        parent.layer.open($.extend(commonObj,{
            type:2,
            title:'添加',
            shadeClose: true,
            maxmin:true,
            content:AddUrl,
            area:[$(window).width()*0.9+'px',$(window).height() - 50+'px'],
            end : function() {
                //关闭弹框后触发
                //alert('触发关闭事件');
                table.reload("DataDemos");  //重载数据表格
            }
        }))
    }
    //打开修改页面
    function openEdit(data){
        parent.layer.open({
            type:1,
            title:'编辑',
            shadeClose: true,
            maxmin:true,
            content:$('#saveOrUpdate'),
            area:[$(window).width()*0.9+'px',$(window).height() - 50+'px'],
            success:function(index){
                let img = $('#FieldImg').attr('alt');
                //填充表单数据
                //console.log(data);
                form.val('DataForm',data);
                if(data[img]){
                    $('#show_img').attr('src',data[img]);
                }else{
                    $('#show_img').attr('src','/admin_file/images/all1.png');
                }
                let ActionUrl = $('#ActionUrl').attr('alt');
                if(ActionUrl){
                    url = ActionUrl;
                }else{
                    url="saveAt";
                }
            }
        })
    }
    //添加/编辑
    form.on('submit(save)',function(obj){
        let url = obj.elem.attributes.alt.nodeValue;
        parent.layer.open({
            type:2,
            title:'信息',
            maxmin:true,
            content:url,
            shadeClose: true,
            area:[$(window).width()*0.9+'px',$(window).height() - 50+'px']
        });
    });
    //删除操作
    function DelData(data,delHan=''){
        //异步发送，把数据提交给后台处理
        let DelUrl = $('#DelUrl').attr('alt');
        if(!DelUrl){
            DelUrl = 'delAll';
        }
        if(delHan){
            DelUrl = delHan;
        }
        var wait = 1000;
        $.ajax({
            url: DelUrl,
            type: "POST",
            data: {data},
            beforeSend: function(){
                layer.load(1,{shade:[0.3,'#000']});
            },
            success: function (data) {
                //关闭等待弹出层
                layer.closeAll();
                //权限不足
                let ob = typeof data;
                if(ob == 'object'){
                    if(data.code == 0){
                        layer.msg(data.msg,{icon:5,time:wait,shade:0.3});
                    }
                }
                let obj = JSON.parse(data);
                if(obj.code == 200){
                    //删除指定行
                    //delHan.del();
                    table.reload("DataDemos");  //重载数据表格
                    layer.msg(obj.msg,{icon:1,time:wait,shade:0.3});
                }else{
                    layer.msg(obj.msg,{icon:2,time:wait,shade:0.3});
                }
            },
            error: function () {
                layer.msg("上传失败！",{icon:2,time:wait,shade:0.3});
                $("#imgWait").hide();
                layer.closeAll('loading'); //关闭加载层
            }
        });
    }
    function ChangeData(data,action){
        //异步发送，把数据提交给后台处理
        var wait = 3000;
        $.ajax({
            url: action,
            type: "POST",
            data: {data},
            beforeSend: function(){
                layer.load(1,{shade:[0.3,'#000']});
            },
            success: function (data) {
                //关闭等待弹出层
                layer.closeAll();
                //权限不足
                let ob = typeof data;
                if(ob == 'object'){
                    if(data.code == 0){
                        layer.msg(data.msg,{icon:5,time:wait,shade:0.3});
                    }
                }
                let obj = JSON.parse(data);
                if(obj.code == 200){
                    //删除指定行
                    //delHan.del();
                    table.reload("DataDemos");  //重载数据表格
                    layer.msg(obj.msg,{icon:1,time:wait,shade:0.3});
                }else{
                    layer.msg(obj.msg,{icon:2,time:wait,shade:0.3});
                }
            },
            error: function () {
                layer.msg("上传失败！",{icon:2,time:wait,shade:0.3});
                $("#imgWait").hide();
                layer.closeAll('loading'); //关闭加载层
            }
        });
    }

    //工具条事件  编辑 + 删除
    table.on('tool(DataDemos)', function(obj){ //注：tool 是工具条事件名，test 是 table 原始容器的属性 lay-filter="对应的值"
        var nowActItem = obj; //获得当前行数据
        var data = obj.data; //获得当前行数据
        var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
        if(layEvent === 'del'){ //删除
            layer.confirm('确定操作吗?', function(index){
                let fileName = $('#FieldName').attr('alt');
                if(!fileName){
                    fileName = 'id';
                }
                DelData(data[fileName]); //执行删除操作
                layer.close(index);
            });
        } else if(layEvent === 'optimize'){ //优化
            layer.confirm('确定操作吗?', function(index){
                let name = $('#optimize').attr('field');
                let url = $('#optimize').attr('action');
                if(!name){
                    name = 'id';
                }
                if(!url){
                    url = 'optimize';
                }
                DelData(data[name],url); //提交后台执行操作
                layer.close(index);
            });
        } else if(layEvent === 'repair'){ //修复
            layer.confirm('确定操作吗?', function(index){
                let name = $('#repair').attr('field');
                let url = $('#repair').attr('action');
                if(!name){
                    name = 'id';
                }
                if(!url){
                    url = 'repair';
                }
                DelData(data[name],url); //提交后台执行操作
                layer.close(index);
            });
        } else if(layEvent === 'edit'){ //编辑
            openEdit(data);
        }else if(layEvent === 'edits'){
            //console.log(obj);
            let FieldName = $('#FieldName').attr('alt');
            let title = $('#ActionUrl').attr('title');
            let ActionUrl = $('#ActionUrl').attr('alt');
            if(!FieldName){
                FieldName = 'id';
            }
            if(!ActionUrl){
                ActionUrl = 'edit';
            }
            if(!title){
                title = '编辑'
            }
            let ids=data[FieldName];
            parent.layer.open({
                type: 2,
                title: title,
                fix: false, //不固定
                maxmin: true,
                shadeClose: true,
                shade:0.4,
                area:[$(window).width()*0.9+'px',$(window).height() - 50+'px'],
                content: ActionUrl+'?id='+ids,
                end : function() {
                    //关闭弹框后触发
                    table.reload("DataDemos");  //重载数据表格
                }
            })
        } else if(layEvent === 'product'){
            let FieldName = $('#FieldName').attr('alt');
            let JumpUrl = $('#JumpUrl').attr('alt');
            let title = $('#JumpUrl').attr('title');
            if(!FieldName){
                FieldName = 'id';
            }
            if(!title){
                title = '编辑';
            }
            if(!JumpUrl){
                JumpUrl = 'product';
            }
            let ids=data[FieldName];
            parent.layer.open({
                type: 2,
                title: title,
                fix: false, //不固定
                maxmin: true,
                shadeClose: true,
                shade:0.4,
                area:[$(window).width()*0.9+'px',$(window).height() - 50+'px'],
                content: JumpUrl+'?id='+ids,
                end : function() {
                    //关闭弹框后触发
                    //alert('触发关闭事件');
                    table.reload("DataDemos");  //重载数据表格
                }
            })
        } else if(layEvent === 'ajaxJson'){
            let FieldName = $('#FieldName').attr('alt');
            let JsonUrl = $('#JsonUrl').attr('alt');
            if(!FieldName){
                FieldName = 'id';
            }
            let ids=data[FieldName];
            let wait=1000;
            //异步发送，把数据提交给php
            $.ajax({
                url: JsonUrl+'?id='+ids,
                type: "GET",
                async:true,  //true发送异步请求,false发送同步请求
                processData: false,  // 告诉jQuery不要去处理发送的数据
                contentType: false,   // 告诉jQuery不要去设置Content-Type请求头
                beforeSend: function(){
                    var loading = layer.load(1,{shade:[0.3,'#000']});
                },
                success: function (data) {
                    //权限不足
                    let ob = typeof data;
                    if(ob == 'object'){
                        if(data.code == 0){
                            layer.closeAll();
                            layer.msg(data.msg,{icon:5,time:wait,shade:0.3});
                        }
                    }
                    let obj = JSON.parse(data);
                    if(obj.code == 200){
                        //关闭弹出层
                        layer.closeAll();
                        //无刷更新字段信息
                        let fieldValue = {notice_msg:obj.notice_msg,or_puser:obj.or_puser,remarks:obj.remarks,notice_remarks:obj.notice_remarks,status:obj.status,pay_time:obj.pay_time};
                        nowActItem.update(fieldValue);
                        //table.reload("DataDemos");  //重载数据表格
                        layer.msg(obj.msg,{icon:1,time:wait,shade:0.3});
                        setTimeout(function(){
                            if(obj.jump_url){
                                //跳转地址存在执行跳转
                                location.href = obj.jump_url;
                            }else{
                                // 获得iframe索引
                                var index = parent.layer.getFrameIndex(window.name);
                                //关闭当前frame
                                parent.layer.close(index);
                            }
                        },wait);

                    }else{
                        layer.closeAll('loading'); //关闭加载层
                        $('input[name="__token__"]').val(obj.token);//更新token防止失效
                        layer.msg(obj.msg,{icon:2,time:wait,shade:0.3});
                    }
                },
                error: function () {
                    layer.msg("上传失败！",{icon:2,time:wait,shade:0.3});
                    $("#imgWait").hide();
                    layer.closeAll('loading'); //关闭加载层
                }
            });
            return false;
        } else if(layEvent === 'products'){
            let FieldName = $('#FieldName').attr('alt');
            let JumpUrls = $('#JumpUrls').attr('alt');
            if(!FieldName){
                FieldName = 'id';
            }
            if(!JumpUrls){
                JumpUrls = 'product';
            }
            let ids=data[FieldName];
            location.href = JumpUrls+'?id='+ids;
        }else if(layEvent === 'LAYTABLE_TIPS'){
            layer.msg('Hi，头部工具栏扩展的右侧图标。');
        }
    });
    //添加节点
    $("#addDown").on('click',function (obj) {
        let ms = $(this).parent('div').html();
        let mess = ms.replace('layui-icon-addition','layui-icon-delete');
        let msg = mess.replace('addDown','delDown');
        let ttr = '<div class="layui-form-item">'+msg+'</div>';
        $(this).parent().after(ttr);
    });
    //删除节点
    $(document).on("click","#delDown",function (obj) {
        $(this).parent().remove();
    });

    //删除
    $(document).on('click','#delete',function (obj) {
        let th = $(this);
        let url = th.attr('alt');

        //console.log($(this).attr('alt'));
        let wait = 1000;
        $.ajax({
            url: url,
            type: "GET",
            beforeSend: function(){
                layer.load(1,{shade:[0.3,'#000']});
            },
            success: function (data) {
                //关闭等待弹出层
                layer.closeAll();
                //权限不足
                let ob = typeof data;
                if(ob == 'object'){
                    if(data.code == 0){
                        layer.msg(data.msg,{icon:5,time:wait,shade:0.3});
                    }
                }
                let obj = JSON.parse(data);
                if(obj.code == 200){
                    //删除指定行
                    th.parent().parent().remove();
                    table.reload("DataDemos");  //重载数据表格
                    layer.msg(obj.msg,{icon:1,time:wait,shade:0.3});
                }else{
                    layer.msg(obj.msg,{icon:2,time:wait,shade:0.3});
                }
            },
            error: function () {
                layer.msg("上传失败！",{icon:2,time:wait,shade:0.3});
                $("#imgWait").hide();
                layer.closeAll('loading'); //关闭加载层
            }
        });
    })

    //保存数据
    form.on('submit(doSubmit)',function(obj){
        let format = obj.form;
        let formId = $(format).attr('id');
        let formData =  new FormData($('#'+formId)[0]); //获取整个表单数据
        let wait = 2000;
        let ActionUrl = $(format).attr('action');
        if(ActionUrl){
            url = ActionUrl;
        }
        //异步发送，把数据提交给后台
        $.ajax({
            url: url,
            type: "POST",
            data:formData,
            async:true,  //true发送异步请求,false发送同步请求
            processData: false,  // 告诉jQuery不要去处理发送的数据
            contentType: false,   // 告诉jQuery不要去设置Content-Type请求头
            beforeSend: function(){
                var loading = layer.load(1,
                    {
                        shade:[0.3,'#000'],
                        content:'<span class="loadtip">0%</span>',
                        success: function (layer) {
                            layer.find('.layui-layer-content').css({
                                'padding-top': '40px',
                                'width': '100px',
                            });
                            layer.find('.loadtip').css({
                                'font-size':'20px',
                                'margin-left':'0px'
                            });
                        }
                    }
                );
            },
            xhr: function() {
                var xhr = new XMLHttpRequest();
                xhr.upload.addEventListener('progress', function(event) {
                    if (event.lengthComputable) {
                        //通过设置进度条的宽度达到效果
                        var percent = Math.round((event.loaded / event.total) * 100);
                        $('.loadtip').html(percent + '%');//完成进度（文字）
                        //console.log('loading:'+percent);
                    }
                }, false);
                return xhr;
            },
            success: function (data) {
                //权限不足
                let ob = typeof data;
                if(ob == 'object'){
                    if(data.code == 0){
                        layer.closeAll();
                        layer.msg(data.msg,{icon:5,time:wait,shade:0.3});
                    }
                }
                let obj = JSON.parse(data);
                if(obj.code == 200){
                    //关闭弹出层
                    layer.closeAll();
                    table.reload("DataDemos");  //重载数据表格
                    layer.msg(obj.msg,{icon:1,time:wait,shade:0.3});
                    setTimeout(function(){
                        if(obj.jump_url){
                            //跳转地址存在执行跳转
                            location.href = obj.jump_url;
                        }else{
                            // 获得iframe索引
                            var index = parent.layer.getFrameIndex(window.name);
                            //关闭当前frame
                            parent.layer.close(index);
                        }
                    },wait);
                }else{
                    let Captcha = $('#refreshCaptcha').attr('src');
                    if(Captcha){
                        $('#refreshCaptcha').attr('src',Captcha.replace(/\?.*$/, '')+'?'+Math.random());
                    }
                    layer.closeAll('loading'); //关闭加载层
                    $('input[name="__token__"]').val(obj.token);//更新token防止失效
                    layer.msg(obj.msg,{icon:2,time:wait,shade:0.3});
                }
            },
            error: function () {
                layer.msg("上传失败！",{icon:2,time:wait,shade:0.3});
                $("#imgWait").hide();
                layer.closeAll('loading'); //关闭加载层
            }
        });
        return false;
    });
    //监听指定开关（启用，禁用）
    form.on('switch(switchTest)', function(data){
        let checked = data.elem.checked;
        let SwitchUrl = $('#SwitchUrl').attr('alt');
        if(!SwitchUrl){
            SwitchUrl = 'switchAt';
        }
        //异步发送，把数据提交给php
        var wait = 1000;
        $.ajax({
            url: SwitchUrl,
            type: "POST",
            data: {id:data.value,field:data.elem.name},
            beforeSend: function(){
                layer.load(1,{shade:[0.3,'#000']});
            },
            success: function (datas) {
                //关闭等待弹出层
                layer.closeAll();
                layer.closeAll('loading'); //关闭加载层
                //权限不足
                let ob = typeof datas;
                if(ob == 'object'){
                    if(data.code == 0){
                        layer.msg(data.msg,{icon:5,time:wait,shade:0.3});
                    }
                }
                let obj = JSON.parse(datas);
                if(obj.code == 200){
                    layer.msg(obj.msg,{icon:1,time:wait,shade:0.3});
                }else{
                    data.elem.checked = !checked; //禁用开关
                    form.render();  //重新渲染开关
                    layer.msg(obj.msg,{icon:2,time:wait,shade:0.3});
                }
            },
            error: function () {
                layer.msg("上传失败！",{icon:2,time:wait,shade:0.3});
                $("#imgWait").hide();
                data.elem.checked = !checked; //禁用开关
                layer.closeAll('loading'); //关闭加载层
            }
        });
    });
    // 执行搜索，表格重载
    $('#doSearch').on('click', function (data) {
        // 搜索条件
        table.reload('DataDemos', {
            method: 'get'
            , where: {
                /*'page':0,*/
                'data': $('#searchData').serializeArray(),
            }
            , page: {
                curr: 1
            }
        });
    });
    //加载城市数据
    form.on('select(province_id)', function (data) {
        let url = $("#GetCity").attr('title');
        let id = data.value;
        if(!url){
            url = '/admin/area/city';
        }
        $.get(url+'?id='+id, function (res) {
            let obj = JSON.parse(res);
            if (obj.code == 200) {
                var str = "";
                $.each(obj.data, function(i,item){
                    str += "<option value=\"" + item.id + "\" >" + item.name + "</option>";
                });
                $(".city-selector").html('<option value="">【请选择市】</option>' + str);
                $(".county-selector").html('<option value="">【请选择县/区】</option>');
                form.render('select');
            }else{
                $(".city-selector").html('<option value="">【请选择市】</option>');
                $(".county-selector").html('<option value="">【请选择县/区】</option>');
                form.render('select');
                return false;
            }
        });
    });
    //加载县区数据
    form.on('select(city_id)', function (data) {
        let url = $("#GetCity").attr('title');
        let id = data.value;
        if(!url){
            url = '/admin/area/city';
        }
        $.get(url+'?id='+id, function (res) {
            let obj = JSON.parse(res);
            if (obj.code == 200) {
                var str = "";
                $.each(obj.data, function(i,item){
                    str += "<option value=\"" + item.id + "\" >" + item.name + "</option>";
                });
                $(".county-selector").html('<option value="">【请选择县/区】</option>' + str);
                form.render('select');
            }else{
                $(".county-selector").html('<option value="">【请选择县/区】</option>');
                form.render('select');
                return false;
            }
        });
    });
    //点击表格图片
    $(document).on('click', '#imgAmplify', function(data){
        var imgAlt=$(this).attr('layer-src');
        var imgSrc=$(this).attr('src');
        var title=$(this).attr('title');
        layer.photos({
            photos: {
                "title": "", //相册标题
                "id": '#layer-photos-demo', //相册id
                "start": 0, //初始显示的图片序号，默认0
                "data": [   //相册包含的图片，数组格式
                    {
                        "alt": title,
                        "pid": 666, //图片id
                        "src": imgAlt, //原图地址
                        "thumb": imgSrc //缩略图地址
                    }
                ]
            },
            success: function () {
                //以鼠标位置为中心的图片滚动放大缩小
                $(document).on("mousewheel", ".layui-layer-photos", function (ev) {
                    //console.log(6666);
                    var oImg = this;
                    var ev = event || window.event;//返回WheelEvent
                    //ev.preventDefault();
                    var delta = ev.detail ? ev.detail > 0 : ev.wheelDelta < 0;
                    var ratioL = (ev.clientX - oImg.offsetLeft) / oImg.offsetWidth,
                        ratioT = (ev.clientY - oImg.offsetTop) / oImg.offsetHeight,
                        ratioDelta = !delta ? 1 + 0.05 : 1 - 0.05,
                        w = parseInt(oImg.offsetWidth * ratioDelta),
                        h = parseInt(oImg.offsetHeight * ratioDelta),
                        l = Math.round(ev.clientX - (w * ratioL)),
                        t = Math.round(ev.clientY - (h * ratioT));
                    //设置相册层宽高
                    $(".layui-layer-photos").css({ width: w, height: h, left: l, top: t });
                    //设置图片外div宽高
                    $("#layui-layer-photos").css({ width: w, height: h });
                    //设置图片宽高
                    $("#layui-layer-photos>img").css({ width: w, height: h });
                });
            },
            //销毁回调
            end: function () {},
            anim: 0 //0-6的选择，指定弹出图片动画类型，默认随机（请注意，3.0之前的版本用shift参数）
        });
    });
    //相册放大
    layer.ready(function(){
        layer.photos({
            photos: '#layer-photos-magnify',
            tab: function(pic, layero){
                //console.log(pic) //当前图片的一些信息
                //以鼠标位置为中心的图片滚动放大缩小
                $(document).on("mousewheel", ".layui-layer-photos", function (ev) {
                    //console.log(5555);
                    var oImg = this;
                    var ev = event || window.event;//返回WheelEvent
                    //ev.preventDefault();
                    var delta = ev.detail ? ev.detail > 0 : ev.wheelDelta < 0;
                    var ratioL = (ev.clientX - oImg.offsetLeft) / oImg.offsetWidth,
                        ratioT = (ev.clientY - oImg.offsetTop) / oImg.offsetHeight,
                        ratioDelta = !delta ? 1 + 0.05 : 1 - 0.05,
                        w = parseInt(oImg.offsetWidth * ratioDelta),
                        h = parseInt(oImg.offsetHeight * ratioDelta),
                        l = Math.round(ev.clientX - (w * ratioL)),
                        t = Math.round(ev.clientY - (h * ratioT));
                    //设置相册层宽高
                    $(".layui-layer-photos").css({ width: w, height: h, left: l, top: t });
                    //设置图片外div宽高
                    $("#layui-layer-photos").css({ width: w, height: h });
                    //设置图片宽高
                    $("#layui-layer-photos>img").css({ width: w, height: h });
                });
            },
            //销毁回调
            end: function () {},
            anim: 0 //0-6的选择，指定弹出图片动画类型，默认随机（请注意，3.0之前的版本用shift参数）
        });
    });
});
/**
 * 图片预览函数
 * @param id
 * @param show
 */
function PreviewImg(id,show) {
    var fileReader = new FileReader();
    f=document.getElementById(id).files[0];
    fileReader.readAsDataURL(f);
    fileReader.onload=function (event) {
        document.getElementById(show).src=this.result;
    };
}