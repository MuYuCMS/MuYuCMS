<?php
namespace app\admin\controller;

use app\admin\controller\Base;
use think\Request;
use think\Db;
use think\facade\Env;
use think\Config;

// 模型字段处理
class Modelfield extends Base
{
    public static function connect()
    {
        return Db::connect();
    }
    public function index(Request $request){
        $modid = $request->param('moid');
        $list = Db::name('modfiel')->where('modid',$request->param('moid'))->paginate(25);
        return view('index',['list'=>$list,'modid'=>$modid]);
    }
    public function edit(Request $request){
        $db = self::connect();
        if($request->isAjax()){
            $data = $request->param();
            $getdata = Db::name('modfiel')->where(['modid'=>$data['modid'],'id'=>$data['id']])->field('field,type,leng')->find();
            $getfield = Db::name('modfiel')->where(['field'=>$data['field']])->find();
            if($data['field'] != $getdata['field']){
                if($getfield != NULL){
                return jsonmsg(0,"字段标识已存在");
                false;
                }
            }
            $fieldif = ["varchar","char"];
            if(in_array($data['type'],$fieldif)){
               if(empty($data['leng'])){
                return jsonmsg(0,"此字段类型请指定长度");
                false;   
               } 
            }
            $tab = Db::name('model')->where('id',$data['modid'])->field('tablename')->find();
            $res = Db::name('modfiel')->update($data);
            if($res){
                if($data['field'] !== $getdata['field']){
                   $db->query("ALTER TABLE ".config("database.prefix").$tab['tablename']." CHANGE ".$getdata['field']." ".$data['field']." ".$data['type']."(".$data['leng'].")");
                }
                if($data['type'] !== $getdata['type'] || $data['leng'] !== $getdata['leng']){
                    if(!empty($data['leng'])){
                   $db->query("ALTER TABLE ".config("database.prefix").$tab['tablename']." MODIFY COLUMN ".$data['field']." ".$data['type']."(".$data['leng'].")");
                    }else{
                    $db->query("ALTER TABLE ".config("database.prefix").$tab['tablename']." MODIFY COLUMN ".$data['field']." ".$data['type']);    
                    }
                }
                return jsonmsg(1,'修改成功');
            }else{
                return jsonmsg(1,'修改失败');
            }
        }
        $list = Db::name("modfiel")->where("id",$request->param('fieid'))->find();
        $sqlfied = config('modelfield.sql');
        $formfied = config('modelfield.form');
        return view('edit',['list'=>$list,'sqlfied'=>$sqlfied,'formfied'=>$formfied]);
    }
    
    public function add(Request $request){
        $db = self::connect();
        if($request->isAjax()){
            $data = $request->param();
            $field = Db::name("modfiel")->where(["modid"=>$data['modid'],"field"=>$data['field']])->find();
            if($field != NULL){
                return jsonmsg(0,'字段标识已存在');
                false;
            }
            $fieldif = ["varchar","char"];
            if(in_array($data['type'],$fieldif)){
               if(empty($data['leng'])){
                return jsonmsg(0,"此字段类型请指定长度");
                false;   
               } 
            }
            $tab = Db::name('model')->where('id',$data['modid'])->field('tablename')->find();
            if($data['chart'] == 2){
                $tab['tablename'] = $tab['tablename']."_data";
            }
            $res = Db::name('modfiel')->insert($data);
            if($res){
                if(!empty($data['leng'])){
                $db->query("ALTER TABLE ".config("database.prefix").$tab['tablename']." ADD COLUMN ".$data['field']." ".$data['type']."(".$data['leng'].") DEFAULT NULL");
                }else{
                $db->query("ALTER TABLE ".config("database.prefix").$tab['tablename']." ADD COLUMN ".$data['field']." ".$data['type']." DEFAULT NULL");    
                }
                return jsonmsg(0,'添加成功');
            }else{
                return jsonmsg(0,'添加失败');
            }
        }
        $modid = $request->param("modid");
        $sqlfied = config('modelfield.sql');
        $formfied = config('modelfield.form');
        return view('add',['modid'=>$modid,'sqlfied'=>$sqlfied,'formfied'=>$formfied]); 
    }
    public function fiedel(Request $request){
        $db = self::connect();
        if($request->isPost()){
          $data = $request->param();
          $getfi = Db::name('modfiel')->where("id",$data['delid'])->field("field,chart,ismuyu")->find();
          if($getfi['ismuyu'] == 1){
            return jsonmsg(500,'系统字段禁止删除');
            false;
          }
          $tab = Db::name('model')->where('id',$data['modid'])->field('tablename')->find();
          if($getfi['chart'] == 2){
              $tab['tablename'] = $tab['tablename']."_data";
          }
          $res = Db::name('modfiel')->delete($data['delid']);
          if($res){
            $db->query("ALTER TABLE ".config("database.prefix").$tab['tablename']." DROP COLUMN ".$getfi['field']."");
            return jsonmsg(1,'删除成功');
          }else{
                return jsonmsg(0,'删除失败');
            }
        }
        return jsonmsg(500,'非法操作');
    }
}