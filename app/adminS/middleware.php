<?php
// 全局中间件定义文件
return [
    // 全局请求缓存
    // \think\middleware\CheckRequestCache::class,
    // 多语言加载
    // \think\middleware\LoadLangPack::class,
    //加载自定义中间件
    \app\admin\middleware\Check::class,
    \app\admin\middleware\Log::class,
];
