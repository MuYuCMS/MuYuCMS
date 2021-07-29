<?php
namespace app\index\controller;
use app\index\controller\Base;
use think\Request;
use app\index\model\Member;
use app\index\model\Comment;
use app\index\model\Feedback;
use app\index\model\Article;
use app\index\model\Hmenu;
use think\facade\Session;
use app\index\model\Email;
use think\Db;

class User extends Base
{   
        //用户注册发送验证码
    	public function sendEmails(Request $request){
        //接收前台传过来的email信息
		$emails = $request->param('email');
		//接收前台传过来的verif信息
		$das = $request->param('verif');
		            //图片验证码
		            if(!captcha_check($das)) {
		                // 校验失败
		                $this -> error("验证码不正确!");
		            }
		 //查询邮箱配置中email,emailpaswsd,smtp,sll,ceemail字段的信息 
		$email=Db::name('email')->where('id',1)->field('email,emailpaswsd,smtp,sll,ceemail')->find();
		//查询网站名称以及logo路径
		$emname = Db::name('system')->where('id',1)->field('title,logo')->find();
		//获取前段网站域名
		$host = $request->domain();
		//调用随机数函数
		$yanzhen=$this->codestr();
		$title = "会员注册验证码";
		$content = "<div style='padding:30px; background:#F5F6F7;'><div style='font-size:14px; border-top:3px solid #2254f4; background:#fff; color:#333; padding:20px 40px; border-radius:4px; width:542px; margin:40px auto;'><div style='text-align:right; margin-bottom:10px;'><img src='".$host.$emname['logo']."' style='display:inline-block;height:40px;width:40px;'></div><div> </div><div>您正在尝试注册为<b style='color:#333;'>".$emname['title']."</b>会员，为验证此电子邮箱属于您，请您在会员注册网页中输入下方授权验证码：</div><div style='font-size:24px; color:#2254f4; margin-bottom:20px; margin-top:10px;'><strong>".$yanzhen."</strong></div><div style='margin-top:10px;'>验证码有效期为<b style='color:#333;'>5分钟</b>，如非本人操作，请忽略该邮件。</div><div style='font-size:12px;color:#999;'>提示：成为".$emname['title']."会员，只有在电子邮箱验证成功后才能被创建。</div><div style='color:#999;text-align:right;line-height:30px;margin-top:40px;padding-top:10px;border-top:1px solid #eee;font-size:12px;'>".$emname['title'].".</div></div></div>";
        //判断是否查询到$email，没有查到执行
		if(! $email){
			 $this -> error("没有邮箱配置信息!");
		}
		//判断是否查询到$emname，没有查到执行
		if(! $emname){
		    $this -> error("没有网站详细信息!");
		}
        //调用发送函数
		$res = sendEmail($email['email'],$email['emailpaswsd'],$email['smtp'],$email['sll'],$emname['title'],$title,$content,$emails);
		 if($res){
		     //获取当前时间
		     $time=time();
		     //设置session值，以便于匹配注册验证码以及注册验证码有效时间
		     Session::set('codestr',$yanzhen);
		     Session::set('time',$time);
		     $this->success("发送成功");
		 }else{
		     $this -> error("发送失败!");
		 }
	}
	
	//用户密码找回发送验证码
	public function passEmail(Request $request){
	    //接收前台传过来的所有信息
	    $data = $request->param();
		            //图片验证码
		            if(!captcha_check($data['verif'])) {
		                // 校验失败
		                $this -> error("验证码不正确!");
		            }
		//查询邮箱配置中email,emailpaswsd,smtp,sll,ceemail字段的信息 
		$email=Db::name('email')->where('id',1)->field('email,emailpaswsd,smtp,sll,ceemail')->find();
		//查询网站名称以及logo路径
		$emname = Db::name('system')->where('id',1)->field('title,logo')->find();
		//查询当前网站域名地址
		$host = $request->domain();
		//调用随机数函数
		$yanzhen=$this->codestr();
		$title = "密码找回验证码";
		$content = "<div style='padding:30px; background:#F5F6F7;'><div style='font-size:14px; border-top:3px solid #2254f4; background:#fff; color:#333; padding:20px 40px; border-radius:4px; width:542px; margin:40px auto;'><div style='text-align:right; margin-bottom:10px;'><img src='".$host.$emname['logo']."' style='display:inline-block;height:40px;width:40px;'></div><div> </div><div>您正在尝试找回<b style='color:#333;'>".$emname['title']."</b>会员的密码，为验证此电子邮箱属于您，请您在会员找回密码网页中输入下方授权验证码：</div><div style='font-size:24px; color:#2254f4; margin-bottom:20px; margin-top:10px;'><strong>".$yanzhen."</strong></div><div style='margin-top:10px;'>验证码有效期为<b style='color:#333;'>5分钟</b>，如非本人操作，请忽略该邮件。</div><div style='font-size:12px;color:#999;'>提示：只有在电子邮箱验证成功后密码才能被找回。</div><div style='color:#999;text-align:right;line-height:30px;margin-top:40px;padding-top:10px;border-top:1px solid #eee;font-size:12px;'>".$emname['title'].".</div></div></div>";
		//判断是否查询到$email，没有查到执行
		if(! $email){
			$this -> error("没有邮箱配置信息!");
		}
		//判断是否查询到$emname，没有查到执行
		if(! $emname){
		    $this -> error("没有网站详细信息!");
		}
		//把会员名传给变量$use
		$use=$data['account'];
		//根据会员名查找邮箱
		$e=Db::name('member')->where('account',$use)->field('email')->find();
        if($e['email']!==$data['email'])
        {
            $this -> error("会员的邮箱不正确!");
        }else{
            $res = sendEmail($email['email'],$email['emailpaswsd'],$email['smtp'],$email['sll'],$emname['title'],$title,$content,$data['email']);
            if($res){
             //获取当前时间   
		     $time=time();
		     //设置session值，以便于匹配找回密码验证码以及找回密码验证码有效时间
		     Session::set('codestr',$yanzhen);
		     Session::set('time',$time);
		     $this->success("发送成功");
		 }else{
		     $this -> error("发送失败!");
		 }
        }	 
	}
	
