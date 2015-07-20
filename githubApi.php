<?php

require_once 'vendor/autoload.php';
require_once 'config.php';

function addUserToSupportEngineersGroup($config, $params) {
	$client = new \Github\Client();
	$client->authenticate($config['github']['token'], "", \Github\Client::AUTH_HTTP_TOKEN);
	// support-engineers team id, 
	// retrieved with request $supportTeam = $client->api('teams')->all('opsway');
	$client->api('teams')->addMember('527996', '');	
}

if (count($argv) != 2) {
	echo "Usage: php githubApi.php githublogin\n";
	die;
}

addUserToSupportEngineersGroup($config, array('githubLogin' => trim(argv[1])));