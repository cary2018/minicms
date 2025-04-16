
//图片延迟显示
(function($){$.fn.lazyload=function(options){var settings={threshold:0,failurelimit:0,event:"scroll",effect:"show",container:window};if(options){$.extend(settings,options)}var elements=this;if("scroll"==settings.event){$(settings.container).bind("scroll",function(event){var counter=0;elements.each(function(){if($.abovethetop(this,settings)||$.leftofbegin(this,settings)){}else if(!$.belowthefold(this,settings)&&!$.rightoffold(this,settings)){$(this).trigger("appear")}else{if(counter++>settings.failurelimit){return false}}});var temp=$.grep(elements,function(element){return!element.loaded});elements=$(temp)})}this.each(function(){var self=this;if(undefined==$(self).attr("original")){$(self).attr("original",$(self).attr("src"))}if("scroll"!=settings.event||undefined==$(self).attr("src")||settings.placeholder==$(self).attr("src")||($.abovethetop(self,settings)||$.leftofbegin(self,settings)||$.belowthefold(self,settings)||$.rightoffold(self,settings))){if(settings.placeholder){$(self).attr("src",settings.placeholder)}else{$(self).removeAttr("src")}self.loaded=false}else{self.loaded=true}$(self).one("appear",function(){if(!this.loaded){$("<img />").bind("load",function(){$(self).hide().attr("src",$(self).attr("original"))[settings.effect](settings.effectspeed);self.loaded=true}).attr("src",$(self).attr("original"))}});if("scroll"!=settings.event){$(self).bind(settings.event,function(event){if(!self.loaded){$(self).trigger("appear")}})}});$(settings.container).trigger(settings.event);return this};$.belowthefold=function(element,settings){if(settings.container===undefined||settings.container===window){var fold=$(window).height()+$(window).scrollTop()}else{var fold=$(settings.container).offset().top+$(settings.container).height()}return fold<=$(element).offset().top-settings.threshold};$.rightoffold=function(element,settings){if(settings.container===undefined||settings.container===window){var fold=$(window).width()+$(window).scrollLeft()}else{var fold=$(settings.container).offset().left+$(settings.container).width()}return fold<=$(element).offset().left-settings.threshold};$.abovethetop=function(element,settings){if(settings.container===undefined||settings.container===window){var fold=$(window).scrollTop()}else{var fold=$(settings.container).offset().top}return fold>=$(element).offset().top+settings.threshold+$(element).height()};$.leftofbegin=function(element,settings){if(settings.container===undefined||settings.container===window){var fold=$(window).scrollLeft()}else{var fold=$(settings.container).offset().left}return fold>=$(element).offset().left+settings.threshold+$(element).width()};$.extend($.expr[':'],{"below-the-fold":"$.belowthefold(a, {threshold : 0, container: window})","above-the-fold":"!$.belowthefold(a, {threshold : 0, container: window})","right-of-fold":"$.rightoffold(a, {threshold : 0, container: window})","left-of-fold":"!$.rightoffold(a, {threshold : 0, container: window})"})})(jQuery);
$(function(){$('.top_list_text li:first-child').addClass('on');$(".top_list_text li").hover(function(){$(this).addClass("on").siblings().removeClass("on")});
$("img.lazy,.single-entry img").lazyload({placeholder:bloghost+"zb_users/theme/pyqly/style/images/grey.gif",effect:"fadeIn",threshold:200,failurelimit:30})});
//UBB
function addNumber(a){document.getElementById("txaArticle").value+=a}
if($('#comment-tools,.msgarticle,.comment-content,.pyq-article-comments').length){objActive="txaArticle";function InsertText(a,b,c){if(b==""){return("")}var d=document.getElementById(a);if(document.selection){if(d.currPos){if(c&&(d.value=="")){d.currPos.text=b}else{d.currPos.text+=b}}else{d.value+=b}}else{if(c){d.value=d.value.slice(0,d.selectionStart)+b+d.value.slice(d.selectionEnd,d.value.length)}else{d.value=d.value.slice(0,d.selectionStart)+b+d.value.slice(d.selectionStart,d.value.length)}}}
function ReplaceText(a,b,c){var d=document.getElementById(a);var e;if(document.selection&&document.selection.type=="Text"){if(d.currPos){var f=document.selection.createRange();f.text=b+f.text+c;return("")}else{e=b+c;return(e)}}else{if(d.selectionStart||d.selectionEnd){e=b+d.value.slice(d.selectionStart,d.selectionEnd)+c;return(e)}else{e=b+c;return(e)}}}}
if($('.face-show').length){$("a.face-show").click(function(){$(".ComtoolsFrame").slideToggle(15)})}
function UBBFace(){if($('.msgarticle,#divNewcomm,#divComments,.pyq-article-comments').length){$('.msgarticle,#divNewcomm,#divComments,.pyq-article-comments').each(function comreplace(){var a=$(this).html();a=a.replace(/\[B\](.*)\[\/B\]/g,'<strong>$1</strong>');a=a.replace(/\[U\](.*)\[\/U\]/g,'<u>$1</u>');a=a.replace(/\[S\](.*)\[\/S\]/g,'<del>$1</del>');a=a.replace(/\[I\](.*)\[\/I\]/g,'<em>$1</em>');a=a.replace(/\[([A-Za-z0-9]*)\]/g,'<img src="'+bloghost+'/zb_users/theme/pyqly/include/emotion/$1.png" alt="$1.png">');$(this).html(a)})}}UBBFace();
zbp.plugin.on("comment.post.success", "pyqly", function (formData, retString, textStatus, jqXhr){$("#cancel-reply").click();UBBFace()});
/* 归档 */
(function($, window){$(function(){var $a = $('#cundang'),$m = $('.al_mon_list.item h3', $a),$l = $('.al_post_list', $a),$l_f = $('.al_post_list:first,.al_mon_list.item:nth-child(2) ul.al_post_list', $a);$l.hide();$l_f.show();$m.css('cursor', 'pointer').on('click', function(){$(this).next().slideToggle(0);});var animate = function(index, status, s) {if (index > $l.length) {return;}if (status == 'up') {$l.eq(index).slideUp(s, function() {animate(index+1, status, (s-10<1)?0:s-10);});} else {$l.eq(index).slideDown(s, function() {animate(index+1, status, (s-10<1)?0:s-10);});}};$('#al_expand_collapse').on('click', function(e){e.preventDefault();if ( $(this).data('s') ) {$(this).data('s', '');animate(0, 'up', 300);} else {$(this).data('s', 1);animate(0, 'down', 300);}});});})(jQuery, window);
//font
jQuery(document).ready(function($){$('#font-change span').click(function(){var selector='.single-entry p';var increment=1;var font_size=15;var fs_css=$(selector).css('fontSize');var fs_css_c=parseFloat(fs_css,10);var fs_unit=fs_css.slice(-2);var id=$(this).attr('id');switch(id){case'font-dec':fs_css_c-=increment;break;case'font-inc':fs_css_c+=increment;break;default:fs_css_c=font_size}$(selector).css('fontSize',fs_css_c+fs_unit);return false})});
//search

