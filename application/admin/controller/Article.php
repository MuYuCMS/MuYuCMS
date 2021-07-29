<?php
namespace app\admin\controller;
use app\admin\controller\Base;
use think\Request;
use think\Session;
use app\admin\model\Type;
use app\admin\model\Hmenu;
use app\admin\model\Article as ArticleModel;
use think\Db;

class Article extends Base
{
    //资讯列表输出
    public function index(Request $request)
    {
		//判断当前IP是否允许操作后台
		$ip = $this->ip_info();
		//判断是否登录
		$user = $this -> user_info();
		$menuid=NULL;
		//接收传递过来的数据
		$id=$request->param('id');
		$state_id=$request->param('state_id');
		$page = $request->param('page') ? $request->param('page') : 1;
		$type = Db::name('hmenu')->where('type',0)->where('pid',0)->field('id,title')->select();
		if($state_id == NULL){
		if($id==NULL){
		 $list = ArticleModel::alias('a')->join('article_data b','a.id = b.aid')->fieldRaw('a.*,b.*')->where(['status'=>[0,1,2]])->order('create_time','desc')->paginate(25);
		}else{
		//根据分类id查文章
		$list = ArticleModel::alias('a')->join('article_data b','a.id = b.aid')->fieldRaw('a.*,b.*')->where(['status'=>[0,1,2]])->where("find_in_set(".$id.",mid)")->order('create_time','desc')->paginate(25);
		$menuid = $id;
		}
		}else{
		  if($id==NULL){
		 $list = ArticleModel::alias('a')->join('article_data b','a.id = b.aid')->fieldRaw('a.*,b.*')->where(['status'=>$state_id])->order('create_time','desc')->paginate(25);
		}else{
		//根据分类id查文章
		$list = ArticleModel::alias('a')->join('article_data b','a.id = b.aid')->fieldRaw('a.*,b.*')->where(['status'=>$state_id])->where("find_in_set(".$id.",mid)")->order('create_time','desc')->paginate(25);
		$menuid = $id; 
		}
		}
		//赋值给模板
		$this -> view -> assign(['type'=>$type,'list'=>$list,'menuid'=>$menuid]);
		
		return $this -> view -> fetch('article_list');
    }

    //资讯列表添加资讯
    public function create()
    {
		//判断当前IP是否允许操作后台
		$ip = $this->ip_info();
		//判断是否登录
		$user = $this -> user_info();
		//输出分类以及栏目
		$type = Db::name('type')->select();
		$menu = Hmenu::where(['type'=>0,'status'=>1,'pid'=>0])->select();
		foreach($menu as $key=>$val){
			$two = Hmenu::where(['pid'=>$val['id'],'status'=>'1'])->select()->toArray();
			$menu[$key]['secontit'] = $two;
		}
		return $this -> view ->fetch('article_new',['menu'=>$menu,'type'=>$type]);
    }
	//执行增加到数据表更新
	public function tonewadd(Request $request)
	{
		//判断当前IP是否允许操作后台
		$ip = $this->ip_info();
		//判断是否登录
		 $user = $this -> user_info();
		 //获取所有文章
		 $orders = ArticleModel::all();
		  //自动排序
		 $order="1";
		 if($orders != NULL | $orders != "")
		 foreach($orders as $key=>$val){
		 	$order = $val["orders"]+1;
		 }
		//接收传递过来的数据
		$data = array_filter($request->post());
		$data['uid'] = $user['id'];
		$data['orders'] = $order;//文章排序
		$data['isadmin'] = 1;//后台发布默认管理员发布
		$data['create_time'] = time();
		$data['update_time'] = time();
		//数据库添加操作
		$res = ArticleModel::insert($data);
		//取得新增数据id
		$uid = Db::name('article')->getLastInsID();
		//判断是否成功并提示
		if($res){
		    $lm_id = Db::name("article")->where("id",$uid)->field("mid")->find();
		    $lms = Db::name("hmenu")->where("id",$lm_id["mid"])->field("pid")->find();
		    if(!empty($lms)){
		    if($lms["pid"] != "0"){
		        Db::name("hmenu")->where(["id"=>$lms["pid"],"id"=>$lm_id["mid"]])->setInc("ar_cont");
		    }else{
		        Db::name("hmenu")->where("id",$lm_id["mid"])->setInc("ar_cont");
		    }
		    }
		    //建立新增文章附属表
		    Db::name('article_data')->insert(['aid'=>$uid]);
			$this -> success("添加成功!",'Article/index');
		}else{
			$this -> error("添加失败!");
		}
	}

