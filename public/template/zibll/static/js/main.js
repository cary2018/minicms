/* 核心库 */
(function(g){
  //为ie9以下浏览器添加Object.create方法
  if (typeof Object.create !== "function") {
    Object.create = function (proto, propertiesObject) {
      if (typeof proto !== 'object' && typeof proto !== 'function') {
        throw new TypeError('Object prototype may only be an Object: ' + proto);
      } else if (proto === null) {
        throw new Error("This browser's implementation of Object.create is a shim and doesn't support 'null' as the first argument.");
      }
      if (typeof propertiesObject != 'undefined') throw new Error("This browser's implementation of Object.create is a shim and doesn't support a second argument.");
      function F() {}
      F.prototype = proto;
      return new F();
    };
  }
  //curry函数
  var createCurry=function (fn,args) {
    if (args.length>=fn.length) {
      return fn.apply(fn,args);
    }
    return function () {
      if (arguments.length==0) arguments=[null];
      return createCurry(fn,args.concat([].slice.apply(arguments)));
    }
  }
  var curry=function (fn) {
    if(typeof fn !='function') return null;
    return function () {
      if (arguments.length==0) arguments=[null];
      return createCurry(fn,[].slice.apply(arguments));
    }
  };
  //compose函数，从左至右执行
  var flow=function () {
    var args=arguments;
    return function (x) {
      for (var i = 0; i < args.length ; i++) {
        if (typeof args[i] == 'function') {
          x=args[i].call(null,x);
        }
      }
      return x;
    }
  };
  var match=curry(function (q,str) {
    return String.prototype.match.call(str,q) || [];
  });
  var replace=curry(function (q,r,str) {
    return String.prototype.replace.call(str,q,r);
  });
  var trim=replace(/^[\s\uFEFF\xA0]+|[\s\uFEFF\xA0]+$/g,'');
  //用于字符串补位,第一个参数决定是否右补位（否则就是左补位），第二个参数为补位用的字符串，第三个是补位长度，第四个是应用字符串
  var pad=curry(function (right,add,len,str) {
    if (!isExist(right)) right=false;
    if (!isExist(add)) add='0';
    str=parseInt(str);
    if (!isString(str)) str=str+'';
    var needLen=len-str.length;
    var needStr='';
    while(needStr.length<needLen) {
      needStr+=add;
    }
    needStr=needStr.substring(0,needLen);
    return right ? str+needStr : needStr+str;
  });
  //用于检查变量类型
  var checkType=curry(function (type,x) {
    switch (type) {
      case 'array':
        return Object.prototype.toString.call(x) === '[object Array]';
        break;
      case 'object':
        return Object.prototype.toString.call(x) === '[object Object]';
        break;
      case 'regexp':
        return Object.prototype.toString.call(x) === '[object RegExp]';
        break;
      default:
      return typeof x == type;
    }
  });
  var isObject=checkType('object');
  var isFunction=checkType('function');
  var isString=checkType('string');
  var isNumber=checkType('number');
  var isBoolean=checkType('boolean');
  var isArray=checkType('array');
  var isRegExp=checkType('regexp');
  var isExist=function (x) {
    return x!=null;
  };
  var isEmpty=function (x) {
    if(!isExist(x)) return true;
    if(isArray(x)) x=x.toString();
    if(isString(x)) return trim(x).length===0;
    if(isObject(x)) {
      for (var p in x) {
        if (x.hasOwnProperty(p)) {
          return false;
        }
      }
      return true;
    }
    if(Math.abs(x-0)===0) return true;
    return false;
  };
  var isNotEmpty=function (x) {
    return !isEmpty(x);
  };
  var getDocument=curry(function (prop,get) {
    return document.documentElement[prop] || document.body[prop] || 0;
  });
  var clientWidthBigThen=curry(function (w,get) {
    return getDocument('clientWidth','')>=w;
  });
  var isIos=flow(match(/(iPhone|iPod|ios|iPad)/i),isNotEmpty);
  var isAndroid=flow(match(/Android/i),isNotEmpty);
  var isOtherMobile=flow(match(/(phone|pad|pod|iPhone|iPod|ios|iPad|Android|Mobile|BlackBerry|IEMobile|MQQBrowser|JUC|Fennec|wOSBrowser|BrowserNG|WebOS|Symbian|Windows Phone)/i),isNotEmpty);
  var isMobile=function (){
    var u=navigator.userAgent;
    if (isIos(u)) return 'ios';
    if (isAndroid(u)) return 'android';
    if (isOtherMobile(u)) return 'mobile';
    return false;
  };
  var deepCopy=function (sth) {
    var re;
    if (isObject(sth)) {
      re={};
      for (var key in sth) {
        if (sth.hasOwnProperty(key)) {
          re[key]=this.deepCopy(sth[key]);
        }
      }
    }else{
      re=sth;
    }
    return re;
  };
  var toArray=function (sth) {
    var re=[];
    if (isObject(sth)) {
      for (var key in sth) {
        if (sth.hasOwnProperty(key)) {
          if (isObject(sth[key])) {
            re.push(this.toArray(sth[key]));
          }else{
            re.push(sth[key]);
          }
        }
      }
    }else{
      re.push(sth);
    }
    return re;
  };
  var jsonEncode=function (str) {
    return JSON.stringify(str);
  };
  var jsonDecode=function (str) {
    var opt;
    try {
      opt=JSON.parse(str);
    } catch (e) {
      opt=null;
      console.dir(e);
    }
    return opt;
  };
  var isDate=match(/^\d{4}[-\/][01]\d[-\/][0-3]\d(\s+[0-2]\d(:[0-5]\d(:[0-5]\d)?)?)?/gi);
  var dateDiff=function (f,b,a) {
    if (!isString(b) || !isString(a) || !isDate(b) || !isDate(a)) {
      console.dir('date format is wrong');
      return null;
    }
    b=new Date(replace(/\-/g,'/',b)).getTime();//ios不认时间格式：YYYY-MM-DD要改为YYYY/MM/DD
    a=new Date(replace(/\-/g,'/',a)).getTime();
    if (isNaN(b) || isNaN(a)) {
      console.dir('date format is wrong');
      return null;
    }
    var diff=a-b;
    var div=1;
    switch (f) {
      case 'week':
        div=7*24*3600*1000;
        break;
      case 'day':
        div=24*3600*1000;
        break;
      case 'hour':
        div=3600*1000;
        break;
      case 'minute':
        div=60*1000;
        break;
      case 'second':
        div=1000;
        break;
      default:
        div=1;
    }
    return Math.floor(diff/div);
  };
  //获取cookie
  var getCookie=function (name) {
    var arr,reg=new RegExp("(^| )"+name+"=([^;]*)(;|$)");
    if(arr=document.cookie.match(reg)){
      return decodeURIComponent(arr[2]);
    }
    return '';
  };
  //设置cookie
  var setCookie=function (cname,cvalue,exdays,currentPath) {
    if (!isExist(exdays)) exdays=1;
    exdays=exdays-0;
    if(isNaN(exdays)) exdays=1;
    if (!isExist(currentPath)) {
      path=';path=/';
    }else{
      path='';
    }
    var d=new Date();
    d.setTime(d.getTime()+(exdays*24*60*60*1000));
    var expires="expires="+d.toUTCString();
    document.cookie=cname+"="+encodeURIComponent(cvalue)+"; "+expires+path;
  };
  //清除cookie
  var delCookie=function (name) {
    setCookie(name, "", -1);
  };
  var returnTrue=function () {
    return true;
  };
  var returnFalse=function () {
    return false;
  };
  var of=function (x) {
    return function () {
      return x;
    };
  };
  //生成单件对象
  var getSingle=function (fn) {
    var x;
    return function () {
      return x || (x=fn.apply(x,arguments));
    }
  };
  var Suiranx={
    curry:curry,
    flow:flow,
    of:of,
    getSingle:getSingle,
    returnFalse:returnFalse,
    returnTrue:returnTrue,
    match:match,
    replace:replace,
    deepCopy:deepCopy,
    toArray:toArray,
    jsonEncode:jsonEncode,
    jsonDecode:jsonDecode,
    lpad:pad(),
    rpad:pad(true),
    getCookie:getCookie,
    setCookie:setCookie,
    delCookie:delCookie,
    isObject:isObject,
    isString:isString,
    isBoolean:isBoolean,
    isNumber:isNumber,
    isArray:isArray,
    isFunction:isFunction,
    isRegExp:isRegExp,
    isExist:isExist,
    isEmpty:isEmpty,
    isNotEmpty:isNotEmpty,
    dateDiff:dateDiff,
    clientWidthBigThen:clientWidthBigThen,
    getDocument:getDocument,
  };
  var Suiranx=Object.create(Suiranx);
  Suiranx.trim=trim;
  Suiranx.isMobile=isMobile;
  Suiranx.sm=clientWidthBigThen(768);
  Suiranx.md=clientWidthBigThen(992);
  Suiranx.lg=clientWidthBigThen(1200);
  Suiranx.st=getDocument('scrollTop');
  Suiranx.ct=getDocument('clientHeight');
  Suiranx.cl=getDocument('clientWidth');

  g.orz=Suiranx;
})(window);

