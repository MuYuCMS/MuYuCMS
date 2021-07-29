<?php

namespace app\admin\controller;

use app\admin\controller\Base;
use app\admin\model\Menu;
use app\admin\model\Adminuser;
use think\Session;
use think\Request;

class Menus extends Base
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        ////判断当前IP是否允许操作后台
		$ip = $this->ip_info();
		//判断是否登录
		$user = $this -> user_info();
		$menu = Menu::order('sort','asc')->select();
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        //
		//判断当前IP是否允许操作后台
				$ip = $this->ip_info();
				//判断是否登录
				$user = $this -> user_info();
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        //
		//判断当前IP是否允许操作后台
				$ip = $this->ip_info();
				//判断是否登录
				$user = $this -> user_info();
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        //
		//判断当前IP是否允许操作后台
				$ip = $this->ip_info();
				//判断是否登录
				$user = $this -> user_info();
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit($id)
    {
        //
		//判断当前IP是否允许操作后台
				$ip = $this->ip_info();
				//判断是否登录
				$user = $this -> user_info();
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        //
		//判断当前IP是否允许操作后台
				$ip = $this->ip_info();
				//判断是否登录
				$user = $this -> user_info();
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        //
		//判断当前IP是否允许操作后台
				$ip = $this->ip_info();
				//判断是否登录
				$user = $this -> user_info();
    }
}
