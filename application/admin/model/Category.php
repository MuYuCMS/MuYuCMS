<?php

namespace app\admin\model;

use think\Model;

class Category extends Model
{
    //
	//status返回值处理
	public function getStatusAttr($value){
		$status = ['已停用','已启用'];
		return $status[$value];
	}
}
