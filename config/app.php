<?php
// +----------------------------------------------------------------------
// | 应用设置
// +----------------------------------------------------------------------
$domain = $_SERVER['HTTP_HOST'];
return [
    // 应用地址
    //'app_host'         => env('app.host', ''),
    'app_host'         => true,
    // 应用的命名空间
    'app_namespace'    => '',
    // 是否启用路由
    'with_route'       => true,
    // 是否支持多模块
    'app_multi_module'   => true,
    'auto_multi_app' => true,
    // 默认应用
    'default_app'      => 'index',
    // 默认时区
    'default_timezone' => 'Asia/Shanghai',

    // 应用映射（自动多应用模式有效）
    'app_map'          => [
        //'article' => 'index/article', // 将 article 映射到 index 模块的 article 控制器
    ],
    // 域名绑定（自动多应用模式有效）
    //'domain_bind'      => ['www.tp8.com' =>  'index','tp8.com' =>  'admin',],
    //'domain_bind'      => [$domain =>  ''],
    // 禁止URL访问的应用列表（自动多应用模式有效）
    'deny_app_list'    => [],

    // 异常页面的模板文件
    'exception_tmpl'   => app()->getThinkPath() . 'tpl/think_exception.tpl',
    //'exception_tmpl'   => root_path().'public/404.html',

    // 错误显示信息,非调试模式有效
    'error_message'    => '页面错误！请稍后再试～',
    // 显示错误信息
    'show_error_msg'   => false,
];
