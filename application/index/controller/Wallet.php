<?php
namespace app\index\controller;
use qqpay\QQPay;
use think\Db;
use think\facade\Env;
use think\facade\Session;
use think\Request;

class Wallet extends Base {
	protected $config;
	protected function initialize() {
		parent::initialize();
		$this->getuserst();
		//获取配置信息
		$this->config = $this->getsystems();
	}

	//我的钱包
	public function myMoney(Request $request) {
		//查询当前会员的信息
		$member = Db::name('member')->where('id', Session::get('Member.id'))->find();
		return $this->fetch('/member_temp/' . $this->config["member_temp"] . '/my_money', ['member' => $member]);
	}

	//钱包充值
	public function moneyPay(Request $request) {
		if (request()->isGet()) {
			//接收数据
			$data = $request->get();
			//获取客户端IP地址
			$data['ip'] = $request->ip();
			//查询所有支付控制开关
			$switch = Db::name('pay_set')->where('id', 1)->find();
			//查询所有支付配置信息
			$payInfo = Db::name('pay')->where('id', 1)->find();
			//查询当前支付控制开关
			$atSwitch = Db::name("pay_set")->field($data["paytype"] . "_close")->find(1);
			//订单号
			$tradeNo = trade_no();
			//当前支付方式为关闭状态,发起第四方支付
			if ($atSwitch[$data['paytype'] . '_close'] == 0) {
				if ($data['paytype'] !== "moneypay" && $switch['epay_close'] == 1) {
//易支付
					if (empty($data)) {
						$this->error("支付信息缺失,终止支付!");
					} else {
						//订单描述
						$parameter = [
							"pid" => (int) $payInfo['epay_appid'], //商户ID
							"type" => $data["paytype"], //支付方式
							"notify_url" => $request->domain() . url("Wallet/epayNotify"), //异步通知地址
							"return_url" => $request->domain() . url("Wallet/epayReturn"), //页面跳转同步通知页面路径
							"out_trade_no" => $tradeNo, //商户订单号
							"name" => "在线充值{$data['moneys']}", //商品名称
							"money" => $data['moneys'], //金额
							"sitename" => $this->config['title'], //站点名称
						];
						//检测易支付API是否支持SSL
						$stream = stream_context_create(array("ssl" => array("capture_peer_cert" => true)));
						$read = fopen($payInfo['epay_url'], "rb", false, $stream);
						$cont = stream_context_get_params($read);
						$result = (isset($cont["options"]["ssl"]["peer_certificate"])) ? true : false;
						if ($result) {
							$transport = "https";
						} else {
							$transport = "http";
						}
						//易支付的配置信息
						$epayConfig = [
							'partner' => $payInfo['epay_appid'], //商户ID
							'key' => $payInfo['epay_key'], //商户KEY
							'sign_type' => strtoupper('MD5'), //签名方式 不需修改
							'input_charset' => strtolower('gbk'), //字符编码格式 目前支持 gbk 或 utf-8
							'transport' => $transport, //访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
							'apiurl' => $payInfo['epay_url'], //支付API地址
						];
						//建立请求
						require_once Env::get('extend_path') . "/epay/lib/epay_submit.class.php";
						$epaySubmit = new \AlipaySubmit($epayConfig);
						$htmlText = $epaySubmit->buildRequestForm($parameter);
						//判断支付类型
						if ($data['paytype'] == "alipay") {
							$payType = 2;
						} elseif ($data['paytype'] == "wxpay") {
							$payType = 1;
						} elseif ($data['paytype'] == "qqpay") {
							$payType = 0;
						}
						//记录订单信息
						$payTradeNoInfo = [
							'order_id' => $tradeNo,
							'uid' => Session::get('Member.id'),
							'pay_type' => $payType,
							'money' => $data['moneys'],
							'pay_ip' => $data["ip"],
							'create_time' => time(),
							'status' => 0,
						];
						Db::name("member_paylog")->insert($payTradeNoInfo);
						echo $htmlText;
					}
				} else {
					$this->error("该支付方式已关闭!");
				}
			} elseif ($atSwitch[$data['paytype'] . '_close'] == 1) {
				if ($data['paytype'] == "alipay") {
					//支付宝当面付
					if ($switch['alipay'] == 0) {
						$params = new \Yurun\PaySDK\AlipayApp\Params\PublicParams();
						$params->sign_type = "RSA2";
						$params->charset = "utf-8";
						$params->version = "1.0";
						//APP ID
						$params->appID = $payInfo["alipayf2f_private_id"];
						//当面付私钥
						$params->appPrivateKey = $payInfo["alipayf2f_private_key"];
						//支付宝公钥
						$params->appPublicKey = $payInfo["alipayf2f_public_key"];
						//实例化，传入配置信息
						$pay = new \Yurun\PaySDK\AlipayApp\SDK($params);

						$requests = new \Yurun\PaySDK\AlipayApp\FTF\Params\QR\Request();
						// 商户订单号
						$requests->businessParams->out_trade_no = $tradeNo;
						// 充值金额
						$requests->businessParams->total_amount = $data['moneys'];
						// 产品标题
						$requests->businessParams->subject = "在线充值{$data['moneys']}元";
						//最晚付款时间,10分钟
						$requests->businessParams->timeout_express = "10m";
						try {
							//调用接口
							$result = $pay->execute($requests);
							if ($pay->checkResult()) {
								//订单创建时间
								$createTime = time();
								//记录订单信息
								$payTradeNoInfo = [
									'order_id' => $tradeNo,
									'uid' => Session::get('Member.id'),
									'pay_type' => 2,
									'money' => $data['moneys'],
									'pay_ip' => $data["ip"],
									'create_time' => $createTime,
									'status' => 0,
								];
								Db::name("member_paylog")->insert($payTradeNoInfo);
								//定义数据集，在支付页面使用
								$data['out_trade_no'] = $tradeNo;
								$data['title'] = "在线充值{$data['moneys']}元";
								$data['time'] = $createTime;
								$data['class'] = "Wallet";
								$data['qr_code'] = $result["alipay_trade_precreate_response"]['qr_code'];
								Session::set('payInfo', $data);
								$this->redirect('Wallet/facepay');
							}
						} catch (Exception $e) {
							$this->error($pay->response->body);
						}
					} elseif ($switch['alipay'] == 1) {
//支付宝支付
						// 公共配置
						$params = new \Yurun\PaySDK\Alipay\Params\PublicParams();
						$params->appID = $payInfo['alipay_pid'];
						$params->md5Key = $payInfo['alipay_key'];
						// SDK实例化，传入公共配置
						$pay = new \Yurun\PaySDK\Alipay\SDK($params);
						$requests = new \Yurun\PaySDK\Alipay\Params\Pay\Request();
						$requests->businessParams->seller_id = $payInfo['alipay_pid'];
						// 支付后通知地址（作为支付成功回调，这个可靠）
						$requests->notify_url = $request->domain() . url("Wallet/aliPayNotify");
						// 支付后跳转返回地址
						$requests->return_url = $request->domain() . url("Wallet/aliPayReturn");
						// 商户订单号
						$requests->businessParams->out_trade_no = $tradeNo;
						// 价格
						$requests->businessParams->total_fee = $data['moneys'];
						// 商品标题
						$requests->businessParams->subject = "在线充值{$data['moneys']}元";
						//最晚付款时间,10分钟
						$requests->businessParams->it_b_pay = "10m";
						//记录订单信息
						$payTradeNoInfo = [
							'order_id' => $tradeNo,
							'uid' => Session::get('Member.id'),
							'pay_type' => 2,
							'money' => $data['moneys'],
							'pay_ip' => $data["ip"],
							'create_time' => time(),
							'status' => 0,
						];
						Db::name("member_paylog")->insert($payTradeNoInfo);
						// 获取跳转url
						$pay->prepareExecute($requests, $url);
						$this->redirect($url);
					}
				} elseif ($data['paytype'] == "wxpay") {
//微信支付
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
					$requests->body = "在线充值{$data['moneys']}元";
					// 订单号
					$requests->out_trade_no = $tradeNo;
					// 订单总金额，单位为：分
					$requests->total_fee = $data['moneys'] * 100;
					// 客户端ip
					$requests->spbill_create_ip = $data["ip"];
					//异步通知地址
					$requests->notify_url = $request->domain() . url("Wallet/wxpay");
					//交易类型
					$requests->trade_type = "NATIVE";
					// 调用接口
					$result = $pay->execute($requests);
					//订单创建时间
					$createTime = time();
					//记录订单信息
					$payTradeNoInfo = [
						'order_id' => $tradeNo,
						'uid' => Session::get('Member.id'),
						'pay_type' => 1,
						'money' => $data['moneys'],
						'pay_ip' => $data["ip"],
						'create_time' => $createTime,
						'status' => 0,
					];
					Db::name("member_paylog")->insert($payTradeNoInfo);
					$info['time'] = $createTime;
					$info['out_trade_no'] = $tradeNo;
					$info['class'] = "Wallet";
					//二维码链接
					$info['code_url'] = $result['code_url'];
					Session::set('payInfo', $info);
					$this->redirect('Wallet/wxpay');
				} elseif ($data['paytype'] == "qqpay") {
//QQ支付
					$qqArr = [
						"mch_id" => $payInfo['qqpay_mchid'], //商户号
						"notify_url" => $request->domain() . url("Palymat/qqpay"), //异步通知回调地址
						"key" => $payInfo['qqpay_key'], //商户key
					];
					$param = [
						"out_trade_no" => $tradeNo, // 订单号
						"trade_type" => "NATIVE", // 固定值
						"total_fee" => $data['moneys'], // 单位为分
						"body" => "在线充值{$data['moneys']}元", //订单标题
					];
					//实例化
					$qq = new QQPay($qqArr);
					//下单操作
					$result = $qq->unifiedOrder($param);
					if ($result["return_code"] == "SUCCESS" && $result["result_code"] == "SUCCESS") {
						//订单创建时间
						$createTime = time();
						//记录订单信息
						$payTradeNoInfo = [
							'order_id' => $tradeNo,
							'uid' => Session::get('Member.id'),
							'pay_type' => 0,
							'money' => $data['moneys'],
							'pay_ip' => $data["ip"],
							'create_time' => $createTime,
							'status' => 0,
						];
						Db::name("member_paylog")->insert($payTradeNoInfo);
						$data['time'] = $createTime;
						$data['trade_no'] = $tradeNo;
						$data['class'] = "Wallet";
						$data['title'] = "在线充值{$data['moneys']}元";
						$data['code_url'] = $result['code_url'];
						Session::set('payInfo', $data);
						$this->redirect('Wallet/qqpay');
					}
				}

			}

		}
	}

