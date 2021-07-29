<?php

namespace app\admin\controller;
use app\admin\controller\Base;
use think\Request;
use app\admin\model\Log as LogModel;
use app\admin\model\Admin;
use think\Session;
use think\Db;

class Log extends Base
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
		//读取管理员表所有信息
		$log = LogModel::name('log')->order('log_time','desc')->paginate(25);
		foreach($log as $key=>$val){
			 $name = Admin::where('id',$val['user_id'])->field('id,name')->order('update_time','desc')->select()->toArray();
			$log[$key]['name'] = $name;
		}
		//赋值给模板
		return $this -> view -> fetch('system_log',['log'=>$log]);
	}
	//日志查看
	public function edit($id,$ids)
	{
		//判断当前IP是否允许操作后台
		$ip = $this->ip_info();
		//判断是否登录
		$user = $this -> user_info();
		//读取日志表信息
		$log = Db::name('log')->get($id);
		$adminname = "";
		if($ids != 0){
			$adminname = Admin::get($ids);
		}
		//LogModel::update(['state'=>1],['id'=>$id]);
		
		//赋值给模板
		
		return $this -> view -> fetch('system_show',['log'=>$log,'user'=>$adminname]);
	}
}