<?php
namespace app\index\controller;
use app\index\controller\Base;
use think\Request;
use app\index\model\Member;
use app\index\model\System;
use think\facade\Session;
use think\Db;
use app\index\validate\Reg;

class Index extends Base
{
    protected $config;
    protected function initialize()
    {
        parent::initialize();
        $this->config = $this->getsystems();
    }
    public function index()
    {
        $catinfo['id'] = "";
        $catinfo['pid'] = "";
        return $this-> fetch('/home_temp/'.$this->config["home_temp"].'/index',["catinfo"=>$catinfo]);
    }
    

    //验证会员登录
    public function login(Request $request)
    {
        //检测登录的session是否存在
		if(Session::has('Member')){
    		  $this -> redirect('index/User/index');
    	}
        //判断是否post请求
        if(request()->isPost()){
            //接收前台传过来的数据
            $data = input('post.');
            //查询前台传过来的username在数据表中是否存在
            $useArr = Member::where('account|email|phone',$data['account'])->find();
            //获取所有系统配置信息
            $usedg = System::where('id',1)->find();
            //获取当前客户端的IP地址
            $ip = $request->ip();
            //登录错误次数达到后的封禁时间
            $cwtime = time()+1800;
            //最后一次登录错误时间加15分钟，15分钟后对所有错误信息清零
            $last_cwtime = time()+900;
            //判断当前会员是否存在
            if($useArr == NULL)
            {
                $this -> error("会员不存在!");
            }
            else
            {
                //获取会员的ID
                $id = $useArr->getData('id');
                //判断是否为超级会员
                if($useArr->getData('super') == 1)
                {
                    //判断当前会员是否为启用状态
                        if($useArr->getData('status') != 1)
                        {
                            //返回禁用信息
                            $this -> error("当前会员已被停用!");
                        }
                    if($useArr->getData('degree') == 0)
                    {
                        //判断登录密码是否相等
                        if(password_verify($data['password'],$useArr['password']))
                        {
                            //登录次数自增1
                            $useArr -> setInc('count');
                            //设置session值
                            Session::set('Member',$useArr->toArray());
                            //删除密码错误后记录的会员名session值
                            Session::delete('username');
                            //返回登录成功的信息并跳到会员中心首页
                            $this->success("登录成功!",'User/index');
                        }
                        else
                        {
                            $pwderror = 'errorpwd';
                            //登录错误次数自增1
                            $useArr -> setInc('degree');
                            //设置密码登录错误session值,便于跳出验证码
                            Session::set('userpwd_error',$pwderror);
                            //设置密码错误后记录会员名存入session
                            Session::set('username',$data['account']);
                            $this -> error("密码错误!");
                        }
                    }
                    else
                    {
                        if(Session::has('userpwd_error')){
                            //验证码验证操作
                            if(!captcha_check($data['verif']))
                            {
                                // 校验失败
                                $this->error('验证码不正确');
                            }
                        }
                        //判断登录密码是否相等
                        if(password_verify($data['password'],$useArr['password']))
                        {
                            //登录次数自增1
                            $useArr -> setInc('count');
                            //将错误登录次数清零
                            $useArr -> save(['degree' => 0]);
                            //设置session值
                            Session::set('Member',$useArr->toArray());
                            //删除密码错误记录的session值
                            Session::delete('userpwd_error');
                            //删除密码错误后记录的会员名session值
                            Session::delete('username');
                            //返回登录成功的信息并跳到会员中心首页
                            $this->success("登录成功!",'User/index');
                        }
                        else
                        {
                            //登录错误次数自增1
                            $useArr -> setInc('degree');
                            $this -> error("密码错误!");
                        }
                    }
                }
                else
                {
                    //判断当前会员是否存在登录密码错误现象，NULL表示没有
                    if($useArr->getData('last_cwtime') == NULL)
                    {
                        //判断当前会员是否为启用状态
                        if($useArr->getData('status') != 1)
                        {
                            //返回禁用信息
                            $this -> error("当前会员已被停用!");
                        }
                        else
                        {
                            //判断登录密码是否相等
                            if(password_verify($data['password'],$useArr['password']))
                            {
                                //登录次数自增1
                                $useArr -> setInc('count');
                                //设置session值
                                Session::set('Member',$useArr->toArray());
                                //删除密码错误后记录的会员名session值
                                Session::delete('username');
                                //返回登录成功的信息并跳到会员中心首页
                                $this->success("登录成功!",'User/index');
                            }
                            else
                            {
                                //记录密码登录错误时间,便于$last_cwtime分钟后对登录错误次数清零
                                $pwderror = $useArr -> save(['last_cwtime'=>$last_cwtime]);
                                //设置密码登录错误session值,便于跳出验证码
                                Session::set('userpwd_error',$pwderror);
                                //设置密码错误后记录会员名存入session
                                Session::set('username',$data['account']);
                                $this -> error("密码错误!");
                            }
                        }
                    }
                    else
                    {
                        //验证码验证操作
                        if(!captcha_check($data['verif']))
                        {
                            // 校验失败
                            $this->error('验证码不正确');
                        }
                        //判断当前时间是否大于或等于最后一次登录错误时间
                        if($useArr->getData('last_cwtime') <= time())
                        {
                            //将最后一次登录错误时间清空
                            $useArr -> save(['last_cwtime' => NULL]);
                            //将错误登录次数清零
                            $useArr -> save(['degree' => 0]);
                            //这里判断是防止封禁时间大于等于最后一次登录错误时间出现的BUG
                            if($useArr->getData('cwtime') == NULL || $useArr->getData('cwtime') <= time())
                            {
                                //将错误登录时间设置为null
                                $useArr -> save(['cwtime' => NULL]);
                                //判断当前会员是否为启用状态
                                if($useArr->getData('status') != 1)
                                {
                                    //返回禁用信息
                                    $this -> error("当前会员已被停用!");
                                }
                                else
                                {
                                    //判断登录密码是否相等
                                    if(password_verify($data['password'],$useArr['password']))
                                    {
                                        //登录次数自增1
                                        $useArr -> setInc('count');
                                        //删除密码错误记录的session值
                                        Session::delete('userpwd_error');
                                        //设置session值
                                        Session::set('Member',$useArr->toArray());
                                        //删除密码错误后记录的会员名session值
                                        Session::delete('username');
                                        //返回登录成功的信息并跳到后台首页
                                        $this->success("登录成功!",'User/index');
                                    }
                                    else
                                    {
                                        //登录错误次数自增1
                                        $useArr -> setInc('degree');
                                        //记录密码登录错误时间,便于$last_cwtime分钟后对登录错误次数清零
                                        $pwderror = $useArr -> save(['last_cwtime'=>$last_cwtime]);
                                        //获取最大错误登录次数
                                        $maxcount = $usedg -> getData('user_error');
                                        //获取当前登录错误次数
                                        $errorcount = $useArr -> getData('degree');
                                        //这里$c表示还有几次登录机会
                                        $c = $maxcount - $errorcount;
                                        $this -> error("密码错误，还有{$c}次机会!");
                                    }
                                }
                            }
                            else
                            {
                                //计算还有几分钟可以登录,这里转为整型
                                $t = (int)(($useArr -> getData('cwtime') - time())/60);
                                //返回错误登录信息
                                $this -> error("登录错误过多,请{$t}分钟后再试!");
                            }
                        }
                        else
                        {
                            //判断是否存在登录错误次数达到后的封禁时间
                            if($useArr->getData('cwtime') == NULL)
                            {
                                if($useArr->getData('status') != 1)
                                {
                                    //返回禁用信息
                                    $this -> error("当前会员已被停用!");
                                }
                                else
                                {
                                    //判断会员登录错误是否大于等于指定次数
                                    if($useArr->getData('degree') >= $usedg->getData('user_error'))
                                    {
                                        //将登录错误次数到达后封禁时间写入数据表
                                        $useArr-> save(['cwtime' => $cwtime]);
                                        //返回登录错误信息
                                        $this -> error("登录错误过多,请5分钟后再试!");
                                    }
                                    else
                                    {
                                        //判断登录密码是否相等
                                        if(password_verify($data['password'],$useArr['password']))
                                        {
                                            //登录次数自增1
                                            $useArr -> setInc('count');
                                            //将错误登录次数清零
                                            $useArr -> save(['degree' => 0]);
                                            //将最后一次登录错误时间清空
                                            $useArr -> save(['last_cwtime' => NULL]);
                                            //删除密码错误记录的session值
                                            Session::delete('userpwd_error');
                                            //设置session值
                                            Session::set('Member',$useArr->toArray());
                                            //删除密码错误后记录的会员名session值
                                            Session::delete('username');
                                            //返回登录成功的信息并跳到会员中心首页
                                            $this->success("登录成功!",'User/index');
                                        }
                                        else
                                        {
                                            //登录错误次数自增1
                                            $useArr -> setInc('degree');
                                            //记录密码登录错误时间,便于$last_cwtime分钟后对登录错误次数清零
                                            $pwderror = $useArr -> save(['last_cwtime'=>$last_cwtime]);
                                            //获取最大错误登录次数
                                            $maxcount = $usedg -> getData('user_error');
                                            //获取当前登录错误次数
                                            $errorcount = $useArr->getData('degree');
                                            //这里$c表示还有几次登录机会
                                            $c = $maxcount - $errorcount;
                                            $this -> error("密码错误，还有{$c}次机会!");
                                        }
                                    }
                                }
                            }
                            else
                            {
                                //判断当前时间是否大于等于封禁时间
                                if($useArr->getData('cwtime') <= time())
                                {
                                    //将错误登录时间设置为null
                                    $useArr -> save(['cwtime' => NULL]);
                                    //将错误登录次数清零
                                    $useArr -> save(['degree' => 0]);
                                    if($useArr->getData('status') != 1)
                                    {
                                        //返回禁用信息
                                        $this -> error("当前会员已被停用!");
                                    }
                                    else
                                    {
                                        if(password_verify($data['password'],$useArr['password']))
                                        {
                                            //登录次数自增1
                                            $useArr -> setInc('count');
                                            //将最后一次登录错误时间清空
                                            $useArr -> save(['last_cwtime' => NULL]);
                                            //删除密码错误记录的session值
                                            Session::delete('userpwd_error');
                                            //设置session值
                                            Session::set('Member',$useArr->toArray());
                                            //删除密码错误后记录的会员名session值
                                            Session::delete('username');
                                            //返回登录成功的信息并跳到会员中心首页
                                            $this->success("登录成功!",'User/index');
                                        }
                                        else
                                        {
                                            //登录错误次数自增1
                                            $useArr -> setInc('degree');
                                            //记录密码登录错误时间,便于$last_cwtime分钟后对登录错误次数清零
                                            $pwderror = $useArr -> save(['last_cwtime'=>$last_cwtime]);
                                            //获取最大错误登录次数
                                            $maxcount = $usedg -> getData('user_error');
                                            //获取当前登录错误次数
                                            $errorcount = $useArr->getData('degree');
                                            //这里$c表示还有几次登录机会
                                            $c = $maxcount - $errorcount;
                                            $this -> error("密码错误，还有{$c}次机会!");
                                        }
                                    }
                                }
                                else
                                {
                                    //计算还有几分钟可以登录,这里转为整型
                                    $t=(int)(($useArr->getData('cwtime') - time())/60);
                                    //返回错误登录信息
                                    $this -> error("登录错误过多,请{$t}分钟后再试!");
                                }
                            }
                        }
                    }
                }
            }
        }
        $abc = Db::name("login_set")->find(1);
        unset($abc["id"]);
        foreach($abc as $key=>$v){
            if($v == 0){
                unset($abc[$key]);
            }
        }
        $info = [];
        foreach($abc as $k=>$a){
            $name = ["qq"=>"QQ登录","weixin"=>"微信登录","sina"=>"微博登录","baidu"=>"百度登录","gitee"=>"Gitee登录","github"=>"Github登录","douyin"=>"抖音登录","dingtalk"=>"钉钉登录"];
            $field = substr($k,0,-6);
            $info[] = ["name"=>$name[$field],"type"=>$field,"url"=>$request->domain().url("Oauth/login",["type"=>$field])];
            
        }
        return $this-> fetch('/member_temp/'.$this->config["member_temp"].'/login',["ulogin"=>$info]);
    }