	//支付宝当面付轮询
	public function facepay(Request $request) {
		$payData = Session::get('payInfo');
		if (request()->isPost()) {
			$payInfo = Db::name("pay")->where('id', 1)->field("alipayf2f_private_id,alipayf2f_private_key,alipayf2f_public_key")->find();
			// 公共配置
			$params = new \Yurun\PaySDK\AlipayApp\Params\PublicParams();
			$params->sign_type = "RSA2";
			$params->charset = "utf-8";
			$params->version = "1.0";
			//APP ID
			$params->appID = $payInfo["alipayf2f_private_id"];
			//支付宝公钥
			$params->appPublicKey = $payInfo["alipayf2f_public_key"];
			//应用私钥
			$params->appPrivateKey = $payInfo["alipayf2f_private_key"];
			// SDK实例化，传入公共配置
			$pay = new \Yurun\PaySDK\AlipayApp\SDK($params);
			$requests = new \Yurun\PaySDK\AlipayApp\Params\Query\Request;
			// 订单支付时传入的商户订单号,和支付宝交易号不能同时为空。
			$requests->businessParams->out_trade_no = $payData['out_trade_no'];
			// 调用接口
			$result = $pay->execute($requests);
			if ($pay->checkResult()) {
				//支付成功
				if ($result["alipay_trade_query_response"]["trade_status"] == "TRADE_SUCCESS") {
					//支付金额异常,退款操作
					if ((float) $payData['moneys'] !== (float) $result["alipay_trade_query_response"]["buyer_pay_amount"]) {
						$refundRequest = new \Yurun\PaySDK\AlipayApp\Params\Refund\Request;
						$refundRequest->businessParams->out_trade_no = $payData['out_trade_no'];
						$refundRequest->businessParams->refund_amount = $result["alipay_trade_query_response"]["buyer_pay_amount"];
						$refundRequest->businessParams->refund_reason = '支付金额异常!';
						// 调用接口
						$result = $pay->execute($refundRequest);
						if ($pay->checkResult()) {
							//删除session
							Session::delete('payInfo');
							return ['msg' => "支付金额异常,终止支付!", 'code' => 2, 'url' => url('User/index')];
						}
					} else {
						//订单更新为已付款
						Db::name("member_paylog")->where("order_id", $payData['out_trade_no'])->update(["status" => "1", "create_time" => time()]);
						//更新
						$res = Db::name("member")->where("id", Session::get('Member.id'))->setInc('money', $result["alipay_trade_query_response"]["buyer_pay_amount"]);
						if ($res) {
							//总充值字段增加
							Db::name("member")->where("id", Session::get('Member.id'))->setInc('consumption', $result["alipay_trade_query_response"]["buyer_pay_amount"]);
							//删除session
							Session::delete('payInfo');
							//更新操作
							$this->success('支付成功!', 'User/index');
						} else {
							//删除session
							Session::delete('payInfo');
							//更新操作
							$this->error('支付失败!');
						}
					}
				}
			}
		}
		return $this->fetch('/home_temp/' . $this->config["home_temp"] . '/facepay', ["facepay" => $payData]);
	}

