<?php
/**
 * Created by PhpStorm.
 * CreateTime  : 2024/06/14 15:41
 * file name : middleware.php
 * User: asusa
 * Author: Hyy-Cary
 * Contact QQ  : 373889161(.)
 * email: 373889161@qq.com
 * WeChat: 18319021313
 */

// 全局中间件定义文件
return [
    //加载自定义中间件
    \app\index\middleware\VisitLog::class,
    //加载站群配置
    \app\index\middleware\Domain::class,
];