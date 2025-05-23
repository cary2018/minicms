<?php
/**
 * Created by PhpStorm.
 * CreateTime  : 2024/12/04 21:49
 * file name : Collect.php
 * User: asusa
 * Author: Hyy-Cary（优）
 * Contact QQ  : 373889161(.)
 * email: 373889161@qq.com
 * WeChat: 18319021313
 */


namespace app\model;


use Overtrue\Pinyin\Pinyin;
use think\Model;
use think\facade\Db;

class Collect extends Model
{
    public function vod($param)
    {
        if(!array_key_exists('type',$param)){
            $data = $this->vod_json($param);

            if($data['code'] == 1){
                return $data;
            }
            else{
                return $this->vod_xml($param);
            }
        }
        if($param['type'] == '1'){
            return $this->vod_xml($param);
        }
        elseif($param['type'] == '2'){
            return $this->vod_json($param);
        }
        else{
            $data = $this->vod_json($param);

            if($data['code'] == 1){
                return $data;
            }
            else{
                return $this->vod_xml($param);
            }
        }
    }

    public function vod_xml($param,$html='')
    {
        $keys = ['t', 'page', 'ids', 'wd', 'ac', 'h', 'rday','param'];
        foreach ($keys as $key) {
            if (!array_key_exists($key, $param)) {
                $param[$key] = '';
            }
        }
        $url_param = [
            'ac' => $param['ac'],
            't' => $param['t'],
            'pg' => is_numeric($param['page']) ? $param['page'] : '',
            'h' => $param['h'] ?: $param['rday'] ?? '',
            'ids' => $param['ids'],
            'wd' => $param['wd'],
        ];

        if(empty($param['h']) && !empty($param['rday'])){
            $url_param['h'] = $param['rday'];
        }

        if($param['ac']!='list'){
            $url_param['ac'] = 'videolist';
        }

        $url = $param['cjurl'];
        if(strpos($url,'?')===false){
            $url .='?';
        }
        else{
            $url .='&';
        }

        $url .= http_build_query($url_param). base64_decode($param['param']);

        $result = $this->checkCjUrl($url);
        if ($result['code'] > 1) {
            return $result;
        }
        $html = GetCurl($param['cjurl'],$url_param);

        //$html = mac_curl_get($url);
        if($html['response_code'] == 200){
            $html = $html['output'];
        }else{
            $html = '';
        }

        if(empty($html)){
            return ['code'=>1001, 'msg'=>lang('get_html_err') . ', url: ' . $url];
        }
        $html = mac_filter_tags($html);
        $xml = @simplexml_load_string($html);
        if(empty($xml)){
            $labelRule = '<pic>'."(.*?)".'</pic>';
            $labelRule = mac_buildregx($labelRule,"is");
            preg_match_all($labelRule,$html,$tmparr);
            $ec=false;
            foreach($tmparr[1] as $tt){
                if(strpos($tt,'[CDATA')===false){
                    $ec=true;
                    $ne = '<pic>'.'<![CDATA['.$tt .']]>'.'</pic>';
                    $html = str_replace('<pic>'.$tt.'</pic>',$ne,$html);
                }
            }
            if($ec) {
                $xml = @simplexml_load_string($html);
            }
            if(empty($xml)) {
                return ['code' => 1002, 'msg'=>lang('model/collect/xml_err')];
            }
        }

        $array_page = [
            'page' => (string)$xml->list->attributes()->page,
            'pagecount' => (string)$xml->list->attributes()->pagecount,
            'pagesize' => (string)$xml->list->attributes()->pagesize,
            'recordcount' => (string)$xml->list->attributes()->recordcount,
            'url' => $url
        ];

        $bind_list = config('bind');


        $key = 0;
        $array_data = [];
        foreach ($xml->list->video as $video) {
            $bind_key = $param['cjflag'] . '_' . (string)$video->tid;

            // Initialize bind_list if not exists
            if (!isset($bind_list[$bind_key])) {
                $bind_list[$bind_key] = '';
            }

            // Set type_id based on bind_list
            $array_data[$key]['type_id'] = ($bind_list[$bind_key] > 0) ? $bind_list[$bind_key] : 0;
            $array_data[$key]['vod_id'] = (string)$video->id;
            $array_data[$key]['vod_name'] = (string)$video->name;
            $array_data[$key]['vod_sub'] = (string)$video->subname;
            $array_data[$key]['vod_remarks'] = (string)$video->note;
            $array_data[$key]['type_name'] = (string)$video->type;
            $array_data[$key]['vod_pic'] = (string)$video->pic;
            $array_data[$key]['vod_lang'] = (string)$video->lang;
            $array_data[$key]['vod_area'] = (string)$video->area;
            $array_data[$key]['vod_year'] = (string)$video->year;
            $array_data[$key]['vod_serial'] = (string)$video->state;
            $array_data[$key]['vod_actor'] = (string)$video->actor;
            $array_data[$key]['vod_director'] = (string)$video->director;
            $array_data[$key]['vod_content'] = (string)$video->des;

            $array_data[$key]['vod_status'] = 1;
            //$array_data[$key]['list_name'] = $array_data[$key]['list_name'] ?? '';
            //$array_data[$key]['vod_type'] = $array_data[$key]['list_name'];
            $array_data[$key]['vod_time'] = (string)$video->last;
            $array_data[$key]['vod_total'] = 0;
            $array_data[$key]['vod_isend'] = empty($array_data[$key]['vod_serial']) ? 1 : 0;

            // Formatting addresses and player
            $array_from = [];
            $array_url = [];
            $array_server = [];
            $array_note = [];

            // Check for video download links
            if (isset($video->dl->dd) && $count = count($video->dl->dd)) {
                for ($i = 0; $i < $count; $i++) {
                    $array_from[] = (string)$video->dl->dd[$i]['flag'];
                    $array_url[] = vod_xml_replace((string)$video->dl->dd[$i]);
                    $array_server[] = 'no';
                    $array_note[] = '';
                }
            } else {
                $array_from[] = (string)$video->dt;
                $array_url[] = '';
                $array_server[] = '';
                $array_note[] = '';
            }

            // Determine whether to use download or play information
            $key_prefix = (strpos(base64_decode($param['param']), 'ct=1') !== false) ? 'vod_down' : 'vod_play';
            $array_data[$key]["{$key_prefix}_from"] = implode('$$$', $array_from);
            $array_data[$key]["{$key_prefix}_url"] = implode('$$$', $array_url);
            $array_data[$key]["{$key_prefix}_server"] = implode('$$$', $array_server);
            $array_data[$key]["{$key_prefix}_note"] = implode('$$$', $array_note);

            $key++;
        }

        //分类列表
        $array_type = [];
        $key=0;
        if ($param['ac'] === 'list') {
            $xmlA = $xml->class->ty ?? []; // 使用空合并运算符处理可能的未定义情况
            foreach ($xmlA as $ty) {
                $array_type[] = [
                    'type_id' => (string)$ty->attributes()->id,
                    'type_name' => (string)$ty
                ];
                $key++;
            }
        }

        $res = ['code'=>1, 'msg'=>'xml', 'page'=>$array_page, 'type'=>$array_type, 'data'=>$array_data ];
        return $res;
    }

