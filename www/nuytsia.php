<?php

require_once (dirname(__FILE__) . '/db.php');
require_once (dirname(__FILE__) . '/issn-functions.php');
//require_once (dirname(__FILE__) . '/lib.php');
require_once (dirname(__FILE__) . '/nameparse.php');


function parse_nuytsia($url)
{

	$text = get($url);
	
	$text = str_replace("\n", "", $text);
	$text = str_replace("\r", "", $text);
	$text = str_replace("\t", "", $text);
	
	
	$paras = explode('</p>', $text);
	
	foreach ($paras as $pp)
	{
		$pp .= '</p>';
		
		
		if (preg_match('/class="article"/', $pp))
		{
			$item = new stdclass;
			$item->authors = array();
		
			if (preg_match('/<\/a>(?<authors>(.*))\((?<year>[0-9]{4})\)./', $pp, $match))
			{
				$item->year = $match['year'];
				
				$authors = $match['authors'];
				$authors = preg_replace('/(.*)<\/a>/', '', $authors);
				$authors = str_replace(' AND ', ', ', $authors);
				$a = explode('.,', trim($authors));
	
				foreach ($a as $value)
				{
					if ($value != '')
					{
						$value = trim($value);
						$value .= ".";
		
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
						array_push($item->authors, $author);
						
					}
					
				}
	
			}
	
			if (preg_match('/<a href="(?<pdf>(http:\/\/www.dec.wa.gov.au(.*)\.pdf))/', $pp, $match))
			{
				$item->pdf = $match['pdf'];
			}
	
			
			if (preg_match('/\([0-9]{4}\).(?<atitle>(.*))<i>Nuytsia<\/i>/', $pp, $match))
			{
				$item->atitle = strip_tags($match['atitle']);
			}
			
			
			// <i>Nuytsia</i> <u>19</u> (1) : 191–196
			// page separator is en dash 2013
			
			if (preg_match('/<i>(Nuytsia)<\/i> <u>(?<volume>(.*))<\/u>\s*\((?<issue>(.*))\)\s*:\s*(?<spage>[0-9]+)–(?<epage>(.*))\.<\/p>/', $pp, $match))
			{
				$item->title='Nuytsia';
				$item->volume = $match['volume'];
				$item->issue = $match['issue'];
				$item->spage = $match['spage'];
				$item->epage = $match['epage'];
				$item->issn = '0085-4417';
			}
			
			print_r($item);
			
			// Store reference here...
			if (find_in_cache($item) == 0)
			{
				store_in_cache($item);
			}
		}
	
	}
}

// test
if (0)
{
	$url = 'http://science.dec.wa.gov.au/nuytsia/search.php?authors=Rye&volume=19&part=1';

	parse_nuytsia($url);
}
?>