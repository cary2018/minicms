<?php
declare (strict_types = 1);

namespace app\index\middleware;

use think\facade\Config;
use think\facade\Db;

class VisitLog
{
    /**
     * 处理请求
     *
     * @param \think\Request $request
     * @param \Closure       $next
     * @return Response
     */
    public function handle($request, \Closure $next)
    {
        $clientIp = get_client_ip();
        $url = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        $ip = new \Ip2Region();
        $region = $ip->btreeSearch($clientIp);
        if(!$region){
            $region = '';
        }
        if(isset($_SERVER['HTTP_REFERER'])){
            $from = $_SERVER['HTTP_REFERER'];
        }else{
            $from = '';
        }
        $today = date('Ymd',time());
        $oldTime = GetCache('clientExpTimes');
        $uv = md5($today.uniqid().time());
        $guv = GetCk('guvs');
        $updateData = [
            'total' => Db::raw('total+1'),
            'pv' => Db::raw('pv+1'),
        ];
        if($oldTime != $today || $guv == ''){
            SetCaChe('clientExpTimes',$today,2);
            $updateData['uv'] = Db::raw('uv+1');
            SetCk('guvs',$uv,86400);
        }
        if(!$oldTime){
            $oldTime = $today;
        }

        if(!$guv){
            //防止cookie初次访问未生效
            $guv = $uv;
        }
        $client = ClientType();
        $data = array(
            'clientType'=>$client,
            'ip'=>$clientIp,
            'guv'=>$guv,
            'from_url'=>$from,
            'to_url'=>$url,
            'region' => $region['region'],
            'clientInfo'=>lang('visit_system').GetOs().'<br>'.GetBrowser().'<br>'.GetLang().'<br>HTTP_ACCEPT：'.$_SERVER['HTTP_ACCEPT'].'<br>HTTP_USER_AGENT：'.$_SERVER['HTTP_USER_AGENT'],
            'createTime'=>time()
        );
        SaveAt('visit',$data);

        $days = FindTable('count_today');
        $sp = 0;
        $vi = 0;
        $un = 0;
        if (stripos($client, '蜘蛛') !== false) {
            $sp = 1;
            $updateData['spider'] = Db::raw('spider+1');
        }
        if ($client == '访客') {
            $vi = 1;
            $updateData['visitor'] = Db::raw('visitor+1');
        }
        if ($client == '未知') {
            $un = 1;
            $updateData['unknown'] = Db::raw('unknown+1');
        }
        //更新访问数据
        Db::name('count_today')->where('id', 1)->update($updateData);

        if($days && $oldTime !== $today){
            // 收集统计数据
            $stats = [
                'pv'=>$days['pv'],
                'uv'=>$days['uv'],
                'ip'=>CountData([]),
                'visitor'=>$days['visitor'],
                'spider'=>$days['spider'],
                'unknown'=>$days['unknown'],
            ];
            //设置缓存
            SetCaChe($oldTime.'metrics', $stats, 7);
        }

        // 初始化或重置今日统计数据
        if (!$days || $oldTime !== $today) {
            $log = [
                'ip'=>$clientIp,
                '数据'=>$days,
                '缓存时间'=>$oldTime,
                '当前时间'=>$today,
                '详细时间'=>date('Y-m-d H:i:s'),
            ];
            file_put_contents('vLog,txt',var_export($log,true),FILE_APPEND | LOCK_EX);
            $todayData = [
                'pv' => 1,
                'uv' => 1,
                'unknown' => (int)$un,
                'spider' => (int)$sp,
                'visitor' => (int)$vi,
            ];
            if(!$days){
                $todayData['total'] = CountTable('visit');
            }
            // 如果是重置而不是首次创建，保留ID
            if ($days && $oldTime != $today) {
                $todayData['id'] = $days['id'];
            }
            SaveAt('count_today', $todayData);
            //截断表数据（清空表数据）
            $prefix = Config::get('database.connections.mysql.prefix');
            //清空今日统计ip表
            Db::query('truncate table '.$prefix.'count_ip');
        }
        //插入ip
        SaveAt('count_ip',['ip'=>$clientIp]);

        //前置中间件
        return $next($request);
    }
}