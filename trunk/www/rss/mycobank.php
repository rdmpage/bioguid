<?php

require_once (dirname(__FILE__) . '/feed_maker.php');
require_once (dirname(__FILE__) . '/resolve.php');

//--------------------------------------------------------------------------------------------------
class MycobankFeed extends FeedMaker
{
	//----------------------------------------------------------------------------------------------
	function Harvest()
	{
		$xml = get($this->url);
		
		//echo $xml;
	
		// Convert Mycobank RSS to JSON
		$xp = new XsltProcessor();
		$xsl = new DomDocument;
		$xsl->load('xsl/rss2.xsl');
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
		
		// Extract details
		foreach ($obj->items as $i)
		{
			$item = new stdclass;
			$item->links = array();
			$item->link = $i->link;
			$item->description = $i->description;
			$item->id = $i->link;
		
			// Mycobank ID
			$id = $i->link;
			$id = str_replace('http://www.mycobank.org/MycoTaxo.aspx?Link=T&Rec=', '', $id);
			
			// Dates
			if (preg_match('/<br>Date of deposit:\s*(?<date>(.*))<br>/', $i->description, $matches))
			{	
				//print_r($matches);
				$item->created =  date("Y-m-d H:i:s", strtotime($matches['date']));
				$item->updated = $item->created;
			}
			
			$have_literature = false;
			
			// Taxon name
			if (preg_match('/<a href=(.*)><i>(?<name>(.*))<\/i><\/a>/', $i->description, $matches))
			{	
				$item->title = $matches['name'];
				
				//print_r($matches);
				
				// Index Fungorum search...
				
				$url = 'http://www.indexfungorum.org/IXFWebService/Fungus.asmx/NameSearch?SearchText=' 
					. str_replace(' ', '%20', $matches['name']) . '&AnywhereInText=false&MaxNumber=1';
				
				//echo $url . "\n";
				
				$xml = get($url);
				
				//echo $xml;
				
				$dom= new DOMDocument;
				$dom->loadXML($xml);
				$xpath = new DOMXPath($dom);
		
				$record_id = 0;
				$nodeCollection = $xpath->query ("//NewDataSet/IndexFungorum/RECORD_x0020_NUMBER");
				foreach($nodeCollection as $node)
				{
					// We have this name in Index Fungorum...
					$record_id = $node->firstChild->nodeValue;
					
					$lsid = 'urn:lsid:indexfungorum.org:names:' . $record_id;
					
					// Store LSID
					array_push($item->links, array('lsid' =>  $lsid));							
						$item->description .= '<br/><a href="http://bioguid.info/' . $lsid. '">' . $lsid . '</a>';
						
					// Get bibliographic details
					$rdf = ResolveGuid($lsid);
					//echo $rdf;
					
					$url = 'http://bioguid.info/openurl?genre=article';
					
					$d = new DOMDocument;
					$d->loadXML($rdf);
					$xpath = new DOMXPath($d);
					
					$n = $xpath->query("//tpub:title");
					foreach($n as $n2)
					{
						$url .= '&title=' . $n2->firstChild->nodeValue;
					}
					$n = $xpath->query("//tpub:volume");
					foreach($n as $n2)
					{
						$url .= '&volume=' . $n2->firstChild->nodeValue;
					}
					$n = $xpath->query("//tpub:pages");
					foreach($n as $n2)
					{
						$url .= '&pages=' . $n2->firstChild->nodeValue;
					}
					$n = $xpath->query("//tpub:year");
					foreach($n as $n2)
					{
						$url .= '&date=' . $n2->firstChild->nodeValue;
					}
					$url .= '&display=json';
					
					//echo $url;
					
					$ref = json_decode(get($url));
					
					if ($ref->status == 'ok')
					{
						$have_literature = true;
						
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
					else
					{
						// No joy...
					}
				}
				
/*				if (!$have_literature)
				{
					// Get from Mycobank, doesn't seem to work when I try and havest with CURL :(
					echo "Get lit\n";
					
					// Get literature from Mycobank
					
					// 1. Get Mycobank web page
					$url = $item->link;
					$html = get($url);
					
					echo "html=" . $html . "\n";
					echo "link=" . $item->link . "\n";
					
					if (preg_match("/<A href='MycoBiblio.aspx\?Link=T&Rec=(.*)'\s+/", $html, $matches))
					{
						print_r($matches);
					}					
					
					
				}
*/				
				$this->StoreFeedItem($item);
			}
		}
	}

}

$url = 'http://www.mycobank.org/Users/MycoBankNewsRSS.xml';
$f = new MycobankFeed($url, 'Mycobank',7);
$f->WriteFeed();

?>