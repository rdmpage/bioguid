<?php

// Pubmed related routines

require_once ('db.php');
require_once ('issn-functions.php');
require_once ('lib.php');


function get_pubmed_from_doi($doi)
{
	$doi = preg_replace('/^doi:/', '', $doi); // ensure
	$pmid = 0;

	$url = 'http://eutils.ncbi.nlm.nih.gov/entrez/eutils/esearch.fcgi?'
	 . 'db=pubmed'
	 . '&term=' . $doi;
	 
	$xml = get($url);
	
	if ($xml == '')
	{
		return $pmid;
	}
	 
	$dom= new DOMDocument;
	$dom->loadXML($xml);
	$xpath = new DOMXPath($dom);
	
	$count = 0;

	// We want one hit
	$xpath_query = "//eSearchResult/Count";
	$nodeCollection = $xpath->query ($xpath_query);
	foreach($nodeCollection as $node)
	{
		$count =  $node->firstChild->nodeValue;
	}
	
	if ($count == 1)
	{
		$xpath_query = "//eSearchResult/IdList/Id";
		$nodeCollection = $xpath->query ($xpath_query);
		foreach($nodeCollection as $node)
		{
			$pmid =  $node->firstChild->nodeValue;
		}
	}
		
	return $pmid;
}


//--------------------------------------------------------------------------------------------------
// Get metadata for a given pmid
function pubmed_metadata ($pmid, &$item)
{
	global $debug;
	
	$ok = false;
	

	$url = 'http://eutils.ncbi.nlm.nih.gov/entrez/eutils/efetch.fcgi?'
		. 'retmode=xml'
		. '&db=pubmed'
		. '&id=' . $pmid;
		
	//echo $url;
			
	
	$xml = get($url);
	//echo $xml;
	
	if (preg_match('/<\?xml /', $xml))
	{
		$ok = true;
		
		if ($debug)
		{
			echo $xml;
		}
		
		$dom= new DOMDocument;
		$dom->loadXML($xml);
		$xpath = new DOMXPath($dom);

/*		$nodeCollection = $xpath->query ("//crossref/error");
		foreach($nodeCollection as $node)
		{
			$ok = false;
		}
		if ($ok)
		{*/
			// Get JSON
			$xp = new XsltProcessor();
			$xsl = new DomDocument;
			$xsl->load('xsl/pubmed2JSON.xsl');
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
				echo '<h3>Boo</h3>';
				print_r($item);
			}
	}	
	
	
	return $ok;
}
// Get nucleotide and cited by links for a pubmed reference
function get_pubmed_links($pmid)
{
	$links = array();
	$links['gi'] = array();
	$links['cited'] = array();
	
	$url = 'http://eutils.ncbi.nlm.nih.gov/entrez/eutils/elink.fcgi?'
				. '&dbfrom=pubmed'
				. '&db=nucleotide'
				. '&id=' . $pmid;
				
	$xml = get($url);
	
	if ($xml .= '')
	{
	
		$dom= new DOMDocument;
		$dom->loadXML($xml);
		$xpath = new DOMXPath($dom);
		$xpath_query = "//eLinkResult/LinkSet/LinkSetDb/Link/Id";
		$nodeCollection = $xpath->query ($xpath_query);
		foreach($nodeCollection as $node)
		{
			$links['gi'][] = $node->firstChild->nodeValue;
		}
	}
	
	$url = 'http://eutils.ncbi.nlm.nih.gov/entrez/eutils/elink.fcgi?dbfrom=pubmed&linkname=pubmed_pubmed_citedin&id='
	. $pmid;
	
	if ($xml .= '')
	{
		$xml = get($url);
		
		$dom->loadXML($xml);
		$xpath = new DOMXPath($dom);
		$xpath_query = "//eLinkResult/LinkSet/LinkSetDb/Link/Id";
		$nodeCollection = $xpath->query ($xpath_query);
		foreach($nodeCollection as $node)
		{
			$links['cited'][] = $node->firstChild->nodeValue;
		}
	}	
	
	return $links;
}


// test

//echo get_pubmed_from_doi('doi:10.1016/j.ijpara.2005.03.014');


//$debug=1;
//$item = new stdClass;
//18490997
//pubmed_metadata(19589848, $item);
//echo get_pubmed_from_doi('doi:10.1016/j.ijpara.2005.03.014');

?>
