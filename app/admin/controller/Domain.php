<?php
/**
 * Created by PhpStorm.
 * CreateTime  : 2024/10/22 23:09
 * file name : Domain.php
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
use app\admin\model\Domain as Model;
class Domain extends BaseController
{
    public function index(){
        return view();
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
        $list = pageTable('domain',$start,$size,$where);
        $count = CountTable('domain',$where);
        foreach ($list as $k=>$v){
            $list[$k]['createTime'] = date('Y-m-d H:i:s',$v['createTime']);
            $list[$k]['updateTime'] = date('Y-m-d H:i:s',$v['updateTime']);
        }
        $arr = array('code'=>0,'msg'=>'ok','count'=>$count,'limit'=>$where,'data'=>$list);
        echo json_encode($arr);
    }
    public function add(){
        $template = RedTemplate();
        View::assign('template',$template);
        return view();
    }
    public function edit(){
        $id = request()->param('id');
        $data = Db::name('domain')->where('id',$id)->find();
        $template = RedTemplate();
        View::assign('template',$template);
        View::assign('data',$data);
        return view();
    }
    public function saveAt(){
        $data = request()->param();
        $res = UploadImg('web_logo',0);
        $ico = UploadImg('web_ico',0);
        if($res['code']==200){
            if($res['ident'] == 1){
                $res['code'] = 300;
                echo json_encode($res,JSON_UNESCAPED_UNICODE);
                die;
            }else{
                $data['web_logo']=$res['result']['img'];
            }
        }
        if($ico['code']==200){
            if($ico['ident'] == 1){
                $ico['code'] = 300;
                echo json_encode($ico,JSON_UNESCAPED_UNICODE);
                die;
            }else{
                $data['web_ico']=$ico['result']['img'];
            }
        }
        $model = new Model();
        echo $model->saveData($data);
        //生成配置文件
        putDomain();
    }
    public function delAll(){
        $id = request()->param('data');
        $data = Db::name('domain')->where('id',$id)->find();
        if($data){
            Db::name('domain')->delete($id);
            if(file_exists($data['web_logo'])) {
                unlink($data['web_logo']);
            }
            if(file_exists($data['web_ico'])) {
                unlink($data['web_ico']);
            }
            $msg = ['code'=>200,'msg'=>lang('delete_message')];
        }else{
            $msg = ['code'=>300,'msg'=>lang('fail_message'),'data'=>$id];
        }
        echo json_encode($msg);
        //生成配置文件
        putDomain();
    }
    public function updateField(){
        $data = request()->param();
        $msg = FieldUpdate('domain',$data);
        echo json_encode($msg);
        //生成配置文件
        putDomain();
    }
}