<?php

//--------------------------------------------------------------------------------------------------
// MySQL
require_once(dirname(__FILE__).'/adodb5/adodb.inc.php');
require_once(dirname(__FILE__).'/config.inc.php');

function get_name($str, $limit = 10)
{
	global $config;
	global $ADODB_FETCH_MODE;
		
	$obj = new stdclass;
	$obj->results = array();
	$obj->num_results = 0;		
		
	$db = NewADOConnection('mysql');
	$db->Connect("localhost", 
		$config['db_user'], $config['db_passwd'], $config['db_name']);
	
	// Ensure fields are (only) indexed by column name
	$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
	
	$nameComplete = $str . '%';
	
	$sql = 'SELECT COUNT(guid) as c FROM ion_lookup
		WHERE (name LIKE ' .  $db->Quote($nameComplete) . ')';
		
	$result = $db->Execute($sql);
	if ($result == false) die("failed $sql"); 
	
	$obj->num_results = $result->fields['c'];
			
	$sql = 'SELECT * FROM ion_lookup
		WHERE (name LIKE ' .  $db->Quote($nameComplete) . ')
		ORDER BY name
		LIMIT ' . $limit;
	
	$result = $db->Execute($sql);
	if ($result == false) die("failed"); 

	$count = 0;
	while (!$result->EOF) 
	{
		$item = new stdclass;
		$item->id = $result->fields['guid'];
		$item->name = $result->fields['name'];
		$item->authorship = $result->fields['taxonAuthor'];
		array_push($obj->results, $item);
		
		$result->MoveNext();
	}
	return $obj;
}


$json = '';
if (isset($_GET['name']))
{
	$str = $_GET['name'];
	$json = json_encode(get_name($str));
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


//header ("Content-type: text/plain; charset=utf-8\n\n");
echo $json;

?>
