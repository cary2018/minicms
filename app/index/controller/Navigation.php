<?php
/**
 * Created by PhpStorm.
 * CreateTime  : 2023/06/26 15:15
 * file name : Navigation.php
 * User: asusa
 * Author: Hyy-Cary
 * Contact QQ  : 373889161(.)
 * email: 373889161@qq.com
 * WeChat: 18319021313
 */


namespace app\index\controller;

use app\index\BaseController;
use think\facade\Db;
use think\facade\View;

class Navigation extends BaseController
{
    public function index(){
        $pass = request()->param('pass');
        //DelSe('access_navigation');
        if($pass == '123..'){
            SetSe('access_navigation',$pass);
            $acc =  GetSe('access_navigation');
            View::assign('pass',$acc);
            return ViewHtml(1);
        }else{
            $data = array();
            $msg = ['code'=>300,'message'=>'访问密码错误！','data'=>$data,'acc'=>$pass];
        }
        //$acc =  GetSe('access_navigation');
        $acc =  '1';
        View::assign('pass',$acc);
        return ViewHtml();
    }
    public function cate(){
        return ViewHtml();
    }

    public function jump(){
        $id = request()->param('id');
        $url = request()->param('url');
        Db::name('navigation')->where('id', $id)->inc('click')->update();
        View::assign('url',$url);
        return view();
        return redirect($url);
    }
}