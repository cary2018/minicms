<?php
/**
 * Created by PhpStorm.
 * CreateTime  : 2025/05/26 17:49
 * file name : Vod.php
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

class Vod extends BaseController
{
    public function index(){
        $tree = GetMenu('category',[['type','=',1]]);
        $info = config('common');
        foreach ($tree as $k=>$v){
            $level = $v['level']-1;
            if( $level > 1){
                $tree[$k]['p'] = str_repeat("&nbsp;&nbsp;&nbsp;",$level).'|--';
            }else{
                $tree[$k]['p']='';
            }
        }
        View::assign('tree',$tree);
        View::assign('status',$info['vod']['status']);
        return View();
    }
    public function dataList(){
        $data = request()->param();
        $size = $data['limit']?$data['limit']:10;
        $start = $data['page']?$data['page']:0;
        $where = [];
        if(array_key_exists('data',$data)){
            foreach ($data['data'] as $k=>$v){
                if($v['value'] !=='' ){
                    if($v['name'] == 'vod_name'){
                        $where[] = [$v['name'],'like','%'.$v['value'].'%'];
                    }else{
                        $where[] = [$v['name'],'=',$v['value']];
                    }
                }
            }
        }
        $count = CountTable('vod',$where);
        $list = joinTables(['where'=>$where,'page'=>$start,'limit'=>$size,'order'=>['vod_time'=>'desc']]);
        foreach ($list as &$v){
            $v['vod_time'] = mac_day($v['vod_time'],'color');
        }
        $arr = array('code'=>0,'msg'=>'ok','count'=>$count,'data'=>$list);
        echo json_encode($arr);
    }
    public function add(){
        $tree = GetMenu('category',[['type','=',1]]);
        $info = config('common');
        $fromlist = config('vodplay');
        foreach ($tree as $k=>$v){
            $level = $v['level']-1;
            if( $level > 1){
                $tree[$k]['p'] = str_repeat("&nbsp;&nbsp;&nbsp;",$level).'|--';
            }else{
                $tree[$k]['p']='';
            }
        }
        View::assign('tree',$tree);
        View::assign('fromlist',$fromlist);
        View::assign('status',$info['vod']['status']);
        View::assign('lock',$info['vod']['lock']);
        View::assign('isend',$info['vod']['isend']);
        View::assign('copyright',$info['vod']['copyright']);
        return View();
    }
    public function edit(){
        $id = request()->param('id');
        $data = FindTable('vod',[['vod_id','=',$id]],['vod_id'=>'desc']);
        $from = explode('$$$',$data['vod_play_from']);
        $play = explode('$$$',$data['vod_play_url']);
        $fromlist = config('vodplay');
        $tree = GetMenu('category',[['type','=',1]]);
        $info = config('common');
        foreach ($tree as $k=>$v){
            $level = $v['level']-1;
            if( $level > 1){
                $tree[$k]['p'] = str_repeat("&nbsp;&nbsp;&nbsp;",$level).'|--';
            }else{
                $tree[$k]['p']='';
            }
        }
        View::assign('data',$data);
        View::assign('tree',$tree);
        View::assign('from',$from);
        View::assign('fromlist',$fromlist);
        View::assign('play',$play);
        View::assign('status',$info['vod']['status']);
        View::assign('lock',$info['vod']['lock']);
        View::assign('isend',$info['vod']['isend']);
        View::assign('copyright',$info['vod']['copyright']);
        return View();
    }
    public function saveAt(){
        $data = request()->param();
        $res = UploadImg('vod_pic');
        $slide = UploadImg('vod_pic_slide',0);
        if($res['code']==200){
            if($res['ident'] == 1){
                $res['code'] = 300;
                echo json_encode($res,JSON_UNESCAPED_UNICODE);
                die;
            }else{
                $data['vod_pic']=$res['result']['img'];
                $data['vod_pic_thumb']=$res['result']['thumb'];
            }
        }
        if($slide['code']==200){
            if($res['ident'] == 1){
                $res['code'] = 300;
                echo json_encode($res,JSON_UNESCAPED_UNICODE);
                die;
            }else{
                $data['vod_pic_slide']=$res['result']['img'];
            }
        }
        $data['vod_play_url'] = implode('$$$',$data['vod_play_url']);
        $data['vod_play_from'] = implode('$$$',$data['vod_play_from']);
        SaveAt('vod',$data);
        $msg = ['code'=>200,'msg'=>lang('success_message')];
        return json_encode($msg,JSON_UNESCAPED_UNICODE);
    }
    public function switchAt(){
        $id = request()->param('id');
        $field = request()->param('field');
        $msg = SwitchStatus(['field'=>$field,'id'=>$id]);
        echo json_encode($msg);
    }
    public function delAll(){
        $id = request()->param('data');
        $data = Db::name('vod')->where('vod_id','in',$id)->select()->toArray();
        if($data){
            foreach ($data as $v){
                if(file_exists($v['vod_pic'])){
                    unlink($v['vod_pic']);
                }
                if(file_exists($v['vod_pic_thumb'])){
                    unlink($v['vod_pic_thumb']);
                }
                if(file_exists($v['vod_pic_slide'])){
                    unlink($v['vod_pic_slide']);
                }
                Db::name('vod')->where('vod_id',$v['vod_id'])->delete();
            }
            $msg = ['code'=>200,'msg'=>lang('delete_message')];
        }else{
            $msg = ['code'=>300,'msg'=>lang('fail_message'),'data'=>$id];
        }
        echo json_encode($msg);
    }
    public function updateField(){
        $data = request()->param();
        if (array_key_exists('id', $data)) {
            // 保留值并修改键名（保持原有顺序）
            $data['vod_id'] = $data['id']; // 添加新键
            unset($data['id']);            // 删除旧键
        }
        $msg = FieldUpdate('vod',$data);
        echo json_encode($msg);
    }
}