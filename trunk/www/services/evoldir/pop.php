<?php
// include class
include("Net/POP3.php");

require_once('config.inc.php');
require_once('db.php');
require_once('lib.php');

// twitter
$username = '<twitter username>';
$password = '<twitter password>';
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


// initialize object
$pop3 = new Net_POP3();

// attempt connection to server
if (!$pop3->connect("<email server>", 110)) {
    die("Error in connection");
} 

// attempt login
$ret = $pop3->login("<email username>", "<email password>");
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
        {
        
        	$id = store_message ($hdrs, $pop3->getBody($x));
			if ($id != '')
			{
				// It's a new message
				
				// generate Tinyurl
				$url = 'http://tinyurl.com/api-create.php?url=http://bioguid.info/services/evoldir/get.php?id=' . $id;
				$tiny = get($url); 
				
				// Send message to twitter
				
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

// disconnect from server
$pop3->disconnect();

curl_close ($ch); 

?>
