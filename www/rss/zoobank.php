<?php

require_once (dirname(__FILE__) . '/feed_maker.php');

//--------------------------------------------------------------------------------------------------
class ZoobankFeed extends FeedMaker
{
	//----------------------------------------------------------------------------------------------
	function Harvest()
	{
		//echo "|" . $this->url . "|";
		$html = get($this->url);
		
		//echo $html;
		
		
		// Extract LSIDs
		if (preg_match_all('/(
		href="\/(?<lsid>urn:lsid:zoobank.org:[a-z]+:[A-Z0-9]+\-[A-Z0-9]+\-[A-Z0-9]+\-[A-Z0-9]+\-[A-Z0-9]+)">)
		/x',  $html, $matches, PREG_PATTERN_ORDER))
		{
			//print_r($matches);
			
			foreach ($matches['lsid'] as $lsid)
			{
				$item = new stdclass;
			
				//Add elements to the feed item
				
				$item->title = $lsid;
				$item->id = $lsid;
				$item->link = 'http://zoobank.org/' . $lsid;
				$item->description = 'LSID for a nomenclatural act, publication, or author';
				$item->links = array();
				
				//array_push ($item->links, array('lsid' => $lsid) );				
				
				// Resolve LSID to get title...		
				
				// For now avoid authors as they are broken
				
				if (preg_match('/urn:lsid:zoobank.org:author:/', $lsid))
				{
				}
				else
				{
					$rdf = get('http://bioguid.info/lsid.php?lsid=' . $lsid . '&display=rdf');
					//echo $rdf;
					
					// extract title
					$title = '';
					$dom= new DOMDocument;
					$dom->loadXML($rdf);
					$xpath = new DOMXPath($dom);
					
					// Register namespaces as these don't occur in all the RDF documents
					$xpath->registerNamespace("dc",
						"http://purl.org/dc/elements/1.1/");					
					$xpath->registerNamespace("PublicationCitation",
						"http://rs.tdwg.org/ontology/voc/PublicationCitation#");					
					$xpath->registerNamespace("tpub",
						"http://rs.tdwg.org/ontology/voc/PublicationCitation#");					


					if (preg_match('/urn:lsid:zoobank.org:act/', $lsid))
					{
						// Act
						$nodeCollection = $xpath->query ("//dc:title");
						foreach($nodeCollection as $node)
						{
							$item->title = $node->firstChild->nodeValue;
						}
						$item->description = "Nomenclatural act";
							
						$nodeCollection = $xpath->query ("//tpub:url");
						foreach($nodeCollection as $node)
						{
							$url = $node->firstChild->nodeValue;
							if (preg_match('/^doi:\s*(.*)$/', $url, $match))
							{
								array_push ($item->links, array('doi' => $match[1]) );
							}
						}
	
						$nodeCollection = $xpath->query ("//tpub:PublicationCitation/dc:identifier");
						foreach($nodeCollection as $node)
						{
							$l = $node->firstChild->nodeValue;
							if (preg_match('/^urn:lsid:(.*)$/', $l, $match))
							{
								array_push ($item->links, array('lsid' => $l) );
							}
						}
					}
					else
					{
						// Publication
						$nodeCollection = $xpath->query ("//PublicationCitation:title");
						foreach($nodeCollection as $node)
						{
							$item->title = $node->firstChild->nodeValue;
						}
						
						// Description
						$nodeCollection = $xpath->query ("//PublicationCitation:authorship");
						foreach($nodeCollection as $node)
						{
							$item->description = $node->firstChild->nodeValue;
						}
						
						// Identifiers
						$nodeCollection = $xpath->query ("//PublicationCitation:url");
						foreach($nodeCollection as $node)
						{
							$url = $node->firstChild->nodeValue;
							if (preg_match('/^doi:\s*(.*)$/', $url, $match))
							{
								array_push ($item->links, array('doi' => $match[1]) );
								$item->description .= '<br/><a href="http://dx.doi.org/' . $match[1] . '">doi:' . $match[1] . '</a>';
							}
						}
						
					}

					


					
				}		
				
				//print_r($item);

				$this->StoreFeedItem($item);
				
			}
		}
		
	
	}

}


$url = 'http://zoobank.org';
$f = new ZoobankFeed($url, 'ZooBank Most Recent Entries');
$f->WriteFeed();

?>