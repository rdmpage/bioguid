<?php

require_once ('../triple_store.php');
require_once (dirname(__FILE__) . '/html.php');
require_once (dirname(__FILE__) . '/uri_functions.php');

// http://snipplr.com/view/231/dead-centre-a-div/

//--------------------------------------------------------------------------------------------------
function append_xml (&$dom, $xml)
{
	if ($xml != '')
	{
		$extraDom = new DOMDocument;
		$extraDom->loadXML($xml);
		$n = $dom->importNode($extraDom->documentElement, true);
		$dom->documentElement->appendChild($n);
	}
}

//--------------------------------------------------------------------------------------------------
function query_articles_from_journal ($uri)
{
	global $store_config;
	global $store;

	$xml = '';

	$sparql = '
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX dcterms: <http://purl.org/dc/terms/>

CONSTRUCT 
{
      ?article dcterms:isPartOf <' . $uri . '> .
  ?article rdf:type ?type .
 ?article dcterms:title ?title 

}

WHERE 
{ 
   ?article dcterms:isPartOf <' . $uri . '> .
  ?article rdf:type ?type .
 ?article dcterms:title ?title 

}';


	$r = $store->query($sparql);
	$index = $r['result'];
	$parser = ARC2::getRDFParser();
	$xml = $parser->toRDFXML($index);
	
	return $xml;
}


//--------------------------------------------------------------------------------------------------
// find sequences linked to a specimen
function query_sequences_from_specimen ($uri)
{
	global $store_config;
	global $store;

	$xml = '';
	
	$sparql = '
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX dcterms: <http://purl.org/dc/terms/>
PREFIX uniprot: <http://purl.uniprot.org/core/>
CONSTRUCT 
{
	?s dcterms:title ?o . 
	?s rdf:type <http://purl.uniprot.org/core/Molecule>
}
WHERE 
{ 
	?s dcterms:relation <' . $uri . '> .
	?s rdf:type <http://purl.uniprot.org/core/Molecule> .
	?s dcterms:title ?o
}';



	$r = $store->query($sparql);
	$index = $r['result'];
	$parser = ARC2::getRDFParser();
	$xml = $parser->toRDFXML($index);
	
	return $xml;
}

//--------------------------------------------------------------------------------------------------
// find publications linked to specimen via sequence
function query_publications_from_specimen ($uri)
{
	global $store_config;
	global $store;

	$xml = '';
	
	$sparql = '
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX dcterms: <http://purl.org/dc/terms/>
PREFIX bibo: <http://purl.org/ontology/bibo/>

CONSTRUCT 
{
  ?pub bibo:doi ?o .
?pub dcterms:title ?doi .
?pub rdf:type ?t .
}
WHERE
{
   ?s dcterms:relation <' . $uri . '> .
?s dcterms:isReferencedBy ?pub . 
?pub rdf:type ?t .
?pub bibo:doi ?o .
?pub dcterms:title ?doi .
  
}';

//echo $sparql;

	$r = $store->query($sparql);
	$index = $r['result'];
	$parser = ARC2::getRDFParser();
	$xml = $parser->toRDFXML($index);
	
	return $xml;
}

//--------------------------------------------------------------------------------------------------
// find sequences for taxon 
function query_sequences_from_taxon ($uri)
{
	global $store_config;
	global $store;

	$xml = '';
	$sparql = '
PREFIX geo: <http://www.w3.org/2003/01/geo/wgs84_pos#>
PREFIX dcterms: <http://purl.org/dc/terms/>

CONSTRUCT 
{
 ?gb rdf:type <http://purl.uniprot.org/core/Molecule> .
?gb dcterms:title ?accession . 
 }
WHERE 
{ 
   ?gb dcterms:subject <' . $uri . '> .
 ?gb dcterms:title ?accession
 
}';

	$r = $store->query($sparql);
	$index = $r['result'];
	$parser = ARC2::getRDFParser();
	$xml = $parser->toRDFXML($index);
	
	return $xml;
}

