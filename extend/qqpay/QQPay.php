<?php
 
namespace QQPay;
 
class QQPay
{
 
    const API_URL_PREFIX = 'https://qpay.qq.com';                   //接口API URL前缀
    const UNIFIEDORDER_URL = "/cgi-bin/pay/qpay_unified_order.cgi"; //下单地址URL
    const ORDERQUERY_URL = "/cgi-bin/pay/qpay_order_query.cgi";     //查询订单URL
    const CLOSEORDER_URL = "/cgi-bin/pay/qpay_close_order.cgi";     //关闭订单URL
 
    private $appid;                 // 应用号ID
    private $mch_id;                // 商户号
    private $nonce_str;             // 随机字符串
    private $sign;                  // 签名
    private $body;                  // 商品描述
    private $out_trade_no;          // 商户订单号
    private $fee_type;              //货币类型
    private $total_fee;             // 支付总金额
    private $spbill_create_ip;      // 终端IP
    private $notify_url;            // 支付结果回调通知地址
    private $trade_type;            // 交易类型 APP、JSAPI、NATIVE、MINIAPP
    private $key;                   // 支付密钥
    public $postCharset = "UTF-8";  // 表单提交字符集编码
    public $signType = "HMAC-SHA1"; // 签名类型
    private $SSLCERT_PATH;          // 证书路径
    private $SSLKEY_PATH;
    private $params = array();      // 所有参数
    private $logs;                  // 日志类
    private $fileCharset = "UTF-8";
    private $appkey;
 
    public function __construct($qqArr)
    {
        $this->mch_id       = $qqArr["mch_id"];
        if(!empty($qqArr["notify_url"])){
            $this->notify_url   = $qqArr["notify_url"];
        }
        if(!empty($qqArr["key"])){
            $this->key          = $qqArr["key"];
        }
        $this->spbill_create_ip = $_SERVER['REMOTE_ADDR'];
        $this->fee_type     = "CNY";
    }
 
    /**
     * 下单方法
     * @param  $params 下单参数
     * @return array
     */
    public function unifiedOrder($params)
    {
        $this->params['mch_id'] = $this->mch_id;
        $this->params['nonce_str'] = $this->nonce_str = $this->genRandomString();
        $this->params['body'] = $this->body = $params['body'];
        $this->params['out_trade_no'] = $this->out_trade_no = $params['out_trade_no'];
        $this->params['fee_type']   = $this->fee_type;
        $this->params['total_fee'] = $this->total_fee = $params['total_fee']*100;
        $this->params['spbill_create_ip'] = $this->spbill_create_ip;
        $this->params['notify_url'] = $this->notify_url;
        $this->params['trade_type'] = $this->trade_type = $params['trade_type'];
 
        //获取签名数据
        $this->sign = $this->MakeSign($this->params);
        $this->params['sign'] = $this->sign;
        $xml = $this->data_to_xml($this->params);
        $response = $this->postXmlCurl($xml, self::API_URL_PREFIX.self::UNIFIEDORDER_URL);
        $result = $this->xml_to_data($response);
 
        if(!empty($result['result_code']) && !empty($result['err_code']))
        {
            $result['err_msg'] = $this->error_code($result['err_code']);
        }
 
        return $result;
    }
 
    /**
     * 查询订单信息
     * @param $out_trade_no 订单号
     * @return array
     */
    public function orderQuery($out_trade_no)
    {
        $this->params['mch_id'] = $this->mch_id;
        $this->params['nonce_str'] = $this->genRandomString();
        $this->params['out_trade_no'] = $out_trade_no;
        //获取签名数据
        $this->sign = $this->MakeSign($this->params);
        $this->params['sign'] = $this->sign;
        $xml = $this->data_to_xml($this->params);
        $response = $this->postXmlCurl($xml, self::API_URL_PREFIX.self::ORDERQUERY_URL);
        
        $result = $this->xml_to_data($response);
        if(!empty($result['result_code']) && !empty($result['err_code']))
        {
            $result['err_msg'] = $this->error_code($result['err_code']);
        }
        return $result;
    }
 
    /**
     * 关闭订单
     * @param $out_trade_no 订单号
     * @return array
     */
    public function closeOrder($out_trade_no)
    {
        $this->params['mch_id'] = $this->mch_id;
        $this->params['nonce_str'] = $this->genRandomString();
        $this->params['out_trade_no'] = $out_trade_no;
        //获取签名数据
        $this->sign = $this->MakeSign($this->params);
        $this->params['sign'] = $this->sign;
        $xml = $this->data_to_xml($this->params);
        $response = $this->postXmlCurl($xml, self::API_URL_PREFIX.self::CLOSEORDER_URL);
        $result = $this->xml_to_data( $response );
        return $result;
    }
 
