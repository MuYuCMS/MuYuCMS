<?php

namespace app\index\controller;
use think\DB;
use think\facade\Session;
use think\facade\Cookie;
use think\Request;
use qqpay\QQPay;
use think\facade\Env;
class Palymat extends Base
{
    protected $config;
    protected function initialize()
    {
        parent::initialize();
        $this->getuserst();
        $this->config = $this->getsystems();
    }
    
    public function topaly(Request $request)
    {
        //接收数据
        $data = $request->param();
        //获取当前客户端IP地址
        $data["buyip"] = $request->ip();
        //查询支付开关控制
        $status = Db::name("pay_set")->field($data["payst"]."_close")->find(1);
        //查询已购买过的产品ID
        $member = Db::name('Member')->where('id',Session::get('Member.id'))->field('buymat')->find();
        //字符串转数组
        if(isset($member)){
	       $array = explode(",",$member['buymat']);
	    }
	    if(in_array($data["matid"],$array)){
	        $this->error("请勿重复购买!",'User/my_buylog');
	    }
        $tab = Db::name("model")->field("tablename")->select();
        foreach($tab as $va){
            $infs = Db::name($va["tablename"])->field("id,title,moneys")->find($data["matid"]);
            if($infs != null){
                $info = $infs;
            }
        }
        //判断该支付方式是否关闭
        if($status[$data["payst"]."_close"] !== 1){
            //查询易支付的相关信息
            $epayInfo = Db::name('pay')->field('epay_url,epay_appid,epay_key')->find(1);
            $epay = Db::name('pay_set')->field('epay_close')->find(1);
            if($epay['epay_close'] == 1 && $data['payst'] !== "moneypay"){//发起易支付
                if(empty($info) || empty($data)){
                    $this->error("支付信息缺失,终止支付!");
                }else{
                    $tradeNo = trade_no();
                    //订单描述
                    $parameter = [
                    		"pid" => (int)$epayInfo['epay_appid'],//商户ID
                    		"type" => $data["payst"],//支付方式
                    		"notify_url"	=> $request->domain().url("Palymat/epayNotify"),//异步通知地址
                    		"return_url"	=> $request->domain().url("Palymat/epayReturn"),//页面跳转同步通知页面路径
                    		"out_trade_no"	=> $tradeNo,//商户订单号
                    		"name"	=> $info['title'],//商品名称
                    		"money"	=> $info['moneys'],//金额
                    		"sitename"	=> $this->config['title']//站点名称
                    ];
                    //检测易支付API是否支持SSL
                    $stream = stream_context_create (array("ssl" => array("capture_peer_cert" => true)));
                    $read = fopen($epayInfo['epay_url'], "rb", false, $stream);
                    $cont = stream_context_get_params($read);
                    $result = (isset($cont["options"]["ssl"]["peer_certificate"])) ? true : false;
                    if($result){
                        $transport = "https";
                    }else{
                        $transport = "http";
                    }
                    //易支付的配置信息
                    $epayConfig = [
                        'partner'       =>  $epayInfo['epay_appid'],//商户ID
                        'key'           =>  $epayInfo['epay_key'],//商户KEY
                        'sign_type'     =>  strtoupper('MD5'),//签名方式 不需修改
                        'input_charset' =>  strtolower('gbk'),//字符编码格式 目前支持 gbk 或 utf-8
                        'transport'     =>  $transport,//访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
                        'apiurl'        =>  $epayInfo['epay_url']//支付API地址
                        ];
                    //建立请求
                    require_once(Env::get('extend_path')."/epay/lib/epay_submit.class.php");
                    $epaySubmit = new \AlipaySubmit($epayConfig);
                    $htmlText = $epaySubmit->buildRequestForm($parameter);
                    if($data['payst'] == "alipay"){
                        $payType = "2";
                    }elseif($data['payst'] == "wxpay"){
                        $payType = "1";
                    }elseif($data['payst'] == "qqpay"){
                        $payType = "0";
                    }
                    //记录订单信息
                    $buyInfo = [
                        'uid'=>Session::get('Member.id'),
                        'order_id'=>$tradeNo,
                        'product_id'=>$info['id'],
                        'buy_type'=>"0",
                        'pay_type'=>$payType,
                        'money'=>$info['moneys'],
                        'buy_ip'=>$data["buyip"],
                        'create_time'=>time(),
                        'intro'=>'购买内容:'.$info['title'],
                        'status'=>"0"
                        ];
                    Db::name("member_buylog")->insert($buyInfo);
                    echo $htmlText;
                }
            }else{
                $this->error('该支付方式已关闭!');
            }
        }else{
            if(empty($info) || empty($data)){
                $this->error("支付信息缺失,终止支付!");
            }else{
                if($data["payst"] == "alipay"){
                    $swich = Db::name("pay_set")->field("alipay")->find(1);
                    //查询当面付配置信息
                    $setalipay = Db::name("pay")->find(1);
                    //等于1为官方支付宝，0为当面支付
                    if($swich['alipay'] == 0){
                        $params = new \Yurun\PaySDK\AlipayApp\Params\PublicParams();
                        $params->sign_type = "RSA2";
                        $params->charset = "utf-8";
                        $params->version = "1.0";
                        //订单号
                        $tradeNo = trade_no();
                        //APP ID
                        $params->appID = $setalipay["alipayf2f_private_id"];
                        //当面付私钥
                        $params->appPrivateKey = $setalipay["alipayf2f_private_key"];
                        //支付宝公钥
                        $params->appPublicKey = $setalipay["alipayf2f_public_key"];
                        //实例化，传入配置信息
                        $pay = new \Yurun\PaySDK\AlipayApp\SDK($params);
                        
                        $requests = new \Yurun\PaySDK\AlipayApp\FTF\Params\QR\Request();
                        // 商户订单号
                        $requests->businessParams->out_trade_no = $tradeNo; 
                        // 价格
                        $requests->businessParams->total_amount = $info['moneys']; 
                        // 产品标题
                        $requests->businessParams->subject = $info['title'];
                        //最晚付款时间,10分钟
                        $requests->businessParams->timeout_express = "10m"; 
                        try{
                            //调用接口
                            $payInfo = $pay->execute($requests);
                            if($pay->checkResult()){
                                //订单创建时间
                                $createTime = time();
                                //记录订单信息
                                $buyInfo = [
                                    'uid'=>Session::get('Member.id'),
                                    'order_id'=>$tradeNo,
                                    'product_id'=>$info['id'],
                                    'buy_type'=>"0",
                                    'pay_type'=>"2",
                                    'money'=>$info['moneys'],
                                    'buy_ip'=>$data["buyip"],
                                    'create_time'=>$createTime,
                                    'intro'=>'购买内容:'.$info['title'],
                                    'status'=>"0"
                                    ];
                                Db::name("member_buylog")->insert($buyInfo);
                                $info['out_trade_no'] = $tradeNo;
                                $info['time'] = $createTime;
                                $info['class'] = "Palymat";
                                $info['qr_code'] = $payInfo["alipay_trade_precreate_response"]['qr_code'];
                                Session::set('MattersInfo',$info);
                                $this->redirect('Palymat/facepay');
                            }
                        }
                        catch(Exception $e){
                            $this->error($pay->response->body);
                        }
                    }elseif($swich['alipay'] == 1){//支付宝官方支付
                        // 公共配置
                        $params = new \Yurun\PaySDK\Alipay\Params\PublicParams();
                        $params->appID = $setalipay['alipay_pid'];
                        $params->md5Key = $setalipay['alipay_key'];
                        // SDK实例化，传入公共配置
                        $pay = new \Yurun\PaySDK\Alipay\SDK($params);
                        $requests = new \Yurun\PaySDK\Alipay\Params\Pay\Request();
                        $requests->businessParams->seller_id = $setalipay['alipay_pid']; 
                        //订单号
                        $tradeNo = trade_no();
                        // 支付后通知地址（作为支付成功回调，这个可靠）
                        $requests->notify_url = $request->domain().url("Palymat/aliPayNotify");
                        // 支付后跳转返回地址
                        $requests->return_url = $request->domain().url("Palymat/aliPayReturn"); 
                        // 商户订单号
                        $requests->businessParams->out_trade_no = $tradeNo; 
                        // 价格
                        $requests->businessParams->total_fee = $info['moneys']; 
                        // 商品标题
                        $requests->businessParams->subject = $info['title']; 
                        //最晚付款时间,10分钟
                        $requests->businessParams->it_b_pay = "10m"; 
                        //记录订单信息
                        $buyInfo = [
                            'uid'=>Session::get('Member.id'),
                            'order_id'=>$tradeNo,
                            'product_id'=>$info['id'],
                            'buy_type'=>"0",
                            'pay_type'=>"2",
                            'money'=>$info['moneys'],
                            'buy_ip'=>$data["buyip"],
                            'create_time'=>time(),
                            'intro'=>'购买内容:'.$info['title'],
                            'status'=>"0"
                            ];
                        Db::name("member_buylog")->insert($buyInfo);
                        // 获取跳转url
                        $pay->prepareExecute($requests, $url);
                        $this->redirect($url);
                }
                }elseif($data["payst"] == "wxpay"){//微信支付
                    //查询微信配置信息
                    $wxpayInfo = Db::name("pay")->field('wxpay_mchid,wxpay_kye,wxpay_appid')->find(1);
                    //生成订单
                    $tradeNo = trade_no();
                    // 公共配置
                    $params = new \Yurun\PaySDK\Weixin\Params\PublicParams();
                    // 支付平台分配给开发者的应用ID 
                    $params->appID = $wxpayInfo['wxpay_appid'];
                    // 微信支付分配的商户号
                    $params->mch_id = $wxpayInfo['wxpay_mchid'];
                    // API 密钥
                    $params->key = $wxpayInfo['wxpay_kye'];
                    // SDK实例化，传入公共配置
                    $pay = new \Yurun\PaySDK\Weixin\SDK($params);
                    // 支付接口
                    $requests = new \Yurun\PaySDK\Weixin\Native\Params\Pay\Request();
                    // 商品描述
                    $requests->body = $info['title']; 
                    // 订单号
                    $requests->out_trade_no = $tradeNo; 
                    // 订单总金额，单位为：分
                    $requests->total_fee = $info['moneys']*100; 
                    // 客户端ip
                    $requests->spbill_create_ip = $data["buyip"]; 
                    //异步通知地址
                    $requests->notify_url = $request->domain().url("Palymat/wxpay"); 
                    //交易类型
                    $requests->trade_type = "NATIVE";
                    //商品ID
                    $requests->product_id = $info['id'];
                    // 调用接口
                    $payInfo = $pay->execute($requests);
                    //订单创建时间
                    $createTime = time();
                    //记录订单信息
                    $buyInfo = [
                        'uid'=>Session::get('Member.id'),
                        'order_id'=>$tradeNo,
                        'product_id'=>$info['id'],
                        'buy_type'=>"0",
                        'pay_type'=>"1",
                        'money'=>$info['moneys'],
                        'buy_ip'=>$data["buyip"],
                        'create_time'=>$createTime,
                        'intro'=>'购买内容:'.$info['title'],
                        'status'=>"0"
                        ];
                    Db::name("member_buylog")->insert($buyInfo);
                    $info['time'] = $createTime;
                    $info['out_trade_no'] = $tradeNo;
                    $info['class'] = "Palymat";
                    //二维码链接
                    $info['code_url'] = $payInfo['code_url'];
                    Session::set('MattersInfo',$info);
                    $this->redirect('Palymat/wxpay');
                }elseif($data["payst"] == "qqpay"){//QQ支付
                    $qqPayInfo = Db::name("pay")->find(1);
                    $tradeNo = trade_no();
                    $qqArr = [
                        "mch_id"     => $qqPayInfo['qqpay_mchid'],//商户号
                        "notify_url" => $request->domain().url("Palymat/qqpay"),//异步通知回调地址
                        "key"        => $qqPayInfo['qqpay_key'],//商户key
                    ];
                    $param = [
                        "out_trade_no"  =>  $tradeNo,// 订单号         
                        "trade_type"    =>  "NATIVE",// 固定值          
                        "total_fee"     =>  $info['moneys'],// 单位为分            
                        "body"          =>  $info['title'],//订单标题     
                    ];
                    //实例化
                    $qq      = new QQPay($qqArr);
                    //下单操作
                    $unified = $qq->unifiedOrder($param);
                    if($unified["return_code"] == "SUCCESS" &&  $unified["result_code"] == "SUCCESS")
                    {
                        //订单创建时间
                        $createTime = time();
                        //记录订单信息
                        $buyInfo = [
                            'uid'=>Session::get('Member.id'),
                            'order_id'=>$tradeNo,
                            'product_id'=>$info['id'],
                            'buy_type'=>"0",
                            'pay_type'=>"0",
                            'money'=>$info['moneys'],
                            'buy_ip'=>$data["buyip"],
                            'create_time'=>time(),
                            'intro'=>'购买内容:'.$info['title'],
                            'status'=>"0"
                            ];
                        Db::name("member_buylog")->insert($buyInfo);
                        $info['time'] = $createTime;
                        $info['trade_no'] = $tradeNo;
                        $info['class'] = "Palymat";
                        $info['code_url'] = $unified['code_url'];
                        Session::set('MattersInfo',$info);
                        $this->redirect('Palymat/qqpay');
                    }else{
                        $this->error('请求出错!');
                    }
                }elseif($data["payst"] == "moneypay"){//余额支付
                    //查询当前会员已购买的文章以及余额
                    $member = Db::name('Member')->where('id',Session::get('Member.id'))->field('buymat,money')->find();
                    if($member['money'] < $info['moneys']){
                        $this->error('余额不足,请充值!');
                    }else{
                        //记录订单信息
                        $buyInfo = [
                            'uid'=>Session::get('Member.id'),
                            'order_id'=>trade_no(),
                            'product_id'=>$info['id'],
                            'buy_type'=>"0",
                            'pay_type'=>"3",
                            'money'=>$info['moneys'],
                            'buy_ip'=>$data["buyip"],
                            'create_time'=>time(),
                            'intro'=>'购买内容:'.$info['title'],
                            'status'=>"1"
                            ];
                        Db::name("member_buylog")->insert($buyInfo);
                        //扣款操作
                        $balance = $member['money'] - $info['moneys'];
                        $res = Db::name('Member')->where('id',Session::get('Member.id'))->update(['money'=>$balance]);
                        if($res){
                            //将当前文章ID作记录
                            if(empty($member['buymat'])){
                                $buyMatid = $info['id'];
                            }else{
                                $buyMatid = $member['buymat'].','.$info['id'];
                            }
                            Db::name('Member')->where('id',Session::get('Member.id'))->update(['buymat'=>$buyMatid]);
                            $this->redirect('User/my_buylog');
                        }else{
                            $this->error('支付失败!');
                        }
                    }
    
                }
            }
        }

    }
    
