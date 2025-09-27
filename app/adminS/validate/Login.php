<?php
/**
 * Created by PhpStorm.
 * CreateTime  : 2023/11/26 15:04
 * file name : Login.php
 * User: asusa
 * Author: Hyy-Cary
 * Contact QQ  : 373889161(.)
 * email: 373889161@qq.com
 * WeChat: 18319021313
 */


namespace app\admin\validate;


use think\Validate;

class Login extends Validate
{
    protected $rule =   [
        'username'  => 'require|token',
        'password'   => 'require',
        'captcha'=> 'require|captcha'
    ];

    protected $message  =   [
        'username.require' => '用户名不能为空',
        'username.token' => 'token错误',
        'password.require'     => '密码不能为空',
        'captcha.require'   => '验证码不能为空',
        'captcha.captcha'   => '验证码错误',
    ];
}