<?php

require_once (dirname(__FILE__) . '/feed_maker.php');

$taxon_id = 2759;
if (isset($_GET['taxon_id']))
{
	$taxon_id = $_GET['taxon_id'];
}

$debug = 0;

//--------------------------------------------------------------------------------------------------
class BarcodeFeed extends FeedMaker
{

	function FeedId ()
	{
		$this->url = str_replace('<TAXON>', $this->title, $this->url);
		$this->title = 'Barcodes for NCBI taxon ' . $this->title;
		$this->id = md5($this->url);
	}

	//----------------------------------------------------------------------------------------------
	function Harvest()
	{
		global $debug;

		$xml = get($this->url);
		
		//echo $xml;
		
		$ids = array();
		
		$dom= new DOMDocument;
		$dom->loadXML($xml);
		$xpath = new DOMXPath($dom);
		$xpath_query = "//eSearchResult/IdList/Id";
		$nodeCollection = $xpath->query ($xpath_query);
		foreach($nodeCollection as $node)
		{
			$id = $node->firstChild->nodeValue;
			array_push ($ids, $id);
		}
		
		//print_r($ids);
		
		// Get GenBank records
		
		
		// Cache, store metadata, and make into RSS feed
		
		
		foreach ($ids as $id)
		{
			$url = 'http://bioguid.info/gi/' . $id . '&display=json';
			$json = get($url);
			
			$obj = json_decode($json);
			
			//print_r($obj);
			
			$item = new stdclass;
			$item->links = array();
			
			if (isset($obj->created))
			{
				$item->created = $obj->created;
			}
			if (isset($obj->updated))
			{
				$item->updated = $obj->updated;
			}
			if (isset($obj->source->latitude))
			{
				$item->latitude = $obj->source->latitude;
			}
			if (isset($obj->source->longitude))
			{
				$item->longitude = $obj->source->longitude;
			}
			
			$item->title = $obj->title;
			$item->description = $obj->description;
			$item->description .= '<br/>' . $obj->taxonomy;
			$item->link = 'http://www.ncbi.nlm.nih.gov/nuccore/' . $obj->gi;
			$item->id = $item->link;
			
			$item->description .= '<br/><a href="http://www.ncbi.nlm.nih.gov/nuccore/' . $item->title . '">' . $obj->title . '</a>';
			
			// Source taxon
			$txid = $obj->source->db_xref;
			$txid = str_replace('taxon:', '', $txid);
			array_push($item->links, array('taxon' =>  $txid));
			$item->description .= '<br/><a href="http://www.ncbi.nlm.nih.gov/Taxonomy/Browser/wwwtax.cgi?id=' . $txid . '">taxon:' . $txid . '</a>';
			
			
			$ref = $obj->references[0];
			if (isset($ref->doi))
			{
				array_push($item->links, array('doi' =>  $ref->doi));							
				$item->description .= '<br/><a href="http://dx.doi.org/' . $ref->doi . '">doi:' . $ref->doi . '</a>';
			}
			if (isset($ref->pmid))
			{
				array_push($item->links, array('pmid' =>  $ref->pmid));
			}
			
			
			//print_r($item);
			
			// Store
			$this->StoreFeedItem($item);
			
		}

	}

}


$url = 'http://eutils.ncbi.nlm.nih.gov/entrez/eutils/esearch.fcgi?db=nucleotide&term=barcode[keyword]+txid<TAXON>[Organism:exp]&retmax=10';

$f = new BarcodeFeed($url, $taxon_id, 1);
$f->WriteFeed();

?>