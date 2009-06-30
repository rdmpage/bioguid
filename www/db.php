<?php

require_once 'config.inc.php';
require_once($config['adodb_dir']);

$db = NewADOConnection('mysql');
$db->Connect("localhost", 
	$config['db_user'] , $config['db_passwd'] , $config['db_name']);

// Ensure fields are (only) indexed by column name
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;


//--------------------------------------------------------------------------------------------------
function retrieve_from_db($id)
{
	global $db;
	global $ADODB_FETCH_MODE;
	
	$item = new stdClass;
	
	$sql = 'SELECT * FROM article_cache WHERE (id = ' . $id . ') 
		AND (created <= NOW())
		AND (modified > NOW())
		LIMIT 1';
	
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);

	if ($result->NumRows() == 1)
	{
		$hard = $result->fields['hard'];
	
	
		// Populate object
		foreach ($result->fields as $k => $v)
		{
			switch ($k)
			{
				case 'id':
				case 'hard':
					break;
					
				case 'epage':
					// Ignore 0 pages, and only return if upper boundary is hard
					if ($v != 0)
					{
						if ($hard != 0)
						{
							$item->epage = $v;
						}
					}
					break;
					
				default:
					if ($v != '')
					{
						$item->$k = $v;
					}
			}
		}
		
		// Authors
		$item->authors = array();
		
		$sql = 'SELECT lastname, forename, suffix FROM author 
			INNER JOIN author_reference_joiner USING(author_id)
			WHERE (author_reference_joiner.reference_id = ' . $id . ')
			AND (author_reference_joiner.created <= NOW())
			AND (author_reference_joiner.modified > NOW())
			ORDER BY author_reference_joiner.author_order';
			
		$result = $db->Execute($sql);
		if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
		
		while (!$result->EOF) 
		{
			$author = new stdClass;
			$author->lastname = $result->fields['lastname'];
			$author->forename = $result->fields['forename'];
			
			if ($result->fields['suffix'] != '')
			{
				$author->suffix = $result->fields['suffix'];
			}
			array_push($item->authors, $author);
			$result->MoveNext();				
		}
	}
	//print_r($item);

	return $item;

}
//--------------------------------------------------------------------------------------------------
function find_in_cache($item, $include_issue = false)
{
	global $db;

	$id = 0;
	
	// Does a reference exist?
	
	// Do we have a version of this in the cache?
	$sql = 'SELECT * FROM article_cache
		WHERE (issn = ' .  $db->Quote($item->issn) . ')
		AND (volume = ' .  $db->Quote($item->volume) . ')
		AND (spage = ' .  $db->Quote($item->spage) . ')';
		
	if ($include_issue)
	{
		if (isset($item->issue))
		{
			if ($item->issue != '')
			{
				$sql .= ' AND (issue = ' .  $db->Quote($item->issue) . ') ';
			}
		}
	}
		
	$sql .= 'AND (created <= NOW())
		AND (modified > NOW())
		LIMIT 1';

	//echo $sql;
				
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
	
	if ($result->NumRows() == 1)
	{
		$id = $result->fields['id'];
	}
	
	return $id;
}

//--------------------------------------------------------------------------------------------------
function find_in_cache_from_guid($namespace, $identifier)
{
	global $db;

	$id = 0;
	
	$ident = $identifier;
	if ($namespace == 'url')
	{
		if (preg_match('/^http:/', $ident))
		{
		}
		else
		{
			$ident = 'http://' . $ident;
		}
	}
	
	// Do we have an article with this GUID in the cache
	$sql = 'SELECT * FROM article_cache
		WHERE (' . $namespace . ' = ' .  $db->Quote($ident) . ')
		AND (created <= NOW())
		AND (modified > NOW())		
		LIMIT 1';

	//echo $sql;
				
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
	
	if ($result->NumRows() == 1)
	{
		$id = $result->fields['id'];
	}
	
	return $id;
}


//--------------------------------------------------------------------------------------------------

