<?php
/**
 * Created by PhpStorm.
 * CreateTime  : 2025/08/11 16:13
 * file name : Musicnte.php
 * User: asusa
 * Author: Hyy-Cary（优）
 * Contact QQ  : 373889161(.)
 * email: 373889161@qq.com
 * WeChat: 18319021313
 */


namespace app\api\controller;
use app\model\Music;
//header('Access-Control-Allow-Origin: *');
class Musicnte
{
    public function index(){
        /**
         * 参数：
            $id (string|array): 歌曲ID，支持单个ID或多个ID数组
            $level (string): 音质等级
            standard: 标准音质
            exhigh: 极高音质
            lossless: 无损音质
            hires: Hi-Res音质
            jyeffect: 高清环绕声
            sky: 沉浸环绕声
            jymaster: 超清母带
            $cookies (array): Cookie数组
         *
         * 搜索参数：
            $keywords (string): 搜索关键词
            $limit (int): 每页数量
            $offset (int): 偏移量
            $cookies (array): Cookie数组
         */
        $name = request()->param('name')??'张学友';
        $id = request()->param('id')??'190233';
        $size = request()->param('limit')??10;
        $start = request()->param('page')??0;
        $api = new Music();
        $cookies = $api->loadCookieFromFile('cookie.txt');

        //请求歌曲播放地址信息
        $url = 'https://api.toubiec.cn/wyapi/getMusicUrl.php';
        /*$data = GetCurl($url,$param);
        if($data['response_code'] == 200){
            $rs = json_decode($data['output']);
            if($rs->code == 200){
                foreach ($rs->data as $item){
                    echo 'ID：'.$item->id.'<br>';
                    echo 'br：'.$item->br.'<br>';
                    echo 'level：'.$item->level.'<br>';
                    echo '大小：'.toSize($item->size).'<br>';
                    echo '时长：'.$item->duration.'<br>';
                    echo '时间：'.$item->time.'<br>';
                    echo '播放地址：'.$item->url.'<br>';
                }
            }
        }*/
        $par = request()->param();
        $level = 'standard';
        if(array_key_exists('data',$par)){
            foreach ($par['data'] as $k=>$v){
                if($v['name'] == 'name' && $v['value']){
                    $name = $v['value'];
                }
                if($v['name'] == 'level' && $v['value']){
                    $level = $v['value'];
                }
            }
        }
        $param = [
            'level'=>$level,
        ];
        //---------------- 搜索音乐------------------------------
        $result = $api->getSearchMusic($name, $size, $start, $cookies);
        foreach ($result['songs'] as &$song) {
            /*$param['id'] = $song['id'];
            $payUrl = GetCurl($url,$param);
            if($payUrl['response_code'] == 200){
                $rs = json_decode($payUrl['output']);
                if($rs->code == 200){
                    $song['playUrl'] = $rs->data[0]->url;
                    $song['size'] = toSize($rs->data[0]->size);
                }
            }*/
            $song['duration']=$this->formatTime($song['duration'],'ms');
        }
        $arr = array('code'=>0,'msg'=>'ok','count'=>$result['total'],'data'=>$result['songs']);
        echo json_encode($arr,JSON_UNESCAPED_UNICODE);
        /*echo "网易音乐资源搜索结果总数: " . $result['total'] . "<br>\n";
        foreach ($result['songs'] as $song) {
            echo "歌曲: " . $song['name'] . " - " . $song['artists'] . "\n";
            echo "专辑: " . $song['album'] . "\n";
            echo "时长: " . $this->formatTime($song['duration'],'ms') . "\n";
            echo "ID: " . $song['id'] . "<br>\n\n";
        }*/

        //------------------------获取无损音质  获取音乐播放链接-----------------------------
        /*$result = $api->getMusicUrl($id, 'lossless', $cookies);

        echo '<br>';
        foreach ($result['data'] as $song) {
            echo "歌曲ID: " . $song['id'] . "<br>\n";
            echo "播放链接: " . $song['url'] . "<br>\n";
            echo "音质: " . $song['level'] . "<br>\n";
            echo "比特率: " . $song['br'] . "<br>\n";
            echo "文件大小: " . toSize($song['size']) . "<br>\n";
        }
        echo '<br>';*/
        //------------------------获取歌词--------------------------------------
        /*$result = $api->getLyric('190233', $cookies);

        echo "原文歌词:\n" . $result['lrc']['lyric'] . "<br>\n";
        echo "翻译歌词:\n" . $result['tlyric']['lyric'] . "<br>\n";*/
    }
    public function musiclrc(){
        $param = request()->param('id');
        $api = new Music();
        $cookies = $api->loadCookieFromFile('cookie.txt');
        $result = $api->getLyric($param, $cookies);
        echo $result['lrc']['lyric'];
    }
    public function musicplay(){
        $param = request()->param();
        $url = 'https://wyapi.toubiec.cn/api/music/url';
        $param['url'] = $url;
        $payUrl = getMusicUrl($param);
//        echo '<pre>';
//        // 使用示例
//        print_r($payUrl);
//        die;
        $level = [
            'standard'=>'标准音质',
            'exhigh'=>'极高音质',
            'lossless'=>'无损音质',
            'hires'=>'Hi-Res音质',
            'jyeffect'=>'高清环绕声',
            'sky'=>'沉浸环绕声',
            'jymaster'=>'超清母带',
        ];
        if($payUrl['response_code'] == 200){
            $rs = $payUrl['output'];
            if($rs['code'] == 200){
                $api = new Music();
                $res = $api->getSongDetail($param['id']);
                foreach ($rs['data'] as &$item){
                    $item['size'] = toSize($item['size']);
                    $item['level'] = $level[$item['level']];
                    $item['pic'] = $res['songs'][0]['al']['picUrl'];
                    $item['name'] = $res['songs'][0]['name'];
                    $item['artists'] = $res['songs'][0]['ar'][0]['name'];
                }
                $arr = array('code'=>200,'msg'=>'ok','data'=>$rs['data']);
                return json_encode($arr,JSON_UNESCAPED_UNICODE);
            }else{
                $arr = array('code'=>500,'msg'=>'该歌曲不支持下载！','data'=>'');
                return json_encode($arr,JSON_UNESCAPED_UNICODE);
            }
        }
    }
    public function download(){
        $param = request()->param();
        $url = 'https://wyapi.toubiec.cn/api/music/url';
        $param['url'] = $url;
        $payUrl = getMusicUrl($param);
        $level = [
            'standard'=>'标准音质',
            'exhigh'=>'极高音质',
            'lossless'=>'无损音质',
            'hires'=>'Hi-Res音质',
            'jyeffect'=>'高清环绕声',
            'sky'=>'沉浸环绕声',
            'jymaster'=>'超清母带',
        ];
        if($payUrl['response_code'] == 200){
            $rs = $payUrl['output'];
            if($rs['code'] == 200){
                $api = new Music();
                $res = $api->getSongDetail($param['id']);
                foreach ($rs['data'] as &$item){
                    $item['size'] = toSize($item['size']);
                    $item['level'] = $level[$item['level']];
                    $item['pic'] = $res['songs'][0]['al']['picUrl'];
                    $item['name'] = $res['songs'][0]['name'];
                    $item['artists'] = $res['songs'][0]['ar'][0]['name'];
                }
                $arr = array('code'=>200,'msg'=>'ok','data'=>$rs['data']);
                return json_encode($arr,JSON_UNESCAPED_UNICODE);
            }else{
                $arr = array('code'=>500,'msg'=>'该歌曲不支持下载！','data'=>'');
                return json_encode($arr,JSON_UNESCAPED_UNICODE);
            }
        }
    }
    // 格式化歌曲播放时长
    function formatTime($input, $unit = 's') {
        // 如果是毫秒，先转换为秒
        $totalSeconds = ($unit === 'ms') ? $input / 1000 : $input;
        $totalSeconds = (int)$totalSeconds; // 确保是整数

        // 计算分钟和秒
        $mins = floor($totalSeconds / 60);
        $secs = floor($totalSeconds % 60);

        // 格式化为两位数，不足补零
        return sprintf("%02d:%02d", $mins, $secs);
    }
}