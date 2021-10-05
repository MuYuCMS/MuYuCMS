<?php
namespace app\index\Controller;
use think\Controller;
use think\facade\Request;
use app\index\model\Member;
use think\facade\Session;
use think\facade\Cookie;
use think\facade\Env;
use think\Db;
use think\facade\Config;

class Base extends Controller
{
    
	// 初始化
    protected function initialize()
    {
        $tgmenu = Db::name("model")->where(["status"=>1,"issue"=>1,"type"=>1])->field("id,tablename,aliase,intro")->select();
        $useid=Session::get('Member.id');
        $usename="";
        if(!empty($useid)){
        $usename = Member::where('id',$useid)->find();
        }
        $daycont = Db::name('bigdata')->whereTime('create_time','today')->find();
		
        $this->assign(["tgmenu"=>$tgmenu,'member'=>$usename,"allconts"=>$daycont]);
        //判断大数据今日的数据是否更新插入
        $bigData = Db::name('bigdata')->whereTime('create_time','today')->find();
         if($bigData == NULL){
            Db::name('bigdata')->strict(false)->insert(['create_time'=>time()]);
        }
        //对pv、uv、ip进行统计
        Db::name('bigdata')->whereTime('create_time','today')->setInc('pv');
        if(!Cookie::has('first')){
            $ip = Request::ip();//获取客户端ip地址
            $isMobile = $this->isMobile();
            //判断是否为移动端
            if($isMobile){
                $source = 0;//0为移动端
                if(Session::has('Member')){
                    $id = Session::get('Member.id');
                    $memberId = Db::name('ip_log')->whereTime('create_time','today')->where('is_member',$id)->find();
                    if($memberId == NULL){
                        Db::name('bigdata')->whereTime('create_time','today')->setInc('uv');
                    }
                }else{
                    $id = 0;//id等于0的为游客
                }
            }else{//PC端
                $source = 1;//1为PC端
                if(Session::has('Member')){
                    $id = Session::get('Member.id');
                    $memberId = Db::name('ip_log')->whereTime('create_time','today')->where('is_member',$id)->find();
                    if($memberId == NULL){
                        Db::name('bigdata')->whereTime('create_time','today')->setInc('uv');
                    }
                }else{
                    $id = 0;//id等于0的为游客
                }            
            }
            $ipLog = Db::name('ip_log')->whereTime('create_time','today')->where('ip',$ip)->find();
            if($ipLog == NULL){
                Db::name('bigdata')->whereTime('create_time','today')->setInc('ip');
            }
            Db::name('ip_log')->insert(['ip'=>$ip,'is_member'=>$id,'source'=>$source,'create_time'=>time()]);
            //设置一个cookie防止刷pv,有效时间为1分钟
            Cookie::set('first',time(),60);
        }
    	//获取配置信息
    	$config = $this->getsystems();
    	//获取当前请求对象
    	$request = Request::instance();
    	//查询网站开关状态
    	$this -> getStatus($request,$config['is_close']);
    }

    //获取网站设置信息
	 public function getsystems(){
	    return Db::name('system')->where('id',1)->find();
	 }
	 
	 
	  
	//生成6位随机验证码
    public function codestr(){
         $arr=array_merge(range('0','9'),range('0','9'),range('0','9'));
         shuffle($arr);
         $arr=array_flip($arr);
         $arr=array_rand($arr,6);
         $res='';
         foreach ($arr as $v){
             $res.=$v;
            }
        return $res;
    }
	  
      //判断前台是否关闭
	  //传递两个参数请求对象和当前配置信息
	  public function getStatus($request,$config){
		  //判断当前不是admin模块才执行操作
		  if($request -> module() !== 'admin'){
			  //根据配置is_close再次判断,为1关闭，为0开启
			  if($config == 1){
				  $this -> error('网站已关闭!');
				  return false;
			  }
		  }
	  }
	  
	  public function getuserst(){
        $module = Request::module();
        $controller = Request::controller();
        $action = Request::action();
        $erurl = Config::get('rule.userer');
        $url = strtolower($module.'/'.$controller.'/'.$action);
        if(!in_array($url,$erurl)){
	      if(Session::has('Member') == null){
	          $this -> redirect('index/index/login');
			  return false;
	      }
        }else{
            $usses = $this->getsystems();
            if($usses["us_tg"] == 1){
                $this->error("管理员已关闭游客投稿!");
            }
        }
	  }
	  
