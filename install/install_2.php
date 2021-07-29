<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="utf-8">
  <title>在线安装引导 - 木鱼内容管理系统</title>
  <meta name="author" content="MuYuCMS leK" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <link href="../public/static/admin/static/layuiadmin/layui/css/layui.css" rel="stylesheet" type="text/css"/>
  <link href="style/muyuloging/style.css" rel="stylesheet" type="text/css"/>
</head>
<body>
	<div class="loging">
		<div class="loging-fom">
			<form class="layui-form" action="" method="post">
				<input type="hidden" value="submit" name="submitform">
				<input type="hidden" value="<?php echo $install_recover;?>" name="install_recover">
				<div class="layui-form-item" style="color: #4E5465;">
				<h2>安装信息配置 - MuYuCMS-后台管理系统</h2>	
				<h1 class="muyuh1">step-2</h1>
				</div>
				<hr style="background: #06a3e2a1;">
		<fieldset class="layui-elem-field layui-field-title" style="margin-top: 10px;">
		  <legend>数据库配置</legend>
		</fieldset>	
		<div style="float: left;width: 100%;text-align: center;margin: 0 0 10px 0;">
			<span style="color: red;" id="errors"><?php echo $install_error;?></span>
		</div>
		<div class="layui-form-item">
		    <label class="layui-form-label" style="width: 120px;">数据库服务器:</label>
		    <div class="layui-input-inline">
		    <input type="text" name="db_host" value="<?php echo $_POST['db_host'] ? $_POST['db_host'] : 'localhost';?>" required lay-verify="required" placeholder="请输入数据库服务器" autocomplete="off" class="layui-input">
		</div>
			<div class="layui-form-mid layui-word-aux">数据库服务器地址，一般为localhost</div>
		</div>
		<div class="layui-form-item">
		    <label class="layui-form-label" style="width: 120px;">数据库端口:</label>
		    <div class="layui-input-inline">
		    <input type="text" name="db_port" value="<?php echo $_POST['db_port'] ? $_POST['db_port'] : '3306';?>" required lay-verify="required" placeholder="请输入数据库端口" autocomplete="off" class="layui-input">
		</div>
			<div class="layui-form-mid layui-word-aux">数据库默认端口一般为3306</div>
		</div>
		<div class="layui-form-item">
		    <label class="layui-form-label" style="width: 120px;">数据库名:</label>
		    <div class="layui-input-inline">
		    <input type="text" name="db_name" value="<?php echo $_POST['db_name'] ? $_POST['db_name'] : 'root';?>" required lay-verify="required" placeholder="请输入数据库名" autocomplete="off" class="layui-input">
		</div>
			<div class="layui-form-mid layui-word-aux"></div>
		</div>
		<div class="layui-form-item">
		    <label class="layui-form-label" style="width: 120px;">数据库用户名:</label>
		    <div class="layui-input-inline">
		    <input type="text" name="db_user" value="<?php echo $_POST['db_user'] ? $_POST['db_user'] : 'root';?>" required lay-verify="required" placeholder="请输入数据库用户名" autocomplete="off" class="layui-input">
		</div>
			<div class="layui-form-mid layui-word-aux"></div>
		</div>
		<div class="layui-form-item">
		    <label class="layui-form-label" style="width: 120px;">数据库密码:</label>
		    <div class="layui-input-inline">
		    <input type="text" name="db_pwd" value="<?php echo $_POST['db_pwd'] ? $_POST['db_pwd'] : '';?>" required lay-verify="required" placeholder="请输入数据库密码" autocomplete="off" class="layui-input">
		</div>
			<div class="layui-form-mid layui-word-aux"></div>
		</div>
		<div class="layui-form-item">
		    <label class="layui-form-label" style="width: 120px;">数据库表前缀:</label>
		    <div class="layui-input-inline">
		    <input type="text" name="db_prefix" value="<?php echo $_POST['db_prefix'] ? $_POST['db_prefix'] : 'muyu_';?>" required lay-verify="required" placeholder="请设置表前缀" autocomplete="off" class="layui-input">
		</div>
			<div class="layui-form-mid layui-word-aux">同一数据库运行多个程序时，请修改前缀</div>
		</div>
		<fieldset class="layui-elem-field layui-field-title" style="margin-top: 10px;">
		  <legend>是否安装测试数据</legend>
		</fieldset>	
		<div class="layui-form-item">
		    <label class="layui-form-label"></label>
		    <div class="layui-input-block">
		      <input type="radio" name="demo_data" value="0" title="全新安装" <?php echo ($_POST['demo_data']!==1 ? 'checked':'');?>>
				<?php if ($demo_data) {?>
		      <input type="radio" name="demo_data" value="1" title="安装测试数据" <?php echo ($_POST['demo_data']==1 ? 'checked':'');?>>
				<?php }?>
		    </div>
		  </div>
		<div class="layui-form-item">
		    <label class="layui-form-label" style="width: 120px;">授权码:</label>
		    <div class="layui-input-inline">
		    <input type="text" name="accre" value="<?php echo $_POST['accre'] ? $_POST['accre'] : '';?>" placeholder="输入您的授权码" autocomplete="off" class="layui-input">
		</div>
			<div class="layui-form-mid layui-word-aux">没有可忽略,<a href="http://www.muyucms.com">立即授权</a>享至尊服务!</div>
		</div>  
		<fieldset class="layui-elem-field layui-field-title" style="margin-top: 10px;">
		  <legend>网站基础信息</legend>
		</fieldset>
		<div class="layui-form-item">
		    <label class="layui-form-label" style="width: 120px;">网站名称:</label>
		    <div class="layui-input-inline">
		    <input type="text" name="site_name" value="<?php echo $_POST['site_name'];?>" placeholder="设置一个牛B普拉斯的名称吧" autocomplete="off" class="layui-input">
		</div>
			<div class="layui-form-mid layui-word-aux">可留空安装后在系统设置修改</div>
		</div>
		<div class="layui-form-item">
		    <label class="layui-form-label" style="width: 120px;">管理员账号:</label>
		    <div class="layui-input-inline">
		    <input type="text" name="admin" value="<?php echo $_POST['admin'];?>" required lay-verify="required|adname" placeholder="请设置管理员账号" autocomplete="off" class="layui-input">
		</div>
			<div class="layui-form-mid layui-word-aux"></div>
		</div>
		<div class="layui-form-item">
		    <label class="layui-form-label" style="width: 120px;">管理员密码:</label>
		    <div class="layui-input-inline">
		    <input type="text" name="password" id="password" value="<?php echo $_POST['password'];?>" required lay-verify="required|adpass" placeholder="请设置一个密码" autocomplete="off" class="layui-input">
		</div>
			<div class="layui-form-mid layui-word-aux">管理员密码不少于6个字符</div>
		</div>
		<div class="layui-form-item">
		    <label class="layui-form-label" style="width: 120px;">确认密码:</label>
		    <div class="layui-input-inline">
		    <input type="text" name="rpassword" value="<?php echo $_POST['rpassword'];?>" required lay-verify="required|adpass|repass" placeholder="请再次确认密码" autocomplete="off" class="layui-input">
		</div>
			<div class="layui-form-mid layui-word-aux">确保两次输入的密码一致</div>
		</div>
		
		<div style="float: left;">
			<span style="color: red;" id="errors"></span>
		</div>
		<div style="text-align: right;padding-right: 10px;">
			<button class="layui-btn layui-btn-danger" onclick="javascript :self.location=document.referrer;">上一步</button>
			<button class="layui-btn layui-btn-normal" lay-submit lay-filter="nextins" id="submit">下一步</button>
		</div>
		</form>
		</div>
	</div>
	<div style="height: 46px;line-height: 46px;text-align: center;color: #000;font-size: 12px;position: absolute;left: 0;right: 0;z-index: 0;">Copyright &copy;2020-<?php echo date('Y');?> MuYuCMS内容管理系统 All Rights Reserved.</p></div>
<script type="text/javascript" src="../public/static/admin/static/layuiadmin/layui/layui.js"></script>
<script>
	layui.use('form', function(){
	  var $ = layui.$
	  ,form = layui.form
	  ,layer = layui.layer;
	  form.verify({
	    adname: function(value, item){ //value：表单的值、item：表单的DOM对象
	      if(!new RegExp("^[a-zA-Z0-9_\u4e00-\u9fa5\\s·]+$").test(value)){
	        return '用户名不能有特殊字符';
	      }
	      if(/(^\_)|(\__)|(\_+$)/.test(value)){
	        return '用户名首尾不能出现下划线\'_\'';
	      }
	      if(/^\d+\d+\d$/.test(value)){
	        return '用户名不能全为数字';
	      }
	    }
	    ,adpass: [
	      /^[\S]{6,12}$/
	      ,'密码必须6到12位，且不能出现空格'
	    ]
		,repass: function(value){
		var repassvalue = $('#password').val();
		if(value != repassvalue){
		return '两次输入的密码不一致!';
		}
		}
	  });
	});
  </script>
</body>
</html>