//手机导航

/* AJAX获取第二页内容 */

//backtop

//switchNightMode

//导航跟随隐藏搜索栏

//点击评论
$(function() {
  const hudongFrElements = document.querySelectorAll('.hudong-fr');
  const funBoxElements = document.querySelectorAll('.fun-box');
  hudongFrElements.forEach(element => {
    element.addEventListener('click', (event) => {
      const funBoxElement = element.nextElementSibling;
      funBoxElements.forEach(fb => {
        fb === funBoxElement ? fb.classList.add('open') : fb.classList.remove('open');
      });
      event.stopPropagation();
    });
  });
  document.body.addEventListener('click', () => {
    funBoxElements.forEach(element => {
      element.classList.remove('open');
    });
  });
});
//全文按钮事件
function quanwenan(){
    var ele = window.event.srcElement.id;
    var elelang=window.event.srcElement.lang;
    var quanwid=ele.replace("qw-","");
    if(elelang == 0){
        var dqdcla = document.getElementById("intro-"+quanwid).className;//获取当前class
        var re = new RegExp("pyq-intro","g"); //定义正则表达式
        var Newdqdcla = dqdcla.replace(re, ""); //替换指定class
        document.getElementById("intro-"+quanwid).className=Newdqdcla;
        document.getElementById("qw-"+quanwid).lang=1;
        document.getElementById("qw--"+quanwid).innerText="收起";
    }else if(elelang == 1){
        document.getElementById("intro-"+quanwid).className +="pyq-intro";
        document.getElementById("qw-"+quanwid).lang=0;
        document.getElementById("qw-"+quanwid).innerText="全文";
    }
}
//为了美观暂时屏蔽F12
/*document.addEventListener('contextmenu', function(event) {
  event.preventDefault();
});*/