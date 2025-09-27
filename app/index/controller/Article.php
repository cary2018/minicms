<?php
/**
 * Created by PhpStorm.
 * CreateTime  : 2023/09/24 16:42
 * file name : Article.php
 * User: asusa
 * Author: Hyy-Cary
 * Contact QQ  : 373889161(.)
 * email: 373889161@qq.com
 * WeChat: 18319021313
 */


namespace app\index\controller;


use app\index\BaseController;

class Article extends BaseController
{
    public function index(){
        $id = request()->param('id');
        $data = FindTable('category',[['id','=',$id],['isShow','=',1]]);
        if(!$data){
            return redirect('/');
        }
        /*$url = (string) \think\facade\Route::buildUrl();
        echo $url;
        die;*/
        return ViewHtml();
    }
}