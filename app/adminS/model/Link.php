<?php
/**
 * Created by PhpStorm.
 * CreateTime  : 2023/05/19 20:13
 * file name : Link.php
 * User: asusa
 * Author: Hyy-Cary
 * Contact QQ  : 373889161(.)
 * email: 373889161@qq.com
 * WeChat: 18319021313
 */


namespace app\admin\model;


use think\Model;

class Link extends Model
{
    public function saveData($data){
        $data['updateTime'] = time();
        if($data['id'] == ''){
            $data['createTime'] = time();
            unset($data['id']);
        }else{
            if(array_key_exists('logo',$data)){
                $img = FindTable('link',[['id','=',$data['id']]]);
                if(file_exists($img['logo'])){
                    unlink($img['logo']);
                }
            }
        }
        SaveAt('link',$data);
        $msg = ['code'=>200,'msg'=>lang('success_message'),'data'=>$data];
        return json_encode($msg,JSON_UNESCAPED_UNICODE);
    }
}