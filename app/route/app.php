<?php
/**
 * Created by PhpStorm.
 * CreateTime  : 2026/01/04 19:51
 * file name : app.php
 * User: asusa
 * Author: Hyy-Cary（优）
 * Contact QQ  : 373889161(.)
 * email: 373889161@qq.com
 * WeChat: 18319021313
 */
//动态路由
Route::get('/:controller', 'index/:controller/index');
Route::get('/:controller/:action', 'index/:controller/:action');