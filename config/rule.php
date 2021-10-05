<?php
return [
    //定义需要登录但是不需要检测权限的路由,注意：这里路由地址请一律小写
    'login' => [
        'admin/index/index',//后台首页
        'admin/index/welcome',//后台我的桌面
        'admin/index/bigdata',//数据中心
        'admin/index/clear',//清除缓存
        'admin/base/get_allfiles',//获取指定文件夹所有文件
        'admin/base/getmyfiles',//获取文件夹所有文件
        'admin/base/gettempfiles',//获取所有模板文件
        'admin/roles/getdata',//权限控制器的请求数据源
        'admin/index/loginout',//退出登录
        'admin/roles/search',//角色搜索
        'admin/admin/search',//管理员搜索
        'admin/base/imgupload',//头像上传
        'admin/base/fileuplod',//文件上传
        'admin/Matter/editorup',//内容管理编辑器图片上传
        'admin/base/allsearch',
        'admin/member/buysearch',
        'admin/member/paysearch'
        ],
    //定义不需要检测登录和不需要检测权限的路由,注意：这里路由地址请一律小写    
    'exclude'=> [
        'admin/login/login'
        ],
    'userer' => [
        'index/user/tg',
        'index/user/tgmd',
        'index/user/tg_new',
        'index/index/index',
        'index/index/login',
        'index/index/reg',
        'index/index/sendemails',
        'index/index/password',
        'index/index/passemail',
        'index/matters/matlist',
        'index/matters/matcont',
        'idnex/matters/discuss',
        'index/matters/sublikes',
        'idnex/matters/comlikes',
        'idnex/matters/artcoun',
        'idnex/search/index',
        'index/requestinfo/matter',
        'index/palymat/alipaynotify',//产品购买支付宝支付异步通知地址
        'index/palymat/epaynotify',//产品购买易支付异步通知地址
        'index/wallet/alipaynotify',//会员中心充值支付宝支付异步通知地址
        'index/wallet/epaynotify',//会员中心充值易支付异步通知地址
        ],   
];