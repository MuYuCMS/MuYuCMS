<?php
namespace app\index\controller;
use app\index\controller\Base;
use think\Request;
use app\index\model\Member;
use app\index\model\Comment;
use app\index\model\Feedback;
use app\index\model\Category;
use think\facade\Session;
use app\index\model\Email;
use app\index\model\MemberPaylog;
use app\index\model\MemberBuylog;
use think\Db;

class User extends Base
{
    protected $config;
    protected function initialize()
    {
        parent::initialize();
        $this->getuserst();
        $this->config = $this->getsystems();
    }

    //用户中心首页
    public function index()
    {
        $att = 0;
        $fans = 0;
		//获取登录会员的ID
	    $useid=Session::get('Member.id');
	    //查询该会员在数据表的所有信息
	    $usename = Db::name("member")->find($useid);
	    //统计会员评论总数
	    $comid=Comment::where('uid',$useid)->count();
	    //统计会员留意总数
	    $feeid=Feedback::where('uid',$useid)->count();
	    //统计会员投稿次数
	    $tab = Db::name("model")->field("tablename")->select();
	    $artid="0";
	    foreach($tab as $me){
	    $artid=Db::name($me['tablename'])->where(['uid'=>$useid,'isadmin'=>"0"])->count();
	    }
	    if($usename['attention'] !== NULL){
	        //将字段attention（字符串类型）分割数组形式并统计其元素个数
	        $att=count(explode(",", $usename['attention']));
	    }
	    if($usename['fans'] !== NULL){
    	    //将字段fans（字符串类型）分割数组形式并统计其元素个数
    	    $fans=count(explode(",", $usename['fans']));
	    }
	    
		//传递给模板
		$this -> view -> assign([
		    'member'=>$usename,
		    'comment_sum'=>$comid,
		    'feedback_sum'=>$feeid,
		    'article_sum'=>$artid,
		    'attention_sum'=>$att,
		    'fans_sum'=>$fans
		    ]);
        return $this-> fetch('/member_temp/'.$this->config["member_temp"].'/index');
    }
    
    //手机邮箱绑定提交
    public function smsBind(Request $request)
    {
        if(request()->isPost()){
            //获取请求的ID
            $id = Session::get('Member.id');
            $data = $request->post();
            if($data['type'] == 'email'){
                $code = Session::get('code');
                //判断验证码是否存在
                $info = Db::name('Member')->where('email',$code['email'])->find();
                if($info){
                    $this->error('邮箱已存在!');
                }else{
                    if($code['yanzhen'] !== $data['code']){
                        $this->error('验证码错误!');
                    }else{
                        //判断验证码是否过期
                        if(time() - $code['time'] >=300){
                            Session::delete('code');
                            $this->error('验证码已过期!');
                        }else{
                            $res = Db::name('Member')->where('id',$id)->update(['email'=>$code['email']]);
                        }
                    }
                }
            }elseif($data['type'] == 'phone'){
                $code = Session::get('code');
                //判断验证码是否存在
                $info = Db::name('Member')->where('phone',$code['phone'])->find();
                if($info){
                    $this->error('手机号码已存在!');
                }else{
                    if($code['yanzhen'] !== $data['code']){
                        $this->error('验证码错误!');
                    }else{
                        //判断验证码是否过期
                        if(time() - $code['time'] >=300){
                            Session::delete('code');
                            $this->error('验证码已过期!');
                        }else{
                            $res = Db::name('Member')->where('id',$id)->update(['phone'=>$code['phone']]);
                        }
                    }
                }
            }else{
                $this->error('非法请求');
            }
            
            if($res){
                Session::delete('code');
                $this->success('绑定成功!');
            }else{
                Session::delete('code');
                $this->error('绑定失败!');
            }
        }
    }
    
