<?php

namespace app\admin\controller;

use app\admin\controller\Base;
use think\Request;
use app\admin\model\Sms as SmsModel;
use think\facade\Session;
use app\admin\model\System;

class Sms extends Base
{
	public function index()
	{
		$email = SmsModel::get(1);
		$this -> view -> assign('sms',$email);
		return $this -> view -> fetch('index');
	}
	//保存配置信息
		public function edit(Request $Request)
		{
		    if(request()->isAjax()){
	    		$data = $Request->post();
	    		if(!empty($data['smsbao_password'])){
	                if (!preg_match("/^[a-z0-9]{32}$/", $data['smsbao_password'])){
	                //对密码进行MD5算法加密
	                $data['smsbao_password'] = md5($data['smsbao_password']);
	                }
	    		}
	    		if(empty($data['sll'])){
	    		    $data['sll'] = 25;
	    		}
	    		$sms = new SmsModel();
	    		$res = $sms->allowField(true)->save($data,['id' => 1]);
	    		if($res){
	    		    $this->logs("修改了发信配置信息");
	    			$this -> success("保存成功!",'Sms/index');
	    		}else{
	    			$this -> error("保存失败!");
	    		}
		    }
		}
	/**
	 * 发送测试邮件
	 *$title:邮件标题,$email:发送人邮箱账号,$emailpaswsd:发送邮箱的授权码,$smtp:发送者邮箱服务器地址,$sll:邮箱协议端口号,$content:邮件正文,$toemail:收件人信息
	 */
	public function sendEmail(Request $request)
	{
	    if(request()->isAjax()){
    	    $data = $request->param();
    		$system = System::where('id',1)->field('title,logo')->find();//查询网站名称
    		$host = $request->domain();
    		$title = "测试邮件";
    		$content = "<div style='padding:30px; background:#F5F6F7;'><div style='font-size:14px; border-top:3px solid #2254f4; background:#fff; color:#333; padding:20px 40px; border-radius:4px; width:542px; margin:40px auto;'><div style='text-align:right; margin-bottom:10px;'><img src='".$host.$system['logo']."' style='display:inline-block;height:40px;width:40px;'></div><div>一封测试邮件</div><div>您已成功收到来自 <b style='color:#333;'>'".$system['title']."'</b>测试邮件信息，正文内容为</div><div style='font-size:24px; color:#2254f4; margin-bottom:20px; margin-top:10px;'><strong>测试邮件发送！</strong></div><div style='margin-top:10px;'>如非本人操作，请忽略该邮件。</div><div style='font-size:12px;color:#999;'>提示：成为'".$system['title']."'会员，只有在电子邮箱验证成功后才能被创建。</div><div style='color:#999;text-align:right;line-height:30px;margin-top:40px;padding-top:10px;border-top:1px solid #eee;font-size:12px;'>最终的落日只有闭着眼睛才能看</div></div></div>";
    		$res = sendEmail($data['email'],$data['emailpaswsd'],$data['smtp'],$data['sll'],$system['title'],$title,$content,$data['ceemail']);
    		 if($res){
    		 	$this -> success("发送成功!",'Sms/index');
    		 }else{
    		 	$this -> error("发送失败!");
    		 }
	    }

	}
	
	//发送测试短信
	public function testSmsbao(Request $request)
	{
	    if(request()->isAjax()){
	        $data = $request->param();
	       //利用正则表达式检测当前的密码是否为MD5字符串
            if (!preg_match("/^[a-z0-9]{32}$/", $data['smsbao_password'])){
                //对密码进行MD5算法加密
                $data['smsbao_password'] = md5($data['smsbao_password']);
            }
            dump($data['smsbao_password']);
            //调用随机验证码方法
            $code = 465741;
            //验证码有效时间(单位：分钟)
            $time = 5;
	        $content = "【测试】这是一条测试内容，您的验证码是{$code}，在{$time}分钟有效。";
            $res = sendSms($data['smsbao_account'],$data['smsbao_password'],$content,$data['smsbao_phone']);
            if ($res == 0){
                $this->success("短信发送成功！");
            }else{
                $this->error("发送失败！");
            }
	    }
	}
	
}