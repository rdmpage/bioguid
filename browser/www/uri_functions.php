<?php

// URI functions

require_once (dirname(dirname(__FILE__)) . '/triple_store.php');

//--------------------------------------------------------------------------------------------------
function get_canonical_uri(&$canonical_uri)
{
	// Triple store
	global $store_config;
	global $store;
	
	// Do we have this object in our triple store?
	$ntriples = num_triples_for_uri($canonical_uri);
	
	if (ntriples == 0)
	{
		// LSID may be present without HTTP proxy (i.e., rdf:about="urn:lsid:...)
		if (preg_match('/(?<lsid>urn:lsid:(.*))/', $canonical_uri, $matches))
		{
			$canonical_uri = $matches['lsid'];
			$ntriples = num_triples_for_uri($canonical_uri);			
		}	
	}
	
	// Pubmed may be present but primary identifier for article is DOI
	if (ntriples == 0)
	{
		if (preg_match('/(?<pmid>pmid:\d+)/', $canonical_uri, $matches))
		{
			$query = 'PREFIX dcterms: <http://purl.org/dc/terms/>
SELECT DISTINCT  ?s
WHERE 
{ 
 ?s dcterms:identifier <http://bioguid.info/' . $matches['pmid'] . '>
}';		
			$r = $store->query($query);
			if (count($r['result']['rows']) == 1)
			{
				$canonical_uri = $r['result']['rows'][0]['s'];
				$ntriples = num_triples_for_uri($canonical_uri);
			}
		}	
	}
	// GenBank accession numbers are primary id
	if (ntriples == 0)
	{
		if (preg_match('/(?<gi>gi:\d+)/', $canonical_uri, $matches))
		{
			$query = 'PREFIX owl: <http://www.w3.org/2002/07/owl#>
SELECT DISTINCT  ?s
WHERE 
{ 
 ?s owl:sameAs <http://bioguid.info/' . $matches['gi'] . '>
}';		
			$r = $store->query($query);
			if (count($r['result']['rows']) == 1)
			{
				$canonical_uri = $r['result']['rows'][0]['s'];
				$ntriples = num_triples_for_uri($canonical_uri);
			}
		}	
	}
	
	return $ntriples;
}


?>