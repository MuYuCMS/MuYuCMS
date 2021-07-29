<?php
namespace app\index\Controller;
use think\Controller;
use think\facade\Request;
use app\index\model\Hmenu;
use app\index\model\Member;
use think\facade\Session;
use think\facade\Env;
use think\Db;

class Base extends Controller
{
    //判断会员是否登录
     public function user_info(){
		  if(Session::has('Member')){
			  $res = Session::get('Member');
			  return $res;
		  }else{
			  $this -> redirect('index/Index/login');
			  return false;
		  }
	  }
	  //检查当前会员是否重复登录
	  public function user_login(){     
		if(Session::has('Member')){
					  $res = Session::get('Member');
					  $this -> error("请勿重复登录!",'index/User/index');
					  return false;
		}  
	  }
	// 初始化
      protected function initialize(){
          $zu = Db::name('hmenu')->where('pid','<>','0')->field('pid')->select()->toArray();
          $zu = array_unique(array_column($zu,'pid'));
          //全局自然序号使用
          $num = 1;
          $this->assign(['num'=>$num,'zu'=>$zu]);
		  //获取配置信息
		  $config = $this->getsystems();
		  //获取当前请求对象
		  $request = Request::instance();
		  $pd = "";
		  $ids = "";
		  $this -> view -> assign(['pd'=>$pd,'ids'=>$ids]);
		  //查询网站开关状态
		  $this -> getStatus($request,$config['is_close']);
}

	 public function getsystems(){
	    return Db::name('system')->where('id',1)->find();
	 }
	 
	 
	  
	//生成6位随机验证码
    public function codestr(){
         $arr=array_merge(range('a','z'),range('A','Z'),range('0','9'));
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
	  
	  
	 /**
     *@todo 敏感词过滤，返回结果
     * @param array $list  定义敏感词一维数组
     * @param string $string 要过滤的内容
     * @return string $log 处理结果
     */
    public function sensitive($list,$string){
        //违规词的个数  
        $count = 0; 
        //违规词
        $sensitiveWord = ''; 
        //替换后的内容
        $stringAfter = $string;  
        //定义正则表达式
        $pattern = "/".implode("|",$list)."/i"; 
        //匹配到了结果
      if(preg_match_all($pattern, $string, $matches)){ 
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

}