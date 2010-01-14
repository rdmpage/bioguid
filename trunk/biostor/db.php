<?php

/**
 * @file db.php Database
 *
 */

require_once (dirname(__FILE__) . '/config.inc.php');
require_once (dirname(__FILE__) . '/adodb5/adodb.inc.php');

require_once (dirname(__FILE__) . '/bhl_date.php');
require_once (dirname(__FILE__) . '/bhl_search.php');

require_once (dirname(__FILE__) . '/identifier.php');
require_once (dirname(__FILE__) . '/utilities.php');

//--------------------------------------------------------------------------------------------------
$db = NewADOConnection('mysql');
$db->Connect("localhost", 
	$config['db_user'] , $config['db_passwd'] , $config['db_name']);

// Ensure fields are (only) indexed by column name
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;


//--------------------------------------------------------------------------------------------------
function db_reference_from_bhl($reference_id)
{
	global $db;
	global $ADODB_FETCH_MODE;
	
	$in_bhl = false;
	
	$sql = 'SELECT PageID FROM rdmp_reference
	WHERE (reference_id = ' . $reference_id . ') LIMIT 1';
	
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
	
	if ($result->NumRows() == 1)
	{
		$in_bhl = ($result->fields['PageID'] != 0);
	}
	
	return $in_bhl;
}

//--------------------------------------------------------------------------------------------------
// references with a given PageID
function bhl_reference_from_pageid($PageID)
{
	global $db;
	global $ADODB_FETCH_MODE;
	
	$references = array();
		
	$sql = 'SELECT reference_id FROM rdmp_reference
		WHERE (PageID=' . $PageID . ')';
				
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

	while (!$result->EOF) 
	{
		$references[] = $result->fields['reference_id'];
		$result->MoveNext();
	}
	
	return $references;
}


//--------------------------------------------------------------------------------------------------
// could do this much better
// Retrieve uBio name by NameBankID
function bhl_retrieve_name_from_namebankid($id)
{
	global $db;
	global $ADODB_FETCH_MODE;
	
	$name = NULL;
	
	$sql = 'SELECT * FROM bhl_page_name
		WHERE (NameBankID=' . $id . ') LIMIT 1';
				
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

	if ($result->NumRows() == 1)
	{
		$name = new stdclass;
		$name->NameBankID = $result->fields['NameBankID'];
		$name->NameString = $result->fields['NameConfirmed'];
		$name->Identifier = 'urn:lsid:ubio.org:namebank:' . $result->fields['NameBankID'];
	}
	
	return $name;
}

//--------------------------------------------------------------------------------------------------
// Retrieve uBio name by name string
function bhl_retrieve_name_from_namestring($namestring)
{
	global $db;
	global $ADODB_FETCH_MODE;
	
	$name = NULL;
	
	$sql = 'SELECT * FROM bhl_page_name
		WHERE (NameConfirmed=' . $db->qstr($namestring) . ') LIMIT 1';
				
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

	if ($result->NumRows() == 1)
	{
		$name = new stdclass;
		$name->NameBankID = $result->fields['NameBankID'];
		$name->NameString = $result->fields['NameConfirmed'];
		$name->Identifier = 'urn:lsid:ubio.org:namebank:' . $result->fields['NameBankID'];
	}
	
	return $name;
}

//--------------------------------------------------------------------------------------------------
/**
 * @brief Find range of pages spanned by a putative article
 *
 * @param start_page_id PageID of first page in article
 * @param num_pages Numbers of contiguous pages in article
 *
 * @return Array of PageIDs in order 
 *
 */
function bhl_page_range ($start_page_id, $num_pages)
{
	global $db;
	$PageID = array();	
	
	// Get ItemID and SequenceOrder for this page
	$sql = 'SELECT ItemID, SequenceOrder FROM page
		WHERE (PageID=' . $start_page_id . ') LIMIT 1';
				
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
	
	if ($result->NumRows() == 1)
	{
		$ItemID = $result->fields['ItemID'];
		$SequenceOrder = $result->fields['SequenceOrder'];
		
		$sql = 'SELECT PageID, SequenceOrder FROM page 
				WHERE (ItemID=' . $ItemID . ')
				ORDER By SequenceOrder';
				
		$result = $db->Execute($sql);
		if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
						
		// Get continuous span of pages. Assume SequenceOrder is linear, but we
		// don't assume SequenceOrder is continuous
		$pages = array();
		$store = false;
		$count = 0;
		while (!$result->EOF) 
		{
			if (!$store)
			{
				$store = ($result->fields['SequenceOrder'] == $SequenceOrder);
			}
			if ($store)
			{
				$PageID[] = $result->fields['PageID'];		
				$count++;
				
				$store = ($count < $num_pages);
			}
			$result->MoveNext();
		}
	}


	return $PageID;
}


