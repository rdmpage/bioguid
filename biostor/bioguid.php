<?php

/**
 * @file bioguid.php
 *
 * Encapsulate http://bioguid.info/ web services
 *
 */
 
require_once (dirname(__FILE__) . '/lib.php'); 

//--------------------------------------------------------------------------------------------------
/**
 * @brief Lookup journal ISSN from title using bioGUID web service
 *
 * @param title Title of journal
 *
 * @return ISSN if found, empty string if not found, or if lookup fails
 *
 */
function issn_from_title($title)
{
	$issn = '';
	$url = 'http://bioguid.info/services/journalsuggest.php?title=' . urlencode($title);
	$json = get($url);
		
	if ($json != '')
	{
		$obj = json_decode($json);
		if (count($obj->results) > 0)
		{
			$issn = $obj->results[0]->issn;
		}
	}
	return $issn;
} 

?>