<?php
/**
 * Created by PhpStorm.
 * CreateTime  : 2025/07/12 16:13
 * file name : app.php
 * User: asusa
 * Author: Hyy-Cary（优）
 * Contact QQ  : 373889161(.)
 * email: 373889161@qq.com
 * WeChat: 18319021313
 */
use think\facade\Route;

Route::get('nav$', 'Navigation/index');

// 绑定到类
Route::bind('\app\index\controller\Article\index');
//访问：http://www.tp8.com/index/article/index/id/3

// 为 /article/id/3 配置路由，映射到 index/article/id
Route::get('article/id/:id', 'index/article/index');

//访问：http://www.tp8.com/article/id/3

// 为 /index/article/id/3 配置路由（保持原样）
Route::get('index/article/id/:id', 'index/article/index');
//访问：http://www.tp8.com/index/article/id/3

/*
 *  要配合 nginx 伪静态
 *
 * location / {
    if (!-e $request_filename){
        # http://www.tp8.com/article/id/3
        rewrite ^/article/id/(\d+)$ /index.php/index/article/id/$1 last;
        # http://www.tp8.com/index/article/id/3
        rewrite ^/index/article/id/(\d+)$ /index.php/index/article/id/$1 last;
		rewrite  ^(.*)$  /index.php?s=$1  last;   break;
	}
}
*/