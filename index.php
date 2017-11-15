<?php
if (!session_id()) {
    session_start();
}
if (isset($_SESSION['fb_access_token'] )) {
	header('location: http://'.$_SERVER['HTTP_HOST'].'/fbd/profile.php');
	exit;
}
?>
<html lang="en">
<head>
	<title>Facebook - Downloader</title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<script type="application/x-javascript"> addEventListener("load", function() { setTimeout(hideURLbar, 0); }, false);
	function hideURLbar(){ window.scrollTo(0,1); } </script>
	
	<link rel="stylesheet" href="libs/css/style_login.css" type="text/css" media="all" /> 
	<link rel="stylesheet" href="libs/css/font-awesome.min.css" />
	<link rel="shortcut icon" href="libs/images/ico/favicon.png">

</head>
<body>
	<section class="agile-main">
		<div class="agile-head">
			<h1 style="color : white;">The Vende</h1>
			<p style="color : white;">Download Facebook album with one click and Backup with Google Drive.</p>
		</div>
		<div class="agile-icon">
			<span><i class="fa fa-hand-o-down" aria-hidden="true"></i></span>
		</div>
		<br/><br/><br/>

		<div class="btn_center">
			<?php
				include 'fb_config.php';
				$helper = $fb->getRedirectLoginHelper();
				$permissions = array(
					'email',
					'user_photos'
				);
				$loginUrl = $helper->getLoginUrl('http://'.$_SERVER['HTTP_HOST'].'/fbd/profile.php', $permissions);
			?>
			<a href="<?php echo $loginUrl; ?>" class="button" style="vertical-align:middle"><span>Login With Facebook! </span></a>
		</div>
		
		<br/><br/>
		
		<div class="agile-social">
			<ul class="social-icons">
				<li><a href="https://www.facebook.com/bansi.nakhuva"><i class="fa fa-facebook" aria-hidden="true"></i></a></li>
				<li><a href="https://twitter.com/Bnakhuva"><i class="fa fa-twitter" aria-hidden="true"></i></a></li>
				<li><a href="https://plus.google.com/+bansinakhuva"><i class="fa fa-google-plus" aria-hidden="true"></i></a></li>
			</ul>
		</div>
		<div class="agile-copyright">
			<footer><p style="background : black;">&copy; <script type="text/javascript"> document.write(new Date().getFullYear()); </script> The Vende. All Rights Reserved. Design And Developed by <a href="https://technoupdt.blogspot.in/" target="blank">Bansi Nakhuva</a></p></footer>
		</div>
	</section>
</body>
</html>