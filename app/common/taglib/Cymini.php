<?php
/**
 * Created by PhpStorm.
 * CreateTime  : 2024/05/12 00:45
 * file name : Minicms.php
 * User: asusa
 * Author: Hyy-Cary
 * Contact QQ  : 373889161(.)
 * email: 373889161@qq.com
 * WeChat: 18319021313
 */


namespace app\common\taglib;
use think\template\TagLib;

class Cymini extends TagLib
{
    /**
     * 定义标签列表
     */
    protected $tags = [
        // 标签定义： attr 属性列表 close 是否闭合（0 或者1 默认1） alias 标签别名 level 嵌套层次
        'banner'              => ['attr' => 'num', 'expression' => 1,'close'=>1], //闭合标签，默认为不闭合
        'emoji'              => ['attr' => 'num', 'expression' => 1,'close'=>1], //闭合标签，默认为不闭合
        'search'              => ['attr' => 'num', 'expression' => 1,'close'=>1], //闭合标签，默认为不闭合
        'link'                => ['attr' => 'start,num','expression'=>1,'close'=>1],
        'tags'                => ['attr' => 'start,num','expression'=>1,'close'=>1],
        'attrid'              => ['attr' => 'num,aid','expression'=>1,'close'=>1],
        'article'             => ['attr' => 'start,num,cid,order','expression'=>1,'close'=>1],
        'navtop'             => ['attr' => '','expression'=>1,'close'=>1],
        'navmenu'             => ['attr' => '','expression'=>1,'close'=>1],
        'cate'                => ['attr' => '','expression'=>1,'close'=>0],
        'detail'              => ['attr' => '','expression'=>1,'close'=>0],
        'page'                => ['attr' => '','close'=>0],
        'total'               => ['attr' => '','close'=>0],
        'totalcount'          => ['attr' => '','close'=>0],
        'feedback'            => ['attr' => 'aid,num,start','expression'=>1,'close'=>1],
        'breadcrumb'          => ['attr' => 'aid','expression'=>1,'close'=>1],
        'next'                => ['attr' => 'cid','expression'=>1,'close'=>1],
        'navigation'          => ['attr' => 'cid','expression'=>1,'close'=>1],
        'navlist'             => ['attr' => 'cid','expression'=>1,'close'=>1],
        'table'               => ['attr' => 'table,where','expression'=>1,'close'=>1],
        'sum'                 => ['attr' => 'table,where','expression'=>1,'close'=>0],
        'rand'                => ['attr' => 'cid,num','expression'=>1,'close'=>1],
        'vod'                 => ['attr' => 'where','expression'=>true,'close'=>1],
        'voddetail'           => ['attr' => 'where','expression'=>1,'close'=>0],
        'vodplay'             => ['attr' => 'where','expression'=>1,'close'=>0],
        'playlist'            => ['attr' => 'where','expression'=>1,'close'=>1],
    ];

    /**
     * @param $tag
     * @param $content
     * @return string
     * banner 广告图片
     */
    public function tagBanner($tag, $content)
    {
        if(empty($tag['id'])){
            $tag['id'] = 'vo';
        }
        if(empty($tag['key'])){
            $tag['key'] = 'key';
        }
        if(empty($tag['where'])){
            $tag['where'] = [];
        }
        $parse = '<?php ';
        $parse .= '$__args__ = \'' . json_encode($tag) . '\';';
        $parse .= '$__Shared__ = SharedTable($__args__);';
        $parse .= ' ?>';
        $parse .= '{volist name="$__Shared__" id="'. $tag['id'].'" key="'.$tag['key'].'"}';
        $parse .= $content;
        $parse .= '{/volist}';
        return $parse;
    }

    public function tagEmoji($tag,$content){
        if(empty($tag['id'])){
            $tag['id'] = 'vo';
        }
        if(empty($tag['key'])){
            $tag['key'] = 'key';
        }
        $parse = '<?php ';
        $parse .= '$__emoji__ = emojiGif();';
        $parse .= ' ?>';
        $parse .= '{volist name="$__emoji__" id="'. $tag['id'].'" key="'.$tag['key'].'"}';
        $parse .= $content;
        $parse .= '{/volist}';
        return $parse;
    }

