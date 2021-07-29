<?php
 //  __  __                                                                                 
 // |  \/  |                                                                                
 // | \  / |  _   _   _   _   _   _    ___   _ __ ___    ___        ___    ___    _ __ ___  
 // | |\/| | | | | | | | | | | | | |  / __| | '_ ` _ \  / __|      / __|  / _ \  | '_ ` _ \ 
 // | |  | | | |_| | | |_| | | |_| | | (__  | | | | | | \__ \  _  | (__  | (_) | | | | | | |
 // |_|  |_|  \__,_|  \__, |  \__,_|  \___| |_| |_| |_| |___/ (_)  \___|  \___/  |_| |_| |_|
 //                    __/ |                                                                
 //                   |___/
				   
//				                  閒浴豀头静自居，此身已濯心何如。
				   
//				                  此心欲濯静中去，静定由来物自除。

// 
namespace app\admin\controller;

use app\admin\controller\Base;
use think\Request;
use app\admin\model\Links;
use app\admin\model\Advertising;
use think\facade\Env;
use think\Db;

class Plug extends Base
{
    protected function initialize()
    {
        
    }
    /**
     * 广告列表
     *
     * @return \think\Response
     */
    public function addindex()
    {
		$list = Advertising::order('id','desc')->paginate(25);
		return view('index_add',['list'=>$list]);
    }
	/**
	 * 友链列表
	 *
	 * @return \think\Response
	 */
	public function linkindex()
	{
	    //
		$list = links::order('orders','asc')->paginate(25);
		$check = links::where("checkurl",NULL)->count();
		return view('index_link',['list'=>$list,'check'=>$check]);
	}
	
	
	/**
	 * 插件列表
	 *
	 * @return \think\Response
	 */
	public function addonslist()
	{
		//获取插件列表数据
		//定义插件所在路径
		$addonspath = Env::get('root_path') . "addons";
		$addons = scandir($addonspath);
		$aons=[];
		foreach($addons as $va){
			if($va=="." || $va==".."){
				//跳过上级路径
				continue;
			}
			if(is_file($addonspath .'/'. $va)){
				//是文件直接跳过
				continue;
			}
			
			if(is_dir($addonspath .'/'. $va)){
			    
				//判断插件是否为有效插件
				//判断是否存在插件类
				if(!is_file($addonspath .'/' . $va .'/' . ucfirst($va) . '.php')){
					//不存在直接跳过
					continue;
				}
				
				//实例化插件类
				$class = get_addon_class($va);
				$info = (new $class)->info;
				//判断是否存在安装标识文件
				if(is_file($addonspath .'/' . $va .'/' . 'install.php')){
				    $info = include($addonspath .'/' . $va .'/' . 'install.php');
				}
				//判断插件基本信息是否有效
				if(!(new $class)->checkInfo()){
					//无效直接跳过
					continue;
				}
				$aons[] = $info;
			}
		}
		$this -> view -> assign(['aons'=>$aons]);
		return view('addons_list');
	}
	

    /**
	 * 插件的安装方法.
	 *
	 * @return \think\Response
	 */
    public function adonsinstall(Request $request){
        if(request()->isAjax()){
        $name = $request->param('name');
        $class = get_addon_class($name);
        $res = (new $class)->install();
        if($res === false){
            $this->error("安装失败!");
        }elseif($res === true){
            $this->success("安装成功!");
        }else{
            $this->error("未知错误!");
        }
        }
        $this->error("非法请求!");
    }
    
    /**
	 * 插件的卸载方法.
	 *
	 * @return \think\Response
	 */
     public function adonsupdate(Request $request){
        if(request()->isAjax()){
        $name = $request->param('name');
        $class = get_addon_class($name);
        $res = (new $class)->uninstall();
        if($res === false){
            $this->error("卸载失败!");
        }elseif($res === true){
            $this->success("卸载成功!");
        }else{
            $this->error("未知错误!");
        }
        }
        $this->error("非法请求!"); 
     }
    
    
    

    
    /**
     * 添加广告
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function addcreate(Request $request)
    {
		//接收post过来的数据
		if($request->isAjax()){
    		$data = $request->param();
    		$data['create_time'] = time();
    		//过期时间转化为时间戳
    		if(!empty($data['outtime'])){
    		$data['outtime'] = strtotime($data['outtime']);
    		if(empty($data['outtime']) || $data['outtime'] == 0){
    		    $data['outtime'] = null;
    		}
			}else{
    		   if(empty($data['outtime']) || $data['outtime'] == 0){
    		    $data['outtime'] = null;
				}
			}
    		//status为空时设置数值为0
    		if(isset($data['status']) == false){
    		    $data['status'] = 0;
    		}
    		//数据库添加操作
    		$admodel = new Advertising;
    		$res = $admodel->allowField(true)->save($data);
    		
    		//判断是否成功并提示
    		if($res){
    		    $getid = $admodel->id;
    		    $this->logs("广告 [ID: ".$getid.'] 添加成功!');
    			$this -> success("添加成功!",'Plug/addindex');
    		}else{
    			$this -> error("添加失败!");
    		}
		}
		return view('new_add');
    }
	
	/**
	 * 添加友情链接
	 *
	 * @return \think\Response
	 */
	public function linkcreate(Request $request)
	{
	    if($request->isAjax()){
	      $data = $request->param();
		  $data['create_time'] = time();
		  if(isset($data['status']) == false){
		     $data['status'] = 0;
		  }
	    //数据库添加操作
	    $linkmod = new Links;
		$res = $linkmod->allowField(true)->save($data);
	    //判断是否成功并提示
	    if($res){
	        $getid = $linkmod->id;
    		$this->logs("友情链接 [ID: ".$getid.'] 添加成功!');
	    	return jsonmsg(1,'添加成功!');
	    }else{
	    	return jsonmsg(0,'添加失败!');
	    }
	    }
	    $linkorder = 1;
		$order = Links::all();
		if($order != ""){
		foreach($order as $val){
			$linkorder = $val['orders']+1;
		}
		}
		return view('new_link',['linkorder'=>$linkorder]);
	}
	