//--------------------------------------------------------------------------------------------------
// If we've geocoded this reference then we will have locality ids for each page, even if they
// are 0
function bhl_has_been_geocoded($reference_id)
{
	global $db;

	$sql = 'SELECT * FROM rdmp_reference_page_joiner 
INNER JOIN rdmp_locality_page_joiner USING(PageID)
WHERE (reference_id = ' . $reference_id . ')';

	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

	return ($result->NumRows() != 0);
}

//--------------------------------------------------------------------------------------------------
// return localities for pages. Pages with no localities have locality_id 0, which doesn't
// occur on locality table, and hence won't be listed here.
function bhl_localities_for_reference($reference_id)
{
	global $db;
	
	$pts = array();
	
	$sql = 'SELECT DISTINCT(locality_id), latitude, longitude, woeid, name FROM rdmp_reference_page_joiner 
INNER JOIN rdmp_locality_page_joiner USING(PageID)
INNER JOIN rdmp_locality USING(locality_id)
WHERE (reference_id = ' . $reference_id . ')';

	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

	while (!$result->EOF) 
	{
		$pt = new stdclass;
		$pt->latitude = $result->fields['latitude'];
		$pt->longitude = $result->fields['longitude'];
		$pt->woeid = $result->fields['woeid'];
		$pt->name = $result->fields['name'];
		$pts[] = $pt;
		$result->MoveNext();
	}
	
	return $pts;

}


//--------------------------------------------------------------------------------------------------
// Store link between page and locality
function bhl_store_locality_link ($PageID, $loc_id)
{
	global $db;
	
	$sql = 'SELECT * FROM rdmp_locality_page_joiner
	WHERE(PageID = ' . $PageID . ') 
	AND (locality_id = ' . $loc_id . ')
	LIMIT 1';

	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

	if ($result->NumRows() == 0)
	{
		$sql = 'INSERT rdmp_locality_page_joiner(PageID, locality_id) VALUES (' 
		. $PageID
		. ',' . $loc_id . ')';

		$result = $db->Execute($sql);
		if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

	}
}

//--------------------------------------------------------------------------------------------------
// Store a locality, ideally with a woeId
function db_store_locality($loc)
{
	global $db;
	global $ADODB_FETCH_MODE;
	
	$loc_id = 0;
		
	if (isset($loc->woeId) && ($loc->woeId != ''))
	{
		$sql = 'SELECT * FROM rdmp_locality
		WHERE(woeid = ' . $db->qstr($loc->woeId) . ') LIMIT 1';
	}
	else
	{
		$sql = 'SELECT * FROM rdmp_locality
		WHERE(latitude = ' . $loc->latitude . ') 
		AND (longitude = ' . $loc->longitude . '
		) LIMIT 1';	
	}
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);


	if ($result->NumRows() == 1)
	{
		// We have this locality
		$loc_id = $result->fields['locality_id'];
	}
	else
	{
		if (isset($loc->woeId) && ($loc->woeId != ''))
		{
			$sql = 'INSERT rdmp_locality(name, latitude, longitude, loc, woeid) VALUES (' 
			. $db->qstr($loc->name) 
			. ',' . $loc->latitude
			. ',' . $loc->longitude
			. ", GeomFromText('POINT(" . $loc->longitude . " " . $loc->latitude . ")')"
			. ',' . $loc->woeId . ')';
		}
		else
		{
			$sql = 'INSERT rdmp_locality(name, latitude, longitude, loc) VALUES (' 
			. $db->qstr($loc->name) 
			. ',' . $loc->latitude
			. ',' . $loc->longitude
			. ", GeomFromText('POINT(" . $loc->longitude . " " . $loc->latitude . ")')"
			.  ')';
		}		

		$result = $db->Execute($sql);
		if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);


		$loc_id = $db->Insert_ID();
	}

	return $loc_id;
}

//--------------------------------------------------------------------------------------------------
function db_retrieve_journal_from_issn ($issn)
{
	global $db;
	global $ADODB_FETCH_MODE;
	
	$journal = NULL;
	
	// for now grab details from references
	$sql = 'SELECT * FROM rdmp_reference WHERE (issn=' . $db->qstr($issn) . ') LIMIT 1';

	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

	if ($result->NumRows() == 1)
	{
		$journal = new stdclass;
		$journal->title = $result->fields['secondary_title'];
		$journal->issn = $issn;
	}
	
	return $journal;
}

