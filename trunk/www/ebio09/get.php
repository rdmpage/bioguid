<?php

require_once (dirname(__FILE__) . '/config.inc.php');
require_once ($config['adodb_dir']);


function get_one_item($guid)
{
	global $config;
	global $ADODB_FETCH_MODE;
	
	$item = '';
	
	$db = NewADOConnection('mysql');
	$db->Connect("localhost", 
		$config['db_user'] , $config['db_passwd'] , $config['db_name']);
	
	// Ensure fields are (only) indexed by column name
	$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
	
	// get list of localities
	$sql = 'SELECT * FROM item
	WHERE (guid=' . $db->qstr($guid) . ') LIMIT 1';
	
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);

	if ($result->NumRows() == 1) 
	{
		$item .= '<strong>' . $result->fields['title'] . '</strong>';
		$item .= '' . $result->fields['description'] . '';
		$item = utf8_encode($item);
	}
	
	return $item;
}


$guid = '';
if (isset($_GET['guid']))
{
	$guid = $_GET['guid'];
}

header('Content-Type:text/html; charset=UTF-8');
echo get_one_item($guid);

?>