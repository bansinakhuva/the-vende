<?php
if (!session_id()) {
    session_start();
}
if (!isset($_SESSION['fb_access_token'] )) {
	header('location: https://'.$_SERVER['HTTP_HOST'].'/index.php');
	exit;
}
include 'fb_config.php';
$fb->setDefaultAccessToken($_SESSION['fb_access_token']);
$download_location = uniqid().'/';
// Mode is 0777 which means the widest possible access of directory
mkdir($download_location, 0777);
function url_get_contents ($Url) {
    if (!function_exists('curl_init')){ 
        die('CURL is not installed!');
    }
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $Url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $output = curl_exec($ch);
    curl_close($ch);
    return $output;
}
function photo_download($album_id, $album_name)
{
	global $fb;
	global $download_location;
	try {
		$photos_request = $fb->get('/'.$album_id.'/photos?fields=source');
		$photos = $photos_request->getGraphEdge();
	} catch(Facebook\Exceptions\FacebookResponseException $e) {
		echo 'Graph returned an error: ' . $e->getMessage();
		exit;
	} catch(Facebook\Exceptions\FacebookSDKException $e) {
		echo 'Facebook SDK returned an error: ' . $e->getMessage();
		exit;
	}
	//echo "<pre>"; print_r($photos); exit;
	$album_location = $download_location.$album_name;
	
	if (!file_exists($album_location)) {		
		mkdir($album_location, 0777);
	}
		// echo "<pre>";
		// print_r($arr);
		// exit;
	do{
		foreach ($photos as $photo) {
			copy($photo['source'],$album_location.'/'.uniqid().".jpg");
			//echo $photo[source];
		}
		$photos = $fb->next($photos);
	}while(!is_null($photos));
}

function make_zip()
{
	global $download_location;
	$directories = glob($download_location. '/*' , GLOB_ONLYDIR);
	echo "<pre>"; //print_r($directories); 
	$zip_path=$directories;
	$zip_path=str_replace("//","/",$directories);
	//print_r($zip_path); exit;
	foreach($zip_path as $tmp){
		$zippath[]=$tmp;
	}
	//print_r(substr(strstr($directories,"//"),2)); exit;
	$zip = new ZipArchive();
	$zip_file='zip/'.uniqid().'.zip';
	$zip->open($zip_file, ZipArchive::CREATE);
	//zip inside folder creation
	// foreach($directories as $tmp){
		// $fname[]=substr(strstr($tmp,"//"),2);
		// $zip->addEmptyDir(substr(strstr($tmp,"//"),2));
		// $i++;
		//print_r(substr(strstr($tmp,"//"),2));
	// }

	$i=0;
	foreach($directories as $tmp){
		
		$files=array_diff(scandir($tmp), array('.', '..'));
			foreach($files as $data){
				$zip->addFile($zip_path[$i]."/".$data);
				//echo $fname[$i]." -> ".$data."<br>";
		}
		$i++;
	}
	$zip->close();
	echo "<a href=".$zip_file." class='btn btn-success btn-lg'>download now</a>";
		require_once( 'unlink_directory.php' );
		$unlink_directory = new unlink_directory();
		$unlink_directory->remove_directory( $download_location );
}

if(isset($_GET['single']) && !empty($_GET['single'])) {
	$single = explode( ",", $_GET['single'] );
	photo_download($single[0],$single[1]);
	make_zip();
}else if(isset($_GET['multiples']) && !empty($_GET['multiples']) && count($_GET['multiples']) > 0) {	
	$multiples = explode("-", $_GET['multiples']);
	foreach ( $multiples as $multiple ) {
		$multiple = explode( ",", $multiple );
		photo_download($multiple[0],$multiple[1]);
	}
	make_zip();
} else if(isset($_GET['all'])) {
	try {
		$response = $fb->get('/me/albums?fields=id,name');
		$albums = $response->getGraphEdge();
	} catch(Facebook\Exceptions\FacebookResponseException $e) {
		echo 'Graph returned an error: ' . $e->getMessage();
		exit;
	} catch(Facebook\Exceptions\FacebookSDKException $e) {
		echo 'Facebook SDK returned an error: ' . $e->getMessage();
		exit;
	}
	foreach ($albums as $album) {
		photo_download($album['id'],$album['name']);
	}
	make_zip();
} else {
	header('location: https://'.$_SERVER['HTTP_HOST'].'/profile.php');
	exit;
}
?>
