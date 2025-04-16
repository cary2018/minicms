<?php 
return array (
  'web_config' => '',
  'web_key' => '站点关键字',
  'web_desc' => '站点描述',
  'web_logo' => 'uploads/images/202408/66ae27b967013.png',
  'web_Copyright' => '<a class="ico-ico" href="http://beian.miit.gov.cn/" rel="nofollow" target="_blank" title="桂ICP备2021006345号">                                    <img src="/images/beian.png" alt="桂ICP备2021006345号">桂ICP备2021006345号                                </a>                                <span class="rt-times">运行时长：0.052秒</span>                                ',
  'web_Copy' => 'Copyright<i class="fa fa-copyright"></i>                                2015-2019<a href="/">站长自主搭建</a>                                版权所有. 基于<a href="https://gitee.com/cary10000/CyMini" rel="nofollow" title="CyMini" target="_blank">CyMini</a>                                搭建 安全运行<span id="iday">5518</span>天
<p id="RunTime"></p>
<script>
var myVar=setInterval(function(){myTimer()},1000);
function myTimer(){
	var d=new Date();
	var t=d.toLocaleTimeString();
	var dangqian = d.getFullYear() + "-" + (d.getMonth() + 1) + "-" + d.getDate() ;
	document.getElementById("RunTime").innerHTML="已经勉强运行："+ DateDiff("2010-08-1",dangqian) +" 天:" + t;
}
/* 计算运行天数的封装函数 */
function  DateDiff(sDate1,  sDate2){    //sDate1和sDate2是2002-12-18格式  
  var  aDate,  oDate1,  oDate2,  iDays  
  aDate  =  sDate1.split("-")  
  oDate1  =  new  Date(aDate[1]  +  \'-\'  +  aDate[2]  +  \'-\'  +  aDate[0])    //转换为12-18-2002格式  
  aDate  =  sDate2.split("-")  
  oDate2  =  new  Date(aDate[1]  +  \'-\'  +  aDate[2]  +  \'-\'  +  aDate[0])  
  iDays  =  parseInt(Math.abs(oDate1  -  oDate2)  /  1000  /  60  /  60  /24)    //把相差的毫秒数转换为天数  
  return  iDays  
}
</script>',
  'sign_key' => '$2y$10$ct781CSlSvYjwkk/rWP52ui8UfuMBKLH2Kbh4M3g6oU7A5B6vTSVC',
  'async_url' => 'http://www.yi.com/ApiData,http://www.tp6.com/api/Callback',
  'view_path' => 'itmkk',
  'sys_logo' => 'uploads/images/202406/666042237a547.jpg',
  'open_html' => '0',
  'web_ico' => 'uploads/images/202410/67187181b38c2.ico',
  'captcha_close' => '1',
  'web_title' => '暗影',
  'web_footer_title' => '从黑暗中感觉到， 在无助的时候， 在夜里是谁对我说， 别心灰想得太多 全因你的一颗心，  在藏着的承诺， 现实里时常对我说， 你定会冲破一切， 全赖你给我一双手臂， 就算在北风中，  你也总没逃避， 全赖你给我一双手臂， 来为我奉上是最真挚的心， 似风呼应',
  'sys_title' => 'CyMini',
  'socket' => '',
  'protocol' => 'socket',
  'host' => '0.0.0.0',
  'port' => '3120',
  'httpPort' => '3122',
  'web_email' => '邮件配置',
  'email_password' => 'PUQJJKOHMBILRCCO',
  'email_port' => '465',
  'email_smtp' => 'smtp.163.com',
  'email_account' => 'kkyhyy@163.com',
  'upload_config' => '',
  'img_type' => 'png,jpg,jpeg,webp,ico,gif,bmp,ico',
  'file_type' => 'zip,gz,rar',
  'img_size' => '10',
  'file_size' => '60',
);