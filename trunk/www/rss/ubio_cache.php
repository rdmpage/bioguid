<?php

require_once (dirname(__FILE__) . '/config.inc.php');
require_once($config['adodb_dir']);

$db = NewADOConnection('mysql');
$db->Connect("localhost", 
	$config['db_user'] , $config['db_passwd'] , $config['db_name']);

// Ensure fields are (only) indexed by column name
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;


//--------------------------------------------------------------------------------------------------
function find_ubio_name_in_cache($name, $has_authority=false, $exact=false)
{
	global $db;
	
	$namebankID = array();

	$sql = 'SELECT * FROM ubio_cache WHERE ';
	if($has_authority || $exact)
	{
		$sql .= 'fullNameString = ' . $db->qstr($name);
	}
	else
	{
		$sql .= 'nameString = ' . $db->qstr($name);
	}

	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
	
	while (!$result->EOF) 
	{
		array_push($namebankID, $result->fields['namebankID']);
		$result->MoveNext();				
	}
	
	return $namebankID;
}

//--------------------------------------------------------------------------------------------------
/**
 * @brief Store in our cache a name record retrieved using uBio's SOAP service
 *
 */
function store_ubio_name($r)
{
	global $db;
	
	# check whe haven't already stored this, as search may return names we've already encountered
	$sql = 'SELECT * from ubio_cache WHERE namebankID=' . $r['namebankID'];
	
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);

	if ($result->NumRows() == 0)
	{
	
		$sql = 'INSERT INTO ubio_cache'
			.'(namebankID,nameString,fullNameString,packageID,packageName,basionymUnit,rankID,rankName) '
			. 'VALUES ('
			. $r['namebankID']
			. ',' . $db->qstr(base64_decode($r['nameString']))
			. ',' . $db->qstr(base64_decode($r['fullNameString']))
			. ',' . $r['packageID']
			. ',' . $db->qstr($r['packageName'])
			. ',' . $r['basionymUnit']
			. ',' . $r['rankID']
			. ',' . $db->qstr($r['rankName'])
			.')';
			
		$result = $db->Execute($sql);
		if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
	}
}


//--------------------------------------------------------------------------------------------------
function get_matching_names_from_cache($name)
{
	global $db;
	
	$names = array();
	
	$sql = 'SELECT * from ubio_cache 
	WHERE (nameString=' . $db->qstr($name)  . ')';
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);

	while (!$result->EOF) 
	{
		$r = array();
		$r['namebankID'] 		= $result->fields['namebankID'];
		$r['nameString'] 		= $result->fields['nameString'];
		$r['fullNameString'] 	= $result->fields['fullNameString'];
		$r['packageID'] 		= $result->fields['packageID'];
		$r['packageName'] 		= $result->fields['packageName'];
		$r['basionymUnit']		= $result->fields['basionymUnit'];
		$r['rankID'] 			= $result->fields['rankID'];
		$r['rankName'] 			= $result->fields['rankName'];
		
		array_push ($names, $r);
		$result->MoveNext();				
	}
	
	return $names;	
}



?>