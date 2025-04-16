<?php
/**
 * Created by PhpStorm.
 * CreateTime  : 2024/12/03 19:11
 * file name : Collect.php
 * User: asusa
 * Author: Hyy-Cary（优）
 * Contact QQ  : 373889161(.)
 * email: 373889161@qq.com
 * WeChat: 18319021313
 */


namespace app\admin\controller;


use app\admin\BaseController;
use Overtrue\Pinyin\Pinyin;
use app\model\Collect as Model;
use think\facade\Db;
use think\facade\View;

class Collect extends BaseController
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
                if($v['value'] !=='' ){
                    if($v['name'] == 'title'){
                        $where[] = [$v['name'],'like','%'.$v['value'].'%'];
                    }else{
                        $where[] = [$v['name'],'=',$v['value']];
                    }
                }
            }
        }
        $count = CountTable('collect',$where);
        $list = pageTable('collect',$start,$size,$where,['collect_id'=>'desc']);

        $baseParams = [
            'ac'=>'',
            'h'=>'',
            't'=>'',
            'ids'=>'',
            'wd'=>'',
            'page'=>0,
        ];

        // 初始化跳转配置
        $types = [
            'jump' => ['ac' => 'list'],
            'today' => ['ac' => 'cj', 'h' => 24],
            'week' => ['ac' => 'cj', 'h' => 168],
            'cjAll' => ['ac' => 'cj'],
        ];

        foreach ($list as &$v) {
            // 公共参数
            $commonParams = [
                'cjflag' => md5($v['collect_url']),
                'cjurl' => $v['collect_url'],
                'type' => $v['collect_type'],
                'mid' => $v['collect_mid'],
                'opt' => $v['collect_opt'],
                'sync_pic_opt' => $v['collect_sync_pic_opt'],
                'filter' => $v['collect_filter'],
                'filter_from' => $v['collect_filter_from'],
                'filter_year' => $v['collect_filter_year'],
                'param' => base64_encode($v['collect_param'])
            ];

            // 为每个类型合并公共参数
            foreach ($types as $key => $params) {
                // 如果 h 值为空，使用基准参数中的默认值
                $v[$key] = http_build_query(array_merge($baseParams,$params, $commonParams));
            }

            // 处理 collect_type 和 collect_mid
            $v['collect_type'] = vodType($v['collect_type']);
            $v['collect_mid'] = vodMid($v['collect_mid']);
            $v['createTime'] = date('Y-m-d H:i:s', $v['createTime']);
            $v['updateTime'] = date('Y-m-d H:i:s', $v['updateTime']);
        }
        $arr = ['code' => 0, 'msg' => 'ok', 'count' => $count, 'limit' => $where, 'data' => $list];
        echo json_encode($arr);

    }

    public function add(){
        return view();
    }

    public function edit(){
        $id = request()->param('id');
        $data = FindTable('collect',[['collect_id','=',$id]],['collect_id'=>'desc']);
        View::assign('data',$data);
        return view();
    }

    public function saveAt(){
        $data = request()->param();
        if(!$data['collect_id']){
            unset($data['collect_id']);
            $data['createTime'] = time();
        }
        $data['updateTime'] = time();
        SaveAt('collect',$data);
        $msg = ['code'=>200,'msg'=>lang('success_message')];
        return json_encode($msg);
    }

    public function delAll()
    {
        $id = request()->param('data');
        $data = Db::name('collect')->where('collect_id', 'in', $id)->select()->toArray();
        if ($data) {
            Db::name('collect')->delete($id);
            $msg = ['code' => 200, 'msg' => lang('delete_message')];
        } else {
            $msg = ['code' => 300, 'msg' => lang('fail_message'), 'data' => $id];
        }
        echo json_encode($msg);
    }

    public function api(){
        $param = request()->param();
        $today = $param;
        $all = $param;
        //分类
        $type_list = GetCache('type_list');

        if (!empty($param['pg'])) {
            $param['page'] = $param['pg'];
            unset($param['pg']);
        }

        if ($param['mid'] == '' || $param['mid'] == '1') {
            $today['ac'] = 'cj';
            $today['h'] = 24;
            $today = http_build_query($today);
            $all['ac'] = 'cj';
            $all['h'] = '';
            $all = http_build_query($all);
            $strParam = http_build_query($param);
            $tree = GetMenu('category');
            foreach ($tree as $k=>$v){
                $level = $v['level']-1;
                if( $level > 1){
                    $tree[$k]['p'] = str_repeat("&nbsp;&nbsp;&nbsp;",$level).'|--';
                }else{
                    $tree[$k]['p']='';
                }
            }
            $model = new Model();
            $res = $model->vod($param);
            $bind_list = config('bind');

            foreach($res['type'] as $k=>$v){
                $key = $param['cjflag'] . '_' . $v['type_id'];
                $res['type'][$k]['isbind'] = 0;
                if(!array_key_exists($key,$bind_list)){
                    $bind_list[$key] = '';
                }
                $local_id = intval($bind_list[$key]);
                if( $local_id>0 ){
                    $res['type'][$k]['isbind'] = 1;
                    $res['type'][$k]['local_type_id'] = $local_id;
                    $type_name = $type_list[$local_id]['name'];
                    if(empty($type_name)){
                        $type_name = lang('unknown_type');
                    }
                    $res['type'][$k]['local_type_name'] = $type_name;
                }
            }
            View::assign('tree',$tree);
            View::assign('type',$res['type']);
            View::assign('param',$param);
            View::assign('today',$today);
            View::assign('all',$all);
            View::assign('url',$strParam);
            return view();
        } elseif ($param['mid'] == '2') {
            return $this->art($param);
        } elseif ($param['mid'] == '3') {
            return $this->actor($param);
        }
        elseif ($param['mid'] == '4') {
            return $this->role($param);
        }
        elseif ($param['mid'] == '5') {
            return $this->website($param);
        }
    }

    public function vod()
    {
        $param = request()->param();
        $today = $param;
        $all = $param;
        if(!array_key_exists('data',$param)){
            $param['data'] = [];
        }
        foreach ($param['data'] as $v){
            $param[$v['name']] = $v['value'];
        }
        unset($param['data']);

        if($param['ac'] != 'list'){
            $key = md5($_SERVER['HTTP_HOST']). '_'.'collect_break_vod';
            SetCaChe($key, url('collect/api').'?'. http_build_query($param) );
        }
        $model = new Model();
        $res = $model->vod($param);
        if($res['code']>1){
            $arr = array('code'=>1,'msg'=>$res['msg'],'count'=>0,'limit'=>'','data'=>0);
            echo json_encode($arr);
            die;
        }

        if($param['ac'] == 'list'){
            foreach($res['data'] as $k=>&$v){
                $v['vod_time'] = mac_day($v['vod_time'],'color');
            }
            $today['ac']='cj';
            $today['h']=24;
            $today=http_build_query($today);
            $all['ac']='cj';
            $all['h']='';
            $all=http_build_query($all);
            $arr = array('code'=>0,'msg'=>'ok','count'=>$res['page']['recordcount'],'today'=>$today,'all'=>$all,'limit'=>'','data'=>$res['data']);
            echo json_encode($arr);
            die;
        }
        $page_now = isset($param['page']) && strlen($param['page']) > 0 ? (int)$param['page'] : 1;
        mac_echo('<title>' . $page_now . '/' . (int)$res['page']['pagecount'] . ' collecting..</title>');
        mac_echo('<style type="text/css">body{font-size:12px;color: #333333;line-height:21px;}span{font-weight:bold;color:#FF0000}</style>');

    }

    public function collectData(){
        $param = request()->param();
        //https://cj.lziapi.com/api.php/provide/vod/at/xml/?ac=videolist&t=&pg=&h=24&ids=&wd=
        $model = new Model();
        $res = $model->vod($param);
        if($res['code']>1){
            $arr = array('code'=>1,'msg'=>$res['msg'],'count'=>0,'limit'=>'','data'=>0);
            echo json_encode($arr);
            die;
        }
        //开始采集数据
        $model->vod_data($param,$res );
    }

    public function select(){
        $data = request()->param();
        return view();
    }

    public function bind(){
        $param = request()->param();
        $col = $param['col'];
        $val = $param['val'];
        $ids = $param['ids'];
        if(!empty($col)){
            $config = config('bind');
            $config[$col] = intval($val);
            $config = array_filter($config);
            $data = [];
            $data['id'] = $col;
            $data['st'] = 0;
            $data['local_type_id'] = $val;
            $data['local_type_name'] = '';
            if(intval($val)>0){
                $data['st'] = 1;
                $type_list = GetCache('type_list');
                $data['local_type_name'] = $type_list[$val]['name'];
            }
            putConfig($config);
            /*$res = mac_arr2file( APP_PATH .'extra/bind.php', $config);
            if($res===false){
                return $this->error(lang('save_err'));
            }
            return $this->success(lang('save_ok'),null, $data);*/
            $msg = ['code'=>200,'msg'=>lang('success_message'),'data'=>$data];
            return json_encode($msg);
        }
        $msg = ['code'=>300,'msg'=>lang('update_error')];
        return json_encode($msg);
    }
}