    /**
     * 生成用于调用收银台SDK的字符串
     * @param $request SDK接口的请求参数对象
     * @return string
     * @author guofa.tgf
     */
    public function sdkExecute($request) {
 
        $this->setupCharsets($request);
 
        $params['appid']       = $request['appid'];
        $params['bargainorId'] = $request['bargainorId'];
        $params['tokenId']     = $request['tokenId'];
        $params['nonce']       = $request['nonce'];
        $params['timestamp']   = time();
        $params['sign']        = $request['sign'];
        $params['sigType']     = $this->signType;
        $params['completion']  = $this->notify_url;
 
        foreach ($params as &$value)
        {
            $value = $this->character($value, $params['charset']);
        }
 
        return http_build_query($params);
    }
 
    /**
     * 校验$value是否非空
     * @param string $value
     * @return boolean
     **/
    protected function checkEmpty($value) {
        if (!isset($value))
            return true;
        if ($value === null)
            return true;
        if (trim($value) === "")
            return true;
 
        return false;
    }
 
    /**
     * 生成APP端支付参数
     * @param  string $prepayid   预支付id
     * @return array
     */
    public function getAppPayParams($prepayid){
 
        $ls_arr = [
            "appId" => $this->appid,
            "bargainorId" => $this->mch_id,
            "nonce" => $this->genRandomString(),
            "pubAcc" => "",
            "tokenId" => $prepayid
        ];
        $sign = $this->appMakeSign($ls_arr, $this->appkey);
        $ls_arr["sig"] = $sign;
        $ls_arr["timeStamp"] = time();
        $ls_arr["sigType"] = "HMAC-SHA1";
        return $ls_arr;
    }
 
    /**
     * 转换字符集编码
     * @param $data
     * @param $targetCharset
     * @return string
     */
    private function character($data, $targetCharset)
    {
 
        if (!empty($data))
        {
            $fileType = $this->fileCharset;
            if (strcasecmp($fileType, $targetCharset) != 0)
            {
                $data = mb_convert_encoding($data, $targetCharset, $fileType);
            }
        }
        return $data;
    }
 
    private function setupCharsets($request)
    {
        if ($this->checkEmpty($this->postCharset))
        {
            $this->postCharset = 'UTF-8';
        }
        $str = preg_match('/[\x80-\xff]/', $this->appId) ? $this->appId : print_r($request, true);
        $this->fileCharset = mb_detect_encoding($str, "UTF-8, GBK") == 'UTF-8' ? 'UTF-8' : 'GBK';
    }
 
 
    /**
     *
     * 获取支付结果通知数据
     * return array
     */
    public function getNotifyData()
    {
        //获取通知的数据
        $xml = $GLOBALS['HTTP_RAW_POST_DATA'];
        $data = array();
        if(empty($xml))
        {
            return false;
        }
        $data = $this->xml_to_data($xml);
        if( !empty($data['return_code']) )
        {
            if($data['return_code'] == 'FAIL')
            {
                return false;
            }
        }
        return $data;
    }
 
    /**
     * 接收通知成功后应答输出XML数据
     */
    public function replyNotify(){
        $data['return_code'] = 'SUCCESS';
        $data['return_msg'] = 'OK';
        $xml = $this->data_to_xml($data);
        echo $xml;
    }
 
    /**
     * 生成签名
     * @param array $params
     * @return 签名
     */
    public function MakeSign($params){
        //签名步骤一：按字典序排序数组参数
        ksort($params);
        $string = $this->ToUrlParams($params);
        //签名步骤二：在string后加入KEY
        $string = $string . "&key=".$this->key;
        //签名步骤三：MD5加密
        $string = md5($string);
        //签名步骤四：所有字符转为大写
        $result = strtoupper($string);
 
        return $result;
    }
 
    /**
     * 将参数拼接为url: key=value&key=value
     * @param   $params
     * @return  string
     */
    public function ToUrlParams($params)
    {
        $string = '';
        if( !empty($params) )
        {
            $array = array();
            foreach( $params as $key => $value )
            {
                $array[] = $key.'='.$value;
            }
            $string = implode("&",$array);
        }
        return $string;
    }
 
    /**
     * app生成签名
     * @param array $params
     * @param string $appkey
     * @return 签名
     */
    public function appMakeSign($params, $appkey)
    {
        //签名步骤一：按字典序排序数组参数
        ksort($params);
        $string = $this->ToUrlParams($params);
        //签名步骤二：在string后加入KEY
        $appkey = $appkey.'&';
        $result  = base64_encode(hash_hmac("sha1", $string, $appkey, true));
 
        return $result;
    }
 
