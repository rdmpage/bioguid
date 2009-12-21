<?php

/**
 * @file bhl_names.php
 *
 * Taxonomic names and tag clouds
 *
 */

require_once(dirname(__FILE__) . '/db.php');
require_once(dirname(__FILE__) . '/bhl_text.php');

	// From http://thraxil.org/users/anders/posts/2005/12/13/scaling-tag-clouds/
	// we use a power law to assign weights to the terms


//---------------------------------------------------------------------------------------------------
function class_from_weight($w,$thresholds)
{
    $i = 0;
    for ($t = 0; $t < count($thresholds); $t++)
    {
    	$i++;
    	if ($w <= $t)
    	{
    		return $i;
    	}
    }
    return $i;
}

//---------------------------------------------------------------------------------------------------
function tag_cloud($obj)
{
	global $config;
	
	$html = '';
	
	$levels = 5;
	
	$thresholds = array();
	
	for ($i = 0; $i < $levels; $i++)
	{
		$thresholds[$i] = pow($obj->max_frequency - $obj->min_frequency + 1, (float)$i/(float)$levels);
	}
		
	$html = '';
	foreach ($obj->names as $name) 
	{
		$font_size = 8 + 3 * class_from_weight($name['count'] - $obj->min_frequency, $thresholds);
		$html .= '<a style="font-size:' . $font_size . 'px;"';
		$html .=  ' ' . 'href="' . $config['web_root'] . 'name/' . $name['NameBankID'];
		$html .= '">';
		$html .=  $name['namestring'];
		$html .= '</a> ';
		$html .= "\n";
	}
	
	return $html;

}

//---------------------------------------------------------------------------------------------------
// Return array of names on a page
function bhl_names_in_page($PageID)
{
	global $db;
	
	$names = array();

	$sql = 'SELECT * FROM bhl_page_name
	WHERE PageID=' . $PageID;
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
	while (!$result->EOF) 
	{	
		if (!isset($names[$result->fields['NameBankID']]))
		{
			$name = array();
			
			$name['namestring'] = $result->fields['NameConfirmed'];
			$name['NameBankID'] = $result->fields['NameBankID'];
			
			$names[$result->fields['NameBankID']] = $name;
		}
		
		$result->MoveNext();
	}
	
	return $names;
}

//---------------------------------------------------------------------------------------------------
function bhl_names_in_reference ($reference_id)
{
	global $db;
	
	$pages = bhl_retrieve_reference_pages($reference_id);
			
	// If we don't have a page range then we can't get taxa
	if (count($pages) == 0)
	{
		return NULL;
	}
	
	$obj = new stdclass;
	$obj->names = array();
	$obj->tags = array();
	$obj->max_frequency = 0;
	$obj->min_frequency = 10000;

	foreach ($pages as $page)
	{
		$sql = 'SELECT * FROM bhl_page_name
		WHERE PageID=' . $page->PageID;
		$result = $db->Execute($sql);
		if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
		while (!$result->EOF) 
		{	
			if (!isset($obj->names[$result->fields['NameBankID']]))
			{
				$name = array();
				
				$name['namestring'] = $result->fields['NameConfirmed'];
				$name['NameBankID'] = $result->fields['NameBankID'];
				$name['count'] = 0;
				
				$obj->names[$result->fields['NameBankID']] = $name;
				
				$obj->tags[] =  $result->fields['NameConfirmed'];
			}
			$obj->names[$result->fields['NameBankID']]['count']++;
			
			$obj->min_frequency = min($obj->min_frequency, $obj->names[$result->fields['NameBankID']]['count']);
			$obj->max_frequency = max($obj->max_frequency, $obj->names[$result->fields['NameBankID']]['count']);
			
			$result->MoveNext();
		}	
	}
	
	// sort alphabetically
	array_multisort($obj->tags, SORT_ASC, SORT_STRING, $obj->names);

	//print_r($obj);
	return $obj;	
}

//--------------------------------------------------------------------------------------------------
// Tag cloud from names object 
function name_tag_cloud($obj)
{
	if ($obj == NULL)
	{
		return '';
	}
	$html = tag_cloud($obj);
	return $html;
}