	//支付宝异步通知地址
	public function aliPayNotify(Request $request) {
		if (request()->isPost()) {
			$payInfo = Db::name("pay")->where('id', 1)->find();
			// 公共配置
			$params = new \Yurun\PaySDK\Alipay\Params\PublicParams();
			$params->appID = $payInfo['alipay_pid'];
			$params->md5Key = $payInfo['alipay_key'];
			// SDK实例化，传入公共配置
			$pay = new \Yurun\PaySDK\Alipay\SDK($params);
			$data = $request->post();
			if ($pay->verifyCallback($data)) {
				if ($data["trade_status"] == "TRADE_SUCCESS") {
					//查询订单信息
					$payData = Db::name("member_paylog")->where("order_id", $data["out_trade_no"])->find();
					//支付金额异常，退款操作
					if ((float) $payData['money'] !== (float) $data["price"]) {
						// 支付接口
						$refundRequest = new \Yurun\PaySDK\Alipay\Params\Refund\Request();
						// 退款批次号
						$refundRequest->businessParams->batch_no = $data["out_trade_no"];
						// 退款请求时间
						$refundRequest->businessParams->refund_date = date('Y-m-d H:i:s');
						// 总笔数
						$refundRequest->businessParams->batch_num = 1;
						// 单笔数据集
						$refundRequest->businessParams->detail_data = "{$data['out_trade_no']}^{$data['price']}^{$data['subject']}";
						// 调用接口
						$result = $pay->execute($refundRequest);
						if ('T' == $result['is_success']) {
							return ['msg' => "支付金额异常,终止支付!", 'code' => 0];
						}
					} else {
						//订单更新为已付款
						Db::name("member_paylog")->where("order_id", $data["out_trade_no"])->update(["status" => "1", "create_time" => time()]);
						Db::name("member")->where("id", $payData['uid'])->setInc('money', $data['price']);
						Db::name("member")->where("id", $payData['uid'])->setInc('consumption', $data['price']);
						return ['msg' => "支付成功!", 'code' => 1];
					}
				}
			}
		}
	}

