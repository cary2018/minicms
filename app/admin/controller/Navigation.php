<?php
/**
 * Created by PhpStorm.
 * CreateTime  : 2023/06/25 17:05
 * file name : Navigation.php
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

class Navigation extends BaseController
{
    public function index(){
        $tree = GetMenu('classify');
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
    public function datalist(){
        $data = request()->param();
        $size = $data['limit']?$data['limit']:10;
        $start = $data['page']?$data['page']:0;
        $where = [];
        if(array_key_exists('data',$data)){
            foreach ($data['data'] as $k=>$v){
                if($v['name'] == 'nav_name'){
                    $where[] = [$v['name'],'like','%'.$v['value'].'%'];
                }else if($v['value'] != ''){
                    $where[] = [$v['name'],'=',$v['value']];
                }
            }
        }
        $count = CountTable('navigation',$where);
        $list = joinTable('navigation','classify',$start,$size,$where);
        foreach ($list as $k=>$v){
            $list[$k]['logo'] = ImgPath($v['logo']);
            $list[$k]['createTime'] = date('Y-m-d H:i:s',$v['createTime']);
            $list[$k]['updateTime'] = date('Y-m-d H:i:s',$v['updateTime']);
        }
        $arr = array('code'=>0,'msg'=>'ok','count'=>$count,'limit'=>$where,'data'=>$list);
        echo json_encode($arr);
    }
    public function add(){
        $tree = GetMenu('classify');
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
    public function edit(){
        $id = request()->param('id');
        $tree = GetMenu('classify');
        $data = FindTable('navigation',[['id','=',$id]]);
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
        $data['updateTime'] = time();
        if($data['id'] == ''){
            $data['createTime'] = time();
            unset($data['id']);
        }
        $res = UploadImg('logo',0);
        if($res['code']==200){
            if($res['ident'] == 1){
                $res['code'] = 300;
                echo json_encode($res,JSON_UNESCAPED_UNICODE);
                die;
            }else{
                $data['logo']=$res['result']['img'];
                $img = FindTable('navigation',[['id','=',$data['id']]]);
                if(file_exists($img['logo'])){
                    unlink($img['logo']);
                }
            }
        }
        SaveAt('navigation',$data);
        //更新上网导航缓存
        Navigation();
        $msg = ['code'=>200,'msg'=>lang('success_message')];
        echo json_encode($msg);
    }
    public function switchAt(){
        $id = request()->param('id');
        $field = request()->param('field');
        $result = SwitchUp('navigation',$field,$id);
        //更新上网导航缓存
        Navigation();
        echo json_encode($result);
    }
    public function updateField(){
        $data = request()->param();
        $msg = FieldUpdate('navigation',$data);
        //更新上网导航缓存
        Navigation();
        echo json_encode($msg);
    }
    public function delAll(){
        $id = request()->param('data');
        $data = Db::name('navigation')->where('id','in',$id)->select()->toArray();
        if($data){
            Db::name('navigation')->delete($id);
            $msg = ['code'=>200,'msg'=>lang('delete_message')];
        }else{
            $msg = ['code'=>300,'msg'=>lang('fail_message'),'data'=>$id];
        }
        //更新上网导航缓存
        Navigation();
        echo json_encode($msg);
    }
}