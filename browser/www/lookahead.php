<?php

// lookahead to resolve URI

require_once ('../triple_store.php');
require_once (dirname(__FILE__) . '/uri_functions.php');

$uri = $_GET['uri'];

$status = 400;

$uri = urldecode($uri);

// URI might be alias
$ntriples = get_canonical_uri($uri);

$obj = new stdclass;
$obj->uri = $uri;
$obj->ntriples = $ntriples;

if ($obj->ntriples == 0)
{
	$query = "LOAD <" . $uri . ">";
	$r = $store->query($query);
	
	if ($r['result']['t_count'] > 0)
	{
		$obj->ntriples = $r['result']['t_count'];
		$status = 200;
	}
}
else
{
	$status = 200;
}


switch ($status)
{			
	case 400:
		ob_start();
		header('HTTP/1.0 400');
		header('Status: 400');
		$_SERVER['REDIRECT_STATUS'] = 400;
		break;
		
	case 200:
		header("Content-type: text/plain");		
		echo json_encode($obj);
		break;
		
	default:
		break;
}

?>