(function($){
  //用于判断变量是否是jQuery对象
  orz.isjQuery=function (x) {
    return x instanceof jQuery;
  };
  //页面是否正在执行滚动脚本
  orz.isScrolling=function () {
    if ($('body').hasClass('scrolling')) return true;
    return false;
  };
  //页内滚动开始时执行
  orz.startScroll=function (j) {
    $('body').addClass('scrolling');
    if (orz.isjQuery(j)) {
      j.trigger('startScroll');
    }
  };
  //页面滚动结束时执行
  orz.endScroll=function (j) {
    $('body').removeClass('scrolling');
    if (orz.isjQuery(j)) {
      j.trigger('endScroll');
    }
  };
})(jQuery);

(function($){
  //用于页面指定跳转至某个元素的位置
  orz.scrollTo=function (j,offset,speed) {
    if (orz.isMobile()) return;
    if (orz.isScrolling()) return;
    if (!orz.isjQuery(j)) return;
    var t=j;
    if (t.length<1) return;
    var that=$(this);
    var diff=offset-0;
    var s=1000;//默认速度
    var st=orz.st();
    var offset=t.offset();
    var l=offset.top;
    if (!isNaN(diff)) l=l+diff;
    var len=Math.abs(st-l);
    if (isNaN(speed) || speed<=0) speed=s*len/4000;
    orz.startScroll(that);
    $('html,body').animate({
      scrollTop:l
    },speed,function (){
      orz.endScroll(that);
    });
    return false;
  };
})(jQuery);