	//支付宝支付回调地址
	public function aliPayReturn(Request $request) {
		if (request()->isGet()) {
			$payInfo = Db::name("pay")->where('id', 1)->find();
			$params = new \Yurun\PaySDK\Alipay\Params\PublicParams();
			$params->appID = $payInfo['alipay_pid'];
			$params->md5Key = $payInfo['alipay_key'];
			// SDK实例化，传入公共配置
			$pay = new \Yurun\PaySDK\Alipay\SDK($params);
			//接收返回参数
			$data = $request->get();
			if ($pay->verifyCallback($data)) {
				$this->redirect('User/index');
			} else {
				$this->error('回调验证失败!');
			}
		}

	}

	//QQ支付轮询地址
	public function qqpay() {
		$payData = Session::get('payInfo');
		if (request()->isPost()) {
			$payInfo = Db::name("pay")->where('id', 1)->find();
			$qqArr = [
				"mch_id" => $payInfo['qqpay_mchid'], //商户号
				"key" => $payInfo['qqpay_key'], //商户key
			];
			//实例化
			$qq = new QQPay($qqArr);
			//查询订单
			$result = $qq->orderQuery($payData['trade_no']);
			//支付成功
			if ($result['trade_state'] == "SUCCESS") {
				if ($result['cash_fee'] / 100 !== (float) $payData['moneys']) {
					Session::delete('payInfo');
					return ['msg' => "支付金额异常,终止支付!", 'code' => 2, 'url' => url('User/index')];
				} else {
					//订单更新为已付款
					Db::name("member_paylog")->where("order_id", $payData["trade_no"])->update(["status" => "1", "create_time" => time()]);
					//查询订单信息
					$res = Db::name("member")->where("id", Session::get('Member.id'))->setInc('money', $result['cash_fee'] / 100);
					if ($res) {
						Db::name("member")->where("id", Session::get('Member.id'))->setInc('consumption', $result['cash_fee'] / 100);
						//删除session
						Session::delete('payInfo');
						//更新操作
						$this->success('支付成功!', 'User/index');
					} else {
						//删除session
						Session::delete('payInfo');
						//更新操作
						$this->error('支付失败!');
					}
				}
			}
		}
		return $this->fetch('/home_temp/' . $this->config["home_temp"] . '/qqpay', ['qqpay' => $payData]);
	}

