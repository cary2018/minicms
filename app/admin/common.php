<?php
/**
 * Created by PhpStorm.
 * CreateTime  : 2023/12/16 20:18
 * file name : common.php
 * User: asusa
 * Author: Hyy-Cary
 * Contact QQ  : 373889161(.)
 * email: 373889161@qq.com
 * WeChat: 18319021313
 */

use think\facade\Db;

/**
 * @throws \think\db\exception\DataNotFoundException
 * @throws \think\db\exception\DbException
 * @throws \think\db\exception\ModelNotFoundException
 * 更新缓存数据
 */
function EmptyCache(){
    //删除缓存文件
    $root = root_path().'runtime/';
    delDirectory($root.'admin/log/');
    delDirectory($root.'admin/temp/');
    delDirectory($root.'api/log/');
    delDirectory($root.'index/log/');
    delDirectory($root.'index/temp/');
    delDirectory($root.'log/');
    delDirectory('html/');
    //删除缓存
    delCache('Menu');
    //删除菜单列表缓存
    delCache('MenuList');
    //更新菜单列表缓存
    caheMenu();
    //更新菜单缓存
    SetMenu();
    //阅读权限
    $str = [
        '待审核',
        '开放浏览'
    ];
    //设置阅读权限缓存
    SetCaChe('readArticle',$str);
    //更新文章属性缓存
    $attr = AllTable('attribute',[['status','=',1]],['orderSort','id'=>'desc']);
    SetCaChe('attribute',$attr);
    //设置前台导航api缓存
    navApi();
    //上网导航缓存
    Navigation();
    //更新行政区域缓存
    AreaList();
    //更新配置文件
    putFile();
}

/**
 * @param $dirname
 * 删除目录下所有文件和文件夹
 */
function delDirectory($dirname){
    if(file_exists($dirname)) {
        $dir=opendir($dirname);
        while($filename=readdir($dir)){
            if($filename!="." && $filename!=".."){
                $file=$dirname."/".$filename;
                if(is_dir($file)){
                    //使用递归删除子目录
                    delDirectory($file);
                    //目录清空后删除空文件夹
                    rmdir($file);
                }else{
                    //删除文件
                    unlink($file);
                }
            }
        }
        closedir($dir);
    }
}

/**
 * @return array|false
 * 读取网站模板
 */
function RedTemplate(){
    // 使用 scandir() 函数读取目录
    $dir = 'template/';
    if(!file_exists($dir)){
        mkdir($dir,0755);
    }
    $contents = scandir($dir);
    return array_filter($contents, function ($item) {
        return $item !== '.' && $item !== '..';
    });
}
/**
 * @param $arr
 * @return mixed
 */
