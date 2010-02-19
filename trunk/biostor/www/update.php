<?php

/**
 * @file update.php
 *
 * Update a database record, with checks that user has permission, etc.
 * Permission might be recaptcha, for example.
 *
 * Called as a Ajax web service with variables passed in $_POST, returns JSON response
 *
 */

$can_update = false;
$response = new stdclass;
$response->is_valid = false;

require_once ('../config.inc.php');
require_once ('../db.php');
require_once ('../nameparse.php');
require_once ('../recaptcha-php-1.10/recaptchalib.php');

if (!isset($_POST))
{
	header('HTTP/1.1 404 Not Found');
	header('Status: 404 Not Found');
	$_SERVER['REDIRECT_STATUS'] = 404;	
	
	echo "No variables passed to web service";
	
	exit();
}

// Check recaptcha
if (isset($_POST['recaptcha_response_field']))
{
	$response = recaptcha_check_answer ($config['recaptcha_privatekey'],
									$_SERVER["REMOTE_ADDR"],
									$_POST["recaptcha_challenge_field"],
									$_POST["recaptcha_response_field"]);
									
	$can_update = $response->is_valid;
}

// Are we storing (e.g., called by openurl.php) or updating (called by display_reference.php)
$updating = false;
if (isset($_POST['update']))
{
	$updating = true;
	$response->updating = 1;
}

if ($can_update)
{
	// update
	$reference = new stdclass;
	$reference->authors = array();
	$PageID = 0;
	
	foreach ($_POST as $key => $value)
	{
		switch ($key)
		{
			case 'genre':
			case 'title':
			case 'secondary_title':
			case 'volume':
			case 'series':
			case 'issue':
			case 'spage':
			case 'epage':
			case 'date':
			case 'year':
			case 'issn':
			case 'url':
			case 'doi':
			case 'lsid':
			case 'oclc':
				$reference->{$key} = stripcslashes($value);
				break;
				
			case 'PageID':
				$PageID = $value;
				break;

			case 'authors':
				$value = trim($value);
				if ($value != '')
				{
					$authors = explode("\n", $value);
					foreach ($authors as $v)
					{
						$parts = parse_name($v);					
						$author = new stdClass();
						if (isset($parts['last']))
						{
							$author->lastname = $parts['last'];
						}
						if (isset($parts['suffix']))
						{
							$author->suffix = $parts['suffix'];
						}
						if (isset($parts['first']))
						{
							$author->forename = $parts['first'];
							
							if (array_key_exists('middle', $parts))
							{
								$author->forename .= ' ' . $parts['middle'];
							}
						}
						$reference->authors[] = $author;
					}
				}
				break;
						
			default:
				break;
		} 
	}
	
	db_store_article($reference, $PageID, $updating);
}

header("Content-type: text/plain; charset=utf-8\n\n");
echo json_encode($response);

?>