//--------------------------------------------------------------------------------------------------
// Note that we use MySQL CAST() to ensure ordering is numeric, not lexical
function db_retrieve_articles_from_journal ($issn)
{
	global $db;
	global $ADODB_FETCH_MODE;
	
	$articles = array();
	
	// for now grab details from references
	$sql = 'SELECT *  FROM rdmp_reference WHERE (issn=' . $db->qstr($issn) . ')
	ORDER BY CAST(volume AS SIGNED), CAST(spage AS SIGNED)';
	
	// , CAST(issue AS SIGNED)

	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

	while (!$result->EOF) 
	{
		$reference = new stdclass;
		$reference->id = $result->fields['reference_id'];
		$reference->title = $result->fields['title'];
		
		if (isset($result->fields['volume']))
		{
			if (!isset($articles[$result->fields['volume']]))
			{
				$articles[$result->fields['volume']] = array();
			}
/*			if (isset($result->fields['issue']))
			{
				if (!isset($articles[$result->fields['volume']][$result->fields['issue']]))
				{
					$articles[$result->fields['volume']][$result->fields['issue']] = array();
				}
				$articles[$result->fields['volume']][$result->fields['issue']][] = $reference;
			}
			else*/
			{
				$articles[$result->fields['volume']][] = $reference;
			}
		}
		else
		{
			$articles[] = $reference;
		}
		$result->MoveNext();
	}
	
	return $articles;

}


//--------------------------------------------------------------------------------------------------
function db_retrieve_journal_names_from_issn ($issn)
{
	global $db;
	global $ADODB_FETCH_MODE;
	
	$titles = array();
	
	// for now grab details from references
	$sql = 'SELECT DISTINCT(secondary_title) FROM rdmp_reference 
	WHERE (issn=' . $db->qstr($issn) . ')';

	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

	while (!$result->EOF) 
	{
		$titles[] = $result->fields['secondary_title'];
		$result->MoveNext();
	}
	
	return $titles;

}

//--------------------------------------------------------------------------------------------------
function bhl_image_source_is_ia($PageID)
{
	global $db;
	global $ADODB_FETCH_MODE;
	
	$is_ia = false;
	
	$sql = 'SELECT * FROM page WHERE PageID=' . $PageID . ' LIMIT 1';
	
	//echo $sql;
	
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
	
	if ($result->NumRows() == 1)
	{
		//echo $FileNamePrefix;
		$FileNamePrefix = $result->fields['FileNamePrefix'];
		$is_ia = !is_numeric($FileNamePrefix{0});
	}
	
	return $is_ia;
}

//--------------------------------------------------------------------------------------------------
function bhl_retrieve_reference_pages($reference_id)
{
	global $db;
	global $ADODB_FETCH_MODE;
	
	$pages = array();
	
	$sql = 'SELECT DISTINCT(PageID), page_order, PagePrefix, PageNumber 
	FROM rdmp_reference_page_joiner 
	INNER JOIN bhl_page USING(PageID)
	WHERE (reference_id = ' . $reference_id . ')
	ORDER BY page_order';
	
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

	while (!$result->EOF) 
	{
		$page = new stdclass;
		$page->PageID = $result->fields['PageID'];
		$page->page_order = $result->fields['page_order'];
		$page->PagePrefix = $result->fields['PagePrefix'];
		$page->PageNumber = $result->fields['PageNumber'];
		
		$pages[] = $page;
		$result->MoveNext();
	}
	
	return $pages;
}