function TmHtml($arr){
    foreach ($arr as $k=>$v){
        switch ($v['sys_type']){
            case 'input':
                if($v['sys_variable'] == 'view_path'){
                    $filteredItems = RedTemplate();
                    $path = Cfg('view_path');
                    $arr[$k]['sys_html'] = '<select name="content['.$v['id'].']">';
                    foreach ($filteredItems as $item){
                        $arr[$k]['sys_html'] .= '<option value="'.$item.'"';
                        $arr[$k]['sys_html'] .= ''.$item==$path?" selected >":"".'>';
                        $arr[$k]['sys_html'] .= $item.'</option>';
                    }
                    $arr[$k]['sys_html'] .= '</select>';
                }else{
                    $arr[$k]['sys_html'] = '<input type="text" name="content['.$v['id'].']" value="'.$v['sys_content'].'" class="layui-input" id="nav_url" >';
                }
                break;
            case 'textarea':
                $arr[$k]['sys_html'] = '<textarea name="content['.$v['id'].']" id="sys_content" style="height:50px;" cols="50" rows="4" class="layui-input">'.$v['sys_content'].'</textarea>';
                break;
            case 'file';
                $arr[$k]['sys_html'] = '<input type="hidden" name="content['.$v['id'].']" value="'.$v['sys_content'].'" class="layui-input" id="nav_url" >';
                $arr[$k]['sys_html'] .= '<img id="imgAmplify" layer-src=/'.$v['sys_content'].' src=/'.$v['sys_content'].'>';
                break;
            case 'bool':
                if($v['sys_content'] == 1){
                    $arr[$k]['sys_html'] = '<input type="radio" name="content['.$v['id'].']" value="1" title="开启" checked>';
                    $arr[$k]['sys_html'] .= '<input type="radio" name="content['.$v['id'].']" value="0" title="关闭" >';
                }else{
                    $arr[$k]['sys_html'] = '<input type="radio" name="content['.$v['id'].']" value="1" title="开启" >';
                    $arr[$k]['sys_html'] .= '<input type="radio" name="content['.$v['id'].']" value="0" title="关闭" checked>';
                }
                break;
            case 'number':
                $arr[$k]['sys_html'] = '<input type="text" oninput="value=value.replace(/[^\d]/g,\'\')" name="content['.$v['id'].']" value="'.$v['sys_content'].'" class="layui-input" id="number" >';
                break;
        }
    }
    return $arr;
}
/**
 * @param $arr
 * @param int $pid
 * @param array $contrast
 * @param string $pids
 * @param string $id
 * @param int $level
 * @return array
 * 获取菜单
 */
