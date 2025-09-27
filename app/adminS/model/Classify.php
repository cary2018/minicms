<?php
/**
 * Created by PhpStorm.
 * CreateTime  : 2023/06/25 16:36
 * file name : Classify.php
 * User: asusa
 * Author: Hyy-Cary
 * Contact QQ  : 373889161(.)
 * email: 373889161@qq.com
 * WeChat: 18319021313
 */


namespace app\admin\model;


use think\facade\Db;
use think\Model;

class Classify extends Model
{
    public function dataSave($data){
        $list = Db::name('classify')->where('id',$data['pid'])->find();
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
            $old = Db::name('classify')->where('id',$data['id'])->find();
        }
        SaveAt('classify',$data);
        //更新下级分类 路径 path
        if(array_key_exists('id',$data)){
            if($old['pid']!=$data['pid']){
                $sun_arr = ArrTree('classify',$data['id'],'pid','id');
                foreach ($sun_arr as $k){
                    $pat = Db::name('classify')->where('id',$k['pid'])->find();
                    //echo $pat['id'];
                    Db::name('classify')->where('id',$k['id'])->save(['path' => $pat['path'].'-'.$pat['id']]);;
                }
            }
        }
        $msg = ['code'=>200,'msg'=>lang('success_message')];
        return json_encode($msg);
    }
}