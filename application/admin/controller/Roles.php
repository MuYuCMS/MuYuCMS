<?php

namespace app\admin\controller;
use think\facade\Session;
use app\admin\model\Role;
use app\admin\model\Admin;
use app\admin\controller\Base;
use think\Request;
use think\Db;
class Roles extends Base
{
    //角色权限列表渲染输出
    public function roleList()
    {
        //获取当前管理员ID
        $id = Session::get('Adminuser.id');
        if($id == 1){
            //查询所有角色
    		$role = Role::order('create_time','desc')->paginate(25);
    		foreach($role as $key=>$val){
    			//查询角色下用户
    			$username = Admin::where('roles',$val['id'])->field('name')->select()->toArray();
    			$role[$key]['username'] = $username;
    		}
        }else{
            //查询当前管理员所用的权限
            $role = Role::all(['id'=>$id]);
            foreach($role as $key=>$val){
    			//查询角色下用户
    			$username = Admin::where('roles',$val['id'])->field('name')->select()->toArray();
    			$role[$key]['username'] = $username;
    		}
        }
        
		return view('list',['roles'=>$role]);
    }

    //请求数据源  添加/编辑在使用
    public function getData(Request $request)
    {
        if(request()->isAjax()){
        //查询一级ID
        $one = Db::name('rule')->where('pid',0)->field(['id','title'])->select()->toArray();
        //循环一级数组
        foreach($one as $key => $val){
            //查询二级ID
            $two = Db::name('rule')->where('pid',$val['id'])->field(['id','title','pid'])->select()->toArray();
            $one[$key]['children'] = $two;
            //循环二级数组
            foreach ($one[$key]['children'] as $item => $value){
                $three = Db::name('rule')->where('pid',$value['id'])->field(['id','title','pid as ppid'])->select()->toArray();
                //循环将pid赋值给$three
                for ($i=0; $i<count($three); $i++){
                    $three[$i]['pid'] = $value['pid'];
                }
                $one[$key]['children'][$item]['children'] = $three;
            }
        }
            return json($one);
        }
    }
    
    //角色添加
    public function add(Request $request)
    {
        //判断是否为ajax请求
        if(request()->isAjax()){
            $sorts = Role::all();
    		$sort = 1;
    		//自动排序
    		foreach($sorts as $key=>$val){
    			if(!empty($val['orders'])){
    				$sort = $val["orders"]+1;
    			}
    		}
    		 //接收提交过来的信息
    		$data = $request->param();
    		$info['name'] = $data['name'];
    		$info['info'] = $data ['info'];
    		//删除数组中指定元素
    		unset($data['name']);
    		unset($data['info']);
    		//将键转换为数字键
    		$array = array_values($data);
    		//数组转字符串
    		$info['jurisdiction'] = implode(',',$array);
    		//创建时间
    		$info['create_time'] = time();
    		//修改时间
    		$info['update_time'] = time();
    		//排序
    		$info['orders'] = $sort;
    		//判断角色名是否为空
    		if(empty($info['name'])){
    			$this -> error("角色名不能为空!");
    			return false;
    		}else{
    		    //判断角色名是否存在
    			if(Role::get(['name'=>$info['name']])){
    			$this -> error("角色名已存在!");
    			return false;	
    		}else{
    			$res = Role::insert($info);
    			//提示信息
    			if($res){
    			    $this -> logs("角色 [ID: ".$info['name'].'] 添加成功!');
    				$this -> success("添加成功!",'Roles/rolelist');
    			}else{
    				$this -> error("添加失败!");
    			    }
    		    }
    	    }
        }
        return view('add');
    }


