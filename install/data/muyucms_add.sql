CREATE TABLE IF NOT EXISTS `#__accre` (
  `accre_id` char(50) NOT NULL DEFAULT 'muyu' COMMENT '授权标识 不可更改',
  `accre` varchar(100) DEFAULT NULL COMMENT '授权码',
  `accre_name` char(50) NOT NULL DEFAULT '免费版' COMMENT '授权类型',
  `accre_sta` tinyint(4) NOT NULL DEFAULT '1' COMMENT '授权状态',
  `accre_time` int(11) DEFAULT NULL COMMENT '授权时限',
  PRIMARY KEY (`accre_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='授权码记录表';

INSERT INTO `#__accre` (`accre_id`, `accre`, `accre_name`, `accre_sta`, `accre_time`) VALUES
('muyu', NULL, '免费版', 1, NULL);

CREATE TABLE `#__admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '管理员id',
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL COMMENT '管理员名称',
  `password` varchar(100) COLLATE utf8_unicode_ci NOT NULL COMMENT '管理员密码',
  `photo` text COLLATE utf8_unicode_ci COMMENT '管理员头像',
  `intro` varchar(250) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '管理员简介',
  `email` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '管理员邮箱',
  `phone` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '管理员手机',
  `outip` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '上次登录ip',
  `outtime` int(11) DEFAULT NULL COMMENT '上次登录时间',
  `roles` int(11) DEFAULT NULL COMMENT '管理员所属角色',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '管理员启用状态 0禁用 1启用',
  `count` int(11) NOT NULL DEFAULT '0' COMMENT '管理员登录次数',
  `degree` int(11) NOT NULL DEFAULT '0' COMMENT '管理员登录错误次数记录',
  `last_cwtime` int(11) DEFAULT NULL COMMENT '管理员最后一次错误登录时间',
  `create_time` int(11) NOT NULL COMMENT '管理员创建时间',
  `update_time` int(11) NOT NULL COMMENT '管理员更新时间',
  `cwtime` int(11) DEFAULT NULL COMMENT '错误时间',
  `login_time` int(11) DEFAULT NULL COMMENT '登录时间记录',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间记录',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='管理员表';

INSERT INTO `#__admin` VALUES (1,'admin','$2y$10$rhRdpOYn26hTFIRhLUGRrem0U.dwo/0HtsJjdc2vBJFE7wYcFRp3O','/favicon.ico','MuYuCMS','muyucms@qq.com','18888888888',NULL,1626012677,1,1,1,0,NULL,1626012677,1626013649,NULL,1626012837,NULL);

CREATE TABLE `#__admin_data` (
  `uid` int(11) NOT NULL COMMENT '关联管理员id',
  `comment` int(11) NOT NULL DEFAULT '0' COMMENT '管理员评论总数',
  `le_word` int(11) NOT NULL DEFAULT '0' COMMENT '管理员留言总数',
  `attention` int(11) NOT NULL DEFAULT '0' COMMENT '管理员关注总数',
  `fans` int(11) NOT NULL DEFAULT '0' COMMENT '管理员粉丝总数',
  `contribute` int(11) NOT NULL DEFAULT '0' COMMENT '管理员累计投稿总数',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `#__admin_data` VALUES (1,0,0,0,0,1,NULL);

CREATE TABLE `#__advertising` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '广告id',
  `title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '广告名称',
  `url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '广告跳转地址',
  `adtext` text COLLATE utf8_unicode_ci COMMENT '广告文本内容',
  `adphoto` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '广告图片',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '广告状态 0隐藏 1显示',
  `outtime` int(11) DEFAULT NULL COMMENT '广告过期时间',
  `outext` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'AD到期显示内容',
  `create_time` int(11) DEFAULT NULL COMMENT '广告创建时间',
  `adset` tinyint(4) NOT NULL DEFAULT '0' COMMENT '广告自动过期隐藏 0否 1是',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='广告表';

INSERT INTO `#__advertising` VALUES (1,'木鱼CMS','http://www.muyucms.com','木鱼CMS内容管理系统','/public/upload/ggpic/609b5c04117dd.gif',1,NULL,NULL,1621516394,0);

CREATE TABLE `#__article` (
  `id` int(11) NOT NULL COMMENT 'id',
  `title` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '标题',
  `ftitle` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '副标题',
  `titlepic` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '标题图片',
  `mid` int(11) DEFAULT NULL COMMENT '所属栏目',
  `uid` int(11) NOT NULL COMMENT '关联会员id',
  `type` int(11) DEFAULT NULL COMMENT '所属分类',
  `keyword` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '关键词',
  `abstract` text CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT '摘要',
  `tag` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '标签',
  `author` varchar(10) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '发布者',
  `editor` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '内容',
  `price` tinyint(2) NOT NULL DEFAULT '0' COMMENT '阅读类型 1免费 2付费 3vip免费 4vip折扣',
  `moneys` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '付费金额',
  `status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '发布状态 0发布 1草稿 2下架 3待审核 4驳回',
  `orders` int(11) NOT NULL DEFAULT '1' COMMENT '排序',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) NOT NULL COMMENT '更新时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  `comment` tinyint(2) NOT NULL DEFAULT '0' COMMENT '是否允许评论 0允许 1不允许',
  `refusal` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '驳回原因',
  `top` tinyint(2) NOT NULL DEFAULT '0' COMMENT '置顶 0不置顶 1置顶',
  `ppts` tinyint(2) NOT NULL DEFAULT '0' COMMENT '标题图片幻灯 0不幻灯 1幻灯',
  `isadmin` tinyint(2) NOT NULL DEFAULT '0' COMMENT '是否管理员发布 0否 1是',
  `likes_ip` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '当前文章点过赞的ip',
  `befrom` char(60) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `#__article_data` (
  `aid` int(11) NOT NULL COMMENT '关联文章id',
  `browse` int(11) NOT NULL DEFAULT '0' COMMENT '文章浏览次数',
  `likes` int(11) NOT NULL DEFAULT '0' COMMENT '文章点赞次数',
  `comment_t` int(200) NOT NULL DEFAULT '0' COMMENT '文章评论总计',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`aid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `#__bigdata` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `article_browse` int(11) NOT NULL DEFAULT '0' COMMENT '文章浏览量',
  `article_likes` int(11) NOT NULL DEFAULT '0' COMMENT '文章点赞量',
  `article_comment` int(11) NOT NULL DEFAULT '0' COMMENT '文章评论数',
  `article_add` int(11) NOT NULL DEFAULT '0' COMMENT '文章发布数',
  `member_add` int(11) NOT NULL DEFAULT '0' COMMENT '会员注册数',
  `feedback_add` int(11) NOT NULL DEFAULT '0' COMMENT '留言数',
  `buy_money` int(11) NOT NULL DEFAULT '0' COMMENT '消费金额',
  `pay_money` int(11) NOT NULL DEFAULT '0' COMMENT '充值金额',
  `downloads` int(11) NOT NULL DEFAULT '0' COMMENT '下载量',
  `ip` int(11) NOT NULL DEFAULT '0' COMMENT '日ip数量',
  `pv` int(11) NOT NULL DEFAULT '0' COMMENT '访问量',
  `uv` int(11) NOT NULL DEFAULT '0' COMMENT '独立访客',
  `create_time` int(10) NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='数据统计表';

INSERT INTO `#__bigdata` VALUES (1,0,0,0,1,0,0,0,0,0,4,42,0,1626012687);

CREATE TABLE `#__category` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `pid` int(11) NOT NULL COMMENT '父id',
  `modid` int(11) NOT NULL COMMENT '所属模型',
  `title` varchar(255) NOT NULL COMMENT '栏目名称',
  `titlepic` text COMMENT '栏目背景图',
  `icon` varchar(255) DEFAULT NULL COMMENT '栏目ICO',
  `href` varchar(255) NOT NULL COMMENT '栏目跳转地址',
  `sort` int(11) NOT NULL COMMENT '排序',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态1显示，0隐藏',
  `comment` varchar(255) DEFAULT NULL COMMENT '描述',
  `type` tinyint(4) DEFAULT '0' COMMENT '0为前台栏目，1其他页面，99为首页',
  `conttemp` varchar(100) NOT NULL COMMENT '栏目内容模板',
  `listtemp` varchar(100) NOT NULL COMMENT '栏目列表模板',
  `ar_cont` int(11) NOT NULL DEFAULT '0' COMMENT '栏目文章总数',
  `links` tinyint(4) NOT NULL DEFAULT '0' COMMENT '前台导航是否外部链接',
  `issue` tinyint(4) NOT NULL DEFAULT '1' COMMENT '栏目是否允许投稿',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='前台栏目';

INSERT INTO `#__category` VALUES (1,0,1,'MuYuCMS','','','index/Matters/matlist',1,1,'',0,'article_content','article_list',1,0,1);

CREATE TABLE `#__comment` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '评论id',
  `pid` int(11) NOT NULL DEFAULT '0' COMMENT '评论子id',
  `title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '评论标题',
  `aid` int(11) NOT NULL COMMENT '评论所属文章',
  `uid` int(11) DEFAULT NULL COMMENT '评论关联会员',
  `plname` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT '评论人昵称',
  `plpic` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '/public/upload/userimages/touxiang.png' COMMENT '写入头像',
  `content` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT '评论内容',
  `create_time` int(11) NOT NULL COMMENT '评论时间',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '评论状态 0待审核 1审核通过 2已下架',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='评论存储表';

CREATE TABLE `#__comment_data` (
  `cid` int(11) NOT NULL COMMENT '关联评论id',
  `likes` int(11) NOT NULL DEFAULT '0' COMMENT '评论点赞量',
  `reply` int(11) NOT NULL DEFAULT '0' COMMENT '评论回复量',
  `comment_ip` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '统计点赞当前评论ip',
  PRIMARY KEY (`cid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `#__custform` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fielname` varchar(100) NOT NULL COMMENT '单页文件名',
  `finame` varchar(100) NOT NULL COMMENT '单页名',
  `path` varchar(255) NOT NULL COMMENT '单页文件路径',
  `admid` tinyint(4) NOT NULL COMMENT '创建人id',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='新建单页存储表';

CREATE TABLE `#__download` (
  `id` int(11) NOT NULL COMMENT 'id',
  `title` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '标题',
  `ftitle` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '副标题',
  `titlepic` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '标题图片',
  `mid` int(11) DEFAULT NULL COMMENT '所属栏目',
  `uid` int(11) NOT NULL COMMENT '关联会员id',
  `type` int(11) DEFAULT NULL COMMENT '所属分类',
  `keyword` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '关键词',
  `abstract` text CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT '摘要',
  `tag` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '标签',
  `author` varchar(10) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '发布者',
  `editor` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '内容',
  `price` tinyint(2) NOT NULL DEFAULT '0' COMMENT '阅读类型 1免费 2付费 3vip免费 4vip折扣',
  `moneys` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '付费金额',
  `status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '发布状态 0发布 1草稿 2下架 3待审核 4驳回',
  `orders` int(11) NOT NULL DEFAULT '1' COMMENT '排序',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) NOT NULL COMMENT '更新时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  `comment` tinyint(2) NOT NULL DEFAULT '0' COMMENT '是否允许评论 0允许 1不允许',
  `refusal` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '驳回原因',
  `top` tinyint(2) NOT NULL DEFAULT '0' COMMENT '置顶 0不置顶 1置顶',
  `ppts` tinyint(2) NOT NULL DEFAULT '0' COMMENT '标题图片幻灯 0不幻灯 1幻灯',
  `isadmin` tinyint(2) NOT NULL DEFAULT '0' COMMENT '是否管理员发布 0否 1是',
  `likes_ip` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '当前文章点过赞的ip',
  `homepage` varchar(80) DEFAULT NULL,
  `demo` varchar(120) DEFAULT NULL,
  `softfj` varchar(255) DEFAULT NULL,
  `language` varchar(16) DEFAULT NULL,
  `softtype` varchar(16) DEFAULT NULL,
  `softsq` varchar(16) DEFAULT NULL,
  `star` varchar(100) DEFAULT NULL,
  `filetype` varchar(6) DEFAULT NULL,
  `filesize` varchar(16) DEFAULT NULL,
  `downpath` mediumtext,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `#__download_data` (
  `aid` int(11) NOT NULL COMMENT '关联文章id',
  `browse` int(11) NOT NULL DEFAULT '0' COMMENT '文章浏览次数',
  `likes` int(11) NOT NULL DEFAULT '0' COMMENT '文章点赞次数',
  `comment_t` int(200) NOT NULL DEFAULT '0' COMMENT '文章评论总计',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`aid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `#__fans` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键id',
  `uid` int(11) DEFAULT NULL COMMENT '被关注用户id',
  `not_uid` int(11) DEFAULT NULL COMMENT '被取关用户id',
  `fansid` int(11) NOT NULL COMMENT '关注/取关用户的id',
  `event` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT '事件记录',
  `create_time` int(11) DEFAULT NULL COMMENT '关注/取关时间',
  `update_time` int(11) DEFAULT NULL COMMENT '取关时间',
  `log_ip` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '客户端IP',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='关注事件记录表';

CREATE TABLE `#__feedback` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '留言id',
  `content` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT '留言内容',
  `uid` int(11) DEFAULT '0' COMMENT '关联会员id 0为匿名 默认',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '留言状态 0未读 1已读',
  `create_time` int(11) NOT NULL COMMENT '留言时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='留言存储表';

CREATE TABLE `#__info` (
  `id` int(11) NOT NULL COMMENT 'id',
  `title` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '标题',
  `ftitle` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '副标题',
  `titlepic` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '标题图片',
  `mid` int(11) DEFAULT NULL COMMENT '所属栏目',
  `uid` int(11) NOT NULL COMMENT '关联会员id',
  `type` int(11) DEFAULT NULL COMMENT '所属分类',
  `keyword` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '关键词',
  `abstract` text CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT '摘要',
  `tag` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '标签',
  `author` varchar(10) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '发布者',
  `editor` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '内容',
  `price` tinyint(2) NOT NULL DEFAULT '0' COMMENT '阅读类型 1免费 2付费 3vip免费 4vip折扣',
  `moneys` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '付费金额',
  `status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '发布状态 0发布 1草稿 2下架 3待审核 4驳回',
  `orders` int(11) NOT NULL DEFAULT '1' COMMENT '排序',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) NOT NULL COMMENT '更新时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  `comment` tinyint(2) NOT NULL DEFAULT '0' COMMENT '是否允许评论 0允许 1不允许',
  `refusal` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '驳回原因',
  `top` tinyint(2) NOT NULL DEFAULT '0' COMMENT '置顶 0不置顶 1置顶',
  `ppts` tinyint(2) NOT NULL DEFAULT '0' COMMENT '标题图片幻灯 0不幻灯 1幻灯',
  `isadmin` tinyint(2) NOT NULL DEFAULT '0' COMMENT '是否管理员发布 0否 1是',
  `likes_ip` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '当前文章点过赞的ip',
  `email` varchar(80) DEFAULT NULL,
  `mycontact` tinytext,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `#__info_data` (
  `aid` int(11) NOT NULL COMMENT '关联文章id',
  `browse` int(11) NOT NULL DEFAULT '0' COMMENT '文章浏览次数',
  `likes` int(11) NOT NULL DEFAULT '0' COMMENT '文章点赞次数',
  `comment_t` int(200) NOT NULL DEFAULT '0' COMMENT '文章评论总计',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`aid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `#__ip_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'IP地址ID',
  `ip` varchar(20) NOT NULL COMMENT 'IP地址记录',
  `is_member` int(11) NOT NULL COMMENT '是否是会员(0为游客,其他为会员ID)',
  `source` tinyint(4) NOT NULL COMMENT '访问来源(0为移动端,1为PC端)',
  `create_time` int(10) NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8 COMMENT='IP地址记录表';

CREATE TABLE `#__links` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '友链id',
  `title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '友链名称',
  `url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '友链地址',
  `ico` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '友链图标/logo',
  `intro` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '友链描述',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '友链状态',
  `email` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '友链联系方式',
  `orders` int(11) NOT NULL DEFAULT '1' COMMENT '友链排序',
  `create_time` int(11) NOT NULL COMMENT '友链创建时间',
  `checkurl` tinyint(4) DEFAULT NULL COMMENT '友链是否上链 空为未检测',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='友链表';

INSERT INTO `#__links` VALUES (1,'木鱼CMS','http://www.muyucms.com',NULL,'MuYuCMS内容管理系统',1,NULL,1,1614229957,NULL);

CREATE TABLE `#__log` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '日志id',
  `user` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT '日志事件人',
  `content` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT '日志内容',
  `log_ip` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT '日志发生ip',
  `log_time` int(11) NOT NULL COMMENT '日志发生时间',
  `user_id` int(11) NOT NULL COMMENT '日志关联管理id',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='日志表';

CREATE TABLE `#__login_set` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '登录id',
  `qq_close` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'QQ登录(0关，1开)',
  `weixin_close` tinyint(4) NOT NULL DEFAULT '0' COMMENT '微信登录(0关，1开)',
  `sina_close` tinyint(4) NOT NULL DEFAULT '0' COMMENT '微博登录(0关，1开)',
  `baidu_close` tinyint(4) NOT NULL DEFAULT '0' COMMENT '百度登录(0关，1开)',
  `gitee_close` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Gitee登录(0关，1开)',
  `github_close` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Github登录(0关，1开)',
  `douyin_close` tinyint(4) NOT NULL DEFAULT '0' COMMENT '抖音登录(0关，1开)',
  `dingtalk_close` tinyint(4) NOT NULL DEFAULT '0' COMMENT '钉钉登录(0关，1开)',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='快捷登录设置表';

INSERT INTO `#__login_set` VALUES (1,0,0,0,0,0,0,0,0);

CREATE TABLE `#__member` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '用户id',
  `name` varchar(250) COLLATE utf8_unicode_ci NOT NULL COMMENT '用户名称',
  `sex` tinyint(4) NOT NULL DEFAULT '2' COMMENT '用户性别 0女 1男 2保密',
  `account` varchar(250) COLLATE utf8_unicode_ci NOT NULL COMMENT '用户账号',
  `password` varchar(250) COLLATE utf8_unicode_ci NOT NULL COMMENT '用户密码',
  `email` varchar(250) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '用户邮箱',
  `phone` bigint(20) DEFAULT NULL COMMENT '用户手机',
  `photo` varchar(250) COLLATE utf8_unicode_ci DEFAULT '/public/upload/userimages/touxiang.png' COMMENT '用户头像',
  `update_time` int(11) NOT NULL COMMENT '更新时间',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '用户状态 0 禁用 1启用',
  `intro` varchar(250) COLLATE utf8_unicode_ci NOT NULL COMMENT '用户简介',
  `outip` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '上次登录ip',
  `super` tinyint(4) NOT NULL DEFAULT '0' COMMENT '超级会员(0为否，1为是)',
  `outtime` int(11) DEFAULT NULL COMMENT '上次登录时间',
  `degree` int(11) NOT NULL DEFAULT '0' COMMENT '登录错误次数记录',
  `last_cwtime` int(11) DEFAULT NULL COMMENT '最后一次登录错误的时间',
  `count` int(11) NOT NULL DEFAULT '0' COMMENT '登录次数记录',
  `cwtime` int(11) DEFAULT NULL COMMENT '登录错误时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间记录',
  `jurisdiction` tinyint(4) NOT NULL DEFAULT '0' COMMENT '用户投稿权限 0需要审核 1直接发布',
  `fans` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '我的粉丝',
  `attention` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '我的关注',
  `artlikes` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '点赞过的文章',
  `commlieks` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '统计当前会员点赞的评论ID',
  `home_count` int(11) NOT NULL DEFAULT '0' COMMENT '个人主页访问量',
  `buymat` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '已购买文章id记录',
  `money` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '余额',
  `consumption` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '总充值',
  `total` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '总消费',
  `wexin_openid` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '微信openid',
  `qq_openid` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'QQopenid',
  `sina_openid` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '微博openid',
  `baidu_openid` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '百度openid',
  `douyin_openid` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '抖音openid',
  `gitee_openid` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Gitee openid',
  `github_openid` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Github openid',
  `dingtalk_openid` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '钉钉openid',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='用户表';

CREATE TABLE `#__member_buylog` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '消费id',
  `uid` int(11) NOT NULL COMMENT '会员id',
  `order_id` varchar(100) NOT NULL COMMENT '订单号',
  `product_id` int(11) NOT NULL COMMENT '产品id',
  `buy_type` tinyint(4) NOT NULL COMMENT '购买类型(0为购买，1为续费)',
  `pay_type` tinyint(4) NOT NULL COMMENT '支付类型(0为QQ，1为微信，2为支付宝，3余额，-1为其他)',
  `money` decimal(10,2) NOT NULL COMMENT '金额',
  `buy_ip` varchar(100) NOT NULL COMMENT '购买ID地址',
  `create_time` int(10) NOT NULL COMMENT '购买时间',
  `intro` varchar(255) NOT NULL COMMENT '产品说明',
  `status` tinyint(4) NOT NULL COMMENT '状态(0未付款，1已付款)',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `#__member_data` (
  `uid` int(11) NOT NULL COMMENT '关联用户id',
  `comment` int(11) NOT NULL DEFAULT '0' COMMENT '用户评论总数统计',
  `le_word` int(11) NOT NULL DEFAULT '0' COMMENT '用户留言总数统计',
  `attention` int(11) NOT NULL DEFAULT '0' COMMENT '用户关注总数',
  `fans` int(11) NOT NULL DEFAULT '0' COMMENT '用户粉丝总数',
  `contribute` int(11) NOT NULL DEFAULT '0' COMMENT '用户累计投稿次数',
  `delete_time` int(11) DEFAULT NULL COMMENT '会员附属表记录删除时间',
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='用户多余数据存储';

CREATE TABLE `#__member_paylog` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '充值id',
  `order_id` varchar(100) NOT NULL COMMENT '订单号',
  `uid` int(11) NOT NULL COMMENT '会员id',
  `pay_type` tinyint(4) NOT NULL COMMENT '充值类型(0为QQ，1为微信，2为支付宝，3手动充值，-1为其他)',
  `money` decimal(10,2) NOT NULL COMMENT '金额',
  `create_time` int(10) NOT NULL COMMENT '充值时间',
  `pay_ip` varchar(100) NOT NULL COMMENT '充值IP地址',
  `status` tinyint(4) NOT NULL COMMENT '状态(0未付款，1已付款)',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='会员充值记录表';

CREATE TABLE `#__model` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '模型id',
  `name` varchar(200) NOT NULL COMMENT '模型名称',
  `aliase` varchar(100) NOT NULL COMMENT '模型别名前台展示',
  `tablename` varchar(200) NOT NULL COMMENT '模型数据',
  `intro` varchar(100) DEFAULT NULL COMMENT '当前模型简介',
  `create_time` int(11) NOT NULL COMMENT '模型创建时间',
  `type` tinyint(4) NOT NULL DEFAULT '1' COMMENT '模型类型0内部 1全局',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '模型状态 0关闭 1启用',
  `issue` tinyint(4) NOT NULL DEFAULT '1' COMMENT '模型投稿控制',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COMMENT='系统模型表';

INSERT INTO `#__model` VALUES (1,'新闻系统模型','新闻内容','news','新闻系统模型',1621067440,1,1,1),(2,'下载系统模型','下载内容','download','下载系统模型',1621068478,1,1,1),(3,'图片系统模型','图片内容','photo','图片系统模型',1621068511,1,1,1),(4,'电影系统模型','电影内容','movie','电影系统模型',1621068540,1,1,1),(5,'商城系统模型','商城内容','shop','商城系统模型',1621068569,1,1,1),(6,'文章系统模型','文章内容','article','文章系统模型(内容存文本)',1621068611,1,1,1),(7,'分类信息模型','分类内容','info','分类信息模型',1621068648,1,1,1),(8,'在线工具模型','工具内容','tools','在线工具模型',1621068676,1,1,1);

CREATE TABLE `#__modfiel` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '字段ID',
  `modid` int(11) NOT NULL COMMENT '模型id',
  `field` varchar(100) NOT NULL COMMENT '字段标识',
  `title` varchar(100) NOT NULL COMMENT '字段名称',
  `type` varchar(255) NOT NULL COMMENT '字段类型',
  `forms` varchar(255) NOT NULL COMMENT '表单元素',
  `defaults` varchar(600) DEFAULT NULL COMMENT '默认值',
  `leng` char(200) DEFAULT NULL COMMENT '长度',
  `adst` tinyint(4) NOT NULL DEFAULT '1' COMMENT '后台是否显示',
  `hmst` tinyint(4) NOT NULL DEFAULT '2' COMMENT '前台是否显示',
  `required` tinyint(4) NOT NULL DEFAULT '1' COMMENT '是否必填',
  `chart` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1主表or2副表',
  `orders` int(11) DEFAULT NULL COMMENT '排序',
  `ismuyu` tinyint(4) NOT NULL DEFAULT '2',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=85 DEFAULT CHARSET=utf8 COMMENT='模型字段表';

INSERT INTO `#__modfiel` VALUES (1,1,'title','标题','varchar','text',NULL,'255',1,1,1,1,0,1),(2,1,'titlepic','标题图片','varchar','img',NULL,'255',1,1,0,1,0,1),(3,1,'ftitle','副标题','varchar','text',NULL,'255',1,1,0,1,0,1),(4,1,'keyword','关键词','varchar','text',NULL,'255',1,1,0,1,0,1),(5,1,'abstract','摘要','varchar','textarea',NULL,'255',1,1,0,1,0,1),(6,1,'author','发布者','varchar','text',NULL,'255',1,1,0,1,0,1),(7,2,'title','标题','varchar','text',NULL,'255',1,1,1,1,0,1),(8,2,'titlepic','标题图片','varchar','img',NULL,'255',1,1,0,1,0,1),(9,2,'ftitle','副标题','varchar','text',NULL,'255',1,1,0,1,0,1),(10,2,'keyword','关键词','varchar','text',NULL,'255',1,1,0,1,0,1),(11,2,'abstract','摘要','varchar','textarea',NULL,'255',1,1,0,1,0,1),(12,2,'author','发布者','varchar','text','','255',1,1,0,1,0,1),(13,3,'title','标题','varchar','text',NULL,'255',1,1,1,1,0,1),(14,3,'titlepic','标题图片','varchar','img','','255',1,1,0,1,0,1),(15,3,'ftitle','副标题','varchar','text',NULL,'255',1,1,0,1,0,1),(16,3,'keyword','关键词','varchar','text',NULL,'255',1,1,0,1,0,1),(17,3,'abstract','摘要','varchar','textarea',NULL,'255',1,1,0,1,0,1),(18,3,'author','发布者','varchar','text',NULL,'255',1,1,0,1,0,1),(19,4,'title','标题','varchar','text',NULL,'255',1,1,1,1,0,1),(20,4,'titlepic','标题图片','varchar','img',NULL,'255',1,1,0,1,0,1),(21,4,'ftitle','副标题','varchar','text',NULL,'255',1,1,0,1,0,1),(22,4,'keyword','关键词','varchar','text',NULL,'255',1,1,0,1,0,1),(23,4,'abstract','摘要','varchar','textarea',NULL,'255',1,1,0,1,0,1),(24,4,'author','发布者','varchar','text',NULL,'255',1,1,0,1,0,1),(25,5,'title','标题','varchar','text',NULL,'255',1,1,1,1,0,1),(26,5,'titlepic','商品大图','varchar','img','','255',1,1,0,1,0,1),(27,5,'ftitle','副标题','varchar','text',NULL,'255',1,1,0,1,0,1),(28,5,'keyword','关键词','varchar','text',NULL,'255',1,1,0,1,0,1),(29,5,'abstract','简单描述','varchar','textarea','','255',1,1,0,1,0,1),(30,5,'author','发布者','varchar','text',NULL,'255',1,1,0,1,0,1),(31,6,'title','标题','varchar','text',NULL,'255',1,1,1,1,0,1),(32,6,'titlepic','标题图片','varchar','img',NULL,'255',1,1,0,1,0,1),(33,6,'ftitle','副标题','varchar','text',NULL,'255',1,1,0,1,0,1),(34,6,'keyword','关键词','varchar','text',NULL,'255',1,1,0,1,0,1),(35,6,'abstract','摘要','varchar','textarea',NULL,'255',1,1,0,1,0,1),(36,6,'author','发布者','varchar','text',NULL,'255',1,1,0,1,0,1),(37,7,'title','标题','varchar','text',NULL,'255',1,1,1,1,0,1),(38,7,'titlepic','标题图片','varchar','img',NULL,'255',1,1,0,1,0,1),(39,7,'ftitle','副标题','varchar','text',NULL,'255',1,1,0,1,0,1),(40,7,'keyword','关键词','varchar','text',NULL,'255',1,1,0,1,0,1),(41,7,'abstract','摘要','varchar','textarea',NULL,'255',1,1,0,1,0,1),(42,7,'author','发布者','varchar','text',NULL,'255',1,1,0,1,0,1),(43,8,'title','工具名称','varchar','text','','255',1,1,1,1,0,1),(44,8,'titlepic','工具图标','varchar','img','','255',1,1,0,1,0,1),(45,8,'ftitle','副标题','varchar','text',NULL,'255',1,1,0,1,0,1),(46,8,'keyword','关键词','varchar','text',NULL,'255',1,1,0,1,0,1),(47,8,'abstract','摘要','varchar','textarea',NULL,'255',1,1,0,1,0,1),(48,8,'author','发布者','varchar','text',NULL,'255',1,1,0,1,0,1),(49,1,'befrom','信息来源','char','text','','60',1,1,1,1,0,2),(50,2,'homepage','官方网站','varchar','text','https://','80',1,1,2,1,0,2),(51,2,'demo','系统演示','varchar','text','https://','120',1,1,1,1,0,2),(52,2,'softfj','运行环境','varchar','text','','255',1,1,2,1,0,2),(53,2,'language','软件语言','varchar','text','','16',1,1,2,1,0,2),(54,2,'softtype','软件类型','varchar','select','1||国产软件||2||汉化软件||3||国外软件\n','16',1,1,2,1,0,2),(55,2,'softsq','授权形式','varchar','select','1||共享软件||2||免费软件||3||自由软件||4||试用软件||5||演示软件||6||商业软件','16',1,1,2,1,0,2),(56,2,'star','软件等级','varchar','select','★☆☆☆☆||一级||★★☆☆☆||二级||★★★☆☆||三级||★★★★☆||四级||★★★★★||五级','100',1,1,2,1,0,2),(57,2,'filetype','文件类型','varchar','select','1||.zip||2||.rar||3||.exe','6',1,1,2,1,0,2),(58,2,'filesize','文件大小','varchar','text','','16',1,1,2,1,0,2),(59,2,'downpath','下载地址','mediumtext','down','','',1,1,2,1,0,2),(60,3,'filesize','文件大小','varchar','text','','10',1,1,2,1,0,2),(61,3,'picsize','图片尺寸','varchar','text','','20',1,1,1,1,0,2),(62,3,'picfrom','来源','varchar','text','','120',1,1,2,1,0,2),(63,3,'picurl','图片大图','varchar','img','','200',1,1,1,1,0,2),(64,4,'movietype','影片类型','varchar','select','1||港台影视||2||海外影视||3||大陆影视||4||日韩影视','16',1,1,2,1,0,2),(65,4,'company','出品公司','varchar','text','','200',1,1,2,1,0,2),(66,4,'movietime','出品时间','varchar','text','','20',1,1,2,1,0,2),(67,4,'player','主演','varchar','text','','255',1,1,1,1,0,2),(68,4,'playadmin','导演','varchar','text','','255',1,1,2,1,0,2),(69,4,'filetype','影片格式','varchar','select','1||.rm||2||.rmbv||3||.mp4||4||.asf||5||.wmv||6||.avi','10',1,1,2,1,0,2),(70,4,'filesize','影片大小','varchar','text','','16',1,1,1,1,0,2),(71,4,'star','推荐等级','tinyint','select','1||1||2||2||3||3||4||4||5||5','1',1,1,2,1,0,2),(72,4,'playtime','片长','varchar','text','','20',1,1,2,1,0,2),(73,4,'moviefen','扣除点数','int','text','','',1,1,2,1,0,2),(74,4,'downpath','下载地址','mediumtext','text','','',1,1,2,1,0,2),(75,5,'productno','商品编号','varchar','text','','30',1,1,1,1,0,2),(76,5,'pbrand','品牌','varchar','text','','30',1,1,1,1,0,2),(77,5,'unit','计量单位','varchar','text','','16',1,1,1,1,0,2),(78,5,'tprice','市场价格','float','text','','11,2',1,1,2,1,0,2),(79,5,'pmaxnum','库存','int','text','100','',1,1,1,1,0,2),(80,5,'psalenum','销售量','int','text','','',1,1,2,1,0,2),(81,6,'befrom','信息来源','char','text','','60',1,1,2,1,0,2),(82,7,'email','邮箱','varchar','text','','80',1,1,2,1,0,2),(83,7,'mycontact','联系方式','text','text','','46',1,1,2,1,0,2),(84,8,'url','工具链接','varchar','text','','50',1,1,2,1,0,2);

CREATE TABLE `#__movie` (
  `id` int(11) NOT NULL COMMENT 'id',
  `title` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '标题',
  `ftitle` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '副标题',
  `titlepic` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '标题图片',
  `mid` int(11) DEFAULT NULL COMMENT '所属栏目',
  `uid` int(11) NOT NULL COMMENT '关联会员id',
  `type` int(11) DEFAULT NULL COMMENT '所属分类',
  `keyword` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '关键词',
  `abstract` text CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT '摘要',
  `tag` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '标签',
  `author` varchar(10) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '发布者',
  `editor` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '内容',
  `price` tinyint(2) NOT NULL DEFAULT '0' COMMENT '阅读类型 1免费 2付费 3vip免费 4vip折扣',
  `moneys` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '付费金额',
  `status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '发布状态 0发布 1草稿 2下架 3待审核 4驳回',
  `orders` int(11) NOT NULL DEFAULT '1' COMMENT '排序',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) NOT NULL COMMENT '更新时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  `comment` tinyint(2) NOT NULL DEFAULT '0' COMMENT '是否允许评论 0允许 1不允许',
  `refusal` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '驳回原因',
  `top` tinyint(2) NOT NULL DEFAULT '0' COMMENT '置顶 0不置顶 1置顶',
  `ppts` tinyint(2) NOT NULL DEFAULT '0' COMMENT '标题图片幻灯 0不幻灯 1幻灯',
  `isadmin` tinyint(2) NOT NULL DEFAULT '0' COMMENT '是否管理员发布 0否 1是',
  `likes_ip` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '当前文章点过赞的ip',
  `movietype` varchar(16) DEFAULT NULL,
  `company` varchar(200) DEFAULT NULL,
  `movietime` varchar(20) DEFAULT NULL,
  `player` varchar(255) DEFAULT NULL,
  `playadmin` varchar(255) DEFAULT NULL,
  `filetype` varchar(10) DEFAULT NULL,
  `filesize` varchar(16) DEFAULT NULL,
  `star` tinyint(1) DEFAULT NULL,
  `playtime` varchar(20) DEFAULT NULL,
  `moviefen` int(11) DEFAULT NULL,
  `downpath` mediumtext,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `#__movie_data` (
  `aid` int(11) NOT NULL COMMENT '关联文章id',
  `browse` int(11) NOT NULL DEFAULT '0' COMMENT '文章浏览次数',
  `likes` int(11) NOT NULL DEFAULT '0' COMMENT '文章点赞次数',
  `comment_t` int(200) NOT NULL DEFAULT '0' COMMENT '文章评论总计',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`aid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `#__news` (
  `id` int(11) NOT NULL COMMENT 'id',
  `title` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '标题',
  `ftitle` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '副标题',
  `titlepic` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '标题图片',
  `mid` int(11) DEFAULT NULL COMMENT '所属栏目',
  `uid` int(11) NOT NULL COMMENT '关联会员id',
  `type` int(11) DEFAULT NULL COMMENT '所属分类',
  `keyword` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '关键词',
  `abstract` text CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT '摘要',
  `tag` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '标签',
  `author` varchar(10) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '发布者',
  `editor` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '内容',
  `price` tinyint(2) NOT NULL DEFAULT '0' COMMENT '阅读类型 1免费 2付费 3vip免费 4vip折扣',
  `moneys` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '付费金额',
  `status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '发布状态 0发布 1草稿 2下架 3待审核 4驳回',
  `orders` int(11) NOT NULL DEFAULT '1' COMMENT '排序',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) NOT NULL COMMENT '更新时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  `comment` tinyint(2) NOT NULL DEFAULT '0' COMMENT '是否允许评论 0允许 1不允许',
  `refusal` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '驳回原因',
  `top` tinyint(2) NOT NULL DEFAULT '0' COMMENT '置顶 0不置顶 1置顶',
  `ppts` tinyint(2) NOT NULL DEFAULT '0' COMMENT '标题图片幻灯 0不幻灯 1幻灯',
  `isadmin` tinyint(2) NOT NULL DEFAULT '0' COMMENT '是否管理员发布 0否 1是',
  `likes_ip` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '当前文章点过赞的ip',
  `befrom` char(60) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `#__news` VALUES (1,'欢迎使用MuYuCMS！','','http://www.muyucms.com/public/upload/imgages/logo.png',1,1,0,'MuYuCMS','欢迎使用MuYuCMS','MuYuCMS','admin','<p><img src="public/upload/images/logo.png" title="muyucms" alt="muyucms"/></p>\n<p>欢迎使用MuYuCMS，这是程序自动生成的文章，您可以删除或是编辑它。</p>\n<p>系统生成了一篇《欢迎使用MuYuCMS！》，祝您使用愉快！</p>\n',1,0.00,0,1,1626013178,1626013643,NULL,0,NULL,0,0,1,NULL,'MuYuCMS');

CREATE TABLE `#__news_data` (
  `aid` int(11) NOT NULL COMMENT '关联文章id',
  `browse` int(11) NOT NULL DEFAULT '0' COMMENT '文章浏览次数',
  `likes` int(11) NOT NULL DEFAULT '0' COMMENT '文章点赞次数',
  `comment_t` int(200) NOT NULL DEFAULT '0' COMMENT '文章评论总计',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`aid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `#__news_data` VALUES (1,0,0,0,NULL);

CREATE TABLE `#__pay` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '支付id',
  `alipay_pid` varchar(50) DEFAULT NULL COMMENT '支付宝PID',
  `alipay_key` varchar(100) DEFAULT NULL COMMENT '支付宝安全效验码',
  `wxpay_mchid` varchar(100) DEFAULT NULL COMMENT '微信商户号',
  `wxpay_kye` varchar(255) DEFAULT NULL COMMENT '微信商户key',
  `wxpay_appid` varchar(100) DEFAULT NULL COMMENT '微信应用AppID',
  `alipayf2f_private_id` varchar(100) DEFAULT NULL COMMENT '支付宝当面付AppID',
  `alipayf2f_private_key` text COMMENT '支付宝当面付私钥',
  `alipayf2f_public_key` text COMMENT '支付宝当面付公钥',
  `qqpay_mchid` varchar(50) DEFAULT NULL COMMENT 'QQ商户号',
  `qqpay_key` varchar(255) DEFAULT NULL COMMENT 'QQ商户key',
  `epay_url` varchar(100) DEFAULT NULL COMMENT '易支付网址',
  `epay_appid` varchar(10) DEFAULT NULL COMMENT '易支付商户号',
  `epay_key` varchar(255) DEFAULT NULL COMMENT '易支付key',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

INSERT INTO `#__pay` VALUES (1,'','','','','','','','','','','','','');

CREATE TABLE `#__pay_set` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '支付设置id',
  `alipay` tinyint(4) NOT NULL DEFAULT '1' COMMENT '支付宝支付(0为官方支付，1为当面付)',
  `alipay_close` tinyint(4) NOT NULL DEFAULT '1' COMMENT '支付宝支付开关(0为关，1为开)',
  `wxpay_close` tinyint(4) NOT NULL DEFAULT '1' COMMENT '微信支付开关(0为关，1为开)',
  `qqpay_close` tinyint(4) NOT NULL DEFAULT '1' COMMENT 'QQ支付开关(0为关，1为开)',
  `moneypay_close` tinyint(4) NOT NULL DEFAULT '1' COMMENT '余额支付开关(0为关，1为开)',
  `epay_close` tinyint(4) NOT NULL DEFAULT '1' COMMENT '易支付开关(0为关，1为开)',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='支付设置开关表';

INSERT INTO `#__pay_set` VALUES (1,1,0,0,0,0,0);

CREATE TABLE `#__photo` (
  `id` int(11) NOT NULL COMMENT 'id',
  `title` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '标题',
  `ftitle` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '副标题',
  `titlepic` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '标题图片',
  `mid` int(11) DEFAULT NULL COMMENT '所属栏目',
  `uid` int(11) NOT NULL COMMENT '关联会员id',
  `type` int(11) DEFAULT NULL COMMENT '所属分类',
  `keyword` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '关键词',
  `abstract` text CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT '摘要',
  `tag` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '标签',
  `author` varchar(10) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '发布者',
  `editor` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '内容',
  `price` tinyint(2) NOT NULL DEFAULT '0' COMMENT '阅读类型 1免费 2付费 3vip免费 4vip折扣',
  `moneys` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '付费金额',
  `status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '发布状态 0发布 1草稿 2下架 3待审核 4驳回',
  `orders` int(11) NOT NULL DEFAULT '1' COMMENT '排序',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) NOT NULL COMMENT '更新时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  `comment` tinyint(2) NOT NULL DEFAULT '0' COMMENT '是否允许评论 0允许 1不允许',
  `refusal` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '驳回原因',
  `top` tinyint(2) NOT NULL DEFAULT '0' COMMENT '置顶 0不置顶 1置顶',
  `ppts` tinyint(2) NOT NULL DEFAULT '0' COMMENT '标题图片幻灯 0不幻灯 1幻灯',
  `isadmin` tinyint(2) NOT NULL DEFAULT '0' COMMENT '是否管理员发布 0否 1是',
  `likes_ip` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '当前文章点过赞的ip',
  `filesize` varchar(10) DEFAULT NULL,
  `picsize` varchar(20) DEFAULT NULL,
  `picfrom` varchar(120) DEFAULT NULL,
  `picurl` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `#__photo_data` (
  `aid` int(11) NOT NULL COMMENT '关联文章id',
  `browse` int(11) NOT NULL DEFAULT '0' COMMENT '文章浏览次数',
  `likes` int(11) NOT NULL DEFAULT '0' COMMENT '文章点赞次数',
  `comment_t` int(200) NOT NULL DEFAULT '0' COMMENT '文章评论总计',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`aid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `#__role` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `name` varchar(250) COLLATE utf8_unicode_ci NOT NULL COMMENT '角色名称',
  `jurisdiction` text COLLATE utf8_unicode_ci COMMENT '角色权限内容',
  `orders` int(11) NOT NULL DEFAULT '1' COMMENT '角色排序',
  `info` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '权限说明',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态(0为已停用，1为已启用)',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) NOT NULL COMMENT '修改时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='角色存储表';

INSERT INTO `#__role` VALUES (1,'超级管理员','1,2,3,4,5,6,7,8,9,10,11,47,48,50,51,33,34,35,36,37,38,39,40,41,42,43,44,45,46,62,63,64,70,71,80,81,79,82,83,84,85,86,87,88,89,90,91,92,96,97,98,93,94,95,99,100,101,102,103,104,105,172,173,106,107,108,142,143,144,145,146,147,148,149,150,151,152,155,117,118,120,121,125,153,122,123,126,124,127,128,129,130,131,132,133,134,135,136,137,138,139,159,160,162,163,161,164,165,166,167,168,169,170',1,'具备后台所有权限!',1,1611128805,1626013739);

CREATE TABLE `#__rule` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '规则id',
  `pid` int(11) NOT NULL COMMENT '父id',
  `title` varchar(20) NOT NULL COMMENT '规则名称',
  `titlepic` varchar(255) DEFAULT NULL COMMENT '菜单图标',
  `type` tinyint(3) NOT NULL DEFAULT '1' COMMENT '类型(为1condition字段可以定义规则表达式)',
  `href` varchar(50) DEFAULT NULL COMMENT '地址(模块/控制器/方法)',
  `orders` int(11) NOT NULL COMMENT '排序',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '状态(0停用,1启用)',
  `condition` varchar(100) NOT NULL COMMENT '规则附加条件',
  `level` tinyint(3) NOT NULL DEFAULT '0' COMMENT '级别',
  `comment` varchar(100) DEFAULT NULL COMMENT '具体描述',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=179 DEFAULT CHARSET=utf8 COMMENT='后台菜单表';

INSERT INTO `#__rule` VALUES (1,0,'管理员管理','layui-icon-user',1,'',100,1,'',0,'管理员管理'),(2,1,'角色管理','',1,'admin/Roles/roleList',2,1,'',1,'角色管理'),(3,2,'列表查看','',1,'admin/Roles/roleList',3,1,'',2,'列表查看'),(4,2,'角色添加','',1,'admin/Roles/add',4,1,'',2,'角色添加'),(5,2,'角色编辑','',1,'admin/Roles/edit',5,1,'',2,'角色编辑'),(6,2,'角色删除','',1,'admin/Roles/deletes',6,1,'',2,'角色删除'),(7,2,'状态变更','',1,'admin/Roles/setStatus',7,1,'',2,'状态变更'),(8,1,'管理员列表','',1,'admin/Admin/adminList',9,1,'',1,'管理员列表'),(9,8,'列表查看','',1,'admin/Admin/adminList',10,1,'',2,'列表查看'),(10,8,'管理员添加','',1,'admin/Admin/add',11,1,'',2,'管理员添加'),(11,8,'管理员编辑','',1,'admin/Admin/edit',12,1,'',2,'管理员编辑'),(12,8,'管理员删除','',1,'admin/Admin/deletes',13,1,'',2,'管理员删除'),(33,0,'会员管理','layui-icon-group',1,'',101,1,'',0,'会员管理'),(34,33,'会员列表','',1,'admin/Member/index',102,1,'',1,'会员列表'),(35,34,'会员添加','',1,'admin/Member/add',104,1,'',2,'会员添加'),(36,34,'列表查看','',1,'admin/Member/index',103,1,'',2,'列表查看'),(37,34,'会员编辑','',1,'admin/Member/edit',105,1,'',2,'会员编辑'),(38,34,'会员删除','',1,'admin/Member/deletes',106,1,'',2,'会员删除'),(39,34,'会员状态','',1,'admin/Member/setStatus',107,1,'',2,'会员状态'),(40,0,'插件管理','layui-icon-util',1,'',104,1,'',0,'插件管理'),(41,40,'插件列表','',1,'admin/Plug/addonslist',1,1,'',1,'插件列表'),(42,41,'列表查看','',1,'admin/Plug/addonslist',2,1,'',2,'插件列表查看'),(43,41,'安装插件','',1,'admin/Plug/adonsinstall',3,1,'',2,'安装插件'),(44,0,'栏目管理','layui-icon-list',1,'',20,1,'',0,'栏目管理'),(45,44,'分类管理','',1,'admin/Types/index',109,1,'',1,'分类管理'),(46,45,'分类列表','',1,'admin/Types/index',110,1,'',2,'分类列表'),(47,1,'管理员回收站','',1,'admin/Admin/dellist',14,1,'',1,'管理员回收站'),(48,47,'列表查看','',1,'admin/Admin/dellist',15,1,'',2,'列表查看'),(49,8,'状态变更','',1,'admin/Admin/setStatus',16,1,'',2,'状态变更'),(50,47,'管理员彻底删除','',1,'admin/Admin/recycle',17,1,'',2,'管理员彻底删除'),(51,47,'管理员还原','',1,'admin/Admin/restore',18,1,'',2,'管理员还原'),(52,45,'分类添加','',1,'admin/Types/add',111,1,'',2,'分类添加'),(53,45,'分类编辑','',1,'admin/Types/edit',112,1,'',2,'分类编辑'),(54,45,'分类删除','',1,'admin/Types/deletes',113,1,'',2,'分类删除'),(55,45,'分类状态','',1,'admin/Types/setStatus',114,1,'',2,'分类状态'),(56,33,'会员回收站','',1,'admin/Member/recycle',108,1,'',1,'会员回收站'),(57,56,'会员回收站列表','',1,'admin/Member/recycle',111,1,'',2,'会员回收站列表'),(58,56,'会员回收站还原','',1,'admin/Member/huanyuan',112,1,'',2,'会员回收站还原'),(59,56,'会员回收站删除','',1,'admin/Member/reallyDel',113,1,'',2,'会员回收站删除'),(60,44,'栏目管理','',1,'admin/Category/index',114,1,'',1,'栏目管理'),(61,60,'栏目列表','',1,'admin/Category/index',115,1,'',2,'栏目列表'),(62,0,'数据管理','layui-icon-form',1,'',277,1,'',0,'数据管理'),(63,62,'数据列表','',1,'admin/Database/index',1,1,'',1,'数据列表'),(64,63,'列表查看','',1,'admin/Database/index',1,1,'',2,'列表查看'),(65,60,'栏目添加','',1,'admin/Category/add',116,1,'',2,'栏目添加'),(66,60,'栏目编辑','',1,'admin/Category/edit',118,1,'',2,'栏目编辑'),(67,60,'栏目删除','',1,'admin/Category/deletes',119,1,'',2,'栏目删除'),(68,40,'友情链接',NULL,1,'admin/Plug/linkindex',2,1,'',1,'友情链接'),(69,68,'列表查看',NULL,1,'admin/Plug/linkindex',2,1,'',2,'列表查看'),(70,0,'系统管理','layui-icon-set',1,'',116,1,'',0,'系统管理'),(71,70,'网站设置',NULL,1,'admin/System/index',117,1,'',1,'网站设置'),(72,68,'友链添加',NULL,1,'admin/Plug/linkcreate',2,1,'',2,'友链添加'),(73,68,'友链编辑',NULL,1,'admin/Plug/linkedit',4,1,'',2,'友链编辑'),(74,68,'友链删除',NULL,1,'admin/Plug/linkdelete',5,1,'',2,'友链删除'),(75,68,'友链验证',NULL,1,'admin/Plug/checkurl',6,1,'',2,'友链验证'),(76,68,'友链状态',NULL,1,'admin/Plug/linkstatus',7,1,'',2,'友链状态'),(77,40,'广告管理',NULL,1,'admin/Plug/addindex',3,1,'',1,'广告管理'),(78,77,'列表查看',NULL,1,'admin/Plug/addindex',1,1,'',2,'列表查看'),(79,70,'屏蔽词',NULL,1,'admin/System/screen',118,1,'',1,'屏蔽词'),(80,71,'列表查看',NULL,1,'admin/System/index',119,1,'',2,'列表查看'),(81,71,'设置编辑',NULL,1,'admin/System/wzedit',119,1,'',2,'设置编辑'),(82,79,'列表查看',NULL,1,'admin/System/screen',120,1,'',2,'列表查看'),(83,79,'屏蔽编辑',NULL,1,'admin/System/screenedit',121,1,'',2,'屏蔽编辑'),(84,70,'发信配置',NULL,1,'admin/Sms/index',121,1,'',1,'发信配置'),(85,84,'列表查看',NULL,1,'admin/Sms/index',122,1,'',2,'列表查看'),(86,84,'邮箱编辑',NULL,1,'admin/Sms/edit',123,1,'',2,'邮箱编辑'),(87,70,'安全设置',NULL,1,'admin/System/safety',123,1,'',1,'安全设置'),(88,87,'列表查看',NULL,1,'admin/System/safety',124,1,'',2,'列表查看'),(89,87,'安全编辑',NULL,1,'admin/System/safetyedit',125,1,'',2,'安全编辑'),(90,70,'系统日志',NULL,1,'admin/Log/index',125,1,'',1,'系统日志'),(91,90,'列表查看',NULL,1,'admin/Log/index',126,1,'',2,'列表查看'),(92,90,'日志编辑',NULL,1,'admin/Log/edit',126,1,'',2,'日志编辑'),(93,0,'系统模型','layui-icon-form',1,NULL,103,1,'',0,'内容管理'),(94,93,'模型管理',NULL,1,'admin/Model/index',1,1,'',1,'模型管理'),(95,94,'模型列表',NULL,1,'admin/Model/index',1,1,'',2,'模型列表'),(96,70,'系统更新',NULL,1,'admin/system/update',117,1,'',1,'系统更新'),(97,96,'版本检测',NULL,1,'admin/update/get_version',117,1,'',2,'版本检测'),(98,96,'更新操作',NULL,1,'admin/update/entrance',117,1,'',2,'更新操作'),(99,94,'模型编辑',NULL,1,'admin/Model/edit',2,1,'',2,'模型编辑'),(100,94,'模型删除',NULL,1,'admin/Model/moddel',3,1,'',2,'模型删除'),(101,94,'新增模型',NULL,1,'admin/Model/add',4,1,'',2,'新增模型'),(102,94,'字段管理',NULL,1,'admin/Modelfield/index',5,1,'',2,'字段管理'),(103,94,'添加字段',NULL,1,'admin/Modelfield/add',6,1,'',2,'添加字段'),(104,94,'编辑字段',NULL,1,'admin/Modelfield/edit',7,1,'',2,'编辑字段'),(105,94,'删除字段',NULL,1,'admin/Modelfield/fiedel',8,1,'',2,'删除字段'),(106,0,'内容管理','layui-icon-read',1,NULL,3,1,'',0,'内容管理'),(107,106,'内容列表',NULL,1,'admin/Matter/index',1,1,'',1,'内容列表'),(108,107,'列表查看',NULL,1,'admin/Matter/index',1,1,'',2,'列表查看'),(109,77,'广告添加',NULL,1,'admin/Plug/addcreate',1,1,'',2,'广告添加'),(110,77,'广告编辑',NULL,1,'admin/Plug/addedit',2,1,'',2,'广告编辑'),(111,77,'广告删除',NULL,1,'admin/Plug/adddelete',3,1,'',2,'广告删除'),(112,77,'广告状态',NULL,1,'admin/Plug/addstatus',4,1,'',2,'广告状态'),(113,33,'购买记录',NULL,1,'admin/Member/buyLog',108,1,'',1,'购买记录'),(114,113,'列表查看',NULL,1,'admin/Member/buyLog',109,1,'',2,'列表查看'),(115,33,'充值记录',NULL,1,'admin/Member/payLog',110,1,'',1,'充值记录'),(116,115,'列表查看',NULL,1,'admin/Member/payLog',110,1,'',2,'列表查看'),(117,0,'模板管理','layui-icon-template-1',1,NULL,104,1,'',0,'模板管理'),(118,117,'模板列表',NULL,1,'admin/Template/index',1,1,'',1,'模板列表'),(119,34,'金额充值',NULL,1,'admin/Member/moneyAdd',108,1,'',2,'金额充值'),(120,118,'模板删除',NULL,1,'admin/Template/deletes',3,1,'',2,'模板删除'),(121,118,'模板上传',NULL,1,'admin/Template/tempup',4,1,'',2,'模板上传'),(122,0,'支付管理','layui-icon-vercode',1,NULL,501,1,'',0,'支付管理'),(123,122,'支付配置',NULL,1,'admin/Pay/index',502,1,'',1,'支付配置'),(124,122,'支付设置',NULL,1,'admin/Pay/setPay',503,1,'',1,'支付设置'),(125,118,'列表查看',NULL,1,'admin/Template/index',2,1,'',2,'列表查看'),(126,123,'支付配置编辑',NULL,1,'admin/Pay/indexEdit',502,1,'',2,'支付配置编辑'),(127,124,'支付设置编辑',NULL,1,'admin/Pay/setPayEdit',503,1,'',2,'支付设置编辑'),(128,0,'互动管理','layui-icon-dialogue',1,NULL,102,1,'',0,'互动管理'),(129,128,'意见反馈',NULL,1,'admin/Feedback/index',3,1,'',1,'意见反馈'),(130,129,'留言列表',NULL,1,'admin/Feedback/index',1,1,'',2,'留言列表'),(131,129,'留言编辑',NULL,1,'admin/Feedback/edit',2,1,'',2,'留言编辑'),(132,129,'留言删除',NULL,1,'admin/Feedback/deletes',3,1,'',2,'留言删除'),(133,128,'评论列表',NULL,1,'admin/Comment/index',1,1,'',1,'评论列表'),(134,133,'列表查看',NULL,1,'admin/Comment/index',1,1,'',2,'列表查看'),(135,133,'评论删除',NULL,1,'admin/Comment/deletes',2,1,'',2,'评论删除'),(136,133,'评论状态',NULL,1,'admin/Comment/setStatus',3,1,'',2,'评论状态'),(137,128,'评论审核',NULL,1,'admin/Comment/audit',2,1,'',1,'评论审核'),(138,137,'评论审核列表',NULL,1,'admin/Comment/audit',1,1,'',2,'评论审核列表'),(139,137,'评论审核',NULL,1,'admin/Comment/shenhe',2,1,'',2,'评论审核'),(142,107,'添加内容',NULL,1,'admin/Matter/add',2,1,'',2,'添加内容'),(143,107,'编辑内容',NULL,1,'admin/Matter/edit',3,1,'',2,'编辑内容'),(144,107,'删除内容',NULL,1,'admin/Matter/mattdel',4,1,'',2,'删除内容'),(145,106,'回收站',NULL,1,'admin/Matter/matdellist',2,1,'',1,'回收站'),(146,145,'列表查看',NULL,1,'admin/Matter/matdellist',1,1,'',2,'列表查看'),(147,145,'回收站删除',NULL,1,'admin/Matter/matdelall',2,1,'',2,'回收站删除'),(148,145,'回收站还原',NULL,1,'admin/Matter/materhy',3,1,'',2,'回收站还原'),(149,106,'内容审核',NULL,1,'admin/Matter/mataudit',3,1,'',1,NULL),(150,149,'审核列表',NULL,1,'admin/Matter/mataudit',1,1,'',2,'审核列表'),(151,149,'审核操作',NULL,1,'admin/Matter/matcheck',2,1,'',2,'审核操作'),(152,149,'驳回操作',NULL,1,'admin/Matter/reject',3,1,'',2,'驳回操作'),(153,118,'模板在线编辑',NULL,1,'admin/Template/tempEdit',104,1,'',2,'模板在线编辑'),(155,149,'审核编辑',NULL,1,'admin/Matter/editaduit',4,1,'',2,'审核编辑'),(156,41,'上传插件',NULL,1,'admin/Plug/addonsup',4,1,'',2,'上传插件'),(157,41,'插件删除',NULL,1,'admin/Plug/addonsdel',5,1,'',2,'插件删除'),(158,41,'插件卸载',NULL,1,'admin/Plug/adonsupdate',5,1,'',2,'插件卸载'),(159,0,'附件管理','layui-icon-picture',1,NULL,105,1,'',0,'附件管理'),(160,159,'图片管理',NULL,1,'admin/Accessory/piclist',1,1,'',1,'图片管理'),(161,159,'文件管理',NULL,1,'admin/Accessory/filelist',2,1,'',1,'文件管理'),(162,160,'列表查看',NULL,1,'admin/Accessory/piclist',1,1,'',2,'列表查看'),(163,160,'图片删除',NULL,1,'admin/Accessory/picdel',2,1,'',2,'图片删除'),(164,161,'列表查看',NULL,1,'admin/Accessory/filelist',1,1,'',2,'列表查看'),(165,161,'文件删除',NULL,1,'admin/Accessory/filesdel',2,1,'',2,'文件删除'),(166,0,'第三方登录管理','layui-icon-key',1,NULL,601,1,'',0,'第三方登录管理'),(167,166,'登录配置',NULL,1,'admin/Oauth/index',602,1,'',1,'登录配置'),(168,167,'登录配置编辑',NULL,1,'admin/Oauth/indexEdit',603,1,'',2,'登录配置编辑'),(169,166,'登录设置',NULL,1,'admin/Oauth/setOauth',604,1,'',1,'登录设置'),(170,169,'登录设置编辑',NULL,1,'admin/Oauth/setOauthEdit',605,1,'',2,'登录设置编辑'),(171,84,'发送测试邮件',NULL,1,'admin/Sms/sendEmail',122,1,'',2,'发送测试邮件'),(172,94,'模型导出',NULL,1,'admin/Model/educemodl',10,1,'',2,'模型导出'),(173,94,'模型导入',NULL,1,'admin/Model/importdata',11,1,'',2,'模型导入'),(174,84,'短信发送测试',NULL,1,'admin/Sms/testSmsbao',122,1,'',2,'短信发送测试'),(175,117,'自定义单页',NULL,1,'admin/Template/customform',2,1,'',1,'自定义单页'),(176,175,'单页列表',NULL,1,'admin/Template/customform',1,1,'',2,'单页列表'),(177,175,'新增单页',NULL,1,'admin/Template/cutformadd',2,1,'',2,'新增单页'),(178,175,'编辑单页',NULL,1,'admin/Template/cutformedit',3,1,'',2,'编辑单页'),(179, 40, '应用中心', NULL, 1, 'admin/Plug/appshop', 4, 1, '', 1, '应用中心'),(180, 179, '应用列表', NULL, 1, 'admin/Plug/appshop', 1, 1, '', 2, '应用列表');

CREATE TABLE `#__shop` (
  `id` int(11) NOT NULL COMMENT 'id',
  `title` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '标题',
  `ftitle` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '副标题',
  `titlepic` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '标题图片',
  `mid` int(11) DEFAULT NULL COMMENT '所属栏目',
  `uid` int(11) NOT NULL COMMENT '关联会员id',
  `type` int(11) DEFAULT NULL COMMENT '所属分类',
  `keyword` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '关键词',
  `abstract` text CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT '摘要',
  `tag` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '标签',
  `author` varchar(10) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '发布者',
  `editor` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '内容',
  `price` tinyint(2) NOT NULL DEFAULT '0' COMMENT '阅读类型 1免费 2付费 3vip免费 4vip折扣',
  `moneys` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '付费金额',
  `status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '发布状态 0发布 1草稿 2下架 3待审核 4驳回',
  `orders` int(11) NOT NULL DEFAULT '1' COMMENT '排序',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) NOT NULL COMMENT '更新时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  `comment` tinyint(2) NOT NULL DEFAULT '0' COMMENT '是否允许评论 0允许 1不允许',
  `refusal` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '驳回原因',
  `top` tinyint(2) NOT NULL DEFAULT '0' COMMENT '置顶 0不置顶 1置顶',
  `ppts` tinyint(2) NOT NULL DEFAULT '0' COMMENT '标题图片幻灯 0不幻灯 1幻灯',
  `isadmin` tinyint(2) NOT NULL DEFAULT '0' COMMENT '是否管理员发布 0否 1是',
  `likes_ip` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '当前文章点过赞的ip',
  `productno` varchar(30) DEFAULT NULL,
  `pbrand` varchar(30) DEFAULT NULL,
  `unit` varchar(16) DEFAULT NULL,
  `tprice` float(11,2) DEFAULT NULL,
  `psalenum` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `#__shop_data` (
  `aid` int(11) NOT NULL COMMENT '关联文章id',
  `browse` int(11) NOT NULL DEFAULT '0' COMMENT '文章浏览次数',
  `likes` int(11) NOT NULL DEFAULT '0' COMMENT '文章点赞次数',
  `comment_t` int(200) NOT NULL DEFAULT '0' COMMENT '文章评论总计',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`aid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `#__sms` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '邮箱配置识别',
  `email` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '邮箱发信账号 ',
  `sll` int(11) DEFAULT '25' COMMENT '发信sll',
  `emailpaswsd` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '发信秘钥',
  `smtp` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '发信smtp',
  `smsbao_account` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '短信宝账号',
  `smsbao_password` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '短信宝密码',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='邮箱发信配置表';

INSERT INTO `#__sms` VALUES (1,'123456@qq.com',465,'','smtp.qq.com','','d41d8cd98f00b204e9800998ecf8427e');

CREATE TABLE `#__system` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `title` varchar(255) NOT NULL COMMENT '网站标题',
  `ftitle` varchar(100) NOT NULL COMMENT '网站副标题',
  `keyword` varchar(255) NOT NULL COMMENT '网站关键字',
  `descri` varchar(255) NOT NULL COMMENT '网站描述',
  `is_close` tinyint(2) NOT NULL DEFAULT '0' COMMENT '网站是否关闭1:关闭0:开启',
  `tg_close` tinyint(2) NOT NULL DEFAULT '0' COMMENT '在线投稿开关（0为开启，1为关闭）',
  `us_tg` tinyint(4) NOT NULL DEFAULT '0' COMMENT '游客投稿控制',
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
  `logo` text COMMENT '网站LOGO',
  `ico` text COMMENT '网站ICO',
  `adminqq` int(20) DEFAULT NULL COMMENT '站长QQ',
  `adminemail` varchar(50) DEFAULT NULL COMMENT '站长邮箱',
  `home_temp` varchar(100) DEFAULT NULL COMMENT '当前使用的模板',
  `member_temp` varchar(100) DEFAULT NULL COMMENT '会员中心模板',
  `version` float NOT NULL DEFAULT '1' COMMENT '系统版本号',
  `accre` varchar(255) DEFAULT NULL COMMENT '授权码',
  `accrest` tinyint(4) NOT NULL DEFAULT '2' COMMENT '授权状态',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='网站配置表';

INSERT INTO `#__system` VALUES (1,'创建成功!欢迎使用MuYuCMS','轻量级开源CMS!','木鱼,MuYuCMS,cms,开源cms,免费开源cms,轻量级cms,cms系统,cms下载,企业cms,内容管理系统,cms建站系统','木鱼CMS基于Thinkphp开发的一套轻量级开源内容管理系统,专注为公司企业、个人站长提供快速建站提供解决方案。2',0,0,0,0,0,0,0,5,1,5,'很好|垃圾|色情|AV|看片|','京ICP备00000000号221','Copyright 2020 木鱼内容管理系统 All Rights Reserved','&lt;script&gt;\r\nvar _hmt = _hmt || [];\r\n(function() {\r\n  var hm = document.createElement(&quot;script&quot;);\r\n  hm.src = &quot;https://hm.baidu.com/hm.js?ea62eb580986c26501fbbb418bbce03f&quot;;\r\n  var s = document.getElementsByTagName(&quot;script&quot;)[0]; \r\n  s.parentNode.insertBefore(hm, s);\r\n})();\r\n&lt;/script&gt;','','/public/upload/images/logo.png','/public/upload/menubg/609f2b46860d9.jpg',123456789,'admin@muyucms.com','muyu_duoguyu','user',2.1);

CREATE TABLE `#__system_upset` (
  `id` int(11) NOT NULL COMMENT '标识',
  `imageext` varchar(255) NOT NULL DEFAULT 'jpg,jpeg,png' COMMENT '图片类型',
  `imagesize` int(11) NOT NULL DEFAULT '1048576' COMMENT '图片大小',
  `fileext` varchar(255) NOT NULL DEFAULT 'zip,rar,7z' COMMENT '附件类型',
  `filesize` int(11) NOT NULL DEFAULT '3145728' COMMENT '附件大小',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='后台配置上传参数表';

INSERT INTO `#__system_upset` VALUES (1,'jpg,jpeg,png',1048576,'zip,rar,7z',31457281);

CREATE TABLE `#__tools` (
  `id` int(11) NOT NULL COMMENT 'id',
  `title` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '标题',
  `ftitle` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '副标题',
  `titlepic` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '标题图片',
  `mid` int(11) DEFAULT NULL COMMENT '所属栏目',
  `uid` int(11) NOT NULL COMMENT '关联会员id',
  `type` int(11) DEFAULT NULL COMMENT '所属分类',
  `keyword` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '关键词',
  `abstract` text CHARACTER SET utf8 COLLATE utf8_unicode_ci COMMENT '摘要',
  `tag` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '标签',
  `author` varchar(10) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '发布者',
  `editor` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT '内容',
  `price` tinyint(2) NOT NULL DEFAULT '0' COMMENT '阅读类型 1免费 2付费 3vip免费 4vip折扣',
  `moneys` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '付费金额',
  `status` tinyint(2) NOT NULL DEFAULT '0' COMMENT '发布状态 0发布 1草稿 2下架 3待审核 4驳回',
  `orders` int(11) NOT NULL DEFAULT '1' COMMENT '排序',
  `create_time` int(11) NOT NULL COMMENT '创建时间',
  `update_time` int(11) NOT NULL COMMENT '更新时间',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  `comment` tinyint(2) NOT NULL DEFAULT '0' COMMENT '是否允许评论 0允许 1不允许',
  `refusal` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '驳回原因',
  `top` tinyint(2) NOT NULL DEFAULT '0' COMMENT '置顶 0不置顶 1置顶',
  `ppts` tinyint(2) NOT NULL DEFAULT '0' COMMENT '标题图片幻灯 0不幻灯 1幻灯',
  `isadmin` tinyint(2) NOT NULL DEFAULT '0' COMMENT '是否管理员发布 0否 1是',
  `likes_ip` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '当前文章点过赞的ip',
  `url` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `#__tools_data` (
  `aid` int(11) NOT NULL COMMENT '关联文章id',
  `browse` int(11) NOT NULL DEFAULT '0' COMMENT '文章浏览次数',
  `likes` int(11) NOT NULL DEFAULT '0' COMMENT '文章点赞次数',
  `comment_t` int(200) NOT NULL DEFAULT '0' COMMENT '文章评论总计',
  `delete_time` int(11) DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`aid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `#__type` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '分类id',
  `pid` int(11) DEFAULT NULL COMMENT '分类所属栏目id',
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT '分类名称',
  `titlepic` text COLLATE utf8_unicode_ci COMMENT '分类背景或ico图片',
  `orders` int(11) DEFAULT '0' COMMENT '分类排序',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '分类状态 0停用 1启用',
  `create_time` int(11) DEFAULT NULL COMMENT '分类创建时间',
  `update_time` int(11) DEFAULT NULL COMMENT '分类更新时间',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='分类存储表';
