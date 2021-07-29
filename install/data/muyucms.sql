CREATE TABLE `#__admin` (
  `id` int(11) NOT NULL COMMENT '管理员id',
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL COMMENT '管理员名称',
  `password` varchar(100) COLLATE utf8_unicode_ci NOT NULL COMMENT '管理员密码',
  `photo` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '管理员头像',
  `intro` varchar(250) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '管理员简介',
  `email` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '管理员邮箱',
  `phone` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '管理员手机',
  `outip` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '上次登录ip',
  `outtime` int(11) DEFAULT NULL COMMENT '上次登录时间',
  `roles` int(11) DEFAULT NULL COMMENT '管理员所属角色',
  `status` enum('0','1') COLLATE utf8_unicode_ci NOT NULL DEFAULT '1' COMMENT '管理员启用状态 0禁用 1启用',
  `count` int(11) NOT NULL DEFAULT '0' COMMENT '管理员登录次数',
  `degree` int(11) NOT NULL DEFAULT '0' COMMENT '管理员登录错误次数记录',
  `last_cwtime` int(11) DEFAULT NULL COMMENT '管理员最后一次错误登录时间',
  `create_time` int(11) NOT NULL COMMENT '管理员创建时间',
  `update_time` int(11) NOT NULL COMMENT '管理员更新时间',
  `cwtime` int(11) DEFAULT NULL COMMENT '错误时间',
  `login_time` int(11) DEFAULT NULL COMMENT '登录时间记录',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间记录'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='管理员表';

INSERT INTO `#__admin` (`id`, `name`, `password`, `photo`, `intro`, `email`, `phone`, `outip`, `outtime`, `roles`, `status`, `count`, `degree`, `last_cwtime`, `create_time`, `update_time`, `cwtime`, `login_time`, `delete_time`) VALUES
(1, 'admin', 'e10adc3949ba59abbe56e057f20f883e', NULL, NULL, NULL, NULL, NULL, NULL, 1, '1', 0, 0, NULL, 1611128805, 1611128805, NULL, NULL, NULL);

CREATE TABLE `#__admin_data` (
  `uid` int(11) NOT NULL COMMENT '关联管理员id',
  `comment` int(11) NOT NULL DEFAULT '0' COMMENT '管理员评论总数',
  `le_word` int(11) NOT NULL DEFAULT '0' COMMENT '管理员留言总数',
  `attention` int(11) NOT NULL DEFAULT '0' COMMENT '管理员关注总数',
  `fans` int(11) NOT NULL DEFAULT '0' COMMENT '管理员粉丝总数',
  `contribute` int(11) NOT NULL DEFAULT '0' COMMENT '管理员累计投稿总数',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `#__admin_data` (`uid`, `comment`, `le_word`, `attention`, `fans`, `contribute`, `delete_time`) VALUES
(1, 0, 0, 0, 0, 0, NULL);

CREATE TABLE `#__advertising` (
  `id` int(11) NOT NULL COMMENT '广告id',
  `title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '广告名称',
  `url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '广告跳转地址',
  `adtext` text COLLATE utf8_unicode_ci COMMENT '广告文本内容',
  `adphoto` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '广告图片',
  `status` enum('0','1') COLLATE utf8_unicode_ci NOT NULL DEFAULT '1' COMMENT '广告状态 0隐藏 1显示',
  `outtime` int(11) DEFAULT NULL COMMENT '广告过期时间',
  `create_time` int(11) DEFAULT NULL COMMENT '广告创建时间',
  `adset` enum('0','1') COLLATE utf8_unicode_ci NOT NULL DEFAULT '0' COMMENT '广告自动过期隐藏 0否 1是'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='广告表';

INSERT INTO `#__advertising` (`id`, `title`, `url`, `adtext`, `adphoto`, `status`, `outtime`, `create_time`, `adset`) VALUES
(1, '木鱼CMS', 'http://www.muyucms.com', '', NULL, '1', -28800, NULL, '1');

CREATE TABLE `#__article` (
  `id` int(11) NOT NULL COMMENT '文章id',
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT '文章标题',
  `ftitle` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '文章副标题',
  `titlepic` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '文章标题图片',
  `mid` int(11) DEFAULT NULL COMMENT '文章所属栏目',
  `uid` int(11) NOT NULL COMMENT '关联会员id',
  `type` int(11) DEFAULT NULL COMMENT '文章所属分类',
  `keyword` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '文章关键词',
  `abstract` text COLLATE utf8_unicode_ci COMMENT '文章摘要',
  `tag` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '文章标签',
  `author` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '文章作者',
  `source` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '文章来源',
  `editor` text COLLATE utf8_unicode_ci NOT NULL COMMENT '文章内容',
  `price` enum('免费','付费','vip免费','vip折扣') COLLATE utf8_unicode_ci NOT NULL DEFAULT '免费' COMMENT '文章阅读类型 1免费 2付费 3vip免费 4vip折扣',
  `status` enum('0','1','2','3','4') COLLATE utf8_unicode_ci NOT NULL DEFAULT '0' COMMENT '文章发布状态 0发布 1草稿 2下架 3待审核 4驳回',
  `orders` int(11) NOT NULL COMMENT '文章排序',
  `create_time` int(11) NOT NULL COMMENT '文章创建时间',
  `update_time` int(11) NOT NULL COMMENT '文章更新时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '文章删除时间',
  `comment` tinyint(2) NOT NULL DEFAULT '0' COMMENT '文章是否允许评论 0允许 1不允许',
  `refusal` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '文章驳回原因',
  `top` enum('0','1') COLLATE utf8_unicode_ci NOT NULL DEFAULT '0' COMMENT '文章置顶 0不置顶 1置顶',
  `ppts` enum('0','1') COLLATE utf8_unicode_ci NOT NULL DEFAULT '0' COMMENT '文章标题图片幻灯 0不幻灯 1幻灯',
  `isadmin` enum('0','1') COLLATE utf8_unicode_ci NOT NULL DEFAULT '0' COMMENT '是否管理员发布 0否 1是',
  `downputh` mediumtext COLLATE utf8_unicode_ci COMMENT '附件下载地址',
  `likes_ip` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '当前文章点过站的ip'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='文章表';

CREATE TABLE `#__article_data` (
  `aid` int(11) NOT NULL COMMENT '关联文章id',
  `browse` int(11) NOT NULL DEFAULT '0' COMMENT '文章浏览次数',
  `likes` int(11) NOT NULL DEFAULT '0' COMMENT '文章点赞次数',
  `comment_t` int(200) NOT NULL DEFAULT '0' COMMENT '文章评论总计',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='文章附属表';

CREATE TABLE `#__comment` (
  `id` int(11) NOT NULL COMMENT '评论id',
  `pid` int(11) NOT NULL DEFAULT '0' COMMENT '评论子id',
  `title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '评论标题',
  `aid` int(11) NOT NULL COMMENT '评论所属文章',
  `uid` int(11) DEFAULT NULL COMMENT '评论关联会员',
  `plname` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT '评论人昵称',
  `plpic` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '/public/upload/userimages/touxiang.png' COMMENT '写入头像',
  `content` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT '评论内容',
  `create_time` int(11) NOT NULL COMMENT '评论时间',
  `status` enum('0','1','2') COLLATE utf8_unicode_ci NOT NULL DEFAULT '0' COMMENT '评论状态 0待审核 1审核通过 2已下架'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='评论存储表';

CREATE TABLE `#__comment_data` (
  `cid` int(11) NOT NULL COMMENT '关联评论id',
  `likes` int(11) NOT NULL DEFAULT '0' COMMENT '评论点赞量',
  `reply` int(11) NOT NULL DEFAULT '0' COMMENT '评论回复量',
  `comment_ip` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '统计点赞当前评论ip'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `#__email` (
  `id` int(11) NOT NULL COMMENT '邮箱配置识别',
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT '邮箱发信账号 ',
  `sll` int(11) NOT NULL DEFAULT '25' COMMENT '发信sll',
  `emailpaswsd` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT '发信秘钥',
  `smtp` varchar(50) COLLATE utf8_unicode_ci NOT NULL COMMENT '发信smtp',
  `ceemail` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '测试邮箱'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='邮箱发信配置表';

INSERT INTO `#__email` (`id`, `email`, `sll`, `emailpaswsd`, `smtp`, `ceemail`) VALUES
(1, 'qq@qq.com', 465, '*********', 'smtp.qq.com', NULL);

CREATE TABLE `#__fans` (
  `id` int(11) NOT NULL COMMENT '主键id',
  `uid` int(11) DEFAULT NULL COMMENT '被关注用户id',
  `not_uid` int(11) DEFAULT NULL COMMENT '被取关用户id',
  `fansid` int(11) NOT NULL COMMENT '关注/取关用户的id',
  `event` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT '事件记录',
  `create_time` int(11) DEFAULT NULL COMMENT '关注/取关时间',
  `update_time` int(11) DEFAULT NULL COMMENT '取关时间',
  `log_ip` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '客户端IP'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='关注事件记录表';

CREATE TABLE `#__feedback` (
  `id` int(11) NOT NULL COMMENT '留言id',
  `content` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT '留言内容',
  `uid` int(11) DEFAULT '0' COMMENT '关联会员id 0为匿名 默认',
  `status` enum('0','1') COLLATE utf8_unicode_ci NOT NULL DEFAULT '0' COMMENT '留言状态 0未读 1已读',
  `create_time` int(11) NOT NULL COMMENT '留言时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='留言存储表';

CREATE TABLE `#__hmenu` (
  `id` int(11) NOT NULL COMMENT 'id',
  `pid` int(11) NOT NULL COMMENT '父id',
  `title` varchar(255) NOT NULL COMMENT '栏目名称',
  `titlepic` varchar(255) DEFAULT NULL COMMENT '栏目背景图',
  `icon` varchar(255) DEFAULT NULL COMMENT '栏目ICO',
  `href` varchar(255) NOT NULL COMMENT '栏目跳转地址',
  `sort` int(11) NOT NULL COMMENT '排序',
  `status` enum('0','1') NOT NULL DEFAULT '1' COMMENT '状态1显示，0隐藏',
  `comment` varchar(255) DEFAULT NULL COMMENT '描述',
  `type` enum('0','1','99') DEFAULT '0' COMMENT '0为前台栏目，1其他页面，99为首页',
  `ar_cont` int(11) NOT NULL DEFAULT '0' COMMENT '栏目文章总数',
  `links` enum('0','1') NOT NULL DEFAULT '0' COMMENT '前台导航是否外部链接',
  `listtemp` varchar(200) NOT NULL DEFAULT 'article_list' COMMENT '栏目列表模板',
  `matterlist` varchar(200) NOT NULL DEFAULT 'article_content' COMMENT '栏目内容模板'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='前台栏目';

CREATE TABLE `#__links` (
  `id` int(11) NOT NULL COMMENT '友链id',
  `title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '友链名称',
  `url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '友链地址',
  `ico` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '友链图标/logo',
  `intro` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '友链描述',
  `status` enum('0','1') COLLATE utf8_unicode_ci NOT NULL DEFAULT '1' COMMENT '友链状态',
  `email` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '友链联系方式',
  `orders` int(11) NOT NULL DEFAULT '1' COMMENT '友链排序',
  `create_time` int(11) NOT NULL COMMENT '友链创建时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='友链表';

INSERT INTO `#__links` (`id`, `title`, `url`, `ico`, `intro`, `status`, `email`, `orders`, `create_time`) VALUES
(1, 'MuYuCMS', 'http://www.muyucms.com', NULL, NULL, '1', NULL, 1, 1605770019);

CREATE TABLE `#__log` (
  `id` int(11) NOT NULL COMMENT '日志id',
  `user` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT '日志事件人',
  `content` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT '日志内容',
  `log_ip` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT '日志发生ip',
  `log_time` int(11) NOT NULL COMMENT '日志发生时间',
  `user_id` int(11) NOT NULL COMMENT '日志关联管理id'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='日志表';

CREATE TABLE `#__member` (
  `id` int(11) NOT NULL COMMENT '用户id',
  `name` varchar(250) COLLATE utf8_unicode_ci NOT NULL COMMENT '用户名称',
  `sex` enum('0','1','2') COLLATE utf8_unicode_ci NOT NULL DEFAULT '2' COMMENT '用户性别 0女 1男 2保密',
  `account` varchar(250) COLLATE utf8_unicode_ci NOT NULL COMMENT '用户账号',
  `password` varchar(250) COLLATE utf8_unicode_ci NOT NULL COMMENT '用户密码',
  `email` varchar(250) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '用户邮箱',
  `phone` bigint(20) DEFAULT NULL COMMENT '用户手机',
  `photo` varchar(250) COLLATE utf8_unicode_ci DEFAULT '/public/upload/userimages/touxiang.png' COMMENT '用户头像',
  `update_time` int(11) NOT NULL COMMENT '更新时间',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `status` enum('0','1') COLLATE utf8_unicode_ci NOT NULL DEFAULT '1' COMMENT '用户状态 0 禁用 1启用',
  `intro` varchar(250) COLLATE utf8_unicode_ci NOT NULL COMMENT '用户简介',
  `outip` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '上次登录ip',
  `super` enum('0','1') COLLATE utf8_unicode_ci NOT NULL DEFAULT '0' COMMENT '超级会员(0为否，1为是)',
  `outtime` int(11) DEFAULT NULL COMMENT '上次登录时间',
  `degree` int(11) NOT NULL DEFAULT '0' COMMENT '登录错误次数记录',
  `last_cwtime` int(11) DEFAULT NULL COMMENT '最后一次登录错误的时间',
  `count` int(11) NOT NULL DEFAULT '0' COMMENT '登录次数记录',
  `cwtime` int(11) DEFAULT NULL COMMENT '登录错误时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间记录',
  `jurisdiction` enum('0','1') COLLATE utf8_unicode_ci NOT NULL DEFAULT '0' COMMENT '用户投稿权限 0需要审核 1直接发布',
  `fans` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '我的粉丝',
  `attention` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '我的关注',
  `artlikes` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '点赞过的文章',
  `commlieks` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '统计当前会员点赞的评论ID',
  `home_count` int(11) NOT NULL DEFAULT '0' COMMENT '个人主页访问量'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='用户表';

CREATE TABLE `#__member_data` (
  `uid` int(11) NOT NULL COMMENT '关联用户id',
  `comment` int(11) NOT NULL DEFAULT '0' COMMENT '用户评论总数统计',
  `le_word` int(11) NOT NULL DEFAULT '0' COMMENT '用户留言总数统计',
  `attention` int(11) NOT NULL DEFAULT '0' COMMENT '用户关注总数',
  `fans` int(11) NOT NULL DEFAULT '0' COMMENT '用户粉丝总数',
  `contribute` int(11) NOT NULL DEFAULT '0' COMMENT '用户累计投稿次数',
  `delete_time` int(11) DEFAULT NULL COMMENT '会员附属表记录删除时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='用户多余数据存储';

CREATE TABLE `#__menu` (
  `id` int(11) NOT NULL COMMENT 'id',
  `pid` int(11) NOT NULL COMMENT '父id',
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT '导航名称',
  `titlepic` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '导航图标',
  `controller` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT '菜单所属操作控制器',
  `href` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '导航地址',
  `orders` int(11) NOT NULL COMMENT '排序',
  `status` enum('0','1') COLLATE utf8_unicode_ci NOT NULL DEFAULT '1' COMMENT '栏目是否可用',
  `comment` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '描述'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='后台左侧栏目';

INSERT INTO `muyu_menu` (`id`, `pid`, `title`, `titlepic`, `controller`, `href`, `orders`, `status`, `comment`) VALUES
(1, 0, '资讯管理', 'news', 'Article', '', 1, '1', '资讯管理'),
(2, 1, '资讯列表', NULL, 'Article', 'Article/index', 2, '1', '资讯列表'),
(3, 1, '管理删除资讯', NULL, 'Article', 'Article/articledel', 4, '1', '管理删除资讯'),
(4, 0, '评论管理', 'feedback2', 'Comment', NULL, 7, '1', '评论管理'),
(5, 4, '评论列表', NULL, 'Comment', 'Comment/index', 8, '1', '评论列表'),
(6, 4, '意见反馈', NULL, 'Feedback', 'Feedback/index', 9, '1', '意见反馈'),
(7, 0, '会员管理', 'user2', 'Member', NULL, 10, '1', '会员管理'),
(8, 7, '会员列表', NULL, 'Member', 'Member/index', 11, '1', '会员列表'),
(9, 7, '删除的会员', NULL, 'Member', 'Member/memberdel', 12, '1', '删除的会员'),
(10, 0, '管理员管理', 'user-zhanzhang', 'Admin', NULL, 98, '1', '管理员管理'),
(11, 10, '角色管理', NULL, 'Roles', 'Roles/rolelist', 14, '1', '角色管理'),
(12, 10, '权限管理', NULL, 'Roles', 'Roles/permission', 15, '0', '权限管理'),
(13, 10, '管理员列表', NULL, 'Admin', 'Admin/index', 16, '1', '管理员列表'),
(14, 10, '已删除列表', NULL, 'Admin', 'Admin/admindel', 17, '1', '已删除列表'),
(15, 0, '系统管理', 'system', 'System', NULL, 99, '1', '系统管理'),
(16, 15, '系统设置', NULL, 'System', 'System/index', 19, '1', '系统设置'),
(17, 15, '屏蔽词', NULL, 'System', 'System/shielding', 20, '1', '屏蔽词'),
(18, 0, '栏目管理', 'menu', 'HomeMenus', NULL, 4, '1', '栏目管理'),
(19, 18, '栏目列表', NULL, 'HomeMenus', 'HomeMenus/menulist', 5, '1', '栏目列表'),
(20, 18, '分类管理', NULL, 'Types', 'Types/typelist', 6, '1', '分类管理'),
(21, 15, '邮箱配置', NULL, 'Emails', 'Emails/index', 21, '1', '邮箱配置'),
(22, 0, '插件管理', 'add2', 'Plug', NULL, 22, '1', '插件管理'),
(23, 22, '广告管理', NULL, 'Plug', 'Plug/addindex', 23, '1', '广告管理'),
(24, 22, '友链管理', NULL, 'Plug', 'Plug/linkindex', 24, '1', '友链管理'),
(25, 0, '附件管理', 'list', 'Accessory', NULL, 25, '1', '附件管理'),
(26, 25, '图片管理', '', 'Base', 'Accessory/piclist', 26, '1', '图片管理'),
(27, 25, '文件管理', NULL, 'Base', 'Accessory/filelist', 27, '1', '文件管理'),
(28, 15, '系统日志', NULL, 'Log', 'Log/index', 28, '1', '系统日志'),
(29, 15, '模板管理', NULL, 'System', 'System/templist', 29, '1', '模板管理'),
(30, 1, '资讯审核', NULL, 'Article', 'Article/audit', 3, '1', '资讯审核'),
(31, 15, '系统更新', NULL, 'System', 'System/upgrade', 26, '1', '系统更新'),
(32, 15, '安全配置', NULL, 'System', 'System/safety', 27, '1', '安全配置');

CREATE TABLE `#__role` (
  `id` int(11) NOT NULL COMMENT 'id',
  `name` varchar(250) COLLATE utf8_unicode_ci NOT NULL COMMENT '角色名称',
  `jurisdiction` text COLLATE utf8_unicode_ci COMMENT '角色权限内容',
  `orders` int(11) NOT NULL DEFAULT '1' COMMENT '角色排序',
  `info` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '权限说明',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) NOT NULL COMMENT '修改时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='角色存储表';

INSERT INTO `#__role` (`id`, `name`, `jurisdiction`, `orders`, `info`, `create_time`, `update_time`) VALUES
(1, '超级管理员', '1,2,3,30,18,19,20,4,5,6,7,8,9,22,23,24,25,26,27,10,11,13,14,15,16,17,21,28,29,31,32', 1, '具备后台所有权限', 1611128805, 1611128805);

CREATE TABLE `#__system` (
  `id` int(11) NOT NULL COMMENT '主键',
  `title` varchar(255) NOT NULL COMMENT '网站标题',
  `ftitle` varchar(100) NOT NULL COMMENT '网站副标题',
  `keyword` varchar(255) NOT NULL COMMENT '网站关键字',
  `descri` varchar(255) NOT NULL COMMENT '网站描述',
  `is_close` tinyint(2) NOT NULL DEFAULT '0' COMMENT '网站是否关闭1:关闭0:开启',
  `tg_close` tinyint(2) NOT NULL DEFAULT '0' COMMENT '在线投稿开关（0为开启，1为关闭）',
  `comment_close` tinyint(2) NOT NULL DEFAULT '0' COMMENT '评论是否关闭1:关闭0:开启',
  `commentaudit_close` tinyint(4) NOT NULL DEFAULT '0' COMMENT '评论审核开关（0为自动，1为手动）',
  `feedback_close` tinyint(2) NOT NULL DEFAULT '0' COMMENT '留言是否关闭1:关闭0:开启',
  `userreg_close` tinyint(2) NOT NULL DEFAULT '0' COMMENT '用户注册开关,1为关闭，0为开启',
  `user_error` int(11) NOT NULL DEFAULT '5' COMMENT '所有会员最大允许登录失败错误次数',
  `is_update` tinyint(2) NOT NULL COMMENT '更新标志位',
  `degree` int(11) NOT NULL DEFAULT '5' COMMENT '后台最大登录错误次数限制',
  `shielding` text COMMENT '屏蔽词',
  `record` varchar(100) DEFAULT NULL COMMENT '备案号',
  `copyright` varchar(50) DEFAULT NULL COMMENT '前台底部授权',
  `statistics` text COMMENT '统计代码',
  `dlip` varchar(255) DEFAULT NULL COMMENT '后台允许访问ip',
  `logo` char(120) DEFAULT NULL COMMENT '网站LOGO',
  `ico` char(120) DEFAULT NULL COMMENT '网站ICO',
  `adminqq` int(20) DEFAULT NULL COMMENT '站长QQ',
  `adminemail` varchar(50) DEFAULT NULL COMMENT '站长邮箱',
  `temp` varchar(100) DEFAULT NULL COMMENT '当前使用的模板',
  `version` float NOT NULL DEFAULT '1' COMMENT '系统版本号'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='网站配置表';

INSERT INTO `#__system` (`id`, `title`, `ftitle`, `keyword`, `descri`, `is_close`, `tg_close`, `comment_close`, `commentaudit_close`, `feedback_close`, `userreg_close`, `user_error`, `is_update`, `degree`, `shielding`, `record`, `copyright`, `statistics`, `dlip`, `logo`, `ico`, `adminqq`, `adminemail`, `temp`, `version`) VALUES
(1, 'MuYuCMS', '轻量级开源CMS!', 'MuYuCMS,cms,开源cms,免费开源cms,轻量级cms,cms系统,cms下载,企业cms,内容管理系统,cms建站系统', 'MuyuCMS基于Thinkphp开发的一套轻量级开源内容管理系统,专注为公司企业、个人站长提供快速建站提供解决方案。', 0, 0, 0, 0, 0, 0, 5, 1, 5, '很好|垃圾|色情|AV|看片', '京ICP备00000000号', 'Copyright 2020 MuYuCMS内容管理系统 All Rights Reserved', '<script>\r\nvar _hmt = _hmt || [];\r\n(function() {\r\n  var hm = document.createElement(\"script\");\r\n  hm.src = \"https://hm.baidu.com/hm.js?ea62eb580986c26501fbbb418bbce03f\";\r\n  var s = document.getElementsByTagName(\"script\")[0]; \r\n  s.parentNode.insertBefore(hm, s);\r\n})();\r\n</script>', '', '/public/upload/imgages/logo.jpg', '/public/upload/imgages/favicon.ico', 0, 'admin@muyucms.com', 'muyu_duoguyu', 1);

CREATE TABLE `#__type` (
  `id` int(11) NOT NULL COMMENT '分类id',
  `mid` int(11) DEFAULT NULL COMMENT '分类所属栏目id',
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT '分类名称',
  `titlepic` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '分类背景或ico图片',
  `orders` int(11) DEFAULT '0' COMMENT '分类排序',
  `status` enum('0','1') COLLATE utf8_unicode_ci NOT NULL DEFAULT '1' COMMENT '分类状态 0停用 1启用',
  `create_time` int(11) DEFAULT NULL COMMENT '分类创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '分类更新时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='分类存储表';

ALTER TABLE `#__admin`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `#__admin_data`
  ADD PRIMARY KEY (`uid`);

ALTER TABLE `#__advertising`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `#__article`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `#__article_data`
  ADD PRIMARY KEY (`aid`);

ALTER TABLE `#__comment`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `#__comment_data`
  ADD PRIMARY KEY (`cid`);

ALTER TABLE `#__email`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `#__fans`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `#__feedback`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `#__hmenu`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `#__links`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `#__log`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `#__member`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `#__member_data`
  ADD PRIMARY KEY (`uid`);

ALTER TABLE `#__menu`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `#__role`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `#__system`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `#__type`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `#__admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '管理员id', AUTO_INCREMENT=2;

ALTER TABLE `#__advertising`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '广告id', AUTO_INCREMENT=2;

ALTER TABLE `#__article`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '文章id';

ALTER TABLE `#__comment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '评论id';

ALTER TABLE `#__email`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '邮箱配置识别', AUTO_INCREMENT=2;

ALTER TABLE `#__fans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键id';

ALTER TABLE `#__feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '留言id';

ALTER TABLE `#__hmenu`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id';

ALTER TABLE `#__links`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '友链id', AUTO_INCREMENT=2;

ALTER TABLE `#__log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '日志id';

ALTER TABLE `#__member`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '用户id';

ALTER TABLE `#__menu`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id', AUTO_INCREMENT=33;

ALTER TABLE `#__role`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id', AUTO_INCREMENT=2;

ALTER TABLE `#__system`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键', AUTO_INCREMENT=2;

ALTER TABLE `#__type`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '分类id';
COMMIT;
