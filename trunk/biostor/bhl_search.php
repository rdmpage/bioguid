<?php

/**
 * @file bhl_search.php
 *
 * Search BHL metadata
 *
 */

// Search BHL content for matches to OpenURL-style queries

// Functions to find PageID from volume and spage (using templates or regular expressions
// to extract info from VolumeInfo. Also need title search.
//

require_once (dirname(__FILE__) . '/config.inc.php');
require_once (dirname(__FILE__) . '/bhl_date.php');
require_once (dirname(__FILE__) . '/bioguid.php');
require_once (dirname(__FILE__) . '/db.php');
require_once (dirname(__FILE__) . '/lcs.php');
require_once (dirname(__FILE__) . '/lib.php');
require_once (dirname(__FILE__) . '/bhl_utilities.php');


//--------------------------------------------------------------------------------------------------
function clean_string ($str)
{
	$str = str_replace ('.', '', $str);
	$str = preg_replace('/\s\s+/', ' ', $str);
	$str = trim($str);

	return $str;
}


//--------------------------------------------------------------------------------------------------
/**
 * @brief Retrieve details about a title from BHL database
 *
 * @param bhl_title_id BHL TitleID
 * @param obj Object we will populate
 *
 */
function bhl_title_retrieve ($bhl_title_id, &$obj)
{
	global $db;
	$PageID = array();	
	
	$sql = 'SELECT * FROM bhl_title
		INNER JOIN bhl_title_identifier USING(TitleID)
		WHERE (TitleID=' . $bhl_title_id . ')';
				
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
	
	while (!$result->EOF) 
	{
		if (!isset($obj->FullTitle))
		{
			$obj->FullTitle = $result->fields['FullTitle'];
		}
	
		switch ($result->fields['IdentifierName'])
		{
			case 'ISBN':
				// may need to clean this
				$obj->ISBN = $result->fields['IdentifierValue'];
				break;
				
			default:
				$k = $result->fields['IdentifierName'];
				$obj->$k = $result->fields['IdentifierValue'];
				break;
		}
		$result->MoveNext();
	}
}

//--------------------------------------------------------------------------------------------------
/**
 * @brief Approximate string search for title
 *
 * Assumes n-gram index available for MySQL, see
 * http://iphylo.blogspot.com/2009/10/n-gram-fulltext-indexing-in-mysql.html for details on installing
 * this.
 *
 * @param str Title string to search for
 * @param threshold Percentage of str that we require to be in longest common subsequence (default is 75%)
 *
 * @return Array of matching titles, together with scores
 */
function bhl_title_lookup($str, $threshold = 75)
{
	global $db;
	
	$matches = array();
	
	$locs = array();
	
	$str = clean_string ($str);
	$str_length = strlen($str);
	
	$sql = 'SELECT TitleID, ShortTitle, MATCH(ShortTitle) AGAINST(' . $db->qstr($str) . ')
AS score FROM bhl_title
WHERE MATCH(ShortTitle) AGAINST(' . $db->qstr($str) . ') LIMIT 10';
	
	$lcs = array();
	$count = 0;
	
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

	while (!$result->EOF) 
	{
		// Get subsequence length
		$cleaned_hit = clean_string ($result->fields['ShortTitle']);
		$cleaned_hit_length = strlen($cleaned_hit);
		
		$C = LCSLength($cleaned_hit, $str);
		
		// length of subsequence as percentage of query string
		$subsequence_length =  round((100.0 * $C[$cleaned_hit_length][$str_length])/$str_length);
		if ($subsequence_length >= $threshold)
		{	
			array_push($matches, array(
				'TitleID' => $result->fields['TitleID'],
				'ShortTitle' => $result->fields['ShortTitle'],			
				'score' => $result->fields['score'],
				'sl' => $subsequence_length,
				'subsequence' => $C[$cleaned_hit_length][$str_length],
				'x' => $str,
				'y' => $cleaned_hit
				)
			);
			
			array_push ($lcs, array('row' => $count, 'subsequence' => $C[$cleaned_hit_length][$str_length]));
		}
		
		
		$count++;
		$result->MoveNext();
	}
	//print_r($lcs);
	$scores = array();
	$index = array();
	foreach ($lcs as $key => $row) 
	{
		$scores[$key]  = $row['subsequence'];
		$index[$key]  = $key;
	}
	array_multisort($scores, SORT_DESC, $index);
	//print_r($scores);
	//print_r($index);
	
	
	return $matches;
}


//--------------------------------------------------------------------------------------------------
/**
 * @brief Retrieve BHL TitleID corresponding to ISSN
 *
 * @param issn ISSN of journal
 *
 * @return BHL TitleID if found, 0 if not found
 *
 */
