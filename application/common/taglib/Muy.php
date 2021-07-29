<?php
namespace app\common\taglib;
use think\template\TagLib;
use think\Db;

class Muy extends Taglib
{
	// 标签定义
	protected $tags = [
	    // 标签定义： attr 属性列表 close 是否闭合（0 或者1 默认1） alias 标签别名 level 嵌套层次
	    'siteseo'        => ['attr' => 'name','close'=>0],//网站相关tdk信息调用
	    'article'        => ['attr' => 'name,id,type,limt,top,order,key,tags,price,fykg','level'=>3,'close'=>1],//文章的相关调用
	    'myad'           => ['attr' => 'id,name','close'=>0],//网站广告的调用
	    'links'          => ['attr' => 'name,order,limt','close'=>1],//友情链接 
	    'feed'           => ['attr' => 'name,id,limt,order','close'=>1],//留言
	    'pl'             => ['attr' => 'name,id,aid,order,pid,limt,uid','level'=>3,'close'=>1],//评论调用标签
	    'nav'            => ['attr' => 'name,id,order,limt,mid','level'=>3,'close'=>1],//导航调用
	    'typ'            => ['attr' => 'name,id,mid,limt,order','close'=>1],//分类调用
	    'tags'           => ['attr' => 'name,id,mid,limt,key','close'=>1],//tag调用key调用
	    'urls'           => ['attr' => 'id,tag,key,arid','close'=>0],//文章链接快捷填写
	    'urlsu'          => ['attr' => 'id','close'=>0],//会员中心链接快捷填写
	    'ppt'            => ['attr' => 'name,id,top,ppts,limit,order','close'=>1],//幻灯片调用
	];
	
	/*
	*系统前台常量调用
	*$price 对应的值条件
	*$seot 获取前台填写值
	*$parse db结果
	*/
	public function tagSiteseo($tag)
	{
		$price = 'title';
		$seot = $tag['name'];
		if($seot == 'title')
		{
			$price = 'title';
		}else{
			if($seot == 'desc'){
				$price = 'descri';	
		}else{
			if($seot == 'key'){
				$price = 'keyword';
		}else{
			if($seot == 'ftitle'){
				$price = 'ftitle';
		}else{
		    if($seot == 'ord'){
		        $price = 'record';
		}else{
		    if($seot == 'copy'){
		       $price = 'copyright'; 
		}else{
		    if($seot == 'stat'){
		        $price = 'statistics'; 
		}else{
		    if($seot == 'logo'){
		        $price = 'logo';
		}else{
		    if($seot == 'ico'){
		        $price = 'ico';  
		}else{
		    if($seot == 'adqq'){
		        $price = 'adminqq';
		}else{
		    if($seot == 'ademail'){
		        $price = 'adminemail';
		    }
		}
		}
		}
		}
		}
		}
		}
		}
		}
		}
		$parse = Db::name('system')->where('id',1)->value($price);
		return $parse;
	}
	
