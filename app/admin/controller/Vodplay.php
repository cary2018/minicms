<?php
/**
 * Created by PhpStorm.
 * CreateTime  : 2025/07/01 19:01
 * file name : Vodplay.php
 * User: asusa
 * Author: Hyy-Cary（优）
 * Contact QQ  : 373889161(.)
 * email: 373889161@qq.com
 * WeChat: 18319021313
 */


namespace app\admin\controller;


use app\admin\BaseController;
use think\facade\Db;
use think\facade\View;

class Vodplay extends BaseController
{
    public function index(){
        return View();
    }
    public function dataList(){
        $list = config('vodplay');
        $count = count($list);
        $arr = array('code'=>0,'msg'=>'ok','count'=>$count,'data'=>$list);
        echo json_encode($arr);
    }
    public function add(){
        return View();
    }
    public function edit(){
        $id = request()->param('id');
        $data = config('vodplay');
        View::assign('data',$data[$id]);
        return View();
    }
    public function saveAt(){
        $data = request()->param();
        $old = config('vodplay');
        $old[$data['from']] = $data;
        $sort=[];
        foreach ($old as $k=>&$v){
            $sort[] = $v['sort'];
        }
        array_multisort($sort, SORT_DESC, SORT_FLAG_CASE , $old);
        putConfig($old,'vodplay.php');
        $msg = array('code'=>200,'msg'=>lang('success_message'));
        echo json_encode($msg);
    }
    public function delAll(){
        $data = request()->param('data');
        $list = config('vodplay');
        if(is_array($data)){
            foreach ($data as $v){
                unset($list[$v]);
            }
        }else{
            unset($list[$data]);
        }
        putConfig($list,'vodplay.php');
        $msg = ['code'=>200,'msg'=>lang('delete_message')];
        echo json_encode($msg);
    }

    public function switchAt(){
        $arr = request()->param();
        $data = config('vodplay');
        if($data[$arr['id']][$arr['field']] == 1){
            $data[$arr['id']][$arr['field']] = 0;
        }else{
            $data[$arr['id']][$arr['field']] = 1;
        }
        putConfig($data,'vodplay.php');
        $msg = array('code'=>200,'msg'=>lang('update_status'));
        echo json_encode($msg);
    }
}