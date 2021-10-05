<?php
namespace app\admin\controller;

use app\admin\controller\Base;
use think\Request;
use think\Db;
use think\facade\Env;
use think\Config;
// 新建模型
class Model extends Base
{
    public static function connect()
    {
        return Db::connect();
    }
    
    public function index(){
        $list = Db::name('model')->paginate(25);
        return view('index',['list'=>$list]);
    }
    public function edit(Request $request){
        if($request->isAjax()){
           $data = $request->param();
           $tabst = "false";
           $mode = Db::name("model")->find($data["id"]);
           $models = Db::name("model")->select();
           foreach($models as $v){
               if($v['id'] != $data['id']){
                   if($data["tablename"] == $v["tablename"]){
                       return jsonmsg(0,'模型标识重复,请更正!');
                   }
               }
           }
           $res = Db::name('model')->update($data);
           if($res){
               if($mode["tablename"] != $data["tablename"]){
                    Db::query("RENAME TABLE ".config("database.prefix").$mode["tablename"]." TO ".config("database.prefix").$data["tablename"]."");
                    Db::query("RENAME TABLE ".config("database.prefix").$mode["tablename"]."_data TO ".config("database.prefix").$data["tablename"]."_data");
                }
               return jsonmsg(1,'编辑成功');
           }else{
               return jsonmsg(0,'编辑失败');
           }
        }
        $list = Db::name("model")->where("id",$request->param('id'))->find();
        return view('edit_model',['list'=>$list]);
    }
    
    public function add(Request $request){
        if($request->isAjax()){
           $db = self::connect();
           $data = $request->param();
           $data['create_time'] = time();
           $tabname = config("database.prefix") . $data['tablename'];
           $tab = Db::name("model")->where("tablename",$data['tablename'])->find();
           if($tab){
               return jsonmsg(0,'模型标识已存在!');
               false;
           }
           $res = Db::name('model')->insert($data);
           $moid = Db::name('model')->getLastInsID();
           if($res){
            $sql =modsql($tabname);
            $db->query($sql);
            $datatab = $tabname."_data";
            $datasql = datasql($datatab);
            $db->query($datasql);
            $fietab = config("database.prefix") . "modfiel";
            $field = fiesql($fietab,$moid);
            $db->query($field);
            return jsonmsg(1,'创建成功');
           }else{
               return jsonmsg(0,'创建失败');
           }
           
        }
        return view('add_model');
    }
    
    public function modelst(Request $request){
        if($request->isPost()){
            $model = Db::name('model')->where('id',$request->param('id'))->find();
            $res = false;
            if($model['status'] == 1){
              Db::name('model')->where('id',$request->param('id'))->update(['status'=>0]);
            }else{
              Db::name('model')->where('id',$request->param('id'))->update(['status'=>1]);
              $res = true;
            }
            if($res){
            return jsonmsg(1,'已启用');    
            }else{
            return jsonmsg(1,'已禁用');    
            }
        }
        return jsonmsg(500,'非法操作');
    }
    
