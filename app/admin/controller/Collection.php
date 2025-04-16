<?php
/**
 * Created by PhpStorm.
 * CreateTime  : 2023/10/23 15:48
 * file name : Collection.php
 * User: asusa
 * Author: Hyy-Cary
 * Contact QQ  : 373889161(.)
 * email: 373889161@qq.com
 * WeChat: 18319021313
 */


namespace app\admin\controller;


use app\admin\BaseController;
use think\facade\Db;
use think\facade\View;
use app\admin\model\CollectionContent as Model;

class Collection extends BaseController
{
    public function index(){
        return View();
    }
    public function datalist(){
        $data = request()->param();
        $size = $data['limit']?$data['limit']:10;
        $start = $data['page']?$data['page']:0;
        $where = [];
        if(array_key_exists('data',$data)){
            foreach ($data['data'] as $k=>$v){
                $where[] = [$v['name'],'like','%'.$v['value'].'%'];
            }
        }
        $list = pageTable('collection',$start,$size);
        $count = CountTable('collection',$where);
        foreach ($list as &$v){
            $v['createTime'] = date('Y-m-d H:i:s',$v['createTime']);
            $v['updateTime'] = date('Y-m-d H:i:s',$v['updateTime']);
        }
        $arr = array('code'=>0,'msg'=>'ok','count'=>$count,'limit'=>$where,'data'=>$list);
        echo json_encode($arr,JSON_UNESCAPED_UNICODE);
    }
    public function add(){
        $id = request()->param('id');
        $data = Db::name('collection')->where('id',$id)->find();
        View::assign('data',$data);
        return View();
    }
    public function edit(){
        $id = request()->param('id');
        $data = Db::name('collection')->where('id',$id)->find();
        View::assign('data',$data);
        return View();
    }
    public function saveAt(){
        $data = request()->param();
        $data['updateTime'] = time();
        if($data['id'] == ''){
            unset($data['id']);
            $data['createTime'] = time();
        }
        SaveAt('collection',$data);
        $msg = ['code'=>200,'msg'=>lang('success_message'),'data'=>$data];
        echo json_encode($msg,JSON_UNESCAPED_UNICODE);
    }
    public function addc(){
        $cid = request()->param('cid');
        View::assign('id',$cid);
        return View();
    }
    public function edits(){
        $id = request()->param('id');
        $cid = request()->param('cid');
        $data = FindTable('collection_content',['id'=>$id]);
        View::assign('data',$data);
        View::assign('id',$cid);
        return View();
    }
    public function saveAts(){
        $data = request()->param();
        $cdata = FindTable('collection',['id'=>$data['cid']]);
        $title = replace_sg($cdata['title']);
        $content = replace_sg($cdata['content']);
        $path = 'download_img/'.date('Ymd');
        //开始采集数据
        if(!$data['title']){
            $data['title'] = cut_html($data['url'],$title[0],$title[1]);
        }
        $text = cut_html($data['url'],$content[0],$content[1]);
        $host = parse_url($data['url']);
        if(isset($host['host']) && $host['host'] == 'mp.weixin.qq.com'){
            $text = str_replace('data-src','src',$text);
        }

        if(!$data['content']){

            if($cdata['download']==1){
                $imgUrl = getImgList($text);

                $reImgUrl = fileUrl($imgUrl[1],$data['url']);
                $imgArr = BatchDownLoadFiles($reImgUrl,$path);

                $text = str_replace($imgUrl[1],$imgArr['imgArr'],$text);
            }
            $data['content'] = $text;
        }
        if(!$data['id']){
            unset($data['id']);
            $data['status'] = 1;
        }
        SaveAt('collection_content',$data);
        $msg = ['code'=>200,'msg'=>lang('success_message'),'data'=>$data];
        echo json_encode($msg,JSON_UNESCAPED_UNICODE);
    }
    public function courl(){
        $query = request()->param();
        $data = Db::name('collection')->where('id',$query['id'])->find();
        if(!$data){
            die('数据出错啦！');
        }
        View::assign('id',$query['id']);
        return View();
    }
    public function import(){
        $sTime = openTime();
        open_buffer();
        $id = request()->param('id');
        $data = FindTable('collection',['id'=>$id]);
        if(!$data){
            die('数据出错啦！');
        }
        $url = get_url($data['url'],$data['ps'],$data['pe']);
        $arrUrl = array();
        $gather = AllTable('collection_content',['cid'=>$id]);

        foreach ($gather as $v=>$item){
            array_push($arrUrl,$item['url']);
        }
        $list = array();
        if($url){
            foreach ($url as $k=>$v){
                $list[] = cut_html($v,$data['start'],$data['end']);
            }
        }
        $html = get_list($list);
        $result = array();
        if($html){
            foreach ($html as $k=>$v){
                $key = $v[1];
                $value = $v[2];
                $res = array_combine($key,$value);
                $result = array_merge($result,$res);
            }
        }
        $arr = array();
        $low = 0;
        $i = 1;
        $lent = count($result);
        $str = replace_msg();
        if($result){
            ProgressBar();
        }
        foreach ($result as $k=>$v){
            $proportion = $i / $lent;
            for ($cv=1;$cv<=1000;$cv++){
                echo str_repeat(' ', 8);
            }
            if($gather && in_array($k,$arrUrl)){
                $low++;
            }else{
                $new = array(
                    'cid'=>$id,
                    'url'=>trim($k),
                    'title'=>trim($v),
                );
                array_push($arr,$new);
            }
            $potion = intval($proportion*100);
            echo sprintf($str,$potion,$potion,$i.'/'.$lent);
            $i++;
            output_buffer();
        }
        batchSave('collection_content',$arr);
        echo lang('collection_run_time').showTime($sTime).lang('collection_msg').count($arr).lang('collection_error').$low.lang('collection_repeat').$low;
        unset($str,$proportion,$potion,$c,$lent,$arr,$new);
    }
    public function codata(){
        $id = request()->param('id');
        $data = FindTable('collection',['id'=>$id]);
        if(!$data){
            die(lang('data_error'));
        }
        View::assign('id',$id);
        return View();
    }

