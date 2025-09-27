<?php
/**
 * Created by PhpStorm.
 * CreateTime  : 2025/09/05 22:49
 * file name : Test.php
 * User: asusa
 * Author: Hyy-Cary（优）
 * Contact QQ  : 373889161(.)
 * email: 373889161@qq.com
 * WeChat: 18319021313
 */


namespace app\index\controller;


use app\index\BaseController;

class Test extends BaseController
{
    public function index(){
        $html = \think\facade\View::fetch('index/index');
        //生成首页html静态文件
        file_put_contents('index.html', $html);
        return $html;
    }
}