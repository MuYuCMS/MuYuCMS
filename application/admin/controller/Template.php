<?php
namespace app\admin\controller;
use app\admin\controller\Base;
use think\Request;
use think\facade\Session;
use think\Db;
use think\facade\Env;

class Template extends base
{
    //模板列表
    public function index()
    {
        //获取正在使用的模板名
        $system = Db::name("system")->field("home_temp,member_temp")->find(1);
        //获取模板数量
        $home = $this->getTempfiles("template/home_temp");
        $member = $this->getTempfiles("template/member_temp");
        //循环列出配置文件内容
        $path = [];
        $userpath = [];
        
        for($i=0;$i<count($home);$i++){
            if(is_dir(config('app.temp_path')."/home_temp/".$home[$i])){
                if(is_file(config('app.temp_path')."/home_temp/".$home[$i]."/config.php")){
                $path[] = include("./template/home_temp/".$home[$i]."/config.php");
            }else{
                continue;
            }
            }else{
                continue;
            }
            
        }
        foreach($path as $key=>$val){
            if(is_file(config('app.temp_path')."/home_temp/".$val['tempname']."/suoluetu.png")){
               $path[$key]["sltpic"] =  "/template/home_temp/".$val['tempname']."/suoluetu.png";
            }else{
               $path[$key]["sltpic"] =  '/public/images/tempic.png';
            }
        }
        for($i=0;$i<count($member);$i++){
            if(is_dir(config('app.temp_path')."/member_temp/".$member[$i])){
                if(is_file(config('app.temp_path')."/member_temp/".$member[$i]."/config.php")){
                $userpath[] = include("./template/member_temp/".$member[$i]."/config.php");
            }else{
                continue;
            }
            }else{
                continue;
            }
            
        }
        foreach($userpath as $key=>$va){
            if(is_file(config('app.temp_path')."/member_temp/".$va['tempname']."/suoluetu.png")){
               $userpath[$key]["sltpic"] =  "/template/member_temp/".$va['tempname']."/suoluetu.png";
            }else{
               $userpath[$key]["sltpic"] =  '/public/images/tempic.png';
            }
        }
        //模板赋值
        $this->view->assign(['system'=>$system,'path'=>$path,'uesrpath'=>$userpath]);
        return view("index");
    }
    //模板本地上传
	public function tempup(Request $request){
	    //接收上传的文件
		$file = request()->file('template');
		$tempurl = $request->param('temp');
		if(empty($tempurl)){
		    return jsonmsg(0,"缺少必要参数");
		}
		if($file){
		   $fileinfo = $file->getInfo();
		   $res = unzip(config('app.temp_path').$tempurl,$fileinfo['tmp_name']);
		   if($res){
		       //return json(['code'=>1,'msg'=>'上传成功!']);
		       return jsonmsg(1,'上传成功');
		   }else{
		       //return json(['code'=>0,'msg'=>'上传失败!']);
		       return jsonmsg(0,"上传失败!");
		   }
		}
	}
	//模板启用
	public function tempstart(Request $request)
	{
	    $temp = $request->param('tp');
	    $mod = $request->param('sts');
	    $res = Db::name('system')->where('id', 1)->update([$mod=>$temp]);
	    if($res){
   	   		   $this -> success("启用成功!",'Template/index');
   	   }else{
   	   		   
   	   		   $this -> error("启用失败!");
   	   }
	}
   //模板文件删除操作
   public function tempdel(Request $request){
      $mod =  $request->param('temn');
      $dir = $_SERVER['DOCUMENT_ROOT']."/template/".$mod.$request ->param('tp');
      $res = rmdirr($dir);
      if($res){
          $this->success('删除成功!','Template/index');
      }else{
          $this->error('删除失败!');
      }
   }
    //模板在线编辑
    public function tempEdit(Request $request)
    {
        $admin = Db::name('admin')->where('id',Session::get('Adminuser.id'))->field('name')->find();
        $_SESSION["poc_username"] = $admin['name'];
        //删除编辑器历史用户信息
        delete_dir_file($_SERVER['DOCUMENT_ROOT'].'/editor/Cache/User');
        $_SESSION["root"] = Session::get('Adminuser');
        $domain = $request->domain();
        $url = $domain.'/editor/index.php';
        header("Location:".$url);
    }
    