function bhl_titleid_from_issn($issn)
{
	global $db;
	
	$TitleID = 0;
	
	$sql = 'SELECT * FROM bhl_title_identifier 
	WHERE (IdentifierName = "ISSN") 
	AND (IdentifierValue = ' . $db->qstr($issn) . ') LIMIT 1';
	
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

	if ($result->NumRows() == 1)
	{
		$TitleID = $result->fields['TitleID'];
	}
	
	return $TitleID;
}

//--------------------------------------------------------------------------------------------------
/**
 * @brief Return the title page(s) of an item
 *
 * For given ItemID returns array of PageIDs for which PageTypeName is 'Title Page'
 *
 * @param ItemID BHL ItemID
 *
 * @return array of PageIDs
 */
function bhl_title_page($ItemID)
{
	global $db;
	
	$pages = array();
	
	$sql = 'SELECT * FROM bhl_page 
	WHERE (ItemID = ' . $ItemID . ') 
	AND (PageTypeName = ' . $db->qstr('Title Page') . ')';
	
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

	while (!$result->EOF) 
	{
		array_push($pages, $result->fields['PageID']);
		$result->MoveNext();
	}
	
	return $pages;
}

//--------------------------------------------------------------------------------------------------
/**
 * @brief Find BHL item(s) that correspond to a given volume
 *
 * We return an array because some volumes (such as volume 16 of J Hymenopt Research)
 * may span more than one item.
 *
 * @param TitleID BHL TitleID for journal
 * @param volume Volume we want
 * @param series Optional series 
 *
 * @return Array of BHL items
 *
 */
function bhl_itemid_from_volume($TitleID, $volume, $series = '')
{
	global $db;
	global $debug;
		
	// Find ItemID of item that contains relevant volume
	$sql = 'SELECT * FROM bhl_item WHERE TitleID=' . $TitleID;
	
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

	$items = array();
		
	while (!$result->EOF) 
	{	
		$info = new stdclass;	
		$VolumeInfo = $result->fields['VolumeInfo'];
		$matched = parse_bhl_date($VolumeInfo, $info);
				
		if ($matched)
		{
			if ($debug)
			{
				echo '<pre>';
				print_r($info);
				echo '</pre>';
			}
			
			if (isset($info->volume_from))
			{
				// range, we store the volume offset of target volume
				if (($volume >= $info->volume_from) && ($volume <= $info->volume_to))
				{
					$item = new stdclass;
					$item->ItemID = $result->fields['ItemID'];
					if (isset($info->series))
					{
						$item->series = $info->series;
					}
					$item->volume_offset = $volume - $info->volume_from;
					array_push($items, $item);
				}				
			}
			else
			{
				// Volume is single number
				if ($info->volume == $volume)
				{
					$found = true;
					
					$item = new stdclass;
					$item->ItemID = $result->fields['ItemID'];
					if (isset($info->series))
					{
						$item->series = $info->series;
						if ($series != '')
						{
							if ($info->series == $series)
							{
								$found = true;
							}
							else
							{
								$found = false;
							}
						}
					}
					
					$item->volume_offset = 0;
					if ($found)
					{
						array_push($items, $item);
					}
				}
				else
				{
					// Volume might also match year
					if (isset($info->start) && ($info->start == $volume))
					{
						
						$item = new stdclass;
						$item->ItemID = $result->fields['ItemID'];
						
						$item->volume_offset = 0;
						array_push($items, $item);
					}
					
				}
			}
				
		}
		else
		{
			if ($debug)
			{
				echo '<pre>';
				echo "*** WARNING *** Line:" . __LINE__ . " Not matched \"" . $VolumeInfo . "\"<\n";
				echo '</pre>';
			}
		}
			
		$result->MoveNext();
	}
	
	return $items;
}


//--------------------------------------------------------------------------------------------------
/**
 * @brief Find set of BHL items whose VolumeInfo field match a pattern
 *
 * Some articles have been treated as titles, e.g. large articles and monographs that are bound 
 * as single books. For these articles the journal and volume information may be contained in the
 * VolumeInfo field.
 *
 * @param search_pattern SQL search pattern, e.g. 'Fieldiana Zoology%'
 * @param mask_pattern Regular expression to remove title, e.g. '/^Fieldiana Zoology/'
 * @param volume Article volume we are searching for
 *
 * @result Array of BHL items that match query
 *
 */
