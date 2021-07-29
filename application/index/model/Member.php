<?php

namespace app\index\model;

use think\Model;

class Member extends Model
{
    //性别返回
	public function getSexAttr($value){
		$sex = ['男','女','保密'];
		return $sex[$value];
	}
}