//--------------------------------------------------------------------------------------------------
function bhl_retrieve_title($id)
{
	global $db;
	global $ADODB_FETCH_MODE;
	
	$title = NULL;
	
	$sql = 'SELECT * FROM bhl_title
		WHERE (TitleID=' . $id . ') LIMIT 1';
				
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

	if ($result->NumRows() == 1)
	{
		$title = new stdclass;
		
		$title->id = $result->fields['TitleID'];
		$title->FullTitle = $result->fields['FullTitle'];
		$title->ShortTitle = $result->fields['ShortTitle'];
		$title->PublicationDetails = $result->fields['PublicationDetails'];
		$title->StartYear = $result->fields['StartYear'];
		$title->EndYear = $result->fields['EndYear'];
		$title->LanguageCode = $result->fields['LanguageCode'];
		$title->TL2Author = $result->fields['TL2Author'];
		
		// Identifiers
		$sql = 'SELECT * FROM bhl_title
			INNER JOIN bhl_title_identifier USING(TitleID)
			WHERE (TitleID=' . $id . ')';
			
		$result = $db->Execute($sql);
		if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
			
		
		$title->identifiers = array();
		
		while (!$result->EOF) 
		{
			$title->identifiers[] = array(
				'namespace' => $result->fields['IdentifierName'],
				'identifier' => $result->fields['IdentifierValue']
				);
			$result->MoveNext();				
		}			
		
		// Institution
		
		
		
		// Date range
		$sql = 'SELECT VolumeInfo, Year FROM bhl_item
			WHERE (TitleID=' . $id . ')';
		
		$result = $db->Execute($sql);
		if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
		
		$title->years = array();
		while (!$result->EOF) 
		{
			$info = new stdclass;
			
			$parsed = bhl_date_from_details($result->fields['Year'], $info);
			if (!$parsed)
			{
				$parsed = parse_bhl_date($result->fields['VolumeInfo'], $info);
			}
			if ($parsed)
			{
				//print_r($info);
				if (isset($info->start))
				{
					if (!isset($title->years[$info->start]))
					{
						$title->years[$info->start] = 0;
					}
					$title->years[$info->start]++;
					if (isset($info->end))
					{
						for ($i = $info->start; $i <= $info->end; $i++)
						{
							if (!isset($title->years[$i]))
							{
								$title->years[$i] = 0;
							}
							$title->years[$i]++;
						}
					}
				}
			}
			
			$result->MoveNext();				
		}
		$years = array_keys($title->years);
		sort($years);
		
		$from = $years[0];
		$to = $years[count($years)-1];
		
		for ($i = $from; $i < $to; $i++)
		{
			if (!in_array($i, $years))
			{
				$title->years[$i] = 0;
			}
		}
		
		// sort
		
		ksort($title->years);
		
	}
	return $title;
}


//--------------------------------------------------------------------------------------------------
function db_retrieve_author($author_id)
{
	global $db;
	global $ADODB_FETCH_MODE;
	
	$author = NULL;

	$sql = 'SELECT * FROM rdmp_author
	WHERE (author_id = ' . $db->qstr($author_id) . ')';
		
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);


	if ($result->NumRows() == 1)
	{
		$author = new stdClass;
		$author->id = $result->fields['author_id'];
		$author->lastname = $result->fields['lastname'];
		$author->forename = $result->fields['forename'];
		
		if ($result->fields['suffix'] != '')
		{
			$author->suffix = $result->fields['suffix'];
		}
	}
	
	return $author;
}

//------------------------------------------------------------------------------
// Get number of references for year for author, and return these as array where
// where years are keys and count is value. Intervening years with no publications are filled with
// zeros.
function db_retrieve_author_timeline ($author_id)
{
	global $db;
	global $ADODB_FETCH_MODE;

	$timeline = array();
	
	/*
	$sql = 'SELECT year, COUNT(reference_id) as c FROM rdmp_reference
INNER JOIN rdmp_author_reference_joiner USING (reference_id)
	WHERE (author_id = ' . $author_id . ')
GROUP BY year
ORDER BY year'; */

	// Authored by cluster of names
	$author_cluster_id = db_get_author_cluster_id($author_id);
	$sql = 'SELECT year, COUNT(reference_id) as c FROM rdmp_reference
INNER JOIN rdmp_author_reference_joiner USING (reference_id)
INNER JOIN rdmp_author USING (author_id)
WHERE (author_cluster_id = ' . $author_cluster_id . ')
GROUP BY year
ORDER BY year';


	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);


	while (!$result->EOF) 
	{
		if ($result->fields['year'] != '')
		{
			$timeline[$result->fields['year']] = $result->fields['c'];
		}
		$result->MoveNext();				
	}
	
	// Fill in missing years
	$years = array_keys($timeline);
	
	$from = $years[0];
	$to = $years[count($years)-1];
	for ($i = $from; $i < $to; $i++)
	{
		if (!in_array($i, $years))
		{
			$timeline[$i] = 0;
		}
	}
	
	// sort
	
	ksort($timeline);
	

	return $timeline;
}

//------------------------------------------------------------------------------
// Get author cluster id
function db_get_author_cluster_id ($author_id)
{
	global $db;
	global $ADODB_FETCH_MODE;
	
	$sql = 'SELECT author_cluster_id 
	FROM rdmp_author
	WHERE author_id = ' . $author_id . ' LIMIT 1';

	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
	
	return $result->fields['author_cluster_id'];
}

