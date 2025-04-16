<?php
/**
 * Created by PhpStorm.
 * CreateTime  : 2024/10/19 19:08
 * file name : Domain.php
 * User: asusa
 * Author: Hyy-Cary（优）
 * Contact QQ  : 373889161(.)
 * email: 373889161@qq.com
 * WeChat: 18319021313
 * 加载站群配置
 */

declare (strict_types = 1);

namespace app\index\middleware;

use think\facade\Config;
use think\facade\View;

class Domain
{
    public function handle($request,\Closure $next){
        $domain = $_SERVER['HTTP_HOST'];
        $arrayInfo = CfgInfo('domain');
        if($arrayInfo){
            if(array_key_exists($domain,$arrayInfo)){
                $webInfo = $arrayInfo[$domain];
                $webInfo = array_filter($webInfo);
                Config::set($webInfo,'web');
            }
        }
        // 指定要读取的目录
        $directory = 'template/'.Cfg('view_path').'/';
        // 配置模板路径
        View::config(['view_path' =>$directory ]);
        $templateDir = '/'.$directory;
        //当前模板路径
        View::assign('view_path',$templateDir);
        /*print_r($info);
        die;*/
        //前置中间件
        return $next($request);
    }
}