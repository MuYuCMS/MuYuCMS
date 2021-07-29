<?php

namespace app\admin\model;

use think\Model;

class Type extends Model
{
        //自动时间戳
    protected $autoWriteTimestamp = true;
    protected $createTime = false;

	//status返回值处理
	public function getStatusAttr($value){
		$status = ['已停用','已启用'];
		return $status[$value];
	}
}
