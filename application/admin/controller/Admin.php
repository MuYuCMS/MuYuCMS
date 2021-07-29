<?php
namespace app\admin\controller;
use app\admin\controller\Base;
use think\Request;
use app\admin\model\Admin as AdminModel;
use app\admin\model\Role;
use think\facade\Session;
use think\Db;

class Admin extends Base
{
    //管理员列表输出
    public function index()
    {
		//判断当前IP是否允许操作后台
		$ip = $this->ip_info();
		//判断是否登录
		$user = $this -> user_info();
		//判断是否初始管理 并输出对应列表
		$userid = Session::get('Adminuser.id');
		if($userid == 1){
        //读取管理员表信息
		$admin = AdminModel::order('create_time','desc')->paginate(10);
		foreach($admin as $key=>$val){
			$roles = Role::where(['id'=>$val['roles']])->field('name')->select();
			$admin[$key]['rolesname'] = $roles;
		}
		}else{
			$admin = AdminModel::all(['id' => $userid]);
			foreach($admin as $key=>$val){
				$roles = Role::where(['id'=>$val['roles']])->field('name')->select();
				$admin[$key]['rolesname'] = $roles;
			}
		}
		//赋值给模板
	    $this -> view -> assign(['admin'=>$admin]);
		return $this -> view -> fetch('admin_list');
    }

	
	//管理员添加增加表单页面
	public function newadd(){
		//判断当前IP是否允许操作后台
		$ip = $this->ip_info();
		//判断用户是否登录
		$user = $this -> user_info();
		//查询所有数据
		$rolename = Role::all();
		$adminadd = AdminModel::all();
		$this -> view -> assign(['adminadd'=>$adminadd,'rolename'=>$rolename]);
		//渲染增加界面
		return $this -> view -> fetch('admin_new');
	}
	//执行增加到数据表更新
	public function tonewadd(Request $request){
		//判断当前IP是否允许操作后台
		$ip = $this->ip_info();
		//判断是否登录
		$user = $this -> user_info();
		//接收传递过来的数据
		$data = $request->post(); 
		$data['password'] = md5($data['password']);
		$data['create_time'] = time();
		$data['update_time'] = 0;
		$data['count'] = 0;
		//数据库添加操作
		$res = AdminModel::insert($data);
		//取得新增数据id
		$uid = Db::name('admin')->getLastInsID();
		//判断是否成功并提示
		if($res){
		    //建立新增管理员附属表
		    Db::name('admin_data')->insert(['uid'=>$uid]);
			$this -> success("添加成功!",'Admin/index');
		}else{
			$this -> error("添加失败!");
		}
	}
	
	//渲染已删除管理员列表
	public function admindel(Request $request){
		//判断当前IP是否允许操作后台
		$ip = $this->ip_info();
		//判断用户是否登录
		$user = $this -> user_info();
		//查询表所有已删除的管理员数据
		$deluser = AdminModel::onlyTrashed()->paginate(10);
		//赋值给模板
		$this -> view -> assign('deluser',$deluser);
		return $this -> view -> fetch('admin_del');
	}
	
	//批量还原
	public function huanyuanall(Request $request){
		//判断当前IP是否允许操作后台
		$ip = $this->ip_info();
		//判断是否登录
		$user = $this -> user_info();
		//获取传递过来的值并还原
		$id = $request -> post('delid');
		$res =Db::name('admin') ->where('id','in',$id) ->setField('delete_time', NULL);
		if($res){
			$this -> success("还原成功!",'Admin/admindel');
		}else{
			$this -> error("还原失败!");
		}
	}


    //管理员列表编辑操作
    public function edit(Request $request)
    {
		//判断当前IP是否允许操作后台
		$ip = $this->ip_info();
		//判断是否登录
		$user = $this -> user_info();
		//获取对应ID
		$id = $request->param('id');
		//读取管理员表信息
		$admin = AdminModel::withTrashed()->get($id);
		$rolename = Role::all();
		//赋值给模板
		$this -> view -> assign(['admin'=>$admin,'rolename'=>$rolename]);
        //
		return $this -> view -> fetch('admin_add');
    }
	//管理员列表编辑提交表单操作
    public function update(Request $request)
    {
		//判断当前IP是否允许操作后台
		$ip = $this->ip_info();
		//判断是否登录
		$user = $this -> user_info();
		//获取所有数据
		$data = array_filter($request->post());
		//查询数据表
		$res = AdminModel::update($data);
		//提示信息
		if($res){
			$this -> success("保存成功!",'Admin/index');
		}else{
			$this -> error("保存失败!");
		}
    }
	//验证用户名是否重复
	public function checkname(Request $request){
		$username = trim($request->post('name'));
		$status = 1;
		$message = '用户名可用';
		if(AdminModel::get(['name' => $username])){
			$status = 0;
			$message = '用户名已存在!';
		}
		return ['status'=>$status,'message'=>$message];

	}
	//验证邮箱是否重复
	public function checkemail(Request $request){
		$useremail = trim($request->post('email'));
		$status = 1;
		$message = '邮箱可用';
		if(AdminModel::get(['email' => $useremail])){
			$status = 0;
			$message = '邮箱已存在!';
		}
		return ['status'=>$status,'message'=>$message];
	}
	
