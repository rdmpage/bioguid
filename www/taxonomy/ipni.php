<?php

// IPNI search functions

require_once('../lib.php');


class Ipni
{
	function Ipni()
	{
	}
	
	
	// Search for a name 
	function Search($name)
	{
		$query = $name;
		$query = str_replace(" ", "+", $query);
		$url = 'http://www.ipni.org:80/ipni/simplePlantNameSearch.do?output_format=delimited-short&find_wholeName=' . $query;
		$data = get($url);
		
		//echo $data;
		
		return $data;
		
		//$this->Process($data);
	
	}
	
	
	function Retrieve($id)
	{
		$url = 'http://www.ipni.org/ipni/idPlantNameSearch.do?output_format=delimited-extended&id=' . $id;
		
		//$url .= '';
		
		echo $url . "\n";
		
		$data = get($url);
		
		echo $data;
		
	}
	
	
	function RetrieveLsid($id)
	{
		$url = 'http://www.ipni.org/ipni/lsidMetadataPlantName?lsid=urn:lsid:ipni.org:names:' . $id;
		$data = get($url);
		
		echo $data;
		
		//$rdf = new simplexml_load_string($data);
		//print_r($rdf);
	}		
	
	function Recent($family, $date)
	{
 	 $url = 'http://www.ipni.org/ipni/advPlantNameSearch.do?'
 		. 'find_family=' . $family
 		. '&find_genus=&find_species=&find_infrafamily=&find_infragenus='
 		. '&find_infraspecies=&find_authorAbbrev=&find_includePublicationAuthors=on'
 		. '&find_includePublicationAuthors=off&find_includeBasionymAuthors=on'
 		. '&find_includeBasionymAuthors=off&find_publicationTitle=&show_extras=on'
 		. '&find_geoUnit=&find_addedSince=' . $date_added 
 		. '&find_modifiedSince='
 		. '&find_isAPNIRecord=on&find_isAPNIRecord=false&find_isGCIRecord=on'
 		. '&find_isGCIRecord=false&find_isIKRecord=on&find_isIKRecord=false'
 		. '&find_rankToReturn=all'
 		. '&output_format=delimited-short'
 		. '&find_sortByFamily=on&find_sortByFamily=off&query_type=by_query'
 		. '&back_page=plantsearch';
		echo $url;
		
		$data = get($url);
		
		echo $data;
		
	
	}
	
	function Process($data)
	{
		$rows = explode("\n", $data);
		foreach ($rows as $row)
		{
			$d = explode('%', $row);
			print_r($d);
		}
	}
	
	function LinkoutUrl2Id($url, $include_version = false)
	{
		$ids = array();
		
		if (preg_match('/find_wholeName=(.*)&output/', $url, $matches))
		{
			//echo $url;
			$data = $this->Search($matches[1]);
			$rows = explode("\n", trim($data));
			
			$headings = explode('%', $rows[0]);
			
			//print_r($headings);
			
			$row_count = 0;
			foreach ($rows as $row)
			{
				$d = explode('%', $row);
				if ($row_count > 0 )
				{
					$id = $d[0];
					if ($include_version)
					{
						$id .= ':' . $d[1];
					}
					array_push($ids, $id);
				}
				$row_count++;
			}
		}
		return $ids;
	}
	
}


/*

$ipni = new Ipni();

$ids = $ipni->LinkoutUrl2Id('http://www.ipni.org/ipni/plantsearch?query_type=by_query&find_wholeName=Coursetia+heterantha&output_format=normal&back_page=plantsearch');
print_r($ids);
*/

//$ipni->Retrieve('20012728-1');

//$ipni->RetrieveLsid('944651-1');

//$ipni->Search('Coursetia heterantha');


// ?genre=article&title=ustral. Syst. Bot.&volume=15&issue=5&year=2002

//$ipni->Recent('Moraceae', '2008-07-01');


?>