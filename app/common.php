<?php
// 应用公共文件
use think\facade\Cache;
use think\facade\Session;
use think\facade\Cookie;
use think\facade\Config;
use think\facade\Db;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
//获取应用下的所有控制器和方法
function AllMethods(){
    //获取应用名称 tp版本：6.1.4
    $app = app('http')->getName();
    //拼接目录地址
    $dir = base_path() . $app . '/controller';
    //列出目录中的文件和目录
    $files = scandir($dir);
    $methods = [];
    $i = 0;
    //过滤方法
    $get_functions = array('initialize', '__construct', 'registerMiddleware', 'beforeAction','fetch','display','assign','filter','engine','validateFailException','validate','__debugInfo','success','error','result','redirect','getResponseType');
    foreach ($files as $file) {
        if (stripos($file, '.php') !== false) {
            $controllerName = str_replace('.php', '', $file);
            $className = '\app\\' . $app . '\controller\\' . $controllerName;
            //echo $className;die;
            //返回方法名
            $met = get_class_methods($className);
            //计算数组的差集
            $result = array_diff($met,$get_functions);
            foreach ($result as $k=>$v){
                $methods[$i]['module'] = $app;
                $methods[$i]['controller'] = $controllerName;
                $methods[$i]['action'] = $v;
                $i++;
            }
        }
    }
    return $methods;
}
/**
 * @param string $mailto 收件地址
 * @param string $nickname  邮件昵称
 * @param string $subject   邮件标题
 * @param string $content   邮件内容
 * @param string $addAttachment
 * 邮件发送
 */
function SendEmail($mailto, $nickname, $subject, $content,$addAttachment='')
{
    $mail = new PHPMailer(true);
    try {
        //Server settings
        $mail->SMTPDebug = SMTP::DEBUG_OFF;                // 调试模式输出
        $mail->isSMTP();                                   // 使用SMTP
        $mail->Host       = Cfg('email_smtp');         // SMTP服务器
        $mail->SMTPAuth   = true;                          // 允许 SMTP 认证
        $mail->Username   = Cfg('email_account');      // SMTP 用户名  即邮箱的用户名
        $mail->Password   = Cfg('email_password');     // SMTP 密码  部分邮箱的授权码(例如163邮箱)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;   // 允许 TLS 或者ssl协议
        $mail->Port       = Cfg('email_port');         // 服务器端口 25 或者465 具体要看邮箱服务器支持

        //Recipients
        $mail->setFrom(Cfg('email_account'), $nickname);//发件人
        $mail->addAddress($mailto, $nickname);//收件人
        //$mail->addAddress('ellen@example.com');               // 可添加多个收件人
        $mail->addReplyTo('info@example.com', 'Information');//回复的时候回复给哪个邮箱 建议和发件人一致
        //$mail->addCC('cc@example.com');//抄送
        //$mail->addBCC('bcc@example.com');//密送

        //Attachments
        if($addAttachment){
            $mail->addAttachment($addAttachment);//发送附件
            //$mail->addAttachment('D:\web\tp\tp6\public\img/5_copy.jpg');  // 发送附件并且重命名
        }

        //Content
        $mail->isHTML(true);         // 是否以HTML文档格式发送  发送后客户端可直接显示对应HTML内容
        $mail->Subject = $subject;          //邮件标题
        $mail->Body    = $content;          //邮件内容
        //果邮件客户端不支持HTML则显示此内容
        $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

        $mail->send();
        $arr = [
            'code'=>200,
            'message'=>lang('email_send'),
            'data'=>'10000',
        ];
        echo json_encode($arr);
    } catch (Exception $e) {
        $arr = [
            'code'=>200,
            'message'=>lang('email_error'),
            'data'=>"{$mail->ErrorInfo}",
        ];
        echo json_encode($arr);
    }
}
/**
 * @param $password
 * @return bool|mixed|string
 * 哈唏+MD5加密
 *
 */
function PasswordSet($password)
{
    $password = password_hash(md5($password),PASSWORD_BCRYPT);
    return $password;
}
/**
 * @param $np    //输入的原始密码
 * @param $op   //加密后的原始密码
 * @return bool
 * 哈唏+MD5验证密码
 */
function PasswordVerify($np,$op)
{
    $result = password_verify(md5($np),$op);
    if($result)
    {
        return true;
    }else{
        return false;
    }
}
/**
 * @param string $name  缓存名
 * @param array $data  数据
 * @param int $outTime  有效时间
 * 设置缓存
 */
function SetCaChe($name,$data,$outTime = 30){
    $outTime = (3600*3600)*$outTime;
    Cache::set($name, $data, $outTime);
}

/**
 * @param $name
 * @return mixed
 * 缓存不存在返回空字符串
 * 获取缓存
 */
function GetCache($name){
    return Cache::get($name,'');
}

/**
 * @param $name
 * 删除缓存
 */
function delCache($name){
    Cache::delete($name);
}

/**
 * @param $name
 * @param $value
 * 设置 session
 */
function SetSe($name,$value){
    Session::set($name, $value);
}

/**
 * @param $name
 * @return mixed
 * 不存在返回空字符串
 * 获取 session
 */
function GetSe($name){
    return Session::get($name,'');
}

/**
 * @param $name
 * 删除 session
 */
function DelSe($name){
    Session::delete($name);
}

/**
 * @param string $name cookie名
 * @param string $value  cookie值
 * @param int $expire 过期时间
 * 设置cookie
 */
function SetCk($name,$value,$expire=3600){
    Cookie::set($name,$value,$expire);
}

/**
 * @param string $name
 * 获取cookie
 */
function GetCk($name){
    return Cookie::get($name,'');
}
/**
 * @param string $name
 * 删除cookie
 */
function DelCk($name){
    Cookie::delete($name);
}

/**
 * @param $str
 * 获取配置文件所有参数
 */
function CfgInfo($str){
    return Config::get($str);
}
/**
 * @param $str
 * @return mixed
 * 获取配置文件单个参数
 */
function Cfg($str=''){
    return Config::get('web.'.$str);
}
function GetConfig($name,$str){
    return Config::get($name.'.'.$str);
}
/**
 * @param string $name
 * @param float|int $outTime
 * @return array|mixed
 * @throws \think\db\exception\DataNotFoundException
 * @throws \think\db\exception\DbException
 * @throws \think\db\exception\ModelNotFoundException
 *
 */
function MenuList($name='Menu',$outTime = 3600*24*30){
    //缓存所有菜单列表
    $menu = caheMenu();
    //缓存后台菜单
    //$cache = GetCache($name);
    $cache = '';
    if($cache){
        $newTree = GetTree($menu,0);
        $cache['menuInfo'] = $newTree;
        return $cache;
    }else{
        //更新后台菜单缓存
        SetMenu();
        return GetCache($name);
    }
}
//菜单列表缓存
function caheMenu(){
    $cache = GetCache('MenuList');
    if($cache){
        return $cache;
    }else{
        $menu = GetMenu('menu',[['isShow','=',1],['ident','<>',1]],'id',['orderSort'=>'desc']);
        SetCaChe('MenuList',$menu);
        return $menu;
    }
}
/**
 * @param string $table  表名
 * @param array $where   条件
 * @param string $field  主键字段名（默认id）
 * @param array $order  排序（支持多字段排序['order','id'=>'desc']）
 * @return \think\Collection
 * @throws \think\db\exception\DataNotFoundException
 * @throws \think\db\exception\DbException
 * @throws \think\db\exception\ModelNotFoundException
 * 获取无限菜单数据
 */
function GetMenu($table='menu',$where=[],$field='id',$order=[]){
    $list = Db::name($table)->field("*,concat(path,'-',$field) as bpath")->where($where)->order($order)->order('bpath')->select()->toArray();
    foreach ($list as $key=>$item){
        $level = count(explode('-',$item['path']));
        //加入层级
        $list[$key]['level'] = $level;
    }
    return $list;
}
/**
 * @param string $name
 * @param int $outTime
 * @throws \think\db\exception\DataNotFoundException
 * @throws \think\db\exception\DbException
 * @throws \think\db\exception\ModelNotFoundException
 * 设置菜单缓存
 */
function SetMenu($name='Menu',$outTime = 30){
    $menuList = caheMenu();
    $index = FindTable('menu',[['ident','=',1]]);
    $tree = GetTree($menuList,0);
    $arr = array(
        'homeInfo'=>['title'=>$index['title'],'href'=>$index['hrefUrl']],
        'logoInfo'=>['title'=>Cfg('sys_title'),'href'=>'','image'=>Cfg('sys_logo')],
        'menuInfo'=>$tree,
    );
    SetCaChe($name,$arr,$outTime);
}
/**
 * @param $arr
 * @param int $pid
 * @param string $pids
 * @param string $id
 * @param int $level
 * @return array
 * 返回多维数组（仅供 layuimini 后台菜单使用）
 */
function GetTree($arr,$pid=0,$pids = 'pid',$id = 'id',$level=0){
    $user = GetSe('admin');
    $menu = [];
    if($user && $user['isAdmin'] != 1){
        $menuId = FindTable('admin',[['id','=',$user['id']]]);
        $roleId = implode(',',json_decode($menuId['role'],true));
        $role = AllTable('role',[['status','=',1],['id','in',$roleId]]);
        $authId = [];
        foreach ($role as $v){
            if($v['menuAuth']){
                $json = json_decode($v['menuAuth']);
                foreach($json as $k=>$item){
                    array_push($authId,$item);
                }
            }
        }
        $aid = implode(',',$authId);
        $auth = AllTable('menu',[['isShow','=',1],['id','in',$aid]]);
        foreach ($auth as $val){
            array_push($menu,$val['id']);
        }
        array_unique($menu);
    }
    //初始化儿子
    $child = '';
    $data = array();
    //循环所有数据找$id的儿子
    foreach ($arr as $key => $v) {
        //找到儿子了
        if ($v[$pids] == $pid) {
            //先去掉自己，自己不可能是自己的儿孙
            unset($arr[$key]);
            $son = GetTree($arr, $v[$id],$pids,$id,$level+1);
            //组装数据
            if($user['isAdmin'] == 1){
                $child = [
                    'id'=>$v['id'],
                    'title'=>$v['title'],
                    'href'=>$v['hrefUrl'],
                    'target'=>$v['target'],
                    'icon'=>'fa '.$v['icon'],
                    'level'=>$level,
                    'child'=>$son
                ];
            }else{
                if(in_array($v['id'],$menu)){
                    $child = [
                        'id'=>$v['id'],
                        'title'=>$v['title'],
                        'href'=>$v['hrefUrl'],
                        'target'=>$v['target'],
                        'icon'=>'fa '.$v['icon'],
                        'level'=>$level,
                        'child'=>$son
                    ];
                }else{
                    continue;
                }
            }
            //压入数组
            array_push($data,$child);
        }
    }
    return $data;
}

/**
 * @param $table
 * @param array $where
 * @param array $order
 * @return array|\think\Model|null
 * @throws \think\db\exception\DataNotFoundException
 * @throws \think\db\exception\DbException
 * @throws \think\db\exception\ModelNotFoundException
 * 查询表返回一条数据
 */
function FindTable($table,$where = [],$order=['id'=>'desc']){
    return Db::name($table)->where($where)->order($order)->find();
}
/**
 * @param $table
 * @param array $where
 * @param string $alias
 * @param string $group
 * @return int
 * 返回表总数据数
 */
function CountTable($table, $where=[],$alias='',$group=''){
    return Db::name($table)->alias($alias)->where($where)->group($group)->count();
}

/**
 * @param $table
 * @param array $where
 * @param string $field
 * @param string $alias
 * @return float
 */
function SumField($table,$where=[],$field='id',$alias=''){
    return Db::name($table)->alias($alias)->where($where)->sum($field);
}
/**
 * @return array|mixed
 * @throws \think\db\exception\DataNotFoundException
 * @throws \think\db\exception\DbException
 * @throws \think\db\exception\ModelNotFoundException
 * 导航信息
 */
function getNav(){
    $data = GetCache('navigation');
    if(!$data){
        $data = AllTable('classify',['status'=>1],['orderBy'=>'desc']);
        foreach ($data as $k=>$v){
            $data[$k]['nav'] = AllTables('navigation',[['cid','=',$v['id']],['is_show','=',1]],$v['number'],['orderBy'=>'desc']);
        }
    }
    return $data;
}

function vodType($n){
    $arr = [
        '1'=>'xml',
        '2'=>'json',
    ];
    return $arr[$n];
}
function vodMid($n){
    $arr = [
        '1'=>'视频',
        '2'=>'文章',
        '3'=>'演员',
        '4'=>'角色',
        '5'=>'网站',
    ];
    return $arr[$n];
}
/**
 * @param string $table  表名
 * @param int $start  起始页
 * @param int $size   显示数量
 * @param array $where  条件
 * @param string[] $order  排序
 * @param string $group  分组
 * @throws \think\db\exception\DataNotFoundException
 * @throws \think\db\exception\DbException
 * @throws \think\db\exception\ModelNotFoundException
 *
 */
function pageTable($table,$start=0,$size=10,$where=[],$order=['id'=>'desc'],$group=''){
    return Db::name($table)->where($where)->group($group)->order($order)->page($start,$size)->select()->toArray();
}

