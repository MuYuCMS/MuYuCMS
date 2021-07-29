<?php

namespace app\index\controller;
use app\index\controller\Base;
use think\Request;
use think\Db;
use think\facade\Session;
use think\facade\Cookie;

class Matters extends Base
{
    protected $config;
    protected function initialize()
    {
        parent::initialize();
        $this->config = $this->getsystems();
    }
    /**
     * 显示资源列表
     *
	 *文章列表页
	 *
     * @return \think\Response
     */
    public function matlist(Request $request)
    {
        $catinfo = Db::name("category")->find($request->param("cateid"));
		return $this->fetch('home_temp/'.$this->config["home_temp"].'/'.$catinfo['listtemp'],['catinfo'=>$catinfo]);
    }
    
    
	//文章详情页
	public function matcont(Request $request)
	{
	    $tab = Db::name("model")->field("id,tablename")->select();
	    $matcont = "";
	    $price = false;
	    $prices = ["1"=>"免费","2"=>"付费"];
	    /****内容基本参数改写****/
	    foreach($tab as $tb){
	        $infos = Db::name($tb['tablename'])
	        ->alias("a")
	        ->join($tb['tablename']."_data b","b.aid=a.id")
	        ->where("a.id",$request->param("contid"))
	        ->fieldRaw("a.*,b.browse,b.likes,b.comment_t")
	        ->find();
	        if($infos !== NULL){
	            $matcont = $infos;
	        }
	        $matcont['tabname'] = $tb['id'];
	    }
	    $category = Db::name("category")->find($matcont['mid']);
		$matcont['catitle'] = $category['title'];
	    $catinfo['id'] = $category['id'];
        $catinfo['pid'] = $category['pid'];
	    $member = "";
	    if($matcont["isadmin"] == 0){
	    if($matcont['uid'] > 0 || $matcont['uid'] !== null){    
	    $member = Db::name("member")
	    ->alias("a")
	    ->join("member_data b","b.uid=a.id")
	    ->where("a.id",$matcont['uid'])
	    ->fieldRaw("a.name,a.photo,a.email,a.intro,a.home_count,b.comment,b.le_word,b.attention,b.fans,b.contribute")
	    ->find();
	    if($member){
	        $matcont['autro'] = $member['intro'];
	        $matcont['auhct'] = $member['home_count'];
	    }
	    $matcont['autro'] = "游客投稿";
	    $matcont['auhct'] = "**";
	    }
	    $matcont['autro'] = "游客投稿";
	    $matcont['auhct'] = "**";
	    }elseif($matcont["isadmin"] == 1){
	    $member = Db::name("admin")
	    ->alias("a")
	    ->join("admin_data b","b.uid=a.id")
	    ->where("a.id",$matcont['uid'])
	    ->fieldRaw("a.name,a.photo,a.email,b.comment,b.le_word,b.attention,b.fans,b.contribute")
	    ->find();
	    $matcont['autro'] = $member['name'];
	    $matcont['auhct'] = "999+";
	    }
	    if(!empty($member)){
	    $matcont['auname'] = $member['name'];
	    $matcont['aupot'] = $member['photo'];
	    $matcont['aueml'] = $member['email'];
	    $matcont['aucom'] = $member['comment'];
	    $matcont['aulrd'] = $member['le_word'];
	    $matcont['auaton'] = $member['attention'];
	    $matcont['aufans'] = $member['fans'];
	    $matcont['auctbe'] = $member['contribute'];
	    }else{
	    $matcont['auname'] = "未知";
	    $matcont['aupot'] = "未知";
	    $matcont['aueml'] = "未知";
	    $matcont['aucom'] = "未知";
	    $matcont['aulrd'] = "未知";
	    $matcont['auaton'] = "未知";
	    $matcont['aufans'] = "未知";
	    $matcont['auctbe'] = "未知";
	    }
	    /****处理下载地址****/
	    $field = Db::name("modfiel")->where(["modid"=>$matcont['tabname'],"forms"=>"down"])->field("field,forms")->find();
	    if(!empty($field)){
	    $patch = "";
	    if(!empty($matcont[$field["field"]])){
		$patch = explode(',',$matcont[$field["field"]]);
		$patch = array_chunk($patch,2);
		foreach($matcont as $val){
		    $matcont[$field["field"]] = $patch;
		}
		foreach($matcont[$field["field"]] as $key=>$val){
		    $matcont[$field["field"]][$key]['0'] = $val['0'];
		    $matcont[$field["field"]][$key]['ptname'] = $val['0'];
		    unset($matcont[$field["field"]][$key]['0']);
		    $matcont[$field["field"]][$key]['1'] = $val['1'];
		    $matcont[$field["field"]][$key]['pturl'] = $val['1'];
		    unset($matcont[$field["field"]][$key]['1']);
		}
		}
	    }
	    
		/****处理下载地址完毕****/
	    /****内容基本参数改写完毕****/
	    if($matcont['delete_time'] !== NULL){
	       $this->error('这篇文章已经永远离世!');
	       return false;
	    }
	    if($matcont['status'] !== 0){
	       $this->error('这篇文章查看不了!');
	       return false;
	    }
	    
	    if($matcont['price'] == 2){
	    if(Session::has('Member')){
	        //查询当前会员已购买过的文章ID
	        $res = Db::name('Member')->where('id',Session::get('Member.id'))->field('buymat')->find();
	        //判断是否为超级管理员
	        if($matcont['isadmin'] != 1){
	       //判断该文章是否为当前会员发布
	        if($res['id'] !== $matcont['uid']){
	            if(isset($res['buymat'])){
	                //字符串转数组
	                $buyArray = explode(",",$res['buymat']);
	                //去掉数组里面的空值
                    $ayy = array_filter($buyArray);
	            }else{
	                $ayy = array($res['buymat']);
	            }
	            if(in_array($matcont['id'],$ayy)){
	                $price = true;
	            }
	                
	        }else{
	            $price = true;
	        }
	        }else{
	            if(isset($res['buymat'])){
	                $buyArray = explode(",",$res['buymat']);
	                //去掉数组里面的空值
                    $ayy = array_filter($buyArray);
	            }else{
	                $ayy = array($res['buymat']);
	            }
	            if(in_array($matcont['id'],$ayy)){
	                $price = true;
	            }
	        }
	    }else{
	        $this -> redirect('index/Index/login');
	    }
	    }elseif($matcont['price'] == 1){
	        $price = true;
	    }
	        $matcont["price"] = $prices[$matcont["price"]];
	    if($price == true){
	        return $this-> fetch('/home_temp/'.$this->config["home_temp"].'/'.$category['conttemp'],['matcont'=>$matcont,'catinfo'=>$catinfo]);
	    }elseif($price == false){
	        $this->redirect('index/matters/toplay?matterid='.$matcont['id']);
	    }
	}
	
	
	//订单详情
	public function toplay(Request $request)
	{
	    //接收文章ID
	    $matid = $request->param("matterid");
	    //查询文章标题，作者，付费金额
        $tab = Db::name("model")->field("tablename")->select();
        foreach($tab as $va){
            $infs = Db::name($va["tablename"])->field("id,title,author,moneys")->find($matid);
            if($infs != null){
                $info = $infs;
            }
        }
        //查询余额
        $member = Db::name('Member')->where('id',Session::get('Member.id'))->field('id,money')->find();
        //还需支付
        $info['need'] = $member['money'] - $info['moneys'];
        if($info['need'] < 0){
            //取绝对值
            $info['need'] = abs($info['need']);
        }else{
            $info['need'] = 0.00;
        }
	    return $this->fetch('/home_temp/'.$this->config["home_temp"].'/topaly',["pay"=>$info,'member'=>$member]);
	}
	
