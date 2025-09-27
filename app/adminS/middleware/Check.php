<?php
declare (strict_types = 1);

namespace app\admin\middleware;
use think\App;
class Check
{
    /**
     * 处理请求
     *
     * @param \think\Request $request
     * @param \Closure       $next
     * @return Response
     */
    public function handle($request, \Closure $next)
    {
        //当前访问路径
        $path = app('http')->getName().'/'.$request->pathinfo();
        //echo $path;
        //登录信息
        $session = GetSe('admin');
        if(stripos($path,'login') == false){
            if($session){
                //获取登录用户角色权限
                $user = FindTable('admin',[['id','=',$session['id']]]);
                if($user['isAdmin']!=1){
                    //权限验证
                    AuthNode($user['role'],$path);
                }
            }else{
                return redirect((string)url('/admin/login'));
            }
        }
        //前置中间件
        return $next($request);
    }
}