    /*
    *自定义单页
    */
    public function customform(Request $request){
        $cutpath = Db::name("custform")->select();
        return view('cusform',['cutpath'=>$cutpath]);
    }
    /*
    *添加页面渲染
    */
    public function cutformadd(Request $request){
        if($request->isAjax()){
        $data = $request->param();
        if(empty($data) || empty($data["fielname"]) || empty($data["path"]) || empty($data["finame"]) || empty($data["content"])){
            return jsonmsg(0,"参数错误!");
        }
        if(isset($data['upfile'])){
	        unset($data['upfile']);    
	        }
        $fieln = explode(".",$data["fielname"]);
        $suffix = ["text","html","php","xml","css","js"];
        if(!in_array($fieln[1],$suffix)){
            return jsonmsg(0,"文件后缀名不合法!");
        }
        if(!is_dir($data["path"])){
		    @mkdir($data["path"],true);
		}
        $data["path"] = $data["path"]."/".$data["fielname"];
        $data["content"]=stripslashes($data['content']);
        if(file_put_contents($data["path"],htmlspecialchars_decode($data["content"]))){
            $info = ["path"=>$data["path"],"finame"=>$data["finame"],"fielname"=>$data["fielname"],"admid"=>Session::get('Adminuser.id'),"create_time"=>time(),"update_time"=>time()];
            if(Db::name("custform")->where("fielname",$data["fielname"])->find() == NULL){
            Db::name("custform")->insert($info);
            }
            $this ->logs("新建单页".$data["finame"]."[".$data["fielname"]."]"."成功",Session::get('Adminuser.name'));
            return jsonmsg(1,"新建成功");
        }else{
            $this ->logs("新建单页".$data["finame"]."[".$data["fielname"]."]"."失败",Session::get('Adminuser.name'));
            return jsonmsg(0,"新建失败");
        }
        }
        return $this->fetch("form_add");
    }
    /*
    *编辑页面渲染
    */
    public function cutformedit(Request $request){
        if($request->isAjax()){
            $data = $request->param();
            if(empty($data) || empty($data["fielname"]) || empty($data["finame"]) || empty($data["content"])){
            return jsonmsg(0,"参数错误!");
            }
            if(isset($data['upfile'])){
	        unset($data['upfile']);    
	        }
            $fieln = explode(".",$data["fielname"]);
            $suffix = ["text","html","php","xml","css","js"];
            if(!in_array($fieln[1],$suffix)){
            return jsonmsg(0,"文件后缀名不合法!");
            }
            $data["content"]=stripslashes($data['content']);
            if(file_put_contents($data["path"],htmlspecialchars_decode($data["content"]))){
            unset($data["content"]);
            $data['update_time'] = time();
            Db::name("custform")->update($data);
            $this ->logs("编辑单页".$data["finame"]."[".$data["fielname"]."]"."成功",Session::get('Adminuser.name'));
            return jsonmsg(1,"编辑成功");
            }else{
            $this ->logs("编辑单页".$data["finame"]."[".$data["fielname"]."]"."失败",Session::get('Adminuser.name'));
            return jsonmsg(0,"编辑失败");
            }
        }
        $forminfo = Db::name("custform")->find($request->param('formid'));
        $html = fopen($forminfo["path"], "r");
        $forminfo["content"] = fread($html, filesize ($forminfo["path"]));
        fclose($html);
        return view("form_edit",['forminfo'=>$forminfo]);
    }
    /*
     *单页文件删除
     *
    */
    public function cutformedel(Request $request){
        $data = $request->param();
        if(empty($data)){
            return jsonmsg(0,"参数错误!");
        }
        if(isset($data["all"])){
            if(isset($data["all"]{3})){
            $fielid = explode(",",$data["all"]);
            }else{
                $fielid = array($data["all"]);
            }
            $status = 1;
            $stfiel = "/";
            $stfiels = "/";
            foreach($fielid as $vs){
            $fiel = Db::name("custform")->field("fielname,path")->find($vs);
            $res = unlink($fiel["path"]);
            if($res){
                Db::name("custform")->delete($vs);
                $stfiel .= $fiel["fielname"]."/";
            }else{
                $status .= 01;
                $stfiels .= $fiel["fielname"]."/";
            }
            }
            if($status == 1){
                if(empty($stfiel)){
                $stfiel = substr($stfiel,0,-1);
                }
                $msg = "";
                if(empty($stfiels)){
                $stfiels = substr($stfiels,0,-1);
                $msg = "-删除[".$stfiels."]失败";
                }
                
                return jsonmsg(1,"删除[".$stfiel."]成功".$msg);
            }else{
                if(empty($stfiels)){
                $stfiels = substr($stfiels,0,-1);
                }
                $msg = "";
                if(empty($stfiel)){
                $stfiel = substr($stfiel,0,-1);
                $msg = "-删除[".$stfiel."]成功";
                }
                return jsonmsg(0,"删除[".$stfiels."]失败".$msg);
            }
        }else{
            $fiel = Db::name("custform")->field("fielname,path")->find($data["formid"]);
            $res = unlink($fiel["path"]);
            if($res){
                Db::name("custform")->delete($data["formid"]);
                return jsonmsg(1,"删除[".$fiel["fielname"]."]成功");
            }else{
                return jsonmsg(0,"删除[".$fiel["fielname"]."]失败");
            }
        }
    }
    
    public function selectdir(){
        $dir = json_encode(myscandir(Env::get('root_path'),JSON_UNESCAPED_SLASHES));
        return $this->fetch("form_select",['dir'=>$dir]);
    }
}