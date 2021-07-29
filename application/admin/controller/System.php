<?php
namespace app\admin\controller;
use app\admin\controller\Base;
use think\Request;
use think\facade\Session;
use think\Db;

class System extends Base
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
		//判断当前IP是否允许操作后台
		$ip = $this->ip_info();
		//判断是否登录
		$user = $this -> user_info();
		//模板赋值
		$system = Db::name('system')->where('id',1)->find();
		$this -> view -> assign('system',$system);
		return $this -> view -> fetch('system_base');
    }
	
	//屏蔽词渲染
	public function shielding()
	{
		//判断当前IP是否允许操作后台
		$ip = $this->ip_info();
		//判断是否登录
		$user = $this -> user_info();
		//模板赋值
		$system = Db::name('system')->where('id',1)->field('id,shielding')->find();
		$this -> view -> assign('system',$system);
		
		return $this -> view -> fetch('system_shielding');
		
	}

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request)
    {
		//判断当前IP是否允许操作后台
		$ip = $this->ip_info();
		//判断是否登录
		$user = $this -> user_info();
        //判断提交类型
		//if($request->isPost(true)){
			//获取提交数据
			$data = $request->post();
			$res = Db::name('system')->where('id', 1)->update($data);
			if($res){
				$this -> success("保存成功!",'System/index');
			}else{
				$this -> error("保存失败!");
			}
		//}
		
    }
	
	public function updates(Request $request)//屏蔽词
	{
		//判断当前IP是否允许操作后台
		$ip = $this->ip_info();
		//判断是否登录
		$user = $this -> user_info();
		$data = $request->post();
		$res = Db::name('system')->where('id', 1)->update($data);
		if($res){
			$this -> success("保存成功!",'System/shielding');
		}else{
			$this -> error("保存失败!");
		}
		
		
	}
	//模板文件列表
	public function templist()
	{
	    //模板赋值
	    $system = Db::name('system')->where('id',1)->field("temp")->find();
	    $ar = $this->getTempfiles("template");
	    $list = count($ar);
	    $path="";
	    for($i=0; $i<count($ar); $i++){
	        $path[] = include("./template/".$ar[$i]."/confing.php");
	    }
		$this -> view -> assign(['system'=>$system,'path'=>$path,'list'=>$list]);
	    return view('sysem_tempfile');
	}
	
	//模板文件删除操作
   public function filedel(Request $request){
      $dir = $_SERVER['DOCUMENT_ROOT']."/template/".$request ->param('filedelur');
      $this->rmdirr($dir);
   }
	
function rmdirr($dir){
//判断当前IP是否允许操作后台
$ip = $this->ip_info();
//判断是否登录
$user = $this -> user_info();
		
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
  $this->success("成功删除模板!",'System/templist');
}else{
  $this -> error("删除失败!");  
}
}

}
   
   
   
	public function tempstart(Request $request)
	{
	    $temp = $request->post('tp');
	    $res = Db::name('system')->where('id', 1)->update(['temp'=>$temp]);
	    if($res){
   	   		   $this -> success("启用成功!",'System/templist');
   	   }else{
   	   		   
   	   		   $this -> error("启用失败!");
   	   }
	}
	//网站ico和LOGO上传
	public function upload(Request $request){
		//判断当前IP是否允许操作后台
		$ip = $this->ip_info();
		//判断是否登录
		$user = $this -> user_info();
		//接收上传的文件
		$file = request()->file('file');
		if(!empty($file)){
			//移动到框架指定目录
			$info = $file->validate(['size'=>1048576,'ext'=>'ico,jpg,png,jpeg,gif'])->rule('uniqid')->move('./public/upload/imgages');
			if($info){
				//获取图片名称
				$imgName = str_replace("\\","/",$info->getSaveName());
				$photo = '/public/upload/imgages/'.$imgName;
			}else{
				$error = $file->getError();
			}
		}else{
			$photo = "";
		}
		//判断上传是否成功
		if($photo == ""){
			$error = $file->getError();
			return ['code'=>404,'msg'=>"上传失败,{$error}"];
		}else{
			return ['code'=>1,'msg'=>'上传成功',"photo"=>$photo];
		}
	} 
	
	//系统升级渲染
	public function upgrade()
	{
	    //判断当前IP是否允许操作后台
		$ip = $this->ip_info();
		//判断是否登录
		$user = $this -> user_info();
		//查询系统设置信息
		$system = Db::name('system')->where('id',1)->find();
		$this -> view -> assign('system',$system);
		return $this -> view -> fetch('system_upgrade');
	}
	
	//系统安全配置渲染
	public function safety()
	{
	    //判断当前IP是否允许操作后台
		$ip = $this->ip_info();
		//判断是否登录
		$user = $this -> user_info();
		$adname = include("./config/safety.php");
		$newname = $adname['adname'];
		$this -> view -> assign('porch',$newname);
		return $this -> view -> fetch('system_safety');
	}
	
	//执行后台入口名称的修改
	public function htporch(Request $request)
	{
	    //判断当前IP是否允许操作后台
		$ip = $this->ip_info();
		//判断是否登录
		$user = $this -> user_info();
		$name = $request->post('htporch');
		$adname = include("./config/safety.php");
		$newname = $adname['adname'];
		if($newname == $name){
		    $this -> error("文件名相同,无需修改!");
		    
		}
		$res = rename('./'.$newname,$name);
        $strConfig=<<<php
<?php
return array(
    //后台入口名称
    'adname'=>'{$name}',
    //其他拓展
);


php;
		if($res){
		    @chmod('./config/safety.php', 0777);
			$fppt = fopen('./config/safety.php', "w");
			fputs($fppt, $strConfig);
			fclose($fppt);
			Session::delete('Adminuser');
   	   		   $this -> success("更改成功,请重新登录!",$name.'/login/login');
   	   }else{
   	   		   
   	   		   $this -> error("更改失败!");
   	   }
	}

}