    public function vod_json($param)
    {
        $keys = ['t', 'page', 'ids', 'wd', 'ac', 'h', 'rday','param'];
        foreach ($keys as $key) {
            if (!array_key_exists($key, $param)) {
                $param[$key] = '';
            }
        }
        $url_param = [
            'ac' => $param['ac'],
            't' => $param['t'],
            'pg' => is_numeric($param['page']) ? $param['page'] : '',
            'h' => $param['h'] ?: $param['rday'] ?? '',
            'ids' => $param['ids'],
            'wd' => $param['wd'],
        ];

        if($param['ac']!='list'){
            $url_param['ac'] = 'videolist';
        }

        $url = $param['cjurl'];
        if(strpos($url,'?')===false){
            $url .='?';
        }
        else{
            $url .='&';
        }
        $url .= http_build_query($url_param). base64_decode($param['param']);
        $result = $this->checkCjUrl($url);
        if ($result['code'] > 1) {
            return $result;
        }
        $html = FCurl_post($url,[],'','','GET');
        //$html = mac_curl_get($url);
        if($html['response_code'] == 0){
            $json = ['code'=>300, 'msg'=>lang('get_html_err') . ', url: ' . $url];
            echo json_encode($json,JSON_UNESCAPED_UNICODE);
            die;
        }
        $html = $html['output'];
        if(empty($html)){
            return ['code'=>1001, 'msg'=>lang('get_html_err') . ', url: ' . $url];
        }
        $html = mac_filter_tags($html);
        $json = json_decode($html,true);
        if(!$json){
            return ['code'=>1002, 'msg'=>lang('model/collect/json_err') . ', url: ' . $url . ', response: ' . mb_substr($html, 0, 15)];
        }

        $array_page = [];
        $array_page['page'] = $json['page'];
        $array_page['pagecount'] = $json['pagecount'];
        $array_page['pagesize'] = $json['limit'];
        $array_page['recordcount'] = $json['total'];
        $array_page['url'] = $url;

        $bind_list = config('bind');

        $key = 0;
        $array_data = [];
        foreach($json['list'] as $key=>$v){
            $array_data[$key] = $v;
            $bind_key = $param['cjflag'] .'_'.$v['type_id'];
            if (!isset($bind_list[$bind_key])) {
                $bind_list[$bind_key] = '';
            }
            if($bind_list[$bind_key] >0){
                $array_data[$key]['type_id'] = $bind_list[$bind_key];
            }
            else{
                $array_data[$key]['type_id'] = 0;
            }

            if(!empty($v['dl'])) {
                //格式化地址与播放器
                $array_from = [];
                $array_url = [];
                $array_server = [];
                $array_note = [];
                //videolist|list播放列表不同
                foreach ($v['dl'] as $k2 => $v2) {
                    $array_from[] = $k2;
                    $array_url[] = $v2;
                    $array_server[] = 'no';
                    $array_note[] = '';
                }

                $array_data[$key]['vod_play_from'] = implode('$$$', $array_from);
                $array_data[$key]['vod_play_url'] = implode('$$$', $array_url);
                $array_data[$key]['vod_play_server'] = implode('$$$', $array_server);
                $array_data[$key]['vod_play_note'] = implode('$$$', $array_note);
            }
        }

        $array_type = [];
        $key=0;
        //分类列表
        if($param['ac'] == 'list'){
            foreach($json['class'] as $k=>$v){
                $array_type[$key]['type_id'] = $v['type_id'];
                $array_type[$key]['type_name'] = $v['type_name'];
                $key++;
            }
        }

        $res = ['code'=>1, 'msg'=>'json', 'page'=>$array_page, 'type'=>$array_type, 'data'=>$array_data ];
        return $res;
    }