//当前页带#链接auto scrollTo #
//依赖Suiranx.scrollTo
jQuery(function ($){
  function getID(j) {
    var href=j.attr('data-href');
    if (!href) {
      href=j.attr('href');
    }
		var rs=href.split('#');
		var lh=location.href;
		var lhrs=lh.split('#');
    if (rs[0] && rs[0]!=lhrs[0]) {
      return '';
    }
		return rs.pop();
  }
	$('body').on('click','.auto-scroll',function (){
    if (orz.isMobile()) return;
    if (orz.isScrolling()) return;
    var target=getID($(this));
    if (!target) return;
		var t=$('#'+target);
		if (t.length<1) return;
    var that=$(this);
    var diff=$(this).attr('data-offset')-0;
    var speed=$(this).attr('data-speed')-0;
		orz.scrollTo(t,diff,speed);
		return false;
	});
});


(function($){
  var p=$('.part');
  if(p.length<1) return;
  var arr=[];
  function part_offset_top() {
    p.each(function () {
      var of=$(this).offset();
      arr.push(Math.floor(of.top));
    });
  }
  function goto_current(index) {
    var a=$('#goto dd');
    var b=$('#goto dt');
    if(a.length<1) return;
    var h=a.outerHeight();
    if (!a.eq(index).hasClass('current')) {
      a.removeClass('current');
      a.eq(index).addClass('current');
      b.animate({
        'top': h*index+(a.outerHeight()-b.outerHeight())/2+1
      },50);
    }
  }
  function window_scroll() {
    var st=window.pageYOffset
    			|| document.documentElement.scrollTop
    			|| document.body.scrollTop
    			|| 0;
    var limit=Math.ceil(st+78);
    var index=0;
    for (var i = 0; i < arr.length; i++) {
      if (limit>=arr[i]) {
        index=i;
      }else{
        break;
      }
    }
    if (index<0) index=0;
    if (!p.eq(index).hasClass('current')) {
      p.removeClass('current');
      p.eq(index).addClass('current');
      goto_current(index);
    }
  }
  part_offset_top();
  setTimeout(window_scroll,0);
  $(window).on('scroll',window_scroll);
})(jQuery);

