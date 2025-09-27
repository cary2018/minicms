<?php
/**
 * Created by PhpStorm.
 * CreateTime  : 2023/03/26 20:06
 * file name : Log.php
 * User: asusa
 * Author: Hyy-Cary
 * Contact QQ  : 373889161(.)
 * email: 373889161@qq.com
 * WeChat: 18319021313
 */
declare (strict_types = 1);

namespace app\admin\middleware;



class Log
{
    /**
     * @param $request
     * @param \Closure $next
     * @return mixed
     *
     */
    public function handle($request, \Closure $next)
    {
        //后置中间件
        $response = $next($request);
        //登录信息
        $session = GetSe('admin');
        if($session){
            WebLog();
        }
        return $response;
    }
}