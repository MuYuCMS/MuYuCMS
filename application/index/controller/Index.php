<?php
namespace app\index\controller;
use app\index\controller\Base;
use think\Request;
use app\index\model\Article;
use app\index\model\Member;
use app\index\model\System;
use think\facade\Session;
use think\Db;

class Index extends Base
{
    public function index()
    {
		//查询数据库文章
		$art=Db::name('article')->select();
		
		//传递给模板
		$this -> view -> assign(['article'=>$art]);
        return $this-> fetch('/index');
    }
    
    //登录渲染
    public function login(Request $request)
    {
        //判断当前是否登录
		$user = $this->user_login();
        return $this-> fetch('/login');
    }
//验证会员登录
public function checklogin(Request $request)
{
    //判断是否post请求
    if(request()->isPost())
    {
        //接收前台传过来的数据
        $data = input('post.');
        //查询前台传过来的username在数据表中是否存在
        $useArr = Member::where('account|email',$data['account'])->find();
        //获取所有系统配置信息
        $usedg=System::where('id',1)->find();
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
                if($useArr->getData('degree') == 0)
                {
                    //判断登录密码是否相等
                    if(md5($data['password']) == $useArr['password'])
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
                    //验证码验证操作
                    if(!captcha_check($data['verif']))
                    {
                        // 校验失败
                        $this->error('验证码不正确');
                    }
                    //判断登录密码是否相等
                    if(md5($data['password']) == $useArr['password'])
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
                        if(md5($data['password']) == $useArr['password'])
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
                                if(md5($data['password']) == $useArr['password'])
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
                                    if(md5($data['password']) == $useArr['password'])
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
                                    if(md5($data['password']) == $useArr['password'])
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
}
    //注册渲染
    public function reg()
    {
		//传递给模板
        return $this-> fetch('/reg');
    }
    //注册执行添加都数据表
    public function newreg(Request $request)
    {
        //判断会员注册验证码
        if(request()->isPost()){
		            $da = $request->param('email_code');
		            $code=Session::get('codestr');
		            //邮箱验证码
		            if($da!==$code) {
		                // 校验失败
		                $this -> error("邮箱验证码不正确!");
		            }
		 //获取当前时间           
		 $times=time();
		 //接收注册页面传过来的数据
        $data = $request->param();
        //对前台传过来的密码进行加密
        $data['password']=md5($data['password']);
        //获取当前时间
        $data['create_time']=time();
        //字段分离
        $t=['account'=>$data['account'],'email'=>$data['email'],'phone'=>$data['phone'],'password'=>$data['password'],'create_time'=>$data['create_time']];
        //查询会员表username字段的所有信息作匹对
        $du=Db::name('member')->where('account',$data['account'])->find();
        //查询会员表email字段的所有信息作匹对
        $de=Db::name('member')->where('email',$data['email'])->find();
        //查询会员表mobile字段的所有信息作匹对
        $dm=Db::name('member')->where('phone',$data['phone'])->find();
		 //查询会员注册开关的状态
        $reg =Db::name('system')->where('id',1)->field('userreg_close')->find();
        //判断注册开关是否关闭
        if($reg['userreg_close']==1){
             $this -> error("注册已关闭!");
        }else{
            if($du!==null){
                $this -> error("会员已存在!");
            }else{
                if($de!==null){
                    $this -> error("邮箱已存在!");
                }else{
                    if($dm!==null){
                         $this -> error("手机号码已存在!");
                    }else{
                        //获取User控制器下sendEmail的Session值
                        $timess=Session::get('time');
                        if($times - $timess >= 300){
                            //删除User控制器下sendEmail的Session值
                            Session::delete('codestr');
                            $this -> error("邮箱验证码已过期!");
                        }else{
                                //将数据插入数据库
                                $res=Db::name('Member')->data($t)->insert();
                                //取得新增数据id
		                        $uid = Db::name('Member')->getLastInsID();
	                        	//判断是否成功并提示
	                         	if($res){
	                         	    //建立新增会员附属表
		                            Db::name('member_data')->insert(['uid'=>$uid]);
	                         	    //删除User控制器下sendEmail的Session值
	                         	    Session::delete('codestr');
	                         	    $this -> success("注册成功!",'index/Index/login');
	                         	    
	                           	}else{
	                           	    //删除User控制器下sendEmail的Session值
	                           	    Session::delete('codestr');
	                           	    $this -> error("注册失败!",'index/index/reg');
	                           	    
	                                	}
                            }
                                 
                         }
                    }
                }
            }
    }
    }  
     //忘记密码渲染
    public function password()
    {
        return $this-> fetch('/password');
    }
    //用户密码找回更新到数据表
    public function updpassword(Request $request)
    {
        //判断会员注册验证码
        if(request()->isPost()){
		            $da = $request->post('email_code');
		            $code=Session::get('codestr');
		            //邮箱验证码
		            if($da!==$code) {
		                // 校验失败
		                $this -> error("邮箱验证码不正确!");
		            }
		 //获取当前时间           
		 $times=time();
		 //获取User控制器下passEmail的Session值
		 $timess=Session::get('time');
		 //接收注册页面传过来的数据
        $pass = $request->post();
        //对前台传过来的密码进行加密
        $pass['password']=md5($pass['password']);
        if($times - $timess >= 300){
            //删除User控制器下passEmail的Session值
            Session::delete('codestr');
            $this -> error("邮箱验证码已过期!");
                        }else{
                            //根据账号将新的密码更新到数据表
                            $res=Db::name('member')->where('account',$pass['account'])->update(['password' => $pass['password']]);
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
    }
    
    //系统安装
    public function sys_install()
    {
        return $this-> fetch('/sys_install');
    }
    
    //更新日志
    public function update_log()
    {
        return $this-> fetch('/update_log');
    }
    
    //开发中心
    public function temp_label()
    {
        return $this-> fetch('/temp_label');
    }
    
    //关于我们
    public function about()
    {
        return $this-> fetch('/about');
    }
    
    //应用模板
    public function temp()
    {
        $app = Db::name('app_temp')->order('create_time','desc')->paginate(12);
        $this->assign('app',$app);
        return $this-> fetch('/temp');
    }
    
    //应用插件
    public function plug()
    {
        $app = Db::name('app_plug')->order('create_time','desc')->paginate(12);
        $this->assign('app',$app);
        return $this-> fetch('/plug');
    }
}