    //邮箱绑定发送验证码
    public function sendCodeEmail(Request $request)
	{
	    if(request()->isPost()){
    		//接收邮箱
    		$data = $request -> post();
    		//查询邮箱配置中email,emailpaswsd,smtp,slll字段的信息 
    		$email = Db::name('sms')->where('id',1)->field('email,emailpaswsd,smtp,sll')->find();
    		//查询网站设置里面得网站名称和LOGO
    		$emname = Db::name('system')->where('id',1)->field('title,logo')->find();
    		//获取当前服务器域名
    		$host = $request->domain();
    		//调用随机数函数
    		$yanzhen=$this->codestr();
    		$title = "邮箱绑定验证码";
    		$content = "<div style='padding:30px; background:#F5F6F7;'><div style='font-size:14px; border-top:3px solid #2254f4; background:#fff; color:#333; padding:20px 40px; border-radius:4px; width:542px; margin:40px auto;'><div style='text-align:right; margin-bottom:10px;'><img src='".$host.$emname['logo']."' style='display:inline-block;height:40px;width:40px;'></div><div> </div><div>您正在<b style='color:#333;'>".$emname['title']."</b>会员中心进行邮箱绑定，为验证此电子邮箱属于您，请您在网页中输入下方授权验证码：</div><div style='font-size:24px; color:#2254f4; margin-bottom:20px; margin-top:10px;'><strong>".$yanzhen."</strong></div><div style='margin-top:10px;'>验证码有效期为<b style='color:#333;'>5分钟</b>，如非本人操作，请忽略该邮件。</div><div style='font-size:12px;color:#999;'>提示：只有在电子邮箱验证成功后才能被绑定。</div><div style='color:#999;text-align:right;line-height:30px;margin-top:40px;padding-top:10px;border-top:1px solid #eee;font-size:12px;'>".$emname['title'].".</div></div></div>";
    		if(! $email){
    			 $this -> error("没有邮箱配置信息!");
    		}
    		if(! $emname){
    		    $this -> error("没有网站详细信息!");
    		}
    		$res = sendEmail($email['email'],$email['emailpaswsd'],$email['smtp'],$email['sll'],$emname['title'],$title,$content,$data['email']);
    		 if($res){
    		     $code['email'] = $data['email'];
    		     $code['yanzhen'] = $yanzhen;
    		     //设置时间是验证验证码是否过期
    		     $code['time'] = time();
    		     //设置session值，以便于页面提交判断邮箱验证码是否正确
    		     Session::set('code',$code);
    		     $this->success("发送成功!");
    		 }else{
    		     $this->error("发送失败!");
    		 }
	    }
	}
	
	//手机号码绑定发送验证码
	public function sendCodePhone(Request $request)
	{
	    if(request()->isPost()){
	        //接收手机号码
	        $data = $request->post();
	        //查询网站信息
	        $system = Db::name('system')->where('id',1)->field('title')->find();
	        //查询发信配置信息
	        $sms = Db::name('sms')->where('id',1)->find();
            //调用随机验证码方法
            $yanzhen = $this->codestr();
            //验证码有效时间(单位：分钟)
            $time = 5;
	        $content = "【{$system['title']}】您正在会员中心绑定手机，验证码是{$yanzhen}，在{$time}分钟内有效。";
            $res = sendSms($sms['smsbao_account'],$sms['smsbao_password'],$content,$data['phone']);
            if ($res == 0){
                //设置session值
                $code['yanzhen'] = $yanzhen;
                $code['time'] = time();
                $code['phone'] = $data['phone'];
                Session::set('code',$code);
                $this->success("短信发送成功！");
            }else{
                $this->error("发送失败！");
            }
	    }
	}
    
    
    //登录解除绑定
    public function removeOauth(Request $request)
    {
        if(request()->isAjax()){
            //获取请求的ID
            $id = Session::get('Member.id');
            //接收数据
            $data = $request->param();
            if($data['type'] == "phone" || $data['type'] == "email"){
                $code = Session::get('code');
                //判断验证码是否错误
                if($code['yanzhen'] !== $data['code']){
                    $this->error('验证码错误!');
                }else{
                    //判断验证码是否过期
                    if(time() - $code['time'] >= 300){
                        Session::delete('code');
                        $this->error('验证码已过期!');
                    }else{
                        //解除操作
                        $res = Db::name('member')->where('id',$id)->update([$data['type']=>NULL]);
                    }
                }
            }else{
                //解除操作
                $res = Db::name('member')->where('id',$id)->update([$data['type'].'_openid'=>NULL]);
            }
            if($res){
                Session::delete('code');
                $this->success('解除成功!');
            }else{
                $this->error('解除失败!');
            }
        }
    }
    
