<?php
/**
 * Created by PhpStorm.
 * CreateTime  : 2023/04/20 14:46
 * file name : Category.php
 * User: asusa
 * Author: Hyy-Cary
 * Contact QQ  : 373889161(.)
 * email: 373889161@qq.com
 * WeChat: 18319021313
 */


namespace app\admin\model;


use think\facade\Db;
use think\Model;

class Category extends Model
{
    public function dataSave($data){
        $list = Db::name('category')->where('id',$data['pid'])->find();
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
            $old = Db::name('category')->where('id',$data['id'])->find();
        }
        SaveAt('category',$data);
        //更新下级分类 路径 path
        if(array_key_exists('id',$data)){
            if($old['pid']!=$data['pid']){
                $sun_arr = ArrTree('category',$data['id'],'pid','id');
                foreach ($sun_arr as $k){
                    $pat = Db::name('category')->where('id',$k['pid'])->find();
                    //echo $pat['id'];
                    Db::name('category')->where('id',$k['id'])->save(['path' => $pat['path'].'-'.$pat['id']]);;
                }
            }
        }
        $msg = ['code'=>200,'msg'=>lang('success_message')];
        return json_encode($msg);
    }
}