function joinTable($table,$table2,$start=0,$size=10,$where=[],$order=['id'=>'desc']){
    return Db::name($table)->alias('a')->leftJoin($table2.' b ','b.id= a.cid')->field('a.*,b.name as nickname')->where($where)->order($order)->page($start,$size)->select()->toArray();
}
/**
 * @param $table
 * @param array $where
 * @param string[] $order
 * @return array
 * @throws \think\db\exception\DataNotFoundException
 * @throws \think\db\exception\DbException
 * @throws \think\db\exception\ModelNotFoundException
 * 返回所有数据
 */
function AllTable($table,$where=[],$order=['id'=>'desc']){
    return Db::name($table)->where($where)->order($order)->select()->toArray();
}
function AllTables($table,$where=[],$number=10,$order=['id'=>'desc']){
    return Db::name($table)->where($where)->order($order)->limit($number)->select()->toArray();
}

function mac_day($t,$f='',$c='#FF0000')
{
    if(empty($t)) { return ''; }
    if(is_numeric($t)){
        $t = date('Y-m-d H:i:s',$t);
    }
    $now = date('Y-m-d',time());
    if($f=='color' && strpos(','.$t,$now)>0){
        return '<font color="' .$c. '">' .$t. '</font>';
    }
    return  $t;
}

function mac_filter_tags($input)
{
    $replacements = array('{:', '<script', '<iframe', '<frameset', '<object', 'onerror');

    // Create a function to perform filtering
    $filter = function($value) use ($replacements) {
        return str_ireplace($replacements, '*', $value);
    };

    // Check if the input is an array or a single value
    if (is_array($input)) {
        // Filter each element if it is not numeric
        return array_map(function($item) use ($filter) {
            return !is_numeric($item) ? $filter($item) : $item;
        }, $input);
    }

    // Filter single value if it is not numeric
    return !is_numeric($input) ? $filter($input) : $input;
}


function mac_buildregx($regstr,$regopt)
{
    return '/'.str_replace('/','\/',$regstr).'/'.$regopt;
}

function mac_echo($str)
{
    echo $str.'<br>';
    ob_flush();flush();
}

/**
 * 采集是否开启
 */
function isCollectEnabled()
{
    $config = config('maccms');
    // 未设置时，默认关闭
    if (!isset($config['app']['vod_search_optimise'])) {
        return false;
    }
    $list = explode('|', $config['app']['vod_search_optimise']);
    return in_array('collect', $list);
}

function mac_txt_explain($txt, $decode = false)
{
    $txtarr = explode('#',$txt);
    $data=[];
    foreach($txtarr as $v){
        if (stripos($v, '=') === false) {
            continue;
        }
        list($from, $to) = explode('=', $v, 2);
        if ($decode === true && stripos($from, '&') !== false && stripos($from, ';') !== false) {
            $from = html_entity_decode($from, ENT_QUOTES, 'UTF-8');
        }
        if ($decode === true && stripos($to, '&') !== false && stripos($to, ';') !== false) {
            $to = html_entity_decode($to, ENT_QUOTES, 'UTF-8');
        }
        $data['from'][] = $from;
        $data['to'][] = $to;
    }
    return $data;
}

function get_array_unique_id_list($list, $need_sort = false) {
    $list = array_unique($list);
    $list = array_map('intval', $list);
    $list = array_filter($list);
    $list = array_values($list);
    $need_sort && sort($list);
    return $list;
}

function mac_array_filter($arr,$str)
{
    if(!is_array($arr)){
        $arr = explode(',',$arr);
    }
    $arr = array_filter($arr);
    if(empty($arr)){
        return false;
    }
    //方式一
    $new_str = str_replace($arr,'*',$str);
    //$badword1 = array_combine($arr,array_fill(0,count($arr),'*'));
    //$new_str = strtr($str, $badword1);
    return $new_str != $str;
}

function mac_txt_merge($txt,$str,$config=0)
{
    if(empty($str)){
        return $txt;
    }
    if($config !='0') {
        if (mb_strlen($str) > 2) {
            $str = str_replace([lang('slice')], [''], $str);
        }
        if (mb_strlen($str) > 2) {
            $str = str_replace([lang('drama')], [''], $str);
        }
    }
    $txt = mac_format_text($txt);
    $str = mac_format_text($str);
    $arr1 = explode(',',$txt);
    $arr2 = explode(',',$str);
    $arr = array_merge($arr1,$arr2);
    return join(',',array_unique( array_filter($arr)));
}

function mac_format_text($str, $allow_space = false)
{
    $finder = array('/', '，', '|', '、', ',,', ',,,');
    if ($allow_space === false) {
        $finder[] = ' ';
    }
    return str_replace($finder, ',', $str);
}

function mac_rep_pse_syn($psearr,$txt)
{
    if(empty($txt)){ $txt=""; }
    if(is_array($psearr['from']) && is_array($psearr['to'])){
        $txt = str_replace($psearr['from'],$psearr['to'],$txt);
    }
    return $txt;
}

function mac_substring($str, $lenth, $start=0)
{
    $len = strlen($str);
    $r = array();
    $n = 0;
    $m = 0;

    for($i=0;$i<$len;$i++){
        $x = substr($str, $i, 1);
        $a = base_convert(ord($x), 10, 2);
        $a = substr( '00000000 '.$a, -8);

        if ($n < $start){
            if (substr($a, 0, 1) == 0) {
            }
            else if (substr($a, 0, 3) == 110) {
                $i += 1;
            }
            else if (substr($a, 0, 4) == 1110) {
                $i += 2;
            }
            $n++;
        }
        else{
            if (substr($a, 0, 1) == 0) {
                $r[] = substr($str, $i, 1);
            }else if (substr($a, 0, 3) == 110) {
                $r[] = substr($str, $i, 2);
                $i += 1;
            }else if (substr($a, 0, 4) == 1110) {
                $r[] = substr($str, $i, 3);
                $i += 2;
            }else{
                $r[] = ' ';
            }
            if (++$m >= $lenth){
                break;
            }
        }
    }
    return  join('',$r);
}

function mac_filter_xss($str)
{
    return trim(htmlspecialchars(strip_tags($str), ENT_QUOTES));
}

function mac_like_arr($s)
{
    $tmp = explode(',',$s);
    $like_arr = [];
    foreach($tmp as $v){
        $like_arr[] = '%'.$v.'%';
    }
    return $like_arr;
}

function mac_get_tag($title,$content){
    $url = base64_decode('aHR0cDovL2FwaS5kcGxheWVyc3RhdGljLmNvbQ==').'/keyword/index?name='.rawurlencode($title).'&txt='.rawurlencode($title).rawurlencode(mac_substring(strip_tags($content),200));
    $data = mac_curl_get($url);
    $json = @json_decode($data,true);
    if($json){
        if($json['code']==1){
            return implode(',',$json['data']);
        }
    }
    return false;
}

function mac_rep_pse_rnd($psearr,$txt,$id=0)
{
    if(empty($psearr)){
        return $txt;
    }
    $i=count($psearr);
    if(empty($txt)){
        if(empty($id)){
            $r = mt_rand(0,$i-1);
        }
        else{
            $r = $id % $i;
        }
        $res= $psearr[$r];
    }
    else{
        if(empty($id)){
            $id = crc32($txt);
        }
        $j=mb_strpos($txt,"<br>");
        $k=mb_strlen($txt);
        if($j==0){ $j=mb_strpos($txt,"<br/>"); }
        if($j==0){ $j=mb_strpos($txt,"<br />"); }
        if($j==0){ $j=mb_strpos($txt,"</p>"); }
        if($j==0){ $j=mb_strpos($txt,"。"); }
        if($j==0){ $j=mb_strpos($txt,"！"); }
        if($j==0){ $j=mb_strpos($txt,"!"); }
        if($j==0){ $j=mb_strpos($txt,"？"); }
        if($j==0){ $j=mb_strpos($txt,"?"); }
        if($j>0){
            $res= mac_substring($txt,$j-1) . $psearr[$id % $i] . mac_substring($txt,$k-$j,$j);
        }
        else{
            $res= $psearr[$id % $i]. $txt;
        }
    }
    return $res;
}

/**
 * 获取结果Id列表
 */
function getResultIdList($search_word, $search_field, $word_multiple = false)
{
    $search_word = trim($search_word);
    $search_word = str_replace(',,', '', $search_word);
    if (strlen($search_word) == 0 || strlen($search_field) == 0) {
        return [];
    }
    // 如果包含多个关键词，使用递归处理
    if ($word_multiple === true) {
        $id_list = [];
        $search_word_exploded = explode(',', $search_word);
        foreach ($search_word_exploded as $search_word) {
            $id_list += getResultIdList($search_word, $search_field);
        }
        $id_list = array_unique($id_list);
        return $id_list;
    }
    $search_key = md5($search_word . '@' . $search_field);
    $where = ['search_key' => $search_key];
    $search_row = Db::name('vod_search')->where($where)->field("search_result_ids, search_hit_count")->find();
    if (empty($search_row)) {
        $where_vod = [];
        $where_vod[$search_field] = ['LIKE', '%' . $search_word . '%'];
        $id_list = Db::name('Vod')->where($where_vod)->order("vod_id ASC")->column("vod_id");
        $id_list = is_array($id_list) ? $id_list : [];
        SaveAt('vod_search',[
            'search_key'           => $search_key,
            'search_word'          => mb_substr($search_word, 0, 128),
            'search_field'         => mb_substr($search_field, 0, 64),
            'search_hit_count'     => 1,
            'search_last_hit_time' => time(),
            'search_update_time'   => time(),
            'search_result_count'  => count($id_list),
            'search_result_ids'    => join(',', $id_list),
        ]);
    } else {
        $id_list = explode(',', (string)$search_row['search_result_ids']);
        $id_list = array_filter($id_list);
        Db::name('vod_search')->where($where)->update([
            'search_hit_count'     => $search_row['search_hit_count'] + 1,
            'search_last_hit_time' => time(),
        ]);
    }
    $id_list = array_map('intval', $id_list);
    $id_list = empty($id_list) ? [0] : $id_list;
    return $id_list;
}

// xss过滤、长度裁剪
function formatDataBeforeDb($data)
{
    $filter_fields = [
        'vod_name'           => 255,
        'vod_sub'            => 255,
        'vod_en'             => 255,
        'vod_color'          => 6,
        'vod_tag'            => 100,
        'vod_class'          => 255,
        'vod_pic'            => 1024,
        'vod_pic_thumb'      => 1024,
        'vod_pic_slide'      => 1024,
        'vod_pic_screenshot' => 65535,
        'vod_actor'          => 255,
        'vod_director'       => 255,
        'vod_writer'         => 100,
        'vod_behind'         => 100,
        'vod_blurb'          => 255,
        'vod_remarks'        => 100,
        'vod_pubdate'        => 100,
        'vod_serial'         => 20,
        'vod_tv'             => 30,
        'vod_weekday'        => 30,
        'vod_area'           => 20,
        'vod_lang'           => 10,
        'vod_year'           => 10,
        'vod_version'        => 30,
        'vod_state'          => 30,
        'vod_author'         => 60,
        'vod_jumpurl'        => 150,
        'vod_tpl'            => 30,
        'vod_tpl_play'       => 30,
        'vod_tpl_down'       => 30,
        'vod_duration'       => 10,
        'vod_reurl'          => 255,
        'vod_rel_vod'        => 255,
        'vod_rel_art'        => 255,
        'vod_pwd'            => 10,
        'vod_pwd_url'        => 255,
        'vod_pwd_play'       => 10,
        'vod_pwd_play_url'   => 255,
        'vod_pwd_down'       => 10,
        'vod_pwd_down_url'   => 255,
        'vod_play_from'      => 255,
        'vod_play_server'    => 255,
        'vod_play_note'      => 255,
        'vod_down_from'      => 255,
        'vod_down_server'    => 255,
        'vod_down_note'      => 255,
    ];
    foreach ($filter_fields as $field => $length) {
        if (!isset($data[$field])) {
            continue;
        }
        $data[$field] = mac_filter_xss($data[$field]);
        $data[$field] = mb_substr($data[$field], 0, $length);
    }
    return $data;
}

//重建重名缓存表
function createRepeatCache(){
    $prefix = Config::get('database.connections.mysql.prefix');
    Db::execute('DROP TABLE IF EXISTS ' . $prefix . 'vod_repeat');
    Db::execute('CREATE TABLE `' . $prefix . 'vod_repeat` (`id1` int unsigned DEFAULT NULL, `name1` varchar(255) NOT NULL DEFAULT \'\') ENGINE=InnoDB');
    Db::execute('ALTER TABLE `' . $prefix . 'vod_repeat` ADD INDEX `name1` (`name1`(100))');
    Db::execute('INSERT INTO `' . $prefix . 'vod_repeat` (SELECT min(vod_id)as id1,vod_name as name1 FROM ' .
        $prefix . 'vod GROUP BY name1 HAVING COUNT(name1)>1)');
    SetCaChe('vod_repeat_table_created_time',time());
}

/**
 * 检查更新搜索结果
 */
