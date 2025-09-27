<?php
/**
 * Created by PhpStorm.
 * CreateTime  : 2023/08/15 14:45
 * file name : Department.php
 * User: asusa
 * Author: Hyy-Cary
 * Contact QQ  : 373889161(.)
 * email: 373889161@qq.com
 * WeChat: 18319021313
 */


namespace app\admin\model;


use think\facade\Db;
use think\Model;

class Department extends Model
{
    public function dataSave($data){
        $list = Db::name('department')->where('id',$data['pid'])->find();
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
            $old = Db::name('department')->where('id',$data['id'])->find();
        }
        SaveAt('department',$data);
        //更新下级分类 路径 path
        if(array_key_exists('id',$data)){
            if($old['pid']!=$data['pid']){
                $sun_arr = ArrTree('department',$data['id'],'pid','id');
                foreach ($sun_arr as $k){
                    $pat = Db::name('department')->where('id',$k['pid'])->find();
                    //echo $pat['id'];
                    Db::name('department')->where('id',$k['id'])->save(['path' => $pat['path'].'-'.$pat['id']]);;
                }
            }
        }
        $msg = ['code'=>200,'msg'=>lang('success_message')];
        return json_encode($msg);
    }
}