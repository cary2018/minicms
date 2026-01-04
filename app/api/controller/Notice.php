<?php
/**
 * Created by PhpStorm.
 * CreateTime  : 2025/08/15 14:13
 * file name : Notice.php
 * User: asusa
 * Author: Hyy-Cary（优）
 * Contact QQ  : 373889161(.)
 * email: 373889161@qq.com
 * WeChat: 18319021313
 */


namespace app\api\controller;


use app\api\BaseController;

class Notice extends BaseController
{
    public function index(){
        //音乐频道公告通知
        $notice = [
            'notice'=>[
                "所有数据均来自网络，不保证一直有效，且听且珍惜！",
                "如有疑问请前往<a href='https://www.itmkk.com/index/message?id=15' target='_blank'>《留言板》</a>进行留言，虽然我不一定会理你",
                "想要独属于自己的网站可联系站长，373889161@qq.com",
                "有偿提供资源查找，需要请联系站长，373889161@.com",
            ],
            'sayings'=>[
                'author'=>'作者：Hyy-Cary',
                'sayings'=>'语录：凡人寻仙问道，我的机缘快快出来！',
                'explain'=>'说明：网站仅用于学习和研究使用， 页面清爽纯净简洁，不存储任何音乐数据， 仅娱乐，在条件允许的情况下尽量支持正版音乐！',
            ],
            'reward'=>[
                'wechat'=>'/images/wechat.png',
                'aliplay'=>'/images/aliplay.jpg',
            ],
        ];
        return json_encode($notice,JSON_UNESCAPED_UNICODE);
    }
    public function ransay(){
        $msg = GetCurl('https://api.nxvav.cn/api/yiyan/?encode=json&charset=utf-8');
        $txt = json_decode($msg['output']);
        return '语录：'.$txt->yiyan;
    }
}