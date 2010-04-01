<?php

// retrieve a GenBank sequence record

require_once(dirname(__FILE__).'/config.inc.php');
require_once(dirname(__FILE__).'/db.php');
require_once(dirname(__FILE__).'/lib.php');
require_once(dirname(__FILE__).'/specimen_codes.php');
require_once(dirname(__FILE__).'/ubios.php');
require_once(dirname(__FILE__).'/utils.php');


// fetch and post process genbank sequences


function gb_postprocess(&$data)
{
	if (isset($data->created))
	{
		$d = format_date($data->created);
		if ('' != $d)
		{
			$data->created = $d;
		}
	}
	if (isset($data->updated))
	{
		$d = format_date($data->updated);
		if ('' != $d)
		{
			$data->updated = $d;
		}
	}
	
	
	// Fix lat/lons
	gb_lat_lon($data);
	
	// Locality
	gb_locality($data);
	
	// Taxonomic group
	gb_taxonomic_group($data);
	
	// Extract specimen guid
	gb_specimen_code($data);
	
	// Host
	gb_host($data);
	

	//echo "Looking up refs\n";
	foreach($data->references as $ref)
	{
		//print_r($ref);
		// Get guid(s) for publication
		if (!isset($ref->pmid))
		{
			// 
			
			// Publication
			// Clean up 'er' prefix
			$ref->bibliographicCitation = preg_replace('/^\(er\) /', '',  $ref->bibliographicCitation);
		
			// Clean up 'e' prefix for starting page of electronic journals
			$ref->bibliographicCitation = preg_replace('/, e([0-9]+) \(/', ', $1 (',  $ref->bibliographicCitation);
			
		
			//echo "Parse ref " . $ref->title . "\n";
			
			// Mol. Phylogenet. Evol. 29 (3), 582-598 (2003)
			
			// embl
			//Bull. Am. Mus. Nat. Hist. 299:1-261(2006).
	
			if (preg_match('/(?<title>.*) (?<volume>\d+)(\s*\((?<issue>\d+)\))?(, |:)(?<spage>\d+)-(?<epage>\d+)\s*\((?<year>\d+)\)/', $ref->bibliographicCitation, $matches))
			{
				//print_r($matches);
				
				// store metadata for possible use later
				$ref->title = $matches['title'];
				$ref->volume = $matches['volume'];
				$ref->spage = $matches['spage'];
				if ($matches['epage'] != '')
				{
					$ref->epage = $matches['epage'];
				}
				$ref->year = $matches['year'];
				
				// Get ISSN for JACC
				$url = 'http://bioguid.info/services/journalsuggest.php?title=' . urlencode($ref->title );
				$json = get($url);
				$j = json_decode($json);
				if (isset($j->results[0]->issn))
				{
					$ref->issn = $j->results[0]->issn;
				}
				
				if ($matches['spage'] && $matches['spage'] != 0)
				{
					
					// Get GUIDs (if any)
					$url = 'http://bioguid.info/openurl?genre=article';
					$url .= '&title=' . urlencode($matches['title']);
					$url .= '&volume=' . $matches['volume'];
					$url .= '&spage=' . $matches['spage'];
					$url .= '&date=' . $matches['year'];
					$url .= '&display=json';
					
					//echo $url;
					
					$json = get($url);
					
					$j = json_decode($json);
					
					//echo $j;
					
					
					
					if ($j->status == 'ok')
					{
						if (isset($j->doi))
						{
							$ref->doi = $j->doi;
						}
						if (isset($j->hdl))
						{
							$ref->hdl = $j->hdl;
						}
						if (isset($j->url))
						{
							$ref->url = $j->url;
						}
					}
				}
				
			}
		}
		else
		{
			// Lookup up PMID and get associated DOI (if exists)
			$url = 'http://bioguid.info/openurl?id=pmid:' . $ref->pmid . '&display=json';
			$json = get($url);
			
			//echo $json . "\n";
			
			$j = json_decode($json);
			
			//echo $j;
			
			
			if ($j->status == 'ok')
			{
				if (isset($j->doi))
				{
					$ref->doi = $j->doi;
				}
				if (isset($j->hdl))
				{
					$ref->hdl = $j->hdl;
				}
				if (isset($j->url))
				{
					$ref->url = $j->url;
				}
				
				// metadata to detect identical papers
				if (isset($j->issn))
				{
					$ref->issn = $j->issn;
				}
				if (isset($j->spage))
				{
					$ref->spage = $j->spage;
				}
				if (isset($j->volume))
				{
					$ref->volume = $j->volume;
				}
				if (isset($j->epage))
				{
					$ref->epage = $j->epage;
				}
				if (isset($j->abstract))
				{
					$ref->abstract = $j->abstract;
				}
				if (isset($j->issue))
				{
					$ref->issue = $j->issue;
				}
				if (isset($j->year))
				{
					$ref->year = $j->year;
				}
				if (isset($j->title))
				{
					$ref->title = $j->title;
				}
				
				
				
			}
		}
	} 
}

