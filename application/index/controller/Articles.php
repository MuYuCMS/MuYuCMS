<?php

namespace app\index\controller;
use app\index\controller\Base;
use think\Request;
use app\index\model\Hmenu;
use app\index\model\Article as ArticleModel;
use think\Db;
use think\facade\Session;

class Articles extends Base
{
    /**
     * 显示资源列表
     *
	 *文章列表页
	 *
     * @return \think\Response
     */
    public function article_list($id,$key=false,$tag=false,$price=false,$orders=false)
    {
        $ids = $id;
        //子栏目数据
        $un = Hmenu::where("pid",$id)->select()->toArray();
        if($un == NULL){
            $p = Hmenu::find($id);
            if($p['pid'] != 0){
            $un = Hmenu::where("pid",$p['pid'])->select()->toArray();
            }
        }
         //面包屑导航（当前位置）
        $n = array();
        if(!empty($ids)){
                $menu = Hmenu::find($ids);
                if($menu["pid"] != 0){
                    $men = Hmenu::find($menu->pid);
                    $n[] = $men;
                    $n[] = $menu;
                }else{
                   $n[] = $menu; 
                }
                }
        
        //栏目信息快捷调用 例如  栏目标题：$tdk->title
        $tdk = Hmenu::where("id",$id)->find();
        $pd = $tdk['pid'];
        
        $tag = $tag !== false ? $tag : "";
        $key = $key !== false ? $key : "";
        $price = $price !== false ? $price : "1";
        $orders = $orders !== false ? $orders : "";
		$this -> view -> assign(['ids'=>$ids,'tag'=>$tag,'key'=>$key,'price'=>$price,'orders'=>$orders,'un'=>$un,'tdk'=>$tdk,'n'=>array_reverse($n),'pd'=>$pd]);
		return $this-> fetch('/article_list');
    }
    
    
	//文章详情页
	public function article_content($id){
	    $article = Db::name('article')->alias("a")->join("article_data b","b.aid=a.id")->leftjoin("member c","c.id=a.uid")->leftjoin("member_data d","d.uid=a.uid")->where('a.id',$id)->fieldRaw("a.*,b.browse,b.likes,b.comment_t,c.name,c.photo,d.comment,d.le_word,d.attention,d.fans,d.contribute")->group("a.id")->find();
	    if($article['delete_time'] === NULL){
	    if($article['status'] === "0"){
	    $patch = "";
		if(!empty($article['downputh'])){
		$patch = explode(',',$article['downputh']);
		$patch = array_chunk($patch,2);
		foreach($article as $val){
		    $article['downputh'] = $patch;
		}
		foreach($article['downputh'] as $key=>$val){
		    $article['downputh'][$key]['0'] = $val['0'];
		    $article['downputh'][$key]['ptname'] = $val['0'];
		    unset($article['downputh'][$key]['0']);
		    $article['downputh'][$key]['1'] = $val['1'];
		    $article['downputh'][$key]['pturl'] = $val['1'];
		    unset($article['downputh'][$key]['1']);
		}
		}
	    $ids = substr($article['mid'], -1);
	     //面包屑导航（当前位置）
        $n = array();
        if(!empty($ids)){
                $menu = Hmenu::find($ids);
                if($menu["pid"] != 0){
                    $men = Hmenu::find($menu->pid);
                    $n[] = $men;
                    $n[] = $menu;
                }else{
                   $n[] = $menu; 
                }
                }
		$this -> view -> assign(['article'=>$article,'ids'=>$ids,'n'=>$n]);
		return $this-> fetch('/article_content');
	    }else{
	       $this->error('这篇文章查看不了!');
	       return false; 
	    }
	    }else{
	       $this->error('这篇文章已经永远离世!');
	       return false;
	    }
	}
	
