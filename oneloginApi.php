<?php    

require './config.php';

function createUser($config, $params) {

    $url = "https://app.onelogin.com/api/v3/users.xml";
    $request = "<user><firstname>". $params['firstname'] . "</firstname>" .
            "<lastname>" . $params['lastname'] . "</lastname>" . 
               "<email>" . $params['email'] . "</email>" .
               "<username>" . $params['opswayLogin'] . "</username>" .
               "<company>OpsWay</company>" .
               "<password>" . $params['password'] . "</password>" .
               "<password_confirmation>" . $params['password'] . "</password_confirmation></user>";

    $headers = array('Content-type: application/xml','Content-Length: ' . strlen($request));
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_USERPWD, $config['onelogin']['apikey']);
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

function addRoles($config, $params) {

    foreach ($params['roles'] as $role_id) {
        $url = "https://app.onelogin.com/api/v3/users/username/" . $params['opswayLogin'];
        $request = "<user>
                        <roles type='array'>
                            <role>" . $role_id . "</role>
                        </roles>
                    </user>";

        $headers = array('Content-type: application/xml','Content-Length: ' . strlen($request));
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_USERPWD, $config['onelogin']['apikey']);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
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
        if ($http_code !="304" || $error ) {
          throw new Exception('Can not update user roles in OneLogin');
        }   
        
    }
}


if (count($argv) != 7) {
    echo "Usage: php hipchatApi.php FirstName LastName opswayLogin email@opsway.com password123g additionalRole\n";
    echo "additionalRole can be one of: Engineer, None\n";
    die;
}

//Roles:

// 52259 - Engineer
// 62481 - OpsWay Employee <-- is assigned via Mapping, no need to do it here
// 79520 - Sales&Marketing
// 63887 - Finance
// 63887 - Expert

$params = array (
        'firstname' => trim($argv[1]),
        'lastname'  => trim($argv[2]), 
        'name' => trim($argv[1]) . " " . trim($argv[2]),
        'opswayLogin' => trim($argv[3]),
        'email' => trim($argv[4]),
        'password' => trim($argv[5])
    );

switch (trim($argv[6])) {
    case 'Engineer':
        $params['roles'] = array('52259');
        break;
    default:
        break;
}


createUser($config, $params);
addRoles($config, $params);