function bhl_itemid_from_pattern($search_pattern, $mask_pattern, $volume)
{
	global $db;
	
	// Find ItemID of item that contains relevant volume
	$sql = 'SELECT * FROM bhl_item WHERE VolumeInfo LIKE ' . $db->qstr($search_pattern);
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

	$items = array();
	
	while (!$result->EOF) 
	{
		$info = new stdclass;
		
		$VolumeInfo = $result->fields['VolumeInfo'];
		if ($mask_pattern != '')
		{
			$VolumeInfo = trim(preg_replace($mask_pattern, '', $VolumeInfo));
		}
		$matched = parse_bhl_date($VolumeInfo, $info);
		
		if ($matched)
		{
			
			if (isset($info->volume_from))
			{
				// range, we store the volume offset of target volume
				if (($volume >= $info->volume_from) && ($volume <= $info->volume_to))
				{
					$item = new stdclass;
					$item->ItemID = $result->fields['ItemID'];
					if (isset($info->series))
					{
						$item->series = $info->series;
					}
					$item->volume_offset = $volume - $info->volume_from;
					array_push($items, $item);
				}				
			}
			else
			{
				if ($info->volume == $volume)
				{
					$item = new stdclass;
					$item->ItemID = $result->fields['ItemID'];
					if (isset($info->series))
					{
						$item->series = $info->series;
					}
					
					$item->volume_offset = 0;
					array_push($items, $item);
				}
			}
				
		}
		else
		{
			if ($debug)
			{
				echo '<pre>';
				echo "*** WARNING *** Line:" . __LINE__ . " Not matched \"" . $VolumeInfo . "\"<\n";
				echo '</pre>';
			}
			
		}
		$result->MoveNext();
	}
	
	return $items;
}


/*
The Bulletin of the British Museum (Natural History) has several series, all with the same FullTitle,
so we first use bioguid to get ISSN for journal name, then retrieve TitleID using ISSN

2192 is Entomology, 2197 is Geology, 2198 is Botany, 2202 is Zoology, 5067 Historical

2192	Bulletin of the British Museum (Natural History).  	0524-6431
2197	Bulletin of the British Museum (Natural History).  	0007-1471
2198	Bulletin of the British Museum (Natural History).  	0068-2292
2202	Bulletin of the British Museum (Natural History).  	0007-1498
5067	Bulletin of the British Museum (Natural History).	0068-2306

*/

//--------------------------------------------------------------------------------------------------
/**
 * @brief Find article in BHL database
 *
 * @param title Title of journal
 * @param volume Volume containing article
 * @param page Page of article (typically this will be the first page in the article
 * @param series Optional series, used in cases where journal has multiple series with same 
 * volume numbers
 *
 * @return Search results as array of objects containing BHL PageID, score of article title match
 * (default = -1) and snippet showing alignment of article title to BHL text (empty string if no
 * article title supplied.
 *
 * <pre>
 * Array
 * (
 *     [0] => stdClass Object
 *         (
 *             [PageID] => 4467383
 *             [score] => 0.583333333333
 *             [snippet] => Kansas Lawrence, Kansas NUMBER 31...
 *         )
 * )
 *
 */