// If we've had to search for an article based on a page within the page range, we may have a
// new upper bound, based on the search page.
function update_page_upperbound($id, $upper_bound)
{
	global $db;

	// Get current upperbound (epage) for article

	// Do we have a version of this in the cache?
	$sql = 'SELECT epage, hard FROM article_cache
		WHERE (id = ' .  $id . ') 
		AND (created <= NOW())
		AND (modified > NOW())		
		LIMIT 1';

	//echo $sql;
				
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
	
	if ($result->NumRows() == 1)
	{
		if ($result->fields['hard'] == 0)
		{
			// We don't have a hard upper boundary
			$epage = $result->fields['epage'];
			if ($epage == '')
			{
				$epage = 0;
			}
			
			if ($epage < $upper_bound)
			{
				$epage = $upper_bound;
				
				// to do: ensure we update the proper version!
				$sql = 'UPDATE  article_cache SET epage=' . $db->Quote($upper_bound) . '
								WHERE (id = ' .  $id . ')';
				$result = $db->Execute($sql);
				if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
			}
		}
	}
}

//--------------------------------------------------------------------------------------------------
function find_in_cache_from_page($item)
{
	global $db;

	$id = 0;
	
	// Does a reference exist?
	
	// Do we have a version of this in the cache?
	$sql = 'SELECT * FROM article_cache
		WHERE (issn = ' .  $db->Quote($item->issn) . ')
		AND (volume = ' .  $db->Quote($item->volume) . ')';
	
	// If page is a number don't quote it, otherwise we will
	// be comparing strings. For example, as numbers 24 is not in the range (233-246), 
	// but as a string it is (in lexical order the strings are 233,24,246)
	if (is_numeric($item->pages))
	{
		$sql .= 'AND (' . $item->pages . ' BETWEEN spage and epage)';
	}
	else
	{
		$sql .= '	AND (' . $db->Quote($item->pages) . ' BETWEEN spage and epage)';
	}
	$sql .= '
		AND (created <= NOW())
		AND (modified > NOW())		
		LIMIT 1';

	//echo $sql;
				
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
	
	if ($result->NumRows() == 1)
	{
		$id = $result->fields['id'];
	}
	
	return $id;
}

//------------------------------------------------------------------------------
function find_author($author)
{
	global $db;
	
	$id = 0;
	
	//print_r($author);
	
	// Clean name
	$author->forename = html_entity_decode($author->forename, ENT_QUOTES, "utf-8" ); 
	$author->lastname = html_entity_decode($author->lastname, ENT_QUOTES, "utf-8" ); 
	
/*	// If author forename comprises more than one capital letter with no spaces, then its is
	// is probably a series of initials, and we space them out. If we don't so this,
	// the title case code below will interpret the string as a name.
	if (preg_match('/^[A-Z][A-Z]+$/', $author->forename))
	{
		if (strlen($author->forename) < 2)
		{
			$tmp = '';
			for ($i = 0; $i < strlen($author->forename); $i++)
			{
				$tmp .= $author->forename[$i] . ' ';
			}
			$author->forename = trim($tmp);
		}
	}	*/
	
	// Make nice (in most cases will already be nice
	$author->forename = mb_convert_case($author->forename, 
		MB_CASE_TITLE, mb_detect_encoding($author->forename));
	$author->lastname = mb_convert_case($author->lastname, 
		MB_CASE_TITLE, mb_detect_encoding($author->lastname));
		
	// Remove '.' from forename
	
	// Replace . with space (in case . is the only separator between initials
	$author->forename = str_replace('.', ' ', $author->forename);
	// Compress extra blank space to a single space
	$author->forename = preg_replace('/\s+/', ' ', $author->forename);
	$author->forename = preg_replace('/ \-/', '-', $author->forename);
	// Trim trailing space
	$author->forename = trim($author->forename);
	
	// CrossRef metadata fuck ups
	if (preg_match('/jr$/i', $author->lastname) and !isset($author->suffix))
	{
		$author->lastname = preg_replace('/jr$/i', '', $author->lastname);
		$author->suffix = 'Jr';
	}
		
	//print_r($author);
	
	// to do: more sophisticated matching
	
	// For now we work on exact matches, could improve this	
	$sql = 'SELECT author_id FROM author 
		WHERE (lastname=' . $db->qstr($author->lastname) . ')
		AND (forename=' . $db->qstr($author->forename) . ') 
		AND (created <= NOW())
		AND (modified > NOW())		
		LIMIT 1';
	
		
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);

	if ($result->RecordCount() == 0)
	{
		// Not found so add
		$insert_sql  = 'INSERT INTO author (lastname, forename ';
		
		if (isset($author->suffix))
		{
			$insert_sql .= ', suffix';
		}
		
		
		$insert_sql .= ') VALUES (' . $db->qstr($author->lastname) 
			. ', ' . $db->qstr($author->forename);
			
		if (isset($author->suffix))
		{
			$insert_sql .= ', ' . $db->qstr($author->suffix);
		}
			
		$insert_sql .= ');';

		$result = $db->Execute($insert_sql);
		if ($result == false) die("failed [" . __LINE__ . "]: " . $insert_sql);
		
		$id = $db->Insert_ID();
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
function store_authors($id, $authors)
{
	global $db;

	$id_list = array();
	
	foreach ($authors as $author)
	{
		$author_id = find_author($author);
		array_push ($id_list, $author_id);
	}
			
	// Link to reference
	$count = count($id_list);
	for ($i = 0; $i < $count; $i++) 
	{
		$sql = 'INSERT INTO author_reference_joiner (author_id, reference_id, author_order) 
		VALUES (' . $id_list[$i] . ',' . $id . ',' . ($i + 1) . ')';
		$result = $db->Execute($sql);
		if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);

	}
}

