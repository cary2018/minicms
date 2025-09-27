<?php
/**
 * Created by PhpStorm.
 * CreateTime  : 2023/03/21 23:20
 * file name : Admin.php
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
use app\admin\model\Admin as AdminModel;

class Admin extends BaseController
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
                if($v['name'] == 'group_id'){
                    $where[] = [$v['name'],'=',$v['value']];
                }else{
                    $where[] = [$v['name'],'like','%'.$v['value'].'%'];
                }
            }
        }
        $list = pageTable('admin',$start,$size,$where);
        $count = CountTable('admin',$where);
        foreach ($list as &$v){
            $v['createTime'] = date('Y-m-d H:i:s',$v['createTime']);
            $v['updateTime'] = date('Y-m-d H:i:s',$v['updateTime']);
            $pos = FindTable('position',['id'=>$v['position_id']]);
            if($pos){
                $v['position'] = $pos['name'];
            }
            $dep = FindTable('department',['id'=>$v['dept_id']]);
            if($dep){
                $v['depart'] = $dep['name'];
            }
        }
        $arr = array('code'=>0,'msg'=>'ok','count'=>$count,'limit'=>$where,'data'=>$list);
        echo json_encode($arr);
    }
    public function add(){
        $role = AllTable('role');
        $postion = AllTable('position',['status'=>1]);
        $city = '';
        $county = '';
        $tree = AllTable('area',['pid'=>0],['id'=>'asc']);
        $depart = GetMenu('department');
        foreach ($depart as &$v){
            $level = $v['level']-1;
            if( $level > 1){
                $v['p'] = str_repeat("&nbsp;&nbsp;&nbsp;",$level).'|--';
            }else{
                $v['p']='';
            }
        }
        View::assign('position',$postion);
        View::assign('depart',$depart);
        View::assign('area',$tree);
        View::assign('city',$city);
        View::assign('county',$county);
        View::assign('role',$role);
        return view();
    }

    public function edit(){
        $id = request()->param('id');
        $data = Db::name('admin')->where('id',$id)->find();
        $role = AllTable('role');
        $postion = AllTable('position',['status'=>1]);
        if($data){
            if($data['province_id']){
                $city = AllTable('area',['pid'=>$data['province_id']],['id'=>'asc']);
            }else{
                $city = '';
            }
            if($data['city_id']){
                $county = AllTable('area',['pid'=>$data['city_id']],['id'=>'asc']);
            }else{
                $county = '';
            }
            if($data['entry_date']){
                $data['entry_date'] = date('Y-m-d H:i:s',$data['entry_date']);
            }else{
                $data['entry_date'] = '';
            }
            if($data['depart_date']){
                $data['depart_date'] = date('Y-m-d H:i:s',$data['depart_date']);
            }else{
                $data['depart_date'] = '';
            }
        }else{
            $city = '';
            $county = '';
        }
        $tree = AllTable('area',['pid'=>0],['id'=>'asc']);
        if($data && $data['role']){
            $check = json_decode($data['role'],true);
        }else{
            $check = [];
        }
        $depart = GetMenu('department');
        foreach ($depart as &$v){
            $level = $v['level']-1;
            if( $level > 1){
                $v['p'] = str_repeat("&nbsp;&nbsp;&nbsp;",$level).'|--';
            }else{
                $v['p']='';
            }
        }

        View::assign('position',$postion);
        View::assign('depart',$depart);
        View::assign('area',$tree);
        View::assign('city',$city);
        View::assign('county',$county);
        View::assign('data',$data);
        View::assign('role',$role);
        View::assign('check',$check);
        return view();
    }

    public function saveAt(){
        $data = request()->param();
        $res = UploadImg('headImg',1);
        if($res['code']==200){
            if($res['ident'] == 1){
                $res['code'] = 300;
                echo json_encode($res,JSON_UNESCAPED_UNICODE);
                die;
            }else{
                $data['headImg']=$res['result']['img'];
                $data['thumbImg']=$res['result']['thumb'];
            }
        }
        $model = new AdminModel;
        $result = $model->saveData($data);
        echo $result;
    }

    public function info(){
        $id = request()->param('id');
        $data = Db::name('admin')->where('id',$id)->find();

        if($data){
            $data['position'] = '';
            $data['city'] = '';
            $data['county'] = '';
            $data['area'] = '';
            $data['depart'] = '';
            if($data['position_id']){
                $position = AllTable('position',[['status','=',1],['id','=',$data['position_id']]]);
                $data['position'] = $position[0]['name'];
            }
            if($data['city_id']){
                $city = AllTable('area',['id'=>$data['city_id']]);
                $data['city'] = $city[0]['name'];
            }
            if($data['county_id']){
                $county = AllTable('area',['id'=>$data['county_id']]);
                $data['county'] = $county[0]['name'];
            }
            if($data['province_id']){
                $area = AllTable('area',['id'=>$data['province_id']]);
                $data['area'] = $area[0]['name'];
            }
            if($data['dept_id']){
                $depart = AllTable('department',['id'=>$data['dept_id']]);
                $data['depart'] = $depart[0]['name'];
            }
        }
        View::assign('data',$data);
        return view();
    }

    public function saveInfo(){
        $data = request()->param();
        $res = UploadImg('headImg',1);
        if($res['code']==200){
            if($res['ident'] == 1){
                $res['code'] = 300;
                echo json_encode($res,JSON_UNESCAPED_UNICODE);
                die;
            }else{
                $data['headImg']=$res['result']['img'];
                $data['thumbImg']=$res['result']['thumb'];
            }
        }
        $model = new AdminModel;
        $result = $model->saveInfo($data);
        echo $result;
    }

    public function delAll(){
        $id = request()->param('data');
        $data = Db::name('admin')->where('id',$id)->find();
        if($data){
            Db::name('admin')->delete($id);
            if(file_exists($data['headImg'])) {
                unlink($data['headImg']);
                unlink($data['thumbImg']);
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
        $msg = SwitchUp('admin',$field,$id);
        echo json_encode($msg);
    }
    //更新字段
    public function updateField(){
        $data = request()->param();
        $msg = FieldUpdate('admin',$data);
        echo json_encode($msg);
    }
}