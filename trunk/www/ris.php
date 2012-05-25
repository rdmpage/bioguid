<?php

require_once('db.php');
require_once('crossref.php');
require_once('identifier.php');
require_once('issn-functions.php');
require_once('nameparse.php');

$debug = 0;

$key_map = array(
	'ID' => 'publisher_id',
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
	
function process_ris_key($key, $value, &$obj)
{
	global $key_map;
	global $debug;
	
	//echo "|$key|-$value\n";

	switch ($key)
	{
		case 'AU':
		case 'A1':					
			// Interpret author names
			
			//echo __LINE__ . " author\n";
			
			$value = trim($value);

			// Handle Highwire author initials such as JM (we want J M)			
			if (preg_match("/,\s*(?<initials>[A-Z]+)$/", $value, $m))
			{
				//print_r($m);
				
				$spaced = '';
				for($i=0;$i<strlen($m['initials']);$i++)
				{
					$spaced .= $m['initials']{$i} . ' ';
				}
				$value = trim(preg_replace("/,\s*" . $m['initials'] . "$/", ", " . $spaced, $value));
			}
			
			//echo __LINE__ . " $value\n";
			
			$p = explode(',', $value);
			//print_r($p);
			if (count($p) > 1)
			{
				$value = '';
				$n = count($p);
				for ($i = 1; $i < $n; $i++)
				{
					$value .= $p[$i] . ' ';
				}
				$value .= $p[0];
			}
			
			
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
				
			//echo __LINE__ . " $value\n";
				
														
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
				
/*				$author->forename = mb_convert_case($author->forename, 
					MB_CASE_TITLE, mb_detect_encoding($author->forename));
*/
				
				if (array_key_exists('middle', $parts))
				{
					$author->forename .= ' ' . $parts['middle'];
				}
			}
			
			
			if ($debug)
			{
				print_r($authors);
			}
			
			
			array_push($obj->authors, $author);
			break;	
			
		// Handle cases where both pages SP and EP are in this field
		case 'SP':
			if (preg_match('/^(?<spage>[0-9]+)[^\d]+(?<epage>[0-9]+)$/', trim($value), $matches))
			{
				$obj->spage = $matches['spage'];
				$obj->epage = $matches['epage'];
			}
			else
			{
				$obj->$key_map[$key] = $value;
			}				
			break;
			
			
		case 'Y1':
			$year = '';
			$date = $value;
			
			// Year might be in YYYY-MM-DD format (e.g., my Zootaxa export)
			if (preg_match("/[0-9]{4}\-[0-9]{1,2}\-[0-9]{1,2}/", $date))
			{			
				// Save the date (taxonomists care about this)
				$obj->date = $date;
				
				if (-1 != strtotime($date))
				{
					$year = date("Y", strtotime($date));
				}
			}
			if (preg_match("/[0-9]{4}\/[0-9]{1,2}\/[0-9]{1,2}/", $date))
			{			
				// Save the date (taxonomists care about this)
				$obj->date = $date;
				
				if (-1 != strtotime($date))
				{
					$year = date("Y", strtotime($date));
				}
			}
			
			// Year is YYYY
			if (preg_match("/^[0-9]{4}$/", $date))
			{
				$year = $date;
			}
			$obj->year = $year;
			break;
		
		case 'PY': // used by Ingenta, and others (incl. Zotero)
			$date = trim($value);	
			
			// Zotero
			if (preg_match("/^(?<year>[0-9]{4})\/(?<month>[0-9]{2})?\/(?<day>[0-9]{2})?\/$/", $date, $m))
			{
				//print_r($m);
				$formatted_date = '';
				$year = '';
				
				// Year
				if (isset($m['year']))
				{
					$obj->year = $m['year'];
					$formatted_date = $m['year'];
				}
				
				if (isset($m['month']))
				{
					$formatted_date .= '-' . $m['month'];
					if (isset($m['day']))
					{
						$formatted_date .= '-' . $m['day'];
					}
					else
					{
						$formatted_date .= '-00';
					}
					
					$obj->date = $formatted_date;
				}
								
				//print_r($obj);
			}
			
			if (preg_match("/^[0-9]{4}\-[0-9]{2}\-[0-9]{2}$/", $date))
			{
				$obj->date = $date;
			}

			if (preg_match("/^[0-9]{4}$/", $date))
			{
				$obj->year = $date;
			}
			break;
			
		case 'M3': // used by Ingenta for DOI
			$id = IdentifierKind($value);
			switch ($id['identifier_type'])
			{
				case IDENTIFIER_DOI:
					$obj->doi = $id['identifier_string'];
					break;
			}
			break;

		case 'JO': // Other name for journal, only use if JF not already set
			if (!isset($obj->$keyMap['JF']))
			{
				$obj->$key_map[$key] = $value;
			}
			break;


		case 'L3': // May be URL, may be identifier with proxy prefix
		case 'UR': // May be URL, may be identifier with proxy prefix
		case 'N1':
			// Extract any other identifiers
			$id = IdentifierKind(urldecode($value));
			
			//print_r($id);
			
			switch ($id['identifier_type'])
			{
				case IDENTIFIER_DOI:
					$obj->doi = $id['identifier_string'];
					break;

				case IDENTIFIER_HANDLE:
					$obj->hdl = $id['identifier_string'];								
					break;
					
				default:						
					if ('UR' == $key)
					{
						if (preg_match('/\.pdf/', $value))
						{
							$obj->$key_map['L1'] = $value;
						}
						else
						{
							$obj->$key_map[$key] = $value;
						}
					}
				
					break;
			}
			break;
			
		case 'L1':
			// ignore local URLs
			if (preg_match('/^http/', $value))
			{
				$obj->$key_map[$key] = $value;
			}
			break;
						
		case 'M1':
			if (preg_match('/^S/', $value))
			{
				// TreeBASE study id
				$obj->treebase->StudyID = $value;
			}
			break;
			
		default:
			if (array_key_exists($key, $key_map))
			{
				// Only set value if it is not empty
				if ($value != '')
				{
					$obj->$key_map[$key] = $value;
				}
			}
			break;
	}
}