    //微信支付轮询地址
    public function wxpay()
    {
        $info = Session::get('MattersInfo');
        if(request()->isPost()){
            //查询微信配置信息
            $wxpayInfo = Db::name("pay")->field('wxpay_mchid,wxpay_kye,wxpay_appid')->find(1);
            // 公共配置
            $params = new \Yurun\PaySDK\Weixin\Params\PublicParams();
            // 支付平台分配给开发者的应用ID 
            $params->appID = $wxpayInfo['wxpay_appid'];
            // 微信支付分配的商户号
            $params->mch_id = $wxpayInfo['wxpay_mchid'];
            // API 密钥
            $params->key = $wxpayInfo['wxpay_kye'];
            // SDK实例化，传入公共配置
            $pay = new \Yurun\PaySDK\Weixin\SDK($params);
            $requests = new \Yurun\PaySDK\Weixin\OrderQuery\Request;
            // 微信订单号，与商户订单号二选一
            $requests->transaction_id = $info['out_trade_no'];
            $result = $pay->execute($requests);
            if($pay->checkResult()){
                //支付成功
                if($result['trade_state '] == "SUCCESS"){
                    if($result['cash_fee']/100 !== (float)$info['moneys']){
                        Session::delete('MattersInfo');
                        return ['msg'=>"支付金额异常,终止支付!",'code'=>2,'url'=>url('User/index')];
                    }else{
                        //订单更新为已付款
                        Db::name("member_buylog")->where("order_id",$info["out_trade_no"])->update(["status"=>"1","create_time"=>time()]);
                        //查询订单信息
                        $buyinfo = Db::name("member_buylog")->where("order_id",$info["out_trade_no"])->find();
                        //查询已购买过的产品ID集
                        $buymat = Db::name('Member')->where('id',$buyinfo["uid"])->field('buymat')->find();
                        if(empty($buymat['buymat'])){
                            $buyMatid = $info['id'];
                        }else{
                            $buyMatid = $buymat['buymat'].','.$info['id'];
                        }
                        $res = Db::name("member")->where("id",$buyinfo["uid"])->update(["buymat"=>$buyMatid]);
                        if($res){
                            //删除session
                            Session::delete('MattersInfo');
                            //更新操作
                            $this->success('支付成功!','User/my_buylog');
                        }
                    }
                }
            }
        }
        return $this->fetch('/home_temp/'.$this->config["home_temp"].'/wxpay',['wxpay'=>$info]);
    }
    
