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

//--------------------------------------------------------------------------------------------------
// Find the majority_rule path, on the asusmption that this is the best way to represent
// what taxon a set of paths represents. We use majority rule (starting from the base of the path)
// as this should ensure that we have a real path
function majority_rule_path($paths)
{	 
	$n = array();
	foreach ($paths as $p)
	{
		$nodes= explode("/", $p);
		
		$num = count($nodes);
		for ($i = 1; $i < $num; $i++)
		{
			if (!isset($n[$i]))
			{
				$n[$i] = array();
			}
			if (isset($n[$i][$nodes[$i]]))
			{
				$n[$i][$nodes[$i]]++;
			}
			else
			{
				$n[$i][$nodes[$i]] = 1;
			}
		}		
		
	}
	// find majority rule taxon...
	
	$num_taxa = count($paths);
	$threshold = round($num_taxa/2);
	if ($num_taxa % 2 == 0)
	{
		$threshold++;
	}
	
	$path_string = '';
	foreach ($n as $level)
	{
		$level_count = 0;
		foreach ($level as $k => $v)
		{
			if ($v >= $threshold)
			{
				$path_string .= '/' . $k;
				$level_count = 1;
			}
		}
		if ($level_count == 0)
		{
			break;
		}
	}
	return $path_string;
}

?>