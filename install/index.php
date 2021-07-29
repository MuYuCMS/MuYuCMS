<?php
set_time_limit(0);   //设置运行时间
error_reporting(E_ALL & ~E_NOTICE);  //显示全部错误
define('ROOT_PATH', dirname(dirname(__FILE__)));  //定义根目录
define('DBCHARSET','UTF8');   //设置数据库默认编码
//开启session
session_start();

if(function_exists('date_default_timezone_set')){
	date_default_timezone_set('Asia/Shanghai');
}
input($_GET);
input($_POST);
function input(&$data){
	foreach ((array)$data as $key => $value) {
		if(is_string($value)){
			if(!get_magic_quotes_gpc()){
				$value = htmlentities($value, ENT_NOQUOTES);
                $value = addslashes(trim($value));
			}
		}else{
			$data[$key] = input($value);
		}
	}
}
//判断是否安装过程序
if(is_file('../mdata/install.lock') && $_GET['step'] != 4){
	@header("Content-type: text/html; charset=UTF-8");
    echo "系统已经安装过了，如果要重新安装，那么请先删除站点mdata目录下的 install.lock 文件";
    exit;
}

require('./include/function.php');
if(!in_array($_GET['step'], array(1,2,3,4))){
	$_GET['step'] = 0;
}
switch ($_GET['step']) {
	case 1:
		require('./include/var.php');
		$error = 0;
		env_check($env_items);
		foreach($env_items as $v){
			if($v["status"] == 0){
				$error++;
			}
		}
        dirfile_check($dirfile_items);
		foreach($dirfile_items as $v){
			if($v["status"] == 0){
				$error++;
			}
		}
        function_check($func_items);
		foreach($func_items as $v){
			if($v["status"] == 0){
				$error++;
			}
		}
		$_SESSION['INSSTEPER'] = $error == 0?'SUCCE':$error;
		break;
	case 2:
	verifystep(2);
		$install_error = '';
        $install_recover = '';
        $demo_data =  file_exists('./data/muyucms_add.sql') ? true : false;
        step2($install_error,$install_recover);
        break;
	case 3:
	verifystep(3);	
		break;
	case 4:
	verifystep(4);
		$sitepath = strtolower(substr($_SERVER['PHP_SELF'], 0, strrpos($_SERVER['PHP_SELF'], '/')));
        $sitepath = str_replace('install',"",$sitepath);
        $auto_site_url = strtolower('http://'.$_SERVER['HTTP_HOST'].$sitepath);
		unset($_SESSION['INSTALLSQLOK']);
		break;
	default:
		# code...
		break;
}

include ("install_{$_GET['step']}.php");