function checkAndUpdateTopResults($vod, $force = false)
{
    static $list;
    $updateTopCount = 50000;
    if (empty($vod['vod_id'])) {
        return;
    }
    if (is_null($list)) {
        $cach_name = 'vod_search_top_result_v2_' . $updateTopCount;
        $list = $force ? [] : GetCache($cach_name);
        if (empty($list)) {
            $list = Db::name('vod_search')->field("search_key, search_word, search_field")->order("search_hit_count DESC, search_last_hit_time DESC")->limit("0," . $updateTopCount)->select();
            $force === false && Cache::set($cach_name, $list, count($list) < ($updateTopCount / 10) ? 3600 : 86400);
            clearOldResult();
        }
    }
    $time_now = time();
    foreach ($list as $row) {
        foreach (explode('|', $row['search_field']) as $field) {
            if (!isset($vod[$field]) || strlen($vod[$field]) == 0) {
                continue;
            }
            if (stripos($vod[$field], $row['search_word']) === false) {
                continue;
            }
            Db::execute("UPDATE `" . config('database.prefix') . 'vod_search' . "` SET 
                    search_update_time='{$time_now}',
                    search_result_count=search_result_count+1,
                    search_result_ids=CONCAT(search_result_ids,',','{$vod['vod_id']}')
                WHERE search_key='{$row['search_key']}'");
        }
    }
}

/**
 * 获取结果缓存的分钟数，后台配置覆盖默认值
 */
function getResultCacheMinutes($config = []) {
    // 默认14天
    $minutes = 20160;
    $config = $config ?: config('maccms');
    if (isset($config['app']['vod_search_optimise_cache_minutes']) && (int)$config['app']['vod_search_optimise_cache_minutes'] > 0) {
        $minutes = (int)$config['app']['vod_search_optimise_cache_minutes'];
    }
    return $minutes;
}

/**
 * 清理老的数据
 */
function clearOldResult($force = false)
{
    // 清理多久前的
    $clear_seconds = getResultCacheMinutes() * 60;
    // 设置间隔，每天最多清理1次
    $cach_name = 'interval_vs_clear_old_v1_' . $clear_seconds;
    $cache_data = GetCache($cach_name);
    if ($force === false && !empty($cache_data)) {
        return;
    }
    Cache::set($cach_name, 1, min($clear_seconds, 86400));
    // vod_actor在采集的时候可提高效率，暂不清理
    $where = [
        'search_field'       => ['neq', 'vod_actor'],
        'search_update_time' => ['lt', time() - $clear_seconds],
    ];
    // 后台强制清理时，都清掉
    if ($force === true) {
        unset($where['search_field']);
    }
    Db::name('vod_search')->where($where)->delete();
}

function mac_jump($url,$sec=0)
{
    echo '<script>setTimeout(function (){location.href="'.$url.'";},'.($sec*1000).');</script><span>'.lang('pause').''.$sec.''.lang('continue_in_second').'  >>>  </span><a href="'.$url.'" >'.lang('browser_jump').'</a><br>';
}

function vod_xml_replace($url)
{
    $array_url = array();
    $arr_ji = explode('#',str_replace('||','//',$url));
    foreach($arr_ji as $key=>$value){
        $urlji = explode('$',$value);
        if( count($urlji) > 1 ){
            $array_url[$key] = $urlji[0].'$'.trim($urlji[1]);
        }else{
            $array_url[$key] = trim($urlji[0]);
        }
    }
    return implode('#',$array_url);
}

/**
 * @param int $start
 * @param int $size
 * @throws \think\db\exception\DataNotFoundException
 * @throws \think\db\exception\DbException
 * @throws \think\db\exception\ModelNotFoundException
 * tag 标签
 *
 */
function Tags($start=0,$size=10){
    $field = 'a.id,a.tag,c.tags,count(c.id) as count';
    $list = Db::name('taglist')->alias('a')->leftJoin('article'.' c ','c.tags LIKE CONCAT(\'%\', a.tag, \'%\')')->field($field)->group('a.id, a.tag')->order(['a.id'=>'desc'])->page($start,$size)->select()->toArray();
    foreach ($list as &$v){
        $v['rand'] = mt_rand(0,10);
    }
    return $list;
}

/**
 * @param int $size  显示数量
 * @param string $aid   属性id
 * @return mixed
 * @throws \think\db\exception\DbException
 * 指定属性文章
 */
function AttrId($size=10,$aid=''){
    $prefix = Config::get('database.connections.mysql.prefix');
    $table = $prefix.'article';
    $table2 = $prefix.'category';
    $sql = "SELECT a.id,a.cid,a.title,a.author,a.attrId,a.description,a.author,a.articleThumbImg,a.createTime,a.updateTime,a.keywords,a.views,b.name,b.temp_archives,b.temp_list,.b.target FROM `$table` as a left join `$table2` as b on a.cid = b.id WHERE FIND_IN_SET($aid,attrId) > 0 and a.status = 1 and a.recycle = 0 ORDER BY a.id DESC LIMIT $size";
    $list = Db::query($sql);
    foreach ($list as &$v){
        if(!$v['articleThumbImg'] || !file_exists($v['articleThumbImg'])){
            $v['articleThumbImg'] = 'images/default.jpg';
        }
        $v['attr'] = AllTable('attribute',[['id','in',$v['attrId']]]);
        $v['month'] = date('m',$v['createTime']);
        $v['day'] = date('d',$v['createTime']);
        $v['createTime'] = date('Y-m-d',$v['createTime']);
        $v['updateTime'] = date('Y-m-d',$v['updateTime']);
        $v['feed'] = Db::name('feedback')->where('aid',$v['id'])->count();
    }
    return $list;
}

function Article($id='',$order='id',$size=12,$start=0){
    $size = request()->param('limit')?request()->param('limit'):$size;
    $start = request()->param('page')?request()->param('page'):$start;
    $where = [['a.status','=',1],['a.recycle','=',0]];
    if($id){
        $where[] = ['a.cid','=',$id];
    }
    $field = 'a.id,a.cid,a.title,a.author,a.attrId,a.articleThumbImg,a.createTime,a.updateTime,a.keywords,a.description,a.views,a.click,b.name,b.target,b.temp_list,b.temp_archives,count(c.id) as feed';
    $list['data'] = Db::name('article')->alias('a')->join('category'.' b ','b.id= a.cid')->leftJoin('feedback'.' c ','c.aid=a.id')->field($field)->where($where)->group('a.id, a.title, b.name')->order(['a.'.$order=>'desc'])->page($start,$size)->select()->toArray();
    if($list){
        $list['__total__'] = CountTable('article',$where,'a');
    }
    foreach ($list['data'] as &$v){
        if(!$v['articleThumbImg'] || !file_exists($v['articleThumbImg'])){
            $v['articleThumbImg'] = 'images/default.jpg';
        }
        $v['attr'] = AllTable('attribute',[['id','in',$v['attrId']]]);
        $v['month'] = date('m',$v['createTime']);
        $v['day'] = date('d',$v['createTime']);
        $v['createTime'] = date('Y-m-d',$v['createTime']);
        $v['updateTime'] = date('Y-m-d',$v['updateTime']);
    }
    return $list;
}

function Feedback($aid='',$cate=0,$size=10,$start=0){
    $where = [['status','=',1]];
    if($aid){
        $where[] = ['aid','=',$aid];
    }
    if($cate){
        $where[] = ['cate','=',$cate];
    }
    $list['data'] = pageTable('feedback',$start,$size,$where);
    $list['__total__'] = CountTable('feedback',$where);
    foreach ($list['data'] as &$v){
        $cate = FindTable('category',[['id','=',$v['cid']]]);
        if($cate){
            $v['temp_archives'] = $cate['temp_archives'];
            $v['temp_list'] = $cate['temp_list'];
            $v['target'] = $cate['target'];
        }
        $v['date'] = date('Y-m-d H:i:s',$v['createTime']);
        $v['createTime'] = date('Y-m-d',$v['createTime']);
    }
    return $list;
}

function Breadcrumb($id=''){
    $tree = GetMenu('category');
    $new = upTree($tree,$id);
    if(!$new){
        $new = [];
    }
    return array_reverse($new);
}

function Detail($id){
    $info = FindTable('article',[['id','=',$id],['status','=',1],['recycle','=',0]]);
    if($info){
        $cate = FindTable('category',[['id','=',$info['cid']]]);
        if($cate){
            $info['temp_archives'] = $cate['temp_archives'];
            $info['temp_list'] = $cate['temp_list'];
        }
        if(!$info['articleThumbImg']){
            $info['articleThumbImg'] = 'images/default.jpg';
        }
        $info['feed'] = CountTable('feedback',['aid'=>$info['id'],['status','=',1]]);
        $info['month'] = date('m',$info['createTime']);
        $info['day'] = date('d',$info['createTime']);
        $info['createTime'] = date('Y-m-d',$info['createTime']);
        $info['updateTime'] = date('Y-m-d',$info['updateTime']);
    }
    return $info;
}

function RandRow($id='',$size=12,$start=0){
    $size = request()->param('limit')?request()->param('limit'):$size;
    $start = request()->param('page')?request()->param('page'):$start;
    $where = [['a.status','=',1],['a.recycle','=',0]];
    if($id){
        $where[] = ['a.cid','=',$id];
    }
    $field = 'a.id,a.cid,a.title,a.author,a.attrId,a.articleThumbImg,a.createTime,a.updateTime,a.keywords,a.description,a.views,a.click,b.name,b.target,b.temp_list,b.temp_archives,count(c.id) as feed';
    $list['data'] = Db::name('article')->alias('a')->join('category'.' b ','b.id= a.cid')->leftJoin('feedback'.' c ','c.aid=a.id')->field($field)->where($where)->group('a.id, a.title, b.name')->orderRand()->page($start,$size)->select()->toArray();
    if($list){
        $list['__total__'] = CountTable('article',$where,'a');
    }
    foreach ($list['data'] as &$v){
        if(!$v['articleThumbImg'] || !file_exists($v['articleThumbImg'])){
            $v['articleThumbImg'] = 'images/default.jpg';
        }
        $v['attr'] = AllTable('attribute',[['id','in',$v['attrId']]]);
        $v['month'] = date('m',$v['createTime']);
        $v['day'] = date('d',$v['createTime']);
        $v['createTime'] = date('Y-m-d',$v['createTime']);
        $v['updateTime'] = date('Y-m-d',$v['updateTime']);
    }
    return $list;
}

/**
 * @param int $table            数据总数
 * @param int $pageSize         每页显示几条数据
 * @param int $showPage         显示几个页码数字 例如显示5个：12345...下一页 尾页 当前1页 共10页
 * @return string
 * 分页函数
 */
function pageBar($table,$pageSize=10,$showPage=5){
    //当前页完整url
    $url = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    //解析url
    $pat = parse_url($url);
    //重组url
    $urls = $pat['scheme'].'://'.$pat['host'].$pat['path'];
    $page = (!empty($_GET['page']))?$_GET['page']:1;
    $total = $table;
    $totalPage = ceil($total / $pageSize);    //获取总页数
    $pageOffset = ($showPage - 1) / 2;    //页码偏移量
    $pageBanner = "<div class='minicms-page'>";
    $start = 1;    //开始页码
    $end = $totalPage;    //结束页码
    if($page > 1){
        $pageBanner .= "<a href=".handlerUrl($urls,array_merge($_GET,['page'=>$start,'limit'=>$pageSize])).">". lang('page_index') ."</a>";
        $pageBanner .= "<a href=".handlerUrl($urls,array_merge($_GET,['page'=>$page-1,'limit'=>$pageSize])).">". lang('page_prev') ."</a>";
    }
    if($totalPage > $showPage){    //当总页数大于显示页数时
        if($page > $pageOffset + 1){    //当当前页大于页码偏移量+1时，也就是当页码为4时 开始页码1替换为...
            $pageBanner .= "...";
        }
        if($page > $pageOffset){        //当当前页大于页码偏移量时 开始页码变为当前页-偏移页码
            $start = $page - $pageOffset;
            $end = $totalPage > $page + $pageOffset ?  $page + $pageOffset : $totalPage;
            //如果当前页数+偏移量大于总页数 那么$end为总页数
        }else{
            $start = 1;
            $end = $totalPage > $showPage ? $showPage : $totalPage;
        }
        if($page + $pageOffset > $totalPage){
            $start = $start - ($page + $pageOffset - $end);
        }
    }
    for($i = $start ; $i <= $end ; $i++){ //循环出页码
        if($i == $page){
            $pageBanner .= "<span class='minincms-curr'>".$i." </span>";
        }else{
            $pageBanner .= "<a href=".handlerUrl($urls,array_merge($_GET,['page'=>$i,'limit'=>$pageSize])).">".$i." </a>";
        }
    }
    if($totalPage > $showPage && $totalPage > $page + $pageOffset){    //当总页数大于页码显示页数时 且总页数大于当前页+偏移量
        $pageBanner .= "...";
    }
    if($page < $totalPage){
        $pageBanner .= "<a href=".handlerUrl($urls,array_merge($_GET,['page'=>$page+1,'limit'=>$pageSize])).">". lang('page_next') ."</a>";
        $pageBanner .= "<a href=".handlerUrl($urls,array_merge($_GET,['page'=>$totalPage,'limit'=>$pageSize])).">". lang('page_tail') ."</a>";
    }
    $pageBanner .= lang('page_current').$page.lang('page');
    $pageBanner .= lang('page_common').$totalPage.lang('page')." </div>";
    return $pageBanner;
}

/**
 * @param string $url  地址
 * @param array $params   参数
 * @return string
 * 重组 url
 */
function handlerUrl($url, array $params): string
{
    if (!$params) {
        return $url;
    }
    $query = http_build_query($params);
    if (stripos($url, '?')) {
        $url = rtrim($url, '&') . '&' . $query;
    } else {
        $url = $url . '?' . $query;
    }
    return $url;
}

/**
 * @param $arr
 * @param int $pid
 * @param array $contrast 对比数据
 * @param string $pids
 * @param string $id
 * @param int $level
 * @return array
 * 获取节点数据
 */
function GetNode($arr,$pid=0,$contrast=[],$pids = 'pid',$id = 'id',$level=0){
    //初始化儿子
    $child = '';
    $data = array();
    //循环所有数据找$id的儿子
    foreach ($arr as $key => $v) {
        //找到儿子了
        if ($v[$pids] == $pid) {
            //先去掉自己，自己不可能是自己的儿孙
            unset($arr[$key]);
            $son = GetNode($arr, $v[$id],$contrast,$pids,$id,$level+1);
            //组装数据
            $child = [
                'id'=>$v['id'],
                'title'=>$v['title'],
                'href'=>$v['href'],
                'field'=>'auth[]',
                'spread'=>0,
                'level'=>$level,
                'children'=>$son
            ];
            if(in_array($v['id'],$contrast)){
                if($son){
                    $child['checked'] = false;
                }else{
                    $child['checked'] = true;
                }
            }
            //压入数组
            array_push($data,$child);
        }
    }
    return $data;
}
/**
 * @param $data
 * @return array
 * 重组 控制器和方法
 */
function arrMac($data){
    $arr = [];
    foreach($data as $k=>$v){
        $model = $v['module'];
        $controller = strtolower($v['controller']);
        $action = $v['action'];
        $mc = $model.'/'.$controller;
        $router = $model.'/'.$controller.'/'.$action;
        if(in_array($mc,$arr)){
            array_push($arr,$router);
        }else{
            array_push($arr,$mc,$router);
        }
    }
    return $arr;
}

/**
 * @param $table
 * @param $data
 * @return array
 * @throws \think\db\exception\DataNotFoundException
 * @throws \think\db\exception\DbException
 * @throws \think\db\exception\ModelNotFoundException
 * 更新字段
 */
function FieldUpdate($table,$data){
    $nar = Db::name($table)->where('id',$data['id'])->find();
    $field = $data['field'];
    if($nar){
        Db::name($table)->save(['id' => $data['id'], $field => $data['value']]);
        $msg = array('code'=>200,'msg'=>lang('update_success'));
    }else{
        $msg = array('code'=>300,'msg'=>lang('update_failed'));
    }
    return $msg;
}
/**
 * @param $table
 * @param $field
 * @param $id
 * @param int $value
 * @return array
 * @throws \think\db\exception\DataNotFoundException
 * @throws \think\db\exception\DbException
 * @throws \think\db\exception\ModelNotFoundException
 * 更新状态
 */
function SwitchUp($table,$field,$id,$value=0){
    $arr = Db::name($table)->where('id',$id)->find();
    if($arr){
        if($arr[$field] == 1){
            $val = $value;
        }else{
            $val = 1;
        }
        Db::name($table)->save(['id' => $id, $field => $val]);
        $msg = array('code'=>200,'msg'=>lang('update_status'));
    }else{
        $msg = array('code'=>300,'msg'=>lang('data_error'));
    }
    return $msg;
}
/**
 * @param array $data  参数
 * @param string $key      密钥
 * @return string
 * 生成签名
 */
function signMd5($data, $key)
{
    ksort($data);
    $sign_str = '';
    foreach ($data as $pk => $pv) {
        $sign_str .= "{$pk}={$pv}&";
    }
    $sign_str .= "key={$key}";
    $sign = md5($sign_str);
    return $sign;
}
/**
 * @param int $type
 * @param bool $adv
 * @return mixed
 * 获取客户ip
 */
function get_client_ip($type = 0,$adv=false) {
    $type       =  $type ? 1 : 0;
    static $ip  =   NULL;
    if ($ip !== NULL) return $ip[$type];
    if($adv){
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $arr    =   explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $pos    =   array_search('unknown',$arr);
            if(false !== $pos) unset($arr[$pos]);
            $ip     =   trim($arr[0]);
        }elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip     =   $_SERVER['HTTP_CLIENT_IP'];
        }elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ip     =   $_SERVER['REMOTE_ADDR'];
        }
    }elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip     =   $_SERVER['REMOTE_ADDR'];
    }
    // IP地址合法验证
    $long = sprintf("%u",ip2long($ip));
    $ip   = $long ? array($ip, $long) : array('0.0.0.0', 0);
    return $ip[$type];
}
/**
 * @param string $url       远程地址
 * @param array $param      需要提交的参数
 * @param string $filename  上传文件名
 * @param int $wait         等待响应时间
 * @param string $method         等待响应时间
 * @return array|bool
 * 请求-提交数据  使用post方式提交
 */
