<?php

// $Id: $

/**
 *@file jstor.php
 *
 * JSTOR and SICI functions
 *
 */
 
require_once ('crossref.php');
require_once ('db.php');
require_once ('issn-functions.php');
require_once ('lib.php');
require_once ('scraping.php');

//--------------------------------------------------------------------------------------------------
// Decompose a SICI, based on regular expressions in Biblio Utils.pm
// I've edited the item regular expression to handle ISSNs that end in 'X'
function unpack_sici($sici)
{
	
	/*	my %out = ();
		($out{item}, $out{contrib}, $out{control}) = ($sici =~ /^(.*)<(.*)>(.*)$/);
		($out{issn}, $out{chron}, $out{enum}) = ($out{item} =~ /^(\d{4}-\d{4})\((.+)\)(.+)/);
		($out{site}, $out{title}, $out{locn}) = (split ":", $out{contrib});
		($out{csi}, $out{dpi}, $out{mfi}, $out{version}, $out{check}) = ($out{control} =~ /^(.+)\.(.+)\.(.+);(.+)-(.+)$/); 
		($out{year}, $out{month}, $out{day}, $out{seryear}, $out{seryear}, $out{sermonth}, $out{serday}) = ($out{chron} =~ /^(\d{4})?(\d{2})?(\d{2})?(\/(\d{4})?(\d{2})?(\d{2})?)?/);
		$out{enum} = [split ":", $out{enum}];
	*/
	
	$out = array();
	
	$match = array();
	if (preg_match('/^(.*)<(.*)>(.*)$/', $sici, $match))
	{
		//print_r($match);
		
		$out['item'] = $match[1];
		$out['contrib'] = $match[2];
		$out['control'] = $match[3];
	}
	
	if (isset($out['item']))
	{
//		if (preg_match('/^(\d{4}-\d{4})\((.+)\)(.+)/', $out['item'], $match))
		if (preg_match('/^(\d{4}-\d{3}([0-9]|X))\((.+)\)(.+)/', $out['item'], $match))
		{	
			//print_r($match);
			
			$out['issn'] = $match[1];
			$out['chron'] = $match[3];
			$out['enum'] = $match[4];
		}
	}
	
	if (isset($out['chron']))
	{
		if (preg_match('/^(\d{4})?(\d{2})?(\d{2})?(\/(\d{4})?(\d{2})?(\d{2})?)?/', $out['chron'], $match))
		{	
			//print_r($match);
			
			if (isset($match[1])) $out['year'] = $match[1];
			if (isset($match[2])) $out['month'] = $match[2];
			if (isset($match[3])) $out['day'] = $match[3];
			if (isset($match[4])) $out['seryear'] = $match[4];
			if (isset($match[5])) $out['sermonth'] = $match[5];
			if (isset($match[6])) $out['serday'] = $match[6];
		}
	}
	
	if (isset($out['contrib']))
	{
		list($out['site'], $out['title'], $out['locn']) = split (":", $out['contrib']);
	}
	
	if (isset($out['enum']))
	{
		list($out['volume'], $out['issue'], $out['locn']) = split (":", $out['enum']);
	}
	
	
	if (isset($out['control']))
	{
		if (preg_match('/^^(.+)\.(.+)\.(.+);(.+)-(.+)$/', $out['control'], $match))
		{
			//print_r($match);
			
			if (isset($match[1])) $out['csi'] = $match[1];
			if (isset($match[2])) $out['dpi'] = $match[2];
			if (isset($match[3])) $out['mfi'] = $match[3];
			if (isset($match[4])) $out['version'] = $match[4];
			if (isset($match[5])) $out['check'] = $match[5];
		
		}
	}
	
	return $out;
}

//--------------------------------------------------------------------------------------------------
/**
 * @brief Create a simple SICI from some metadata
 *
 * @param metadata An object with basic metadata, such as issn, volume, etc.
 *
 * @return SICI (if sufficient metadata, otherwise returns empty string)
 */
function sici_from_meta($metadata)
{
	global $debug;
	
	$sici = '';

/*	if ($debug)
	{
		echo '<pre>';
		print_r($metadata);
		echo '</pre>';
	}
*/	
	$issn = '';
	$year = '';
	$volume = '';
	$spage = '';
	$issue = '';
	
	if (array_key_exists('issn', $metadata)) { $issn = $metadata['issn']; }
	if (array_key_exists('date', $metadata)) { $year = $metadata['date']; }
	if (array_key_exists('volume', $metadata)) { $volume = $metadata['volume']; }
	if (array_key_exists('spage', $metadata)) { $spage = $metadata['spage']; }
	if (array_key_exists('issue', $metadata)) { $issue = $metadata['issue']; }
	
	if (
		($issn != '')
		and ($year != '')
		and ($volume != '')
		and ($spage != '')
		)
	{
		$sici = $issn . '(' . $year . ')' . $volume;

		if ($issue != '')
		{
			$sici .= ':' . $issue;
		}
		$sici .= '<' . $spage . '>';
		
		// Add simple control code
		$sici .= '2.0.CO;' ;
	}

	return $sici;
}



