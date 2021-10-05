
            <?php
			$http_host = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';
            return [
                //腾讯QQ登录配置
                'qq' => [
                    'app_key' => '', //应用注册成功后分配的 APP ID
                    'app_secret' => '',  //应用注册成功后分配的KEY
                    'callback' => $http_host.$_SERVER['HTTP_HOST'].'/oauth/callback/type/qq', // 应用回调地址
                ],
                //微信扫码登录配置
                'weixin' => [
                    'app_key' => '', //应用注册成功后分配的 APP ID
                    'app_secret' => '',  //应用注册成功后分配的KEY
                    'callback' => $http_host.$_SERVER['HTTP_HOST'].'/oauth/callback/type/weixin', // 应用回调地址
                ],
                //新浪登录配置
                'sina' => [
                    'app_key' => '', //应用注册成功后分配的 APP ID
                    'app_secret' => '',  //应用注册成功后分配的KEY
                    'callback' => $http_host.$_SERVER['HTTP_HOST'].'/oauth/callback/type/sina', // 应用回调地址
                ],
                //Baidu登录配置
                'baidu' => [
                    'app_key' => '', //应用注册成功后分配的 APP ID
                    'app_secret' => '',  //应用注册成功后分配的KEY
                    'callback' => $http_host.$_SERVER['HTTP_HOST'].'/oauth/callback/type/baidu', // 应用回调地址
                ],
                //Gitee登录配置
                'gitee' => [
                    'app_key' => '', //应用注册成功后分配的 APP ID
                    'app_secret' => '',  //应用注册成功后分配的KEY
                    'callback' => $http_host.$_SERVER['HTTP_HOST'].'/oauth/callback/type/gitee', // 应用回调地址
                ],
                //Github登录配置
                'github' => [
                    'app_key' => '', //应用注册成功后分配的 APP ID
                    'app_secret' => '',  //应用注册成功后分配的KEY
                    'callback' => $http_host.$_SERVER['HTTP_HOST'].'/oauth/callback/type/github', // 应用回调地址
                ],
                //Douyin登录配置
                'douyin' => [
                    'app_key' => '', //应用注册成功后分配的 APP ID
                    'app_secret' => '',  //应用注册成功后分配的KEY
                    'callback' => $http_host.$_SERVER['HTTP_HOST'].'/oauth/callback/type/douyin', // 应用回调地址
                ],
                //Dingtalk登录配置
                'dingtalk' => [
                    'app_key' => '', //应用注册成功后分配的 APP ID
                    'app_secret' => '',  //应用注册成功后分配的KEY
                    'callback' => $http_host.$_SERVER['HTTP_HOST'].'/oauth/callback/type/dingtalk', // 应用回调地址
                ]
            ];
            