function gb_host(&$data)
{
	if  (isset($data->source->host))
	{
		$names = ubio_namebank_search_rest(trim($data->source->host), false, true); // just take simple exact match
		
		if (count($names) > 0)
		{
			$data->source->host_namebankID = $names[0];
		}
	}
}


// Vertebrate digir collections need to know what kind of animal it is
function gb_taxonomic_group (&$data)
{
	// Handle specimen info
	$taxonomicGroup = '';

	//-----------------------------------------------------------------
	// What kind of organism are we dealing with?
	// We need this for DiGIR-based GUIDs
	if (preg_match ('/ Amphibia;/', $data->taxonomy))
	{
		$taxonomicGroup = 'Amphibia';
	}
	if (preg_match ('/ Lepidosauria;/', $data->taxonomy))
	{
		$taxonomicGroup = 'Reptiles';
	}
	if (preg_match ('/ Testudines;/', $data->taxonomy))
	{
		$taxonomicGroup = 'Reptiles';
	}
	if (preg_match ('/ Mammalia;/', $data->taxonomy))
	{
		$taxonomicGroup = 'Mammals';
	}
	if (preg_match ('/ Aves;/', $data->taxonomy))
	{
		$taxonomicGroup = 'Birds';
	}
	if (preg_match ('/ Actinopterygii;/', $data->taxonomy))
	{
		$taxonomicGroup = 'Fish';
	}
	
	if ($taxonomicGroup != '')
	{
		$data->taxonomic_group = $taxonomicGroup;
	}
}


function gb_specimen_code(&$data)
{
	// get taxonomic group (we might need this even if we lack a specimen code)



	$voucher_code = '';
	
	if  (isset($data->source->specimen_voucher))
	{
		$v = $data->source->specimen_voucher;
		
		// clean

		// Cases such as EF629441 have colons in the specimen name
		$v = str_replace(":", " ", $v);
		//  AY193412 has - in name
		$v = str_replace("-", " ", $v);
		
		
		//echo "v=$v\n";
		
		$ids = extract_specimen_codes($v);
		
		//print_r($ids);
		
		if (count($ids) == 1)
		{
			$voucher_code = $ids[0];
		}
	}


	if ('' == $voucher_code)
	{
		// Try isolate field
		if  (isset($data->source->isolate))
		{
			$ids = extract_specimen_codes($data->source->isolate);
			
			if (count($ids) == 1)
			{
				$voucher_code = $ids[0];
			}
		}
	}


	
	if ($voucher_code != '')
	{
		$data->source->specimen_code = $voucher_code;
		
		//echo "voucher=$voucher\n";
		
		// Can we get linked data?
		$collectionCode = '';
		if (isset($data->taxonomic_group))
		{
			switch ($data->taxonomic_group)
			{
				case 'Amphibia':
				case 'Reptiles':					
					$collectionCode = 'Herps'; // default
					
					$parts = split(" ", $voucher_code);
					switch ($parts[0])
					{
						// MCZ
						case 'MCZ':
							if ($data->taxonomic_group == 'Amphibia')
							{
								$collectionCode='Amph';
							}
							else
							{
								$collectionCode='Rept';
							}
							break;
							
						// LACM
						case 'LACM':
							$collectionCode = '';
							break;
							
						// Australian stuff
						case 'AM':
						case 'SAMA':
						case 'ANWC':
						case 'AMS';
						case 'WAM':
						case 'NT':							
							$collectionCode = '';
							break;
							
						default:
							break;
					}
					
					break;
					
				default:
					$collectionCode = $data->taxonomic_group;
					break;
			}
			
			$parts = split(" ", $voucher_code);
			$url = 'http://bioguid.info/openurl?genre=specimen&institutionCode=' . $parts[0] .
				'&collectionCode=' . $collectionCode . '&catalogNumber=' . $parts[1] . '&display=json';
				
			//echo $url;
				
			// fetch
			$json = get($url);
			//echo $json;
			$j = json_decode($json);
			if (isset($j->title))
			{
				$data->source->specimen = $j;
			}
			
		
		}
		
		
		
	}
}

