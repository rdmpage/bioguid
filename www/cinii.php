<?php

// $Id: $

/**
 * @file cinii.php OpenURL queries to CiNii http://ci.nii.ac.jp/
 *
 */

require_once (dirname(__FILE__) . '/db.php');
require_once (dirname(__FILE__) . '/issn-functions.php');
require_once (dirname(__FILE__) . '/lib.php');


//--------------------------------------------------------------------------------------------------
function cinii_rdf($rdf_url, &$item, $issn='', $debug = 0)
{
	$result = 0;
	
	$debug = 0;
	
	$rdf = get($rdf_url);
	
	// convert...
	$dom= new DOMDocument;
	$dom->loadXML($rdf);
	$xpath = new DOMXPath($dom);

	// Get JSON
	$xp = new XsltProcessor();
	$xsl = new DomDocument;
	$xsl->load('xsl/cinii.xsl');
	$xp->importStylesheet($xsl);
	
	$xml_doc = new DOMDocument;
	$xml_doc->loadXML($rdf);
	
	$json = $xp->transformToXML($xml_doc);
	
	if ($debug)
	{
		echo $json;
	}
	
	$item = json_decode($json);
	
	if ($debug)
	{
		print_r($item);
	}
	
	// Ensure we have ISSN (might not be in the metadata)
	if (!isset($item->issn) != '')
	{
		if ($issn != '')
		{
			$item->issn = $issn;
		}
		else
		{
			if (isset($item->title))
			{
				$issn = issn_from_journal_title($item->title);
				if ($issn != '')
				{
					$item->issn = $issn;
				}
			}
		}
	}
	
	// Check we have journal name
	if ($item->title == '')
	{
		$item->title = journal_title_from_issn($item->issn);
	}
		
	// Id
	$item->publisher_id = str_replace('http://ci.nii.ac.jp/naid/', '', $item->url);

	// Do some cleaning of authors
	foreach ($item->authors as $a)
	{
		// Last name in ALL CAPS
		if (preg_match('/^(?<lastname>[A-Z]+),?\s*(?<forename>[A-Z](.*)$)/', $a->author, $matches))
		{
			$a->lastname = mb_convert_case($matches['lastname'], MB_CASE_TITLE, mb_detect_encoding($matches['lastname']));
			$a->forename = mb_convert_case($matches['forename'], MB_CASE_TITLE, mb_detect_encoding($matches['forename']));
		}
		else
		{
			$parts = explode (",",  $a->author);
			$a->lastname = trim($parts[0]);
			$a->forename = trim($parts[1]);
		}
	}
	
	if ($debug)
	{
		print_r($item);
	}
	
	return $result;
}


//--------------------------------------------------------------------------------------------------
/**
 *
 * http://ci.nii.ac.jp/info/en/if_link_receive.html
 *
 * @brief Retrieve metadata from CiNii using their OpenURL resolver
 *
 * CiNii supports RDF and embeds links to it the HEAD of the HTML document, e.g.
 *
 * <link rel="meta" type="application/rdf+xml" title="RDF" href="http://ci.nii.ac.jp/naid/110003352408/rdf" />
 *
 * We do an OpenURL lookup (http://ci.nii.ac.jp/openurl/query), extract the RDF link, then convert 
 * the RDF to json
 *
 * @param jtitle Journal title
 * @param issn Journal ISSN
 * @param volume Journal volume
 * @param spage Article starting page
 * @param debug 1 to display debugging info
 *
 * @return True if article found, false otherwise
 */
