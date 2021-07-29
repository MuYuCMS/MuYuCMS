<?php

namespace app\admin\controller;

use app\admin\controller\Base;
use think\Request;
use app\admin\model\Email;
use think\facade\Session;
use app\admin\model\System;

class Emails extends Base
{
	//渲染邮箱配置页面
	public function index(){
		//判断是否登录
		$user = $this -> user_info();
		//
		$email = Email::get(1);
		$this -> view -> assign('email',$email);
		return $this -> view -> fetch('email_add');
	}
	//保存邮箱配置
	public function update(Request $Request){
		//判断是否登录
		$user = $this -> user_info();
		
		$data = $Request->post();
		$res = Email::update($data);
		if($res){
			$this -> success("保存成功!",'Emails/index');
		}else{
			$this -> error("保存失败!");
		}
	}
	/**
	 * 发送测试邮件
	 *$title:邮件标题,$email:发送人邮箱账号,$emailpaswsd:发送邮箱的授权码,$smtp:发送者邮箱服务器地址,$sll:邮箱协议端口号,$content:邮件正文,$toemail:收件人信息
	 */
	public function sendEmail(Request $request){
		//判断当前IP是否允许操作后台
		$ip = $this->ip_info();
		//phpinfo();
		$email = Email::where('id',1)->field('email,emailpaswsd,smtp,sll,ceemail')->find();
		$emname = System::where('id',1)->field('title,logo')->find();//查询网站名称
		$host = $request->domain();
		$title = "测试邮件";
		$content = "<div style='padding:30px; background:#F5F6F7;'><div style='font-size:14px; border-top:3px solid #2254f4; background:#fff; color:#333; padding:20px 40px; border-radius:4px; width:542px; margin:40px auto;'><div style='text-align:right; margin-bottom:10px;'><img src='".$host.$emname->logo."' style='display:inline-block;height:40px;width:40px;'></div><div>一封测试邮件</div><div>您已成功收到来自 <b style='color:#333;'>'".$emname->title."'</b>测试邮件信息，正文内容为</div><div style='font-size:24px; color:#2254f4; margin-bottom:20px; margin-top:10px;'><strong>真是个可爱的大 S B</strong></div><div style='margin-top:10px;'>如非本人操作，请忽略该邮件。</div><div style='font-size:12px;color:#999;'>提示：成为'".$emname->title."'会员，只有在电子邮箱验证成功后才能被创建。</div><div style='color:#999;text-align:right;line-height:30px;margin-top:40px;padding-top:10px;border-top:1px solid #eee;font-size:12px;'>最终的落日只有闭着眼睛才能看</div></div></div>";

		if(! $email){
			$this -> error("没有获得邮箱信息!");
		}
	    // $emails = '93653142@qq.com';
		// $emailpaswsd = 'ednppciuukwycaef';

		// // $sll = 465;
		// $toemail = '1656239759@qq.com';

		$res = sendEmail($email->email,$email->emailpaswsd,$email->smtp,$email->sll,$emname->title,$title,$content,$email->ceemail);

		 if($res){
			 Email::where('id',1)->update(['ceemail'=>NULL]);
		 	$this -> success("发送成功!",'Emails/index');
		 }else{
		 	$this -> error("发送失败!");
		 }
	}
	
}