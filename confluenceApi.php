<?php    

require './config.php';

function createUser($config, $params) {

    $url = "https://opsway.atlassian.net/rest/api/2/user";
    $request = '{
				"name": "test testss",
				"password": "passsss112",
				"emailAddress": "test33@opsway.com",
				"displayName": "Test Test",
				"notification" : false,
			}';

    $headers = array('Content-type: application/xml','Content-Length: ' . strlen($request));
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_USERPWD, $config['confluence']['login'] . ":" . $config['confluence']['password']);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1) ;
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_HEADER, true);

    $http_result = curl_exec($ch);
    $error       = curl_error($ch);
    $http_code   = curl_getinfo($ch ,CURLINFO_HTTP_CODE);

    curl_close($ch);

    var_dump($http_result);
    if ($http_code !="201" || $error) {
      throw new Exception('Can not create user in OneLogin');
    }   
    
}

createUser($config, '');