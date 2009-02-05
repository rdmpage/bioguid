<?php

/**
 * @file rss.php
 *
 */

require_once 'config.inc.php';
require_once($config['adodb_dir']);


/**
 *
 * @brief Get contents of RSS feed, optionally checking whether it has been modified.
 *
 * We use HTTP conditional GET to check whether feed has been updated, see 
 * http://fishbowl.pastiche.org/2002/10/21/http_conditional_get_for_rss_hackers.
 * ETag and Last Modified header values are stored in a MySQL database.
 * ETag is a double-quoted string sent by the HTTP server, e.g. "2f4511-8b92-44717fa6"
 * (note the string includes the enclosing double quotes). Last Modified is date,
 * written in the form Mon, 22 May 2006 09:08:54 GMT.
 *
 * @param url Feed URL
 * @param rss Return RSS feed in this variable
 *
 * @return 0 if feed exists and is modified, otherwise an HTTP code or an error
 * code.
 *
 */
function GetRSS ($url, &$rss, $check = false)
{
	global $config;
	global $ADODB_FETCH_MODE;
	
	$result = 0;

	// 1. Get details of ETag and LastModified from database
		
	$db = NewADOConnection('mysql');
	$db->Connect("localhost", 
		$config['db_user'] , $config['db_passwd'] , $config['db_name']);
	
	// Ensure fields are (only) indexed by column name
	$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
	
	$sql = 'SELECT last_modified, etag FROM feed WHERE (url = "' . $url . '")';

	$sql_result = $db->Execute($sql);
	if ($sql_result == false) die("failed [" . __LINE__ . "]: " . $sql);
	
	$ETag = '';
	$LastModified = '';
	if ($sql_result->RecordCount() == 0)
	{
		$sql = 'INSERT feed (url) VALUES(' . $db->qstr($url) . ')';
		$sql_result = $db->Execute($sql);
		if ($sql_result == false) die("failed [" . __LINE__ . "]: " . $sql);
	}
	else
	{
		$ETag = $sql_result->fields['etag'];
		$LastModified = $sql_result->fields['last_modified'];
	}
	
	// Construct conditional GET header
	$if_header = array();
	
	if ($check)
	{
		if ($LastModified != "''")
		{
			array_push ($if_header, 'If-Modified-Since: ' . $LastModified);
		}
		
		// Only add this header if server returned an ETag value, otherwise
		// Connotea doesn't play nice.
		if ($ETag != "''")
		{
			array_push ($if_header,'If-None-Match: ' . $ETag);
		}
	}
	
	//print_r($if_header);
	 

	$ch = curl_init(); 
	curl_setopt($ch, CURLOPT_URL, $url); 
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1); 
	curl_setopt ($ch, CURLOPT_HEADER,		  1); 
//	curl_setopt ($ch, CURLOPT_FOLLOWLOCATION,	1); 
	
	if ($check)
	{
		curl_setopt ($ch, CURLOPT_HTTPHEADER,	  $if_header); 
	}
	
	if ($config['proxy_name'] != '')
	{
		curl_setopt ($ch, CURLOPT_PROXY, $config['proxy_name'] . ":" . $config['proxy_port']);
	}
			
	$curl_result = curl_exec ($ch); 
	
	if(curl_errno ($ch) != 0 )
	{
		// Problems with CURL
		$result = curl_errno ($ch);
	}
	else
	{
		 $info = curl_getinfo($ch);
		 
		 //print_r($info);
		 
		 
		 $header = substr($curl_result, 0, $info['header_size']);
		 
		 $result = $info['http_code'];
		 
		//echo $header;

		if ($result == 200)
		{
			// HTTP 200 means the feed exists and has been modified since we 
			// last visited (or this is the first time we've looked at it)
			// so we grab it, remembering to trim off the header. We store
			// details of the feed in our database.
			$result = 0;
			
			$rss = substr ($curl_result, $info['header_size']);


			if ($check)
			{
				// Retrieve ETag and LastModified
				$rows = split ("\n", $header);
				foreach ($rows as $row)
				{
					$parts = split (":", $row, 2);
					if (count($parts) == 2)
					{
						if (preg_match("/ETag/", $parts[0]))
						{
							$ETag = $parts[1];
						}
						
						if (preg_match("/Last-Modified/", $parts[0]))
						{
							$LastModified = $parts[1];
						}
						
					}
				}
				
				// Store in database
				$sql = 'UPDATE feed SET last_modified=' . $db->qstr($LastModified) . ', etag=' . $db->qstr($ETag) 
					. ' WHERE (url = "' . $url . '")';
	
				$sql_result = $db->Execute($sql);
				if ($sql_result == false) die("failed [" . __LINE__ . "]: " . $sql);
			}

		}
		 
	}
	return $result;
}

/*

// test


//	$url = "http://localhost/~rpage/ants/rss/Formicidae.rss";
//	$url = 'http://www.connotea.org/rss/tag/phylogeny';
	$url = 'http://names.ubio.org/rss/rss_feed.php?username=rdmpage&rss1=1';
	
	$rss = '';
	$msg = '';

	$result = GetRSS ($url, &$rss, true);
	if ($result == 0)
	{
		//echo $rss;
		
		if ($result == 0) 
		{ 
			$msg = 'OK';
		}
	}
	else
	{
		switch ($result)
		{
			case 304: $msg = 'Feed has not changed since last fetch (' . $result . ')'; break;
			default: $msg = 'Badness happened (' . $result . ')'; break;
		}	
	}

	echo $msg;

*/
?>