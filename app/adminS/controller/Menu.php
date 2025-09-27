<?php
/**
 * Created by PhpStorm.
 * CreateTime  : 2023/03/14 22:28
 * file name : Menu.php
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
use app\admin\model\Menu as menuModel;

class Menu extends BaseController
{
    public function index(){
        return view();
    }
    public function dataList(){
        $list = GetMenu();
        $count = CountTable('menu');
        $arr = array('code'=>0,'msg'=>'ok','count'=>$count,'data'=>$list);
        echo json_encode($arr);
    }
    public function add(){
        $id = request()->param('id');
        $menu = GetMenu();
        foreach ($menu as $k=>$v){
            $level = $v['level']-1;
            if( $level > 1){
                $menu[$k]['p'] = str_repeat("&nbsp;&nbsp;&nbsp;",$level).'|--';
            }else{
                $menu[$k]['p']='';
            }
        }
        View::assign('menu',$menu);
        View::assign('id',$id);
        return view();
    }
    public function edit(){
        $id = request()->param('id');
        $data = Db::name('menu')->where('id',$id)->find();
        $menu = GetMenu();
        foreach ($menu as $k=>$v){
            $level = $v['level']-1;
            if( $level > 1){
                $menu[$k]['p'] = str_repeat("&nbsp;&nbsp;&nbsp;",$level).'|--';
            }else{
                $menu[$k]['p']='';
            }
        }
        View::assign('menu',$menu);
        View::assign('data',$data);
        return view();
    }
    public function saveAt(){
        $data = request()->param();
        $menu = new menuModel;
        $result = $menu->saveData($data);
        //更新菜单缓存
        delCache('MenuList');
        caheMenu();
        echo $result;
    }
    public function switchAt(){
        $id = request()->param('id');
        $field = request()->param('field');
        $arr = Db::name('menu')->where('id',$id)->find();
        if($arr){
            if($arr[$field] == 1){
                $val = 2;
            }else{
                $val = 1;
            }
            Db::name('menu')->save(['id' => $id, $field => $val]);
            //更新菜单缓存
            delCache('MenuList');
            caheMenu();
            $msg = array('code'=>200,'msg'=>lang('update_status'));
        }else{
            $msg = array('code'=>300,'msg'=>lang('data_error'));
        }
        echo json_encode($msg);
    }
    public function delAll(){
        $id = request()->param('data');
        $data = Db::name('menu')->where('id',$id)->find();
        if($data){
            if(ArrTree('menu',$id)){
                $msg = ['status'=>300,'msg'=>lang('error_column_message')];
            }else{
                Db::name('menu')->delete($id);
                //更新菜单缓存
                delCache('MenuList');
                caheMenu();
                $msg = ['code'=>200,'msg'=>lang('delete_message')];
            }
        }else{
            $msg = ['code'=>300,'msg'=>lang('fail_message'),'data'=>$id];
        }
        echo json_encode($msg);
    }
    //更新字段
    public function updateField(){
        $data = request()->param();
        $nar = Db::name('menu')->where('id',$data['id'])->find();
        $field = $data['field'];
        if($nar){
            Db::name('menu')->save(['id' => $data['id'], $field => $data['value']]);
            //更新菜单缓存
            delCache('MenuList');
            caheMenu();
            $msg = array('code'=>200,'msg'=>lang('update_success'));
        }else{
            $msg = array('code'=>300,'msg'=>lang('update_failed'));
        }
        echo json_encode($msg);
    }

}