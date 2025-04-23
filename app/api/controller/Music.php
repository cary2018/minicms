<?php
/**
 * Created by PhpStorm.
 * CreateTime  : 2025/02/28 17:14
 * file name : Music.php
 * User: asusa
 * Author: Hyy-Cary（优）
 * Contact QQ  : 373889161(.)
 * email: 373889161@qq.com
 * WeChat: 18319021313
 */


namespace app\api\controller;


use app\api\MusicController;
use Metowolf\Meting;
header('Access-Control-Allow-Origin: *');
class Music extends MusicController
{
    public function index(){
        /**
         *
         * [
                {
                    "id": 歌曲ID,
                    "name": 歌曲名字,
                    "artist": [
                        "歌手1",
                        "歌手2"
                    ],
                    "album": 专辑名称,
                    "pic_id": 专辑图片ID,
                    "url_id": 歌曲地址ID,
                    "lyric_id": 歌词ID,
                    "source": 来源
                },
                ...
            ]
         *
         * 搜索
         * http://www.kuwo.cn/search/searchMusicBykeyWord?vipver=1&client=kt&ft=music&cluster=0&strategy=2012&encoding=utf8&rformat=json&mobi=1&issubtitle=1&show_copyright_off=1&pn=0&rn=30&all=Dear+John
         * https://search.kuwo.cn/r.s?pn=0&rn=10&all=唯一&ft=music&rformat=json&encoding=utf8&pcjson=1
         * http://search.kuwo.cn/r.s?prod=kwplayer_ar_9.3.7.2&corp=kuwo&newver=2&vipver=9.3.7.2&source=kwplayer_ar_9.3.7.2_meizu.apk&p2p=1&notrace=0&client=kt&all=%E5%AE%B9%E7%A5%96%E5%84%BF&pn=0&rn=45&ver=kwplayer_ar_9.3.7.2&vipver=1&show_copyright_off=1&newver=2&correct=1&ft=music&cluster=0&strategy=2012&encoding=utf8&rformat=json&vermerge=1&mobi=1&searchapi=2&issubtitle=1&spPrivilege=0
         *
         */
        //header('Access-Control-Allow-Origin: *');
        //vue 配置垮域 https://www.bilibili.com/video/BV1tV4y187Tp/?vd_source=83d44d8a241fc732b726a72bc1cb1652
        //$key = request()->param();
        $limit = request()->param('limit')?request()->param('limit'):30;
        $page = request()->param('page')?request()->param('page'):0;
        $name = request()->param('name')?request()->param('name'):'';
        //$url = 'https://search.kuwo.cn/r.s?pn='.$page.'&rn='.$limit.'&all='.$name.'&ft=music&rformat=json&encoding=utf8&pcjson=1';
        $url = 'http://search.kuwo.cn/r.s';
        $str = 'prod=kwplayer_ar_9.3.7.2&corp=kuwo&newver=2&vipver=9.3.7.2&source=kwplayer_ar_9.3.7.2_meizu.apk&p2p=1&notrace=0&client=kt&all='.$name.'&pn='.$page.'&rn='.$limit.'&ver=kwplayer_ar_9.3.7.2&vipver=1&show_copyright_off=1&newver=2&correct=1&ft=music&cluster=0&strategy=2012&encoding=utf8&rformat=json&vermerge=1&mobi=1&searchapi=2&issubtitle=1&spPrivilege=0';
        //参数转数组
        parse_str($str,$param);
        $kw = GetCurl($url,$param);
        if($kw['response_code'] == 200){

            echo $kw['output'];
            die;
        }else{
            $des = json_decode($kw['output']);
            $des = [];
        }
    }

