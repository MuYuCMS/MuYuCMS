<?php
namespace app\index\controller;
use app\index\controller\Base;
use think\Request;
use think\Db;

class Search extends Base
{  
    public function index(Request $request){
        $sc  = !empty(input('search')) ? input('search') : $request->param('sc');
        $scc = urldecode(input('search'));
        $ids = "0";
        if (empty($sc) || $scc == ' ') {
            return $this->error('请输入要搜索的内容!');
        } else {
            $article = Db::name('article');
            $map[] = ['a.status','=',0];
            $map[] = ['a.title|a.keyword|a.ftitle|a.abstract|a.type','like', "%{$scc}%"];
            $scar = $article->alias('a')->join('article_data b', 'b.aid=a.id')->fieldRaw('a.*,b.*')->order('a.create_time desc')->where($map)->paginate(10, false, $config = ['query' => array('sc' => $sc)]);
            $this->assign(['scar'=>$scar,'ids'=>$ids]);
            return $this-> fetch('/search');
        }
    }
}