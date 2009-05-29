<?php

require_once (dirname(__FILE__) . '/feed_maker.php');
require_once (dirname(__FILE__) . '/nameparse.php');
require_once (dirname(__FILE__) . '/resolve.php');

//--------------------------------------------------------------------------------------------------
class PlaziFeed extends FeedMaker
{
	//----------------------------------------------------------------------------------------------
	function Harvest()
	{
		$xml = get($this->url);
		
		//echo $xml;
	
		// Convert Plazi RSS to JSON
		$xp = new XsltProcessor();
		$xsl = new DomDocument;
		$xsl->load('xsl/rss1.xsl');
		$xp->importStylesheet($xsl);
				
		
		$xml_doc = new DOMDocument;
		$xml_doc->loadXML($xml);
		
		$json = $xp->transformToXML($xml_doc);
		
		// replace carriage returns and end of lines, which break JSON
		$json = str_replace("\n", " ", $json);
		$json = str_replace("\r", " ", $json);
		//echo $json;
		
		$obj = json_decode($json);
		
		//print_r($obj);
		
		// Extract details
		foreach ($obj->items as $i)
		{
			//echo $i->link . "\n";
			
			$item = new stdclass;
		
			//Add elements to the feed item
			
			$item->title = $i->title;
			$item->id = $i->link;
			$item->link = $i->link;
			$item->description = $i->description;
			$item->links = array();
			
			
			// Fetch record from DSpace using identfier based on handle
			
			$handle = $i->link;
			$handle = str_replace('http://hdl.handle.net/', '', $handle);
			
			// Store handle
			array_push ($item->links, array('hdl' => $handle) );			
			
			$url = 'http://plazi.org:8080/dspace-oai/request?verb=GetRecord&identifier=oai:plazi.org:' . $handle . '&metadataPrefix=oai_dc';
			
			$oai_xml = get($url);
			
			$oai_xml = str_replace('xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/ http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd"', '', $oai_xml);
			
			//echo $oai_xml;
			
			// extract what we need
	
			$dom = new DOMDocument;
			$dom->loadXML($oai_xml);
			$xpath = new DOMXPath($dom);
			
			// Register namespaces
			$xpath->registerNamespace("dc",
				"http://purl.org/dc/elements/1.1/");
			$xpath->registerNamespace("oai",
				"http://www.openarchives.org/OAI/2.0/");
						
			// Date of record
			$n = $xpath->query("//oai:datestamp");
			foreach($n as $n2)
			{
				$item->updated = date("Y-m-d H:i:s", strtotime($n2->firstChild->nodeValue));
			}
			
			// Bibliographic details (mangled, sigh)
			$biblio = new stdclass;
			
			$biblio->hdl = $handle;
			
			$type = '';
			$n = $xpath->query("//dc:type");
			foreach($n as $n2)
			{
				$type = $n2->firstChild->nodeValue;
				//echo $type . "\n";
			}

			$n = $xpath->query("//dc:title");
			foreach($n as $n2)
			{
				$biblio->atitle = $n2->firstChild->nodeValue;
			}
			
			// Authors
			$biblio->authors = array();
			$n = $xpath->query("//dc:creator");
			foreach($n as $n2)
			{
			
				$value = trim($n2->firstChild->nodeValue);
				
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
				
				array_push($biblio->authors, $author);
			}
			
			// Subjects
			$biblio->tags = array();
			$n = $xpath->query("//dc:subject");
			foreach($n as $n2)
			{
				array_push($biblio->tags,  $n2->firstChild->nodeValue);
			}
			
						
			// Volume 
			$n = $xpath->query("//dc:relation[1]");
			foreach($n as $n2)
			{
				if (preg_match('/(?<volume>[0-9]+)(\((?<issue>[0-9]+)\))/', $n2->firstChild->nodeValue, $matches))
				{
					$biblio->volume = $matches['volume'];
					$biblio->issue = $matches['issue'];
				}
				else
				{			
					$biblio->volume = $n2->firstChild->nodeValue;
				}
			}
			$n = $xpath->query("//dc:relation[2]");
			foreach($n as $n2)
			{
				$biblio->title =  $n2->firstChild->nodeValue;
			}
			
			$n = $xpath->query("//dc:relation[3]");
			foreach($n as $n2)
			{
				$value = $n2->firstChild->nodeValue;
				
				if (preg_match('/pp.\s*(?<spage>[0-9]+)\-(?<epage>[0-9]+)/', $value, $matches))
				{
					$biblio->spage = $matches['spage'];
					$biblio->epage = $matches['epage'];
				}
				if (preg_match('/.pdf$/', $value))
				{
					$biblio->pdf = $value;
				}
			}
			$n = $xpath->query("//dc:relation[4]");
			foreach($n as $n2)
			{
				$value = $n2->firstChild->nodeValue;
				
				if (preg_match('/pp.\s*(?<spage>[0-9]+)\-(?<epage>[0-9]+)/', $value, $matches))
				{
					$biblio->spage = $matches['spage'];
					$biblio->epage = $matches['epage'];
				}
				if (preg_match('/.pdf$/', $value))
				{
					$biblio->pdf = $value;
				}
				
			}
			$n = $xpath->query("//dc:date[3]");
			foreach($n as $n2)
			{
				$biblio->year = $n2->firstChild->nodeValue;
			}
			
			// Look for existing identifiers
			if (isset($biblio->title)
			 && isset($biblio->volume)
			 && isset($biblio->spage))
			 {
				$url = 'http://bioguid.info/openurl/?genre=article';
				
				$url .= '&title=' . urlencode($biblio->title);
				$url .= '&volume=' . $biblio->volume;
				$url .= '&pages=' . $biblio->spage;
				$url .= '&display=json';
				//echo $url;
				
				$j = get($url);
				
				$ref = json_decode($j);
				//print_r($ref);
				
				if ($ref->status == 'ok')
				{
					if (isset($ref->doi))
					{
						array_push($item->links, array('doi' =>  $ref->doi));	
						$item->description .= '<br/><a href="http://dx.doi.org/' . $ref->doi . '">doi:' . $ref->doi . '</a>';
						$biblio->doi = $ref->doi;
					}
					if (isset($ref->url))
					{
						array_push($item->links, array('url' =>  $ref->url));
						$biblio->url = $ref->url;
					}
				}				
			}
			
			//print_r($biblio);
			
			$item->payload = $biblio;
		
			$this->StoreFeedItem($item);
		}
	}

}

$url = 'http://plazi.org:8080/dspace/feed/rss_1.0/10199/12';
$f = new PlaziFeed($url, 'Plazi',1);
$f->WriteFeed();

//http://plazi.org:8080/dspace-oai/request?verb=GetRecords&identifier=oai:plazi.org:10199/3504&metadataPrefix=oai_dc

?>