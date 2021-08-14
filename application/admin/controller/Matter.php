<?php
namespace app\admin\controller;
use app\admin\controller\Base;
use think\Request;
use think\facade\Session;
use think\Db;
use think\facade\Config;
use think\facade\Env;

class Matter extends Base
{
    protected $entrance;
    protected function initialize()
    {
        parent::initialize();
        $this->entrance = Config::get("safety.adname");
    }
    //资讯列表输出
    public function index(Request $request)
    {
        $cateid="";
        $mod = [];
		$list="";
		$status = ["msg"=>"暂时没有内容","status"=>1];
        if(!empty($request->param('cateid'))){
            $cateid = Db::name('category')->where(['id'=>$request->param('cateid')])->field('id,title,pid,modid')->find();
        }else{
            $cateid = Db::name('category')->order('id','asc')->field('id,title,pid,modid')->find();
        }
		$watch = Db::name('model')->field("id,tablename")->select();
		foreach($watch as $key=>$va){
		    $mod[$va['id']] = $va['tablename'];
		}
		$category = Db::name('category')->where(["status"=>1,"type"=>0])->select()->toArray();
		foreach($category as $key=>$cate){
			$category[$key]['href'] = "/".$this->entrance."/matter/index/cateid/".$cate['id'];
			$category[$key]['spread'] = true;
		}
		$category = json_encode(alldigui($category,0),JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
		if(empty($cateid)){
            $status["msg"] = "没有栏目数据,无法查看内容!";
            $status["status"] = 0;
            return view('index',['list'=>$list,'cateid'=>$cateid,'category'=>$category,'status'=>$status]);
        }
		$modif = Db::name('model')->find($cateid['modid']);
        if($modif == NULL){
        $status["msg"] = "模型已丢失!请重新指定栏目ID-".$cateid['id']."-所属模型!";
        $status["status"] = 0;
        return view('index',['list'=>$list,'cateid'=>$cateid,'category'=>$category,'status'=>$status]);
        }
		if(!empty($mod)){
		    $tab = $mod[$cateid['modid']];
		    $cateid['modid'] = $tab;
		    $contid = $cateid['id'];
		    if($cateid['pid'] == 0){
            $allid = Db::name('category')->where(['pid'=>$contid])->field('id')->select()->toArray();
            foreach($allid as $vcc){
                 $contid .= ",".$vcc['id'];
            }
            }
		    $list = Db::name($tab)->where("mid","in",$contid)->where(['status'=>[0,1,2],'delete_time'=>NULL])->order("create_time desc")->field('id,mid,title,titlepic,author,create_time,status,top,ppts,price')->paginate(25);
		}
		return view('index',['list'=>$list,'category'=>$category,'cateid'=>$cateid,'status'=>$status]);
    }
    //添加文章
	public function add(Request $request)
	{
	    if($request->isAjax()){
	        $data = $request->param();
	        $tab = $data['modid'];;
	        unset($data['modid']);
	        if(isset($data['file'])){
	        unset($data['file']);
	        }
	        if(isset($data['upfile'])){
	        unset($data['upfile']);    
	        }
		    if(isset($data['editor'])){
	        $data['editor'] = html_entity_decode(htmlspecialchars_decode($data['editor']));    
	        }  
	        $data['create_time'] = time();
	        $data['update_time'] = time();
	        $setid = setconid();
	        $data['id'] = $setid['id'] + 1;
	        $ord = Db::name($tab)->select();//当前模型表文章自动排序
	        if($ord != NULL || $ord != ""){
	            foreach($ord as $key=>$val){
		 	    $data['orders'] = $val["orders"] + 1;
		        }
	        }
	        if($data['abstract']){
	        $data['abstract'] = substr($data['abstract'],0,90);
	        }else{
	            $data['abstract'] = substr($data['editor'],0,90);
	        }
	        $data['isadmin'] = 1;//后台发布默认管理员发布
	        $uid=Session::get('Adminuser');
	        $data['author'] = isset($data['author']) ? $data['author'] : "管理员";
	        $data['uid'] = $uid['id'];//文章作者id默认当前登录管理员id
	        $res = Db::name($tab)->insert($data);
	        if($res){
	        if(!isset($data['status'])){     
		    $lms = Db::name("category")->where("id",$data["mid"])->field("pid")->find();
		    if(!empty($lms)){
		    if($lms["pid"] != "0"){
		        Db::name("category")->where(["id"=>$lms["pid"]])->setInc("ar_cont");
		        Db::name("category")->where(["id"=>$data["mid"]])->setInc("ar_cont");
		    }else{
		        Db::name("category")->where("id",$data["mid"])->setInc("ar_cont");
		    }
		    }
	        }
	        Db::name("admin_data")->where("uid",$uid['id'])->setInc("contribute");
		    //建立新增文章附属表
		    Db::name($tab."_data")->insert(['aid'=>$data['id']]); 
		    //在今日大数据表文章发布数字段自增1
		    Db::name('bigdata')->whereTime('create_time','today')->setInc('article_add');
		    return jsonmsg(1,"添加成功");
	        }else{
	        return jsonmsg(0,"添加失败");    
	        }
	    }
	    $modid = Db::name("model")->where("tablename",$request->param("modid"))->field("id,tablename")->find();
	    $field = Db::name('modfiel')->where(["modid"=>$modid['id'],"adst"=>1])->order("orders",'asc')->select()->toArray();
	    foreach($field as $key=>$vals){
	        if($vals['forms'] == 'select' || $vals['forms'] == 'radio' || $vals['forms'] == 'checkbox'){
		        if(!empty($vals['defaults'])){
		        $field[$key]['defaults'] = array_chunk(explode('||',$vals['defaults']),2);
		        }
	        }
	    }
	    $type = Db::name("type")->where("status",1)->field('id,title')->select();
	    $category['lmid'] = $request->param('id');
	    $category['modid'] = $request->param('modid');
	    return view('add',['field'=>$field,'category'=>$category,'type'=>$type]);
	}

    //资讯编辑
    public function edit(Request $request)
    {
        if($request->isAjax()){
            $data = $request->param();
            $tab = $data['modid'];
            unset($data['modid']);
	        if(isset($data['file'])){
	        unset($data['file']);
	        }
	        if(isset($data['upfile'])){
	        unset($data['upfile']);    
	        }
	        if(isset($data['editor'])){
	        $data['editor'] = html_entity_decode(htmlspecialchars_decode($data['editor']));
	        }
	        $data['update_time'] = time();
	        if($data['abstract']){
	        $data['abstract'] = substr($data['abstract'],0,90);
	        }else{
	            $data['abstract'] = substr($data['editor'],0,90);
	        }
	        $res = Db::name($tab)->update($data);
	        if($res){
	            return jsonmsg(1,'修改成功');
	        }else{
	            return jsonmsg(0,'修改失败');
	        }
        }
        $list = Db::name($request->param('modid'))->find($request->param('aid'));
        $modid = Db::name("model")->where(["tablename"=>$request->param("modid")])->field("id")->find();
		$field = Db::name('modfiel')->where(["modid"=>$modid["id"],"adst"=>1])->order("orders",'asc')->select()->toArray();
	    foreach($field as $key=>$vals){
	        if($vals['forms'] == 'select' || $vals['forms'] == 'radio' || $vals['forms'] == 'checkbox'){
		        if(!empty($vals['defaults'])){
		        $field[$key]['defaults'] = array_chunk(explode('||',$vals['defaults']),2);
		        }
	        }
	        foreach($list as $keys=>$vac){
	                if($vals['field'] == $keys){
	                   if($vals['forms'] == 'down'){
	                   $field[$key]['value'] = array_chunk(explode(',',$list[$keys]),2);
	                   }else{
	                   $field[$key]['value'] = $list[$keys];    
	                   }
	                }
	        }
	    }
	    $type = Db::name("type")->where("status",1)->field('id,title')->select();
	    $category['lmid'] = $list["mid"];
	    $category['modid'] = $request->param('modid');	
		return view('edit',['field'=>$field,'category'=>$category,'type'=>$type,'list'=>$list]);
    }
		//撰写内容图片上传
	public function editorup(Request $request){
		    $file = $request->file('editormd-image-file');
		    $url = "/public/upload/images";
		    $photo = "";
			if(!empty($file)){
				//移动到框架指定目录
				$info = $file->validate(['ext'=>'jpg,jpeg,png,gif','size'=>1048576])->rule('uniqid')->move('.'.$url);
				if($info){
					//获取图片名称
					$imgName = str_replace("\\","/",$info->getSaveName());
					$photo = $url.'/'.$imgName;
				}else{
					$error = $file->getError();
				}
			}
			//判断上传是否成功
			if($photo == ""){
				$error = $file->getError();
				return json(['success'=>0,'message'=>"上传失败,{$error}"]);
			}else{
				return json(['success'=>1,'message'=>'上传成功',"url"=>$photo]);
			}
		}
	//文章状态变更
	public function matstatus(Request $request){
	    if($request->isAjax()){
	       $data = Db::name($request->param('modid'))->field('status,mid')->find($request->param('id'));
	       if($data['status'] == 2){
	          Db::name($request->param('modid'))->update(['id'=>$request->param('id'),'status'=>0]);
	          Db::name('category')->where("id",$data["mid"])->setInc("ar_cont");
	       }elseif($data['status'] == 0){
	          Db::name($request->param('modid'))->update(['id'=>$request->param('id'),'status'=>2]);
	          Db::name('category')->where("id",$data["mid"])->setDec("ar_cont");
	       }else{
	          return jsonmsg(0,"状态变更失败!"); 
	       }
	       return jsonmsg(1,"状态已变更");
	    }
	    return jsonmsg(0,'非法操作');
	}
	//文章置顶状态变更
	public function setTop(Request $request){
		if($request->isAjax()){
	       $data = Db::name($request->param('modid'))->field('top')->find($request->param('id'));
	       if($data['top'] == 0){
	          Db::name($request->param('modid'))->update(['id'=>$request->param('id'),'top'=>1]);
	       }elseif($data['top'] == 1){
	          Db::name($request->param('modid'))->update(['id'=>$request->param('id'),'top'=>0]);
	       }else{
	          return jsonmsg(0,"置顶状态变更失败!"); 
	       }
	       return jsonmsg(1,"置顶状态已变更");
	    }
	    return jsonmsg(0,'非法操作');
	}
	//文章幻灯片状态变更
	public function setPpts(Request $request){
		if($request->isAjax()){
	       $data = Db::name($request->param('modid'))->field('ppts')->find($request->param('id'));
	       if($data['ppts'] == 0){
	          Db::name($request->param('modid'))->update(['id'=>$request->param('id'),'ppts'=>1]);
	       }elseif($data['ppts'] == 1){
	          Db::name($request->param('modid'))->update(['id'=>$request->param('id'),'ppts'=>0]);
	       }else{
	          return jsonmsg(0,"幻灯状态变更失败!"); 
	       }
	       return jsonmsg(1,"幻灯状态已变更");
	    }
	    return jsonmsg(0,'非法操作');
	}
	//内容的删除和批量删除
    public function materdel(Request $request)
    {
        if($request->isAjax()){
            if($request->param('all') != NULL){
                $ifda = array_chunk(explode(',',$request->param('all')),3);
            foreach($ifda as $va){
                $mid = Db::name("category")->field("id,pid")->find($va['1']);
                $res = Db::name($va['2'])->where(['id'=>$va['0']])->update(["delete_time"=>time()]);
                if($res){
                if($mid["pid"] != 0){
		        Db::name("category")->where(["id"=>$mid["id"]])->setDec("ar_cont");
		        Db::name("category")->where(["id"=>$mid["pid"]])->setDec("ar_cont");
		        }else{
		        Db::name("category")->where("id",$mid["id"])->setDec("ar_cont");  
		        }
		        Db::name($va['2']."_data")->where(["aid"=>$va['0']])->update(["delete_time"=>time()]);
                }else{
                return jsonmsg(0,'操作失败!');    
                }
            }
            return jsonmsg(1,'已放置回收站');
            }else{
            $mid = Db::name("category")->field("id,pid")->find($request->param('mid'));
            $res = Db::name($request->param('mod'))->where(['id'=>$request->param('id')])->update(["delete_time"=>time()]);
            if($res){
		    if($mid["pid"] != 0){
		        Db::name("category")->where(["id"=>$mid["id"]])->setDec("ar_cont");
		        Db::name("category")->where(["id"=>$mid["pid"]])->setDec("ar_cont");
		    }else{
		        Db::name("category")->where("id",$mid["id"])->setDec("ar_cont");  
		    }
		        Db::name($request->param('mod')."_data")->where(["aid"=>$request->param('id')])->update(["delete_time"=>time()]);
		        return jsonmsg(1,'已放置回收站');
            }
            return jsonmsg(0,'操作失败!');
            }
        }
        return jsonmsg(0,'非法操作');
    }
	//渲染已删除资讯列表
	public function matdellist(Request $request){
		$tab = Db::name('model')->field("tablename")->select();
		$allist = [];
		$list = "";
		foreach($tab as $val){
		    $res = Db::name($val['tablename'])->where("delete_time","<>",NULL)->select()->toArray();
		    foreach($res as $key=>$v){
		       $res[$key]['tabname'] = $val['tablename']; 
		    }
		   $allist[] = $res;
		}
		$num = 0;
		if(!empty($allist)){
		$list = ary3_ary2($allist);
		$num = count($list);
		}
		return view('dellist',['list'=>$list,'num'=>$num]);
	}
	//彻底删除文章
	public function matdelall(Request $request){
	    if($request->isAjax()){
            if($request->param('all') != NULL){
                $ifda = array_chunk(explode(',',$request->param('all')),3);
                foreach($ifda as $va){
                $res = Db::name($va['2'])->where(['id'=>$va['0']])->delete();
                if($res){
		        Db::name($va['2']."_data")->where(["aid"=>$va['0']])->delete();
                }else{
                return jsonmsg(0,'操作失败!');    
                }
            }
            return jsonmsg(1,'已彻底删除');
            }else{
            $res = Db::name($request->param('tabname'))->where(['id'=>$request->param('id')])->delete();
            if($res){
		        Db::name($request->param('tabname')."_data")->where(["aid"=>$request->param('id')])->delete();
		        return jsonmsg(1,'已彻底删除');
            }
            return jsonmsg(0,'操作失败!');
            } 
	    }
	    return jsonmsg(0,'非法操作');
	}
	/*还原单个/批量 已删除内容
	*id    必须：内容id
	*mid    必须 栏目id
	*tabname  必须  所在表
	***************************************************
	*批量****
	*
	*/
	public function materhy(Request $request){
	    if($request->isAjax()){
	       if($request->param('all') != NULL){
	        $data = array_chunk(explode(',',$request->param('all')),3);
	        foreach($data as $vl){
	          $mid = Db::name("category")->where("id",$vl['1'])->find();
	          $res = Db::name($vl['2'])->where('id',$vl['0'])->setField(['delete_time'=>NULL]);
	          if($res){
		        if($mid["pid"] != 0){
		        Db::name("category")->where(["id"=>$vl['1']])->setInc("ar_cont");
		        Db::name("category")->where(["id"=>$mid["pid"]])->setInc("ar_cont");
		        }else{
		        Db::name("category")->where("id",$vl['1'])->setInc("ar_cont");
		        }
		        Db::name($vl['2'].'_data')->where('aid',$vl['0']) ->setField('delete_time', NULL);
	          }else{
	            return jsonmsg(0,'处理失败');  
	          }
	        }
	        return jsonmsg(1,'处理成功');
	       }else{ 
	      if(!empty($request->param("id")) || !empty($request->param("tabname")) || !empty($request->param("mid"))){
	         $mid = Db::name("category")->where("id",$request->param("mid"))->find();
	         $res = Db::name($request->param("tabname"))->where('id',$request->param("id"))->setField(['delete_time'=>NULL]);
	         if($res){
		        if($mid["pid"] != 0){
		        Db::name("category")->where(["id"=>$request->param("mid")])->setInc("ar_cont");
		        Db::name("category")->where(["id"=>$mid["pid"]])->setInc("ar_cont");
		        }else{
		        Db::name("category")->where("id",$request->param("mid"))->setInc("ar_cont");
		        }
		      Db::name($request->param("tabname").'_data')->where('aid',$request->param("id")) ->setField('delete_time', NULL);
	          return jsonmsg(1,'还原成功');  
	         }else{
	          return jsonmsg(0,'还原失败');   
	         }
	      }else{
	       return jsonmsg(0,'缺少必要参数');   
	      }
	     }
	    }
	    return jsonmsg(0,'非法操作');
	}
		//资讯审核列表
	public function mataudit(Request $request){
        $tab = Db::name('model')->field("id,tablename")->select();
		$allist = [];
		$list = "";
		foreach($tab as $val){
		    $res = Db::name($val['tablename'])->where(['status'=>[3,4],'delete_time'=>NULL])->select()->toArray();
		    foreach($res as $key=>$v){
		       $res[$key]['tabname'] = $val['tablename'];
		       $res[$key]['modelid'] = $val['id'];
		    }
		   $allist[] = $res;
		}
		$num = 0;
		if(!empty($allist)){
		$list = ary3_ary2($allist);
		foreach($list as $key=>$va){
		    $lm = Db::name("category")->where(['id'=>$va['mid']])->field('title')->find();
		    $list[$key]['creag'] = $lm['title'];
		}
		$num = count($list);
		}
    return view('audit',['list'=>$list,'num'=>$num]);
	}
		//资讯驳回
	public function reject(Request $request){
	    if($request->isAjax()){
	       $res = Db::name($request->param('tabname'))->update(['id'=>$request->param('id'),'refusal'=>$request->param('refusal'),'status'=>4]);
	       if($res){
	       return jsonmsg(1,'驳回成功');
	       }else{
	       return jsonmsg(1,'驳回失败');    
	       }
	    }
	    return jsonmsg(0,'非法操作');
	}
		//内容审核
    public function matcheck(Request $request){
        if($request->isAjax()){
            if($request->param('all') != NULL){
                $ifda = array_chunk(explode(',',$request->param('all')),3);
            foreach($ifda as $val){
                $mid = Db::name("category")->field("id,pid")->find($val['1']);
                $res = Db::name($val['2'])->where(['id'=>$val['0']])->update(["status"=>0]);
                if($res){
                if($mid["pid"] != 0){
		        Db::name("category")->where(["id"=>$mid["id"]])->setInc("ar_cont");
		        Db::name("category")->where(["id"=>$mid["pid"]])->setInc("ar_cont");
		        }else{
		        Db::name("category")->where("id",$mid["id"])->setInc("ar_cont");  
		        }
		        Db::name($val['2']."_data")->insert(["aid"=>$val['0']]);
                }else{
                return jsonmsg(0,'操作失败!');    
                }
            }
            Db::name('bigdata')->whereTime('create_time','today')->setInc('article_add');
            return jsonmsg(1,'已审核');
            }else{
            $mid = Db::name("category")->field("id,pid")->find($request->param('mid'));
            $res = Db::name($request->param('tabname'))->where(['id'=>$request->param('id')])->update(["status"=>0]);
            if($res){
		    if($mid["pid"] != 0){
		        Db::name("category")->where(["id"=>$mid["id"]])->setInc("ar_cont");
		        Db::name("category")->where(["id"=>$mid["pid"]])->setInc("ar_cont");
		    }else{
		        Db::name("category")->where("id",$mid["id"])->setInc("ar_cont");  
		    }
		        Db::name($request->param('tabname')."_data")->insert(["aid"=>$request->param('id')]);
		        Db::name('bigdata')->whereTime('create_time','today')->setInc('article_add');
		        return jsonmsg(1,'已审核');
            }
            return jsonmsg(0,'操作失败!');
            }
        }
        return jsonmsg(0,'非法操作');
	}
	//审核编辑
	public function editaduit(Request $request){
	if($request->isAjax()){
            $data = $request->param();
            $tab = $data['tabname'];
            unset($data['tabname']);
	        if(isset($data['file'])){
	        unset($data['file']);
	        }
	        if(isset($data['upfile'])){
	        unset($data['upfile']);    
	        }
	        if(isset($data['editor'])){
	        $data['editor'] = html_entity_decode(htmlspecialchars_decode($data['editor']));
	        }
	        $data['update_time'] = time();
	        
	        $data['abstract'] = substr($data['editor'],0,90);
	        
	        $res = Db::name($tab)->update($data);
	        if($res){
	            return jsonmsg(1,'保存成功');
	        }else{
	            return jsonmsg(0,'保存失败');
	        }
        }
        $list = Db::name($request->param('tabname'))->find($request->param('id'));
		$field = Db::name('modfiel')->where(["modid"=>$request->param("modid"),"adst"=>1])->order("orders",'asc')->select()->toArray();
	    foreach($field as $key=>$vals){
	        if($vals['forms'] == 'select' || $vals['forms'] == 'radio' || $vals['forms'] == 'checkbox'){
		        if(!empty($vals['defaults'])){
		        $field[$key]['defaults'] = array_chunk(explode('||',$vals['defaults']),2);
		        }
	        }
	        foreach($list as $keys=>$vac){
	                if($vals['field'] == $keys){
	                   if($vals['forms'] == 'down'){
	                   $field[$key]['value'] = array_chunk(explode(',',$list[$keys]),2);
	                   }else{
	                   $field[$key]['value'] = $list[$keys];    
	                   }
	                }
	        }
	    }
	    $type = Db::name("type")->where("status",1)->field('id,title')->select();
	    $category['mid'] = $request->param('mid');
	    $category['modid'] = $request->param('tabname');	
		return view('editaduit',['field'=>$field,'category'=>$category,'type'=>$type,'list'=>$list]);    
	}
	public function solist(Request $request){
	    $list = $request->param('list');
	    $num = count($list);
	    if(empty($list)){
	        return jsonmsg(0,'没有数据');
	    }
	    return view('solist',['list'=>$list,'num'=>$num]);
	}

}
