<?php

require_once('db.php');
require_once('identifier.php');
require_once('issn-functions.php');
require_once('nameparse.php');

$debug = 1;

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

	switch ($key)
	{
		case 'AU':
		case 'A1':					
			// Interpret author names
			
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
			
			
			print_r($authors);
			
			array_push($obj->authors, $author);
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
		
		case 'PY': // used by Ingenta, and others
			$date = $value;	
			
			if (preg_match("/\/\/\/[A-Za-z]*\s*/", $date))
			{			
				$date = preg_replace("/\/\/\//", "", $date);
				$formatted_date = '';
				$year = '';
				
				if (-1 != strtotime($date))
				{
					$formatted_date = date("Y-m-d", strtotime($date));
				}		
				if (-1 != strtotime($date))
				{
					$year = date("Y", strtotime($date));
				}	
				
				if ($formatted_date != '')
				{
					$obj->date = $formatted_date;
				}
				if ($year != '')
				{
					$obj->year = $year;
				}
			}
			
			
			if (preg_match("/^[0-9]{4}\-[0-9]{2}\-[0-9]{2}$/", $date))
			{
				$obj->date = $date;
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
			$value = urldecode($value);
			$id = IdentifierKind($value);
			
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
						$obj->$key_map[$key] = $value;
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
			
			echo 'Line: ' . __LINE__ . "\n";
			echo "\n=== Import this object ==\n";
			
			// to do: we might want to do a DOI lookup here to get more GUIDs...
			
			// ISSN lookup
			if (!isset($obj->issn) && 'article' == $genre)
			{
				$issn = issn_from_journal_title($obj->title);
				if ('' != $issn)
				{
					$obj->issn = $issn;
				}
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