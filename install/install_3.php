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
				<h2>正在安装 - MuYuCMS-后台管理系统</h2>	
				<h1 class="muyuh1">step-3</h1>
				</div>
				<hr style="background: #06a3e2a1;">
		<div class="insmain mys">
			<div tyle="text-align: center;">
		    <pre class="cont" readonly="readonly" id="xieyi">
				<p>正在准备初始数据......</p>
		    </pre>
		    </div>
		</div>		
				
				
		<div style="text-align: right;padding-right: 10px;">
			<button class="layui-btn layui-btn-danger" onclick="javascript :self.location=document.referrer;">上一步</button>
			<a class="layui-btn layui-btn-normal" href="#" id="submit">下一步</a>
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
	var scroll_height = 0;
	function showmessage(message) {
	    document.getElementById('xieyi').innerHTML += "<p>"+message+"</p><br/>";
	    document.getElementById("xieyi").scrollTop = 500+scroll_height;
	    scroll_height += 40;
	}
  </script>
</body>
</html>