//--------------------------------------------------------------------------------------------------
// Update the value of a single attrribute for a single item in the article cache
function update_article_attribute($id, $attribute_name, $attribute_value)
{
	global $db;

	$sql = 'UPDATE article_cache SET ' 	. $attribute_name . '=' . $db->qstr($attribute_value)
		. ' WHERE (id=' . $id . ')';
		
	//echo $sql;
	
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
}		



//--------------------------------------------------------------------------------------------------
function store_in_cache($item)
{
	global $db;

	$sql = 'INSERT INTO article_cache(';
	$columns = '';
	$values = ') VALUES (';
	
	$first = true;	
	
	// ISSN
	if (isset($item->issn))
	{
		if (!$first) { $columns .= ','; }
		if (!$first) { $values .= ','; }
		if ($first) { $first = false; }

		//	Format ISSN	
		$clean = ISN_clean($item->issn);
		$class = ISSN_classifier($clean);
		if ($class == "checksumOK")
		{
			$columns .= 'issn';
			$values .= $db->qstr(canonical_ISSN($item->issn));
		}	
		$first = false;
	}
	
	// eISSN
	if (isset($item->eissn))
	{
		if (!$first) { $columns .= ','; }
		if (!$first) { $values .= ','; }
		if ($first) { $first = false; }

		//	Format ISSN	
		$clean = ISN_clean($item->eissn);
		$class = ISSN_classifier($clean);
		if ($class == "checksumOK")
		{
			$columns .= 'eissn';
			$values .= $db->qstr(canonical_ISSN($item->eissn));
		}	
		$first = false;
	}


	// Volume
	if (isset($item->volume))
	{
		if (!$first) { $columns .= ','; }
		if (!$first) { $values .= ','; }
		if ($first) { $first = false; }
		
		$columns .= 'volume';
		$values .= $db->qstr($item->volume);		
	}
	
	// Issue
	if (isset($item->issue))
	{
		if (!$first) { $columns .= ','; }
		if (!$first) { $values .= ','; }
		if ($first) { $first = false; }
		
		$columns .= 'issue';
		$values .= $db->qstr($item->issue);		
	}
	
	// Spage
	if (isset($item->spage))
	{
		if (!$first) { $columns .= ','; }
		if (!$first) { $values .= ','; }
		if ($first) { $first = false; }
		
		$columns .= 'spage';
		$values .= $db->qstr($item->spage);		
	}
	
	// EPage
	if (isset($item->epage))
	{
		if (!$first) { $columns .= ','; }
		if (!$first) { $values .= ','; }
		if ($first) { $first = false; }
		
		$columns .= 'epage';
		$values .= $db->qstr($item->epage);		


		$columns .= ',hard';
		$values .= ',1';		
	}

	// Year
	if (isset($item->year))
	{
		if (!$first) { $columns .= ','; }
		if (!$first) { $values .= ','; }
		if ($first) { $first = false; }
		
		$columns .= 'year';
		$values .= $db->qstr($item->year);		
	}

	// Date
	if (isset($item->date))
	{
		if (!$first) { $columns .= ','; }
		if (!$first) { $values .= ','; }
		if ($first) { $first = false; }
		
		$columns .= 'date';
		$values .= $db->qstr($item->date);		
	}
	
	// Article title
	if (isset($item->atitle))
	{
		if (!$first) { $columns .= ','; }
		if (!$first) { $values .= ','; }
		if ($first) { $first = false; }
		
		// Clean up and make UTF-8
		$atitle = html_entity_decode($atitle, ENT_QUOTES, "utf-8" ); 
		$atitle = strip_tags($item->atitle);

		$columns .= 'atitle';
		$values .= $db->qstr(trim($atitle));		
	}

	// Journal title
	if (isset($item->title))
	{
		if (!$first) { $columns .= ','; }
		if (!$first) { $values .= ','; }
		if ($first) { $first = false; }
		
		$columns .= 'title';
		$values .= $db->qstr(trim($item->title));		
	}
	
	// URL
	if (isset($item->url))
	{
		if (!$first) { $columns .= ','; }
		if (!$first) { $values .= ','; }
		if ($first) { $first = false; }
		
		$columns .= 'url';
		$values .= $db->qstr($item->url);		
	}
	
	// doi
	if (isset($item->doi))
	{
		if (!$first) { $columns .= ','; }
		if (!$first) { $values .= ','; }
		if ($first) { $first = false; }
		
		$columns .= 'doi';
		$values .= $db->qstr($item->doi);		
	}

	// handle
	if (isset($item->hdl))
	{
		if (!$first) { $columns .= ','; }
		if (!$first) { $values .= ','; }
		if ($first) { $first = false; }
		
		$columns .= 'hdl';
		$values .= $db->qstr($item->hdl);		
	}

	// sici
	if (isset($item->sici))
	{
		if (!$first) { $columns .= ','; }
		if (!$first) { $values .= ','; }
		if ($first) { $first = false; }
		
		$columns .= 'sici';
		$values .= $db->qstr($item->sici);		
	}


	// pmid
	if (isset($item->pmid))
	{
		if (!$first) { $columns .= ','; }
		if (!$first) { $values .= ','; }
		if ($first) { $first = false; }
		
		$columns .= 'pmid';
		$values .= $db->qstr($item->pmid);		
	}


	// pdf
	if (isset($item->pdf))
	{
		if (!$first) { $columns .= ','; }
		if (!$first) { $values .= ','; }
		if ($first) { $first = false; }
		
		$columns .= 'pdf';
		$values .= $db->qstr($item->pdf);		
	}

	// publisher id, such as OAI urn
	if (isset($item->publisher_id))
	{
		if (!$first) { $columns .= ','; }
		if (!$first) { $values .= ','; }
		if ($first) { $first = false; }
		
		$columns .= 'publisher_id';
		$values .= $db->qstr($item->publisher_id);		
	}

	// XML url (e.g., from Scielo
	if (isset($item->xml_url))
	{
		if (!$first) { $columns .= ','; }
		if (!$first) { $values .= ','; }
		if ($first) { $first = false; }
		
		$columns .= 'xml_url';
		$values .= $db->qstr($item->xml_url);		
	}


	// abstract
	if (isset($item->abstract))
	{
		if (!$first) { $columns .= ','; }
		if (!$first) { $values .= ','; }
		if ($first) { $first = false; }

		// Clean up and make UTF-8
		$abstract = html_entity_decode($abstract, ENT_QUOTES, "utf-8" ); 
		$abstract = strip_tags($item->abstract);
		
		$columns .= 'abstract';
		$values .= $db->qstr($abstract);		
	}
	
	// Set open_access flag to 1 if item is Open access
	// availability
	if (isset($item->availability))
	{
		if ($item->availability == 'Open access')
		{
			if (!$first) { $columns .= ','; }
			if (!$first) { $values .= ','; }
			if ($first) { $first = false; }
		
			$columns .= 'open_access';
			$values .= $db->qstr('Y');
		}
	}
	
	
	$sql .= $columns . $values . ');';
	
	//echo $sql;
	
	// Store
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
	
	$id = $db->Insert_ID();
	
	// Authors (don't actually need them, but seems a shame to through this information away)
	if (isset($item->authors))
	{
		store_authors($id, $item->authors);
	}

	return $id;
}

