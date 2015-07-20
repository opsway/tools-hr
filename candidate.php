<?php

require_once 'vendor/autoload.php';
require_once 'config.php';

function deleteTeam($client, $githubOrg, $tempRepoName) {
	$teamToDeleteId = '';
	foreach ($client->api('teams')->all($githubOrg) as $team) {
		if ($team['name'] == $tempRepoName) {
			$teamToDeleteId = $team['id'];
		}
	}
	$client->api('teams')->remove($teamToDeleteId);
}

function createRepoAndPutFiles($client, $tempRepoName, $githubOrg, $githubTasksRepo, $type) {
	$client->api('repo')->create($tempRepoName, 'Temporary repo for HR test', '', true, $githubOrg);
    if ($type != 'php') {
        $result = $client->api('git_data')->trees()->show($githubOrg, $githubTasksRepo, 'master');
        foreach ($result['tree'] as $file) {
            $content = $client->api('repo')->contents()->show($githubOrg, $githubTasksRepo, $file['path'], 'master');
            $client->api('repo')->contents()
                ->create($githubOrg, $tempRepoName, $file['path'], base64_decode($content['content']),
                    "Auto-created for HR test");
        }
    }
}


if (count($argv) != 4) {
	echo "Usage: php candidate.php type create/delete githublogin\n";
	echo "example: php candidate.php php delete githublogin\n";
	echo "type = php or frontend\n";
	die;
}

$type			 = trim($argv[1]);
$action 		 = trim($argv[2]);
$candidateGithub = trim($argv[3]);

$client 		 = new \Github\Client();
$client->authenticate($config['github']['token'], "", \Github\Client::AUTH_HTTP_TOKEN);
$candidate = $client->api('user')->show($candidateGithub);
$tempRepoName = "hr_test_" . (urlencode($candidateGithub));

switch ($type) {
	case 'php':
		$githubTasksRepo = "hr_php_test";
		break;
	
	case 'frontend':
		$githubTasksRepo = "hr_frontend_test";
		break;

    default:
        die('Wrong type test.'.PHP_EOL);
        break;
}

switch ($action) {
 	case 'create':
 		createRepoAndPutFiles($client, $tempRepoName, $config['github']['org'], $githubTasksRepo, $type);
		$newTeam = $client->api('teams')->create($config['github']['org'], array('name' => $tempRepoName, 'permission' => 'push'));
		$client->api('teams')->addRepository($newTeam['id'], $config['github']['org'], $tempRepoName);
		$client->api('teams')->addMember($newTeam['id'], $candidateGithub);
        if (isset($config['github']['jenkins_hook_url']) && $config['github']['jenkins_hook_url'] != '' && $type == 'php') {
            foreach ($jenkinsGithub as $user) {
                $client->api('teams')->addMember($newTeam['id'], $user);
            }
            $client->api('repo')->hooks()->create($config['github']['org'], $tempRepoName,
                array('name' => 'jenkins', 'config' => array('jenkins_hook_url' => $jenkins_hook_url), 'active' => true));
        }
		echo "User $candidateGithub added to team with access to $tempRepoName\n";
		break;
	case 'delete':
		try {
			$client->api('repo')->remove($config['github']['org'], $tempRepoName);
		} catch (Exception $e) {}
		try {
			deleteTeam($client, $config['github']['org'], $tempRepoName);
		} catch (Exception $e) {}
		echo "Deleted team and repo $tempRepoName\n";
		break;
 	default:
 		echo "No action $action found";
 		break;
}