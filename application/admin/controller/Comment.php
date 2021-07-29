<?php

namespace app\admin\controller;
use app\admin\controller\Base;
use think\Request;
use app\admin\model\Comment as CommentModel;
use app\admin\model\Member as MemberModel;
use think\Db;

class Comment extends Base
{
    //评论列表
    public function index()
    {
		//查询评论的所有信息并按时间排序
		$comment = CommentModel::alias('a')->join('comment_data b','a.id = b.cid')->leftjoin('member c','a.uid = c.id')->leftjoin('article d','a.aid = d.id')->fieldRaw('a.id,a.plname,a.content,a.create_time,a.status,b.*,c.name,d.title')->where(['a.status'=>[1,2]])->order('a.create_time','desc')->paginate(25);
		//赋值给模板
		return $this -> view -> fetch('list',['comment'=>$comment]);
    }
    
    //待审核列表
    public function audit()
    {
		//查询评论的待审核列表并按时间排序
		$comment = CommentModel::alias('a')->join('comment_data b','a.id = b.cid')->leftjoin('member c','a.uid = c.id')->leftjoin('article d','a.aid = d.id')->fieldRaw('a.id,a.plname,a.content,a.create_time,a.status,b.*,c.name,d.title')->where('a.status',0)->order('a.create_time','desc')->paginate(25);
		//赋值给模板
		return $this -> view -> fetch('audit',['comment'=>$comment]);
    }
    
	//评论状态变更
	public function setStatus(Request $request){
		//获取前台传递id
		$comment_id = $request->param('id');
		//根据id查询数据
		$result = CommentModel::get($comment_id);
		//查询原生数据进行判断
		if($result->getData('status') == 1){
			$res = CommentModel::update(['status'=>2],['id'=>$comment_id]);
			if($res){
			    $this -> logs("评论状态 [ID: ".$comment_id.'] 屏蔽成功!');
			    $this->success("屏蔽成功！",'Comment/index');
			}else{
			    $this->error("屏蔽失败！");
			}
		}else{
			$res = CommentModel::update(['status'=>1],['id'=>$comment_id]);
			if($res){
			    $this -> logs("评论状态 [ID: ".$comment_id.'] 展现成功!');
			    $this->success("展现成功！",'Comment/index');
			}else{
			    $this->error("展现失败！");
			}
		}
	}

    //评论删除-批量删除
    public function deletes(Request $request)
    {
        $id = $request->param('id');
        //获取传递过来的值并删除
		$res =CommentModel::destroy($id);
		if($res){
		    //同步删除data表
		    Db::name('comment_data')
		    ->where('cid',$id)
		    ->delete();
		    $this -> logs("删除评论 [ID: ".$id.'] 删除成功!');
			$this -> success("删除成功!",'Comment/index');
		}else{
			$this -> error("删除失败!");
		}
    }
    

	
	//评论审核-批量审核
	public function shenhe(Request $request){
		$id = $request -> param('id');
		$res=CommentModel::where('id','in',$id)->update(['status'=>1]);
		if($res){
		    $this -> logs("评论审核 [ID: ".$id.'] 审核成功!');
		    $this->success("审核成功！",'Comment/audit');
		}else{
		    $this->error("审核失败！");
		}

	}
	
	/*
	*评论搜索操作
	*/
	    public function search(Request $request){
			//获取搜索关键词和参数
			$res = $request -> param('keywords');
			$t = $request->param('t');
			if($t=='list'){//评论列表搜索
			    if($res != ''){
    			    $comment =CommentModel::alias('a')->join('comment_data b','a.id = b.cid')->join('member c','a.uid = c.id')->join('article d','a.aid = d.id')->fieldRaw('a.id,a.plname,a.content,a.create_time,a.status,b.*,c.name,d.title')->where([['d.title|a.content|c.name', 'like', "%{$res}%"]])->where(["a.status"=>[1,2]])->select();
    			}
			}else if($t == 'audit'){//审核列表搜索
			    if($res != ''){
    			    $comment =CommentModel::alias('a')->join('comment_data b','a.id = b.cid')->join('member c','a.uid = c.id')->join('article d','a.aid = d.id')->fieldRaw('a.id,a.plname,a.content,a.create_time,a.status,b.*,c.name,d.title')->where([['d.title|a.content|c.name', 'like', "%{$res}%"],['a.status','=',0]])->select();
    			}
			}
			
			$this ->assign('comment',$comment);
			return view('list');
		}
}