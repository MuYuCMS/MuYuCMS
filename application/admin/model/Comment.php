<?php

namespace app\admin\model;

use think\Model;

class Comment extends Model
{
    //status返回值处理
	public function getStatusAttr($value){
		$state = ['待审核','已通过','已下架'];
		return $state[$value];
	}
// 	//status返回值处理
// 	public function getAnonymityAttr($value){
// 		$anonymity = ['否','是'];
// 		return $anonymity[$value];
// 	}
	
	public function searchObjectAttr($query, $value, $data)
	    {
	        $query->where('object','like', $value . '%');
	    }
	    
	public function searchUpdateTimeAttr($query, $value, $data)
	    {
	        $query->whereBetweenTime('create_time', $value[0], $value[1]);
	    } 
}
