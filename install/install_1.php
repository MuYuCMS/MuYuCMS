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
			<form class="layui-form" action="" method="get">
			<input type="hidden" name="step" value="2">
				<div class="layui-form-item" style="color: #4E5465;">
				<h2>环境及权限检查 - MuYuCMS-后台管理系统</h2>	
				<h1 class="muyuh1">step-1</h1>
				</div>
				<hr style="background: #06a3e2a1;">
		
		<table class="layui-table">
			<caption>
			环境检查
			</caption>
		    <tr>
		      <th>项目</th>
		      <th>程序所需</th>
		      <th>最佳配置推荐</th>
			  <th>当前服务器</th>
		    </tr> 
			<?php foreach($env_items as $v){?>
		    <tr>
		      <td><?php echo $v['name'];?></td>
		      <td><?php echo $v['min'];?></td>
		      <td><?php echo $v['good'];?></td>
			  <td><span class="<?php echo $v['status'] ? 'yes' : 'no';?>"><i></i><?php echo $v['cur'];?></span></td>
		    </tr>
			<?php }?>
		</table>
		<table class="layui-table">
			<caption>
			目录检查
			</caption>
		    <tr>
		      <th>目录文件</th>
		      <th>所需状态</th>
		      <th>当前状态</th>
		    </tr> 
			<?php foreach($dirfile_items as $k => $v){?>
		    <tr>
		      <td><?php echo $v['path'];?></td>
		      <td><span>可写</span></td>
			  <td><span class="<?php echo $v['status'] == 1 ? 'yes' : 'no';?>"><i></i><?php echo $v['status'] == 1 ? '可写' : '不可写';?></span></td>
		    </tr>
			<?php }?>
		</table>
		<table class="layui-table">
			<caption>
			函数检查
			</caption>
		    <tr>
		      <th>函数名称</th>
		      <th>所需状态</th>
		      <th>当前状态</th>
		    </tr> 
			<?php foreach($func_items as $k =>$v){?>
		    <tr>
		      <td><?php echo $v['name'];?>()</td>
		      <td><span>支持</span></td>
			  <td><span class="<?php echo $v['status'] == 1 ? 'yes' : 'no';?>"><i></i><?php echo $v['status'] == 1 ? '支持' : '不支持';?></span></td>
		    </tr>
			<?php }?>
		</table>
		<div style="float: left;">
			<span style="color: red;" id="errors"></span>
		</div>
		<div style="text-align: right;padding-right: 10px;">
			<a class="layui-btn layui-btn-danger" onclick="javascript:self.location=document.referrer;">上一步</a>
			<button class="layui-btn layui-btn-normal layui-btn-disabled" lay-submit lay-filter="nextins" id="submit">下一步</button>
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
	  $('#submit').addClass("layui-btn-disabled").attr("disabled",true);
	  if (typeof($('.no').html()) == 'undefined'){
		  $("#submit").removeClass('layui-btn-disabled').attr("disabled",false);
	  }else{
	      document.getElementById("errors").innerHTML = $('.no').eq(0).parent().parent().find('td:first').html()+"未通过检测";
	      $('#submit').addClass("layui-btn-disabled").attr("disabled",true);
	  }
	});
  </script>
</body>
</html>