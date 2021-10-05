<?php

namespace app\index\model;

use think\Model;

class MemberPaylog extends Model
{
    //支付状态
	public function getStatusAttr($value){
	    $arr = ["0"=>"未付款","1"=>"已付款"];
		return $arr[$value];
	}
	
	//充值类型
	public function getPayTypeAttr($value){
	    $arr = ["0"=>"QQ","1"=>"微信","2"=>"支付宝","3"=>"手动充值","-1"=>"其他"];
		return $arr[$value];
	}
	
	//
}