    //注册
    public function reg(Request $request)
    {
        //判断会员注册验证码
        if(request()->isPost()){
		 //接收注册页面传过来的数据
        $data = $request->post();
        //对数据进行验证
        $validate = new Reg();//实例化验证器
        if (!$validate->check($data)) {
            $this->error($validate->getError());
        }
        $code=Session::get('codestr');
        //邮箱验证码
        if($data['email_code'] !== $code['code']) {
            // 校验失败
            $this -> error("邮箱验证码不正确!");
        }
        //对前台传过来的密码进行加密
        $data['password'] = password_hash($data['password'],PASSWORD_BCRYPT);
		 //查询会员注册开关的状态
        $regClose =Db::name('system')->where('id',1)->field('userreg_close')->find();
        //判断注册开关是否关闭
        if($regClose['userreg_close'] == 1){
             $this -> error("注册已关闭!");
        }else{
            if(time() - $code['time'] >= 300){
                //删除User控制器下sendEmail的Session值
                Session::delete('codestr');
                $this -> error("邮箱验证码已过期!");
            }else{
                //从session中获取邮箱作比较，防止中间篡改
                if($data['email'] != $code['email']){
                    $this->error('当前邮箱无效!');
                }else{
                    $data['create_time'] = time();
                    $res = Db::name('Member')->strict(false)->insert($data);
                    //取得新增数据id
                    $uid = Db::name('Member')->getLastInsID();
                    if($res){
                 	    //建立新增会员附属表
                        Db::name('member_data')->insert(['uid'=>$uid]);
                   	    //删除User控制器下sendEmail的Session值
                   	    Session::delete('codestr');
                        $this->success("注册成功!",'index/Index/login');
                    }else{
                   	    //删除User控制器下sendEmail的Session值
                   	    Session::delete('codestr');
                   	    $this -> error("注册失败!",'index/Index/reg');
                    }
                }
            }
        }
    }
        return $this-> fetch('/member_temp/'.$this->config["member_temp"].'/reg');
    }
    
