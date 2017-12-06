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
/*mkdir($download_location, 0777);
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
}*/
function photo_download($album_id)
{
	global $fb;
/*	try {
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
			file_put_contents( $album_location.'/'.uniqid().".jpg", url_get_contents( $photo['source']) );
			//copy($photo['source'],$album_location.'/'.uniqid().".jpg");
			//echo $photo[source];
		}
		$photos = $fb->next($photos);
	}while(!is_null($photos));*/

	 try {
            $response = $fb->get('/'.$album_id.'?fields=name,photos.limit(100){images,name,created_time}');
            $edge = $response->getGraphNode();
            $album = $edge->asArray();
            $edge = $response->getGraphNode()['photos'];
            $photos = array();
            
            do{
                $photos = array_merge($photos,$edge->asArray());
                $edge = $fb->next($edge);
            }while($edge !== NULL);
            
            $album['photos'] = $photos;
            
        } catch (Facebook\Exceptions\FacebookResponseException $e) {
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch (Facebook\Exceptions\FacebookSDKException $e) {
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }
        $album_name = $album['name'];
        //echo $album_name;exit;
		$path = $album_name.'-'.$album_id.'/';
        mkdir($path,0777);
		foreach($album['photos'] as $photo) {
              // Initilized blank photo name if there is no caption for the photo
              $photoName = "";
              // If there is name of photo
              if(isset($photo['name'])){
                // Senitize it
                $photoName = mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $photo['name']);
                $photoName = mb_ereg_replace("([\.]{2,})", '', $photoName);  
              }
              // Set filename as <photo_caption>-<photo_id>.jpg
              $file = $photoName.'-'.$photo['id'].'.jpg';
              // Copy to the server
              copy($photo['images'][0]['source'],$path.$file);
        }        
        $zip = new \ZipArchive;
        $zipFile = $album_name.'-'.$album_id.'.zip';
        if ($zip->open($zipFile, \ZipArchive::CREATE)!==TRUE) {
            exit("cannot open <$zipFile>\n");
        }

        $options = array('add_path' => $album_name.'-'.$album_id.'/', 'remove_all_path' => TRUE);
        $zip->addGlob($path.'*.*',GLOB_BRACE, $options);
        $zip->close();
        echo $zipFile;
        echo '<a href="'.$zipFile.'" class="btn btn-success btn-lg">download now</a>';
		require_once( 'unlink_directory.php' );
		$unlink_directory = new unlink_directory();
		$unlink_directory->remove_directory( $path );
}
/*
function make_zip()
{
	global $download_location;
	$directories = glob($download_location. '/*' , GLOB_ONLYDIR);
	//echo "<pre>"; //
	//print_r($directories); exit;
	$zip_path=$directories;
	$zip_path=str_replace("//","/",$directories);
	print_r($zip_path);exit;
	//print_r($zip_path); exit;
	foreach($zip_path as $tmp){
		$zippath[]=$tmp;
	}
	//print_r(substr(strstr($directories,"//"),2)); exit;
	$zip = new ZipArchive();
	$zip_file='zip/'.$download_location.'.zip';
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
*/
if(isset($_GET['single']) && !empty($_GET['single'])) {
	$single = explode( ",", $_GET['single'] );
	photo_download($single[0]);
	//make_zip();
}else if(isset($_GET['multiples']) && !empty($_GET['multiples']) && count($_GET['multiples']) > 0) {	
	$multiples = explode("-", $_GET['multiples']);
	foreach ( $multiples as $multiple ) {
		photo_download($multiple);
	}
//	make_zip();
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
		photo_download($album['id']);
	}
//	make_zip();
} else {
	header('location: https://'.$_SERVER['HTTP_HOST'].'/profile.php');
	exit;
}
?>
