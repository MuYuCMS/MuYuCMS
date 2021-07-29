<?php

namespace app\admin\controller;

use app\admin\controller\Base;
use think\Request;

class Accessory extends Base
{
    //
	/*
	*渲染附件管理-图片管理列表
	*
	*/
   public function piclist(){
	   return view('pic_list');
   }
   /*
   	*渲染附件管理-文件管理列表
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
	   $picdelurl = $request ->post('picdelur');
	   $res = unlink($picdelurl);
	   if($res){
		   $this -> error("删除成功!",'Accessory/piclist');
	   }else{
		   
		   $this -> error("删除失败!");
	   }
   }
   /*
   	*删除文件操作
   	*
   	*/
   public function filesdel(Request $request){
   	   $filedelurl = $request ->post('filedelur');
   	   $res = unlink($filedelurl);
   	   if($res){
   	   		   $this -> error("删除成功!",'Accessory/piclist');
   	   }else{
   	   		   
   	   		   $this -> error("删除失败!");
   	   }
   }
}