    //易支付回调地址
    public function epayReturn(Request $request)
    {
        if(request()->isGet()){
            //接收回调
            $data = $request->get();
            $epayInfo = Db::name('pay')->field('epay_url,epay_appid,epay_key')->find(1);
            //检测易支付API是否支持SSL
            $stream = stream_context_create (array("ssl" => array("capture_peer_cert" => true)));
            $read = fopen($epayInfo['epay_url'], "rb", false, $stream);
            $cont = stream_context_get_params($read);
            $result = (isset($cont["options"]["ssl"]["peer_certificate"])) ? true : false;
            if($result){
                $transport = "https";
            }else{
                $transport = "http";
            }
            //易支付的配置信息
            $epayConfig = [
                'partner'       =>  $epayInfo['epay_appid'],//商户ID
                'key'           =>  $epayInfo['epay_key'],//商户KEY
                'sign_type'     =>  strtoupper('MD5'),//签名方式 不需修改
                'input_charset' =>  strtolower('gbk'),//字符编码格式 目前支持 gbk 或 utf-8
                'transport'     =>  $transport,//访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
                'apiurl'        =>  $epayInfo['epay_url']//支付API地址
                ];
            require_once(Env::get('extend_path')."/epay/lib/epay_notify.class.php");
            $epaySubmit = new \AlipayNotify($epayConfig);
            $verifyResult = $epaySubmit->verifyReturn(); 
            if($verifyResult){
                if($data['trade_status'] == "TRADE_SUCCESS"){
                    $this->redirect('User/my_buylog');
                }else{
                    $this->error('回调验证失败!');
                }
            }
        }
    }
    
