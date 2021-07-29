<?php
namespace app\admin\Model;

use think\Model;
use think\model\concern\SoftDelete;

class Admin extends Model
{
	//导入软删除方法集
	use SoftDelete;
	protected $deleteTime = 'delete_time';
	//status返回值处理
	public function getStatusAttr($value){
		$status = ['已停用','已启用'];
		return $status[$value];
	}
}