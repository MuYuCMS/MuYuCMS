<?php

namespace app\admin\controller;
use app\admin\controller\Base;
use app\admin\model\Feedback as FeedbackModel;
use app\admin\model\Member as MemberModel;
use think\Request;
use think\Session;

class Feedback extends Base
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
		//$feedback = FeedbackModel::all();
		$feedback = FeedbackModel::order('create_time','desc')->paginate(10);
		foreach($feedback as $key=>$val){
			 $name = MemberModel::where('id',$val['uid'])->field('id,name')->select()->toArray();
			$feedback[$key]['memname'] = $name;
		}
		//赋值给模板
		return view('feedback_list',['feedback'=>$feedback]);
    }
	
	/* 显示编辑资源表单页.
	 *
	 * @param  int  $id
	 * @return \think\Response
	 */
	public function edit($id,$ids)
	{
	    //判断当前IP是否允许操作后台
	    $ip = $this->ip_info();
		//判断是否登录
		$user = $this -> user_info();
		//获取对应ID
		//读取留言表信息
		$feedback = FeedbackModel::get($id);
		$username = "";
		if($ids != 0){
			$username = MemberModel::get($ids);
		}
		FeedbackModel::update(['status'=>1],['id'=>$id]);
		
		//赋值给模板
		
		return $this -> view -> fetch('feedback_add',['feedback'=>$feedback,'user'=>$username]);
		
		
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
		$res =FeedbackModel::destroy($request -> post('ids'));
		if($res){
			$this -> success("删除成功!",'Feedback/index');
		}else{
			$this -> error("删除失败!");
		}
    }
	
	//留言批量删除
	public function deleteall(Request $request){
		//判断当前IP是否允许操作后台
		$ip = $this->ip_info();
		//判断是否登录
		$user = $this -> user_info();
		//获取传递过来的值并删除
		$res =FeedbackModel::destroy($request -> post('delid'));
		if($res){
			$this -> success("删除成功!",'Feedback/index');
		}else{
			$this -> error("删除失败!");
		}
	}
	
	//留言一键已读
	public function states(Request $request){
		//判断当前IP是否允许操作后台
		$ip = $this->ip_info();
		//判断是否登录
		$user = $this -> user_info();
		//获取传递过来的值并删除
		$id = $request -> post('delid');
		$res=FeedbackModel::where('id','in',$id)->update(['status'=>1]);
		
		if($res){
			$this -> success("已读成功!",'Feedback/index');
		}else{
			$this -> error("已读失败!");
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
			$feedback = "";
			if($time1!='' && $time2 != ''){
				/*$feedback = FeedbackModel::withSearch(['create_time'], ['create_time'	=>	[$time1,$time2]])->select();*/
				$feedback =FeedbackModel::alias('a')->join('member b','a.uid=b.id')->fieldRaw('a.*,b.name')->where([[['create_time', 'between time', [$time1, $time2]]],['a.content|b.name', 'like', "%{$res}%"]])->select();
			}else if($res != ''){
    			/*$feedback = FeedbackModel::withSearch(['content','feedback_id'], ['content'	=>	$res])->select();*/
    			$feedback =FeedbackModel::alias('a')->join('member b','a.uid=b.id')->fieldRaw('a.*,b.name')->where([['a.content|b.name', 'like', "%{$res}%"]])->select();
			}
			$this ->assign('feedback',$feedback);
			return view('feedback_lists');
		}
	
}
