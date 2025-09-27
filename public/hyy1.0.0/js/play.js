var videoObject = {
    menu:[
        {
            title:'关于视频',
            click:'aboutShow'
        }
    ],
    container: '.PlayVideo', //视频容器
    seek:'cookie',//指定跳转到cookie记录的时间，使用该属性必需配置属性cookie
    cookie:PlayUrl,//cookie名称,请在同一域中保持唯一
    poster:'video/poster.png',//封面图片
    plug:'hls.js',//设置使用hls插件
    autoplay: true,  //自动播放
    rightBar:true,
    screenshot:true,
    smallWindows:true,
    playbackrateOpen:true,
    webFull:true,
    theatre:true,
    video:PlayUrl//视频地址
};
var player=new ckplayer(videoObject)//调用播放器并赋值给变量player