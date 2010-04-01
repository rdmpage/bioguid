<?php

/**
 * @file ion_rss.php
 * 
 * Harvest ION RSS feeds, extracting names and literature links
 *
 */

require_once (dirname(__FILE__) . '/config.inc.php');
require_once (dirname(__FILE__) . '/lib.php');
require_once (dirname(__FILE__) . '/ref.php');
require_once (dirname(__FILE__) . '/rss.php');

//--------------------------------------------------------------------------------------------------
/**
 * @brief Format an arbitrary date as YYYY-MM-DD
 *
 * @param date A string representation of a date
 *
 * @return Date in YYYY-MM-DD format
 */
function format_date($date)
{
	$formatted_date = '';
	
	// Dates like 2006-8-7T15:47:36.000Z break PHP strtotime, so
	// replace the T with a space.
	$date = preg_replace('/-([0-9]{1,2})T([0-9]{1,2}):/', '-$1 $2:', $date);
	
	if (PHP_VERSION < 5.0)
	{
		if (-1 != strtotime($date))
		{
			$formatted_date = date("Y-m-d", strtotime($date));
		}		
	}
	else
	{
		if (false != strtotime($date))
		{
			$formatted_date = date("Y-m-d", strtotime($date));
		}
	}
	return $formatted_date;
}


//--------------------------------------------------------------------------------------------------
/**
 * @brief Return true if we have this item already
 *
 * @param item ION record
 *
 */
function item_exists($item)
{
	global $config;
	
	$db = NewADOConnection('mysql');
	$db->Connect("localhost", 
		$config['db_user'] , $config['db_passwd'] , $config['db_name']);
	
	// Ensure fields are (only) indexed by column name
	$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
	
	$sql = 'SELECT * FROM ion_rss WHERE (guid=' . $item->guid . ') LIMIT 1';
	
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $db->ErrorMsg());

	return ($result->NumRows() == 1);
}


//--------------------------------------------------------------------------------------------------
/**
 * @brief Store one ION record in local database
 *
 * @param item ION record
 *
 */

/*
    [title] => New Taxon Alert:- (Hylidae) Litoria robinsonae
    [link] => http://www.organismnames.com/namedetails.htm?lsid=2014990&from=rss
    [pubDate] => 2008-10-15
    [description] => <p><div style="font-weight: bold;">Taxon Name: </div>Litoria robinsonae</p><p><div style="font-weight: bold;">Publication Title: </div>A new species of treefrog (Hylidae, Litoria) from the southern lowlands of New Guinea.</p>
    [guid] => 2014990
    [group] => Hylidae
    [name] => Litoria robinsonae
    [publicationTitle] => A new species of treefrog (Hylidae, Litoria) from the southern lowlands of New Guinea.
    [taxonAuthor] => Oliver, Stuart-Fox & Richards 2008
    [full_publication] => A new species of treefrog (Hylidae, Litoria) from the southern lowlands of New Guinea. Current Herpetology, 27(1), June 2008: 35-42.  
    [publication] => stdClass Object
        (
            [journal] =>  Current Herpetology
            [volume] => 27
            [issue] => 1
            [year] => 2008
            [spage] => 35
            [epage] => 42
            [date] =>  June 2008
            [actualyear] => 
            [yyyy_mm_dd] => 2008-06-01
            [doi] => 10.3105/1345-5834(2008)27[35:ANSOTH]2.0.CO;2
        )

)
*/

function store_item($item)
{
	global $config;
	
	$db = NewADOConnection('mysql');
	$db->Connect("localhost", 
		$config['db_user'] , $config['db_passwd'] , $config['db_name']);
	
	// Ensure fields are (only) indexed by column name
	$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
	
	$sql = 'SELECT * FROM ion_rss WHERE (guid=' . $item->guid . ') LIMIT 1';
	
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $db->ErrorMsg());

	if ($result->NumRows() == 0)
	{
		$fields = 'guid, pubDate, `group`, `name`, taxonAuthor, full_publication, publicationTitle';
		$values = $item->guid
			. ',' . $db->qstr(trim($item->pubDate))
			. ',' . $db->qstr(trim($item->group))
			. ',' . $db->qstr(trim($item->name))
			. ',' . $db->qstr(trim($item->taxonAuthor))
			. ',' . $db->qstr(trim($item->full_publication))
			. ',' . $db->qstr(trim($item->publicationTitle));
			
		if (isset($item->publication))
		{
			foreach ($item->publication as $k => $v)
			{
				$fields .= ',' . $k;
				$values .= ',' . $db->qstr(trim($v));
			}
		}
		
		$sql = 'INSERT INTO ion_rss (' . $fields . ') VALUES (' . $values . ')';
		
		//echo $sql;

		$result = $db->Execute($sql);
		if ($result == false) die("failed [" . __LINE__ . "]: " . $db->ErrorMsg());
	}
	else
	{
		// Update details (our reference parsing has gotten better)
		if (isset($item->publication->journal))
		{
			if ($result->fields['journal'] != trim($item->publication->journal))
			{	
				$count = 0;
				$sql = 'UPDATE ion_rss SET ';
				foreach ($item->publication as $k => $v)
				{
					if ($count > 0)
					{
						$sql .= ',';
					}
					$sql .= $k . '=' . $db->qstr(trim($v));
					$count++;
				}
				$sql .= ' WHERE guid= ' . $item->guid;
				
				echo $sql;

				$result = $db->Execute($sql);
				if ($result == false) die("failed [" . __LINE__ . "]: " . $db->ErrorMsg());
			}
		}
	}

}


