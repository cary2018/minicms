<?php
/**
 * Created by PhpStorm.
 * CreateTime  : 2023/03/27 02:47
 * file name : Weblog.php
 * User: asusa
 * Author: Hyy-Cary
 * Contact QQ  : 373889161(.)
 * email: 373889161@qq.com
 * WeChat: 18319021313
 */


namespace app\admin\controller;


use app\admin\BaseController;
use think\facade\Db;

class Weblog extends BaseController
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
        $list = pageTable('weblog',$start,$size,$where);
        $count = CountTable('weblog',$where);
        foreach ($list as $k=>$v){
            $list[$k]['createTime'] = date('Y-m-d H:i:s',$v['createTime']);
        }
        $arr = array('code'=>0,'msg'=>'ok','count'=>$count,'limt'=>$where,'data'=>$list);

        echo json_encode($arr);
    }
    public function delAll(){
        $id = request()->param('data');
        if(empty($id)){
            $msg = ['code'=>300,'msg'=>lang('error_column_message')];
            echo json_encode($msg);die;
        }
        $data = Db::name('weblog')->where('id','in',$id)->select();
        if($data){
            Db::name('weblog')->delete($id);
            $msg = ['code'=>200,'msg'=>lang('delete_message')];
        }else{
            $msg = ['code'=>300,'msg'=>lang('fail_message'),'data'=>$id];
        }
        echo json_encode($msg);
    }
    //更新字段
    public function updateField(){
        $data = request()->param();
        $nar = Db::name('weblog')->where('id',$data['id'])->find();
        $field = $data['field'];
        if($nar){
            Db::name('weblog')->save(['id' => $data['id'], $field => $data['value']]);
            $msg = array('code'=>200,'msg'=>lang('update_success'));
        }else{
            $msg = array('code'=>300,'msg'=>lang('update_failed'));
        }
        echo json_encode($msg);
    }
    public function emptyData(){
        //截断表数据
        Db::query('truncate table cy_weblog');
        $msg = ['code'=>1,'msg'=>lang('wipeData')];
        echo json_encode($msg);
    }
}