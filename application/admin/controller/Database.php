<?php

namespace app\admin\controller;

use app\admin\controller\Base;
use my\Backup;
use think\Request;
use think\Db;


class Database extends Base{
    /**
    *获取数据库表信息
    * 
    * @return \think\response\View
    */
    public function index(){
        $backup = new Backup();
        $list = $backup->dataList();
        $this -> view -> assign(['list'=>$list]);
        return $this -> view -> fetch('index');
    }
    
    /**
    *表的优化
    * 
    * @return \
    */
    public function optimize(Request $request){ 
        if($request->isPost()){
          $table = input('post.tablename');
          if(!empty($table)){
              if(substr_count($table,',')>=1){
                $table = explode(",",$table);  
              }
            $backup = new Backup();
            $res = $backup->optimize($table);
            if($res[0]['Msg_text'] === "Table does not support optimize, doing recreate + analyze instead"){
            if($res[1]['Msg_text']==="OK"){
                return json(['code'=>1,'msg'=>'优化成功!']);
            }
            }elseif($res[0]['Msg_text']==="OK"){
                return json(['code'=>1,'msg'=>'优化成功!']);
            }
            return json(['code'=>0,'msg'=>'优化失败!']);
          }
        }else{
            return json(['code'=>0,'msg'=>'非法请求!']);
        }
    }
    
    /**
    *表的修复
    * 
    * @return \
    */
    public function repair(Request $request){
        if($request->isPost()){
          $table = input('post.tablename');
          if(!empty($table)){
              if(substr_count($table,',')>=1){
                $table = explode(",",$table);  
              }
            $backup = new Backup();
            $res = $backup->repair($table);
            if($res[0]['Msg_text']==="OK"){
                return json(['code'=>1,'msg'=>'修复成功!']);
            }else{
            return json(['code'=>0,'msg'=>'修复失败!']);
            }
          }
        }else{
            return json(['code'=>0,'msg'=>'非法请求!']);
        }
    }
    /**
    *表的分析
    * 
    * @return \
    */
    public function analyze(Request $request){
        if($request->isPost()){
          $table = input('post.tablename');
          if(!empty($table)){
              if(substr_count($table,',')>=1){
                $table = explode(",",$table);  
              }
            $backup = new Backup();
            $res = $backup->analyze($table);
            if($res[0]['Msg_text']==="OK"){
                return json(['code'=>1,'msg'=>'分析成功!']);
            }else{
            return json(['code'=>0,'msg'=>'分析失败!']);
            }
          }
        }else{
            return json(['code'=>0,'msg'=>'非法请求!']);
        }
    }
    /**
    *表的备份-获取备份列表
    * 
    * @return \
    */
    public function backuplst(){
        //获取表名
        $table = input('tb');
        //实例化
        $config = [
            'path' => './public/data/'.$table.'/',
            ];
        $backup = new Backup($config);
        $list = $backup->fileList();
        $this -> view -> assign(['list'=>$list,'table'=>$table]);
        return $this -> view -> fetch('backuplst');
    }
    /**
    *表的备份-备份操作
    * 
    * @return \
    */
    public function dbbackup(Request $request){
        if($request->isPost()){
        $table = input('tb');
        $config = [
            'path' => './public/data/'.$table.'/',
            'compress' => 0,
            ];
        $backup = new Backup($config);
        $file = ['name'=>date('Ymd-His'),'part'=>1];
        if($table == 'database'){
        $database = \think\facade\Config::get('database.database');
        $sql = "show tables";
        $tables = Db::query($sql);
        foreach($tables as $v){
        $start = $backup->setFile($file)->backup($v['Tables_in_'.$database],0);
        }
        }else{
        $start = $backup->setFile($file)->backup($table,0);
        }
        if($start == 0){
            return json(['code'=>1,'msg'=>'备份成功!']);
        }else{
            return json(['code'=>0,'msg'=>'备份失败!']);
        }
        }
            return json(['code'=>500,'msg'=>'非法请求!']);
    }
    /**
    *表的还原-单表还原
    * 
    * @return \
    */
    public function restore(Request $request){
        if($request->isPost()){
           $table = input('post.tb');
           $name = input('post.name');
           $config = [
            'path' => './public/data/'.$table.'/',
            'compress' => 1,
            ];
           $backup = new Backup($config);
           $start = 0;
           $start = $backup->setFile(['name' => $name])->import($start);
           if($start == 0){
            return json(['code'=>1,'msg'=>'还原成功!']);
        }else{
            return json(['code'=>0,'msg'=>'还原失败!']);
        }
        }
        return json(['code'=>500,'msg'=>'非法请求!']);
    }
    /**
    *表的备份-单表删除
    * 
    * @return \
    */
    public function sqldel(Request $request){
        if($request->isPost()){
            $table = input('post.tb');
           $name = input('post.name');
           $config = [
            'path' => './public/data/'.$table.'/',
            ];
           $backup = new Backup($config);
           $res = $backup->delFile($name);
           return $res;
        }
        return json(['code'=>500,'msg'=>'非法请求!']);
    }
}