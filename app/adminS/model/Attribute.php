<?php
/**
 * Created by PhpStorm.
 * CreateTime  : 2023/04/20 23:31
 * file name : Attribute.php
 * User: asusa
 * Author: Hyy-Cary
 * Contact QQ  : 373889161(.)
 * email: 373889161@qq.com
 * WeChat: 18319021313
 */


namespace app\admin\model;


use think\Model;

class Attribute extends Model
{
    public function dataSave($data){
        if($data['id'] == ''){
            unset($data['id']);
        }
        SaveAt('attribute',$data);
        $attr = AllTable('attribute',[['status','=',1]],['orderSort','id'=>'desc']);
        SetCaChe('attribute',$attr);
        $msg = ['code'=>200,'msg'=>lang('success_message')];
        return json_encode($msg,JSON_UNESCAPED_UNICODE);
    }
}