function search_cinii($jtitle, $issn, $volume, $spage, &$item, $debug = 0)
//function search_cinii($jtitle, $issn, $volume, $spage, &$item)
{
	global $config;
	$found = false;
	//$debug = 0;
	
	$item->authors = array();
		
	$url = 'http://ci.nii.ac.jp/openurl/query?ctx_ver=Z39.88-2004&url_ver=Z39.88-2004';

	$url .= '&rft_val_fmt=info%3aofi%2ffmt%3akev%3amtx%3ajournal'; // article
	$url .= '&ctx_enc=info%3aofi%2fenc%3aUTF-8'; // URL is UTF-8 encoded

//	$url .= '&rft.date=' . $date;
	$url .= '&rft.volume=' . $volume;
	$url .= '&rft.spage=' . $spage;		
	
/*	if ($epage != 0)
	{
		$url .= '&rft.epage=' . $epage;		
	}
*/	
	if ($issn != '')
	{
		$item->issn = $issn;
	}
	
	if ($jtitle == '')
	{
		if ($issn != 0)
		{
			$jtitle = journal_title_from_issn($issn);
			$item->title = $jtitle;
		}
	}		
	$url .= '&rft.jtitle=' . str_replace(" ", "%20", $jtitle);
	
	if ($debug)
	{
		echo $url . "\n";
	}
	$html = get($url);
	
	// get RDF link
	preg_match_all('|<link[^>]+rel=\"([^\"]*)\"\s*(type=\"([^\"]*)\"\s*)?(title=\"([^\"]*)\"\s*)?(href=\"([^\"]*)\"\s*)?|',  $html, $out, PREG_PATTERN_ORDER);
	
	$r = print_r ($out, true);
			
	if ($debug)
	{
		print_r($r);
	}
	
	$rdf_url = '';
	
	foreach($out[1] as $k => $v)
	{
		switch ($v)
		{
			case 'meta':
				$rdf_url = $out[7][$k];
				break;
				
			default:
				break;
		}
	}
		
	if ($rdf_url != '')
	{
		// We have this article, so get metadata
		$found = true;
		
		cinii_rdf($rdf_url, $item, $issn, $debug);
		
/*		$rdf = get($rdf_url);
		
		// convert...
		$dom= new DOMDocument;
		$dom->loadXML($rdf);
		$xpath = new DOMXPath($dom);

		// Get JSON
		$xp = new XsltProcessor();
		$xsl = new DomDocument;
		$xsl->load('xsl/cinii.xsl');
		$xp->importStylesheet($xsl);
		
		$xml_doc = new DOMDocument;
		$xml_doc->loadXML($rdf);
		
		$json = $xp->transformToXML($xml_doc);
		
		if ($debug)
		{
			echo $json;
		}
		
		$item = json_decode($json);
		
		if ($debug)
		{
			print_r($item);
		}
		
		// Ensure we have ISSN (might not be in the metadata
		if (!isset($item->issn) != '')
		{
			$item->issn = $issn;
		}
		
		// Check we have journal name
		if ($item->title == '')
		{
			$item->title = journal_title_from_issn($item->issn);
		}
			
		// Id
		$item->publisher_id = str_replace('http://ci.nii.ac.jp/naid/', '', $item->url);

		// Do some cleaning of authors
		foreach ($item->authors as $a)
		{
			//echo $a->author . "\n";
			if (preg_match('/^(?<lastname>[A-Z]+),?\s*(?<forename>[A-Z](.*)$)/', $a->author, $matches))
			{
				//print_r($matches);
				
				$a->lastname = mb_convert_case($matches['lastname'], MB_CASE_TITLE, mb_detect_encoding($matches['lastname']));
				$a->forename = mb_convert_case($matches['forename'], MB_CASE_TITLE, mb_detect_encoding($matches['forename']));
			}
		}
		
		if ($debug)
		{
			print_r($item);
		}
		*/
	}	

	return $found;
}

// test
if (0)
{
$item = new stdClass;
//search_cinii('', '0003-5092', 18, 185, 0, 1939, $item);

//search_cinii('','1343-8786', 2, 439, 445, 1999, $item);

// 1(2), June 25 1998: 233-239.  
// Entomological Science
//search_cinii('','1343-8786', 1, 233, 239, 1998, $item);

// ANNOTATIONES ZOOLOGICAE JAPONENSES
//search_cinii('', '0003-5092', 56, 338, 350, 1983, $item);

// Entomological REview
//search_cinii('', '0286-9810', 58, 1, 6, 2003, $item);

//search_cinii('', '0286-9810', 58, 1, 6, 2003, $item);

//search_cinii('', '0286-9810', 61, 119, 126, 2006, $item);

// new
//search_cinii('', '0286-9810', 58, 1, $item);

//search_cinii('', '0003-5092', 56, 338, $item);

search_cinii('','1343-8786', 2, 439, $item);


print_r($item);

		if (find_in_cache($item))
		{
			echo "already exists\n";
		}
		else
		{
			echo "store\n";
			store_in_cache($item);
		}	
		
}		

?>