	public function gettdk(Request $request){
	   if($request->isAjax()){
	   $url = $request->param("urls");
	   $res =  geturltdk($url);
	   if($res == 0){
	       return jsonmsg(0,'获取失败!');
	   }else{
	       return jsonmsg(1,$res);
	   }
	   }
	   return jsonmsg(0,"非法请求!");
	}

    

    //编辑广告
    public function addedit(Request $request)
    {
		if($request->isAjax()){
    		$data = $request->param();
    		//过期时间转化为时间戳
    		if(!empty($data['outtime'])){
    		 $data['outtime'] = strtotime($data['outtime']);
    		 if(empty($data['outtime']) || $data['outtime'] == 0){
    		    	$data['outtime'] = null;
    		 }
    		}else{
    		    if(empty($data['outtime']) || $data['outtime'] == 0){
    		    	$data['outtime'] = null;
    		 }
    		}
    		//status为空时设置数值为0
    		if(isset($data['status']) == false){
    		    $data['status'] = 0;
    		}
    		//数据库添加操作
    		$res = Advertising::update($data);
    		//判断是否成功并提示
    		if($res){
    		    $this->logs("广告 [ID：".$data['id'].'] 更新成功!');
    			$this -> success("更新成功!",'Plug/addindex');
    		}else{
    			$this -> error("更新失败!");
    		}
		}
		//获取旧数据
		//查询对应id信息并赋值
		$id = $request->param('id');
		$add = Advertising::get($id);
		//var_dump($add);
		return view('edit_add',['add'=>$add]);
		
    }
	
	
	//编辑友情链接
	public function linkedit(Request $request)
	{
		if($request->isPost()){
	    $data = $request->param();
	    //dump($data);
	    unset($data['file']);
	    //数据库添加操作
	    $res = Links::update($data);
	    //判断是否成功并提示
	    if($res){
	        $this->logs("友情链接 [ID：".$data['id'].'] 更新成功!');
	    	return jsonmsg(1,"更新成功!");
	    }else{
	    	return jsonmsg(0,"更新失败!");
	    }
	    }
	    $id = $request->param('id');
		$link = Links::get($id);
		return view('edit_link',['link'=>$link]);
	}


	
	//广告状态变更
	public function addstatus(Request $request){
		//获取前台传递id
		$add_id = $request->param('id');
		//根据id查询数据
		$result = Advertising::get($add_id);
		//查询原生数据进行判断
		if($result->getData('status') == 1){
			$res = Advertising::update(['status'=>0],['id'=>$add_id]);
			if($res){
			    $this->logs("广告 [ID：".$add_id.'] 隐藏成功!');
			    $this->success("隐藏成功！",'Plug/addindex');
			}else{
			    $this->error("隐藏失败！");
			}
		}else{
			$res = Advertising::update(['status'=>1],['id'=>$add_id]);
			if($res){
			    $this->logs("广告 [ID：".$add_id.'] 显示成功!');
			    $this->success("显示成功！",'Plug/addindex');
			}else{
			    $this->error("显示失败！");
			}
		}
	}
	
