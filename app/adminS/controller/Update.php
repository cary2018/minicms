<?php
/**
 * Created by PhpStorm.
 * CreateTime  : 2024/07/08 19:20
 * file name : Update.php
 * User: asusa
 * Author: Hyy-Cary（优）
 * Contact QQ  : 373889161(.)
 * email: 373889161@qq.com
 * WeChat: 18319021313
 */


namespace app\admin\controller;


use app\admin\BaseController;
use think\facade\Db;

class Update extends BaseController
{
    public function step1(){
        $path = root_path();
        $savePath = $path.'app/data/update/';
        $downFile = GetConfig('version','domain').'/'.GetConfig('version','updateFile');
        $down = DownloadFile($downFile,$savePath,'',1);
        echo lang('update_down');
        ob_flush();flush();
        sleep(1);
        if($down['code'] === 200){
            echo lang('update_down_complete');
            ob_flush();flush();
            sleep(1);
        }else{
            echo lang('update_down_fail').$downFile;
            die;
        }
        mini_jump('step2');
    }
    public function step2(){
        //解压覆盖目录
        $path = root_path();
        $savePath = $path.'app/data/update/';
        $zip = $savePath.GetConfig('version','updateFile');
        if(file_exists($zip)){
            echo lang('update_find_wrap');
            $res = DealZip($zip,$path);
            ob_flush();flush();
            sleep(1);
            if($res['code']==200){
                echo lang('update_handle_wrap');
                @unlink($zip);
            }else{
                echo lang('decompression_error').':'.$zip;
                die;
            }
            ob_flush();flush();
            sleep(1);
        }
        mini_jump('step3');
    }
    public function step3(){
        $path = root_path();
        $savePath = $path.'app/data/update/';
        $sqlFile = $savePath.GetConfig('version','updateSql');
        if(file_exists($sqlFile)){
            echo lang('update_find_database');
            sleep(1);
            $info = pathinfo($sqlFile);
            $sql = $info['extension']=='sql'?file_get_contents($sqlFile):read_gz($sqlFile);
            $sql_list = mini_parse_sql($sql,0,['cy_'=>GetConfig('database','connections.mysql.prefix')]);
            echo lang('update_handle_database');
            sleep(1);
            if ($sql_list) {
                $db_connect = Db::connect();
                $sql_list = array_filter($sql_list);
                foreach ($sql_list as $v) {
                    try {
                        $db_connect->execute($v);
                        echo lang('update_handle_success').$v.'<br>';
                    } catch(\Exception $e) {
                        //echo lang('update_handle_fail').$e->getMessage().'<br>';
                    }
                }
            }
            echo lang('update_handle_complete');
            @unlink($sqlFile);
            ob_flush();flush();
            sleep(1);
        }
        echo lang('update_system_complete');
    }
}