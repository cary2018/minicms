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
        /*$html = \think\facade\View::fetch();
        $c = \think\facade\Request::controller().'/'.\think\facade\Request::action();
        echo $c;
        echo $html;
        die;*/
        return ViewHtml();
    }
}