    //用户中心首页
    public function index()
    {
        //判断当前会员是否登录
        $user = $this -> user_info();
        $att = 0;
        $fans = 0;
		//获取登录会员的ID
	    $useid=Session::get('Member.id');
	    //查询该会员在数据表的所有信息
	    $usename = Member::where('id',$useid)->find();
	    //统计会员评论总数
	    $comid=Comment::where('uid',$useid)->count();
	    //统计会员留意总数
	    $feeid=Feedback::where('uid',$useid)->count();
	    //统计会员投稿次数
	    $artid=Article::where(['uid'=>$useid,'isadmin'=>"0"])->count();
	    if($usename['attention'] !== NULL){
	        //将字段attention（字符串类型）分割数组形式并统计其元素个数
	        $att=count(explode(",", $usename['attention']));
	    }
	    if($usename['fans'] !== NULL){
    	    //将字段fans（字符串类型）分割数组形式并统计其元素个数
    	    $fans=count(explode(",", $usename['fans']));
	    }
		//传递给模板
		$this -> view -> assign(['member'=>$usename,'comment_sum'=>$comid,'feedback_sum'=>$feeid,'article_sum'=>$artid,'attention_sum'=>$att,'fans_sum'=>$fans]);
        return $this-> fetch('/user/index');
    }
    //用户头像上传
	public function upload(Request $request){
		//判断当前会员是否登录
        $user = $this -> user_info();
		//接收上传的文件
		$photo = "";
		$file = request()->file('file');
		if(!empty($file)){
			//移动到框架指定目录
			$info = $file->validate(['size'=>51200,'ext'=>'jpg,png,jpeg,gif'])->rule('uniqid')->move('./public/upload/userimages');
			if($info){
				//获取图片名称
				$imgName = str_replace("\\","/",$info->getSaveName());
				$photo = '/public/upload/userimages/'.$imgName;
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
		    //获取登录会员的ID
	        $useid=Session::get('Member.id');
		    Member::where('id',$useid)->update(['photo'=>$photo]);
			return ['code'=>1,'msg'=>'上传成功',"photo"=>$photo];
		}
		
	}
    
    
    
    //用户中心评论
    public function my_comment(Request $request)
    {
        //判断当前会员是否登录
        $user = $this -> user_info();
		 //获取登录会员的ID
	    $useid=Session::get('Member.id');
	    //查询该会员在数据表的所有信息
	    $usename = Member::where('id',$useid)->find();
		//查询评论表中当前会员的评论
		$comment=Comment::where('uid',$useid)->order('create_time','desc')->paginate();
		//传递给模板
		$this -> view -> assign(['member'=>$usename,'comment'=>$comment]);
        return $this-> fetch('/user/my_comment');
    }
    //用户中心我的评论删除
    public function comment_del(Request $request)
    {
		//判断当前会员是否登录
		$user = $this -> user_info();
		//接收前台传过来的id信息
		$id = $request -> param('ids');
        //获取传递过来的值并删除
		$res =Comment::destroy($id);
		if($res){
		    //删除评论附属表
		    Db::name('comment_data')->where('cid',$id)->delete();
			$this -> success("删除成功!",'User/my_comment');
		}else{
			$this -> error("删除失败!");
		}
    }
    //用户中心我的评论修改
    public function comment_modify(Request $request)
    {
        //判断当前会员是否登录
        $user = $this -> user_info();
        //接收前台传过来的content信息
        $data=$request->param('content');
        //接收前台传过来的id信息
        $id=$request->param('id');
        //根据评论ID查询当前文章ID
        $article_id=Db::name('comment')->where('id',$id)->field('aid')->find();
        //根据文章ID查询当前文章是否允许评论
        $article_close=Db::name('article')->where('id',$article_id['aid'])->field('comment')->find();
        //查询屏蔽词
        $shile=Db::name('system')->where('id',1)->field('shielding,comment_close,commentaudit_close')->find();
        //调用过滤屏蔽词函数
        $result = $this->sensitive($shile, $data);
        //判断评论开关是否为开
        if($shile['comment_close']==0){
            //判断当前文章是否允许评论
            if($article_close['comment']==0){
                //判断评论是否为自动审核
                if($shile['commentaudit_close']==0){
                    //判断评论是否有屏蔽词
                if($result['status']==0){
                 //将过滤后的评论内容更新到数据表中
                    $res=Db::name('comment')->where('id',$id)->update(['content'=>$result['log'],'status'=>1]);
                     if($res){
                        $this->success("修改成功!");
                         }else{
                        $this->error("修改失败!");
                        }
                    }else{
                        if($result['status']==1){
                        //返回屏蔽词信息
                        $this->error("存在敏感词:{$result['message']},请修改!");
                        }
                        }
                }else{
                //判断评论是否有屏蔽词
                if($result['status']==0){
                 //将过滤后的评论内容更新到数据表中
                    $res=Db::name('comment')->where('id',$id)->update(['content'=>$result['log']]);
                     if($res){
                        $this->success("修改成功!");
                         }else{
                        $this->error("修改失败!");
                        }
                    }else{
                        if($result['status']==1){
                        //返回屏蔽词信息
                        $this->error("存在敏感词:{$result['message']},请修改!");
                        }
                        }
                }
            }else{
                $this->error("当前文章评论已关闭!");
            }
        }else{
            $this->error("评论已关闭!");
        }
        
    }
    //用户中心留言
    public function my_feedback()
    {
        //判断当前会员是否登录
        $user = $this -> user_info();
		 //获取登录会员的ID
	    $useid=Session::get('Member.id');
	    //查询该会员在数据表的所有信息
	    $usename = Member::where('id',$useid)->find();
		//查询评论表中当前会员的留言
		$feedback=Feedback::where('uid',$useid)->order('create_time','desc')->paginate();
		//传递给模板
		$this -> view -> assign(['member'=>$usename,'feedback'=>$feedback]);
        return $this-> fetch('/user/my_feedback');
    }
    //用户中心留言删除
    public function feedback_del(Request $request)
    {
        //判断是否登录
		$user = $this -> user_info();
		//获取传递过来的id
		$id = trim($request -> param('ids'));
        //获取传递过来的值并删除
		$res =Feedback::destroy($request -> post('ids'));
		if($res){
			$this -> success("删除成功!",'User/my_feedback');
		}else{
			$this -> error("删除失败!");
		}
    }
    //用户中心我的留言修改
    public function feedback_modify(Request $request)
    {
        //判断当前会员是否登录
        $user = $this -> user_info();
        //接收前台传过来的content信息
        $data=$request->param('content');
        //接收前台传过来的id信息
        $id=$request->param('id');
        //查询屏蔽词
        $shile=Db::name('system')->where('id',1)->field('shielding,feedback_close')->find();
        //调用过滤屏蔽词函数
        $result = $this->sensitive($shile, $data);
        //判断评论开关是否为开
        if($shile['feedback_close']==0){
            if($result['status']==0){
        //将过滤后的评论内容更新到数据表中
        $res=Db::name('feedback')->where('id',$id)->update(['content'=>$result['log']]);
            if($res){
                $this->success("修改成功!");
            }else{
                $this->error("修改失败!");
            }
        }else{
            if($result['status']==1){
                //返回屏蔽词信息
                $this->error("存在敏感词:{$result['message']},请修改!");
            }
        }
        }else{
            $this->error("留言已关闭!");
        }
        
        
        
    }
     
    //用户中心投稿
    public function tg(Request $request)
    {   
        //判断当前会员是否登录
        $user = $this -> user_info();
        //获取登录会员的ID
	    $useid=Session::get('Member.id');
	    //查询该会员在数据表的所有信息
	    $usename = Member::where('id',$useid)->find();
		//输出分类以及栏目
		$type = Db::name('type')->select();
		$menu = Hmenu::where(['type'=>0,'status'=>1,'pid'=>0])->select();
		foreach($menu as $key=>$val){
			$two = Hmenu::where(['pid'=>$val['id'],'status'=>'1'])->select()->toArray();
			$menu[$key]['secontit'] = $two;
		}
		//传递给模板
		$this -> view -> assign(['member'=>$usename,'menu'=>$menu,'type'=>$type]);
        return $this-> fetch('/user/tg');
    }
    
    //在线投稿执行添加到数据表
	public function tg_new(Request $request)
	{
	    //判断当前会员是否登录
        $user = $this -> user_info();
        //获取登录会员的ID
	    $useid=Session::get('Member.id');
	    //查询该会员在数据表的所有信息
	    $usename = Member::where('id',$useid)->find();
	    //接收前台传过来的所有信息
         $data = $request->post();
		            //图片验证码
		            if(!captcha_check($data['verif'])) {
		                // 校验失败
		                $this -> error("验证码不正确!");
		            }
		 //获取当前时间
		$data['create_time'] = time();
		//将前台发送过来的资讯赋予作者，作者为当前会员
		$data['author'] = $usename['name'];
		//把文章状态设置为待审核
		$data['status'] = 3;
		//给资讯赋值标志位，这里的资讯为非管理员发布
		$data['isadmin'] = 0;
		$orders = Article::all();
		  //自动排序
		 $order="1";
		 if($orders != NULL | $orders != "")
		 foreach($orders as $key=>$val){
		 	$order = $val["orders"]+1;
		 }
		 //文章排序
		 $data['orders'] = $order;
	    //查询系统配置表在线投稿开关状态
		$tg_close=Db::name('system')->field('tg_close')->find();
		//分离验证码字段值$data['verif']
		$d=['title'=>$data['articleTitle'],'mid'=>$data['articleClassify'],'type'=>$data['type'],'editor'=>$data['articleEditor'],'create_time'=>$data['create_time'],'author'=>$data['author'],'status'=>$data['status'],'isadmin'=>$data['isadmin'],'uid'=>$useid,'orders'=>$data['orders']];
		//判断投稿开关是否打开
		if($tg_close['tg_close']==0){
		//数据库添加操作
		$res = Article::insert($d);
		//取得新增数据id
		$uid = Db::name('article')->getLastInsID();
		if($res){
		    //建立新增文章附属表
		    Db::name('article_data')->insert(['aid'=>$uid]);
			$this -> success("投稿成功!",'User/tg_record');
		}else{
			$this -> error("投稿失败!");
		}
		}else{
		    $this->error("投稿已关闭!");
		}
		
	}
    
    //用户中心投稿记录
    public function tg_record()
    {
        //判断当前会员是否登录
        $user = $this -> user_info();
		 //获取登录会员的ID
	    $useid=Session::get('Member.id');
	    //查询该会员在数据表的所有信息
	    $usename = Member::where('id',$useid)->find();
	    //查询当前会员发布的所有文章
	    //$all=Article::where('uid',$useid)->select();
	    $all=Db::name('article')->alias('a')->join('article_data b','a.id = b.aid')->fieldRaw('a.*,b.*')->where(['a.uid'=>$useid,'a.isadmin'=>"0"])->select()->toArray();
	    foreach($all as $key=>$val){
			$name=Db::name('hmenu')->where('id',$val['mid'])->find();
			$all[$key]['mid'] = $name['title'];
		}
		//取出数组更新时间字段作为排序标志
        $or = array_column($all, 'create_time');
        //按照取出字段排序
        array_multisort($or, SORT_DESC, $all);
		//统计当前会员有多少文章
		$tg_sum=count($all);
		$this -> view -> assign(['member'=>$usename,'article'=>$all,'sum'=>$tg_sum]);
        return $this-> fetch('/user/tg_record');
    }
    
    //会员中心编辑投稿文章渲染
    public function tg_edit($id)
    {
        //判断当前会员是否登录
        $user = $this -> user_info();
        //获取登录会员的ID
	    $useid=Session::get('Member.id');
	    //查询该会员在数据表的所有信息
	    $usename = Member::where('id',$useid)->find();
        //输出当前ID的所有文章信息
		$article = Article::get($id);
		//输出分类以及栏目
		$type = Db::name('type')->select();
		$menu = Hmenu::where([['pid','=',0],['id','>=',2]])->select();
		foreach($menu as $key=>$val){
			$two = Hmenu::where(['pid'=>$val['id'],'status'=>'1'])->select()->toArray();
			$menu[$key]['secontit'] = $two;
		}
		$article['type'] = explode(',',$article['type']);
		$this -> view ->assign(['member'=>$usename,'article'=>$article,'type'=>$type,'menu'=>$menu]);
		
		return $this -> view ->fetch('/user/tg_edit');
    }
    
    //会员中心编辑文章提交表单
    public function tg_edits(Request $request)
    {
        //判断当前会员是否登录
        $user = $this -> user_info();
        //获取登录会员的ID
	    $useid=Session::get('Member.id');
	    //查询该会员在数据表的所有信息
	    $usename = Member::where('id',$useid)->find();
	    //接收前台传过来的所有信息
         $data = $request->param();
		            //图片验证码
		            if(!captcha_check($data['verif'])) {
		                // 校验失败
		                $this -> error("验证码不正确!");
		            }
		 //获取当前时间
		$data['update_time'] = time();
		//把文章状态设置为待审核
		$data['status'] = 3;
		//将前台发送过来的资讯赋予作者，作者为当前会员
		$data['author'] = $usename['name'];
	    //查询系统配置表在线投稿开关状态
		$tg_close=Db::name('system')->field('tg_close')->find();
		//分离验证码字段值$data['verif']
		$d=['title'=>$data['articleTitle'],'mid'=>$data['articleClassify'],'type'=>$data['type'],'keyword'=>$data['articleKeywords'],'tag'=>$data['tag'],'source'=>$data['articleSource'],'editor'=>$data['articleEditor'],'update_time'=>$data['update_time'],'author'=>$data['author'],'status'=>$data['status']];
		//判断投稿开关是否打开
		if($tg_close['tg_close']==0){
		    //数据库添加操作
		//$res = Article::update($d);
		$res =Db::name('article')->where('id',$data['id'])->update($d);
		if($res){
			$this -> success("修改成功!",'User/tg_record');
		}else{
			$this -> error("修改失败!");
		}
		}else{
		    $this->error("投稿已关闭!");
		}
		
        
    }
    
    //投稿列表删除文章操作
    public function tg_del(Request $request)
    {
        //判断当前会员是否登录
        $user = $this -> user_info();
        //获取传递过来的id
		$id = trim($request -> param('ids'));
        //获取传递过来的值并删除
		$res =Article::destroy($id);
		if($res){
		    //删除文章附属表
		    Db::name('article_data')->where('aid',$id)
	        ->useSoftDelete('delete_time',time())
            ->delete();
			$this -> success("删除成功!");
		}else{
			$this -> error("删除失败!");
		}
    }
        //用户中心修改资料前验证码审核验证
    	public function sendCode_means(Request $request){
    	//判断当前会员是否登录
        $user = $this -> user_info();
		//获取当前登录会员的ID
	    $useid=Session::get('Member.id');
	    //查询该会员在数据表的所有信息
	    $usename = Member::where('id',$useid)->find();
	    //查询当前会员的注册邮箱赋值给$emails
	    $emails=$usename['email'];
		$das = $request->param('verif');
		            //图片验证码
		            if(!captcha_check($das)) {
		                // 校验失败
		                $this -> error("验证码不正确!");
		            }
		//查询邮箱配置中email,emailpaswsd,smtp,sll,ceemail字段的信息 
		$email=Db::name('email')->where('id',1)->field('email,emailpaswsd,smtp,sll,ceemail')->find();
		//查询网站设置里面得网站名称和LOGO
		$emname = Db::name('system')->where('id',1)->field('title,logo')->find();
		//获取当前服务器域名
		$host = $request->domain();
		//调用随机数函数
		$yanzhen=$this->codestr();
		$title = "资料修改验证码";
		$content = "<div style='padding:30px; background:#F5F6F7;'><div style='font-size:14px; border-top:3px solid #2254f4; background:#fff; color:#333; padding:20px 40px; border-radius:4px; width:542px; margin:40px auto;'><div style='text-align:right; margin-bottom:10px;'><img src='".$host.$emname['logo']."' style='display:inline-block;height:40px;width:40px;'></div><div> </div><div>您正在<b style='color:#333;'>".$emname['title']."</b>会员中心修改资料，为验证此电子邮箱属于您，请您在资料修改网页中输入下方授权验证码：</div><div style='font-size:24px; color:#2254f4; margin-bottom:20px; margin-top:10px;'><strong>".$yanzhen."</strong></div><div style='margin-top:10px;'>验证码有效期为<b style='color:#333;'>5分钟</b>，如非本人操作，请忽略该邮件。</div><div style='font-size:12px;color:#999;'>提示：只有在电子邮箱验证成功后资料才能被修改。</div><div style='color:#999;text-align:right;line-height:30px;margin-top:40px;padding-top:10px;border-top:1px solid #eee;font-size:12px;'>".$emname['title'].".</div></div></div>";
		if(! $email){
			 $this -> error("没有邮箱配置信息!");
		}
		if(! $emname){
		    $this -> error("没有网站详细信息!");
		}
		$res = sendEmail($email['email'],$email['emailpaswsd'],$email['smtp'],$email['sll'],$emname['title'],$title,$content,$emails);
		 if($res){
		     $time=time();
		     Session::set('codestr',$yanzhen);
		     Session::set('time',$time);
		     $this->success("发送成功!");
		 }else{
		     $this->error("发送失败!");
		 }
	}
	
	//用户中心修改密码前验证码审核验证
	public function sendCode_pass(Request $request){
    	//判断当前会员是否登录
        $user = $this -> user_info();
		//获取当前登录会员的ID
	    $useid=Session::get('Member.id');
	    //查询该会员在数据表的所有信息
	    $usename = Member::where('id',$useid)->find();
	    ////接收前台传过来的原密码信息
	    $oldpass = $request->param('oldpass');
	    //验证原密码是否正确
	    if(md5($oldpass)!==$usename['password']){
	        $this -> error("原密码不正确!");
	    }
	    //查询当前会员的注册邮箱赋值给$emails
	    $emails=$usename['email'];
	    //接收前台传过来的verif信息
		$das = $request->param('verif');
		            //图片验证码
		            if(!captcha_check($das)) {
		                // 校验失败
		                $this -> error("验证码不正确!");
		            }
		//查询邮箱配置中email,emailpaswsd,smtp,sll,ceemail字段的信息 
		$email=Db::name('email')->where('id',1)->field('email,emailpaswsd,smtp,sll,ceemail')->find();
		//查询网站设置里面得网站名称和LOGO
		$emname = Db::name('system')->where('id',1)->field('title,logo')->find();
		//获取当前服务器域名
		$host = $request->domain();
		//调用随机数函数
		$yanzhen=$this->codestr();
		$title = "密码修改验证码";
		$content = "<div style='padding:30px; background:#F5F6F7;'><div style='font-size:14px; border-top:3px solid #2254f4; background:#fff; color:#333; padding:20px 40px; border-radius:4px; width:542px; margin:40px auto;'><div style='text-align:right; margin-bottom:10px;'><img src='".$host.$emname['logo']."' style='display:inline-block;height:40px;width:40px;'></div><div> </div><div>您正在<b style='color:#333;'>".$emname['title']."</b>会员中心修改密码，为验证此电子邮箱属于您，请您在密码修改网页中输入下方授权验证码：</div><div style='font-size:24px; color:#2254f4; margin-bottom:20px; margin-top:10px;'><strong>".$yanzhen."</strong></div><div style='margin-top:10px;'>验证码有效期为<b style='color:#333;'>5分钟</b>，如非本人操作，请忽略该邮件。</div><div style='font-size:12px;color:#999;'>提示：只有在电子邮箱验证成功后密码才能被修改。</div><div style='color:#999;text-align:right;line-height:30px;margin-top:40px;padding-top:10px;border-top:1px solid #eee;font-size:12px;'>".$emname['title'].".</div></div></div>";
		if(! $email){
			 $this -> error("没有邮箱配置信息!");
		}
		if(! $emname){
		    $this -> error("没有网站详细信息!");
		}
		$res = sendEmail($email['email'],$email['emailpaswsd'],$email['smtp'],$email['sll'],$emname['title'],$title,$content,$emails);
		 if($res){
		     //获取当前时间
		     $time=time();
		     //设置session值，以便于页面提交判断邮箱验证码是否正确
		     Session::set('codestr',$yanzhen);
		     //设置时间session值，以便于对邮箱验证码设置一个有效时间
		     Session::set('time',$time);
		     $this->success("发送成功!");
		 }else{
		     $this->error("发送失败!");
		 }
	}
	
	
    //用户中心修改资料
    public function update_means(Request $request)
    {
        //判断当前会员是否登录
        $user = $this -> user_info();
		 //获取登录会员的ID
	    $useid=Session::get('Member.id');
	    //查询该会员在数据表的所有信息
	    $usename = Member::where('id',$useid)->find();
		//传递给模板
		$this -> view -> assign(['member'=>$usename]);
        return $this-> fetch('/user/update_means');
    }
    //用户中心修改资料提交
    public function update_meanss(Request $request)
    {
        //判断当前会员是否登录
        $user = $this -> user_info();
        //判断会员修改资料验证码
        if(request()->isPost()){
                    //接收前台传过来的email_code信息
		            $da = $request->post('email_code');
		            //获取名为codestr的session值
		            $code=Session::get('codestr');
		            //邮箱验证码
		            if($da!==$code) {
		                // 校验失败
		                $this -> error("邮箱验证码不正确!");
		            }
		  //获取登录会员的ID
	    $useid=Session::get('Member.id');
	    //查询该会员在数据表的所有信息
	    $usename = Member::where('id',$useid)->find();          
		 //获取当前时间           
		 $times=time();
		 //获取User控制器下sendCode_means的Session值
		 $timess=Session::get('time');
		 //判断验证码是否大于300秒，大于则验证码过期
		 if($times-$timess>300){
		     Session::delete('codestr');
             $this -> error("邮箱验证码已过期!");
		 }else{
		     //接收前台传过来的所有信息
		     $data=$request->param();
		     //判断前台传过来的手机是否重复
		     $mo=Db::name('member')->where('phone',$data['phone'])->find();
		     //判断前台传过来的邮箱是否重复
		     $em=Db::name('member')->where('email',$data['email'])->find();
             //判断前台传过来的手机是否重复，当前会员手机号码除外
		     if($mo!==null&&(int)$mo['phone']!==$usename['phone']){
		         $this -> error("手机号码已存在!");
		     }else{
		         //判断前台传过来的邮箱是否重复，当前会员邮箱除外
		         if($em!==null&&$em['email']!==$usename['email']){
		             $this -> error("邮箱已存在!");
		         }else{
		                //字段分离
		                $t =['sex'=>$data['sex'],'intro'=>$data['intro'],'phone'=>$data['phone'],'email'=>$data['email']];
		                //将字段值更新到数据表
		                $res=Db::name('member')->where('id', $useid)->update($t);
		             if($res){
		                 $this -> success("修改成功!");
		             }else{
		                  $this -> error("修改失败!");
		             }
		             
		         }
		     }
		 }
        }
    }
    //用户中心密码修改
    public function update_passwd()
    {
        //判断当前会员是否登录
        $user = $this -> user_info();
		 //获取登录会员的ID
	    $useid=Session::get('Member.id');
	    //查询该会员在数据表的所有信息
	    $usename = Member::where('id',$useid)->find();
		//传递给模板
		$this -> view -> assign(['member'=>$usename]);
        return $this-> fetch('/user/update_passwd');
    }
    //用户中心密码修改提交表单到数据库
    public function update_passwds(Request $request)
    {
        //判断当前会员是否登录
        $user = $this -> user_info();
        //判断会员修改资料验证码
        if(request()->isPost()){
                    //接收前台传过来的email_code信息
		            $da = $request->post('email_code');
		            //获取名为codestr的session值
		            $code=Session::get('codestr');
		            //邮箱验证码
		            if($da!==$code) {
		                // 校验失败
		                $this -> error("邮箱验证码不正确!");
		            }
		 //获取登录会员的ID
	    $useid=Session::get('Member.id');          
	     //获取当前时间           
		 $times=time();
		 //获取User控制器下sendCode_means的Session值
		 $timess=Session::get('time');
		 //判断验证码是否大于300秒，大于则验证码过期
		 if($times-$timess>300){
		     Session::delete('codestr');
             $this -> error("邮箱验证码已过期!");
		 }else{
		     //接收前台传过来的密码password信息
		     $passwd=md5($request->param('password'));
		      //将字段值更新到数据表
		      $res=Db::name('member')->where('id', $useid)->update(['password'=>$passwd]);
		      if($res){
		          Session::delete('Member');
		          $this->success("修改成功!");
		      }else{
		          $this->error("修改失败!");
		      }
		 }
        }
    }
    //用户中心个人主页
    public function my_home($id)
    {
	    //查询该会员在数据表的所有信息
	    $usename = Member::where('id',$id)->find();
	    //查询当前会员发布的文章信息
	    $art=Article::where(['uid'=>$id,'isadmin'=>"0"])->select();
	    //个人主页访问量自增1
	    $usename -> setInc('home_count');
	     //将字段fans（字符串类型）分割数组形式并统计其元素个数
	    $fans=count(explode(",", $usename['fans']));
		//传递给模板
		$this -> view -> assign(['member'=>$usename,'fans_sum'=>$fans,'article'=>$art]);
        return $this-> fetch('/user/my_home');
    }
    //用户中心我的关注
    public function my_follow()
    {
        //判断当前会员是否登录
        $user = $this -> user_info();
		 //获取登录会员的ID
	    $useid=Session::get('Member.id');
	    //查询该会员在数据表的所有信息
	    $usename = Member::where('id',$useid)->find();
	    //将字段attention（字符串类型）分割数组形式
	    $uid=explode(",", $usename['attention']);
	    //根据数组$uid查询数据表中所有会员信息
	    $use = Member::where('id','in',$uid)->order('create_time','desc')->paginate();
		//传递给模板
		$this -> view -> assign(['member'=>$usename,'user'=>$use]);
        return $this-> fetch('/user/my_follow');
    }
    //用户中心我的关注列表取消关注提交表单
    public function cancel_follow(Request $request)
    {
        //判断当前会员是否登录
        $user = $this -> user_info();
        //获取登录会员的ID
	    $useid=Session::get('Member.id');
        //接收前台传过来的id
        $id=$request->param('ids');
        //查询当前会员的所有字段信息
        $data=Member::where('id',$useid)->find();
        //判断数据表attention字段中是否只有一个字符
        if(count(str_split($data['attention']))==1){
            $res=Db::name('member')->where('id',$useid)->update(['attention'=>NULL]);
        }else{
            //将字段attention（字符串类型）分割数组形式
	        $arr=explode(",", $data['attention']);
            $str = array_merge(array_diff($arr, array($id)));
            //将数组转换为字符串,并与逗号隔开
            $trs=implode(",",$str);
            $res=Db::name('member')->where('id',$useid)->update(['attention'=>$trs]);
        }
        if($res)
        {
            $nofid=Db::name('member')->where('id',$id)->find();
            //将event写入fans表
            Db::name('fans')->insert(['fansid'=>$useid,'not_uid'=>$id,'event'=>"【{$data['name']}】取关了【{$nofid['name']}】",'log_ip'=>$request->ip(),'create_time'=>time()]);
            $this->success('已取消!');
        }else {
            $this->error('取消失败!');
        }
    }
    //用户中心我的粉丝
    public function my_fans()
    {
        //判断当前会员是否登录
        $user = $this -> user_info();
		 //获取登录会员的ID
	    $useid=Session::get('Member.id');
	    //查询该会员在数据表的所有信息
	    $usename = Member::where('id',$useid)->find();
	    //将字段fans（字符串类型）分割数组形式
	    $uid=explode(",", $usename['fans']);
	    //根据数组$uid查询数据表中所有会员信息
	    $use = Member::where('id','in',$uid)->order('create_time','desc')->paginate();
		//传递给模板
		$this -> view -> assign(['member'=>$usename,'user'=>$use]);
        return $this-> fetch('/user/my_fans');
    }
    //用户中心我的粉丝列表点击关注提交表单
    public function like_fans(Request $request)
    {
        //判断当前会员是否登录
        $user = $this -> user_info();
        //获取登录会员的ID
	    $useid=Session::get('Member.id');
        //接收前台传过来的id
        $id=$request->param('ids');
        //查询当前会员的所有字段信息
        $data=Member::where('id',$useid)->find();
        if($data['attention']==NULL){
            //将$id更新到数据库中
            $res=Db::name('member')->where('id',$useid)->update(['attention'=>$id]);
        }else{
            //var_dump(array_search($id ,str_split($data['attention'])));
            if(array_search($id ,str_split($data['attention']))!==false){
                $this->error('请勿重复关注!');
            }else{
                if($id==$useid){
                    $this->error('不能关注自己!');
                }else{
                    if(count(str_split($data['attention']))==1){
            //将字符串转为数组
            $split=str_split($data['attention']);
            //在数组后面添加$id值,得到数组$str
            array_push($split,$id);
            //将数组转换为字符串,并与逗号隔开
            $trs=implode(",",$split);
            //将$str更新到数据库中
            $res=Db::name('member')->where('id',$useid)->update(['attention'=>$trs]);
            }else{
            $da=explode(",",$data['attention']);
            //在数组后面添加$id值,得到数组$str
            array_push($da,$id);
            //将数组转换为字符串,并与逗号隔开
            $trs=implode(",",$da);
            //将$id更新到数据库中
            $res=Db::name('member')->where('id',$useid)->update(['attention'=>$trs]);
                    }
                }
            }
        }
        if($res)
        {
            $fid=Db::name('member')->where('id',$id)->find();
            //将event写入fans表
            Db::name('fans')->insert(['fansid'=>$useid,'uid'=>$id,'event'=>"【{$data['name']}】关注了【{$fid['name']}】",'log_ip'=>$request->ip(),'create_time'=>time()]);
            $this->success('已关注!');
        }else {
            $this->error('关注失败!');
        }
    }
    //我的动态
    public function my_dynamic()
    {
        //判断当前会员是否登录
        $user = $this -> user_info();
        //获取登录会员的ID
	    $id=Session::get('Member.id');
	    //查询当前会员的所有字段信息
        $data=Member::where('id',$id)->find();
        //我关注了谁 时间存放 $fans
        $fans=Db::name('fans')->alias('a')->join('member b','a.uid = b.id')->fieldRaw('a.*,b.name')->where('fansid',$id)->select()->toArray();
        //我取关了谁
        $notfans=Db::name('fans')->alias('a')->join('member b','a.not_uid = b.id')->fieldRaw('a.*,b.name')->where('fansid',$id)->select()->toArray();
        //谁关注了我
        $fan=Db::name('fans')->alias('a')->join('member b','a.fansid = b.id')->fieldRaw('a.*,b.name')->where('uid',$id)->select()->toArray();
        //合并查询信息
        $tt = array_merge($fans,$fan,$notfans);
        //取出数组更新时间字段作为排序标志
        $or = array_column($tt, 'create_time');
        //按照取出字段排序
        array_multisort($or, SORT_DESC, $tt);
        //统计与我相关共有多少条数据
        $sum=count($tt);
        for($i=0;$i<$sum;$i++)
        {
            //获取要替换的内容
            $string=$tt[$i]["event"];
            //将字符串中$data['name']的值替换为“我”
            $tt[$i]["event"]=str_replace($data['name'],"我",$string);
        }
	    //传递给模板
		$this -> view -> assign(['member'=>$data,'tt'=>$tt,'sum'=>$sum]);
        return $this-> fetch('/user/my_dynamic');
    }
    //用户退出
    public function loginout(Request $request)
    {
        //判断当前会员是否登录
        $user = $this -> user_info();
        //接收前台传过来的会员account字段信息
        $na = $request->param('account');
        $data = [];
        //获取当前客户端的IP地址并赋值给$data变量
		$data['outip'] = $request->ip();
		//获取当前时间并赋值给$data变量
		$data['outtime'] = time();
		//将$data更新到会员数据表
        Member::where('account',$na)->update($data);
        //删除当前Session
        Session::delete('Member');
        //返回退出信息
	    $this -> success('已安全退出','index/Index/login');
        
    }
    
}