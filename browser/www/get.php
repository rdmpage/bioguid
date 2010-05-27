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
function query_specimens_from_collection ($uri)
{
	global $store_config;
	global $store;
	
	if (preg_match('/^urn:/', $uri))
	{
		$uri = 'http://bioguid.info/' . $uri;
	}

	$xml = '';
	
	$sparql = '
PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
PREFIX geo: <http://www.w3.org/2003/01/geo/wgs84_pos#>
PREFIX dcterms: <http://purl.org/dc/terms/>
PREFIX toccurrence: <http://rs.tdwg.org/ontology/voc/TaxonOccurrence#>


CONSTRUCT 
{
 ?specimen toccurrence:hostCollection <' . $uri . '> .
 ?specimen geo:lat ?lat . 
?specimen geo:long ?long . 
?specimen rdf:type ?type .
}
WHERE 
{ 
   ?specimen toccurrence:hostCollection <' . $uri .'> .
?specimen rdf:type ?type .
OPTIONAL
{
  ?specimen geo:lat ?lat . 
?specimen geo:long ?long . 
}
}
';

//echo $sparql;

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
		if (in_array('http://purl.org/ontology/bibo/Journal', $type))
		{
			$topic_title = get_title($uri);
			$xml = query_articles_from_journal($uri);
			append_xml ($dom, $xml);
		}

		// Collection
		if (in_array('http://rs.tdwg.org/ontology/voc/Collection#Collection', $type))
		{
			$topic_title = get_title($uri);
			$xml = query_specimens_from_collection($uri);
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
		else
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
		
		//echo html_page_header(true, $uri);
		
		echo '<div id="container">' . "\n";
		echo '   <div id="banner">' . "\n";
		echo html_page_header(true, $uri);
		echo '   </div>' . "\n";
		
		/*echo '<div id="nav">';
		echo '   </div>' . "\n";
		echo '<div id="content">';
		echo 'xxxxxx';
		echo '   </div>' . "\n"; */
		
		
/*		echo '<div class="main">';
		
		echo '<div class="maincontent">';
		echo '<div class="maincontent_border">'; */
		
		
		if (1)
		{
			echo $html;
		}
		else
		{
	?>
<div id="nav">
  <div>
    <b>On the Web</b>
    <br>
    <ul type="square">
      <li>
        <a href="http://dx.doi.org/10.1073/pnas.0907926106" target="_new">doi:10.1073/pnas.0907926106</a>
      </li>
    </ul>
    <b>Post to:</b>
    <br>
    <ul type="square">
      <li>Citeulike</li>
      <li>Connotea</li>
      <li>Mendeley</li>
    </ul>
  </div>
</div>
<div id="content">
  <h1>[Article] Bacterial gut symbionts are tightly linked with the evolution of herbivory in ants.</h1>
  <h2>Jacob A Russell, Corrie S Moreau, Benjamin Goldman-Huertas, Mikiko Fujiwara, David J Lohman, Naomi E Pierce</h2>
  <div><span class="internal_link" onclick="lookahead('http://bioguid.info/issn:0027-8424')">Proceedings of the National Academy of Sciences of the United States of America</span> 106: 21236 (2009) doi:10.1073/pnas.0907926106</div>
  <div class="abstract">Ants are a dominant feature of terrestrial ecosystems, yet we know little about the forces that drive their evolution. Recent findings illustrate that their diets range from herbivorous to predaceous, with &amp;quot;herbivores&amp;quot; feeding primarily on exudates from plants and sap-feeding insects. Persistence on these nitrogen-poor food sources raises the question of how ants obtain sufficient nutrition. To investigate the potential role of symbiotic microbes, we have surveyed 283 species from 18 of the 21 ant subfamilies using molecular techniques. Our findings uncovered a wealth of bacteria from across the ants. Notable among the surveyed hosts were herbivorous &amp;quot;turtle ants&amp;quot; from the related genera Cephalotes and Procryptocerus (tribe Cephalotini). These commonly harbored bacteria from ant-specific clades within the Burkholderiales, Pseudomonadales, Rhizobiales, Verrucomicrobiales, and Xanthomonadales, and studies of lab-reared Cephalotes varians characterized these microbes as symbiotic residents of ant guts. Although most of these symbionts were confined to turtle ants, bacteria from an ant-specific clade of Rhizobiales were more broadly distributed. Statistical analyses revealed a strong relationship between herbivory and the prevalence of Rhizobiales gut symbionts within ant genera. Furthermore, a consideration of the ant phylogeny identified at least five independent origins of symbioses between herbivorous ants and related Rhizobiales. Combined with previous findings and the potential for symbiotic nitrogen fixation, our results strongly support the hypothesis that bacteria have facilitated convergent evolution of herbivory across the ants, further implicating symbiosis as a major force in ant evolution.</div>
  <div>
    <ul type="square">
      <li><span class="internal_link" onclick="lookahead('http://bioguid.info/genbank:AF465438')">AF465438</span></li>
    </ul>
  </div>
</div>

	<?php
		}
		
		echo '   <div id="footer">' . "\n";
		echo '     <p>About:</p>' . "\n";
		echo '   </div>' . "\n";
		echo '</div>' . "\n"; // container
		
echo '	
<div id="horizon">
	<div id="progress" style="display:none">
        <p>Hello</p>
	</div>
</div>';		
		

		echo '
<script type="text/javascript">
	SyntaxHighlighter.all()
</script>';

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