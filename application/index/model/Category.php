<?php

namespace app\index\model;

use think\Model;

class Category extends Model
{
    //
	//status返回值处理
	public function getStatusAttr($value){
        $arr = ["0"=>"已停用","1"=>"已启用"];
		return $arr[$value];
	}
}
