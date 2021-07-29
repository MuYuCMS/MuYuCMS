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
			<form class="layui-form">
				<div class="layui-form-item" style="color: #4E5465;">
				<h2>安装完成 - MuYuCMS-后台管理系统</h2>	
				<h1 class="muyuh1">step-4</h1>
				</div>
				<hr style="background: #06a3e2a1;">
		<div class="insmain mys">
			<div tyle="text-align: center;">
		    <img src="/img/succes.png" alt="">
			<p style="text-align: left;color: red;">*<strong>系统管理默认地址:&nbsp;</strong><a href="<?php echo $auto_site_url;?>admin.php" target="_blank"><?php echo $auto_site_url;?>admin.php</a><strong>为了安全,请立即在后台安全配置修改默认后台管理入口</strong></p>
			<p style="text-align: left;"><strong>网站首页默认地址:&nbsp;</strong><a href="<?php echo $auto_site_url;?>" target="_blank"><?php echo $auto_site_url;?></a></p>
		    </div>
		</div>		
				
				
		<div style="text-align: center;padding-right: 10px;">
			<a class="layui-btn" href="<?php echo $auto_site_url;?>">前往首页</a>
			<a class="layui-btn layui-btn-normal" href="<?php echo $auto_site_url;?>admin.php" id="submit">管理后台</a>
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
	  
	  
	});
  </script>
</body>
</html>