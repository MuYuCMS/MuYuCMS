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

class Plug extends Base
{
    /**
     * 显示广告列表
     *
     * @return \think\Response
     */
    public function addindex()
    {
        //判断用户是否登录
        $user = $this -> user_info();
		$add = Advertising::order('id','asc')->paginate(10);
		return view('add_index',['add'=>$add]);
    }
	/**
	 * 显示友链列表
	 *
	 * @return \think\Response
	 */
	public function linkindex()
	{
		//判断用户是否登录
		$user = $this -> user_info();
	    //
		$linklist = links::order('orders','asc')->paginate(10);
		return view('link_index',['linklist'=>$linklist]);
	}

    /**
     * 显示创建广告资源表单页.
     *
     * @return \think\Response
     */
    public function addcreate()
    {
		//判断用户是否登录
		$user = $this -> user_info();
        
		return view('add_new');
    }
	
	/**
	 * 显示创建友情链接资源表单页.
	 *
	 * @return \think\Response
	 */
	public function linkcreate()
	{
		//判断用户是否登录
		$user = $this -> user_info();
	    //
		$linkorder = 1;
		$order = Links::all();
		if($order != ""){
		foreach($order as $val){
			$linkorder = $val['orders']+1;
		}
		}
		return view('link_new',['linkorder'=>$linkorder]);
	}

    /**
     * 保存新建的广告资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function addsave(Request $request)
    {
        //判断用户是否登录
        $user = $this -> user_info();
		//接收post过来的数据
		$data = $request->post();
		$data['create_time'] = time();
		if(!empty($data['outtimes'])){
			$data['outtimes'] = strtotime($data['outtimes']);
		}
		//数据库添加操作
		$res = Advertising::insert($data);
		//判断是否成功并提示
		if($res){
			$this -> success("添加成功!",'Plug/addindex');
		}else{
			$this -> error("添加失败!");
		}
    }
	
	/**
	 * 保存新建的友联资源
	 *
	 * @param  \think\Request  $request
	 * @return \think\Response
	 */
	public function linksave(Request $request)
	{
	    //判断用户是否登录
	    $user = $this -> user_info();
	    //接收post过来的数据
	    $data = $request->post();
	    $data['create_time'] = time();
	    //数据库添加操作
	    $res = Links::insert($data);
	    //判断是否成功并提示
	    if($res){
	    	$this -> success("添加成功!",'Plug/linkindex');
	    }else{
	    	$this -> error("添加失败!");
	    }
	}

    /**
     * 显示编辑广告资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function addedit(Request $request)
    {
        //
		//判断用户是否登录
		$user = $this -> user_info();
		//查询对应id信息并赋值
		$id = $request->get('id');
		$add = Advertising::get($id);
		return view('add_add',['add'=>$add]);
    }
	
	/**
	 * 显示编辑友联资源表单页.
	 *
	 * @param  int  $id
	 * @return \think\Response
	 */
	public function linkedit(Request $request)
	{
	    //
		//判断用户是否登录
		$user = $this -> user_info();
		//
		$id = $request->get('id');
		$link = Links::get($id);
		return view('link_add',['link'=>$link]);
	}

    /**
     * 保存更新广告的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function addupdate(Request $request)
    {
        //判断用户是否登录
		$user = $this -> user_info();
		$data = $request->post();
		if(!empty($data['outtime'])){
			$data['outtime'] = strtotime($data['outtime']);
		}
		//数据库添加操作
		$res = Advertising::update($data);
		//判断是否成功并提示
		if($res){
			$this -> success("更新成功!",'Plug/addindex');
		}else{
			$this -> error("更新失败!");
		}
    }
	
	/**
	 * 保存更新的链接资源
	 *
	 * @param  \think\Request  $request
	 * @param  int  $id
	 * @return \think\Response
	 */
	public function linkupdate(Request $request)
	{
	    //判断用户是否登录
	    $user = $this -> user_info();
	    $data = $request->post();
	    //数据库添加操作
	    $res = Links::update($data);
	    //判断是否成功并提示
	    if($res){
	    	$this -> success("更新成功!",'Plug/linkindex');
	    }else{
	    	$this -> error("更新失败!");
	    }
	}
	
	//广告状态变更
	public function addstatus(Request $request){
		//判断是否登录
		$user = $this -> user_info();
		//获取前台传递id
		$add_id = $request->param('id');
		//根据id查询数据
		$result = Advertising::get($add_id);
		dump($add_id);
		dump($result);
		//查询原生数据进行判断
		if($result->getData('status') == 1){
			Advertising::update(['status'=>0],['id'=>$add_id]);
		}else{
			Advertising::update(['status'=>1],['id'=>$add_id]);
		}
	}
	
	//友链状态变更
	public function linkstatus(Request $request){
		//判断是否登录
		$user = $this -> user_info();
		//获取前台传递id
		$link_id = $request->param('id');
		//根据id查询数据
		$result = Links::get($link_id);
		//查询原生数据进行判断
		if($result->getData('status') == 1){
			Links::update(['status'=>0],['id'=>$link_id]);
		}else{
			Links::update(['status'=>1],['id'=>$link_id]);
		}
	}

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function adddelet(Request $request)
    {
        //判断是否登录
        $user = $this -> user_info();
        //获取传递过来的id
        $id = trim($request -> post('ids'));
        //获取传递过来的值并删除
        $res =Advertising::destroy($request -> post('ids'));
        if($res){
        	$this -> success("删除成功!",'Plug/addindex');
        }else{
        	$this -> error("删除失败!");
        }
    }
	
	//批量删除
	public function adddeleteall(Request $request){
		//判断是否登录
		$user = $this -> user_info();
		//获取传递过来的值并删除
		$res =Advertising::destroy($request -> post('delid'));
		if($res){
			$this -> success("删除成功!",'Plug/addindex');
		}else{
			$this -> error("删除失败!");
		}
	}
	
	/**
	 * 删除指定友链资源
	 *
	 * @param  int  $id
	 * @return \think\Response
	 */
	public function linkdelet(Request $request)
	{
	    //判断是否登录
	    $user = $this -> user_info();
	    //获取传递过来的id
	    $id = trim($request -> post('ids'));
	    //获取传递过来的值并删除
	    $res =Links::destroy($request -> post('ids'));
	    if($res){
	    	$this -> success("删除成功!",'Plug/linkindex');
	    }else{
	    	$this -> error("删除失败!");
	    }
	}
	
	//批量删除
	public function linkdeleteall(Request $request){
		//判断是否登录
		$user = $this -> user_info();
		//获取传递过来的值并删除
		$res =Links::destroy($request -> post('delid'));
		if($res){
			$this -> success("删除成功!",'Plug/linkindex');
		}else{
			$this -> error("删除失败!");
		}
	}
	
	//广告图片上传
	public function addload(Request $request){
		//判断是否登录
		$user = $this -> user_info();
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
			return ['code'=>404,'msg'=>"上传失败,{$error}"];
		}else{
			return ['code'=>1,'msg'=>'上传成功',"photo"=>$photo];
		}
		
	}
	//友情链接图标上传
	public function linkload(Request $request){
		//判断当前IP是否允许操作后台
		$ip = $this->ip_info();
		//判断是否登录
		$user = $this -> user_info();
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
			return ['code'=>404,'msg'=>"上传失败,{$error}"];
		}else{
			return ['code'=>1,'msg'=>'上传成功',"photo"=>$photo];
		}
		
	}
}
