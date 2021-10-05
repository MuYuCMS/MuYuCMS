<?php

namespace app\index\model;

use think\Model;

class Feedback extends Model
{
    //status返回值处理
	public function getStatusAttr($value){
	    $arr = ["0"=>"未读","1"=>"已读"];
		return $arr[$value];
	}
}