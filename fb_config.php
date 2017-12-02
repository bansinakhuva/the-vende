<?php
require_once __DIR__ . '/vendor/autoload.php';
$appId = '466264270413471';
$fb = new Facebook\Facebook([
	'app_id' => $appId,
	'app_secret' => 'e5f6c62093d5e7532f8e3629150c7b81',
	'default_graph_version' => 'v2.11',
]);
?>