//--------------------------------------------------------------------------------------------------
$key_map = array(
	'T1' => 'atitle',
	'TI' => 'atitle',
	'SN' => 'issn',
	'JO' => 'title',
	'JF' => 'title',
	'VL' => 'volume',
	'IS' => 'issue',
	'SP' => 'spage',
	'EP' => 'epage',
	'N2' => 'abstract',
	'UR' => 'url',
	'AV' => 'availability',
	'L1' => 'pdf', 
	'L2' => 'fulltext' // check this, we want to have a link to the PDF...
	);


//--------------------------------------------------------------------------------------------------
function export_ris($id)
{
	$item = retrieve_from_db($id);
	
	$ris = "TY  - JOUR\n";
	$ris .= "TI  - " . $item->atitle . "\n";
	
	foreach ($item->authors as $a)
	{
		$ris .= 'AU  - ';
		$ris .= $a->lastname;
		$ris .= ", " . $a->forename;
		if (isset($a->suffix)) $ris .= ", " . $a->suffix;
		$ris .= "\n";
	}
	
	$ris .= "SN  - " . $item->issn . "\n";
	$ris .= "JF  - " . $item->title . "\n";
	$ris .= "VL  - " . $item->volume . "\n";
	$ris .= "IS  - " . $item->issue . "\n";
	$ris .= "SP  - " . $item->spage . "\n";
	if (isset($item->epage)) $ris .= "EP  - " . $item->epage . "\n";
	if (isset($item->year)) $ris .= "Y1  - " . $item->year . "\n";
	if (isset($item->date)) $ris .= "PY  - " . $item->date . "\n";
	if (isset($item->url)) $ris .= "UR  - " . $item->url . "\n";
	if (isset($item->pdf)) $ris .= "L1  - " . $item->pdf . "\n";
	if (isset($item->doi)) $ris .= "M3  - http://dx.doi.org/" . $item->doi . "\n";
	if (isset($item->abstract)) $ris .= "N2  - " . $item->abstract . "\n";
	$ris .= "ER  - \n\n";

	return $ris;

}

