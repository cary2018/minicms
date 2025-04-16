<?php
/**
 * Created by PhpStorm.
 * CreateTime  : 2023/04/03 20:09
 * file name : system.php
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
use app\admin\model\Config as systemModel;

class System extends BaseController
{
    public function index(){
        $arr = GetMenu('config');
        $html = TmHtml($arr);
        $data = MdaTree($html);
        View::assign('data',$data);
        return View();
    }
    public function list(){
        return View();
    }
    public function dataList(){
        $list = GetMenu('config');
        $count = CountTable('config');
        $arr = array('code'=>0,'msg'=>'ok','count'=>$count,'data'=>$list);
        echo json_encode($arr);
    }
    public function add(){
        $id = request()->param('id');
        $pid = GetMenu('config');
        foreach ($pid as $key=>$val){
            $level = $val['level']-1;
            if( $level > 1){
                $pid[$key]['p'] = str_repeat("&nbsp;&nbsp;&nbsp;",$level).'|--';
            }else{
                $pid[$key]['p']='';
            }
        }
        View::assign('pid',$pid);
        View::assign('id',$id);
        return View();
    }
    public function edit(){
        $id = request()->param('id');
        $data = FindTable('config',[['id','=',$id]]);
        $pid = GetMenu('config');
        foreach ($pid as $key=>$val){
            $level = $val['level']-1;
            if( $level > 1){
                $pid[$key]['p'] = str_repeat("&nbsp;&nbsp;&nbsp;",$level).'|--';
            }else{
                $pid[$key]['p']='';
            }
        }
        View::assign('pid',$pid);
        View::assign('data',$data);
        return View();
    }
    public function saveAt(){
        $data = request()->param();
        if($data['sys_type']=='file'){
            $res = UploadImg('variableFile',0);
            if($res['code']==200){
                if($res['ident'] == 1){
                    $res['code'] = 300;
                    echo json_encode($res,JSON_UNESCAPED_UNICODE);
                    die;
                }else{
                    $data['sys_content']=$res['result']['img'];
                }
            }
        }
        $model = new systemModel();
        $result = $model->saveData($data);
        //写入配置文件
        putFile();
        echo $result;
    }
    public function batchSave(){
        $data = request()->param();
        $model = new systemModel();
        $arr = [];
        foreach ($data['content'] as $k=>$v){
            array_push($arr,['id'=>$k,'sys_content'=>$v]);
        }
        $model->saveAll($arr);
        //写入配置文件
        putFile();
        $msg = ['code'=>200,'msg'=>'更新成功'];
        echo json_encode($msg);
    }
    public function delAll(){
        $id = request()->param('data');
        $data = Db::name('config')->where('id',$id)->find();
        if($data){
            if(ArrTree('config',$id)){
                $msg = ['status'=>300,'msg'=>lang('error_column_message')];
            }else{
                $img = FindTable('config',[['id','=',$id]]);
                if(file_exists($img['sys_content'])){
                    unlink($img['sys_content']);
                }
                Db::name('config')->delete($id);
                $msg = ['code'=>200,'msg'=>lang('delete_message')];
            }
        }else{
            $msg = ['code'=>300,'msg'=>lang('fail_message'),'data'=>$id];
        }
        //写入配置文件
        putFile();
        echo json_encode($msg);
    }
    //更新字段
    public function updateField(){
        $data = request()->param();
        $nar = Db::name('config')->where('id',$data['id'])->find();
        $field = $data['field'];
        if($nar){
            Db::name('config')->save(['id' => $data['id'], $field => $data['value']]);
            //写入配置文件
            putFile();
            $msg = array('code'=>200,'msg'=>lang('update_success'));
        }else{
            $msg = array('code'=>300,'msg'=>lang('update_failed'));
        }
        echo json_encode($msg);
    }
}