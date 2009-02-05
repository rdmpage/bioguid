<?php

// $Id: $

/* CiNii */

require_once ('db.php');
require_once ('issn-functions.php');
require_once ('lib.php');

/*//--------------------------------------------------------------------------------------------------
// True if CrossRef is likely to have metadata for this article
function in_crossref($issn, $date = '', $volume = '')
{
	global $db;
	
	$found = false;
	
	$sql = 'SELECT * FROM crossref
		WHERE (issn = ' .  $db->Quote($issn) . ')';
		
	if ($date != '')
	{
		$sql .= ' AND (start_date <= ' . $db->Quote($date) . ')';
	}
	if ($volume != '')
	{
		$sql .= ' AND (start_volume <= ' . $db->Quote($volume) . ')';
	}
	$sql .= ' LIMIT 1';
	
	$result = $db->Execute($sql);
	if ($result == false) die("failed"); 
	
	$found = ($result->NumRows() == 1);

	return $found;
}
*/



//--------------------------------------------------------------------------------------------------
// Convert % encoded string, which may be in EUC-JP encoding, into UTF-8
function decode_string($str)
{
	$str = str_replace('+', ' ', $str);
	$decoded_str = rawurldecode($str);
	if (mb_detect_encoding($decoded_str) == 'ASCII')
	{
	}
	else
	{
		$decoded_str = mb_convert_encoding($decoded_str, 'UTF-8', 'EUC-JP');
	}
	return $decoded_str;
}


//--------------------------------------------------------------------------------------------------
/**
 *
 * http://ci.nii.ac.jp/cinii/pages/link_receive.html
 *
 *@brief
 */
function search_cinii($jtitle, $issn, $volume, $spage, $epage, $date, &$item)
{
	global $config;
	
	$item->authors = array();
		
	$url = 'http://ci.nii.ac.jp/openurl/query?ctx_ver=Z39.88-2004&url_ver=Z39.88-2004';
	
	$url .= '&ctx_enc=info%3aofi%2fenc%3aUTF-8'; // URL is UTF-8 encoded

	$url .= '&rft.date=' . $date;
	$url .= '&rft.volume=' . $volume;
	$url .= '&rft.spage=' . $spage;		
	
	if ($epage != 0)
	{
		$url .= '&rft.epage=' . $epage;		
	}
	
	if ($issn != '')
	{
		$item->issn = $issn;
	}
	
	if ($jtitle == '')
	{
		if ($issn != 0)
		{
			$jtitle = journal_title_from_issn($issn);
			$item->title = $jtitle;
		}
	}		
	$url .= '&rft.jtitle=' . str_replace(" ", "%20", $jtitle);
	
	echo $url;
		
	$html = get($url);
	
	//echo $html;
	
	// get bibliographic details
	
	$matches = array();
	
	
	// PDF?
	if (preg_match('/href="(?<pdf>\/cinii\/servlet\/CiNiiLog_Navi(.*)type=pdf(.*)[0-9]{3})">/', $html, $matches))
	{
		print_r($matches);
		
		$item->pdf = 'http://ci.nii.ac.jp' . $matches['pdf'];
	}
	
	
	if (preg_match('/href="\/openurl\/servlet\/createData\?type=bib(.*)">/', $html, $matches))
	{
		print_r($matches);
		
		// pull out stuff
		$parts = split("&", $matches[1]);
		
		foreach ($parts as $p)
		{
			list($k, $v) = explode ("=", $p);
			
			//echo $k, "=", $v, "\n";
			
			switch ($k)
			{
				case 'vol':
					$v = preg_replace('/^0*/', '', $v);
					$item->volume = $v;
					break;

				case 'num':
					$v = preg_replace('/^0*/', '', $v);
					$item->issue = $v;
					break;

				case 'title':
					$item->atitle = decode_string($v);
					break;

				case 'jtitle':
					$item->title = decode_string($v);
					break;

				case 'au':
					$a = new stdClass;
					$parts = explode('+', $v);
					switch (count($parts))
					{
						case 1: $a->lastname = decode_string($v);
							break;
						case 2:
						default:
							$a->lastname = decode_string($parts[0]);
							$a->forename = decode_string($parts[1]);
							break;
					}
					array_push($item->authors, $a);
					break; 

				case 'perm_link':
					$item->url = decode_string($v);
					break;

				case 'year':
					$date = $v;
					$m = array();
					if (preg_match('/([0-9]{4})([0-9]{2})([0-9]{2})/', $v, $m))
					{
						$item->year = $m[1];
						$item->date = $m[1] . '-' . $m[2] . '-' . $m[3];
					}
					break;

				case 'spage':
					list($spage, $epage) = explode("-", $v);
					$item->spage = $spage;
					$item->epage = $epage;
					break;

				default:
					break;
			}
		}
	}	
	
	
	// Get authors (need English version)
	
/*	if (isset($item->url))
	{
		echo $item->url . "\n";
		$url = $item->url . 'en/';
		echo $url . "\n";
		$html = get($url);
		
		echo $html;
		
		$matches = array();
		if (preg_match('/href="\/openurl\/servlet\/createData\?type=bib(.*)">/', $html, $matches))
		{
			print_r($matches);
			
			// pull out stuff
			$parts = split("&", $matches[1]);
			
			foreach ($parts as $p)
			{
				list($k, $v) = explode ("=", $p);
				
				echo $k, "=", $v, "\n";
				
				switch ($k)
				{
					case 'au':
						break;
				}
			}
		}
	}
*/		


}

// test

$item = new stdClass;
//search_cinii('', '0003-5092', 18, 185, 0, 1939, $item);

//search_cinii('','1343-8786', 2, 439, 445, 1999, $item);

// 1(2), June 25 1998: 233-239.  
//search_cinii('','1343-8786', 1, 233, 239, 1998, $item);

// Annotionaties Zo Jap
//search_cinii('', '0003-5092', 56, 338, 350, 1983, $item);

// Entomological REview
//search_cinii('', '0286-9810', 58, 1, 6, 2003, $item);

search_cinii('', '0286-9810', 58, 1, 6, 2003, $item);



print_r($item);

		if (find_in_cache($item))
		{
			echo "already exists\n";
		}
		else
		{
			store_in_cache($item);
		}	


?>