    //易支付异步通知地址
    public function epayNotify(Request $request)
    {
        if(request()->isGet()){
            //接收回调
            $data = $request->get();
            $epayInfo = Db::name('pay')->field('epay_url,epay_appid,epay_key')->find(1);
            //检测易支付API是否支持SSL
            $stream = stream_context_create (array("ssl" => array("capture_peer_cert" => true)));
            $read = fopen($epayInfo['epay_url'], "rb", false, $stream);
            $cont = stream_context_get_params($read);
            $result = (isset($cont["options"]["ssl"]["peer_certificate"])) ? true : false;
            if($result){
                $transport = "https";
            }else{
                $transport = "http";
            }
            //易支付的配置信息
            $epayConfig = [
                'partner'       =>  $epayInfo['epay_appid'],//商户ID
                'key'           =>  $epayInfo['epay_key'],//商户KEY
                'sign_type'     =>  strtoupper('MD5'),//签名方式 不需修改
                'input_charset' =>  strtolower('gbk'),//字符编码格式 目前支持 gbk 或 utf-8
                'transport'     =>  $transport,//访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
                'apiurl'        =>  $epayInfo['epay_url']//支付API地址
                ];
            require_once(Env::get('extend_path')."/epay/lib/epay_notify.class.php");
            $epaySubmit = new \AlipayNotify($epayConfig);
            $verifyResult = $epaySubmit->verifyNotify();
            if($verifyResult){
                //支付成功
                if($data['trade_status'] == "TRADE_SUCCESS"){
                    if($data['type'] == "alipay"){
                        $payType = 2;
                    }elseif($data['type'] == "wxpay"){
                        $payType = 1;
                    }elseif($data['type'] == "qqpay"){
                        $payType = 0;
                    }else{
                        $payType = -1;
                    }
                    //订单更新为已付款
                    Db::name("member_buylog")->where("order_id",$data["out_trade_no"])->update(["status"=>"1","create_time"=>time(),'pay_type'=>$payType]);
                     //查询订单信息
                    $buyinfo = Db::name("member_buylog")->where("order_id",$data["out_trade_no"])->find();
                    $tab = Db::name("model")->field("tablename")->select();
                    foreach($tab as $va){
                        $infs = Db::name($va["tablename"])->field("id,title,moneys")->find($buyinfo["product_id"]);
                        if($infs != null){
                            $info = $infs;
                        }
                    }
                    //查询已购买过的产品ID集
                    $buymat = Db::name('Member')->where('id',$buyinfo["uid"])->field('buymat')->find();
                    if(empty($buymat['buymat'])){
                        $buyMatid = $info['id'];
                        //更新
                        Db::name("member")->where("id",$buyinfo["uid"])->update(["buymat"=>$buyMatid]);
                    }else{
                        $buyArray = explode(',',$buymat['buymat']);
                        //防止多次回调重复存入ID值
                        if(!in_array($info['id'],$buyArray)){
                            $buyMatid = $buymat['buymat'].','.$info['id'];
                            //更新
                            Db::name("member")->where("id",$buyinfo["uid"])->update(["buymat"=>$buyMatid]);
                        }
                    }
                }
            }
        }
    }
    