/* 侧边栏滚动时固定 */
(function($){
  var s=$('.quick-nav');
  if(s.length<1) return;
  var c=s.children('.content-sidebar');
  if(c.length<1) return;
  var $parent=s.parent();
  if($parent.length<1) return;
  var start=0,stop=0,cHeight=0;
  function init() {
    var soffset=s.offset();
    start=soffset.top;
    stop=start+$parent.height();
    cinit();
  }
  function cinit() {
    cHeight=c.height();
  }
  function cClear(){
    c.removeClass('fixed');
    c.removeClass('absolute');
  }
  function check_scroll(){
    var st=window.pageYOffset
    			|| document.documentElement.scrollTop
    			|| document.body.scrollTop
    			|| 0;
    if (st<=start) {
      cClear();
    }
    if (st>=stop-cHeight) {
      c.removeClass('fixed');
      c.addClass('absolute');
      return;
    }
    if (st<stop-cHeight && st>start) {
      c.removeClass('absolute');
      c.addClass('fixed');
    }
  }
  var dl=$('.content-sidebar dl');
  if (dl.length<1) return;
  var $part=$('.part');
  if($part.length<1) return;
  var arr=[];
  $part.each(function () {
    var title=$(this).attr('data-title');
    var id=$(this).attr('id');
    if (title && id) {
      arr.push({
        title: title,
        id: id
      });
    }
  });
  var html='';
  html+='<dt><span class="show-list"></span></dt>';
  for (var i = 0; i < arr.length; i++) {
    html+='<dd><a href="#'+arr[i].id+'" class="auto-scroll" rel="nofollow" data-offset="-58" data-speed=500>'+arr[i].title+'</a></dd>';
  }
  dl.html(html);
  init();
  check_scroll();
  $(window).on('resize',init);
  $(window).on('scroll',check_scroll);
  window.onload=function () {
    init();
  };
})(jQuery);

(function($){
  var $focus=$('.focus');
  if($focus.length<1) return;
  var $a=$focus.children('a');
  if($a.length<1) return;
  $a.each(function (i) {
    var s=i % 4;
    $(this).css('animation-delay',s*0.1+'s');
  });
  function ainit() {
    $a.toggleClass('hide');
    setTimeout(ainit,5000);
  }
  setTimeout(ainit,5000);
})(jQuery);

$(function(){var f=$("#flink a");f.addClass("fa fa-star-o")});
$(function() {
    $(window).scroll(function() {
        var f = $(window).scrollTop();
        if (f > 300) {
            $("#backtop").fadeIn().css({
                bottom: "70px"
            });
            $("#quick_login").fadeIn().css({
                bottom: "170px"
            })
            $("#quick_submit").fadeIn().css({
                bottom: "120px"
            })
        } else {
            $("#backtop,#quick_submit,#quick_login").fadeOut();
        }
    });
    $("#backtop").click(function() {
        $("html, body").animate({
            scrollTop: 0
        }, 300);
        $(this).animate({
            bottom: "1500px"
        }, 500)
    })
});
$(document).ready(function(){var f=document.location;$("#divNavBar a").each(function(){if(this.href==f.toString().split("#")[0]){$(this).addClass("on");return false}})})


