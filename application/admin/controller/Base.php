<?php
namespace app\admin\Controller;
use think\Controller;
use think\facade\Session;
use think\facade\Request;
use think\facade\Env;
use app\admin\model\Feedback;
use think\Db;

class Base extends Controller
{
      protected function initialize(){
				 $fdb = Feedback::where('status','0')->limit(5)->select();
				 $fdbs = Feedback::where('status','0')->select();
				 $ro = Request::Controller();
				 $me = Session::get('Adminuser');
				 $mes = Db::name("role")->where("id",$me['roles'])->field("jurisdiction")->find();
				 $mes = explode(",",$mes['jurisdiction']);
				 $me1 = Db::name("menu")->where(["id"=>$mes])->field("controller")->select()->toArray();
				 $roles = array_unique(array_column($me1,'controller'));
				 if($ro != "Index" && $ro != "Login" && $ro != "Update"){
				 if(!in_array($ro,$roles)){
				     $this->error("没有权限！");
				 }
				 }
				 $this->assign(['fdb'=>$fdb,'fdbs'=>$fdbs]);
				 
}
      public function user_info(){
		  if(Session::has('Adminuser')){
			  $res = Session::get('Adminuser');
			  return $res;
		  }else{
			  $this -> redirect('Login/login');
			  return false;
		  }
	  }
	  public function user_login(){
		if(Session::has('Adminuser')){
					  $res = Session::get('Adminuser');
					  $this -> error("请勿重复登录!",'Index/index');
					  return false;
		}  
	  }
	  
	  //判断ip是否允许登录
	  public function ip_info(){
	    //获取系统设置表的所有信息
	    $config = $this->getsystems();
	    //判断允许访问的IP是否为空
	    if($config['dlip'] != NULL){
	        //获取到的dlip字段分割成数组的形式
	    $pieces = explode(",", $config['dlip']);
	    //统计有多少个IP地址
	    $count = count($pieces);
	    //将数组$pieces赋值给$ALLOWED_IP
	    $ALLOWED_IP=$pieces;
	    //获取当前客户端的IP地址
        $IP=Request::ip();
        //要检测的ip拆分成数组
        $check_ip_arr= explode('.',$IP);
        //限制IP
        if(!in_array($IP,$ALLOWED_IP)) {
        foreach ($ALLOWED_IP as $val){
            //发现有*号替代符
            if(strpos($val,'*')!==false){
                 $arr=array();//
                 $arr=explode('.', $val);
                 //用于记录循环检测中是否有匹配成功的
                 $bl=true;
                 for($i=0;$i<$count;$i++){
                     //不等于*  就要进来检测，如果为*符号替代符就不检查
                     if($arr[$i]!='*'){
                         if($arr[$i]!=$check_ip_arr[$i]){
                             $bl=false;
                             //终止检查本个ip 继续检查下一个ip
                             break;
                         }
                     }
                 }//end for 
                 //如果是true则找到有一个匹配成功的就返回
                 if($bl){
                     return;
                     die;
                 }
            }
        }//end foreach
        header('HTTP/1.1 403 Forbidden');
        echo "当前IP地址不可访问该页面!";
        die;
        }
	    }
	    
	  }
	  
	  //获取配置信息
	  public function getsystems(){
	      //直接输出配置表信息
		  return Db::name('system')->where('id',1)->find();
	  }
	  
	  /*
	  *get_allfiles
	  *获取指定文件夹所有文件
	  *需要参数：$path 文件夹路径，&$files 文件数组
	  */
	 public function get_allfiles($path,&$files){
	     //判断当前IP是否允许操作后台
		$ip = $this->ip_info();
		//判断是否登录
		$user = $this -> user_info();
		 //判断$path是否为文件夹路径
		 if(is_dir($path)){
			 //继续打开文件夹
			 //#返回一个Directory对象
			 $dp = dir($path);
			 //读取该目录所有文件名
			 while($file = $dp->read()){
				 if($file != "." && $file != ".."){
					 $this->get_allfiles($path.'/'.$file,$files);
				 }
			 }
			 $dp->close();
		 }
		 if(is_file($path)){
			 $files[] = $path;
		 }
	 }
	  /*
	  *getMyfiles获取文件夹所有文件
	  */
	 public function getMyfiles(){
	     //判断当前IP是否允许操作后台
		$ip = $this->ip_info();
		//判断是否登录
		$user = $this -> user_info();
		 //接收path
		 $path = str_replace('\\','/',Request::param('path'));
		 if(!$path){
			 return false;
		 }else{
			 $img = array();//给予一个空数组来保存图片的路径
			 
			 $path = Env::get('root_path').$path;
			 $this->get_allfiles($path,$img);
			 foreach($img as $key=>$val){
				 $img[$key] = str_replace(Env::get('root_path').'public',"",$val);
			 }
			 $this->success($img);
		 }
		 
	 }
	 //获取所有模板文件
	public function getTempfiles($path)
    {

    //判断当前IP是否允许操作后台
	$ip = $this->ip_info();
	//判断是否登录
	$user = $this -> user_info();
    
    $handler = opendir($path);//当前目录中的文件夹下的文件夹
    while( ($filename = readdir($handler)) !== false ) {
        if($filename != "." && $filename != ".."){
            $dile_name[] = $filename;
        }
    }
    closedir($handler);
    return $dile_name;
    }
    
    
}