<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title><?php echo $html_title;?></title>
<link href="css/install.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="js/jquery.js"></script>
<link href="css/perfect-scrollbar.min.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="js/perfect-scrollbar.min.js"></script>
<script type="text/javascript" src="js/jquery.mousewheel.js"></script>
</head>
<body>
<?php echo $html_header;?>
<div class="main">
  <div class="final-succeed"> <span class="ico"></span>
    <h2>程序已成功安装</h2>
    <h5>选择您要进入的页面</h5>
  </div>
  <div class="final-site-nav">
    <div class="arrow"></div>
    <ul>
      <li class="shop">
        <div class="ico"></div>
        <h5><a href="<?php echo $auto_site_url;?>" target="_blank">首页</a></h5>
        <h6>系统首页</h6>
      </li>
      
      <li class="admin">
        <div class="ico"></div>
        <h5><a href="<?php echo $auto_site_url;?>admin.php" target="_blank">系统管理</a></h5>
        <h6>系统后台</h6>
      </li>
    </ul>
  </div>
  <div class="final-intro">
    <p><strong>系统管理默认地址:&nbsp;</strong><a href="<?php echo $auto_site_url;?>admin.php" target="_blank"><?php echo $auto_site_url;?>admin.php</a><strong>为了安全,请立即在后台安全配置修改默认后台管理入口</strong></p>
    <p><strong>网站首页默认地址:&nbsp;</strong><a href="<?php echo $auto_site_url;?>" target="_blank"><?php echo $auto_site_url;?></a></p>
  </div>
</div>
<div class="footer">
  <h5>Powered by <font class="blue">MuYuCMS</font><font class="orange"></font></h5>
  <h6>版权所有 2020-<?php echo date('Y');?> &copy; <a href="http://www.muyucms.com" target="_blank">MuYuCMS</a></h6>
</div>
<script type="text/javascript">
$(document).ready(function(){
    //自定义滚定条
    $('#text-box').perfectScrollbar();
});
</script>
</body>
</html>
