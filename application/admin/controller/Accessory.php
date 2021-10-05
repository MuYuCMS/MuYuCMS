<?php

namespace app\admin\controller;

use app\admin\controller\Base;
use think\Request;
use think\facade\Env;

class Accessory extends Base
{
	/*
	*附件管理-图片管理列表
	*
	*/
   public function piclist(){
	   return view('pic_list');
   }
   /*
   	*附件管理-文件管理列表
   	*
   	*/
   public function filelist(){
   	   return view('file_list');
   }
   /*
   	*删除图片操作
   	*
   	*/
   public function picdel(Request $request){
	   $picdelurl = Env::get('root_path'). "public" . $request ->param('picdelur');
	   $res = unlink($picdelurl);
	   if($res){
	       $this -> logs("删除图片操作 [ID: ".$picdelurl.'] 删除成功!');
		   $this -> success("删除成功!",'Accessory/piclist');
	   }else{
		   $this -> logs("删除图片操作 [ID: ".$picdelurl.'] 删除失败!');
		   $this -> error("删除失败!");
	   }
   }
   /*
   	*删除文件操作
   	*
   	*/
   public function filesdel(Request $request){
	   
   	   $filedelurl = Env::get('root_path'). "public" . $request ->param('filedelur');
   	   $res = unlink($filedelurl);
   	   if($res){
   	           $this -> logs("删除文件操作 [ID: ".$filedelurl.'] 删除成功!');
   	   		   $this -> success("删除成功!",'Accessory/filelist');
   	   }else{
   	   		   $this -> logs("删除文件操作 [ID: ".$filedelurl.'] 删除失败!');
   	   		   $this -> error("删除失败!");
   	   }
   }
}
