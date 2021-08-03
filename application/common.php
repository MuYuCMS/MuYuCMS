<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
use phpmailer\phpmailer;
use phpmailer\SMTP;
use think\Db;
use think\facade\Request;
use app\index\model\Category;
use think\facade\Env;
/*
 * 应用公共函数文件，函数不能定义为public类型，
 * 如果我们要使用我们定义的公共函数，直接在我们想用的地方直接调用函数即可。
 * */
// 公共发送邮件函数
function sendEmail($email,$emailpaswsd,$smtp,$sll,$emname,$title,$content,$toemail){

    $mail = new PHPMailer(true);                              // Passing `true` enables exceptions
    try {
        // 实例化PHPMailer核心类
        $mail = new PHPMailer();
        // 是否启用smtp的debug进行调试 开发环境建议开启 生产环境注释掉即可 默认关闭debug调试模式
        //$mail->SMTPDebug = 0;
        // 使用smtp鉴权方式发送邮件
        $mail->isSMTP();
        // smtp需要鉴权 这个必须是true
        $mail->SMTPAuth = true;
        // 链接smtp域名邮箱的服务器地址
        $mail->Host = $smtp;
        // 设置使用ssl加密方式登录鉴权
        $mail->SMTPSecure = 'ssl';
        // 设置ssl连接smtp服务器的远程服务器端口号
        $mail->Port = $sll;
        // 设置发送的邮件的编码
        $mail->CharSet = 'UTF-8';
        // 设置发件人昵称 显示在收件人邮件的发件人邮箱地址前的发件人姓名
        $mail->FromName = $emname;
        // smtp登录的账号 QQ邮箱即可
        $mail->Username = $email;
        // smtp登录的密码 使用生成的授权码
        $mail->Password = $emailpaswsd;
        // 设置发件人邮箱地址 同登录账号
        $mail->From = $email;
        // 邮件正文是否为html编码 注意此处是一个方法
        $mail->isHTML(true);
        // 设置收件人邮箱地址
        $mail->addAddress($toemail);
        // 添加多个收件人 则多次调用方法即可
        //$mail->addAddress('87654321@163.com');
        // 添加该邮件的主题
        $mail->Subject = $title;
        // 添加邮件正文
        $mail->Body = $content;
        // 为该邮件添加附件
        //$mail->addAttachment('./example.pdf');
        // 发送邮件 返回状态
        $status = $mail->send();
        return $status;
 
    } catch (Exception $e) {
        echo '邮件发送失败: ', $mail->ErrorInfo;
    }
}

    /**
     * 短信宝短信发送
     * $user短信宝账号, $passMD5加密的密码, $content短信内容, $phone手机号码
     */
    function sendSms($user,$pass,$content,$phone)
    {
        $statusStr = array(
            "0" => "短信发送成功",
            "-1" => "参数不全",
            "-2" => "服务器空间不支持,请确认支持curl或者fsocket，联系您的空间商解决或者更换空间！",
            "30" => "密码错误",
            "40" => "账号不存在",
            "41" => "余额不足",
            "42" => "帐户已过期",
            "43" => "IP地址限制",
            "50" => "内容含有敏感词"
        );
        $smsapi = "http://www.smsbao.com/"; //短信网关
        $sendurl = $smsapi."sms?u=".$user."&p=".$pass."&m=".$phone."&c=".urlencode($content);
        $result =file_get_contents($sendurl) ;
        return $statusStr[$result];
    }
    /**
     * 订单号生成函数
     * */
    function trade_no() 
    {
        list($usec, $sec) = explode(" ", microtime());
        $usec = substr(str_replace('0.', '', $usec), 0 ,4);
        $str  = rand(10,99);
        return date("YmdHis").$usec.$str;
    }
	
	/**
	 * 循环删除目录和文件
	 * @param string $dir_name
	 * @return bool
	 */
	function delete_dir_file($dir_name) {
	    $result = false;
	    if(is_dir($dir_name)){
	        if ($handle = opendir($dir_name)) {
	            while (false !== ($item = readdir($handle))) {
	                if ($item !== '.' && $item !== '..') {
	                    if (is_dir($dir_name . DIRECTORY_SEPARATOR .$item)) {
	                        delete_dir_file($dir_name . DIRECTORY_SEPARATOR . $item);
	                    } else {
	                        unlink($dir_name . DIRECTORY_SEPARATOR . $item);
	                    }
	                }
	            }
	            closedir($handle);
	            if (rmdir($dir_name)) {
	                $result = true;
	            }
	        }
	    }
	    return $result;
	}
	/*
	*递归遍历目录，作自定义文件选择目录
	*/
	function myscandir($pathName){
     //将结果保存在result变量中
    $result = array();
    //判断传入的变量是否是目录
    if(!is_dir($pathName) || !is_readable($pathName)) {
    return null;
    }
    //取出目录中的文件和子目录名,使用scandir函数
    $allFiles = scandir($pathName);
    //遍历
    foreach($allFiles as $key=>$fileName) {
    if(in_array($fileName, array('.', '..'))) {
      continue;
    }
    //路径加文件名
    $fullName = $pathName.'/'.$fileName;
    //如果是目录的话就继续遍历这个目录
    if(is_dir($fullName)) {
      //将这个目录信息存入到数组中
      $result[] = ["title"=>$fileName,"id"=>str_replace(Env::get('root_path'),'.',$fullName),"children"=>myscandir($fullName)];
        }
    }
    return array_filter($result);
    }
	
	
	//XSS/sql过滤/还需要进一步简化优化 达到更高效安全的最佳的方法
	function delete_XSS($val)
    {
    $str = trim($val);
    if (empty($str)) return false;
    $str = htmlspecialchars($str);
    $str = str_replace( '/', "", $str);
    $str = str_replace( '"', "", $str);
    $str = str_replace( '(', "", $str);
    $str = str_replace( ')', "", $str);
    $str = str_replace( 'CR', "", $str);
    $str = str_replace( 'ASCII', "", $str);
    $str = str_replace( 'ASCII 0x0d', "", $str);
    $str = str_replace( 'LF', "", $str);
    $str = str_replace( 'ASCII 0x0a', "", $str);
    $str = str_replace( ',', "", $str);
    $str = str_replace( '%', "", $str);
    $str = str_replace( ';', "", $str);
    $str = str_replace( 'eval', "", $str);
    $str = str_replace( 'open', "", $str);
    $str = str_replace( 'sysopen', "", $str);
    $str = str_replace( 'system', "", $str);
    $str = str_replace( '$', "", $str);
    $str = str_replace( "'", "", $str);
    $str = str_replace( "'", "", $str);
    $str = str_replace( 'ASCII 0x08', "", $str);
    $str = str_replace( '"', "", $str);
    $str = str_replace( '"', "", $str);
    $str = str_replace("", "", $str);
    $str = str_replace("&gt", "", $str);
    $str = str_replace("&lt", "", $str);
    $str = str_replace("<SCRIPT>", "", $str);
    $str = str_replace("</SCRIPT>", "", $str);
    $str = str_replace("<script>", "", $str);
    $str = str_replace("</script>", "", $str);
    $str = str_replace("select","",$str);
    $str = str_replace("join","",$str);
    $str = str_replace("union","",$str);
    $str = str_replace("where","",$str);
    $str = str_replace("insert","",$str);
    $str = str_replace("delete","",$str);
    $str = str_replace("update","",$str);
    $str = str_replace("like","",$str);
    $str = str_replace("drop","",$str);
    $str = str_replace("DROP","",$str);
    $str = str_replace("create","",$str);
    $str = str_replace("modify","",$str);
    $str = str_replace("rename","",$str);
    $str = str_replace("alter","",$str);
    $str = str_replace("cas","",$str);
    $str = str_replace("&","",$str);
    $str = str_replace(">","",$str);
    $str = str_replace("<","",$str);
    $str = str_replace(" ",chr(32),$str);
    $str = str_replace(" ",chr(9),$str);
    $str = str_replace("    ",chr(9),$str);
    $str = str_replace("&",chr(34),$str);
    $str = str_replace("'",chr(39),$str);
    $str = str_replace("<br />",chr(13),$str);
    $str = str_replace("''","'",$str);
    $str = str_replace("css","'",$str);
    $str = str_replace("CSS","'",$str);
    $str = str_replace("<!--","",$str);
    $str = str_replace("convert","",$str);
    $str = str_replace("md5","",$str);
    $str = str_replace("passwd","",$str);
    $str = str_replace("password","",$str);
    $str = str_replace("../","",$str);
    $str = str_replace("./","",$str);
    $str = str_replace("Array","",$str);
    $str = str_replace("or 1='1'","",$str);
    $str = str_replace(";set|set&set;","",$str);
    $str = str_replace("`set|set&set`","",$str);
    $str = str_replace("--","",$str);
    $str = str_replace("OR","",$str);
    $str = str_replace('"',"",$str);
    $str = str_replace("*","",$str);
    $str = str_replace("-","",$str);
    $str = str_replace("+","",$str);
    $str = str_replace("/","",$str);
    $str = str_replace("=","",$str);
    $str = str_replace("'/","",$str);
    $str = str_replace("-- ","",$str);
    $str = str_replace(" -- ","",$str);
    $str = str_replace(" --","",$str);
    $str = str_replace("(","",$str);
    $str = str_replace(")","",$str);
    $str = str_replace("{","",$str);
    $str = str_replace("}","",$str);
    $str = str_replace("-1","",$str);
    $str = str_replace("1","",$str);
    $str = str_replace(".","",$str);
    $str = str_replace("response","",$str);
    $str = str_replace("write","",$str);
    $str = str_replace("|","",$str);
    $str = str_replace("`","",$str);
    $str = str_replace(";","",$str);
    $str = str_replace("etc","",$str);
    $str = str_replace("root","",$str);
    $str = str_replace("//","",$str);
    $str = str_replace("!=","",$str);
    $str = str_replace("$","",$str);
    $str = str_replace("&","",$str);
    $str = str_replace("&&","",$str);
    $str = str_replace("==","",$str);
    $str = str_replace("#","",$str);
    $str = str_replace("@","",$str);
    $str = str_replace("mailto:","",$str);
    $str = str_replace("CHAR","",$str);
    $str = str_replace("char","",$str);
    return $str;
    }
    /*
	*@function 查询所有模型文章 fuck 标签库实在不想拼接 放在这里处理
	*
	*@param : $limit 查询数量
	*
	*@param ： $order 数据排序条件
	*
	*@return array_slice($all,$start,$limt) 返回数据
	*
	*/
    function setallmat($limt,$order){
        $page = input("page") ? input("page") : "1";
        if(strpos($limt,",")){
            $limt = explode(",",$limt);
        }
        if(is_array($limt)){
            $page = $limt["0"];
            $limt = $limt["1"];
        }
        
        $table = Db::name("model")->field("tablename")->select();
        $all = [];
        foreach($table as $v){
            $mater = Db::name($v["tablename"])
            ->alias("a")
            ->join($v["tablename"]."_data b","find_in_set(b.aid,a.id)")
            ->leftjoin("category c","find_in_set(c.id,a.mid)")
	        ->leftjoin("member d","find_in_set(d.id,a.uid)")
	        ->leftjoin("type e","find_in_set(e.id,a.type)")
	        ->fieldRaw("a.*,b.likes,b.browse,comment_t,c.title as catitle,d.name,e.title as tytitle")
            ->where(["a.delete_time"=>NULL,"a.status"=>"0"])
            ->limit($limt)
            ->select()
            ->toArray();
            if(!empty($mater)){
                $all[] = $mater;
            }
        }
        if(!empty($all)){
            $all = ary3_ary2($all);
        }
        $all = arrorder($all,$order);
        $count = count($all);//总条数
        $start = ($page - 1)*$limt;
        return array_slice($all,$start,$limt);
    }
    
    //文章栏目处理
	/*
	*@function 文章栏目处理 配合文章标签使用
	*@param : $mid 需要处理的栏目id
	*
	*@return : $tab 返回结果数组
	*
	*/
    function muyname($mid){
            $ids = Db::name("category")->where("id",$mid)->field("pid,modid")->find();
            $lmid = $mid;
    		$tab = ['ftabname'=>'','mid'=>$lmid,'tablename'=>''];
    		if(!empty($ids)){
            $lmid = muynamedigui($lmid);
            $tab = Db::name("model")->field("tablename")->find($ids['modid']);
    		$tab['ftabname'] = $tab['tablename'].'_data';
    		$tab['mid'] = $lmid;
    		}
            return $tab;
        }
        /*
    	*@function  配合muyname此函数处理栏目所有子孙数据
    	*
    	*@param  : $pid 需要查子孙数据id的标识
    	*
    	**
    	*/
    	function muynamedigui($pid){
    		$list = $pid;
    		if(is_numeric($list)){
    			$son = Db::name("category")->where("pid",$list)->field("id")->select()->toArray();
    			if(!empty($son)){
    			foreach($son as $vs){
    				$list .= "," . $vs['id'];
    				$list .= ",".muynamedigui($vs['id']);//继续查询子孙数据的子孙数据；
    			}
    			$a = explode(",",$list);
    			$b = array_unique($a);
    			$c = implode(",",$b);
    			$list = trim($c,',');
    			}
    		}
    		return $list;
    	}
	
	/*
	*@function 递归处理，还需改变，不适宜数据量过大的情况，否则效率大打折扣
	*@param : $data 需要递归处理的数据
	*
	*@param : $pid 子数据匹配条件 默认0
	*
	*@return $list 返回递归处理完成的数据
	*/
    function alldigui($data,$pid=0,$live=0){
           $list = array();//声明新数组，存放新单元
           if($live == 0){
           foreach($data as $v){
           if($v['pid'] == $pid){//匹配子数组			 
               $v['children'] = alldigui($data,$v['id']);//递归
               $list[] = $v;//处理好的数据存入新数组
               }
           }
           }else{
               foreach($data as $v){
                   if($v['id'] == $live){
                       $v['children'] = $data;
                       $list[] = $v;
                   }
               }
           }
           return $list;//返回
    }
    //循环删除文件夹文件
    function rmdirr($dir){
		
         if ($handle = opendir("$dir")){
          while (false !== ($item = readdir($handle))){
            if ($item != '.' && $item != '..'){
          if (is_dir("$dir/$item")){
            $this->rmdirr("$dir/$item");
           }else{
           if(unlink("$dir/$item")){
           }

            }
              }
             }
            closedir($handle);
            if(rmdir($dir)){
            return true;
            }else{
            return false;  
            }
          }

        }
     
    /*数据大小显示的转换
	*目前只应用于数据库表的大小显示转换
	*/
    function format_size($size){
        //定义数组
        $arr=['B','KB','M','G','T'];
        //定义变量i
        $i = 0;
        //循环
        while ($size>=1024){
            $size = $size/1024;
            $i++;
        }
        return round($size,2).$arr[$i];
    }
    
    
    /**
    * 获取TDK
    *$url 目标url 必须带http://或https://协议
    */
    function links_check($url){
        $myurl = think\facade\Request::domain();
		$key ="http";
		if(strpos($url,$key) !== false && strpos($url,$key) == 0){
        $data = file_get_contents($url,false);
        if(strpos($data,$myurl) !== false){
        return "ok";
        }else{
        return "false";
        }
		}
		return json_encode(array("code"=>0,"msg"=>"请检查url的正确性"),JSON_UNESCAPED_UNICODE);
    }
	/*
	*控制器便捷返回json数据信息
	*$code 状态码
	*$msg 提示信息
	*/
    function jsonmsg($code,$msg){
        return json(['code'=>$code,'msg'=>$msg]);
    }
    /**
    * 获取TDK
	*$url 目标url 必须带http://或https://协议
    */
    function geturltdk($url)
    {
	$key ="http";
	if(strpos($url,$key) !== false && strpos($url,$key) == 0){ 
    $html = file_get_contents($url,false);
    if (!$html) {
        return "0";
    }
    $data['title']['length']  = $data['keywords']['length'] = $data['description']['length'] = 0;
    $data['title']['content'] = $data['keywords']['content'] = $data['description']['content'] = '';
    $html                     = mb_substr($html, 0, 1000);
    preg_match("/<title>(.*)<\/title>/i", $html, $title);
    preg_match("/<meta name=\"keywords\" content=(.*)\/>/i", $html, $keywords);
    preg_match("/<meta name=\"description\" content=(.*)\/>/i", $html, $description);
    if (isset($title[1])) {
        $data['title']['content'] = str_replace('"', '', $title[1]);
        $data['title']['length']  = mb_strlen($data['title']['content']);
    }
    if (isset($keywords[1])) {
        $data['keywords']['content'] = str_replace('"', '', $keywords[1]);
        $data['keywords']['length']  = mb_strlen($data['keywords']['content']);
    }
    if (isset($description[1])) {
        $data['description']['content'] = str_replace('"', '', $description[1]);
        $data['description']['length']  = mb_strlen($data['description']['content']);
    }
    return $data;
	}
	return json_encode(array("code"=>0,"msg"=>"请检查url的正确性"),JSON_UNESCAPED_UNICODE);
    }
    /*
        *获取路径中文件名方法
        *$path 路径
        *$postfix 文件后缀
        *$fix 是否带当前文件名后缀输出 null输出文件名后缀
        */
    function gainfile($path,$postfix,$fix=null){
        $dile_name=[];
        foreach(glob($path . "/*" . $postfix) as $v)
        {
            if(is_null($fix)){
            $dile_name[]=basename($v);
            }else{
            $dile_name[]=basename($v,$fix);    
            }
        }
        return $dile_name;
    }
    
    function modsql($tabname){
        $sql="CREATE TABLE `$tabname` (
        `id` int(11) NOT NULL COMMENT 'id',
        `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT '标题',
        `ftitle` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '副标题',
        `titlepic` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '标题图片',
        `mid` int(11) DEFAULT NULL COMMENT '所属栏目',
        `uid` int(11) NOT NULL COMMENT '关联会员id',
        `type` int(11) DEFAULT NULL COMMENT '所属分类',
        `keyword` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '关键词',
        `abstract` text COLLATE utf8_unicode_ci COMMENT '摘要',
        `tag` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '标签',
        `author` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '发布者',
        `editor` text COLLATE utf8_unicode_ci NOT NULL COMMENT '内容',
        `price` tinyint(2) NOT NULL DEFAULT '0' COMMENT '阅读类型 1免费 2付费 3vip免费 4vip折扣',
        `moneys` decimal(10,2) NOT NULL DEFAULT '0' COMMENT '付费金额',
        `status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '发布状态 0发布 1草稿 2下架 3待审核 4驳回',
        `orders` int(11) NOT NULL DEFAULT '1' COMMENT '排序',
        `create_time` int(11) NOT NULL COMMENT '创建时间',
        `update_time` int(11) NOT NULL COMMENT '更新时间',
        `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
        `comment` tinyint(2) NOT NULL DEFAULT '0' COMMENT '是否允许评论 0允许 1不允许',
        `refusal` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '驳回原因',
        `top` tinyint(2) NOT NULL DEFAULT '0' COMMENT '置顶 0不置顶 1置顶',
        `ppts` tinyint(2) NOT NULL DEFAULT '0' COMMENT '标题图片幻灯 0不幻灯 1幻灯',
        `isadmin` tinyint(2) NOT NULL DEFAULT '0' COMMENT '是否管理员发布 0否 1是',
        `likes_ip` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '当前文章点过赞的ip',
        PRIMARY KEY (`id`)
        )";
        return $sql;
    }
    function datasql($datatab){
        $sql = "CREATE TABLE `$datatab` (
            `aid` int(11) NOT NULL COMMENT '关联文章id',
            `browse` int(11) NOT NULL DEFAULT '0' COMMENT '文章浏览次数',
            `likes` int(11) NOT NULL DEFAULT '0' COMMENT '文章点赞次数',
            `comment_t` int(200) NOT NULL DEFAULT '0' COMMENT '文章评论总计',
            `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
            PRIMARY KEY (`aid`)
            )";
            return $sql;
    }
    function fiesql($fietab,$modid){
        $sql="INSERT INTO `$fietab` (`modid`, `field`, `title`, `type`, `forms`, `defaults`, `leng`, `adst`, `hmst`, `required`, `chart`, `orders`, `ismuyu`) VALUES
        ($modid, 'title', '标题', 'varchar', 'text', NULL, '255', 1, 1, 1, 1, 0, 1),
        ($modid, 'titlepic', '标题图片', 'varchar', 'img', NULL, '255', 1, 1, 0, 1, 0, 1),
        ($modid, 'ftitle', '副标题', 'varchar', 'text', NULL, '255', 1, 1, 0, 1, 0, 1),
        ($modid, 'keyword', '关键词', 'varchar', 'text', NULL, '255', 1, 1, 0, 1, 0, 1),
        ($modid, 'abstract', '摘要', 'varchar', 'textarea', NULL, '255', 1, 1, 0, 1, 0, 1),
        ($modid, 'author', '发布者', 'varchar', 'text', NULL, '255', 1, 1, 0, 1, 0, 1);";
        return $sql;
    }
	/*
	*针对各个模型增加数据id统一自增
	*/
    function setconid(){
        $watch = Db::name('model')->field("tablename")->select();
        $as=[];
		foreach($watch as $key=>$va){
		   $as[] = Db::name($va['tablename'])->field("id")->order("id","desc")->find();
		}
		return max($as);
    }
    /*
	*解压缩方法
	*$path 需要解压的路径
	*需要解压的文件
	*
	*/
    function unzip($path,$filename){
       //解压缩
        $zip = new \ZipArchive;
        //要解压的文件
        $zipfile = $filename;
        $res = $zip->open($zipfile);
        if($res != true){
            return false;
        }
        //要解压到的目录
        $toDir = $path;
        if(!file_exists($toDir)) {
            mkdir($toDir,755);
        }
        $ress = $zip->extractTo($toDir);
        $zip->close();
        if($ress != true){
           return false; 
        }
        return true; 
    }
    /*
    *$file 接收需要上传的文件     *必须
    *
    *$url 上传文件需要保存的路径    *必须
    *
    *$ext上传类型 file文件上传  image 图片上传  *必须
    *
    *$id 自定义需要返回的处理值  可不传
    */
    function allup($file,$url,$ext,$id=NULL){
        $set = Db::name('system_upset')->find(1);
        $size = '1048576';
        if($ext == 'file'){
            $ext = $set['fileext'];
            $size = $set['filesize'];
        }else if($ext == 'image'){
            $ext = $set['imageext'];
            $size = $set['imagesize'];
        }else{
            return ['code'=>500,'msg'=>"上传参数配置错误!"];
            false;
        }
        $photo = "";
			if(!empty($file)){
				//移动到框架指定目录
				$info = $file->validate(['ext'=>$ext,'size'=>$size])->rule('uniqid')->move('.'.$url);
				if($info){
					//获取名称
					$imgName = str_replace("\\","/",$info->getSaveName());
					$photo = $url.'/'.$imgName;
				}else{
					$error = $file->getError();
				}
			}
			//判断上传是否成功
			if($photo == ""){
				$error = $file->getError();
				return ['code'=>404,'msg'=>"上传失败,{$error}"];
			}else{
				return ['code'=>1,'msg'=>'上传成功',"photo"=>$photo,'id'=>$id];
			}
    }
    
    	/*
    * @function                         三维数组转二维数组
    * @Param:      $array :             传入参数
    * @Return:     $tempArr             返回结果数组
    */
    function ary3_ary2($array){
	if(!empty($array)){
		if(is_array($array)){
		$array = array_filter($array);
		$array = array_values($array);
		$tempArr = [];
		foreach ($array as $orderKey =>$orderVal){
		    $count = count($orderVal);
		    if($count > 1){
		        for ($i = 0;$i < $count;$i++){
		            $tempArr[] = $orderVal[$i];
		        }
		    }else{
		        $tempArr[] = $orderVal[0];
		    }
		}
		return $tempArr;
		}
	}	
    return;
    }
	/*
	*@function 对数组排序 
	*
	*@param : $array 需要排序的数组
	*
	*@param : $order 排序的条件
	*
	*@return $array 返回排序后数组
	*
	*/
	 function arrorder($array,$order){
	    $newArr = array();
	    $ord = explode(" ",$order);
	    if($ord['1'] = "desc"){
	        $orderss = SORT_DESC;
	    }elseif($ord['1'] = "asc"){
	        $orderss = SORT_ASC;
	    }
	    foreach($array as $key=>$v){
	    $newArr[$key][$ord["0"]] = $v[$ord["0"]];
	    }
	    array_multisort($newArr,$orderss,$array);
	    return $array;
	}
	/*
	*@function  判断文件$file_name后缀是否符合$allow_type数组里的后缀
	*
	*@param $file_name 被判定文件后缀的文件
	*
	*@param $allow_type 判定后缀依据
	*
	*@return 返回bool值true or false;
	*/
    function get_file_suffix($file_name, $allow_type = array())
    {
	//分割文件得到当前文件的后缀数组
    $fnarray=explode('.', $file_name);
	//将后缀转换为小写/删除数组最后一个元素‘.’
    $file_suffix = strtolower(array_pop($fnarray));
	//如果没有判定依据就直接输出当前文件后缀
    if (empty($allow_type))
    {
        return $file_suffix;
    }
    else
    {
        if (in_array($file_suffix, $allow_type))
        {
        return true;
        }
        else
        {
        return false;
        }
    }
    }
    
    /*
	*@function  根据标签库获取条件进行查询栏目数据处理
	*@param : $limt 查询的数量
	*@param : $order 排序方式
	*@param : $id 栏目ID 有值则只查此id的父子数据
	*@return : $res 返回递归处理数据
	*/
    function hnavs($limt,$order,$id=""){
        $where[] = ['status','=',1];
        $where[] = ['type','=',0];
        $live = 0;
        if(!empty($id)  && is_numeric($id)){
        $sonid = $id;    
        $son = Db::name("category")->where("pid",$sonid)->field("id")->select()->toArray();
        if(!empty($son)){
            foreach($son as $v){
                $sonid .= "," . $v['id'];
            }
        }else{
            $one = Db::name("category")->field("pid")->find($sonid);
			if(!empty($one)){
            if($one['pid'] != 0){
			$live = $sonid;
			$where[] = ['pid','=',$one['pid']];
            $sons = Db::name("category")->where($where)->field("id")->select()->toArray();
            if(!empty($sons)){
                foreach($sons as $v){
                $sonid .= "," . $v['id'];
                }
            }
            }
			}
        }    
        $where[] = ['id','in',$sonid];
        }
        $res = Db::name("category")->where($where)->orderRaw($order)->limit($limt)->select()->toArray();
		$res = alldigui($res,0,$live);
        return $res;
    }
    
    /*
    *导航标签高亮在此处理
    *也可直接页面使用
    *{$id|thisclass=###,"layui-this"}
    *
    *$thisid 当前栏目id
    *$class 高亮样式
    */
    function thisclass($thisid,$class){
        $classid = input("cateid") ? input("cateid") : null;
        $aid = input("contid") ? input("contid") : null;
        if($aid){
            $table = Db::name("model")->field("tablename")->select();
            foreach ($table as $t){
                $ainfo = Db::name($t['tablename'])->field("mid")->find($aid);
                if($ainfo){
                    $classid = $ainfo["mid"];
                }
            }
        }
        if($classid){
            $catinfo = Db::name("category")->field("id,pid")->find($classid);
            if($thisid == $classid){
                return $class;
            }elseif($thisid == $catinfo["pid"]){
                return $class;
            }
        }
        return;
    }
    
    /*
    *面包屑函数 snav($mid,$aid,$symbol)
    *$mid 栏目id 列表页可省略
    *$cid 内容id 内容页可省略
    *$symbol 分割符号 默认 / 
    */
    function snavs($mid="",$symbol="/"){
        $mid = $mid ? $mid : input("cateid");
        $html = '<a href="http://'.$_SERVER["SERVER_NAME"].'">首页</a>';
        if(!empty($mid)){
            $tinfo = Db::name("category")->field("id,pid,title")->find($mid);
            if($tinfo["pid"] != 0){
                $classinfo = Db::name("category")->field("id,pid,title")->find($tinfo["pid"]);
                $html .= '<span>'.$symbol.'</span><a href="http://'.$_SERVER["SERVER_NAME"]."/matlist_".$classinfo['id'].'.html">'.$classinfo["title"].'</a>';
            }
            $html .= '<span>'.$symbol.'</span><a href="http://'.$_SERVER["SERVER_NAME"]."/matlist_".$tinfo['id'].'.html">'.$tinfo["title"].'</a>';
        }
        return $html;
    }
    
    //处理tags/和key的调用
    function tagkey($limt,$field,$order,$catid="",$matid=""){
        $list = [];
        if(empty($catid) && empty($matid)){
            $tab = Db::name("model")->field("tablename")->select();
            foreach($tab as $v){
                $det = Db::name($v['tablename'])->order($order)->limit($limt)->field($field)->select()->toArray();
                if($det){
                $list[] = $det;
                }
            }
			if(!empty($list)){
				$list = ary3_ary2($list);
				$l = array_column($list,$field);
				$list = array_filter(array_unique(explode(",",implode(",",$l))));
			}else{
				$list = array();
			}
        }
        if($catid){
            $modid = Db::name("category")->field("modid")->find($catid);
            $tab = Db::name("model")->field("tablename")->find($modid['modid']);
            $list = Db::name($tab['tablename'])->order($order)->limit($limt)->field($field)->select()->toArray();
            $l = array_column($list,$field);
            $list = array_unique(explode(",",implode(",",$l)));
        }
        if($matid){
            $tab = Db::name("model")->field("tablename")->select();
            foreach($tab as $v){
                $det = Db::name($v['tablename'])->field($field)->find($matid);
                if($det){
                    $list = $det;
                }
            }
            $list = array_unique(explode(",",$list[$field]));
        }
        return $list;
    }
    //处理幻灯标签查询
    function getppt($limt,$order,$operate,$slide="",$top="",$tab=""){
            $where[] = ["status","=",0];
            $where[] = ["delete_time","=",NULL];
            $field = "id,title,ftitle,titlepic,create_time";
            $list = "";
            if(!empty($slide) && $slide == 'true'){
                $where[] = ["ppts","=",1];
            }
            if(!empty($top) && is_numeric($top) ){
                $where[] = ["top","=",$top];
            }
            if(!in_array($operate,array("0","1","2")) || !is_numeric($operate)){//不在类型内 结束
    	        return;
    	    }
            if($operate == "0"){
                $tabs = Db::name("model")->field("tablename")->select();
                foreach($tabs as $v){
                    $list = Db::name($v["tablename"])->where($where)->limit($limt)->order($order)->field($field)->select();
                }
            }elseif($operate == "1"){
                if(!is_numeric($tab) || empty($tab)){
                    return;
                }
                $where[] = ["mid","=",$tab];
                $modid = Db::name("category")->field("modid")->find($tab);
                $tabn = Db::name("model")->field("tablename")->find($modid["modid"]);
                $list = Db::name($tabn["tablename"])->where($where)->limit($limt)->order($order)->field($field)->select();
            }elseif($operate == "2"){
                if(empty($tab)){
                    return;
                }
                $list = Db::name($tab)->where($where)->limit($limt)->order($order)->field($field)->select();
            }
            if(empty($list)){
                $list =array();
            }
            return $list;
        }
	//评论标签获取对应文章ID、标题、标题图片、、
    function matsection($aid){
        if(!empty($aid)){
            $tab = Db::name("model")->field("tablename")->select();
            $array= "";
            foreach($tab as $v){
                $info = Db::name($v["tablename"])->field("id,title,titlepic")->find($aid);
                if($info){
                    $array = $info;
                }
            }
            return $array;
        }
        return;
    }
    
    /*
    *muysubstr($str, $start=0, $length, $charset=”utf-8″, $suffix=true)
    *$str:要截取的字符串
    *$start=0：开始位置，默认从0开始
    *$length：截取长度
    *$charset=”utf-8″：字符编码，默认UTF－8
    *$suffix=true：是否在截取后的字符后面显示符号,可任意的定义
    *
    *{$val.title|muysubstr=###,0,7,'utf-8'}
    *
    */
    function muysubstr($str, $start=0, $length, $charset="utf-8", $suffix="")  
    {  
    if(function_exists("mb_substr")){
            return mb_substr($str, $start, $length, $charset).$suffix;
         }elseif(function_exists('iconv_substr')){
            return iconv_substr($str,$start,$length,$charset).$suffix; 
         }  
         $re['utf-8']   = "/[x01-x7f]|[xc2-xdf][x80-xbf]|[xe0-xef][x80-xbf]{2}|[xf0-xff][x80-xbf]{3}/";  
         $re['gb2312'] = "/[x01-x7f]|[xb0-xf7][xa0-xfe]/";  
         $re['gbk']    = "/[x01-x7f]|[x81-xfe][x40-xfe]/";  
         $re['big5']   = "/[x01-x7f]|[x81-xfe]([x40-x7e]|xa1-xfe])/";  
         preg_match_all($re[$charset], $str, $match);  
         $slice = join("",array_slice($match[0], $start, $length));  
         if($suffix) return $slice.$suffix;  
         return $slice;
    }
