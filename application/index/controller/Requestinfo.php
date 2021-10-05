<?php
namespace app\index\controller;
use app\index\controller\Base;
use think\Request;
use think\Db;

class Requestinfo extends Base
{
    /**
    * 前台ajax请求文章数据方法/注意所有参数存放'matter'下
    * ['tab']  查询的栏目id或者表名      {tab:"1/news"}
    * ['limit']  查询数量  注意:limit="第几行开始,数量" 或 limit="数量" ; 默认查询15条数据   {limit:"15/0,15"}
    * ['father']  是否递归子孙数据 true是  false只查自身//此项对表查询无效   {father:"true/false"}
    * ['operate'] 操作类型(0栏目最新 1栏目推荐)[tab=栏目id](2表最新 3表推荐)[tab=表名,无需表前缀]4 查询所有表信息[默认]   {operate:"0/1/2/3/4"}
    * ['order']  排序依据 默认create_time desc(倒序【最新】)  {order:"create_time desc"}
    * 
    * 请求地址示例 {:url('index/Requestinfo/matter')}
    * 提交参数示例 {tab:"1",operate:"0"} 栏目1最新数据
    **/
    public function matter(Request $request){
        if(request()->isAjax()){
        $info = $request->param("matter");
        $where[] = ["a.delete_time","=",NULL];
        $where[] = ["a.status","=",0];
        if(empty($info)){
            return jsonmsg(0,'参数错误');
        }
        if(!isset($info['operate'])){//默认操作查所有
	        $info['operate'] = 4;
	    }
        if(!in_array($info['operate'],array("0","1","2","3","4"))){//不在类型内 结束
	        return jsonmsg(0,'操作类型不存在');
	    }
	    if($info['operate'] != 4){
	        if(!isset($info['tab']) || empty($info['tab'])){
	        return jsonmsg(0,'请指定查询目标');
	    }
	    }
	    $name = "";
	    $names = "";
	    if(!isset($info['order'])){//如果未填写排序条件给定默认值
	        $info['order'] = "create_time desc";
	    }
	    if(!isset($info['limit'])){//如果未填写数量限制给定默认值/防止数据量过大造成内存过大开销
	        $info['limit'] = "15";
	    }
	    if(!isset($info['father'])){//默认递归查子孙
            $info['father'] = 'true';
        }
        if($info['operate'] == 0 || $info['operate'] == 2){//根据操作方法重定排序条件
            $info['order'] = "create_time desc";
        }else if($info['operate'] == 1 || $info['operate'] == 3){//根据操作方法增加where条件
            $where[] = ["a.top","=",1];
        }
        if($info['operate'] == 2 || $info['operate'] == 3 || $info['operate'] == 4){//是否查子孙对表查询无效直接改写为查子孙
            $info['father'] = 'true';
        }
        if($info['operate'] != 4){//如果不是查询所有 处理相关值
        if(is_numeric($info['tab'])){//如果查询目标是数字也就是栏目id进入函数muyname处理数据
            $tabinfo = muyname($info['tab'],$info['father']);
            $where[] = ["a.mid","in",$tabinfo['mid']];
            $name = $tabinfo['tablename'];
            $names = $tabinfo['ftabname'];
        }else{//如果不是数字定义主表和附表
            $name = $info['tab'];
            $names = $info['tab']."_data";
        }
        if(!empty($name) && !empty($names)){
            $matter = Db::name($name)
	        ->alias("a")
	        ->fieldRaw("a.*,b.likes,b.browse,comment_t,c.title as catitle,d.name,d.photo,d.id as userid,d.email as uemail,e.title as tytitle")
	        ->join($names." b","find_in_set(b.aid,a.id)")
	        ->leftjoin("category c","find_in_set(c.id,a.mid)")
	        ->leftjoin("member d","find_in_set(d.id,a.uid)")
	        ->leftjoin("type e","find_in_set(e.id,a.type)")
	        ->where($where)
	        ->orderRaw($info['order'])
	        ->limit($info['limit'])
	        ->select()
	        ->toArray();
	        foreach($matter as $key=>$v){
	            $matter[$key]['create_time'] = date('Y-m-d H:i:s', $v['create_time']);
	            $matter[$key]['delete_time'] = date('Y-m-d H:i:s', $v['delete_time']);
	            $matter[$key]['update_time'] = date('Y-m-d H:i:s', $v['update_time']);
	        }
	        return json_encode($matter,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
        }else{//如果指定查询处理失败 走查询所有返回 不提示
            $matter = setallmat($info['limit'],$info['order']);//使用函数setallmat获取所有文章 传递数量以及排序
            foreach($matter as $key=>$v){
	            $matter[$key]['create_time'] = date('Y-m-d H:i:s', $v['create_time']);
	            $matter[$key]['delete_time'] = date('Y-m-d H:i:s', $v['delete_time']);
	            $matter[$key]['update_time'] = date('Y-m-d H:i:s', $v['update_time']);
	        }
            return json_encode($matter,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
        }
        }else{
            $matter = setallmat($info['limit'],$info['order']);//使用函数setallmat获取所有文章 传递数量以及排序
            foreach($matter as $key=>$v){
	            $matter[$key]['create_time'] = date('Y-m-d H:i:s', $v['create_time']);
	            $matter[$key]['delete_time'] = date('Y-m-d H:i:s', $v['delete_time']);
	            $matter[$key]['update_time'] = date('Y-m-d H:i:s', $v['update_time']);
	        }
            return json_encode($matter,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
        }
        return jsonmsg(0,'查询失败');
        }
        return jsonmsg(0,'非法请求');
    }
    /**
    *前台ajax请求栏目数据方法/注意所有参数存放'hnav'下
    * ['all'] 是否调用所有数据  是 true  否 false  {all:"true/false"} 默认调用所有
    * ['hid'] 指定栏目ID数据  {hid:"1"}
    * ['order']  数据排序条件 默认id asc  {order:"id asc"}
    * ['limit']  查询数量  注意:limit="第几行开始,数量" 或 limit="数量" ; 默认查询15条数据   {limit:"15/0,15"}
    * 
    * 
    **/
    public function hnavs(Request $request){
        if(request()->isAjax()){
            $info = $request->param("hnav");
            if(empty($info)){
                return jsonmsg(0,'参数错误');
            }
            $info['all'] = "true";
            if(isset($info['hid'])){
                $info['all'] = "false";
            }
            if(!isset($info['order'])){
                $info['order'] = "id asc";
            }
            if(!isset($info['limit'])){
                $info['limit'] = "10";
            }
            $hnav = array();
            if($info['all'] == "false"){
            if(!is_numeric($info['hid'])){
                return jsonmsg(0,'hid参数错误');
            }    
            $hnav = hnavs($info['limit'],$info['order'],$info['hid']);
            }else if($info['all'] == "true"){
            $hnav = hnavs($info['limit'],$info['order']);    
            }else{
                return jsonmsg(0,'参数错误');
            }
            if(!empty($hnav)){
            return json_encode($hnav,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
            }else{
                return jsonmsg(0,'查询失败');
            }
        }
        return jsonmsg(0,'非法请求');
    }
    
    
    
    /**
    *前台ajax请求评论数据方法/注意所有参数存放'comment'下
    * ['comid'] 调用指定ID的评论
    * ['matid'] 调用某文章ID的评论
    * ['uid'] 调用某用户ID的评论
    * ['limit'] 限制调用数量， 默认30 防止数据量过大
    * ['order'] 排序 默认 id desc
    * ['compid'] 调用指定id的评论的子评论
    * 
    **/
    public function section(Request $request){
        if(request()->isAjax()){
        $where[] = ["a.status","=","1"];
        $data = $request->param("comment");
        if(!empty($data['matid'])){
            if(is_numeric($data['matid'])){
                $where[] = ["a.aid","=",$data['matid']];
            }else{
                return jsonmsg(0,'matid参数错误');
            }
        }
        if(!empty($data['uid'])){
            if(is_numeric($data['uid'])){
                $where[] = ["a.uid","=",$data['uid']];
            }else{
                return jsonmsg(0,'uid参数错误');
            }
        }
        if(!empty($data['comid'])){
            if(is_numeric($data['comid'])){
                $where[] = ["a.id","=",$data['comid']];
            }else{
                return jsonmsg(0,'comid参数错误');
            }
        }
        if(!empty($data['compid'])){
            if(is_numeric($data['comid'])){
                $where[] = ["a.pid","=",$data['compid']];
            }else{
                return jsonmsg(0,'compid参数错误');
            }
        }
        if(empty($data['limit'])){
            $data['limit'] = 30;
        }
        if(empty($data['order'])){
            $data['order'] = 'id desc';
        }
        $res = Db::name("comment")
        ->alias("a")
		->fieldRaw("a.*,c.*")
		->join("comment_data c","find_in_set(a.id,c.cid)")
        ->where($where)
        ->limit($data['limit'])
        ->order($data['order'])
        ->select()
        ->toArray();
        if(!empty($res)){
            $res = alldigui($res);
            foreach($res as $k=>$vs){
                $res[$k]['create_time'] = date('Y-m-d H:i:s', $vs['create_time']);
            }
        }
        return json_encode($res,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
        }
        return jsonmsg(0,'非法请求');
    }
}