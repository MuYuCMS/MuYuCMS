<?php
namespace app\admin\controller;
use app\admin\controller\Base;
use think\Request;
use think\facade\Session;
use think\Db;
use app\admin\model\SystemUpset as UpsetModel;
use think\facade\Config;

class System extends Base
{
    //网站配置
    public function index()
    {
		//模板赋值
		$system = Db::name('system')->where('id',1)->find();
		
		$this -> view -> assign('system',$system);
		return $this -> view -> fetch('index');
    }
	
	//屏蔽词渲染
	public function screen()
	{
		//模板赋值
		$system = Db::name('system')->where('id',1)->field('id,shielding')->find();
		$this -> view -> assign('system',$system);
		return $this -> view -> fetch('screen');
	}

    //网站配置编辑
    public function wzedit(Request $request)
    {
        //判断提交类型
		//if($request->isPost(true)){
			//获取提交数据
			$data = $request->param();
			$res = Db::name('system')->where('id', 1)->update($data);
			if($res){
			    $this->logs("网站配置编辑");
				return jsonmsg(1,'保存成功');
			}else{
				return jsonmsg(0,'保存失败');
			}
		//}
    }
    
	//屏蔽词编辑
	public function screenedit(Request $request)
	{
		$data = $request->param();
		$res = Db::name('system')->where('id', 1)->update($data);
		if($res){
		 	$this->logs('屏蔽词编辑');   
			$this -> success("保存成功!",'System/screen');
		
		}else{
			$this -> error("保存失败!");
		}
	}

	
	//模板文件删除操作
   public function tempdel(Request $request){
      $dir = $_SERVER['DOCUMENT_ROOT']."/template/".$request ->param('filedelur');
      $res = rmdirr($dir);
      if($res){
          $this->success('删除成功!');
      }else{
          $this->error('删除失败!');
      }
   }
   
   //模板启用
	public function tempstart(Request $request)
	{
	    $temp = $request->param('tp');
	    $res = Db::name('system')->where('id', 1)->update(['temp'=>$temp]);
	    if($res){
   	   		   $this -> success("启用成功!",'System/templist');
   	   }else{
   	   		   
   	   		   $this -> error("启用失败!");
   	   }
	}

	
	//系统升级渲染
	public function update()
	{
		//查询系统设置信息
		$system = Db::name('system')->where('id',1)->find();
		//版本为整数的时候保留小数部分
        $system['version'] = json_encode($system['version'],JSON_PRESERVE_ZERO_FRACTION);
		$this -> view -> assign(['system'=>$system]);
		return $this -> view -> fetch('update');
	}
	
	//系统安全配置渲染
	public function safety()
	{
	    //新后台入口
		$adname = Config::get("safety.adname");
		//上传配置
		$upset = Db::name('system_upset')->where('id',1)->find();
		//赋值模板
		$this -> view -> assign(['porch'=>$adname,'upset'=>$upset]);
		return $this -> view -> fetch('safety');
	}
	
	//安全设置修改
	public function safetyedit(Request $request)
	{
		$data = $request->param();
		$status = '1';
		$adname = Config::get("safety.adname");
		$msg = '修改成功!';
		if(isset($data['htporch'])){
		    if($adname == $data['htporch']){
		    return json(['code'=>0,'msg'=>'入口未改变!无需修改']);
		    false;
		}
		$res = rename('./'.$adname,$data['htporch']);
        $strConfig=<<<php
        <?php
            return array(
            //后台入口名称
            'adname'=>'{$data['htporch']}',
            //其他拓展
        );
php;
		if($res){
		    @chmod('./config/safety.php', 0777);
			$fppt = fopen('./config/safety.php', "w");
			fputs($fppt, $strConfig);
			fclose($fppt);
			Session::delete('Adminuser');
			$status = '2';
			$msg .= '请重新登录';
		}
	  }
	$upsetmode = new UpsetModel;
    $re = $upsetmode->allowField(true)->save($data,['id' => 1]);
    if($re){
            //权限恢复
            @chmod('./config/safety.php', 0775);
    	  return json(['code'=>1,'msg'=>$msg,'status'=>$status]);
    }else{
    	 return json(['code'=>0,'msg'=>'修改失败']);
    }
	}
	
	public function accrecheck(Request $request){
		if($request->isAjax()){
		$data = $request->param();
		if(empty($data['accres'])){
			return jsonmsg(0,"非法操作!");
		}
		$oldaccre = Db::name("accre")->where(["accre_id"=>"muyu","accre"=>$data["accres"]])->find();
		if(!empty($oldaccre)){
			return jsonmsg(0,"授权码未做变更!无需再次授权");
		}
		$hostname = $_SERVER['SERVER_NAME'];
		$getipname = gethostbyname($_SERVER['SERVER_NAME']);
		if(!empty($getipname) && $getipname !== "127.0.0.1" && $getipname !== "127.0.1.0" && !empty($hostname) && $hostname !== "localhost" && !empty($data['accres'])){
		$postUrl = "http://api.muyucms.com/installsetinfo/checkinstall";
		$postdata['ip'] = $getipname;
		$postdata['host'] = $hostname;
		$postdata['accre'] = $data['accres'];
		$curlPost = $postdata;
		$postdatas = http_build_query($curlPost);//处理请求数据,生成一个经过 URL-encode 的请求字符串
		$options = array(
		    'http' => array(
		      'method' => 'POST',//请求方式POST
		      'header' => 'Content-type:application/x-www-form-urlencoded',
		      'content' => $postdatas,
		      'timeout' => 5 // 超时时间（单位:s）
		    )
		);
		$context = stream_context_create($options);//创建并返回文本数据流
		$result = file_get_contents($postUrl, false, $context);//将文本数据流读入字符串
		$da = json_decode($result,true);
		if($da['accre'] == 1){
			$updata = ["accre_id"=>"muyu","accre"=>$data["accres"],"accre_sta"=>$da['accre_sta'],"accre_time"=>$da['accre_time'],"accre_name"=>$da['accre_name']];
			if(Db::name("accre")->update($updata)){
				return jsonmsg(1,$da['msg']);
			}else{
				return jsonmsg(0,"授权写入失败请稍后重试");
			}
		}else if($da['accre'] == 0){
				return jsonmsg(0,$da['msg']);
		}else{
			return jsonmsg(0,"授权验证失败!");
		}
		
		}
		return jsonmsg(0,"参数配置失败或为本地环境!请部署服务器!");
		}
		return jsonmsg(0,"非法操作!");
	}
}
