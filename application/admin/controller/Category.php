<?php

namespace app\admin\controller;

use app\admin\controller\Base;
use think\Request;
use think\Session;
use app\admin\model\Admin;
use app\admin\model\Category as CategoryModel;
use think\Db;
use think\facade\Env;
use think\Config;

class Category extends Base
{
    //栏目列表
    public function index(Request $request)
    {
		//接收页码
		$page = $request->get('page') ? $request->get('page') : 1;
        $menu = [];
        $menus = Db::name("category")->field("id,pid,title,titlepic,icon,href,sort,status,comment")->select()->toArray();
        $arr = ['0' =>'顶级栏目'];
        foreach($menus as $vs){
            $arr[$vs['id']] = $vs['title'];
        }
        foreach($menus as $key=>$k){
            $menus[$key]['p_title'] = $arr[$k['pid']];
        }
        foreach($menus as $v){
            $menu[] = ["id"=>$v["id"],"pid"=>$v["pid"],"p_title"=>$v["p_title"],"title"=>$v["title"],"titlepic"=>$v["titlepic"],"icon"=>$v["icon"],"href"=>$v["href"],"sort"=>$v["sort"],"status"=>$v["status"],"comment"=>$v["comment"]];
        }
        $menu = json_encode($menu,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
		return view($template = 'list',['menu'=>$menu,'page'=>$page]);
    }
    
    public function catesort($data,$pid=0,$level=0){
        static $arr  = [];
        foreach($data  as $v){
            if ($pid == $v['pid']){
                $v['level']=$level;
                $arr[] =  $v;
                $this->catesort($data,$v['id'],$level+1);
            }
        }
        return $arr;
    }
    
    
	//查询pid相同的数据
	public function lmz($pid){
	   $menu2 = CategoryModel::order('id','asc')->select(); //查询所有数据
	   $arr = ['0' =>'顶级栏目'];
	  //创建数组 id为主键title建值，查询标题作用
		foreach($menu2 as $key=>$val){
			$arr[$val['id']] = $val['title'];
		}
	  $menu3 = CategoryModel::where("pid",$pid)->order('id','asc')->select()->toArray();//查询pid相同的数据
	    foreach($menu3 as $key=>$val){
		        $menu3[$key]['p_title'] = $arr[$val['pid']];
		    }
	 return $menu3;
	}
	
	//栏目状态变更
	public function setStatus(Request $request){
		//判断是否为ajax提交
		if(request()->isAjax()){
		//获取前台传递id
		$id = $request->param('id');
		//根据id查询数据
		$result = CategoryModel::get($id);
		//查询原生数据进行判断
		if($result->getData('status') == 1){
			$res = CategoryModel::update(['status'=>0],['id'=>$id]);
    			if($res){
    			    $this -> logs("栏目 [ID: ".$id.'] 停用成功!');
        			$this -> success("停用成功!",'Category/index');
        		}else{
        			$this -> error("停用失败!",'Category/index');
        		}
		}else{
			$res = CategoryModel::update(['status'=>1],['id'=>$id]);
    			if($res){
    			    $this -> logs("栏目 [ID: ".$id.'] 启用成功!');
        			$this -> success("启用成功!",'Category/index');
        		}else{
        			$this -> error("启用失败!",'Category/index');
        		}
		}
		}
	}


    //栏目添加
    public function add(Request $request)
    {
		//判断当前IP是否允许操作后台
		if(request()->isAjax()){
		$sort = CategoryModel::all();
		$order="1";
		 if($sort != NULL | $sort != "")
		 foreach($sort as $key=>$val){
		 	$order = $val["sort"]+1;
		 }
		$data = $request->param();
		//issue为空时设置数值为0
		if(isset($data['issue']) == false){
		    $data['issue'] = 0;
		}
		$data['sort'] = $order;
		if($data['links'] == 0){
		if($data['type']==0){
		$data['href'] = "index/Matters/matlist";
		}elseif ($data['type']==99) {
		    $data['href'] = "index/index/index";
		}
		}
		if(!isset($data["modid"]) || empty($data["modid"]) || $data["modid"] == 0){
		    return jsonmsg(0,"请检查栏目所属模型项");
		}
		//$res = CategoryModel::insert($data);
		$admin = new CategoryModel;
 		// 过滤post数组中的非数据表字段数据
 		$res = $admin->allowField(true)->save($data);
		if($res){
		    $this->logs("添加栏目 :".$data['title']);
			$this -> success("添加成功!",'Category/index');
		}else{
			$this -> error("添加失败!",'Category/index');
		}
		}
		$config = Db::name('system')->where('id',1)->find();
        $tempaht = Env::get('root_path') . "template/home_temp/" . $config['home_temp'];
        $temp = gainfile($tempaht,'.html','.html');
		//获取栏目下拉列表
        $menunew = CategoryModel::field('id,pid,title')->select()->toArray();
		$menunew = json_encode(alldigui($menunew,0),JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
        $this -> view -> assign('menunew',$menunew);
        //获取模型下拉列表
        $mod = Db::name('model')->field('id,name')->select();
        $this->view->assign(['mod'=>$mod,'temp'=>$temp]);
        //渲染增加界面
        return $this -> view -> fetch('add');
    }



    //栏目编辑
    public function edit(Request $request)
    {
		//判断是否为ajax请求
		if(request()->isAjax()){
		//获取所有数据
		$data = $request->param();
		if($data['links'] == 0){
		    if($data['type']==0){
    		    $data['href'] = "index/Matters/matlist";
    		    }elseif ($data['type']==99) {
    		        $data['href'] = "index/index/index";
    		}
		}
		$res =CategoryModel::update($data);
		if($res){
		    $this->logs("栏目 [ID: ".$data['title'].'] 修改成功!');
			$this -> success("修改成功!",'Category/index?page={$page}');
		}else{
		    $this->logs("栏目 [ID: ".$data['title'].'] 修改失败!');
			$this -> error("修改失败!");
		}
		}
		$config = Db::name('system')->where('id',1)->find();
        $tempaht = Env::get('root_path') . "template/home_temp/" . $config['home_temp'];
        $temp = gainfile($tempaht,'.html','.html');
        //显示旧数据
        $id = $request->param('id');
        //获取栏目下拉列表
        $list = CategoryModel::get($id);
		$hmenus = CategoryModel::field('id,pid,title')->select()->toArray();
		$hmenus = json_encode(alldigui($hmenus,0),JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
		//获取模型下拉列表
        $mod = Db::name('model')->field('id,name')->select();
        $this->view->assign('mod',$mod);
        //赋值给模板
        $this -> view -> assign(['list'=>$list,'hmenus'=>$hmenus,'temp'=>$temp]);
        //渲染增加界面
        return $this -> view -> fetch('edit');
    }

   
    //栏目删除/批量删除
    public function deletes(Request $request)
    {
		//判断是否为ajax请求
        if (request()->isAjax()){
        //获取传递过来的id
        $tab = Db::name("model")->field("tablename")->select();
        $more = false;
		$id = $request -> post('id');
        if(is_array($id)){
            $more = true;
         foreach($id as $val){
        $pm = CategoryModel::where("pid",$val)->select()->toArray();
        if(!empty($pm)){
           $this -> error("当前栏目有子栏目,请先删除子栏目!");
           return false;
        }
        foreach($tab as $v){
        $mat = Db::name($v['tablename'])->where("mid",$val)->select()->toArray();
        if(!empty($mat)){
            $this -> error("当前栏目存在内容,请先删除内容数据!");
            return false;
        }
        }
        }
        }else{
        $pm = CategoryModel::where("pid",$id)->select()->toArray();
        if(!empty($pm)){
           $this -> error("当前栏目有子栏目,请先删除子栏目!");
           return false;
        }
        foreach($tab as $v){
        $mat = Db::name($v['tablename'])->where("mid",$id)->select()->toArray();
        if(!empty($mat)){
            $this -> error("当前栏目存在内容,请先删除内容数据!");
            return false;
        }
        }
        }
        //获取传递过来的值并删除
        $res =CategoryModel::destroy($request -> post('id'));
        if($res){
            if($more){
                foreach($id as $v){
                $this -> logs("删除栏目 :".$v);
                }
            }else{
                $this -> logs("删除栏目 :".$id);
            }
        	$this -> success("删除成功!",'Category/index');
        }else{
        	$this -> error("删除失败!");
        }
        }
    }

	


}
