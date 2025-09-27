<?php
/**
 * Created by PhpStorm.
 * CreateTime  : 2023/09/27 17:30
 * file name : Tags.php
 * User: asusa
 * Author: Hyy-Cary
 * Contact QQ  : 373889161(.)
 * email: 373889161@qq.com
 * WeChat: 18319021313
 */


namespace app\admin\controller;


use app\admin\BaseController;
use think\facade\Db;
use think\facade\View;

class Tags extends BaseController
{
    public function index()
    {
        return View();
    }

    public function datalist()
    {
        $data = request()->param();
        $size = $data['limit'] ? $data['limit'] : 10;
        $start = $data['page'] ? $data['page'] : 0;
        $where = [];
        if (array_key_exists('data', $data)) {
            foreach ($data['data'] as $k => $v) {
                $where[] = [$v['name'], 'like', '%' . $v['value'] . '%'];
            }
        }
        $list = Db::name('taglist')->alias('a')->join('category' . ' b ', 'b.id= a.cid')->leftJoin('article' . ' c ', 'c.tags LIKE CONCAT(\'%\', a.tag, \'%\')')->field('a.*,b.name,count(c.id) as ArCount')->where($where)->group('a.id, a.tag, b.name')->order(['a.id' => 'desc'])->page($start, $size)->select()->toArray();
        $count = CountTable('taglist', $where, 'a');
        foreach ($list as &$v) {
            $v['createTime'] = date('Y-m-d H:i:s', $v['createTime']);
        }
        $arr = array('code' => 0, 'msg' => 'ok', 'count' => $count, 'limit' => [], 'data' => $list);
        echo json_encode($arr);
    }

    public function delAll()
    {
        $id = request()->param('data');
        $data = Db::name('taglist')->where('id', 'in', $id)->select()->toArray();
        if ($data) {
            Db::name('taglist')->delete($id);
            $msg = ['code' => 200, 'msg' => lang('delete_message')];
        } else {
            $msg = ['code' => 300, 'msg' => lang('fail_message'), 'data' => $id];
        }
        echo json_encode($msg);
    }

    public function add(){
        $id = request()->param('id');
        $tree = GetMenu('category');
        $data = FindTable('taglist',[['id','=',$id]]);
        if(!$data){
            $data['id'] = '';
            $data['cid'] = '';
            $data['tag'] = '';
        }
        foreach ($tree as $k=>$v){
            $level = $v['level']-1;
            if( $level > 1){
                $tree[$k]['p'] = str_repeat("&nbsp;&nbsp;&nbsp;",$level).'|--';
            }else{
                $tree[$k]['p']='';
            }
        }
        View::assign('tree',$tree);
        View::assign('data',$data);
        return View();
    }

    public function saveAt(){
        $data = request()->param();
        if(!$data['id']){
            unset($data['id']);
        }
        $data['createTime'] = time();
        SaveAt('taglist',$data);
        $msg = ['code'=>200,'msg'=>lang('success_message')];
        return json_encode($msg);
    }
}