	//草稿保存
	public function draft(Request $request)
	{
		//判断当前IP是否允许操作后台
		$ip = $this->ip_info();
		//判断是否登录
		$user = $this -> user_info();
		//自动排序
		$orders = ArticleModel::all();
		 $order="1";
		 if($orders != NULL | $orders != "")
		 foreach($orders as $key=>$val){
		 	$order = $val["orders"]+1;
		 }
		//接收传递过来的数据
		$data = array_filter($request->post());
		$data['uid'] = $user['id'];
		$data['create_time'] = time();
		$data['update_time'] = time();
		//后台发布默认管理员发布
		$data['isadmin'] = 1;
		//文章排序
		$data['orders'] = $order;
		//发布状态（草稿)
		$data['status'] = 1;
		//数据库添加操作
		$res = ArticleModel::insert($data);
		//取得新增数据id
		$uid = Db::name('article')->getLastInsID();
		//判断是否成功并提示
		if($res){
		    //建立新增文章附属表
		    Db::name('article_data')->insert(['aid'=>$uid]);
			$this -> success("保存成功!",'Article/index');
		}else{
			$this -> error("保存失败!");
		}
	}
	
    /*
    *资讯列表搜索操作
    */
    public function soulist(Request $request){
		//判断当前IP是否允许操作后台
		$ip = $this->ip_info();
		//判断是否登录
		$user = $this -> user_info();
		//搜索放这里,接收前台传过来的数据
		$res = $request -> get('so');
		//获取时间戳
		$time1 = strtotime($request->get('t1'));
		$time2 = strtotime($request->get('t2'));
		$article = "";
		//根据时间以及搜索内容查询数据表中的标题和作者字段所有值
		if($time1!='' && $time2 != ''){
			$article =ArticleModel::alias('a')->join('article_data b','a.id = b.aid')->fieldRaw('a.*,b.*')->where([[['a.create_time', 'between time', [$time1, $time2]]],['a.title|a.author', 'like', "%{$res}%"],['a.status', 'in', [0,1,2]]])->select();
		}else if($res != ''){
    		    $article =ArticleModel::alias('a')->join('article_data b','a.id = b.aid')->fieldRaw('a.*,b.*')->where([['a.title|a.author', 'like', "%{$res}%"],['a.status', 'in', [0,1,2]]])->select();
		}
		$this ->assign('article',$article);
		return view('article_lists');
	}
	//文章编辑修改
    public function update(Request $request)
    {
		//判断当前IP是否允许操作后台
		$ip = $this->ip_info();
       //判断是否登录
       $user = $this -> user_info();
		//接收传递过来的数据
		$data = $request->post();
		$ar = Db::name("article")->where("id",$data["id"])->field("mid")->find();
		$res = Db::name("article")->update($data);
		//判断是否成功并提示
		if($res){
		    if($ar["mid"] !== $data["mid"]){
		      Db::name("hmenu")->where("id",$data["mid"])->setInc("ar_cont");  
		      Db::name("hmenu")->where("id",$ar["mid"])->setDec("ar_cont");
		    }
			$this -> success("修改成功!",'Article/index');
		}else{
			$this -> error("修改失败!");
		}
    }
    //资讯编辑
    public function edit(Request $request)
    {
		//判断当前IP是否允许操作后台
		$ip = $this->ip_info();
		//判断是否登录
		$user = $this -> user_info();
		$id = $request -> param('id');
		$article = ArticleModel::withTrashed()->get($id);
		$patch = "";
		if(!empty($article['downputh'])){
		$patch = explode(',',$article['downputh']);
		$patch = array_chunk($patch,2);
		}
		//输出分类以及栏目
		$type = Db::name('type')->select();
		$menu = Hmenu::where(['type'=>0,'status'=>1,'pid'=>0])->select();
		foreach($menu as $key=>$val){
			$two = Hmenu::where(['pid'=>$val['id'],'status'=>'1'])->select()->toArray();
			$menu[$key]['secontit'] = $two;
		}
		$article['type'] = explode(',',$article['type']);
		$this -> view ->assign(['article'=>$article,'type'=>$type,'menu'=>$menu,'patch'=>$patch]);
		
		return $this -> view ->fetch('article_add');
    }


