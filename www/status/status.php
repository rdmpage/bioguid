<?php

// Check the status of a bunch of services

require_once('/Library/WebServer/bioguid/www/config.inc.php');
require_once('/Library/WebServer/bioguid/www/' . $config['adodb_dir']);

$db = NewADOConnection('mysql');
$db->Connect("localhost", 
	$config['db_user'], $config['db_passwd'], $config['db_name']);

// Ensure fields are (only) indexed by column name
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;


$sql = 'SELECT * FROM services';

$result = $db->Execute($sql);
if ($result == false) die("failed"); 

while (!$result->EOF) 
{
	$ch = curl_init(); 
	curl_setopt ($ch, CURLOPT_URL, 				$result->fields['url']); 
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER,	1); 
	curl_setopt ($ch, CURLOPT_FOLLOWLOCATION,	1); 
	curl_setopt ($ch, CURLOPT_HEADER,			1);  
	curl_setopt ($ch, CURLOPT_TIMEOUT,			20); 
	curl_setopt ($ch, CURLOPT_COOKIEJAR,		'cookie.txt');
		
	if ($config['proxy_name'] != '')
	{
		curl_setopt ($ch, CURLOPT_PROXY, $config['proxy_name'] . ':' . $config['proxy_port']);
	}
	
	// LSID
	if ($result->fields['kind'] == 'LSID')
	{
		curl_setopt ($ch, CURLOPT_URL, 	$result->fields['url'] . '/authority/'); 
	}			
	$curl_result = curl_exec ($ch); 
	
	$status = 0;
	$total_time = 0;
	$server = 'unknown';
		
	if (curl_errno ($ch) != 0 )
	{
		//echo "CURL error: ", curl_errno ($ch), " ", curl_error($ch);
		$status = curl_errno ($ch);
	}
	else
	{
		$info = curl_getinfo($ch);
		$http_code = $info['http_code'];
		$status = $http_code;
		$total_time = $info['total_time'];
		
		//print_r($info);
		
		$header = substr($curl_result, 0, $info['header_size']);
		//echo $header;
		// Get server details
		$rows = split ("\n", $header);
		foreach ($rows as $row)
		{
			$parts = split (":", $row, 2);
			if (count($parts) == 2)
			{
				if (preg_match("/Server/", $row))
				{
					$server = trim($parts[1]);
				}
			}
		}
		
	}
	
	$sql = 'INSERT INTO status(service_id,status,total_time,server) VALUES('
		. $result->fields['id']
		. ',' . $status
		. ',' . $db->qstr($total_time)
		. ',' . $db->qstr($server)
		. ')';
	
	$r = $db->Execute($sql);
	if ($r == false) die("failed " . $sql); 
	
	$result->MoveNext();	
	
}
?>




