<?php
/**
 * 支付管理类
 * */
namespace app\admin\controller;
use app\admin\controller\Pay;
use think\Request;
use think\Db;

class Pay extends Base
{
    //配置参数
    public function index()
    {
        //查询所有配置参数
        $pay = Db::name('pay')->where('id',1)->find();
        return view('index',['pay'=>$pay]);
    }
    
    //配置参数编辑
    public function indexEdit(Request $request)
    {
        //判断是否为ajax请求
        if(request()->isAjax()){
            //接收前端的数据
            $data = $request->param();
            //更新数据
            $res = Db::name('pay')->where('id',1)->update($data);
            if($res){
                //记录日志
                $this->logs("更新了支付配置信息!");
                $this->success("更新成功!");
            }else{
                //记录日志
                $this->logs("更新支付配置信息失败!");
                $this->error("更新失败!");
            }
        }
    }
    
    //支付设置
    public function setPay()
    {
        //查询开关信息
        $pay = Db::name('pay_set')->where('id',1)->find();
        return view('setpay',['pay'=>$pay]);
    }
    
    //支付设置编辑
    public function setPayEdit(Request $request)
    {
        //判断是否为ajax请求
        if(request()->isAjax()){
            //接收前端的数据
            $data = $request->param();
            //更新数据
            $res = Db::name('pay_set')->where('id',1)->update($data);
            if($res){
                //记录日志
                $this->logs("更新了支付设置信息!");
                $this->success("更新成功!");
            }else{
                //记录日志
                $this->logs("更新支付设置信息失败!");
                $this->error("更新失败!");
            }
        }
    }
}