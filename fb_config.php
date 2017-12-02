<?php
require_once __DIR__ . '/vendor/autoload.php';
$appId = '440948386100123';
$fb = new Facebook\Facebook([
	'app_id' => $appId,
	'app_secret' => '312ca3c39c54e75c4d2932a2f38fdeb8',
	'default_graph_version' => 'v2.5',
]);
?>