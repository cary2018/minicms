<?php
/**
 * Created by PhpStorm.
 * CreateTime  : 2023/10/11 16:53
 * file name : Callback.php
 * User: asusa
 * Author: Hyy-Cary
 * Contact QQ  : 373889161(.)
 * email: 373889161@qq.com
 * WeChat: 18319021313
 */


namespace app\api\controller;


use app\api\BaseController;

class Callback extends BaseController
{
    public function index(){
        $sign_key = Cfg('sign_key');
        if($sign_key){
            $data = request()->param();
            $data_sign = $data['sign'];
            unset($data['sign']);
            $sign = signMd5($data,$sign_key);
            if($sign != $data_sign){
                return '签名错误！';
            }
            //$pid = [1=>23,11=>15,2=>16,8=>17,9=>18,10=>5];
            $path = '/download_img/'.date('Ymd');
            $text = $data['content'];

            $imgUrl = getImgList($text);
            if ($imgUrl[1]){

                $reImgUrl = fileUrl($imgUrl[1],$data['url']);
                $imgArr = BatchDownLoadFiles($reImgUrl,$path);

                $text = str_replace($imgUrl[1],$imgArr['imgArr'],$text);
            }
            $arr = [
                'cid'=>$data['cid'],
                'title'=>$data['title'],
                'tags'=>$data['tags'],
                'keywords'=>$data['keywords'],
                'description'=>$data['description'],
                'content'=>$text,
                'status'=>$data['status'],
                'orderSort'=>$data['orderSort'],
                'downloadJur'=>$data['downloadJur'],
                'createTime'=>time(),
                'updateTime'=>time(),
            ];
            $img = UploadImg('file',1);
            if($img['code']==200){
                if($img['ident'] != 1){
                    $arr['articleImg']=$img['result']['img'];
                    $arr['articleThumbImg']=$img['result']['thumb'];
                }
            }
            //保存数据
            SaveAt('article',$arr);
            return 'success';
        }else{
            return 'refuse';
        }
    }
}





