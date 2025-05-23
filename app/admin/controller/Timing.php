<?php
/**
 * Created by PhpStorm.
 * CreateTime  : 2025/05/08 18:48
 * file name : Timing.php
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

class Timing extends BaseController
{
    public function index(){
        return view();
    }
    public function datalist(){
        $data = request()->param();
        $size = $data['limit']?$data['limit']:10;
        $start = $data['page']?$data['page']:0;
        $list = pageTable('timing',$start,$size);
        $count = CountTable('timing');
        foreach ($list as $k=>$v){
            $list[$k]['key'] = md5($v['name']);
            $list[$k]['createTime'] = date('Y-m-d H:i:s',$v['createTime']);
            $list[$k]['updateTime'] = $v['updateTime']?date('Y-m-d H:i:s',$v['updateTime']):'';
        }
        $arr = array('code'=>0,'msg'=>'ok','count'=>$count,'data'=>$list);
        echo json_encode($arr);
    }
    public function add(){
        return view();
    }
    public function edit(){
        $id = request()->param('id');
        $data = FindTable('timing',[['id','=',$id]]);

        $weeks = json_decode($data['weeks']);
        $hours = json_decode($data['hours']);

        View::assign('weeks',(array)$weeks);
        View::assign('hours',(array)$hours);
        View::assign('data',$data);
        return view();
    }
    public function saveAt(){
        $data = request()->param();
        if($data['id'] == ''){
            $data['createTime'] = time();
            unset($data['id']);
        }
        $keys = ['weeks', 'hours'];
        foreach ($keys as $key) {
            if (array_key_exists($key, $data)) {
                $data[$key] = json_encode($data[$key]);
            }else{
                $data[$key] = '';
            }
        }

        SaveAt('timing',$data);
        $newArray = [];
        $originalArray = AllTable('timing');
        foreach ($originalArray as $item) {
            $key = md5($item['name']);       // 获取当前元素的 name 值
            $newArray[$key] = $item;    // 以 name 为键，存储整个子数组
        }
        putConfig($newArray,'timing.php');
        $msg = ['code'=>200,'msg'=>lang('success_message'),'data'=>$data];
        return json_encode($msg,JSON_UNESCAPED_UNICODE);
    }
    public function delAll(){
        $id = request()->param('data');
        $data = FindTable('timing',[['id','=',$id]]);
        if($data){
            Db::name('timing')->delete($id);
            $msg = ['code'=>200,'msg'=>lang('delete_message')];
        }else{
            $msg = ['code'=>300,'msg'=>lang('fail_message'),'data'=>$id];
        }
        echo json_encode($msg);
    }
    public function switchAt(){
        $id = request()->param('id');
        $field = request()->param('field');
        $msg = SwitchUp('timing',$field,$id);
        $newArray = [];
        $originalArray = AllTable('timing');
        foreach ($originalArray as $item) {
            $key = md5($item['id'].$item['name']);       // 获取当前元素的 name 值
            $newArray[$key] = $item;    // 以 name 为键，存储整个子数组
        }
        putConfig($newArray,'timing.php');
        echo json_encode($msg);
    }
}