function FCurl_post($url, $param = array(), $filename = '', $wait = 30,$method = 'POST')
{
    if (!is_array($param)) {
        return false;
    }

    if($filename){
        if($_FILES[$filename]['name']){
            $param['file'] = new \CURLFile($_FILES[$filename]['tmp_name'], $_FILES[$filename]['type'], $_FILES[$filename]['name']);
        }
    }

    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "gzip",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => $wait,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_POSTFIELDS => $param, // 直接将参数数组作为POST数据
        CURLOPT_HTTPHEADER => array(
            "cache-control: no-cache",
        ),
    ));

    if (stripos($url, "https") === 0) {
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    }
    //echo $curl;
    $response = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $err = curl_error($curl);

    curl_close($curl);


    return [
        'response_code' => $httpCode,
        'output' => $response,
        'error' => $err,
    ];
}

function GetCurl($url,$param=[]){
    $param = http_build_query($param);
    $url = $url.'?'.$param;
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false, // 测试时关闭SSL验证
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_FOLLOWLOCATION => true, // 跟随重定向
        CURLOPT_HTTPHEADER => [
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
            'Referer: http://www.kuwo.cn/',
            'Accept: application/json, text/plain, */*',
        ]
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);

    curl_close($ch);


    if ($httpCode == 200) {
        if (!empty($response)) {
            return [
                'response_code' => $httpCode,
                'output' => $response,
                'error' => $err,
            ];
        } else {
            echo "请求成功但返回内容为空，请检查参数或接口限制。";
        }
    } else {
        echo "请求失败，HTTP状态码：{$httpCode}";
    }
}

/**
 * @param $originalUrl
 * @return string
 * 酷我播放地址转换
 */
function transformUrl($originalUrl) {
    $parts = parse_url($originalUrl);

    if (!isset($parts['host'])) {
        return $originalUrl; // 如果URL中没有host部分，直接返回原URL
    }

    $host = $parts['host'];
    $pos = strpos($host, '.');

    if ($pos !== false) {
        $newHost = substr_replace($host, '-', $pos, 1);
    } else {
        $newHost = $host; // 没有点则无需替换
    }

    // 重建URL
    $newUrl = $parts['scheme'] . '://' . $newHost;

    if (isset($parts['port'])) {
        $newUrl .= ':' . $parts['port'];
    }
    if (isset($parts['path'])) {
        $newUrl .= $parts['path'];
    }
    if (isset($parts['query'])) {
        //$newUrl .= '?' . $parts['query'];
    }
    if (isset($parts['fragment'])) {
        //$newUrl .= '#' . $parts['fragment'];
    }
    return $newUrl;
}

/**
 * @param $originalUrl
 * @return string
 * 酷我图片地址转换
 */
function ImgFormUrl($originalUrl) {
    $parsed = parse_url($originalUrl);
    if (!isset($parsed['host'])) return $originalUrl;

    // 分割主机名
    $hostParts = explode('.', $parsed['host']);
    $length = count($hostParts);

    // 检查是否为 [任意前缀].kuwo.cn 结构
    if ($length >= 3 && $hostParts[$length-2] === 'kuwo' && $hostParts[$length-1] === 'cn') {
        // 保留第一个前缀和最后两个部分（kuwo.cn）
        $newHost = $hostParts[0] . '.kuwo.cn';
        $parsed['host'] = $newHost;
    }

    // 重建URL
    $scheme = isset($parsed['scheme']) ? $parsed['scheme'] . '://' : '';
    $host = $parsed['host'];
    $path = isset($parsed['path']) ? $parsed['path'] : '';
    $query = isset($parsed['query']) ? '?' . $parsed['query'] : '';
    $fragment = isset($parsed['fragment']) ? '#' . $parsed['fragment'] : '';

    return "$scheme$host$path$query$fragment";
}
/**
 * 将秒数（含小数）格式化为 MM:SS.cc 时间格式
 * @param float $totalSeconds 总秒数（如 249.15 代表 4分9秒15百分秒）
 * @return string 格式化后的时间字符串（如 "04:09.15"）
 * @throws InvalidArgumentException 输入非数字或负数时抛出异常
 */
function formatTime($totalSeconds) {
    // 参数校验：确保输入合法
    if (!is_numeric($totalSeconds)) {
        throw new InvalidArgumentException("输入必须为数值");
    }
    if ($totalSeconds < 0) {
        throw new InvalidArgumentException("时间不能为负数");
    }

    // 将总秒数转换为整型百分秒（四舍五入到最近的百分秒）
    $totalCentiseconds = (int)round($totalSeconds * 100);

    // 分解为分钟、剩余百分秒、秒和百分秒
    $minutes = (int)($totalCentiseconds / 6000);          // 1分钟 = 6000百分秒
    $remainingCentiseconds = $totalCentiseconds % 6000;   // 剩余不足1分钟的部分
    $seconds = (int)($remainingCentiseconds / 100);       // 转换为整秒
    $centiseconds = $remainingCentiseconds % 100;         // 剩余百分秒

    // 格式化为两位数补零的字符串
    return sprintf(
        "%02d:%02d.%03d",
        $minutes,
        $seconds,
        $centiseconds
    );
}
/**
 * @param string $url   要需下载的文件地址
 * @param string $save_dir  保存目录
 * @param string $filename  保存文件名
 * @param int $type   下载类型
 * @return array|bool
 * 下载远程文件
 */
function DownloadFile($url, $save_dir = '', $filename = '', $type = 0) {
    $ext = array('gif','jpg','jpeg','bmp','png','webp','zip','gz');
    if (trim($url) == '') {
        return false;
    }
    if (trim($save_dir) == '') {
        $save_dir = './';
    }
    if (0 !== strrpos($save_dir, '/')) {
        $save_dir.= '/';
    }
    //创建保存目录
    if (!file_exists($save_dir) && !mkdir($save_dir, 0777, true)) {
        return false;
    }
    //获取远程文件所采用的方法
    if ($type) {
        $ch = curl_init();
        $timeout = 100;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // 跟随重定向
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // 禁用SSL证书验证
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        if (stripos($url, "https") === 0) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }
        $content = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);
        if($httpCode !== 200){
            return [
                'code'=>$httpCode,
                'content'=>$content,
                'error'=>$err,
            ];
        }
    } else {
        ob_start();
        $dofile = @readfile($url);
        if($dofile ===false){
            return array(
                'code'=>300,
                'msg'=>lang('remote_error'),
            );
        }
        $content = ob_get_contents();
        ob_end_clean();
    }
    $size = strlen($content);
    //文件大小
    $info = pathinfo($url);
    $weurl = parse_url($url);
    if(isset($weurl['host']) && $weurl['host'] == 'mmbiz.qpic.cn'){
        if(isset($weurl['query'])){
            parse_str($weurl['query'],$parr);
            $exp = $parr['wx_fmt'];
        }else{
            $exp = 'jpg';
        }
        $info['extension'] = $exp;
        $info['basename'] = 'WeChat_'.time().'.'.$info['extension'];
    }
    if(isset($info['extension']) && in_array($info['extension'],$ext)){
        if($filename){
            $fiex = '.'.$info['extension'];
            $fp2 = @fopen($save_dir . $filename.$fiex, 'a');
            $newname = $filename.$fiex;
        }else{
            $fp2 = @fopen($save_dir . $info['basename'], 'a');
            $newname = $info['basename'];
        }
        fwrite($fp2, $content);
        fclose($fp2);
        unset($content, $url);
        return array(
            'code'=>200,
            'file_name' => $newname,
            'save_path' => $save_dir . $newname
        );
    }else{
        return array(
            'code'=>300,
            'file_name' => $url,
            'save_path' => $url
        );
    }
}

/**
 * @param array $urls
 * @param string $saveDir
 * @param int $concurrency
 * @return array
 * @throws Exception
 * 批量下载文件
 */
