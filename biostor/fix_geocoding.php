<?php

// geocode a reference
require_once (dirname(__FILE__) . '/db.php');
require_once (dirname(__FILE__) . '/lib.php');
require_once (dirname(__FILE__) . '/geocoding.php');
require_once (dirname(__FILE__) . '/bhl_text.php');

$ids = array(55225);

foreach ($ids as $reference_id)
{
	global $db;
	
	echo $reference_id . "\n";
	// remove any existing geocoding...
	
	$pages = bhl_retrieve_reference_pages($reference_id);
	
	foreach ($pages as $page)
	{
		$sql = 'DELETE FROM rdmp_locality_page_joiner WHERE PageID=' . $page->PageID;
		$result = $db->Execute($sql);
		if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
	}	
	
	// redo
	bhl_geocode_reference($reference_id);
	
}


?>