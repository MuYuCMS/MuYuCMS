<?php
namespace app\admin\Model;

use think\Model;
use think\model\concern\SoftDelete;

class Role extends Model
{
    	//status返回值处理
	public function getStatusAttr($value){
		$status = ['已停用','已启用'];
		return $status[$value];
	}
	
}