    public function tagNavmenu($tag, $content){
        //导航菜单
        if(empty($tag['id'])){
            $tag['id'] = 'vo';
        }
        if(empty($tag['key'])){
            $tag['key'] = 'key';
        }
        if(empty($tag['type'])){
            $tag['type'] = 0;
        }
        $parse = '<?php ';
        $parse .= '$__navmenu__ = NavMenu('.intval($tag['type']).');';
        $parse .= '$__LIST__ = $__navmenu__;';
        $parse .= ' ?>';
        $parse .= '{volist name="$__LIST__" id="' . $tag['id'] . '" key="'.$tag['key'].'"';
        if(!empty($tag['type'])){
            $parse .= ' type="'.$tag['type'].'"';
        }
        $parse .= '}';
        $parse .= $content;
        $parse .= '{/volist}';
        return $parse;
    }

    /**
     * @param $tag
     * @param $content
     * @return string
     * 友情链接
     */
    public function tagLink($tag, $content)
    {
        if(empty($tag['id'])){
            $tag['id'] = 'vo';
        }
        if(empty($tag['key'])){
            $tag['key'] = 'key';
        }
        if(empty($tag['num'])){
            $tag['num'] = 10;
        }
        $parse = '<?php ';
        $parse .= '$__link__ = pageTable(\'link\',0,' . intval($tag['num']) . ',[\'enable\'=>1]' .',[\'orderSort\'=>\'desc\']);';
        $parse .= '$__LIST__ = $__link__;';
        $parse .= ' ?>';
        $parse .= '{volist name="__LIST__" id="' . $tag['id'] . '" key="'.$tag['key'].'"';
        if(!empty($tag['num'])){
            $parse .= ' num="'.$tag['num'].'"';
        }
        $parse .= '}';
        $parse .= $content;
        $parse .= '{/volist}';
        return $parse;
    }

    public function tagTags($tag, $content){
        if(empty($tag['id'])){
            $tag['id'] = 'vo';
        }
        if(empty($tag['key'])){
            $tag['key'] = 'key';
        }
        if(empty($tag['start'])){
            $tag['start'] = 0;
        }
        if(empty($tag['num'])){
            $tag['num'] = 10;
        }
        $parse = '<?php ';
        $parse .= '$__Tags__ = Tags('.intval($tag['start']).',' . intval($tag['num']) . ');';
        $parse .= '$__LIST__ = $__Tags__;';
        $parse .= ' ?>';
        $parse .= '{volist name="__LIST__" id="' . $tag['id'] . '" key="'.$tag['key'].'"';
        if(!empty($tag['num'])){
            $parse .= ' num="'.$tag['num'].'"';
        }
        if(!empty($tag['start'])){
            $parse .= ' start="'.$tag['start'].'"';
        }
        $parse .= '}';
        $parse .= $content;
        $parse .= '{/volist}';
        return $parse;
    }

    public function tagAttrId($tag, $content)
    {
        if(empty($tag['id'])){
            $tag['id'] = 'vo';
        }
        if(empty($tag['key'])){
            $tag['key'] = 'key';
        }
        if(empty($tag['num'])){
            $tag['num'] = 10;
        }
        $parse = '<?php ';
        $parse .= '$__attrid__ = AttrId(' . intval($tag['num']) . ','.intval($tag['aid']).');';
        $parse .= '$__LIST__ = $__attrid__;';
        $parse .= ' ?>';
        $parse .= '{volist name="__LIST__" id="' . $tag['id'] . '" key="'.$tag['key'].'"';
        if(!empty($tag['num'])){
            $parse .= ' num="'.$tag['num'].'"';
        }
        if(!empty($tag['aid'])){
            $parse .= ' aid="'.$tag['aid'].'"';
        }
        $parse .= '}';
        $parse .= $content;
        $parse .= '{/volist}';
        return $parse;
    }

