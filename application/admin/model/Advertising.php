<?php

namespace app\admin\model;

use think\Model;

class Advertising extends Model
{
    //
	//addstatus返回值处理
	public function getStatusAttr($value){
		$addstatus = ['隐藏','显示'];
		return $addstatus[$value];
	}
}