function gb_lat_lon(&$data)
{
	//echo 'latlon' . $data->source->lat_lon . "\n";
	if (isset($data->source->lat_lon))
	{
		$lat_lon = $data->source->lat_lon;

		if (preg_match ("/(N|S)[;|,] /", $lat_lon))
		{
			// it's a literal string description, not a pair of decimal coordinates.
			
			//  35deg12'07'' N; 83deg05'2'' W, e.g. DQ995039
			if (preg_match("/([0-9]{1,2})deg([0-9]{1,2})'(([0-9]{1,2})'')?\s*([S|N])[;|,]\s*([0-9]{1,3})deg([0-9]{1,2})'(([0-9]{1,2})'')?\s*([W|E])/", $lat_lon, $matches))
			{
				//print_r ($matches);
				
				$degrees = $matches[1];
				$minutes = $matches[2];
				$seconds = $matches[4];
				$hemisphere = $matches[5];
				$lat = $degrees + ($minutes/60.0) + ($seconds/3600);
				if ($hemisphere == 'S') { $lat *= -1.0; };

				$data->source->latitude = $lat;

				$degrees = $matches[6];
				$minutes = $matches[7];
				$seconds = $matches[9];
				$hemisphere = $matches[10];
				$long = $degrees + ($minutes/60.0) + ($seconds/3600);
				if ($hemisphere == 'W') { $long *= -1.0; };
				
				$data->source->longitude = $long;
				
				
			}
			else
			{
				
				list ($lat, $long) = split ("; ", $lat_lon);
	
				list ($degrees, $rest) = explode (" ", $lat);
				list ($minutes, $rest) = explode ('.', $rest);
	
				list ($decimal_minutes, $hemisphere) = explode ("'", $rest);
	
	
				$lat = $degrees + ($minutes/60.0) + ($decimal_minutes/6000);
				if ($hemisphere == 'S') { $lat *= -1.0; };
	
				$data->source->latitude = $lat;
	
				list ($degrees, $rest) = explode (" ", $long);
				list ($minutes, $rest) = explode ('.', $rest);
	
				list ($decimal_minutes, $hemisphere) = explode ("'", $rest);
	
				$long = $degrees + ($minutes/60.0) + ($decimal_minutes/6000);
				if ($hemisphere == 'W') { $long *= -1.0; };
				$data->source->longitude = $long;
			}

		}
		
		// 8 deg 45 min S, 63 deg 26 min W [DQ098864]
		if (preg_match("/([0-9]{1,2})\s*deg\s*([0-9]{1,2})\s*min\s*([S|N]),\s*([0-9]{1,3})\s*deg\s*([0-9]{1,2})\s*min\s*([W|E])/", $lat_lon, $matches))
		{
			print_r ($matches);
			
			$degrees = $matches[1];
			$minutes = $matches[2];
			$seconds = 0;
			$hemisphere = $matches[3];
			$lat = $degrees + ($minutes/60.0) + ($seconds/3600);
			if ($hemisphere == 'S') { $lat *= -1.0; };
		
			$data->source->latitude = $lat;
		
			$degrees = $matches[4];
			$minutes = $matches[5];
			$seconds = 0;
			$hemisphere = $matches[6];
			$long = $degrees + ($minutes/60.0) + ($seconds/3600);
			if ($hemisphere == 'W') { $long *= -1.0; };
			
			$data->source->longitude = $long;
		}
		
		
		// N19.49048, W155.91167 [EF219364]
		if (preg_match ("/(?<lat_hemisphere>(N|S))(?<latitude>(\d+(\.\d+))), (?<long_hemisphere>(W|E))(?<longitude>(\d+(\.\d+)))/", $lat_lon, $matches))
		{
			//print_r($matches);
			
			$lat = $matches['latitude'];
			if ($matches['lat_hemisphere'] == 'S') { $lat *= -1.0; };
			$data->source->latitude = $lat;
			
			$long = $matches['longitude'];
			if ($matches['long_hemisphere'] == 'W') { $long *= -1.0; };
			$data->source->longitude = $long;

		}
		
		if (!isset($data->source->latitude))
		{
			//13.2633 S 49.6033 E
			if (preg_match("/([0-9]+(\.[0-9]+)*) ([S|N]) ([0-9]+(\.[0-9]+)*) ([W|E])/", $lat_lon, $matches))
			{
				//print_r ($matches);
				
				$lat = $matches[1];
				if ($matches[3] == 'S') { $lat *= -1.0; };
				$data->source->latitude = $lat;
	
				$long = $matches[4];
				if ($matches[6] == 'W') { $long *= -1.0; };
				$data->source->longitude = $long;
			}
		}
		
		if (!isset($data->source->latitude))
		{
			$parts = explode (",", $lat_lon);
			$data->source->latitude = $parts[0];
			$data->source->longitude = $parts[1];
		}
		
		
	}
}
function gb_locality(&$data)
{
	if  (isset($data->source->country))
	{
		$country = $data->source->country;
		$matches = array();	
		$parts = explode (":", $country);	
		$data->source->country = $parts[0];
					
		if (count($parts) > 1)
		{
			$data->source->locality = trim($parts[1]);
			// Clean up
			$data->source->locality = str_replace(' GPS', '', $data->source->locality);				
		}	
		
		// Handle AMNH stuff
		if (preg_match('/[0-9]+deg[0-9]{1,2}\'\s*[N|S]/i', $country, $matches))
		{
			list ($degrees, $rest) = explode ("deg", $matches[0]);
			list ($minutes, $hemisphere) = split ('(&apos;|\')', $rest);
			$lat = $degrees + ($minutes/60);		
			if ($hemisphere == 'S') { $lat *= -1.0; };
			$data->source->latitude = $lat;
		}
		if (preg_match('/[0-9]+deg[0-9]{1,2}\'\s*[W|E]/i', $country, $matches))
		{
			list ($degrees, $rest) = split ("deg", $matches[0]);
			list ($minutes, $hemisphere) = split ('(&apos;|\')', $rest);
			$long = $degrees + ($minutes/60);
			if ($hemisphere == 'W') { $long *= -1.0; };
			$data->source->longitude = $long;
		}
		
		//(GPS: 33 38' 07'', 146 33' 12'') e.g. AY281244
		if (preg_match("/\(GPS:\s*([0-9]{1,2})\s*([0-9]{1,2})'\s*([0-9]{1,2})'',\s*([0-9]{1,3})\s*([0-9]{1,2})'\s*([0-9]{1,2})''\)/", $country, $matches))
		{
			print_r($matches);
			
			$lat = $matches[1] + $matches[2]/60 + $matches[3]/3600;
			
			// OMG
			if ($data->source->country == 'Australia')
			{
				$lat *= -1.0;
			}
			$long = $matches[4] + $matches[5]/60 + $matches[6]/3600;

			$data->source->latitude = $lat;
			$data->source->longitude = $long;
			
		}
		
		
	}
	
	// Some records have lat and lon in isolation_source, e.g. AY922971
	if  (isset($data->source->isolation_source))
	{
		$isolation_source = $data->source->isolation_source;
		$matches = array();
		if (preg_match('/([0-9]+\.[0-9]+) (N|S), ([0-9]+\.[0-9]+) (W|E)/i', $isolation_source, $matches))
		{
			//print_r($matches);	
			
			$data->source->latitude = $matches[1];
			if ($matches[2] == 'S')
			{
				$data->source->latitude *= -1;
			}
			$data->source->longitude = $matches[3];
			if ($matches[4] == 'W')
			{
				$data->source->longitude *= -1;
			}
			
		}
		if  (!isset($data->source->locality))
		{
			$data->source->locality = $data->source->isolation_source;
		}
	}
}	

