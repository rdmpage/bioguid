<?php

require_once (dirname(__FILE__) . '/feed_maker.php');
require_once (dirname(__FILE__) . '/ubio_findit.php');


//--------------------------------------------------------------------------------------------------
class uBioRSSFeed extends FeedMaker
{
	//----------------------------------------------------------------------------------------------
	function Harvest()
	{
		$xml = get($this->url);
		
		//echo $xml;
	
		// Convert uBio RSS to JSON
		$xp = new XsltProcessor();
		$xsl = new DomDocument;
		$xsl->load('xsl/ubiorss.xsl');
		$xp->importStylesheet($xsl);
				
		// replace carriage returns and end of lines, which break JSON
		$xml = str_replace("\n", " ", $xml);
		$xml = str_replace("\r", " ", $xml);
		
		$xml_doc = new DOMDocument;
		$xml_doc->loadXML($xml);
		
		$json = $xp->transformToXML($xml_doc);
		
		//echo $json;
		
		$obj = json_decode($json);
		//print_r($obj);
		
		$count = 0;
		
		// Extract details
		foreach ($obj->items as $i)
		{		
			// Skip WoRMS
			if (preg_match('/http:\/\/(www.)?marinespecies.org\//', $i->link))
			{
			}
			else
			{
			
				$item = new stdclass;
				$item->links = array();
				$item->link = $i->link;
				$item->description = strip_tags($i->description);
				$item->id = $i->link;
				
				$item->title = $i->title;
				
				
				// Taxonomic names stored as tags in the payload
				$item->payload = new stdclass;
				$item->payload->tags = array();
				$item->payload->tagids = array();
				
				foreach ($i->keywords as $k)
				{
					array_push($item->payload->tags, $k);
					
					// uBio lookup takes too long...
					/*
					// get identifier			
					$names = ubio_findit($k);
				
					foreach ($names as $n)
					{
						foreach ($n as $k => $v)
						{
							switch ($k)
							{
								case 'namebankID':
									array_push($item->payload->tagids, 'urn:lsid:ubio.org:namebank:' . $v);
									break;
								default:
									break;
							}
						}
					}
					*/
				}
				
				
				
				// Publication identifiers
				$item->links = array();
				
				$doi = '';
				$pmid = '';
						
				// DOIs
				if (preg_match ('/^(10.[0-9]*\/(.*))/', $item->identifier))
				{
					$doi = $item->identifier;
					array_push($item->links, array('doi' =>  $item->identifier));
					$item->description .= '<br/><a href="http://dx.doi.org/' . $item->identifier . '">doi:' . $item->identifier . '</a>';
					
				}
				if (preg_match ('/^(doi:)/', $item->identifier))
				{
					$doi = str_replace('doi:', '', $item->identifier);
					array_push($item->links, array('doi' =>  $doi));
					$item->description .= '<br/><a href="http://dx.doi.org/' . $doi . '">doi:' . $doi . '</a>';
				}
				
				// Extract identifiers if missing (this get's PMIDs, the rest will need bioguid.info
				if ($item->identifier == '')
				{
					// echo no identifier
					if (preg_match('/&list_uids=(?<pmid>\d+)&/', $item->link, $matches))
					{
						$pmid = $matches['pmid'];
						array_push($item->links, array('pmid' =>  $pmid));
					}
					if ($doi == '')
					{
						if ($pmid != '')
						{
							// Look for DOI...
							$url = 'http://bioguid.info/openurl?id='
								. 'pmid:' . $pmid
								. '&display=json';
						}
						else
						{
							// Look up identifier based on URL
							$url = 'http://bioguid.info/openurl?id='
								. urlencode($item->link)
								. '&display=json';
						}
						
						$j = json_decode(get($url));
						
						if ($debug)
						{
							print_r($j);
						}
						
						if ($j->status == 'ok')
						{
							if (isset($j->doi))
							{
								array_push($item->links, array('doi' =>  $j->doi));
								$item->description .= '<br/><a href="http://dx.doi.org/' . $j->doi . '">doi:' . $j->doi . '</a>';
							}
							if (isset($j->pmid))
							{
								if ($pmid == '') // we don't have a PMID already...
								{
									array_push($item->links, array('pmid' =>  $j->pmid));
									$item->description .= '<br/><a href="http://www.ncbi.nlm.nih.gov/pubmed/' . $j->pmid . '">pmid:' . $j->pmid . '</a>';
								}
							}
							if (isset($j->hdl))
							{
								array_push($item->links, array('hdl' =>  $j->hdl));
								$item->description .= '<br/><a href="http://hdl.handle.net/' . $j->hdl . '">hdl:' . $j->hdl . '</a>';
								$item->hdl = $j->hdl;
							}
							if (isset($j->url))
							{
								array_push($item->links, array('url' =>  $j->url));
								$item->description .= '<br/><a href="' . $j->url . '">' . $j->url . '</a>';
							}
						}
					}
				}	
		
				// tags
				$item->description .= '<br/>';
				foreach ( $item->payload->tags as $tag )
				{
					$item->description .= '<b>' . $tag . '</b><br/>';
				}
				
				
				$this->StoreFeedItem($item);
				
				//print_r($item);
				/*echo '<div style="border:1px solid black;">';
				print_r($item);
				echo '</div>';*/
			}
		}
	}

}

$url = 'http://www.ubio.org/rss/rss_feed_nov.php?rss1=1';
$f = new uBioRSSFeed($url, 'uBioRSS.Novum',1);
$f->WriteFeed();

?>