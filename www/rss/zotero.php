<?php

// harvest my Zotero feed

// Need absolute file paths as we run this as a service
$root_dir = dirname(__FILE__);
$root_dir = preg_replace('/rss$/', '', $root_dir);

require_once (dirname(__FILE__) . '/nameparse.php');
require_once ($root_dir . 'db.php');
require_once ($root_dir . 'issn-functions.php');
require_once (dirname(__FILE__) . '/rss.php');

$url = 'https://api.zotero.org/users/14509/items';

$result = GetRSS ($url, $rss, true);

echo $result . "\n";

//exit();

if ($result == 0)
{

	$rss = preg_replace('/\s*Content\-Type: application\/atom\+xml\s*/', '', $rss);
	//echo "|" . $rss;

	$dom= new DOMDocument;
	$dom->loadXML($rss);
	$xpath = new DOMXPath($dom);
	
	$xpath->registerNamespace("atom",
						"http://www.w3.org/2005/Atom");	
	$xpath->registerNamespace("xhtml",
						"http://www.w3.org/1999/xhtml");	

	$nodeCollection = $xpath->query ("//atom:entry");
	foreach($nodeCollection as $node)
	{
		$obj = new stdclass;
		$obj->authors = array();
		
		$itemType = '';
		
		// what kind is it?
		$nc = $xpath->query ("atom:content/xhtml:div/xhtml:table/xhtml:tr[@class='itemType']/xhtml:td", $node);
		foreach ($nc as $n)
		{
			$itemType = $n->firstChild->nodeValue;
		}
		
		// Zotero URL is default identifier
		$nc = $xpath->query ("atom:id", $node);
		foreach ($nc as $n)
		{
			$obj->zotero = $n->firstChild->nodeValue;
			//$obj->url = $n->firstChild->nodeValue;
		}
		
		$nc = $xpath->query ("atom:title", $node);
		foreach ($nc as $n)
		{
			switch ($itemType)
			{
				case 'Journal Article':
					$obj->atitle = strip_tags($n->firstChild->nodeValue);
					$obj->genre = article;
					break;
					
				default:
					break;
			}
		}
		
		// authors
		$nc = $xpath->query ("atom:content/xhtml:div/xhtml:table/xhtml:tr[@class='creator']/xhtml:td", $node);
		foreach ($nc as $n)
		{
			$value = $n->firstChild->nodeValue;
			
			// Trim trailing periods and other junk
			$value = preg_replace("/\.$/", "", $value);
			$value = preg_replace("/&nbsp;$/", "", $value);

			// Clean Ingenta crap						
			$value = preg_replace("/\[[0-9]\]/", "", $value);
			
			// Space initials nicely
			$value = preg_replace("/\.([A-Z])/", ". $1", $value);
			
			// Make nice
			$value = mb_convert_case($value, 
				MB_CASE_TITLE, mb_detect_encoding($value));
						
			// Get parts of name
			$parts = parse_name($value);
						
			$author = new stdClass();
			
			if (isset($parts['last']))
			{
				$author->lastname = $parts['last'];
			}
			if (isset($parts['suffix']))
			{
				$author->suffix = $parts['suffix'];
			}
			if (isset($parts['first']))
			{
				$author->forename = $parts['first'];
				
				if (array_key_exists('middle', $parts))
				{
					$author->forename .= ' ' . $parts['middle'];
				}
			}
			array_push($obj->authors, $author);
			
		}
		
		
		// abstract
		$nc = $xpath->query ("atom:content/xhtml:div/xhtml:table/xhtml:tr[@class='abstractNote']/xhtml:td", $node);
		foreach ($nc as $n)
		{
			$obj->abstract = $n->firstChild->nodeValue;
		}
		
		// journal name
		$nc = $xpath->query ("atom:content/xhtml:div/xhtml:table/xhtml:tr[@class='publicationTitle']/xhtml:td", $node);
		foreach ($nc as $n)
		{
			$obj->title = $n->firstChild->nodeValue;
		}		
		
		// DOI
		$nc = $xpath->query ("atom:content/xhtml:div/xhtml:table/xhtml:tr[@class='DOI']/xhtml:td", $node);
		foreach ($nc as $n)
		{
			$obj->doi = $n->firstChild->nodeValue;
		}
		
		// URL
		$nc = $xpath->query ("atom:content/xhtml:div/xhtml:table/xhtml:tr[@class='url']/xhtml:td", $node);
		foreach ($nc as $n)
		{
			$done = false;
			$url = $n->firstChild->nodeValue;
			
			if (preg_match('/\.pdf$/', $url))
			{
				$obj->pdf = $url;
				$done = true;
			}
			if (preg_match('/^http:\/\/dx.doi.org\//', $url))
			{
				$obj->doi = $url;
				$obj->doi = str_replace('http://dx.doi.org/', '', $obj->doi);
				$done = true;
			}
			
			if (!$done)
			{
				$obj->url = $url;
			}
		}
		
		// issn
		$nc = $xpath->query ("atom:content/xhtml:div/xhtml:table/xhtml:tr[@class='ISSN']/xhtml:td", $node);
		foreach ($nc as $n)
		{
			$obj->issn = $n->firstChild->nodeValue;
		}

		// volume
		$nc = $xpath->query ("atom:content/xhtml:div/xhtml:table/xhtml:tr[@class='volume']/xhtml:td", $node);
		foreach ($nc as $n)
		{
			$obj->volume = $n->firstChild->nodeValue;
		}
		
		// issue
		$nc = $xpath->query ("atom:content/xhtml:div/xhtml:table/xhtml:tr[@class='issue']/xhtml:td", $node);
		foreach ($nc as $n)
		{
			$obj->issue = $n->firstChild->nodeValue;
		}
		
		// pages
		$nc = $xpath->query ("atom:content/xhtml:div/xhtml:table/xhtml:tr[@class='pages']/xhtml:td", $node);
		foreach ($nc as $n)
		{
			$pages = $n->firstChild->nodeValue;
			$parts = explode("-", $pages);
			$obj->spage = $parts[0];
			$obj->epage = $parts[1];
		}
		
		// cleanup
		if (!isset($obj->url) && !isset($obj->doi))
		{
			$obj->url = $obj->zotero;
		}

		
			// ISSN lookup
			if (!isset($obj->issn) && 'article' == $obj->genre)
			{
				$issn = issn_from_journal_title($obj->title);
				if ('' != $issn)
				{
					$obj->issn = $issn;
				}
			}
		
		print_r($obj);
		
		
		// Store
			if (find_in_cache($obj) == 0)
			{
			
				if (isset($obj->issn))
				{
					if ($obj->issn != '')
					{
						store_in_cache($obj);
					}
				}
			}
		
	}
}
?>