function BatchDownLoadFiles(array $urls, string $saveDir = './downloads/', int $concurrency = 5): array
{
    // 初始化参数
    $ext = ['gif', 'jpg', 'jpeg', 'bmp', 'png', 'webp', 'zip', 'gz'];
    $results = [];

    // 创建保存目录
    if (!file_exists($saveDir) && !mkdir($saveDir, 0777, true)) {
        throw new Exception("无法创建目录: $saveDir");
    }

    $mh = curl_multi_init();
    $handles = [];
    $active = null;

    // 初始化批处理句柄
    foreach ($urls as $i => $url) {
        $url = trim($url);
        if (empty($url)) {
            $results[$i] = ['code' => 400, 'error' => 'URL为空'];
            continue;
        }

        // 解析文件名
        $parsed = parse_url($url);
        $path = $parsed['path'] ?? '';
        $query = $parsed['query'] ?? '';

        // 处理微信特殊格式
        if (isset($parsed['host']) && $parsed['host'] === 'mmbiz.qpic.cn') {
            parse_str($query, $queryParams);
            $extType = $queryParams['wx_fmt'] ?? 'jpg';
            $filename = 'WeChat_' . md5($url) . '.' . $extType;
        } else {
            $filename = basename($path) ?: md5($url) . '.dat';
        }

        $savePath = rtrim($saveDir, '/') . '/' . $filename;

        // 跳过已存在文件
        if (file_exists($savePath)) {
            $results['imgArr'][$i] = '/'.$savePath;
            $results[$i] = ['code' => 200, 'file' => $savePath, 'status' => 'exists'];
            continue;
        }

        // 验证文件扩展名
        $fileExt = pathinfo($savePath, PATHINFO_EXTENSION);
        if (!in_array(strtolower($fileExt), $ext)) {
            $results[$i] = ['code' => 403, 'error' => '文件类型禁止下载'];
            continue;
        }

        // 初始化CURL
        $fp = fopen($savePath, 'w+');
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_FILE => $fp,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36'
        ]);

        curl_multi_add_handle($mh, $ch);
        $handles[$i] = ['ch' => $ch, 'fp' => $fp, 'path' => $savePath];
    }

    // 执行并发下载
    do {
        $status = curl_multi_exec($mh, $active);
        if ($active) {
            curl_multi_select($mh, 0.1);
        }
    } while ($active && $status == CURLM_OK);

    // 处理结果
    foreach ($handles as $i => $handle) {
        $httpCode = curl_getinfo($handle['ch'], CURLINFO_HTTP_CODE);
        $error = curl_error($handle['ch']);
        fclose($handle['fp']);

        if ($httpCode === 200 && filesize($handle['path']) > 0) {
            //压缩图片
            ImgCompress($handle['path']);
            $results['imgArr'][$i] = '/'.$handle['path'];
            $results[$i] = ['code' => 200, 'file' => $handle['path'], 'size' => filesize($handle['path'])];
        } else {
            @unlink($handle['path']);
            $results[$i] = [
                'code' => $httpCode ?: 500,
                'error' => $error ?: '下载失败',
                'url' => $urls[$i]
            ];
        }

        curl_multi_remove_handle($mh, $handle['ch']);
        curl_close($handle['ch']);
    }

    curl_multi_close($mh);
    return $results;
}
/**
 * @param $data
 * @param $url
 * 节点权限验证
 */
function AuthNode($data,$url){
    //免签验证
    $skip = [
        'admin/',
        'admin/welcome',
    ];
    if($data){
        $roleId = implode(',',json_decode($data,true));
        $role = AllTable('role',[['status','=',1],['id','in',$roleId]]);
        $authId = [];
        foreach ($role as $v){
            if($v['auth']){
                $json = json_decode($v['auth']);
                foreach($json as $k=>$item){
                    array_push($authId,$item);
                }
            }
        }
        $aid = implode(',',$authId);
        $auth = AllTable('node',[['isOpen','=',1],['id','in',$aid]]);
        $node = [];
        foreach ($auth as $val){
            array_push($node,$val['nodeUrl']);
        }
        if(!in_array($url,$skip)){
            if(!in_array($url,$node)){
                $msg = ['code'=>300,'msg'=>lang('authority_error')];
                echo json_encode($msg,JSON_UNESCAPED_UNICODE);
                die;
            }
        }
    }else{
        $msg = ['code'=>300,'msg'=>lang('authority_error')];
        echo json_encode($msg,JSON_UNESCAPED_UNICODE);
        die;
    }
}
/**
 * @param string $src  源文件地址
 * @param int $percent 图片质量
 * GD库 图片压缩
 */
