<?php

require 'vendor/autoload.php';
require './config.php';

function createUser($config, $params) {
	$token = $config['hipchat']['token'];
	$hc = new HipChat\HipChat($token);

	$result = $hc->create_user(
		$params['email'],
		$params['name'],
		$params['opswayLogin'],
		'',
		0,
		$params['password']
		);	
	var_dump($result);
}


if (count($argv) != 6) {
	echo "Usage: php hipchatApi.php FirstName LastName opswayLogin email@opsway.com password123g\n",
	die;
}


$params = array (
		'name' => trim($argv[1]) . " " . trim($argv[2]),
		'opswayLogin' => trim($argv[3]),
		'email' => trim($argv[4]),
		'password' => trim($argv[5]) 
	);

createUser($config, $params);