	/*
	*文章的相关调用
	*name 必填值 用作循环键值
	*id  调用指定栏目的文章
	*type  调用指定分类的文章
	*limt 调用指定数量的文章
	*top 调用置顶文章
	*key 调用包含key关键字的文章
	*tags 调用包含tag标签的文章
	*price 根据文章的付费状态调用
	*fykg 是否开启分页
	*/
		public function tagArticle($tag,$content)
	{
	    
	    $name  = $tag['name'];//必填项 不做判断 循环变量
	    $fynu  = !empty($tag['fykg']) ? $tag['fykg'] : '';
	    $id    = isset($tag['id']) ? $this->autoBuildVar($tag['id']):'';//调用指定栏目文章
	    $type  = isset($tag['type']) ? $this->autoBuildVar($tag['type']):'';//调用指定分类文章
	    $top   = !empty($tag['top']) ? $tag['top']:'';//调用置顶文章
	    $order = !empty($tag['order']) ? $tag['order'] : 'a.create_time desc';//排序方式
	    $limt  = empty($tag['limt']) ? '10' : $tag['limt'];//调用数量  默认 10条数据
	    $key = isset($tag['key']) ? $this->autoBuildVar($tag['key']) :'';
	    $tags = isset($tag['tags']) ? $this->autoBuildVar($tag['tags']) :'';
	    $price = isset($tag['price']) ? $this->autoBuildVar($tag['price']) :'';
	    $wheress = "select()";
	    $whereid = "";
	    $limits = "->limit('".$limt."')";
	    if($fynu){
	        $wheress='paginate("'.$limt.'")';
	        $limits = "";
	    }
	    $fyname = '$__LIST';
	    $where = "find_in_set('0',a.status)";
	    if($top){
	        $where .= ' and find_in_set('. $top .',a.top)';
	        $fyname = '$__TLIST';
	    }
	    $parse = '<?php ';
	    if($id){
	        $parse .= '$_A = '.$id.';';
	        $parse .= '$_A= wnzla($_A);';
	        $whereid = '->where("a.mid","in","$_A")';
	        $fyname = '$__DLIST';
	    }
	    if($type){
	        $parse .= '$_T = '.$type.';';
	        $where .= ' and find_in_set($_T,a.type)';
	        $fyname = '$__PLIST';
	    }
	    if($key){
	        $parse .= '$_K = '.$key.';';
	        $where .= ' and find_in_set($_K,a.keyword)';
	        $fyname = '$__KLIST';
	    }
	    if($tags){
	        $parse .= '$_G = '.$tags.';';
	        $where .= ' and a.tag = $_G';
	        $fyname = '$__TTLIST';
	    }
	    if($price){
	        $parse .= '$_P = '.$price.';';
	        $where .= ' and a.price = $_P';
	        $fyname = '$__PTTLIST';
	    }
	    $parse .= ''.$fyname.' = Db::name("article")
	    ->alias("a")
	    ->fieldRaw("a.*,d.*,b.title as tm,c.title as hm,e.photo,group_concat(distinct b.title),group_concat(distinct c.title)")
	    ->leftjoin("type b","find_in_set(b.id,a.type)")
	    ->leftjoin("hmenu c","find_in_set(c.id,a.mid)")
	    ->join("article_data d","find_in_set(d.aid,a.id)")
	    ->leftjoin("member e","find_in_set(e.id,a.uid)")
	    ->where("'.$where.'")
	    '.$whereid.'
	    ->where("a.delete_time",NULL)
	    ->orderRaw("'.$order.'")
	    ->group("a.id")
	    '.$limits.'
	    ->'.$wheress.';';
        $parse .= ' ?>';
        $parse .= '{volist name="'.$fyname.'" id="' . $name . '"}';
        $parse .= $content;
        $parse .= '{/volist}';
        return $parse;
        //新建分页调用标签 接收数据对应数据库总数据 达到总页数计算 $limit 文章标签赋值总页数 **count到页面
	}
	
