<?php
/**
 * Created by PhpStorm.
 * CreateTime  : 2025/04/16 23:51
 * file name : MusicController.php
 * User: asusa
 * Author: Hyy-Cary（优）
 * Contact QQ  : 373889161(.)
 * email: 373889161@qq.com
 * WeChat: 18319021313
 */
declare (strict_types = 1);

namespace app\api;

use think\App;
use think\exception\ValidateException;
use think\Validate;
abstract class MusicController
{
    /**
     * Request实例
     * @var \think\Request
     */
    protected $request;

    /**
     * 应用实例
     * @var \think\App
     */
    protected $app;

    /**
     * 是否批量验证
     * @var bool
     */
    protected $batchValidate = false;

    /**
     * 控制器中间件
     * @var array
     */
    protected $middleware = [];

    /**
     * 构造方法
     * @access public
     * @param  App  $app  应用对象
     */
    public function __construct(App $app)
    {
        $this->app     = $app;
        $this->request = $this->app->request;

        // 控制器初始化
        $this->initialize();
    }

    // 初始化
    protected function initialize()
    {
        $url = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        $ip = new \Ip2Region();
        $region = $ip->btreeSearch(get_client_ip());
        if(!$region){
            $region = '';
        }
        if(isset($_SERVER['HTTP_REFERER'])){
            $from = $_SERVER['HTTP_REFERER'];
        }else{
            $from = '';
        }
        $day = strtotime(date('Y-m-d',strtotime('-'. 0 .' day')));
        $uv = md5($day.uniqid().time());
        if(GetSe('clientExpTime') != $day || GetCk('guvs') == ''){
            SetSe('clientExpTime',$day);
            if(GetCk('guvs') == ''){
                SetCk('guvs',$uv,86400);
            }
        }
        $guv = GetCk('guvs');
        if(!$guv){
            //防止cookie初次访问未生效
            $guv = $uv;
        }
        $data = array(
            'ip'=>get_client_ip(),
            'guv'=>$guv,
            'from_url'=>$from,
            'to_url'=>$url,
            'region' => $region['region'],
            'createTime'=>time()
        );
        SaveAt('visit_api',$data);
    }

    /**
     * 验证数据
     * @access protected
     * @param  array        $data     数据
     * @param  string|array $validate 验证器名或者验证规则数组
     * @param  array        $message  提示信息
     * @param  bool         $batch    是否批量验证
     * @return array|string|true
     * @throws ValidateException
     */
    protected function validate(array $data, $validate, array $message = [], bool $batch = false)
    {
        if (is_array($validate)) {
            $v = new Validate();
            $v->rule($validate);
        } else {
            if (strpos($validate, '.')) {
                // 支持场景
                [$validate, $scene] = explode('.', $validate);
            }
            $class = false !== strpos($validate, '\\') ? $validate : $this->app->parseClass('validate', $validate);
            $v     = new $class();
            if (!empty($scene)) {
                $v->scene($scene);
            }
        }

        $v->message($message);

        // 是否批量验证
        if ($batch || $this->batchValidate) {
            $v->batch(true);
        }

        return $v->failException(true)->check($data);
    }
}