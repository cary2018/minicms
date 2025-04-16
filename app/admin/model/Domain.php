<?php
/**
 * Created by PhpStorm.
 * CreateTime  : 2023/05/19 15:46
 * file name : Banner.php
 * User: asusa
 * Author: Hyy-Cary
 * Contact QQ  : 373889161(.)
 * email: 373889161@qq.com
 * WeChat: 18319021313
 */


namespace app\admin\model;

use think\Model;

class Domain extends Model
{
    public function saveData($data){
        $data['updateTime'] = time();
        if($data['id'] == ''){
            $data['createTime'] = time();
            unset($data['id']);
        }else{
            if(array_key_exists('web_logo',$data)){
                $img = FindTable('domain',[['id','=',$data['id']]]);
                if(file_exists($img['web_logo']) && $data['web_logo']!=$img['web_logo']){
                    unlink($img['web_logo']);
                }
                if(file_exists($img['web_ico']) && $data['web_ico']!=$img['web_ico']){
                    unlink($img['web_ico']);
                }
            }
        }
        SaveAt('domain',$data);
        $msg = ['code'=>200,'msg'=>lang('success_message'),'data'=>$data];
        return json_encode($msg,JSON_UNESCAPED_UNICODE);
    }
}