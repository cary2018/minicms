<?php
/**
 * Created by PhpStorm.
 * CreateTime  : 2023/04/18 15:56
 * file name : Article.php
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
use app\admin\model\Article as Model;

class Article extends BaseController
{
    public function index(){
        $tree = GetMenu('category',[['type','=',0]]);
        $read = config('common');
        $attr = GetCache('attribute');
        foreach ($tree as $k=>$v){
            $level = $v['level']-1;
            if( $level > 1){
                $tree[$k]['p'] = str_repeat("&nbsp;&nbsp;&nbsp;",$level).'|--';
            }else{
                $tree[$k]['p']='';
            }
        }
        View::assign('read',$read['read']);
        View::assign('attr',$attr);
        View::assign('tree',$tree);
        return View();
    }
    public function dataList(){
        $data = request()->param();
        $size = $data['limit']?$data['limit']:10;
        $start = $data['page']?$data['page']:0;
        $where = [['recycle','=',0]];
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
        $tree = GetMenu('category',[['type','=',0]]);
        $read = config('common');
        $attr = GetCache('attribute');
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
        View::assign('attr',$attr);
        return View();
    }

    public function edit(){
        $id = request()->param('id');
        $data = Db::name('article')->where('id',$id)->find();
        $tree = GetMenu('category',[['type','=',0]]);
        $soft = AllTable('software',[['aid','=',$id]]);
        $read = config('common');
        $attr = GetCache('attribute');
        foreach ($tree as $k=>$v){
            $level = $v['level']-1;
            if( $level > 1){
                $tree[$k]['p'] = str_repeat("&nbsp;&nbsp;&nbsp;",$level).'|--';
            }else{
                $tree[$k]['p']='';
            }
        }
        if ($data){
            $data['attrId'] = explode(',',$data['attrId']);
        }
        View::assign('data',$data);
        View::assign('tree',$tree);
        View::assign('soft',$soft);
        View::assign('read',$read['read']);
        View::assign('attr',$attr);
        return View();
    }

    public function saveAt(){
        $data = request()->param();
        $async = $data['async'];
        unset($data['async']);
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
                $data['annex']=$file['result']['file'];
            }
        }
        $model = new Model();
        $result = $model->dataSave($data);
        if($async == 1){
            $arr = [
                'url'=>$_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'],
                'cid'=>$data['cid'],
                'title'=>$data['title'],
                'tags'=>$data['tags'],
                'keywords'=>$data['keywords'],
                'description'=>$data['description'],
                'content'=>$data['content'],
                'status'=>$data['status'],
                'orderSort'=>$data['orderSort'],
                'downloadJur'=>$data['downloadJur'],
            ];
            $sign = signMd5($arr,Cfg('sign_key'));
            $arr['sign'] = $sign;
            //数据同步
            $post_url = explode(',',Cfg('async_url'));
            foreach ($post_url as $item){
                $asyncResult = FCurl_post($item,$arr,'articleImg');
                //print_r($asyncResult);
            }
        }
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
        $data = Db::name('article')->where('id','in',$id)->select()->toArray();
        if($data){
            foreach ($data as $v){
                Db::name('article')->where('id',$v['id'])->update(['recycle'=>1]);
            }
            $msg = ['code'=>200,'msg'=>lang('delete_message')];
        }else{
            $msg = ['code'=>300,'msg'=>lang('fail_message'),'data'=>$id];
        }
        echo json_encode($msg);
    }
    public function del(){
        $id = request()->param('id');
        $data = Db::name('software')->where('id',$id)->find();
        if($data){
            Db::name('software')->delete($id);
            $msg = ['code'=>200,'msg'=>lang('delete_message')];
        }else{
            $msg = ['code'=>300,'msg'=>lang('fail_message'),'data'=>$id];
        }
        echo json_encode($msg);
    }
    public function updateField(){
        $data = request()->param();
        $msg = FieldUpdate('article',$data);
        echo json_encode($msg);
    }

    public function recycle(){
        $tree = GetMenu('category',[['type','=',0]]);
        $read = config('common');
        $attr = GetCache('attribute');
        foreach ($tree as $k=>$v){
            $level = $v['level']-1;
            if( $level > 1){
                $tree[$k]['p'] = str_repeat("&nbsp;&nbsp;&nbsp;",$level).'|--';
            }else{
                $tree[$k]['p']='';
            }
        }
        View::assign('read',$read['read']);
        View::assign('attr',$attr);
        View::assign('tree',$tree);
        return View();
    }
    public function recList(){
        $data = request()->param();
        $size = $data['limit']?$data['limit']:10;
        $start = $data['page']?$data['page']:0;
        $where = [['recycle','=',1]];
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
    public function delRecycle(){
        $id = request()->param('data');
        $data = Db::name('article')->where('id','in',$id)->select()->toArray();
        if($data){
            foreach ($data as $v){
                Db::name('article')->delete($v['id']);
                if(file_exists($v['articleImg'])){
                    unlink($v['articleImg']);
                }
                if(file_exists($v['articleThumbImg'])){
                    unlink($v['articleThumbImg']);
                }
                if(file_exists($v['annex'])){
                    unlink($v['annex']);
                }
                Db::name('software')->where('aid',$v['id'])->delete();
            }
            $msg = ['code'=>200,'msg'=>lang('delete_message')];
        }else{
            $msg = ['code'=>300,'msg'=>lang('fail_message'),'data'=>$id];
        }
        echo json_encode($msg);
    }

    public function revert(){
        $id = request()->param('data');
        $data = Db::name('article')->where('id','in',$id)->select()->toArray();
        if($data){
            foreach ($data as $v){
                Db::name('article')->where('id',$v['id'])->update(['recycle'=>0]);
            }
            $msg = ['code'=>200,'msg'=>lang('recycle_message')];
        }else{
            $msg = ['code'=>300,'msg'=>lang('recycle_fail'),'data'=>$id];
        }
        echo json_encode($msg);
    }

}