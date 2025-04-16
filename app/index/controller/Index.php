<?php

namespace app\index\controller;

use app\index\BaseController;
use think\facade\Db;

class Index extends BaseController
{
    public function index()
    {
        //调用插件勾子
        /*hook('show', ['id'=>1]);
        hook('testhook', ['id'=>1]);
        die;*/

        return ViewHtml();
    }
}


