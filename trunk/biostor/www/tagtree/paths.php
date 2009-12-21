<?php

/**
 * @file paths.php
 *
 * Extract paths from tree from local copy of Catalogue of Life database
 *
 */

//--------------------------------------------------------------------------------------------------
// MySQL

$dir = dirname(__FILE__);
$root_dir = str_replace('/www/tagtree', '', $dir);

require_once ($root_dir . '/db.php');

//--------------------------------------------------------------------------------------------------
// Get key-value name-path pairs for a list of names
function get_paths($names)
{
	global $db;
	
	$paths = array();
	$names = array_unique($names);
	
	foreach ($names as $name)
	{
		$sql = 'SELECT name, path FROM col_tree
INNER JOIN taxa USING(record_id)
WHERE (name =  ' . $db->qstr($name) . ') LIMIT 1';
		
		$result = $db->Execute($sql);
		if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
		
		if ($result->NumRows() != 0)
		{
			$paths[$name] = $result->fields['path'];
		}
	}
	
	return $paths;
}

//--------------------------------------------------------------------------------------------------
// Get key-value name-path pairs for a list of paths
function get_names($path_list)
{
	global $db;
	
	$paths = array();
	$path_list = array_unique($path_list);
	
	foreach ($path_list as $path)
	{
		$sql = 'SELECT name, path FROM col_tree
INNER JOIN taxa USING(record_id)
WHERE (path =  ' . $db->qstr($path) . ') LIMIT 1';
		
		$result = $db->Execute($sql);
		if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
		
		if ($result->NumRows() != 0)
		{
			$paths[$result->fields['name']] = $path;
		}
	}
	
	return $paths;
}


?>