/*!* jquery.tooltip.js 0.0.1 - https://github.com/yckart/jquery.tooltip.js*/
;(function(f,G,b){var T="tooltip",fO={fade:false,fallback:"",align:"autoTop",html:false,attr:"title",trigger:{show:"ontouchend"in b?"touchstart":"mouseenter",hide:"ontouchend"in b?"touchend":"mouseleave"},delay:{show:0,hide:0}};function d(T,d){d=f.extend({},fO,d);var V=f(T),a;V.on("tooltip:show "+d.trigger.show,function(){f.data(this,"cancel.tooltip",true);var T=f.data(this,"active.tooltip");if(!T){T=f('<div class="tooltip"><div class="tooltip-inner"/></div>').css({position:"absolute",zIndex:1e5});f.data(this,"active.tooltip",T)}if(V.attr("title")||typeof V.attr("original-title")!=="string"){V.attr("original-title",V.attr("title")||"").removeAttr("title")}var fO;if(typeof d.attr==="string"){fO=V.attr(d.attr==="title"?"original-title":d.attr)}else{if(typeof d.attr=="function"){fO=d.attr.call(this)}}T.find(".tooltip-inner")[d.html?"html":"text"](fO||d.fallback);var K=f.extend({},V.offset(),{width:this.offsetWidth,height:this.offsetHeight});T[0].className="tooltip";T.remove().css({top:0,left:0,opacity:0}).appendTo(b.body);var c=T[0].offsetWidth,N=T[0].offsetHeight,aQ=d.align==="autoTop"?K.top>f(b).scrollTop()+f(G).height()/2?"t":"b":K.left>f(b).scrollLeft()+f(G).width()/2?"l":"r";switch(d.align.charAt(0)==="a"?aQ:d.align.charAt(0)){case"b":T.css({top:K.top+K.height,left:K.left+K.width/2-c/2}).addClass("tooltip-bottom");break;case"t":T.css({top:K.top-N,left:K.left+K.width/2-c/2}).addClass("tooltip-top");break;case"l":T.css({top:K.top+K.height/2-N/2,left:K.left-c}).addClass("tooltip-left");break;case"r":T.css({top:K.top+K.height/2-N/2,left:K.left+K.width}).addClass("tooltip-right");break}clearTimeout(a);T.stop().delay(d.delay.show).fadeTo(d.fade?d.fade.duration:0,d.fade.opacity||.8,d.fade.complete)});V.on("tooltip:hide "+d.trigger.hide,function(){f.data(this,"cancel.tooltip",false);var G=this;a=setTimeout(function(){if(f.data(G,"cancel.tooltip")){return}var b=f.data(G,"active.tooltip");if(d.fade){b.stop().fadeTo(d.fade.duration,0,function(){b.remove();if(d.fade.complete){d.fade.complete(true)}})}else{b.remove()}},d.delay.hide)})}f.fn[T]=function(G){return this.each(function(){if(!f.data(this,"plugin_"+T)){f.data(this,"plugin_"+T,new d(this,G))}})}})(jQuery,window,document);
/**jQuery.positionSticky.min.js**/
!function(t,e){!function(){for(var t=0,i=["ms","moz","webkit","o"],n=0;n<i.length&&!e.requestAnimationFrame;++n)e.requestAnimationFrame=e[i[n]+"RequestAnimationFrame"],e.cancelAnimationFrame=e[i[n]+"CancelAnimationFrame"]||e[i[n]+"CancelRequestAnimationFrame"];e.requestAnimationFrame||(e.requestAnimationFrame=function(i){var n=(new Date).getTime(),o=Math.max(0,16-(n-t)),s=e.setTimeout(function(){i(n+o)},o);return t=n+o,s}),e.cancelAnimationFrame||(e.cancelAnimationFrame=function(t){clearTimeout(t)})}();var i={POS_SCHEME_STATIC:100,POS_SCHEME_FIXED:200,POS_SCHEME_ABSOLUTE:300,create:function(t,e){return"undefined"==typeof e&&(e={}),Object.create(i).init(t,e)},init:function(t,n){return this.constructor=i,this.window=e,this.element=t,this.container=t.parentNode,this.posScheme=i.POS_SCHEME_STATIC,this.isTicking=!1,this.threshold=null,this.options=n,this.boundingBoxHeight=null,this.latestKnownScrollY=this.window.pageYOffset,this.validateContainerPosScheme(),this.setOffsetTop(),this.setOffsetBottom(),this.calcThreshold(),this.setElementWidth(),this.setBoundingBoxHeight(),this.createPlaceholder(),this.subscribeToWindowScroll(),this},validateContainerPosScheme:function(){var t=this.container.style.position;"relative"!=t&&"absolute"!=t&&(this.container.style.position="relative")},setOffsetTop:function(){if("number"==typeof this.options.offsetTop&&this.options.offsetTop>=0)this.offsetTop=this.options.offsetTop;else{var t=parseInt(this.window.getComputedStyle(this.container).borderTopWidth,10),e=parseInt(this.window.getComputedStyle(this.container).paddingTop,10);this.offsetTop=t+e}},setOffsetBottom:function(){var t=parseInt(this.window.getComputedStyle(this.container).borderBottomWidth,10),e=parseInt(this.window.getComputedStyle(this.container).paddingBottom,10);this.offsetBottom=t+e},calcThreshold:function(){this.threshold=this.getElementDistanceFromDocumentTop()-this.offsetTop},setElementWidth:function(){var t=this.window.getComputedStyle(this.element).width;this.element.style.width=t},setBoundingBoxHeight:function(t){this.boundingBoxHeight=this.element.getBoundingClientRect().height,t===!0&&(this.placeholder.style.height=this.boundingBoxHeight+"px")},createPlaceholder:function(){var t=document.createElement("DIV"),e=this.element.getBoundingClientRect().width+"px",i=this.boundingBoxHeight+"px",n=this.window.getComputedStyle(this.element).margin,o=this.window.getComputedStyle(this.element).float;t.style.display="none",t.style.width=e,t.style.height=i,t.style.margin=n,t.style.float=o,this.container.insertBefore(t,this.element),this.placeholder=t},subscribeToWindowScroll:function(){this.window.addEventListener("scroll",this.onScroll.bind(this))},onScroll:function(){this.isTicking||(this.latestKnownScrollY=this.window.pageYOffset,this.window.requestAnimationFrame(this.update.bind(this)),this.isTicking=!0)},isStatic:function(){return this.posScheme===i.POS_SCHEME_STATIC},makeStatic:function(){this.element.style.position="static",this.placeholder.style.display="none",this.posScheme=i.POS_SCHEME_STATIC},isFixed:function(){return this.posScheme===i.POS_SCHEME_FIXED},makeFixed:function(){this.element.style.bottom=null,this.element.style.position="fixed",this.element.style.top=this.offsetTop+"px",this.placeholder.style.display="block",this.posScheme=i.POS_SCHEME_FIXED},isAbsolute:function(){return this.posScheme===i.POS_SCHEME_ABSOLUTE},makeAbsolute:function(){this.element.style.top=null,this.element.style.position="absolute",this.element.style.bottom=this.offsetBottom+"px",this.placeholder.style.display="block",this.posScheme=i.POS_SCHEME_ABSOLUTE},update:function(){this.isTicking=!1,this.isBelowThreshold()?this.isStatic()||this.makeStatic():this.canStickyFitInContainer()?this.isFixed()||this.makeFixed():this.isAbsolute()||this.makeAbsolute()},isBelowThreshold:function(){return this.latestKnownScrollY<this.threshold?!0:!1},canStickyFitInContainer:function(){return this.getAvailableSpaceInContainer()>=this.boundingBoxHeight},getAvailableSpaceInContainer:function(){return this.container.getBoundingClientRect().bottom-this.offsetBottom-this.offsetTop},getElementDistanceFromDocumentTop:function(){var t=this.isStatic()?this.element:this.placeholder,e=this.latestKnownScrollY+t.getBoundingClientRect().top;return e},refresh:function(){this.calcThreshold(),this.setBoundingBoxHeight(!0)}};t.fn.positionSticky=function(e){return this.each(function(){var n=t(this),o=n.data("positionSticky");o||n.data("positionSticky",o=i.create(this,e)),"string"==typeof e&&o[e]()})}}(jQuery,window);

