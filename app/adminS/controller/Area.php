<?php
/**
 * Created by PhpStorm.
 * CreateTime  : 2023/08/12 19:17
 * file name : Area.php
 * User: asusa
 * Author: Hyy-Cary
 * Contact QQ  : 373889161(.)
 * email: 373889161@qq.com
 * WeChat: 18319021313
 */


namespace app\admin\controller;


use app\admin\BaseController;
use app\admin\model\Area as Model;
use think\facade\Db;
use think\facade\View;

class Area extends BaseController
{
    public function index(){
        return View();
    }
    public function datalist(){
        $id = request()->param('id');
        $tree = AllTable('area',['pid'=>$id],['id'=>'asc']);
        $count = CountTable('area');
        foreach ($tree as &$v){
            $result = FindTable('area',['pid'=>$v['id']]);
            $v['createTime'] = date('Y-m-d H:i:s',$v['createTime']);
            if($result){
                $v['haveChild'] = true;
            }
        }
        View::assign('tree',$tree);
        $arr = array('code'=>200,'msg'=>'ok','data'=>$tree);
        echo json_encode($arr,JSON_UNESCAPED_UNICODE);
    }
    public function add(){
        $id = request()->param('id');
        $tree = GetCache('area');
        foreach ($tree as $k=>$v){
            $level = $v['level']-1;
            if( $level > 1){
                $tree[$k]['p'] = str_repeat("&nbsp;&nbsp;&nbsp;",$level).'|--';
            }else{
                $tree[$k]['p']='';
            }
        }
        View::assign('tree',$tree);
        View::assign('id',$id);
        return View();
    }
    public function edit(){
        $id = request()->param('id');
        $tree = GetCache('area');
        $data = FindTable('area',[['id','=',$id]]);
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
        $model = new Model();
        $result = $model->dataSave($data);
        //更新行政区域缓存
        AreaList();
        echo $result;
    }
    public function city(){
        $id = request()->param('id');
        if($id){
            $tree = AllTable('area',['pid'=>$id],['id'=>'asc']);
            $msg = ['code'=>200,'msg'=>'ok','data'=>$tree];
        }else{
            $msg = ['code'=>300,'msg'=>'ok','data'=>[]];
        }
        return json_encode($msg);
    }
    public function delAll(){
        $id = request()->param('id');
        $data = Db::name('area')->where('id',$id)->find();
        if($data){
            if(ArrTree('area',$id)){
                $msg = ['status'=>300,'msg'=>lang('error_column_message')];
            }else{
                Db::name('area')->delete($id);
                //更新行政区域缓存
                AreaList();
                $msg = ['code'=>200,'msg'=>lang('delete_message')];
            }
        }else{
            $msg = ['code'=>300,'msg'=>lang('fail_message'),'data'=>$id];
        }
        echo json_encode($msg);
    }
}