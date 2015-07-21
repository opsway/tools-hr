<?php

require './src/shiftplanning.php';
require './config.php';

function createUser($config, $params) {
	$shiftplanning = new shiftplanning(
	    array(
	        'key' => $config['shiftplanning']['apikey'] 
	    )
	);

	$session = $shiftplanning->getSession();

	if( !$session )
	{	$response = $shiftplanning->doLogin(
			array(
				'username' => $config['shiftplanning']['username'],
				'password' => $config['shiftplanning']['password'],
			)
		);
		if( $response['status']['code'] == 1 )
		{
			$session = $shiftplanning->getSession();
			
		}
		else
		{
			throw new Exception("Can not login to ShifPlanning API");
		}
	}
	
	$response = $shiftplanning->createEmployee(
				array(
						'name' 		=> $params['name'],
						'eid'  		=> $params['opswayLogin'],
						'status'	=> 1,
						'group'		=> 5,
						'email'		=> $params['email'],
						'nick_name' => $params['opswayLogin'],
						'cell_phone'=> $params['cellphone'],
						'username'  => $params['opswayLogin']
					)
			);

	if ( $response['status']['code'] != 1) {
		throw new Exception("Error during ShifPlanning user creation");
	} else {
		echo "Successfully created user\n";
	}
	var_dump($response);
}


if (count($argv) != 6) {
	echo "Usage: php shiftplanningApi.php FirstName LastName opswayLogin email@opsway.com 380-1234-1234\n",
	die;
}


$params = array (
		'name' => trim($argv[1]) . " " . trim($argv[2]),
		'opswayLogin' => trim($argv[3]),
		'email' => trim($argv[4]),
		'cellphone' => trim($argv[5]) 
	);

createUser($config, $params);