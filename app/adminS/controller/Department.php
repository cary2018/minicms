<?php
/**
 * Created by PhpStorm.
 * CreateTime  : 2023/08/15 14:42
 * file name : Department.php
 * User: asusa
 * Author: Hyy-Cary
 * Contact QQ  : 373889161(.)
 * email: 373889161@qq.com
 * WeChat: 18319021313
 */


namespace app\admin\controller;


use app\admin\BaseController;
use app\admin\model\Department as Model;
use think\facade\Db;
use think\facade\View;

class Department extends BaseController
{
    public function index(){
        return View();
    }
    public function dataList(){
        $tree = GetMenu('department');
        $count = CountTable('department');
        View::assign('tree',$tree);
        $arr = array('code'=>0,'msg'=>'ok','count'=>$count,'limit'=>[],'data'=>$tree);
        echo json_encode($arr);
    }
    public function add(){
        $id = request()->param('id');
        $tree = GetMenu('department');
        foreach ($tree as $k=>$v){
            $level = $v['level']-1;
            if( $level > 1){
                $tree[$k]['p'] = str_repeat("&nbsp;&nbsp;&nbsp;",$level).'|--';
            }else{
                $tree[$k]['p']='';
            }
        }
        View::assign('tree',$tree);
        View::assign('id',$id);
        return View();
    }
    public function edit(){
        $id = request()->param('id');
        $tree = GetMenu('department');
        $data = FindTable('department',[['id','=',$id]]);
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
        echo $result;
    }
    public function switchAt(){
        $id = request()->param('id');
        $field = request()->param('field');
        $result = SwitchUp('department',$field,$id);
        echo json_encode($result);
    }
    public function delAll(){
        $id = request()->param('data');
        $data = Db::name('department')->where('id',$id)->find();
        if($data){
            if(ArrTree('department',$id)){
                $msg = ['status'=>300,'msg'=>lang('error_column_message')];
            }else{
                Db::name('department')->delete($id);
                $msg = ['code'=>200,'msg'=>lang('delete_message')];
            }
        }else{
            $msg = ['code'=>300,'msg'=>lang('fail_message'),'data'=>$id];
        }
        echo json_encode($msg);
    }
    public function updateField(){
        $data = request()->param();
        $msg = FieldUpdate('department',$data);
        echo json_encode($msg);
    }
}