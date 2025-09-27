<?php
/**
 * Created by PhpStorm.
 * CreateTime  : 2023/03/24 18:18
 * file name : Role.php
 * User: asusa
 * Author: Hyy-Cary
 * Contact QQ  : 373889161(.)
 * email: 373889161@qq.com
 * WeChat: 18319021313
 */


namespace app\admin\validate;


use think\Validate;

class Role extends Validate
{
    protected $rule =   [
        'title'  => 'require',
    ];

    protected $message  =   [
        'title.require' => '角色名称不能为空',
    ];
}