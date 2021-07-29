{__NOLAYOUT__}<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>信息提示</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta charset="utf-8">
<meta name="keywords" content="" />
<link href="../../public/static/admin/static/muyuadmin/css/404css/font-awesome.min.css" rel="stylesheet" type="text/css" media="all">
<link href="../../public/static/admin/static/muyuadmin/css/404css/style.css" rel="stylesheet" type="text/css" media="all"/>
<link href="http://fonts.googleapis.com/css?family=Raleway:400,500,600,700,800,900" rel="stylesheet">
</head>
<body>
<div class="error_main">
	<div class="content">
	    <?php switch ($code) {?>
            <?php case 1:?>
            <h1>温馨提示</h1>
            <?php break;?>
            <?php case 0:?>
            <h1>你怎么总是出错</h1>
            <?php break;?>
        <?php } ?>
			<div class="error_content">
				<span class="fa fa-frown-o"></span>
				
				<h2><?php echo(strip_tags($msg));?></h2>
				
				<p>页面自动 <a id="href" href="<?php echo($url);?>">跳转</a> 等待时间： <b id="wait"><?php echo($wait);?></b></p>
				<a class="b-home" href="<?php echo(input('server.REQUEST_SCHEME').'://'.input('server.SERVER_NAME'));?>">返回首页</a>
			</div>
		<div class="footer">
		 <p>Copyright &copy; <a target="_blank" href="http://www.muyucms.com">MuYuCMS</a></p>
		</div>
	</div>
	
</div>
    <script type="text/javascript">
        (function(){
            var wait = document.getElementById('wait'),
                href = document.getElementById('href').href;
            var interval = setInterval(function(){
                var time = --wait.innerHTML;
                if(time <= 0) {
                    location.href = href;
                    clearInterval(interval);
                };
            }, 1000);
        })();
    </script>
</body>
</html>