	//评论提交地址
	public function arping(Request $request)
	{
	    if(request()->isPost()){
	    $pinl  = !empty(input('post.')) ? input('post.') : $request->post();
	    $config = $this->getsystems();
	    $msg="";
	    if($config['comment_close'] != 0)
	    {
	       $this -> error("管理员已关闭评论功能!");
	       return false;
	    }
	    $arcon = Db::name("article")->where("id",$pinl['id'])->field('comment')->find();
	    if($arcon['comment'] != 0){
	      $this -> error("当前文章评论已关闭!");
	      return false;
	    }
	    if($pinl['content'] == '' or $pinl['content'] == NULL or $pinl['content'] == " "){
	      $this -> error("请先填写内容!");
	      return false;
	    }
	    $pincon = delete_XSS($pinl['content']);
	    $pbc = Db::name('system')->where('id',1)->field('shielding')->find();
	    $pbcc = $this->sensitive($pbc,$pincon);
	    if($pbcc['status'] != 0)
	    {
	      $this -> error("您的内容包含(-".$pbcc['message']."-)敏感词,请修正!");
	      return false;  
	    }elseif ($pbcc['status'] = 1) {
	        $pbcc = $pbcc['log'];
	    }
	    $names = "游客".time()."";
	    $uid = NULL;
	    $pic = "/public/upload/userimages/touxiang.png";
	    if(Session::has('Member')){
			  $res = Session::get('Member');
			  $uid = $res['id'];
			  $names = $res['name'];
			  $pic = $res['photo'];
		  }
		  $status = '1';
		  if($config['commentaudit_close'] = 1){
		      $status = '0';
		      $msg = '-但需要管理员审核后展现';
		  }
	    $data = ['title'=>$pinl['title'],'aid'=>$pinl['id'],'uid'=>$uid,'plname'=>$names,'plpic'=>$pic,'content'=>$pinl['content'],'status'=>$status,'create_time'=>time()];
	    $res = Db::name('comment')->insert($data);
	    $aid = Db::name('comment')->getLastInsID();
	    $arct = Db::name('article_data')->where(['aid'=>$pinl['id']])->field('comment_t')->find();
	    
	    $arcomt = '1';
	        if($arct['comment_t'] !== "0"){
	            $arcomt = $arct['comment_t']+1;
	        }
	    if($res){
	        Db::name('comment_data')->insert(['cid'=>$aid]);
	        Db::name('article_data')->where(['aid'=>$pinl['id']])->update(['comment_t'=>$arcomt]);
	        $this->success("评论成功!$msg");
	    }else{
	        $this -> error("评论失败!");
	    }
	    }else{
	        $this -> error("非法请求!");
	        return false;
	    }
	}
	
	
			//评论回复提交地址
	public function arphui(Request $request)
	{
	    if(request()->isPost()){
	    $pinl  = !empty(input('post.')) ? input('post.') : $request->post();
	    $config = $this->getsystems();
	    $msg="";
	    if($config['comment_close'] != 0)
	    {
	       $this -> error("管理员已关闭评论功能!");
	       return false;
	    }
	    $arcon = Db::name("article")->where("id",$pinl['id'])->field('comment')->find();
	    if($arcon['comment'] != 0){
	      $this -> error("当前文章评论已关闭!");
	      return false;
	    }
	    if($pinl['content'] == '' or $pinl['content'] == NULL or $pinl['content'] == " "){
	      $this -> error("请先填写内容!");
	      return false;
	    }
	    $pincon = delete_XSS($pinl['content']);//过滤评论，防止XSS和sql攻击 还需优化
	    $pbc = Db::name('system')->where('id',1)->field('shielding')->find();
	    $pbcc = $this->sensitive($pbc,$pincon);//过滤后台屏蔽词典 防止恶意评论
	    if($pbcc['status'] != 0)
	    {
	      $this -> error("您的内容包含(-".$pbcc['message']."-)敏感词,请修正!");
	      return false;  
	    }elseif ($pbcc['status'] = 1) {
	        $pbcc = $pbcc['log'];
	    }
	    $names = "游客".time()."";
	    $uid = NULL;
	    $pic = "/public/upload/userimages/touxiang.png";
	    if(Session::has('Member')){//判断当前评论者是否登录，已登录写入登录信息
			  $res = Session::get('Member');
			  $uid = $res['id'];
			  $names = $res['name'];
			  $pic = $res['photo'];
		  }
		  $status = '1';
		  if($config['commentaudit_close'] = 1){//判断是否需要后台审核
		      $status = '0';//如果需要 则新增评论状态修改为0待审核
		      $msg = '-但需要管理员审核后展现';
		  }
	    $data = ['title'=>$pinl['title'],'aid'=>$pinl['id'],'pid'=>$pinl['pid'],'uid'=>$uid,'plname'=>$names,'plpic'=>$pic,'content'=>$pinl['content'],'status'=>$status,'create_time'=>time()];//拼装新增数据
	    $res = Db::name('comment')->insert($data);//新增评论
	    $aid = Db::name('comment')->getLastInsID();//获取新增评论id
	    $arct = Db::name('article_data')->where(['aid'=>$pinl['id']])->field('comment_t')->find();
	    //评论数量自增
	    $arcomt = '1';
	        if($arct['comment_t'] !== "0"){
	            $arcomt = $arct['comment_t']+1;
	        }
	    if($res){
	        Db::name('comment_data')->insert(['cid'=>$aid]);//同步创建评论附属表
	        Db::name('article_data')->where(['aid'=>$pinl['id']])->update(['comment_t'=>$arcomt]);//当前文章评论总数修改
	        $this->success("回复成功!$msg");
	    }else{
	        $this -> error("回复失败!");
	    }
	    }else{
	        $this -> error("非法请求!");
	        return false;
	    }
	}
	
	
	 //点赞提交
    public function subLikes(Request $request)
	{
	    $id = $request->post('id');
		//获取当前客户端的IP地址
		$ip = $request->ip();
		//当前已登录
		if(Session::has('Member')){
			//获取当前会员的点赞列表
			$like = Db::name('member')->where('id',Session::get('Member.id'))->field('artlikes')->find();
			if($like['artlikes'] !== NULL){
			    //根据","进行字符串转数组
    			$array = explode(',',$like['artlikes']);
    			//判断数组中是否存在$id
    			if(in_array($id,$array)){
    				foreach($array as $k=>$v)
    				{
    					//存在，则是删除
    					if($v == $id){
    						unset($array[$k]);
    						if($array == NULL){
    						    //更新数据
    						    $res = Db::name('member')->where('id',Session::get('Member.id'))->update(['artlikes'=>NULL]);
    						}else{
        						//数组转字符串
        						$str = implode(",",$array);
        						//更新数据
        						$res = Db::name('member')->where('id',Session::get('Member.id'))->update(['artlikes'=>$str]);
    						}
    						if($res){
    							//查询当前文章的点赞总量
    							$data = Db::name('article_data')->where('aid',$id)->field('likes')->find();
    							//查询当前文章点过赞的IP地址
    					        $cip = Db::name('article')->where('id',$id)->field('likes_ip')->find();
    					        if($cip['likes_ip'] !== NULL){
                    				//根据","进行字符串转数组
                    				$ip_array = explode(',',$cip['likes_ip']);
                    				//判断当前IP地址是否存在
                    				if(in_array($ip,$ip_array)){
                    				    foreach ($ip_array as $k=>$v){
                    				        //存在则删除
                    				        unset($ip_array[$k]);
                    				        if($ip_array == NULL){
                    				            Db::name('article')->where('id',$id)->update(['likes_ip'=>NULL]);
                    				        }else{
                    				            $str = implode(',',$ip_array);
                    				            Db::name('article')->where('id',$id)->update(['likes_ip'=>$str]);
                    				        }
                    				    }
                    				}
                    			}
    							//判断当前文章点赞量
    							if($data['likes'] == 0){
    								$this->success('已取消!');
    							}else{
    								//点赞总量减1
    								Db::name('article_data')->where('aid',$id)->setDec('likes');
    								$this->success('已取消!');
    							}
    						}else{
    							$this->error("取消失败!");
    						}
    					}
    				}
    			}else{
    				//将$id添加到数组中
    				array_push($array,$id);
    				//数组转字符串
    				$str = implode(",",$array);
    				//更新数据
    				$res = Db::name('member')->where('id',Session::get('Member.id'))->update(['artlikes'=>$str]);
    				if($res){
    					//查询当前文章点过赞的IP地址
    					$cip = Db::name('article')->where('id',$id)->field('likes_ip')->find();
    					if($cip['likes_ip'] !== NULL){
    						//根据","进行字符串转数组
    						$ip_array = explode(',',$cip['likes_ip']);
    						//将$id添加到数组中
    						array_push($ip_array,$ip);
    						//数组转字符串
    						$str = implode(",",$ip_array);
    						Db::name('article')->where('id',$id)->update(['likes_ip'=>$str]);
    					}else{
    						Db::name('article')->where('id',$id)->update(['likes_ip'=>$ip]);
    					}
    					//点赞总量加1
    					Db::name('article_data')->where('aid',$id)->setInc('likes');
    					$this->success('已点赞!');
    				}else{
    					$this->error('点赞失败!');
    				}
    			}
			}else{
			    //查询当前文章点过赞的IP地址
    			$cip = Db::name('article')->where('id',$id)->field('likes_ip')->find();
    			if($cip['likes_ip'] !== NULL){
    				//根据","进行字符串转数组
    				$ip_array = explode(',',$cip['likes_ip']);
    				//将$id添加到数组中
    				array_push($ip_array,$ip);
    				//数组转字符串
    				$str = implode(",",$ip_array);
    				Db::name('article')->where('id',$id)->update(['likes_ip'=>$str]);
    			}else{
    				Db::name('article')->where('id',$id)->update(['likes_ip'=>$ip]);
    			}
			    //更新数据
    			$res = Db::name('member')->where('id',Session::get('Member.id'))->update(['artlikes'=>$id]);
    			if($res){
    				//点赞总量加1
    				Db::name('article_data')->where('aid',$id)->setInc('likes');
    				$this->success('已点赞!');
    			}else{
    				$this->error('点赞失败!');
    			}
			}
		}else{//当前未登录
			//获取当前文章所以点过赞的IP地址
			$cip = Db::name('article')->where('id',$id)->field('likes_ip')->find();
			if($cip['likes_ip'] !== NULL){
			    //根据","进行字符串转数组
    			$ip_array = explode(',',$cip['likes_ip']);
    			if(in_array($ip,$ip_array)){
    				foreach($ip_array as $k=>$v)
    				{
    					//存在，则是删除
    					if($v == $ip){
    						unset($ip_array[$k]);
    						if($ip_array==NULL){
    						    //更新数据
    						    $res = Db::name('article')->where('id',$id)->update(['likes_ip'=>NULL]);
    						}else{
        						//数组转字符串
        						$str = implode(",",$ip_array);
        						//更新数据
        						$res = Db::name('article')->where('id',$id)->update(['likes_ip'=>$str]);
    						}
    						if($res){
    							//查询当前文章的点赞总量
    							$data = Db::name('article_data')->where('aid',$id)->field('likes')->find();
    							//判断当前文章点赞量
    							if($data['likes'] == 0){
    								$this->success('已取消!');
    							}else{
    								//点赞总量减1
    								Db::name('article_data')->where('aid',$id)->setDec('likes');
    								$this->success('已取消!');
    							}
    						}else{
    							$this->error("取消失败!");
    						}
    					}
    				}	
    			}else{
    				//将$id添加到数组中
    				array_push($ip_array,$ip);
    				//数组转字符串
    				$str = implode(",",$ip_array);
    				//更新数据
    				$res = Db::name('article')->where('id',$id)->update(['likes_ip'=>$str]);
    				if($res){
    					//点赞总量加1
    					Db::name('article_data')->where('aid',$id)->setInc('likes');
    					$this->success('已点赞!');
    				}else{
    					$this->error('点赞失败!');
    				}
    			}
			}else{
			    //更新数据
    			$res = Db::name('article')->where('id',$id)->update(['likes_ip'=>$ip]);
    			if($res){
    				//点赞总量加1
    				Db::name('article_data')->where('aid',$id)->setInc('likes');
    				$this->success('已点赞!');
    			}else{
    				$this->error('点赞失败!');
    			}
			}
		}
	}
	
