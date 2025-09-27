<?php
/**
 * Created by PhpStorm.
 * CreateTime  : 2023/08/15 14:16
 * file name : Position.php
 * User: asusa
 * Author: Hyy-Cary
 * Contact QQ  : 373889161(.)
 * email: 373889161@qq.com
 * WeChat: 18319021313
 */


namespace app\admin\controller;


use app\admin\BaseController;
use app\admin\model\Position as Model;
use think\facade\Db;
use think\facade\View;

class Position extends BaseController
{
    public function index(){
        return View();
    }
    public function dataList(){
        $data = request()->param();
        $size = $data['limit']?$data['limit']:10;
        $start = $data['page']?$data['page']:0;
        $where = [];
        if(array_key_exists('data',$data)){
            foreach ($data['data'] as $k=>$v){
                $where[] = [$v['name'],'like','%'.$v['value'].'%'];
            }
        }
        $list = pageTable('position',$start,$size,$where);
        $count = CountTable('position',$where);
        $arr = array('code'=>0,'msg'=>'ok','count'=>$count,'limit'=>$where,'data'=>$list);
        echo json_encode($arr);
    }
    public function add(){
        return view();
    }

    public function edit(){
        $id = request()->param('id');
        $data = Db::name('position')->where('id',$id)->find();
        View::assign('data',$data);
        return view();
    }

    public function saveAt(){
        $data = request()->param();
        $model = new Model;
        $result = $model->saveData($data);
        echo $result;
    }

    public function delAll(){
        $id = request()->param('data');
        $data = Db::name('position')->where('id',$id)->find();
        if($data){
            Db::name('position')->delete($id);
            $msg = ['code'=>200,'msg'=>lang('delete_message')];
        }else{
            $msg = ['code'=>300,'msg'=>lang('fail_message'),'data'=>$id];
        }
        echo json_encode($msg);
    }

    public function switchAt(){
        $id = request()->param('id');
        $field = request()->param('field');
        $msg = SwitchUp('position',$field,$id);
        echo json_encode($msg);
    }
    public function updateField(){
        $data = request()->param();
        $msg = FieldUpdate('position',$data);
        echo json_encode($msg);
    }
}