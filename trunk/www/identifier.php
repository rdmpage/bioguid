<?php

/**
 * @file identifier.php
 *
 * Define identifiers
 *
 */

require_once('ISBN-ISSN.php');

define('IDENTIFIER_UNKNOWN', 	0);	
define('IDENTIFIER_DOI', 		1);	
define('IDENTIFIER_LSID', 		2);	
define('IDENTIFIER_GENBANK',	3);	
define('IDENTIFIER_DIGIR',		4);	
define('IDENTIFIER_PUBMED',		5);	
define('IDENTIFIER_HANDLE',		6);	
define('IDENTIFIER_TAXID',		7);	
define('IDENTIFIER_ISSN',		8);	
define('IDENTIFIER_SICI',		9);	
define('IDENTIFIER_URL',	   10);	
define('IDENTIFIER_GI',	       11);	
define('IDENTIFIER_OCLC',	   12);	


//--------------------------------------------------------------------------------------------------
/**
 * @brief Classify an identifier string
 *
 * Use regular expressions to match (and clean) the identifier.
 *
 * @return Array 
 */
function IdentifierKind($id)
{
	$result = array();
	$identifierType = IDENTIFIER_UNKNOWN;
	
	$identifierString = $id;
	$matches = array();
	
	// DOI
	if (preg_match ('/^(http:\/\/dx.doi.org\/|doi:|info:doi\/|info:doi\/http:\/\/dx.doi.org\/)?(10.[0-9]*\/(.*))/i', $identifierString, $matches))
	{
		$identifierString = $matches[2];
		$identifierType = IDENTIFIER_DOI;		
	}
	
	if (IDENTIFIER_DOI != $identifierType)
	{	
		// Handle
		if (preg_match ('/^(http:\/\/hdl.handle.net\/|hdl:|info:hdl\/)?(([0-9][0-9]*(.[0-9]*)?)\/(.*))/i', $identifierString, $matches))
		{
			$identifierString = $matches[2];
			$identifierType = IDENTIFIER_HANDLE;		
		}
	}
	
	// SICI
	if (preg_match ('/^(http:\/\/links.jstor.org\/sici\?sici=|info:sici\/|sici:)(.*)/i', $identifierString, $matches))
	{
		$identifierString = $matches[2];
		$identifierType = IDENTIFIER_SICI;		
	}
	
	
	// PubMed
	if (preg_match('/http:\/\/www.ncbi.nlm.nih.gov/', $identifierString))
	{
		preg_match('/list_uids=([0-9]+)/', $identifierString, $matches);
	
		//print_r($matches);
		if (isset($matches[1]))
		{
			$identifierString = $matches[1];
			$identifierType = IDENTIFIER_PUBMED;		
		}
		
		if (preg_match('/http:\/\/www.ncbi.nlm.nih.gov\/pubmed\/([0-9]+)/', $identifierString, $matches))
		{
			$identifierString = $matches[1];
			$identifierType = IDENTIFIER_PUBMED;		
		}
	}
	


	
	if (preg_match ('/^(pmid:|info:pmid\/)([0-9]*)/i', $identifierString, $matches))
	{
		$identifierString = $matches[2];
		$identifierType = IDENTIFIER_PUBMED;		
	} 
	
	if (preg_match ('/^(genbank:|info:ddbj-embl-genbank\/)(.*)/i', $identifierString, $matches))
	{
		$identifierString = $matches[2];
		$identifierType = IDENTIFIER_GENBANK;		
	}
	
	if (preg_match ('/^(gi:)([0-9]*)/i', $identifierString, $matches))
	{
		$identifierString = $matches[2];
		$identifierType = IDENTIFIER_GI;		
	} 
	
	
	// LSIDs
	if (preg_match("/^(lsidres:)?([uU][rR][nN]:[lL][sS][iI][dD]:([A-Za-z0-9][\w\(\)\+\,\-\.\=\@\;\$\"\!\*\']*):([A-Za-z0-9][\w\(\)\+\,\-\.\=\@\;\$\"\!\*\']*):[A-Za-z0-9][\w\(\)\+\,\-\.\=\@\;\$\"\!\*\']*(:[A-Za-z0-9][\w\(\)\+\,\-\.\=\@\;\$\"\!\*\']*)?)$/", $identifierString, $matches))
	{
		// 3 is the authority, 4 is the namespace
		$identifierString = $matches[2];
		$identifierType = IDENTIFIER_LSID;		
		
	}
	
	// ISSN
	if (IDENTIFIER_UNKNOWN == $identifierType)
	{		
		$ISSN_proto = $id;
		
		// strip any prefix
		$ISSN_proto = preg_replace("/^issn:/i", '', $ISSN_proto);
		
		$clean = ISN_clean($ISSN_proto);
		
		$class = ISSN_classifier($clean);
		if ($class == "checksumOK")
		{
			$identifierString = canonical_ISSN($ISSN_proto);
			$identifierType = IDENTIFIER_ISSN;		
		}
	}
	
	
	// URL
	if (IDENTIFIER_UNKNOWN == $identifierType)
	{		
		if (preg_match ('/^(http:\/\/(.*))/i', $identifierString, $matches))
		{
			$identifierString = $matches[2];
			$identifierType = IDENTIFIER_URL;		
		}
	}	
	
	// OCLC
	if (IDENTIFIER_UNKNOWN == $identifierType)
	{		
		if (preg_match ('/^(info:oclcnum\/)(.*)/i', $identifierString, $matches))
		{
			$identifierString = $matches[2];
			$identifierType = IDENTIFIER_OCLC;		
		}
	}		
	
	
	$result['identifier_type'] = $identifierType;
	$result['identifier_string'] = $identifierString;
	
	//print_r($result);
	
	return $result;
	
}

?>