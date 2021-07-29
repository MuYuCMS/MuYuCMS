<?php

namespace app\admin\controller;
use app\admin\controller\Base;
use think\Request;
use app\admin\model\Comment as CommentModel;
use app\admin\model\Member as MemberModel;
use think\Db;

class Comment extends Base
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
		//查询评论的所有信息并按时间排序
		$comment = CommentModel::alias('a')->join('comment_data b','a.id = b.cid')->leftjoin('member c','a.uid = c.id')->leftjoin('article d','a.aid = d.id')->fieldRaw('a.id,a.plname,a.content,a.create_time,a.status,b.*,c.name,d.title')->order('a.create_time','desc')->paginate(10);
		/*foreach($comment as $key=>$val){
			$name = Db::name('member')->where('id',$val['uid'])->field('id,name')->order('create_time','desc')->select()->toArray();
			$comment[$key]['name'] = $name;
		}*/
		//赋值给模板
		return $this -> view -> fetch('comment_list',['comment'=>$comment]);
    }
	//评论状态变更
	public function setStatus(Request $request){
		//判断当前IP是否允许操作后台
		$ip = $this->ip_info();
		//判断是否登录
		$user = $this -> user_info();
		//获取前台传递id
		$comment_id = $request->param('id');
		//根据id查询数据
		$result = CommentModel::get($comment_id);
		//查询原生数据进行判断
		if($result->getData('status') == 1){
			CommentModel::update(['status'=>2],['id'=>$comment_id]);
		}else{
			CommentModel::update(['status'=>1],['id'=>$comment_id]);
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
        //获取传递过来的值并删除
		$res =CommentModel::destroy($request -> post('ids'));
		if($res){
		    //同步删除data表
		    Db::name('comment_data')
		    ->where('cid',$request -> post('ids'))
		    ->delete();
			$this -> success("删除成功!",'Comment/index');
		}else{
			$this -> error("删除失败!");
		}
    }
	//评论批量删除
	public function deleteall(Request $request){
		//判断当前IP是否允许操作后台
		$ip = $this->ip_info();
		//判断是否登录
		$user = $this -> user_info();
		//获取传递过来的值并删除
		$res =CommentModel::destroy($request -> post('delid'));
		if($res){
		    //同步删除data表
		    Db::name('comment_data')
		    ->where('cid','in',$request -> post('ids'))
		    ->delete();
			$this -> success("删除成功!",'Comment/index');
		}else{
			$this -> error("删除失败!");
		}
	}
	
	//评论批量审核
	public function audit(Request $request){
		//判断当前IP是否允许操作后台
		$ip = $this->ip_info();
		//判断是否登录
		$user = $this -> user_info();
		$id = $request -> post('id');
		$res=CommentModel::where('id','in',$id)->update(['status'=>1]);

		if($res){
			$this -> success("审核通过!",'Comment/index');
		}else if(CommentModel::where('status'==1))
		{
			$this -> error("无数据需要审核!");
		}else{
			$this -> error("审核失败!");
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
			$comment = "";
			if($time1!='' && $time2 != ''){
				/*$comment = CommentModel::withSearch(['create_time'], ['create'	=>	[$time1,$time2]])->select();*/
				$comment =CommentModel::alias('a')->join('comment_data b','a.id = b.cid')->join('member c','a.uid = c.id')->join('article d','a.aid = d.id')->fieldRaw('a.id,a.plname,a.content,a.create_time,a.status,b.*,c.name,d.title')->where([[['create_time', 'between time', [$time1, $time2]]],['d.title|a.content|c.name', 'like', "%{$res}%"]])->select();
			}else if($res != ''){
			    $comment =CommentModel::alias('a')->join('comment_data b','a.id = b.cid')->join('member c','a.uid = c.id')->join('article d','a.aid = d.id')->fieldRaw('a.id,a.plname,a.content,a.create_time,a.status,b.*,c.name,d.title')->where([['d.title|a.content|c.name', 'like', "%{$res}%"]])->select();
			/*$comment = CommentModel::withSearch(['object'], ['object'	=>	$res])->select();*/
			}
			$this ->assign('comment',$comment);
			return view('comment_lists');
		}
}