    //邮箱解除绑定发送验证码
    public function emailRemove(Request $request)
	{
	    if(request()->isPost()){
    		//查询绑定的邮箱
    		$info = Db::name('member')->where('id',Session::get('Member.id'))->field('email')->find();
    		//查询邮箱配置中email,emailpaswsd,smtp,slll字段的信息 
    		$email = Db::name('sms')->where('id',1)->field('email,emailpaswsd,smtp,sll')->find();
    		//查询网站设置里面得网站名称和LOGO
    		$emname = Db::name('system')->where('id',1)->field('title,logo')->find();
    		//获取当前服务器域名
    		$host = $request->domain();
    		//调用随机数函数
    		$yanzhen=$this->codestr();
    		$title = "邮箱解除验证码";
    		$content = "<div style='padding:30px; background:#F5F6F7;'><div style='font-size:14px; border-top:3px solid #2254f4; background:#fff; color:#333; padding:20px 40px; border-radius:4px; width:542px; margin:40px auto;'><div style='text-align:right; margin-bottom:10px;'><img src='".$host.$emname['logo']."' style='display:inline-block;height:40px;width:40px;'></div><div> </div><div>您正在<b style='color:#333;'>".$emname['title']."</b>会员中心解除邮箱绑定，为验证此电子邮箱属于您，请您在网页中输入下方授权验证码：</div><div style='font-size:24px; color:#2254f4; margin-bottom:20px; margin-top:10px;'><strong>".$yanzhen."</strong></div><div style='margin-top:10px;'>验证码有效期为<b style='color:#333;'>5分钟</b>，如非本人操作，请忽略该邮件。</div><div style='font-size:12px;color:#999;'>提示：只有在电子邮箱验证成功后才能被解除绑定。</div><div style='color:#999;text-align:right;line-height:30px;margin-top:40px;padding-top:10px;border-top:1px solid #eee;font-size:12px;'>".$emname['title'].".</div></div></div>";
    		if(! $email){
    			 $this -> error("没有邮箱配置信息!");
    		}
    		if(! $emname){
    		    $this -> error("没有网站详细信息!");
    		}
    		$res = sendEmail($email['email'],$email['emailpaswsd'],$email['smtp'],$email['sll'],$emname['title'],$title,$content,$info['email']);
    		 if($res){
    		     $code['yanzhen'] = $yanzhen;
    		     //设置时间是验证验证码是否过期
    		     $code['time'] = time();
    		     //设置session值，以便于页面提交判断邮箱验证码是否正确
    		     Session::set('code',$code);
    		     $this->success("发送成功!");
    		 }else{
    		     $this->error("发送失败!");
    		 }
	    }
	}
	
