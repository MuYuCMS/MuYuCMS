<?php

namespace app\admin\controller;

use app\admin\controller\Base;
use think\Request;
use think\Session;
use app\admin\model\Admin;
use app\admin\model\Hmenu;

class HomeMenus extends Base
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function menulist(Request $request)
    {
		//判断当前IP是否允许操作后台
		$ip = $this->ip_info();
		
		//判断是否登录
		$user = $this -> user_info();
		//接收页码
		$page = $request->get('page') ? $request->get('page') : 1;

        //查询所有栏目,并进行分页
		$menu = Hmenu::where('pid','0')->order('id','asc')->paginate(10);
		$menu2 = Hmenu::order('id','asc')->select();
		//添加一个数组索引
		$arr = ['0' =>'顶级栏目'];
		//创建数组 id为主键title建值
		foreach($menu2 as $key=>$val){
			$arr[$val['id']] = $val['title'];
		}
		
		foreach($menu as $key=>$val){
		    $menu[$key]['p_title'] = $arr[$val['pid']];
		    $menu[$key]['pmenu'] = $this->lmz($val['id']);
		}
		return view($template = 'list',['menu'=>$menu,'page'=>$page]);
    }
	
	public function lmz($pid){
	   $menu2 = Hmenu::order('id','asc')->select(); 
	   $arr = ['0' =>'顶级栏目'];
	  //创建数组 id为主键title建值
		foreach($menu2 as $key=>$val){
			$arr[$val['id']] = $val['title'];
		}
	  $menu3 = Hmenu::where("pid",$pid)->order('id','asc')->select()->toArray();
	    foreach($menu3 as $key=>$val){
		        $menu3[$key]['p_title'] = $arr[$val['pid']];
		    }
	 return $menu3;
	}
	
	//栏目状态变更
	public function setStatus(Request $request){
		//判断当前IP是否允许操作后台
		$ip = $this->ip_info();
		//判断是否登录
		$user = $this -> user_info();
		//获取前台传递id
		$admin_id = $request->param('id');
		//根据id查询数据
		$result = Hmenu::get($admin_id);
		//查询原生数据进行判断
		if($result->getData('status') == 1){
			Hmenu::update(['status'=>0],['id'=>$admin_id]);
		}else{
			Hmenu::update(['status'=>1],['id'=>$admin_id]);
		}
	}

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
		//判断当前IP是否允许操作后台
		$ip = $this->ip_info();
        //判断用户是否登录
        $user = $this -> user_info();
        //查询所有数据
        $menunew = Hmenu::field('id,title')->select();
        $this -> view -> assign('menunew',$menunew);
        //渲染增加界面
        return $this -> view -> fetch('list_new');
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
		//判断当前IP是否允许操作后台
		$ip = $this->ip_info();
		//判断用户是否登录
		$user = $this -> user_info();
        //
		$sort = Hmenu::all();
		$order="1";
		 if($sort != NULL | $sort != "")
		 foreach($sort as $key=>$val){
		 	$order = $val["sort"]+1;
		 }
		$data = $request->post();
		$data['sort'] = $order;
		if($data['links'] == 0){
		if($data['type']==0){
		$data['href'] = "index/Articles/Article_list";
		}elseif ($data['type']==99) {
		    $data['href'] = "index/index/index";
		}
		}
		$hmenu = Hmenu::insert($data);
		if($hmenu){
			$this -> success("添加成功!",'HomeMenus/menulist');
		}else{
			$this -> error("添加失败!",'HomeMenus/menulist');
		}
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit(Request $request)
    {
		//判断当前IP是否允许操作后台
		$ip = $this->ip_info();
        //判断是否登录
        $user = $this -> user_info();
		$page = $request->get('page');
        //获取对应ID
        $id = $request->get('id');
        //读取管理员表信息
        $list = Hmenu::get($id);
		$hmenus = Hmenu::where('pid',0)->field('id,title')->select();
        //赋值给模板
        $this -> view -> assign(['list'=>$list,'hmenus'=>$hmenus,'page'=>$page]);
        //
        return $this -> view -> fetch('list_add');
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
		//获取所有数据
		$data = $request->post();
		if($data['links'] == 0){
		if($data['type']==0){
		$data['href'] = "index/Articles/Article_list";
		}elseif ($data['type']==99) {
		    $data['href'] = "index/index/index";
		}
		}
		$page = $data['page'];
		unset($data['page']);
		$res =Hmenu::update($data);
		if($res){
			$this -> success("修改成功!",'HomeMenus/menulist?page={$page}');
		}else{
			$this -> error("修改失败!");
		}
        //
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete(Request $request)
    {
		//判断当前IP是否允许操作后台
		$ip = $this->ip_info();
        //判断是否登录
        $user = $this -> user_info();
        //获取传递过来的id
        $id = trim($request -> post('ids'));
        $pm = Hmenu::where("pid",$id)->select()->toArray();
        if(!empty($pm)){
           $this -> error("当前栏目有子栏目,请先删除子栏目!");
           return false;
        }
        //获取传递过来的值并删除
        $res =Hmenu::destroy($request -> post('ids'));
        if($res){
        	$this -> success("删除成功!",'HomeMenus/menulist');
        }else{
        	$this -> error("删除失败!");
        }
    }
	//栏目背景图上传
	public function upload(Request $request){
		//判断当前IP是否允许操作后台
		$ip = $this->ip_info();
		//判断是否登录
		$user = $this -> user_info();
		//接收上传的文件
		$file = request()->file('file');
		if(!empty($file)){
			//移动到框架指定目录
			$info = $file->validate(['size'=>1048576,'ext'=>'jpg,png,jpeg,gif'])->rule('uniqid')->move('./public/upload/menubg');
			if($info){
				//获取图片名称
				$imgName = str_replace("\\","/",$info->getSaveName());
				$photo = '/public/upload/menubg/'.$imgName;
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
	
	//批量删除
	public function deleteall(Request $request){
		//判断当前IP是否允许操作后台
		$ip = $this->ip_info();
		//判断是否登录
		$user = $this -> user_info();
		
		$ds = explode(",",$request -> post('delid'));
		foreach($ds as $val){
		$pm = Hmenu::where("pid",$val)->select()->toArray();
		if(!empty($pm)){
		   $this -> error("当前选择的栏目中,有包含子栏目的数据,请查正!");
           return false; 
		}
		}
		//获取传递过来的值并删除
		$res =Hmenu::destroy($request -> post('delid'));
		if($res){
			$this -> success("删除成功!",'HomeMenus/menulist');
		}else{
			$this -> error("删除失败!");
		}
	}

}
