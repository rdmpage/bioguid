<?php

require_once ('../config.inc.php');
require_once('../' . $config['adodb_dir']);
require_once('../db.php');
require_once('../ISBN-ISSN.php');

$issn = '';
$format = 'html';

if (isset($_GET['issn']))
{
	$issn = $_GET['issn'];
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

if ($issn == '')
{
	header("Content-type: text/html; charset=utf-8");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<title>Journal lookup by ISSN</title>
	<meta name="generator" content="BBEdit 9.0" />
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

  <h1>ISSN resolver</h1>
<p>This is a front end for a linked data ISSN resolver. A URL such as <a href="http://bioguid.info/issn/1000-0739">http://bioguid.info/issn/1000-0739</a>
 will return a web page in a browser, but RDF for a linked data client.</p>

<div class="blueRect" style="width:100%">
	<div class="top">
		<div class="cn tl"></div>
		<div class="cn tr"></div>
	</div>
	<div class="middle">

<form action="index.php" method="get">
<label>Journal:</label><br/>
 <input id="issn" name="issn" size="20" />
 <select name="format">
	<option value="html" "selected">HTML</option>
<!--	<option value="json">JSON</option> -->
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

	// Check ISSN
	$clean = ISN_clean($issn);
	$class = ISSN_classifier($clean);
	if ($class == "checksumOK")
	{
		$issn = canonical_ISSN($issn);
		
		// Find journal in our database
		$sql = 'SELECT * FROM issn WHERE (issn = ' . $db->Quote($issn) . ') 
		ORDER BY LENGTH(title) DESC';

		$result = $db->Execute($sql);
		if ($result == false) die("failed: " . $sql);
	
		if ($result->NumRows() > 0)
		{
			// We have this
			
			$state = 200;
			
						
			$obj->issn = $issn;
			$obj->titles = array();
			$obj->language_codes = array();

			while (!$result->EOF) 
			{			
				array_push($obj->titles, $result->fields['title']);
				array_push($obj->language_codes, $result->fields['language_code']);
				$result->MoveNext();
			}
		}
	}
	else
	{
		$state = 400;
	}
	
	//echo $state;
	
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
					$feed = new DomDocument('1.0');
					$rdf = $feed->createElement('rdf:RDF');
					$rdf->setAttribute('xmlns:rdf', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');
					$rdf->setAttribute('xmlns:dcterms', 'http://purl.org/dc/terms/');
					$rdf->setAttribute('xmlns:prism', 'http://prismstandard.org/namespaces/1.2/basic/');
					$rdf->setAttribute('xmlns:bibo', 'http://purl.org/ontology/bibo/');
					
					$rdf = $feed->appendChild($rdf);
					
					$journal = $rdf->appendChild($feed->createElement('bibo:journal'));
					$journal->setAttribute('rdf:about', 'urn:issn:' . $issn);
					
					$bibo_issn = $journal->appendChild($feed->createElement('bibo:issn'));
					$bibo_issn->appendChild($feed->createTextNode($obj->issn));
			
					$dcterm_title = $journal->appendChild($feed->createElement('dcterms:title'));
					$dcterm_title->setAttribute('xml:lang', $obj->language_codes[0]);
					$dcterm_title->appendChild($feed->createTextNode($obj->titles[0]));
					
					$num_titles = count($obj->titles);
					for ($i = 1; $i < $num_titles; $i++)
					{
						$bibo_shortTitle = $journal->appendChild($feed->createElement('bibo:shortTitle'));
						$bibo_shortTitle->setAttribute('xml:lang', $obj->language_codes[$i]);			
						$bibo_shortTitle->appendChild($feed->createTextNode($obj->titles[$i]));
					}
					
					header("Content-type: application/rdf+xml; charset=utf-8\n\n");	
					$feed->encoding='utf-8';
					echo $feed->saveXML();
				
					break;

				case 'html':
				default:
					header("Content-type: text/html; charset=utf-8\n\n");	
					echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">' 
						. "\n" . '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">' . "\n";
					echo '<head>';
					echo '<title>' . $obj->issn . '</title>';
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
					echo '<p><a href="/issn">Back</a></p>';
					echo '<h1>' . $obj->issn . ':' . $obj->titles[0] . '</h1>';

					echo '<ul>';
					$num_titles = count($obj->titles);
					for ($i = 0; $i < $num_titles; $i++)
					{
						echo '<li>' . $obj->titles[$i] . ' (' .  $obj->language_codes[$i] . ')' . '</li>';
					}
					echo '</ul>';
					
					echo '<h2>More resources</h2>';
					echo '<ul>';
					echo '<a href="http://www.worldcat.org/issn/' . $obj->issn . '">WorldCat</a></li>';
					echo '</ul>';
					
					echo '</body>';
					echo '</html>';
					break;
			}
		
	
	}
}

?>