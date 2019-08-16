<?php defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Welcome to CodeIgniter</title>
	<style type="text/css">
	#container {
		text-align: center;
	}
	</style>
</head>
<body>
<div id="container">
	<h1>CodeIgniter API Library V.1.1.7</h1>
	<p>This extension is powered by <b>Jeevan Lal</b>.</p>
	<p class="footer">Page rendered in <strong>{elapsed_time}</strong> seconds. <?php echo  (ENVIRONMENT === 'development') ?  'CodeIgniter Version <strong>' . CI_VERSION . '</strong>' : '' ?></p>
</div>
<hr>
<a href="https://www.getpostman.com/downloads/">Download Postman for API check</a><br>
<a href="https://chrome.google.com/webstore/detail/postman/fhbjgbiflinjbdggehcddcbncdddomop?hl=en">Download Postman Chrome App</a>
<hr><h2>Postman Image</h2>
<img src="<?= base_url("assets/image-demo1.png"); ?>" style="width: 100%;" alt="Image">
<br><br>
<hr><h2>Demo API Routes</h2>
<ul>
	<li>{base_url} /api/user/demo</li>
	<li>{base_url} /api/user/login</li>
	<li>{base_url} /api/user/view</li>
</ul>
</body>
</html>