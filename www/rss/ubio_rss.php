<?php

// $Id: $

// Import uBio new name RSS feed, postprocess, and store

require_once (dirname(__FILE__) . '/config.inc.php');
require_once (dirname(__FILE__) . '/lib.php');
require_once (dirname(__FILE__) . '/extract.php');
require_once (dirname(__FILE__) . '/rss.php');
require_once (dirname(__FILE__) . '/ubio_search.php');

// functions

// extract name (a: b: c)
// extract sp. nov, etc.

// call external services to handle identifiers/links

//--------------------------------------------------------------------------------------------------
//
/**
 * @brief Fetch an uBio RSS feed, and convert to object for ease of processing
 *
 * We convert RSS to JSON to create object. We use conditional GET to check whether
 * feed has been modified.
 *
 * @param url Feed URL
 * @param data Object
 *
 * @return Result from RSS fetch (0 is OK, 304 is feed unchanged, anything else is an error)
 */
function ubio_fetch_rss($url, &$data)
{
	$rss = '';
	$msg = '200';
	
	$result = GetRSS ($url, $rss, true);
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
		$xsl->load(dirname(__FILE__) . '/xsl/ubiorss.xsl');
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
function store_item($item)
{
	global $config;
	
	$id = 0;
	
	$db = NewADOConnection('mysql');
	$db->Connect("localhost", 
		$config['db_user'] , $config['db_passwd'] , $config['db_name']);
	
	// Ensure fields are (only) indexed by column name
	$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
	
	$sql = 'SELECT * FROM ubio_rss WHERE (link=' . $db->qstr($item->link) . ') LIMIT 1';
	
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $db->ErrorMsg());

	if ($result->NumRows() == 0)
	{
		$fields = '';
		$values = '';
		$count = 0;
		
		foreach ($item as $k => $v)
		{
			switch ($k)
			{
				// eat these
				case 'identifier':
				case 'keywords':
					break;
				
				default:
					if ($count > 0)
					{
						$fields .= ',';
						$values .= ',';
					}
					$fields .= '`' . $k . '`';
					$values .= $db->qstr(trim($v));
					$count++;
					break;
			}
					
		}
		$sql = 'INSERT INTO ubio_rss (' . $fields . ') VALUES (' . $values . ')';
		
		//echo $sql;

		$result = $db->Execute($sql);
		if ($result == false) die("failed [" . __LINE__ . "]: " . $db->ErrorMsg());
		
		$id = $db->Insert_ID();
	}
	else
	{
		$id = $result->fields['id'];
	}

	return $id;
}

//--------------------------------------------------------------------------------------------------
function store_one_name($name, $namebankID, $rank, $is_new, $item_id)
{
	global $config;
	
	$id = 0;
	
	$db = NewADOConnection('mysql');
	$db->Connect("localhost", 
		$config['db_user'] , $config['db_passwd'] , $config['db_name']);
	
	// Ensure fields are (only) indexed by column name
	$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
	
	$sql = 'SELECT * FROM ubio_rss_name WHERE (name=' . $db->qstr($name) . ') LIMIT 1';
	
	//echo $sql . "\n";
	
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $db->ErrorMsg());

	if ($result->NumRows() == 0)
	{
		$sql = 'INSERT INTO ubio_rss_name(name, namebankID) VALUES ('
			. $db->qstr($name)
			. ',' . $namebankID
			. ')';

		$result = $db->Execute($sql);
		if ($result == false) die("failed [" . __LINE__ . "]: " . $db->ErrorMsg());
		
		$id = $db->Insert_ID();
		
	}
	else
	{
		$id = $result->fields['id'];
	}
	
	// Join 
	$sql = 'SELECT * FROM ubio_rss_name_ref_joiner WHERE (rss_id = ' . $item_id .') AND (name_id=' . $id . ') LIMIT 1';
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $db->ErrorMsg());
	if ($result->NumRows() == 0)
	{
		$sql = 'INSERT INTO ubio_rss_name_ref_joiner(rss_id, name_id, is_new) VALUES ('
			. $item_id
			. ',' . $id
			. ',' . $is_new
			. ')';
		//echo $sql;

		$result = $db->Execute($sql);
		if ($result == false) die("failed [" . __LINE__ . "]: " . $db->ErrorMsg());
	}
	
}

