<?php

require_once ('../config.inc.php');
require_once('../' . $config['adodb_dir']);
require_once('../db.php');
require_once('../lib.php');
require_once('../utils.php');
require_once('ipni.php');

$taxon_id = '';
$format = 'html';

if (isset($_GET['taxon_id']))
{
	$taxon_id = $_GET['taxon_id'];
}
if (isset($_GET['format']))
{
	switch($format)
	{
		case 'html':
		case 'rdf':
			$format = $_GET['format'];
			break;
			
		default:
			$format = 'html';
			break;
	}
}

if ($taxon_id == '')
{
	header("Content-type: text/html; charset=utf-8");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<title>NCBI Taxonomy</title>
	<meta name="generator" content="BBEdit 9.0" />
	
	<script type="application/javascript" language="javascript">
	function validateFormOnSubmit(theForm) 
	{
		if (theForm.taxon_id.value == '')
		{
			alert("Please enter a NCBI taxonomy ID");
			return false;
		}
		var inpVal = parseInt(theForm.taxon_id.value,10);
		if (isNaN(inpVal))
		{
			alert("NCBI taxonomy ID must be a number");
			return false;
		}
		return true;
	}
	</script>
	    <style type="text/css">
	body 
	{
		font-family: Verdana, Arial, sans-serif;
		font-size: 12px;
		padding:30px;
	
	}
	
.blueRect {
	background-color: rgb(239, 239, 239);
	border:1px solid rgb(239, 239, 239);
	background-repeat: repeat-x;
	color: #000;
	width: 400px;
}
.blueRect .bottom {
	height: 10px;
}
.blueRect .middle {
	margin: 10px 12px 0px 12px;
}
.blueRect .cn {
	background-image: url(../images/c6.png);
	background-repeat: no-repeat;
	height: 10px;
	line-height: 10px;
	position: relative;
	width: 10px;
}
.blueRect .tl {
	background-position: top left;
	float: left;
	margin: -2px 0px 0px -2px;
}
.blueRect .tr {
	background-position: top right;
	float: right;
	margin: -2px -2px 0px 0px;
}
.blueRect .bl {
	background-position: bottom left;
	float: left;
	margin: 2px 0px -2px -2px;
}
.blueRect .br {
	background-position: bottom right;
	float: right;
	margin: 2px -2px -2px 0px;
}		
    
	#details
	{
		display: none;
		position:absolute;
		background-color:white;
		border: 1px solid rgb(128,128,128);
	}
    </style>	
</head>
<body>

<p><a href="/">Home</a></p>

  <h1>NCBI taxonomy to RDF resolver</h1>
<p>This is a front end for a linked data NCBI taxonomy resolver resolver.</p>

<div class="blueRect" style="width:100%">
	<div class="top">
		<div class="cn tl"></div>
		<div class="cn tr"></div>
	</div>
	<div class="middle">


<form action="index.php" method="get" onsubmit="return validateFormOnSubmit(this)">
<label>NCBI Taxonomy ID:</label><br/>
 <input id="taxon_id" name="taxon_id" size="20" />
 <select name="format">
	<option value="html" "selected">HTML</option>
	<option value="xml">XML</option> 
	<option value="rdf">RDF/XML</option>
</select>
<input type="submit" value="Go">
</form>


	</div>
	<div class="bottom">
		<div class="cn bl"></div>
		<div class="cn br"></div>
	</div>
</div>

</body>
</html>
<?php
}
else
{
	$state = 404; // not found

	$obj = new stdclass;
	$obj->TaxonId = $taxon_id;
	$obj->seeAlso = array();
	$obj->hasName = array();
	
	$url = 'http://eutils.ncbi.nlm.nih.gov/entrez/eutils/efetch.fcgi?retmode=xml&db=taxonomy&id='
		. $taxon_id;
		
	//echo $url;
		
	$xml = get($url);
	
	$state = 200;
	
	//echo htmlentities($xml);
	
	$dom= new DOMDocument;
	$dom->loadXML($xml);
	$xpath = new DOMXPath($dom);
	
	$nodeCollection = $xpath->query ('//TaxaSet/Taxon/ScientificName');	
	foreach($nodeCollection as $node)
	{
		$obj->ScientificName = $node->firstChild->nodeValue;
	}

	$nodeCollection = $xpath->query ('//TaxaSet/Taxon/Rank');	
	foreach($nodeCollection as $node)
	{
		$obj->Rank = $node->firstChild->nodeValue;
	}

	$nodeCollection = $xpath->query ('//TaxaSet/Taxon/Lineage');	
	foreach($nodeCollection as $node)
	{
		$obj->Lineage = $node->firstChild->nodeValue;
	}

	$nodeCollection = $xpath->query ('//TaxaSet/Taxon/ScientificName');	
	foreach($nodeCollection as $node)
	{
		$obj->ScientificName = $node->firstChild->nodeValue;
	}

	$nodeCollection = $xpath->query ('//TaxaSet/Taxon/CreateDate');	
	foreach($nodeCollection as $node)
	{
		$obj->CreateDate = format_date($node->firstChild->nodeValue);
	}

	$nodeCollection = $xpath->query ('//TaxaSet/Taxon/UpdateDate');	
	foreach($nodeCollection as $node)
	{
		$obj->UpdateDate = format_date($node->firstChild->nodeValue);
	}

	$nodeCollection = $xpath->query ('//TaxaSet/Taxon/PubDate');	
	foreach($nodeCollection as $node)
	{
		$obj->PubDate = format_date($node->firstChild->nodeValue);
	}
	
	// Links to names
	
	$url = 'http://eutils.ncbi.nlm.nih.gov/entrez/eutils/elink.fcgi?dbfrom=taxonomy&db=all&id=' 
		. $taxon_id . '&cmd=llinks';
		
	$xml = get($url);
	//echo htmlentities($xml);

	$xp = new XsltProcessor();
	$xsl = new DomDocument;
	$xsl->load('linkout.xsl');
	$xp->importStylesheet($xsl);
	
	$xml_doc = new DOMDocument;
	$xml_doc->loadXML($xml);
	
	$json = $xp->transformToXML($xml_doc);
	
	$obj->links = json_decode($json);
	
	foreach ($obj->links->linkouts as $link)
	{
		// all links get stored
		array_push($obj->seeAlso, $link->Url);
		
		// extract LSIDs, etc. from links
		switch ($link->NameAbbr)
		{
			case 'Fungorum':
				$id = $link->Url;
				$id = str_replace('http://www.indexfungorum.org/Names/namesrecord.asp?RecordId=', '', $id);
				array_push($obj->hasName, 'urn:lsid:indexfungorum.org:names:' . $id);
				break;

			case 'IPNI':
			
				$ipni = new Ipni();
				
				$ids = $ipni->LinkoutUrl2Id($link->Url, true);
				
				foreach ($ids as $id)
				{
					array_push($obj->hasName, 'urn:lsid:ipni.org:names:' . $id);
				}
				break;
				
			default:
				
				break;
		}
	}
				
/*	echo '<pre>';
	print_r($obj);
	echo '</pre>';
*/	
	switch ($state)
	{
		case 404:
			ob_start();
			header('HTTP/1.1 404 Not Found');
			header('Status: 404 Not Found');
			$_SERVER['REDIRECT_STATUS'] = 404;
			break;
			
		case 400:
			ob_start();
			header('HTTP/1.0 400');
			header('Status: 400');
			$_SERVER['REDIRECT_STATUS'] = 400;
			break;
			
		case 200:
			switch ($format)
			{
				case 'rdf':
				case 'xml':
					$feed = new DomDocument('1.0');
					$rdf = $feed->createElement('rdf:RDF');
					$rdf->setAttribute('xmlns:rdf', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');
					$rdf->setAttribute('xmlns:rdfs', 'http://www.w3.org/2000/01/rdf-schema#');
					$rdf->setAttribute('xmlns:dcterms', 'http://purl.org/dc/terms/');
					$rdf->setAttribute('xmlns:tcommon','http://rs.tdwg.org/ontology/voc/Common#');
					$rdf->setAttribute('xmlns:tc', 'http://rs.tdwg.org/ontology/voc/TaxonConcept#');
					
					$rdf = $feed->appendChild($rdf);
					
					$taxon = $rdf->appendChild($feed->createElement('tc:TaxonConcept'));
					$taxon->setAttribute('rdf:about', 'taxonomy:' . $obj->TaxonId);
					
					// Dublin Core
					$dcterm_title = $taxon->appendChild($feed->createElement('dcterms:title'));
					$dcterm_title->appendChild($feed->createTextNode($obj->ScientificName));

					$dcterm_created = $taxon->appendChild($feed->createElement('dcterms:created'));
					$dcterm_created->appendChild($feed->createTextNode($obj->CreateDate));

					$dcterm_modified = $taxon->appendChild($feed->createElement('dcterms:modified'));
					$dcterm_modified->appendChild($feed->createTextNode($obj->UpdateDate));

					$dcterm_issued= $taxon->appendChild($feed->createElement('dcterms:issued'));
					$dcterm_issued->appendChild($feed->createTextNode($obj->PubDate));

					// TaxonConcept
					$tc_nameString = $taxon->appendChild($feed->createElement('tc:nameString'));
					$tc_nameString->appendChild($feed->createTextNode($obj->ScientificName));

					$tc_rankString = $taxon->appendChild($feed->createElement('tc:rankString'));
					$tc_rankString->appendChild($feed->createTextNode($obj->Rank));

					// Comma delimited lineage string
					$tcommon_taxonomicPlacementFormal = $taxon->appendChild($feed->createElement('tcommon:taxonomicPlacementFormal'));
					$tcommon_taxonomicPlacementFormal->appendChild($feed->createTextNode(str_replace(';', ',', $obj->Lineage)));

					// LSIDs or linked data-style links
					$num_names = count($obj->hasName);
					for ($i = 0; $i < $num_names; $i++)
					{
						$tc_hasName = $taxon->appendChild($feed->createElement('tc:hasName'));
						$tc_hasName->setAttribute('rdf:resource', $obj->hasName[$i]);									
					}

					// Simple URls
					$num_seeAlso = count($obj->seeAlso);
					for ($i = 0; $i < $num_seeAlso; $i++)
					{
						$rdfs_seeAlso = $taxon->appendChild($feed->createElement('rdfs:seeAlso'));
						$rdfs_seeAlso->setAttribute('rdf:resource', $obj->seeAlso[$i]);									
					}
						
					$h = 'Content-type: application/';
					if ($format == 'rdf')
					{
						$h .= 'rdf+';
					}
					$h .= "xml; charset=utf-8\n\n";
					
					header($h);	
					$feed->encoding='utf-8';
					echo $feed->saveXML();
				
					break;

				case 'html':
				default:
					header("Content-type: text/html; charset=utf-8\n\n");	
					echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">' 
						. "\n" . '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">' . "\n";
					echo '<head>';
					echo '<title>' . $obj->TaxonId . '</title>';
					echo '<style type="text/css">
					body 
					{
						font-family: Verdana, Arial, sans-serif;
						font-size: 12px;
						padding:30px;
					
					}
					</style>';
					
					echo '</head>';
					echo '<body>';
					echo '<p><a href="/taxonomy">Back</a></p>';
					echo '<h1>' . $obj->TaxonId . ': ' . $obj->ScientificName . '</h1>';

					
					echo '</body>';
					echo '</html>';
					break;
			}
		
	
	}
	
	
	
	
	
	
	
}

?>
