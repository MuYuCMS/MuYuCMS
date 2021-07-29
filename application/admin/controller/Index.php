<?php
namespace app\admin\controller;
use app\admin\controller\Base;
use think\Request;
use app\admin\model\Admin;
use think\facade\Session;
use app\admin\model\Member;
use app\admin\model\Feedback;
use think\Db;
use think\facade\Env;

class Index extends Base
{

    public function index()
    {
		$menu = Db::name('rule')->where(['pid'=>0,'status'=>1])->order('orders','asc')->select()->toArray();
		foreach($menu as $key=>$val){
			$two = Db::name('rule')->where(['pid'=>$val['id'],'status'=>1])->order('orders','asc')->select();
			$menu[$key]['secontit'] = $two;
		}
        //查询是否存在未读留言
        $isRead = Db::name('feedback')->where('status',0)->find();
        if($isRead){
            $isRead = true;
        }else{
            $isRead = false;
        }
		$this->view->assign(['menu'=>$menu,'isRead'=>$isRead]);
        return $this -> view -> fetch();
    }
    
	public function welcome(Request $request)
	{
	    $array = [];
	    $data = $this->get_url_content("http://api.muyucms.com/AD/url.txt");
	    $data = explode('|',$data);
        foreach($data as $k=>$val){
            $data = explode('&',$val);
            foreach($data as $va){
                $data = explode('=>',$va);
                $data[0] = trim($data[0]);
                $array[$data[0]] = $data[1];
            }
        }
        //查询系统信息
        $system = Db::name('system')->where('id',1)->find();
        //版本为整数的时候保留小数部分
        $system['version'] = json_encode($system['version'],JSON_PRESERVE_ZERO_FRACTION);
        //当前管理员信息
        $admin = Session::get('Adminuser');
        //当前管理所属角色
        $role = Db::name('role')->where('id',$admin['roles'])->find();
        //查询最新5篇文章
        $matter = [];
        $table = Db::name("model")->field("tablename")->select();
        foreach($table as $t){
            $al = Db::name($t["tablename"])->limit(6)->order('create_time','desc')->select()->toArray();
            if($al != null){
                $matter[] = $al;
            }
        }
        if(!empty($matter)){
            $matter = ary3_ary2($matter);
            $matter = arrorder($matter,"create_time desc");
            $matter = array_slice($matter,0,6);
        }
        //查询待审核文章有多少篇
        $checkMatter = [];
        foreach($table as $t){
            $al = Db::name($t["tablename"])->where(['status'=>3,'delete_time'=>NULL])->select()->toArray();
            if($al != null){
                $checkMatter[] = $al;
            }
        }
        if(!empty($checkMatter)){
            $checkMatter = ary3_ary2($checkMatter);
            $checkMatter = count($checkMatter);
        }else{
            $checkMatter = 0;
        }
        //查询待审核评论有多少条
        $checkComment = Db::name('comment')->where('status',3)->count();
        //统计未读留言
        $unreadFeedback = Db::name('feedback')->where('status',0)->count();
        //文章总数
        $articleSum = Db::name('bigdata')->sum("article_add");
        //留言总数
        $feedbackSum = Db::name('feedback')->count();
        //评论总数
        $commentSum = Db::name('comment')->count();
        //收入总数
        $moneysums = Db::name('member_paylog')->sum('money');
        //会员总数
        $memberSum = Db::name('member')->count();
        //管理员总数
        $adminSum = Db::name('admin')->count();
        //角色总数
        $roleSum = Db::name('role')->count();
        //总浏览量
        $browse = Db::name('bigdata')->sum('article_browse');
        //总点赞量
        $like = Db::name('bigdata')->sum('article_likes');
        //统计友情友链
        $blogroll = Db::name('links')->count();
        //统计广告
        $advertising = Db::name('advertising')->count();
        //查询大数据表中今天的数据
        $bigdata = Db::name('bigdata')->whereTime('create_time','today')->find();
		//赋值给模板
	    $this -> view -> assign([
	        'system'=>$system,
	        'admin'=>$admin,
	        'role'=>$role,
	        'matter'=>$matter,
	        'checkmatter'=>$checkMatter,
	        'checkcomment'=>$checkComment,
	        'unreadfeedback'=>$unreadFeedback,
	        'articlesum'=>$articleSum,
	        'feedbacksum'=>$feedbackSum,
	        'commentsum'=>$commentSum,
	        'moneysum'=>$moneysums,
	        'membersum'=>$memberSum,
	        'adminsum'=>$adminSum,
	        'rolesum'=>$roleSum,
	        'articlebrowse'=>$browse,
	        'articlelike'=>$like,
	        'blogroll'=>$blogroll,
	        'advertising'=>$advertising,
	        'ad'=>$array,
	        'bigdata'=>$bigdata
	        ]);
	    return $this -> view -> fetch();
	}
	
