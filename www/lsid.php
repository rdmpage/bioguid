<?php

// $Id: $

/**
 * @file lsid.php
 *
 * @brief Basic LSID client
 *
 */
 
require_once (dirname(__FILE__) . '/class_lsid.php');
require_once (dirname(__FILE__) . '/arc/ARC2.php');

// Triple store
$store_config = array
(
  /* db */
  'db_name' => 'bioguid',
  'db_user' => $config['db_user'],
  'db_pwd' => $config['db_passwd'],
  /* store */
  'store_name' => 'arc'
);

$store_config['proxy_host'] = $config['proxy_name'];
$store_config['proxy_port'] = $config['proxy_port'];
	
$store = ARC2::getStore($store_config);
if (!$store->isSetUp()) 
{
	$store->setUp();
}

$lsid = '';
$display = 'html';

if (isset($_GET['lsid']))
{
	$lsid = $_GET['lsid'];
}

if (isset($_GET['display']))
{
	$display = $_GET['display'];
	switch ($display)
	{
		case 'rdf':
		case 'html':
			break;
			
		default:
			$display = 'html';
			break;
	}
}

if ($lsid == '')
{
	header("Content-type: text/html; charset=utf-8");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<title>LSID Resolver</title>
	<meta name="generator" content="BBEdit 9.0" />
    <style type="text/css">
	body 
	{
		font-family: Verdana, Arial, sans-serif;
		font-size: 12px;
		padding:30px;
	
	}
	
	.suggestion:hover
	{
		background-color:rgb(181,213,255);
	}
		

	#details
	{
		display: none;
		position:absolute;
		background-color:white;
		border: 1px solid rgb(128,128,128);
		font-family: Verdana, Arial, sans-serif;
		font-size: 11px;
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
    </style>
	
</head>
<body>


<h1>LSID Resolver</h1>

<div id="details" ></div>
<div>


<h2>Resolve a LSID</h2>

<div class="blueRect" style="width:100%">
	<div class="top">
		<div class="cn tl"></div>
		<div class="cn tr"></div>
	</div>
	<div class="middle">


<form action="lsid.php" method="get">
<input name="lsid" size="60" value="urn:lsid:ipni.org:names:20012728-1:1.1"/>
<input type="submit" value="Resolve"><br/><br/>
<select name="display">
	<option value="html">HTML</option>
	<option value="rdf">RDF</option>
</select>
</form>
<p/>

	</div>
</div>
</body>
</html>
<?php
}
else
{

	$xml = ResolveLSID($lsid);
	
	// check is OK
	if (preg_match('/<error/', $xml))
	{
		// Houston we have a problem...
		switch ($display)
		{
			case 'html':
				// convert...
				$dom= new DOMDocument;
				$dom->loadXML($xml);
				$xpath = new DOMXPath($dom);
			
				// Get JSON
				$xp = new XsltProcessor();
				$xsl = new DomDocument;
				$xsl->load('xsl/xmlverbatim.xsl');
				$xp->importStylesheet($xsl);
				
				$xml_doc = new DOMDocument;
				$xml_doc->loadXML($xml);
				
				$html = $xp->transformToXML($xml_doc);
				
				echo $html;
				break;
				
			case 'rdf':
				header("Content-type: application/xml; charset=utf-8\n\n");	
				echo $xml;
				break;
				
			default:
				break;
		}
	}
	else
	{	
		// all ok, store triples
		$parser = ARC2::getRDFParser();		
		$base = 'http://example.com/';
		$parser->parse($base, $xml);	

		$triples = $parser->getTriples();
		
		// store
		$store->insert($triples, 'http://bioguid.info/' . $lsid);
		
		switch ($display)
		{
			case 'html':
		
				//print_r($triples);	
				
				// Make pretty output...
$html = '				<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>' . $lsid . '</title>
<style type="text/css">
body 
{
	font-family: Verdana, Arial, sans-serif;
	font-size: 12px;
	padding:30px;

}
</style>
</head>
<body>';		
				$html .= '<h1>' . $lsid . '</h1>';
				$html .= '<table>';
				foreach ($triples as $t)
				{
					$html .= '<tr>';	
					
					$html .= '<td>' . $t['p'] . '</td>';
					$html .= '<td>';
					
					if (preg_match('/^urn:lsid:/', $t['o']))
					{
						$html .= '<a href="http://bioguid.info/' . $t['o'] . '">' . $t['o'] . '</a>';
					}
					else
					{
						$html .= $t['o'];
					}
					$html .= '</td>';
					$html .= '</tr>';	
				}
				$html .= '</table>';
				
				$html .= '</body></html>';
				

				header("Content-type: text/html; charset=utf-8\n\n");	
				echo $html;
				break;
		
			case 'rdf':	
				header("Content-type: application/rdf+xml; charset=utf-8\n\n");	
				echo $xml;
				break;
	
			default:
				break;
		}
	}

}

?>
