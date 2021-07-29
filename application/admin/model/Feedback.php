<?php

namespace app\admin\model;

use think\Model;

class Feedback extends Model//用户意见反馈
{
	//state返回值处理
	public function getstatusAttr($value){
		$status = ['未读','已读'];
		return $status[$value];
	}	
	public function searchFeedbackIdAttr($query, $value, $data)
	    {
	        $query->where('uid','like', $value . '%');
	    }
	public function searchContentAttr($query, $value, $data)
	    {
	        $query->where('content','like', $value . '%');
	    }    
	public function searchCreateTimeAttr($query, $value, $data)
	    {
	        $query->whereBetweenTime('create_time', $value[0], $value[1]);
	    } 
}