//------------------------------------------------------------------------------
function db_get_all_author_names($author_id)
{
	global $db;
	global $ADODB_FETCH_MODE;

	$author_names = array();
	
	$author_cluster_id = db_get_author_cluster_id($author_id);

	$sql = 'SELECT * 
	FROM rdmp_author
	WHERE author_cluster_id = ' . $author_cluster_id;

	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

	while (!$result->EOF) 
	{
		$name = trim($result->fields['forename'] 
			. ' ' . $result->fields['lastname']
			. ' ' . $result->fields['suffix']);
		
		$names[] = array(
			'author_id' => $result->fields['author_id'],
			'name' => $name); 
		$result->MoveNext();				
	}

	return $names;
}
	

//------------------------------------------------------------------------------
function db_retrieve_authored_references ($author_id, $start = 0, $limit = 10)
{
	global $db;
	global $ADODB_FETCH_MODE;

	$refs = array();
	
	// Authored by just this author name
/*	$sql = 'SELECT * FROM rdmp_reference
INNER JOIN rdmp_author_reference_joiner USING (reference_id)
	WHERE (author_id = ' . $author_id . ')
ORDER BY year'; */

	// Authored by cluster of names
	$author_cluster_id = db_get_author_cluster_id($author_id);
	$sql = 'SELECT * FROM rdmp_reference
INNER JOIN rdmp_author_reference_joiner USING (reference_id)
INNER JOIN rdmp_author USING (author_id)
WHERE (author_cluster_id = ' . $author_cluster_id . ')
ORDER BY year';

	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);


	while (!$result->EOF) 
	{
		$refs[] = $result->fields['reference_id'];
		$result->MoveNext();				
	}

	return $refs;
}

//------------------------------------------------------------------------------
function db_retrieve_coauthors ($author_id)
{
	global $db;
	global $ADODB_FETCH_MODE;

	$c = new stdclass;
	$c->author_id = $author_id;
	$c->coauthors = array();
	
	// Get set of author_ids for this cluster containing this author
	$all_names = db_get_all_author_names($author_id);
	$all_ids = array();
	foreach ($all_names as $name)
	{
		$all_ids[] = $name['author_id'];
	}

	$sql = 'SELECT coauthored.author_id, coauthor.forename, coauthor.lastname, coauthor.suffix, COUNT(coauthored.author_id) AS c  
	FROM rdmp_author
	INNER JOIN rdmp_author_reference_joiner USING (author_id)
	INNER JOIN rdmp_author_reference_joiner AS coauthored USING (reference_id)
	INNER JOIN rdmp_author AS coauthor ON coauthor.author_id = coauthored.author_id
	WHERE rdmp_author.author_id IN (' . implode(",", $all_ids) . ')
	AND (coauthor.author_id <> rdmp_author.author_id)
	GROUP BY coauthored.author_id
	ORDER BY coauthor.lastname';
	
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);


	while (!$result->EOF) 
	{
		$author = new stdClass;
		$author->id = $result->fields['author_id'];
		$author->lastname = str_replace("'", "&rsquo;", $result->fields['lastname']);
		$author->forename = $result->fields['forename'];
		$author->count = $result->fields['c'];
		
		if ($result->fields['suffix'] != '')
		{
			$author->suffix = $result->fields['suffix'];
		}
		$c->coauthors[] = $author;
		$result->MoveNext();				
	}

	return $c;
	
}