//--------------------------------------------------------------------------------------------------
// We need a ISSN and a year in order to make a SICI to search JSTOR
function enough_for_jstor_lookup(&$metadata)
{
	$issn = '';
	$year = '';
	
	check_for_missing_issn($metadata);
		
	if (array_key_exists('issn', $metadata))
	{
		$issn = $metadata['issn'];
	}

	if (array_key_exists('date', $metadata))
	{
		$date = $metadata['date'];
	}
	
	//print_r($metadata);

	return (($issn != '') and ($date != ''));
}


//--------------------------------------------------------------------------------------------------
/**
 * @brief Test if journal is covered by JSTOR's moving wall
 *
 * @param issn ISSN of journal
 * @param date Year of publication
 *
 * @return True if covered, false otherwise
 */
function in_jstor($issn, $date)
{
	global $db;
	global $debug;

	$found = false;

	$sql = 'SELECT * FROM jstor 
		WHERE (issn = ' . $db->Quote($issn) . ')
		AND (' . $db->Quote($date) . ' BETWEEN startDate and endDate)
		LIMIT 1';
		
	//echo $sql;

	$result = $db->Execute($sql);
	if ($result == false) die("failed: " . $sql);

	if ($result->NumRows() == 1)
	{
		$found = true;
	}

	return $found;
}


//--------------------------------------------------------------------------------------------------
// Get metadata for a given SICI
function jstor_metadata ($sici, &$item)
{
	global $config;
	global $debug;
	
	$found = false;

	$url = 'http://links.jstor.org/sici?sici=' . urlencode($sici);
	
	//echo $url;
		
	$html = get($url);
	
	if ($debug)
	{
		echo '<pre style="text-align: left;border: 1px solid #c7cfd5;background: #f1f5f9;padding:15px;">';
		echo $url . "\n";
		echo htmlentities($html);
		echo "</pre>";
	}
	
	
	// Check for any error messages
	if (preg_match("/<h1>We're Sorry.<\/h1>/", $html))
	{
		return $found;
	}
	else
	{
		$found = true;
	}
				
	if ('' == $config['proxy_name'])
	{
		// Outside Glasgow so we get metadata directly
	}
	else
	{
		// Inside Glasgow, we are licensed, so we need one more step
		// Extract stable indentifier
		preg_match('/&amp;suffix=([0-9]+)/', $html, $match);
		
		//print_r($match);
	
		if (isset($match[1]))
		{
			$stable = $match[1];
			$item->url = 'http://www.jstor.org/stable/' . $match[1];
			
			// ok, harvest						
			$html = get('http://www.jstor.org/stable/info/' . $match[1]);
		}			
	}

	// Add line feeds so regular expresison works
	$html = str_replace('<meta', "\n<meta", $html);

	// Pull out the meta tags
	preg_match_all("|<meta\s*name=\"(dc.[A-Za-z]*)\"\s*(scheme=\"(.*)\")?\s*(content=\"(.*)\")><\/meta>|",  $html, $out, PREG_PATTERN_ORDER);

	$r = print_r ($out, true);
		
	parseDcMeta($out, $item);

	//print_r($out);
		
	$out = unpack_sici($sici);
	
	//print_r($out);
	
	if (isset($out['issn'])) $item->issn = $out['issn'];
	if (isset($out['year'])) $item->year = $out['year'];
	
	// Some JSTOR articles, such as Copeia, have all three elements in the enumeration,
	// so that the volume and issue are the second and third elements
	
	if (isset($out['locn']))
	{
		if (isset($out['volume'])) $item->volume = $out['issue'];
		if (isset($out['issue'])) $item->issue = $out['locn'];
	}
	else
	{
		if (isset($out['volume'])) $item->volume = $out['volume'];
		if (isset($out['issue'])) $item->issue = $out['issue'];
	}
	if (isset($out['site'])) $item->spage = $out['site'];
	
	// Handle identifiers
	
	// Make stable URL
	if (isset($item->doi))
	{
		$stable = $item->doi;
		$stable = str_replace("10.2307/", "", $stable);
		$stable = 'http://www.jstor.org/stable/' . $stable;
		$item->url = $stable;
	}
	
	// Is the DOI valid? (not all DOIs in the HTML metadata are valid
	if (isset($item->doi))
	{
		$crossref_item = new stdClass;
		$exists = doi_metadata($item->doi, $crossref_item);
		if ($exists)
		{
			// DOI is cool, so add journal name
			if (isset($crossref_item->title))
			{
				$item->title = $crossref_item->title;
			}
		}
		else
		{
			// Dud DOI, so remove it from the metadata
			unset($item->doi);
		}
	}
	
	// Might not have journal name
	if (!isset($item->title))
	{
		$title = journal_title_from_issn($item->issn);
		if ($title != '')
		{
			$item->title = $title;
		}
	}
	
	
	return $found;
}


//test

/*

$sici = '0010-065X(198806)42:2<167:LHNSAT>2.0.CO;2-M';

	$out = unpack_sici($sici);
	echo '<pre>';
	print_r($out);
	echo '</pre>';
*/

?>