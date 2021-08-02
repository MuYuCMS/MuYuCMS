<?php
namespace app\index\controller;
use app\index\controller\Base;
use think\Request;
use think\Db;

class Search extends Base
{
    protected $config;
    protected function initialize()
    {
        parent::initialize();
        $this->config = $this->getsystems();
    }
    public function index(Request $request){
        $sc  = !empty(input('sech')) ? urldecode(input('sech')) : urldecode($request->param('sech'));
        $tab = !empty(input('tab')) ? input('tab') : $request->param('tab');//可指定栏目或模型标识进行搜索范围控制
        $field = !empty(input('field')) ? input('field') : $request->param('field');//指定搜索字段
        $order = !empty(input('order')) ? input('order') : $request->param("order");
	$list = array();
        if(empty($order)){
            $order = "create_time desc";
        }
        $selist = "";
        $catinfo['id'] = "";
        $catinfo['pid'] = "";
        if(empty($sc)){
            return $this->error("请输入要搜索的内容",$_SERVER['HTTP_REFERER']);
        }
        $tabs = "";
        $tablename = Db::name("model")->field("tablename")->select();
        $map[] = ['a.status','=',0];
        $map[] = ['a.delete_time','=',NULL];
        if($field){
        $f = explode("|",$field);
        $new = "";
        foreach($f as $fe){
            $new .= "a.".$fe."|";
        }
        $field = substr($new,0,-1);
        $map[] = [$field,'like', "%{$sc}%"];    
        }else{
        $map[] = ['a.title|a.keyword|a.ftitle|a.abstract|e.title','like', "%{$sc}%"];
        }
        if($tab){
            if(is_numeric($tab)){
                $modid = Db::name("category")->field("modid")->find($tab);
                $tabname = Db::name("model")->field("tablename")->find($modid["modid"]);
                $tabs = $tabname["tablename"];
                $map[] = ["a.mid","=",$tab];
            }else{
                $res = "false";
                foreach($tablename as $v){
                if($tab == $v["tablename"]){
                    $tabs = $tab;
                    $catinfo['id'] = $tab;
                    $res = "true";
                }
                }
                if($res == "false"){
                    return $this->error("标识无效或配置错误",$_SERVER['HTTP_REFERER']);
                }
            }
            $list = Db::name($tabs)
                ->alias("a")
                ->join($tabs."_data b","b.aid=a.id")
                ->leftjoin("category c","c.id=a.mid")
	            ->leftjoin("member d","d.id=a.uid")
	            ->leftjoin("type e","e.id=a.type")
                ->fieldRaw("a.*,b.likes,b.browse,comment_t,c.title as catitle,d.name,e.title as tytitle")
                ->where($map)
                ->orderRaw($order)
                ->paginate(10,false,$config = ['query' => array('sech' => $sc)]);
        }else{
            foreach($tablename as $asv){
                $lists = Db::name($asv["tablename"])
                ->alias("a")
                ->join($asv["tablename"]."_data b","b.aid=a.id")
                ->leftjoin("category c","c.id=a.mid")
	            ->leftjoin("member d","d.id=a.uid")
	            ->leftjoin("type e","e.id=a.type")
                ->fieldRaw("a.*,b.likes,b.browse,comment_t,c.title as catitle,d.name,e.title as tytitle")
                ->where($map)
                ->orderRaw($order)
                ->paginate(10,false,$config = ['query' => array('sech' => $sc)]);
		if(!$lists->isEmpty()){
                    $list = $lists;
                }    
            }
        }
        return $this-> fetch('/home_temp/'.$this->config["home_temp"].'/search',['list'=>$list,'catinfo'=>$catinfo]);
    }
}