    /**
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * 内容采集
     */
    public function imdata(){
        $sTime = openTime();
        open_buffer();
        $id = request()->param('id');
        $data = FindTable('collection',['id'=>$id]);
        $html = AllTable('collection_content',['cid'=>$id,'status'=>0]);
        $title = replace_sg($data['title']);
        $content = replace_sg($data['content']);
        if(!$data){
            die(lang('data_error'));
        }

        $cons = array();
        $lent = count($html);
        $script = replace_msg();
        $r = 0;
        $i = 1;
        if(!$html){
            echo lang('collection_data_error');
        }else{
            ProgressBar();
        }
        $path = 'download_img/'.date('Ymd');
        $cimg = 0;
        foreach ($html as $k=>$v){
            $proportion = $i/$lent;
            $potion = intval($proportion*100);
            $text = cut_html($v['url'],$content[0],$content[1]);
            for ($cv=1;$cv<=1000;$cv++){
                echo str_repeat(' ', 8);
            }
            $host = parse_url($v['url']);
            if(isset($host['host']) && $host['host'] == 'mp.weixin.qq.com'){
                $text = str_replace('data-src','src',$text);
            }
            if($data['download']==1){
                $imgUrl = getImgList($text);

                $reImgUrl = fileUrl($imgUrl[1],$data['url']);
                $imgArr = BatchDownLoadFiles($reImgUrl,$path);

                foreach ($imgUrl[1] as $kk=>$vv){
                    $cimg++;
                    echo sprintf($script, $potion, $potion, $i.'/'.$lent.lang('collection_success').$r.lang('collection_img').$cimg);
                    output_buffer();
                }
                $text = str_replace($imgUrl[1],$imgArr['imgArr'],$text);
            }
            if($v['content']=='' && $v['status']==0){
                $new = array(
                    'id'=>$v['id'],
                    'status'=>1,
                    'content'=>$text
                );
                array_push($cons,$new);
                $r++;
            }
            echo sprintf($script, $potion, $potion, $i.'/'.$lent.lang('collection_success').$r.lang('collection_img').$cimg);
            $i++;
            output_buffer();
        }
        $model = new Model();
        $model->saveAll($cons);
        echo lang('collection_consume').showTime($sTime);
    }
    public function content(){
        $id = request()->param('id');
        View::assign('id',$id);
        return View();
    }
    public function conlist(){
        $data = request()->param();
        $size = $data['limit']?$data['limit']:10;
        $start = $data['page']?$data['page']:0;
        $where = [];
        if($data['id']){
            $where[] = ['cid','=',$data['id']];
        }
        if(array_key_exists('data',$data)){
            foreach ($data['data'] as $k=>$v){
                $where[] = [$v['name'],'like','%'.$v['value'].'%'];
            }
        }
        $list = pageTable('collection_content',$start,$size,$where);
        $count = CountTable('collection_content',$where);
        $str = [lang('collection_to_be'),lang('collection_in_be'),lang('collection_in_pro')];
        foreach ($list as &$v){
            $v['status'] = $str[$v['status']];
        }
        $arr = array('code'=>0,'msg'=>'ok','count'=>$count,'limit'=>$where,'data'=>$list);
        echo json_encode($arr,JSON_UNESCAPED_UNICODE);
    }
    public function delAll(){
        $id = request()->param('data');
        $data = Db::name('collection_content')->where('id','in',$id)->select()->toArray();
        if($data){
            Db::name('collection_content')->delete($id);
            $msg = ['code'=>200,'msg'=>lang('delete_message')];
        }else{
            $msg = ['code'=>300,'msg'=>lang('fail_message'),'data'=>$id];
        }
        echo json_encode($msg);
    }

