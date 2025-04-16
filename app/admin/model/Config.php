<?php
/**
 * Created by PhpStorm.
 * CreateTime  : 2023/04/04 14:29
 * file name : System.php
 * User: asusa
 * Author: Hyy-Cary
 * Contact QQ  : 373889161(.)
 * email: 373889161@qq.com
 * WeChat: 18319021313
 */


namespace app\admin\model;


use think\facade\Db;
use think\Model;

class Config extends Model
{
    public function saveData($data){
        $list = Db::name('config')->where('id',$data['pid'])->find();
        $find = FindTable('config',[['sys_variable','=',$data['sys_variable']],['id','<>',$data['id']]]);
        if($find){
            $msg = ['code'=>300,'msg'=>lang('variableNameError'),'data'=>$data];
            return json_encode($msg,JSON_UNESCAPED_UNICODE);
        }
        if($list){
            $data['path'] = $list['path'].'-'.$data['pid'];
        }else{
            $data['path'] = '-';
        }
        if($data['id'] == ''){
            unset($data['id']);
        }else{
            $lv = explode('-',$data['path']);
            if(in_array($data['id'],$lv)){
                $msg = ['code'=>300,'msg'=>lang('error_menu')];
                return json_encode($msg);
            }
            if($data['sys_type']=='file'){
                $img = FindTable('config',[['id','=',$data['id']]]);
                if(file_exists($img['sys_content']) && $data['sys_content']!=$img['sys_content']){
                    unlink($img['sys_content']);
                }
            }
        }
        Db::name('config')->save($data);
        $msg = ['code'=>200,'msg'=>lang('success_message')];
        return json_encode($msg);
    }
}