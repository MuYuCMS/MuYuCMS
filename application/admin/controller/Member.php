<?php
namespace app\admin\controller;
use app\admin\controller\Base;
use app\admin\model\Member as MemberModel;
use think\Request;
use think\Session;
use think\Db;
class Member extends Base
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
		//读取会员表所有信息
		//$member = MemberModel::all();
		$member = MemberModel::order('update_time','desc')->paginate(10);
		//赋值给模板
		return $this -> view -> fetch('member_list',['member'=>$member]);
    }
    
	//验证用户名是否重复
	public function checkname(Request $request){
		$username = trim($request->post('username'));
		$state = 1;
		$message = '用户名可用';
		if(MemberModel::get(['name' => $username])){
			$state = 0;
			$message = '用户名已存在!';
		}
		return ['state'=>$state,'message'=>$message];
	
	}
	//验证邮箱是否重复
	public function checkemail(Request $request){
		$useremail = trim($request->post('email'));
		$state = 1;
		$message = '邮箱可用';
		if(MemberModel::get(['email' => $useremail])){
			$state = 0;
			$message = '邮箱已存在!';
		}
		return ['state'=>$state,'message'=>$message];
	}
	//验证手机是否重复
	public function checkmobile(Request $request){
		$usermobile = trim($request->post('mobile'));
		$state = 1;
		$message = '手机可用';
		if(MemberModel::get(['phone' => $usermobile])){
			$state = 0;
			$message = '手机已存在!';
		}
		return ['state'=>$state,'message'=>$message];
	}

