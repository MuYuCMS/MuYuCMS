<?php
namespace app\admin\controller;
use app\admin\controller\Base;
use app\admin\model\Member as MemberModel;
use app\admin\validate\Member as MemberValidate;
use think\Request;
use think\Session;
use think\Db;
class Member extends Base
{
    //会员列表
	public function index()
    {
		//读取会员表所有信息
		$member = MemberModel::order('create_time','desc')->paginate(25);
		//赋值给模板
		$this -> view -> assign('member',$member);
		return $this -> view -> fetch('list');
    }
    

	//用户添加
	public function add(Request $request){
		//判断是否为ajax提交
		if(request()->isAjax()){
		//接收传递过来的数据
		$data = $request->param();
		//进行数据验证
		$validate = new MemberValidate;
		if (!$validate->check($data)) {
            $this->error($validate->getError());
        }
		//读取前台传递的username字段值
		$newname = trim($request->post('username'));
		$newemail = trim($request->post('email'));
		$data['update_time'] = time();
		$data['create_time'] = time();
		//哈希加密
		$data['password'] = password_hash($data['password'],PASSWORD_BCRYPT);
		//过滤post数组中的非数据表字段数据
		$m = new MemberModel;
		$res = $m->allowField(true)->save($data);
		//取得新增数据id
		$uid = $m->id;
		//判断是否成功并提示
		if($res){
		    Db::name('member_data')->insert(['uid'=>$uid]);
		    $this->logs("会员 [ID: ".$uid.'] 添加成功!');
		    //在今日大数据表会员注册字段自增1
		    Db::name('bigdata')->whereTime('create_time','today')->setInc('member_add');
			$this -> success("添加成功!",'Member/index');
		}else{
			$this -> error("添加失败!");
			}
		}
		//渲染
		return $this -> view -> fetch('add');
	}

	
    //会员编辑
    public function edit(Request $request)
    {
		//显示旧数据
		$id = $request ->param('id');//获取对应ID
		//读取会员表信息
		$member = MemberModel::withTrashed()->get($id);
		//判断是否为ajax请求
		if(request()->isAjax()){
		//获取数据
		$data = $request->param();
		//进行数据验证
		$validate = new MemberValidate;
		if(!$validate->check($data)){
		    $this->error($validate->getError());
		}
		//哈希加密
		if($data['password'] !== $member['password']){
		    $data['password'] = password_hash($data['password'],PASSWORD_BCRYPT);
		}
		//过滤post数组中的非数据表字段数据
		$m = new MemberModel;
		$res = $m->allowField(true)->save($data,['id'=>$data['id']]);
		$uid = $m->id;
		//提示信息
		if($res){
		    $this->logs("会员 [ID: ".$uid.'] 修改成功!');
			$this -> success("修改成功!",'Member/index');
		}else{
		    $this->logs("会员 [ID: ".$uid.'] 修改失败!');
			$this -> error("修改失败!");
		}
		}
		
		//赋值给模板
		$this -> view -> assign('member',$member);
		return $this -> view -> fetch('edit');
    }

	
	//会员员状态变更
	public function setStatus(Request $request){
		//判断是否为ajax请求
		if(request()->isAjax()){
		//获取前台传递id
		$id = $request->param('id');
		//根据id查询数据
		$result = MemberModel::get($id);
		//查询原生数据进行判断
		if($result->getData('status') == 1){
			$res = MemberModel::update(['status'=>0],['id'=>$id]);
			if($res){
			    $this->logs("会员 [ID: ".$id.'] 已停用!');
				$this->success("已停用！",'member/index');
			}else{
				$this->error("停用失败！");
			}
		}else{
			$res = MemberModel::update(['status'=>1],['id'=>$id]);
			if($res){
			    $this->logs("会员 [ID: ".$id.'] 已启用!');
				$this->success("已启用！",'member/index');
			}else{
				$this->error("启用失败！");
			}
		}
		}
	}	


	//会员删除/批量删除
    public function deletes(Request $request)
    {
		//判断是否为ajax请求
		if(request()->isAjax()){
		//获取传递过来的id
		$id = $request->param('id');
		//转为数组
		//$array = explode(',',$id);
		//查找数组中的空元素
		//$key = array_search("",$array);
		//if ($key !== false){
                //删除数组中空元素
                //array_splice($array, $key, 1);
            //}
        //获取传递过来的值并删除
		$res =MemberModel::destroy($id);
		if($res){
		    Db::name('member_data')->where('uid',$id)
	            ->useSoftDelete('delete_time',time())
                ->delete();
            $this->logs("会员 [ID: ".$id.'] 删除成功!');
			$this -> success("删除成功!",'Member/index');
		}else{
			$this -> error("删除失败!");
		}
		}
    }


