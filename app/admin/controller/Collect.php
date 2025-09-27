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
            /*'t'=>'',
            'ids'=>'', //
            'wd'=>'',
            'page'=>0,*/
        ];

        // 初始化跳转配置
        $types = [
            'jump' => ['ac' => 'list'],
            'today' => ['ac' => 'cj', 'h' => 24],
            'week' => ['ac' => 'cj', 'h' => 168],
            'cjAll' => ['ac' => 'cj'],
        ];
        //ac=cj&h=24&cjflag=dyttzy&cjurl=http%3A%2F%2Fcaiji.dyttzyapi.com%2Fapi.php%2Fprovide%2Fvod%2Ffrom%2Fdyttm3u8%2Fat%2Fjson%2F
        $pinyin = new Pinyin();
        foreach ($list as &$v) {
            // 公共参数
            $commonParams = [
                'cjflag' => $pinyin->abbr($v['collect_name']),
                'cjurl' => $v['collect_url'],
                /*'type' => $v['collect_type'],
                'mid' => $v['collect_mid'],
                'opt' => $v['collect_opt'],
                'sync_pic_opt' => $v['collect_sync_pic_opt'],
                'filter' => $v['collect_filter'],
                'filter_from' => $v['collect_filter_from'],
                'filter_year' => $v['collect_filter_year'],
                'param' => base64_encode($v['collect_param'])*/
            ];

            // 为每个类型合并公共参数
            foreach ($types as $key => $params) {
                // 如果 h 值为空，使用基准参数中的默认值
                if($key=='week'){
                    $commonParams['cjflag'] = $commonParams['cjflag'].'week';
                }
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
        $name = '';
        if(array_key_exists('name',$param)){
            $name = $param['name'];
            unset($param['name']);
        }
        $today = $param;
        $all = $param;
        //分类
        $type_list = VodMen();

        if (!empty($param['pg'])) {
            $param['page'] = $param['pg'];
            unset($param['pg']);
        }
        $keys = ['mid'];
        foreach ($keys as $key) {
            if (!array_key_exists($key, $param)) {
                $param[$key] = '';
            }
        }
        if ($param['mid'] == '' || $param['mid'] == '1') {
            $today['ac'] = 'cj';
            $today['h'] = 24;
            $today = http_build_query($today);
            $all['ac'] = 'cj';
            $all['h'] = '';
            $all = http_build_query($all);
            $strParam = http_build_query($param);
            $tree = GetMenu('category',[['type','=',1]]);
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
            if($res['code'] == 1001){
                return $res['msg'];
            }
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
                    $type_name = '';
                    if(array_key_exists($local_id,$type_list)){
                        $type_name = $type_list[$local_id]['name'];
                    }
                    if(empty($type_name)){
                        $type_name = lang('unknown_type');
                    }
                    $res['type'][$k]['local_type_name'] = $type_name;
                }
            }
            $ps = explode(',',$res['data'][0]['vod_play_from']);
            $playlist = config('vodplay');
            foreach ($ps as $k=>$v){
                $playlist[$v]=[
                    'from'=>$v,
                    'show'=>$name.$k,
                    'sort'=>100,
                    'status'=>1,
                    'ps'=>0,
                    'parse'=>'',
                    'des'=>'支持手机电脑在线播放',
                    'tip'=>'无需安装任何插件',
                ];
            }
            $sort=[];
            foreach ($playlist as $k=>&$v){
                $sort[] = $v['sort'];
            }
            array_multisort($sort, SORT_DESC, SORT_FLAG_CASE , $playlist);
            putConfig($playlist,'vodplay.php');

            View::assign('tree',$tree);
            View::assign('type',$res['type']);
            View::assign('param',$param);
            View::assign('today',$today);
            View::assign('all',$all);
            View::assign('url',$strParam);
            View::assign('page',$res['page']['page']);
            View::assign('limit',$res['page']['pagesize']);
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

    public function bindType(){
        $param = request()->param();
        $tree = GetMenu('category',[['type','=',1]]);
        foreach ($tree as $k=>$v){
            $level = $v['level']-1;
            if( $level > 1){
                $tree[$k]['p'] = str_repeat("&nbsp;&nbsp;&nbsp;",$level).'|--';
            }else{
                $tree[$k]['p']='';
            }
        }
        //视频分类
        $type_list = VodMen();
        $model = new Model();
        $res = $model->vod($param);
        $config = config('bind');
        $table = AllTable('category',[['type','=',1]]);
        // 创建name到id的映射
        $categoryMap = array_column($table ?: [], 'id', 'name');
        // 处理绑定
        foreach ($res['type'] as $val) {
            if (empty($val['type_name']) || empty($val['type_id'])) {
                continue; // 跳过无效数据
            }
            $configKey = ($param['cjflag'] ?? 'default') . '_' . $val['type_id'];
            if(!array_key_exists($configKey,$config)){
                $config[$configKey] = $categoryMap[$val['type_name']] ?? null;
            }
        }
        $config = array_filter($config);
        putConfig($config);

        //$bind_list = config('bind');
        foreach($res['type'] as $k=>$v){
            $key = $param['cjflag'] . '_' . $v['type_id'];
            $res['type'][$k]['isbind'] = 0;
            if(!array_key_exists($key,$config)){
                $config[$key] = '';
            }

            $local_id = intval($config[$key]);
            if( $local_id>0 ){
                $res['type'][$k]['isbind'] = 1;
                $res['type'][$k]['local_type_id'] = $local_id;
                $type_name = '';
                if(array_key_exists($local_id,$type_list)){
                    $type_name = $type_list[$local_id]['name'];
                }
                if(empty($type_name)){
                    $type_name = lang('unknown_type');
                }
                $res['type'][$k]['local_type_name'] = $type_name;
            }
        }

        View::assign('tree',$tree);
        View::assign('type',$res['type']);
        View::assign('param',$param);
        return view();
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

    public function timing(){
        $data = request()->param();
        $name = $data['name'];
        unset($data['name']);
        $param = http_build_query($data);
        $arr = [
            'name'=>$data['cjflag'],
            'remark'=>$name,
            'status'=>1,
            'type'=>'collect',
            'param'=>$param,
        ];
        $weeks = array_merge(
            range(1,6), // 周一到周六
            [0]         // 周日
        );
        $arr['weeks'] = json_encode($weeks);
        $hours = array_map(
            fn($h) => sprintf("%02d", $h),
            range(0,23)
        );
        $arr['hours'] = json_encode($hours);
        $result = FindTable('timing',[['name','=',$data['cjflag']]]);
        if(empty($result)){
            SaveAt('timing',$arr);
        }else{
            $arr['id'] = $result['id'];
            SaveAt('timing',$arr);
        }
        $newArray = [];
        $originalArray = AllTable('timing');
        foreach ($originalArray as $item) {
            $key = md5($item['name']);       // 获取当前元素的 name 值
            $newArray[$key] = $item;    // 以 name 为键，存储整个子数组
        }
        putConfig($newArray,'timing.php');
    }

    public function bind(){
        $param = request()->param();
        $col = $param['col'];
        $val = $param['val'];
        //$ids = $param['ids'];
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
                $type_list = GetCache('VodMen_1');
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