	//评论提交地址
	public function discuss(Request $request)
	{
	    if(request()->isPost()){
	    $pinl  = !empty(input('post.')) ? input('post.') : $request->post();
	    $config = $this->getsystems();
	    $msg="";
	    $msg1 = "评论";
	    if($config['comment_close'] != 0)
	    {
	       $this -> error("管理员已关闭评论功能!");
	       return false;
	    }
	    $tab = Db::name("model")->field("tablename")->select();
	    foreach($tab as $v){
	    $arcon = Db::name($v["tablename"])->field('comment')->find($pinl['matid']);
	    $arcon["tab"] = $v["tablename"];
	    }
	    if($arcon['comment'] != 0){
	      $this -> error("当前文章评论已关闭!");
	      return false;
	    }
	    $pincon = delete_XSS($pinl['content']);
	    if(empty($pincon) || $pincon == NULL){
	      $this -> error("请先填写内容!");
	      return false;
	    }
	    $pbcc = $this->sensitive($config["shielding"],$pincon);
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
		$compid = 0;
		if(isset($pinl['pid']) || empty($pinl['pid'])){
		    $compid = $pinl['pid'];
		    $msg1 = "回复";
		}
	    $data = ['title'=>$pinl['title'],'aid'=>$pinl['matid'],$compid,'uid'=>$uid,'plname'=>$names,'plpic'=>$pic,'content'=>$pinl['content'],'status'=>$status,'create_time'=>time()];
	    $res = Db::name('comment')->insert($data);
	    $aid = Db::name('comment')->getLastInsID();
	    $arct = Db::name($arcon["tab"].'_data')->where(['aid'=>$pinl['matid']])->field('comment_t')->find();
	    $arcomt = '1';
	        if($arct['comment_t'] !== "0"){
	            $arcomt = $arct['comment_t']+1;
	        }
	    if($res){
	        Db::name('comment_data')->insert(['cid'=>$aid]);
	        Db::name($arcon["tab"].'_data')->where(['aid'=>$pinl['matid']])->update(['comment_t'=>$arcomt]);
	        //今日大数据表文章评论字段自增1
	        Db::name('bigdata')->whereTime('create_time','today')->setInc('article_comment');
	        $this->success("$msg1成功!$msg");
	    }else{
	        $this -> error("$msg1失败!");
	    }
	    }else{
	        $this -> error("非法请求!");
	        return false;
	    }
	}
	
	
	 //点赞提交
    public function subLikes(Request $request)
	{
	    $id = $request->post('matid');
	    $mod = Db::name("model")->field("tablename")->select();
	    $tab = [];
	    foreach($mod as $key=>$v){
	        $arr = Db::name($v["tablename"])->find($id);
	        if($arr != null){
	            $mod = $arr;
	            $tab['tabn'] = $v["tablename"];
	        }
	    }
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
    							//查询当前文章点过赞的IP地址
    					        $cip = Db::name($tab['tabn'])->where('id',$id)->field('likes_ip')->find();
    					        if($cip['likes_ip'] !== NULL){
                    				//根据","进行字符串转数组
                    				$ip_array = explode(',',$cip['likes_ip']);
                    				//判断当前IP地址是否存在
                    				if(in_array($ip,$ip_array)){
                    				    foreach ($ip_array as $k=>$v){
                    				        //存在则删除
                    				        unset($ip_array[$k]);
                    				        if($ip_array == NULL){
                    				            Db::name($tab['tabn'])->where('id',$id)->update(['likes_ip'=>NULL]);
                    				        }else{
                    				            $str = implode(',',$ip_array);
                    				            Db::name($tab['tabn'])->where('id',$id)->update(['likes_ip'=>$str]);
                    				        }
                    				    }
                    				}
                    			}
    							//查询当前文章的点赞总量
    							$data = Db::name($tab['tabn'].'_data')->where('aid',$id)->field('likes')->find();
    							//查询今天大数据表的数据
    							$bigdata = Db::name('bigdata')->whereTime('create_time','today')->field('article_likes')->find();
    							//判断当前文章点赞量
    							if($data['likes'] == 0){
    							    if($bigdata['article_likes'] == 0){
    								    $this->success('已取消!');
    							    }else{
    							        //大数据表文章点赞字段的值自减1
    							        Db::name('bigdata')->whereTime('create_time','today')->setDec('article_likes');
    							        $this->success('已取消!');
    							    }
    							}else{
    								//点赞总量减1
    								Db::name($tab['tabn'].'_data')->where('aid',$id)->setDec('likes');
    							    if($bigdata['article_likes'] == 0){
    								    $this->success('已取消!');
    							    }else{
    							        //大数据表文章点赞字段的值自减1
    							        Db::name('bigdata')->whereTime('create_time','today')->setDec('article_likes');
    							        $this->success('已取消!');
    							    }
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
    					$cip = Db::name($tab['tabn'])->where('id',$id)->field('likes_ip')->find();
    					if($cip['likes_ip'] !== NULL){
    						//根据","进行字符串转数组
    						$ip_array = explode(',',$cip['likes_ip']);
    						//将$id添加到数组中
    						array_push($ip_array,$ip);
    						//数组转字符串
    						$str = implode(",",$ip_array);
    						Db::name($tab['tabn'])->where('id',$id)->update(['likes_ip'=>$str]);
    					}else{
    						Db::name($tab['tabn'])->where('id',$id)->update(['likes_ip'=>$ip]);
    					}
    					//点赞总量加1
    					Db::name($tab['tabn'].'_data')->where('aid',$id)->setInc('likes');
    					//大数据表文章点赞字段的值自增1
    					Db::name('bigdata')->whereTime('create_time','today')->setInc('article_likes');
    					$this->success('已点赞!');
    				}else{
    					$this->error('点赞失败!');
    				}
    			}
			}else{
			    //查询当前文章点过赞的IP地址
    			$cip = Db::name($tab['tabn'])->where('id',$id)->field('likes_ip')->find();
    			if($cip['likes_ip'] !== NULL){
    				//根据","进行字符串转数组
    				$ip_array = explode(',',$cip['likes_ip']);
    				//将$id添加到数组中
    				array_push($ip_array,$ip);
    				//数组转字符串
    				$str = implode(",",$ip_array);
    				Db::name($tab['tabn'])->where('id',$id)->update(['likes_ip'=>$str]);
    			}else{
    				Db::name($tab['tabn'])->where('id',$id)->update(['likes_ip'=>$ip]);
    			}
			    //更新数据
    			$res = Db::name('member')->where('id',Session::get('Member.id'))->update(['artlikes'=>$id]);
    			if($res){
    				//点赞总量加1
    				Db::name($tab['tabn'].'_data')->where('aid',$id)->setInc('likes');
					//大数据表文章点赞字段的值自增1
					Db::name('bigdata')->whereTime('create_time','today')->setInc('article_likes');
    				$this->success('已点赞!');
    			}else{
    				$this->error('点赞失败!');
    			}
			}
		}else{//当前未登录
			//获取当前文章所以点过赞的IP地址
			$cip = Db::name($tab['tabn'])->where('id',$id)->field('likes_ip')->find();
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
    						    $res = Db::name($tab['tabn'])->where('id',$id)->update(['likes_ip'=>NULL]);
    						}else{
        						//数组转字符串
        						$str = implode(",",$ip_array);
        						//更新数据
        						$res = Db::name($tab['tabn'])->where('id',$id)->update(['likes_ip'=>$str]);
    						}
    						if($res){
    							//查询当前文章的点赞总量
    							$data = Db::name($tab['tabn'].'_data')->where('aid',$id)->field('likes')->find();
    							//查询今天大数据表的数据
    							$bigdata = Db::name('bigdata')->whereTime('create_time','today')->field('article_likes')->find();
    							//判断当前文章点赞量
    							if($data['likes'] == 0){
    							    if($bigdata['article_likes'] == 0){
    								    $this->success('已取消!');
    							    }else{
    							        //大数据表文章点赞字段的值自减1
    							        Db::name('bigdata')->whereTime('create_time','today')->setDec('article_likes');
    							        $this->success('已取消!');
    							    }
    							}else{
    								//点赞总量减1
    								Db::name($tab['tabn'].'_data')->where('aid',$id)->setDec('likes');
    							    if($bigdata['article_likes'] == 0){
    								    $this->success('已取消!');
    							    }else{
    							        //大数据表文章点赞字段的值自减1
    							        Db::name('bigdata')->whereTime('create_time','today')->setDec('article_likes');
    							        $this->success('已取消!');
    							    }
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
    				$res = Db::name($tab['tabn'])->where('id',$id)->update(['likes_ip'=>$str]);
    				if($res){
    					//点赞总量加1
    					Db::name($tab['tabn'].'_data')->where('aid',$id)->setInc('likes');
    					//大数据表文章点赞字段的值自增1
					    Db::name('bigdata')->whereTime('create_time','today')->setInc('article_likes');
    					$this->success('已点赞!');
    				}else{
    					$this->error('点赞失败!');
    				}
    			}
			}else{
			    //更新数据
    			$res = Db::name($tab['tabn'])->where('id',$id)->update(['likes_ip'=>$ip]);
    			if($res){
    				//点赞总量加1
    				Db::name($tab['tabn'].'_data')->where('aid',$id)->setInc('likes');
    				//大数据表文章点赞字段的值自增1
					Db::name('bigdata')->whereTime('create_time','today')->setInc('article_likes');
    				$this->success('已点赞!');
    			}else{
    				$this->error('点赞失败!');
    			}
			}
		}
	}
	
	//评论的点赞和取消点赞
	public function comlikes(Request $request){
	    $id = $request->param('comid');
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
	public function artcoun(Request $request){
	    $tab = Db::name("model")->field("tablename")->select();
	    //接收文章ID
	    $id = $request->param("matid");
	    foreach($tab as $v){
	        $rarr = Db::name($v["tablename"])->find($id);
	        if($rarr != null){
	            //利用Cookie实现防刷浏览量
	            if(!Cookie::has('matcont_'.$id)){
	                // 设置Cookie 有效期为 一天
                    Cookie::set('matcont_'.$id,$id,86400);
	                Db::name($v['tablename']."_data")->where('aid',$id)->setInc('browse');
	                //同步到大数据表
	                Db::name('bigdata')->whereTime('create_time','today')->setInc('article_browse');
	                }
	        }
	    }
	}
	
	public function artsctform(Request $request){
	    $tepm = $request->param();
	    $info = "";
	    $tpname = "";
	    if(!empty($tepm["formid"])){
	        $info = Db::name("custform")->find($tepm["formid"]);
	        $tpname = explode(".",$info["fielname"]);
	    }elseif(!empty($tepm["t"])){
	        $info = Db::name("custform")->where("fielname",$tepm["t"])->find();
	        $tpname = explode(".",$info["fielname"]);
	    }
	    if(!empty($info)){
	        return $this-> fetch($info["path"]);
	    }else{
	        return $this->error("访问出错啦!");
	    }
	}

}