    /*
     * 输出xml字符
     * @param  array $params  参数名称
     * return xml
     */
    public function data_to_xml($params)
    {
        if(!is_array($params)|| count($params) <= 0)
        {
            return false;
        }
        $xml = "<xml>";
        foreach ($params as $key=>$val)
        {
            if (is_numeric($val)){
                $xml.="<".$key.">".$val."</".$key.">";
            }else{
                $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
            }
        }
        $xml.="</xml>";
        return $xml;
    }
 
    /*
     * 将xml转为array
     * @param string $xml
     * return array
     */
    public function xml_to_data($xml){
        if(!$xml){
            return false;
        }
        //将XML转为array
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $data;
    }
 
    /*
     * 验证签名
     * @param string $xml
     * return array
     */
    public function validationSignature($params){
        //签名步骤一：按字典序排序数组参数
        ksort($params);
        $string = $this->ToUrlParams($params);
        return $string;
        //签名步骤二：在string后加入KEY
        $string = $string . "&key=".$this->key;
        //签名步骤三：MD5加密
        $string = md5($string);
        //签名步骤四：所有字符转为大写
        $result = strtoupper($string);
 
        return $result;
    }
 
    /**
     * 获取毫秒级别的时间戳
     */
    private static function getMillisecond()
    {
        //获取毫秒的时间戳
        $time = explode ( " ", microtime () );
        $time = $time[1] . ($time[0] * 1000);
        $time2 = explode( ".", $time );
        $time = $time2[0];
        return $time;
    }
 
    /**
     * 产生一个指定长度的随机字符串,并返回给用户
     * @param int $len 产生字符串的长度
     * @return string 随机字符串
     */
    private function genRandomString($len = 32)
    {
        $chars = array(
            "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k",
            "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v",
            "w", "x", "y", "z", "A", "B", "C", "D", "E", "F", "G",
            "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R",
            "S", "T", "U", "V", "W", "X", "Y", "Z", "0", "1", "2",
            "3", "4", "5", "6", "7", "8", "9"
        );
        $charsLen = count($chars) - 1;
        // 将数组打乱
        shuffle($chars);
        $output = "";
        for ($i = 0; $i < $len; $i++) {
            $output .= $chars[mt_rand(0, $charsLen)];
        }
        return $output;
    }
 
    /*
     * 以post方式提交xml到对应的接口url
     *
     * @param string $xml  需要post的xml数据
     * @param string $url  url
     * @param bool $useCert 是否需要证书，默认不需要
     * @param int $second   url执行超时时间，默认30s
     * @throws WxPayException
     */
    private function postXmlCurl($xml, $url, $useCert = false, $second = 30){
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,2);
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        if($useCert == true){
            //设置证书
            //使用证书：cert 与 key 分别属于两个.pem文件
            curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
            //curl_setopt($ch,CURLOPT_SSLCERT, WxPayConfig::SSLCERT_PATH);
            curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
            //curl_setopt($ch,CURLOPT_SSLKEY, WxPayConfig::SSLKEY_PATH);
        }
        //post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        //运行curl
        $data = curl_exec($ch);
 
        //返回结果
        if($data){
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            curl_close($ch);
            return false;
        }
    }
 
    /*
     * 错误代码
     * @param  $code       服务器输出的错误代码
     * return string
     */
    public function error_code($code){
        $errList = array(
            'LACK_PARAMS'           =>  '缺少必要的请求参数',
            'NOAUTH'                =>  '商户未开通此接口权限',
            'NOTENOUGH'             =>  '用户帐号余额不足',
            'ORDERNOTEXIST'         =>  '订单号不存在',
            'ORDERPAID'             =>  '商户订单已支付，无需重复操作',
            'ORDERCLOSED'           =>  '当前订单已关闭，无法支付',
            'SYSTEMERROR'           =>  '系统错误!系统超时',
            'APPID_NOT_EXIST'       =>  '参数中缺少APPID',
            'MCHID_NOT_EXIST'       =>  '参数中缺少MCHID',
            'APPID_MCHID_NOT_MATCH' =>  'appid和mch_id不匹配',
            'LACK_PARAMS'           =>  '缺少必要的请求参数',
            'OUT_TRADE_NO_USED'     =>  '同一笔交易不能多次提交',
            'SIGNERROR'             =>  '参数签名结果不正确',
            'XML_FORMAT_ERROR'      =>  'XML格式错误',
            'REQUIRE_POST_METHOD'   =>  '未使用post传递参数 ',
            'POST_DATA_EMPTY'       =>  'post数据不能为空',
            'NOT_UTF8'              =>  '未使用指定编码格式',
        );
        if(array_key_exists($code,$errList)){
            return $errList[$code];
        }
    }
 
 
}