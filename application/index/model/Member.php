<?php

namespace app\index\model;

use think\Model;

class Member extends Model
{
    //性别返回
	public function getSexAttr($value){
	    $arr = ["0"=>"女","1"=>"男","2"=>"保密"];
		return $arr[$value];
	}
}
