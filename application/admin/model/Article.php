<?php

namespace app\admin\model;
use think\model\concern\SoftDelete;
use think\Model;


class Article extends Model//资讯列表
{
    //自动时间戳
    protected $autoWriteTimestamp = true;
    protected $createTime = false;
	//导入软删除方法集
	use SoftDelete;
	protected $deleteTime = 'delete_time';
    //state返回值处理
	public function getStatusAttr($value){
		$state = ['已发布','草稿','已下架','待审核','已驳回'];
		return $state[$value];
	}
	
		
}