	//微信支付轮询地址
	public function wxpay() {
		$payData = Session::get('payInfo');
		if (request()->isPost()) {
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
			$requests->transaction_id = $payData['out_trade_no'];
			$result = $pay->execute($requests);
			if ($pay->checkResult()) {
				//支付成功
				if ($result['trade_state '] == "SUCCESS") {
					if ($result['cash_fee'] / 100 !== (float) $payData['moneys']) {
						Session::delete('payInfo');
						return ['msg' => "支付金额异常,终止支付!", 'code' => 2, 'url' => url('User/index')];
					} else {
						//订单更新为已付款
						Db::name("member_buylog")->where("order_id", $payData["out_trade_no"])->update(["status" => "1", "create_time" => time()]);
						//更新余额
						$res = Db::name("member")->where("id", Session::get('Member.id'))->setInc('money', $result['cash_fee'] / 100);
						if ($res) {
							//更新总充值
							Db::name("member")->where("id", Session::get('Member.id'))->setInc('consumption', $result['cash_fee'] / 100);
							//删除session
							Session::delete('payInfo');
							//更新操作
							$this->success('支付成功!', 'User/my_buylog');
						} else {
							Session::delete('payInfo');
							$this->error('支付失败!');
						}
					}
				}
			}
		}
		return $this->fetch('/home_temp/' . $this->config["home_temp"] . '/wxpay', ['wxpay' => $payData]);
	}

