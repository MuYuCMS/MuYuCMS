<?php
namespace app\admin\controller;
use app\admin\controller\Base;
use think\Request;
use app\admin\model\Admin;
use app\admin\model\Article;
use think\facade\Session;
use app\admin\model\Member;
use app\admin\model\Feedback;
use think\Db;
use think\facade\Env;

class Index extends Base
{
    public function index()
    {
		
		//判断当前IP是否允许操作后台
    	$ip = $this->ip_info();
    	//判断是否登录
    	$user = $this -> user_info();
		$jurisdiction = Db::name('role')->where('id',Session::get('Adminuser.roles'))->field('jurisdiction')->find();
		$jurisdiction = explode(',',$jurisdiction['jurisdiction']);
		
		$menu = Db::name('menu')->where(['pid'=>0,'id'=>$jurisdiction,'status'=>1])->order('orders','asc')->select()->toArray();
		foreach($menu as $key=>$val){
			$two = Db::name('menu')->where(['pid'=>$val['id'],'id'=>$jurisdiction,'status'=>1])->order('orders','asc')->select();
			$menu[$key]['secontit'] = $two;
		}
		
		$nameid = Session::get('Adminuser.roles');
		$roname = Db::name('role')->where('id',$nameid)->find();
		$this->view->assign(['roname'=>$roname,'menu'=>$menu]);
        return $this -> view -> fetch();
    }
	public function welcome(Request $request)
	{
		
		//判断当前IP是否允许操作后台
    	$ip = $this->ip_info();
    	//判断是否登录
    	$user = $this -> user_info();
        //读取管理员表信息
		$nameid = Session::get('Adminuser.roles');
		$roname = Db::name('role')->where('id',$nameid)->find();
		$admin = Admin::all();
		//文章表的查询
		$article = Article::all();
		//查询会员所有信息
		$member = Member::all();
		//查询留言所有信息
		$feedback = Feedback::all();
		//查询今天发布的文章信息
		$articles =Db::name('article')->whereTime('create_time','today')->select();
		//查询今天注册的会员
		$members =Db::name('member')->whereTime('create_time','today')->select();
		//查询今天留言的信息
		$feedbacks =Db::name('feedback')->whereTime('create_time','today')->select();
		//查询系统信息
		$system = Db::name('system')->where('id',1)->find();
		$wzjs = count($articles);
		$wzjs1 = count($members);
		$wzjs2 = count($feedbacks);

		//赋值给模板
	    $this -> view -> assign(['admin'=>$admin,'wzjs'=>$wzjs,'wzjs1'=>$wzjs1,'wzjs2'=>$wzjs2,'article'=>$article,'roname'=>$roname,'member'=>$member,'feedback'=>$feedback,'system'=>$system]);
	    return $this -> view -> fetch();
	}
	
	
	
	/**
	* 清除缓存
	*/
	public function clear()
	{
	    //判断当前IP是否允许操作后台
    	$ip = $this->ip_info();
    	//判断是否登录
    	$user = $this -> user_info();
    	$CACHE_PATH = Env::get('runtime_path') . 'cache/';
    	$TEMP_PATH = Env::get('runtime_path'). 'temp/';
        if (delete_dir_file($CACHE_PATH) && delete_dir_file($TEMP_PATH)) {
            return json(["code"=>1,"msg"=>"清除缓存成功!"]);
        } else {
            return json(["code"=>0,"msg"=>"清除缓存失败!"]);
        }
    }

}
