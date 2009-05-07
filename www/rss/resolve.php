<?php

require_once (dirname(__FILE__) . '/config.inc.php');
require_once (dirname(__FILE__) . '/lib.php');


// Resolution of GUIDs via bioGUID, etc.


// Resolve GUID using bioGUID with content negotiation to get RDF
function ResolveGuid($guid)
{
	global $config;

	$url = 'http://bioguid.info/' . $guid;

	$data = '';
	
	$ch = curl_init(); 
	curl_setopt ($ch, CURLOPT_URL, $url); 
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1); 
	curl_setopt ($ch, CURLOPT_FOLLOWLOCATION,	1); 
	
	// Tell resolver we want RDF
	$request_header = array();
	array_push ($request_header, 'Accept: application/rdf+xml');
	curl_setopt ($ch, CURLOPT_HTTPHEADER, $request_header); 
		
	if ($config['proxy_name'] != '')
	{
		curl_setopt ($ch, CURLOPT_PROXY, $config['proxy_name'] . ':' . $config['proxy_port']);
	}
			
	$curl_result = curl_exec ($ch); 
	
	if (curl_errno ($ch) != 0 )
	{
		echo "CURL error: ", curl_errno ($ch), " ", curl_error($ch);
	}
	else
	{
		$info = curl_getinfo($ch);
		$http_code = $info['http_code'];
		if (HttpCodeValid ($http_code))
		{
			$data = $curl_result;
		}
	}
	return $data;
}
	

/*
$lsid = 'urn:lsid:ipni.org:names:60451056-2';

//echo ResolveGuid($lsid);

$doi = 'doi:10.1111/j.1095-8339.2008.00915.x';

echo ResolveGuid($doi);
*/

?>