function step2(&$install_error,&$install_recover){
    global $html_title,$html_header,$html_footer;
    if ($_POST['submitform'] != 'submit') return;
    $db_host = $_POST['db_host'];
    $db_port = $_POST['db_port'];
    $db_user = $_POST['db_user'];
    $db_pwd = $_POST['db_pwd'];
    $db_name = $_POST['db_name'];
    $db_prefix = $_POST['db_prefix'];
    $admin = $_POST['admin'];
    $password = $_POST['password'];
    if (!$db_host || !$db_port || !$db_user || !$db_pwd || !$db_name || !$db_prefix || !$admin || !$password){
        $install_error = '输入不完整，请检查';
    }
    if(strpos($db_prefix, '.') !== false) {
        $install_error .= '数据表前缀为空，或者格式错误，请检查';
    }

    if(strlen($admin) > 15 || preg_match("/^$|^c:\\con\\con$|　|[,\"\s\t\<\>&]|^游客|^Guest/is", $admin)) {
        $install_error .= '非法用户名，用户名长度不应当超过 15 个英文字符，且不能包含特殊字符，一般是中文，字母或者数字';
    }
    if ($install_error != '') reutrn;
        $mysqli = @ new mysqli($db_host, $db_user, $db_pwd, '', $db_port);
        if($mysqli->connect_error) {
            $install_error = '数据库连接失败';return;
        }

    if($mysqli->get_server_info()> '5.1') {
        $mysqli->query("CREATE DATABASE IF NOT EXISTS `$db_name` DEFAULT CHARACTER SET ".DBCHARSET);
    } else {
        $install_error = '数据库必须为MySQL5.1版本以上';return;
    }
    if($mysqli->error) {
        $install_error = $mysqli->error;return ;
    }
    if($_POST['install_recover'] != 'yes' && ($query = $mysqli->query("SHOW TABLES FROM $db_name"))) {
        while($row = mysqli_fetch_array($query)) {
            if(preg_match("/^$db_prefix/", $row[0])) {
                $install_error = '数据表已存在，继续安装将会覆盖已有数据';
                $install_recover = 'yes';
                return;
            }
        }
    }

    require ('install_3.php');
    $sitepath = strtolower(substr($_SERVER['PHP_SELF'], 0, strrpos($_SERVER['PHP_SELF'], '/')));
    $sitepath = str_replace('install',"",$sitepath);
    $auto_site_url = strtolower('http://'.$_SERVER['HTTP_HOST'].$sitepath);
    write_config($auto_site_url);
    
    $_charset = strtolower(DBCHARSET);
    $mysqli->select_db($db_name);
    $mysqli->set_charset($_charset);
	if(!is_file("data/muyucms.sql")){
		showjsmessage('数据文件丢失...... ');
		exit();
	}
    $sql = file_get_contents("data/muyucms.sql");
    //判断是否安装测试数据
    if ($_POST['demo_data'] == '1'){
        $sql .= file_get_contents("data/muyucms_add.sql");
    }
    $sql = str_replace("\r\n", "\n", $sql);
    runquery($sql,$db_prefix,$mysqli);
    showjsmessage('初始化数据 ... 成功 ');

    /**
     * 转码
     */
    $sitename = empty($_POST['site_name']) ? "站点创建成功!--MuYuCMS" : $_POST['site_name'];
    $username = $_POST['admin'];
    $password = password_hash($_POST['password'],PASSWORD_DEFAULT);
    $mysqli->query("UPDATE {$db_prefix}system SET id = '1' , title = '{$sitename}' WHERE id = 1");
	
    //管理员账号密码
	if($mysqli->query("UPDATE {$db_prefix}admin SET id = '1' , name = '{$username}' ,password = '{$password}' ,outtime = '".time()."' ,roles = '1' ,create_time = '".time()."' , update_time = '".time()."' WHERE id = 1")){
		showjsmessage('管理员信息写入成功...... ');
	}else{
		showjsmessage('管理员信息写入失败...... ');
		showjsmessage('使用默认管理登录 - U:admin  P:123456 ...... ');
	}
	$hostname = $_SERVER['HTTP_HOST'];
	$getipname = "";
	$accres = $_POST['accre'];
	if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
	        $ip = getenv('HTTP_CLIENT_IP');
	    } elseif(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
	        $ip = getenv('HTTP_X_FORWARDED_FOR');
	    } elseif(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
	        $ip = getenv('REMOTE_ADDR');
	    } elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
	        $ip = $_SERVER['REMOTE_ADDR'];
	    }
	    $getipname =  preg_match ( '/[\d\.]{7,15}/', $ip, $matches ) ? $matches [0] : '';
	 if($getipname !== null && $getipname !== "127.0.0.1" && $getipname !== "127.0.1.0" && $hostname !== "localhost"){
		if(!empty($getipname) && !empty($hostname)){
		$postUrl = "http://api.muyucms.com/installsetinfo/checkinstall";
		$postdata['ip'] = $getipname;
		$postdata['host'] = $hostname;
		$postdata['accre'] = $accres;
		$curlPost = $postdata;
		$postdatas = http_build_query($curlPost);//处理请求数据,生成一个经过 URL-encode 的请求字符串
		$options = array(
		    'http' => array(
		      'method' => 'POST',//请求方式POST
		      'header' => 'Content-type:application/x-www-form-urlencoded',
		      'content' => $postdatas,
		      'timeout' => 5 // 超时时间（单位:s）
		    )
		);
		$context = stream_context_create($options);//创建并返回文本数据流
		$result = file_get_contents($postUrl, false, $context);//将文本数据流读入字符串
		$da = json_decode($result,true);
		if($da['accre'] == 1){
			if($mysqli->query("UPDATE {$db_prefix}accre SET accre_id = 'muyu' , accre = '{$accres}' , accre_sta= '{$da['accre_sta']}' , accre_time= '{$da['accre_time']}' , accre_name='{$da['accre_name']}' WHERE accre_id = muyu")){
				showjsmessage('授权码正确且成功授权...... ');
			}else{
				showjsmessage('授权码正确，但是授权失败...... ');
				showjsmessage('请稍后后台重新授权此站点...... ');
			}
		}else if($da['accre'] == 0){
				showjsmessage($da['msg'].',不予授权...... ');
		}else{
			showjsmessage('授权码验证失败...... ');
		}
		}
	 }else{
		 showjsmessage('本地测试环境或配置失败 不进行授权处理...... ');
	 }
	$_SESSION['INSTALLSQLOK'] = "1"; 
    //新增一个标识文件，用来屏蔽重新安装
    $fp = @fopen('../mdata/install.lock','wb+');
    @fclose($fp);
	unset($_SESSION['INSSTEPER']);
    exit("<script type=\"text/javascript\">document.getElementById('submit').href='index.php?step=4';</script>");
    exit();
}
function verifystep($step = 2){
	if($step >= 2 && $step < 4){
		if(!isset($_SESSION['INSSTEPER'])){
			header('location:./index.php');
			exit();
		}
		if($_SESSION['INSSTEPER'] !== "SUCCE"){
			header('location:./index.php?step=1');
			exit();
		}
	}
	if($step == 3){
		//未提交数据
		if(empty($_POST)){
			header('location:./index.php?step=2');
			exit();
		}
	}
	if($step >= 4){
		//数据库未写入完成
		if(!isset($_SESSION['INSTALLSQLOK'])){
			header('location:./index.php?step=2');
			exit();
		}
	}
}
//execute sql
function runquery($sql, $db_prefix, $mysqli) {
//  global $lang, $tablepre, $db;
    if(!isset($sql) || empty($sql)) return;
    $sql = str_replace("\r", "\n", str_replace('#__', $db_prefix, $sql));
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
                showjsmessage('数据表  '.$data_name.' ... 创建成功');
                $mysqli->query(droptable($data_name));
                $mysqli->query($query);
                unset($line,$data_name);
            } else {
                $mysqli->query($query);
            }
        }
    }
}
//抛出JS信息
function showjsmessage($message) {
    echo '<script type="text/javascript">showmessage(\''.addslashes($message).' \');</script>'."\r\n";
    flush();
    ob_flush();
}
//写入config文件
function write_config($url) {
    extract($GLOBALS, EXTR_SKIP);
    $config = 'data/config.php';
	if(!is_file($config)){
		showjsmessage('配置文件模板丢失...... ');
		exit();
	}
    $configfile = @file_get_contents($config);
    $configfile = trim($configfile);
    $configfile = substr($configfile, -2) == '?>' ? substr($configfile, 0, -2) : $configfile;
    $charset = 'UTF-8';
    $db_host = $_POST['db_host'];
    $db_port = $_POST['db_port'];
    $db_user = $_POST['db_user'];
    $db_pwd = $_POST['db_pwd'];
    $db_name = $_POST['db_name'];
    $db_prefix = $_POST['db_prefix'];
    $admin = $_POST['admin'];
    $password = $_POST['password'];
    $db_type = 'mysql';
    $cookie_pre = strtoupper(substr(md5(random(6).substr($_SERVER['HTTP_USER_AGENT'].md5($_SERVER['SERVER_ADDR'].$db_host.$db_user.$db_pwd.$db_name.substr(time(), 0, 6)), 8, 6).random(5)),0,4)).'_';
    $configfile = str_replace("!==url==!",          $url, $configfile);
    $configfile = str_replace("!==db_prefix==!",    $db_prefix, $configfile);
    $configfile = str_replace("!==db_charset==!",   $charset, $configfile);
    $configfile = str_replace("!==db_host==!",      $db_host, $configfile);
    $configfile = str_replace("!==db_user==!",      $db_user, $configfile);
    $configfile = str_replace("!==db_pwd==!",       $db_pwd, $configfile);
    $configfile = str_replace("!==db_name==!",      $db_name, $configfile);
    $configfile = str_replace("!==db_port==!",      $db_port, $configfile);
    @file_put_contents('../config/database.php', $configfile);
}