//--------------------------------------------------------------------------------------------------
/**
 * @brief Populate record for one name with details from ION web site using screen scraping
 *
 * @param item ION record
 *
 */
function ion_process(&$item)
{
	$debug = true;
	
	$url = $item->link;

	echo $url . "\n";
	$html = get($url);
	
	//echo $html;
	
	$author = '';
	
	// extract
	
	$matches = array();
	if (preg_match('/<\/h1><p>(.*)<\/p><div class="documentContent">/', $html, $matches))
	{
		//print_r($matches);
		
		$author = html_entity_decode($matches[1]);
		if (preg_match('/(.*)<\/li>/', $author))
		{
			$pos = strpos($author, "<");
			if ($pos != false)
			{
				$author = substr($author, 0, $pos);
			}
		}
		
		$item->taxonAuthor = $author;
		//echo "author=$author\n";
	}
	
//	if (preg_match('/<h4>Original Description Reference<\/h4><ul><li>(.*)\s*\[Zoological/', $html, $matches))

	if (preg_match('/<h3>Original Description Reference<\/h3><ul><li>([^<]+|(?R))*<\/li>/', $html, $matches))
	{
		//print_r($matches);
		
		$description = html_entity_decode($matches[1]);
		
		$item->full_publication = $description;
		
		if (preg_match('/(.*)\[Zoological Record/', $description))
		{
			$pos = strpos($description, "[Zoological Record");
			if ($pos != false)
			{
				$description = substr($description, 0, $pos);
			}
		}
		
		
		// Remove article title
		$description = trim(str_replace($item->publicationTitle, '', $description));

		//echo "description=$description\n";
		
		$item->full_publication = $description;
		
		//Natuurwetenschappelijke Studiekring voor Suriname en de Nederlandse Antillen, No. 112 1984: 1-167.
		
		
		// Extract bibliographic details
		if (parse_ion_ref($description , $matches))
		{
			//print_r($matches);
			
			$item->publication->journal = $matches['journal'];
			$item->publication->volume = $matches['volume'];
			$item->publication->issue = $matches['issue'];
			$item->publication->year = $matches['year'];
			$item->publication->spage = $matches['spage'];
			$item->publication->epage = $matches['epage'];
			$item->publication->date = $matches['date'];
			$item->publication->actualyear = $matches['actualyear'];
			
			if (isset($item->publication->date))
			{
				$d = format_date($item->publication->date);
				if ($d != '')
				{
					$item->publication->yyyy_mm_dd = $d;
				}
			}
		}
	}
	
	// Do stuff for this record... (such as get DOI if it exists)
	if (isset($item->publication->journal)
	 && isset($item->publication->volume)
	 && isset($item->publication->spage)
	)
	{
		$url = 'http://bioguid.info/openurl?genre=article'
			. '&title=' . urlencode($item->publication->journal) 
			. '&volume=' . $item->publication->volume
			. '&spage=' . $item->publication->spage
			. '&display=json';
		$j = json_decode(get($url));
		
		if ($debug)
		{
			print_r($j);
		}
		
		if ($j->status == 'ok')
		{
			if (isset($j->doi))
			{
				$item->publication->doi = $j->doi;
			}
			if (isset($j->pmid))
			{
				$item->publication->pmid = $j->pmid;
			}
			if (isset($j->hdl))
			{
				$item->publication->hdl = $j->hdl;
			}
			if (isset($j->url))
			{
				$item->publication->url = $j->url;
			}
		}
	
		
	}
	// Store
	store_item($item);	
}