//用户添加增加表单页面
	public function newadd(){
		//判断当前IP是否允许操作后台
		$ip = $this->ip_info();
		//判断用户是否登录
		$user = $this -> user_info();
		//查询所有数据
		$memberadd = MemberModel::all();
		$this -> view -> assign('memberadd',$memberadd);
		//渲染增加界面
		return $this -> view -> fetch('member_new');
	}
	//执行增加到数据表更新
	public function tonewadd(Request $request){
		//判断当前IP是否允许操作后台
		$ip = $this->ip_info();
		//判断是否登录
		$user = $this -> user_info();
		//接收传递过来的数据
		$data = $request->post();
		//读取前台传递的username字段值
		$newname = trim($request->post('username'));
		$newemail = trim($request->post('email'));
		$data['update_time'] = time();
		$data['create_time'] = time();
		$data['password'] = md5($data['password']);
		//数据库添加操作
		$res = MemberModel::insert($data);
		//取得新增数据id
		$uid = Db::name('member')->getLastInsID();
		//判断是否成功并提示
		if($res){
		    Db::name('member_data')->insert(['uid'=>$uid]);
			$this -> success("添加成功!",'Member/index');
		}else{
			$this -> error("添加失败!");
			}
	}
	
	//渲染已删除用户列表
	public function memberdel(Request $request){
		//判断当前IP是否允许操作后台
		$ip = $this->ip_info();
		//判断用户是否登录
		$user = $this -> user_info();
		//查询表所有已删除的用户数据
		$deluser = MemberModel::onlyTrashed()->order('delete_time','desc')->paginate(10);
		// var_dump($deluser);
		//赋值给模板
		$this -> view -> assign('deluser',$deluser);
		return $this -> view -> fetch('member_del');
	}
	//批量删除用户
	public function setdel(Request $request){
		//判断当前IP是否允许操作后台
		$ip = $this->ip_info();
		//判断用户是否登录
		$user = $this -> user_info();
		//彻底删除对应信息
		$delid = $request->param('id');
		//删除对应ID信息
		$res=Db::name('member')->delete($delid);
		if($res){
		    Db::name('member_data')->where('uid','in',$delid)
	            ->useSoftDelete('delete_time',time())
                ->delete();
			$this -> success("删除成功!",'Member/index');
		}else{
			$this -> error("删除失败!");
		}
	}
	//还原已删除用户
	public function huanyuan(Request $request){
		//判断当前IP是否允许操作后台
		$ip = $this->ip_info();
		//判断用户是否登录
		$user = $this -> user_info();
		//获取前台传递的id
		$delid = $request->param('id');
		//恢复相应id信息
		$res=Db::name('member') ->where('id',$delid) ->setField('delete_time', NULL);
		if($res){
		    Db::name('member_data') ->where('uid',$id) ->setField('delete_time', NULL);
			$this -> success("还原成功!",'Member/memberdel');
		}else{
			$this -> error("还原失败!");
		}
	}
	
	//批量还原
	public function huanyuanall(Request $request){
		//判断当前IP是否允许操作后台
		$ip = $this->ip_info();
		//判断是否登录
		$user = $this -> user_info();
		//获取传递过来的值并还原
		$id = $request -> post('delid');
		$res =Db::name('member') ->where('id','in',$id) ->setField('delete_time', NULL);
		if($res){
		    Db::name('member_data') ->where('uid','in',$id) ->setField('delete_time', NULL);
			$this -> success("还原成功!",'Member/memberdel');
		}else{
			$this -> error("还原失败!");
		}
	}
	
    //会员列表编辑操作
    public function edit(Request $request)
    {
        //判断当前IP是否允许操作后台
        $ip = $this->ip_info();
		//判断是否登录
		$user = $this -> user_info();
		//获取对应ID
		$id = $request ->param('id');
		//读取会员表信息
		$member = MemberModel::withTrashed()->get($id);
		//赋值给模板
		$this -> view -> assign('member',$member);
		
		return $this -> view -> fetch('member_add');
		
		
    }


	//会员员状态变更
	public function setStatus(Request $request){
		//判断当前IP是否允许操作后台
		$ip = $this->ip_info();
		//判断是否登录
		$user = $this -> user_info();
		//获取前台传递id
		$member_id = $request->param('id');
		//根据id查询数据
		$result = MemberModel::get($member_id);
		//查询原生数据进行判断
		if($result->getData('status') == 1){
			MemberModel::update(['status'=>0],['id'=>$member_id]);
		}else{
			MemberModel::update(['status'=>1],['id'=>$member_id]);
		}
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
		if(!empty($data['password'])){
		$data['password'] = md5($data['password']);
		}else{
		    $data=array_filter($data);
		}
		//更新数据表
		$res = Db::name("member")->update($data);
		//提示信息
		// var_dump(controller($res)->error());
		if($res){
			$this -> success("修改成功!",'Member/index');
		}else{
			$this -> error("修改失败!");
		}
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
        //获取传递过来的值并删除
		$res =MemberModel::destroy($id);
		if($res){
		    Db::name('member_data')->where('uid',$id)
	            ->useSoftDelete('delete_time',time())
                ->delete();
			$this -> success("删除成功!",'Member/index');
		}else{
			$this -> error("删除失败!");
		}
    }
	/**
		 * 用户搜索列表批量删除指定资源
		 *
		 * @param  int  $id
		 * @return \think\Response
		 */
		public function deleteall(Request $request){
			//判断当前IP是否允许操作后台
			$ip = $this->ip_info();
			//判断是否登录
			$user = $this -> user_info();
			//获取传递过来的值并删除
			$res =MemberModel::destroy($request -> post('delid'));
			if($res){
			    Db::name('member_data')->where('uid','in',$request -> post('delid'))
	            ->useSoftDelete('delete_time',time())
                ->delete();
				$this -> success("删除成功!",'Member/index');
			}else{
				$this -> error("删除失败!");
			}
		}
		//删除的会员列表批量删除
		public function deletealls(Request $request){
			//判断当前IP是否允许操作后台
			$ip = $this->ip_info();
			//判断是否登录
			$user = $this -> user_info();
			$res=Db::name('member')->where('id','in',$request -> post('delid'))
                ->delete();
			if($res){
			    Db::name('member_data')->where('uid','in',$request -> post('delid'))
                ->delete();
				$this -> success("删除成功!",'Member/index');
			}else{
				$this -> error("删除失败!");
			}
		}
		
	/*
	*搜索操作
	*/
	    public function soulist(Request $request){
			//判断当前IP是否允许操作后台
			$ip = $this->ip_info();
			//判断是否登录
			$user = $this -> user_info();
			//搜索放这里
			$res = $request -> get('so');
			$time1 = strtotime($request->get('t1'));
			$time2 = strtotime($request->get('t2'));
			//对前台传过来的数据进行查询
	    	if($time1!='' && $time2 != ''){
	    	    $member =MemberModel::alias('a')->join('member_data b','a.id = b.uid')->fieldRaw('a.*,b.*')->where([[['create_time', 'between time', [$time1, $time2]]]],['name|account|email|phone','like','%'.$res.'%'])->select();
	    	}else if($res != ''){
	    	    $member =MemberModel::alias('a')->join('member_data b','a.id = b.uid')->fieldRaw('a.*,b.*')->where([['name|account|email|phone', 'like', '%'.$res. '%']])->select();
	    	}
			$this ->assign('member',$member);
			return view('member_lists');
		}
}
