<?php
/**
 * Created by PhpStorm.
 * CreateTime  : 2023/11/25 23:12
 * file name : Login.php
 * User: asusa
 * Author: Hyy-Cary
 * Contact QQ  : 373889161(.)
 * email: 373889161@qq.com
 * WeChat: 18319021313
 */

namespace app\admin\controller;

use app\admin\BaseController;
use app\admin\validate\Login as LoginValidate;
use think\exception\ValidateException;
use think\facade\View;

class Login extends BaseController
{
    public function index(){
        $sys = Cfg('sys_title')?Cfg('sys_title'):'';
        View::assign('sys',$sys);
        $session = GetSe('admin');
        if($session){
            return redirect((string)url('/admin'));
        }
        $host = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].'/api/bing';
        view::assign('host',$host);
        return View();
    }
    public function check(){
        $data = request()->param();
        $url = app('http')->getName().'/'.request()->controller(true).'/'.request()->action();
        $ip = get_client_ip();
        $reg = new \Ip2Region();
        $region = $reg->btreeSearch($ip);
        $visit = [
            'username'=>$data['username'],
            'ip'=>$ip,
            'region'=>$region['region'],
            'path'=>$url,
            'remark'=>'',
            'createTime'=>time(),
        ];
        try {
            if(config('web.captcha_close') === 1){
                validate(LoginValidate::class)->check($data);
            }else{
                validate(LoginValidate::class)->remove('captcha','require')->check($data);
            }
            $user = FindTable('admin',[['username','=',$data['username']],['status','=',1],['group_id','=',0]]);
            //生成 token 防止验证失败 token 失效
            $token = request()->buildToken('__token__', 'sha1');
            if($user){
                if(PasswordVerify($data['password'],$user['password'])){
                    //保存登录信息
                    SetSe('admin',$user);
                    $msg = ['code'=>200,'msg'=>lang('login_success'),'jump_url'=>'/admin'];
                }else{
                    $msg = ['code'=>300,'msg'=>lang('login_error'),'token'=>$token];
                }
            }else{
                $msg = ['code'=>300,'msg'=>lang('login_error'),'token'=>$token];
            }
        } catch (ValidateException $e) {
            //生成 token 防止验证失败 token 失效
            $token = request()->buildToken('__token__', 'sha1');
            // 验证失败 输出错误信息
            $msg = ['code'=>300,'msg'=>$e->getError(),'token'=>$token];
        }
        $visit['remark'] = $msg['msg'];
        SaveAt('weblog',$visit);
        echo json_encode($msg);
    }
    public function logout(){
        //退出登录
        DelSe('admin');
        return redirect((string)url('/admin/login'));
    }
}