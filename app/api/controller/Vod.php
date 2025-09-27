<?php
/**
 * Created by PhpStorm.
 * CreateTime  : 2025/06/12 22:38
 * file name : Vod.php
 * User: asusa
 * Author: Hyy-Cary（优）
 * Contact QQ  : 373889161(.)
 * email: 373889161@qq.com
 * WeChat: 18319021313
 */


namespace app\api\controller;


use app\api\BaseController;
use app\model\Collect;

class Vod extends BaseController
{
    public function index(){
        $wd = request()->param('wd');
        $ids = request()->param('ids');
        $ac = request()->param('ac')??'list';
        $page = request()->param('page')??'1';
        $par = [
            'ac' => 'list', //cj(播放数据),list(分类数据)
            'cjflag'=>'',
            'page'=>$page,
            'ids'=>$ids,  //视频id，示例：123,526,5656
            'cjurl'=>'https://api.yzzy-api.com/inc/api_mac10.php',
            'mid'=>'',
            'wd'=>$wd,
        ];
        $res = new Collect();
        $result = $res->vod($par);
        echo '<pre>';
        print_r($par);
        print_r($result);
    }
    public function detail(){
        $wd = request()->param('wd');
        $ids = request()->param('ids');
        $ac = request()->param('ac')??'list';
        $page = request()->param('page')??'1';
        $par = [
            'ac' => 'cj', //cj(播放数据),list(分类数据)
            'cjflag'=>'',
            'page'=>$page,
            'ids'=>$ids,  //视频id，示例：123,526,5656
            'cjurl'=>'https://api.yzzy-api.com/inc/api_mac10.php',
            'mid'=>'',
            'wd'=>$wd,
        ];
        $res = new Collect();
        $result = $res->vod($par);
        echo '<pre>';
        print_r($par);
        print_r($result);
    }
}