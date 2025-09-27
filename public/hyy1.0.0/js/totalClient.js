var host = location.host;
var protocol = location.protocol;
var url = protocol+"//"+host+':'+port;
var socket = io(url);

// 当连接服务端成功时触发connect默认事件
socket.on('connect', function(){
    let data = {
        'key':userKey,
    }
    //向服务器 添加用户
    socket.emit('totalUser', data);
    //向服务器发送用户上线信息
    socket.emit('totalOnlineUser', data);
});
socket.on('totalOnline',function (msg) {
    //服务端返回的数据
    $('.totalOnline').html('在线人数：'+msg);
});

//用户离线
socket.on('totalOutline',function (userKey) {
    //当前uid
    $('.totalOnline').html('在线人数：'+userKey);
    //console.log('用户离开--：'+userKey);
});