	/*
	*评论调用标签
	*name 非必填 默认 $p 
	*id 调用指定id单条评论
	*limt 调用指定数量评论 默认10
	*aid 调用指定文章的评论
	*order 排序  
	*uid 调用指定会员的评论
	*pid 调用上级评论的回复评论
	*/
	public function tagPl($tag,$content){
	    $name = empty($tag['name']) ? 'p' : $tag['name'];
	    $id = isset($tag['id']) ? $tag['id'] : '';
	    $arid = empty($this->autoBuildVar($tag['aid'])) ? '':$this->autoBuildVar($tag['aid']);
	    $uid = empty($this->autoBuildVar($tag['uid'])) ? '':$this->autoBuildVar($tag['uid']);
	    $pid = empty($this->autoBuildVar($tag['pid'])) ? '':$this->autoBuildVar($tag['pid']);
	    $limt = empty($tag['limt']) ? '10' : $tag['limt'];
	    $order = empty($tag['order']) ? 'a.id desc' : $tag['order'];
	    $where = "find_in_set('1',a.status)";
	    $wherepid = "find_in_set('0',pid)";
	    $wheres = 'paginate("'.$limt.'")';
	    $vname = '$_PLIST';
	    $limit = '';
	    if($id){
	        $where .= 'and find_in_set("'.$id.'",a.id)';
	        $vname = '$_ZPLIST';
	        $wheres = 'select()';
	        $limit = '->limit('.$limt.')';
	    }
	    $parse = '<?php ';
	    if($arid){
	    $parse .= '$_M = '.$arid.';';
	    $where .= 'and find_in_set($_M,a.aid)';
	    }
	    if($pid){
	    $parse .= '$_P = '.$pid.';';
	    $wherepid = 'find_in_set($_P,a.pid)';
	    $vname = '$_ZPLIST';
	    $wheres = 'select()';
	    $limit = '->limit('.$limt.')';
	    }
	    if($uid){
	    $parse .= '$_U = '.$uid.';';
	    $where .= 'and find_in_set($_U,a.uid)';
	    $vname = '$_ZPLIST';
	    $wheres = 'select()';
	    $limit = '->limit('.$limt.')';
	    }
	    $parse .= ''.$vname.' = Db::name("comment")
	    ->alias("a")
	    ->fieldRaw("a.*,c.*")
	    ->join("comment_data c","find_in_set(a.id,c.cid)")
	    ->where("'.$wherepid.'")
	    ->where("'.$where.'")
	    '.$limit.'
	    ->orderRaw("'.$order.'")
	    ->'.$wheres.';';
	    $parse .= ' ?>';
	    $parse .= '{volist name="'.$vname.'" id="'.$name.'"}';
	    $parse .= $content;
	    $parse .= '{/volist}';
	    return $parse;
	}
	
	
	/*
	*文章tag和key的调用
	*name 默认 tg
	*id 调用指定id文章的tag或key
	*mid 调用指定栏目下的tag或key
	*limt 限制调用数量
	*key 是否调用关键字 true 调用关键字 false调用tag
	*/
	public function tagTags($tag,$content){
	    $name = isset($tag['name']) ? $tag['name'] : 'tg';
	    $id = empty($tag['id']) ? '' :$this->autoBuildVar($tag['id']);
	    $mid = empty($tag['mid']) ? '' :$this->autoBuildVar($tag['mid']);
	    $key = isset($tag['key']) ? $tag['key'] : 'false';
	    $limt = empty($tag['limt']) ? '10' : $tag['limt'];
	    $where = 'find_in_set(0,status)';
	    if($key == 'true'){
	        $parse = '<?php ';
	        if($id){
	        $parse .= '$_D = '.$id.';';
	        $where .= 'and find_in_set($_D,id)';
	        }
	        if($mid){
	            $parse .='$_MD = '.$mid.';';
	            $where .= 'and find_in_set($_MD,mid)';
	        }
	        $parse .= '$_LIST = Db::name("article")
	        ->where("'.$where.'")
	        ->fieldRaw("keyword")
	        ->limit("'.$limt.'")
	        ->select()
	        ->toArray();';
	        $parse .= '$_L = array_column($_LIST,"keyword");';
	        $parse .= '$_LS = array_unique(explode(",",implode(",",$_L)));';
	        $parse .= ' ?>';
	        $parse .= '{volist name="$_LS" id="'.$name.'"}';
	        $parse .= $content;
	        $parse .= '{/volist}';
	        return $parse;
	    }
	    $parse = '<?php ';
	    if($id){
	        $parse .= '$_D = '.$id.';';
	        $where .= 'and find_in_set($_D,id)';
	        }
	    if($mid){
	        $parse .='$_MD = '.$mid.';';
	        $where .= 'and find_in_set($_MD,mid)';
	    }
	    $parse .= '$_LIST = Db::name("article")
	        ->where("'.$where.'")
	        ->fieldRaw("tag")
	        ->limit("'.$limt.'")
	        ->select()
	        ->toArray();';
	    $parse .= '$_L = array_column($_LIST,"tag");';
	    $parse .= '$_LS = array_unique(explode(",",implode(",",$_L)));';
	    $parse .= ' ?>';
	    $parse .= '{volist name="$_LS" id="'.$name.'"}';
	    $parse .= $content;
	    $parse .= '{/volist}';
	    return $parse;
	}
	
	
	/*
	*栏目导航调用
	*name 默认 $navs
	*id 调用指定id栏目信息
	*order 栏目排序
	*limt 栏目数量
	*mid  调用相关子栏目
	*/
	public function tagNav($tag,$content){
	    $name = empty($tag['name']) ? 'navs' : $tag['name'];
	    $id = isset($tag['id']) ? $tag['id'] : '';
	    $order = isset($tag['order']) ? $tag['order'] : 'id asc';
	    $limt = !empty($tag['limt']) ? $tag['limt'] : '10';
	    $mid = $this->autoBuildVar($tag['mid']);
	    $where = "find_in_set('1',status)";
	    if($id){
	        $where .= 'and find_in_set('.$id.',id)';
	    }
	    $where .= 'and find_in_set(0,pid)';
	    if($mid != ""){
	        $parse = '<?php ';
	        $parse .= '$_M = '.$mid.';';
	        $parse .= '$_M = lmpid($_M);';
	        $parse .= '$_LIST = Db::name("hmenu")
	        ->where("find_in_set($_M,pid)")
	        ->where("find_in_set(1,status)")
	        ->orderRaw("'.$order.'")
	        ->limit("'.$limt.'")
	        ->select();';
	        $parse .= ' ?>';
	        $parse .= '{volist name="$_LIST" id="'.$name.'"}';
	        $parse .= $content;
	        $parse .= '{/volist}';
	        return $parse;
	    }else{
	        
	    }
	    $parse = '<?php ';
	    $parse .= '$_LIST = Db::name("hmenu")
	    ->where("'.$where.'")
	    ->orderRaw("'.$order.'")
	    ->limit("'.$limt.'")
	    ->select()->toArray();';
	    $parse .= ' ?>';
	    $parse .= '{volist name="$_LIST" id="'.$name.'"}';
	    $parse .= $content;
	    $parse .= '{/volist}';
	    return $parse;
	}
	
	
	/*
	*分类调用标签 示例{muy:typ name="type" }{$type.type_name}{/muy:typ}
	*name 默认 type
	*id 调用指定id分类信息
	*mid 调用指定栏目下分类
	*limt 分类调用数量
	*order 分类排序方式
	*/
	public function tagTyp($tag,$content){
	    $name = empty($tag['name']) ? 'type' : $tag['name'];
	    $id = isset($tag['id']) ? $tag['id'] : '';
	    $mid = isset($tag['mid']) ? $this->autoBuildVar($tag['mid']) : '';
	    $order = isset($tag['order']) ? $tag['order'] : 'id asc';
	    $limt = !empty($tag['limt']) ? $tag['limt'] : '10';
	    $where = 'find_in_set(1,status)';
	    if($id){
	        $where .='and find_in_set('.$id.',id)';
	    }
	    $parse = '<?php ';
	    if($mid){
	        $parse .= '$_M = '.$mid.';';
	        $where .= 'and find_in_set($_M,mid)';
	    }
	    $parse .= '$__LIST__ = Db::name("type")
	    ->where("'.$where.'")
	    ->limit("'.$limt.'")
	    ->orderRaw("'.$order.'")
	    ->select();';
	    $parse .= ' ?>';
	    $parse .= '{volist name="$__LIST__" id="'.$name.'"}';
	    $parse .= $content;
	    $parse .= '{/volist}';
	    return $parse;
	}
	
	
	/*
	*广告调用标签 myad
	*id 调用指定id广告  必填
	*/
	public function tagMyad($tag)
	{
	    $id = $tag['id']; // 必填 需要调用的广告ID
	    $adname = isset($tag['name']) ? $tag['name'] : 'title';//调用广告$id的内容
	    $parse = Db::name("advertising")->where("id",$id)->value($adname);
	    return $parse;
	}
	
