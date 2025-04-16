var zbpConfig = {
    bloghost: "http://white.tou5.cn/",
    blogversion: "173290",
    ajaxurl: "http://white.tou5.cn/zb_system/cmd.php?act=ajax&src=",
    cookiepath: "/",
    lang: {
        error: {
            72: "名称不能为空或格式不正确",
            29: "邮箱格式不正确，可能过长或为空",
            46: "评论内容不能为空或过长"
        }
    },
    comment: {
        useDefaultEvents: true,
        inputs: {
            action: {
                getter: function () {
                    return $("#inpId").parent("form").attr("action");
                }
            },
            name: {
                selector: '#inpName',
                saveLocally: true,
                required: true,
                validateRule: /^[^\s　]+$/ig,
                validateFailedErrorCode: 72,
            },
            email: {
                selector: '#inpEmail',
                saveLocally: true,
                validateRule: /^[\w-]+(\.[\w-]+)*@[\w-]+(\.[\w-]+)+$/ig,
                validateFailedErrorCode: 29,
            },
            homepage: {
                selector: '#inpHomePage',
                getter: function () {
                    var t = $('#inpHomePage').val();
                    return (!/^(.+)\:\/\//.test(t) && t !== "") ? 'http://' + t : t; 
                },
                saveLocally: true
            },
            postid: {
                selector: '#inpId',
                required: true
            },
            verify: {
                selector: '#inpVerify'
            },
            content: {
                selector: '#txaArticle',
                required: true,
                validateRule: /./ig,
                validateFailedErrorCode: 46,
            },
            replyid: {
                selector: '#inpRevID'
            },
            format: {
                getter: function () {return 'json';}
            }
        }
    }
};
var zbp = new ZBP(zbpConfig);

var bloghost = zbp.options.bloghost;
var cookiespath = zbp.options.cookiepath;
var ajaxurl = zbp.options.ajaxurl;
var lang_comment_name_error = zbp.options.lang.error[72];
var lang_comment_email_error = zbp.options.lang.error[29];
var lang_comment_content_error = zbp.options.lang.error[46];

$(function () {

    zbp.cookie.set("timezone", (new Date().getTimezoneOffset()/60)*(-1));
    var $cpLogin = $(".cp-login").find("a");
    var $cpVrs = $(".cp-vrs").find("a");
    var $addinfo = zbp.cookie.get("addinfo");
    if (!$addinfo){
        return ;
    }
    $addinfo = JSON.parse($addinfo);

    if ($addinfo.chkadmin){
        $(".cp-hello").html("欢迎 " + $addinfo.useralias + " (" + $addinfo.levelname  + ")");
        $cpLogin.html("后台管理");
    }

    if($addinfo.chkarticle){
        $cpVrs.html("新建文章");
        $cpVrs.attr("href", zbp.options.bloghost + "zb_system/cmd.php?act=ArticleEdt");
    }
});
$(function(){
  let inpNameVal = $(zbpConfig.comment.inputs.name.selector).val();
  if (typeof inpNameVal === "undefined") {
    return;
  }
  if (inpNameVal.trim() === "" || inpNameVal === "访客"){
    zbp.userinfo.output();
  }
});

document.writeln("<script src='http://white.tou5.cn/zb_users/plugin/UEditor/third-party/prism/prism.js' type='text/javascript'></script><link rel='stylesheet' type='text/css' href='http://white.tou5.cn/zb_users/plugin/UEditor/third-party/prism/prism.css'/>");$(function(){var compatibility={as3:"actionscript","c#":"csharp",delphi:"pascal",html:"markup",xml:"markup",vb:"basic",js:"javascript",plain:"markdown",pl:"perl",ps:"powershell"};var runFunction=function(doms,callback){doms.each(function(index,unwrappedDom){var dom=$(unwrappedDom);var codeDom=$("<code>");if(callback)callback(dom);var languageClass="prism-language-"+function(classObject){if(classObject===null)return"markdown";var className=classObject[1];return compatibility[className]?compatibility[className]:className}(dom.attr("class").match(/prism-language-([0-9a-zA-Z]+)/));codeDom.html(dom.html()).addClass("prism-line-numbers").addClass(languageClass);dom.html("").addClass(languageClass).append(codeDom)})};runFunction($("pre.prism-highlight"));runFunction($('pre[class*="brush:"]'),function(preDom){var original;if((original=preDom.attr("class").match(/brush:([a-zA-Z0-9\#]+);/))!==null){preDom.get(0).className="prism-highlight prism-language-"+original[1]}});Prism.highlightAll()});
var prompt = "你已经投过一次了，还想投么(￣口￣)！！！";
$(function(){ $("body").append("<div class='biaodan-box' style='left: 30px;bottom: 50px;'><img src='http://white.tou5.cn/zb_users/plugin/Catbei_Bdtj/images/kefu.png'></div><div class='cat-kefu-box'><div class='cat-kefu-top'><span class='cat-kefu-title'>在线留言</span><span class='cat-kefu-close'>x</span></div><form action='#' id='biaoForm' method='post'><div class='biaodan-input'><span>姓名</span><input name='name' maxlength='10' type='text' placeholder='输入姓名'></div><div class='biaodan-input'><span>联系方式</span><input name='lianxi' type='text' placeholder='联系方式(手机/邮箱/微信)'></div><div class='biaodan-input'><span>留言内容</span><textarea name='content' placeholder='请在此输入留言内容，我们将尽快与您联系。'></textarea></div><div class='biaodan-buju'><div class='biaodan-lianxi'><a href='tel:18009098898'>联系电话</a></div><div class='biaodan-btn'>提 交</div></div></form></div>") })
$(function(){ $("body").append("<link href='http://white.tou5.cn/zb_users/plugin/Catbei_Bdtj/style/biao.css' rel='stylesheet' type='text/css' /><script src='http://white.tou5.cn/zb_users/plugin/Catbei_Bdtj/style/biao.js'></script>") })