function MenuNode($arr,$pid=0,$contrast=[],$pids = 'pid',$id = 'id',$level=0){
    //初始化儿子
    $child = '';
    $data = array();
    //循环所有数据找$id的儿子
    foreach ($arr as $key => $v) {
        //找到儿子了
        if ($v[$pids] == $pid) {
            //先去掉自己，自己不可能是自己的儿孙
            unset($arr[$key]);
            $son = MenuNode($arr, $v[$id],$contrast,$pids,$id,$level+1);
            //组装数据
            $child = [
                'id'=>$v['id'],
                'title'=>$v['title'],
                'href'=>$v['hrefUrl'],
                'field'=>'menuAuth[]',
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
 * @param $cer
 * @return string
 * 定义控制器名称
 */
function getCer($cer){
    $arr = [
        'welcome'=>'欢迎页面',
        'index'=>'后台主页',
        'admin'=>'管理员',
        'menu'=>'后台菜单',
        'node'=>'后台节点',
        'role'=>'角色权限',
        'weblog'=>'网站日志',
        'cache'=>'缓存',
        'system'=>'系统配置',
        'login'=>'缓存',
        'article'=>'文章',
        'attribute'=>'文章属性',
        'category'=>'文章分类',
        'banner'=>'广告横幅',
        'link'=>'友情链接',
        'album'=>'相册管理',
        'albumcategory'=>'相册分类',
        'classify'=>'导航分类',
        'navigation'=>'导航',
        'liar'=>'失信名单',
        'area'=>'行政区域',
        'position'=>'职位管理',
        'department'=>'部门管理',
        'feedback'=>'评论管理',
        'tags'=>'tag标签',
        'visit'=>'访客记录',
        'collection'=>'采集管理',
        'database'=>'数据库管理',
        'file'=>'文件管理',
        'update'=>'系统更新',
    ];
    $strCode = strtolower($cer);
    $strArr = array_change_key_case($arr,CASE_LOWER);
    if(array_key_exists($strCode,$strArr)){
        $name = $strArr[$strCode];
    }else{
        $name = '未定义';
    }
    return $name;
}
/**
 * @param $action
 * @return string
 * 定义方法名称
 */
function getAct($action){
    $arr = [
        'index'=>'列表',
        'recycle'=>'回收站',
        'list'=>'配置项',
        'datalist'=>'数据',
        'reclist'=>'回收站数据',
        'add'=>'添加',
        'edit'=>'编辑',
        'city'=>'行政区域',
        'editSave'=>'保存编辑',
        'saveat'=>'保存',
        'switchat'=>'状态开关',
        'del'=>'删除',
        'delall'=>'删除',
        'revert'=>'回收站还原数据',
        'delRecycle'=>'删除回收站数据',
        'logout'=>'退出',
        'updatefield'=>'更新字段',
        'updatenode'=>'更新系统节点',
        'info'=>'用户资料',
        'saveinfo'=>'保存用户资料',
        'emptydata'=>'清空数据',
        'check'=>'登录验证',
        'batchsave'=>'批量保存',
        'addc'=>'添加采集',
        'edits'=>'编辑采集',
        'courl'=>'采集网址',
        'codata'=>'内容采集',
        'content'=>'内容发布',
        'conlist'=>'采集数据列表',
        'import'=>'采集url',
        'imdata'=>'采集内容',
        'saveAts'=>'采集单个',
        'importAll'=>'导入全部',
        'Allimport'=>'执行导入全部',
        'select'=>'导入选中数据',
        'selectImport'=>'执行导入选中',
        'export'=>'数据备份',
        'optimize'=>'优化数据',
        'repair'=>'修复数据',
        'restore'=>'还原数据',
        'sql'=>'执行Sql',
        'rep'=>'批量替换',
        'repcon'=>'执行批量替换',
        'columns'=>'获取数据表信息',
        'rename'=>'修改文件名',
        'saveName'=>'执行修改文件名',
        'saveFile'=>'保存修改文件',
        'remove'=>'移动文件',
        'removeFile'=>'执行移动文件',
        'create'=>'新建文件',
        'createFile'=>'执行新建文件',
        'createdir'=>'新建文件夹',
        'saveDir'=>'执行新建文件夹',
        'countSize'=>'统计文件夹大小',
        'step1'=>'系统更新，下载升级包',
        'step2'=>'系统更新，解压升级包',
        'step3'=>'系统更新，检测数据库完成升级',
    ];
    $strCode = strtolower($action);
    $strArr = array_change_key_case($arr,CASE_LOWER);
    if(array_key_exists($strCode,$strArr)){
        $name = $strArr[$strCode];
    }else{
        $name = '未定义';
    }
    return $name;
}

/**
 * @throws Exception
 * 系统日志
 */
function WebLog(){
    $con = request()->controller(true);
    $path = request()->action();
    $url = app('http')->getName().'/'.request()->controller(true).'/'.request()->action();
    $mc = getCer($con);
    $str = $mc.' / '.getAct($path);
    if($con == 'menu'){
        if($path == 'add'){
            $str = $mc.' / 添加';
        }
    }
    if($con == 'welcome'){
        if($path == 'index'){
            $str = $mc.' / 后台首页';
        }
    }
    if($con == 'cache'){
        if($path == 'index'){
            $str = $mc.' / 清理缓存';
        }
    }
    if($con == 'system'){
        if($path == 'index'){
            $str = $mc.' / 参数配置';
        }
        if($path == 'list'){
            $str = $mc.' / 配置项';
        }
        if($path == 'add'){
            $str = $mc.' / 添加';
        }
    }
    $ip = get_client_ip();
    $reg = new Ip2Region();
    $region = $reg->btreeSearch($ip);
    $user = GetSe('admin');
    if($user){
        $name = $user['username'];
    }else{
        $name = '';
    }
    $data = [
        'username'=>$name,
        'ip'=>$ip,
        'region'=>$region['region'],
        'path'=>$url,
        'remark'=>$str,
        'createTime'=>time(),
    ];
    $skip = [
        'index',
        'dataList',
        'add',
        'addc',
        'edit',
        'edits',
        'list',
        'codata',
        'conlist',
        'courl',
        'select',
        'content',
        'importAll',
        'rename',
        'remove',
        'create',
        'createdir',
        'countSize',
        'columns',
        'repcon',
        'rep',
        'sql',
        'check',
    ];
    $bool = inArr($path,$skip);
    if(!$bool){
        SaveAt('weblog',$data);
    }
}
//转小写
function inArr($needle, $haystack) {
    return in_array(strtolower($needle), array_map('strtolower', $haystack));
}

/**
 * @param $table
 * @param $name
 * @return array|bool|int
 * 数据表备份导出
 */
function backup($table,$name){
    //打开缓冲
    open_buffer();
    $find = glob(backupDatabasePath().$name.".*");
    if(!$find){
        $sql  = "-- -----------------------------\n";
        $sql .= "-- MiniCms MySQL Data Transfer \n";
        $sql .= "-- \n";
        $sql .= "-- Host     : " . env('database.hostname') . "\n";
        $sql .= "-- Port     : " . env('database.hostport') . "\n";
        $sql .= "-- Database : " . env('database.database') . "\n";
        $sql .= "-- \n";
        $sql .= "-- Target Server Type : MYSQL\n";
        $sql .= "-- Date : " . date("Y-m-d H:i:s") . "\n";
        $sql .= "-- -----------------------------\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";
        $filename = write($sql,$name);
    }

    //备份表结构
    $result = Db::query("SHOW CREATE TABLE `{$table}`");
    $sql  = "\n";
    $sql .= "-- -----------------------------\n";
    $sql .= "-- Table structure for `{$table}`\n";
    $sql .= "-- -----------------------------\n";
    $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";
    $sql .= trim($result[0]['Create Table']) . ";\n\n";
    $filename = write($sql,$name);

    //数据总数
    $result = Db::query("SELECT COUNT(*) AS count FROM `{$table}`");
    $count  = $result['0']['count'];

    //备份表数据
    if($count){
        //写入数据注释
        $sql  = "-- -----------------------------\n";
        $sql .= "-- Records of `{$table}`\n";
        $sql .= "-- -----------------------------\n";
        $filename = write($sql,$name);
        //备份数据记录
        //计算页面码
        $ce = ceil($count/1000)-1;
        $size = 1000;
        for($i=0;$i<=$ce;$i++){
            $limit = $i*$size;
            $result = Db::query("SELECT * FROM `{$table}` LIMIT {$limit},$size");
            $rows = count($result)-1;
            $sql = "INSERT INTO `{$table}` VALUES ";
            foreach ($result as $k=>$row) {
                $one = '';
                foreach ($row as $v){
                    $one .= (gettype($v) == 'string') ? "'".str_replace("'","\'",$v)."'," : $v.",";
                }
                $one = rtrim($one,',');
                $one = str_replace(["\n","\r"],'',$one);
                $sql .= $rows == $k ? "(" . $one . ");\n" : "(" . $one . "),\n";
            }
            $filename = write($sql,$name);
            //输出缓冲
            output_buffer();
        }
    }
    return ['code'=>200,'msg'=>'完成操作'];
}
//数据备份存放地址
function backupDatabasePath(){
    return base_path().'data/backup/database/';
}

/**
 * @param $sql
 * @param $name
 * @return false|resource
 * 压缩写入文件
 */
function write($sql,$name){
    $size = strlen($sql);
    $path = backupDatabasePath();
    if(!file_exists($path)){
        mkdir($path,0777,true);
    }
    $filename = $path.$name.'.sql';
    $filename = gzopen($filename.'.gz','a9');
    gzwrite($filename,$sql);
    gzclose($filename);
    return $filename;
}

/*读取GZ文件*/
function read_gz($gz_file){
    $buffer_size = 5120; // read 5kb at a time
    $file = gzopen($gz_file, 'rb');
    $str='';
    while(!gzeof($file)) {
        $str.=gzread($file, $buffer_size);
    }
    gzclose($file);
    return $str;
}

//************************************************************************************************************
//****************************  文件管理相关函数  START *********************************************************
//************************************************************************************************************

//文件大小单位转换================================
function toSize($size){
    $dw = 'Bytes';
    if($size > pow(2 , 30)){
        $size = round($size/pow(2,30),2);
        $dw = ' GB';
    }else if($size > pow(2,20)){
        $size = round($size/pow(2,20),2);
        $dw = ' MB';
    }else if($size > pow(2,10)){
        $size = round($size/pow(2,10),2);
        $dw = ' KB';
    }else{
        $dw = ' Bytes';
    }
    return $size.($dw);
}
//文件夹大小
function dirSize($dirname){
    $dirSize = 0;
    //打开资源
    $dir = opendir($dirname);
    while($filename = readdir($dir)){
        $file = $dirname .'/'. $filename;
        if($filename != '.' && $filename != '..'){
            if(is_dir($file)){
                $dirSize += dirSize($file);
            }else{
                $dirSize += filesize($file);
            }
        }
    }
    //关闭资源
    closedir($dir);
    return $dirSize;
}
//遍历目录
function dirList($dir){
    $data = array();
    $i = 0;
    if (is_dir($dir)) {
        if ($dh = opendir($dir)) {
            while (($file = readdir($dh)) !== false) {
                if($file != '.' && $file != '..'){
                    $path = $dir.$file;
                    $data[$i]['filename'] = $file;
                    $data[$i]['path'] = $path;
                    $data[$i]['type'] = filetype($path);
                    $data[$i]['mtime'] = date('Y-m-d H:i:s',filemtime($path));
                    $data[$i]['parts'] = 'dir';
                    $type = mime_content_type($path);
                    $data[$i]['mime_type'] = $type;
                    if(strpos($type,'text') !== false){
                        $data[$i]['mime_type'] = 'text';
                    }
                    if(strpos($type,'application') !== false){
                        $data[$i]['mime_type'] = 'text';
                    }
                    if(is_dir($path)){
                        $data[$i]['size'] = '计算';
                    }else{
                        $info = pathinfo($path);
                        if(array_key_exists('extension',$info)){
                            $data[$i]['parts'] = $info['extension'];
                            $arr = ['txt','yml','gitignore','lock','env','md','ini','htaccess','vue','svg','woff','woff2','ttf','eot'];
                            if(in_array($info['extension'],$arr)){
                                $data[$i]['parts'] = 'file';
                            }
                        }else{
                            $data[$i]['parts'] = 'default';
                        }
                        $data[$i]['size'] = toSize(filesize($path));
                    }
                    $i++;
                }
            }
            closedir($dh);
            foreach($data as $k=>$v){
                $size[$k] = $v['size'];
                $time[$k] = $v['mtime'];
                $name[$k] = $v['filename'];
            }
            //排序
            if($data){
                array_multisort($size,SORT_ASC,SORT_NUMERIC, $data);
            }
        }
    }
    return $data;
}
//删除目录
function delDir($dirname){
    if(file_exists($dirname)) {
        $dir=opendir($dirname);
        while($filename=readdir($dir)){
            if($filename!="." && $filename!=".."){
                $file=$dirname."/".$filename;
                if(is_dir($file)){
                    deldir($file); //使用递归删除子目录
                }else{
                    //echo '删除文件<b>'.$file.'</b>成功<br>';
                    unlink($file);
                }
            }
        }
        closedir($dir);
        //echo '删除目录<b>'.$dirname.'</b>成功<br>';
        rmdir($dirname);
    }
}
//复制目录
function copyDir($dirSrc, $dirTo){
    if(is_file($dirTo)){
        echo "目标不是目录不能创建！";
        return;
    }
    if(!file_exists($dirTo)){
        //创建目录
        mkdir($dirTo);
    }
    $dir=opendir($dirSrc);
    while($filename=readdir($dir)){
        if($filename!="." && $filename!=".."){
            $file1=$dirSrc."/".$filename;
            $file2=$dirTo."/".$filename;
            if(is_dir($file1)){
                copydir($file1, $file2); //递归处理
            }else{
                copy($file1, $file2);
            }
        }
    }
    closedir($dir);
}

//************************************************************************************************************
//****************************  文件管理相关函数  END ***********************************************************
//************************************************************************************************************