//------------------------------------------------------------------------------
function db_find_author($author)
{
	global $db;
	
	$id = 0;
		
	// Clean name
	$author->forename = html_entity_decode($author->forename, ENT_QUOTES, "utf-8" ); 
	$author->lastname = html_entity_decode($author->lastname, ENT_QUOTES, "utf-8" ); 
	
	// Handle forename as initials without spaces
	if (preg_match("/^([A-Z]+)$/", $author->forename))
	{
		$spaced = '';
		$forename =  $author->forename;
		for($i=0;$i<strlen($author->forename);$i++)
		{
			$spaced .= $forename{$i} . ' ';
		}
		$author->forename = trim($spaced);
	}
	// Replace . with space (in case . is the only separator between initials
	$author->forename = str_replace('.', ' ', $author->forename);

	// Compress extra blank space to a single space
	$author->forename = preg_replace('/\s+/', ' ', $author->forename);
	$author->forename = preg_replace('/ \-/', '-', $author->forename);
	
	// Make nice (in most cases will already be nice)
	$author->forename = mb_convert_case($author->forename, 
		MB_CASE_TITLE, mb_detect_encoding($author->forename));
	$author->lastname = mb_convert_case($author->lastname, 
		MB_CASE_TITLE, mb_detect_encoding($author->lastname));
	
	// For now we work on exact matches, could improve this	
	$sql = 'SELECT author_id FROM rdmp_author 
		WHERE (lastname=' . $db->qstr($author->lastname) . ')
		AND (forename=' . $db->qstr($author->forename) . ') 
		LIMIT 1';
			
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

	if ($result->RecordCount() == 0)
	{
		// Not found so add
		$sql  = 'INSERT INTO rdmp_author (lastname, forename ';
		
		if (isset($author->suffix))
		{
			$sql .= ', suffix';
		}
				
		$sql .= ') VALUES (' . $db->qstr($author->lastname) 
			. ', ' . $db->qstr($author->forename);
			
		if (isset($author->suffix))
		{
			$sql .= ', ' . $db->qstr($author->suffix);
		}
			
		$sql .= ');';

		$result = $db->Execute($sql);
		if ($result == false) die("failed [" . __LINE__ . "]: " . $insert_sql);
		
		$id = $db->Insert_ID();
		
		// By default new author is in their own cluster
		$sql = 'UPDATE rdmp_author SET author_cluster_id=' . $id . ' WHERE author_id=' . $id;

		$result = $db->Execute($sql);
		if ($result == false) die("failed [" . __LINE__ . "]: " . $insert_sql);		
	}
	else
	{
		$id = $result->fields['author_id'];
	}

	return $id;
}

//------------------------------------------------------------------------------
/**
 * @brief Store author names and link them to the reference
 *
 * Based on the model in MyPHPBib, we store unique author names in
 * the <b>author</b> table, and use the table <b>author_reference_joiner</b>
 * to link them to their publications.
 *
 * @param id Local id of reference
 * @param authors Array of author names
 *
 */
function db_store_authors($id, $authors, $primary_authors = true)
{
	global $db;

	$id_list = array();
	
	foreach ($authors as $author)
	{
		$author_id = db_find_author($author);
		array_push ($id_list, $author_id);
	}
	
	// Table
	$join_name = 'rdmp_author_reference_joiner';
	if (!$primary_authors)
	{
		$join_name = 'rdmp_secondary_author_reference_joiner';
	}
			
	// Link to reference
	$count = count($id_list);
	for ($i = 0; $i < $count; $i++) 
	{
		$sql = 'INSERT INTO ' . $join_name . ' (author_id, reference_id, author_order) 
		VALUES (' . $id_list[$i] . ',' . $id . ',' . ($i + 1) . ')';
		$result = $db->Execute($sql);
		if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);


	}
}


//------------------------------------------------------------------------------
function db_find_article($article)
{
	global $db;

	$id = 0;
	
	// Does a reference exist?
	
	$sql = '';
	
	// Basic triple
	if (
		(isset($article->issn) && ($article->issn != ''))
		&& isset($article->volume)
		&& isset($article->spage)
		)
	{
		$sql = 'SELECT * FROM rdmp_reference
			WHERE (issn = ' .  $db->Quote($article->issn) . ')
			AND (volume = ' .  $db->Quote($article->volume) . ')
			AND (spage = ' .  $db->Quote($article->spage) . ')
			LIMIT 1';
	}
	else
	{
		// No ISSN so try and match on journal title
		$sql = 'SELECT * FROM rdmp_reference
			WHERE (secondary_title = ' .  $db->Quote($article->secondary_title) . ')
			AND (volume = ' .  $db->Quote($article->volume) . ')
			AND (spage = ' .  $db->Quote($article->spage) . ')
			LIMIT 1';
	}
				
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

	
	if ($result->NumRows() == 1)
	{
		$id = $result->fields['reference_id'];
	}
	
	return $id;
}

//--------------------------------------------------------------------------------------------------
function db_retrieve_reference_from_doi($doi)
{
	global $db;

	$id = 0;
	
	$sql = 'SELECT * FROM rdmp_reference WHERE doi=' . $db->qstr($doi) . ' LIMIT 1';
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
	
	if ($result->NumRows() == 1)
	{
		$id = $result->fields['reference_id'];
	}
	return $id;
}


