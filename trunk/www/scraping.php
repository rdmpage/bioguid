<?php

/**
 * @file scrapping.php HTML scraping routines
 *
 */

require_once('nameparse.php');
require_once('ris.php');


//--------------------------------------------------------------------------------------------------
/**
 * @brief Parse Dubin Core metadata embedded in <meta> tags
 *
 * @param dc array of tag attributes
 * @param item Object we are populating
 *
 */
function parseDcMeta($dc, &$item)
{
	// The content may be at position 4 or 5, depending on
	// whether the tags include a schema definition or not. In any
	// event this will be the last position in the array
	$index = count($dc) - 1;
		
	$authors = array();

	foreach($dc[1] as $k => $v)
	{
		// make case insensitive (not everybody follows the rules)
		$v = strtolower($v);
	
		switch ($v)
		{
			case 'dcterms.ispartof':
				$issn = trim($dc[$index][$k]);			
				$issn = str_replace('urn:ISSN:','',$issn);
				$item->issn = $issn;
				break;
	
			case 'dc.title':
				$item->atitle = trim($dc[$index][$k]);
				break;

			case 'dc.identifier':
				$id = trim($dc[$index][$k]);
				if (preg_match('/info:doi\//', $id))
				{
					$id = str_replace('info:doi/', '', $id);
					$item->doi = urldecode($id);
				}
				if ($dc[3][$k] == 'doi') // check scheme
				{
					$item->doi = urldecode($id);
				}
				break;

			case 'dc.creator':
				$author = trim($dc[$index][$k]);
				$author = preg_replace('/\[[1-9]\]/', '', $author);	
				array_push($authors, $author);
				break;

			case 'dc.description':
				$abstract = trim($dc[$index][$k]);
				$item->abstract = $abstract;
				break;

			/* JSTOR buggers the date
			case 'dc.date':
				$date = trim($dc[$index][$k]);
				$item->date = $date;
				$match=array();
				if (preg_match('/([0-9]{4})(\/[0-9]{2}\/[0-9]{2})?/', $date, $match))
				{
					$year = $match[1];
					$item->year = $year;
				}
				break;
			*/
				
			default:
				break;
		}
	}

	// Clean up authors
	$item->authors = array();
	$authors = array_unique($authors);
	
	foreach ($authors as $a)
	{
	
		$a = mb_convert_case($a, 
			MB_CASE_TITLE, mb_detect_encoding($a));
	
		// Get parts of name
		$parts = parse_name($a);
		
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


//--------------------------------------------------------------------------------------------------
/**
 * @brief Extract metadata from RIS format file
 *
 * @param ris RIS text
 * @param item Object we are populating
 *
 */
function parseRIS($ris, &$item)
{
	$lines = split("\n", $ris);
	
	$item->comment='RIS';
	
/*	echo "<pre>";
	print_r($lines);
	echo "</pre>"; */
	
	foreach ($lines as $k => $v)
	{
		if (preg_match('/^[A-Z]{1}([A-Z]|[0-9])  \- /', $v))
		{
			list($key, $value) = split ('  - ', $v);
			
			// Clean
			$value = str_replace('\\r', '', $value);
			
			process_ris_key($key, $value, $item);
		}
	}
}



//--------------------------------------------------------------------------------------------------
/**
 * @brief Extract metadata from COInS format file
 *
 * @param coins Array of coins tags
 * @param item Object we are populating
 *
 */
function parseCoins($coins, &$item)
{
	$item->comment = 'CoinS';
	
	$author = new stdClass;
	
	
	foreach($coins[1] as $k => $v)
	{
		//echo $k, ' ', $coins[4][$k], " $v <br/>";
		
		switch ($v)
		{
			case 'rft.issn':
			case 'rft_issn':
				$issn = $coins[4][$k];			
				$item->issn = $issn;
				break;

			case 'rft.atitle':
			case 'rft_atitle':
				$value = $coins[4][$k];	
								
				// clean
				$value = str_replace("[ratio]", ":", $value);
				
				$item->atitle = $value;
				break;

			case 'rft.jtitle':
			case 'rft_jtitle':
				$value = $coins[4][$k];			
				$item->title = $value;
				break;

			case 'rft.date':
			case 'rft_date':
				$value = $coins[4][$k];			
				$item->date = $value;
				
				if (-1 != strtotime($item->date))
				{
					$item->year = date("Y", strtotime($item->date));
				}	
				
				break;

			case 'rft.aulast':
			case 'rft_aulast':
				$value = $coins[4][$k];			
				$value = mb_convert_case($value, 
					MB_CASE_TITLE, mb_detect_encoding($value));
				
				$item->aulast = $value;
				
				$author->lastname = $value;
				$author->forename = '';				
				break;

			case 'rft.aufirst':
			case 'rft_aufirst':
				$value = $coins[4][$k];		
				$value = mb_convert_case($value, 
					MB_CASE_TITLE, mb_detect_encoding($value));
				
				$item->aufirst = $value;
				$author->forename = $value;
				array_push($item->authors, $author);
				break;
				
			case 'rft.au':
			case 'rft_au':
				// Get parts of name
				$author = new stdClass;
				$author->lastname = '';
				$author->forename = '';

				$value = $coins[4][$k];		
				
				
				$value = mb_convert_case($value, 
					MB_CASE_TITLE, mb_detect_encoding($value));
				
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
				
				//print_r($author);
				
				array_push($item->authors, $author);
			
			
				break;


			case 'rft_id':
			case 'rft.id':
				$id = $coins[4][$k];
				if (preg_match('/info:doi/', $id))
				{
					$id = str_replace("info:doi/", "", $id);
					$id = str_replace("doi:", "", $id);
					
					if ('' != $id)
					{
						$item->doi= urldecode($id);
					}
				}
				if (preg_match('/^http:/', $id))
				{
					
					if ('' != $id)
					{
						$item->url= $id;
					}
				}
				break;



			default:
				break;
		}
	}
	
	//print_r($item);

}


?>