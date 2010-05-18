<?php

require_once ('../triple_store.php');
require_once (dirname(__FILE__) . '/html.php');
require_once (dirname(__FILE__) . '/uri_functions.php');

// http://snipplr.com/view/231/dead-centre-a-div/




//--------------------------------------------------------------------------------------------------
function main($uri)
{
	global $config;

	// Triple store
	global $store_config;
	global $store;
	
	$uri = urldecode($uri);	
	$ntriples = get_canonical_uri($uri);
		
	if ($ntriples == 0)
	{
		// Fetch URI
		echo '<html>';
		echo '<body>';
		echo  'Sorry, don\'t have this URI <b>' . $uri . '</b>, trying to fetch it...';
		
		// can we get it, if so redirect...
		$query = "LOAD <" . $uri . ">";
		$r = $store->query($query);
		
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
							
		$type = array();
		$xsl_filename = '';
		$html = '';

		//------------------------------------------------------------------------------------------
		// Name
		$name = '';
		$nodeCollection = $xpath->query ('//rdf:type/@rdf:resource');
		foreach($nodeCollection as $node)
		{
			$type[] = $node->firstChild->nodeValue;
		}
		
		//------------------------------------------------------------------------------------------
		// add links...?		
		
		//print_r($type);
				
		//------------------------------------------------------------------------------------------
		// Display
		
		if (in_array('http://purl.org/ontology/bibo/Article', $type))
		{
			$xsl_filename = 'xsl/article.xsl';
		}

		if (in_array('http://www.w3.org/2002/07/owl#Thing', $type))
		{
			$xsl_filename = 'xsl/dbpedia.xsl';
		}
		
		if (in_array('http://purl.uniprot.org/core/Molecule', $type))
		{
			$xsl_filename = 'xsl/genbank.xsl';
		}
		
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
			$html .= '<pre class="brush:xml">' . htmlentities($rdfxml_doc, ENT_COMPAT, 'UTF-8') . '</pre>';
			$html .= '</div>';
		}
		
		// Display...
		header("Content-type: text/html; charset=utf-8\n\n");
		echo html_html_open();
		echo html_head_open();
		echo html_title($uri . ' - ' . $config['site_name']);
		
		echo html_include_css('css/main.css');
		
		echo html_include_script('js/prototype.js');
		echo html_include_script('js/lookahead.js');

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

	echo '</div>';
		
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