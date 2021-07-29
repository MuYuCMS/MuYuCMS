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
	//日志列表
	public function index()
	{
		//读取管理员表所有信息
		$log = LogModel::name('log')->order('log_time','desc')->paginate(25);
		foreach($log as $key=>$val){
			 $name = Admin::where('id',$val['user_id'])->field('id,name')->order('update_time','desc')->select()->toArray();
			$log[$key]['name'] = $name;
		}
		//赋值给模板
		return $this -> view -> fetch('index',['log'=>$log]);
	}
	//日志查看
	public function edit($id,$ids)
	{

		//读取日志表信息
		$log = Db::name('log')->find($id);
		$adminname = [];
		if($ids != 0){
			$adminname = Admin::get($ids);
		}
		if($log['user_id'] === 0){
		    $adminname['name'] = $log["user"];
		    $adminname['email'] = "未知";
		    $adminname['create_time'] = $log["log_time"];
		}
		//LogModel::update(['state'=>1],['id'=>$id]);
		//赋值给模板
		return $this -> view -> fetch('edit',['log'=>$log,'user'=>$adminname]);
	}
	
	
	//日志搜索
	public function search(Request $request){
	    $keyword = $request->param('keywords');
	    $log = Db::name('log')->where('id|user|user_id|log_ip|content','like','%'.$keyword.'%')->order('log_time','desc')->paginate(25,false,['query'=>request()->param()]);
	    $this->assign('log',$log);
	    return view('index');
	}
}