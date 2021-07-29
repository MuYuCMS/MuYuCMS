<?php
namespace app\admin\controller;
use app\admin\controller\Base;
use think\Request;
use app\admin\model\Admin as AdminModel;
use app\admin\model\Role;
use think\facade\Session;
use think\Db;
use app\admin\validate\Admin as AdminValidate;

class Admin extends Base
{
    //管理员列表输出
    public function adminList()
    {
		//判断是否初始管理 并输出对应列表
		$userid = Session::get('Adminuser.id');
		if($userid == 1){
            //读取管理员表信息
    		$admin = AdminModel::order('create_time','desc')->paginate(25);
    		foreach($admin as $key=>$val){
    			$roles = Role::where(['id'=>$val['roles']])->field('name')->select()->toArray();
    			$admin[$key]['rolesname'] = $roles;
    		}
		}else{
			$admin = AdminModel::all(['id' => $userid]);
			foreach($admin as $key=>$val){
				$roles = Role::where(['id'=>$val['roles']])->field('name')->select()->toArray();
				$admin[$key]['rolesname'] = $roles;
			}
		}
		return view('list',['admin'=>$admin]);
    }

	
	//管理员添加增加表单页面
	public function add(Request $request)
	{
	    if(request()->isAjax()){
	        //接收传递过来的数据
    		$data = $request->post(); 
    		//进行数据验证
    		$validate = new AdminValidate;
    		if (!$validate->check($data)) {
                $this->error($validate->getError());
            }
            //哈希加密
            $data['password'] = password_hash($data['password'],PASSWORD_DEFAULT);
            $data['create_time'] = time();
            $data['update_time'] = time();
            //实例化模型
            $admin =  new AdminModel;
            // 过滤post数组中的非数据表字段数据
            $res = $admin->allowField(true)->save($data);
            // 获取自增ID
            $uid = $admin->id;
            if($res){
                //建立新增管理员附属表
                Db::name('admin_data')->insert(['uid'=>$uid]);
                $this -> success("添加成功!",'Admin/adminList');
            }else{
                $this -> error("添加失败!");
            }
	    }
		//查询所有数据
		$rolename = Role::all();
		return view('add',['rolename'=>$rolename]);
	}
	
    //管理员列表编辑操作
    public function edit(Request $request)
    {
		//获取对应ID
		$id = $request->param('id');
		//读取管理员表信息
		$admin = AdminModel::withTrashed()->get($id);
		if(request()->isAjax()){
		    //接收传递过来的数据
		    $data = $request->param();
    		//进行数据格式验证
    		$validate = new AdminValidate;
    		if (!$validate->check($data)) {
                $this->error($validate->getError());
            }
            //密码进行了修改则重新加密
            if($data['password'] !== $admin['password']){
                //哈希加密
    		    $data['password'] = password_hash($data['password'],PASSWORD_DEFAULT);
            }
    		$data['update_time'] = time();
    		//实例化模型
    		$admin =  new AdminModel;
    		// 过滤post数组中的非数据表字段数据
            $res = $admin->allowField(true)->save($data,['id'=>$data['id']]);
            if($res){
                //更新附属表
                Db::name('admin_data')->where('uid',$data['id'])->strict(false)->update($data);
    			$this -> success("修改成功!",'Admin/adminList');
            }else{
                $this -> error("修改失败!");
            }
		}
		$rolename = Role::all();
		return view('edit',['admin'=>$admin,'rolename'=>$rolename]);
    }

