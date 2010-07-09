<?php

/**
 * @file ris.php
 *
 */

// Parse RIS file and try and find first page of article in BHL

require_once (dirname(__FILE__) . '/nameparse.php');

$debug = false;

$logfile;

$key_map = array(
	'ID' => 'publisher_id',
	'T1' => 'title',
	'TI' => 'title',
	'SN' => 'issn',
	'JO' => 'secondary_title',
	'JF' => 'secondary_title',
	'VL' => 'volume',
	'IS' => 'issue',
	'SP' => 'spage',
	'EP' => 'epage',
	'N2' => 'abstract',
	'UR' => 'url',
	'AV' => 'availability',
	'Y1' => 'year',
	'L1' => 'pdf', 
	'L2' => 'fulltext' // check this, we want to have a link to the PDF...
	);
	
//--------------------------------------------------------------------------------------------------
function process_ris_key($key, $value, &$obj)
{
	global $key_map;
	global $debug;
	
	switch ($key)
	{
		case 'AU':
		case 'A1':					
			// Interpret author names
			
			// Trim trailing periods and other junk
			//$value = preg_replace("/\.$/", "", $value);
			$value = preg_replace("/&nbsp;$/", "", $value);
			$value = preg_replace("/,([^\s])/", ", $1", $value);
			
			// Handle case where initials aren't spaced
			$value = preg_replace("/, ([A-Z])([A-Z])$/", ", $1 $2", $value);

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
			break;	
	
		case 'JF':
			$value = mb_convert_case($value, 
				MB_CASE_TITLE, mb_detect_encoding($value));
				
			$value = preg_replace('/ Of /', ' of ', $value);	
			$value = preg_replace('/ the /', ' the ', $value);	
			$value = preg_replace('/ and /', ' and ', $value);	
			$obj->$key_map[$key] = $value;
			break;
			
		case 'T1':
			$value = preg_replace('/([^\s])\(/', '$1 (', $value);	
			$value = str_replace("\ü", "ü", $value);
			$value = str_replace("\ö", "ö", $value);

			$value = str_replace("“", "\"", $value);
			$value = str_replace("”", "\"", $value);
						
			$obj->$key_map[$key] = $value;
			break;
				
		// Handle cases where both pages SP and EP are in this field
		case 'SP':
			if (preg_match('/^(?<spage>[0-9]+)\s*[-|–|—]\s*(?<epage>[0-9]+)$/u', trim($value), $matches))
			{
				$obj->spage = $matches['spage'];
				$obj->epage = $matches['epage'];
			}
			else
			{
				$obj->$key_map[$key] = $value;
			}				
			break;

		case 'EP':
			if (preg_match('/^(?<spage>[0-9]+)\s*[-|–|—]\s*(?<epage>[0-9]+)$/u', trim($value), $matches))
			{
				$obj->spage = $matches['spage'];
				$obj->epage = $matches['epage'];
			}
			else
			{
				$obj->$key_map[$key] = $value;
			}				
			break;
			
		// DOI
		case 'M3':
			if (preg_match('/^10\./u', trim($value)))
			{
				$obj->doi = trim($value);
			}				
			break;			
			
		case 'PY': // used by Ingenta, and others
		case 'Y1':
		   $date = $value; 
		   
		   if (preg_match("/(?<year>[0-9]{4})\/(?<month>[0-9]{1,2})\/(?<day>[0-9]{1,2})/", $date, $matches))
		   {                       
			   $obj->year = $matches['year'];
			   $obj->date = sprintf("%d-%02d-%02d", $matches['year'], $matches['month'], $matches['day']);			   
		   }
		   

		   if (preg_match("/(?<year>[0-9]{4})\/(?<month>[0-9]{1,2})\/\//", $date, $matches))
		   {                       
				   $obj->year = $matches['year'];
		   }

		   if (preg_match("/[0-9]{4}\/\/\//", $date))
		   {                       
			   $year = trim(preg_replace("/\/\/\//", "", $date));
			   if ($year != '')
			   {
					   $obj->year = $year;
			   }
		   }

		   if (preg_match("/^[0-9]{4}$/", $date))
		   {                       
				  $obj->year = $date;
		   }
		   
		   
		   if (preg_match("/^(?<year>[0-9]{4})\-[0-9]{2}\-[0-9]{2}$/", $date, $matches))
		   {
		   		$obj->year = $matches['year'];
				  $obj->date = $date;
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



//--------------------------------------------------------------------------------------------------
function import_ris($ris, $callback_func = '')
{
	global $debug;
	
	$volumes = array();
	
	$rows = split("\n", $ris);
	
	$state = 1;	
		
	foreach ($rows as $r)
	{
		$parts = split ("  - ", $r);
		
		$key = '';
		if (isset($parts[1]))
		{
			$key = trim($parts[0]);
			$value = trim($parts[1]); // clean up any leading and trailing spaces
		}
				
		if (isset($key) && ($key == 'TY'))
		{
			$state = 1;
			$obj = new stdClass();
			$obj->authors = array();
			
			if ('JOUR' == $value)
			{
				$obj->genre = 'article';
			}
		}
		if (isset($key) && ($key == 'ER'))
		{
			$state = 0;
						
			// Cleaning...						
			if ($debug)
			{
				print_r($obj);
			}	
			
			if ($callback_func != '')
			{
				$callback_func($obj);
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


?>