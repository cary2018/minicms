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

class Banner extends Model
{
    public function saveData($data){
        $data['updateTime'] = time();
        if($data['id'] == ''){
            $data['createTime'] = time();
            unset($data['id']);
        }else{
            if(array_key_exists('img',$data)){
                $img = FindTable('banner',[['id','=',$data['id']]]);
                if(file_exists($img['img']) && $data['img']!=$img['img']){
                    unlink($img['img']);
                }
                if(file_exists($img['thumbImg']) && $data['thumbImg']!=$img['thumbImg']){
                    unlink($img['thumbImg']);
                }
            }
        }
        SaveAt('banner',$data);
        $msg = ['code'=>200,'msg'=>lang('success_message'),'data'=>$data];
        return json_encode($msg,JSON_UNESCAPED_UNICODE);
    }
}