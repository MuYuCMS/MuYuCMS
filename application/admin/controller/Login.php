<?php
 //  __  __                                                                                 
 // |  \/  |                                                                                
 // | \  / |  _   _   _   _   _   _    ___   _ __ ___    ___        ___    ___    _ __ ___  
 // | |\/| | | | | | | | | | | | | |  / __| | '_ ` _ \  / __|      / __|  / _ \  | '_ ` _ \ 
 // | |  | | | |_| | | |_| | | |_| | | (__  | | | | | | \__ \  _  | (__  | (_) | | | | | | |
 // |_|  |_|  \__,_|  \__, |  \__,_|  \___| |_| |_| |_| |___/ (_)  \___|  \___/  |_| |_| |_|
 //                    __/ |                                                                
 //                   |___/
				   
//				                  閒浴豀头静自居，此身已濯心何如。
				   
//				                  此心欲濯静中去，静定由来物自除。

//                                                                老詹保佑    
namespace app\admin\controller;
use app\admin\controller\Base;
use app\admin\model\Admin;
use app\admin\model\System;
use think\Request;
use think\facade\Session;
use think\Db;

class Login extends Base
{
    public function login(Request $request)
    {
        if(Session::has('Adminuser')){
					  $res = Session::get('Adminuser');
					  $this -> error("请勿重复登录!",'Index/index');
					  return false;
		} 
        //判断是否post请求
		if(request()->isPost()){
			//查询前台传过来的name字段值
			$useArr = Admin::where("name",input("post.adminName"))->find();
			//获取所有系统配置信息
			$usedg = System::where('id',1)->find();
			//登录错误次数达到后的封禁时间，默认为5分钟
			$cwtime = time() + 300;
			//最后一次登录错误时间
			$last_cwtime = time() + 900;
			//判断当前账户是否存在
			if($useArr == NULL){
			    $this ->logs("尝试登录不存在账户:".input("post.adminName"),input("post.adminName"));
				$this -> error("账户".input("post.adminName")."不存在!");
			}else{
			    //引入更新控制器，便于调用系统版本检测函数
				$e = controller('Update');
				//获取账户的ID
				$id = $useArr->getData('id');
				//判断当前账户是否存在登录密码错误现象，NULL表示没有
				if($useArr->getData('last_cwtime') == NULL){
					//判断当前账户是否为启用状态
					if($useArr->getData('status') != 1){
						//返回禁用信息
						$this->logs("被禁用户:".input("post.adminName")."尝试登录!",input("post.adminName"));
						$this -> error("当前账户已被禁用!");
					}else{
						//判断登录密码是否相等
		                if(password_verify($request -> post('password'),$useArr['password'])){
		                    //登录次数自增1
		                    $useArr -> setInc('count');
		                    //记录登录时间
				            $useArr -> save(['login_time'=>time()]);
				            //设置session值
				            Session::set('Adminuser',$useArr->toArray());
				            //返回登录成功的信息并跳到后台首页
				            $this ->logs(input("post.adminName")."登录成功");
				            //系统版本检测
                            $res = $e->get_version();
				            $this -> success("欢迎回来-{$request->post('adminName')}",'Index/index');
		                }else{
		                    //登录错误次数自增1
							$useArr -> setInc('degree');
							//记录密码登录错误时间,便于$last_cwtime分钟后对登录错误次数清零
							$useArr -> save(['last_cwtime'=>$last_cwtime]);
				            //设置密码登录错误session值,便于跳出验证码
				            Session::set('pwderror',$useArr);
				            //将已输入且存在的账号存入session
				            session::set('adname',$request->post('adminName'));
				            $this ->logs(input("post.adminName")."密码错误!  错误密码:".$request->post('password'),input("post.adminName"));
				            $this -> error("密码错误!");
		                }
					}
				}else{
					$data = input('post.');
					if(Session::has('pwderror')){
    					//验证码验证操作
    		            if(!captcha_check($data['captcha'])) {
    		                // 校验失败
    		                $this->error('验证码不正确');
    		            }
					}
					//判断当前时间是否大于或等于最后一次登录错误时间
					if($useArr->getData('last_cwtime') <= time()){
						//将最后一次登录错误时间清空
						$useArr -> save(['last_cwtime' => NULL]);
						//将错误登录次数清零
						$useArr -> save(['degree' => 0]);
						//这里判断是防止封禁时间大于等于最后一次登录错误时间出现的BUG
						if($useArr->getData('cwtime') == NULL || $useArr->getData('cwtime') <= time()){
							//将错误登录时间设置为null
							$useArr -> save(['cwtime' => NULL]);
							//判断当前账户是否为启用状态
							if($useArr->getData('status') != 1){
							//返回禁用信息
							$this->logs("被禁用户:".input("post.adminName")."尝试登录!",input("post.adminName"));
							$this -> error("当前账户已被禁用!");
							}else{
								//判断登录密码是否相等
								if(password_verify($request -> post('password'),$useArr['password'])){
									//登录次数自增1
									$useArr -> setInc('count');
									//记录登录时间
									$useArr -> save(['login_time'=>time()]);
									//删除密码错误记录的session值
									Session::delete('pwderror');
									//删除记录账号session
									Session::delete('adname');
									//设置session值
									Session::set('Adminuser',$useArr->toArray());
									//返回登录成功的信息并跳到后台首页
									$this ->logs(input("post.adminName")."登录成功");
									//系统版本检测
                                    $res = $e->get_version();
									$this -> success("欢迎回来-{$request->post('adminName')}",'Index/index');
								}else{
									//登录错误次数自增1
									$useArr -> setInc('degree');
									//记录密码登录错误时间,便于$last_cwtime分钟后对登录错误次数清零
				                    $pwderror=$useArr -> save(['last_cwtime'=>$last_cwtime]);
									//获取最大错误登录次数
									$maxcount=$usedg->getData('degree');
									//获取当前登录错误次数
									$errorcount=$useArr->getData('degree');
									//这里$c表示还有几次登录机会
									$c=$maxcount - $errorcount;
									$this->logs(input("post.adminName")."密码错误!错误密码:".$request -> post('password'),input("post.adminName"));
									$this -> error("密码错误，还有{$c}次机会!");
								}
							}
						}else{
								//计算还有几分钟可以登录,这里转为整型
								$t=(int)(($useArr->getData('cwtime') - time())/60);
								//返回错误登录信息
								$this->logs(input("post.adminName")."多次错误登录,限制登录!",input("post.adminName"));
								$this -> error("登录错误过多,请{$t}分钟后再试!");
							}
					}else{
						//判断是否存在登录错误次数达到后的封禁时间
						if($useArr->getData('cwtime') == NULL){
							if($useArr->getData('status') != 1){
							//返回禁用信息
							$this->logs("被禁用户:".input("post.adminName")."尝试登录!",input("post.adminName"));
							$this -> error("当前账户已被禁用!");
							}else{
								//判断账户登录错误是否大于等于指定次数
								if($useArr->getData('degree') >= $usedg['degree']){
									//将登录错误次数到达后封禁时间写入数据表
									$useArr-> save(['cwtime' => $cwtime]);
									//返回登录错误信息
									$this->logs(input("post.adminName")."多次错误登录,限制登录!",input("post.adminName"));
									$this -> error("登录错误过多,请5分钟后再试!");
								}else{
									//判断登录密码是否相等
									if(password_verify($request -> post('password'),$useArr['password'])){
										//登录次数自增1
										$useArr -> setInc('count');
										//记录登录时间
										$useArr -> save(['login_time'=>time()]);
										//将错误登录次数清零
										$useArr -> save(['degree' => 0]);
										//将最后一次登录错误时间清空
										$useArr -> save(['last_cwtime' => NULL]);
										//删除密码错误记录的session值
									    Session::delete('pwderror');
									    //删除记录账号session
									    Session::delete('adname');
										//设置session值
										Session::set('Adminuser',$useArr->toArray());
										//返回登录成功的信息并跳到后台首页
										$this ->logs(input("post.adminName")."登录成功");
										//系统版本检测
                                        $res = $e->get_version();
										$this -> success("欢迎回来-{$request->post('adminName')}",'Index/index');
									}else{
										//登录错误次数自增1
										$useArr -> setInc('degree');
										//记录密码登录错误时间,便于$last_cwtime分钟后对登录错误次数清零
				                        $pwderror=$useArr -> save(['last_cwtime'=>$last_cwtime]);
										//获取最大错误登录次数
										$maxcount=$usedg->getData('degree');
										//获取当前登录错误次数
										$errorcount=$useArr->getData('degree');
										//这里$c表示还有几次登录机会
										$c=$maxcount - $errorcount;
										$this->logs(input("post.adminName")."多次错误登录!登录错误密码:".$request -> post('password'),input("post.adminName"));
										$this -> error("密码错误，还有{$c}次机会!");
									}
								}
							}
						}else{
							//判断当前时间是否大于等于封禁时间
							if($useArr->getData('cwtime') <= time()){
								//将错误登录时间设置为null
								$useArr -> save(['cwtime' => NULL]);
								//将错误登录次数清零
								$useArr -> save(['degree' => 0]);
								if($useArr->getData('status') != 1){
								//返回禁用信息
								$this->logs("被禁用户:".input("post.adminName")."尝试登录!",input("post.adminName"));
								$this -> error("当前账户已被禁用!");
								}else{
										if(password_verify($request -> post('password'),$useArr['password'])){
										//登录次数自增1
										$useArr -> setInc('count');
										//记录登录时间
										$useArr -> save(['login_time'=>time()]);
										//将最后一次登录错误时间清空
										$useArr -> save(['last_cwtime' => NULL]);
										//删除密码错误记录的session值
									    Session::delete('pwderror');
									    //删除记录账号session
									    Session::delete('adname');
										//设置session值
										Session::set('Adminuser',$useArr->toArray());
										//返回登录成功的信息并跳到后台首页
										$this ->logs(input("post.adminName")."登录成功");
										//系统版本检测
                                        $res = $e->get_version();
										$this -> success("欢迎回来-{$request->post('adminName')}",'Index/index');
									}else{
										//登录错误次数自增1
										$useArr -> setInc('degree');
										//记录密码登录错误时间,便于$last_cwtime分钟后对登录错误次数清零
				                        $pwderror=$useArr -> save(['last_cwtime'=>$last_cwtime]);
										//获取最大错误登录次数
										$maxcount=$usedg['degree'];
										//获取当前登录错误次数
										$errorcount=$useArr->getData('degree');
										//这里$c表示还有几次登录机会
										$c=$maxcount - $errorcount;
										$this->logs(input("post.adminName")."多次错误!登录错误密码:".$request -> post('password'),input("post.adminName"));
										$this -> error("密码错误，还有{$c}次机会!");
									}
							}
						}else{
							//计算还有几分钟可以登录,这里转为整型
							$t=(int)(($useArr->getData('cwtime') - time())/60);
							//返回错误登录信息
							$this->logs(input("post.adminName")."多次错误登录,限制登录!",input("post.adminName"));
							$this -> error("登录错误过多,请{$t}分钟后再试!");
						}
					}
				}
			}
		}
	}
        return $this -> fetch('login/login');
    }

}
