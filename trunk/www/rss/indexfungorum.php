<?php

/**
 * @file indexfungorum.php
 *
 * Harvest new names added to Index Fungorum via their web service described at
 * http://www.indexfungorum.org/IXFWebService/Fungus.asmx
 *
 */

require_once (dirname(__FILE__) . '/feed_maker.php');
require_once (dirname(__FILE__) . '/resolve.php');

$debug = 0;

//--------------------------------------------------------------------------------------------------
class IndexFungorumFeed extends FeedMaker
{

	//----------------------------------------------------------------------------------------------
	function Harvest()
	{
		global $debug;
		
		$url = 'http://www.indexfungorum.org/IXFWebService/Fungus.asmx/NewNames?rank=sp.&startDate=';
		
		$d = date("Ymd", strtotime("now - 1 day"));
		
		$url .= $d;

		//echo $url;
		
		$xml = get($url);
		
		//echo $xml;
		
		if ($xml == '') return;
		
		// Extract LSIDs
		$dom= new DOMDocument;
		$dom->loadXML($xml);
		$xpath = new DOMXPath($dom);
		
		$lsids = array();

		$nodeCollection = $xpath->query ('//FungusNameLSID');
		foreach($nodeCollection as $node)
		{
			$lsid = $node->firstChild->nodeValue;
			
			array_push($lsids, $lsid);
		}
		
		//print_r($lsids);
		
		// Resolve LSIDs and extract bibliographic metadata (and try to resolve this)
		foreach ($lsids as $lsid)
		{
			$url = 'http://www.indexfungorum.org/IXFWebService/Fungus.asmx/NameByKeyRDF?NameLsid=' . $lsid;
			$rdf = get($url);
									
			if ($debug)
			{
				echo $rdf;
			}
			
			if ($rdf == '')
			{
			}
			else
			{
				$item = new stdclass;
			
			
				// extract extra details...
				$dom= new DOMDocument;
				$dom->loadXML($rdf);
				$xpath = new DOMXPath($dom);
				
				$xpath->registerNamespace("dc", "http://purl.org/dc/elements/1.1/");
				$xpath->registerNamespace("tpub", "http://rs.tdwg.org/ontology/voc/PublicationCitation#");
				$xpath->registerNamespace("tn", "http://rs.tdwg.org/ontology/voc/TaxonName#");	
				
				$nodeCollection = $xpath->query ("//tn:nameComplete");
				foreach($nodeCollection as $node)
				{
					$item->title = $node->firstChild->nodeValue;
					$item->description = $node->firstChild->nodeValue;
				}
				$nodeCollection = $xpath->query ("//tn:authorship");
				foreach($nodeCollection as $node)
				{
					$item->description .= ' ' . $node->firstChild->nodeValue;
				}
				$nodeCollection = $xpath->query ("//tn:year");
				foreach($nodeCollection as $node)
				{
					$item->description .= ' ' . $node->firstChild->nodeValue;
				}
				
				$item->id = $lsid;
				$item->link = str_replace('urn:lsid:indexfungorum.org:names:', 'http://www.indexfungorum.org/Names/NamesRecord.asp?RecordID=', $lsid);
	
	
	
				// Identifiers
				$item->links = array();
				
				
				$journal = '';
				$volume = '';
				$pages = '';
				$year = '';
				
				$nodeCollection = $xpath->query ("//tpub:title");
				foreach($nodeCollection as $node)
				{
					$journal = $node->firstChild->nodeValue;
				}
				$nodeCollection = $xpath->query ("//tpub:volume");
				foreach($nodeCollection as $node)
				{
					$volume = $node->firstChild->nodeValue;
				}
				$nodeCollection = $xpath->query ("//tpub:pages");
				foreach($nodeCollection as $node)
				{
					$pages = $node->firstChild->nodeValue;
				}
				$nodeCollection = $xpath->query ("//tpub:year");
				foreach($nodeCollection as $node)
				{
					$year = $node->firstChild->nodeValue;
				}
				
				if (
					($journal != '')
					&& ($volume != '')
					&& ($pages != '')
					)
				{
					$url = 'http://bioguid.info/openurl/?genre=article';
						
						$url .= '&title=' . urlencode($journal);
						$url .= '&volume=' . $volume;
						$url .= '&pages=' . $pages;
						$url .= '&date=' . $year;
						$url .= '&display=json';
						
						if ($debug)
						{
							echo $url;
						}
						
						$j = get($url);
						
						$ref = json_decode($j);
						
						if ($debug)
						{
							print_r($ref);
						}
						
						if ($ref->status == 'ok')
						{
							if (isset($ref->doi))
							{
								array_push($item->links, array('doi' =>  $ref->doi));							
								$item->description .= '<br/><a href="http://dx.doi.org/' . $ref->doi . '">doi:' . $ref->doi . '</a>';
							}
							if (isset($ref->pmid))
							{
								array_push($item->links, array('pmid' =>  $ref->pmid));
							}
							if (isset($ref->hdl))
							{
								array_push($item->links, array('hdl' =>  $ref->hdl));
								$item->description .= '<br/><a href="http://hdl.handle.net/' . $ref->hdl . '">doi:' . $ref->hdl . '</a>';
							}
							if (isset($ref->url))
							{
								array_push($item->links, array('url' =>  $ref->url));
								$item->description .= '<br/><a href="' . $ref->url . '">' . $ref->url . '</a>';
							}
						}
						
						
				}
				
				//print_r($item);
				$this->StoreFeedItem($item);
	
			}
		}
	}

}


$url = 'http://www.indexfungorum.org';

$f = new IndexFungorumFeed($url, 'Index Fungorum New Names', 1);
$f->WriteFeed();

?>