    //QQ支付轮询地址
    public function qqpay()
    {
        $info = Session::get('MattersInfo');
        if(request()->isPost()){
            $qqPayInfo = Db::name("pay")->find(1);
            $qqArr = [
                "mch_id"     => $qqPayInfo['qqpay_mchid'],//商户号
                "key"        => $qqPayInfo['qqpay_key'],//商户key
            ];
            //实例化
            $qq      = new QQPay($qqArr);
            //查询订单
            $unified = $qq->orderQuery($info['trade_no']);
            //支付成功
            if ($unified['trade_state'] == "SUCCESS") {
                if($unified['cash_fee']/100 !== (float)$info['moneys']){
                    Session::delete('MattersInfo');
                    return ['msg'=>"支付金额异常,终止支付!",'code'=>2,'url'=>url('User/index')];
                }else{
                    //订单更新为已付款
                    Db::name("member_buylog")->where("order_id",$info["trade_no"])->update(["status"=>"1","create_time"=>time()]);
                    //查询订单信息
                    $buyinfo = Db::name("member_buylog")->where("order_id",$info["trade_no"])->find();
                    //查询已购买过的产品ID集
                    $buymat = Db::name('Member')->where('id',$buyinfo["uid"])->field('buymat')->find();
                    if(empty($buymat['buymat'])){
                        $buyMatid = $info['id'];
                    }else{
                        $buyMatid = $buymat['buymat'].','.$info['id'];
                    }
                    $res = Db::name("member")->where("id",$buyinfo["uid"])->update(["buymat"=>$buyMatid]);
                    if($res){
                        //删除session
                        Session::delete('MattersInfo');
                        //更新操作
                        $this->success('支付成功!','User/my_buylog');
                    }
                }
            }
        }
        return $this->fetch('/home_temp/'.$this->config["home_temp"].'/qqpay',['qqpay'=>$info]);
    }
    