	//管理员状态变更
	public function setStatus(Request $request){
		//判断当前IP是否允许操作后台
		$ip = $this->ip_info();
		//判断是否登录
		$user = $this -> user_info();
		//获取前台传递id
		$admin_id = $request->param('id');
		//根据id查询数据
		$result = AdminModel::get($admin_id);
		//查询原生数据进行判断
		if($result->getData('status') == 1){
			AdminModel::update(['status'=>0],['id'=>$admin_id]);
		}else{
			AdminModel::update(['status'=>1],['id'=>$admin_id]);
		}
	}
    

    //管理员列表删除操作
    public function delete(Request $request)
    {
		//判断当前IP是否允许操作后台
		$ip = $this->ip_info();
		//判断是否登录
		$user = $this -> user_info();
		//获取传递过来的id
		$id = trim($request -> post('ids'));
		if($id == 1){
			$this -> error("初始管理不可删除!");
			return false;
		}
		if($id == Session::get('Adminuser.id')){
		    $this -> error("自己不可删除!");
			return false;
		}
        //获取传递过来的值并删除
		$res =AdminModel::destroy($id);
		if($res){
		    //删除管理员附属表
		    Db::name('admin_data')->where('uid',$id)->useSoftDelete('delete_time',time())->delete();
			$this -> success("删除成功!",'Admin/index');
		}else{
			$this -> error("删除失败!");
		}
    }
	
	//管理员批量删除
	public function deleteall(Request $request){
		//判断当前IP是否允许操作后台
		$ip = $this->ip_info();
		//判断是否登录
		$user = $this -> user_info();
		//接收传递过来的id数据
		$id = $request -> post('delid');
		//获取传递过来的值并删除
		$res =AdminModel::destroy($id);
		if($res){
		    //同步批量删除对应附属表
			Db::name('admin_data')->where('uid','in',$id)->useSoftDelete('delete_time',time())->delete();
			$this -> success("删除成功!",'Admin/index');
		}else{
			$this -> error("删除失败!");
		}
	}
	
	//删除的管理员列表批量删除
	public function deletealls(Request $request){
		//判断当前IP是否允许操作后台
		$ip = $this->ip_info();
		//判断是否登录
		$user = $this -> user_info();
		//彻底删除对应信息
		$id = $request->post('delid');
		//删除对应ID信息
		$res = Db::name('admin')->delete($id);
		if($res){
		    //删除管理员附属表对应信息
		    Db::name('admin_data')->where('uid','in',$id)->delete();
			$this -> success("删除成功!",'Admin/admindel');
		}else{
			$this -> error("删除失败!");
		}
	}
	
	
	//彻底删除管理员
	public function setdel(Request $request){
		//判断当前IP是否允许操作后台
		$ip = $this->ip_info();
		//判断用户是否登录
		$user = $this -> user_info();
		//彻底删除对应信息
		$delid = $request->param('id');
		//删除对应ID信息
		$res = Db::name('admin')->delete($delid);
		if($res){
		    //同步删除管理员附属表
		    Db::name('admin_data')->where('uid',$delid)->delete();
		    $this->success('删除成功!');
		}else{
		    $this->error('删除失败!');
		}
	}
	//还原已删除管理员
	public function huanyuan(Request $request){
		//判断当前IP是否允许操作后台
		$ip = $this->ip_info();
		//判断用户是否登录
		$user = $this -> user_info();
		//获取前台传递的id
		$delid = $request->param('id');
		//恢复相应id信息
		$res = Db::name('admin') ->where('id',$delid) ->setField('delete_time', NULL);
		if($res){
		    $this->success('还原成功!');
		}else{
		    $this->error('还原失败!');
		}
	}
}
