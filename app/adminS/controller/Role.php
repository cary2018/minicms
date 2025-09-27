<?php
/**
 * Created by PhpStorm.
 * CreateTime  : 2023/03/24 15:18
 * file name : Role.php
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
use app\admin\model\Role as RoleModel;

class Role extends BaseController
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
        $list = pageTable('role',$start,$size,$where);
        $count = CountTable('role',$where);
        foreach ($list as $k=>$v){
            $list[$k]['createTime'] = date('Y-m-d H:i:s',$v['createTime']);
            $list[$k]['updateTime'] = date('Y-m-d H:i:s',$v['updateTime']);
        }
        $arr = array('code'=>0,'msg'=>'ok','count'=>$count,'limt'=>$where,'data'=>$list);
        echo json_encode($arr);
    }
    public function add(){
        $node = GetMenu('node',[['isOpen','=',1]]);
        $result = GetNode($node,0,[]);
        $en = json_encode($result,JSON_UNESCAPED_UNICODE);
        $menu = GetMenu('menu',[['isShow','=',1]]);
        $res = MenuNode($menu,0,[]);
        $me = json_encode($res,JSON_UNESCAPED_UNICODE);
        View::assign('role',$en);
        View::assign('menu',$me);
        return View();
    }
    public function edit(){
        $id = request()->param('id');
        $data = Db::name('role')->where('id',$id)->find();
        $authCon = json_decode($data['auth'],true);
        $menuCon = json_decode($data['menuAuth'],true);
        $node = GetMenu('node',[['isOpen','=',1]]);
        $result = GetNode($node,0,$authCon?$authCon:[]);
        $en = json_encode($result,JSON_UNESCAPED_UNICODE);
        $menu = GetMenu('menu',[['isShow','=',1]]);
        $res = MenuNode($menu,0,$menuCon?$menuCon:[]);
        $me = json_encode($res,JSON_UNESCAPED_UNICODE);
        View::assign('role',$en);
        View::assign('menu',$me);
        View::assign('data',$data);
        return View();
    }
    public function saveAt(){
        $data = request()->param();
        $model = new RoleModel();
        $result = $model->saveData($data);
        echo $result;
    }
    public function switchAt(){
        $id = request()->param('id');
        $field = request()->param('field');
        $arr = Db::name('role')->where('id',$id)->find();
        if($arr){
            if($arr[$field] == 1){
                $val = 2;
            }else{
                $val = 1;
            }
            Db::name('role')->save(['id' => $id, $field => $val]);
            $msg = array('code'=>200,'msg'=>lang('update_status'));
        }else{
            $msg = array('code'=>300,'msg'=>lang('data_error'));
        }
        echo json_encode($msg);
    }
    public function delAll(){
        $id = request()->param('data');
        $data = Db::name('role')->where('id',$id)->find();
        if($data){
            Db::name('role')->delete($id);
            $msg = ['code'=>200,'msg'=>lang('delete_message')];
        }else{
            $msg = ['code'=>300,'msg'=>lang('fail_message'),'data'=>$id];
        }
        echo json_encode($msg);
    }
    //更新字段
    public function updateField(){
        $data = request()->param();
        $nar = Db::name('role')->where('id',$data['id'])->find();
        $field = $data['field'];
        if($nar){
            Db::name('role')->save(['id' => $data['id'], $field => $data['value']]);
            $msg = array('code'=>200,'msg'=>lang('update_success'));
        }else{
            $msg = array('code'=>300,'msg'=>lang('update_failed'));
        }
        echo json_encode($msg);
    }
}