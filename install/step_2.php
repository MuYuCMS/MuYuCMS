<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title><?php echo $html_title;?></title>
<link href="css/install.css" rel="stylesheet" type="text/css">
<script src="js/jquery.js"></script>
<script src="js/jquery.icheck.min.js"></script>
<script>
$(document).ready(function(){
  $('input[type="radio"]').on('ifChecked', function(event){
    if(this.id == 'radio-0'){
            $('.select-module').show();
        }else{
            $('.select-module').hide();
        }
  }).iCheck({
    checkboxClass: 'icheckbox_flat-green',
    radioClass: 'iradio_flat-green'
  });
  $('input[type="checkbox"]').iCheck({
    checkboxClass: 'icheckbox_flat-green',
    radioClass: 'iradio_flat-green'
  });
    $('#next').click(function(){
        if ($('#cms').attr('checked')){
            $('#install_form').submit();
        }else{
            alert('CMS必须安装');
        }
    });
});
</script>
</head>

<body>
<?php ECHO $html_header;?>
<div class="main">
  <div class="step-box" id="step2">
    <div class="text-nav">
      <h1>Step.2</h1>
      <h2>选择安装方式</h2>
      <h5>根据需要选择系统模块完全或手动安装</h5>
    </div>
    <div class="procedure-nav">
      <div class="schedule-ico"><span class="a"></span><span class="b"></span><span class="c"></span><span class="d"></span></div>
      <div class="schedule-point-now"><span class="a"></span><span class="b"></span><span class="c"></span><span class="d"></span></div>
      <div class="schedule-point-bg"><span class="a"></span><span class="b"></span><span class="c"></span><span class="d"></span></div>
      <div class="schedule-line-now"><em></em></div>
      <div class="schedule-line-bg"></div>
      <div class="schedule-text"><span class="a">检查安装环境</span><span class="b">选择安装方式</span><span class="c">创建数据库</span><span class="d">安装</span></div>
    </div>
  </div>
  <form method="get" id="install_form" action="index.php">
  <input type="hidden" value="3" name="step">
    <div class="select-install">
      <label>
      <input type="radio" name="iCheck" value="full" id="radio-1" class="green-radio" checked >
      <h4>完全安装 MuYuCMS</h4>
      <h5>系统</h5>
      </label>
    </div>
<div class="select-module" id="result" style="display: none;">
	<div class="arrow"></div>
	<ul>
		<li class="cms">
			<input type="checkbox" name="cms" id="cms" value="1" checked="checked" >
			<div class="ico"></div>
			<h4>内容管理</h4>
			<p>内容管理是本程序现有的主要模块，后续将会进行其他更多样化的拓展...</p>
		</li>
	</ul>
</div>
    <div class="btn-box"><a href="index.php?step=1" class="btn btn-primary">上一步</a><a id="next" href="javascript:void(0);" class="btn btn-primary">下一步</a></div>
  </form>
</div>
<div class="footer">
  <h5>Powered by <font class="blue">MuYuCMS</font><font class="orange"></font></h5>
  <h6>版权所有 2020-<?php echo date('Y');?> &copy; <a href="http://www.muyucms.com" target="_blank">MuYuCMS</a></h6>
</div>
</body>
</html>
