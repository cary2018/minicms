<?php
/**
 * Created by PhpStorm.
 * CreateTime  : 2023/09/24 16:27
 * file name : Detail.php
 * User: asusa
 * Author: Hyy-Cary
 * Contact QQ  : 373889161(.)
 * email: 373889161@qq.com
 * WeChat: 18319021313
 */


namespace app\index\controller;


use app\index\BaseController;
use think\facade\Db;

class Detail extends BaseController
{
    public function index(){
        $id = request()->param('id');
        $data = FindTable('article',[['id','=',$id],['status','=',1]]);
        if(!$data){
            return redirect('/');
        }

        //更新阅读量
        Db::name('article')->where('id', $id)->inc('views')->update();
        return ViewHtml();
    }
    public function action(){
        $id = request()->param('id');
        //更新阅读量
        Db::name('article')->where('id', $id)->inc('click')->update();
        $list = FindTable('article',[['id','=',$id],['status','=',1]]);
        $arr = array('code'=>200,'msg'=>'点赞成功！','data'=>$list,'id'=>$id);
        echo json_encode($arr);
    }
}