    //支付宝支付通知地址
    public function aliPayNotify(Request $request)
    {   
       if(request()->isPost()){
            $setAlipay = Db::name("pay")->find(1);
            // 公共配置
            $params = new \Yurun\PaySDK\Alipay\Params\PublicParams();
            $params->appID = $setAlipay['alipay_pid'];
            $params->md5Key = $setAlipay['alipay_key'];
            // SDK实例化，传入公共配置
            $pay = new \Yurun\PaySDK\Alipay\SDK($params);
            $data = $request->post();
            if($pay->verifyCallback($data)){
                if($data["trade_status"] == "TRADE_SUCCESS"){
                    //查询订单信息
                    $buyinfo = Db::name("member_buylog")->where("order_id",$data["out_trade_no"])->find();
                    //查询文章价格作比对
                    $tab = Db::name("model")->field("tablename")->select();
                    foreach($tab as $va){
                        $infs = Db::name($va["tablename"])->field("id,title,moneys")->find($buyinfo["product_id"]);
                        if($infs != null){
                            $info = $infs;
                        }
                    }
                    if((float)$info['moneys'] !== (float)$data["price"]){
                        // 支付接口
                        $refundRequest = new \Yurun\PaySDK\Alipay\Params\Refund\Request();
                        $refundRequest->businessParams->batch_no = Session::get('MattersInfo.payinfo.businessParams.out_trade_no'); // 退款批次号
                        $refundRequest->businessParams->refund_date = date('Y-m-d H:i:s'); // 退款请求时间
                        $refundRequest->businessParams->batch_num = 1; // 总笔数
                        $refundRequest->businessParams->detail_data = "{$data['out_trade_no']}^{$data['price']}^{$data['subject']}"; // 单笔数据集
                        // 调用接口
                        $result = $pay->execute($refundRequest);
                        if ('T' == $result['is_success']){
                            return ['msg'=>"支付金额异常,终止支付!",'code'=>0];
                        }

                    }else{
                        //订单更新为已付款
                        Db::name("member_buylog")->where("order_id",$data["out_trade_no"])->update(["status"=>"1","create_time"=>time()]);
                        //查询已购买过的产品ID集
                        $buymat = Db::name('Member')->where('id',$buyinfo["uid"])->field('buymat,money')->find();
                        if(empty($buymat['buymat'])){
                            $buyMatid = $info['id'];
                            //更新
                            Db::name("member")->where("id",$buyinfo["uid"])->update(["buymat"=>$buyMatid]);
                        }else{
                            $buyArray = explode(',',$buymat['buymat']);
                            //防止多次回调重复存入ID值
                            if(!in_array($info['id'],$buyArray)){
                                $buyMatid = $buymat['buymat'].','.$info['id'];
                                //更新
                                Db::name("member")->where("id",$buyinfo["uid"])->update(["buymat"=>$buyMatid]);
                            }
                        }
                    }
                }
            }else{
                $this->error('回调验证失败!');
            }
         }

    }
    
