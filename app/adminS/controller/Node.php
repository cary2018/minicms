<?php
/**
 * Created by PhpStorm.
 * CreateTime  : 2023/03/19 19:12
 * file name : Node.php
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

class Node extends BaseController
{
    public function index(){
        $node = GetMenu('node');
        $result = GetNode($node,0);
        $en = json_encode($result,JSON_UNESCAPED_UNICODE);
        View::assign('data',$en);
        return View();
    }
    public function dataList(){
        $list = GetMenu('node');
        $count = CountTable('menu');
        foreach ($list as $k=>$v){
            $list[$k]['createTime'] = date('Y-m-d H:i:s',$v['createTime']);
        }
        $arr = array('code'=>0,'msg'=>'ok','count'=>$count,'data'=>$list);
        echo json_encode($arr,JSON_UNESCAPED_UNICODE);
    }
    //更新字段
    public function updateField(){
        $data = request()->param();
        $nar = Db::name('node')->where('id',$data['id'])->find();
        $field = $data['field'];
        if($nar){
            Db::name('node')->save(['id' => $data['id'], $field => $data['value']]);
            $msg = array('code'=>200,'msg'=>lang('update_success'));
        }else{
            $msg = array('code'=>300,'msg'=>lang('update_failed'));
        }
        echo json_encode($msg);
    }
    public function switchAt(){
        $id = request()->param('id');
        $field = request()->param('field');
        $arr = Db::name('node')->where('id',$id)->find();
        if($arr){
            if($arr[$field] == 1){
                $val = 0;
            }else{
                $val = 1;
            }
            Db::name('node')->save(['id' => $id, $field => $val]);
            $msg = array('code'=>200,'msg'=>lang('update_status'));
        }else{
            $msg = array('code'=>300,'msg'=>lang('data_error'));
        }
        echo json_encode($msg);
    }
    //更新节点
    public function updateNode(){
        $result = AllMethods();
        $data = GetMenu('node');
        $arr = arrMac($result);
        //删除失效节点
        foreach ($data as $k=>$v){
            if(!in_array($v['nodeUrl'],$arr)){
                Db::name('node')->delete($v['id']);
            }
        }
        //更新节点
        foreach($result as $k=>$v){
            $model = $v['module'];
            $controller = strtolower($v['controller']);
            $action = $v['action'];
            $cn = getCer($v['controller']);
            $ac = getAct($action);
            $mc = $model.'/'.$controller;
            $router = $model.'/'.$controller.'/'.$action;
            if($router == 'admin/cache/index'){
                $ac = '清理缓存';
            }
            if($router == 'admin/system/index'){
                $ac = '参数配置';
            }
            if($router == 'admin/system/list'){
                $ac = '配置项';
            }
            $skip = ['admin/login','admin/welcome'];
            if(in_array($mc,$skip)){
                continue;
            }
            $res = Db::name('node')->where('nodeUrl',$mc)->find();
            if($res){
                $son = Db::name('node')->where('nodeUrl',$router)->find();
                $savData = [
                    'title'=>$ac,
                    'pid'=>$res['id'],
                    'path'=>'--'.$res['id'],
                    'nodeUrl'=>$router,
                    'createTime'=>time(),
                ];
                if(!$son){
                    Db::name('node')->save($savData);
                }
            }else{
                $son = Db::name('node')->where('nodeUrl',$mc)->find();
                $savData = [
                    'title'=>$cn,
                    'path'=>'-',
                    'nodeUrl'=>$mc,
                    'createTime'=>time(),
                ];
                if(!$son){
                    $id = Db::name('node')->insertGetId($savData);
                    $savData['pid'] = $id;
                    $savData['path'] = '--'.$id;
                    $savData['title'] = $ac;
                    $savData['nodeUrl'] = $router;
                    Db::name('node')->save($savData);
                }
            }
        }
        $newData = GetMenu('node');
        $count = CountTable('node');
        $msg = ['code'=>200,'msg'=>lang('node_update_success'),'count'=>$count,'data'=>$newData];
        echo json_encode($msg,JSON_UNESCAPED_UNICODE);
    }
}