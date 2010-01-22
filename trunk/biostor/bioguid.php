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

//--------------------------------------------------------------------------------------------------
/**
 * @brief Lookup taxon name in uBio using bioGUID web service
 *
 * @param name Name to look for
 *
 * @return NameBankID if found, 0 if name not found
 *
 */
function bioguid_ubio_search($name)
{
	$NameBankID = 0;

	$url = 'http://bioguid.info/ubiosearch.php?name=' . urlencode($name);
	$json = get($url);
	if ($json != '')
	{
		$ids = json_decode($json);
		if (count($ids) == 1)
		{
			$NameBankID = $ids[0];	
		}
	}
	return $NameBankID;
}


?>