/**
 * @brief Import bibliograpohic data from a RIS file
 *
 */
function import_ris($ris)
{
	global $debug;
	
	$rows = split("\n", $ris);
	
	if ($debug)
	{
		echo '<pre style="text-align: left;border: 1px solid #c7cfd5;background: #f1f5f9;padding:15px;">';
		print_r($rows);
		echo "</pre>";		
	}	
	
	$state = 1;	
	
	$genre = '';
		
	
	foreach ($rows as $r)
	{
		$parts = split ("  - ", $r);
		$key = '';
		if (isset($parts[1]))
		{
			$key = $parts[0];
			$value = trim($parts[1]); // clean up any leading and trailing spaces
		}
				
				
		if (isset($key) && ($key == 'TY'))
		{
			$state = 1;
			$obj = new stdClass();
			$obj->authors = array();
			
			if ('JOUR' == $value)
			{
				$genre = 'article';
			}
		}
		if (isset($key) && ($key == 'ER'))
		{
			$state = 0;
			
			if ($debug)
			{
				echo 'Line: ' . __LINE__ . "\n";
				echo "\n=== Import this object ==\n";
			}
			
			// ISSN lookup
			if (!isset($obj->issn) && 'article' == $genre)
			{
				$issn = issn_from_journal_title($obj->title);
				if ('' != $issn)
				{
					$obj->issn = $issn;
				}
			}
			
			// to do: we might want to do a DOI lookup here to get more GUIDs...
			
			if (!isset($obj->doi))
			{
				if (in_crossref($obj->issn, $obj->year, $obj->volume))
				{
					$item = new stdclass;
					$doi = search_for_doi($obj->issn, $obj->volume, $obj->spage, 'article', $item);
					if ($doi != '')
					{
						$obj->doi = $doi;
						
						// Fix missing metadata
						if (!isset($obj->epage) && isset($item->epage))
						{
							$obj->epage= $item->epage;
						}
					}
				}
			}
			
			// http://en.wikipedia.org/wiki/Chinese_name
			// For some journals (e.g., Chinese) we need to reverse the name parts returned
			// by parse_name
			
			//echo "boo1 |" . $obj->issn . "|\n";
			
			switch ($obj->issn)
			{
				case '0529-1526': // Acta Phytotaxonomica Sinica
					for ($i = 0; $i < count($obj->authors); $i++)
					{
						$tmp = $obj->authors[$i]->forename;
						$obj->authors[$i]->forename = $obj->authors[$i]->lastname;
						$obj->authors[$i]->lastname = $tmp;
					}
					break;
					
				default:
					break;
			}
			
			
			// Cleaning...						
			if ($debug)
			{
				echo '<pre style="text-align: left;border: 1px solid #c7cfd5;background: #f1f5f9;padding:15px;">';
				print_r($obj);
				echo "</pre>";		
			}	
			
			// Store reference here...
			if (find_in_cache($obj) == 0)
			{
			
				if (isset($obj->issn))
				{
					if ($obj->issn != '')
					{
						store_in_cache($obj);
					}
				}
				/* for Pac Sci
				if (isset($obj->volume))
				{
					if ($obj->volume != '')
					{
						store_in_cache($obj);
					}
				}
				*/
				
			}
			

		}
		
		if ($state == 1)
		{
			if (isset($value))
			{
				process_ris_key($key, $value, $obj);
			}
		}
	}
}



// test


if (0)
{
	echo '<pre>';
	$filename = 'naturalis.ris';

	
	echo $filename, "\n";
	
	$file = @fopen($filename, "r") or die("could't open file \"$filename\"");
	$ris = @fread($file, filesize ($filename));
	fclose($file);
	
	import_ris($ris);
	
	echo '</pre>';
	
}	
?>