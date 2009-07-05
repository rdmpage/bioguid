<?php

// $Id: $

/* CrossRef */

require_once ('db.php');
require_once ('issn-functions.php');
require_once ('lib.php');

//--------------------------------------------------------------------------------------------------
// True if CrossRef is likely to have metadata for this article
function in_crossref($issn, $date = '', $volume = '')
{
	global $db;
	global $debug;
	
	$found = false;
	
	$sql = 'SELECT * FROM crossref
		WHERE (issn = ' .  $db->Quote($issn) . ')';
		
	if ($date != '')
	{
		$sql .= ' AND (start_date <= ' . $db->Quote($date) . ')';
	}
	if ($volume != '')
	{
		$sql .= ' AND (start_volume <= ' . $db->Quote($volume) . ')';
	}
	$sql .= ' LIMIT 1';
	
	//echo $sql;
	
	$result = $db->Execute($sql);
	if ($result == false) die("failed"); 
	
	$found = ($result->NumRows() == 1);
	
	if ($debug)
	{
		echo '<pre style="text-align: left;border: 1px solid #c7cfd5;background: #f1f5f9;padding:15px;">';
		echo 'Looking for journal in CrossRef - ';
		if (!$found)
		{
			echo ' not';
		}
		echo ' found' . "\n";
		echo "</pre>";
	}

	return $found;
}


//--------------------------------------------------------------------------------------------------
/**
 *@brief DOI search using CrossRef's OpenURL service
 *
 * We use CrossRef's OpenURL service to look for a DOI matching this
 * record. If successful, we return the DOI.
 *
 * @return DOI if found.
 */
function search_for_doi($issn, $volume, $page, $genre, &$item)
{
	global $config;
	global $debug;

	$doi = '';
	$url = 'http://www.crossref.org/openurl?';

	$url .=  "pid=" . $config['crossref_user'] . ":" . $config['crossref_pass'];

	$url .= "&genre=" . $genre;
	$url .= "&issn=" . $issn;
	$url .= "&volume=" . $volume;

	// Pages
	$url .= "&spage=" . $page;	

	$url .= "&noredirect=true";
	$url .= "&format=unixref";
	
	if ($debug)
	{
		echo '<pre style="text-align: left;border: 1px solid #c7cfd5;background: #f1f5f9;padding:15px;">';
		echo $url;
		echo "</pre>";
	}
	
	//echo $url;

	$xml = get($url);
	
	if ($debug)
	{
		echo '<pre style="text-align: left;border: 1px solid #c7cfd5;background: #f1f5f9;padding:15px;">';
		echo htmlentities($xml);
		echo "</pre>";
	}
	
	if (preg_match('/<doi_records/', $xml))
	{
		// Did we get a hit?
		
		$ok = true;
		
		$xml = str_replace("\n", "", $xml);
		$xml = str_replace("\r", "", $xml);
		$xml = preg_replace('/\s\s+/', " ", $xml);
		
		
		$dom= new DOMDocument;
		$dom->loadXML($xml);
		$xpath = new DOMXPath($dom);

		$nodeCollection = $xpath->query ("//crossref/error");
		foreach($nodeCollection as $node)
		{
			$ok = false;
		}
		if ($ok)
		{
			// Get JSON
			$xp = new XsltProcessor();
			$xsl = new DomDocument;
			$xsl->load('xsl/unixref2JSON.xsl');
			$xp->importStylesheet($xsl);
			
			$xml_doc = new DOMDocument;
			$xml_doc->loadXML($xml);
			
			$json = $xp->transformToXML($xml_doc);
			
			//echo $json;
			
			//echo json_format($json);
			
		
			$item = json_decode($json);
			
			// Ensure metadata is OK (assumes a journal for now)
			if (!isset($item->issn))
			{
				$issn = '';
				if (isset($item->title))
				{
					$issn = issn_from_journal_title($item->title);
				}
				if ($issn == '')
				{
					if (isset($item->eissn))
					{
						$issn = $item->eissn;
					}
				}
				if ($issn != '')
				{
					$item->issn = $issn;
				}
			}
			
			
			
			$doi = $item->doi;
		}			
	}

	return $doi;

}


//--------------------------------------------------------------------------------------------------
// Get metadata for a given DOI
function doi_metadata ($doi, &$item)
{
	global $config;
	global $debug;
	
	$ok = false;
	
	$url = "http://www.crossref.org/openurl"
		. "?pid=" . $config['crossref_user'] . ":" . $config['crossref_pass']
		. "&rft_id=info:doi/$doi&noredirect=true"
		. "&format=unixref";
	
	//echo $url;

	$xml = get($url);
	
	if ($debug)
	{
		echo '<pre style="text-align: left;border: 1px solid #c7cfd5;background: #f1f5f9;padding:15px;">';
		echo htmlentities($xml);
		echo "</pre>";
	}
	
	//echo mb_detect_encoding($xml);
		
	if (preg_match('/<doi_record/', $xml))
	{
		// Did we get a hit?
				
		$ok = true;
		
		// remove
		$xml = str_replace('xmlns="http://www.crossref.org/xschema/1.0"', '', $xml);
		$xml = str_replace('xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"', '', $xml);
		
		// strip end of lines (CSIRO sometimes has this, and it kills the JSON decoding
		$xml = str_replace("\n", "", $xml);
		$xml = str_replace("\r", "", $xml);
		$xml = preg_replace('/\s\s+/', " ", $xml);
		
		//echo $xml;
		
		
		$dom= new DOMDocument;
		$dom->loadXML($xml);
		$xpath = new DOMXPath($dom);

		$nodeCollection = $xpath->query ("//crossref/error");
		foreach($nodeCollection as $node)
		{
			$ok = false;
		}
		if ($ok)
		{
			// Get JSON
			$xp = new XsltProcessor();
			$xsl = new DomDocument;
			$xsl->load('xsl/unixref2JSON.xsl');
			$xp->importStylesheet($xsl);
			
			$xml_doc = new DOMDocument;
			$xml_doc->loadXML($xml);
			
			$json = $xp->transformToXML($xml_doc);
			
			
			//echo $json;
			
			
			$item = json_decode($json);
			
			// post process
			// Ensure metadata is OK (assumes a journal for now)
			if (!isset($item->issn))
			{
				$issn = '';
				if (isset($item->title))
				{
					$issn = issn_from_journal_title($item->title);
				}
				if ($issn == '')
				{
					if (isset($item->eissn))
					{
						$issn = $item->eissn;
					}
				}
				if ($issn != '')
				{
					$item->issn = $issn;
				}
			}
			
			if ($debug)
			{
				print_r($item);
			}
		}			
	}

	return $ok;

}

//--------------------------------------------------------------------------------------------------
// Does DOI exist?
function doi_exists ($doi)
{
	$item = new stdClass;
	$exists = doi_metadata($doi, $item);
	return $exists;
}


?>
