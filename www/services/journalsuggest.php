<?php

// $Id: journalJSON.php,v 1.1 2008/02/19 10:26:53 rdmp1c Exp $

// JSON webservice to return list of journal titles and ISSNs matching a user-supplied title

require ('../config.inc.php');
require_once('../' . $config['adodb_dir']);


function getJournal($str)
{
	global $config;
	global $ADODB_FETCH_MODE;
		
	$db = NewADOConnection('mysql');
	$db->Connect("localhost", 
		$config['db_user'], $config['db_passwd'], $config['db_name']);
	
	// Ensure fields are (only) indexed by column name
	$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
	
	$str = str_replace (' ', '%', $str);
	$str .= '%';
		
	$sql = 'SELECT * FROM issn
		WHERE title LIKE ' .  $db->Quote($str) .'
		ORDER BY title
		LIMIT 10';

	$result = $db->Execute($sql);
	if ($result == false) die("failed"); 
		
	$result = $db->Execute($sql);
	if ($result == false) die("failed");  
	
	$json = "{\"results\":\n[";
	
	$count = 0;
	while (!$result->EOF) 
	{
		if ($count >0)
		{
			$json .= ",";
		}
		$json .= "\n";
		$json .= "{";
		
		// fix quotes
		$title = $result->fields['title'];
		$title = str_replace("'", "\\\'", $title);
		$title = str_replace('"', '\"', $title);
		
		// Note UTF-8 encoding
//		$json .= '"title":"' . utf8_encode($title) . '",';

		// Use as-is because table is in UTF-8

		$json .= '"title":"' . $title . '",';
		$json .= '"issn":"' . $result->fields['issn'] . '",';
		$json .= '"language_code":"' . $result->fields['language_code'] . '"';
		$json .= "}";
		$count++;

		$result->MoveNext();
	}
	$json .= "\n]\n}\n";
	return $json;
}

$json = '';
if (isset($_GET['title']))
{
	$str = $_GET['title'];
	$json = getJournal($str);
}
else
{
	$json = '{results:[]}';
}

$callback = '';
if (isset($_GET['callback']))
{
	$callback = $_GET['callback'];
	$json = $callback . '(' . $json . ')';
}


header ("Content-type: text/plain; charset=utf-8\n\n");
echo $json;

?>

