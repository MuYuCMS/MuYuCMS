<?php
namespace app\admin\Controller;
use think\Controller;
use think\facade\Session;
use think\facade\Request;
use think\facade\Env;
use app\admin\model\Feedback;
use think\Db;
use auth\Auth;//auth权限类引入
use think\facade\Config;//获取配置类
class Base extends Controller
{
    protected $exclude;
    protected $login;
    //初始化
    protected function initialize()
    {
		$accres = Db::name('accre')->find('muyu');
		$accstatus = ['1'=>"未授权","2"=>"期限授权","3"=>"永久授权"];
		$accres["accre_sta"] = $accstatus[$accres["accre_sta"]];
		$this -> view -> assign(['accre'=>$accres]);
        //获取路由地址信息
        $this->exclude = Config::get('rule.exclude');
        $this->login = Config::get('rule.login');
        //获取系统设置表的所有信息
	    $config = Db::name('system')->where('id',1)->find();
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
	    
	    
        //判断大数据今日的数据是否更新插入
        $bigData = Db::name('bigdata')->whereTime('create_time','today')->find();
        if($bigData == NULL){
            Db::name('bigdata')->strict(false)->insert(['create_time'=>time()]);
        }
	    
	    
	    //权限检测, 获取当前模块名
        $module = Request::module();
        // 获取当前访问的控制器
        $controller = Request::controller();
        // 获取当前访问的方法
        $action = Request::action();
        //合并
        $url = strtolower($module.'/'.$controller.'/'.$action);
        //排除不需要检测登录和不需要检测权限的路由
        if (!in_array($url,$this->exclude)){
            //判断是否已经登录
            if(Session::has('Adminuser')){
    			  //排除不需要检测权限的路由
                if (!in_array($url,$this->login)){
                         //ID为1的是超级管理员，无须权限检测
                        if (Session::get('Adminuser.id') !== 1){
                            // 获取auth实例
                            $auth = new Auth();
                            // 检测权限
                            if(!$auth->check($url,Session::get('Adminuser.id'))){//规则名称,管理员ID
                                //没有操作权限
                                $this->error("无操作权限！",'Index/welcome');
                            }
                        }
                }
                $res = Session::get('Adminuser');
			    return $res;
		  }else{
			  $this -> redirect('Login/login');
			  return false;
		  }
        }
        
        

    }
    
    
    //图片上传
	public function imgupload(Request $request)
	{
		$file = request()->file('file');
		$res = allup($file,request()->param('upurl'),'image',request()->param('id'));
		return $res;
	}
	