    //用户注册发送验证码
	public function sendEmails(Request $request)
    {
        if(request()->isPost()){
            //接收前台传过来的email信息
    		$data = $request->post();
    		 //查询邮箱配置中email,emailpaswsd,smtp,sll字段的信息 
    		$email=Db::name('sms')->where('id',1)->field('email,emailpaswsd,smtp,sll')->find();
    		//查询网站名称以及logo路径
    		$emname = Db::name('system')->where('id',1)->field('title,logo')->find();
    		//获取前段网站域名
    		$host = $request->domain();
    		//调用随机数函数
    		$yanzhen = $this->codestr();
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
    		$res = sendEmail($email['email'],$email['emailpaswsd'],$email['smtp'],$email['sll'],$emname['title'],$title,$content,$data['email']);
    		 if($res){
    		     $code['time'] = time();
    		     $code['code'] = $yanzhen;
    		     $code['email'] = $data['email'];
    		     //设置session值，以便于匹配注册验证码以及注册验证码有效时间
    		     Session::set('codestr',$code);
    		     $this->success("发送成功");
    		 }else{
    		     $this -> error("发送失败!");
    		 }
        }
	}
     //忘记密码渲染
    public function password(Request $request)
    {
        //判断会员注册验证码
        if(request()->isPost()){
            $data = $request->post();
            $code = Session::get('codestr');
            //邮箱验证码
            if($data['email_code'] !== $code) {
                // 校验失败
                $this -> error("邮箱验证码不正确!");
            }
		    //获取User控制器下passEmail的Session值
		    $time = Session::get('time');
            //对前台传过来的密码进行加密
            $data['password'] = password_hash($data['password'],PASSWORD_BCRYPT);
            if(time() - $time >= 300){
                //删除User控制器下passEmail的Session值
                Session::delete('codestr');
                $this -> error("邮箱验证码已过期!");
            }else{
                //根据账号将新的密码更新到数据表
                $res = Db::name('member')->where('account',$data['account'])->update(['password' => $data['password']]);
                if($res){
                     //删除User控制器下passEmail的Session值
                    Session::delete('codestr');
                    $this->success("修改成功!",'index/Index/login');
                }else{
                     //删除User控制器下passEmail的Session值
                    Session::delete('codestr');
                    $this -> error("修改失败!");
                }
            }
        }
        return $this-> fetch('/member_temp/'.$this->config["member_temp"].'/password');
    }
    
	//用户密码找回发送验证码
	public function passEmail(Request $request)
	{
        if(request()->isPost()){
    	    //接收前台传过来的所有信息
    	    $data = $request->post();
    		//查询邮箱配置中email,emailpaswsd,smtp,sll字段的信息 
    		$email=Db::name('sms')->where('id',1)->field('email,emailpaswsd,smtp,sll')->find();
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
	}
}