//--------------------------------------------------------------------------------------------------
// Dump all refs (or those with a given ISSN) to a RIS file
function export_all_ris ($risfilename, $issn = '')
{
	global $db;
	
	$risfile = @fopen($risfilename, "w+") or die("could't open file --\"$risfilename\"");
	
	// For now we work on exact matches, could improve this	
	$sql = 'SELECT id FROM article_cache
		WHERE (created <= NOW())
		AND (modified > NOW())';	
	
	if ($issn != '')
	{
		$sql .= ' AND (issn=' . $db->qstr($issn) . ')';
	}
	
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
	
	while (!$result->EOF) 
	{
		@fwrite($risfile, export_ris($result->fields['id']) );	
		$result->MoveNext();				
	}

	fclose($risfile);
	
}

//--------------------------------------------------------------------------------------------------
function find_specimen($institutionCode, $collectionCode, $catalogNumber)
{
	global $db;
	
	$id = 0;
	
	$sql = 'SELECT * FROM darwin_core
	WHERE (institutionCode = ' . $db->qstr($institutionCode) . ')
	AND (collectionCode = ' . $db->qstr($collectionCode) . ')
	AND (catalogNumber = ' . $db->qstr($catalogNumber) . ') LIMIT 1';
	
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $db->ErrorMsg() . ' ' . $sql );

	if ($result->NumRows() == 1)
	{
		$id = $result->fields['id'];
	}
	
	return $id;
		
}

//--------------------------------------------------------------------------------------------------
function retrieve_specimen_json($id)
{
	global $db;
	$json = '';
	
	$sql = 'SELECT json FROM darwin_core
	WHERE (id = ' . $db->qstr($id) . ') LIMIT 1';
	
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $db->ErrorMsg() . ' ' . $sql );
	if ($result->NumRows() == 1)
	{
		$json = $result->fields['json'];
	}
	
	return $json;
}

//--------------------------------------------------------------------------------------------------
function store_specimen($d)
{
	global $db;
	
	$sql = 'INSERT INTO darwin_core(';
	$fields = '';
	$values = '';
	$count = 0;

	
	// Generate SQL
	foreach ($d as $k => $v)
	{
		if ($k != 'namebankID')
		{
			if ($count > 0)
			{
				$fields .= ',';
				$values .= ',';
			}
	
			$fields .= "`" . $k . "`";
			
			if ($k == 'loc')
			{
				$values .= $v;
			}
			else
			{
				$values .= $db->qstr($v);
			}
			$count++;
		}
	}
	
	
	// cache
	$fields .= ',json';
	$values .= ',' . $db->qstr(json_encode($d));
	
	$sql .= $fields . ') VALUES (' . $values . ');';
	
	//echo $sql;
		
	// Store
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $db->ErrorMsg() . ' ' . $sql );
	
	$id = $db->Insert_ID();
	
	// join specimen to uBio
	for ($i = 0; $i < count($d->namebankID); $i++) 
	{
		$sql = 'INSERT INTO darwin_core_ubio_joiner (darwin_core_id, namebankID) 
		VALUES (' . $id . ',' . $d->namebankID[$i] .  ')';
		$result = $db->Execute($sql);
		if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
	}
	
	
	
	// Authors (don't actually need them, but seems a shame to through this information away)
