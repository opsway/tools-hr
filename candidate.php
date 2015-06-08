<?php

/*
Requires two variables set, presumably in github.conf.php
Example

$githubToken	 = "d123178833fa00328d9a33dfb3450123123123";
$githubOrg	 = "opsway";
*/
require_once 'vendor/autoload.php';
require_once 'github.conf.php';

function deleteTeam($client, $githubOrg, $tempRepoName) {
	$teamToDeleteId = '';
	foreach ($client->api('teams')->all($githubOrg) as $team) {
		if ($team['name'] == $tempRepoName) {
			$teamToDeleteId = $team['id'];
		}
	}
	$client->api('teams')->remove($teamToDeleteId);
}

function createRepoAndPutFiles($client, $tempRepoName, $githubOrg, $githubTasksRepo) {
	$client->api('repo')->create($tempRepoName, 'Temporary repo for HR test', '', false, $githubOrg);
	$result = $client->api('git_data')->trees()->show($githubOrg,$githubTasksRepo,'master');
	foreach ($result['tree'] as $file) {
		$content = $client->api('repo')->contents()->show($githubOrg,$githubTasksRepo,$file['path'],'master');
		$client->api('repo')->contents()->create($githubOrg, $tempRepoName, $file['path'], base64_decode($content['content']), "Auto-created for HR test");
	}
}

if (count($argv) != 3) {
	echo "Usage: php candidate.php type create/delete githublogin\n";
	echo "example: php candidate.php php delete githublogin\n";
	echo "type = php or frontend\n"
	die;
}

$type			 = trim($argv[1]);
$action 		 = trim($argv[2]);
$candidateGithub = trim($argv[3]);

$client 		 = new \Github\Client();
$client->authenticate($githubToken, "", \Github\Client::AUTH_HTTP_TOKEN);
$candidate = $client->api('user')->show($candidateGithub);
$tempRepoName = "hr_test_" . (urlencode($candidateGithub));

switch ($type) {
	case 'php':
		$githubTasksRepo = "hr-test";
		break;
	
	case 'frontend':
		$githubTasksRepo = "hr_test_frontend";
		break;
}
$githubTasksRepo = 

switch ($action) {
 	case 'create':
 		createRepoAndPutFiles($client, $tempRepoName, $githubOrg,$githubTasksRepo);
		$newTeam = $client->api('teams')->create($githubOrg, array('name' => $tempRepoName, 'permission' => 'push'));
		$client->api('teams')->addRepository($newTeam['id'], $githubOrg, $tempRepoName);
		$client->api('teams')->addMember($newTeam['id'], $candidateGithub);
		echo "User $candidateGithub added to team with access to $tempRepoName\n";
		break;
	case 'delete':
		try {
			$client->api('repo')->remove($githubOrg, $tempRepoName);
		} catch (Exception $e) {}
		try {
			deleteTeam($client, $githubOrg, $tempRepoName);
		} catch (Exception $e) {}
		echo "Deleted team and repo $tempRepoName\n";
		break;
 	default:
 		echo "No action $action found";
 		break;
}