//--------------------------------------------------------------------------------------------------
function db_retrieve_reference_from_sici($sici)
{
	global $db;

	$id = 0;
	
	$s = new Sici($sici);
	
	// First attempt exact macth to SICI
	if ($id == 0)
	{
		$sql = 'SELECT * FROM rdmp_reference WHERE sici=' . $db->qstr($sici) . ' LIMIT 1';
		$result = $db->Execute($sql);
		if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
		
		if ($result->NumRows() == 1)
		{
			$id = $result->fields['reference_id'];
		}
	}
	
	/*
	 * Array
	 * (
	 *     [item] => 0096-3801(1958)108:3392
	 *     [contrib] => 25:BCANSO
	 *     [control] => 2.0.CO;2-R
	 *     [issn] => 0096-3801
	 *     [chron] => 1958
	 *     [enum] => 108:3392
	 *     [year] => 1958
	 *     [locn] => 
	 *     [title] => BCANSO
	 *     [site] => 25
	 *     [issue] => 3392
	 *     [volume] => 108
	 *     [csi] => 2
	 *     [dpi] => 0
	 *     [mfi] => CO
	 *     [version] => 2
	 *     [check] => R
	 * )
	 */
	
	// If no hit, unpack SICI and seek match for metadata
	if ($id == 0)
	{
		$parts =$s->unpack();
		
		$title = '';
		if (isset($parts['title']))
		{
			$n = strlen($parts['title']);
			for($i = 0; $i < $n; $i++)
			{
				$title .= $parts['title']{$i} . '% ';
			}
			$title = trim($title);
		}
		
		$sql = 'SELECT * FROM rdmp_reference
			WHERE (issn = ' .  $db->Quote($parts['issn']) . ')
			AND (volume = ' .  $db->Quote($parts['volume']) . ')
			AND (spage = ' .  $db->Quote($parts['site']) . ')';
			
		if ($title != '')
		{
			$sql .= ' AND (title LIKE ' . $db->qstr($title) . ')';
		}
		$sql .= ' LIMIT 1';
		
		//echo $sql;
		
		$result = $db->Execute($sql);
		if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
		
		if ($result->NumRows() == 1)
		{
			$id = $result->fields['reference_id'];
		}		
	}
	
	return $id;
}


