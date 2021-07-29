<?php

namespace app\index\model;

use think\Model;

class Feedback extends Model
{
    //status返回值处理
	public function getStatusAttr($value){
		$state = ['未读','已读'];
		return $state[$value];
	}
}