//	store_authors($id, $item->authors);

	return $id;
}


//--------------------------------------------------------------------------------------------------
function find_ubio_name_in_cache($name, $has_authority=false, $exact=false)
{
	global $db;
	
	$namebankID = array();

	$sql = 'SELECT * FROM ubio_cache WHERE ';
	if($has_authority || $exact)
	{
		$sql .= 'fullNameString = ' . $db->qstr($name);
	}
	else
	{
		$sql .= 'nameString = ' . $db->qstr($name);
	}

	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
	
	while (!$result->EOF) 
	{
		array_push($namebankID, $result->fields['namebankID']);
		$result->MoveNext();				
	}
	
	return $namebankID;
}

//--------------------------------------------------------------------------------------------------
function store_ubio_name($r)
{
	global $db;
	
	# check whe haven't already stored this, as search may return names we've already encountered
	$sql = 'SELECT * from ubio_cache WHERE namebankID=' . $r['namebankID'];
	
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);

	if ($result->NumRows() == 0)
	{
	
		$sql = 'INSERT INTO ubio_cache'
			.'(namebankID,nameString,fullNameString,packageID,packageName,basionymUnit,rankID,rankName) '
			. 'VALUES ('
			. $r['namebankID']
			. ',' . $db->qstr(base64_decode($r['nameString']))
			. ',' . $db->qstr(base64_decode($r['fullNameString']))
			. ',' . $r['packageID']
			. ',' . $db->qstr($r['packageName'])
			. ',' . $r['basionymUnit']
			. ',' . $r['rankID']
			. ',' . $db->qstr($r['rankName'])
			.')';
			
		$result = $db->Execute($sql);
		if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
	}
}


//--------------------------------------------------------------------------------------------------
function find_genbank($acc)
{
	global $db;
	
	$id = 0;
	
	if (is_numeric($acc))
	{
		$sql = 'SELECT * FROM genbank
		WHERE (gi = ' . $db->qstr($acc) . ') LIMIT 1';
	}
	else
	{
		$sql = 'SELECT * FROM genbank
		WHERE (accession = ' . $db->qstr($acc) . ') LIMIT 1';
	}
	
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $db->ErrorMsg() . ' ' . $sql );

	if ($result->NumRows() == 1)
	{
		$id = $result->fields['id'];
	}
	
	return $id;
		
}


//--------------------------------------------------------------------------------------------------
	// We have a sequence with this accession but the GI hasn't been set
		// for example if we've been harvesting EMBL flat files

function set_gi($acc, $gi)
{
	global $db;
	
	$id = 0;
	
		
	$sql = 'SELECT * FROM genbank
	WHERE (accession = ' . $db->qstr($acc) . ') LIMIT 1';
	
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $db->ErrorMsg() . ' ' . $sql );

	if ($result->NumRows() == 1)
	{
		$id = $result->fields['id'];	
			
		$sql = 'UPDATE genbank SET gi=' . $gi . ' WHERE id=' . $id;
		$res = $db->Execute($sql);
		if ($res == false) die("failed [" . __LINE__ . "]: " . $db->ErrorMsg() . ' ' . $sql );
	}
	
	return $id;
}


//--------------------------------------------------------------------------------------------------
function retrieve_genbank_json($id)
{
	global $db;
	$json = '';
	
	$sql = 'SELECT * FROM genbank
	WHERE (id = ' . $db->qstr($id) . ') LIMIT 1';
	
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $db->ErrorMsg() . ' ' . $sql );
	if ($result->NumRows() == 1)
	{
		$json = $result->fields['json'];
		
		
		// ensure JSON is up to date with any field we may have manually edited
		
		$data = json_decode($json);
		if (!isset($data->source->latitude))
		{
			$latitude = $result->fields['latitude'];
			$longitude = $result->fields['longitude'];
			
			if ($latitude != '')
			{
				$data->source->latitude = $latitude;
				$data->source->longitude = $longitude;
			}
		}
		
		if (!isset($data->source->specimen_code))
		{
			$specimen_code = $result->fields['specimen_code'];
			
			if ($specimen_code != '')
			{
				$data->source->specimen_code = $specimen_code;
			}
		}
		
		
		$json = json_encode($data);
		
	}
	
	return $json;
}