    //支付宝支付回调地址
    public function aliPayReturn(Request $request)
    {
        if(request()->isGet()){
            $setAlipay = Db::name("pay")->find(1);
            $params = new \Yurun\PaySDK\Alipay\Params\PublicParams();
            $params->appID = $setAlipay['alipay_pid'];
            $params->md5Key = $setAlipay['alipay_key'];
            // SDK实例化，传入公共配置
            $pay = new \Yurun\PaySDK\Alipay\SDK($params);
            //接收返回参数
            $data = $request->get();
            if($pay->verifyCallback($data)){
                $this->redirect('User/my_buylog');
            }else{
                $this->error('回调验证失败!');
            }        
        }

    }
    
    //支付宝当面付轮询
    public function facepay(Request $request)
    {
        $info = Session::get('MattersInfo');
        if(request()->isPost()){
            $infos = Db::name("pay")->field("alipayf2f_private_id,alipayf2f_private_key,alipayf2f_public_key")->find(1);
            // 公共配置
            $params = new \Yurun\PaySDK\AlipayApp\Params\PublicParams();
            $params->sign_type = "RSA2";
            $params->charset = "utf-8";
            $params->version = "1.0";
            //APP ID
            $params->appID = $infos["alipayf2f_private_id"];
            //支付宝公钥
            $params->appPublicKey = $infos["alipayf2f_public_key"];
            //应用私钥
            $params->appPrivateKey = $infos["alipayf2f_private_key"];
            // SDK实例化，传入公共配置
            $pay = new \Yurun\PaySDK\AlipayApp\SDK($params);
            $requests = new \Yurun\PaySDK\AlipayApp\Params\Query\Request;
            // 订单支付时传入的商户订单号,和支付宝交易号不能同时为空。
            $requests->businessParams->out_trade_no = $info['out_trade_no']; 
            // 调用接口
            $result = $pay->execute($requests);
            if($pay->checkResult())
            {
                //支付成功
                if($result["alipay_trade_query_response"]["trade_status"] == "TRADE_SUCCESS"){
                    //支付金额异常,退款操作
                    if((float)$info['moneys'] !== (float)$result["alipay_trade_query_response"]["buyer_pay_amount"]){
                        $refundRequest = new \Yurun\PaySDK\AlipayApp\Params\Refund\Request;
                        $refundRequest->businessParams->out_trade_no = $info['out_trade_no']; 
                        $refundRequest->businessParams->refund_amount = $result["alipay_trade_query_response"]["buyer_pay_amount"]; 
                        $refundRequest->businessParams->refund_reason = '支付金额异常!';
                        // 调用接口
                        $result = $pay->execute($refundRequest);
                        if($pay->checkResult()){
                            //删除session
                            Session::delete('MattersInfo');
                            return ['msg'=>"支付金额异常,终止支付!",'code'=>2,'url'=>url('User/index')];
                        }
                    }else{
                        //订单更新为已付款
                        Db::name("member_buylog")->where("order_id",$result["alipay_trade_query_response"]["out_trade_no"])->update(["status"=>"1","create_time"=>time()]);
                        //查询订单信息
                        $buyinfo = Db::name("member_buylog")->where("order_id",$result["alipay_trade_query_response"]["out_trade_no"])->find();
                        //查询已购买过的产品ID集
                        $buymat = Db::name('Member')->where('id',$buyinfo["uid"])->field('buymat')->find();
                        if(empty($buymat['buymat'])){
                            $buyMatid = $info['id'];
                        }else{
                            $buyMatid = $buymat['buymat'].','.$info['id'];
                        }
                        //更新
                        $res = Db::name("member")->where("id",$buyinfo["uid"])->update(["buymat"=>$buyMatid]);
                        if($res){
                            //删除session
                            Session::delete('MattersInfo');
                            //更新操作
                            $this->success('支付成功!','User/my_buylog');
                        }
                    }
                }else{
                    $this->error('扫码成功!');
                }
            }else{
                $this->error('等待支付中...');
            }            
        }
        return $this->fetch('/home_temp/'.$this->config["home_temp"].'/facepay',["facepay"=>$info]);
    }
    
    
}