function bhl_find_article($atitle, $title, $volume, $page, $series = '')
{
	global $db;
	global $debug;
	
	// Data structure to hold search result
	$obj = new stdclass;
	$obj->TitleID = 0;
	$obj->ISSN = '';
	$obj->ItemIDs = array();
	$obj->hits = array();
	
	// Step one
	// --------
	// Map journal title to BHL titles. We try to achieve this by first finding ISSN for title,
	// then querying BHL for that ISSN in the bhl_title_identifier table. If we don't have an ISSN,
	// or BHL doesn't have this ISSN then we try approximate string matching. This may return multiple
	// hits, for now we take the best one. If we still haven't found the title, it may be in the
	// VolumeInfo field (for example, large articles or monographs may be bound separately and hence
	// treated as individual titles, rather than as items of a title (e.g., Fieldiana). If still no
	// hits, we abandon search.
	
	// Can we do this via ISSN?	
	$obj->ISSN = issn_from_title($title);
	if ($obj->ISSN != '')
	{
		$obj->TitleID = bhl_titleid_from_issn($obj->ISSN);
	}
	
	if ($debug)
	{
		echo __FILE__ . ' line ' . __LINE__ . ' ISSN = ' . $obj->ISSN . "\n";
	}
	
	// Special cases where mapping is tricky
	switch ($obj->ISSN)
	{
		// Transactions of the Linnean Society
		case '1945-9432':
			$obj->TitleID = 2203;
			break;
		
		default:
			break;
	}
	
	if ($debug)
	{
		echo __FILE__ . ' line ' . __LINE__ . ' TitleID = ' . $obj->TitleID . "\n";
	}
	
	
	// If no ISSN, or no mapping available via ISSN, so try string matching
	if ($obj->TitleID == 0)
	{
		$hits = bhl_title_lookup($title);
		
		if ($debug)
		{
			echo __FILE__ . ' line ' . __LINE__ . "\n";
			echo '<pre>';
			print_r($hits);
			echo '</pre>';
		}
		
		
		if (count($hits) > 0)
		{
			$obj->TitleID = $hits[0]['TitleID'];
		}		
	}
	
	if ($debug)
	{
		echo __FILE__ . ' line ' . __LINE__ . ' TitleID = ' . $obj->TitleID . "\n";
	}
	
	// Special cases where title is in VolumeInfo (e.g., article is treated as a monograph)
	if ($obj->TitleID == 0)	
	{
		if (isset($obj->ISSN))
		{
			switch ($obj->ISSN)
			{
				case '0015-0754':
					//echo $title . "\n";
					//echo "Handle Fieldiana...\n";
					$obj->ItemIDs = bhl_itemid_from_pattern ('Fieldiana Zoology%', '/^Fieldiana Zoology/', $volume);
					break;
				
				default:
					break;
			}
		}
	}
	
	// At this point if we have a title we then want to find items for this title
	if($obj->TitleID != 0)
	{		
		bhl_title_retrieve ($obj->TitleID, $obj);
				
		// Problem -- volume info varies across titles (and sometimes within...)
		
		if ($debug)
		{
			echo __LINE__ . "<br/>TitleID:" . $obj->TitleID . "<br/>Volume: " . $volume . "<br/>Series: " . $series . "<br/>";
		}
		$volume_offset = 0;
		$obj->ItemIDs = bhl_itemid_from_volume($obj->TitleID, $volume, $series);

		if ($debug)
		{
			echo __LINE__ . " ItemIDs<br/>\n";
			print_r($obj->ItemIDs);
		}
		
		// Special cases where we know there are problems. For example, there may be multiple titles
		// that correspond to the same journal. In these cases we clear the item list, and add to it 
		// items from all titles that match our query
		$title_list = array();
		switch ($obj->TitleID)
		{
				
			// Annales de la Société entomologique de Belgique
			case 11933:
			case 11938:
				$title_list = array(11933, 11938);
				break;
		
			// Archiv für Naturgeschichte
			case 6638:
			case 7051:
			case 2371:
			case 5923:
			case 12937:
			case 12938:
				$title_list = array(6638,7051,2371,5923,12937,12938);
				break;
				
			// Bulletin de la Société botanique de France
			case 359:
			case 5948:
				$title_list = array(359,5948);
				break;
				
			// Bulletin du Muséum National d'Histoire Naturelle
			case 14109:
			case 5943:	
			case 14964:
			case 12908:
			case 13855:
				$title_list = array(14109,5943,14964,12908,13855);
				break;
				
			// Bulletin of the Natural History Museum (Entomology)
			case 2192:
			case 2201:
				$title_list = array(2192, 2201);
				break;

			// Entomological News
			case 34360:
			case 2356:
			case 2359:
				$title_list = array(34360, 2356,2359);
				break;
				
			// Occasional papers of the Museum of Natural History, the University of Kansas.
			case 4672:
			case 5584:
				$title_list = array(4672, 5584);
				break;				
				
			// Proceedings of the Biological Society of Washington
			case 2211:
			case 3622:
				$title_list = array(2211, 3622);
				break;

			// Proceedings of the California Academy of Sciences
			case 3952:
			case 7411:
			case 15816:
			case 3966:
			case 4274:
			case 3943:
				$title_list = array(3952, 7411, 15816, 3966, 4274, 3943);
				break;
			
			// Transactions of Kansas Academy of Sciences
			case 8255:
			case 8256:
				$title_list = array(8255, 8256);
				break;
				
			// Transactions of the Connecticut Academy of Arts and Sciences
			case 7541:
			case 5604:
			case 13505:
				$title_list = array(7541, 5604, 13505);
				break;
				
			// Verhandlungen des Zoologisch-Botanischen Vereins in Wien
			// and variations
			case 13275: // Verhandlungen der Kaiserlich-Königlichen Zoologisch-Botanischen Gesellschaft in Wien
			case 11285: // Verhandlungen des Zoologisch-Botanischen Vereins in Wien
				$title_list = array(11285, 13275);
				break;
			

			default:
				break;
		}
		if (count($title_list) != 0)
		{
			$obj->ItemIDs = array();
			foreach ($title_list as $id)
			{
				$obj->ItemIDs = array_merge(bhl_itemid_from_volume($id, $volume, $series), $obj->ItemIDs);
			}
		}
	}
	
	//echo __LINE__;
		
	// At this point if we have any items then we have a potential hit. For each item in the list we
	// query the BHL database and look for pages with PageNumber matching our query
	$num_items = count($obj->ItemIDs);
	if ($num_items != 0)
	{
		for ($i = 0; $i < $num_items; $i++)
		{
			$sql = 'SELECT * FROM bhl_page 
			INNER JOIN page USING(PageID)
			WHERE (bhl_page.ItemID = ' . $obj->ItemIDs[$i]->ItemID . ') 
			AND (PageNumber = ' . $db->qstr($page) . ') 
			ORDER BY SequenceOrder';
			
			//echo $sql;
			
			$result = $db->Execute($sql);
			if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
			
			$obj->ItemIDs[$i]->pages = array();			

			switch ($result->NumRows())
			{
				case 0:
					//no hit :(
					
					// Try and handle case where page one is a title page
					$guess = bhl_step_back(
						$obj->ItemIDs[$i]->ItemID, 
						$page, 
						$obj->ItemIDs[$i]->volume_offset);
					if ($guess != 0)
					{
						$obj->ItemIDs[$i]->pages[] = $guess;
						$obj->hits[] = $guess;
						$obj->ItemIDs[$i]->PageID = $guess;						
					}					
					break;
					
				case 1:
					// unique hit
					$obj->ItemIDs[$i]->pages[] = $result->fields['PageID'];
					$obj->hits[] = $result->fields['PageID'];
					$obj->ItemIDs[$i]->PageID = $result->fields['PageID'];
					break;
					
				default:
					// More than one hit, for example if Item has multiple volumes, hence potentially
					// more than one page with this number.
										
					while (!$result->EOF) 
					{
						$obj->ItemIDs[$i]->pages[] = $result->fields['PageID'];
						$obj->hits[] = $result->fields['PageID'];
						$result->MoveNext();
					}
					// Assume page in nth volume is nth page in SequenceOrder
					// (but actually we store all hits and use string matching on title to filter)
					$obj->ItemIDs[$i]->PageID = $obj->ItemIDs[$i]->pages[$obj->ItemIDs[$i]->volume_offset];
					break;
			}
		}
	}
		
	if ($debug)
	{
		echo '<pre>';
		print_r($obj);
		echo '</pre>';
	}	

	// Summarise results as array of hits 	
	$search_results = array();
	
	// Post process hits, filtering by title match...
	$n = count($obj->ItemIDs);
	for ($i = 0; $i < $n; $i++)
	{
		foreach ($obj->ItemIDs[$i]->pages as $page)
		{
			$search_hit = bhl_score_page($page, $atitle);
			
/*			new stdclass;
			$search_hit->PageID = $page;
			$search_hit->score = -1;
			$search_hit->snippet = '';
			
			// Score title match
			if ($atitle != '')
			{
				$search_hit->score = bhl_score_string(
					$search_hit->PageID, 
					$atitle, 
					$search_hit->snippet
					);
			} */
			
			$search_results[] = $search_hit;
		}	
	}
	
	//return $obj;
	return $search_results;
}

