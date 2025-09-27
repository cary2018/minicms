<?php
/**
 * Created by PhpStorm.
 * CreateTime  : 2023/11/24 20:28
 * file name : Index.php
 * User: asusa
 * Author: Hyy-Cary
 * Contact QQ  : 373889161(.)
 * email: 373889161@qq.com
 * WeChat: 18319021313
 */

namespace app\admin\controller;

use app\admin\BaseController;

class Index extends BaseController
{
    public function index(){
        return View();
    }
    public function datalist(){
        $arr = MenuList();
        echo json_encode($arr,JSON_UNESCAPED_UNICODE);
    }
}