    public function tagArticle($tag, $content)
    {
        if(empty($tag['id'])){
            $tag['id'] = 'vo';
        }
        if(empty($tag['key'])){
            $tag['key'] = 'key';
        }
        if(empty($tag['cid'])){
            $tag['cid'] = '""';
        }
        if(empty($tag['order'])){
            $tag['order'] = 'id';
        }
        if(empty($tag['num'])){
            $tag['num'] = 12;
        }
        if(empty($tag['start'])){
            $tag['start'] = 0;
        }
        $parse = '<?php ';
        $parse .= '$__article__ = Article('.$tag['cid'].',"' . $tag['order'] . '",' . intval($tag['num']) . ','.intval($tag['start']).');';
        $parse .= '$__total__ = $__article__["__total__"];';
        $parse .= '$__LIST__ = $__article__["data"];';
        $parse .= ' ?>';
        $parse .= '{volist name="__LIST__" id="' . $tag['id'] . '" key="'.$tag['key'].'"';
        if(!empty($tag['id'])){
            $parse .= ' cid='.$tag['cid'].'';
        }
        if(!empty($tag['order'])){
            $parse .= ' order="'.$tag['order'].'"';
        }
        if(!empty($tag['num'])){
            $parse .= ' num="'.$tag['num'].'"';
        }
        if(!empty($tag['id'])){
            $parse .= ' cid="'.$tag['cid'].'"';
        }
        $parse .= '}';
        $parse .= $content;
        $parse .= '{/volist}';
        return $parse;
    }

    public function tagSearch($tag,$content){
        if(empty($tag['id'])){
            $tag['id'] = 'vo';
        }
        if(empty($tag['key'])){
            $tag['key'] = 'key';
        }
        $parse = '<?php ';
        $parse .= '$__Search__ = Search(\''.json_encode($tag).'\');';
        $parse .= '$__total__ = $__Search__["__total__"];';
        $parse .= '$__Search__ = $__Search__["data"];';
        $parse .= ' ?>';
        $parse .= '{volist name="$__Search__" id="'. $tag['id'].'" key="'.$tag['key'].'"}';
        $parse .= $content;
        $parse .= '{/volist}';

        return $parse;
    }

    public function tagNavtop($tag, $content)
    {
        if(empty($tag['id'])){
            $tag['id'] = 'vo';
        }
        if(empty($tag['key'])){
            $tag['key'] = 'key';
        }
        if(empty($tag['num'])){
            $tag['num'] = 16;
        }
        $parse = '<?php ';
        $parse .= '$__navtop__ = topNav('.$tag['num'].');';
        $parse .= ' ?>';
        $parse .= '{volist name="__navtop__" id="' . $tag['id'] . '" key="'.$tag['key'].'"}';
        $parse .= $content;
        $parse .= '{/volist}';
        return $parse;
    }

    public function tagFeedback($tag, $content){
        if(empty($tag['id'])){
            $tag['id'] = 'vo';
        }
        if(empty($tag['key'])){
            $tag['key'] = 'key';
        }
        if(empty($tag['aid'])){
            $tag['aid'] = '';
        }
        if(empty($tag['cate'])){
            $tag['cate'] = 0;
        }
        if(empty($tag['num'])){
            $tag['num'] = 12;
        }
        if(empty($tag['start'])){
            $tag['start'] = 0;
        }
        $parse = '<?php ';
        $parse .= '$__feedback__ = Feedback('.intval($tag['aid']).','.intval($tag['cate']).','.intval($tag['num']).','.intval($tag['start']).');';
        $parse .= '$__total__ = $__feedback__["__total__"];';
        $parse .= '$__LIST__ = $__feedback__["data"];';
        $parse .= ' ?>';
        $parse .= '{volist name="__LIST__" id="' . $tag['id'] . '" key="'.$tag['key'].'"';
        if(!empty($tag['aid'])){
            $parse .= ' aid="'.$tag['aid'].'"';
        }
        if(!empty($tag['cate'])){
            $parse .= ' cate="'.$tag['cate'].'"';
        }
        if(!empty($tag['num'])){
            $parse .= ' num="'.$tag['num'].'"';
        }
        if(!empty($tag['start'])){
            $parse .= ' start="'.$tag['start'].'"';
        }
        $parse .= '}';
        $parse .= $content;
        $parse .= '{/volist}';
        return $parse;
    }