	//图片上传
	public function imgupload(Request $request)
	{
		$file = request()->file('file');
		$url = upcheckurl(request()->param('upurl'));
		if($url == "false"){
		    return jsonmsg(0,"存储地址非法");
		}
		$res = allup($file,$url,'image',request()->param('id'));
		return $res;
	}
	
	//文件上传
	public function fileuplod(Request $request){
	   //接收上传的文件
		$file = request()->file('file');
		$url = upcheckurl(request()->param('upurl'));
		if($url == "false"){
		    return jsonmsg(0,"存储地址非法");
		}
		$res = allup($file,$url,'file',request()->param('id'));
		return $res; 
	}  
	  
	 /**
     *@todo 敏感词过滤，返回结果
     * @param array $list  定义敏感词一维数组
     * @param string $string 要过滤的内容
     * @return string $log 处理结果
     */
    public function sensitive($list,$string)
    {
        //违规词的个数  
        $count = 0; 
        //违规词
        $sensitiveWord = ''; 
        //替换后的内容
        $stringAfter = $string;  
        //定义正则表达式
        if(!empty($list)){
        $pattern = explode("|",$list);
	$pattern = array_filter($pattern);
        foreach($pattern as $key=>$vs){
            $pattern[$key] = "/".$vs."/i";
        }
        //匹配到了结果
      foreach($pattern as $vsl){ 
      if(preg_match_all($vsl, $string, $matches)){ 
        //匹配到的数组
        $patternList = $matches[0];  
        $count = count($patternList);
        //敏感词数组转字符串
        $sensitiveWord = implode(',', $patternList); 
        //把匹配到的数组进行合并，替换使用
        $replaceArray = array_combine($patternList,array_fill(0,count($patternList),'*')); 
        //结果替换
        $stringAfter = strtr($string, $replaceArray); 
      }
      }
      //$log = "原句为 [ {$string} ]<br/>";
      if($count==0){
        $status=0;
        //没有屏蔽词就原话返回
        $log = $string;
        return ['status'=>$status,'log'=>$log];
      }else{
        $status=1;
        //将屏蔽词$sensitiveWord赋值给$message
        $message=$sensitiveWord;
        return ['status'=>$status,'message'=>$message];
        /*$log .= "匹配到 [ {$count} ]个敏感词：[ {$sensitiveWord} ]<br/>".
          "替换后为：[ {$stringAfter} ]";*/
      }
        }
        $status=0;
        //没有设置违禁词原话返回
        $log = $string;
        return ['status'=>$status,'log'=>$log];
    }
    
    
    /**
     * 判断用户请求设备是否是移动设备
     * @return bool
     */
    public function isMobile() 
    {
    
        //如果有HTTP_X_WAP_PROFILE则一定是移动设备
        if (isset($_SERVER['HTTP_X_WAP_PROFILE'])){
            return true;
        }
        //如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
        if (isset($_SERVER['HTTP_VIA']) && stristr($_SERVER['HTTP_VIA'], 'wap')) {
            return true;
        }
        //野蛮方法,判断手机发送的客户端标志,兼容性有待提高
        if (isset($_SERVER['HTTP_USER_AGENT'])){
            $clientKeywords = ['nokia','sony','ericsson','mot','samsung','htc','sgh','lg','sharp','sie-','philips','panasonic','alcatel','lenovo','iphone','ipod','blackberry','meizu','android','netfront','symbian','ucweb','windowsce','palm','operamini','operamobi','openwave','nexusone','cldc','midp','wap','mobile'];
            //从HTTP_USER_AGENT中查找手机浏览器的关键字
            if (preg_match("/(".implode('|', $clientKeywords).")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
                return true;
            }
        }
        //协议法,因为有可能不准确,放到最后判断
        if (isset($_SERVER['HTTP_ACCEPT'])){
            //如果只支持wml并且不支持html那一定是移动设备
            //如果支持wml和html但是wml在html之前则是移动设备
            if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
                return true;
            }
        }
        return false;
    }
    

}