// debugging
if (0)
{

$filename = 'ubio.rss.xml';
$filename = 'u2.xml';
echo $filename, "\n";

$file = @fopen($filename, "r") or die("could't open file \"$filename\"");
$rss = @fread($file, filesize ($filename));
fclose($file);

//echo $rss;

// Transform to JSON
$xp = new XSLTProcessor();
$xsl = new DomDocument;
$xsl->load('xsl/ubiorss.xsl');
$xp->importStylesheet($xsl);

$xml_doc = new DOMDocument;
$xml_doc->loadXML($rss);

$json = $xp->transformToXML($xml_doc);

//echo $json;

$data = json_decode($json);
//print_r($data);
}
else
{
	ubio_fetch_rss('http://www.ubio.org/rss/rss_feed_nov.php?rss1=1', $data);
}

// Extract data...
$debug = true;
foreach ($data->items as $item)
{
	// We want article identifier (may need to do a lookup),
	// any taxa we can show are newly described, and lineage strings
	// for higher taxa. We also want any uBio IDs for the taxon names
	// extracted.

	// Clean HTML from text
	
	echo $item->title, "\n";
	echo $item->description, "\n";
	
	// Skip WoRMS
	if (preg_match('/http:\/\/marinespecies.org\//', $item->link)) continue;
	
	//----------------------------------------------------------------------------------------------
	// Get publication identifiers...

	// Ensure DOIs have 'doi:' prefix
	if (preg_match ('/^(10.[0-9]*\/(.*))/', $item->identifier))
	{
		$item->doi = 'doi:' . $item->identifier;
	}
	if (preg_match ('/^(doi:)/', $item->identifier))
	{
		$item->doi = str_replace('doi:', '', $item->identifier);
	}
	
	// Extract identifiers if missing (this get's PMIDs, the rest will need bioguid.info
	if ($item->identifier == '')
	{
		// echo no identifier
		if (preg_match('/&list_uids=(?<pmid>\d+)&/', $item->link, $matches))
		{
			print_r($matches);
			$item->pmid = $matches['pmid'];
		}
		
		if (!isset($item->doi))
		{
			if (isset($item->pmid))
			{
				// Look for DOI...
				$url = 'http://bioguid.info/openurl?id='
					. 'pmid:' . $item->pmid
					. '&display=json';
			}
			else
			{
				// Look up identifier based on URL
				$url = 'http://bioguid.info/openurl?id='
					. urlencode($item->link)
					. '&display=json';
			}
			
			echo $url . "\n";
			
			$j = json_decode(get($url));
			
			if ($debug)
			{
				print_r($j);
			}
			
			if ($j->status == 'ok')
			{
				if (isset($j->doi))
				{
					$item->doi = $j->doi;
				}
				if (isset($j->pmid))
				{
					$item->pmid = $j->pmid;
				}
				if (isset($j->hdl))
				{
					$item->hdl = $j->hdl;
				}
				if (isset($j->url))
				{
					$item->url = $j->url;
				}
			}
		}
	}	
	print_r($item);
	$item_id = store_item($item);
	
//	if ($item->pmid != '') exit();

	//-----------------------------------------------------------------------------------------------
	// Handle names


	// keywords are taxon names uBio has extracted from articles/abstracts	
	foreach ($item->keywords as $k)
	{
		echo $k, "\n";
		
		
	}
	
	// Do our thang
	
	$annotations = extract_new_names(strip_tags($item->title), $item->keywords);
	echo "Names--------------------\n";
	print_r($annotations);
	
	
	// store names
	foreach ($annotations as $k => $v)
	{
		// lookup name
		$namebankID = 0;
		$find = ubio_namebank_search_rest($k, false, true); 
		if (count($find) != 0)
		{
			$namebankID = $find[0];
		}
		
		// store
		echo $k . ' ' . $namebankID . ' ' . $v['rank'] . ' ' . $v['new'] . "\n";
		
		store_one_name($k,$namebankID,$v['rank'],$v['new'], $item_id); 
		
		// join to source
	}
	
	
	
	$lineages = extract_lineages(strip_tags($item->title), $item->keywords);
	echo "Lineages-----------------\n";
	print_r($lineages);
	

	
}


?>