    public function tagBreadcrumb($tag, $content){
        if(empty($tag['id'])){
            $tag['id'] = 'vo';
        }
        if(empty($tag['key'])){
            $tag['key'] = 'key';
        }
        if(empty($tag['aid'])){
            $tag['aid'] = 'request()->param("id")';
        }
        $parse = '<?php ';
        $parse .= '$__Breadcrumb__ = Breadcrumb('.$tag['aid'].');';
        $parse .= ' ?>';
        $parse .= '{volist name="__Breadcrumb__" id="' . $tag['id'] . '" key="'.$tag['key'].'"';
        if(!empty($tag['aid'])){
            $parse .= ' aid="'.$tag['aid'].'"';
        }
        $parse .= '}';
        $parse .= $content;
        $parse .= '{/volist}';
        return $parse;
    }

    public function tagNext($tag,$content){
        if(empty($tag['id'])){
            $tag['id'] = 'vo';
        }
        if(empty($tag['key'])){
            $tag['key'] = 'key';
        }
        if(empty($tag['cid'])){
            $tag['cid'] = '';
        }
        if(empty($tag['pae'])){
            $tag['pae'] = ">";
        }
        if(empty($tag['order'])){
            $tag['order'] = 'asc';
        }
        $parse = '<?php ';
        $parse .= '$__next__ = FindTable("article",[["status","=",1],["cid","=",'.$tag['cid'].'],["id","'.$tag['pae'].'",request()->param(\'id\')]],["id"=>"'.$tag['order'].'"]);';
        $parse .= '$__cate__ = FindTable("category",[["id","=",'.$tag['cid'].'],["isShow","=",1]]);';
        $parse .= 'if($__next__){ $__next__["temp_archives"] = $__cate__["temp_archives"]; }';
        $parse .= 'if($__next__){ $pare = array(0=>$__next__);}else{ $pare = array();}';
        $parse .= '$__LIST__ = $pare;';
        $parse .= ' ?>';
        $parse .= '{volist name="__LIST__" id="' . $tag['id'] . '" key="'.$tag['key'].'"';
        if(!empty($tag['cid'])){
            $parse .= ' cid="'.$tag['cid'].'"';
        }
        if(!empty($tag['pae'])){
            $parse .= ' pae="'.$tag['pae'].'"';
        }
        if(!empty($tag['order'])){
            $tag['order'] = 'asc';
        }
        $parse .= '}';
        $parse .= $content;
        $parse .= '{/volist}';
        return $parse;
    }

    public function tagDetail($tag){
        if(empty($tag['id'])){
            $tag['id'] = 'vo';
        }
        if(empty($tag['key'])){
            $tag['key'] = 'key';
        }
        $field = 1;
        if(empty($tag['field'])){
            $field = 0;
        }
        $parse = '<?php ';
        $parse .= '$__detail__ = Detail(request()->param(\'id\'));';
        $parse .= 'if(('.$field.')){';
        $parse .= 'echo $__detail__["'.$tag['field'].'"];';
        $parse .= '}';
        $parse .= ' ?>';
        return $parse;
    }

    public function tagCate($tag){
        $field = 1;
        if(empty($tag['field'])){
            $field = 0;
        }
        $parse = '<?php ';
        $parse .= '$__category__ = FindTable("category",[["id","=",request()->param(\'id\')],["isShow","=",1]]);';
        $parse .= 'if($__category__){echo $__category__["'.$tag['field'].'"];}';
        $parse .= ' ?>';
        return $parse;
    }

    /**
     * @param $tag
     * @return string
     * 分页标签
     */
    public function tagPage($tag)
    {
        if(empty($tag['total'])){
            $tag['total'] = 0;
        }
        if(empty($tag['num'])){
            $tag['num'] = 10;
        }
        $parse = '<?php ';
        $parse .= '$__page__ = pageBar('.$tag['total'].','.$tag['num'].');';
        $parse .= 'echo $__page__;';
        $parse .= ' ?>';
        return $parse;
    }