//--------------------------------------------------------------------------------------------------
// find publications for taxon 
function query_publications_from_taxon ($uri)
{
	global $store_config;
	global $store;

	$xml = '';
	$sparql = '
PREFIX dcterms: <http://purl.org/dc/terms/>
PREFIX bibo: <http://purl.org/ontology/bibo/>


CONSTRUCT 
{
  ?pub bibo:doi ?o .
?pub dcterms:title ?doi .
?pub rdf:type ?t .
}
WHERE
{
    ?gb dcterms:subject <' . $uri . '> .
 ?gb dcterms:title ?accession .
?gb dcterms:isReferencedBy ?pub .
?pub rdf:type ?t .
?pub bibo:doi ?o .
?pub dcterms:title ?doi .
}';


	$r = $store->query($sparql);
	$index = $r['result'];
	$parser = ARC2::getRDFParser();
	$xml = $parser->toRDFXML($index);
	
	return $xml;
}

//--------------------------------------------------------------------------------------------------
// find localities for taxon based on localities for sequences (and specimens linked to sequences)
function query_localities_from_taxon ($uri)
{
	global $store_config;
	global $store;

	$xml = '';
	$sparql = '
	PREFIX geo: <http://www.w3.org/2003/01/geo/wgs84_pos#>
PREFIX dcterms: <http://purl.org/dc/terms/>

CONSTRUCT 
{
   ?specimen geo:lat ?lat . 
   ?specimen geo:long ?long .
 ?specimen rdf:type <http://rs.tdwg.org/ontology/voc/TaxonOccurrence#TaxonOccurrence> .
}
WHERE 
{ 
   ?gb dcterms:subject <' . $uri . '> .
 
 ?gb dcterms:relation ?specimen .
?specimen geo:lat ?lat .
?specimen geo:long ?long
}';

	$r = $store->query($sparql);
	$index = $r['result'];
	$parser = ARC2::getRDFParser();
	$xml = $parser->toRDFXML($index);
	
	return $xml;
}

//--------------------------------------------------------------------------------------------------
// find sequences published by this paper from GenBank records
function query_sequences_from_publication ($uri)
{
	global $store_config;
	global $store;

	$xml = '';
	$sparql = '
	PREFIX dcterms: <http://purl.org/dc/terms/>

CONSTRUCT 
{
   ?gb dcterms:isReferencedBy <' . $uri . '> .
?gb rdf:type ?type .
 ?gb dcterms:title ?title .
}
WHERE 
{ 
  ?gb dcterms:isReferencedBy <' . $uri . '> .
 ?gb rdf:type ?type .
 ?gb dcterms:title ?title .
}';

	$r = $store->query($sparql);
	$index = $r['result'];
	$parser = ARC2::getRDFParser();
	$xml = $parser->toRDFXML($index);
	
	return $xml;
}

//--------------------------------------------------------------------------------------------------
// find localities for taxon based on localities for sequences (and specimens linked to sequences)
function query_localities_from_publication ($uri)
{
	global $store_config;
	global $store;

	$xml = '';
	$sparql = '
	PREFIX geo: <http://www.w3.org/2003/01/geo/wgs84_pos#>
PREFIX dcterms: <http://purl.org/dc/terms/>

CONSTRUCT 
{
   ?specimen geo:lat ?lat . 
   ?specimen geo:long ?long .
 ?specimen rdf:type <http://rs.tdwg.org/ontology/voc/TaxonOccurrence#TaxonOccurrence> .
}
WHERE 
{ 
   ?gb dcterms:isReferencedBy <' . $uri . '> .
 
 ?gb dcterms:relation ?specimen .
?specimen geo:lat ?lat .
?specimen geo:long ?long
}';

	$r = $store->query($sparql);
	$index = $r['result'];
	$parser = ARC2::getRDFParser();
	$xml = $parser->toRDFXML($index);
	
	return $xml;
}