    public function vod_data($param,$data,$show=1)
    {
        if($show==1) {
            mac_echo('[' . __FUNCTION__ . '] ' . lang('data_tip1', [$data['page']['page'],$data['page']['pagecount'],$data['page']['url']]));
        }
        $config = array (
            'status' => '1',
            'hits_start' => '1',
            'hits_end' => '1000',
            'updown_start' => '1',
            'updown_end' => '1000',
            'score' => '1',
            'pic' => '0',
            'tag' => '0',
            'class_filter' => '1',
            'psename' => '1',
            'psernd' => '0',
            'psesyn' => '0',
            'pseplayer' => '0',
            'psearea' => '0',
            'pselang' => '0',
            'urlrole' => '0',
            'inrule' => ',a,f,g',
            'uprule' => ',a,d,j,r,u,v',
            'filter' => '色戒,色即是空',
            'namewords' => '第1季=第一季#第2季=第二季#第3季=第三季#第4季=第四季',
            'thesaurus' => ' =',
            'playerwords' => '',
            'areawords' => '',
            'langwords' => '',
            'words' => 'aaa#bbb#ccc#ddd#eee',
            'inrule_first_change' => true,
        );

        $filter_year = !empty($param['filter_year']) ? $param['filter_year'] : '';
        $filter_year_list = $filter_year ? get_array_unique_id_list(explode(',', $filter_year)) : [];


        $type_list = GetCache('type_list');
        $filter_arr = explode(',',$config['filter']);
        $filter_arr = array_filter($filter_arr);
        $pse_rnd = explode('#',$config['words']);
        $pse_rnd = array_filter($pse_rnd);
        $pse_name = mac_txt_explain($config['namewords'], true);
        $pse_syn = mac_txt_explain($config['thesaurus'], true);
        $pse_player = mac_txt_explain($config['playerwords'], true);
        $pse_area = mac_txt_explain($config['areawords'], true);
        $pse_lang = mac_txt_explain($config['langwords'], true);
        $ArrData = [];
        foreach($data['data'] as $k=>$v){
            $color='red';
            $des='';
            $msg='';
            $tmp='';

            if ($v['type_id'] ==0) {
                $des = lang('type_err');
            } elseif (empty($v['vod_name'])) {
                $des = lang('name_err');
            } elseif (mac_array_filter($filter_arr,$v['vod_name']) !==false) {
                $des = lang('name_in_filter_err');
            } elseif ($filter_year_list && !in_array(intval($v['vod_year']), $filter_year_list)) {
                // 采集时，过滤年份
                // https://github.com/magicblack/maccms10/issues/1057
                $color = 'orange';
                $des = 'year [' . intval($v['vod_year']) . '] not in: ' . join(',', $filter_year_list);
            } else {
                unset($v['vod_id']);

                foreach($v as $k2=>$v2){
                    if(strpos($k2,'_content')===false && $k2!=='vod_plot_detail') {
                        $v[$k2] = strip_tags($v2);
                    }
                }

                $v['type_pid_id'] = intval($type_list[$v['type_id']]['pid']);
                $pinyin = new Pinyin();
                $v['vod_en'] = $pinyin->sentence($v['vod_name'],'');
                $v['vod_letter'] = strtoupper(substr($v['vod_en'],0,1));
                // 使用资源站的添加时间，更新时间保持当前
                // https://github.com/magicblack/maccms10/issues/780
                if (empty($v['vod_time_add']) || strlen($v['vod_time_add']) != 10) {
                    $v['vod_time_add'] = time();
                }
                // 支持外部自定义修改时间
                // https://github.com/magicblack/maccms10/issues/862
                $v['vod_time'] = time();
                if (!empty($v['vod_time_update']) && strlen($v['vod_time_update']) == 10) {
                    $v['vod_time'] = (int)$v['vod_time_update'];
                }

                if($config['hits_start']>0 && $config['hits_end']>0) {
                    $v['vod_hits'] = rand($config['hits_start'], $config['hits_end']);
                    $v['vod_hits_day'] = rand($config['hits_start'], $config['hits_end']);
                    $v['vod_hits_week'] = rand($config['hits_start'], $config['hits_end']);
                    $v['vod_hits_month'] = rand($config['hits_start'], $config['hits_end']);
                }

                if($config['updown_start']>0 && $config['updown_end']){
                    $v['vod_up'] = rand($config['updown_start'], $config['updown_end']);
                    $v['vod_down'] = rand($config['updown_start'], $config['updown_end']);
                }

                if($config['score']==1) {
                    $v['vod_score_num'] = rand(1, 1000);
                    $v['vod_score_all'] = $v['vod_score_num'] * rand(1, 10);
                    $v['vod_score'] = round($v['vod_score_all'] / $v['vod_score_num'], 1);
                }

                if ($config['psename'] == 1) {
                    $v['vod_name'] = mac_rep_pse_syn($pse_name, $v['vod_name']);
                }
                if ($config['psernd'] == 1) {
                    $v['vod_content'] = mac_rep_pse_rnd($pse_rnd, $v['vod_content']);
                }
                if ($config['psesyn'] == 1) {
                    $v['vod_content'] = mac_rep_pse_syn($pse_syn, $v['vod_content']);
                }
                if ($config['pseplayer'] == 1) {
                    $v['vod_play_from'] = mac_rep_pse_syn($pse_player, $v['vod_play_from']);
                }
                if ($config['psearea'] == 1) {
                    $v['vod_area'] = mac_rep_pse_syn($pse_area, $v['vod_area']);
                }
                if ($config['pselang'] == 1) {
                    $v['vod_lang'] = mac_rep_pse_syn($pse_lang, $v['vod_lang']);
                }

                if(empty($v['vod_blurb'])){
                    $v['vod_blurb'] = mac_substring( strip_tags($v['vod_content']) ,100);
                }

                $where = [];

                if (strpos($config['inrule'], 'a')!==false) {
                    $where['vod_name'] = mac_filter_xss($v['vod_name']);
                }

                //验证地址
                $info = Db::name('Vod')->where($where)->find();

                if (!$info) {
                    // 新增
                    if ($param['opt'] == 2) {
                        $des= lang('not_check_add');
                    } else {
                        //2024 ------- 判断播放地址是否存在，存在则执行添加操作
                        $vod_id = 0;
                        if($v['vod_play_url']){
                            $vod_id = SaveAt('vod',$v);
                        }
                        if ($vod_id > 0) {
                            //$vod_search_enabled && checkAndUpdateTopResults(['vod_id' => $vod_id] + $v, true);
                            $color = 'green';
                            $des = lang('add_ok');
                        } else {
                            $color = 'red';
                            $des = 'vod insert failed';
                        }
                    }
                } else {
                    // 更新
                    if(empty($config['uprule'])){
                        $des = lang('uprule_empty');
                    }
                    elseif ($info['vod_lock'] == 1) {
                        $des = lang('data_lock');
                    }
                    elseif($param['opt'] == 1){
                        $des = lang('not_check_update');
                    }
                    else {
                        unset($v['vod_time_add']);
                        unset($v['type_name']);
                        $v=array('vod_id'=>$info['vod_id'])+$v;
                        //重组播放数组
                        $old_play = combinePlayData($info['vod_play_from'],$info['vod_play_url']);
                        $new_play = combinePlayData($v['vod_play_from'],$v['vod_play_url']);
                        //合并新旧播放地址
                        $new_arr = array_merge($old_play,$new_play);
                        //重组播放地址数组转字符串
                        $str_url = implode('$$$',$new_arr);
                        $str_from = implode('$$$',array_keys($new_arr));
                        $v['vod_play_from'] = $str_from;
                        $v['vod_play_url'] = $str_url;
                        if(strpos($info['vod_play_from'],$v['vod_play_from']) === false){
                            // 新类型播放组，加入
                            $color = 'green';
                            $des .= lang('playgroup_add_ok',[$info['vod_play_from']]);
                            $v['vod_play_server'] = $info['vod_play_server'].'$$$'.$v['vod_play_server'];
                            $v['vod_play_note'] = $info['vod_play_note'].$v['vod_play_note'];
                        }else{
                            $color = 'green';
                            $des .= lang('playgroup_update_ok',[$info['vod_play_from']]);
                            $v['vod_play_server'] = $info['vod_play_server'];
                            $v['vod_play_note'] = $info['vod_play_note'];
                        }

                        if($v['vod_id']>0){
                            $v['vod_time'] = time();
                            $res = SaveAt('vod',$v);
                            $color = 'green';
                            if ($res === false) {

                            }
                        }
                        else{
                            $des = lang('not_need_update');
                        }

                    }
                }
            }
            if($show==1) {
                mac_echo( ($k + 1) .'、'. $v['vod_name'] . " <font color='{$color}'>" .$des .'</font>'. $msg.'' );
            }
            else{
                return ['code'=>($color=='red' ? 1001 : 1),'msg'=>$des ];
            }
        }

        //一次采集完成，重建视频缓存
        createRepeatCache();
        //http://www.mac10.com/api.php/timming/index.html?enforce=1&name=subozyzanzhu06
        $key = md5($_SERVER['HTTP_HOST']). '_'.'collect_break_vod';
        /*echo '<pre>';
        echo $key;
        print_r($data);
        die;*/
        /*$con = request()->controller(true);
        $path = request()->action();
        echo '<pre>';
        print_r($param);
        echo $url;
        die;*/
        $entrance = app('http')->getName().'/'.request()->controller(true);
        if($entrance=='api/timing'){
            delCache($key);
            if ($data['page']['page'] < $data['page']['pagecount']) {
                $param['page'] = intval($data['page']['page']) + 1;
                $res = $this->vod($param);
                if($res['code']>1){
                    return $this->error($res['msg']);
                }
                $this->vod_data($param,$res );
                output_buffer();
            }
            mac_echo(lang('is_over'));
            die;
        }

        if(empty($GLOBALS['config']['app']['collect_timespan'])){
            $GLOBALS['config']['app']['collect_timespan'] = 3;
        }
        if($show==1) {
            if ($param['ac'] == 'cjsel') {
                delCache($key);
                mac_echo(lang('is_over'));
                unset($param['ids']);
                $param['ac'] = 'list';
                $url = url('collectData') . '?' . http_build_query($param);
                $ref = $_SERVER["HTTP_REFERER"];
                if(!empty($ref)){
                    $url = $ref;
                }
                mac_jump($url, 5);
            } else {
                if ($data['page']['page'] >= $data['page']['pagecount']) {
                    delCache($key);
                    mac_echo(lang('is_over'));
                    unset($param['page'],$param['ids']);
                    $param['ac'] = 'list';
                    $param['page'] = 0;
                    $param['ids'] = '';
                    $url = url('api') . '?' . http_build_query($param);
                    mac_jump($url, 4);
                } else {
                    $param['page'] = intval($data['page']['page']) + 1;
                    $url = url('collectData') . '?' . http_build_query($param);
                    mac_jump($url, 3 );
                }
            }
        }
    }

