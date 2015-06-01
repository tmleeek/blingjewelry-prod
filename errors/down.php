<?php
header('HTTP/1.1 503 Service Temporarily Unavailable');
header('Status: 503 Service Temporarily Unavailable');
header('Retry-After: 10800');//300 seconds
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="icon" href="http://media.blingjewelry.com/media/favicon/default/favicon.ico" type="image/x-icon" />
<link rel="shortcut icon" href="http://media.blingjewelry.com/media/favicon/default/favicon.ico" type="image/x-icon" />
<title>We will be right back!</title>
<link rel="stylesheet" type="text/css" href="http://skin.blingjewelry.com/skin/frontend/ultimo/default/css/custom.css" media="all" />
<link href='//fonts.googleapis.com/css?family=Lato&subset=latin,latin-ext' rel='stylesheet' type='text/css' />
<style type="text/css">
body p {
	text-align: center;
	font-weight: bold;
}
</style>
</head>

<body>
<p><img src="http://skin.blingjewelry.com/skin/frontend/ultimo/default/images/site-down.jpg" />
  <div>
</p>
<!--<p>We are currently adding more bling to our site. We will be back shortly....</p>-->
</body>
</html>