    //手机解除绑定发送验证码
    public function phoneRemove(Request $request)
    {
		//查询绑定的手机
		$info = Db::name('member')->where('id',Session::get('Member.id'))->field('phone')->find();
		//查询网站标题
		$system = Db::name('system')->where('id',1)->field('title')->find();
        //查询发信配置信息
        $sms = Db::name('sms')->where('id',1)->find();
        //调用随机验证码方法
        $yanzhen = $this->codestr();
        //验证码有效时间(单位：分钟)
        $time = 5;
        $content = "【{$system['title']}】您正在会员中心解除手机绑定，验证码是{$yanzhen}，在{$time}分钟内有效。";
        $res = sendSms($sms['smsbao_account'],$sms['smsbao_password'],$content,$info['phone']);
        if ($res == 0){
            //设置session值
            $code['yanzhen'] = $yanzhen;
            $code['time'] = time();
            Session::set('code',$code);
            $this->success("短信发送成功！");
        }else{
            $this->error("发送失败！");
        }
    }
	
    
    //用户头像上传
	public function upload(Request $request)
	{
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
		 //获取登录会员的ID
	    $useid=Session::get('Member.id');
	    //查询该会员在数据表的所有信息
	    $usename = Member::where('id',$useid)->find();
		//查询评论表中当前会员的评论
		$comment=Comment::where('uid',$useid)->order('create_time','desc')->paginate();
		//传递给模板
		$this -> view -> assign(['member'=>$usename,'comment'=>$comment]);
        return $this-> fetch('/member_temp/'.$this->config["member_temp"].'/my_comment');
    }
    //用户中心我的评论删除
    public function comment_del(Request $request)
    {
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
		 //获取登录会员的ID
	    $useid=Session::get('Member.id');
	    //查询该会员在数据表的所有信息
	    $usename = Member::where('id',$useid)->find();
		//查询评论表中当前会员的留言
		$feedback=Feedback::where('uid',$useid)->order('create_time','desc')->paginate();
		//传递给模板
		$this -> view -> assign(['member'=>$usename,'feedback'=>$feedback]);
        return $this-> fetch('/member_temp/'.$this->config["member_temp"].'/my_feedback');
    }
    //用户中心留言删除
    public function feedback_del(Request $request)
    {
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
    
    //用户中心-充值记录
    public function my_paylog(){
        //获取登入会员id
        $userid = Session::get('Member.id');
        //查询会员名称
        $username = Member::where('id',$userid)->find();
        //根据会员id查询充值记录
        $paylog = MemberPaylog::where('uid',$userid)->order('create_time','desc')->paginate();
        //赋值模板
        $this->view->assign(['member'=>$username,'paylog'=>$paylog]);
        return $this->fetch('/member_temp/'.$this->config["member_temp"].'/my_paylog');
    }
    
    //用户中心-购买记录
    public function my_buylog(){
        //获取登入会员id
        $userid = Session::get('Member.id');
        //查询会员名称
        $username = Member::where('id',$userid)->find();
        //根据会员id查询购买记录
        $buylog = MemberBuylog::where('uid',$userid)->order('create_time','desc')->paginate();
        //赋值模板
        $this->view->assign(['member'=>$username,'buylog'=>$buylog]);
        return $this->fetch('/member_temp/'.$this->config["member_temp"].'/my_buylog');
    }
     
    //用户中心投稿
    public function tg(Request $request)
    {
        if($this->config["tg_close"] !=0){
            $this->error("管理已关闭投稿!");
        }elseif(!Session::has('Member')){
            if($this->config["us_tg"] !=0){
                $this->error("管理已关闭游客投稿!");
            }
        }
        
        $data = $request->param();
        unset($data["/user/tg_html"]);
        if(empty($data) || !isset($data["modid"])){
            $setmod = Db::name("model")->where(["type"=>1,"issue"=>1,"status"=>1])->field("id,aliase")->select();
            return $this-> fetch('/member_temp/'.$this->config["member_temp"].'/tgmd',["setmod"=>$setmod]);
        }
        $tgname = Db::name("model")->find($data['modid']);
        if($tgname['status'] != 1){
            $this->error("当前模型已关闭");
        }elseif($tgname['issue'] != 1){
            $this->error("当前模型不支持投稿");
        }elseif($tgname['type'] != 1){
            $this->error("当前模型不可操作");
        }
        
	    $field = Db::name('modfiel')->where(["modid"=>$tgname['id'],"hmst"=>1])->order("orders",'asc')->select()->toArray();
	    foreach($field as $key=>$vals){
	        if($vals['forms'] == 'select' || $vals['forms'] == 'radio' || $vals['forms'] == 'checkbox'){
		        if(!empty($vals['defaults'])){
		        $field[$key]['defaults'] = array_chunk(explode('||',$vals['defaults']),2);
		        }
	        }
	    }
	    $type = Db::name("type")->where("status",1)->field('id,title')->select();
        
		//输出分类以及栏目
		$menu = Db::name("category")->where(['modid'=>$data['modid'],'type'=>0,'status'=>1,'pid'=>0,'issue'=>1])->field("id,title")->select()->toArray();
		foreach($menu as $key=>$val){
			$two = Db::name("category")->where(['pid'=>$val['id'],'status'=>'1'])->field("id,title")->select()->toArray();
			$menu[$key]['secontit'] = $two;
		}
		//传递给模板
		$this -> view -> assign(['field'=>$field,'menu'=>$menu,'type'=>$type,'tgname'=>$tgname]);
        return $this-> fetch('/member_temp/'.$this->config["member_temp"].'/tg');
    }
    
    //在线投稿执行添加到数据表
	public function tg_new(Request $request)
	{
        //获取登录会员的ID
	    $useid=Session::get('Member.id');
	    //查询该会员在数据表的所有信息
	    $usename = [];
	    if(!empty($useid)){
	    $usename = Member::find($useid);
	    }
	    //接收前台传过来的所有信息
         $data = $request->post();
         
		            //图片验证码
		            if(!captcha_check($data['verif'])) {
		                // 校验失败
		                $this -> error("验证码不正确!");
		            }
		unset($data['verif']);            
		 //获取当前时间
		$data['create_time'] = time();
		$data['update_time'] = time();
		if(!empty($usename)){
		//当前登录会员为此内容作者
		$data['author'] = $usename['name'];
		$data['uid'] = $usename['id'];
		if($usename['super'] == 1){
		    $data['status'] = 0;
		}elseif($usename['jurisdiction'] == 1){
		    $data['status'] = 0;
		}else{
		    $data['status'] = 3;
		}
		}else{
		$data['author'] = "游客".time();
		$data['uid'] = 0;
		$data['status'] = 3;
		}
		//给资讯赋值标志位，这里的资讯为非管理员发布
		$data['isadmin'] = 0;
		$data['price']  = 1;
		$cateisu = Db::name("category")->field("issue")->find($data["mid"]);
		$tabnname = Db::name("model")->field("status,issue,tablename,type")->find($data["modid"]);
		unset($data["modid"]);
		if($tabnname['status'] != 1){
            $this->error("当前模型已关闭");
        }elseif($tabnname['issue'] != 1){
            $this->error("当前模型不支持投稿");
        }elseif($tabnname['type'] != 1){
            $this->error("当前模型不可操作");
        }elseif($cateisu["issue"] != 1){
            $this->error("当前栏目已关闭投稿");
        }
		$orders = Db::name($tabnname['tablename'])->select();
		  //自动排序
		 $order="1";
		 if($orders != NULL | $orders != "")
		 foreach($orders as $key=>$val){
		 	$order = $val["orders"] + 1;
		 }
		 //文章排序
		 $data['orders'] = $order;
	    //查询系统配置表在线投稿开关状态
		$tg_close=Db::name('system')->field('tg_close')->find();
		unset($data["/user/tg_new_html"]);
		foreach($data as $key=>$das){
		    if($key == "undefined"){
		        unset($data["undefined"]);
		    }
		}
		$metid = setconid();
	    $data['id'] = $metid['id'] + 1;
		//判断投稿开关是否打开
		if($tg_close['tg_close']==0){
		//数据库添加操作
		$res = Db::name($tabnname['tablename'])->insert($data);
		//取得新增数据id
		$uid = $data['id'];
		if($res){
		    //建立新增文章附属表
		    Db::name($tabnname['tablename'].'_data')->insert(['aid'=>$uid]);
			$this -> success("投稿成功!",'User/tg');
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
		 //获取登录会员的ID
	    $useid=Session::get('Member.id');
	    //查询当前会员发布的所有文章
	    $table = Db::name("model")->where(["type"=>1,"issue"=>1,"status"=>1])->field("id,tablename")->select();
	    $all = "";
	    foreach($table as $ta){
	    $matter=Db::name($ta["tablename"])->alias('a')->join($ta["tablename"].'_data b','a.id = b.aid')->fieldRaw('a.*,b.browse,b.likes,b.comment_t')->where(['a.uid'=>$useid,'a.isadmin'=>"0"])->select()->toArray();
	    if($matter != null){
	        $all = $matter;
	        foreach($all as $key=>$val){
			$name=Db::name('Category')->where('id',$val['mid'])->find();
			$all[$key]['mid'] = $name['title'];
			$all[$key]["modname"] = $ta["tablename"];
			
		}
	    }
	    }
	    if(!empty($all)){
		//取出数组更新时间字段作为排序标志
        $or = array_column($all, 'create_time');
        //按照取出字段排序
        array_multisort($or, SORT_DESC, $all);
	    }else{
	        $all = array();
	    }
		//统计当前会员有多少文章
		$tg_sum=count($all);
		$this -> view -> assign(['article'=>$all,'sum'=>$tg_sum]);
        return $this-> fetch('/member_temp/'.$this->config["member_temp"].'/tg_record');
    }
    
    //会员中心编辑投稿文章渲染
    public function tg_edit($id)
    {
        $table = Db::name("model")->field("id,tablename")->select();
        $article = "";
        foreach($table as $tb){
            $ardd = Db::name($tb["tablename"])->find($id);
            if($ardd != null){
            $article = Db::name($tb["tablename"])->find($id);
            $article["modname"] = $tb["tablename"];
            $article["modid"] = $tb["id"];
            }
        }
        $field = Db::name('modfiel')->where(["modid"=>$article["modid"],"hmst"=>1])->order("orders",'asc')->select()->toArray();
        foreach($field as $key=>$vals){
	        if($vals['forms'] == 'select' || $vals['forms'] == 'radio' || $vals['forms'] == 'checkbox'){
		        if(!empty($vals['defaults'])){
		        $field[$key]['defaults'] = array_chunk(explode('||',$vals['defaults']),2);
		        }
	        }
	        foreach($article as $keys=>$vac){
	                if($vals['field'] == $keys){
	                   if($vals['forms'] == 'down'){
	                   $field[$key]['value'] = array_chunk(explode(',',$article[$keys]),2);
	                   }else{
	                   $field[$key]['value'] = $article[$keys];    
	                   }
	                }
	        }
	    }
	    
		//输出分类以及栏目
		$type = Db::name('type')->select();
		$menu = Category::where([['pid','=',0],['type','=',0],['issue','=',1],["status","=",1]])->select();
		foreach($menu as $key=>$val){
			$two = Category::where(['pid'=>$val['id'],'status'=>'1'])->select()->toArray();
			$menu[$key]['secontit'] = $two;
		}
		$this -> view ->assign(['field'=>$field,'type'=>$type,'menu'=>$menu,'article'=>$article]);
		
		return $this -> view ->fetch('/member_temp/'.$this->config["member_temp"].'/tg_edit');
    }
    
    //会员中心编辑文章提交表单
    public function tg_edits(Request $request)
    {
        //接收前台传过来的所有信息
         $data = $request->post();
		            //图片验证码
		            if(!captcha_check($data['verif'])) {
		                // 校验失败
		                $this -> error("验证码不正确!");
		            }
		unset($data['verif']);
		$data['update_time'] = time();
	    //查询系统配置表在线投稿开关状态
		$tg_close=Db::name('system')->field('tg_close')->find();
		unset($data["/user/tg_edits_html"]);
		foreach($data as $key=>$das){
		    if($key == "undefined"){
		        unset($data["undefined"]);
		    }
		}
		$table = $data["modname"];
		unset($data["modname"]);
		//判断投稿开关是否打开
		if($tg_close['tg_close']==0){
		//更新操作
		$res = Db::name($table)->where(["id"=>$data['id']])->update($data);
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
        //获取传递过来的id
		$id = trim($request -> param('ids'));
        //获取传递过来的值并删除
		$res = Db::name($request->param('mod'))->where("id",$id)->update(['delete_time'=>time()]);
		if($res){
		    //删除文章附属表
		    Db::name($request -> param('mod').'_data')->where('aid',$id)
	        ->useSoftDelete('delete_time',time())
            ->delete();
			$this -> success("删除成功!");
		}else{
			$this -> error("删除失败!");
		}
    }

    //用户中心修改资料
    public function updateMeans(Request $request)
    {        
        //判断会员修改资料验证码
        if(request()->isPost()){
            //接收前台传过来的email_code信息
            $data = $request->post();
    		  //获取登录会员的ID
    	    $uid=Session::get('Member.id');
    	    //查询该会员在数据表的所有信息
    	    $usename = Member::where('id',$uid)->find();
             //将字段值更新到数据表
             $res=Db::name('member')->where('id', $uid)->strict(false)->update($data);
             if($res){
                 $this -> success("修改成功!");
             }else{
                  $this -> error("修改失败!");
             }
        }
        return $this-> fetch('/member_temp/'.$this->config["member_temp"].'/update_means');
    }
    
    //用户中心密码修改
    public function updatePassword(Request $request)
    {
        if(request()->isPost()){
            //接收前台传过来的email_code信息
            $data = $request->post();
            //获取名为codestr的session值
            $code=Session::get('codestr');
            //邮箱验证码
            if($data['email_code'] !== $code) {
                // 校验失败
                $this -> error("邮箱验证码不正确!");
            }
		    //获取登录会员的ID
	        $uid=Session::get('Member.id');  
		    //获取User控制器下sendCode_means的Session值
		    $time = Session::get('time');
		    //判断验证码是否大于300秒，大于则验证码过期
		    if(time() - $time > 300){
		     Session::delete('codestr');
             $this -> error("邮箱验证码已过期!");
		    }else{
    		     //接收前台传过来的密码password信息
    		     $passwd = password_hash($data['password'],PASSWORD_BCRYPT);
    		      //将字段值更新到数据表
    		      $res=Db::name('member')->where('id', $uid)->update(['password'=>$passwd]);
    		      if($res){
    		          Session::delete('Member');
    		          $this->success("修改成功!");
    		      }else{
    		          $this->error("修改失败!");
    		      }
		    }
        }
        return $this-> fetch('/member_temp/'.$this->config["member_temp"].'/update_passwd');
    }
    
	//用户中心修改密码前验证码审核验证
	public function sendCodePass(Request $request)
	{
	    if(request()->isPost()){
    		//获取当前登录会员的ID
    	    $uid=Session::get('Member.id');
    	    //查询该会员在数据表的所有信息
    	    $usename = Member::where('id',$uid)->find();
    	    //接收前台传过来的原密码信息
    	    $oldpass = $request->param('oldpass');
    	    //验证原密码是否正确
    	    if(!password_verify($oldpass,$usename['password'])){
    	        $this -> error("原密码不正确!");
    	    }
    		//查询邮箱配置中email,emailpaswsd,smtp,slll字段的信息 
    		$email = Db::name('sms')->where('id',1)->field('email,emailpaswsd,smtp,sll')->find();
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
    		$res = sendEmail($email['email'],$email['emailpaswsd'],$email['smtp'],$email['sll'],$emname['title'],$title,$content,$usename['email']);
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
	}
    
    //用户中心个人主页
    public function my_home(Request $request)
    {
        $id = $request->param("uid");
        $page = input("page") ? input("page") : "1";
	    //查询该会员在数据表的所有信息
	    $usename = Member::find($id);
	    //查询当前会员发布的文章信息
	    $tab = Db::name("model")->field("id,tablename")->select();
	    $art = [];
	    foreach($tab as $v){
	        $mat = Db::name($v["tablename"])->where(['uid'=>$id,'isadmin'=>"0"])->select()->toArray();
	        if(!empty($mat)){
	            $art[] = $mat;
	        }
	    }
	    if(!empty($art)){
            $art = ary3_ary2($art);
        }
        $art = arrorder($art,"create_time");
        $count = count($art);//总条数
        $start = ($page - 1)*$request->param("limit");
        $art["mat"] = array_slice($art,$start,$request->param("limit"));
        $art["cont"] = $count;
	    //个人主页访问量自增1
	    $usename -> setInc('home_count');
	     //将字段fans（字符串类型）分割数组形式并统计其元素个数
	    $fans=count(explode(",", $usename['fans']));
		//传递给模板
		$this -> view -> assign(['member'=>$usename,'fans_sum'=>$fans,'article'=>$art]);
        return $this-> fetch('/member_temp/'.$this->config["member_temp"].'/my_home');
    }
    //用户中心我的关注
    public function my_follow()
    {
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
        return $this-> fetch('/member_temp/'.$this->config["member_temp"].'/my_follow');
    }
    //用户中心我的关注列表取消关注提交表单
    public function cancel_follow(Request $request)
    {
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
        return $this-> fetch('/member_temp/'.$this->config["member_temp"].'/my_fans');
    }
    //用户中心我的粉丝列表点击关注提交表单
    public function like_fans(Request $request)
    {
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
        return $this-> fetch('/member_temp/'.$this->config["member_temp"].'/my_dynamic');
    }
    //用户退出
    public function loginout(Request $request)
    {
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
