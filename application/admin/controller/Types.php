<?php
namespace app\admin\controller;
use app\admin\controller\Base;
use think\Request;
use think\Session;
use app\admin\model\Admin;
use app\admin\model\Type;
use app\admin\model\Category as CategoryModel;

class Types extends Base
{
    //分类列表
    public function index(Request $request)
    {
		//接收页码
		$page = $request->get('page') ? $request->get('page') : 1;

        //查询所有文章分类,并进行分页
		$list = Type::order('orders','desc')->paginate(25);
		$menu2 = CategoryModel::order('pid','asc')->select();
		//将文章分类home_menu_id对应文本
		//添加一个数组索引
		$arr = ['0'=>'无关联'];
		//创建数组 id为主键title建值
		foreach($menu2 as $key=>$val){
			$arr[$val['id']] = $val['title'];
		}
		//根据上方修改pid
		foreach($list as $key=>$val){
		    if(isset($arr[$val['pid']])){
			$list[$key]['home_menu'] = $arr[$val['pid']];
		    }else{
		    $list[$key]['home_menu'] = "栏目丢失!";    
		    }
		}
		return view($template = 'list',['list'=>$list,'page'=>$page]);
    }
	
	//分类状态
	public function setStatus(Request $request)
	{
		//判断是否为ajax提交
		if(request()->isAjax()){
		//获取前台传递id
		$id = $request->param('id');
		//根据id查询数据
		$result = Type::get($id);
		//查询原生数据进行判断
		if($result->getData('status') == 1){
			$res=Type::update(['status'=>0],['id'=>$id]);
			if($res){
			    $this -> logs("分类状态 [ID: ".$id.'] 停用成功!');
			    $this->success("停用成功！",'Types/index');
			}else{
			    $this->error("停用失败！");
			}
		}else{
			$res=Type::update(['status'=>1],['id'=>$id]);
			if($res){
			    $this -> logs("分类状态 [ID: ".$id.'] 启用成功!');
			    $this->success("启用成功！",'Types/index');
			}else{
			    $this->error("启用失败！");
			}
		}
		}
	}


    //分类添加
    public function add(Request $request)
    {
		//判断是否为ajax提交
		if(request()->isAjax()){
		$orders = Type::all();
		$order="1";
		 if($orders != NULL | $orders != "")
		 foreach($orders as $key=>$val){
		 	$order = $val["orders"]+1;
		 }
        //接收提交数据
		//$data = array_filter($request->param());
		$data = $request->param();
		//if($data['mid'] =null){
		    //$data['mid'] = 0;
		//}
		//var_dump($data);
		$data['create_time'] = $data['update_time'] = time();//时间
		$data['orders'] = $order;//排序
		//$res = Type::insert($data);//添加
 		$admin = new Type;
 		// 过滤post数组中的非数据表字段数据
 		$res = $admin->allowField(true)->save($data);
		if($res){
		    $getid = $admin->id;
		    $this -> logs("分类添加 [ID: ".$getid.'] 添加成功!');
			$this -> success("添加成功!",'Types/index');
		}else{
			$this -> error("添加失败!",'Types/index');
		}
		}
		//查询分类所属数据
		$menunew = CategoryModel::field('id,pid,title')->select()->toArray();
		$menunew = json_encode(alldigui($menunew,0),JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
        $this -> view -> assign('menunew',$menunew);
        //渲染界面
        return $this -> view -> fetch('add');
    }


    //分类编辑
    public function edit(Request $request)
    {
		//判断是否为ajax提交
		if(request()->isAjax()){
		//获取所有数据
		$data = array_filter($request->param());
		$res =Type::update($data);
		if($res){
		    $this -> logs("分类编辑 [ID: ".$data['id'].'] 修改成功!');
			$this -> success("修改成功!",'Types/index');
		}else{
			$this -> error("修改失败!");
		}
		}
        //显示旧数据
        $id = $request->get('id');//获取对应ID
        //读取旧数据
        $list = Type::get($id);
		//查询分类所属数据
		$menunew = CategoryModel::field('id,pid,title')->select()->toArray();
		$menunew = json_encode(alldigui($hmenus,0),JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
        //赋值给模板
        $this -> view -> assign(['list'=>$list,'menunew'=>$menunew]);
        return $this -> view -> fetch('edit');
    }

	
    //分类删除/批量删除
    public function deletes(Request $request)
    {
		//判断是否为ajax提交
		if(request()->isAjax()){
        //获取传递过来的id
        $id = trim($request -> post('id'));
        //获取传递过来的值并删除
        $res =Type::destroy($id);
        if($res){
            $this -> logs("分类删除 [ID: ".$id.'] 删除成功!');
        	$this -> success("删除成功!",'Types/index');
        }else{
        	$this -> error("删除失败!");
        }
		}
    }

	


}
