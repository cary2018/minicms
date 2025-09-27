<?php
/**
 * Created by PhpStorm.
 * CreateTime  : 2023/05/19 20:10
 * file name : Link.php
 * User: asusa
 * Author: Hyy-Cary
 * Contact QQ  : 373889161(.)
 * email: 373889161@qq.com
 * WeChat: 18319021313
 */


namespace app\admin\controller;


use app\admin\BaseController;
use app\admin\model\Link as LinkModel;
use think\facade\Db;
use think\facade\View;

class Link extends BaseController
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
        $list = pageTable('link',$start,$size,$where);
        $count = CountTable('link',$where);
        foreach ($list as $k=>$v){
            $list[$k]['createTime'] = date('Y-m-d H:i:s',$v['createTime']);
            $list[$k]['updateTime'] = date('Y-m-d H:i:s',$v['updateTime']);
        }
        $arr = array('code'=>0,'msg'=>'ok','count'=>$count,'limit'=>$where,'data'=>$list);
        echo json_encode($arr);
    }
    public function add(){
        return view();
    }
    public function edit(){
        $id = request()->param('id');
        $data = Db::name('link')->where('id',$id)->find();
        View::assign('data',$data);
        return view();
    }
    public function saveAt(){
        $data = request()->param();
        $res = UploadImg('logo',1);
        if($res['code']==200){
            if($res['ident'] == 1){
                $res['code'] = 300;
                echo json_encode($res,JSON_UNESCAPED_UNICODE);
                die;
            }else{
                $data['logo']=$res['result']['img'];
            }
        }
        $model = new LinkModel;
        $result = $model->saveData($data);
        echo $result;
    }

    public function delAll(){
        $id = request()->param('data');
        $data = Db::name('link')->where('id',$id)->find();
        if($data){
            Db::name('link')->delete($id);
            if(file_exists($data['logo'])) {
                unlink($data['logo']);
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
        $msg = SwitchUp('link',$field,$id);
        echo json_encode($msg);
    }
    public function updateField(){
        $data = request()->param();
        $msg = FieldUpdate('link',$data);
        echo json_encode($msg);
    }
}