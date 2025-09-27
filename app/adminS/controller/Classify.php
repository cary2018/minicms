<?php
/**
 * Created by PhpStorm.
 * CreateTime  : 2023/06/25 14:53
 * file name : Classify.php
 * User: asusa
 * Author: Hyy-Cary
 * Contact QQ  : 373889161(.)
 * email: 373889161@qq.com
 * WeChat: 18319021313
 */


namespace app\admin\controller;


use app\admin\BaseController;
use app\admin\model\Classify as Model;
use think\facade\Db;
use think\facade\View;

class Classify extends BaseController
{
    public function index(){
        return View();
    }
    public function datalist(){
        $tree = GetMenu('classify');
        $count = CountTable('classify');
        View::assign('tree',$tree);
        $arr = array('code'=>0,'msg'=>'ok','count'=>$count,'limit'=>[],'data'=>$tree);
        echo json_encode($arr);
    }
    public function add(){
        $id = request()->param('id');
        $tree = GetMenu('classify');
        $data = FindTable('classify',[['id','=',$id]]);
        foreach ($tree as $k=>$v){
            $level = $v['level']-1;
            if( $level > 1){
                $tree[$k]['p'] = str_repeat("&nbsp;&nbsp;&nbsp;",$level).'|--';
            }else{
                $tree[$k]['p']='';
            }
        }
        View::assign('tree',$tree);
        View::assign('data',$data);
        return View();
    }
    public function edit(){
        $id = request()->param('id');
        $tree = GetMenu('classify');
        $data = FindTable('classify',[['id','=',$id]]);
        foreach ($tree as $k=>$v){
            $level = $v['level']-1;
            if( $level > 1){
                $tree[$k]['p'] = str_repeat("&nbsp;&nbsp;&nbsp;",$level).'|--';
            }else{
                $tree[$k]['p']='';
            }
        }
        View::assign('tree',$tree);
        View::assign('data',$data);
        return View();
    }
    public function saveAt(){
        $data = request()->param();
        $model = new Model();
        $result = $model->dataSave($data);
        //更新上网导航缓存
        Navigation();
        echo $result;
    }
    public function switchAt(){
        $id = request()->param('id');
        $field = request()->param('field');
        $result = SwitchUp('classify',$field,$id);
        //更新上网导航缓存
        Navigation();
        echo json_encode($result);
    }
    public function updateField(){
        $data = request()->param();
        $msg = FieldUpdate('classify',$data);
        //更新上网导航缓存
        Navigation();
        echo json_encode($msg);
    }
    public function delAll(){
        $id = request()->param('data');
        $data = Db::name('classify')->where('id',$id)->find();
        if($data){
            if(ArrTree('classify',$id)){
                $msg = ['status'=>300,'msg'=>lang('error_column_message')];
            }else{
                Db::name('classify')->delete($id);
                $msg = ['code'=>200,'msg'=>lang('delete_message')];
            }
        }else{
            $msg = ['code'=>300,'msg'=>lang('fail_message'),'data'=>$id];
        }
        //更新上网导航缓存
        Navigation();
        echo json_encode($msg);
    }
}