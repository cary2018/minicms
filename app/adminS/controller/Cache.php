<?php
/**
 * Created by PhpStorm.
 * CreateTime  : 2023/11/25 23:41
 * file name : Cache.php
 * User: asusa
 * Author: Hyy-Cary
 * Contact QQ  : 373889161(.)
 * email: 373889161@qq.com
 * WeChat: 18319021313
 */

namespace app\admin\controller;

use app\admin\BaseController;

class Cache extends BaseController
{
    public function index(){
        EmptyCache();
        $msg = ['code'=>1,'msg'=>lang('cacheData')];
        echo json_encode($msg,JSON_UNESCAPED_UNICODE);
    }
}