    public function select(){
        $data = request()->param();
        if(!$data['ids']){
            die(lang('collection_no_data'));
        }
        $tree = GetMenu('category');
        foreach ($tree as $k=>$v){
            $level = $v['level']-1;
            if( $level > 1){
                $tree[$k]['p'] = str_repeat("&nbsp;&nbsp;&nbsp;",$level).'|--';
            }else{
                $tree[$k]['p']='';
            }
        }
        View::assign('id',$data['ids']);
        View::assign('tree',$tree);
        return View();
    }
    public function selectImport()
    {
        $s = openTime();
        open_buffer();
        // 设置缓冲区大小为 4096 字节
        ini_set('output_buffering', '0');
        $cid = request()->param('cid');
        $id = request()->param('id');
        $data = Db::name('collection_content')->where([['id', 'in', $id], ['status', '=', 1]])->select()->toArray();
        if (!$data) {
            return lang('collection_no_data_import');
        } else {
            ProgressBar();
        }
        $arr = array();
        $lent = count($data);
        $c = 1;
        $str = replace_msg();
        foreach ($data as $k => $v) {
            /*for ($cv=1;$cv<=1000;$cv++){
                echo str_repeat(' ', 8);
            }*/
            $proportion = $c / $lent;
            $potion = intval($proportion * 100);
            $new = [
                'cid' => $cid,
                'title' => $v['title'],
                'keywords' => $v['title'],
                'description' => $v['title'],
                'content' => $v['content'],
                'createTime' => time(),
                'updateTime' => time(),
            ];
            array_push($arr, $new);
            echo sprintf($str, $potion, $potion, $c . '/' . $lent);
            $c++;
            output_buffer();
        }
        //更新采集状态
        Db::name('collection_content')->where([['id', 'in', $id]])->update(['status' => '2']);
        //批量添加文档数据
        batchSave('article', $arr);
        echo '运行时间：' . showTime($s);
    }
    public function importAll(){
        $data = request()->param();
        $tree = GetMenu('category');
        foreach ($tree as $k=>$v){
            $level = $v['level']-1;
            if( $level > 1){
                $tree[$k]['p'] = str_repeat("&nbsp;&nbsp;&nbsp;",$level).'|--';
            }else{
                $tree[$k]['p']='';
            }
        }
        View::assign('tree',$tree);
        View::assign('id',$data['id']);
        return View();
    }
    public function Allimport(){
        $s = openTime();
        open_buffer();
        // 设置缓冲区大小为 4096 字节
        ini_set('output_buffering', '0');
        $get = request()->param();
        $data = Db::name('collection_content')->where([['cid','=',$get['id']],['status','=',1]])->select()->toArray();
        if(!$data){
            return lang('collection_no_data_import');
        }else{
            ProgressBar();
        }
        $arr = array();
        $lent = count($data);
        $c = 1;
        $str = replace_msg();
        foreach ($data as $k=>$v){
            /*for ($cv=1;$cv<=1000;$cv++){
                echo str_repeat(' ', 8);
            }*/
            $proportion = $c / $lent;
            $potion = intval($proportion*100);
            $new = [
                'cid'=>$get['cid'],
                'title'=>$v['title'],
                'keywords'=>$v['title'],
                'description'=>$v['title'],
                'content'=>$v['content'],
                'createTime'=>time(),
                'updateTime'=>time(),
            ];
            array_push($arr,$new);
            echo sprintf($str,$potion,$potion,$c.'/'.$lent);
            $c++;
            output_buffer();
        }
        //更新采集状态
        Db::name('collection_content')->where([['cid','=',$get['id']]])->update(['status' => '2']);
        //批量添加文档数据
        batchSave('article',$arr);
        echo lang('collection_run_time').showTime($s);
    }
}