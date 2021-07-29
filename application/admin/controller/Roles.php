<?php

namespace app\admin\controller;

use app\admin\model\Role;
use app\admin\model\Admin;
use app\admin\controller\Base;
use think\Request;
use think\Db;

class Roles extends Base
{
    //角色权限列表渲染输出
    public function rolelist()
    {
		//判断当前IP是否允许操作后台
		$ip = $this->ip_info();
		//验证用户是否登录
		$user = $this -> user_info();
        //查询所有角色
		$role = Role::order('create_time','desc')->paginate(10);
		foreach($role as $key=>$val){
			//查询角色下用户
			$username = Admin::where('roles',$val['id'])->field('name')->select()->toArray();
			$role[$key]['username'] = $username;
		}
		return view('admin_role',['role'=>$role]);
    }

    //角色添加
    public function create()
    {
        //判断当前IP是否允许操作后台
        $ip = $this->ip_info();
		//判断用户是否登录
		$user = $this -> user_info();
		$menu = Db::name('menu')->where(['pid'=>0])->order('orders','asc')->select()->toArray();
		foreach($menu as $key=>$val){
			$two = Db::name('menu')->where(['pid'=>$val['id'],'status'=>'1'])->select()->toArray();
			$menu[$key]['secontit'] = $two;
		}
		return view('admin_role_new',['menu'=>$menu]);
    }

    //添加角色提交表单
    public function save(Request $request)
    {
		//判断当前IP是否允许操作后台
		$ip = $this->ip_info();
		//判断用户是否登录
		$user = $this -> user_info();
		$sorts = Role::all();
		$sort = 1;
		//自动排序
		foreach($sorts as $key=>$val){
			if(!empty($val['orders'])){
				$sort = $val["orders"]+1;
			}
		}
		 //接收提交过来的信息
		$data = array_filter($request->post());
		//创建时间
		$data['create_time'] = time();
		//修改时间
		$data['update_time'] = 0;
		//排序
		$data['orders'] = $sort;
		$data['jurisdiction'] = substr($data['jurisdiction'],0,-1);
		if(empty($data['name'])){
			$this -> error("角色名不能为空!");
			return false;
		}else{
			if(Role::get(['name'=>$data['name']])){
			$this -> error("角色名已存在!");
			return false;	
		}else{
			$res = Role::insert($data);
			//提示信息
			if($res){
				$this -> success("添加成功!",'Roles/rolelist');
			}else{
				$this -> error("添加失败!");
			}
		}
		}
		
    }

    //角色管理列表编辑
    public function edit($id)
    {
        //判断当前IP是否允许操作后台
        $ip = $this->ip_info();
		//判断是否登录
		$user = $this -> user_info();
		//获取对应ID的角色信息
		$roles = Role::get($id);
		$menu = Db::name('menu')->where(['pid'=>0])->order('orders','asc')->select()->toArray();
		foreach($menu as $key=>$val){
			$two = Db::name('menu')->where(['pid'=>$val['id'],'status'=>'1'])->select()->toArray();
			$menu[$key]['secontit'] = $two;
		}
		$roles['jurisdiction'] = explode(',',$roles['jurisdiction']);
		return view('admin_role_add',['roles'=>$roles,'menu'=>$menu]);
    }

    //角色管理列表编辑提交表单
    public function update(Request $request)
    {
		//判断当前IP是否允许操作后台
		$ip = $this->ip_info();
        //判断是否登录
        $user = $this -> user_info();
		$data = array_filter($request->post());
		$data['jurisdiction'] = substr($data['jurisdiction'],0,-1);
		$res = Role::update($data);
		//提示信息
		if($res){
			$this -> success("保存成功!",'Roles/rolelist');
		}else{
			$this -> error("保存失败!");
		}
    }

    //角色管理列表删除操作
    public function delete(Request $request)
    {
		//判断当前IP是否允许操作后台
		$ip = $this->ip_info();
        //判断是否登录
        $user = $this -> user_info();
        //获取传递过来的值并删除
        $res =Role::destroy($request -> post('ids'));
        if($res){
        	$this -> success("删除成功!",'Roles/rolelist');
        }else{
        	$this -> error("删除失败!");
        }
		
    }
    
	//角色管理列表批量删除
	public function deleteall(Request $request){
		//判断当前IP是否允许操作后台
		$ip = $this->ip_info();
		//判断是否登录
		$user = $this -> user_info();
		//获取传递过来的值并删除
		$res =Role::destroy($request -> post('delid'));
		if($res){
			$this -> success("删除成功!",'Roles/rolelist');
		}else{
			$this -> error("删除失败!");
		}
	}
	/**
	 * 权限管理
	 *
	 * 
	 * 
	 */
	public function permission(){
		//判断当前IP是否允许操作后台
		$ip = $this->ip_info();
		//判断是否登录
		$user = $this -> user_info();
		return view('admin_permission');
	}
}