	//标题图片上传
	public function upload(Request $request){
		//判断当前IP是否允许操作后台
		$ip = $this->ip_info();
		//判断是否登录
		$user = $this -> user_info();
		//接收上传的文件
		$file = request()->file('file');
		if(!empty($file)){
			//移动到框架指定目录
			$info = $file->validate(['size'=>1048576,'ext'=>'jpg,png,jpeg,gif'])->rule('uniqid')->move('./public/upload/menubg');
			if($info){
				//获取图片名称
				$imgName = str_replace("\\","/",$info->getSaveName());
				$photo = '/public/upload/menubg/'.$imgName;
			}else{
				$error = $file->getError();
			}
		}else{
			$photo = "";
		}
		//判断上传是否成功
		if($photo == ""){
			$error = $file->getError();
			return ['code'=>404,'msg'=>"上传失败,{$error}"];
		}else{
			return ['code'=>1,'msg'=>'上传成功',"photo"=>$photo];
		}
		
	}
	
		//文章附件上传
		public function pathupload(Request $request){
			//判断当前IP是否允许操作后台
			$ip = $this->ip_info();
			//判断是否登录
			$user = $this -> user_info();
			//接收上传的文件
			$pathtext = request()->param('id');
			$file = request()->file('file');
			if(!empty($file)){
				//移动到框架指定目录
				$info = $file->validate(['ext'=>'zip,rar,7z','size'=>1048576])->rule('uniqid')->move('./public/upload/files');
				if($info){
					//获取图片名称
					$imgName = str_replace("\\","/",$info->getSaveName());
					$photo = '/public/upload/files/'.$imgName;
				}else{
					$error = $file->getError();
				}
			}else{
				$photo = "";
			}
			//判断上传是否成功
			if($photo == ""){
				$error = $file->getError();
				return ['code'=>404,'msg'=>"上传失败,{$error}"];
			}else{
				return ['code'=>1,'msg'=>'上传成功',"photo"=>$photo,'pathtext'=>$pathtext];
			}
			
		}

	//文章状态变更
	public function setStatus(Request $request){
		//判断当前IP是否允许操作后台
		$ip = $this->ip_info();
		//判断是否登录
		$user = $this -> user_info();
		//获取前台传递id
		$aritcle_id = $request->param('id');
		//根据id查询数据
		$result = ArticleModel::get($aritcle_id);
		//查询原生数据进行判断
		if($result->getData('status') == 0){
			ArticleModel::update(['status'=>2,'top'=>0,'ppts'=>0,'id'=>$aritcle_id]);
			$md = Db::name("article")->where("id",$aritcle_id)->field("mid")->find();
			Db::name("hmenu")->where("id",$md["mid"])->setDec("ar_cont");
			$this -> success("已下架!");
		}else{
			ArticleModel::update(['status'=>0,'id'=>$aritcle_id,'refusal'=>NULL]);
			$md = Db::name("article")->where("id",$aritcle_id)->field("mid")->find();
			Db::name("hmenu")->where("id",$md["mid"])->setInc("ar_cont");
			$this -> success("已发布!");
		}
	}
	
