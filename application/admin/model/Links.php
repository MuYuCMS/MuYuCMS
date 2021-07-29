<?php

namespace app\admin\model;

use think\Model;

class Links extends Model
{
    //
	//addstatus返回值处理
	public function getStatusAttr($value){
		$Linkstatus = ['隐藏','显示'];
		return $Linkstatus[$value];
	}
}