	/*
	*友情链接调用
	*name 循环变量，随意  默认 link
	*/
	public function tagLinks($tag,$content)
	{
	    $name = isset($tag['name']) ? $tag['name'] : 'link';
	    $order = empty($tag['order']) ? 'orders asc' : $tag['order'];
	    $limt = empty($tag['limt']) ? '20' : $tag['limt'];
	    $parse = '<?php ';
	    $parse .= '$__LIST__ = Db::name("links")
	    ->where("status",1)
	    ->limit("'. $limt .'")
	    ->orderRaw("'. $order .'")
	    ->select();';
	    $parse .= ' ?>';
	    $parse .= '{volist name="__LIST__" id="'. $name .'"}';
	    $parse .= $content;
	    $parse .= '{/volist}';
	    return $parse;
	}
	

	
	/*
	*留言调用
	*name 变量值 随意 默认myfe
	*limt 调用数量 默认15条
	*order 排序方式
	*id 调用指定id单条评论
	*/
	public function tagFeed($tag,$content)
	{
	   $name = isset($tag['name']) ? $tag['name'] : 'fed';
	   $limt = empty($tag['limt']) ? '15' : $tag['limt'];
	   $order = isset($tag['order']) ? $tag['order'] : 'a.id desc';
	   $id = isset($tag['id']) ? $tag['id'] : '';
	   if($id){
	       $where = 'find_in_set('.$id.',a.id)';
	   }
	   $parse = '<?php ';
	   $parse .= '$_ly = Db::name("feedback")
	   ->alias("a")
	   ->leftjoin("member b","find_in_set(b.id,a.uid)")
	   ->where("'.$where.'")
	   ->limit("'.$limt.'")
	   ->orderRaw("'.$order.'")
	   ->group("a.id")
	   ->select();';
	   $parse .= ' ?>';
	   $parse .= '{volist name="_ly" id="'.$name.'"}';
	   $parse .= $content;
	   $parse .= '{/volist}';
	   return $parse; 
	}
	