//--------------------------------------------------------------------------------------------------
/**
 * @brief Score a page in BHL
 *
 * @param PageID BHL PageID
 * @param title Title of reference being sought
 *
 * @return Search hit
 *
 */
function bhl_score_page($PageID, $title)
{
	$search_hit = new stdclass;

	$search_hit->score = -1;
	$search_hit->snippet = '';	
	$search_hit->PageID = $PageID;

	if ($title != '')
	{
		$search_hit->score = bhl_score_string(
			$search_hit->PageID, 
			$title, 
			$search_hit->snippet
			);
	}
	
	return $search_hit;
}


//--------------------------------------------------------------------------------------------------
/**
 * @brief Step through BHL database to find article
 *
 * We step forwards through pages until we find a numbered page, then compute the position of
 * the target page. 
 *
 * For example, for "The identities of the Colombian Frogs confused with Eleutherodactulys latidiscus
 * (Boulenger) (Amphibia: Anura: Leptodactylidae)" page 1 is http://biodiversitylibrary.org/page/4466887
 * but this is labelled "Title Page", not "Page 1". The next page is labelled "Page 2". If we were
 * searching for "Page 1" we increment page numbers until we hit a numbered page (in this case we get
 * hit "Page 2" http://biodiversitylibrary.org/page/4466857 . Using the SequenceOrder for this item,
 * we get the predecessor of "Page 2" in the item (http://biodiversitylibrary.org/page/4466887) and
 * return that as our hit.
 *
 * For multiple volume items the success of this approach depends on the Item having all volumes, 
 * which is often not the case.
 *
 * @param ItemID BHL ItemID
 * @param page Target page of article
 * @param offset Offset for target volume if Item has multiple volumes, default is 0 (one volume in item)
 * @param window How far we want to look ahead for a numbered page
 *
 * @return
 *
 */