    /**
     * 统计
     */
    public function tagTotal($tag)
    {
        if(empty($tag['table'])){
            $tag['table'] = 'article';
        }
        if(empty($tag['where'])){
            $tag['where'] = '[["status","=",1]]';
        }
        $parse = '<?php ';
        $parse .= '$__totals__ = CountTable("'.$tag['table'].'",'.$tag['where'].');';
        $parse .= 'echo $__totals__;';
        $parse .= ' ?>';
        return $parse;
    }

    public function tagTotalCount($tag)
    {
        if(empty($tag['table'])){
            $tag['table'] = 'article';
        }
        $today_start=mktime(0,0,0,date('m'),date('d'),date('Y'));
        $today_end=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
        if(empty($tag['where'])){
            $tag['where'] = "[['createTime','between',[mktime(0,0,0,date('m'),date('d'),date('Y')),mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1]]]";
        }
        $parse = '<?php ';
        $parse .= '$__totals__ = CountTable("'.$tag['table'].'",'.$tag['where'].');';
        $parse .= 'echo $__totals__;';
        $parse .= ' ?>';
        return $parse;
    }

    public function tagNavigation($tag,$content){
        if(empty($tag['id'])){
            $tag['id'] = 'vo';
        }
        if(empty($tag['key'])){
            $tag['key'] = 'key';
        }
        $parse = '<?php ';
        $parse .= '$__navigation__ = getNav();';
        $parse .= ' ?>';
        $parse .= '{volist name="__navigation__" id="' . $tag['id'] . '" key="'.$tag['key'].'"';
        $parse .= '}';
        $parse .= $content;
        $parse .= '{/volist}';
        return $parse;
    }
    public function tagNavlist($tag,$content){
        if(empty($tag['id'])){
            $tag['id'] = 'vo';
        }
        if(empty($tag['key'])){
            $tag['key'] = 'key';
        }
        $parse = '<?php ';
        $parse .= '$__navlist__ = navlist(\''.json_encode($tag).'\');';
        $parse .= '$__total__ = $__navlist__[0]["__total__"];';
        $parse .= ' ?>';
        $parse .= '{volist name="__navlist__" id="' . $tag['id'] . '" key="'.$tag['key'].'"';
        $parse .= '}';
        $parse .= $content;
        $parse .= '{/volist}';
        return $parse;
    }

    public function tagTable($tag, $content){
        if(empty($tag['id'])){
            $tag['id'] = 'vo';
        }
        if(empty($tag['key'])){
            $tag['key'] = 'key';
        }
        if(empty($tag['table'])){
            $tag['table'] = 'category';
        }
        if(empty($tag['where'])){
            $tag['where'] = '[]';
        }
        if(empty($tag['order'])){
            $tag['order'] = '';
        }
        $parse = '<?php ';
        $parse .= '$__LIST__ = AllTable("'.$tag['table'].'",['.$tag['where'].'],"'.$tag['order'].'");';
        $parse .= ' ?>';
        $parse .= '{volist name="$__LIST__" id="'. $tag['id'].'" key="'.$tag['key'].'"';
        if(!empty($tag['table'])){
            $parse .= ' table="'.$tag['table'].'"';
        }
        if(!empty($tag['where'])){
            $parse .= ' where="'.$tag['where'].'"';
        }
        $parse .= '}';
        $parse .= $content;
        $parse .= '{/volist}';

        return $parse;
    }
    public function tagSum($tag){
        if(empty($tag['table'])){
            $tag['table'] = 'article';
        }
        if(empty($tag['where'])){
            $tag['where'] = '[["status","=",1]]';
        }
        if(empty($tag['field'])){
            $tag['field'] = 'views';
        }
        $parse = '<?php ';
        $parse .= '$__sum__ = SumField("'.$tag['table'].'",'.$tag['where'].',"'.$tag['field'].'");';
        $parse .= 'echo $__sum__;';
        $parse .= ' ?>';
        return $parse;
    }
    public function tagRand($tag,$content){
        if(empty($tag['id'])){
            $tag['id'] = 'vo';
        }
        if(empty($tag['key'])){
            $tag['key'] = 'key';
        }
        if(empty($tag['cid'])){
            $tag['cid'] = '';
        }
        if(empty($tag['num'])){
            $tag['num'] = 10;
        }
        $parse = '<?php ';
        $parse .= '$__Rand__ = RandRow("'.$tag['cid'].'",'.$tag['num'].');';
        $parse .= '$__LIST__ = $__Rand__["data"];';
        $parse .= ' ?>';
        $parse .= '{volist name="__LIST__" id="'. $tag['id'].'" key="'.$tag['key'].'"';
        if(!empty($tag['cid'])){
            $parse .= ' cid="'.$tag['cid'].'"';
        }
        if(!empty($tag['num'])){
            $parse .= ' num="'.$tag['num'].'"';
        }
        $parse .= '}';
        $parse .= $content;
        $parse .= '{/volist}';

        return $parse;
    }