//------------------------------------------------------------------------------
function db_store_article($article, $PageID = 0, $updating = false)
{
	global $db;
	
	$update = false;
	
	$id = db_find_article($article);
	if ($id != 0)
	{
		if ($updating)
		{
			$update = true;
		}
		else
		{
			return $id;
		}
	}
	
	if (!isset($article->genre))
	{
		$article->genre = 'article';
	}
	
	$keys = array();
	$values = array();
	
	// Article metadata
	foreach ($article as $k => $v)
	{
		switch ($k)
		{
			// Ignore as it's an array
			case 'authors':
				break;		
							
			case 'date':			
				$keys[] = 'date';
				$values[] = $db->qstr($v);
				if (!isset($article->year))
				{
					$keys[] = 'year';
					$values[] = $db->qstr(year_from_date($v));
				}
				break;
				
			// Don't store BHL URL here
			case 'url':
				if (preg_match('/^http:\/\/(www\.)?biodiversitylibrary.org\/page\/(?<pageid>[0-9]+)/', $v))
				{
				}
				else
				{
					$keys[] = $k;
					$values[] = $db->qstr($v);
				}
				break;			
				
			
			// Things we store as is
			case 'title':
			case 'secondary_title':
			case 'volume':
			case 'series':
			case 'issue':
			case 'spage':
			case 'epage':
			case 'year':
			case 'date':
			case 'issn':
			case 'genre':
			case 'doi':
			case 'lsid':
				$keys[] = $k;
				$values[] = $db->qstr($v);
				break;			
			
			// Things we ignore
			default:
				break;
		}
	}
	
	// Date
	if (!isset($article->date) && isset($article->year))
	{
		$keys[] = 'date';
		$values[] = $db->qstr($article->year . '-00-00');
	}

	
	// BHL PageID
	if ($PageID != 0)
	{
		$keys[] = 'PageID';
		$values[] = $PageID;
	}	
	
	// SICI
	$s = new Sici;
	$sici = $s->create($article);
	if ($sici != '')
	{
		$keys[] = 'sici';
		$values[] = $db->qstr($sici);
	}
	
	if ($update)
	{
		// Versioning?
	
		// Delete links	(author, pages, etc)
		$sql = 'DELETE FROM rdmp_reference_page_joiner WHERE reference_id=' . $id;
		$result = $db->Execute($sql);
		if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
		
		$sql = 'DELETE FROM rdmp_author_reference_joiner WHERE reference_id = ' . $id;
		$result = $db->Execute($sql);
		if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

		// update (updated timestamp will be automatically updated)
		
		$sql = 'UPDATE rdmp_reference SET ';
		
		$num_values = count($keys);
		for ($i = 0; $i < $num_values; $i++)
		{
			if ($i > 0)
			{
				$sql .= ', ';
			}
			$sql .= $keys[$i] . '=' . $values[$i];
		}
		$sql .= ' WHERE reference_id=' . $id;
		
		$cache_file = @fopen('/tmp/update.sql', "w+") or die("could't open file");
		@fwrite($cache_file, $sql);
		fclose($cache_file);

		$result = $db->Execute($sql);
		if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
		
	}
	else
	{
		// Versioning?
	
		// Adding article for first time so add 'created' and 'updated' timestamp 
		$keys[] = 'created';
		$values[] = 'NOW()';
		$keys[] = 'updated';
		$values[] = 'NOW()';
	
		$sql = 'INSERT INTO rdmp_reference (' . implode (",", $keys) . ') VALUES (' . implode (",", $values) . ')';
	
		$result = $db->Execute($sql);
		if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

		$id = $db->Insert_ID();
	}
	
	// Store this object in version table so we can recover it if we overwrite item
	$ip = getip();
	$sql = 'INSERT INTO rdmp_reference_version(reference_id, ip, json) VALUES('
		. $id 
		. ', ' .  'INET_ATON(\'' . $ip . '\')'
		. ',' . $db->qstr(json_encode($article)) . ')';
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
		
	// Store author as and link to the article
	if (isset($article->authors))
	{
		db_store_authors($id, $article->authors);
	}

	// Store page range
	if ($PageID != 0)
	{
		$page_range = array();
		if (isset($article->spage) && isset($article->epage))
		{
			$page_range = 
				bhl_page_range($PageID, $article->epage - $article->spage + 1);
		}
		else
		{
			// No epage, so just get spage (to do: how do we tell user we don't have page range?)
			$page_range = 
				bhl_page_range($PageID, 0);				
		}
		$count = 0;
		foreach ($page_range as $page)
		{
			$sql = 'INSERT INTO rdmp_reference_page_joiner (reference_id, PageID, page_order) 
			VALUES (' . $id . ',' . $page . ',' . $count++ . ')';
			$result = $db->Execute($sql);
			if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
		}		
	}	
	
	return $id;
}




//--------------------------------------------------------------------------------------------------
function db_retrieve_reference($id)
{
	global $db;
	global $ADODB_FETCH_MODE;
	
	$article = NULL;
	
	$sql = 'SELECT * FROM rdmp_reference WHERE (reference_id = ' . $id . ') 
		LIMIT 1';
	
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);


	if ($result->NumRows() == 1)
	{
		$article = new stdClass;
	
		// Populate object
		foreach ($result->fields as $k => $v)
		{
			switch ($k)
			{
					
				default:
					if ($v != '')
					{
						$article->$k = $v;
					}
			}
		}
		
		// Authors
		$article->authors = array();
		
		$sql = 'SELECT author_id, lastname, forename, suffix FROM rdmp_author 
			INNER JOIN rdmp_author_reference_joiner USING(author_id)
			WHERE (rdmp_author_reference_joiner.reference_id = ' . $id . ')
			ORDER BY rdmp_author_reference_joiner.author_order';
			
		$result = $db->Execute($sql);
		if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
		
		while (!$result->EOF) 
		{
			$author = new stdClass;
			$author->id = $result->fields['author_id'];
			$author->lastname = $result->fields['lastname'];
			$author->forename = $result->fields['forename'];
			
			if ($result->fields['suffix'] != '')
			{
				$author->suffix = $result->fields['suffix'];
			}
			array_push($article->authors, $author);
			$result->MoveNext();				
		}
	}

	return $article;
}

//--------------------------------------------------------------------------------------------------
// return all localities
function db_retrieve_localities()
{
	global $db;
	
	$pts = array();
	
	$sql = 'SELECT latitude, longitude, woeid, name FROM rdmp_locality';

	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

	while (!$result->EOF) 
	{
		$pt = new stdclass;
		$pt->latitude = $result->fields['latitude'];
		$pt->longitude = $result->fields['longitude'];
		$pt->woeid = $result->fields['woeid'];
		$pt->name = $result->fields['name'];
		$pts[] = $pt;
		$result->MoveNext();
	}
	
	return $pts;

}

?>