	//回收站列表
	public function recycle(Request $request){
		//查询表所有已删除的用户数据
		$list = MemberModel::onlyTrashed()->order('delete_time','desc')->paginate(25);
		//赋值给模板
		$this -> view -> assign('list',$list);
		return $this -> view -> fetch('recycle');
	}
	
	
	//回收站还原/批量还原
	public function huanyuan(Request $request){
		//判断是否为ajax提交
		if(request()->isAjax()){
		//获取传递过来的值并还原
		$id = $request -> post('id');
		$res =Db::name('member') ->where('id','in',$id) ->setField('delete_time', NULL);
		if($res){
		    Db::name('member_data') ->where('uid','in',$id) ->setField('delete_time', NULL);
			$this -> success("还原成功!",'Member/recycle');
		}else{
			$this -> error("还原失败!");
		}
		}
	}
	

		//回收站删除/批量删除
		public function reallyDel(Request $request)
		{
			//判断是否为ajax提交
		    if(request()->isAjax()){
			$res=Db::name('member')->where('id','in',$request -> post('id'))
                ->delete();
			if($res){
			    Db::name('member_data')->where('uid','in',$request -> post('id'))
                ->delete();
				$this -> success("删除成功!",'Member/recycle');
			}else{
				$this -> error("删除失败!");
			}
		    }
		}


		//点击搜索操作
	    public function search(Request $request)
		{
			//接收数据
			$res = $request -> param('keywords');
			//进行模糊查询
	    	    $member =MemberModel::alias('a')->join('member_data b','a.id = b.uid')->fieldRaw('a.*,b.*')->where([['id|name|account|email|phone', 'like', '%'.$res. '%']])->order('create_time','desc')->paginate(25,false,['query'=>request()->param()]);
			$this ->assign('member',$member);
			return view('list');
		}


		//回收站搜索
	    public function recycleSearch(Request $request)
		{
			//接收数据
			$res = $request -> param('keywords');
			//进行模糊查询
	    	    $list =MemberModel::onlyTrashed()->alias('a')->join('member_data b','a.id = b.uid')->fieldRaw('a.*,b.*')->where([['id|name|account|email|phone', 'like', '%'.$res. '%']])->order('create_time','desc')->paginate(25,false,['query'=>request()->param()]);
			$this ->assign('list',$list);
			return view('recycle');
		}
		
		
		//会员购买记录
		public function buyLog()
		{
		    //查询所有数据
		    $log = Db::name('member_buylog')->order('create_time', 'desc')->paginate(25);
		    //给模板赋值
		    $this->assign(['log'=>$log]);
		    return view('buylog');
		}
		
		//会员购买记录搜索
		public function buySearch(Request $request)
		{
		    $data = $request -> param('keywords');
		    $log = Db::name('member_buylog')->where([['order_id', 'like', '%'.$data. '%']])->order('create_time', 'desc')->paginate(25,false,['query'=>request()->param()]);
		    $this->assign('log',$log);
		    return view('buylog');
		}
    		
    	//会员充值记录
		public function payLog()
		{
		    //查询所有数据
		    $log = Db::name('member_paylog')->order('create_time', 'desc')->paginate(25);
		    //给模板赋值
		    $this->assign('log',$log);
		    return view('paylog');
		}
		
		//会员充值记录搜索
		public function paySearch(Request $request)
		{
		    $data = $request -> param('keywords');
		    $log = Db::name('member_paylog')->where([['order_id', 'like', '%'.$data. '%']])->order('create_time', 'desc')->paginate(25,false,['query'=>request()->param()]);
		    $this->assign('log',$log);
		    return view('paylog');
		}
		
		//金额充值
		public function moneyAdd(Request $request)
		{
		    //判断是否为ajax请求
		    if(request()->isAjax()){
		        //接收数据
		        $data = $request->param();
		        //更新金额
                $res = Db::name('member')->where('id',$data['id'])->setInc('money',$data['money']);
                if($res){
                    //记录日志
                    $this ->logs("给会员[ID：{$data['id']}]充值了{$data['money']}元!");
                    $info['uid'] = $data['id'];
                    //3为手动充值
                    $info['pay_type'] = 3;
                    $info['money'] = $data['money'];
                    //记录当前时间
                    $info['create_time'] = time();
                    //记录当前IP
                    $info['pay_ip'] = $request->ip();
                    //状态为1表示付款
                    $info['status'] = 1;
                    //生成订单号
                    $info['order_id'] = trade_no();
                    //充值记录
                    Db::name('member_paylog')->insert($info);
                    //在今日大数据表会员注册字段自增1
		            Db::name('bigdata')->whereTime('create_time','today')->setInc('pay_money',$data['money']);
                    $this->success("充值成功!");
                }else{
                    //记录日志
                    $this ->logs("给会员[ID：{$data['id']}]充值{$data['money']}元失败!");
                    $this->error("充值失败!");
                }
		    }
		}
    
}