// It may be that first page is a cover/title page, especially if spage = 1
function bhl_step_back($ItemID, $page, $offset = 0, $window = 3)
{
	global $db;
	
	$hit = 0;
	
	// 0. All pages in item in SequenceOrder
	$item_sequence = array();
	$sql = 'SELECT PageID, SequenceOrder FROM page 
			WHERE (ItemID=' . $ItemID . ')
			ORDER By SequenceOrder';
	
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);


	
	while (!$result->EOF) 
	{
		$item_sequence[$result->fields['SequenceOrder']] = $result->fields['PageID'];
		$result->MoveNext();
	}
	
	
	// 1. Go forward from target page until we hit a numbered page
	$pages 			= array();
	$page2sequence 	= array();
	$page_type 		= array();
	$page_number 	= $page;
	$sequence2page 	= array();
	$found = false;

	// Step through pages until we get a numbered page
	while (($page_number < $page + $window) && !$found)
	{
		$page_number++;
		
		$sql = 'SELECT * FROM bhl_page 
	INNER JOIN page USING(PageID)
	WHERE (bhl_page.ItemID = ' . $ItemID . ') 
	AND (PageNumber = ' . $db->qstr($page_number) . ') 
	ORDER BY SequenceOrder';
	

		$result = $db->Execute($sql);
		if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);


		
		while (!$result->EOF) 
		{
			array_push($pages, $result->fields['PageID']);
			$page2sequence[$result->fields['PageID']] = $result->fields['SequenceOrder'];
			$sequence2page[$result->fields['SequenceOrder']] = $result->fields['PageID'];
			$page_type[$result->fields['PageID']] = $result->fields['PageTypeName'];
			$found = true;
			
			$result->MoveNext();			
		}
	}
	
	// If we got a hit step back to actual target page. For example, if looking for Page 1 and
	// we find Page 2, then we step back one (in sequence order) to get Page 1
	if (count($pages) > 0)
	{
		/*echo '<pre>';
		print_r($pages);
		print_r($page2sequence);
		print_r($page_type);
		echo '</pre>';
		*/
		$found_page = $pages[$offset];
		$found_sequence = $page2sequence[$found_page];
		
		$hit = $item_sequence[$found_sequence - ($page_number - $page)];
	}
	
	return $hit;

}


//--------------------------------------------------------------------------------------------------
function bhl_find_article_from_article_title($atitle, $title, $volume, $page, $series = '')
{
	global $db;
	
	// Data structure to hold search result
	$obj = new stdclass;
	$obj->TitleID = 0;
	$obj->ISSN = '';
	$obj->ItemIDs = array();
	$obj->hits = array();
	
	// Do we have this title?
	$hits = bhl_title_lookup($atitle);
	
	if (count($hits) > 0)
	{
		if ($hits[0]['sl'] > 90)
		{
			$obj->TitleID = $hits[0]['TitleID'];
		}
	}
	
	if ($obj->TitleID != 0)
	{
		bhl_title_retrieve ($obj->TitleID, $obj);
		
		//echo $obj->TitleID;
		
		$volume_offset = 0;
		$obj->ItemIDs = bhl_itemid_from_volume($obj->TitleID, $volume, $series);
		{
			//print_r($obj->ItemIDs);
		}
		
		$sql = 'SELECT * FROM bhl_page 
		INNER JOIN page USING(PageID)
		WHERE (bhl_page.ItemID = ' . $obj->ItemIDs[0]->ItemID . ') 
		AND (PageNumber = ' . $db->qstr($page) . ') 
		ORDER BY SequenceOrder';
				
		$result = $db->Execute($sql);
		if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

		switch ($result->NumRows())
		{
			case 0:
				//no hit :(
				
				// Try and handle case where page one is a title page
				if ($page == 1)
				{
					$guess = bhl_step_back($obj->ItemIDs[0]->ItemID, $page, $obj->ItemIDs[0]->volume_offset);
					if ($guess != 0)
					{
						array_push($obj->hits, $guess);
						$obj->ItemIDs[0]->PageID = $guess;						
					}
				}
				break;
				
			case 1:
				// unique hit
				array_push($obj->hits, $result->fields['PageID']);
				$obj->ItemIDs[0]->PageID = $result->fields['PageID'];
				break;
		
			default:
				break;
				
		}
		
		
	
	}

//	return $obj;

	// Summarise results as array of hits 	
	$search_results = array();
	
	foreach ($obj->hits as $hit)
	{
		// Post process hits, filtering by title match...
		$search_hit = new stdclass;
		$search_hit->PageID = $hit;
		$search_hit->score = -1;
		$search_hit->snippet = '';
		
		// Score title match
		if ($atitle != '')
		{
			$search_hit->score = bhl_score_string(
				$search_hit->PageID, 
				$atitle, 
				$search_hit->snippet
				);
		}
		$search_results[] = $search_hit;
	}
	
	return $search_results;
}




