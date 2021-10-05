<?php

namespace app\index\model;

use think\Model;

class Comment extends Model
{
    //status返回值处理
	public function getStatusAttr($value){
	    $arr = ["0"=>"待审核","1"=>"待审核","2"=>"已下架"];
		$status = $arr[$value];
		return $status;
	}
}
