<?php

require_once (dirname(__FILE__) . '/feed_maker.php');
require_once (dirname(__FILE__) . '/latlong.php');
require_once (dirname(__FILE__) . '/ref.php');

//--------------------------------------------------------------------------------------------------
class WikispeciesFeed extends FeedMaker
{
	//----------------------------------------------------------------------------------------------
	function Harvest()
	{
		$xml = get($this->url);
		
		// Extract records from RSS2 feed
		$dom= new DOMDocument;
		$dom->loadXML($xml);
		$xpath = new DOMXPath($dom);

		// Add namespaces to XPath to ensure our queries work
		$xpath->registerNamespace("rss", "http://purl.org/rss/1.0/");
		$xpath->registerNamespace("dc", "http://purl.org/dc/elements/1.1/");
		
		$nodeCollection = $xpath->query ("//item");
		foreach($nodeCollection as $node)
		{
			$item = new stdclass;
			
			$nc = $xpath->query ("title", $node);
			foreach ($nc as $n)
			{
				$item->title = $n->firstChild->nodeValue;
			}
			$nc = $xpath->query ("link", $node);
			foreach ($nc as $n)
			{
				$item->link = $n->firstChild->nodeValue;
				$item->id = $n->firstChild->nodeValue;
			}
			$nc = $xpath->query ("description", $node);
			foreach ($nc as $n)
			{
				$item->description = $n->firstChild->nodeValue;
			}
			$nc = $xpath->query ("pubDate", $node);
			foreach ($nc as $n)
			{
				$item->created = date("Y-m-d H:i:s", strtotime($n->firstChild->nodeValue));
				$item->updated = date("Y-m-d H:i:s", strtotime($n->firstChild->nodeValue));
			}
		
			// Get what we want...
			$item->links = array();
			
			// Extract details
			$rows = explode("\n", $item->description);
			
			foreach ($rows as $row)
			{
				// Database identifiers ------------------------------------------------------------

				// IPNI
				if (preg_match_all("/
					(\{\{IPNI\|(?<id>[0-9]+\-[123])\|([^\}\}]+|(?R))*\}\})
					/x", $row, $m))
				{
					array_push ($item->links, array('lsid' => 'urn:lsid:ipni.org:names:' . $m['id'][0]) );
				}

				// ITIS
				if (preg_match_all("/
					(\{\{ITIS\|(?<id>[0-9]+)([^\}\}]+|(?R))*\}\})
					/x", $row, $m))
				{
					array_push ($item->links, array('itis' => $m['id'][0]) );
				}

				// MSW
				if (preg_match_all("/
					(\{\{MSWsp\|(?<id>[0-9]+)([^\}\}]+|(?R))*\}\})
					/x", $row, $m))
				{
					array_push ($item->links, array('msw' => $m['id'][0]) );
				}
				
				// Index Fungorum
				if (preg_match_all("/
					(\[http:\/\/www.indexfungorum.org\/Names\/NamesRecord.asp\?RecordID=(?<id>[0-9]+)\s+)
					/x", $row, $m))
				{
					array_push ($item->links, array('lsid' => 'urn:lsid:indexfungorum.org:names:' . $m['id'][0]) );
				}
				
				
				// Locality information ------------------------------------------------------------
				// Geotagging
				// Type locality: Kenya, Kakamega Forest, 1590 m., 00º21'N, 034º51'E.<br />
				$row = str_replace("º", "°", $row);
				$row = str_replace("&#xBA;", "°", $row);

				// Type locality
				if (preg_match('/(?<degreesLatitude>\d+)°(?<minutesLatitude>\d+)\'(?<hemisphereLatitude>[S|N]),\s+(?<degreesLongitude>\d+)°(?<minutesLongitude>\d+)\'(?<hemisphereLongitude>[W|E])/', $row, $matches)) 
				{
					// latitude
					$seconds = 0;
					$minutes = 0;
					$degrees = $matches['degreesLatitude'];
					if (isset($matches['minutesLatitude']))
					{
						$minutes = $matches['minutesLatitude'];
					}
					if (isset($matches['secondsLatitude']))
					{
						$seconds = $matches['secondsLatitude'];
					}
					$latitude = degrees2decimal($degrees, $minutes, $seconds, $matches['hemisphereLatitude']);
					$latlong['latitude'] = $latitude;
			
					// longitude
					$seconds = 0;
					$minutes = 0;
					$degrees = $matches['degreesLongitude'];
					if (isset($matches['minutesLongitude']))
					{
						$minutes = $matches['minutesLongitude'];
					}
					if (isset($matches['secondsLongitude']))
					{
						$seconds = $matches['secondsLongitude'];
					}
					$longitude = degrees2decimal($degrees, $minutes, $seconds, $matches['hemisphereLongitude']);
					$latlong['longitude'] = $longitude;
					
					$item->latitude = $latlong['latitude'];
					$item->longitude = $latlong['longitude'];
					
				}
				
				
			}
				
			// Bibliographic references --------------------------------------------------------
			
			// extract reference block
			$in_refs = false;
			$ref_text = '';
			foreach ($rows as $row)
			{	
				if (preg_match('/^==\s*References\s*==/', $row))
				{
					$in_refs = true;
				}	
				
				if (preg_match('/^\*/', $row) && $in_refs)
				{
					$ref_text .= $row . "\n";
				}
			
				if (preg_match('/^==\s*^(References)\s*==/', $row))
				{
					$in_refs = false;
				}
			}
			
			$refs = explode("\n", trim($ref_text));

			$item->payload->bibliography = array();
			
			foreach ($refs as $ref)
			{
				// Need to add this to feedmaker to we add it to the feed
				array_push($item->payload->bibliography, $ref);
							
				/* Lookup reference guids, time consuming, maybe defer...
				$obj = new stdclass;
				$matched = parse_wikispecies_ref($ref, $obj, 0);
				
				if ($matched)
				{
					$url = 'http://bioguid.info/openurl/?genre=article';
					
					$url .= '&title=' . urlencode($obj->journal);
					$url .= '&volume=' . $obj->volume;
					$url .= '&spage=' . $obj->spage;
					$url .= '&date=' . $obj->year;
					$url .= '&display=json';

					$j = get($url);
					
					$ref = json_decode($j);
					
					if ($ref->status == 'ok')
					{
						if (isset($ref->doi))
						{
							array_push($item->links, array('doi' =>  $ref->doi));							
						}
						if (isset($ref->pmid))
						{
							array_push($item->links, array('pmid' =>  $ref->pmid));
						}
						if (isset($ref->hdl))
						{
							array_push($item->links, array('hdl' =>  $ref->hdl));
						}
						if (isset($ref->url))
						{
							array_push($item->links, array('url' =>  $ref->url));
						}
					}
				}
				
				*/
			}
			$this->StoreFeedItem($item);
		}
	}
}


$url = 'http://species.wikimedia.org/w/index.php?title=Special:NewPages&limit=100&feed=rss';
$f = new WikispeciesFeed($url, 'Wikispecies New Pages', 1);
$f->WriteFeed();

?>