	//文件上传
	public function fileuplod(Request $request){
	   //接收上传的文件
		$file = request()->file('file');
		$res = allup($file,request()->param('upurl'),'file',request()->param('id'));
		return $res; 
	}
	 //搜索事件
	 /*
	 * modall  true所有模型表 false单表
	 *
	 * tab   指定表  不存在模型下所有数据表查询 必传此项/其他表搜索直接传表名
	 *
	 * status  查询状态控制 1,2
	 *
	 * time  事件范围  开始时间,结束时间
	 *
	 * field 查询字段  title|author......
	 *
	 * data  是否查询附属表 true | false
	 *
	 * res 搜索内容
	 */
	 public function allsearch(Request $request){
	    if($this->request->isAjax()){ 
	    $list = "";
	    $alllist = [];
		$data = Request::param();
        if(!empty($data)){
        if(!isset($data['field']) || !isset($data['res'])){
          return json(['code'=>0,'msg'=>"缺少必要参数!"]);  
        }
        }else{
        return json(['code'=>0,'msg'=>"缺少必要参数!"]);    
        }
        $data['data'] = isset($data['data']) ? $data['data'] : 'false';
        $data['modall'] = isset($data['modall']) ? $data['modall'] : 'false';
		$where[] = [$data['field'], 'like', "%{$data['res']}%"];
		if(isset($data['status'])){
		  $where[] = ['status', 'in', $data['status']];     
		}
		$tab = Db::name('model')->field("id,tablename")->select();
		if($data['modall'] == "true"){
		   if($data['data'] == "true"){
		    $where[] = ['a.delete_time','=',NULL];
		    if(isset($data['time'])){//如果存在时间范围搜索
	    	$times = explode(",",$data['time']);
	    	foreach($tab as $v){
		    $ar = Db::name($v['tablename'])->alias("a")->join($v['tablename'].'_data b','a.id = b.aid')->fieldRaw('a.*,b.*')->where($where)->whereTime('create_time',[strtotime($times['0']), strtotime($times['1'])])->select()->toArray();
		    if(!empty($ar)){
		    foreach($ar as $key=>$l){
		        $ar[$key]['modid'] = $v['id'];
		        $ar[$key]['tabname'] = $v['tablename'];
		    }
		    }
		    $alllist[] = $ar;
	    	}
	    	if(!empty($alllist)){
		    $list = ary3_ary2($alllist);
		    }
		    }else{
		     foreach($tab as $v){
		    $ar = Db::name($v['tablename'])->alias("a")->join($v['tablename'].'_data b','a.id = b.aid')->fieldRaw('a.*,b.*')->where($where)->select()->toArray();
		    if(!empty($ar)){
		    foreach($ar as $key=>$l){
		        $ar[$key]['modid'] = $v['id'];
		        $ar[$key]['tabname'] = $v['tablename'];
		    }
		    }
		    $alllist[] = $ar;
	    	}
	    	if(!empty($alllist)){
		    $list = ary3_ary2($alllist);
		    }
		    }
		   }else{
		    $where[] = ['delete_time','=',NULL];     
		    if(isset($data['time'])){//如果存在时间范围搜索
		        $times = explode(",",$data['time']);
		        foreach($tab as $v){
		        $ar = Db::name($v['tablename'])->where($where)->whereTime('create_time',[strtotime($times['0']), strtotime($times['1'])])->select()->toArray();
		        if(!empty($ar)){
		        foreach($ar as $key=>$l){
		        $ar[$key]['modid'] = $v['id'];
		        $ar[$key]['tabname'] = $v['tablename'];
		        }
		        }
		        $alllist[] = $ar;
		        }
		        if(!empty($alllist)){
		        $list = ary3_ary2($alllist);
		        }
		    }else{
		        foreach($tab as $v){
		        $ar = Db::name($v['tablename'])->where($where)->select()->toArray();
		        if(!empty($ar)){
		        foreach($ar as $key=>$l){
		        $ar[$key]['modid'] = $v['id'];
		        $ar[$key]['tabname'] = $v['tablename'];
		        }
		        }
		        $alllist[] = $ar;
		        }
		        if(!empty($alllist)){
		        $list = ary3_ary2($alllist);
		        }
		    }
		   }
		}else{
		 if($data['data'] == "true"){
		    $where[] = ['a.delete_time','=',NULL];
		    if(isset($data['time'])){//如果存在时间范围搜索
	    	$times = explode(",",$data['time']);
		    $ar = Db::name($data['tab'])->alias("a")->join($data['tab'].'_data b','a.id = b.aid')->fieldRaw('a.*,b.*')->where($where)->whereTime('create_time',[strtotime($times['0']), strtotime($times['1'])])->select()->toArray();
		    if(!empty($ar)){
		    foreach($tab as $v){
		       foreach($ar as $key=>$va){
		          if($data['tab'] == $v['tablename']){
		            $ar[$key]['modid'] = $v['id'];
		            $ar[$key]['tabname'] = $v['tablename'];
		          } 
		       } 
		    }
		    }
		    $alllist[] = $ar;
	    	if(!empty($alllist)){
		    $list = ary3_ary2($alllist);
		    }
		    }else{
		    $ar = Db::name($data['tab'])->alias("a")->join($data['tab'].'_data b','a.id = b.aid')->fieldRaw('a.*,b.*')->where($where)->select()->toArray();
		    if(!empty($ar)){
		    foreach($tab as $v){
		       foreach($ar as $key=>$va){
		          if($data['tab'] == $v['tablename']){
		            $ar[$key]['modid'] = $v['id'];
		            $ar[$key]['tabname'] = $v['tablename'];
		          } 
		       } 
		    }
		    }
		    $alllist[] = $ar;
	    	if(!empty($alllist)){
		    $list = ary3_ary2($alllist);
		    }
		    }
		   }else{
		    $where[] = ['delete_time','=',NULL];     
		    if(isset($data['time'])){//如果存在时间范围搜索
		        $times = explode(",",$data['time']);
		        $ar = Db::name($data['tab'])->where($where)->whereTime('create_time',[strtotime($times['0']), strtotime($times['1'])])->select()->toArray();
		        if(!empty($ar)){
		        foreach($tab as $v){
		       foreach($ar as $key=>$va){
		          if($data['tab'] == $v['tablename']){
		            $ar[$key]['modid'] = $v['id'];
		            $ar[$key]['tabname'] = $v['tablename'];
		                } 
		            } 
		        }
		        }
		        $alllist[] = $ar;
		        if(!empty($alllist)){
		        $list = ary3_ary2($alllist);
		        }
		    }else{
		        $ar = Db::name($data['tab'])->where($where)->select()->toArray();
		        if(!empty($ar)){
		        foreach($tab as $v){
		        foreach($ar as $key=>$va){
		          if($data['tab'] == $v['tablename']){
		            $ar[$key]['modid'] = $v['id'];
		            $ar[$key]['tabname'] = $v['tablename'];
		                } 
		            } 
		        }
		        }
		        $alllist[] = $ar;
		        if(!empty($alllist)){
		        $list = ary3_ary2($alllist);
		        }
		    }  
		   }
		}
		if(empty($list)){
		   $msg = "没搜到内容";
		   return ['code'=>0,'msg'=>$msg];
		}
        $msg = "搜索成功";
	     return ['list'=>$list,'code'=>1,'msg'=>$msg];
	    }
	    $msg = "非法操作";
	    return ['code'=>0,'msg'=>$msg];
	 }
	 
	  
	  /*
	  *get_allfiles
	  *获取指定文件夹所有文件
	  *需要参数：$path 文件夹路径，&$files 文件数组
	  */
	 public function get_allfiles($path,&$files){
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
        //定义过滤压缩包后缀 防止模板列表渲染报错
        $passfile = ['rar','zip','7z','arj','bz2','cab','gz','iso','jar','lz','lzh','tar','uue','xz','z','zipx'];
        $handler = opendir($path);//当前目录中的文件夹下的文件夹
        $dile_name = [];
        while( ($filename = readdir($handler)) !== false ) {
            if($filename == "." || $filename == ".."){
                continue;
            }
            if(is_file($filename)){
                continue;
            }
            if(get_file_suffix($filename,$passfile)){
                continue;
            }
            $dile_name[] = $filename;
        }
        closedir($handler);
        return $dile_name;
    }
    
    
    /**
     * 读取远程文件内容
     * @return string
     */
    public function get_url_content($url)
    {
		$key ="http";
		if(strpos($url,$key) !== false && strpos($url,$key) == 0){
		$file_contents = file_get_contents($url,false);
		return $file_contents;
		}
		return json_encode(array("code"=>0,"msg"=>"请检查url的正确性"),JSON_UNESCAPED_UNICODE);
    }
    //记录系统日志
    public function logs($content,$name=""){
        //获取当前客户端的IP地址
        $data['log_ip'] = $this->request->ip();
        //获取记录时间
        $data['log_time'] = time();
        //获取用户name
        if(Session::has('Adminuser')){
        $data['user'] = Session::get('Adminuser.name');
        //获取用户id
        $data['user_id'] = Session::get('Adminuser.id');
        //记录内容
        }else{
        if(empty($name)){
            $data['user'] = "未知";
        }else{    
        $data['user'] = $name;
        //获取用户名
        }
        $data['user_id'] = "0";
        //用户id    
        }
        $data['content'] = $content;
        $res = Db::name('log')->insert($data);
        //删除三个月以前的数据
        $time1 = strtotime(date('Y-m-01 00:00:00',strtotime('-3 month')));
        $dd = Db::name('log')->where('log_time','<=',$time1)->delete();
        //var_dump($time1);
    }
    
    
}