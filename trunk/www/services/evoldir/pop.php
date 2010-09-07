<?php
// include class
include("Net/POP3.php");

require_once('config.inc.php');
require_once('db.php');
require_once('lib.php');

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
	// twitter
	$username = 'evoldir';
	$password = 'peacrab';
	$url = 'http://twitter.com/statuses/update.json';
	$ch = curl_init(); 
	curl_setopt($ch, CURLOPT_URL, $url); 
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1); 
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
	if ($config['proxy_name'] != '')
	{
		curl_setopt ($ch, CURLOPT_PROXY, $config['proxy_name'] . ':' . $config['proxy_port']);
	}
}

// initialize object
$pop3 = new Net_POP3();

// attempt connection to server
if (!$pop3->connect("udcf.gla.ac.uk", 110)) {
    die("Error in connection");
} 

// attempt login
$ret = $pop3->login("dpage", "peacrab");
if (is_a($ret, 'PEAR_Error')) {
    die("Error in authentication: " . $ret->getMessage());
} 

// print number of messages found
echo $pop3->numMsg() . " message(s) in mailbox\n";

// print message headers
if ($pop3->numMsg() > 0) {
    for ($x=1; $x<=$pop3->numMsg(); $x++) {
    
     
        $hdrs = $pop3->getParsedHeaders($x);
        //print_r($hdrs);
        //echo $hdrs['From'] . "\n" . $hdrs['Subject'] . "\n" . $hdrs['Message-Id'] . "\n\n"; 
        
        // Only process emails from xxx.
        if (preg_match('/evoldir\@evol.biology.mcmaster.ca/', $hdrs['From']))
 //       if (preg_match('/springeralerts@springer.delivery.net/', $hdrs['From']))
        {
        
        	$id = store_message ($hdrs, $pop3->getBody($x));
			if ($id != '')
			{
				// It's a new message
				
				// generate Tinyurl
				$url = 'http://tinyurl.com/api-create.php?url=http://bioguid.info/services/evoldir/get.php?id=' . $id;
				$tiny = get($url); 
				
				$status = $hdrs['Subject'] . ' ' . $tiny;
				echo $status . "\n";
				
				// Send message to twitter
				if ($config['oauth'])
				{
					$parameters = array('status' => $status);
					$status = $connection->post('statuses/update', $parameters);
					print_r($status);
				}
				else
				{
					curl_setopt($ch, CURLOPT_POSTFIELDS, "status=" . $hdrs['Subject'] . ' ' . $tiny);
					$result=curl_exec ($ch); 
					
					if( curl_errno ($ch) != 0 )
					{
						echo "error\n";
					}
				}
			}
        
        }
        
        
    }
}

// disconnect from server
$pop3->disconnect();

if ($config['oauth'])
{
}
else
{
	curl_close ($ch); 
}

?>
