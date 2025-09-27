<?php
/**
 * Created by PhpStorm.
 * CreateTime  : 2023/03/22 22:02
 * file name : Admin.php
 * User: asusa
 * Author: Hyy-Cary
 * Contact QQ  : 373889161(.)
 * email: 373889161@qq.com
 * WeChat: 18319021313
 */


namespace app\admin\validate;


use think\Validate;

class Admin extends Validate
{
    protected $rule =   [
        'username'  => 'require',
        'password'   => 'require|length:6,16',
        'repassword' => 'requireWith:password|confirm:password',
        'captcha'=> 'require|captcha'
    ];

    protected $message  =   [
        'username.require' => '用户名不能为空',
        'username.token' => 'token错误',
        'password.require'     => '密码不能为空',
        'password.length'     => '密码长度不能少于6位',
        'repassword.requireWith' => '确认密码不能为空',
        'repassword.confirm'     => '确认密码错误',
        'captcha.require'   => '验证码不能为空',
        'captcha.captcha'   => '验证码错误',
    ];
    protected $scene = [
        'add'  =>  ['username','password','repassword'],
        'edit'  =>  ['username','repassword'],
    ];
}