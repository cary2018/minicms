<?php
/**
 * Created by PhpStorm.
 * CreateTime  : 2023/03/22 17:39
 * file name : Admin.php
 * User: asusa
 * Author: Hyy-Cary
 * Contact QQ  : 373889161(.)
 * email: 373889161@qq.com
 * WeChat: 18319021313
 */


namespace app\admin\model;


use think\exception\ValidateException;
use think\Model;
use app\admin\validate\Admin as AdminValidate;
class Admin extends Model
{
    public function saveData($data){
        $se = GetSe('admin');
        if($se['isAdmin'] !==1 && $se['id']!==$data['id']){
            $msg = ['code'=>300,'msg'=>lang('errorUser')];
            return json_encode($msg,JSON_UNESCAPED_UNICODE);
        }
        $find = FindTable('admin',[['username','=',$data['username']],['id','<>',$data['id']]]);
        if($find){
            $msg = ['code'=>300,'msg'=>lang('errorName')];
            return json_encode($msg,JSON_UNESCAPED_UNICODE);
        }
        try{
            if(isset($data['entry_date']) && $data['entry_date']){
                $data['entry_date'] = strtotime($data['entry_date']);
            }
            if(isset($data['depart_date']) && $data['depart_date']){
                $data['depart_date'] = strtotime($data['depart_date']);
            }
            if(array_key_exists('role',$data)){
                $data['role'] = json_encode($data['role']);
            }else{
                $data['role'] = '';
            }
            $data['updateTime'] = time();
            if($data['id'] == ''){
                validate(AdminValidate::class)->scene('add')->check($data);
                $data['path'] = $se['path'].'-'.$se['id'];
                $data['pid'] = $se['id'];
                $data['createTime'] = time();
                unset($data['id']);
            }else{
                validate(AdminValidate::class)->scene('edit')->check($data);
                if(array_key_exists('headImg',$data)){
                    $img = FindTable('admin',[['id','=',$data['id']]]);
                    if(file_exists($img['headImg'])){
                        unlink($img['headImg']);
                    }
                    if(file_exists($img['thumbImg'])){
                        unlink($img['thumbImg']);
                    }
                }
            }
            if($data['password']){
                $data['password'] = PasswordSet($data['password']);
            }else{
                unset($data['password']);
            }
            unset($data['repassword']);
            SaveAt('admin',$data);
            $msg = ['code'=>200,'msg'=>lang('success_message'),'data'=>$data];
        }catch (ValidateException $e){
            $msg = ['code'=>300,'msg'=>$e->getError()];
        }
        return json_encode($msg,JSON_UNESCAPED_UNICODE);
    }

    public function saveInfo($data){
        $se = GetSe('admin');
        if($se['id'] != $data['id']){
            $msg = ['code'=>300,'msg'=>lang('errorUser')];
            return json_encode($msg,JSON_UNESCAPED_UNICODE);
        }
        $find = FindTable('admin',[['username','=',$data['username']],['id','<>',$data['id']]]);
        if($find){
            $msg = ['code'=>300,'msg'=>lang('errorName')];
            return json_encode($msg,JSON_UNESCAPED_UNICODE);
        }
        try{
            validate(AdminValidate::class)->scene('edit')->check($data);
            if(array_key_exists('headImg',$data)){
                $img = FindTable('admin',[['id','=',$data['id']]]);
                if(file_exists($img['headImg'])){
                    unlink($img['headImg']);
                }
                if(file_exists($img['thumbImg'])){
                    unlink($img['thumbImg']);
                }
            }
            if($data['password']){
                $data['password'] = PasswordSet($data['password']);
            }else{
                unset($data['password']);
            }
            unset($data['repassword']);
            SaveAt('admin',$data);
            $msg = ['code'=>200,'msg'=>lang('success_message'),'data'=>$data];
        }catch (ValidateException $e){
            $msg = ['code'=>300,'msg'=>$e->getError()];
        }
        return json_encode($msg,JSON_UNESCAPED_UNICODE);
    }
}