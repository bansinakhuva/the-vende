<?php
if (!session_id()) {
    session_start();
}

include 'fb_config.php';

$helper = $fb->getRedirectLoginHelper();

if(isset($_GET['logout']))
{
	$fbLogoutUrl = $helper->getLogoutUrl($_SESSION['fb_access_token'], 'http://'.$_SERVER['HTTP_HOST'].'/fbd/index.php');    
	session_destroy();
	unset($_SESSION['access_token']);
	header("Location: $fbLogoutUrl");
	exit;
}

if (!isset($_SESSION['fb_access_token'] )) {
	
	try {
		$accessToken = $helper->getAccessToken();
	} catch(Facebook\Exceptions\FacebookResponseException $e) {
		// When Graph returns an error
		echo 'Graph returned an error: ' . $e->getMessage();
		exit;
	} catch(Facebook\Exceptions\FacebookSDKException $e) {
		// When validation fails or other local issues
		echo 'Facebook SDK returned an error: ' . $e->getMessage();
		exit;
	}

	if (! isset($accessToken)) {
		if ($helper->getError()) {
			header('HTTP/1.0 401 Unauthorized');
			echo "Error: " . $helper->getError() . "\n";
			echo "Error Code: " . $helper->getErrorCode() . "\n";
			echo "Error Reason: " . $helper->getErrorReason() . "\n";
			echo "Error Description: " . $helper->getErrorDescription() . "\n";
		} else {
			header('HTTP/1.0 400 Bad Request');
			echo 'Bad request';
		}
		exit;
	}

	// The OAuth 2.0 client handler helps us manage access tokens
	$oAuth2Client = $fb->getOAuth2Client();

	// Get the access token metadata from /debug_token
	$tokenMetadata = $oAuth2Client->debugToken($accessToken);

	// Validation (these will throw FacebookSDKException's when they fail)
	$tokenMetadata->validateAppId('440948386100123');
	$tokenMetadata->validateExpiration();

	if (! $accessToken->isLongLived()) {
		// Exchanges a short-lived access token for a long-lived one
		try {
			$accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
		} catch (Facebook\Exceptions\FacebookSDKException $e) {
			echo "<p>Error getting long-lived access token: " . $e->getMessage() . "</p>\n\n";
			exit;
		}
	}

	$_SESSION['fb_access_token'] = (string) $accessToken;
}
?>
<!DOCTYPE html>
<!--[if lt IE 7]> <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang="en"> 
<![endif]-->
<!--[if IE 7]> <html class="no-js lt-ie9 lt-ie8" lang="en"> 
<![endif]-->
<!--[if IE 8]> <html class="no-js lt-ie9" lang="en"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
<head>

	<title>The Vende</title>
    <meta name="keywords" content="" />
	<meta name="description" content="" />

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">

    <link href='https://fonts.googleapis.com/css?family=Open+Sans:400italic,600italic,700italic,400,600,700,800' rel='stylesheet' type='text/css'>

	<!-- CSS Bootstrap & Custom -->
    <link rel="stylesheet" href="libs/css/bootstrap.min.css">
    <link rel="stylesheet" href="libs/css/animate.css">
    <link rel="stylesheet" href="libs/css/font-awesome.min.css">
	<link rel="stylesheet" href="libs/css/templatemo_misc.css">

	<!-- Main CSS -->
	<link rel="stylesheet" href="libs/css/templatemo_style.css">

	<!-- Favicons -->
    <link rel="shortcut icon" href="libs/images/ico/favicon.png">
	
	<style>
	.chkchecked{
		background-color: #4CAF50;
	}
	</style>

