<?php
/**
 * Created by PhpStorm.
 * CreateTime  : 2024/06/22 20:22
 * file name : Database.php
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

class Database extends BaseController
{
    public function index(){
        $dir = backupDatabasePath();
        if(!file_exists($dir)){
            mkdir($dir,0755,true);
        }
        $filteredItems = dirList($dir);
        View::assign('filesql',$filteredItems);
        return View();
    }

    public function dataList(){
        $list = Db::query('show table status');
        foreach ($list as &$item){
            $item['Data_length'] = toSize($item['Data_length']);
            $item['Data_free'] = toSize($item['Data_free']);
        }
        $arr = array('code'=>200,'msg'=>'ok','count'=>count($list),'data'=>$list);
        echo json_encode($arr);
    }

    /**
     * 导出备份数据
     */
    public function export(){
        $par = request()->param();
        $arr = ['code'=>300,'msg'=>lang('database_select_table')];
        $name = date('Ymd-His');
        if($par){
            foreach ($par['data'] as $v){
                $arr = backup($v,$name);
            }
        }
        echo json_encode($arr);
    }

    /**
     * 优化数据表
     */
    public function optimize(){
        $par = request()->param();
        $arr = ['code'=>300,'msg'=>lang('database_select_table_error')];
        $res = '';
        if($par){
            if(is_array($par['data'])){
                foreach ($par['data'] as $v){
                    $res = Db::query("OPTIMIZE TABLE `{$v}`");
                }
            }else{
                $res = Db::query("OPTIMIZE TABLE `{$par['data']}`");
            }
            if($res){
                $arr = ['code'=>200,'msg'=>lang('complete')];
            }
        }
        echo json_encode($arr);
    }

    /**
     *修复数据表
     */
    public function repair(){
        $par = request()->param();
        $arr = ['code'=>300,'msg'=>lang('database_select_table_error')];
        $res = '';
        if($par){
            if(is_array($par['data'])){
                foreach ($par['data'] as $v){
                    $res = Db::query("REPAIR TABLE `{$v}`");
                }
            }else{
                $res = Db::query("REPAIR TABLE `{$par['data']}`");
            }
            if($res){
                $arr = ['code'=>200,'msg'=>lang('complete')];
            }
        }
        echo json_encode($arr);
    }

    /**
     * 还原数据
     */
    public function restore(){
        $data = request()->param();
        $path = backupDatabasePath().$data['data'];
        $info = pathinfo($path);
        $sql = $info['extension']=='sql'?file_get_contents($path):read_gz($path);
        $sql_list = mini_parse_sql($sql,0,['cy_'=>GetConfig('database','connections.mysql.prefix')]);
        $res = redSql($sql_list);
        echo json_encode($res);
    }

    /**
     * 删除备份数据
     */
    public function delAll(){
        $data = request()->param();
        $res = '';
        if($data){
            if(is_array($data['data'])){
                foreach ($data['data'] as $v){
                    $path = backupDatabasePath().$v;
                    if(file_exists($path)){
                        unlink($path);
                    }
                }
            }else{
                $path = backupDatabasePath().$data['data'];
                if(file_exists($path)){
                    unlink($path);
                }
            }
        }
        $arr = ['code'=>200,'msg'=>lang('complete')];
        echo json_encode($arr);
    }

    /**
     * @return \think\response\View
     * 执行sql页面
     */
    public function sql(){
        return View();
    }
    /**
     * @return false|string
     * 执行sql语句
     */
    public function saveAt(){
        $sql = request()->param('sql');
        if($sql){
            try{
                Db::query($sql);
            }catch (\Exception $e){
                return json_encode(['code'=>300,'msg'=>$e->getMessage()]);
            }
        }
        return json_encode(['code'=>200,'msg'=>'执行成功！','sql'=>$sql]);
    }

    /**
     * @return \think\response\View
     * 批量替换页面
     */
    public function rep(){
        $table = Db::query('show table status');
        View::assign('table',$table);
        return View();
    }

    /**
     * @return false|string
     * 获取数据表信息
     */
    public function columns(){
        $data = request()->param();
        $columns = Db::query('show columns from '.$data['table']);
        return json_encode(['code'=>200,'msg'=>lang('get_data_success'),'data'=>$columns]);
    }

    /**
     * @return false|string
     * 执行批量替换
     */
    public function repcon(){
        $data = request()->param();
        if($data['field'] && $data['old'] && $data['new']){
            try{
                $sql = "UPDATE ".$data['table']." set ".$data['field']."=Replace(".$data['field'].",'".$data['old']."','".$data['new']."') where 1=1 ". $data['where'];
                Db::query($sql);
            }catch(\Exception $e){
                return json_encode(['code'=>300,'msg'=>$e->getMessage()]);
            }
        }
        return json_encode(['code'=>200,'msg'=>lang('execute_success')]);
    }

}