//--------------------------------------------------------------------------------------------------
function store_genbank($d)
{
	global $db;
	
	
	$sql = 'INSERT INTO genbank(';
	$fields = '';
	$values = '';


	$fields .= "accession";
	$values .= $db->qstr($d->accession);

	$fields .= ",gi";
	$values .= "," . $db->qstr($d->gi);
	
	// store those things that we are likely to make use of, or will want to debug later
	
	$fields .= ",organism";
	$values .= "," . $db->qstr($d->source->organism);

	$tx = $d->source->db_xref;
	$tx = str_replace("taxon:", "", $tx);
	$fields .= ",taxon";
	$values .= "," . $tx;
	
	if (isset($d->source->country))
	{
		$fields .= ",country";
		$values .= "," . $db->qstr($d->source->country);
	}
	if (isset($d->source->locality))
	{
		$fields .= ",locality";
		$values .= "," . $db->qstr($d->source->locality);
	}
	if (isset($d->source->specimen_voucher))
	{
		$fields .= ",specimen_voucher";
		$values .= "," . $db->qstr($d->source->specimen_voucher);
	}
	if (isset($d->source->isolate))
	{
		$fields .= ",isolate";
		$values .= "," . $db->qstr($d->source->isolate);
	}
	if (isset($d->source->specimen_code))
	{
		$fields .= ",specimen_code";
		$values .= "," . $db->qstr($d->source->specimen_code);
	}
	if (isset($d->source->host))
	{
		$fields .= ",host";
		$values .= "," . $db->qstr($d->source->host);
	}
	if (isset($d->source->host_namebankID))
	{
		$fields .= ",host_namebankID";
		$values .= "," . $db->qstr($d->source->host_namebankID);
	}
	if (isset($d->source->lat_lon))
	{
		$fields .= ",lat_lon";
		$values .= "," . $db->qstr($d->source->lat_lon);
	}
	
	
	if (isset($d->source->latitude))
	{
		$fields .= ",latitude";
		$values .= "," . $db->qstr($d->source->latitude);
	}
	if (isset($d->source->longitude))
	{
		$fields .= ",longitude";
		$values .= "," . $db->qstr($d->source->longitude);
	}

	// group
	if (isset($d->taxonomic_group))
	{
		$fields .= ",taxonomic_group";
		$values .= "," . $db->qstr($d->taxonomic_group);
	}

	if (isset($d->description))
	{
		$fields .= ",description";
		$values .= "," . $db->qstr($d->description);
	}
	
	
	// reference
	if (isset($d->references[0]->title))
	{
		$fields .= ",reference";
		$values .= "," . $db->qstr($d->references[0]->title);
	}
	if (isset($d->references[0]->doi))
	{
		$fields .= ",doi";
		$values .= "," . $db->qstr($d->references[0]->doi);
	}
	if (isset($d->references[0]->pmid))
	{
		$fields .= ",pmid";
		$values .= "," . $db->qstr($d->references[0]->pmid);
	}
	if (isset($d->references[0]->hdl))
	{
		$fields .= ",hdl";
		$values .= "," . $db->qstr($d->references[0]->hdl);
	}
	if (isset($d->references[0]->url))
	{
		$fields .= ",url";
		$values .= "," . $db->qstr($d->references[0]->url);
	}

	// sequence
	if (isset($d->sequence))
	{
		$fields .= ",sequence";
		$values .= "," . $db->qstr($d->sequence);
	}
	
	// dates
	if (isset($d->created))
	{
		$fields .= ",created";
		$values .= "," . $db->qstr($d->created);
	}
	if (isset($d->updated))
	{
		$fields .= ",updated";
		$values .= "," . $db->qstr($d->updated);
	}
	

	$fields .= ",json";
	$values .= "," . $db->qstr(json_encode($d));
	
	$sql .= $fields . ') VALUES (' . $values . ');';
	
	//echo $sql;
		
	// Store
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $db->ErrorMsg() . ' ' . $sql );
	
	$id = $db->Insert_ID();
	

	return $id;
}



// test

//export_all_ris('zoover.ris', '0024-1652');

//echo export_ris(7206);


?>