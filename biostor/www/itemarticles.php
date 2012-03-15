<?php

require_once ('../bhl_utilities.php');
require_once ('../reference.php');

$ItemID = -1;

if (isset($_GET['item']))
{
	$ItemID = $_GET['item'];
}

// handle possible synonym
if (isset($_GET['ItemID']))
{
	$ItemID = $_GET['ItemID'];
}

$callback = '';

if (isset($_GET['callback']))
{
	$callback = $_GET['callback'];
}

$obj = new stdclass;

$bhl_pages = bhl_retrieve_item_pages($ItemID);

$sql = 'SELECT * FROM bhl_title INNER JOIN bhl_item USING(TitleID) WHERE ItemID=' . $ItemID . ' LIMIT 1';

$result = $db->Execute($sql);
if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

if ($result->NumRows() == 1)
{
	$obj->ItemID 		= $result->fields['ItemID'];
	$obj->FullTitle		= $result->fields['FullTitle'];
	$obj->VolumeInfo	= $result->fields['VolumeInfo'];
}
else
{
}

if (isset($obj->ItemID))
{
	$ids = array();
	
	$sql = 'SELECT rdmp_reference.reference_id, page.SequenceOrder 
	FROM rdmp_reference_page_joiner
	INNER JOIN page USING(PageID) 
	INNER JOIN rdmp_reference USING(PageID)
	WHERE ItemID=' . $obj->ItemID . '
	ORDER BY CAST(page.SequenceOrder AS SIGNED)';
	
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
	
	while (!$result->EOF) 
	{	
		$ids[] = $result->fields['reference_id'];
		$result->MoveNext();		
	}
	
	$obj->articles = array();
	
	foreach ($ids as $reference_id)
	{
		$reference = db_retrieve_reference($reference_id);
		
		if (isset($reference->oclc))
		{
			if ($reference->oclc == 0)
			{
				unset($reference->oclc);
			}
		}
		if (isset($reference->sici))
		{
			unset($reference->sici);
		}
		if (isset($reference->reference_cluster_id))
		{
			unset($reference->reference_cluster_id);
		}		
		
		// get pages
		$pages = bhl_retrieve_reference_pages($reference_id);
		foreach ($pages as $page)
		{
			$reference->bhl_pages[] = (Integer)$page->PageID;
		}
		
		$obj->articles[] = $reference;
	}
}

header('Content-type: text/plain');
if ($callback != '')
{
	echo $callback .'(';
}
echo json_format(json_encode($obj));
if ($callback != '')
{
	echo ')';
}

?>