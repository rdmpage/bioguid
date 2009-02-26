<?php

// $Id: $

/*

This is a PHP port of the Perl script described by jake at Third Rail
http://3.rdrail.net/blog/twittering-subversion-commits/

Google Code provides Post-Commit Web Hooks (http://code.google.com/p/support/wiki/PostCommitWebHooks)
that you can set up to received commit notifications. The code below takes this notification and 
tweets the message, with a TinyURL link to the revision. The notification takes the form of a
POST request from Google to a URL that you provide (e.g., http://bioguid.info/google/).

Key things I needed to find out were how to get headers from the request ($_SERVER), and how to
get the POST body (this is not the same as the POST parameters, which are stored in $_POST). The
global variable $HTTP_RAW_POST_DATA has the POST body.

To ensure that the message comes from Google, you need to check that the 
HTTP_GOOGLE_CODE_PROJECT_HOSTING_HOOK_HMAC header has been set, and that the value of the header
matches the HMAC-MD5 hash of the POST body. PHP doesn't implement HMAC-MD5, so I used the
code provided by http://uk3.php.net/manual/en/function.md5.php#56934. You compare the 
HTTP_GOOGLE_CODE_PROJECT_HOSTING_HOOK_HMAC value with the HMAC-MD5 hash (using the 
Post-Commit Authentication Key provided by Google). if they match, you can handle the request.

To set up the web hooks you need to be an administrator of the project hosted by Google, and
go to the "Administer" tab and select the "Source" subtab.

*/


require_once(dirname(__FILE__) . '/config.inc.php');
require_once(dirname(__FILE__) . '/lib.php');

//--------------------------------------------------------------------------------------------------
// from http://uk3.php.net/manual/en/function.md5.php#56934
function hmac($key, $data, $hash = 'md5', $blocksize = 64) 
{
	if (strlen($key)>$blocksize) 
	{
		$key = pack('H*', $hash($key));
	}
	$key  = str_pad($key, $blocksize, chr(0));
	$ipad = str_repeat(chr(0x36), $blocksize);
	$opad = str_repeat(chr(0x5c), $blocksize);
	return $hash(($key^$opad) . pack('H*', $hash(($key^$ipad) . $data)));
}

//--------------------------------------------------------------------------------------------------
// Get request headers
$h = print_r($_SERVER, true);
// Get body of POST request
$p = $HTTP_RAW_POST_DATA;

// Check for Google Digest header
if ($_SERVER['HTTP_GOOGLE_CODE_PROJECT_HOSTING_HOOK_HMAC'])
{	
	// Check digests match
	$remote_digest = $_SERVER['HTTP_GOOGLE_CODE_PROJECT_HOSTING_HOOK_HMAC'];
	$digest = hmac ($config['secret_key'], $p);
	
	if ($digest != $remote_digest)
	{
		die ("digests don't match");
	}
	
	// Debugging to capture output
	/*
	$gfilename = 'tmp/g.txt';
	$gfile = @fopen($gfilename, "w+") or die("could't open file --\"" . $gfilename . "\"");
	fwrite($gfile, $h . $p . $digest);
	fclose($gfile);
	*/
	
	// Get contents of POST body
	$obj = json_decode($p);
	
	$url = 'http://twitter.com/statuses/update.json';
	$ch = curl_init(); 
	curl_setopt ($ch, CURLOPT_URL, $url); 
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1); 
	curl_setopt ($ch, CURLOPT_POST, 1);
	curl_setopt ($ch, CURLOPT_USERPWD, $config['twitter_username'] . ':' . $config['twitter_password']);
	if ($config['proxy_name'] != '')
	{
		curl_setopt ($ch, CURLOPT_PROXY, $config['proxy_name'] . ':' . $config['proxy_port']);
	}
	
	// generate Tinyurl
	$url = 'http://tinyurl.com/api-create.php?url=http://code.google.com/p/' . $obj->project_name . '/source/detail?r='. $obj->revisions[0]->revision;
	$tiny = get($url); 
	
	// Send message to twitter
	
	$max_length = 140;
	
	$tiny_length = strlen($tiny);
	$msg_length = $max_length - $tiny_length - 1;
	
	$msg = "svn: " . $obj->revisions[0]->message;
	$msg = substr($msg, 0, $msg_length);
	$msg .= ' ' . $tiny;
	
	curl_setopt ($ch, CURLOPT_POSTFIELDS, "status=" . $msg);
	$result=curl_exec ($ch); 
	
	if( curl_errno ($ch) != 0 )
	{
		echo "error\n";
	}
	curl_close ($ch); 
}

?>