    public function moddel(Request $request){
        if($request->isPost()){
            $db = self::connect();
            $data = $request->param("delid");
            $cate = Db::name("category")->where("modid","in",$data)->select()->toArray();
            if(!empty($cate)){
                return jsonmsg(0,'删除失败,当前模型下存在栏目数据!');
            }
            $tab = Db::name("model")->where("id","in",$data)->field("tablename")->select();
            $res = Db::name("model")->delete($data);
            if($res != 0 && $res >= 1){
                foreach($tab as $v){
                    $db->query("DROP TABLE ".config("database.prefix").$v['tablename']."");
                    $db->query("DROP TABLE ".config("database.prefix").$v['tablename']."_data");
                }
               Db::name("modfiel")->where('modid',$data)->delete();
               return jsonmsg(1,'删除成功');
            }else{
               return jsonmsg(0,'删除失败');   
            }
        }
        return jsonmsg(500,'非法操作');
    }
    //模型的导出
    public function educemodl(Request $request){
        if($request->isAjax()){
        $data = $request->param();
        if(empty($data)){
            return jsonmsg(0,"缺少必要参数");
        }
        if(!array_key_exists("modid",$data) || !array_key_exists("tablename",$data) || !array_key_exists("tabst",$data)){
            return jsonmsg(0,"缺少必要参数,中断操作!");
        }
        if(!is_dir(Env::get("root_path")."public/model/".$data['tablename'])){
            mkdir(Env::get("root_path")."public/model/".$data['tablename'], 0755, true);
        }
        $fielnams = $data['tablename'].mt_rand(0,1000).time();
        $fiename = Env::get("root_path")."public/model/".$data['tablename']."/".$fielnams;
        $sqltabname = array($data["tablename"],$data["tablename"]."_data");
        foreach($sqltabname as $tabss){
        if($data['tabst'] == 1){//获取当前模型标识表结构 tabst=1备份数据tabst=0只备份表结构
            $result = Db::query("SHOW CREATE TABLE `".config("database.prefix").$tabss."`");
            $sql = "\n";
            $sql .= "DROP TABLE IF EXISTS `#__".$tabss."`;\n";
            $sqls = str_replace("\r", "\n", str_replace(config("database.prefix"),'#__' , $result[0]['Create Table']));
            $sql .= trim($sqls) . ";\n\n";
            if (false === file_put_contents($fiename.".model",$sql, FILE_APPEND | LOCK_EX)) {
                return jsonmsg(0,"导出文件失败");
                false;
            }
            $tabnum =  Db::query("SELECT COUNT(*) AS count FROM `".config("database.prefix").$tabss."`");//查询当前模型标识数据总数
            $count = $tabnum['0']['count'];
            if ($count) {
            //备份数据记录
            $list = Db::query("SELECT * FROM `".config("database.prefix").$tabss."` LIMIT 0, 1000");
            $result = Db::query("SHOW COLUMNS FROM `".config("database.prefix").$tabss."`");
            foreach ($list as $row) {
                $sss = 0;
                $rowsql = '';
                foreach($row as $v){
                  if(strpos($result[$sss]["Type"],'int') !== false || strpos($result[$sss]["Type"],'varchar') || strpos($result[$sss]["Type"],'tinyint') !== false){    
                  if($v === NULL){
                      $rowsql .= "NULL, ";
                  }else{
                      $rowsql .= "$v, ";
                  }
                  }else{
                      $rowsql .= "'$v', ";
                  }
                  $sss++;
                }
                $rowsql = substr($rowsql, 0, -2);
                $sql = "INSERT INTO `#__".$tabss."` VALUES (" . $rowsql . ");\n\n";
                if (false === file_put_contents($fiename.".model",$sql, FILE_APPEND | LOCK_EX)) {
                return jsonmsg(0,"导出文件失败");
                false;
                }
            }
        }
        }elseif($data['tabst'] == 0){
            $result = Db::query("SHOW CREATE TABLE `".config("database.prefix").$tabss."`");
            $sql = "\n";
            $sql .= "DROP TABLE IF EXISTS `#__".$tabss."`;\n";
            $sqls = str_replace("\r", "\n", str_replace(config("database.prefix"),'#__' , $result[0]['Create Table']));
            $sql .= trim($sqls) . ";\n\n";
            if (false === file_put_contents($fiename.".model",$sql, FILE_APPEND | LOCK_EX)) {
                return jsonmsg(0,"导出文件失败");
                false;
            }
        }
        }
        $mods = Db::query("SELECT * FROM `".config("database.prefix")."model` WHERE id='".$data["modid"]."' LIMIT 1");//获取当前备份模型数据
        $modelin = Db::query("SHOW COLUMNS FROM `".config("database.prefix")."model`");
        foreach($mods as $mod){
            $rowsql = substr($this->echosql($mod,$modelin), 0, -2);
            $sql = "INSERT INTO `#__model` VALUES (" . $rowsql . ");\n\n";
            if (false === file_put_contents($fiename.".model",$sql, FILE_APPEND | LOCK_EX)) {
            return jsonmsg(0,"导出文件失败");
                false;
            }
        }
        $fieldnum =  Db::query("SELECT COUNT(modid) AS count FROM `".config("database.prefix")."modfiel` WHERE modid='".$data["modid"]."'");//查询当前模型字段总数
        $count = $fieldnum['0']['count'];
            if ($count) {
            //备份数据记录
            $list = Db::query("SELECT * FROM `".config("database.prefix")."modfiel` WHERE modid='".$data["modid"]."'");
            $result = Db::query("SHOW COLUMNS FROM `".config("database.prefix")."modfiel`");
            foreach ($list as $row) {
                $rowsql = substr($this->echosql($row,$result), 0, -2);
                $sql = "INSERT INTO `#__modfiel` VALUES (" . $rowsql . ");\n\n";
                if (false === file_put_contents($fiename.".model",$sql, FILE_APPEND | LOCK_EX)) {
                return jsonmsg(0,"导出文件失败");
                false;
                }
            }
        }
        $url = $request->scheme()."://".$request->host()."/public/model/".$data['tablename']."/".$fielnams.".model";
        return json(["code"=>1,"msg"=>"导出模型成功","modurl"=>$url,"fiename"=>$fielnams.".model","yurl"=>"public/model/".$data['tablename']."/".$fielnams.".model"]);
        }
        return jsonmsg([404,"非法操作"]);
    }
    public function echosql($row,$result){
                $sss = 0;
                $rowsql = '';
                foreach($row as $v){
                  if(strpos($result[$sss]["Type"],'int') !== false || strpos($result[$sss]["Type"],'varchar') || strpos($result[$sss]["Type"],'tinyint') !== false){    
                  if($v === NULL){
                      $rowsql .= "NULL, ";
                  }else{
                      $rowsql .= "$v, ";
                  }
                  }else{
                      $rowsql .= "'$v', ";
                  }
                  $sss++;
                }
                return $rowsql;
    }
    //模型导入
    public function importdata(Request $request){
        //接收上传的文件
		$file = request()->file('modata');
		if($file){
		   $fileinfo = $file->getInfo(); 
		   $res = file_get_contents($fileinfo['tmp_name']);
		   $sql = str_replace("\r\n", "\n", $res);
		   if(!isset($sql) || empty($sql)) return;
            $sql = str_replace("\r", "\n", str_replace('#__', config("database.prefix"), $sql));
            $ret = array();
            $num = 0;
            foreach(explode(";\n", trim($sql)) as $query) {
            $ret[$num] = '';
            $queries = explode("\n", trim($query));
            foreach($queries as $query) {
            $ret[$num] .= (isset($query[0]) && $query[0] == '#') || (isset($query[1]) && isset($query[1]) && $query[0].$query[1] == '--') ? '' : $query;
            }
            $num++;
            }
            unset($sql);
            foreach($ret as $query) {
            $query = trim($query);
            if($query) {
            if(substr($query, 0, 12) == 'CREATE TABLE') {
                $line = explode('`',$query);
                $data_name = $line[1];
                $musql = Db::query("SHOW TABLES LIKE '".$data_name."'");
                if(!empty($musql)){
                    return jsonmsg(0,"当前导入模型标识表已存在!");
                    false;
                }
                Db::query($query);
                unset($line,$data_name,$musql);
                } else {
                $line = explode('`',$query);
                $data_name = $line[1];
                if(substr($data_name, -5) == "model"){
                    $fina = explode("', '",$query);
                    $musql = Db::name("model")->where("tablename",$fina[2])->find(); 
                    if(!empty($musql)){
                    return jsonmsg(0,"当前导入模型数据已存在!");
                    false;
                }
                }
                Db::query($query);
                unset($line,$data_name);
                }
            }
        }
        return jsonmsg(1,"导入操作执行完成");
		}
    }
}