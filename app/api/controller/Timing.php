<?php
/**
 * Created by PhpStorm.
 * CreateTime  : 2025/05/09 17:13
 * file name : Timing.php
 * User: asusa
 * Author: Hyy-Cary（优）
 * Contact QQ  : 373889161(.)
 * email: 373889161@qq.com
 * WeChat: 18319021313
 */


namespace app\api\controller;


use app\api\BaseController;
use app\model\Collect;

class Timing extends BaseController
{
    public function index(){
        $key = request()->param('key');
        $list = config('timing');
        if (!array_key_exists($key, $list)) {
            http_response_code(404); // 明确HTTP状态码
            exit('任务不存在');
        }
        $data = $list[$key];
        if (!is_array($data) || !isset($data['status'])) {
            http_response_code(500);
            exit('任务数据异常');
        }
        if ($data['status'] == 1){
            $oldweek= date('w',$data['updateTime']);
            $oldhours= date('H',$data['updateTime']);
            $curweek= date('w',time()) ;
            $curhours= date("H",time());
            //echo '旧：'.$oldweek.'-'.$oldhours;
            //echo '新：'.$curweek.'-'.$curhours;
            $model = new Collect();
            parse_str($data['param'],$param);

            $res = $model->vod($param);
            $model->vod_data($param,$res);
            echo '<pre>';
            print_r($param);
            print_r($data);
        }else{
            echo '任务已停止';
        }
    }
}