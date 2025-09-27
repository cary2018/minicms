<?php
/**
 * Created by PhpStorm.
 * CreateTime  : 2023/10/01 16:36
 * file name : Feedback.php
 * User: asusa
 * Author: Hyy-Cary
 * Contact QQ  : 373889161(.)
 * email: 373889161@qq.com
 * WeChat: 18319021313
 */


namespace app\admin\controller;


use app\admin\BaseController;
use think\facade\Db;

class Feedback extends BaseController
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
        $list = pageTable('feedback',$start,$size,$where);
        $count = CountTable('feedback',$where);
        foreach ($list as &$v){
            $cate = FindTable('category',['id'=>$v['cid']]);
            $v['createTime'] = date('Y-m-d H:i:s',$v['createTime']);
            $v['temp_archives'] = $cate['temp_archives'];
        }
        $arr = array('code'=>0,'msg'=>'ok','count'=>$count,'data'=>$list);
        echo json_encode($arr);
    }

    public function delAll(){
        $id = request()->param('data');
        $data = Db::name('feedback')->where('id','in',$id)->select()->toArray();
        if($data){
            Db::name('feedback')->delete($id);
            $msg = ['code'=>200,'msg'=>lang('delete_message')];
        }else{
            $msg = ['code'=>300,'msg'=>lang('fail_message'),'data'=>$id];
        }
        echo json_encode($msg);
    }
    public function switchAt(){
        $id = request()->param('id');
        $field = request()->param('field');
        $msg = SwitchUp('feedback',$field,$id);
        echo json_encode($msg);
    }
}