    /**
     * 同步图片
     *
     * @param $pic_status int 是否同步。为1时，同步图片
     * @param $pic_url
     * @param string $flag
     * @return array
     */
    private function syncImages($pic_status, $pic_url, $flag = 'vod')
    {
        $img_url_downloaded = $pic_url;
        if ($pic_status == 1) {
            $config = (array)config('maccms.upload');
            $img_url_downloaded = model('Image')->down_load($pic_url, $config, $flag);
            if ($img_url_downloaded == $pic_url) {
                // 下载失败，显示老图信息
                $des = '<a href="' . $pic_url . '" target="_blank">' . $pic_url . '</a><font color=red>'.lang('download_err').'!</font>';
            } else {
                // 下载成功，显示新图信息
                if (str_starts_with($img_url_downloaded, 'upload/')) {
                    $link = MAC_PATH . $img_url_downloaded;
                } else {
                    $link = str_replace('mac:', $config['protocol'] . ':', $img_url_downloaded);
                }
                $des = '<a href="' . $link . '" target="_blank">' . $link . '</a><font color=green>'.lang('download_ok').'!</font>';
            }
        }
        return ['pic' => $img_url_downloaded, 'msg' => $des];
    }

    /**
     * 检查url合法性
     * https://github.com/magicblack/maccms10/issues/763
     */
    private function checkCjUrl($url)
    {
        $result = parse_url($url);
        if (empty($result['host']) || in_array($result['host'], ['127.0.0.1', 'localhost'])) {
            return ['code' => 1001, 'msg' => lang('model/collect/cjurl_err') . ': ' . $url];
        }
        return ['code' => 1];
    }
}