//--------------------------------------------------------------------------------------------------
//
/**
 * @brief Fetch an ION RSS feed, and convert to object for ease of processing
 *
 * We convert RSS to JSON to create object. We use conditional GET to check whether
 * feed has been modified.
 *
 * @param url Feed URL
 * @param data Object
 *
 * @return Result from RSS fetch (0 is OK, 304 is feed unchanged, anything else is an error)
 */
function ion_fetch_rss($url, &$data)
{
	$rss = '';
	$msg = '200';
	
	$result = GetRSS ($url, $rss, false);//true);
	if ($result == 0)
	{
		// Archive
		$dir = dirname(__FILE__) . '/tmp/' . date("Y-m-d");
		if (!file_exists($dir))
		{
			$oldumask = umask(0); 
			mkdir($dir, 0777);
			umask($oldumask);
		}
		$rss_file_name = $dir . '/' . md5($url) . '.xml';
		$rss_file = fopen($rss_file_name, "w+") or die("could't open file --\"$rss_file_name\"");
		fwrite($rss_file, $rss);
		fclose($rss_file);
	
	
		// Convert to JSON		
		$xp = new XsltProcessor();
		$xsl = new DomDocument;
		$xsl->load(dirname(__FILE__) . '/xsl/ionrss.xsl');
		$xp->importStylesheet($xsl);
		
		$xml_doc = new DOMDocument;
		$xml_doc->loadXML($rss);
		
		$json = $xp->transformToXML($xml_doc);
				
		$data = json_decode($json);
	}
	else
	{
		switch ($result)
		{
			case 304: 
				$msg = 'Feed has not changed since last fetch (' . $result . ')'; 
				break;
			default: 
				$msg = 'Badness happened (' . $result . ') ' . $url; 
				break;
		}	
	}
	
	echo $msg, "\n";
	
	return $result;
}


//--------------------------------------------------------------------------------------------------
//
/**
 * @brief Harvest ION RSS Alerts
 *
 */