	//友链状态变更
	public function linkstatus(Request $request){
		//获取前台传递id
		$link_id = $request->param('id');
		//根据id查询数据
		$result = Links::get($link_id);
		//查询原生数据进行判断
		if($result->getData('status') == 1){
			Links::update(['status'=>0],['id'=>$link_id]);
			$this->logs("友链 [ID ：".$link_id.'] 隐藏成功!');
			return json(['code'=>1,'msg'=>'已隐藏!']);
		}else{
			Links::update(['status'=>1],['id'=>$link_id]);
			$this->logs("友链 [ID ：".$link_id.'] 显示成功!');
			return json(['code'=>1,'msg'=>'已显示!']);
		}
		return json(['code'=>0,'msg'=>'操作失败!']);
	}
	
	
	//检测友链是否上本站链接
     public function checkurl(Request $request)
     {
        if($request->isPost()){
            $curl="";
            $urlid = $request->param('id'); 
            if(!empty($urlid)){
                if(substr_count($urlid,',')>=1){  
                $urlid = explode(",",$urlid);
                $curl = Db::name("links")->where(["id"=>$urlid])->field("id,url")->select()->toArray();
                foreach($curl as $val){
	            $res = links_check($val['url']);
	            if($res == "ok"){
                links::where("id",$val['id'])->update(["checkurl"=>1]);
                }else{
                links::where("id",$val['id'])->update(["checkurl"=>0]);
                }
	            }
	            return json(['code'=>1,'msg'=>'检查完成!']);
              }else{
                $curl = links::where("id",$urlid)->field("id,url")->find();  
                $res = links_check($curl['url']);
                if($res == "ok"){
                links::where("id",$curl['id'])->update(["checkurl"=>1]);
                }else{
                links::where("id",$curl['id'])->update(["checkurl"=>0]);
                }
                return json(['code'=>1,'msg'=>'检查完成!']);
              }
            }
            return json(['code'=>0,'msg'=>'非法操作!']);    
        }
        return json(['code'=>0,'msg'=>'非法操作!']);
     }
     
	
	//广告删除/批量删除
	public function adddelete(Request $request){
	    if($request->isAjax()){
    		//获取传递过来的值并删除
    		$res =Advertising::destroy($request -> param('delid'));
    		if($res){
    		    $this -> logs("广告删除成功");
    			$this -> success("删除成功!",'Plug/addindex');
    		}else{
    			$this -> error("删除失败!");
    		}
	    }
	}
	
	//友链删除
	public function linkdelete(Request $request){
	    if($request->isPost()){
		//获取传递过来的值并删除
		$res =Links::destroy($request -> param('delid'));
		if($res){
			return jsonmsg(1,"删除成功");
		}else{
			return jsonmsg(0,"删除失败");
		}
	    }
	    return jsonmsg(0,"非法操作");
	}
	
	//广告图片上传
	public function addload(Request $request){
		//接收上传的文件
		$file = request()->file('file');
		if(!empty($file)){
			//移动到框架指定目录
			$info = $file->validate(['size'=>1048576,'ext'=>'jpg,png,jpeg,gif'])->rule('uniqid')->move('./public/upload/ggpic');
			if($info){
				//获取图片名称
				$imgName = str_replace("\\","/",$info->getSaveName());
				$photo = '/public/upload/ggpic/'.$imgName;
			}else{
				$error = $file->getError();
			}
		}else{
			$photo = "";
		}
		//判断上传是否成功
		if($photo == ""){
			$error = $file->getError();
			return ['code'=>0,'msg'=>"上传失败,{$error}"];
		}else{
			return ['code'=>1,'msg'=>'上传成功',"photo"=>$photo];
		}
		
	}
	//友情链接图标上传
	public function linkload(Request $request){
		//接收上传的文件
		$file = request()->file('file');
		if(!empty($file)){
			//移动到框架指定目录
			$info = $file->validate(['size'=>1048576,'ext'=>'jpg,png,jpeg,gif'])->rule('uniqid')->move('./public/upload/linkpic');
			if($info){
				//获取图片名称
				$imgName = str_replace("\\","/",$info->getSaveName());
				$photo = '/public/upload/linkpic/'.$imgName;
			}else{
				$error = $file->getError();
			}
		}else{
			$photo = "";
		}
		//判断上传是否成功
		if($photo == ""){
			$error = $file->getError();
			return ['code'=>0,'msg'=>"上传失败,{$error}"];
		}else{
			return ['code'=>1,'msg'=>'上传成功',"photo"=>$photo];
		}
		
	}
	
	/**
	*插件删除方法
	*
	*
	*
	*/
	public function addonsdel(Request $request){
      $dir = ADDON_PATH.$request ->param('filename');
      if(is_file($dir .'/' . 'install.php')){
        $this->error('请先卸载插件!');
		return false;
		}
      $res = rmdirr($dir);
      if($res){
          $this->success('删除成功!');
      }else{
          $this->error('删除失败!');
      }
      
	}
	
	//插件本地上传
	public function addonsup(Request $request){
	    //接收上传的文件
		$file = request()->file('addons');
		if($file){
		   $fileinfo = $file->getInfo(); 
		   $res = unzip(ADDON_PATH,$fileinfo['tmp_name']);
		   if($res){
		       return json(['code'=>1,'msg'=>'上传成功!']);
		   }else{
		       return json(['code'=>0,'msg'=>'上传失败!']);
		   }
		}
	}
	/*
	*应用中心渲染
	*
	*我们正在马不停蹄的开发中
	*
	*
	*/
	public function appshop(Request $request){
	    $status = ["msg"=>"我们正在马不停蹄的开发中","status"=>1];
	    return view('appshop',['status'=>$status]);
	    
	}
}
