<?php

namespace app\admin\controller;

use app\admin\controller\Base;
use think\Request;
use think\Session;
use app\admin\model\Admin;
use app\admin\model\Type;
use app\admin\model\Hmenu;

class Types extends Base
{
    /**
     * 显示文章分类列表
     *
     * @return \think\Response
     */
    public function typelist(Request $request)
    {
		//判断当前IP是否允许操作后台
		$ip = $this->ip_info();
		
		//判断是否登录
		$user = $this -> user_info();
		//接收页码
		$page = $request->get('page') ? $request->get('page') : 1;

        //查询所有文章分类,并进行分页
		$article_type = Type::order('orders','asc')->paginate(10);
		$menu2 = Hmenu::order('pid','asc')->select();
		//将文章分类home_menu_id对应文本
		//添加一个数组索引
		$arr = ['0'=>'无关联'];
		//创建数组 id为主键title建值
		foreach($menu2 as $key=>$val){
			$arr[$val['id']] = $val['title'];
		}
		//根据上方修改pid
		foreach($article_type as $key=>$val){
			$article_type[$key]['home_menu'] = $arr[$val['mid']];
		}
		return view($template = 'list',['article_type'=>$article_type,'page'=>$page]);
    }
	
	//文章分类状态变更
	public function setStatus(Request $request){
		//判断当前IP是否允许操作后台
		$ip = $this->ip_info();
		//判断是否登录
		$user = $this -> user_info();
		//获取前台传递id
		$admin_id = $request->param('id');
		//根据id查询数据
		$result = Type::get($admin_id);
		//查询原生数据进行判断
		if($result->getData('status') == 1){
			Type::update(['status'=>0],['id'=>$admin_id]);
		}else{
			Type::update(['status'=>1],['id'=>$admin_id]);
		}
	}

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
		//判断当前IP是否允许操作后台
		$ip = $this->ip_info();
        //判断用户是否登录
        $user = $this -> user_info();
        //查询所有数据
        $menunew = Hmenu::field('id,title')->select();
        $this -> view -> assign('menunew',$menunew);
        //渲染增加界面
        return $this -> view -> fetch('list_new');
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
		//判断当前IP是否允许操作后台
		$ip = $this->ip_info();
		//判断用户是否登录
		$user = $this -> user_info();
		$orders = Type::all();
		$order="1";
		 if($orders != NULL | $orders != "")
		 foreach($orders as $key=>$val){
		 	$order = $val["orders"]+1;
		 }

        //
		$data = $request->post();
		if($data['titlepic'] !="" | $data['titlepic'] !=NULL){
		$data['titlepic'] = str_replace("\\","/",$data['titlepic']);
		}
		$data['create_time'] = $data['update_time'] = time();
		$data['orders'] = $order;
		$hmenu = Type::insert($data);
		if($hmenu){
			$this -> success("添加成功!",'Types/typelist');
		}else{
			$this -> error("添加失败!",'Types/typelist');
		}
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit(Request $request)
    {
		//判断当前IP是否允许操作后台
		$ip = $this->ip_info();
        //判断是否登录
        $user = $this -> user_info();
		$page = $request->get('page');
        //获取对应ID
        $id = $request->get('id');
        //读取管理员表信息
        $list = Type::get($id);
		$hmenus = Hmenu::where('pid',0)->field('id,title')->select();
        //赋值给模板
        $this -> view -> assign(['list'=>$list,'hmenus'=>$hmenus,'page'=>$page]);
        //
        return $this -> view -> fetch('list_add');
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request)
    {
		//判断当前IP是否允许操作后台
		$ip = $this->ip_info();
		//判断是否登录
		$user = $this -> user_info();
		//获取所有数据
		$data = $request->post();
		$page = $data['page'];
		unset($data['page']);
		$res =Type::update($data);
		if($res){
			$this -> success("修改成功!",'Types/typelist?page={$page}');
		}else{
			$this -> error("修改失败!");
		}
        //
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete(Request $request)
    {
		//判断当前IP是否允许操作后台
		$ip = $this->ip_info();
        //判断是否登录
        $user = $this -> user_info();
        //获取传递过来的id
        $id = trim($request -> post('ids'));
        //获取传递过来的值并删除
        $res =Type::destroy($request -> post('ids'));
        if($res){
        	$this -> success("删除成功!",'Types/typelist');
        }else{
        	$this -> error("删除失败!");
        }
    }
	//分类背景图上传
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
	
	//批量删除
	public function deleteall(Request $request){
		//判断当前IP是否允许操作后台
		$ip = $this->ip_info();
		//判断是否登录
		$user = $this -> user_info();
		//获取传递过来的值并删除
		$res =Type::destroy($request -> post('delid'));
		if($res){
			$this -> success("删除成功!",'Types/typelist');
		}else{
			$this -> error("删除失败!");
		}
	}

}
