<?php
/**
 * Created by PhpStorm.
 * CreateTime  : 2023/04/20 22:58
 * file name : Attribute.php
 * User: asusa
 * Author: Hyy-Cary
 * Contact QQ  : 373889161(.)
 * email: 373889161@qq.com
 * WeChat: 18319021313
 */


namespace app\admin\controller;


use app\admin\BaseController;
use think\facade\Db;
use think\facade\View;
use app\admin\model\Attribute as Model;

class Attribute extends BaseController
{
    public function index(){
        return View();
    }
    public function dataList(){
        $data = request()->param();
        $size = $data['limit']?$data['limit']:10;
        $start = $data['page']?$data['page']:0;
        $list = pageTable('attribute',$start,$size);
        $count = CountTable('attribute');
        $arr = array('code'=>0,'msg'=>'ok','count'=>$count,'limit'=>[],'data'=>$list);
        echo json_encode($arr);
    }

    public function add(){
        return View();
    }
    public function edit(){
        $id = request()->param('id');
        $data = FindTable('attribute',[['id','=',$id]]);
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
        $arr = FindTable('attribute',[['id','=',$id]]);
        if($arr){
            if($arr[$field] == 1){
                $val = 0;
            }else{
                $val = 1;
            }
            Db::name('attribute')->save(['id' => $id, $field => $val]);
            $attr = AllTable('attribute',[['status','=',1]],['orderSort','id'=>'desc']);
            SetCaChe('attribute',$attr);
            $msg = array('code'=>200,'msg'=>lang('update_status'));
        }else{
            $msg = array('code'=>300,'msg'=>lang('data_error'));
        }
        echo json_encode($msg);
    }
    public function delAll(){
        $id = request()->param('data');
        $data = FindTable('attribute',[['id','=',$id]]);
        if($data){
            Db::name('attribute')->delete($id);
            $attr = AllTable('attribute',[['status','=',1]],['orderSort','id'=>'desc']);
            SetCaChe('attribute',$attr);
            $msg = ['code'=>200,'msg'=>lang('delete_message')];
        }else{
            $msg = ['code'=>300,'msg'=>lang('fail_message'),'data'=>$id];
        }
        echo json_encode($msg);
    }

    public function updateField(){
        $data = request()->param();
        $nar = FindTable('attribute',[['id','=',$data['id']]]);
        $field = $data['field'];
        if($nar){
            Db::name('attribute')->save(['id' => $data['id'], $field => $data['value']]);
            $attr = AllTable('attribute',[['status','=',1]],['orderSort','id'=>'desc']);
            SetCaChe('attribute',$attr);
            $msg = array('code'=>200,'msg'=>lang('update_success'));
        }else{
            $msg = array('code'=>300,'msg'=>lang('update_error'));
        }
        echo json_encode($msg);
    }
}