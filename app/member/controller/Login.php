<?php
/**
 * Created by PhpStorm.
 * CreateTime  : 2024/08/13 13:37
 * file name : Login.php
 * User: asusa
 * Author: Hyy-Cary（优）
 * Contact QQ  : 373889161(.)
 * email: 373889161@qq.com
 * WeChat: 18319021313
 */


namespace app\member\controller;


use app\admin\validate\Login as LoginValidate;
use app\member\BaseController;
use think\exception\ValidateException;
use think\facade\View;

class Login extends BaseController
{
    public function index(){
        $host = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].'/api/bing';
        view::assign('host',$host);
        $user = GetSe('MemberCenter');
        if($user){
            return redirect((string)url('/member'));
        }
        return view();
    }
    public function check(){
        $data = request()->param();
        try {
            validate(LoginValidate::class)->check($data);
            $user = FindTable('admin',[['username','=',$data['username']],['status','=',1]]);
            //生成 token 防止验证失败 token 失效
            $token = request()->buildToken('__token__', 'sha1');
            if($user){
                if(PasswordVerify($data['password'],$user['password'])){
                    //保存登录信息
                    SetSe('MemberCenter',$user);
                    $msg = ['code'=>200,'msg'=>lang('login_success'),'jump_url'=>'/member'];
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
        echo json_encode($msg);
    }
    public function register(){
        $data = request()->param();
        $check = request()->checkToken('__token__', request()->param());
        //更新token
        $token = request()->buildToken('__token__', 'sha1');
        if(false === $check) {
            //throw new ValidateException('invalid token');
            $arr = ['code'=>300,'message'=>'令牌数据无效，请重新操作！','token'=>$token];
            return json_encode($arr,JSON_UNESCAPED_UNICODE);
        }
        //获取验证码
        $valida = GetSe('validaCode');
        //获取验证邮箱
        $validaEmail = GetSe('validaEmail');
        $user = FindTable('admin',[['username','=',$data['username']]]);
        if($user){
            $arr = ['code'=>300,'msg'=>'用户名已存在！','token'=>$token];
            return json_encode($arr,JSON_UNESCAPED_UNICODE);
        }
        if($data['password'] !== $data['repass']){
            $arr = ['code'=>300,'msg'=>'密码不一致！','token'=>$token];
            return json_encode($arr,JSON_UNESCAPED_UNICODE);
        }
        if($valida == $data['VCode'] && $validaEmail == $data['Eemail']){
            $data['email'] = $data['Eemail'];
            $data['createTime'] = time();
            $data['updateTime'] = time();
            $data['status'] = PasswordSet($data['password']);
            $data['status'] = 1;
            unset($data['__token__']);
            unset($data['Eemail']);
            unset($data['VCode']);
            unset($data['repass']);
            SaveAt('admin',$data);
            $member = FindTable('admin',[['username','=',$data['username']],['status','=',1]]);
            //保存登录信息
            SetSe('MemberCenter',$member);
            $arr = ['code'=>200,'msg'=>'注册成功！'];
            DelSe('validaCode');
            DelSe('validaEmail');
        }else{
            $arr = ['code'=>300,'msg'=>'验证码错误或邮箱号与验证码不匹配！','token'=>$token];
        }
        return json_encode($arr,JSON_UNESCAPED_UNICODE);
    }
    public function logout(){
        $jump = request()->param();
        //退出登录
        DelSe('MemberCenter');
        if($jump){
            return json_encode(['code'=>200]);
        }else{
            return redirect((string)url('/member/login'));
        }
    }
}