function ImgCompress($src,$percent=1){
    //配置内存大小
    ini_set('memory_limit', '256M');
    //typearr存的是一些图片的格式，用作下文getimagesize()获取图片信息中的图片格式进行比对
    $typearr = array("gif", "jpeg", "png", "swf", "psd", "bmp",'17'=>'webp');
    list($width, $height, $type, $attr) = getimagesize($src);		//获取图片信息
    /**-----------------------------------------------------------------------------------------------------*/
    //对imagecreatefrom 系列函数进行拼接从文件或 URL 载入一幅图像，成功返回图像资源，失败则返回一个空字符串
    $func = "imagecreatefrom" . $typearr[$type - 1];
    $image = @$func($src);
    /**-----------------------------------------------------------------------------------------------------*/
    //创建一个新的真彩色图像
    $new_width = intval($width * $percent);
    $new_height = intval($height * $percent);
    $image_thump = imagecreatetruecolor($new_width, $new_height);
    /**-----------------------------------------------------------------------------------------------------*/
    // 处理透明背景图片变成黑色的问题
    if(strtolower($typearr[$type - 1])=='png'){
        //imageantialias($image_thump, true);
        $color = imagecolorallocatealpha($image_thump, 0, 0, 0, 127);
        imagecolortransparent($image_thump, $color);
        imagefill($image_thump, 0, 0, $color);
        imagesavealpha($image_thump,true);
    }
    /**----------------------------------------------------------------------------------------------------*/
    //图像处理
    imagecopyresampled($image_thump, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
    /**--------------------------------------------------------------------------------------------------------------------*/
    //拼接一个image系列函数（将图片输出到浏览器或文件）
    $imgfunc= "image" . $typearr[$type - 1];
    //原图覆盖
    $imgfunc($image_thump,$src);
    //销毁图像资源
    imagedestroy($image);
    //销毁给定的变量
    unset($image);
}


/**
 *
 *  $src = "hyy.jpg";
 *  $dir
 *  $newwid = 640;
 *  $newhei = 480;
 */
/**
 * @param string $src
 * @param string $saveDir
 * @param int $newWid
 * @param int $newHei
 * GD库 压缩图片 缩略图
 */
function imgZip($src,$saveDir='', $newWid=350, $newHei=350)
{
    if($saveDir){
        $saveDir = 'uploads/'.$saveDir.'/'.date('Ym').'/';
        if(!file_exists($saveDir)) {
            mkdir($saveDir,0777,true);
        }
    }else{
        $saveDir = 'uploads/thumb/'.date('Ym').'/';
        if(!file_exists($saveDir)) {
            mkdir($saveDir,0777,true);
        }
    }
    $imgInfo = getimagesize($src);

    $imgType = image_type_to_extension($imgInfo[2], false);

    $fun = "imagecreatefrom{$imgType}";
    //声明图片 打开图片 在内存中
    $image = $fun($src);
    // 获取原始图片的宽高
    $sourceWidth = imagesx($image);
    $sourceHeight = imagesy($image);
    // 计算缩放比例
    $scale = min($newWid / $sourceWidth, $newHei / $sourceHeight);
    // 计算缩略图的宽高
    $thumbnailWidth = $sourceWidth * $scale;
    $thumbnailHeight = $sourceHeight * $scale;
    //在内存中建立一张图片
    $images2 = imagecreatetruecolor($thumbnailWidth, $thumbnailHeight); //建立一个500*320的图片
    /**-----------------------------------------------------------------------------------------------------*/
    // 处理透明背景图片变成黑色的问题
    if(strtolower($imgType)=='png'){
        imageantialias($images2, true);
        $color = imagecolorallocate($images2, 255, 255, 255);
        imagecolortransparent($images2, $color);
        imagefill($images2, 0, 0, $color);
    }
    /**----------------------------------------------------------------------------------------------------*/
    //将原图复制到新建图片中
    //imagecopyresampled($dst_image, $src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h)

    imagecopyresampled($images2, $image, 0, 0, 0, 0, $thumbnailWidth, $thumbnailHeight, $imgInfo[0], $imgInfo[1]);
    //销毁原始图片
    imagedestroy($image);
    //直接输出图片文件
    $imgfunc = 'image' . $imgType;
    /*header("Content-type: " . $imgInfo['mime']);
    $imgfunc($images2);*/
    //保存图片 到新文件
    $saveImg = $saveDir.date('Ymd').uniqid().'.'.$imgType;
    $imgfunc($images2,$saveImg ); //10代码输出图片的质量 0-100 100质量最高
    //销毁
    imagedestroy($images2);
    return $saveImg;
}

/**
 * @param $data
 * @param int $pid
 * @param string $pids
 * @param string $id
 * @param int $level
 * @return array
 * 递归返回所有上级菜单
 */
function upTree($data,$pid=0,$pids = 'pid',$id='id',$level=0){
    static $tree = array();
    foreach($data as $k=>$v){
        if($v[$id] == $pid){
            $v['lv'] = $level;
            $tree[] = $v;
            upTree($data,$v[$pids],$pids,$id,$level+1);
        }
    }
    return $tree;
}
/**
 * @param $arr
 * @param int $pid
 * @param string $pids
 * @param string $id
 * @param int $level
 * @return array
 * 递归返回多维数据
 */
function MdaTree($arr,$pid=0,$pids = 'pid',$id='id',$level=0){
    $tree = array();
    foreach($arr as $k=>$v){
        if($v[$pids] == $pid){
            $v['lv'] = $level;
            $tree[$k] = $v;
            $tree[$k]['son'] = MdaTree($arr,$v[$id],$pids,$id,$level+1);
        }
    }
    return $tree;
}
/**
 * @param $table
 * @param int $pid
 * @param string $p_id
 * @param string $id
 * @param int $level
 * @return array
 * 返回分类下的所有子分类 （一级数组）
 */
function ArrTree($table,$pid=0,$p_id = 'pid',$id = 'id',$level=0)
{
    $arr = Db::name($table)->select()->toArray();
    static $tree = array();
    foreach($arr as $v){
        if($v[$p_id] == $pid){
            $v['lv'] = $level;
            $tree[$v['id']] = $v;
            ArrTree($table,$v[$id],$p_id,$id,$level+1);
        }
    }
    return $tree;
}

/**
 * @param $table
 * @param int $pid
 * @param string $pids
 * @param string $id
 * @param int $level
 * @return array
 * @throws \think\db\exception\DataNotFoundException
 * @throws \think\db\exception\DbException
 * @throws \think\db\exception\ModelNotFoundException
 * 递归返回所有下级分类
 */
function MoreTree($table,$pid=0,$pids = 'pid',$id = 'id',$level=0)
{
    $arr = Db::name($table)->select()->toArray();
    $tree = array();
    foreach($arr as $k=>$v){
        if($v[$pids] == $pid){
            $v['lv'] = $level;
            $tree[$k] = $v;
            $tree[$k]['son'] = MdaTree($arr,$v[$id],$pids,$id,$level+1);
        }
    }
    return $tree;
}

/**
 * @param $arr
 * @return array
 * 多维数组转成二维数组
 */
function oneArr($arr){
    $tree = [];
    foreach($arr as $v){
        $nar = array(
            'id' => $v['id'],
            'rid' => $v['rid'],
            'aid' => $v['aid'],
            'cid' => $v['cid'],
            'username' => $v['username'],
            'article' => $v['article'],
            'email' => $v['email'],
            'ip' => $v['ip'],
            'msg' => $v['msg'],
            'good' => $v['good'],
            'bad' => $v['bad'],
            'cate' => $v['cate'],
            'status' => $v['status'],
            'createTime' => $v['createTime'],
            'lv' => $v['lv']
        );
        $tree[] = $nar;
        if($v['son']){
            $tree = array_merge($tree,oneArr($v['son']));
        }
    }
    return $tree;
}

function ubb($Text) {
    $Text=trim($Text);
    $Text=preg_replace("/\[Addoil\]/is","<img style='width:22px;' src='/ubb/Addoil.png' />",$Text);
    $Text=preg_replace("/\[Applause\]/is","<img style='width:22px;' src='/ubb/Applause.png' />",$Text);
    $Text=preg_replace("/\[Badlaugh\]/is","<img style='width:22px;' src='/ubb/Badlaugh.png' />",$Text);
    $Text=preg_replace("/\[Bomb\]/is","<img style='width:22px;' src='/ubb/Bomb.png' />",$Text);
    $Text=preg_replace("/\[Coffee\]/is","<img style='width:22px;' src='/ubb/Coffee.png' />",$Text);
    $Text=preg_replace("/\[Fabulous\]/is","<img style='width:22px;' src='/ubb/Fabulous.png' />",$Text);
    $Text=preg_replace("/\[Facepalm\]/is","<img style='width:22px;' src='/ubb/Facepalm.png' />",$Text);
    $Text=preg_replace("/\[Feces\]/is","<img style='width:22px;' src='/ubb/Feces.png' />",$Text);
    $Text=preg_replace("/\[Frown\]/is","<img style='width:22px;' src='/ubb/Frown.png' />",$Text);
    $Text=preg_replace("/\[Heyha\]/is","<img style='width:22px;' src='/ubb/Heyha.png' />",$Text);
    $Text=preg_replace("/\[Insidious\]/is","<img style='width:22px;' src='/ubb/Insidious.png' />",$Text);
    $Text=preg_replace("/\[KeepFighting\]/is","<img style='width:22px;' src='/ubb/KeepFighting.png' />",$Text);
    $Text=preg_replace("/\[NoProb\]/is","<img style='width:22px;' src='/ubb/NoProb.png' />",$Text);
    $Text=preg_replace("/\[PigHead\]/is","<img style='width:22px;' src='/ubb/PigHead.png' />",$Text);
    $Text=preg_replace("/\[Shocked\]/is","<img style='width:22px;' src='/ubb/Shocked.png' />",$Text);
    $Text=preg_replace("/\[Sinistersmile\]/is","<img style='width:22px;' src='/ubb/Sinistersmile.png' />",$Text);
    $Text=preg_replace("/\[Slap\]/is","<img style='width:22px;' src='/ubb/Slap.png' />",$Text);
    $Text=preg_replace("/\[Social\]/is","<img style='width:22px;' src='/ubb/Social.png' />",$Text);
    $Text=preg_replace("/\[Sweat\]/is","<img style='width:22px;' src='/ubb/Sweat.png' />",$Text);
    $Text=preg_replace("/\[Tolaugh\]/is","<img style='width:22px;' src='/ubb/Tolaugh.png' />",$Text);
    $Text=preg_replace("/\[Watermelon\]/is","<img style='width:22px;' src='/ubb/Watermelon.png' />",$Text);
    $Text=preg_replace("/\[Witty\]/is","<img style='width:22px;' src='/ubb/Witty.png' />",$Text);
    $Text=preg_replace("/\[Wow\]/is","<img style='width:22px;' src='/ubb/Wow.png' />",$Text);
    $Text=preg_replace("/\[Yeah\]/is","<img style='width:22px;' src='/ubb/Yeah.png' />",$Text);
    $Text=preg_replace("/\[Yellowdog\]/is","<img style='width:22px;' src='/ubb/Yellowdog.png' />",$Text);

    return $Text;
}

/**
 * @param string $sourcePath  源图地址
 * @param string $destinationPath 保存地址
 * @param int $quality 压缩比例 0-100
 * @throws ImagickException
 * Imagick 压缩方式
 */
function compressImage($sourcePath, $destinationPath, $quality) {
    $image = new Imagick($sourcePath);
    $image->setImageCompressionQuality($quality);
    $image->writeImage($destinationPath);
    $image->destroy();
}
/**
 * @throws \think\db\exception\DataNotFoundException
 * @throws \think\db\exception\DbException
 * @throws \think\db\exception\ModelNotFoundException
 * 设置 api 导航菜单缓存
 */
function navApi(){
    $cate = GetMenu('category',[['isShow','=',1]],'id',['orderSort'=>'desc']);
    $nav = MdaTree($cate);
    $type = ArrTree('category');
    SetCaChe('NavMenu',$nav);
    SetCaChe('type_list',$type);
}

/**
 * @param string $table
 * @param string $table2
 * @throws \think\db\exception\DataNotFoundException
 * @throws \think\db\exception\DbException
 * @throws \think\db\exception\ModelNotFoundException
 * 上网导航缓存
 */
function Navigation($table='classify',$table2='navigation'){
    $data = AllTable($table,['status'=>1],['orderBy'=>'desc']);
    foreach ($data as $k=>$v){
        $data[$k]['nav'] = AllTables($table2,[['cid','=',$v['id']],['is_show','=',1]],$v['number'],['orderBy'=>'desc']);
    }
    SetCaChe('navigation',$data);
}
/**
 * @throws \think\db\exception\DataNotFoundException
 * @throws \think\db\exception\DbException
 * @throws \think\db\exception\ModelNotFoundException
 * 获取区域
 */
function AreaList(){
    $city = GetMenu('area');
    SetCaChe('area',$city);
}

/**
 * @param string $dir
 * @throws \think\db\exception\DataNotFoundException
 * @throws \think\db\exception\DbException
 * @throws \think\db\exception\ModelNotFoundException
 * 更新配置文件
 */
function putFile($dir = 'web.php'){
    $data = GetMenu('config');
    $arr = [];
    foreach($data as $v){
        $arr[$v['sys_variable']] = $v['sys_content'];
    }
    $path = config_path().$dir;
    $str = "<?php \r\n".'return '.var_export($arr,true).';';
    file_put_contents($path,$str);
}

/**
 * @param string $dir
 * @param $content
 */
function putConfig($content,$dir='bind.php'){
    $path = config_path().$dir;
    $str = "<?php \r\n".'return '.var_export($content,true).';';
    file_put_contents($path,$str);
}
/**
 * @param string $dir
 * @throws \think\db\exception\DataNotFoundException
 * @throws \think\db\exception\DbException
 * @throws \think\db\exception\ModelNotFoundException
 * 生成站群配置文件
 */
function putDomain($dir = 'domain.php'){
    $data = AllTable('domain');
    $arr = [];
    foreach ($data as $item){
        $domain = $item['web_domain'];
        unset($item['web_domain']);
        $arr[$domain] = $item;
    }
    $path = config_path().$dir;
    $str = "<?php \r\n".'return '.var_export($arr,true).';';
    file_put_contents($path,$str);
}

/**
 * @param $from
 * @param $url
 * @return false|string[]
 * 播放数组与播放地址交换
 */
function combinePlayData($from, $url) {
    return array_combine(explode('$$$', $from), explode('$$$', $url));
}

/**
 * @param $table
 * @param $data
 * @return int|string
 * 保存数据
 */
function SaveAt($table,$data){
    return Db::name($table)->save($data);
}

/**
 * @param $table
 * @param $data
 * @return int|string
 * 保存数据并返回自增 id
 */
function saveId($table,$data){
    return Db::name($table)->insertGetId($data);
}

/**
 * @param $table
 * @param $data
 * $data = [
 *      ['foo' => 'bar', 'bar' => 'foo'],
 *      ['foo' => 'bar1', 'bar' => 'foo1'],
 *      ['foo' => 'bar2', 'bar' => 'foo2']
 *   ];
 * 添加多条数据
 */
function batchSave($table,$data){
    Db::name($table)->insertAll($data);
}

/**
 * @param $name
 * @param int $thumb  生成缩略图（0-1）
 * @param string $path  保存路径
 * @param int $newWid  生成缩略图宽度
 * @param int $newHei  生成缩略图高度
 * @return array
 * 图片上传
 */
function UploadImg($name,$thumb = 1,$path='',$newWid=350, $newHei=350){
    error_reporting(E_WARNING);
    //允许上传的文件类型
    $fileType = Cfg('img_type');
    $imgArr = explode(',',$fileType);
    //允许上传的文件大小
    $fileSize = (1024 * 1024) * Cfg('img_size');
    $saveName = [];
    //自定目录
    if($path){
        $paths = $path.'/'.date('Ym');
    }else{
        $paths = 'images/'.date('Ym');
    }
    if (isset($_FILES[$name]['name']) && is_array($_FILES[$name]['name'])) {
        if (!empty($_FILES[$name]['name'][0])) {
            // 获取上传文件 多文件上传到止报错
            $files = request()->file($name);
            foreach($files as $k=>$file){
                //获取文件后缀
                $ext = $file->extension();
                //判断文件上传类型
                if(!in_array($ext,$imgArr)){
                    $msg = [
                        'code'=>200,
                        'ident'=>1,
                        'msg'=>lang('upload_type_error').$fileType,
                        'result'=>'',
                    ];
                    return $msg;
                }
                //获取文件大小
                $Size = $file->getSize();
                //判断文件上传大小
                if($Size > $fileSize){
                    $msg = [
                        'code'=>200,
                        'ident'=>1,
                        'msg'=>lang('upload_size_error').$fileSize,
                        'result'=>'',
                    ];
                    return $msg;
                }
                $saveName[$k]['img'] = \think\facade\Filesystem::disk('public')->putFile( $paths, $file,'uniqid');
                $saveName[$k]['img'] = 'uploads/'.$saveName[$k]['img'];
                //获取文件存放路径
                $saveName[$k]['code'] = 200;
                $saveName[$k]['msg'] = lang('upload_success');
                //压缩图片
                if($ext != 'ico'){
                    ImgCompress($saveName[$k]['img'],1);
                }
                //生成缩略图
                if($thumb){
                    $saveName[$k]['thumb'] = imgZip($saveName[$k]['img'],$path,$newWid,$newHei);
                }
            }
            $msg = [
                'code'=>200,
                'ident'=>2,
                'msg'=>lang('upload_success'),
                'result'=>$saveName,
            ];
            return $msg;
        }else{
            $msg = [
                'code'=>300,
                'msg'=>lang('upload_select'),
                'result'=>'',
            ];
            return $msg;
        }
    } else {
        if(isset($_FILES[$name]) && $_FILES[$name]['error'] === UPLOAD_ERR_OK) {
            // 获取上传文件
            $files = request()->file($name);
            $saveName['img'] = \think\facade\Filesystem::disk('public')->putFile( $paths, $files,'uniqid');
            $saveName['img'] = 'uploads/'.$saveName['img'];
            //获取文件后缀
            $ext = $files->extension();
            //判断文件上传类型
            if(!in_array($ext,$imgArr)){
                $msg = [
                    'code'=>200,
                    'ident'=>1,
                    'msg'=>lang('upload_type_error').$fileType,
                    'result'=>'',
                ];
                return $msg;
            }
            //获取文件大小
            $Size = $files->getSize();
            //判断文件上传大小
            if($Size > $fileSize){
                $msg = [
                    'code'=>200,
                    'ident'=>1,
                    'msg'=>lang('upload_size_error').$fileSize,
                    'result'=>'',
                ];
                return $msg;
            }
            //压缩图片
            if($ext != 'ico'){
                ImgCompress($saveName['img'],1);
            }
            //生成缩略图
            if($thumb){
                $saveName['thumb'] = imgZip($saveName['img'],$path,$newWid,$newHei);
            }
            $msg = [
                'code'=>200,
                'ident'=>2,
                'msg'=>lang('upload_success'),
                'result'=>$saveName,
            ];
            return $msg;
        }else{
            $msg = [
                'code'=>300,
                'msg'=>lang('upload_select'),
                'result'=>'',
            ];
            return $msg;
        }
    }
}

/**
 * @param $name
 * @return array
 * 上传文件
 */
function uploadFile($name){
    if(isset($_FILES[$name]) && $_FILES[$name]['error'] === UPLOAD_ERR_OK) {
        // 获取上传文件
        $files = request()->file($name);
        //获取文件后缀
        $ext = $files->extension();
        $fileType = Cfg('file_type');
        $imgArr = explode(',',$fileType);
        //判断文件上传类型
        if(!in_array($ext,$imgArr)){
            $msg = [
                'code'=>200,
                'ident'=>1,
                'msg'=>lang('upload_type_error').$fileType,
                'result'=>'',
            ];
            return $msg;
        }
        //获取文件大小
        $Size = $files->getSize();
        $fileSize = (1024 * 1024) * Cfg('file_size');
        //判断文件上传大小
        if($Size > $fileSize){
            $msg = [
                'code'=>200,
                'ident'=>1,
                'msg'=>lang('upload_size_error').$fileSize,
                'result'=>'',
            ];
            return $msg;
        }
        $saveName = [];
        if(is_array($files)){
            foreach($files as $k=>$file){
                $saveName[$k]['file'] = \think\facade\Filesystem::disk('public')->putFile( 'uploadFile', $file,'uniqid');
                $saveName[$k]['file'] = 'uploads/'.$saveName[$k]['img'];
                //获取文件存放路径
                $saveName[$k]['code'] = 200;
                $saveName[$k]['msg'] = lang('upload_success');
            }
        }else{
            $saveName['file'] = \think\facade\Filesystem::disk('public')->putFile( 'uploadFile', $files,'uniqid');
            $saveName['file'] = 'uploads/'.$saveName['file'];
        }
        $msg = [
            'code'=>200,
            'ident'=>2,
            'msg'=>lang('upload_success'),
            'result'=>$saveName,
        ];
        return $msg;
    } else {
        // 文件上传失败或未上传
        $msg = [
            'code'=>300,
            'msg'=>lang('upload_select'),
            'result'=>'',
        ];
        return $msg;
    }
}

/**
 * @param string $sql
 * @param int $limit
 * @param array $prefix
 * @return array|false|string|string[]
 * 处理 sql 文件
 */
function mini_parse_sql($sql='',$limit=0,$prefix=[])
{
    // 被替换的前缀
    $from = '';
    // 要替换的前缀
    $to = '';

    // 替换表前缀
    if (!empty($prefix)) {
        $to   = current($prefix);
        $from = current(array_flip($prefix));
    }

    if ($sql != '') {
        // 纯sql内容
        $pure_sql = [];

        // 多行注释标记
        $comment = false;

        // 按行分割，兼容多个平台
        $sql = str_replace(["\r\n", "\r"], "\n", $sql);
        $sql = explode("\n", trim($sql));
        // 循环处理每一行
        foreach ($sql as $key => $line) {
            // 跳过空行
            if ($line == '') {
                continue;
            }

            // 跳过以#或者--开头的单行注释
            if (preg_match("/^(#|--)/", $line)) {
                continue;
            }

            // 跳过以/**/包裹起来的单行注释
            if (preg_match("/^\/\*(.*?)\*\//", $line)) {
                continue;
            }

            // 多行注释开始
            if (substr($line, 0, 2) == '/*') {
                $comment = true;
                continue;
            }

            // 多行注释结束
            if (substr($line, -2) == '*/') {
                $comment = false;
                continue;
            }

            // 多行注释没有结束，继续跳过
            if ($comment) {
                continue;
            }

            // 替换表前缀
            if ($from != '') {
                $line = str_replace('`'.$from, '`'.$to, $line);
            }
            if ($line == 'BEGIN;' || $line =='COMMIT;') {
                continue;
            }
            // sql语句
            array_push($pure_sql, $line);
        }

        // 只返回一条语句
        if ($limit == 1) {
            return implode("",$pure_sql);
        }

        // 以数组形式返回sql语句
        $pure_sql = implode("\n",$pure_sql);
        $pure_sql = explode(";\n", $pure_sql);
        return $pure_sql;
    } else {
        return $limit == 1 ? '' : [];
    }
}

function redSql($sql_list){
    $db_connect = Db::connect();
    $msg = ['code'=>200,'msg'=>lang('complete')];
    if ($sql_list) {
        $sql_list = array_filter($sql_list);
        foreach ($sql_list as $v) {
            try {
                $db_connect->execute($v);
            } catch(\Exception $e) {
                $msg = ['code'=>300,'msg'=>$e->getMessage()];
            }
        }
    }
    return $msg;
}

//打开缓冲
function open_buffer(){
    set_time_limit(0);  //设置程序执行时间
    ignore_user_abort(true);    //设置断开连接继续执行
    ob_end_clean();//清空缓存
    ob_start(); //打开输出缓冲控制
}
//输出缓冲
function output_buffer(){
    //echo ob_get_clean();    //用于获取缓冲区的内容，并清空缓冲区。
    ob_flush();   //用于刷新缓冲区，将内容立即发送到浏览器。
    flush();   //刷新缓冲区的内容，输出
}
//进度条
function ProgressBar(){
    $show = '<div style="padding: 8px; background: #fff; width: %upx"><div style="padding: 0;border-radius:20px; background:#eee; width: %upx"><div id="progress" style="padding: 0;border-radius:20px; background-color: #FFCC66; border: 0; width:0px; text-align:center; height:18px;line-height:18px;"></div></div><div id="msg" style="font-family: Tahoma; font-size: 9pt;">正在处理...</div><div id="percent" style="position:relative;top:-34px;text-align:center;font-weight:bold;font-size:8pt;height:18px;line-height:18px;">0%%</div></div>';
    echo $show;
}
//进度条数据实时加载
function replace_msg(){
    return $script = '<script>document.getElementById("percent").innerText="%u%%";document.getElementById("progress").style.width="%u%%";document.getElementById("msg").innerText="%s";</script>';
}
//开启统计程序执行时间
function openTime(){
    return microtime(true);
}
//显示程序执行时间
function showTime($time,$flo=3){
    $end = microtime(true);
    return  round($end-$time,$flo);
}
/**
 * @param string $url   url列表地址
 * @param string $s     开始匹配
 * @param string $e     结束匹配
 * @return array|bool|false|string
 * 返回列表下的文章链接网址
 */
function get_url($url,$s,$e){
    //获取列表页
    $list = array();
    if($url){
        //获取列表页
        $str = stripos($url,'(*)');
        if($str){
            for($i=$s;$i<=$e;$i++){
                $list[] = str_replace('(*)',$i,$url);
            }
            return $list;
        }else{
            return  @file_get_contents($url);
        }
    }else{
        return false;
    }
}
/**
 * @param string $html    url地址
 * @param string $start   开始分割的边界
 * @param string $end     结束分割的边界
 * @return bool|string    返回内容结果
 * 返回匹配到的内容
 */
function cut_html($html, $start, $end) {
    if (empty($html)) return false;
    if(is_array($html)){
        $list = array();
        foreach ($html as $k=>$v){
            $list[] = $v;
        }
        $arr = array();
        foreach ($list as $kk=>$vv){
            $cdtexts = GetCurl($vv);
            $html = str_replace(array("\r", "\n"), "",$cdtexts['output']);
            $start = str_replace(array("\r", "\n"), "", $start);
            $end = str_replace(array("\r", "\n"), "", $end);
            $html = explode(trim($start), $html);
            if(is_array($html) && isset($html[1])){
                $arr[] = explode(trim($end), $html[1]);
            }else{
                $arr[] = array('');
            }
        }
        $str = '';
        foreach ($arr as $ky=>$vy){
            $str .= ($vy[0]);
        }
        return $str;
    }else{
        $cdtext = GetCurl($html);
        $html = str_replace(array("\r", "\n"), "", $cdtext['output']);
        $start = str_replace(array("\r", "\n"), "", $start);
        $end = str_replace(array("\r", "\n"), "", $end);
        $html = explode(trim($start), $html);
        if(is_array($html) && isset($html[1])){
            $html = explode(trim($end), $html[1]);
        }else{
            $html = array('');
        }
        return trim($html[0]);
    }
}
/**
 * @param string $page_html  要匹配的字符串内容
 * @return array
 * 返回匹配出的网址
 */
function get_list($page_html){
    if($page_html){
        if(is_array($page_html)){
            $arr = array();
            foreach ($page_html as $k=>$v){
                preg_match_all('/<a [^>]*href=[\'"]?([^>\'" ]*)[\'"]?[^>]*>([^<\/]*)<\/a>/Ssi', $v, $out);
                array_push($arr,$out);
            }
            return $arr;
        }else{
            preg_match_all('/<a [^>]*href=[\'"]?([^>\'" ]*)[\'"]?[^>]*>([^<\/]*)<\/a>/Ssi', $page_html, $out);
            return $out;
        }
    }
}
/**
 * @param $html
 * @return array|false|string[]
 * 返回匹配规则
 */
function replace_sg($html) {
    $list = explode('[内容]', $html);
    if (is_array($list)) foreach ($list as $k=>$v) {
        $list[$k] = str_replace(array("\r", "\n"), '', trim($v));
    }
    return $list;
}
/**
 * @param string $path  传入的路径(用于判断是否为远程地址)
 * @param string $url   远程地址，例：http://www.demo.com/lkf/kdi 或者：http://www.demo.com
 * @return string
 * 返回绝对地址路径 例：htt://www.demo.com/img/test.jpg
 */
function fileUrl($path,$url){
    $imgUrl = [];
    if(is_array($path)){
        foreach ($path as $kk=>$vv){
            //图片地址
            $urls = parse_url($vv);
            if(!isset($urls['scheme']) && !isset($urls['host'])){
                $nurl = parse_url($vv);
                $imgUrl[$kk] = $nurl['scheme'].'://'.$nurl['host'].$vv;
            }else{
                $imgUrl[$kk] = $vv;
            }
        }
    }else{
        $urls = parse_url($path);
        if(!isset($urls['scheme']) && !isset($urls['host'])){
            $nurl = parse_url($url);
            $imgUrl[] = $nurl['scheme'].'://'.$nurl['host'].$path;
        }else{
            $imgUrl[] = $path;
        }
    }
    return $imgUrl;
}

/**
 * @param $path
 * @return string
 * 返回图片路径（判断图片是否为远程路径，返回对应图片地址）
 */
function ImgPath($path){
    $urls = parse_url($path);
    if(!isset($urls['scheme']) && !isset($urls['host'])){
        $imgUrl = '/'.$path;
    }else{
        $imgUrl = $path;
    }
    return $imgUrl;
}
/**
 * @param string $text  文本内容
 * @return mixed
 * 返回匹配的图片地址
 */
function getImgList($text){
    $url = parse_url($text);
    if(isset($url['scheme']) && isset($url['host'])){
        $page_html = file_get_contents($text);
    }else{
        $page_html = $text;
    }
    preg_match_all('/<img[^>]*src=[\'"]?([^>\'"\s]*)[\'"]?[^>]*>/Ssi', $page_html, $out);
    return $out;
}

/**
 * @return string
 * 获得访客浏览器类型
 */
function GetBrowser()
{
    if (!empty($_SERVER['HTTP_USER_AGENT'])) {
        $br = $_SERVER['HTTP_USER_AGENT'];
        if (preg_match('/MSIE/i', $br)) {
            $br = 'MSIE';
        } elseif (preg_match('/Firefox/i', $br)) {
            $br = 'Firefox';
        } elseif (preg_match('/Chrome/i', $br)) {
            $br = 'Chrome';
        } elseif (preg_match('/Safari/i', $br)) {
            $br = 'Safari';
        } elseif (preg_match('/Opera/i', $br)) {
            $br = 'Opera';
        } else {
            $br = 'Other';
        }
        return lang('browser') . $br;
    } else {
        return lang('browser_error');
    }
}
/**
 * @return string
 * 获得访客浏览器语言
 */
function GetLang()
{
    if (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        $lang = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
        $lang = substr($lang, 0, 5);
        if (preg_match("/zh-cn/i", $lang)) {
            $lang = lang('lang_ch');
        } elseif (preg_match("/zh/i", $lang)) {
            $lang = lang('lang_tw');
        } else {
            $lang = lang('lang_en');
        }
        return lang('browser_lang') . $lang;
    } else {
        return lang('browser_lang_error');
    }
}
/**
 * @return string
 * 获取客户端操作系统信息包括win10
 * 获取访问设备操作系统
 */
function GetOs(){
    $agent = $_SERVER['HTTP_USER_AGENT'];
    $os = $agent;
    if (preg_match('/win/i', $agent) && stripos($agent, '95'))
    {
        $os = 'Windows 95';
    }
    if (preg_match('/win 9x/i', $agent) && stripos($agent, '4.90'))
    {
        $os = 'Windows ME';
    }
    if (preg_match('/win/i', $agent) && preg_match('/98/i', $agent))
    {
        $os = 'Windows 98';
    }
    if (preg_match('/win/i', $agent) && preg_match('/nt/i', $agent))
    {
        $os = 'Windows NT';
    }
    if (preg_match('/win/i', $agent) && preg_match('/nt 6.0/i', $agent))
    {
        $os = 'Windows Vista';
    }
    if (preg_match('/win/i', $agent) && preg_match('/nt 6.1/i', $agent))
    {
        $os = 'Windows 7';
    }
    if (preg_match('/win/i', $agent) && preg_match('/nt 6.2/i', $agent))
    {
        $os = 'Windows 8';
    }
    if(preg_match('/win/i', $agent) && preg_match('/nt 10.0/i', $agent))
    {
        $os = 'Windows 10';#添加win10判断
    }
    if (preg_match('/win/i', $agent) && preg_match('/nt 5.1/i', $agent))
    {
        $os = 'Windows XP';
    }
    if (preg_match('/win/i', $agent) && preg_match('/nt 5/i', $agent))
    {
        $os = 'Windows 2000';
    }
    if (preg_match('/win/i', $agent) && preg_match('/nt 5.0/i', $agent))
    {
        $os = 'Windows 2000';
    }
    if (preg_match('/win/i', $agent) && preg_match('/32/i', $agent))
    {
        $os = 'Windows 32';
    }
    if (stripos($agent,'linux'))
    {
        $os = 'Linux';
    }
    if (stripos($agent,'unix'))
    {
        $os = 'Unix';
    }
    if (preg_match('/sun/i', $agent) && preg_match('/os/i', $agent))
    {
        $os = 'SunOS';
    }
    if (preg_match('/ibm/i', $agent) && preg_match('/os/i', $agent))
    {
        $os = 'IBM OS/2';
    }
    if (preg_match('/Mac/i', $agent) && preg_match('/OS/i', $agent))
    {
        $os = 'Mac OS';
    }
    if (preg_match('/Mac/i', $agent) && preg_match('/PC/i', $agent))
    {
        $os = 'Macintosh';
    }
    if (stripos($agent,'PowerPC'))
    {
        $os = 'PowerPC';
    }
    if (stripos($agent,'AIX'))
    {
        $os = 'AIX';
    }
    if (stripos($agent,'HPUX'))
    {
        $os = 'HPUX';
    }
    if (stripos($agent,'NetBSD'))
    {
        $os = 'NetBSD';
    }
    if (stripos($agent,'BSD'))
    {
        $os = 'BSD';
    }
    if (stripos($agent,'OSF1'))
    {
        $os = 'OSF1';
    }
    if (stripos($agent,'IRIX'))
    {
        $os = 'IRIX';
    }
    if (stripos($agent,'FreeBSD'))
    {
        $os = 'FreeBSD';
    }
    if (stripos($agent,'teleport'))
    {
        $os = 'teleport';
    }
    if (stripos($agent,'flashget'))
    {
        $os = 'flashget';
    }
    if (stripos($agent,'webzip'))
    {
        $os = 'webzip';
    }
    if (stripos($agent,'offline'))
    {
        $os = 'offline';
    }
    if(stripos($agent, 'iphone')){
        $os = 'iphone';
    }
    if(stripos($agent, 'ipad')){
        $os = 'ipad';
    }
    if(stripos($agent, 'android')){
        $os = 'android';
    }
    if (stripos($agent, "SAMSUNG") || stripos($agent, "Galaxy") || stripos($agent, "GT-") || stripos($agent, "SCH-") || stripos($agent, "SM-")) {
        $os = 'android ->三星';
    }
    if (stripos($agent, "Huawei") || stripos($agent, "Honor") || stripos($agent, "H60-") || stripos($agent, "H30-")) {
        $os = 'android ->华为';
    }
    if (stripos($agent, "Lenovo")) {
        $os = 'android ->联想';
    }
    if (stripos($agent, "MI-ONE") || stripos($agent, "MI 1S") || stripos($agent, "MI 2") || stripos($agent, "MI 3") || stripos($agent, "MI 4") || stripos($agent, "MI-4") || stripos($agent,"xiaomi"))  {
        $os = 'android ->小米';
    }
    if (stripos($agent, "HM NOTE") || stripos($agent, "HM201")) {
        $os = 'android ->红米';
    }
    if (stripos($agent, "Coolpad") || stripos($agent, "8190Q") || stripos($agent, "5910")) {
        $os = 'android ->酷派';
    }
    if (stripos($agent, "ZTE") || stripos($agent, "X9180") || stripos($agent, "N9180") || stripos($agent, "U9180")) {
        $os = 'android ->中兴';
    }
    if (stripos($agent, "OPPO") || stripos($agent, "X9007") || stripos($agent, "X907") || stripos($agent, "X909") || stripos($agent, "R831S") || stripos($agent, "R827T") || stripos($agent, "R821T") || stripos($agent, "R811") || stripos($agent, "R2017")) {
        $os = 'android ->OPPO';
    }
    if (stripos($agent, "HTC") || stripos($agent, "Desire")) {
        $os = 'android ->HTC';
    }
    if (stripos($agent, "vivo")) {
        $os = 'android ->vivo';
    }
    if (stripos($agent, "K-Touch")) {
        $os = 'android ->天语';
    }
    if (stripos($agent, "Nubia") || stripos($agent, "NX50") || stripos($agent, "NX40")) {
        $os = 'android ->努比亚';
    }
    if (stripos($agent, "M045") || stripos($agent, "M032") || stripos($agent, "M355")) {
        $os = 'android ->魅族';
    }
    if (stripos($agent, "DOOV")) {
        $os = 'android ->朵唯';
    }
    if (stripos($agent, "GFIVE")) {
        $os = 'android ->基伍';
    }
    if (stripos($agent, "Gionee") || stripos($agent, "GN")) {
        $os = 'android ->金立';
    }
    if (stripos($agent, "HS-U") || stripos($agent, "HS-E")) {
        $os = 'android ->海信';
    }
    if (stripos($agent, "Nokia")) {
        $os = 'android ->诺基亚';
    }
    return $os;
}

function checkAccept(){
    if(stripos($_SERVER['HTTP_ACCEPT'], 'text/html') !== false){
        //可能是真实浏览器
    }else{
        // 判断蜘蛛
        $bot = isbot($_SERVER['HTTP_USER_AGENT']);
        if (!$bot) {
            //蜘蛛不存在终止访问
            die;
        }
        //die('坚决杜绝一切非正常访问');
    }
}

function isbot($tmp){
    //谷歌蜘蛛
    if(stripos($tmp, 'compatible; Googlebot/2.1') !== false){$flag = '谷歌蜘蛛';}
    else if(stripos($tmp, 'Googlebot-Mobile') >0){$flag = '谷歌蜘蛛';}
    else if(stripos($tmp, 'Googlebot-Image') >0){$flag = '谷歌图片蜘蛛';}
    else if(stripos($tmp, 'Mediapartners-Google') >0){$flag = '谷歌广告蜘蛛';}
    else if(stripos($tmp, 'Adsbot-Google') >0){$flag = '谷歌质量蜘蛛';}
    else if(stripos($tmp, 'Googlebot') >0){$flag = '谷歌蜘蛛';}
    else if(stripos($tmp, 'GoogleOther') !==false){$flag = '谷歌蜘蛛';}
    //百度蜘蛛
    else if(stripos($tmp, 'Baiduspider-mobile') >0){$flag = '百度蜘蛛';}
    else if(stripos($tmp, 'Baidu-Thumbnail') >0){$flag = '百度图片蜘蛛';}
    else if(stripos($tmp, 'Baiduspider-image') >0){$flag = '百度图片蜘蛛';}
    else if(stripos($tmp, 'Baiduspider-news') >0){$flag = '百度新闻蜘蛛';}
    else if(stripos($tmp, 'Baiduspider-video') >0){$flag = '百度视频蜘蛛';}
    else if(stripos($tmp, 'Baidu-Transcoder') >0){$flag = '百度音乐蜘蛛';}
    else if(stripos($tmp, 'baiduspider-mobile-gate') >0){$flag = '百度移动蜘蛛';}
    else if(stripos($tmp, 'Baiduspider') >0){$flag = '百度蜘蛛';}
    //搜搜蜘蛛
    else if(stripos($tmp, 'Sosospider') >0){$flag = '搜搜蜘蛛';}
    else if(stripos($tmp, 'Sosoimagespider') >0){$flag = '搜搜图片蜘蛛';}
    //雅虎蜘蛛
    else if(stripos($tmp, 'Yahoo! Slurp China') !== false){$flag = '雅虎中文蜘蛛';}
    else if(stripos($tmp, 'Yahoo ContentMatch Crawler') !== false){$flag = '雅虎竞价蜘蛛';}
    else if(stripos($tmp, 'Yahoo-MMCrawler') !== false){$flag = '雅虎图片蜘蛛';}
    else if(stripos($tmp, 'Yahoo! Slurp') !== false){$flag = '雅虎英文蜘蛛';}
    //微软蜘蛛
    else if(stripos($tmp, 'msnbot') !== false){$flag = '微软蜘蛛';}
    else if(stripos($tmp, 'msnbot-media') !== false){$flag = '微软媒体蜘蛛';}
    else if(stripos($tmp, 'MSNBot-Media') !== false){$flag = '微软多媒体蜘蛛';}
    else if(stripos($tmp, 'MSNBot-NewsBlogs') !== false){$flag = '微软新闻及blog蜘蛛';}
    else if(stripos($tmp, 'MSNBot-Academic') !== false){$flag = '微软学术蜘蛛';}
    else if(stripos($tmp, 'MSNBot') !== false){$flag = '微软网页蜘蛛';}
    //360蜘蛛
    else if(stripos($tmp, 'Sosospider') !== false){$flag = '360蜘蛛';}
    //有道蜘蛛
    else if(stripos($tmp, 'YodaoBot') !== false || stripos($tmp, 'OutfoxBot') !== false){$flag = '有道蜘蛛';}
    //搜狗蜘蛛
    else if(stripos($tmp, 'Sogou web spider') !== false || stripos($tmp, 'Sogou Orion spider') !== false){$flag = '搜狗蜘蛛';}
    else if(stripos($tmp, 'Sogou inst spider') !== false){$flag = '搜狗蜘蛛';}
    else if(stripos($tmp, 'Sogou News Spider') !== false){$flag = '搜狗新闻蜘蛛';}
    else if(stripos($tmp, 'Sogou spider2') !== false){$flag = '搜狗蜘蛛';}
    else if(stripos($tmp, 'Sogou blog') !== false){$flag = '搜狗blog蜘蛛';}
    else if(stripos($tmp, 'sogou spider') !== false){$flag = '搜狗蜘蛛';}
    //其他蜘蛛
    else if(stripos($tmp, 'bingbot') !== false){$flag = '必应蜘蛛';}
    else if(stripos($tmp, 'EtaoSpider') !== false){$flag = '一淘网蜘蛛';}
    else if(stripos($tmp, 'Scooter') !== false){$flag = 'Altavista蜘蛛';}
    else if(stripos($tmp, 'Lycos_Spider') !== false){$flag = 'Lycos蜘蛛';}
    else if(stripos($tmp, 'FAST-WebCrawler') !== false){$flag = 'Alltheweb蜘蛛';}
    else if(stripos($tmp, 'Slurp ASPSeek ASPSeek') !== false){$flag = 'INKTOMI蜘蛛';}
    else if(stripos($tmp, 'lanshanbot') !== false){$flag = '东方网景爬虫';}
    else if(stripos($tmp, 'BSpider') !== false){$flag = '日本爬虫';}
    else if(stripos($tmp, 'fast-webcrawler') !== false){$flag = 'fast-webcrawler';}
    else if(stripos($tmp, 'Gaisbot') !== false){$flag = 'Gaisbot';}
    else if(stripos($tmp, 'ia_archiver') !== false){$flag = 'Alexa蜘蛛';}
    else if(stripos($tmp, 'altavista') !== false){$flag = 'altavista爬虫';}
    else if(stripos($tmp, 'lycos_spider') !== false){$flag = 'Lycos蜘蛛';}
    else if(stripos($tmp, 'Inktomi slurp') !== false){$flag = 'Inktomi slurp';}
    else if(stripos($tmp, 'YandexBot') !== false){$flag = 'YandexBot';}
    else if(stripos($tmp, 'AhrefsBot') !== false){$flag = 'AhrefsBot';}
    else if(stripos($tmp, 'ezooms.bot') !== false){$flag = 'ezooms.bot';}
    else if(stripos($tmp, 'YisouSpider') !== false){$flag = '神马搜索';}
    else if(stripos($tmp, 'MJ12bot') !== false){$flag = 'majestic.com';}
    else{$flag = '';}
    return $flag;
}

function ClientType(){
    // 判断蜘蛛
    $bot = isbot($_SERVER['HTTP_USER_AGENT']);
    //蜘蛛不存在
    $msg = "未知";
    if(!$bot) {
        if(stripos($_SERVER['HTTP_ACCEPT'], 'text/html') !== false){
            //可能是真实浏览器
            $msg = "访客";
        }
    }else{
        $msg = "蜘蛛：".$bot;
    }
    return $msg;
}

/**
 * @param $file                 //要解压的文件
 * @param string $path          //解压后要存放的目录
 * @return array
 */
function DealZip($file,$path = ''){
    $zip = new PclZip($file);
    $msg = ['code'=>300,'msg'=>lang('decompression_error')];
    if ($zip->extract(PCLZIP_OPT_PATH,$path,PCLZIP_OPT_REPLACE_NEWER)) {
        $msg = ['code'=>200,'msg'=>lang('decompression_success')];
    }
    return $msg;
}

/**
 * @param $url
 * @param int $sec
 * 跳转
 */
function mini_jump($url,$sec=0)
{
    echo '<script>setTimeout(function (){location.href="'.$url.'";},'.($sec*1000).');</script><br>';
}

/**
 * @param array $param
 * @return string
 * 生成 HTML 文件
 */
function buildHtml($param = []){
    if(empty($param)){
        $param = request()->get();
    }
    //生成静态
    $staticHtmlDir = "html/" . \think\facade\Request::controller();
    //目录不存在，则创建
    if(!file_exists($staticHtmlDir)){
        mkdir($staticHtmlDir,0755,true);
    }
    //参数md5
    $param = md5(json_encode($param));
    return $staticHtmlDir .'/'.\think\facade\Request::action() . '_'. $param .'.html';
}

/**
 * @param array $param
 * @return string
 * 判断 HTML 是否存在静态
 */
function beforeBuild($param = []){
    $staticHtmlFile = buildHtml($param);
    //静态文件存在,并且没有过期
    if(file_exists($staticHtmlFile) && filectime($staticHtmlFile)>=time()-60*60*24*5) {
        include_once $staticHtmlFile;
        exit();
    }
}


/**
 * @param $html
 * @param array $param
 * @return string
 * 开始生成 HTML 静态文件
 */
function afterBuild($html, array $param=[]){
    $staticHtmlFile = buildHtml($param);
    if (!empty($staticHtmlFile) && !empty($html)) {
        if (file_exists($staticHtmlFile)) {
            \unlink($staticHtmlFile);
        }
        if (file_put_contents($staticHtmlFile, $html)) {
            include_once $staticHtmlFile;
            exit();
        }
    }
}

/**
 * @param int $star
 * @param array $param
 * @return \think\response\View
 * 控制视图是否生成 HTML
 */
function ViewHtml($star=0,$param=[]){
    $html = \think\facade\View::fetch();
    $open = Cfg('open_html');
    if($open){
        if($star){
            //重新生成html
            afterBuild($html,$param);
        }
        beforeBuild($param);
        afterBuild($html,$param);
    }
    return $html;
}