<?php

namespace app\index\model;

use think\Model;

class Comment extends Model
{
    //status返回值处理
	public function getStatusAttr($value){
		$status = ['待审核','已通过','已下架'];
		return $status[$value];
	}
}