	//数据中心
	public function bigdata()
	{   
	    //今日数据
        $bigdata = Db::name('bigdata')->whereTime('create_time','today')->find();
        //统计文章总和
        $ArticleSum = Db::name('bigdata')->sum("article_add");
        //统计总点赞量
        $ArticleLike = Db::name('bigdata')->sum('article_likes');
        //统计会员总数
        $memberSum = Db::name('member')->count();
        //月净收入
        $moneyMonthSum = Db::name('bigdata')->whereTime('create_time','month')->sum('pay_money');
        //总收入
        $moneySum = Db::name('member_paylog')->sum('money');
        //总留言数
        $feedbackSum = Db::name('feedback')->count();
        //总评论数
        $commentSum =Db::name('comment')->count();
        //总消费
        $buyMoney = Db::name('member_buylog')->sum('money');
        //总下载量
        $downloadsSum = Db::name('bigdata')->sum('downloads');
        //文章浏览量排行榜
        $maxArticle = [];
        $table = Db::name("model")->field("tablename")->select();
        foreach($table as $t){
            $al = Db::name($t["tablename"])
            ->alias("a")
            ->join($t["tablename"]."_data b","b.aid=a.id")
            ->fieldRaw("a.id,a.title,b.aid,b.browse")
            ->limit(7)
            ->order('browse','desc')
            ->select()
            ->toArray();
            if($al != null){
                $maxArticle[] = $al;
            }
            
        }
        if(!empty($maxArticle)){
            $maxArticle = ary3_ary2($maxArticle);
            $is = 0;
            $class = ["0"=>"first","1"=>"second","2"=>"third"];
            foreach($maxArticle as $key=>$ma){
                    if($is <= 2){
                    $maxArticle[$key]["class"] = $class[$is];
                    }else{
                    $maxArticle[$key]["class"] = "";    
                    }
                    $is++;
                }
            $maxArticle = arrorder($maxArticle,"browse desc");
            $maxArticle = array_slice($maxArticle,0,7);
        }
        //充值排行榜
        $maxPayMoney = Db::name('member')->field('id,name,consumption')->order('consumption','desc')->limit(7)->select()->toArray();
        //消费排行榜
        $maxBuyMoney = Db::name('member')->field('id,name,total')->order('total','desc')->limit(7)->select()->toArray();
        //访问数据分析
        $access = Db::name('bigdata')->field('create_time,ip,uv,pv')->order('create_time','desc')->limit(7)->select()->toArray();
        //流量概况逻辑运算
        $pv = [];
        $uv = [];
        $ip = [];
        for($i=0;$i<=22;){
            $startTime = $i;
            $endTime = $i + 2;
            if($endTime == 24){
                $twoTime = Db::name('ip_log')
                    ->whereTime('create_time', '>=', strtotime(date("Y-m-d {$startTime}:00:00")))
                	->whereTime('create_time', '<', strtotime(date("Y-m-d 00:00:00",strtotime("+1 day"))))
                    ->select();
            }else{
                $twoTime = Db::name('ip_log')
                    ->whereTime('create_time', '>=', strtotime(date("Y-m-d {$startTime}:00:00")))
                	->whereTime('create_time', '<', strtotime(date("Y-m-d {$endTime}:00:00")))
                    ->select();
            }
            $pv[] = $twoTime->count();
            $columnUV = $twoTime->column('is_member');
            $columnIP = $twoTime->column('ip');
            //删除数组中游客的数据
            $columnUV = array_merge(array_diff($columnUV, array(0)));
            //去掉数组重复的值并统计个数
            $uv[] = count(array_unique($columnUV));
            $ip[] = count(array_unique($columnIP));        
            $i = $i + 2;
        }
        //转成字符串
        $pv = implode(',',$pv);
        $uv = implode(',',$uv);
        $ip = implode(',',$ip);
        //统计移动端和PC端的流量
        $mobile = Db::name('ip_log')->whereTime('create_time','today')->where('source',0)->count();
        $pc = Db::name('ip_log')->whereTime('create_time','today')->where('source',1)->count();
        //最近一周会员注册量
        $weekArray = [];
        $week = -6;
        while($week<=0){
            $weekdata = Db::name('bigdata')->whereBetweenTime('create_time',date('Y-m-d',strtotime("{$week} day")))->field('member_add')->order('create_time','asc')->find();
            if($weekdata == NULL){
                $weekdata['member_add'] = 0;
            }
            $weekArray[] = $weekdata['member_add'];
            $week = $week + 1;
        }
        //转为字符串
        $week = implode(',',$weekArray);
        //数据概括
        $buyMoneyData = [];
        $payMoneyData = [];
        $articleBrowse = [];
        $articleAdd = [];
        $half = -14;
        while($half<=0){
            $halfMonth = Db::name('bigdata')->whereBetweenTime('create_time',date('Y-m-d',strtotime("{$half} day")))->field('article_add,article_browse,buy_money,pay_money')->order('create_time','asc')->find();
            if($halfMonth == NULL){
                $halfMonth['article_add'] = 0;
                $halfMonth['article_browse'] = 0;
                $halfMonth['buy_money'] = 0;
                $halfMonth['pay_money'] = 0;
            }
            $articleAdd[] = $halfMonth['article_add'];
            $articleBrowse[] = $halfMonth['article_browse'];
            $buyMoneyData[] = $halfMonth['buy_money'];
            $payMoneyData[] = $halfMonth['pay_money'];
            $half = $half + 1;
        }
        //转为字符串
        $articleAdd = implode(',',$articleAdd);
        $articleBrowse = implode(',',$articleBrowse);
        $buyMoneyData = implode(',',$buyMoneyData);
        $payMoneyData = implode(',',$payMoneyData);
        //月发文同期增长
        $lastMonthArticleAdd = Db::name('bigdata')->whereTime('create_time', 'last month')->sum('article_add');//上个月发文量
        $monthArticleAdd = Db::name('bigdata')->whereTime('create_time', 'month')->sum('article_add');//本个月发文量
        if($monthArticleAdd != 0){
            $growthArticleAdd = ($monthArticleAdd - $lastMonthArticleAdd)/$monthArticleAdd*100;//同期增长
        }else{
            $growthArticleAdd = 0;
        }
        $growthArticleAdd = round($growthArticleAdd,2);//保留小数点后两位
        //月浏览量同期增长
        $lastMonthArticleBrowse = Db::name('bigdata')->whereTime('create_time', 'last month')->sum('article_browse');//上个月浏览量
        $monthArticleBrowse = Db::name('bigdata')->whereTime('create_time', 'month')->sum('article_browse');//本个月发文量
        if($monthArticleBrowse != 0){
            $growthArticleBrowse = ($monthArticleBrowse - $lastMonthArticleBrowse)/$monthArticleBrowse*100;//同期增长
        }else{
            $growthArticleBrowse = 0;
        }
        $growthArticleBrowse = round($growthArticleBrowse,2);//保留小数点后两位
        //月浏览量同期增长
        $lastMonthPayMoney = Db::name('bigdata')->whereTime('create_time', 'last month')->sum('pay_money');//上个月收入
        $monthPayMoney = Db::name('bigdata')->whereTime('create_time', 'month')->sum('pay_money');//本个月收入
        if($monthPayMoney != 0){
            $growthPayMoney = ($monthPayMoney - $lastMonthPayMoney)/$monthPayMoney*100;//同期增长
        }else{
            $growthPayMoney = 0;
        }
        $growthPayMoney = round($growthPayMoney,2);//保留小数点后两位
        $this->view->assign([
            'bigdata'=>$bigdata,
            'articlesum'=>$ArticleSum,
            'articlelike'=>$ArticleLike,
            'membersum'=>$memberSum,
            'moneymonthsum'=>$moneyMonthSum,
            'moneysum'=>$moneySum,
            'feedbacksum'=>$feedbackSum,
            'commentsum'=>$commentSum,
            'buymoney'=>$buyMoney,
            'downloads'=>$downloadsSum,
            'maxarticle'=>$maxArticle,
            'maxpaymoney'=>$maxPayMoney,
            'maxbuymoney'=>$maxBuyMoney,
            'access'=>$access,
            'pv'=>$pv,
            'uv'=>$uv,
            'ip'=>$ip,
            'mobile'=>$mobile,
            'pc'=>$pc,
            'week'=>$week,
            'articleadd'=>$articleAdd,
            'articlebrowse'=>$articleBrowse,
            'buymoneydata'=>$buyMoneyData,
            'paymoneydata'=>$payMoneyData,
            'growtharticleadd'=>$growthArticleAdd,
            'growtharticlebrowse'=>$growthArticleBrowse,
            'growthpaymoney'=>$growthPayMoney
            ]);
	    return $this->view->fetch();
	}
	//退出登录
	public function loginout(Request $request)
	{
		//接收前台传过来的管理员name值
		$na = $request->post('name');
		$date = [];
		//获取当前客户端的IP地址并赋值给$date变量
		$date['outip'] = $request->ip();
		//获取当前时间并赋值给$date变量
		$date['outtime'] = time();
		//将$date更新到管理员数据表
		Db::name('admin')->where('name',$na)->update($date);
		//删除当前Sessino
		Session::delete('Adminuser');
		//返回退出信息
	    $this -> success('已安全退出','admin/Login/login');
	}
	
	/**
	* 后台首页刷新
	*/
	public function clear()
	{
    	$CACHE_PATH = Env::get('runtime_path') . 'cache/';
    	$TEMP_PATH = Env::get('runtime_path'). 'temp/';
    	$EDITTOR_LOG_PATH = $_SERVER['DOCUMENT_ROOT'].'/editor/Log';
    	$EDITTOR_CACHE_PATH = $_SERVER['DOCUMENT_ROOT'].'/editor/Cache';
        if (delete_dir_file($CACHE_PATH) || delete_dir_file($TEMP_PATH) || delete_dir_file($EDITTOR_LOG_PATH) || delete_dir_file($EDITTOR_CACHE_PATH)) {
            return json(["code"=>1,"msg"=>"清除成功!"]);
        } else {
            return json(["code"=>0,"msg"=>"清除失败!"]);
        }
    }

}
