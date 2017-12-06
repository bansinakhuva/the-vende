<?php
require_once __DIR__ . '/vendor/autoload.php';
$appId = '373072976469406';
$fb = new Facebook\Facebook([
	'app_id' => $appId,
	'app_secret' => 'f705fe6e09ac0bed053559b118ed7fc4',
	'default_graph_version' => 'v2.11',
]);

?>