//--------------------------------------------------------------------------------------------------
// What pages have this name?
// need sparkline type visualisation over time, a la bioguid
function bhl_pages_with_name($name_id)
{
	global $db;

}

//--------------------------------------------------------------------------------------------------
// What reference have this name?
function bhl_references_with_name($NameBankID)
{
	global $db;

	$refs = array();
	
	$sql = 'SELECT DISTINCT(reference_id) FROM bhl_page_name
	INNER JOIN rdmp_reference_page_joiner USING(PageID)
	INNER JOIN rdmp_reference USING(reference_id)
	WHERE NameBankID=' . $NameBankID . '
	ORDER BY rdmp_reference.year';
	
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
	
	while (!$result->EOF) 
	{
		$refs[] = $result->fields['reference_id'];
		$result->MoveNext();				
	}

	return $refs;

}

function bhl_pages_in_reference_with_name($reference_id, $NameBankID)
{	
	global $db;

	$hits = array();
	$sql = 'SELECT bhl_page_name.PageID FROM bhl_page_name
	INNER JOIN rdmp_reference_page_joiner USING(PageID)
	WHERE NameBankID=' . $NameBankID . ' AND reference_id=' . $reference_id;
	
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
	
	while (!$result->EOF) 
	{
		$hits[] = $result->fields['PageID'];
		$result->MoveNext();				
	}
	
	return $hits;
}

function bhl_pages_with_name_thumbnails($reference_id, $NameBankID)
{
	global $config;
	
	$html = '';

	$hits = bhl_pages_in_reference_with_name($reference_id, $NameBankID);
	
	foreach ($hits as $hit)
	{
		// filter on figure
		$has_figure = false;
		$text = bhl_fetch_ocr_text($hit);
		$lines = explode("\\n", $text);
		foreach ($lines as $line)
		{
			//$html .= '<p>' . $line . '</p>';
			if (preg_match('/^(Fig\.|Figure|Figs\.)/i', $line))
			{
				$has_figure = true;
			}
		}
		
		if ($has_figure)
		{
			$image = bhl_fetch_page_image($hit);
			$html .= '<a href="' . $config['web_root'] . 'reference/' . $reference_id . '/page/' . $hit . '">';
			$html .=  '<img style="padding:2px;border:1px solid blue;margin:2px;" id="thumbnail_image_' . $page->PageID . '" src="' . $image->thumbnail->url . '" width="' . $image->thumbnail->width . '" height="' . $image->thumbnail->height . '"/>';	
			$html .= '</a>';
		}
	}
	return $html;
}


// Distribution of name through a reference as a sparkline (inspired by TileBars)
// Hearst, M. TileBars: Visualization of Term Distribution Information in Full Text Information Access, Proceedings of the ACM SIGCHI Conference on Human Factors in Computing Systems (CHI), Denver, CO, 1995. pdfÊ ps (6.5M) psÊ(gz) html (at sigchi)
// http://people.ischool.berkeley.edu/~hearst/papers/chi95.pdf
function bhl_pages_with_name_sparkline($reference_id, $NameBankID)
{
	global $db;
	
	// Get pages in reference
	$pages = array();
	
	$sql = 'SELECT * FROM rdmp_reference_page_joiner 
	WHERE (reference_id = ' . $reference_id . ')
	ORDER BY page_order';
	
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

	while (!$result->EOF) 
	{
		$pages[]=$result->fields['PageID'];
		$result->MoveNext();	
	}
	
	
	$hits = bhl_pages_in_reference_with_name($reference_id, $NameBankID);

	$html = '<span class="sparkline" style="border:1px solid #AAA;">';
	foreach ($pages as $p)
	{
		if (in_array ($p, $hits))
		{
			$html .= '<span class="index"><span class="count" style="height: 100%;">1,</span> </span>';		
		}
		else
		{
			$html .= '<span class="index"><span class="count" style="height: 0%;">0,</span> </span>';
		}
	}
	$html .= '</span>';

	return $html;

}




	


?>