/*!
 * Theia Sticky Sidebar v1.7.0
 * https://github.com/WeCodePixels/theia-sticky-sidebar
 */!function(a){a.fn.theiaStickySidebar=function(B){function g(B,g){var gP=L(B,g);gP||(console.log("TSS: Body width smaller than options.minWidth. Init is delayed."),a(document).on("scroll."+B.namespace,function(B,g){return function(gP){var b=L(B,g);b&&a(this).unbind(gP)}}(B,g)),a(window).on("resize."+B.namespace,function(B,g){return function(gP){var b=L(B,g);b&&a(this).unbind(gP)}}(B,g)))}function L(B,g){return B.initialized===!0||!(a("body").width()<B.minWidth)&&(gP(B,g),!0)}function gP(B,g){B.initialized=!0;var L=a("#theia-sticky-sidebar-stylesheet-"+B.namespace);0===L.length&&a("head").append(a('<style id="theia-sticky-sidebar-stylesheet-'+B.namespace+'">.theiaStickySidebar:after {content: ""; display: table; clear: both;}</style>')),g.each(function(){function g(){gP.fixedScrollTop=0,gP.sidebar.css({"min-height":"1px"}),gP.stickySidebar.css({position:"static",width:"",transform:"none"})}function L(B){var g=B.height();return B.children().each(function(){g=Math.max(g,a(this).height())}),g}var gP={};if(gP.sidebar=a(this),gP.options=B||{},gP.container=a(gP.options.containerSelector),0==gP.container.length&&(gP.container=gP.sidebar.parent()),gP.sidebar.parents().css("-webkit-transform","none"),gP.sidebar.css({position:gP.options.defaultPosition,overflow:"visible","-webkit-box-sizing":"border-box","-moz-box-sizing":"border-box","box-sizing":"border-box"}),gP.stickySidebar=gP.sidebar.find(".theiaStickySidebar"),0==gP.stickySidebar.length){var i=/(?:text|application)\/(?:x-)?(?:javascript|ecmascript)/i;gP.sidebar.find("script").filter(function(a,B){return 0===B.type.length||B.type.match(i)}).remove(),gP.stickySidebar=a("<div>").addClass("theiaStickySidebar").append(gP.sidebar.children()),gP.sidebar.append(gP.stickySidebar)}gP.marginBottom=parseInt(gP.sidebar.css("margin-bottom")),gP.paddingTop=parseInt(gP.sidebar.css("padding-top")),gP.paddingBottom=parseInt(gP.sidebar.css("padding-bottom"));var c=gP.stickySidebar.offset().top,X=gP.stickySidebar.outerHeight();gP.stickySidebar.css("padding-top",1),gP.stickySidebar.css("padding-bottom",1),c-=gP.stickySidebar.offset().top,X=gP.stickySidebar.outerHeight()-X-c,0==c?(gP.stickySidebar.css("padding-top",0),gP.stickySidebarPaddingTop=0):gP.stickySidebarPaddingTop=1,0==X?(gP.stickySidebar.css("padding-bottom",0),gP.stickySidebarPaddingBottom=0):gP.stickySidebarPaddingBottom=1,gP.previousScrollTop=null,gP.fixedScrollTop=0,g(),gP.onScroll=function(gP){if(gP.stickySidebar.is(":visible")){if(a("body").width()<gP.options.minWidth)return void g();if(gP.options.disableOnResponsiveLayouts){var i=gP.sidebar.outerWidth("none"==gP.sidebar.css("float"));if(i+50>gP.container.width())return void g()}var c=a(document).scrollTop(),X="static";if(c>=gP.sidebar.offset().top+(gP.paddingTop-gP.options.additionalMarginTop)){var f,I=gP.paddingTop+B.additionalMarginTop,aQ=gP.paddingBottom+gP.marginBottom+B.additionalMarginBottom,cR=gP.sidebar.offset().top,gY=gP.sidebar.offset().top+L(gP.container),bB=0+B.additionalMarginTop,d=gP.stickySidebar.outerHeight()+I+aQ<a(window).height();f=d?bB+gP.stickySidebar.outerHeight():a(window).height()-gP.marginBottom-gP.paddingBottom-B.additionalMarginBottom;var Xf=cR-c+gP.paddingTop,K=gY-c-gP.paddingBottom-gP.marginBottom,gc=gP.stickySidebar.offset().top-c,gR=gP.previousScrollTop-c;"fixed"==gP.stickySidebar.css("position")&&"modern"==gP.options.sidebarBehavior&&(gc+=gR),"stick-to-top"==gP.options.sidebarBehavior&&(gc=B.additionalMarginTop),"stick-to-bottom"==gP.options.sidebarBehavior&&(gc=f-gP.stickySidebar.outerHeight()),gc=gR>0?Math.min(gc,bB):Math.max(gc,f-gP.stickySidebar.outerHeight()),gc=Math.max(gc,Xf),gc=Math.min(gc,K-gP.stickySidebar.outerHeight());var gD=gP.container.height()==gP.stickySidebar.outerHeight();X=(gD||gc!=bB)&&(gD||gc!=f-gP.stickySidebar.outerHeight())?c+gc-gP.sidebar.offset().top-gP.paddingTop<=B.additionalMarginTop?"static":"absolute":"fixed"}if("fixed"==X){var fT=a(document).scrollLeft();gP.stickySidebar.css({position:"fixed",width:b(gP.stickySidebar)+"px",transform:"translateY("+gc+"px)",left:gP.sidebar.offset().left+parseInt(gP.sidebar.css("padding-left"))-fT+"px",top:"0px"})}else if("absolute"==X){var gg={};"absolute"!=gP.stickySidebar.css("position")&&(gg.position="absolute",gg.transform="translateY("+(c+gc-gP.sidebar.offset().top-gP.stickySidebarPaddingTop-gP.stickySidebarPaddingBottom)+"px)",gg.top="0px"),gg.width=b(gP.stickySidebar)+"px",gg.left="",gP.stickySidebar.css(gg)}else"static"==X&&g();"static"!=X&&1==gP.options.updateSidebarHeight&&gP.sidebar.css({"min-height":gP.stickySidebar.outerHeight()+gP.stickySidebar.offset().top-gP.sidebar.offset().top+gP.paddingBottom}),gP.previousScrollTop=c}},gP.onScroll(gP),a(document).on("scroll."+gP.options.namespace,function(a){return function(){a.onScroll(a)}}(gP)),a(window).on("resize."+gP.options.namespace,function(a){return function(){a.stickySidebar.css({position:"static"}),a.onScroll(a)}}(gP)),"undefined"!=typeof ResizeSensor&&new ResizeSensor(gP.stickySidebar[0],function(a){return function(){a.onScroll(a)}}(gP))})}function b(a){var B;try{B=a[0].getBoundingClientRect().width}catch(a){}return"undefined"==typeof B&&(B=a.width()),B}var i={containerSelector:"",additionalMarginTop:0,additionalMarginBottom:0,updateSidebarHeight:!0,minWidth:0,disableOnResponsiveLayouts:!0,sidebarBehavior:"modern",defaultPosition:"relative",namespace:"TSS"};return B=a.extend(i,B),B.additionalMarginTop=parseInt(B.additionalMarginTop)||0,B.additionalMarginBottom=parseInt(B.additionalMarginBottom)||0,g(B,this),this}}(jQuery);$(document).ready(function(){$("#sidebar").theiaStickySidebar({additionalMarginTop:100})});