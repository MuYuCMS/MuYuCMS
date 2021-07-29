<?php

namespace app\admin\controller;
use app\admin\controller\Base;
use app\admin\model\Feedback as FeedbackModel;
use app\admin\model\Member as MemberModel;
use think\Request;
use think\Session;

class Feedback extends Base
{
    //留言列表
    public function index()
    {
		//获取留言列表数据
		$feedback = FeedbackModel::order('create_time','desc')->paginate(25);
		//获取uid对应的会员信息
		foreach($feedback as $key=>$val){
			 $name = MemberModel::where('id',$val['uid'])->field('id,name')->select()->toArray();
			$feedback[$key]['memname'] = $name;
		}
		//赋值给模板
		//dump($feedback);
		return view('list',['feedback'=>$feedback]);
    }
	

	 //留言编辑
	public function edit($id,$ids)
	{
		//根据id获取留言信息
		$feedback = FeedbackModel::get($id);
		$username = "";
		if($ids != 0){
			$username = MemberModel::get($ids);//根据会员id获取会员信息
		}
		//状态为0的改为1
		FeedbackModel::update(['status'=>1],['id'=>$id]);
		//赋值给模板
		return $this -> view -> fetch('edit',['feedback'=>$feedback,'user'=>$username]);
	}
	

    //留言删除/批量删除
    public function deletes(Request $request)
    {
		//获取传递过来的id
		$id = $request -> param('id');
        //获取传递过来的值并删除
		$res =FeedbackModel::destroy($id);
		if($res){
		    $this -> logs("留言 [ID: ".$id.'] 删除成功!');
			$this -> success("删除成功!",'Feedback/index');
		}else{
			$this -> error("删除失败!");
		}
    }
	

	
	//留言一键已读
	public function states(Request $request){
		//判断当前IP是否允许操作后台
		
		//获取传递过来的值并删除
		$id = $request -> param('id');
		$res=FeedbackModel::where('id','in',$id)->update(['status'=>1]);
		
		if($res){
		    $this -> logs("留言 [ID: ".$id.'] 已读成功!');
			$this -> success("已读成功!",'Feedback/index');
		}else{
			$this -> error("已读失败!");
		}
	}
	
	//搜索操作
    public function search(Request $request){
		//搜索放这里
		$res = $request -> param('keywords');
		if($res != ''){
			$feedback =FeedbackModel::alias('a')->join('member b','a.uid=b.id')->fieldRaw('a.*,b.name')->where([['a.content|b.name', 'like', "%{$res}%"]])->paginate(25,false,['query'=>request()->param()]);
		}
		//获取uid对应的会员信息
		foreach($feedback as $key=>$val){
			 $name = MemberModel::where('id',$val['uid'])->field('id,name')->select()->toArray();
			$feedback[$key]['memname'] = $name;
		}
		//赋值给模板
		$this ->assign('feedback',$feedback);
		return view('list');
	}
	
}
