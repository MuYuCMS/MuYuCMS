<?php

namespace app\admin\controller;
use app\admin\controller\Base;
use think\Request;
use app\admin\model\Comment as CommentModel;
use think\Db;

class Comment extends Base
{
    //评论列表
    public function index()
    {
        $status = ['0'=>'待审核','1'=>'已通过','2'=>'已屏蔽'];
        $comment = Db::name("comment")->alias('a')->join('comment_data b','a.id = b.cid')->leftjoin('member c','a.uid = c.id')->field('a.*,b.*,c.name')->where(['a.status'=>[1,2]])->order('a.create_time','desc')->paginate(25);
        $mode = Db::name('model')->field("tablename")->select();
        $data = $comment->all();
        if(!empty($data)){
        foreach($data as $key=>$val){
            $val['status'] = $status[$val['status']];
            $val['catetitle'] = "-文章已遗失-";
            if(empty($val["name"])){
                $val["name"] = $val["plname"];
            }
            foreach($mode as $v){
                $t = Db::name($v['tablename'])->field("title")->find($val['aid']);
                if(!empty($t)){
                    $val['catetitle'] = $t['title'];
                }
            }
            $comment[$key] = $val;
        }
        }
		//赋值给模板
		return $this -> view -> fetch('list',['comment'=>$comment]);
    }
    
    //待审核列表
    public function audit()
    {
		$status = ['0'=>'待审核','1'=>'已通过','2'=>'已屏蔽'];
        $comment = Db::name("comment")->alias('a')->join('comment_data b','a.id = b.cid')->leftjoin('member c','a.uid = c.id')->field('a.*,b.*,c.name')->where(['a.status'=>0])->order('a.create_time','desc')->paginate(25);
        $mode = Db::name('model')->field("tablename")->select();
        if(!empty($data)){
        $data = $comment->all();
        foreach($data as $key=>$val){
            $val['status'] = $status[$val['status']];
            $val['catetitle'] = "-文章已遗失-";
            if(empty($val["name"])){
                $val["name"] = $val["plname"];
            }
            foreach($mode as $v){
                $t = Db::name($v['tablename'])->field("title")->find($val['aid']);
                if(!empty($t)){
                    $val['catetitle'] = $t['title'];
                }
            }
            $comment[$key] = $val;
        }
        }
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
			$comment = array();
			if(!empty($res)){
			if($t=='list'){//评论列表搜索
			$status = ['0'=>'待审核','1'=>'已通过','2'=>'已屏蔽'];
            $comment = Db::name("comment")->alias('a')->join('comment_data b','a.id = b.cid')->leftjoin('member c','a.uid = c.id')->field('a.*,b.*,c.name')->where([['a.content|c.name', 'like', "%{$res}%"]])->where(['a.status'=>[1,2]])->order('a.create_time','desc')->paginate(25);
            $mode = Db::name('model')->field("tablename")->select();
            $data = $comment->all();
            if(!empty($data)){
            foreach($data as $key=>$val){
            $val['status'] = $status[$val['status']];
            $val['catetitle'] = "-文章已遗失-";
            if(empty($val["name"])){
                $val["name"] = $val["plname"];
            }
            foreach($mode as $v){
                $t = Db::name($v['tablename'])->field("title")->find($val['aid']);
                if(!empty($t)){
                    $val['catetitle'] = $t['title'];
                }
            }
            $comment[$key] = $val;
            }
            }
			}else if($t == 'audit'){//审核列表搜索
    			    $status = ['0'=>'待审核','1'=>'已通过','2'=>'已屏蔽'];
            $comment = Db::name("comment")->alias('a')->join('comment_data b','a.id = b.cid')->leftjoin('member c','a.uid = c.id')->field('a.*,b.*,c.name')->where([['a.content|c.name', 'like', "%{$res}%"]])->where(['a.status'=>0])->order('a.create_time','desc')->paginate(25);
            $mode = Db::name('model')->field("tablename")->select();
            $data = $comment->all();
            if(!empty($data)){
            foreach($data as $key=>$val){
            $val['status'] = $status[$val['status']];
            $val['catetitle'] = "-文章已遗失-";
            if(empty($val["name"])){
                $val["name"] = $val["plname"];
            }
            foreach($mode as $v){
                $t = Db::name($v['tablename'])->field("title")->find($val['aid']);
                if(!empty($t)){
                    $val['catetitle'] = $t['title'];
                }
            }
            $comment[$key] = $val;
            }
    		}
			}
			}
			$this ->assign('comment',$comment);
			return view('list');
		}
}