	//易支付回调地址
	public function epayReturn(Request $request) {
		if (request()->isGet()) {
			//接收回调
			$data = $request->get();
			$epayInfo = Db::name('pay')->field('epay_url,epay_appid,epay_key')->find(1);
			// 判断易支付接口的协议
			$preg = '/^http(s)?:\\/\\/.+/';
			if (preg_match($preg, $epayInfo['epay_url'])) {
				$transport = 'https';
			} else {
				$transport = 'http';
			}
			//易支付的配置信息
			$epayConfig = [
				'partner' => $epayInfo['epay_appid'], //商户ID
				'key' => $epayInfo['epay_key'], //商户KEY
				'sign_type' => strtoupper('MD5'), //签名方式 不需修改
				'input_charset' => strtolower('gbk'), //字符编码格式 目前支持 gbk 或 utf-8
				'transport' => $transport, //访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
				'apiurl' => $epayInfo['epay_url'], //支付API地址
			];
			require_once Env::get('extend_path') . "/epay/lib/epay_notify.class.php";
			$epaySubmit = new \AlipayNotify($epayConfig);
			$verifyResult = $epaySubmit->verifyReturn();
			if ($verifyResult) {
				if ($data['trade_status'] == "TRADE_SUCCESS") {
					$this->redirect('User/index');
				} else {
					$this->error('回调验证失败!');
				}
			}
		}
	}

	//易支付异步通知地址
	public function epayNotify(Request $request) {
		if (request()->isGet()) {
			//接收回调
			$data = $request->get();
			$epayInfo = Db::name('pay')->field('epay_url,epay_appid,epay_key')->find(1);
			// 判断易支付接口的协议
			$preg = '/^http(s)?:\\/\\/.+/';
			if (preg_match($preg, $epayInfo['epay_url'])) {
				$transport = 'https';
			} else {
				$transport = 'http';
			}
			//易支付的配置信息
			$epayConfig = [
				'partner' => $epayInfo['epay_appid'], //商户ID
				'key' => $epayInfo['epay_key'], //商户KEY
				'sign_type' => strtoupper('MD5'), //签名方式 不需修改
				'input_charset' => strtolower('gbk'), //字符编码格式 目前支持 gbk 或 utf-8
				'transport' => $transport, //访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
				'apiurl' => $epayInfo['epay_url'], //支付API地址
			];
			require_once Env::get('extend_path') . "/epay/lib/epay_notify.class.php";
			$epaySubmit = new \AlipayNotify($epayConfig);
			$verifyResult = $epaySubmit->verifyNotify();
			if ($verifyResult) {
				//支付成功
				if ($data['trade_status'] == "TRADE_SUCCESS") {
					if ($data['type'] == "alipay") {
						$payType = 2;
					} elseif ($data['type'] == "wxpay") {
						$payType = 1;
					} elseif ($data['type'] == "qqpay") {
						$payType = 0;
					} else {
						$payType = -1;
					}
					//订单更新为已付款
					Db::name("member_paylog")->where("order_id", $data["out_trade_no"])->update(["status" => "1", "create_time" => time(), 'pay_type' => $payType]);
					//查询订单信息
					$payinfo = Db::name("member_paylog")->where("order_id", $data["out_trade_no"])->find();
					if ((float) $payinfo['money'] !== (float) $data['money']) {
						return ['msg' => "支付金额异常,终止支付!", 'code' => 0];
					} else {
						Db::name("member")->where("id", $payinfo['uid'])->setInc('money', $data['money']);
						Db::name("member")->where("id", Session::get('Member.id'))->setInc('consumption', $data['money']);
					}
				}
			}
		}
	}
}