	//评论的点赞和取消点赞
	public function comlikes(Request $request){
	    $id = $request->param('id');
	    		//获取当前客户端的IP地址
		$ip = $request->ip();
		//当前已登录
		if(Session::has('Member')){
			//获取当前会员的点赞列表
			$like = Db::name('member')->where('id',Session::get('Member.id'))->field('commlieks')->find();
			if($like['commlieks'] !== NULL){
			    //根据","进行字符串转数组
    			$array = explode(',',$like['commlieks']);
    			//判断数组中是否存在$id
    			if(in_array($id,$array)){
    				foreach($array as $k=>$v)
    				{
    					//存在，则是删除
    					if($v == $id){
    						unset($array[$k]);
    						if($array == NULL){
    						    //更新数据
    						    $res = Db::name('member')->where('id',Session::get('Member.id'))->update(['commlieks'=>NULL]);
    						}else{
        						//数组转字符串
        						$str = implode(",",$array);
        						//更新数据
        						$res = Db::name('member')->where('id',Session::get('Member.id'))->update(['commlieks'=>$str]);
    						}
    						if($res){
    							//查询当前文章的点赞总量
    							$data = Db::name('comment_data')->where('cid',$id)->field('likes')->find();
    							//查询当前文章点过赞的IP地址
    					        $cip = Db::name('comment_data')->where('cid',$id)->field('comment_ip')->find();
    					        if($cip['comment_ip'] !== NULL){
                    				//根据","进行字符串转数组
                    				$ip_array = explode(',',$cip['comment_ip']);
                    				//判断当前IP地址是否存在
                    				if(in_array($ip,$ip_array)){
                    				    foreach ($ip_array as $k=>$v){
                    				        //存在则删除
                    				        unset($ip_array[$k]);
                    				        if($ip_array == NULL){
                    				            Db::name('comment_data')->where('cid',$id)->update(['comment_ip'=>NULL]);
                    				        }else{
                    				            $str = implode(',',$ip_array);
                    				            Db::name('comment_data')->where('cid',$id)->update(['comment_ip'=>$str]);
                    				        }
                    				    }
                    				}
                    			}
    							//判断当前文章点赞量
    							if($data['likes'] == 0){
    								$this->success('已取消!');
    							}else{
    								//点赞总量减1
    								Db::name('comment_data')->where('cid',$id)->setDec('likes');
    								$this->success('已取消!');
    							}
    						}else{
    							$this->error("取消失败!");
    						}
    					}
    				}
    			}else{
    				//将$id添加到数组中
    				array_push($array,$id);
    				//数组转字符串
    				$str = implode(",",$array);
    				//更新数据
    				$res = Db::name('member')->where('id',Session::get('Member.id'))->update(['commlieks'=>$str]);
    				if($res){
    					//查询当前文章点过赞的IP地址
    					$cip = Db::name('comment_data')->where('cid',$id)->field('comment_ip')->find();
    					if($cip['comment_ip'] !== NULL){
    						//根据","进行字符串转数组
    						$ip_array = explode(',',$cip['comment_ip']);
    						//将$id添加到数组中
    						array_push($ip_array,$ip);
    						//数组转字符串
    						$str = implode(",",$ip_array);
    						Db::name('comment_data')->where('cid',$id)->update(['comment_ip'=>$str]);
    					}else{
    						Db::name('comment_data')->where('cid',$id)->update(['comment_ip'=>$ip]);
    					}
    					//点赞总量加1
    					Db::name('comment_data')->where('cid',$id)->setInc('likes');
    					$this->success('已点赞!');
    				}else{
    					$this->error('点赞失败!');
    				}
    			}
			}else{
			    //查询当前文章点过赞的IP地址
    			$cip = Db::name('comment_data')->where('cid',$id)->field('comment_ip')->find();
    			if($cip['comment_ip'] !== NULL){
    				//根据","进行字符串转数组
    				$ip_array = explode(',',$cip['comment_ip']);
    				//将$id添加到数组中
    				array_push($ip_array,$ip);
    				//数组转字符串
    				$str = implode(",",$ip_array);
    				Db::name('comment_data')->where('cid',$id)->update(['comment_ip'=>$str]);
    			}else{
    				Db::name('comment_data')->where('cid',$id)->update(['comment_ip'=>$ip]);
    			}
			    //更新数据
    			$res = Db::name('member')->where('id',Session::get('Member.id'))->update(['commlieks'=>$id]);
    			if($res){
    				//点赞总量加1
    				Db::name('comment_data')->where('cid',$id)->setInc('likes');
    				$this->success('已点赞!');
    			}else{
    				$this->error('点赞失败!');
    			}
			}
		}else{//当前未登录
			//获取当前文章所以点过赞的IP地址
			$cip = Db::name('comment_data')->where('cid',$id)->field('comment_ip')->find();
			if($cip['comment_ip'] !== NULL){
			    //根据","进行字符串转数组
    			$ip_array = explode(',',$cip['comment_ip']);
    			if(in_array($ip,$ip_array)){
    				foreach($ip_array as $k=>$v)
    				{
    					//存在，则是删除
    					if($v == $ip){
    						unset($ip_array[$k]);
    						if($ip_array==NULL){
    						    //更新数据
    						    $res = Db::name('comment_data')->where('cid',$id)->update(['comment_ip'=>NULL]);
    						}else{
        						//数组转字符串
        						$str = implode(",",$ip_array);
        						//更新数据
        						$res = Db::name('comment_data')->where('cid',$id)->update(['comment_ip'=>$str]);
    						}
    						if($res){
    							//查询当前文章的点赞总量
    							$data = Db::name('comment_data')->where('cid',$id)->field('likes')->find();
    							//判断当前文章点赞量
    							if($data['likes'] == 0){
    								$this->success('已取消!');
    							}else{
    								//点赞总量减1
    								Db::name('comment_data')->where('cid',$id)->setDec('likes');
    								$this->success('已取消!');
    							}
    						}else{
    							$this->error("取消失败!");
    						}
    					}
    				}	
    			}else{
    				//将$id添加到数组中
    				array_push($ip_array,$ip);
    				//数组转字符串
    				$str = implode(",",$ip_array);
    				//更新数据
    				$res = Db::name('comment_data')->where('cid',$id)->update(['comment_ip'=>$str]);
    				if($res){
    					//点赞总量加1
    					Db::name('comment_data')->where('cid',$id)->setInc('likes');
    					$this->success('已点赞!');
    				}else{
    					$this->error('点赞失败!');
    				}
    			}
			}else{
			    //更新数据
    			$res = Db::name('comment_data')->where('cid',$id)->update(['comment_ip'=>$ip]);
    			if($res){
    				//点赞总量加1
    				Db::name('comment_data')->where('cid',$id)->setInc('likes');
    				$this->success('已点赞!');
    			}else{
    				$this->error('点赞失败!');
    			}
			}
		}
	}
	
	//文章浏览量统计
	public function artcoun($id){
	    if($id){
	    Db::name('article_data')->where('aid',$id)->setInc('browse');
	    }
	}

}