    //播放列表
    public function paylist(){
        $id = request()->param('id')?request()->param('id'):3778678;
        $page = request()->param('page')?request()->param('page'):0;
        $limit = request()->param('limit')?request()->param('limit'):100;

        //http://music.163.com/song/media/outer/url?id=2083785152.mp3
        //https://api.injahow.cn/meting/?type=url&id=2083785152
        //https://kw-api.cenguigui.cn/?id=3597551428&limit=30&type=list
        $url = 'https://kw-api.cenguigui.cn/';
        $param = [
            'id'=>$id,
            'page'=>$page,
            'limit'=>$limit,
            'type'=>'list'
        ];
        $kw = FCurl_post($url,$param);
        if($kw['response_code']==200){
            $des = json_decode($kw['output']);
            $des = $des->data;
            if($des){
                echo json_encode($des->musicList,JSON_UNESCAPED_UNICODE);
            }else{
                echo json_encode([]);
            }
            die;
        }else{
            echo json_encode([]);
        }
    }
    //播放地址
    public function payurl(){
        //header('Access-Control-Allow-Origin: *');
        $id = request()->param('id');
        $api = new Meting('netease');
        $url = json_decode($api->format(true)->url($id));
        //header('Content-Type: audio/mpeg');
        //header('Content-Disposition: attachment; filename='.$url->url);
        //return $url->url;
        return redirect($url->url);
    }
    //歌词
    public function lyric(){
        //header('Access-Control-Allow-Origin: *');
        $id = request()->param('id');
        $api = new Meting('netease');
        //$lyric = $api->lyric(2033799350);
        $lyric = $api->lyric($id);
        $lyric = json_decode($lyric);

        echo $lyric->lrc->lyric;
    }
    //专辑图片
    public function pic(){
        //header('Access-Control-Allow-Origin: *');
        $id = request()->param('id');
        $api = new Meting('netease');
        $pic = json_decode($api->pic($id));

        return redirect($pic->url);
    }
    //专辑
    public function album(){
        $id = request()->param('id');
        $api = new Meting('netease');
        $album = $api->album($id);
        return $album;
    }
    //最新最热歌单排行
    public function playlists(){
        $limit = request()->param('limit')?request()->param('limit'):20;
        $page = request()->param('page')?request()->param('page'):0;
        $type = request()->param('type')?request()->param('type'):'new';
        $url = 'http://wapi.kuwo.cn/api/pc/classify/playlist/getRcmPlayList';
        $param = [
            'loginUid'=>0,
            'loginSid'=>0,
            'appUid'=>38668888,
            'pn'=>$page,
            'rn'=>$limit,
            'order'=>$type  //最新：new  最热：hot
        ];
        $data = FCurl_post($url,$param,'',30,'GET');
        if($data['response_code']==200){
            $des = json_decode($data['output']);
            /*echo '<pre>';
            print_r($des->data);*/
            echo json_encode($des->data,JSON_UNESCAPED_UNICODE);
            die;
        }
    }
    //歌单对应歌曲列表
    public function songlist(){
        $id = request()->param('id')?request()->param('id'):3778678;
        $page = request()->param('page')?request()->param('page'):0;
        $limit = request()->param('limit')?request()->param('limit'):100;

        $url = 'http://nplserver.kuwo.cn/pl.svc';
        $param = [
            'op'=>'getlistinfo',
            'pid'=>$id,
            'pn'=>$page,
            'rn'=>$limit,
            'encode'=>'utf8',
            'keyset'=>'pl2012',
            'vipver'=>'MUSIC_9.1.1.2_BCS2',
            'newver'=>1,
        ];
        $kw = GetCurl($url,$param);
        if($kw['response_code']==200){
            $des = json_decode($kw['output']);
            if($des->musiclist){
                echo json_encode($des->musiclist,JSON_UNESCAPED_UNICODE);
            }else{
                echo json_encode([]);
            }
            die;
        }else{
            echo json_encode([]);
        }
    }
    //歌单详情
    public function playDetail(){
        /**
         * 歌单详情
         * http://nplserver.kuwo.cn/pl.svc?op=getlistinfo&pid=3595572047&pn=0&rn=10&encode=utf8&keyset=pl2012&vipver=MUSIC_9.1.1.2_BCS2&newver=1
         *
         */
        $id = request()->param('id');
        $limit = request()->param('limit')?request()->param('limit'):30;
        $page = request()->param('page')?request()->param('page'):1;
        $url = 'http://nplserver.kuwo.cn/pl.svc';
        $param = [
            'op'=>'getlistinfo',
            'pid'=>$id,
            'pn'=>$page,
            'rn'=>$limit,
            'encode'=>'utf8',
            'keyset'=>'pl2012',
            'vipver'=>'MUSIC_9.1.1.2_BCS2',
            'newver'=>1,
        ];
        $data = FCurl_post($url,$param,'',30,'GET');

    }
    //实时检索相关歌曲
    public function searchKey(){
        $key = request()->param('name');
        $url = 'http://www.kuwo.cn/openapi/v1/www/search/searchKey';
        $param = [
            'key'=>$key,
            'httpsStatus'=>1,
            'plat'=>'web_www',
            'reqId'=>'aa1947c0-0588-11f0-9b86-8b98e87f82dd',
            'from'=>'',
        ];
        $data = FCurl_post($url,$param,'',30,'GET');

        if($data['response_code']==200){
            $des = json_decode($data['output']);
            echo json_encode($des->data,JSON_UNESCAPED_UNICODE);
            die;
        }else{
            echo json_encode([]);
        }
    }
    public function detail(){
        $id = request()->param('id');
        $url = 'https://api.leafone.cn/api/kuwo';
        $param = [
            'id'=>$id,          //歌曲id
            'type'=>5,          //歌曲质量：1-6 。默认：5 高品质 ,1-2：有损音质，3-4：标准音质，5：高品音质，6：无损音质
        ];
        $data = GetCurl($url,$param);
        echo '<pre>';
        print_r($data['output']);
    }
    public function taglist(){
        $url = 'http://wapi.kuwo.cn/api/pc/classify/playlist/getTagList';
        $param = [
            'cmd'=>'rcm_keyword_playlist',
            'user'=>0,
            'prod'=>'kwplayer_pc_9.1.1.2',
            'vipver'=>'9.1.1.2',
            'source'=>'kwplayer_pc_9.1.1.2',
            'loginUid'=>0,
            'loginSid'=>0,
            'appUid'=>38668888,
        ];
        $result = GetCurl($url,$param);
        if($result['response_code'] == 200){
            $des = json_decode($result['output']);
            if($des->code == 200){
                echo $result['output'];
            }else{
                return $des->msg;
            }
        }else{
            return '获取数据失败...';
        }
    }
    public function tagplaylist(){
        $id = request()->param('id')?request()->param('id'):168;
        $page = request()->param('page')?request()->param('page'):1;
        $limit = request()->param('limit')?request()->param('limit'):30;
        $url = 'http://wapi.kuwo.cn/api/pc/classify/playlist/getTagPlayList';
        $param = [
            'loginUid'=>0,
            'loginSid'=>0,
            'appUid'=>38668888,
            'id'=>$id,
            'pn'=>$page,
            'rn'=>$limit,
        ];
        $result = GetCurl($url,$param);
        if($result['response_code'] == 200){
            $des = json_decode($result['output']);
            if($des->code == 200){
//                echo "<pre>";
//                print_r($des);
                echo json_encode($des->data);
            }else{
                echo $des->msg;
            }
        }else{
            echo '获取数据失败...';
        }
    }
    //kw歌词
    public function kwlrc(){
        $id = request()->param('id');
        $url = 'http://m.kuwo.cn/newh5/singles/songinfoandlrc';
        $param = [
            'musicId'=>$id,//歌曲id
            'httpsStatus'=>1,
            'reqId'=>'969ba290-4b49-11eb-8db2-ebd372233623',
        ];
        $data = GetCurl($url,$param);
        if($data['response_code']==200){
            $result = json_decode($data['output']);
            if($result->status == 200){
                $lrcstr = '';
                $pic = $result->data->songinfo;
                $lrc = $result->data->lrclist;

                foreach ($lrc as $item){
                    echo '['.formatTime($item->time).']'.$item->lineLyric."\n";
                }
//                return json_encode(['code'=>200,'lrc'=>$lrcstr],JSON_UNESCAPED_UNICODE);
                //print_r($lrc);
            }else{
                echo $result->msg;
            }
        }else{
            echo '请求失败。。。';
        }
    }
    //图片
    public function kwpic(){
        $id = request()->param('id');
        $url = 'http://m.kuwo.cn/newh5/singles/songinfoandlrc';
        $param = [
            'musicId'=>$id,//歌曲id
            'httpsStatus'=>1,
            'reqId'=>'969ba290-4b49-11eb-8db2-ebd372233623',
        ];
        $data = GetCurl($url,$param);
        if($data['response_code']==200){
            $result = json_decode($data['output']);
            if($result->status == 200){
                $pic = $result->data->songinfo;
                $img = ImgFormUrl($pic->pic);
                return redirect($img);
            }else{
                echo $result->msg;
            }
        }else{
            echo '请求失败。。。';
        }
        $url = 'http://artistpicserver.kuwo.cn/pic.web';
        $param = [
            'type'=>'rid_pic',
            'pictype'=>'url',
            'size'=>'[200,200]',
            'rid'=>$id
        ];
        $data = GetCurl($url,$param);
        if($data['response_code']==200){
            $result = $data['output'];
            $img = ImgFormUrl($result);
            return redirect($img);
        }else{
            echo '请求失败。。。';
        }
    }
    public function kwurl(){
        $id = request()->param('id');
        $type = request()->param('type');
        /**
         *
         * http://mobi.kuwo.cn/mobi.s?f=web&source=kwplayer_ar_5.1.0.0_B_jiakong_vh.apk&type=convert_url_with_sign&rid=169877&br=320kmp3
         *
         * http://nmobi.kuwo.cn/mobi.s?f=web&source=kwplayerhd_ar_4.3.0.8_tianbao_T1A_qirui.apk&type=convert_url_with_sign&rid=171873696&br=320kmp3
         * acc 普通音质 ACC
            wma 普通音质 WMA
            ogg 标准音质 ogg
            standard 低音质 MP3
            exhigh 高音质 MP3
            ape 无损音质(ape) FLAC
            lossless 无损 FLAC
            hires HiRes音质 FLAC
            zp 超高音质(zp) flac
         *
         */
        $url = 'http://mobi.kuwo.cn/mobi.s';
        $param = [
            'f'=>'web',
            'source'=>'kwplayerhd_ar_4.3.0.8_tianbao_T1A_qirui.apk',
            'type'=>'convert_url_with_sign',
            'rid'=>$id,
            'br'=>'320kmp3'
        ];
        if($type && $type == 6){
            $param['br'] = '';
            $param['format'] = 'flac';
        }
        $data = GetCurl($url,$param);
        if($data['response_code']==200){
            $result = json_decode($data['output']);
            if($result->code == 200){
                $des = $result->data;
                if($des->bitrate < 320){
                    $param['br'] = '128kmp3';
                    $data = GetCurl($url,$param);
                    $result = json_decode($data['output']);
                    $des = $result->data;
                }
                $reUrl = transformUrl($des->url);
                //$urls = $hostMappings[$path['host']].$path['path'];
                return redirect($reUrl);
            }
        }
        $url = 'https://kw-api.cenguigui.cn';
        $acc = [5=>'exhigh',6=>'hires'];
        $type = $type?$type:5;
        $param = [
            'id'=>$id,
            'type'=>'song',
            'level'=>$acc[$type]?$acc[$type]:'hires',
            'format'=>'json',
        ];
        $data = GetCurl($url,$param);
        if($data['response_code']==200){
            $result = json_decode($data['output']);
            if($result->code == 200){
                $des = $result->data;
                return redirect($des->url);
            }
        }

        //备用
        $url = 'https://api.leafone.cn/api/kuwo';
        $param = [
            'id'=>$id,          //歌曲id
            'type'=>$type,          //歌曲质量：1-6 。默认：5 高品质 ,1-2：有损音质，3-4：标准音质，5：高品音质，6：无损音质
        ];
        $data = GetCurl($url,$param);
        if($data['response_code']==200){
            $result = json_decode($data['output']);
            if($result->code == 200){
                $des = $result->data;
                return redirect($des->url);
            }else{
                echo $result->msg;
                echo $result->tips.'--修正5秒';
            }
        }else{
            echo '请求失败。。。';
        }
    }
    public function kwpurl(){
        $id = request()->param('id');
        $type = request()->param('type');

        $url = 'https://antiserver.kuwo.cn/anti.s';
        $param = [
            'rid'=>$id,          //歌曲id
            'type'=>'convert_url3',
            'format'=>'mp3',
        ];
        $data = GetCurl($url,$param);
        if($data['response_code']==200){
            $result = json_decode($data['output']);
            if($result->code == 200){
                return redirect($result->url);
            }else{
                echo '获取失败！！！';
            }
        }else{
            echo '请求失败。。。';
        }
    }
    public function test(){
        $param = request()->param();
        // 示例参数（需与前端一致）
        $key = 'thisIs32BytesKeyForAES256CBCMode'; // 32字节密钥
        $iv = 'LQ33SL2G7dsiJee1mTimNw=='; // 16字节IV
        //$iv = openssl_random_pseudo_bytes(16); // 生成 16 字节随机二进制数据
        /*$ivBase64 = base64_encode($iv); // 存储或传输时需编码
        $enf = $this->aesDecrypt($param['firstName'],$key,$iv);
        $enl = $this->aesDecrypt($param['lastName'],$key,$iv);
        $sing = $this->generateSignature($param,$key);*/
        var_dump($param);
        die;
        // 处理前端请求
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $encrypted = mb_convert_encoding($param['firstName'],'UTF-8', 'auto');
            $decrypted = $this->aesDecrypt($encrypted, $key, $iv);
            echo json_encode(['decrypted' => $decrypted]);
        }
    }
    function aesDecrypt($encryptedData, $key, $iv) {
        $data = base64_decode($encryptedData);
        return openssl_decrypt($data, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
    }

    function aesEncrypt($rawData, $key, $iv) {
        $encrypted = openssl_encrypt($rawData, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
        return base64_encode($encrypted);
    }
    // 加密
    function encryptData($plaintext, $key) {
        $iv = openssl_random_pseudo_bytes(16);
        $ciphertext = openssl_encrypt($plaintext, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv);
        return base64_encode($iv . $ciphertext); // 拼接 IV 和密文
    }

    // 解密
    function decryptData($encryptedData, $key) {
        $data = base64_decode($encryptedData);
        $iv = substr($data, 0, 16); // 提取前 16 字节作为 IV
        $ciphertext = substr($data, 16);
        return openssl_decrypt($ciphertext, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv);
    }
    // PHP 端：AES-256-CBC 加密 + HMAC 签名
    function encryptAndSign($data, $key, $iv) {
        $encrypted = openssl_encrypt($data, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
        $signature = hash_hmac('sha256', $encrypted, $key);
        return base64_encode($iv . $encrypted . $signature);
    }
    // 示例代码（HMAC-SHA256）
    function generateSignature($params, $secretKey) {
        ksort($params);
        $paramStr = http_build_query($params);
        return base64_encode(hash_hmac('sha256', $paramStr, $secretKey, true));
    }
}