</head>
<body>
	<div class="site-header">
		<div class="main-navigation">
			<div class="responsive_menu">
				<ul>
					<li><a class="show-1 templatemo_home" href="#">User's Profile</a></li>
					<!--<li><a class="show-3 templatemo_page3" href="#">Usage</a></li>-->
					<!--<li><a class="show-4 templatemo_page4" href="#">About</a></li>-->
					<!--<li><a class="show-5 templatemo_page5" href="#">Contact</a></li>-->
					<li><a href="<?php echo $_SERVER['PHP_SELF'].'?logout'?>">Sign-out</a></li>
				</ul>
			</div>
			<div class="container">
				<div class="row">
					<div class="col-md-12 responsive-menu">
						<a href="#" class="menu-toggle-btn">
				            <i class="fa fa-bars"></i>
				        </a>
					</div> <!-- /.col-md-12 -->
					<div class="col-md-12 main_menu">
						<ul>
							<li><a class="show-1 templatemo_home" href="#">User's Profile</a></li>
							<!--<li><a class="show-5 templatemo_page5" href="#">Contact</a></li>-->
							<li><a href="<?php echo $_SERVER['PHP_SELF'].'?logout'?>">Sign-out</a></li>
						</ul>
					</div> <!-- /.col-md-12 -->
				</div> <!-- /.row -->
			</div> <!-- /.container -->
		</div> <!-- /.main-navigation -->

		<div class="container">
			<br/>
			<div class="row">
				<div class="col-md-1">
					<?php
						$fb->setDefaultAccessToken($_SESSION['fb_access_token']);

						// Get User Details
						$res = $fb->get( '/me?fields=name,gender,email' );
						$user = $res->getGraphObject();
					?>
					<img src="<?php echo 'https://graph.facebook.com/'. $user->getProperty( 'id' ) .'/picture?type=normal'?>" style="float:left;background-color: white; padding: 2px; border: 1px solid #dddddd;" class="img img-circle"/>
				</div>
				<div class="col-md-3">
					<div style="padding-left:20px;">
						<p>Welcome, <strong><?php echo $user->getProperty( 'name' ); ?></strong></p>
						<p>Gender: <strong><?php echo $user->getProperty( 'gender' ); ?></strong></p>
						<p>Email: <strong><?php echo $user->getProperty( 'email' ); ?></strong></p>
						<p>UserID: <strong><?php echo $user->getProperty( 'id' ); ?></strong></p>
					</div>
				</div>
					
				<div class="col-md-8 text-center">
					<br/>
					<div id="top_buttons">
						<button id="download_all" data-toggle="tooltip" title="Download All Albums" class="btn btn-primary">Download All</button>
						<button id="download_selected" data-toggle="tooltip" title="Download Selected Albums" class="btn btn-primary">Download Selected</button>
						<button id="move_all" data-toggle="tooltip" title="Move All Albums" class="btn btn-primary">Move All</button>
						<button id="move_selected" data-toggle="tooltip" title="Move Selected Albums" class="btn btn-primary">Move Selected</button>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-md-12 text-center">
					<a href="#" class="templatemo_logo" style="color: #282499">
						<h3><?php echo $user->getProperty( 'name' ); ?>'s Facebook Albums</h3>
					</a> <!-- /.logo -->
				</div> <!-- /.col-md-12 -->
			</div> <!-- /.row -->
		</div> <!-- /.container -->
	</div> <!-- /.site-header -->
	
	<div id="menu-container">
		<div class="content homepage" id="menu-1">
			<div class="container">
					<div class="row">
						<span id="loader" class="navbar-fixed-top"></span>

						<div class="modal fade" id="download-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
							<div class="modal-dialog">
								<div class="modal-content">
									<div class="modal-header">
										<button type="button" class="close" data-dismiss="modal">
											<span aria-hidden="true">&times;</span><span class="sr-only">Close</span>
										</button>
										<h4 class="modal-title" id="myModalLabel">Albums Notification</h4>
									</div>
									<div class="modal-body" id="display-response">
										<!-- Download Response -->
									</div>
									<div class="modal-footer">
										<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="row">
					<?php
						// Get All User Album
						try {
							$response = $fb->get('/me/albums?fields=id,name,cover_photo,count,created_time');
							$albums = $response->getGraphEdge();
						} catch(Facebook\Exceptions\FacebookResponseException $e) {
							echo 'Graph returned an error: ' . $e->getMessage();
							exit;
						} catch(Facebook\Exceptions\FacebookSDKException $e) {
							echo 'Facebook SDK returned an error: ' . $e->getMessage();
							exit;
						}

						// Get All User Album Details
						foreach ($albums as $album) {
							$cover_photo_id = $album['cover_photo']['id'];
							try {
								$photos_request = $fb->get('/'.$album['id'].'/photos?fields=source');
								$photos = $photos_request->getGraphEdge();
							} catch(Facebook\Exceptions\FacebookResponseException $e) {
								echo 'Graph returned an error: ' . $e->getMessage();
								exit;
							} catch(Facebook\Exceptions\FacebookSDKException $e) {
								echo 'Facebook SDK returned an error: ' . $e->getMessage();
								exit;
							}
							$is_cover = False;
							$count_pic = 0;
							$album_cover = '';
							do{
								foreach ($photos as $photo) {
									if ($cover_photo_id == $photo['id'])
									{
										$is_cover = True;
										$album_cover = $photo['source'];
									}
								}
								$count_pic += count($photos);
								$photos = $fb->next($photos);
							}while(!is_null($photos));

							?>
						<div class="col-md-4 col-sm-6">
							<div class="product-item">
								<div class="gallery-item">
									<div class="overlay">
										<!-- Old Slideshow -->
										<!-- <a href="<?php //echo 'slideshow.php?album_id='.$album['id']; ?>" class="fa fa-expand"></a> -->
										<a href="<?php echo 'slideshow2.php?album_id='.$album['id']; ?>" class="fa fa-expand"></a>
										
									</div>	
									<?php 
									if ($is_cover)
									{
										echo '<img src="'.$album_cover.'" height="300" alt="'.$album['id'].'"/>'; 
									}
									else
									{
										
									}
									?>
									<div class="content-gallery">
										<h3><?php echo $album['name'].' ('.$count_pic.')'; ?></h3>
									</div>
								</div>
								<label class="btn button btn-block ">
									<input type="checkbox" value="<?php echo $album['id'].','.$album['name']; ?>" class="chk">&nbsp;Select
								</label>
								<button id="<?php echo $album['id'].','.$album['name']; ?>" data-toggle="tooltip" title="Download this album" class="btn button album_download_btn">Download</button>
								<button id="<?php echo $album['id'].','.$album['name']; ?>" data-toggle="tooltip" title="Move this album" class="btn button album_move_btn">Move to Drive</button>
							</div> <!-- /.product-item -->
						</div> <!-- /.col-md-4 -->		
							<?php
						}
					?>
					</div> <!-- /.row -->
			</div> <!-- /.slide-item -->
		</div> <!-- /.products -->


	</div> <!-- /#menu-container -->

	<div id="templatemo_footer">
		<div class="container">
			<div class="row">
				<div class="col-md-12 text-center">
					<p>&copy; <script type="text/javascript"> document.write(new Date().getFullYear()); </script> The Vende. All Rights Reserved. Developed by <a href="https://technoupdt.blogspot.in/" target="blank">Bansi Nakhuva</a></p>
				</div> <!-- /.col-md-12 -->
			</div> <!-- /.row -->
		</div> <!-- /.container -->
	</div> <!-- /.templatemo_footer -->

	<!-- Scripts -->
	<script src="libs/js/jquery-1.10.2.min.js"></script>
	<script src="https://npmcdn.com/tether@1.2.4/dist/js/tether.min.js"></script>
	<script src="https://npmcdn.com/bootstrap@4.0.0-alpha.5/dist/js/bootstrap.min.js"></script>
	<script src="libs/js/tabs.js"></script>
	<script src="libs/js/templatemo_custom.js"></script>
	<script src="libs/js/bootstrap-waitingfor.min.js"></script>
	
	<!-- Task Handler -->
	<script>
		$(document).ready(function(){
			
			// On Select Buttons Show/Hide
			var $download_selected = $("#download_selected").hide();
			var $move_selected = $("#move_selected").hide();
			
            $cbs = $('.chk').click(function() {
                $download_selected.toggle( $cbs.is(":checked") );
				$move_selected.toggle( $cbs.is(":checked") );
				$(this).parent().parent().toggleClass("chkchecked"); 
            });
			
			// Asynchronous Process Handler
			function background_downloader(link){
			
				waitingDialog.show('Please Wait...',{
					progressType: 'success'
				});
			
				$.ajax({
					url:link,
					success:function(res){
						$("#display-response").html(res);
						waitingDialog.hide();
						$("#download-modal").modal({
							show: true
						});
					}
				});
			}
			
			// Get All Selected Album ID & NAME
			function get_selected()
			{
				var chkArray = [];
				$(".chk:checked").each(function() {
					chkArray.push($(this).val());
				});
				var selected;
				selected = chkArray.join('-');
				return selected;
			}
			
			// Download Handler Buttons
			$("#download_all").click(function(){
				background_downloader("albumDownload.php?all");
			});
			
			$(".album_download_btn").click(function(){
				var property = $(this).attr("id");
				var album = property.split(",");
				background_downloader("albumDownload.php?single="+album[0]+","+album[1]);
			});
			
			$("#download_selected").click(function(){
				background_downloader("albumDownload.php?multiples="+get_selected());
			});
			
			// Google Drive tranfer handler Buttons
			$("#move_all").click(function(){
				background_downloader("albumMove.php?all");
			});
			
			$(".album_move_btn").click(function(){
				var property = $(this).attr("id");
				var album = property.split(",");
				background_downloader("albumMove.php?single="+album[0]+","+album[1]);
			});
			
			$("#move_selected").click(function(){
				background_downloader("albumMove.php?multiples="+get_selected());
			});	
		});
	</script>

</body>
</html>
