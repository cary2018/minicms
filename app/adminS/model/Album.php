<?php
/**
 * Created by PhpStorm.
 * CreateTime  : 2023/05/23 14:56
 * file name : Album.php
 * User: asusa
 * Author: Hyy-Cary
 * Contact QQ  : 373889161(.)
 * email: 373889161@qq.com
 * WeChat: 18319021313
 */


namespace app\admin\model;


use think\Model;

class Album extends Model
{
    public function saveData($data){
        $data['createTime'] = time();
        if($data['id'] == ''){
            unset($data['id']);
        }else{
            if(array_key_exists('img',$data)){
                $img = FindTable('album',[['id','=',$data['id']]]);
                if(file_exists($img['img'])){
                    unlink($img['img']);
                }
                if(file_exists($img['thumbImg'])){
                    unlink($img['thumbImg']);
                }
            }
        }
        SaveAt('album',$data);
        $msg = ['code'=>200,'msg'=>lang('success_message'),'data'=>$data];
        return json_encode($msg,JSON_UNESCAPED_UNICODE);
    }
}