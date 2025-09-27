<?php
/**
 * Created by PhpStorm.
 * CreateTime  : 2025/08/11 18:02
 * file name : Message.php
 * User: asusa
 * Author: Hyy-Cary（优）
 * Contact QQ  : 373889161(.)
 * email: 373889161@qq.com
 * WeChat: 18319021313
 */


namespace app\index\controller;


use app\index\BaseController;

class Message extends BaseController
{
    public function index(){

        return ViewHtml();
    }
}