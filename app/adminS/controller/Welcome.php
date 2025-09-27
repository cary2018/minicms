<?php
/**
 * Created by PhpStorm.
 * CreateTime  : 2023/03/27 00:01
 * file name : Welcome.php
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

class Welcome extends BaseController
{
    public function index(){
        $today_start=mktime(0,0,0,date('m'),date('d'),date('Y'));
        $today_end=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
        $where = [['createTime','between',[$today_start,$today_end]]];
        $feedCount = CountTable('feedback',[['status','=',1]]);
        $todayCount = CountTable('visit',$where);
        $todayGuv = CountTable('visit',$where,'','guv');
        $todayIp = CountTable('visit',$where,'','ip');
        $article =  CountTable('article');
        $sql = 'SELECT VERSION() AS version';
        $version = Db::query($sql);
        $mysql = $version[0]['version'];
        $arr = [
            'v'=>GetConfig('version','code'),
            'h'=>GetConfig('version','host'),
            'ip'=>get_client_ip(),
        ];
        $update = FCurl_post(GetConfig('version','domain'),$arr);
        if($update['response_code'] == 200){
            $update = json_decode($update['output'],true);
        }else{
            $update = ['code'=>'400','msg'=>lang('update_detection_failed')];
        }
        $path = root_path();
        $savePath = $path.'app/data/update/';
        $sqlFile = $savePath.GetConfig('version','updateSql');
        $updateSql = ['code'=>'300','msg'=>lang('update_sql_failed')];
        if(file_exists($sqlFile)){
            $updateSql = ['code'=>'200','msg'=>lang('update_sql_success')];
        }
        $wekdata = $this->weekdata();
        View::assign('wekData',$wekdata);
        View::assign('feed',$feedCount);
        View::assign('today',$todayCount);
        View::assign('todayGuv',$todayGuv);
        View::assign('todayIp',$todayIp);
        View::assign('article',$article);
        View::assign('mysql',$mysql);
        View::assign('update',$update);
        View::assign('updatesql',$updateSql);
        return View();
    }
    function weekdata(){
        // 使用示例
        $weekTimestamps = $this->getWeekDayTimestamps();
        $type = 'line';

        // 定义指标配置
        $metrics = [
            'to' => ['name' => '浏览量(pv)', 'where' => []],
            'vis' => ['name' => '访客量(uv)', 'field' => 'guv'],
            'ip' => ['name' => 'IP量', 'field' => 'ip'],
            'us' => ['name' => '访客', 'where' => [['clientType', 'like', '%访客%']]],
            'bo' => ['name' => '蜘蛛', 'where' => [['clientType', 'like', '%蜘蛛%']]],
            'un' => ['name' => '未知', 'where' => [['clientType', 'like', '%未知%']]]
        ];

        // 初始化结果数组
        $result = array_fill_keys(array_keys($metrics), []);

        foreach ($weekTimestamps as $day) {
            $dateKey = $day['date'];
            $isToday = ($dateKey == date('Y-m-d'));

            // 检查是否有缓存（今天除外）
            if (!$isToday && ($cached = GetCache($dateKey.'metrics'))) {
                foreach ($metrics as $key => $_) {
                    $result[$key][] = $cached[$key] ?? 0;
                }
                continue;
            }

            // 基础查询条件
            $baseWhere = [['createTime', 'between', [$day['start'], $day['end']]]];

            // 收集统计数据
            $stats = [];
            foreach ($metrics as $key => $config) {
                $where = $baseWhere;
                if (!empty($config['where'])) {
                    $where = array_merge($where, $config['where']);
                }

                $stats[$key] = CountTable('visit', $where, '', $config['field'] ?? '');
            }

            // 如果不是今天，设置缓存
            if (!$isToday) {
                SetCaChe($dateKey.'metrics', $stats, 7);
            }

            // 填充结果
            foreach ($metrics as $key => $_) {
                $result[$key][] = $stats[$key];
            }
        }

        // 构建最终输出格式
        $output = [];
        foreach ($metrics as $key => $config) {
            $output[] = [
                'name' => $config['name'],
                'type' => $type,
                'data' => $result[$key]
            ];
        }

        //return $output;
        return json_encode($output,JSON_UNESCAPED_UNICODE);
    }
    function getWeekDayTimestamps() {
        $weekDays = [];
        $current = strtotime('monday this week');
        $today = date('w');
        if($today == 0){
            $today = 7;
        }
        for ($i = 0; $i < $today; $i++) {
            $dayStart = strtotime(date('Y-m-d 00:00:00', $current));
            $dayEnd = strtotime(date('Y-m-d 23:59:59', $current));

            $weekDays[] = [
                'day' => date('l', $current),
                'date' => date('Y-m-d', $current),
                'start' => $dayStart,
                'end' => $dayEnd
            ];

            $current = strtotime('+1 day', $current);
        }
        return $weekDays;
    }
}

