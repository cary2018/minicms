<?php
/**
 * Created by PhpStorm.
 * CreateTime  : 2023/05/23 14:55
 * file name : Album.php
 * User: asusa
 * Author: Hyy-Cary
 * Contact QQ  : 373889161(.)
 * email: 373889161@qq.com
 * WeChat: 18319021313
 */


namespace app\admin\controller;


use app\admin\BaseController;
use app\admin\model\Album as AlbumModel;
use think\facade\Db;
use think\facade\View;

class Album extends BaseController
{
    public function index(){
        $tree = GetMenu('albumCategory');
        foreach ($tree as $k=>$v){
            $level = $v['level']-1;
            if( $level > 1){
                $tree[$k]['p'] = str_repeat("&nbsp;&nbsp;&nbsp;",$level).'|--';
            }else{
                $tree[$k]['p']='';
            }
        }
        View::assign('tree',$tree);


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
        $list = joinTable('album','album_category',$start,$size,$where);
        $count = CountTable('album',$where);
        foreach ($list as $k=>$v){
            $list[$k]['createTime'] = date('Y-m-d H:i:s',$v['createTime']);
        }
        $arr = array('code'=>0,'msg'=>'ok','count'=>$count,'limit'=>$where,'data'=>$list);
        echo json_encode($arr);
    }
    public function add(){
        $id = request()->param('id');
        $tree = GetMenu('albumCategory');
        foreach ($tree as $k=>$v){
            $level = $v['level']-1;
            if( $level > 1){
                $tree[$k]['p'] = str_repeat("&nbsp;&nbsp;&nbsp;",$level).'|--';
            }else{
                $tree[$k]['p']='';
            }
        }
        View::assign('id',$id);
        View::assign('tree',$tree);
        return View();
    }

    public function edit(){
        $id = request()->param('id');
        $tree = GetMenu('albumCategory');
        $data = FindTable('album',[['id','=',$id]]);
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
        $data = json_decode($data['form'],true);
        $arr = [];
        foreach($data as $v){
            $arr[$v['name']]=$v['value'];
        }
        $res = UploadImg('thumbImg');
        if($res['code']==200){
            if($res['ident'] == 1){
                $res['code'] = 300;
                echo json_encode($res,JSON_UNESCAPED_UNICODE);
                die;
            }else{
                $arr['img']=$res['result']['img'];
                $arr['thumbImg']=$res['result']['thumb'];
            }
        }
        $model = new AlbumModel;
        $result = $model->saveData($arr);
        echo $result;
    }
    public function editSave(){
        $data = request()->param();
        $res = UploadImg('thumbImg');
        if($res['code']==200){
            if($res['ident'] == 1){
                $res['code'] = 300;
                echo json_encode($res,JSON_UNESCAPED_UNICODE);
                die;
            }else{
                $data['img']=$res['result']['img'];
                $data['thumbImg']=$res['result']['thumb'];
            }
        }
        $model = new AlbumModel;
        $result = $model->saveData($data);
        echo $result;
    }

    public function delAll(){
        $id = request()->param('data');
        $data = Db::name('album')->where('id','in',$id)->select()->toArray();
        if($data){
            foreach ($data as $v){
                Db::name('album')->delete($v['id']);
                if(file_exists($v['img'])) {
                    unlink($v['img']);
                    unlink($v['thumbImg']);
                }
            }
            $msg = ['code'=>200,'msg'=>lang('delete_message')];
        }else{
            $msg = ['code'=>300,'msg'=>lang('fail_message'),'data'=>$id];
        }
        echo json_encode($msg);
    }

    public function switchAt(){
        $id = request()->param('id');
        $field = request()->param('field');
        $msg = SwitchUp('album',$field,$id);
        echo json_encode($msg);
    }
    public function updateField(){
        $data = request()->param();
        $msg = FieldUpdate('album',$data);
        echo json_encode($msg);
    }
}