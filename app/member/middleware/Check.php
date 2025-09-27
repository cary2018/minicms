<?php
/**
 * Created by PhpStorm.
 * CreateTime  : 2024/08/13 15:12
 * file name : Check.php
 * User: asusa
 * Author: Hyy-Cary（优）
 * Contact QQ  : 373889161(.)
 * email: 373889161@qq.com
 * WeChat: 18319021313
 */
declare (strict_types = 1);

namespace app\member\middleware;

class Check
{
    /**
     * @param $request
     * @param \Closure $next
     * @return mixed|\think\response\Redirect
     *
     */
    public function handle($request, \Closure $next)
    {
        //当前访问路径
        $path = app('http')->getName().'/'.$request->pathinfo();
        //echo $path;
        //登录信息
        $session = GetSe('MemberCenter');
        if(stripos($path,'login') == false){
            if(!$session){
                return redirect((string)url('/member/login'));
            }
        }
        //前置中间件
        return $next($request);
    }
}