	/*
	*通用文章列表地址快捷书写
	*id  栏目id
	*tag 文章tag
	*key 文章关键字
	*/
	public function tagUrls($tag){
	    $id = isset($tag['id']) ? $this->autoBuildVar($tag['id']) : '';
	    $tags = isset($tag['tag']) ? $this->autoBuildVar($tag['tag']) : '';
	    $key = isset($tag['key']) ? $this->autoBuildVar($tag['key']) : '';
	    $arid = isset($tag['arid']) ? $this->autoBuildVar($tag['arid']) : '';
	    if($id){
	    $parse = '<?php ';
	    $parse .= '$_D = '.$id.';';
	    $parse .= '$_U = "";';
	    $parse .= '$_D == 1 ? $_U="index/Index/index" : $_U = "index/Articles/article_list";';
	    $parse .= ' ?>';
	    $parse .= '{:url("$_U",["id"=>$_D])}';
	    return $parse;
	    }
	    if($tags){
	    $parse = '<?php ';
	    $parse .= '$_T = '.$tags.';';
	    $parse .= ' ?>';
	    $parse .= '{:url("index/articles/article_list",["tag"=>$_T])}';
	    return $parse;
	    }
	    if($key){
	     $parse = '<?php ';
	    $parse .= '$_K = '.$key.';';
	    $parse .= ' ?>';
	    $parse .= '{:url("/index/articles/article_list",["key"=>$_K])}';
	    return $parse;   
	    }
	    if($arid){
	    $parse = '<?php ';
	    $parse .= '$_F = '.$arid.';';
	    $parse .= ' ?>';
	    $parse .= '{:url("index/articles/article_content",["id"=>$_F])}';
	    return $parse;
	    }
	}
	
	/*
	*会员中心url快捷书写
	*urlsu
	*id：会员id
	*书写格式：{muy:urlsu id='*'}
	*/
	public function tagUrlsu($tag){
	    $id=isset($tag['id']) ? $this->autoBuildVar($tag['id']) : '';
	    if($id){
	    $parse = '<?php ';
	    $parse .= '$_D = '.$id.';';
	    $parse .= ' ?>';
	    $parse .= '{:url("index/user/my_home",["id"=>$_D])}';
	    return $parse;
	    }
	}
	/*
	 *幻灯片的调用
	 *name  当前幻灯循环别名；
	 *limit 调用数量
	 *id 调用指定栏目文章图作为幻灯
	 *top 调用指定文章图作为幻灯 默认目前只有1级指定
	 *ppts 调用幻灯条件的文章图作为幻灯片
	*/
	public function tagPpt($tag,$content){
	    $name = empty($tag['name']) ? 'muppt': $tag['name'];
	    $limit = empty($tag['limit']) ? '5' : $tag['limit'];
	    $top = isset($tag['top']) ? $this->autoBuildVar($tag['top']) : '';
	    $id = isset($tag['id']) ? $this->autoBuildVar($tag['id']) : '';
	    $ppts = isset($tag['ppts']) ? $this->autoBuildVar($tag['ppts']) : '';
	    $order = empty($tag['order']) ? 'a.create_time': $tag['order'];
	    $where = 'find_in_set(0,a.status)';
	    $parse = '<?php ';
	    if($id){
	        $parse .= '$_ID = '.$id.';';
	        $where .= 'and find_in_set($_ID, a.mid)';
	    }
	    if($ppts){
	        $parse .= '$_PT = '.$ppts.';';
	        $where .= 'and find_in_set($_PT,a.ppts)';
	    }
	    if($top){
	        $parse .= '$_TP = '.$top.';';
	        $where .= 'and find_in_set($_TP,a.top)';
	    }
	    $parse .= '$_LIST= Db::name("article")
	    ->alias("a")
	    ->fieldRaw("a.*,b.title as name,group_concat(distinct b.title)")
	    ->leftjoin("hmenu b","find_in_set(b.id,a.mid)")
	    ->where("'.$where.'")
	    ->where("a.delete_time",NULL)
	    ->order("'.$order.'")
	    ->group("a.id")
	    ->limit("'.$limit.'")
	    ->select();';
	    $parse .= ' ?>';
	    $parse .= '{volist name="$_LIST" id="'.$name.'"}';
	    $parse .= $content;
	    $parse .= '{/volist}';
	    return $parse;
	}
	
}