// get sequence from NCBI
function get_sequence($accession, &$item)
{
	$id = find_genbank($accession);
	if ($id == 0)
	{
		// We don't have this sequence (but see below)
		$url = 'http://eutils.ncbi.nlm.nih.gov/entrez/eutils/efetch.fcgi?db=nucleotide&id=' 
				. $accession . '&rettype=gb&retmode=xml';	
				
		//echo $url;
		
		$xml = get($url);
		
		//echo $xml;
		
		// Did we get an error?
		
		// Nothing returned
		if ($xml == '')
		{
			return 0;
		}
		
		//echo "\n\n" . __LINE__ . "\n\n";
		
		// NCBI error (sequence doesn't exist, or might not be released
		$dom= new DOMDocument;
		$dom->loadXML($xml);
		$xpath = new DOMXPath($dom);
		$xpath_query = "//Error";
		$nodeCollection = $xpath->query ($xpath_query);
		
		$ok = true;
		foreach ($nodeCollection as $node)
		{
			if ($node->firstChild->nodeValue != '')
			{
				$ok = false;
			}
		}
		if (!$ok)
		{
			return 0;
		}		
	
		//echo "\n\n" . __LINE__ . "\n\n";
	
	
		$xp = new XsltProcessor();
		$xsl = new DomDocument;
		$xsl->load('xsl/gb2JSON.xsl');
		$xp->importStylesheet($xsl);
		
		$xml_doc = new DOMDocument;
		$xml_doc->loadXML($xml);
		
		$json = $xp->transformToXML($xml_doc);
		
		
		//echo $json;
		
		$data = json_decode($json);
		
		// Handle case where was have this sequnece from EMBL harvesting
		$id = set_gi($data->accession, $data->gi);
		if ($id == 0)
		{
			// new sequence
			gb_postprocess($data);
	
			//print_r($data);
	
			$id = store_genbank($data);
	
			$item = $data;
		}
		else
		{
			// we have this already from EMBL
			$json = retrieve_genbank_json($id);
			$item = json_decode($json);
		}
	}
	else
	{
		//echo 'have it' . "\n";
		$json = retrieve_genbank_json($id);
		$item = json_decode($json);
	}
	return $id;
}


// post process

/*
$accession = 'AY217980';
//$accession = 'DQ433658';
$accession = 'DQ675400';
$accession = 'AY214427'; // zootaxa
//$accession = 'DQ116471';

//$accession = 'DQ282907';

//$accession = 'AY862179';

$accession = 'AF128460'; //'NC_009683'; //EU165430'; //'DQ306566'; //33286585; // 'AY662900'; //'EU275783';

$accession = '40809698';

$accession = 'AF128501'; //'AY217980';

$accession = 'DQ080041';

echo get_sequence($accession, $item);
*/

?>