	//文章置顶状态变更
	public function setTop(Request $request){
		//判断当前IP是否允许操作后台
		$ip = $this->ip_info();
		//判断是否登录
		$user = $this -> user_info();
		//获取前台传递id
		$aritcle_id = $request->param('id'); 
		//根据id查询数据
		$result = ArticleModel::get($aritcle_id);
		//查询原生数据进行判断
		if($result->getData('status') == 1)
		{
			$this -> error("草稿状态无法顶置!");
		}else{
			if($result->getData('status') == 2)
			{
				$this -> error("下架状态无法顶置!");
			}else{
				if($result->getData('top') == 0)
				{
					ArticleModel::update(['top'=>1],['id'=>$aritcle_id]);
					$this -> success("顶置成功!");
				}else{
					ArticleModel::update(['top'=>0],['id'=>$aritcle_id]);
					$this -> success("已取消顶置!");
				}
			}
			
		}
	}
	
	//文章幻灯片状态变更
	public function setPpts(Request $request){
		//判断当前IP是否允许操作后台
		$ip = $this->ip_info();
		//判断是否登录
		$user = $this -> user_info();
		//获取前台传递id
		$aritcle_id = $request->param('id'); 
		//根据id查询数据
		$result = ArticleModel::get($aritcle_id);
		//查询原生数据进行判断
		if($result->getData('status') == 1)
		{
			$this -> error("草稿状态无法展现!");
		}else{
			if($result->getData('status') == 2)
			{
				$this -> error("下架状态无法展现!");
			}else{
				if($result->getData('ppts') == 0)
				{
					ArticleModel::update(['ppts'=>1],['id'=>$aritcle_id]);
					$this -> success("已展现!");
				}else{
					ArticleModel::update(['ppts'=>0],['id'=>$aritcle_id]);
					$this -> success("已取消展现!");
				}
			}
			
		}
	}
	
