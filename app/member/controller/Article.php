<?php
/**
 * Created by PhpStorm.
 * CreateTime  : 2024/08/13 12:34
 * file name : Article.php
 * User: asusa
 * Author: Hyy-Cary（优）
 * Contact QQ  : 373889161(.)
 * email: 373889161@qq.com
 * WeChat: 18319021313
 */


namespace app\member\controller;


use app\member\model\Article as Model;
use app\member\BaseController;
use think\facade\Db;
use think\facade\View;

class Article extends BaseController
{
    public function index(){
        $tree = GetMenu('category',[['share','=',1],['type','=',0]]);
        $read = config('common');
        foreach ($tree as $k=>$v){
            $level = $v['level']-1;
            if( $level > 1){
                $tree[$k]['p'] = str_repeat("&nbsp;&nbsp;&nbsp;",$level).'|--';
            }else{
                $tree[$k]['p']='';
            }
        }
        View::assign('read',$read['read']);
        View::assign('tree',$tree);
        return view();
    }
    public function datalist(){
        $data = request()->param();
        $member = GetSe('MemberCenter');
        $size = $data['limit']?$data['limit']:10;
        $start = $data['page']?$data['page']:0;
        $where = [['uid','=',$member['id']],['recycle','=',0]];
        if(array_key_exists('data',$data)){
            foreach ($data['data'] as $k=>$v){
                if($v['value'] !=='' ){
                    if($v['name'] == 'title'){
                        $where[] = [$v['name'],'like','%'.$v['value'].'%'];
                    }else{
                        $where[] = [$v['name'],'=',$v['value']];
                    }
                }
            }
        }
        $count = CountTable('article',$where);
        $list = joinTable('article','category',$start,$size,$where);
        $str = config('common');
        foreach ($list as &$v){
            $v['status1'] = $str['read'][0];
            $v['status2'] = $str['read'][1];
            $v['createTime'] = date('Y-m-d H:i:s',$v['createTime']);
            $v['updateTime'] = date('Y-m-d H:i:s',$v['updateTime']);
        }
        $arr = array('code'=>0,'msg'=>'ok','count'=>$count,'data'=>$list);
        echo json_encode($arr);
    }
    public function add(){
        $tree = GetMenu('category',[['share','=',1],['type','=',0]]);
        $read = config('common');
        foreach ($tree as $k=>$v){
            $level = $v['level']-1;
            if( $level > 1){
                $tree[$k]['p'] = str_repeat("&nbsp;&nbsp;&nbsp;",$level).'|--';
            }else{
                $tree[$k]['p']='';
            }
        }
        View::assign('tree',$tree);
        View::assign('read',$read['read']);
        return View();
    }

    public function edit(){
        $id = request()->param('id');
        $data = Db::name('article')->where('id',$id)->find();
        $tree = GetMenu('category',[['share','=',1],['type','=',0]]);
        $soft = AllTable('software',[['aid','=',$id]]);
        $read = config('common');
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
        View::assign('soft',$soft);
        View::assign('read',$read['read']);
        return View();
    }

    public function saveAt(){
        $data = request()->param();
        $res = UploadImg('articleImg');
        if($res['code']==200){
            if($res['ident'] == 1){
                $res['code'] = 300;
                echo json_encode($res,JSON_UNESCAPED_UNICODE);
                die;
            }else{
                $data['articleImg']=$res['result']['img'];
                $data['articleThumbImg']=$res['result']['thumb'];
            }
        }
        $file = uploadFile('annex');
        if($file['code']==200){
            if($file['ident'] == 1){
                $file['code'] = 300;
                echo json_encode($file,JSON_UNESCAPED_UNICODE);
                die;
            }else{
                $data['annex']=$file['result']['img'];
            }
        }
        $model = new Model();
        $result = $model->dataSave($data);
        echo $result;
    }

    public function switchAt(){
        $id = request()->param('id');
        $field = request()->param('field');
        $msg = SwitchUp('article',$field,$id);
        echo json_encode($msg);
    }
    public function delAll(){
        $id = request()->param('data');
        $member = GetSe('MemberCenter');
        $data = Db::name('article')->where([['id','in',$id],['uid','=',$member['id']]])->select()->toArray();
        if($data){
            foreach ($data as $v){
                Db::name('article')->where('id',$v['id'])->update(['recycle'=>1]);
            }
            $msg = ['code'=>200,'msg'=>lang('delete_message'),'data'=>$data];
        }else{
            $msg = ['code'=>300,'msg'=>lang('fail_message'),'data'=>$id];
        }
        echo json_encode($msg);
    }
}