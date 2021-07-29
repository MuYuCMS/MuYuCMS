<?php
// [ 应用入口文件 ]
namespace think;

// 检测程序安装
if(!is_file(__DIR__ . '/mdata/install.lock')){
	header('Location: ./install/index.php');
	exit;
}

// 定义应用目录
define('APP_PATH', __DIR__ . '/application/');
// 加载基础文件
require __DIR__ . '/thinkphp/base.php';

// 支持事先使用静态方法设置Request对象和Config对象

// 执行应用并响应
Container::get('app')->bind('index')->run()->send();
