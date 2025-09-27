<?php
/**
 * Created by PhpStorm.
 * CreateTime  : 2023/06/27 15:31
 * file name : Liar.php
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

class Liar extends BaseController
{
    public function index(){
        return View();
    }
    public function datalist(){
        $data = request()->param();
        $size = $data['limit']?$data['limit']:10;
        $start = $data['page']?$data['page']:0;
        $where = [];
        if(array_key_exists('data',$data)){
            foreach ($data['data'] as $k=>$v){
                $where[] = [$v['name'],'like','%'.$v['value'].'%'];
            }
        }
        $list = pageTable('liar',$start,$size,$where);
        $count = CountTable('liar',$where);
        foreach ($list as $k=>$v){
            $list[$k]['createTime'] = date('Y-m-d H:i:s',$v['createTime']);
        }
        View::assign('tree',$list);
        $arr = array('code'=>0,'msg'=>'ok','count'=>$count,'limit'=>[],'data'=>$list);
        echo json_encode($arr);
    }
    public function add(){
        $id = request()->param('id');
        $data = FindTable('liar',['id'=>$id]);
        $img = AllTable('liar_img',['lid'=>$id]);
        View::assign('data',$data);
        View::assign('img',$img);
        return View();
    }
    public function switchAt(){
        $id = request()->param('id');
        $field = request()->param('field');
        $result = SwitchUp('liar',$field,$id);
        echo json_encode($result);
    }
    public function updateField(){
        $data = request()->param();
        $msg = FieldUpdate('liar',$data);
        echo json_encode($msg);
    }
    public function delAll(){
        $id = request()->param('data');
        $data = Db::name('liar')->where('id',$id)->find();
        if($data){
            Db::name('liar')->delete($id);
            $img = Db::name('liar_img')->where(['lid'=>$id])->select();
            if($img){
                foreach ($img as $v){
                    if(file_exists($v['img'])){
                        unlink($v['img']);
                    }
                    if(file_exists($v['thumbImg'])){
                        unlink($v['thumbImg']);
                    }
                    Db::name('liar_img')->delete($v['id']);
                }
            }
            $msg = ['code'=>200,'msg'=>lang('delete_message')];
        }else{
            $msg = ['code'=>300,'msg'=>lang('fail_message'),'data'=>$id];
        }
        echo json_encode($msg);
    }
}