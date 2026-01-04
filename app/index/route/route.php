<?php
/**
 * Created by PhpStorm.
 * CreateTime  : 2025/07/12 15:42
 * file name : route.php
 * User: asusa
 * Author: Hyy-Cary（优）
 * Contact QQ  : 373889161(.)
 * email: 373889161@qq.com
 * WeChat: 18319021313
 */
use think\facade\Route;

Route::get('navigation$', 'app\index\Navigation@index');  // 映射到 Navigation 控制器的 index 方法
// 将根路径路由到index应用的控制器
Route::get('/', 'index/index/index');

// 将控制器直接路由到index应用下的对应控制器
Route::get('/:controller/:action', 'index/:controller/:action');
/*Route::group('index', function () {
    //Route::get('navigation/:id', 'index/navigation');
    //Route::post('update', 'User/update');
});*/