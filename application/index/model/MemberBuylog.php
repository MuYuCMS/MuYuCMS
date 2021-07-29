<?php

namespace app\index\model;

use think\Model;

class MemberBuylog extends Model
{
    //支付状态
	public function getStatusAttr($value){
	    $arr = ["0"=>"未付款","1"=>"已付款"];
		return $arr[$value];
	}
	
	//支付类型
	public function getPayTypeAttr($value){
	    $arr = ["0"=>"QQ","1"=>"微信","2"=>"支付宝","3"=>"余额","-1"=>"其他"];
		return $arr[$value];
	}
	
	//购买类型
	public function getBuyTypeAttr($value){
	    $arr = ["0"=>"购买","1"=>"续费"];
	    return $arr[$value];
	}
}
