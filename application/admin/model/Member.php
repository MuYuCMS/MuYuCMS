<?php
namespace app\admin\model;
use think\Model;
use think\model\concern\SoftDelete;

class Member extends Model
{
    //自动时间戳
    protected $autoWriteTimestamp = true;
    protected $createTime = false;    
	//导入软删除方法集
	use SoftDelete;
	protected $deleteTime = 'delete_time';
    //status返回值处理
	public function getStatusAttr($value){
		$state = ['已停用','已启用'];
		return $state[$value];
	}
	
	//性别返回
	public function getSexAttr($value){
		$sex = ['女','男','保密'];
		return $sex[$value];
	}
	

}