//--------------------------------------------------------------------------------------------------
function main($uri)
{
	global $config;

	// Triple store
	global $store_config;
	global $store;
	
	$uri = urldecode($uri);	
	$ntriples = get_canonical_uri($uri);
	
	//echo $ntriples; exit();
		
	if ($ntriples == 0)
	{
		// Fetch URI
		echo '<html>';
		echo '<body>';
		echo  'Sorry, don\'t have this URI <b>' . $uri . '</b>, trying to fetch it...';
		
		$uri_to_fetch = $uri;
		if (preg_match('/^urn:lsid:/', $uri_to_fetch))
		{
			$uri_to_fetch = 'http://bioguid.info/' . $uri_to_fetch;
		}
		echo $uri_to_fetch;
		
		// can we get it, if so redirect...
		$query = "LOAD <" . $uri_to_fetch . ">";
		$r = $store->query($query);
		
		/*echo $query;
		
		echo '<pre>';
		print_r($r);
		echo '</pre>';
		exit(); */
		
		if ($r['result']['t_count'] > 0)
		{
			// Got it, redirect to web page for this URI
			echo '<script type="text/javascript">';
			echo 'document.location="' . $config['web_root'] . 'uri/' . $uri . '";';
			echo '</script>';
		}
		else
		{
			// Bugger...
			echo "Badness happened";
		}
		echo '</body>';
		echo '</html>';
	}
	else
	{
		// Display info about this object (having issues with CONSTRUCT not returning language codes!?)
	
		$sparql = "
CONSTRUCT
{
	<$uri> ?o ?p
}

WHERE 
{ 
	<$uri> ?o ?p
}
";

		$sparql = "DESCRIBE <$uri>";

		//echo $sparql . "\n";
	
		// get object
		$r = $store->query($sparql);
		$index = $r['result'];
		$parser = ARC2::getRDFParser();
		$rdfxml_doc = $parser->toRDFXML($index);
		
		//echo $rdfxml_doc;

		// What type if this?
		$dom= new DOMDocument;
		$dom->loadXML($rdfxml_doc);
		$xpath = new DOMXPath($dom);
		
		$xpath->registerNamespace("rdf", "http://www.w3.org/1999/02/22-rdf-syntax-ns#");
		$xpath->registerNamespace("dcterms", "http://purl.org/dc/terms/");
							
		$type = array();
		$xsl_filename = '';
		$html = '';
		
		$topic_title = '';

		//------------------------------------------------------------------------------------------
		// Get type(s) of objects
		$name = '';
		$nodeCollection = $xpath->query ('//rdf:type/@rdf:resource');
		foreach($nodeCollection as $node)
		{
			$type[] = $node->firstChild->nodeValue;
		}
		
		//------------------------------------------------------------------------------------------
		// Post process objects...
		
		// Publication add sequences...
		// possibe relations are isReferencedBy (stated in GenBank record) and references
		// which is stated in publication if we have links via PubMed.
		if (in_array('http://purl.org/ontology/bibo/Article', $type))
		{
			$topic_title = get_title ($uri);		
		
			// Sequences
			$xml = query_sequences_from_publication($uri);
			append_xml ($dom, $xml);
			
			// Taxa
			
			// Geography
			$xml = query_localities_from_publication($uri);
			append_xml ($dom, $xml);

		}
		
		// Journal
		
		// Journal
		if (in_array('http://purl.org/ontology/bibo/Journal', $type))
		{
			$topic_title = get_title($uri);
			$xml = query_articles_from_journal($uri);
			append_xml ($dom, $xml);
		}
		
		
				
		
		// GenBank: Add specimen if we have it...
		if (in_array('http://purl.uniprot.org/core/Molecule', $type))
		{
			$topic_title = get_title ($uri);		

			$specimen_uri = '';
			$nodeCollection = $xpath->query ('//dcterms:relation/@rdf:resource');
			foreach($nodeCollection as $node)
			{
				$specimen_uri = $node->firstChild->nodeValue;
			}			
			
			if ($specimen_uri != '')
			{
				// Fetch RDF
				$r = describe($specimen_uri);
				$index = $r['result'];
				$extraXml = $parser->toRDFXML($index);

				// Load into current DOM
				$extraDom = new DOMDocument;
				$extraDom->loadXML($extraXml);
				$n = $dom->importNode($extraDom->documentElement, true);
				// Append to root node
				$dom->documentElement->appendChild($n);
			}			
		}
		
		// Specimen
		if (in_array('http://rs.tdwg.org/ontology/voc/TaxonOccurrence#TaxonOccurrence', $type))
		{
			// Get sequences from this specimen
			
			$xml = query_sequences_from_specimen($uri);
			append_xml ($dom, $xml);

			$xml = query_publications_from_specimen($uri);
			append_xml ($dom, $xml);
			
		}
		
		// NCBI taxon
		if (in_array('http://rs.tdwg.org/ontology/voc/TaxonConcept#TaxonConcept', $type))
		{
			$topic_title = get_title($uri, '<http://rs.tdwg.org/ontology/voc/TaxonConcept#nameString>');
		
			// Get sequences from this specimen
			
			$xml = query_sequences_from_taxon($uri);
			append_xml ($dom, $xml);

			$xml = query_localities_from_taxon($uri);
			append_xml ($dom, $xml);

			$xml = query_publications_from_taxon($uri);
			append_xml ($dom, $xml);
			
		}
		
		
		// Dbpedia
		if (in_array('http://www.opengis.net/gml/_Feature', $type))
		{
			$topic_title = get_title($uri, 'rdfs:label', 'en');
		}
		
		if (in_array('http://www.w3.org/2002/07/owl#Thing', $type))
		{
			$topic_title = get_title($uri, 'rdfs:label', 'en');
		}
		

		
		
		//print_r($type);
				
		//------------------------------------------------------------------------------------------
		// Display
		
		// Article
		if (in_array('http://purl.org/ontology/bibo/Article', $type))
		{
			$xsl_filename = 'xsl/article.xsl';
		}

		// Journal
		if (in_array('http://purl.org/ontology/bibo/Journal', $type))
		{
			$xsl_filename = 'xsl/journal.xsl';
		}

		// Dbpedia thing
		if (in_array('http://www.w3.org/2002/07/owl#Thing', $type))
		{
			$xsl_filename = 'xsl/dbpedia.xsl';
		}

		// Dbpedia feature
		if (in_array('http://www.opengis.net/gml/_Feature', $type))
		{
			$xsl_filename = 'xsl/dbpedia.xsl';
		}
		
		
		// genbank sequence
		if (in_array('http://purl.uniprot.org/core/Molecule', $type))
		{
			$xsl_filename = 'xsl/genbank.xsl';			
		}

		// taxon concept
		if (in_array('http://rs.tdwg.org/ontology/voc/TaxonConcept#TaxonConcept', $type))
		{
			$xsl_filename = 'xsl/taxonomy.xsl';			
		}

		// Collection
		if (in_array('http://rs.tdwg.org/ontology/voc/Collection#Collection', $type))
		{
			$xsl_filename = 'xsl/collection.xsl';			
		}

		// Specimen (by itself)
		if (in_array('http://rs.tdwg.org/ontology/voc/TaxonOccurrence#TaxonOccurrence', $type) && !in_array('http://purl.uniprot.org/core/Molecule', $type))
		{
			$xsl_filename = 'xsl/occurrence.xsl';
		}

		
		//------------------------------------------------------------------------------------------
		if ($xsl_filename != '')
		{
			$xp = new XsltProcessor();
			$xsl = new DomDocument;
			$xsl->load($xsl_filename);
			$xp->importStylesheet($xsl);
						
			$html = $xp->transformToXML($dom);
		}
		//else
		{
			$html .= '<p/>';
			$html .= '<div style="padding:10px;background:white;-webkit-border-radius:10px;">';
			$html .= '<pre class="brush:xml">' . htmlentities($dom->saveXML(), ENT_COMPAT, 'UTF-8') . '</pre>';
			$html .= '</div>';
		}
		
		// Display...
		header("Content-type: text/html; charset=utf-8\n\n");
		echo html_html_open();
		echo html_head_open();
		echo html_title($topic_title);
		
		echo html_include_css('css/main.css');
		
		echo html_include_script('js/prototype.js');
		echo html_include_script('js/lookahead.js');
		echo html_include_script('js/browse.js');

		// RDF display
		echo html_include_script('js/shCore.js');
		echo html_include_script('js/shBrushXml.js');
		echo html_include_css('css/shCore.css');
		echo html_include_css('css/shThemeDefault.css');
		
		echo html_head_close();
		echo html_body_open();
		echo html_page_header(true, $uri);
		
		echo '<div class="main">';
		
		echo '<div class="maincontent">';
		echo '<div class="maincontent_border">';
		
		echo $html;
		
echo '	
<div id="horizon">
	<div id="content" style="display:none">
        <p>Hello</p>
	</div>
</div>';		
		echo '</div>'; // maincontent_border
		echo '</div>'; // maincontent
		
		
		

		echo '
<script type="text/javascript">
	SyntaxHighlighter.all()
</script>';

	echo '<div style="
	margin-top:20px;
	padding:0px;
	border-top:1px dotted rgb(128,128,128);
	"><p>About:</p></div>';	
	

	echo '</div>'; // main
	
	// footer

			
		echo html_body_close();
		echo html_html_close();	
	}
}

// test

if (0)
{
	$uri = 'http://bioguid.info/doi:10.1054/tice.2001.0207';
	main($uri);
}
else
{
	$uri = '';
	
	if (isset($_GET['uri']))
	{
		$uri = $_GET['uri'];
		
		main($uri);
	}
	else
	{
		echo "No URI";
	}
}


?>