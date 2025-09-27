<?php
/**
 * Created by PhpStorm.
 * CreateTime  : 2024/06/27 20:39
 * file name : File.php
 * User: asusa
 * Author: Hyy-Cary
 * Contact QQ  : 373889161(.)
 * email: 373889161@qq.com
 * WeChat: 18319021313
 */


namespace app\admin\controller;


use app\admin\BaseController;
use think\facade\View;

class File extends BaseController
{
    public function index(){
        $path = request()->param('path');
        $path = $path ?? '';
        $dir = $path ? root_path().$path.'/' : root_path();
        $list = dirList($dir);
        $arr = explode('/',$path);
        array_pop($arr);
        $arr = implode('/',$arr);
        View::assign('path',$path);
        View::assign('url',$arr);
        View::assign('list',$list);
        return View();
    }
    public function rename(){
        $param= request()->param('path') ?? '';
        $info = pathinfo($param);
        View::assign('info',$info);
        View::assign('url',$param);
        return View();
    }
    public function saveName(){
        $data = request()->param() ?? '';
        $path = root_path();
        $old = $path.$data['path'].'/'.$data['oldname'];
        $new = $path.$data['path'].'/'.$data['newname'];
        if($data['newname']){
            rename($old,$new);  //修改文件名
            $msg = array('code'=>200,'msg'=>lang('complete'));
        }else{
            $msg = array('code'=>300,'msg'=>lang('operate_error'));
        }
        return json_encode($msg);
    }
    public function edit(){
        $param = request()->param('path');
        $path = $param ?? '';
        $dir = root_path();
        $file = $dir.$path;
        $center = file_get_contents($file);
        $info = pathinfo($path);
        $arr = explode('/',$param);
        array_pop($arr);
        $arr = implode('/',$arr);
        View::assign('url',$arr);
        View::assign('info',$info);
        View::assign('center',$center);
        return View();
    }
    //保存修改文件
    public function saveFile(){
        $data = request()->param();
        $path =  root_path().$data['path'].'/'.$data['filename'];
        if(file_exists($path)){
            file_put_contents($path,$data['center']);
            $msg = array('code'=>200,'msg'=>lang('edit_success'));
        }else{
            $msg = array('code'=>300,'msg'=>lang('edit_fail'));
        }
        return json_encode($msg);
    }
    public function remove(){
        $param= request()->param('path') ?? '';
        $info = pathinfo($param);
        View::assign('info',$info);
        View::assign('url',$param);
        return View();
    }
    public function removeFile(){
        $data = request()->param() ?? '';
        $path = root_path();
        $old = $path.$data['path'].'/'.$data['oldname'];
        $new = $path.'/'.$data['newname'];
        if(file_exists($new)){
            if($data['newname']){
                rename($old,$new.'/'.$data['oldname']);
                $msg = array('code'=>200,'msg'=>lang('remove_success'));
            }else{
                $msg = array('code'=>300,'msg'=>lang('create_dir_empty'));
            }
        }else{
            $msg = array('code'=>300,'msg'=>lang('create_dir'));
        }
        return json_encode($msg);
    }
    public function delAll(){
        $dirname = request()->param('path');
        $path = root_path();
        if($dirname){
            if(is_array($dirname)){
                foreach ($dirname as $v){
                    $file = $path.$v;
                    if(file_exists($file)){
                        if(is_dir($file)){
                            //删除目录（递归删除目录下所有文件）
                            delDir($file);
                        }elseif(is_file($file)){
                            //删除文件
                            unlink($file);
                        }
                    }
                }
            }else{
                $file = $path.$dirname;
                if(file_exists($file)){
                    if(is_dir($file)){
                        //删除目录（递归删除目录下所有文件）
                        delDir($file);
                    }elseif(is_file($file)){
                        //删除文件
                        unlink($file);
                    }
                }
            }
        }
        return json_encode(['code'=>200,'msg'=>'删除成功！']);
    }
    public function create(){
        $param= request()->param('path') ?? '';
        View::assign('url',$param);
        return View();
    }
    public function createFile(){
        $url = request()->param() ?? '';
        $path = root_path().$url['path'].'/'.$url['filename'];
        //echo $path;
        if($url){
            if(is_dir($path)){
                $msg = array('code'=>300,'msg'=>lang('input_name'));
            }else{
                if(!file_exists($path)){
                    file_put_contents($path,$url['center']);
                    $msg = array('code'=>200,'msg'=>lang('create_dir_success'));
                }else{
                    $msg = array('code'=>300,'msg'=>lang('file_name_exist'));
                }
            }
        }else{
            $msg = array('code'=>300,'msg'=>lang('create_dir_error'));
        }
        return json_encode($msg);
    }
    public function createdir(){
        $param= request()->param('path') ?? '';
        View::assign('path',$param);
        View::assign('url',$param);
        return View();
    }
    public function saveDir(){
        $url = request()->param() ?? '';
        $path = root_path().$url['path'].'/'.$url['filename'];
        if($url['filename']){
            if(!file_exists($path)){
                mkdir($path,0777,true);
                $msg = array('code'=>200,'msg'=>lang('create_dir_success'));
            }else{
                $msg = array('code'=>300,'msg'=>lang('file_name_exist'));
            }
        }else{
            $msg = array('code'=>300,'msg'=>lang('create_dir_error_empty'));
        }
        return json_encode($msg);
    }
    //计算文件夹大小
    public function countSize(){
        $data = request()->param();
        $path = root_path();
        $dir = $path.$data['key'];
        $size = toSize(dirSize($dir));
        return json_encode(array('code'=>200,'size'=>$size));
    }
}