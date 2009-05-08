<?php

require_once (dirname(__FILE__) . '/feed_maker.php');
require_once (dirname(__FILE__) . '/ref.php');
require_once('resolve.php');

$family = '';
if (isset($_GET['family']))
{
	$family = $_GET['family'];
}

$debug = 0;

//--------------------------------------------------------------------------------------------------
class IpniFeed extends FeedMaker
{

	function FeedId ()
	{
		$this->id = md5($this->url . $this->title);
	}

	//----------------------------------------------------------------------------------------------
	function Harvest()
	{
		global $debug;
		
		//echo "|" . $this->url . "|";
		//$html = get($this->url);
		
		//echo $html;
		
		$url = 'http://www.ipni.org/ipni/advPlantNameSearch.do?find_family='
		 . $this->title 
		 . '&find_genus=&find_species=&find_infrafamily=&find_infragenus=&find_infraspecies=&find_authorAbbrev=&find_includePublicationAuthors=on&find_includePublicationAuthors=off&find_includeBasionymAuthors=on&find_includeBasionymAuthors=off&find_publicationTitle=&show_extras=on&find_geoUnit=&find_addedSince='
		 . $d = date("Y-m-d", strtotime("now - 2 months")) 
		 . '&find_modifiedSince=&find_isAPNIRecord=on&find_isAPNIRecord=false&find_isGCIRecord=on&find_isGCIRecord=false&find_isIKRecord=on&find_isIKRecord=false&find_rankToReturn=all&output_format=delimited-minimal&find_sortByFamily=on&find_sortByFamily=off&query_type=by_query&back_page=plantsearch';
		 
		 //echo $url;
		
		$text = 'Id%Version%Family%Full name without family and authors%Authors
77096980-1%1.2%Begoniaceae%Begonia hekensis%D.C.Thomas
77097937-1%1.1%Begoniaceae%Begonia mysteriosa%L.Kollmann & A.P.Fontana
77096979-1%1.1%Begoniaceae%Begonia ozotothrix%D.C.Thomas';

		$text = get($url);
		$text = trim($text);
		
/*$text='Id%Version%Family%Full name without family and authors%Authors
60451177-2%1.1%Euphorbiaceae%Croton subgen. Geiseleria%(Klotzsch) A.Gray
77097911-1%1.1%Euphorbiaceae%Croton pallidulus var. glabrus%L.R.Lima
77097476-1%1.1%Euphorbiaceae%Euphorbia confinalis subsp. rhodesiaca%L.C.Leach
77097491-1%1.1%Euphorbiaceae%Euphorbia maryrichardsiae%G.Will.
77098208-1%1.2%Euphorbiaceae%Euphorbia ohiva%Swanepoel
60451526-2%1.1.2.1%Euphorbiaceae%Luntia%Neck. ex Raf.';	*/	
		
		if ($debug)
		{
			echo $url . "\n";
			echo $text . "\n";
		}

		// Get array of individual lines
		$lines = explode ("\n", $text);
				
		// Extract headings from first line
		$parts = explode ("%", $lines[0]);
		$size=count($parts);
		$heading = array();
		for ($i=0; $i < $size; $i++)
		{
			$heading[$parts[$i]] = $i;
		}
		
		// Read each remaining line				
		$size=count($lines);
		for ($i=1; $i < $size; $i++)
		{
			$parts = explode ("%", $lines[$i]);
			
			//print_r($parts);
	
			$item = new stdclass;
		
			//Add elements to the feed item
			$lsid = 'urn:lsid:ipni.org:names:' . $parts[$heading["Id"]];
			$item->title = $parts[$heading["Full name without family and authors"]];
			$item->id = $lsid;
			$item->link = 'http://www.ipni.org/ipni/idPlantNameSearch.do?id=' . $parts[$heading["Id"]];
			$item->description = '<i>' . $parts[$heading["Full name without family and authors"]] . '</i> ' . $parts[$heading["Authors"]];
			
			$item->description = str_replace('subsp.', '</i>subsp.<i>', $item->description);
			$item->description = str_replace('var.', '</i>var.<i>', $item->description);
			
			// Identifiers
			$item->links = array();
			
			// tag
			//  $parts[$heading["Family"]]
	
			// retrieve metadata...
			
			$rdf =  ResolveGuid($lsid);
			
			// Fix IPNI bug
			$rdf = preg_replace('/ & /', ' &amp; ', $rdf);
			
			if ($debug)
			{
				echo $rdf;
			}
			
			//echo $rdf;
			
			// extract extra details...
			$dom= new DOMDocument;
			$dom->loadXML($rdf);
			$xpath = new DOMXPath($dom);
		
			$nodeCollection = $xpath->query ("//tcom:publishedIn");
			foreach($nodeCollection as $node)
			{
				$publishedIn = $node->firstChild->nodeValue;
				$item->description .= '<br/>' . $publishedIn;
				
				// Can we get any GUIDs for this...?
				
				$matches = array();
				if (parse_ipni_ref($publishedIn , $matches))
				{
					//print_r($matches);
					
					// we parsed it OK, now find guid...
					$url = 'http://bioguid.info/openurl/?genre=article';
					
					$url .= '&title=' . urlencode($matches['journal']);
					$url .= '&volume=' . $matches['volume'];
					$url .= '&pages=' . $matches['page'];
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
						// No guid found, but we did parse it OK...
						
						
						
					}
				}
				else
				{
					// Don't understand this reference at all...
				}		
			}
			$nodeCollection = $xpath->query ("//dcterms:created");
			foreach($nodeCollection as $node)
			{
				$item->created =  $node->firstChild->nodeValue;
			}
			$nodeCollection = $xpath->query ("//dcterms:modified");
			foreach($nodeCollection as $node)
			{
				$item->updated = $node->firstChild->nodeValue;
			}
			
			//print_r($item);
			$this->StoreFeedItem($item);
		}		
	}

}


$url = 'http://www.ipni.org';

$f = new IpniFeed($url, $family, 30);
$f->WriteFeed();

?>