function test_bhl_find()
{
	$tests = array();
	
	//----------------------------------------------------------------------------------------------
	// Journal of Hymenoptera Research
	array_push($tests, array(
		'title' => 'Journal of Hymenoptera Research', 
		'volume' => 6,
		'spage'=> 256,
		'PageID' => 4491707)
		);
		
	// Multiple pages in same item (multiple volumes)
	array_push($tests, array(
		'title' => 'Journal of Hymenoptera Research', 
		'volume' => 8,
		'spage'=> 1,
		'PageID' => 4491014)
		);
	
	//----------------------------------------------------------------------------------------------
	// Fieldiana
	array_push($tests, array(
		'title' => 'Fieldiana, Zoology', 
		'volume' => 31,
		'spage'=> 149,
		'PageID' => 2763486)
		);
	array_push($tests, array(
		'title' => 'Fieldiana, Zoology', 
		'volume' => 39,
		'spage'=> 577,
		'PageID' => 2866715)
		);
		
	// Two hits
	array_push($tests, array(
		'title' => 'Fieldiana, Zoology', 
		'volume' => 73,
		'spage'=> 49,
		'PageID' => 2759622)
		);
	array_push($tests, array(
		'title' => 'Fieldiana, Zoology', 
		'volume' => 77,
		'spage'=> 1,
		'PageID' => 2866529)
		);
		
		
	//----------------------------------------------------------------------------------------------
	// University of Kansas Science Bulletin
	array_push($tests, array(
		'title' => 'University of Kansas Science Bulletin', 
		'volume' => 35,
		'spage'=> 577,
		'PageID' => 4413503)
		);
		
	//----------------------------------------------------------------------------------------------
	// Bulletin of Zoological Nomenclature
	array_push($tests, array(
		'title' => 'Bulletin of Zoological Nomenclature', 
		'volume' => 23,
		'spage'=> 169,
		'PageID' => 12222978)
		);
		
	//----------------------------------------------------------------------------------------------
	// Proceedings of the California Academy of Sciences
	array_push($tests, array(
		'title' => 'Proceedings of the California Academy of Sciences', 
		'volume' => 47,
		'spage'=> 47,
		'PageID' => 15776069)
		);		

	//----------------------------------------------------------------------------------------------
	// Ann Mag Nat Hist
	array_push($tests, array(
		'title' => 'Ann Mag Nat Hist', 
		'volume' => 20,
		'spage'=> 413,
		'series' => 8,
		'PageID' => 15611435)
		);	
		
	//----------------------------------------------------------------------------------------------
	// Bulletin of the British Museum (Natural History). Zoology

	array_push($tests, array(
		'title' => 'Bulletin of the British Museum (Natural History). Zoology', 
		'volume' => 34,
		'spage'=> 65,
		'PageID' => 2261841)
		);	
	array_push($tests, array(
		'title' => 'Bulletin of the British Museum (Natural History). Zoology', 
		'volume' => 27,
		'spage'=> 65,
		'PageID' => 2261309)
		);	
	array_push($tests, array(
		'title' => 'Bulletin of the British Museum (Natural History). Zoology', 
		'volume' => 27,
		'spage'=> 59,
		'PageID' => 2261319)
		);	
		
	//----------------------------------------------------------------------------------------------
	// Bulletin of the British Museum (Natural History). Entomology

	array_push($tests, array(
		'title' => 'Bulletin of the British Museum (Natural History): Entomology', 
		'volume' => 12,
		'spage'=> 247,
		'PageID' => 2298342)
		);	
		
	//----------------------------------------------------------------------------------------------
	// Memoirs of the Museum of Comparative Zoölogy
	
	array_push($tests, array(
		'title' => 'Memoirs of the Museum of Comparative Zoölogy', 
		'volume' => 50,
		'spage'=> 85,
		'PageID' => 15776069)
		);	
	
	//----------------------------------------------------------------------------------------------
	// Proc. ent. Soc. Wash.
	// Banks, N. (1899b). Some spiders from northern Louisiana. Proc. ent. Soc. Wash. 4: 188-195.

	array_push($tests, array(
		'title' => 'Proc. ent. Soc. Wash.', 
		'volume' => 4,
		'spage'=> 188,
		'PageID' => 2299619)
		);	

		
	//----------------------------------------------------------------------------------------------
	// Ann. Soc. ent. Fr.
	// Simon, E. (1885c). Etudes arachnologiques. 17e Mémoire. XXIV. Arachnides recuellis dans la 
	// vallée de Tempé et sur le mont Ossa (Thessalie). Ann. Soc. ent. Fr. (6) 5: 209-218.

	array_push($tests, array(
		'title' => 'Ann. Soc. ent. Fr.', 
		'volume' => 5,
		'spage'=> 209,
		'series' => 6,
		'PageID' => 10171703)
		);
		
	//----------------------------------------------------------------------------------------------
	// Mitteilungen der Schweizerischen Entomologischen Gesellschaft
	// Forel A (1887) Fourmis récoltées à Madagascar par le Dr. Conrad Keller. Mitteilungen der Schweizerischen Entomologischen Gesellschaft 7: 381–389. 
		
	array_push($tests, array(
		'title' => 'Mitteilungen der Schweizerischen Entomologischen Gesellschaft', 
		'volume' => 7,
		'spage'=> 381,
		'PageID' => 10395996)
		);

	
	//----------------------------------------------------------------------------------------------
	// Revue zoologique africaine
	array_push($tests, array(
		'title' => 'Revue zoologique africaine', 
		'volume' => 9,
		'spage'=> 1,
		'PageID' => 4491707)
		);
		
		
	echo '<pre>';
	$ok = 0;
	$failed = array();
	foreach ($tests as $test)
	{
		echo $test['title'] . ' ' . $test['volume'] . ' ' . $test['spage'] . ' ...';
	
		$search_hits = bhl_find_article(
			$test['title'], 
			$test['volume'],
			$test['spage'],
			(isset($test['series']) ? $test['series'] : '')
			);
		$hits = $search_hits;
		
		$matched = in_array($test['PageID'], $hits->hits); 
		
		if ($matched)
		{
			$ok++;
			echo " [" . count($hits->hits) . "] ok\n";
		}
		else
		{
			echo " not found\n";
			array_push($failed, array($test, $hits));
		}
	}
	
	// Report
	echo count($tests) . ' references, ' . (count($tests) - $ok) . ' failed' . "\n";
	print_r($failed);
	
	echo '</pre>';
	
	
}



