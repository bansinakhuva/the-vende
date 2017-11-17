<?php
require_once __DIR__ . '/libs/SDK/facebook/vendor/autoload.php';
$fb = new Facebook\Facebook([
	'app_id' => $_ENV['FB_APP_ID'],
	'app_secret' => $_ENV['FB_APP_SECRET'],
	'default_graph_version' => 'v2.5',
]);
?>