    //管理员状态变更
	public function setStatus(Request $request)
	{
        if(request()->isAjax()){
            //接收ID
            $id = $request->param('id');
            //判断是否为超级管理员
            if($id !== 1){
                //自己不能停用
                if($id !== Session::get('Adminuser.id')){
                    //查询当前id的状态
                    $info = Db::name('admin')->where('id',$id)->field('status')->find();
                    //判断状态
                    if($info['status'] == 1){
                        //执行更新
                        $res = Db::name('admin')->where('id',$id)->update(['status'=>0]);
                        if($res){
                            $this->success("已停用!",'Admin/adminList');
                        }else{
                            $this->error("停用失败!");
                        }
                    }else{
                        //执行更新
                        $res = Db::name('admin')->where('id',$id)->update(['status'=>1]);
                        if($res){
                            $this->success("已启用!",'Admin/adminList');
                        }else{
                            $this->error("启用失败!");
                        }
                    }
                }else{
                    $this->error("自己不能停用!");
                }
            }else{
                $this->error("超级管理员不能停用!");
            }
        }
	}
	
	 //管理员列表批量删除/删除操作
    public function deletes(Request $request)
    {
         //判断是否为ajax请求
        if(request()->isAjax()){
            //接收提交过来的信息
            $id = $request->param('adid');
            //转为数组
            $array = explode(',',$id);
            //删除数组中空元素
            $array = array_filter($array);
            if(!in_array(1,$array)){
                if(!in_array(Session::get('Adminuser.id'),$array)){
                    //获取传递过来的值并删除
		            $res =AdminModel::destroy($array);
                    if($res){
                        //删除管理员附属表
		                Db::name('admin_data')->where('uid','in',$array)->useSoftDelete('delete_time',time())->delete();
                    	$this -> success("删除成功!",'Admin/adminList');
                    }else{
                    	$this -> error("删除失败!");
                    }
                }else{
                    $this -> error("自己不可删除!");
                }
            }else{
                $this->error("超级管理员不可删除!");
            }
        }
    }
    
    //搜索列表
    public function search(Request $request)
    {
        //接收数据
        $keywords = $request->param('keywords');
        //判断是否初始管理 并输出对应列表
    	$userid = Session::get('Adminuser.id');
    	if($userid == 1){
            //超级管理员能搜索全部
    		$admin = AdminModel::where('name|intro|phone|email','like','%'.$keywords.'%')->order('create_time','desc')->paginate(10,false,['query'=>request()->param()]);
    		foreach($admin as $key=>$val){
    			$roles = Role::where(['id'=>$val['roles']])->field('name')->select()->toArray();
    			$admin[$key]['rolesname'] = $roles;
    		}
    	}else{
    	    //普通管理员只能搜索自己
    		$admin = AdminModel::where('name|intro|phone|email','like','%'.$keywords.'%')->where('id',$userid)->order('create_time','desc')->paginate(10,false,['query'=>request()->param()]);
    		foreach($admin as $key=>$val){
    			$roles = Role::where(['id'=>$val['roles']])->field('name')->select()->toArray();
    			$admin[$key]['rolesname'] = $roles;
    		}
    	}
        return view('list',['admin'=>$admin]);
    }
    
    //删除的管理员列表
	public function delList(Request $request)
	{
		//查询表所有已删除的管理员数据
		$admin = AdminModel::onlyTrashed()->paginate(10,false,['query'=>request()->param()]);
		//赋值给模板
		return view('recycle',['admin'=>$admin]);
	}
	
	//批量还原/批量还原
	public function restore(Request $request)
	{
	    if(request()->isAjax()){
    		//获取传递过来的值并还原
    		$id = $request -> post('id');
    		$res =Db::name('admin') ->where('id','in',$id) ->setField('delete_time', NULL);
    		if($res){
    			$this -> success("还原成功!",'Admin/delList');
    		}else{
    			$this -> error("还原失败!");
    		}	        
	    }

	}
	
	//删除的管理员列表批量删除/删除
	public function recycle(Request $request)
	{
	    if(request()->isAjax())
	    {
	        //彻底删除对应信息
    		$id = $request->post('id');
    		//删除对应ID信息
    		$res = Db::name('admin')->delete($id);
    		if($res){
    		    //删除管理员附属表对应信息
    		    Db::name('admin_data')->where('uid','in',$id)->delete();
    			$this -> success("删除成功!",'Admin/delList');
    		}else{
    			$this -> error("删除失败!");
    		}
	    }
	}
	
}