function main()
{

	// Organism hierarchy from http://www.organismnames.com/alerts.htm
	// Extract these using Firefox Extract Links plugin
	$ids = array(
		"Acanthocephala" => "http://www.organismnames.com/RSS/Acanthocephala.xml",
		"Acritarcha" => "http://www.organismnames.com/RSS/Acritarcha.xml",
		"Animalia" => "http://www.organismnames.com/RSS/Animalia.xml",
		"Annelida" => "http://www.organismnames.com/RSS/Annelida.xml",
		"Apicomplexa" => "http://www.organismnames.com/RSS/Apicomplexa.xml",
		"Archaeocyatha" => "http://www.organismnames.com/RSS/Archaeocyatha.xml",
		"Arthropoda" => "http://www.organismnames.com/RSS/Arthropoda.xml",
		"Ascetospora" => "http://www.organismnames.com/RSS/Ascetospora.xml", 
		"Brachiopoda" => "http://www.organismnames.com/RSS/Brachiopoda.xml",
		"Bryozoa" => "http://www.organismnames.com/RSS/Bryozoa.xml",
		"Chaetognatha" => "http://www.organismnames.com/RSS/Chaetognatha.xml",
		"Chitinozoa" => "http://www.organismnames.com/RSS/Chitinozoa.xml",
		"Chordata" => "http://www.organismnames.com/RSS/Chordata.xml",
		"Ciliophora" => "http://www.organismnames.com/RSS/Ciliophora.xml",
		"Cnidaria" => "http://www.organismnames.com/RSS/Cnidaria.xml",
		"Conodonta" => "http://www.organismnames.com/RSS/Conodonta.xml",
		"Conulariida" => "http://www.organismnames.com/RSS/Conulariida.xml",
		"Ctenophora" => "http://www.organismnames.com/RSS/Ctenophora.xml",
		"Cycliophora" => "http://www.organismnames.com/RSS/Cycliophora.xml",
		"Echinodermata" => "http://www.organismnames.com/RSS/Echinodermata.xml",
		"Echiura" => "http://www.organismnames.com/RSS/Echiura.xml",
		"Entoprocta" => "http://www.organismnames.com/RSS/Entoprocta.xml",
		"Gastrotricha" => "http://www.organismnames.com/RSS/Gastrotricha.xml",
		"Gnathostomulida" => "http://www.organismnames.com/RSS/Gnathostomulida.xml",
		"Graptolithina" => "http://www.organismnames.com/RSS/Graptolithina.xml",
		"Hemichordata" => "http://www.organismnames.com/RSS/Hemichordata.xml",
		"Hemimastigophora" => "http://www.organismnames.com/RSS/Hemimastigophora.xml",
		"Kinorhyncha" => "http://www.organismnames.com/RSS/Kinorhyncha.xml",
		"Labyrinthomorpha" => "http://www.organismnames.com/RSS/Labyrinthomorpha.xml",
		"Loricifera" => "http://www.organismnames.com/RSS/Loricifera.xml",
		"Mesozoa" => "http://www.organismnames.com/RSS/Mesozoa.xml",
		"Micrognathozoa" => "http://www.organismnames.com/RSS/Micrognathozoa.xml",
		"Microspora" => "http://www.organismnames.com/RSS/Microspora.xml",
		"Mollusca" => "http://www.organismnames.com/RSS/Mollusca.xml",
		"Myxozoa" => "http://www.organismnames.com/RSS/Myxozoa.xml",
		"Nematoda" => "http://www.organismnames.com/RSS/Nematoda.xml",
		"Nematomorpha" => "http://www.organismnames.com/RSS/Nematomorpha.xml",
		"Nemertinea" => "http://www.organismnames.com/RSS/Nemertinea.xml",
		"Onychophora" => "http://www.organismnames.com/RSS/Onychophora.xml",
		"Perkinsozoa" => "http://www.organismnames.com/RSS/Perkinsozoa.xml",
		"Petalonamae" => "http://www.organismnames.com/RSS/Petalonamae.xml",
		"Phoronida" => "http://www.organismnames.com/RSS/Phoronida.xml",
		"Placididea" => "http://www.organismnames.com/RSS/Placididea.xml",
		"Placozoa" => "http://www.organismnames.com/RSS/Placozoa.xml",
		"Platyhelminthes" => "http://www.organismnames.com/RSS/Platyhelminthes.xml",
		"Porifera" => "http://www.organismnames.com/RSS/Porifera.xml",
		"Priapulida" => "http://www.organismnames.com/RSS/Priapulida.xml",
		"Protozoa" => "http://www.organismnames.com/RSS/Protozoa.xml",
		"Rotifera" => "http://www.organismnames.com/RSS/Rotifera.xml",
		"Sarcomastigophora" => "http://www.organismnames.com/RSS/Sarcomastigophora.xml",
		"Sipuncula" => "http://www.organismnames.com/RSS/Sipuncula.xml",
		"Tardigrada" => "http://www.organismnames.com/RSS/Tardigrada.xml",
		"Xenusia" => "http://www.organismnames.com/RSS/Xenusia.xml"
	);
	
	
	// Test for one id
	//$id = "Anura";
	
	// Loop through all categories
	foreach ($ids as $taxon => $url)
	{
		// Get RSS feed for this taxon
		
		$data = new stdClass;
		
		// Fetch RSS and transform into object
		$result = ion_fetch_rss($url, $data);
		
		if ($result == 0)
		{
			#print_r($data);
			
			// Extract details from each item
			foreach ($data->items as $item)
			{
			
				//print_r($item);
			
				// Don't hammer the server
				if (item_exists($item))
				{
					echo "Item " . $item->guid . " exists\n";
				}
				else
				{	
					// Clean taxon name and extract taxonomic group
					
					if (preg_match('/New Taxon Alert:-\s+\((?<group>(.*))\)\s+(?<name>(.*)\s*\((.*)\)\s*(.*))$/', $item->title, $matches))
					{
						$item->group = $matches['group'];
						$item->name = $matches['name'];
					}
					else if (preg_match('/New Taxon Alert:-\s+\((?<group>(.*))\)\s+(?<name>(.*))$/', $item->title, $matches))
					{
						#print_r($matches);
						
						$item->group = $matches['group'];
						$item->name = $matches['name'];
					}
				
					// Clean publication title so we can remove this when we parse reference
					$item->publicationTitle = '';
					$matches = array();
					if (preg_match('/Publication Title:(.*)/', $item->description, $matches))
					{
						//print_r($matches);
						
						$title = $matches[1];
						$title = strip_tags($title);
						$title= trim($title);
						
						$item->publicationTitle = $title;
						//echo "$title\n";
					}
					
					// Fetch HTML so we can harvest taxon author name and bibliographic details
					ion_process($item);
					
					// Output result
					print_r($item);
					
					sleep(5);
				}
			}
		}
	}
}

main();

?>