    /**
     * @param $tag
     * @param $content
     * @return string
     * 示例
     * {cymini:vod where='[["type_id","=",17]]' limit='5' order="vod_time desc"}
     * {$vo.vod_name}--->{$vo.nickname} ---{$vo.vod_pic}
     * {/cymini:vod}
     *
     */
    public function tagVod($tag,$content){
        if(empty($tag['id'])){
            $tag['id'] = 'vo';
        }
        if(empty($tag['key'])){
            $tag['key'] = 'key';
        }
        if(!empty($tag['where'])){
            $arr = json_decode($tag['where']);
            $tag['where'] = array_merge($arr,[['vod_status','=',1]]);
        }else{
            $tag['where'] = [['vod_status','=',1]];
        }
        $parse = '<?php ';
        $parse .= '$__TAG__ = \'' . json_encode($tag) . '\';';
        $parse .= '$__Vod__ = VodData($__TAG__);';
        $parse .= ' ?>';
        $parse .= '{volist name="__Vod__" id="'. $tag['id'].'" key="'.$tag['key'].'"}';
        $parse .= $content;
        $parse .= '{/volist}';

        return $parse;
    }
    public function tagVoddetail($tag){

        if(!empty($tag['where'])){
            $arr = json_decode($tag['where']);
            $tag['where'] = array_merge($arr,[['vod_status','=',1]]);
        }else{
            $tag['where'] = [['vod_status','=',1]];
        }

        $parse = '<?php ';
        $parse .= '$__vod__ = VodDetail(\''.json_encode($tag).'\');';
        if(!empty($tag['field'])){
            $parse .= 'echo $__vod_detail__["'.$tag['field'].'"];';
        }
        $parse .= ' ?>';
        return $parse;
    }

    public function tagVodPlay($tag){
        $parse = '<?php ';
        $parse .= '$__play__ = VodPlay();';
        $parse .= '$is_ps = $__play__["select_ps"];';
        $parse .= '$parse_url = "https://cdn.zyc888.top/?url=";';
        $parse .= 'if($is_ps == 1){ $parse_url = $__play__["select_parse"]; }';
        $parse .= '$play_url = $__play__["select_url"];';
        if(!empty($tag['play'])){
            $parse .= 'echo "<iframe width=\"100%\" height=\"100%\" src=\"'.'$parse_url'.'$play_url'.'\" frameborder=\"0\" allowfullscreen=\"true\" border=\"0\" marginwidth=\"0\" marginheight=\"0\" scrolling=\"no\"></iframe>";';
        }
        if(!empty($tag['names'])){
            $parse .= 'echo $__play__["'.$tag['names'].'"];';
        }
        $parse .= ' ?>';
        return $parse;
    }

    public function tagPlayList($tag,$content){
        if(empty($tag['id'])){
            $tag['id'] = 'vo';
        }
        if(empty($tag['key'])){
            $tag['key'] = 'key';
        }
        $parse = '<?php ';
        $parse .= '$__vod_lIST__ = playLIst();';
        $parse .= ' ?>';
        $parse .= '{volist name="$__vod_lIST__" id="'. $tag['id'].'" key="'.$tag['key'].'"}';
        $parse .= $content;
        $parse .= '{/volist}';

        return $parse;
    }
}