<?php
return [
	// 是否自动读取取插件钩子配置信息（默认是关闭）
    'autoload' => true,
    // 当关闭自动获取配置时需要手动配置hooks信息
    'hooks' => [
        // 可以定义多个钩子
		// 'testhook' => 'test',
		// 'astehook' => 'aste',
                    // 多个插件可以用数组也可以用逗号分割
    ],
    'fileflag' => 'install.php'  //插件标识
];