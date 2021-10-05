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
	    'matter'         => ['attr' => 'key,tab,limit,operate,order,sql,father','level'=>3,'close'=>1],//文章的相关调用
	    'hnav'           => ['attr' => 'all,hid,order,limit,class','level'=>3,'close'=>1],//导航调用	
	    'myad'           => ['attr' => 'id,name','close'=>0],//网站广告的调用
	    'links'          => ['attr' => 'order,limit','close'=>1],//友情链接 
	    'feed'           => ['attr' => 'uid,feid,limit,order','close'=>1],//留言
	    'section'        => ['attr' => 'comid,matid,order,compid,limit,uid','level'=>3,'close'=>1],//评论调用标签
	    'typ'            => ['attr' => 'id,mid,limit,order','close'=>1],//分类调用
	    'tags'           => ['attr' => 'matid,catid,operate,limit,order','close'=>1],//tag调用key调用
	    'maturl'         => ['attr' => 'cateid,contid','close'=>0],//文章链接快捷填写
	    'urlsu'          => ['attr' => 'id','close'=>0],//会员中心链接快捷填写
	    'ppt'            => ['attr' => 'tab,operate,slide,top,limit,order','close'=>1],//幻灯片调用
	    'cuf'            => ['attr' => 'temp,id','close'=>0],//单页调用标签
	];
	
	/*
	*系统TDK信息调用
	*$seot 获取前台填写值
	*$parse db结果
	*/
	public function tagSiteseo($tag)
	{
		$seot = isset($tag['name']) ? $tag['name'] : 'title';
		$pass = ['title','descri','keyword','ftitle','record','copyright','statistics','logo','ico','adminqq','adminemail'];
		if(!in_array($seot,$pass)){//超出调用范围 结束
		    return;
		}
		$parse = Db::name('system')->where('id',1)->value($seot);
		return $parse;
	}
	
	/*
	*文章的相关调用
	*tab 栏目id 表名
	*limit 调用指定数量
	*father true查子孙数据 false只查自身 默认查子孙
	*operate 操作类型 (0栏目最新 1栏目推荐)[tab=栏目id] (2表最新 3表推荐)[tab=表名,无需表前缀]
	4 查询所有表信息  注意:limit="页码,显示数量" 或 limit="数量" ; 默认关闭分页
	*order 排序
	*pag 分页
	*key 特殊项 可为空
	*/
		public function tagMatter($tag,$content)
	{
	    $tab = !empty($tag['tab']) ? $tag['tab'] : '1';
	    $limit = isset($tag['limit']) ? $tag['limit'] : '10';
	    $muyuop = isset($tag['operate']) ? $tag['operate'] : '0';
	    $order = empty($tag['order']) ? 'create_time desc' : $tag['order'];
	    $pag = isset($tag['pag']) ? $tag['pag'] : 'false';
	    $father = isset($tag['father']) ? $tag['father'] : 'true';
	    $keyis = isset($tag['key']) ? $tag['key'] : '';
	    $sql = empty($tag['sql']) ? '' :  '->'.$tag['sql'];//附加条件 例如:whereTime('create_time', 'week')本周的内容
	    $whereCe = "select()";
	    $limits = "->limit('".$limit."')";
	    if($pag == "true"){//判断是否开启分页
	        $whereCe = 'paginate("'.$limit.'")';
	        $limits = ""; 
	    }
	    if(!in_array($muyuop,array("0","1","2","3","4"))){//不在类型内 结束
	        return;
	    }
	    $where = '->where(["a.delete_time"=>NULL])';//给定默认查询条件
	    if($muyuop == '0' || $muyuop == '2'){//根据操作类型改写排序
	        $order = 'create_time desc';
	    }elseif($muyuop == '1' || $muyuop == '3'){
	        $where = '->where(["a.delete_time"=>NULL,"a.top"=>1])';//根据操作类型改写查询条件
	    }
	    $echo = '$_LI';//根据操作类型改变变量名方便不同分页互不干扰
	    if($muyuop == 1){
	        $echo = '$_LIT';
	    }elseif($muyuop == 2){
	        $echo = '$_LIS';
	    }elseif($muyuop == 3){
	        $echo = '$_LIST';
	    }
	    if($muyuop == '2' || $muyuop == '3' || $muyuop == '4'){
	        $father ="true";
	    }
	    if(!empty($keyis)){
	        $echo = '$_'.$keyis;
	    }
	    $lmwer = "";//定义一个空变量
	        $parse = '<?php ';
	        if($muyuop == '0' || $muyuop == '1'){//根据操作类型处理数据
			if("$" == substr($tab, 0, 1)){//如果是变量
				$tab = $this->autoBuildVar($tab);//解析
				$parse .= '$_TAB = '.$tab.';';
				$parse .= 'if(is_numeric($_TAB)){ $_TABS = muyname($_TAB,'.$father.'); $_TAB = $_TABS["tablename"]; $_MID = $_TABS["mid"]; $_FTB = $_TABS["ftabname"]; };';//判断变量是数字 获取需要的值
				$lmwer = '->where("a.mid","in","$_MID")';//增加查询条件 查询顶级栏目以及子栏目数据
			}else{
				$parse .= '$_TAB = "'.$tab.'";';
				$parse .= 'if(is_numeric($_TAB)){ $_TABS = muyname($_TAB,'.$father.'); $_TAB = $_TABS["tablename"]; $_MID = $_TABS["mid"]; $_FTB = $_TABS["ftabname"]; };';
				$lmwer = '->where("a.mid","in",$_MID)';
			} 
	        }elseif($muyuop == '2' || $muyuop == '3'){
	        $parse .= '$_TAB = "'.$tab.'";';
	        $parse .= '$_FTB = "'.$tab.'_data";';
	        }
	        if($muyuop == 4){
	        $parse .= ''.$echo.' =  setallmat("'.$limit.'","'.$order.'");';
	        }else{
	        $parse .= 'if(!empty($_FTB) && !empty($_TAB)) : '.$echo.' = Db::name($_TAB)
	        ->alias("a")
	        ->fieldRaw("a.*,b.likes,b.browse,comment_t,c.title as catitle,d.name,e.title as tytitle")
	        ->join("$_FTB b","find_in_set(b.aid,a.id)")
	        ->leftjoin("category c","find_in_set(c.id,a.mid)")
	        ->leftjoin("member d","find_in_set(d.id,a.uid)")
	        ->leftjoin("type e","find_in_set(e.id,a.type)")
	        '.$where.'
	        '.$lmwer.'
	        '.$sql.'
	        ->where(["a.status"=>0])
	        ->orderRaw("'.$order.'")
	        '.$limits.'
	        ->'.$whereCe.';';//拼装Db查询
	        $parse .= 'else: ';
	        $parse .= ''.$echo.' = array();';
			$parse .= 'endif;';
	        }
	        $parse .= '$_NUM = 0;';
	        $parse .= 'foreach('.$echo.' as $mat):';
	        $parse .= '$_PRICE = ["1"=>"免费","2"=>"付费"];';
	        $parse .= '$mat["price"] = $_PRICE[$mat["price"]];';
	        $parse .= '$mat["matnu"] = $_NUM++;';
	        $parse .= 'extract($mat); ?>';//处理$mat
	        $parse .= $content;
			$parse .= '<?php endforeach; ?>';
	        return $parse;
	}
	
		/*
	*栏目导航调用
	*hid 调用指定id栏目信息
	*order 栏目排序
	*limit 栏目数量
	*/
	public function tagHnav($tag,$content){
	    $all = isset($tag['all']) ? $tag['all'] : 'false';
	    $id = isset($tag['hid']) ? $tag['hid'] : '';
	    $order = isset($tag['order']) ? $tag['order'] : 'id asc';
	    $limit = !empty($tag['limit']) ? $tag['limit'] : '10';
	    $calss = isset($tag['class']) ? $tag['class'] : '' ;
	    $parse = '<?php ';
	    if($all == 'false'){
	    if($id){
	    if("$" == substr($id, 0, 1)){
	    $id = $this->autoBuildVar($id);
	    $parse .= '$_CID = '.$id.';';
	    $parse .= '$_NAVS = hnavs("'.$limit.'","'.$order.'","$_CID");';
	    }else{
	    $parse .= '$_NAVS = hnavs("'.$limit.'","'.$order.'","'.$id.'");';   
	    }
	    }
	    }else{
	    $parse .= '$_NAVS = hnavs("'.$limit.'","'.$order.'");';     
	    } 
	    $parse .= 'foreach($_NAVS as  $key=>$va):';
	    $parse .= '$navclass = thisclass($va["id"],"'.$calss.'");';
	    $parse .= 'extract($va);?>';//处理$va
	    $parse .= $content;
	    $parse .= '<?php endforeach; ?>';
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
		    if(empty($id)){
		        return;
		    }
		    $adinfo = Db::name("advertising")->find($id);
		    if(empty($adinfo)){
		        return;
		    }
		    if($adinfo["status"] == 0){
		        return;
		    }
		    if($adinfo['outtime'] != null || !empty($adinfo['outtime'])){
		    if($adinfo["adset"] == 1){
		        if($adinfo['outtime'] <= time()){
		        Db::name("advertising")->where("id",$id)->update(["status"=>0]);
				return;
		        }
		    }
		    }
		    $parse = Db::name("advertising")->where(["id"=>$id])->value($adname);
		    if($adinfo['outtime'] != null || !empty($adinfo['outtime'])){
		    if($adinfo['outtime'] <= time()){
		        $parse = Db::name("advertising")->where("id",$id)->value("outext");
		    }
		    }
		    return $parse;
		}
	
	
		/*
	*友情链接调用
	*/
	public function tagLinks($tag,$content)
	{
	    $order = empty($tag['order']) ? 'orders asc' : $tag['order'];
	    $limit = empty($tag['limit']) ? '20' : $tag['limit'];
	    $parse = '<?php ';
	    $parse .= '$__LIST__ = Db::name("links")
	    ->where("status",1)
	    ->limit("'. $limit .'")
	    ->orderRaw("'. $order .'")
	    ->select();';
	    $parse .= 'foreach($__LIST__ as $ls):';
	    $parse .= 'extract($ls);?>';//处理$ls
	    $parse .= $content;
	    $parse .= '<?php endforeach; ?>';
	    return $parse;
	}
	
		/*
	*留言调用
	*uid 会员的所有评论
	*limit 调用数量 默认15条
	*order 排序方式
	*id 调用指定id单条评论
	*/
	public function tagFeed($tag,$content)
	{
	   $uid = isset($tag['uid']) ? $tag['uid'] : '';
	   $limit = empty($tag['limit']) ? '15' : $tag['limit'];
	   $order = isset($tag['order']) ? $tag['order'] : 'a.id desc';
	   $id = isset($tag['feid']) ? $tag['feid'] : '';
	   $where = "";
	   $parse = '<?php ';
	   if($uid){
	       if("$" == substr($uid, 0, 1)){
	           $uid = $this->autoBuildVar($uid);
	           $parse .= '$_UID = '.$uid.';';
	           $where = '->where(["a.uid"=>$_UID])';
	       }else{
	           $where = '->where(["a.uid"=>'.$uid.'])';
	       }
	   }
	   if($id){
	       if("$" == substr($uid, 0, 1)){
	           $id = $this->autoBuildVar($id);
	           $parse .= '$_ID = '.$id.';';
	           $where = '->where(["a.id"=>$_ID])';
	       }else{
	           $where = '->where(["a.id"=>'.$id.'])';
	       }
	       
	   }
	   if(!empty($id) && !empty($uid)){
	       if("$" == substr($uid, 0, 1) || "$" == substr($id, 0, 1)){
	           $uid = $this->autoBuildVar($uid);
	           $parse .= '$_UID = '.$uid.';';
	           $id = $this->autoBuildVar($id);
	           $parse .= '$_ID = '.$id.';';
	           $where = '->where(["a.uid"=>$_UID,"a.id"=>$_ID])';
	       }else{
	           $where = '->where(["a.uid"=>'.$uid.',"a.id"=>'.$id.'])';
	       }
	   }
	   $parse .= '$_LY = Db::name("feedback")
	   ->alias("a")
	   ->leftjoin("member b","find_in_set(b.id,a.uid)")
	   '.$where.'
	   ->limit("'.$limit.'")
	   ->fieldRaw("a.*,b.name,b.photo,b.email")
	   ->orderRaw("'.$order.'")
	   ->select();';
	   $parse .= 'foreach($_LY as $feed):';
	    $parse .= 'extract($feed);?>';//处理$feed
	    $parse .= $content;
	    $parse .= '<?php endforeach; ?>';
	    return $parse; 
	}
	
	/*
		*评论调用标签
		*comid 调用指定id单条评论
		*limit 调用指定数量评论 默认10
		*matid 调用指定文章的评论
		*order 排序  
		*uid 调用指定会员的评论
		*compid 调用上级评论的回复评论
		*/
	public function tagSection($tag,$content){
		    $id = isset($tag['comid']) ? $tag['comid'] : '';
		    $arid = empty($tag['matid']) ? '':$tag['matid'];
		    $uid = empty($tag['uid']) ? '':$tag['uid'];
		    $pid = empty($tag['compid']) ? '':$tag['compid'];
		    $limit = empty($tag['limit']) ? '10' : $tag['limit'];
		    $order = empty($tag['order']) ? 'id desc' : $tag['order'];
		    $where = '->where(["a.status"=>1,"a.pid"=>0])';
		    $catup = 'paginate("'.$limit.'")->toArray()';
		    $vname = '$_PIST';
		    $limit = '';
		    $parse = '<?php ';
		    if($id){
		        if("$" == substr($id, 0, 1)){
		            $id = $this->autoBuildVar($id);
		            $parse .= '$_COMID = '.$id.';';
		            $where = '->where(["a.status"=>1,"a.id"=>$_COMID])';
		            $vname = '$_ZPIST';
		            $catup = 'select()->toArray()';
		            $limit = '->limit('.$limit.')';
		        }else{
		            $where = '->where(["a.status"=>1,"a.id"=>'.$id.'])';
		            $vname = '$_ZPIST';
		            $catup = 'select()->toArray()';
		            $limit = '->limit('.$limit.')';
		        }
		    }
		    if($arid){
		        if("$" == substr($arid, 0, 1)){
		            $arid = $this->autoBuildVar($arid);
		            $parse .= '$_MATID = '.$arid.';';
		            $where = '->where(["a.status"=>1,"a.aid"=>$_MATID])';
		        }else{
		            $where = '->where(["a.status"=>1,"a.pid"=>0,"a.aid"=>'.$arid.'])';
		        }
		    }
		    if($pid){
		        if("$" == substr($pid, 0, 1)){
		            $pid = $this->autoBuildVar($pid);
		            $parse .= '$_COMPID = '.$pid.';';
		            $vname = '$_ZPIST';
		            $where = '->where(["a.status"=>1,"a.pid"=>$_COMPID])';
		            $catup = 'select()->toArray()';
		            $limit = '->limit('.$limit.')';
		        }else{
		            $where = '->where(["a.status"=>1,"a.pid"=>'.$pid.'])';
		            $vname = '$_ZPIST';
		            $catup = 'select()->toArray()';
		            $limit = '->limit('.$limit.')';
		        }
		    }
		    if($uid){
		        if("$" == substr($uid, 0, 1)){
		            $uid = $this->autoBuildVar($uid);
		            $parse .= '$_UID = '.$uid.';';
		            $where = '->where(["a.status"=>1,"a.pid"=>0,"a.uid"=>$_UID])';
		        }else{
		            $where = '->where(["a.status"=>1,"a.pid"=>0,"a.uid"=>'.$uid.'])';
		        }
		    }
		    $parse .= ''.$vname.' = Db::name("comment")
		    ->alias("a")
		    ->fieldRaw("a.*,c.*")
		    ->join("comment_data c","find_in_set(a.id,c.cid)")
		    '.$where.'
		    '.$limit.'
		    ->orderRaw("'.$order.'")
		    ->'.$catup.';';
		    $parse .= 'if(isset('.$vname.'["data"])) : '.$vname.' = alldigui('.$vname.'["data"]);';
		    $parse .= 'else: ';
		    $parse .= ''.$vname.' = alldigui('.$vname.');';
		    $parse .= 'endif;';
		    $parse .= '$_NUM = 0;';
		    $parse .= 'foreach('.$vname.' as $com):';
		    $parse .= '$com["comnu"] = $_NUM++;';
		    $parse .= '$MATINFO = matsection($com["aid"]);';
		    $parse .= '$com["matitle"] = $MATINFO["title"];';
		    $parse .= 'extract($com);?>';//处理$com
		    $parse .= $content;
		    $parse .= '<?php endforeach; ?>';
		    return $parse;
		}
	
		/*
	*分类调用标签 示例{muy:typ name="type" }{$type.type_name}{/muy:typ}
	*name 默认 type
	*id 调用指定id分类信息
	*mid 调用指定栏目下分类
	*limit 分类调用数量
	*order 分类排序方式
	*/
	public function tagTyp($tag,$content){
	    $id = isset($tag['id']) ? $tag['id'] : '';
	    $mid = isset($tag['caid']) ? $this->autoBuildVar($tag['caid']) : '';
	    $order = isset($tag['order']) ? $tag['order'] : 'id asc';
	    $limit = !empty($tag['limit']) ? $tag['limit'] : '10';
	    $where = '->where("status",1)';
	    $parse = '<?php ';
	    if($id){
	        if("$" == substr($id, 0, 1)){
	            $id = $this->autoBuildVar($id);
	            $parse .= '$_TYID = '.$id.';';
	            $where = '->where(["status"=>1,"id"=>$_TYID])';
	        }else{
	            $where = '->where(["status"=>1,"id"=>'.$id.'])';
	        }
	    }           //处理单id和栏目id以变量方式传入
	    if($mid){
	       if("$" == substr($mid, 0, 1)){
	            $mid = $this->autoBuildVar($mid);
	            $parse .= '$_CAID = '.$mid.';';
	            $where .= '->where("mid",$_CAID)';
	       }else{
	            $where .= '->where("mid",'.$mid.')';
	       } 
	    }
	    $parse .= '$__LIST__ = Db::name("type")
	    '.$where.'
	    ->limit("'.$limit.'")
	    ->orderRaw("'.$order.'")
	    ->select();';
	    $parse .= '$_NUM = 0;';
	    $parse .= 'foreach($__LIST__ as $ts):';
	    $parse .= '$ts["tynu"] = $_NUM++;';
	    $parse .= 'extract($ts);?>';//处理$ts
	    $parse .= $content;
	    $parse .= '<?php endforeach; ?>';
	    return $parse;
	}
	
	
	/*
	*tag和key的调用
	*matid 调用指定id文章的tag或key
	*catid 调用指定栏目下的tag或key
	*limit 限制调用数量
	*operate 0调用关键词 1调用tags
	*order 排序
	*/
	public function tagTags($tag,$content){
	    $id = empty($tag['matid']) ? '' :$tag['matid'];
	    $mid = empty($tag['catid']) ? '' :$tag['catid'];
	    $operate = isset($tag['operate']) ? $tag['operate'] : '0' ;
	    $limit = empty($tag['limit']) ? '10' : $tag['limit'];
	    $order = empty($tag['order']) ? 'create_time asc' : $tag['order'];
	    $field = "keyword";
		if(!empty($id) && !empty($mid)){
		    return;
		}
	    if($operate == 1){
	        $field = "tag";
	    }
	    $parse = '<?php ';
	    $parse .= '$_LIST = tagkey('.$limit.',"'.$field.'","'.$order.'");';
	    if(!empty($id)){
	        if("$" == substr($id, 0, 1)){
	            $id = $this->autoBuildVar($id);
	            $parse .= '$_MATID = '.$id.';';
	            $parse .= '$_LIST = tagkey('.$limit.',"'.$field.'","'.$order.'","",$_MATID);';
	        }else{
	            $parse .= '$_LIST = tagkey('.$limit.',"'.$field.'","'.$order.'","",'.$id.');';
	        }
	    }
	    if(!empty($mid)){
	        if("$" == substr($mid, 0, 1)){
	            $mid = $this->autoBuildVar($mid);
	            $parse .= '$_CATID = '.$mid.';';
	            $parse .= '$_LIST = tagkey('.$limit.',"'.$field.'","'.$order.'",$_CATID);';
	        }else{
	            $parse .= '$_LIST = tagkey('.$limit.',"'.$field.'","'.$order.'",'.$mid.');';
	        }
	    }
	    $parse .= 'foreach($_LIST as $'.$field.'):?>';
	    $parse .= $content;
	    $parse .= '<?php endforeach; ?>';
	    return $parse;
	}
	
	
	/*
	*内容相关url便捷书写
	*cateid  栏目id
	*
	*contid  内容id
	*/
	public function tagMaturl($tag){
	    $cateid = isset($tag['cateid']) ? $tag['cateid'] : '';
	    $contid = isset($tag['contid']) ? $tag['contid'] : '';
	    if(!empty($cateid) && !empty($contid)){
	        return;
	    }
	    if($cateid){  
	    if("$" == substr($cateid, 0, 1)){
	        $parse = '<?php ';
	        $cateid = $this->autoBuildVar($cateid);
	        $parse .= '$_CATID = '.$cateid.';';
	        $parse .= ' ?>';
	        $parse .= '{:url("index/Matters/matlist",["cateid"=>$_CATID])}';
	        return $parse;
	    }else{
	        $parse = '{:url("index/Matters/matlist",["cateid"=>'.$cateid.'])}';
	        return $parse;
	    }    
	    }elseif($contid){
	    if("$" == substr($contid, 0, 1)){
	        $parse = '<?php ';
	        $contid = $this->autoBuildVar($contid);
	        $parse .= '$_CONTID = '.$contid.';';
	        $parse .= ' ?>';
	        $parse .= '{:url("index/Matters/matcont",["contid"=>$_CONTID])}';
	        return $parse;
	    }else{
	        $parse = '{:url("index/Matters/matcont",["contid"=>'.$contid.'])}';
	        return $parse;
	    }    
	    }
	    return;
	}
	
	/*
	*会员中心url快捷书写
	*urlsu
	*uid：会员id
	*书写格式：{muy:urlsu id='*'}
	*/
	public function tagUrlsu($tag){
	    $id=isset($tag['uid']) ? $this->autoBuildVar($tag['uid']) : '';
	    if($id){
	    $parse = '<?php ';
	    $parse .= '$_D = '.$id.';';
	    $parse .= ' ?>';
	    $parse .= '{:url("index/user/my_home",["uid"=>$_D])}';
	    return $parse;
	    }
	}
	/**
	* 幻灯片调用标签
	* tab 调用栏目/模型下文章图为幻灯;必须结合operate操作方法1/2使用 operate=0 无需填写tab
	*
	* slide 是否限制幻灯属性 true/false;
	*
	* top 是否限制置顶属性 填写置顶级别 目前暂时1级;
	* 
	* operate 操作类型 0所有不限制(默认项 无需指定) 1根据栏目id操作 2根据模型标识操作;
	* 
	* limit  限制调用数量，默认限制10条;
	* 
	* order 数据排序方式 默认'create_time asc';
	*/
	public function tagPpt($tag,$content){
	    $limit = empty($tag['limit']) && !is_numeric($tag['limit']) ? '10' : intval($tag['limit']);
	    $tab = isset($tag['tab']) ? $tag['tab'] : '';
	    $operate = isset($tag['operate']) && is_numeric($tag['operate']) ? intval($tag['operate']) : '0';
	    $slide = isset($tag['slide']) && $tag['slide'] == 'true' ? $tag['slide'] : 'false';
	    $top = isset($tag['top']) && !is_numeric($tag['top']) ? intval($tag['top']) : '0';
	    $order = isset($tag['order']) ? $tag['order'] : 'create_time asc';
	    $parse ='<?php ';
	    $parse .= '$_LIST = getppt('.$limit.',"'.$order.'",'.$operate.',"'.$slide.'",'.$top.');';
	    if(!empty($tab)){
	        if("$" == substr($tab, 0, 1)){
	            $tab = $this->autoBuildVar($tab);
	            $parse .= '$_TAB = '.$tab.';';
	            $parse .= '$_LIST = getppt('.$limit.',"'.$order.'",'.$operate.',"'.$slide.'",'.$top.',"$_TAB");';
	        }else{
	            $parse .= '$_LIST = getppt('.$limit.',"'.$order.'",'.$operate.',"'.$slide.'",'.$top.',"'.$tab.'");';
	        }
	    }
	    $parse .= 'foreach($_LIST as $ppt):';
	    $parse .= 'extract($ppt);?>';//处理$ppt
	    $parse .= $content;
	    $parse .= '<?php endforeach; ?>';
	    return $parse;
	}
	
	/*
	*单页文件调用
	*
	*
	**/
	public function tagCuf($tag){
	    $id = isset($tag['id']) ? $tag['id'] : '';
	    $temp = isset($tag['temp']) ? $tag['temp'] : '';
	    if(!empty($id)){
	        if("$" == substr($id, 0, 1)){
	            $parse ='<?php ';
	            $id = $this->autoBuildVar($id);
	            $parse .= '$_CUFID = '.$id.';';
	            $parse .= ' ?>';
	            $parse .= '{:url("index/matters/artsctform",["formid"=>$_CUFID])}';
	            return $parse;
	        }else{
	            $parse = '{:url("index/matters/artsctform",["formid"=>'.$id.'])}';
	            return $parse;
	        }
	        return;
	    }
	    if(!empty($temp)){
	        if("$" == substr($temp, 0, 1)){
	            $parse ='<?php ';
	            $temp = $this->autoBuildVar($temp);
	            $parse .= '$_CUFT = '.$temp.';';
	            $parse .= ' ?>';
	            $parse .= '{:url("index/matters/artsctform",["t"=>$_CUFT])}';
	            return $parse;
	        }else{
	            $parse = '{:url("index/matters/artsctform",["t"=>"'.$temp.'"])}';
	            return $parse;
	        }
	        return;
	    }
	    return;
	    
	}
}