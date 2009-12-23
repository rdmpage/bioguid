<?php

/**
 * @file col.php
 *
 * Catalogue of Life
 *
 */
 
require_once (dirname(__FILE__) . '/col.php');

 
function col_references_for_name($name)
{
	global $db;
	
	$references = array();
	
	$sql = 'SELECT DISTINCT(`references`.record_id), author, year, title, source, reference_type
	FROM taxa
INNER JOIN scientific_name_references USING(name_code)
INNER JOIN `references` ON scientific_name_references.reference_id = `references`.record_id
WHERE taxa.name=' . $db->qstr($name) . '
ORDER BY `references`.year';

	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

	while (!$result->EOF) 
	{
		$reference = new stdclass;
		
		$reference->record_id = $result->fields['record_id'];
		$reference->author = $result->fields['author'];
		$reference->year = $result->fields['year'];
		$reference->title = $result->fields['title'];
		$reference->source = strip_tags($result->fields['source']);
		$reference->reference_type = $result->fields['reference_type'];
	
		$references[] = $reference;

		$result->MoveNext();				
	}

	return $references;
}

function col_name_from_lsid($lsid)
{
	global $db;
	
	$col_taxon = new stdclass;

	$sql = 'SELECT * FROM taxa WHERE lsid=' . $db->qstr($lsid) . ' LIMIT 1';

	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
	
	if ($result->NumRows() == 1)
	{
		$col_taxon->name_code = $result->fields['name_code'];
		$col_taxon->record_id = $result->fields['record_id'];
		$col_taxon->author = $result->fields['name'];
	}
	
	return $col_taxon;
}

function col_accepted_name_for($name)
{
	global $db;
	
	$col_taxon = new stdclass;
	
	if (!preg_match('/^\w+ \w+/', $name))
	{
		return $col_taxon;
	}
	
	$sql = 'SELECT * FROM scientific_names WHERE ';
	
	$parts = explode(" ", trim($name));
	
	if (isset($parts[0]))
	{
		$sql .= '(genus=' . $db->qstr($parts[0]) . ')';
	}
	if (isset($parts[1]))
	{
		$sql .= ' AND (species=' . $db->qstr($parts[1]) . ')';
	}
	if (isset($parts[2]))
	{
		$sql .= ' AND (infraspecies=' . $db->qstr($parts[2]) . ')';
	}
	$sql .= ' LIMIT 1';
		
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

	if ($result->NumRows() == 1)
	{
		if ($result->fields['is_accepted_name'] == 0)
		{
			$sql = 'SELECT * FROM scientific_names WHERE name_code = ' . $db->qstr($result->fields['accepted_name_code']) . ' LIMIT 1';
			
			$result = $db->Execute($sql);
			if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
			
			if ($result->NumRows() == 1)
			{
				$col_taxon->name_code = $result->fields['name_code'];
				$col_taxon->record_id = $result->fields['record_id'];
				$col_taxon->author = $result->fields['author'];
				$col_taxon->name = trim($result->fields['genus'] . ' ' . $result->fields['species']	. ' ' . $result->fields['infraspecies']);	
			}
		}
		else
		{
			$col_taxon->name_code = $result->fields['name_code'];
			$col_taxon->record_id = $result->fields['record_id'];
			$col_taxon->author = $result->fields['author'];
			$col_taxon->name = trim($result->fields['genus'] . ' ' . $result->fields['species']	. ' ' . $result->fields['infraspecies']);	
		}		
	}
	
	return $col_taxon;
	
}
/*
function col_synonyms_for_name($name)
{
	global $db;
		
	$synonyms = array();
	
	$sql = 'SELECT * FROM taxa
INNER JOIN scientific_name_references USING(name_code)
INNER JOIN `references` ON scientific_name_references.reference_id = `references`.record_id
WHERE taxa.name=' . $db->qstr($name);

	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

	while (!$result->EOF) 
	{
		$reference = new stdclass;
		
		$reference->record_id = $result->fields['record_id'];
		$reference->author = $result->fields['author'];
		$reference->year = $result->fields['year'];
		$reference->title = $result->fields['title'];
		$reference->source = $result->fields['source'];
	
		$references[] = $reference;

		$result->MoveNext();				
	}

	return $references;
}
*/

?>