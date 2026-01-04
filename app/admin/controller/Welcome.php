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
        $todayData = FindTable('count_today');
        $countIp = CountData([]);
        $feedCount = CountTable('feedback',[['status','=',1]]);

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
        View::assign('today',$todayData);
        View::assign('todayIp',$countIp);
        View::assign('article',$article);
        View::assign('mysql',$mysql);
        View::assign('update',$update);
        View::assign('updatesql',$updateSql);
        return View();
    }
    function weekdata(){
        // 使用示例
        $weekTimestamps = getWeekDayTimestamps();
        $type = 'line';
        //查询今天的访问数据
        $todayData = FindTable('count_today');
        $countIp = CountData([]);
        if(!$todayData){
            $todayData = [
                'pv'=>0,
                'uv'=>0,
                'visitor'=>0,
                'spider'=>0,
                'unknown'=>0,
            ];
        }
        // 定义指标配置
        $metrics = [
            'pv' => ['name' => '浏览量(pv)', 'countData' => $todayData['pv']],
            'uv' => ['name' => '访客量(uv)', 'countData' => $todayData['uv']],
            'ip' => ['name' => 'IP量', 'countData' => $countIp],
            'visitor' => ['name' => '访客', 'countData' => $todayData['visitor']],
            'spider' => ['name' => '蜘蛛', 'countData' => $todayData['spider']],
            'unknown' => ['name' => '未知', 'countData' => $todayData['unknown']],
        ];

        // 初始化结果数组
        $result = array_fill_keys(array_keys($metrics), []);

        foreach ($weekTimestamps as $day) {
            $dateKey = $day['date'];
            $isToday = ($dateKey == date('Y-m-d'));

            // 检查是否有缓存（今天除外）
            if (!$isToday && ($cached = GetCache($dateKey.'metrics'))) {
                foreach ($metrics as $key => $_) {
                    //读取缓存数据
                    $result[$key][] = $cached[$key] ?? 0;
                }
                continue;
            }

            // 收集统计数据
            $stats = [];
            foreach ($metrics as $key => $config) {
                //读取今日数据
                $stats[$key] = $metrics[$key]['countData'];
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
}