    //角色管理列表编辑
    public function edit(Request $request)
    {
        //判断是否为ajax请求
        if(request()->isAjax()){
    	    //接收提交过来的信息
    		$data = $request->param();
		    //获取对应ID的角色信息
		    $roles = Role::get($data['id']);
    		$info['name'] = $data['name'];
    		$info['info'] = $data['info'];
    		$info['id'] = $data['id'];
    		//删除数组中指定元素
    		unset($data['name']);
    		unset($data['info']);
    		unset($data['id']);
    		//将键转换为数字键
    		$array = array_values($data);
    		//数组转字符串
    		$info['jurisdiction'] = implode(',',$array);
    		//修改时间
    		$info['update_time'] = time();
    		//提示信息
    		if(empty($info['name'])){
    			$this -> error("角色名不能为空!");
    			return false;
    		}else{
    		    //判断角色名是否存在
    			if(Role::get(['name'=>$info['name']] && $info['name'] != $roles['name'])){
    			$this -> error("角色名已存在!");
    			return false;	
        		}else{
        			$res = Role::update($info);
        			//提示信息
        			if($res){
        			    $this -> logs("角色 [ID: ".$info['id'].'] 修改成功!');
        				$this -> success("修改成功!",'Roles/rolelist');
        			}else{
        				$this -> error("修改失败!");
        			    }
        		    }
    	        }
            }
        $id = $request->param('id');
		//获取对应ID的角色信息
		$roles = Role::get($id);
		//字符串转数组
 		$array = explode(',',$roles['jurisdiction']);
 		//删除0级1级的url，以防模板渲染错误
 		foreach ($array as $key => $val){
 		    $info = Db::name('rule')->where('id',$val)->field('level')->find();
 		    if($info['level'] != 2){
 		        unset($array[$key]);
 		    }
 		}
 		//转为字符串
 		$roles['jurisdiction'] = implode(',',$array);
		return view('edit',['roles'=>$roles]);
    }
    

    //角色管理列表删除/批量删除
    public function deletes(Request $request)
    {
        //判断是否为ajax请求
        if(request()->isAjax()){
            //接收提交过来的信息
            $id = $request->param('id');
            //转为数组
            $array = explode(',',$id);
            //删除数组中空元素
            $array = array_filter($array);
            if(!in_array(1,$array)){
                //查询当前正在使用的角色
                $admin = Db::name('admin')->where('id',Session::get('Adminuser.id'))->field('roles')->find();
                if(!in_array($admin['roles'],$array)){
                    //删除操作
                    $res =Role::destroy($array);
                    if($res){
                    	$this -> success("删除成功!",'Roles/rolelist');
                    }else{
                    	$this -> error("删除失败!");
                    }
                }else{
                    $this->error("正在使用的角色不可删除!");
                }
            }else{
                $this->error("超级权限不可删除!");
            }
        }
		
    }
    
    //状态变更
    public function setStatus(Request $request)
    {
        if(request()->isAjax()){
            //接收ID
            $id = $request->param('id');
            //判断是否为超级管理员
            if($id !== 1){
                //正在使用的角色不能停用
                if($id !== Session::get('Adminuser.roles')){
                    //查询当前id的状态
                    $info = Db::name('role')->where('id',$id)->field('status')->find();
                    //判断状态
                    if($info['status'] == 1){
                        //执行更新
                        $res = Db::name('role')->where('id',$id)->update(['status'=>0]);
                        if($res){
                            $this->success("已停用!",'Roles/rolelist');
                        }else{
                            $this->error("停用失败!");
                        }
                    }else{
                        //执行更新
                        $res = Db::name('role')->where('id',$id)->update(['status'=>1]);
                        if($res){
                            $this->success("已启用!",'Roles/rolelist');
                        }else{
                            $this->error("启用失败!");
                        }
                    }
                }else{
                    $this->error("正在使用的角色不能停用!");
                }
            }else{
                $this->error("超级权限不能停用!");
            }
        }
    }
    
    //角色搜索
    public function search(Request $request)
    {
        //接收数据
        $keywords = $request->param('keywords');
        $id = Session::get('Adminuser.id');
        if($id = 1){
            //进行模糊查询
    		$role = Role::where('name|info','like','%'.$keywords.'%')->order('create_time','desc')->paginate(25,false,['query'=>request()->param()]);
            foreach($role as $key=>$val){
            	//查询角色下用户
            	$username = Admin::where('roles',$val['id'])->field('name')->select()->toArray();
            	$role[$key]['username'] = $username;
    		 }            
        }else{
            //进行模糊查询
    		$role = Role::where('name|info','like','%'.$keywords.'%')->where('id',$id)->order('create_time','desc')->paginate(25,false,['query'=>request()->param()]);
            foreach($role as $key=>$val){
            	//查询角色下用户
            	$username = Admin::where('roles',$val['id'])->field('name')->select()->toArray();
            	$role[$key]['username'] = $username;
    		 }              
        }

        return view('list',['roles'=>$role]);
    }
}
