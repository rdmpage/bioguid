<?php

require_once('config.inc.php');
require_once('lib.php');


date_default_timezone_set('UTC');

// OAuth
require_once('twitteroauth/twitteroauth.php');

function getConnectionWithAccessToken($oauth_token, $oauth_token_secret) 
{
	global $config;
	
	$connection = new TwitterOAuth($config['consumer_key'], $config['consumer_secret'], $oauth_token, $oauth_token_secret);
	return $connection;
}

if ($config['oauth'])
{
	$connection = getConnectionWithAccessToken($config['oauth_token'], $config['oauth_token_secret']);
}
else
{
}

$lastpublished = strtotime ('10 September 2000');
$published = $lastpublished;

echo $lastpublished . "\n";

$filename =  dirname(__FILE__) . '/published.json';
if (file_exists($filename))
{
	$file = @fopen($filename, "a+") or die("could't open file --\"$filename\"");
	$json = fread($file, filesize($filename));
	fclose($file);
	
	$lastpublished = json_decode($json);
	$published = $lastpublished;
}

echo "Last published=$lastpublished\n";
echo "Published=$published\n";

// Fetch feed

//$feed = file_get_contents('atom.xml');
$feed = get('http://taxacom.markmail.org/atom/');

//echo $feed;

// Parse
$dom = new DOMDocument;
$dom->loadXML($feed);
$xpath = new DOMXPath($dom);
$xpath->registerNamespace("atom", "http://www.w3.org/2005/Atom");


$nodeCollection = $xpath->query ("//atom:entry");

$updates = array();
			
foreach($nodeCollection as $node)
{
	$status = '';

	$nc = $xpath->query ("atom:author/atom:name", $node);
	foreach($nc as $n)
	{
		$status .= $n->firstChild->nodeValue;
	}

	$nc = $xpath->query ("atom:link/@href", $node);
	foreach($nc as $n)
	{
		// generate Tinyurl
		$url = 'http://tinyurl.com/api-create.php?url=' . $n->firstChild->nodeValue;
		$tiny = get($url);
	}
	
	$len = strlen($status . $tiny);
	
	$body = '';
	$nc = $xpath->query ("atom:title", $node);
	foreach($nc as $n)
	{
		$body = $n->firstChild->nodeValue;
		$body = str_replace(" [Taxacom] ", "", $body);
	}
	
	$bodylength = strlen($body);
		
	$l = $len + $bodylength + 2;
	if ($l > 140)
	{
		$body = substr($body, 0, 140 - $len - 4);
		$body .= '…';
	}
	
	$status = $status . ' ' . $body . ' ' . $tiny;
	
	$nc = $xpath->query ("atom:published", $node);
	foreach($nc as $n)
	{
		
		$timestamp = strtotime($n->firstChild->nodeValue);

		
		echo $n->firstChild->nodeValue .  ' ' . $timestamp . "\n";
		
		if ($timestamp > $lastpublished)
		{
			$published = max($timestamp, $published);
			echo $status . "\n";
			
			$updates[] = $status;
				
			
		}
	}	
	
	
	
	
}

echo $published . "\n";

$updates = array_reverse($updates);

print_r($updates);

foreach ($updates as $update)
{			
	echo $update;
	$parameters = array('status' => $update);
	$status = $connection->post('statuses/update', $parameters);
	print_r($status);
}


$file = fopen($filename, "w") or die("could't open file --\"$filename\"");
fwrite($file, json_encode($published));
fclose($file);
	



if ($config['oauth'])
{
}
else
{
	curl_close ($ch); 
}

?>