	//删除文章
    public function delete(Request $request)
    {
		//判断当前IP是否允许操作后台
		$ip = $this->ip_info();
		//判断是否登录
		$user = $this -> user_info();
		//获取传递过来的id
		$id = trim($request -> param('ids'));
		$md = Db::name("article")->where("id",$id)->field("mid")->find();
        //获取传递过来的值并删除
		$res =ArticleModel::destroy($id);
		if($res){
		    $lmd = Db::name("hmenu")->where("id",$md["mid"])->find();
		    if($lmd["pid"] != 0){
		        Db::name("hmenu")->where("id",$lmd["id"])->setDec("ar_cont");
		        Db::name("hmenu")->where("id",$lmd["pid"])->setDec("ar_cont");
		    }else{
		        Db::name("hmenu")->where("id",$lmd["id"])->setDec("ar_cont");  
		    }
		    //删除文章附属表
		    Db::name('article_data')->where('aid',$id)
	        ->useSoftDelete('delete_time',time())
            ->delete();
			$this -> success("删除成功!");
		}else{
			$this -> error("删除失败!");
		}
    }
	//渲染已删除资讯列表
	public function articledel(Request $request){
		//判断当前IP是否允许操作后台
		$ip = $this->ip_info();
		//判断用户是否登录
		$user = $this -> user_info();
		//查询表所有已删除的文章数据
		$delart = ArticleModel::onlyTrashed()->alias('a')->join('article_data b','a.id = b.aid')->fieldRaw('a.*,b.browse,b.likes')->order('delete_time','desc')->paginate(25);
		//赋值给模板
		$this -> view -> assign('delart',$delart);
		return $this -> view -> fetch('article_del');
	}
	//彻底删除文章
	public function setdel(Request $request){
		//判断当前IP是否允许操作后台
		$ip = $this->ip_info();
		//判断用户是否登录
		$user = $this -> user_info();
		//彻底删除对应信息
		$delid = $request->param('id');
		//删除对应ID信息
		$res=Db::name('article')->delete($delid);
		if($res){
		    //同步删除文章附属表
		    Db::name('article_data')->where('aid',$delid)->delete();
		    $this->success('删除成功!');
		}else{
		    $this->error('删除失败!');
		}
	}
	//还原已删除文章
	public function huanyuan(Request $request){
		//判断当前IP是否允许操作后台
		$ip = $this->ip_info();
		//判断用户是否登录
		$user = $this -> user_info();
		//获取前台传递的id
		$delid = $request->param('id');
		$md = Db::name("article")->where("id",$delid)->field("mid")->find();
		$mid = Db::name("hmenu")->where("id",$md["mid"])->find();
		//恢复相应id信息
		$res=Db::name('article') ->where('id',$delid) ->setField('delete_time', NULL);
		if($res){
		    if($mid["pid"] != 0){
		        Db::name("hmenu")->where("id",$md["mid"])->setInc("ar_cont");
		        Db::name("hmenu")->where("id",$mid["pid"])->setInc("ar_cont");
		    }else{
		        Db::name("hmenu")->where("id",$md["mid"])->setInc("ar_cont");
		    }
		    //同步恢复文章附属表
		    Db::name('article_data')->where('aid',$delid) ->setField('delete_time', NULL);
		    $this->success('还原成功!');
		}else{
		    $this->error('还原失败!');
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
			$md = Db::name("article")->where("id","in",$id)->field("mid")->select()->toArray();
			$res =Db::name('article') ->where('id','in',$id) ->setField('delete_time', NULL);
			if($res){
			    foreach($md as $key=>$val){
			    $mdd = Db::name("hmenu")->where("id",$val["mid"])->field("pid")->select()->toArray();
			    foreach($mdd as $val){
		    	if($val["pid"] != 0){
			    $md[$key]["pid"] = $val["pid"];
			    }
			    }
			    }
			    foreach($md as $val){
			        Db::name("hmenu")->where("id",$val["mid"])->setInc("ar_cont");
			        if(!empty($val["pid"])){
			        Db::name("hmenu")->where("id",$val["pid"])->setInc("ar_cont");
			        }
			    }
			    //同步还原文章附属表
			    Db::name('article_data') ->where('aid','in',$id) ->setField('delete_time', NULL);
				$this -> success("还原成功!",'Article/articledel');
			}else{
				$this -> error("还原失败!");
			}
		}
		
	    //资讯列表/资讯搜索列表批量删除
		public function deleteall(Request $request){
			//判断当前IP是否允许操作后台
			$ip = $this->ip_info();
			//判断是否登录
			$user = $this -> user_info();
			$md = Db::name("article")->where("id","in",$request -> post('delid'))->field("mid")->select()->toArray();
			//获取传递过来的值并删除
			$res =ArticleModel::destroy($request -> post('delid'));
			if($res){
			    foreach($md as $key=>$val){
			    $mdd = Db::name("hmenu")->where("id",$val["mid"])->field("pid")->select()->toArray();
			    foreach($mdd as $val){
		    	if($val["pid"] != 0){
			    $md[$key]["pid"] = $val["pid"];
			    }
			    }
			    }
			    foreach($md as $val){
			        Db::name("hmenu")->where("id",$val["mid"])->setDec("ar_cont");
			        if(!empty($val["pid"])){
			        Db::name("hmenu")->where("id",$val["pid"])->setDec("ar_cont");
			        }
			    }
			    //同步批量删除对应附属表
			    Db::name('article_data')->where('aid','in',$request -> post('delid'))->useSoftDelete('delete_time',time())->delete();
				$this -> success("删除成功!");
			}else{
				$this -> error("删除失败!");
			}
		}
		
		
		//删除的资讯列表批量删除
		public function deletealls(Request $request){
			//判断当前IP是否允许操作后台
			$ip = $this->ip_info();
			//判断是否登录
			$user = $this -> user_info();
			//删除对应ID信息
			$res = Db::name('article')->delete($request->post('delid'));
			if($res){
			    Db::name('article_data')->where('aid','in',$request->post('delid'))->delete();
				$this -> success("删除成功!",'Article/articledel');
			}else{
				$this -> error("删除失败!");
			}
		}
		
		
		
		//资讯审核列表
		public function audit(Request $request){
    		//判断当前IP是否允许操作后台
    		$ip = $this->ip_info();
    		//判断是否登录
    		$user = $this -> user_info();
    		//查询数据表待审核和已驳回的文章
    		$article=ArticleModel::alias('a')->join('article_data b','b.aid = a.id')->fieldRaw('a.*,b.browse,b.likes')->where(['a.status'=>[3,4]])->order('a.create_time','desc')->paginate(10);
    		//赋值给模板
    		$this -> view -> assign(['article'=>$article]);
    		return $this->view->fetch('article_audit');
		}
		
		//资讯驳回
		public function refusal(Request $request){
		//判断当前IP是否允许操作后台
		$ip = $this->ip_info();
		//判断是否登录
		$user = $this -> user_info();
		//获取前台传递过来的数据
		$data=$request->get();
		$res=Db::name('article')->where('id', $data['id'])->update(['refusal' => $data['refusal'],'status'=>4]);
		if($res){
		    $this->success("驳回成功!");
		}else{
		    $this->error("驳回失败!");
		    }
		}
		
		//文章批量审核
    	public function audits(Request $request){
		//判断当前IP是否允许操作后台
		$ip = $this->ip_info();
		//判断是否登录
		$user = $this -> user_info();
		$id = $request -> post('id');
		$md = Db::name("article")->where("id","in",$id)->field("mid")->select()->toArray();
		$res=ArticleModel::where('id','in',$id)->update(['status'=>0]);

		if($res){
		    foreach($md as $key=>$val){
			    $mdd = Db::name("hmenu")->where("id",$val["mid"])->field("pid")->select()->toArray();
			    foreach($mdd as $val){
		    	if($val["pid"] != 0){
			    $md[$key]["pid"] = $val["pid"];
			    }
			    }
			    }
			    foreach($md as $val){
			        Db::name("hmenu")->where("id",$val["mid"])->setInc("ar_cont");
			        if(!empty($val["pid"])){
			        Db::name("hmenu")->where("id",$val["pid"])->setInc("ar_cont");
			        }
			    }
			$this -> success("审核通过!");
		}else if(ArticleModel::where('status'==0))
		{
			$this -> error("无数据需要审核!");
		}else{
			$this -> error("审核失败!");
		} 
	}
	
	/*
    *资讯审核搜索操作
    */
    public function soulists(Request $request){
		//判断当前IP是否允许操作后台
		$ip = $this->ip_info();
		//判断是否登录
		$user = $this -> user_info();
		//搜索放这里，接收前台传过来的数据
		$res = $request -> get('so');
		//获取时间戳
		$time1 = strtotime($request->get('t1'));
		$time2 = strtotime($request->get('t2'));
		$article = "";
		//对前台传过来的数据进行查询
		if($time1!='' && $time2 != ''){
		    $article =ArticleModel::alias('a')->join('article_data b','a.id = b.aid')->fieldRaw('a.*,b.*')->where([[['create_time', 'between time', [$time1, $time2]]],['title|author', 'like', "%{$res}%"],['status', 'in', [3,4]]])->select();
		}else if($res != ''){
		    $article =ArticleModel::alias('a')->join('article_data b','a.id = b.aid')->fieldRaw('a.*,b.*')->where([['title|author', 'like', "%{$res}%"],['status', 'in', [3,4]]])->select();
		}
		$this ->assign('article',$article);
		return view('article_soaudit');
	}

}
