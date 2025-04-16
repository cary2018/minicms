<?php
/**
 * Created by PhpStorm.
 * CreateTime  : 2023/10/15 20:58
 * file name : Visit.php
 * User: asusa
 * Author: Hyy-Cary
 * Contact QQ  : 373889161(.)
 * email: 373889161@qq.com
 * WeChat: 18319021313
 */


namespace app\admin\controller;


use app\admin\BaseController;
use think\facade\Config;
use think\facade\View;
use think\facade\Db;

class Visit extends BaseController
{
    public function index(){
        $today_start=mktime(0,0,0,date('m'),date('d'),date('Y'));
        $today_end=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
        $where = [['createTime','between',[$today_start,$today_end]]];
        $todayCount = CountTable('visit',$where);
        $views =  CountTable('visit');
        View::assign('today',$todayCount);
        View::assign('views',$views);
        return View();
    }
    public function datalist(){
        $data = request()->param();
        $size = $data['limit']?$data['limit']:10;
        $start = $data['page']?$data['page']:0;
        $where = [];
        $group = '';
        if(array_key_exists('data',$data)){
            foreach ($data['data'] as $k=>$v){
                if(!$v['value']){
                    continue;
                }
                if($v['name'] == 'range'){
                    if($v['value']){
                        $atime = explode(' ~ ',$v['value']);
                        $st = strtotime($atime[0]);
                        $et = strtotime($atime[1]);
                        $where[] = ['createTime','between',[$st,$et]];
                    }
                }
                if($v['name'] == 'guv'){
                    if($v['value']){
                        $group = $v['value'];
                    }
                }else{
                    $where[] = [$v['name'],'like','%'.$v['value'].'%'];
                }
            }
        }
        $list = pageTable('visit',$start,$size,$where,['id'=>'desc'],$group);
        $count = CountTable('visit',$where,'',$group);
        foreach ($list as &$v){
            $v['createTime'] = date('Y-m-d H:i:s',$v['createTime']);
        }
        $arr = array('code'=>0,'msg'=>'ok','count'=>$count,'limit'=>$where,'data'=>$list);
        echo json_encode($arr);
    }
    public function delAll(){
        $id = request()->param('data');
        if(empty($id)){
            $msg = ['code'=>300,'msg'=>lang('error_column_message')];
            echo json_encode($msg);die;
        }
        $data = Db::name('visit')->where('id','in',$id)->select();
        if($data){
            Db::name('visit')->delete($id);
            $msg = ['code'=>200,'msg'=>lang('delete_message')];
        }else{
            $msg = ['code'=>300,'msg'=>lang('fail_message'),'data'=>$id];
        }
        echo json_encode($msg);
    }
    public function emptyData(){
        //截断表数据（清空表数据）
        $prefix = Config::get('database.connections.mysql.prefix');
        Db::query('truncate table '.$prefix.'visit');
        $msg = ['code'=>1,'msg'=>lang('wipeData')];
        echo json_encode($msg);
    }
}