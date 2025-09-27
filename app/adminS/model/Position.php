<?php
/**
 * Created by PhpStorm.
 * CreateTime  : 2023/08/15 14:32
 * file name : Position.php
 * User: asusa
 * Author: Hyy-Cary
 * Contact QQ  : 373889161(.)
 * email: 373889161@qq.com
 * WeChat: 18319021313
 */


namespace app\admin\model;


use think\Model;

class Position extends Model
{
    public function saveData($data){

        if($data['id'] == ''){
            unset($data['id']);
        }
        SaveAt('position',$data);
        $msg = ['code'=>200,'msg'=>lang('success_message'),'data'=>$data];
        return json_encode($msg,JSON_UNESCAPED_UNICODE);
    }
}