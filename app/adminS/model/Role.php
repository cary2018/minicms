<?php
/**
 * Created by PhpStorm.
 * CreateTime  : 2023/03/24 18:08
 * file name : Role.php
 * User: asusa
 * Author: Hyy-Cary
 * Contact QQ  : 373889161(.)
 * email: 373889161@qq.com
 * WeChat: 18319021313
 */


namespace app\admin\model;


use think\exception\ValidateException;
use think\Model;
use app\admin\validate\Role as RoleValidate;

class Role extends Model
{
    public function saveData($data){
        if(array_key_exists('auth',$data)){
            $data['auth'] = json_encode($data['auth']);
        }else{
            $data['auth'] = '';
        }
        if(array_key_exists('menuAuth',$data)){
            $data['menuAuth'] = json_encode($data['menuAuth']);
        }else{
            $data['menuAuth'] = '';
        }
        try{
            $data['updateTime'] = time();
            if($data['id'] == ''){
                $data['createTime'] = time();
                unset($data['id']);
            }
            validate(RoleValidate::class)->check($data);
            SaveAt('role',$data);
            $msg = ['code'=>200,'msg'=>lang('success_message'),'data'=>$data];
        }catch(ValidateException $e){
            $msg = ['code'=>300,'msg'=>$e->getError()];
        }
        return json_encode($msg,JSON_UNESCAPED_UNICODE);
    }
}