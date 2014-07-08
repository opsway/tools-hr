<?php

/*
Requires two variables set, presumably in github.conf.php
Example

$githubToken	 = "d123178833fa00328d9a33dfb3450123123123";
$githubOrg	 = "opsway";
$githubTasksRepo = "hr-test"
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
	// $client->api('repo')->create($tempRepoName, 'Temporary repo for HR test', '', false, $githubOrg);
	$tree = $client->api('git_data')->trees($githubOrg,$githubTasksRepo,'8e51dae8e8baf2614c6e6a0dac803e95b9e7a773');
	echo($tree);
	die;
}

if (count($argv) != 3) {
	echo "Usage: php candidate.php create githublogin\n";
	echo "OR php candidate.php delete githublogin\n";
	die;
}

$action 		 = trim($argv[1]);
$candidateGithub = trim($argv[2]);

$client 		 = new \Github\Client();
$client->authenticate($githubToken, "", \Github\Client::AUTH_HTTP_TOKEN);
$candidate = $client->api('user')->show($candidateGithub);
$tempRepoName = "hr_test_" . (urlencode($candidateGithub));

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