// test parsers
//test_bhl_find();


/*




// Display hits...
echo '<b>Page hit</b>';
echo '<table border="0" cellpadding="10">';
foreach ($search_hits->hits as $h)
{
	echo '<tr>';
	echo '<td>';
	echo '<img style="border:1px solid rgb(128,128,128);" src="thumbnail.php?PageID=' . $h . '"/><br/>';
	echo '</td>';
	
	echo '<td valign="top">';	
	$text = get('http://iphylo.org/~rpage/bhl/ocr.php?PageID=' . $h);
	$snippet = trim_text($text, 40);
	echo '<span style="color:green;">' . $snippet . '</span><br/>';
	
	// direct link to BHL page
	echo '<a href="http://www.biodiversitylibrary.org/page/' . $h . '">' . $h . '</a><br/>';
	
	// OpenURL
	
	
	// COinS
	echo '</td>';
	
	
	
	echo '</tr>';
}
echo '</table>';

// can we offer a fallback to item?
if (count($search_hits->hits) == 0)
{
	// No hits to page level, but maybe we have just one item
	
	if (count($search_hits->ItemIDs) == 1)
	{
		// display title page of item
		$pages = bhl_title_page($search_hits->ItemIDs[0]->ItemID);
		
		echo "<b>Title hit (didn't get page hit)</b>";
		echo '<table border="0" cellpadding="10">';
		foreach ($pages as $PageID)
		{
			echo '<tr>';
			echo '<td>';
			echo '<img style="border:1px solid rgb(128,128,128);" src="thumbnail.php?PageID=' . $PageID . '"/><br/>';
			echo '</td>';
			
			echo '<td valign="top">';
			$text = get('http://iphylo.org/~rpage/bhl/ocr.php?PageID=' . $PageID);
			$snippet = trim_text($text, 40);
			echo '<span style="color:green;">' . $snippet . '</span><br/>';
			
			// direct link to BHL page
			echo '<a href="http://www.biodiversitylibrary.org/page/' . $PageID . '">' . $PageID . '</a><br/>';
			
			// OpenURL
			
			
			// COinS
			echo '</td>';
			echo '</tr>';
			
		}
		echo '</table>';
	}
		
	
	
	
}



*/



?>