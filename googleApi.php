<?php
require 'vendor/autoload.php';
require './config.php';

define('APPLICATION_NAME', 'Directory API Quickstart');
define('CREDENTIALS_PATH', 'googleapps-credentials.json');
define('CLIENT_SECRET_PATH', 'client_secret.json');
define('SCOPES', implode(' ', array(
  Google_Service_Directory::ADMIN_DIRECTORY_USER_READONLY,
  Google_Service_Directory::ADMIN_DIRECTORY_GROUP_MEMBER,
  Google_Service_Directory::ADMIN_DIRECTORY_GROUP)
));

/**
 * Returns an authorized API client.
 * @return Google_Client the authorized client object
 */
function getClient($config) {
  $client = new Google_Client();
  $client->setApplicationName(APPLICATION_NAME);
  $client->setScopes(SCOPES);

  $client->setAuthConfigFile(CLIENT_SECRET_PATH);
  $client->setAccessType('offline');

  // Load previously authorized credentials from a file.
  $credentialsPath = CREDENTIALS_PATH;
  if ($config['googleapps']['credentials']) {
    $accessToken = $config['googleapps']['credentials'];

  } else {
    // Request authorization from the user.
    $authUrl = $client->createAuthUrl();
    printf("Open the following link in your browser:\n%s\n", $authUrl);
    print 'Enter verification code: ';
    $authCode = trim(fgets(STDIN));

    // Exchange authorization code for an access token.
    $accessToken = $client->authenticate($authCode);

    // Store the credentials to disk.
    if(!file_exists(dirname($credentialsPath))) {
      mkdir(dirname($credentialsPath), 0700, true);
    }
    file_put_contents($credentialsPath, $accessToken);
    printf("Credentials saved to %s\n", $credentialsPath);
  }
  $client->setAccessToken($accessToken);

  // Refresh the token if it's expired.
  if ($client->isAccessTokenExpired()) {
    $client->refreshToken($client->getRefreshToken());
    file_put_contents($credentialsPath, $client->getAccessToken());
  }
  return $client;
}


function createGroup($config, $params) {
  // Get the API client and construct the service object.
  $client = getClient($config);
  $service = new Google_Service_Directory($client);


  $newGroup = new Google_Service_Directory_Group();
  $newGroup->setName($params['name']);
  $newGroup->setDescription($params['name']);
  $newGroup->setEmail($params['email']);

  $group = $service->groups->insert($newGroup); 
  //https://developers.google.com/admin-sdk/directory/v1/guides/manage-groups
  //In addition, your client must wait a minute before adding a member or sending a message to a newly created group.
  sleep(100);

  $newMember = new Google_Service_Directory_Member();
  $newMember->setRole('MEMBER');
  $newMember->setEmail(array($params['privateEmail']));
  $service->members->insert($params['email'],$newMember);
}

file_put_contents(CLIENT_SECRET_PATH, $config['googleapps']['client_secret']);
file_put_contents(CREDENTIALS_PATH, $config['googleapps']['client_secret']);




function deleteGroup() {
  /******
  This is commented out intentionally, and can be used later, when deleting users

  try {
     $results = $service->groups->get('team@opsway.com');
   } catch (Exception $e) {
     $errors = $e->getErrors();
  //   $reason = $errors[0]['reason'];
   }
  **/  
}

if (count($argv) != 5) {
    echo "Usage: php googleApi.php FirstName LastName email@opsway.com oldemail@mail.com\n";
    die;
}


$params = array (
        'firstname' => trim($argv[1]),
        'lastname'  => trim($argv[2]), 
        'name' => trim($argv[1]) . " " . trim($argv[2]),
        'email' => trim($argv[3]),
        'privateEmail' => trim($argv[4]) 
    );

createGroup($config, $params);