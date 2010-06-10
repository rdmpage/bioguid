<?php



/* 
New version of OpenURL resolver that supports

-CrossRef
-JSTOR

-DOI
-SICI

and also supports notion of bibliographic coordinates 
(see Roger Hyam http://markmail.org/message/vjawesm4mdbtfdfn )

*/

require_once (dirname(__FILE__) . '/cinii.php');
require_once('crossref.php');
require_once('db.php');
require_once('gb.php');
require_once('jstor.php');
require_once('class_openurl.php');
require_once('digir.php');
require_once('pubmed.php');
require_once('url2meta.php');

$debug = 0;
if (isset($_GET['debug']))
{
	$debug = ($_GET['debug']);
}


// define some errors 
define('ERROR_OK', 								0);	
define('ERROR_IDENTIFIER_TYPE_UNKNOWN', 		1);	
define('ERROR_FAILED_TO_RESOLVE_IDENTIFIER', 	2);	
define('ERROR_DOI_NOT_IN_CROSSREF',				3);
define('ERROR_SICI_NOT_IN_JSTOR',				4);
define('ERROR_DPMID_NOT_IN_PUBMED',				5);
define('ERROR_DOI_NOT_IN_CROSSREF',				6);
define('ERROR_UNSUPPORTED_GENRE',				7);
define('ERROR_NOT_FOUND_IN_CROSSREF',			8);
define('ERROR_NOT_ENOUGH_FOR_JSTOR_LOOKUP',		9);
define('ERROR_NOT_FOUND_FROM_METADATA',			10);

$error = ERROR_OK;
$error_msg = '';


if (count($_GET) == 0)
{
	// No parameters, so just tell people about service
	
	// Get article count
	$article_count = 0;
	
	$sql = 'SELECT COUNT(id) AS c FROM article_cache';
	
	
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);

	if ($result->NumRows() == 1)
	{
		$article_count = $result->fields['c'];	
	}
	
	
	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>OpenURL Resolver</title>

	
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


  <!-- JSONscriptRequest -->
  <script type="text/javascript" src="<?php echo $config['server']; ?>scripts/jsr_class.js"></script>
  
  <!-- Dynamic web form -->
 
 
<script language="JavaScript" type="text/javascript">

 <!-- Form validation -->

function validateISSN (element)
{		
	var issn_ok = false;
	
	var issn = element.value;
	// Trim
	issn = issn.replace(/^\s+|\s+$/g, '');
	
	//Pattern ISSN should match
	var issnPattern = /^[0-9]{4}\-[0-9]{3}([0-9]|X)$/;
	if (issnPattern.test(issn))
	{
		// ISSN is OK
		issn_ok = true;
	}
	else
	{
		// Problem with ISSN 
		issnPattern = /^[0-9]{4}[0-9]{3}([0-9]|X)$/;
		if (issnPattern.test(element.value))
		{
			// Missing '-', so reformat to NNNN-NNNX
			issn = element.value.substring(0, 4);
			issn += '-';
			issn += element.value.substring(4);
			issn_ok = true;
		}
		else
		{
			// Not an ISSN, make form field red
		}
	}
	return issn_ok;
}

function validate_jacc_form(form)
{
	if (!validateISSN(form.issn))
	{
		alert(form.issn.value + " is not a valid ISSN");
		form.issn.focus();
		return false;
	}
	if (form.volume.value == '')
	{
		alert("Please enter a volume number");
		form.volume.focus();
		return false;
	}
	if (form.spage.value == '')
	{
		alert("Please enter a starting page number");
		form.spage.focus();
		return false;
	}



    return true;
 
}

function validate_openref_form(form)
{
	if (form.title.value == '')
	{
		alert("Please enter a journal name");
		form.volume.focus();
		return false;
	}
	if (form.date.value == '')
	{
		alert("Please enter a year");
		form.volume.focus();
		return false;
	}
	if (form.volume.value == '')
	{
		alert("Please enter a volume number");
		form.volume.focus();
		return false;
	}
	if (form.spage.value == '')
	{
		alert("Please enter a starting page number");
		form.spage.focus();
		return false;
	}



    return true;
 
}

function validate_advanced_form(form)
{
	if (form.title.value == '')
	{
		alert("Please enter a journal name");
		form.volume.focus();
		return false;
	}
	if (form.volume.value == '')
	{
		alert("Please enter a volume number");
		form.volume.focus();
		return false;
	}
	if ((form.spage.value == '') && (form.pages.value == ''))
	{
		alert("Please enter either a starting page or a page within the article");
		form.spage.focus();
		return false;
	}
    return true;
 
}

function validate_identifier_form(form)
{
/*if (!validateISSN(form.issn))
	{
		alert(form.issn.value + " is not a valid ISSN");
		form.issn.focus();
		return false;
	}*/
	if (form.id.value == '')
	{
		alert("Please enter an identifier");
		form.id.focus();
		return false;
	}
	
	// does it look like an identifier?

    return true; 
}


 

function ws_journals(jData) 
{
  if (jData == null) 
  {
    // There was a problem parsing search results
    return;
  }

   //ShowDiv("details");
   
   var details = document.getElementById("details");
  
  	list = '';
    for (i=0;i< jData.results.length;i++) 
    {
 		list += '<span class="suggestion" onclick="setText(\'title\', \'' +  jData.results[i].title + '\');'
 			+ 'setText(\'issn\', \'' + jData.results[i].issn + '\');">'
 			+ jData.results[i].title 
 			+ '</span>'
 			+ '<br/>';
  	}
  	details.innerHTML = list;
  	
  	ShowDiv("details");
}

function journalsuggest(title) 
{
	if (title == '')
	{
		HideDiv("details");
	}
	else
	{
		setText ('issn', '');
		request = '<?php echo $config['server']; ?>services/journalsuggest.php?title=' + title + '&callback=ws_journals';
	
		// Create a new script object
		aObj = new JSONscriptRequest(request);
		// Build the script tag
		aObj.buildScriptTag();
		// Execute (add) the script tag
		aObj.addScriptTag();
	}
}

function positionDiv(divid)
{
	var el = document.getElementById('title');
	var x = 0;
	var y = el.offsetHeight;

	//Walk up the DOM and add up all of the offset positions.
	while (el.offsetParent && el.tagName.toUpperCase() != 'BODY')
	{
		x += el.offsetLeft;
		y += el.offsetTop;
		el = el.offsetParent;
	}

	x += el.offsetLeft;
	y += el.offsetTop;
	
	document.getElementById(divid).style.left = x + 'px';
	document.getElementById(divid).style.top = y + 'px';
}


function ShowDiv(divid)
{
	
   document.getElementById(divid).style.display="block";
   positionDiv(divid);
}

function HideDiv(divid)
{
   document.getElementById(divid).style.display="none";
}

function setText(id, str)
{
	document.getElementById(id).value = str;
	HideDiv('details');

}



</script>  



</head>
<body>
<p><a href=".">Home</a></p>

<!-- <img src="images/bioGUID24.png" align="absmiddle"/><br/> -->

<h1>OpenURL Resolver</h1>

<div id="details" ></div>
<div>

<p>Resolver uses CrossRef, PubMed, JSTOR, and local cache to locate an article. The cache currently has <b><?php echo $article_count;?></b> articles.</p>

<!-- <fieldset>
<legend>Quick search</legend> -->

<h2>Simple lookup using metadata</h2>

<div class="blueRect" style="width:100%">
	<div class="top">
		<div class="cn tl"></div>
		<div class="cn tr"></div>
	</div>
	<div class="middle">


<form action="<?php echo $config['server']; ?>openurl/" method="GET" onsubmit="return validate_jacc_form(this)">

<input type="hidden" name="genre" value="article" />

<table>
<tr>
<!--<td></td>-->
<td>issn</td>
<td>volume</td>
<td>start&nbsp;page</td>
<td>format</td>
<td></td>
</tr>

<tr>
<!--<td width="200" align="right"><b>bioguid.info/openurl/jacc/</b></td>-->
<td><input id="issn" name="issn" value=""/></td>
<td><input name="volume" value="" size="6"/></td>
<td><input name="spage" value="" size="6"/></td>
<td>
<select name="display">
	<option value="html">HTML</option>
	<option value="json">JSON</option>
	<option value="rdf">RDF</option>
	<option value="cite">Wikipedia</option>
	<option value="itaxon">iTaxon</option>
</select>

</td>
<td>&nbsp;<input type="Submit" name="submit" value="Go" /></td></tr>

<tr>
<!--<td></td>-->
<td><input id="title" name="title" value="" onkeyup="journalsuggest(this.value)" autocomplete="off" /></td>
<!--<td></td>
<td></td>-->
<td></td>
<td></td>
<td></td>
<td></td>
</tr>

<tr>
<!--<td></td>-->
<td>journal</td>
<!--<td></td>
<td></td>-->
<td></td>
<td></td>
<td></td>
<td></td>
</tr>

</table>



</form>

	</div>
	<div class="bottom">
		<div class="cn bl"></div>
		<div class="cn br"></div>
	</div>
</div>

<h2>Simple lookup using identifier</h2>

<div class="blueRect" style="width:100%">
	<div class="top">
		<div class="cn tl"></div>
		<div class="cn tr"></div>
	</div>
	<div class="middle">


<form action="openurl.php" method="GET" onsubmit="return validate_identifier_form(this)">

<table>
<tr>
<!--<td></td>-->
<!-- <td>namespace</td> -->
<td>identifier<br/>(include prefix, such as doi: or pmid:)</td><td></td>
<td>format</td>
<td></td>
</tr>

<tr>
<!--<td width="200" align="right"><b>bioguid.info/</b></td>-->

<!--<td>
<select name="namespace">
<option value="doi:">doi</option>
<option value="hdl:">hdl</option>
<option value="pmid:">pmid</option>
<option value="url:">url</option>
</select>
</td> -->

<td><input id="id" name="id" value="" size="40"/></td>

<td>
<select name="display">
<option value="html">HTML</option>
<option value="json">JSON</option>
<option value="rdf">RDF</option>
<option value="cite">Wikipedia</option>
<option value="itaxon">iTaxon</option>
</select>
</td>

<td>&nbsp;<input type="Submit" name="submit" value="Go" /></td></tr>


</table>



</form>

	</div>
	<div class="bottom">
		<div class="cn bl"></div>
		<div class="cn br"></div>
	</div>
</div>

<!--
<h2>Simple lookup using OpenRef</h2>

<div class="blueRect" style="width:100%">
	<div class="top">
		<div class="cn tl"></div>
		<div class="cn tr"></div>
	</div>
	<div class="middle">


<form action="<?php echo $config['server']; ?>openurl/" method="GET" onsubmit="return validate_openref_form(this)">

<input type="hidden" name="genre" value="article" />

<table>
<tr>
<td></td>
<td>journal</td><td></td>
<td>year</td><td></td>
<td>volume</td><td></td>
<td>start&nbsp;page</td><td></td>
</tr>

<tr>
<td width="200" align="right"><b>bioguid.info/openref/</b></td>
<td><input id="title" name="title" value=""/></td>
<td><b>/</b></td>
<td><input id="date" name="date" value="" size="6"//></td>
<td><b>/</b></td>
<td><input name="volume" value="" size="6"/></td>
<td><b>/</b></td>
<td><input name="spage" value="" size="6"/></td>
<td>&nbsp;<input type="Submit" name="submit" value="Go" /></td></tr>


</table>



</form>

	</div>
	<div class="bottom">
		<div class="cn bl"></div>
		<div class="cn br"></div>
	</div>
</div>

-->

<h2>Clean URLs</h2>

<div class="blueRect" style="width:100%">
	<div class="top">
		<div class="cn tl"></div>
		<div class="cn tr"></div>
	</div>
	<div class="middle">

<p>You can resolve identifiers such as DOIs by adding them to the URL <b>bioguid.info/openurl/</b>, for example:</p>
<a href="http://bioguid.info/doi:10.1016/j.ympev.2006.06.014">http://bioguid.info/doi:10.1016/j.ympev.2006.06.014</a><br />

<!--<p>By default bioGUID displays HTML. However, if you want JSON simply append <b>.json</b> to the end of the URL, for example:</p>

<a href="http://bioguid.info/doi:10.1016/j.ympev.2006.06.014.json">http://bioguid.info/doi:10.1016/j.ympev.2006.06.014.json</a><br /> -->
	</div>
	<div class="bottom">
		<div class="cn bl"></div>
		<div class="cn br"></div>
	</div>
</div>

<h2>Formats</h2>

<div class="blueRect" style="width:100%">
	<div class="top">
		<div class="cn tl"></div>
		<div class="cn tr"></div>
	</div>
	<div class="middle">

<p>By default the resolver returns HTML, but you can append "&amp;display=json" or ""&amp;display=rdf" to the OpenURL query to change the output to either JSON or RDF. For example:</p>

<a href="http://bioguid.info/openurl?genre=article&title=Molecular Phylogenetics and Evolution&volume=42&spage=157&display=json">http://bioguid.info/openurl?genre=article&title=Molecular Phylogenetics and Evolution&volume=42&spage=157&display=json</a> returns JSON

<p>and</p>

<a href="http://bioguid.info/openurl?genre=article&title=Molecular Phylogenetics and Evolution&volume=42&spage=157&display=rdf">http://bioguid.info/openurl?genre=article&title=Molecular Phylogenetics and Evolution&volume=42&spage=157&display=rdf</a> returns RDF.

<p>Data browsers
such as <a href="http://dataviewer.zitgist.com/">Zitgist</a> will retrieve RDF automatically, for example:</p>

<a href="http://dataviewer.zitgist.com/?uri=http%3A//bioguid.info/doi%3A10.1016/j.ympev.2006.06.014">http://bioguid.info/doi:10.1016/j.ympev.2006.06.014</a>

<br />
	</div>
	<div class="bottom">
		<div class="cn bl"></div>
		<div class="cn br"></div>
	</div>
</div>



<!-- </fieldset> -->


<h2>Advanced search</h2>


<div class="blueRect" style="width:100%">
	<div class="top">
		<div class="cn tl"></div>
		<div class="cn tr"></div>
	</div>
	<div class="middle">
	
<form action="<?php echo $config['server']; ?>openurl/" method="GET" onsubmit="return validate_advanced_form(this)">

<input type="hidden" name="genre" value="article" />
	
	
<table>
<tr><td width="200">Journal</td><td><input name="title" value="" /></td><td></td></tr>
<tr><td>Volume</td><td><input name="volume" value="" /></td><td></td></tr>
<tr><td>Starting page</td><td><input name="spage" value="" /></td><td>First page in the article</td></tr>
<tr><td>Ending page</td><td><input name="epage" value="" /></td><td>Last page in the article</td></tr>
<tr><td>Pages</td><td><input name="pages" value="" /></td><td>Enter any page within the article</td></tr>
<tr><td>Year</td><td><input name="date" value="" /></td><td>Some repositories (e.g., JSTOR) need this</td></tr>

<tr><td></td><td><input type="Submit" name="submit" value="Go" /></td></tr>
</table>
	</form>
	
	</div>
	<div class="bottom">
		<div class="cn bl"></div>
		<div class="cn br"></div>
	</div>
</div>


<!--

<fieldset>
<legend>Advanced search</legend>
<form action="." method="GET">

<input type="hidden" name="genre" value="article" />
<table>
<tr><td>Journal</td><td><input name="title" value="" /></td></tr>
<tr><td>Volume</td><td><input name="volume" value="" /></td></tr>
<tr><td>Starting page</td><td><input name="spage" value="" /></td></tr>
<tr><td>Ending page</td><td><input name="epage" value="" /></td></tr>
<tr><td>Pages</td><td><input name="pages" value="" /></td></tr>
<tr><td>Year</td><td><input name="date" value="" /></td></tr>

<tr><td></td><td><input type="Submit" name="submit" value="Go" /></td></tr>
</table>



</form>
</fieldset>

-->
<script type="text/javascript">
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
try {
var pageTracker = _gat._getTracker("UA-4542557-2");
pageTracker._trackPageview();
} catch(err) {}</script>
</body>
</html>

<?php
}
else
{
	// Do stuff
	
	
//--------------------------------------------------------------------------------------------------
// Specimens
function find_specimen_from_metadata($values, &$item)
{
	global $debug;
	global $error;
	
	$found = false;
	
	if ($debug)
	{
		echo '<div style="border: 1px solid #c7cfd5;background: rgb(255,255,153);padding:15px;">';
		echo "<p><b>Find specimen from metadata</b></p>";
		echo '<pre>';
		print_r($values);
		echo '</pre>';
		echo '</div>';
	}
	
	
	if (get_specimen($values['institutionCode'], $values['collectionCode'], 
		$values['catalogNumber'], &$item) != 0)
	{
		//print_r($item);
		$found = true;
	}

	
	
	
	return $found;	
}

//--------------------------------------------------------------------------------------------------
// Genbank
function find_genbank_from_id($referent, &$item)
{
	global $debug;
	global $error;
	
	$found = false;
	
	if ($debug)
	{
		echo '<div style="border: 1px solid #c7cfd5;background: rgb(255,255,153);padding:15px;">';
		echo "<p><b>Find GenBank sequence from accession number</b></p>";
		echo '<pre>';
		print_r($values);
		echo '</pre>';
		echo '</div>';
	}
	
	$acc = '';
	if (array_key_exists('genbank', $referent->id)) 
	{
		$acc = $referent->id['genbank'];
	}
	else
	{
		$acc = $referent->id['gi'];
	}
	
	//echo $acc;
	
	
	
	if (get_sequence($acc, &$item) != 0)
	{
		//print_r($item);
		$found = true;
	}

	
	return $found;	
}


//--------------------------------------------------------------------------------------------------
function find_article_from_id($referent, &$item)
{
	global $debug;
	global $error;
	
	if ($debug)
	{
		echo '<div style="border: 1px solid #c7cfd5;background: rgb(255,255,153);padding:15px;">';
		echo "<p><b>Resolve at least one of the identifiers</b></p>";
		echo '<pre>';
		print_r($id);
		echo '</pre>';
		echo '</div>';
	}		

	// Resolve identifier
	$found = false;
	
	$error = ERROR_IDENTIFIER_TYPE_UNKNOWN;
	
	$cache_id = 0;
	
	if ($debug)
	{
		echo '<h3>Resolve identifier</h3>';
	}
	
	//----------doi---------------
	if (array_key_exists('doi', $referent->id))
	{
		$error = ERROR_OK;
		
		$cache_id = find_in_cache_from_guid('doi', $referent->id['doi']);

		if ($cache_id != 0)
		{
			$item = retrieve_from_db($cache_id);
			if ($debug)
			{
				echo "<h3>Article is in cache</h3>";
				echo '<pre style="text-align: left;border: 1px solid #c7cfd5;background: #f1f5f9;padding:15px;">';
				print_r($item);
				echo "</pre>";
			}
			$found=true;
		}
		else
		{
			// Off to CrossRef
			if (doi_metadata ($referent->id['doi'], $item))
			{
				// flesh out with other identifiers (do this here as this is a freshly discovered DOI)
				$pmid = get_pubmed_from_doi($referent->id['doi']);
				if ($pmid != 0)
				{
					$item->pmid=$pmid;
					
					// Abstract?
					$tmp = new stdclass;
					if (pubmed_metadata ($pmid, $tmp))
					{
						if (isset($tmp->abstract))
						{
							$item->abstract = $tmp->abstract;
						}
					}
					
					
				}
				if ($debug)
				{
					echo "<h3>Article</h3>";
					echo '<pre style="text-align: left;border: 1px solid #c7cfd5;background: #f1f5f9;padding:15px;">';
					print_r($item);
					echo "</pre>";
				}
				
				$found = true;
			}
			else
			{
				$error = ERROR_DOI_NOT_IN_CROSSREF;
				$error_msg = $referent->id['doi'];
			}
		}
	}
	
	//----------hdl---------------
	if (array_key_exists('hdl', $referent->id))
	{
		
		$cache_id = find_in_cache_from_guid('hdl', $referent->id['hdl']);

		if ($cache_id != 0)
		{
			$item = retrieve_from_db($cache_id);
			if ($debug)
			{
				echo "<h3>Article is in cache</h3>";
				echo '<pre style="text-align: left;border: 1px solid #c7cfd5;background: #f1f5f9;padding:15px;">';
				print_r($item);
				echo "</pre>";
			}
			$found=true;
		}
		else
		{
			// We don't have this locally, and we have no obvious way of getting metadata without a
			// lookup table
			if ($error == ERROR_OK)
			{
				// we've got a DOI from above, so it's OK
			}
			else
			{
				$error = ERROR_FAILED_TO_RESOLVE_IDENTIFIER;
				if ($debug)
				{
					echo '<p>Don\'t know how to get metadata for a handle</p>';
				}
			
			}
			
		}
	}	
	
	
	//----------sici---------------
	if (array_key_exists('sici', $referent->id))
	{
		$error = ERROR_OK;

		$cache_id = find_in_cache_from_guid('sici', $referent->id['sici']);
		
		if ($cache_id != 0)
		{
			$item = retrieve_from_db($cache_id);
			$found=true;
		}
		else
		{
			if (jstor_metadata ($referent->id['sici'], $item))
			{
				$item->sici = $referent->id['sici'];
				$found = true;
				
				if ($debug)
				{
					echo "<h3>Article</h3>";
					echo '<pre style="text-align: left;border: 1px solid #c7cfd5;background: #f1f5f9;padding:15px;">';
					print_r($item);
					echo "</pre>";
				}
			}
			else
			{
				$error = ERROR_SICI_NOT_IN_JSTOR;
				$error_msg = $referent->id['sici'];				
			}
		}
	}	
	
	//----------pmid---------------
	if (array_key_exists('pmid', $referent->id))
	{
		$error = ERROR_OK;

		$cache_id = find_in_cache_from_guid('pmid', $referent->id['pmid']);
		
		if ($cache_id != 0)
		{
			//echo 'cache';
			$item = retrieve_from_db($cache_id);
			$found=true;
		}
		else
		{
			if (pubmed_metadata ($referent->id['pmid'], $item))
			{
			
				//print_r($item);
			
				$found = true;
				
				// Do we have a DOI?
				if (!isset($item->doi))
				{
					
				
					if (in_crossref($item->issn, $item->year, $item->volume))
					{
						$tmp_item = new stdClass;
						$doi = search_for_doi($item->issn, $item->volume, $item->spage, 'article', $tmp_item);
						if ($doi != '')
						{
							$item->doi = $doi;
						}
					}
				}
				
				if ($debug)
				{
					echo "<h3>Article</h3>";
					echo '<pre style="text-align: left;border: 1px solid #c7cfd5;background: #f1f5f9;padding:15px;">';
					print_r($item);
					echo "</pre>";
				}
			}
			else
			{
				$error = ERROR_PMID_NOT_IN_PUBMED;
				$error_msg = $referent->id['pmid'];				
			
			}
		}
	}	
	
	//----------url---------------
	if (array_key_exists('url', $referent->id))
	{
		$error = ERROR_OK;
		
		$cache_id = find_in_cache_from_guid('url', 'http://' . urldecode($referent->id['url']));
		
		//echo $referent->id['url'];
		//echo $cache_id;
		
		if ($cache_id != 0)
		{
			$item = retrieve_from_db($cache_id);
			$found=true;
		}
		else
		{
			// Can we get metadata from the URL?
			$item = url2meta('http://' . $referent->id['url']);
			
			//print_r($item);

			if ($item->status == 'ok')
			{
				$found = true;
				
				//print_r($item);

				// Do we need to flesh out the metadata?
				
				if (isset($item->doi))
				{
					if (!isset($item->atitle))
					{
						// store any specific metadata
						$tmp_values = new stdclass;
						if (isset($item->publisher_id))
						{
							$temp_values->publisher_id = $item->publisher_id;
						}
						if (isset($item->xml_url))
						{
							$temp_values->xml_url = $item->xml_url;
						}
						if (isset($item->url))
						{
							$temp_values->url = $item->url;
						}					
						
						if (doi_metadata ($item->doi, $item))
						{
							
							if (isset($temp_values->publisher_id))
							{
								$item->publisher_id = $temp_values->publisher_id;
							}
							if (isset($temp_values->xml_url))
							{
								$item->xml_url = $temp_values->xml_url;
							}
							if (isset($temp_values->url))
							{
								$item->url = $temp_values->url;
							}	
						}
						else
						{
							// Bad DOI, bail out...
							$error = ERROR_DOI_NOT_IN_CROSSREF;
							$error_msg = $referent->id['url'];
							return false;
						}
							
						
						//echo "\n" . __LINE__ . "\n";
						//print_r($item);
					}
					
					// Check we haven't found object with this DOI before...
					$cache_id = find_in_cache_from_guid('doi', $item->doi);
					
					
				}
				else
				{
/*					// It might be worth looking for a DOI (Ingenta, for example, may lack it).
					$tmp_item = new stdClass;
					$doi = search_for_doi($item->issn, $item->volume, $item->spage, 'article', $tmp_item);
					if ($doi != '')
					{
						$item->doi = $doi;
					}*/
				}
				
				// Have we already got this object?
				$cache_id = find_in_cache($item);
				if ($cache_id != 0)
				{
					// yes, we already have this
					
					// Update info
					update_article_attribute($cache_id, 'url' , 'http://' . $referent->id['url']);
				}
			}
			else
			{
				$error = ERROR_FAILED_TO_RESOLVE_IDENTIFIER;
				$error_msg = $referent->id['url'];
			}
		}		
	}	
	
	
	
	// If this is a new reference store it
	if ($found and ($cache_id == 0))
	{
		if (find_in_cache($item) == 0)
		{
		
			// Sanity check
			
			$sane = false;
			
			if (
				(isset($item->issn) || isset($item->title))
				&& (isset($item->volume) || isset($item->doi))
				&& (isset($item->spage) || isset($item->doi))
				)
			{
				$sane = true;
			}
			
			if ($sane)
			{
				store_in_cache($item);
			}
			else
			{
				$found = false;
				$error = ERROR_FAILED_TO_RESOLVE_IDENTIFIER;
				$error_msg = $referent->id['url'];
			}
		}
	}
	
	return $found;
}


//--------------------------------------------------------------------------------------------------
// Exact lookup
function find_article_have_spage($values, &$item)
{
	global $debug;
	global $error;
	
	$found = false;

	// Is it in our cache?
	$tmp_item = new stdClass;
	$tmp_item->issn = $values['issn'];
	$tmp_item->volume = $values['volume'];
	$tmp_item->spage = $values['spage'];
	
	$cache_id = find_in_cache($tmp_item);
	if ($cache_id != 0)
	{
		$item = retrieve_from_db($cache_id);
		$found=true;
	}
	else
	{
		// Off to the Cloud...
		$doi = search_for_doi($values['issn'], $values['volume'], $values['spage'], $values['genre'], $item);
		
		if ($doi != '')
		{
			$found = true;
			
			// flesh out with other identifiers (do this here as this is a freshly discovered DOI)
			$pmid = get_pubmed_from_doi($doi);
			if ($pmid != 0)
			{
				$item->pmid=$pmid;
			}
			
		}
		else
		{
			$error = ERROR_NOT_FOUND_IN_CROSSREF;
			if ($debug)
			{
				echo '<p>Not found in CrossRef</p>';
			}
		}
				
		if (!$found)
		{		
		
			if (enough_for_jstor_lookup($values))
			{
				if ($debug)
				{
					echo '<p>Trying JSTOR</p>';
				}
				
				// Make a simple SICI to search JSTOR
				$sici = sici_from_meta($values);
				
				
				
				$found = jstor_metadata($sici, $item);
				
				if ($found)
				{
					$error = ERROR_OK;
				}
				
			}
			else
			{
				$error = ERROR_NOT_ENOUGH_FOR_JSTOR_LOOKUP;
				if ($debug)
				{
					echo '<p>Not enough for lookup in JSTOR</p>';
				}
			}
		}
		
		// Try CiNii
		if (!$found)
		{
			if ($debug)
			{
				echo '<p>CiNii</p>';
			}
		
			$found = search_cinii($values['title'], $values['issn'], $values['volume'], $values['spage'], $item, $debug);
			if ($found)
			{
				$error = ERROR_OK;
			}
		}
		
		if ($found)
		{
			if (find_in_cache($item) == 0)
			{
				store_in_cache($item);
			}
		}
	}	
	//echo $error;
	return $found;
}


//--------------------------------------------------------------------------------------------------
// Find article from a page in the article range
// This is Roger Hyam's bibliograpic coordinates idea
function find_article_from_page($values, &$item)
{
	global $debug;
	
	$found = false;

	// Is it in our cache?
	$tmp_item = new stdClass;
	$tmp_item->issn = $values['issn'];
	$tmp_item->volume = $values['volume'];
	$tmp_item->pages = $values['pages'];
	
	$cache_id = find_in_cache_from_page($tmp_item);
	
	if ($cache_id != 0)
	{
		$item = retrieve_from_db($cache_id);
		$found=true;
	}
	else
	{
		// Off to the Cloud...
		
		// For now limit ourselves to CrossRef
		
		$year = '';
		if (array_key_exists('date', $values))
		{
			$year = $values['date'];
		}
		
		if (in_crossref($values['issn'], $year, $values['volume']))
		{
			//echo 'Should be in CrossRef';
			
			$max_tries = 10;
			$doi = '';
			
			$page = $values['pages'];
			$upper_bound = $page; // save the original starting page
				
			$count = 0;
			while (!$found && ($count < $max_tries) && ($page >= 0))
			{
				if ($debug)
				{
					echo $count, '.';
				}

				$doi = search_for_doi($values['issn'], $values['volume'], $page, $values['genre'], $item);
				
				if ($doi == '')
				{
					// Decrease page
					$page--;
					
					// We might now be in the range of a previously found range
					$tmp_item->issn = $values['issn'];
					$tmp_item->volume = $values['volume'];
					$tmp_item->pages = $page;
					
					$cache_id = find_in_cache_from_page($tmp_item);
					if ($cache_id != 0)
					{
						$item = retrieve_from_db($cache_id);		
						$found = true;
						
						// Update upper bound
						update_page_upperbound($cache_id, $upper_bound);
					}
				}
				else
				{
					$found = true;
					
					$cache_id = find_in_cache($item);
					if ($cache_id == 0)
					{
						$cache_id = store_in_cache($item);
						
					}
					// Update upper bound
					update_page_upperbound($cache_id, $upper_bound);

					
					//echo 'got it!';
				}
				$count++;
			}
		}
		
		
		// OK, try JSTOR (gulp)
		if (!$found)
		{		
			if ($debug)
			{
				echo '<p>Trying JSTOR ' . $values['issn'] . '</p>';
				
				if (in_jstor($values['issn'], $values['date']))
				{
					echo "in JSTOR\n";
				}
				
			}
			
			
		
			
			if (enough_for_jstor_lookup($values) && in_jstor($values['issn'], $values['date']) )
			{
				
				
				$max_tries = 20;
				
				
				$page = $values['pages'];
				$upper_bound = $page; // save the original starting page
				
				$temp_values = $values;				
					
				$count = 0;
				while (!$found && ($count < $max_tries) && ($page >= 0))
				{
					if ($debug)
					{
						echo $count, '.';
					}
					
					$temp_values['spage'] = $page;
					$sici = sici_from_meta($temp_values);
					
					if ($debug)
					{
						print_r($temp_values);
						echo urlencode($sici);
					}
					
				
					$found = jstor_metadata($sici, $item);
					
					if (!$found)
					{
						// Decrease page
						$page--;
						$temp_values['spage'] = $page;
						
						// We might now be in the range of a previously found range
						$tmp_item->issn = $values['issn'];
						$tmp_item->volume = $values['volume'];
						$tmp_item->pages = $page;
						
						$cache_id = find_in_cache_from_page($tmp_item);
						if ($cache_id != 0)
						{
							$item = retrieve_from_db($cache_id);		
							$found = true;
							
							// Update upper bound
							update_page_upperbound($cache_id, $upper_bound);
						}
					}
					else
					{						
						$cache_id = find_in_cache($item);
						if ($cache_id == 0)
						{
							$cache_id = store_in_cache($item);
							
						}
						// Update upper bound
						update_page_upperbound($cache_id, $upper_bound);
	
						
						//echo 'got it!';
					}
					$count++;
				}
				
				
				
				
			}
			else
			{
				if ($debug)
				{
					echo '<p>Not enough for JSTOR lookup, or out of range ' . $values['issn'] . ' ' . $values['date'] . '</p>';
					
				}
			}
				
		}
		
		
	}	
	return $found;
}

//--------------------------------------------------------------------------------------------------
function find_article_from_metadata($values, &$item)
{
	global $debug;
	global $error;
	
	$found = false;
	
	if ($debug)
	{
		echo '<div style="border: 1px solid #c7cfd5;background: rgb(255,255,153);padding:15px;">';
		echo "<p><b>Find article from metadata</b></p>";
		echo '<pre>';
		print_r($values);
		echo '</pre>';
		echo '</div>';
	}		
	
	// We need ISSN for cache lookup
	check_for_missing_issn($values);
	
//	echo __LINE__ , ' boo';
	
	// Case 1: User is enough to locate article (i.e., has starting page	
	if (array_key_exists('spage', $values))
	{
//	echo __LINE__ , ' boo';

		// Simple lookup
		if ($values['spage'] != '')
		{
			$found = find_article_have_spage ($values, $item);
			if (!found)
			{
				$error = ERROR_NOT_FOUND_FROM_METADATA;
			}
		}
	}
//	echo __LINE__ , ' boo';
	
	// Case 2: User has a page in the range spage-epage (e.g., a nomenclator)
	if (array_key_exists('pages', $values))
	{
		// Harder case, we have a page in the range
		if ($values['pages'] != '')
		{
			$found = find_article_from_page ($values, $item);
			if (!found)
			{
				$error = ERROR_NOT_FOUND_FROM_METADATA;
			}
		}
	}

	
	return $found;	
}

//--------------------------------------------------------------------------------------------------
function find_article($referent, &$item)
{
	$found = false;
	
	if (count($referent->id) > 0)
	{
		$found = find_article_from_id($referent->id);
	}
	else
	{
		$found = find_article_from_metadata($referent->values, $item);
	}
	
	return $found;
	
}

//--------------------------------------------------------------------------------------------------
// to do: note this assumes an article, need to modify for books...
function write_coins($item)
{
	$html = '';
	$html .= '<span class="Z3988"';
	$html .= ' title="ctx_ver=Z39.88-2004&amp;rft_val_fmt=info:ofi/fmt:kev:mtx:journal';
	if (count($item->authors) > 0)
	{
		$html .= '&amp;rft.aulast=' . urlencode($item->authors[0]->lastname);
		$html .= '&amp;rft.aufirst=' . urlencode($item->authors[0]->forename);
	}
	$html .= '&amp;rft.jtitle=' . urlencode($item->title);
	$html .= '&amp;rft.atitle=' . urlencode($item->atitle);
	$html .= '&amp;rft.issn=' . $item->issn;
	$html .= '&amp;rft.volume=' . $item->volume;
	$html .= '&amp;rft.spage=' . $item->spage;
	
	if (isset($item->doi))
	{
		$html .= '&amp;rft_id=info:doi/' . urlencode($item->doi);
	}
	else if (isset($item->hdl))
	{
		$html .= '&amp;rft_id=info:hdl/' . urlencode($item->hdl);
	}
	else if (isset($item->url))
	{
		$html .= '&amp;rft_id='. urlencode($item->url);
	}
	
	$html .= '">';
	$html .= '</span>';
	
	return $html;
}

//--------------------------------------------------------------------------------------------------
function get_error_msg($error)
{
	$msg = '';
	switch ($error)
	{
		case ERROR_DOI_NOT_IN_CROSSREF:
			$msg  = 'DOI not in CrossRef';
			break;

		case ERROR_SICI_NOT_IN_JSTOR:
			$msg = 'SICI not in JSTOR';
			break;

		case ERROR_PMID_NOT_IN_PUBMED:
			$msg = 'PMID  not in PubMed';
			break;
			
		case ERROR_IDENTIFIER_TYPE_UNKNOWN:
			$msg = 'Identifier type unknown';
			break;

		case ERROR_FAILED_TO_RESOLVE_IDENTIFIER:
			$msg = 'Failed to resolve identifier';
			break;
		case ERROR_UNSUPPORTED_GENRE:
			$msg = 'Unsupported genre';
			break;
		case ERROR_NOT_FOUND_IN_CROSSREF:
			$msg = 'Not found in CrossRef';
			break;
		case ERROR_NOT_ENOUGH_FOR_JSTOR_LOOKUP:
			$msg = 'Not enought for JSTOR lookup (need ISSN, year, volume, spage)';
			break;
		case ERROR_NOT_FOUND_FROM_METADATA:
			$msg = 'Not found from metadata';
			break;								
		default:
			$msg = "Unknown error $error";
			break;
	}
	return $msg;
}
	


//--------------------------------------------------------------------------------------------------
function display_error($display_type)
{
	global $config;
	global $error;
	global $error_msg;
	
	switch ($display_type)
	{
		case DISPLAY_JSON:
			header("Content-type: text/plain; charset=utf-8\n\n");	

			$obj = new stdClass;
			$obj->status = "error";
			$obj->error = $error . ' ' . get_error_msg($error);
			$json = json_encode($obj);
			echo $json;
			
			break;
			
		case DISPLAY_RDF:
			header("Content-type: application/rdf+xml; charset=utf-8\n\n");	
			$rdf = '<?xml version="1.0" encoding="UTF-8"?>';
			
			$rdf .= '<rdf:RDF';
			$rdf .= ' xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" ';
			$rdf .= ' xmlns:rdfs="http://www.w3.org/2000/01/rdf-schema#" ';
			$rdf .= ' xmlns:dc="http://purl.org/dc/elements/1.1/" ';
			$rdf .= ' xmlns:dcterms="http://purl.org/dc/terms/" ';
			$rdf .= ' xmlns:prism="http://prismstandard.org/namespaces/2.0/basic/" ';
			$rdf .= '>';
			$rdf .= '<!--' . get_error_msg($error) . '-->';
			$rdf .= '</rdf:RDF>';
			echo $rdf;
			break;
		
		
			
		case DISPLAY_HTML:	
		case DISPLAY_REDIRECT:	
			echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">' 
				. "\n" . '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">' . "\n";
			echo '<head>';
			echo '<base href="' . $config['webroot'] . '" />';
			echo '<title>Error</title>';
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
				
			echo '<span style="color:white;background-color:red;">';
			echo get_error_msg($error);
			echo '</span>';
			echo '<br/>';
			
			echo "<a href=\"" . $config['server'] . "openurl/\">Back</a>";
?>
<script type="text/javascript">
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
try {
var pageTracker = _gat._getTracker("UA-4542557-2");
pageTracker._trackPageview();
} catch(err) {}</script>
<?php
			echo '</body>';
			echo '</html>';
			break;
			
		case DISPLAY_CITE:
			header("Content-type: text/plain; charset=utf-8\n\n");	
			echo $error . ' ' . get_error_msg($error);
			break;
			
			
	}
}



//--------------------------------------------------------------------------------------------------
function display_json($item)
{
	$item->status = 'ok';

	header("Content-type: text/plain; charset=utf-8\n\n");	
	$json = json_format(json_encode($item));
	echo $json;
}

//--------------------------------------------------------------------------------------------------
function display_rdf($item)
{
	global $config;
	
	$item->status = 'ok';

	header("Content-type: application/rdf+xml; charset=utf-8\n\n");	
	$rdf = '<?xml version="1.0" encoding="UTF-8"?>';
	
	$rdf .= '<rdf:RDF';
	$rdf .= ' xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" ';
	$rdf .= ' xmlns:rdfs="http://www.w3.org/2000/01/rdf-schema#" ';
	$rdf .= ' xmlns:owl="http://www.w3.org/2002/07/owl#" ';

	//$rdf .= ' xmlns:dc="http://purl.org/dc/elements/1.1/" ';
	$rdf .= ' xmlns:dcterms="http://purl.org/dc/terms/" ';
	$rdf .= ' xmlns:prism="http://prismstandard.org/namespaces/2.0/basic/" ';
	$rdf .= ' xmlns:bibo="http://purl.org/ontology/bibo/"' ;
	$rdf .= '>';
	
	// Primary identifier
	$primaryId = '';
	$id = '';
	if (isset($item->doi))
	{
		if ($config['rdf_about_is_http_uri'])
		{
			// HTTP URI
			$id = 'http://bioguid.info/doi:' . $item->doi;
		}
		else
		{
			// Don't use a specific resolver for rdf:about
			$id = 'doi:' . $item->doi;
		}		
		$primaryId = 'doi';
	}
	if ($id == '')
	{
		if (isset($item->pmid))
		{
			if ($config['rdf_about_is_http_uri'])
			{
				// HTTP URI
				$id = 'http://bioguid.info/pmid:' . $item->pmid;
			}
			else
			{
				// Don't use a specific resolver for rdf:about
				$id = 'pmid:' . $item->pmid;
			}
			$primaryId = 'pmid';
		}
	}
	if ($id == '')
	{
		if (isset($item->hdl))
		{
			if ($config['rdf_about_is_http_uri'])
			{
				// HTTP URI
				$id = 'http://bioguid.info/hdl:' . $item->hdl;
			}
			else
			{
				// Don't use a specific resolver for rdf:about
				$id = 'hdl:' . $item->hdl;
			}
			$primaryId = 'hdl';
		}
	}
	if ($id == '')
	{
		if (isset($item->url))
		{
			if (preg_match('/(?<cinii>(http:\/\/ci.nii.ac.jp\/naid\/[0-9]+))/', $item->url, $match))
			{
				$id = $match['cinii'] . '#article';
				$primaryId = 'url';
			}
			if (preg_match('/http:\/\/www.jstor.org\/stable/', $item->url, $match))
			{
				$id = 'http://bioguid.info/' . $item->url;
				$primaryId = 'url';
			}
		}
	}
	
	if ($id == '')
	{
		// jacc
		if (isset($item->issn) && isset($item->volume) && isset($item->spage))
		{
			if ($config['rdf_about_is_http_uri'])
			{
				// HTTP URI
				$id = 'http://bioguid.info/' . "jacc:" . $item->issn . ":" . $item->volume . '@' . $item->spage;
			}
			else
			{
				// Don't use a specific resolver for rdf:about
				$id = "jacc:" . $item->issn . ":" . $item->volume . '@' . $item->spage;
			}
		}
		else
		{
			// Local id (not resolvable)
			$id = 'http://bioguid.info/item/' . md5($item->title);
		}
		
	}
	
	$rdf .= '<bibo:Article rdf:about="' . $id . '">';
	
	// Identifiers
	
	if (isset($item->doi))
	{
		$rdf .= '<bibo:doi>' . $item->doi . '</bibo:doi>';
		$rdf .= '<prism:doi>' . $item->doi . '</prism:doi>';
		$rdf .= '<dcterms:identifier>' . 'doi:' . htmlspecialchars($item->doi, ENT_NOQUOTES) . '</dcterms:identifier>';
		$rdf .= '<dcterms:identifier>' . 'http://dx.doi.org/' . htmlspecialchars($item->doi, ENT_NOQUOTES) . '</dcterms:identifier>';
	}
	
	if (isset($item->pmid))
	{
		if ($primaryId != 'pmid')
		{
			if ($config['rdf_about_is_http_uri'])
			{	
				$rdf .= '<dcterms:identifier rdf:resource="http://bioguid.info/pmid:' . $item->pmid . '" />';
			}
			else
			{
				$rdf .= '<dcterms:identifier rdf:resource="pmid:' . $item->pmid . '" />';
			}
			$rdf .= '<dcterms:identifier>' . 'pmid:' . $item->pmid . '</dcterms:identifier>';
		}
		$rdf .= "<!-- link to other RDF sources using sameAs -->\n";
		$rdf .= '<owl:sameAs rdf:resource="http://purl.uniprot.org/pubmed/' . $item->pmid . '" />';
		$rdf .= '<owl:sameAs rdf:resource="http://bio2rdf.org/pubmed:' . $item->pmid . '" />';
	}
			
	if (isset($item->hdl))
	{
		if ($primaryId != 'hdl')
		{
			$rdf .= '<dcterms:identifier rdf:resource="http://bioguid.info/hdl:' . $item->hdl . '" />';
		}
	}
	
	if (isset($item->url))
	{
		$rdf .= '<prism:url rdf:resource="'. str_replace('&', '&amp;', $item->url) . '" />';
	}
	
	if (isset($item->publisher_id))
	{
		$rdf .= '<dcterms:identifier>' . htmlspecialchars($item->publisher_id, ENT_NOQUOTES) . '</dcterms:identifier>';
	}
	
	// PMID links
	if (isset($item->pmid))
	{
		// citation links
		$links = get_pubmed_links($item->pmid);
		foreach ($links['cited'] as $pmid)
		{
			$rdf .= '<bibo:citedBy rdf:resource="http://bioguid.info/pmid:' . $pmid . '" />';			
		}
		// genbank links 
		foreach ($links['gi'] as $gi)
		{
			$rdf .= '<dcterms:references rdf:resource="http://bioguid.info/gi:' . $gi . '" />';			
		}

	}
		
	// Authors
	$num_authors = count($item->authors);
	if ($num_authors > 0)
	{
		foreach ($item->authors as $author)
		{
			$rdf .= '<dcterms:creator>' . $author->forename . ' ' . $author->lastname;
			if (isset($author->suffix))
			{
				$rdf .= ' ' . $author->suffix;
			}
			$rdf .= '</dcterms:creator>';
		}
	}
	
	
	// Bibliographic details
	if (isset($item->atitle))
	{
		$rdf .= '<dcterms:title>' . htmlspecialchars($item->atitle, ENT_NOQUOTES) . '</dcterms:title>';
	}
	if (isset($item->title))
	{
		$rdf .= '<prism:publicationName>' .  htmlspecialchars($item->title, ENT_NOQUOTES) . '</prism:publicationName>';
	}
	
	if (isset($item->issn))
	{
		$rdf .= '<prism:issn>' . $item->issn . '</prism:issn>';
		if ($config['rdf_about_is_http_uri'])
		{	
			$rdf .= '<dcterms:isPartOf rdf:resource="http://bioguid.info/issn:' . $item->issn . '" />';
		}
		else
		{
			$rdf .= '<dcterms:isPartOf rdf:resource="' . 'urn:issn:' . $item->issn . '" />';
		}
		
	}
	
	
	if (isset($item->volume))
	{
		$rdf .= '<bibo:volume>' . $item->volume . '</bibo:volume>';
		$rdf .= '<prism:volume>' . $item->volume . '</prism:volume>';
	}
	
	if (isset($item->issue))
	{
		$rdf .= '<bibo:issue>' . $item->issue . '</bibo:issue>';
		$rdf .= '<prism:number>' . $item->issue . '</prism:number>';
	}
	
	if (isset($item->spage))
	{
		$rdf .= '<bibo:pageStart>' . $item->spage . '</bibo:pageStart>';
		$rdf .= '<prism:startingPage>' . $item->spage . '</prism:startingPage>';
	}
	
	if (isset($item->epage))
	{
		$rdf .= '<bibo:pageEnd>' . $item->epage . '</bibo:pageEnd>';
		$rdf .= '<prism:endingPage>' . $item->epage . '</prism:endingPage>';
	}
	
	if (isset($item->year))
	{
		$rdf .= '<dcterms:date>' . $item->year . '</dcterms:date>';
	}
	
	if (isset($item->abstract))
	{
		$rdf .= '<dcterms:abstract>' . htmlspecialchars($item->abstract, ENT_NOQUOTES) . '</dcterms:abstract>';
	}
	
	// pdf
	if (isset($item->pdf))
	{
		$rdf .= '<dcterms:relation>';
		$rdf .= '<bibo:Document rdf:about="' . htmlspecialchars($item->pdf, ENT_NOQUOTES) . '" >';
		$rdf .= '<bibo:uri>' . htmlspecialchars($item->pdf, ENT_NOQUOTES) . '</bibo:uri>';
		$rdf .= '<dcterms:format>' . 'application/pdf' . '</dcterms:format>';
		$rdf .= '</bibo:Document>';
		$rdf .= '</dcterms:relation>';
	}

	// flash
	if (isset($item->swf))
	{
		$swf = $config['webroot'] . "files/" . $item->issn . "/swf/" . $item->swf;
	
		$rdf .= '<dcterms:relation>';
		$rdf .= '<bibo:Document rdf:about="' . htmlspecialchars($swf, ENT_NOQUOTES) . '" >';
		$rdf .= '<bibo:uri>' . htmlspecialchars($swf, ENT_NOQUOTES) . '</bibo:uri>';
		$rdf .= '<dcterms:format>' . 'application/x-shockwave-flash' . '</dcterms:format>';
		$rdf .= '</bibo:Document>';
		$rdf .= '</dcterms:relation>';
	}
	
	$rdf .= '</bibo:Article>';
	$rdf .= '</rdf:RDF>';
	
	echo $rdf;
}

//--------------------------------------------------------------------------------------------------
function display_html($item)
{
	global $config;
	
	//print_r($item);
	
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">' 
		. "\n" . '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">' . "\n";
	echo '<head>';
	echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />' . "\n";
	echo '<base href="' . $config['webroot'] . '" />';
	echo "<!-- Prototype -->\n";
  	echo '<script type="text/javascript" src="' . $config['server'] . 'scripts/prototype.js"></script>' . "\n";
	echo "<!-- Connotea -->\n";
  	echo '<script type="text/javascript">
function ws_connotea(obj) 
{
	$(\'connotea\').innerHTML = obj.html;

};
</script>';
  	  	
	echo '<title>' . $item->atitle . '</title>';
	echo '<style type="text/css">
	body 
	{
				font-family: Verdana, Arial, sans-serif;
				font-size: 12px;
				padding:10px;
	}
	
	a:hover { background-color:rgb(0,99,220); color:white;}
	
	
	.doi {
	  margin-left: 3px;
	  padding: 2px 2px 2px 19px;
	  background: url("images/doi.png") no-repeat 0 50%;
	}
	
	.jstor {
	  margin-left: 3px;
	  padding: 2px 2px 2px 19px;
	  background: url("images/jstor.png") no-repeat 0 50%;
	}
	
	
	.hdl {
	  margin-left: 3px;
	  padding: 2px 2px 2px 19px;
	  background: url("images/handle.png") no-repeat 0 50%;
	}
	
	
	.ncbi {
	  margin-left: 3px;
	  padding: 2px 2px 2px 19px;
	  background: url("images/ncbi.png") no-repeat 0 50%;
	}
	
	
	.url {
	  margin-left: 3px;
	  padding: 2px 2px 2px 19px;
	  background: url("images/world_go.png") no-repeat 0 50%;
	}
	
	
	
	.pdf {
	  margin-left: 3px;
	  padding: 2px 2px 2px 19px;
	  background: url("images/page_white_acrobat.png") no-repeat 0 50%;
	}
	.link {
	  margin-left: 3px;
	  padding: 2px 2px 2px 19px;
	  background: url("images/link.png") no-repeat 0 50%;
	}
	.connotea {
	  margin-left: 3px;
	  padding: 2px 2px 2px 19px;
	  background: url("images/connotea.png") no-repeat 0 50%;
	}
	</style>';
	
	echo '</head>';
	echo '<body>';
    echo '<p><a href=".">Home</a></p>';


	echo '<div style="font-size:18px;font-family:Georgia,Times,serif;font-weight:bold;">' . $item->atitle . '</div>';
	// Authors
	echo '<div>';				
	$count = 0;
	$num_authors = count($item->authors);
	if ($num_authors > 0)
	{
		foreach ($item->authors as $author)
		{
			echo $author->forename . '&nbsp;' . $author->lastname;
			if (isset($author->suffix))
			{
				echo '&nbsp;' . $author->suffix;
			}
			$count++;
			if ($count < $num_authors -1)
			{
				echo ', ';
			}
			else if ($count < $num_authors)
			{
				echo ' and ';
			}
			
		}
	}
	echo '</div>';
					
	echo '<div>';
	echo '<span style="font-style:italic;">' . $item->title . '</span>';
	echo '&nbsp;<span style="font-weight:bold">' . $item->volume . '</span>';
	if (isset($item->issue))
	{
		echo '&nbsp;(' . $item->issue . ')';
	}
	echo ',';
	
	if (isset($item->spage))
	{
		echo '&nbsp;' . $item->spage;
	}
	if (isset($item->epage))
	{
		echo '-' . $item->epage;
	}
	if (isset($item->year))
	{
		echo '&nbsp;(' . $item->year . ')';
	}
	echo '</div>';
	
	echo '<div>';
	
	// CoinS to support OpenURL
	//echo '<div>';
	echo write_coins($item);
	//echo '</div>';

	echo '<div>';
	echo '<ul style="list-style-type:none;padding-left:0px;">';
	// Identifiers
	if (isset($item->doi))
	{
		echo '<li style="padding-bottom:4px;">';
		echo '<a class="doi" href="http://dx.doi.org/' . $item->doi . '" target="_blank">doi:' . $item->doi . '</a>';
		echo '</li>';
	}
	if (isset($item->sici))
	{
		echo '<li style="padding-bottom:4px;">';
		echo '<a class="jstor" href="http://links.jstor.org/sici?sici=' . $item->sici . '" target="_blank">sici:' . $item->sici . '</a>';
		echo '</li>';
	}
	if (isset($item->hdl))
	{
		echo '<li style="padding-bottom:4px;">';
		echo '<a class="hdl" href="http://hdl.handle.net/' . $item->hdl . '" target="_blank">hdl:' . $item->hdl . '</a>';
		echo '</li>';
	}
	if (isset($item->pmid))
	{
		echo '<li style="padding-bottom:4px;">';
		echo '<a class="ncbi" href="http://view.ncbi.nlm.nih.gov/pubmed/' . $item->pmid . '" target="_blank">pmid:' . $item->pmid . '</a>';
		echo '</li>';
	}
	if (isset($item->url))
	{
		echo '<li style="padding-bottom:4px;">';
		echo '<a class="url" href="' . $item->url . '" target="_blank">' . $item->url . '</a>';
		echo '</li>';
	}
	if (isset($item->pdf))
	{
		echo '<li style="padding-bottom:4px;">';
		echo '<a class="pdf" href="' . $item->pdf . '" target="_blank">' . 'PDF' . '</a>';
		echo '</li>';
	}
	
	if (
		isset($item->issn)
		&& isset($item->volume)
		&& isset($item->spage)
		)
	{	
		if (
			($item->issn != '')
			&& ($item->volume != '')
			&& ($item->spage != '')
			)
		{
	
			$jacc = $item->issn . ':' . $item->volume . '@' . $item->spage;
			echo '<li style="padding-bottom:4px;">';
			echo '<a class="link" href="jacc/' . $jacc . '" target="_blank">[JACC]' . $jacc . '</a>';
			echo '</li>';
		}
	}
	
	if (
		isset($item->title)
		&& isset($item->year)
		&& isset($item->volume)
		&& isset($item->spage)
		)
	{	
		if (
			($item->issn != '')
			&& ($item->volume != '')
			&& ($item->spage != '')
			)
		{
		
/*			$openref = $item->title . '/' . $item->year . '/' .  $item->volume . '/' . $item->spage;
			echo '<li style="padding-bottom:4px;">';
			echo '<a class="link" href="openref/' . $openref . '" target="_blank">openref://' . $openref . '</a>';
			echo '</li>';
*/		}
	}
	
	
	if (isset($item->doi) or isset($item->pmid))
	{
		if (isset($item->pmid))
		{
			echo '<li style="padding-bottom:4px;">';
			echo '<a class="connotea" style="cursor:pointer;" onclick="javascript:u=\'';
			echo 'pmid:' . $item->pmid . '\';';
			echo 'a=false;x=window;e=x.encodeURIComponent;d=document;w=open(\'http://www.connotea.org/addpopup?continue=confirm&uri=\'+e(u),\'add\',\'width=660,height=300,scrollbars,resizable\');void(x.setTimeout(\'w.focus()\',200));">';
			echo 'Bookmark in Connotea</a>';
			echo '</li>';
		}
		else
		{
			echo '<li style="padding-bottom:4px;">';
			echo '<a class="connotea" style="cursor:pointer;" onclick="javascript:u=\'';
			echo 'doi:' . $item->doi . '\';';
			echo 'a=false;x=window;e=x.encodeURIComponent;d=document;w=open(\'http://www.connotea.org/addpopup?continue=confirm&uri=\'+e(u),\'add\',\'width=660,height=300,scrollbars,resizable\');void(x.setTimeout(\'w.focus()\',200));">';
			echo 'Bookmark in Connotea</a>';
			echo '</li>';
		}
	}
	
	
	echo '</ul>';
	echo '</div>';
	
	// Connotea tags
/*	if ($config['connotea_user'] != '')
	{
		if (isset($item->doi) or isset($item->pmid))
		{
			echo '<div id="connotea">';
			if (isset($item->doi))
			{
				echo '<script type="text/javascript" src="' . $config['server'] . 'services/connotea.php?uri=doi:' . $item->doi . '&callback=ws_connotea"></script>';
			}
			else
			{
				echo '<script type="text/javascript" src="' . $config['server'] . 'services/connotea.php?uri=pmid:' . $item->pmid . '&callback=ws_connotea"></script>';
			}
			echo '</div>';
		}	
	}*/
	echo '</div>';
	
	if (isset($item->abstract))
	{
		echo '<div style="border:1px solid #666666;background-color:#FFFFCC;padding:4px;">';
		echo $item->abstract;
		echo '</div>';
	}
	
	
	if (isset($item->swf))
	{
		$w = 600;
		$h = 600;
		
		$swf = $config['webroot'] . "files/" . $item->issn . "/swf/" . $item->swf;
		
//		echo '<div style="background-color:rgb(128,128,128);text-align:center;padding-top:20px;border-top: 1px dotted rgb(128,128,128);padding-bottom:20px;border-bottom: 1px dotted rgb(128,128,128);">' . "\n";
		echo '<div style="text-align:left;border-top: 1px dotted rgb(128,128,128);border-bottom: 1px dotted rgb(128,128,128);">' . "\n";
		
		
		echo '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="' . $w . '" height="' . $h . '">' . "\n";
        echo '<param name="movie" value="' . $swf . '" />' . "\n";
		echo '<!--[if !IE]>-->' . "\n";
        echo '<object type="application/x-shockwave-flash" data="' . $swf . '" width="' . $w . '" height="' . $h . '">' . "\n";
        echo '<!--<![endif]-->' . "\n";
		echo '        <!--[if !IE]>-->
        </object>
        <!--<![endif]-->
      </object>';

		echo '</div>';
	}
	
	
	
	echo "<p><a href=\"" . $config['server'] . "openurl/\">Back</a></p>";
?>
<script type="text/javascript">
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
try {
var pageTracker = _gat._getTracker("UA-4542557-2");
pageTracker._trackPageview();
} catch(err) {}</script>
<?	
	echo '</body>';
	echo '</html>';

}

//--------------------------------------------------------------------------------------------------
// Wikipedia cite
function display_cite($item)
{
	$item->status = 'ok';

	header("Content-type: text/plain; charset=utf-8\n\n");	
	$cite = '{{cite journal ';
	
	// Identifiers
	
	if (isset($item->doi))
	{
		$cite .= '| doi = '  . $item->doi;
	}
	
	if (isset($item->pmid))
	{
		$cite .= '| pmid = '  . $item->pmid;
	}
			
	if (isset($item->hdl))
	{
		if (!isset($item->url))
		{
			$cite .= '| url = '  . 'http://hdl.handle.net' . $item->hdl;
		}
	}
	
	if (isset($item->url))
	{
		$cite .= '| url  = '  . $item->url;
	}
		
	// Authors
	$num_authors = count($item->authors);
	if ($num_authors > 0)
	{
		$cite .= '| authors = ';
		$count = 0;
		foreach ($item->authors as $author)
		{
			if ($count == 0)
			{
				$cite .= $author->lastname . ', ' . $author->forename;
			}
			else
			{
				if ($count < ($num_authors - 1))
				{
					$cite .= ', ';
				}
				else
				{
					$cite .= ' & ';
				}
				$cite .= $author->forename . ' ' . $author->lastname;
			}
			$count++;
		}
	}
	
	
	// Bibliographic details
	if (isset($item->atitle))
	{
		$cite .= '| title = ' . $item->atitle;
	}
	if (isset($item->title))
	{
		$cite .= '| journal = [[' . $item->title . ']]';
	}
	if (isset($item->issn))
	{
		$cite .= '| issn = ' . $item->issn;
	}
	if (isset($item->volume))
	{
		$cite .= '| volume = ' . $item->volume;
	}
	if (isset($item->issue))
	{
		$cite .= '| issue = ' . $item->issue;
	}
	$pages = '| pages = ';
	if (isset($item->spage))
	{
		$pages .= $item->spage;
	}
	if (isset($item->epage))
	{
		$pages .= '-' . $item->epage;
	}
	$cite .= $pages;
	if (isset($item->year))
	{
		$cite .= '| year = ' . $item->year;
	}
	/*
	if (isset($item->date))
	{
		$cite .= '| date = ' . $item->date;
	}
	*/
	$cite .= '}}';
	
	echo $cite;
}



//--------------------------------------------------------------------------------------------------
// iTaxon cite
function display_publication_itaxon($item)
{
	global $config;
	
	$item->status = 'ok';

	header("Content-type: text/plain; charset=utf-8\n\n");	
	$cite = '{{Publication ' . "\n";
	
	// Identifiers
	
	if (isset($item->doi))
	{
		$cite .= '| doi = '  . $item->doi . "\n";

		$wiki_safe_doi = $item->doi;
		$wiki_safe_doi = preg_replace('/^(.*)\[(.*)\](.*)$/i', "$1-$2-$3", $wiki_safe_doi);
		$wiki_safe_doi = preg_replace('/^(.*)\<(.*)\>(.*)$/i', "$1-$2-$3", $wiki_safe_doi);
		
		$cite .= '|wikisafedoi=' . $wiki_safe_doi . "\n";
	}
	
	if (isset($item->pmid))
	{
		$cite .= '| pmid = '  . $item->pmid . "\n";
	}
			
	if (isset($item->hdl))
	{
		$cite .= '| hdl = '  . $item->hdl . "\n";
	}
	
	if (isset($item->url))
	{
		$cite .= '| url  = '  . $item->url . "\n";
	}
		
	// Authors
	$num_authors = count($item->authors);
	if ($num_authors > 0)
	{
		$cite .= '| authors = ';
		$count = 0;
		foreach ($item->authors as $author)
		{
			if ($count == 0)
			{
				$cite .=  $author->forename . ' ' . $author->lastname;
			}
			else
			{
				$cite .= ', ';
				$cite .= $author->forename . ' ' . $author->lastname;
			}
			$count++;
		}
		$cite .= "\n";
	}
	
	if (isset($item->issn)
		&& isset($item->volume)
		&& isset($item->spage)
		)
	{
		$cite .= "|jacc=" . $item->issn . ":" . $item->volume . "@" . $item->spage . "\n";
	}
	
	// Bibliographic details
	if (isset($item->atitle))
	{
		$cite .= '|title = ' . $item->atitle . "\n";
	}
	if (isset($item->title))
	{
		$cite .= '| journal = ' . $item->title . "\n";
	}
	if (isset($item->issn))
	{
		$cite .= '| issn = ' . $item->issn . "\n";
	}
	if (isset($item->volume))
	{
		$cite .= '| volume = ' . $item->volume . "\n";
	}
	if (isset($item->issue))
	{
		$cite .= '| issue = ' . $item->issue . "\n";
	}
	if (isset($item->spage))
	{
		$cite .= '| spage = ' . $item->spage . "\n";
	}
	if (isset($item->epage))
	{
		$cite .= '| epage = '  . $item->epage . "\n";
	}
	if (isset($item->year))
	{
		$cite .= '| year = ' . $item->year . "\n";
	}
	
	$cite .= '}}' . "\n";
	
	if (isset($item->abstract))
	{
		$cite .= "\n===Abstract===\n" . $item->abstract;
	}
	
	// Flash paper
	if (isset($item->swf))
	{
		$swf = $config['webroot'] . "files/" . $item->issn . "/swf/" . $item->swf;
		$cite .= "<swf width=\"400\" height=\"500\">$swf</swf>\n";
	}
	
	echo $cite;
}



//--------------------------------------------------------------------------------------------------
function display($item, $display_type)
{
	// We might be able to redirect
	if ($display_type == DISPLAY_REDIRECT)
	{
		$can_redirect = false;
		
		// DOI
		if (isset($item->doi))
		{
			$can_redirect = true;
			header("Location: " . "http://dx.doi.org/" . $item->doi);
			exit(0);
		}
		
		
		if (!$can_redirect)
		{
			// Can't redirect
			$display_type = DISPLAY_HTML;
		}
	}					
	
	
	switch ($display_type)
	{
		case DISPLAY_JSON:
			display_json($item);
			break;

		case DISPLAY_RDF:			
			display_rdf($item);
			break;

		case DISPLAY_CITE:			
			display_cite($item);
			break;

		case DISPLAY_ITAXON:			
			display_publication_itaxon($item);
			break;

		case DISPLAY_HTML:
		default:
			display_html($item);
			break;
	}
}


//--------------------------------------------------------------------------------------------------
function display_specimen($item, $display_type)
{
	global $config;
		
	switch ($display_type)
	{
		case DISPLAY_JSON:
			header("Content-type: text/plain; charset=utf-8\n\n");	
			$json = json_format(json_encode($item));
			echo $json;
			break;
			
		case DISPLAY_HTML:
			display_specimen_html($item);
			break;
			
		case DISPLAY_ITAXON:
			display_specimen_itaxon($item);
			break;
			
		case DISPLAY_RDF:
			header("Content-type: application/rdf+xml; charset=utf-8\n\n");	
			echo display_specimen_rdf($item);
			break;	

		case DISPLAY_XML:
			header("Content-type: application/xml; charset=utf-8\n\n");			
			echo display_specimen_rdf($item);
			break;
			
			
		default:
			break;
	}
}

//--------------------------------------------------------------------------------------------------
function display_specimen_rdf($item)
{
	$feed = new DomDocument('1.0');
	$rdf = $feed->createElement('rdf:RDF');
	$rdf->setAttribute('xmlns:rdf', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');
	$rdf->setAttribute('xmlns:rdfs', 'http://www.w3.org/2000/01/rdf-schema#');
	$rdf->setAttribute('xmlns:dcterms', 'http://purl.org/dc/terms/');
	
	$rdf->setAttribute('xmlns:geo', 'http://www.w3.org/2003/01/geo/wgs84_pos#');
		
	$rdf->setAttribute('xmlns:tcommon', 'http://rs.tdwg.org/ontology/voc/Common#');
	$rdf->setAttribute('xmlns:toccurrence', 'http://rs.tdwg.org/ontology/voc/TaxonOccurrence#');

	// Specimen
	$specimen = $rdf->appendChild($feed->createElement('toccurrence:TaxonOccurrence'));
	$specimen->setAttribute('rdf:about', 'http://bioguid.info/occurrence:' . $item->guid);
	
	// Document metadata
	$modified = $specimen->appendChild($feed->createElement('dcterms:modified'));
	$modified->appendChild($feed->createTextNode($item->dateModified));
	
	
	// Specimen codes
	$institutionCode = $specimen->appendChild($feed->createElement('toccurrence:institutionCode'));
	$institutionCode->appendChild($feed->createTextNode($item->institutionCode));

	$collectionCode = $specimen->appendChild($feed->createElement('toccurrence:collectionCode'));
	$collectionCode->appendChild($feed->createTextNode($item->collectionCode));

	$catalogNumber = $specimen->appendChild($feed->createElement('toccurrence:catalogNumber'));
	$catalogNumber->appendChild($feed->createTextNode($item->catalogNumber));

	// Taxon
	$identifiedToString = $specimen->appendChild($feed->createElement('toccurrence:identifiedToString'));
	$identifiedToString->appendChild($feed->createTextNode($item->organism));
	
	
	// Type status
	if (isset($item->typeStatus))
	{
		$typeStatusString = $specimen->appendChild($feed->createElement('toccurrence:typeStatusString'));
		$typeStatusString->appendChild($feed->createTextNode($item->typeStatus));
	}
	
	// Locality information
	if (isset($item->latitude))
	{
		$latitude = $specimen->appendChild($feed->createElement('toccurrence:decimalLatitude'));
		$latitude->appendChild($feed->createTextNode($item->latitude));

		// geo
		$latitude = $specimen->appendChild($feed->createElement('geo:lat'));
		$latitude->appendChild($feed->createTextNode($item->latitude));

	}
	if (isset($item->longitude))
	{
		$longitude = $specimen->appendChild($feed->createElement('toccurrence:decimalLongitude'));
		$longitude->appendChild($feed->createTextNode($item->longitude));

		// geo
		$longitude = $specimen->appendChild($feed->createElement('geo:long'));
		$longitude->appendChild($feed->createTextNode($item->longitude));
	}
	
	if (isset($item->locality))
	{
		$locality = $specimen->appendChild($feed->createElement('toccurrence:locality'));
		$locality->appendChild($feed->createTextNode($item->locality));
	}
	if (isset($item->county))
	{
		$county = $specimen->appendChild($feed->createElement('toccurrence:county'));
		$county->appendChild($feed->createTextNode($item->county));
	}
	if (isset($item->island))
	{
		$island = $specimen->appendChild($feed->createElement('toccurrence:island'));
		$island->appendChild($feed->createTextNode($item->island));
	}
	if (isset($item->country))
	{
		$country = $specimen->appendChild($feed->createElement('toccurrence:country'));
		$country->appendChild($feed->createTextNode($item->country));
	}
	if (isset($item->stateProvince))
	{
		$stateProvince = $specimen->appendChild($feed->createElement('toccurrence:stateProvince'));
		$stateProvince->appendChild($feed->createTextNode($item->stateProvince));
	}
	if (isset($item->continentOcean))
	{
		$continentOcean = $specimen->appendChild($feed->createElement('toccurrence:continentOcean'));
		$continentOcean->appendChild($feed->createTextNode($item->continentOcean));
	}
		

	// Collector details
	if (isset($item->collector))
	{
		$collector = $specimen->appendChild($feed->createElement('toccurrence:collector'));
		$collector->appendChild($feed->createTextNode($item->collector));
	}
	if (isset($item->collectorNumber))
	{
		$collectorsFieldNumber = $specimen->appendChild($feed->createElement('toccurrence:collectorsFieldNumber'));
		$collectorsFieldNumber->appendChild($feed->createTextNode($item->collectorNumber));
	}
	if (isset($item->fieldNumber))
	{
		$collectorsBatchNumber = $specimen->appendChild($feed->createElement('toccurrence:collectorsBatchNumber'));
		$collectorsBatchNumber->appendChild($feed->createTextNode($item->fieldNumber));
	}
	if (isset($item->verbatimCollectingDate))
	{
		$verbatimCollectingDate = $specimen->appendChild($feed->createElement('toccurrence:verbatimCollectingDate'));
		$verbatimCollectingDate->appendChild($feed->createTextNode($item->verbatimCollectingDate));
	}
	if (isset($item->dateCollected))
	{
		$earliestDateCollected = $specimen->appendChild($feed->createElement('toccurrence:earliestDateCollected'));
		$earliestDateCollected->appendChild($feed->createTextNode($item->dateCollected));

		$latestDateCollected = $specimen->appendChild($feed->createElement('toccurrence:latestDateCollected'));
		$latestDateCollected->appendChild($feed->createTextNode($item->dateCollected));
	}
	
	// BCI
	if (isset($item->bci))
	{
		$type = $specimen->appendChild($feed->createElement('toccurrence:hostCollection'));
		$type->setAttribute('rdf:resource', 'http://biocol.org/' . $item->bci);		
	}

	
	$rdf = $feed->appendChild($rdf);

	$feed->encoding='utf-8';
	return $feed->saveXML();

}


//--------------------------------------------------------------------------------------------------
function display_specimen_html($item)
{
	global $config;
	
	//print_r($item);
	
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">' 
		. "\n" . '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">' . "\n";
	echo '<head>';
	echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />' . "\n";
	echo '<base href="' . $config['webroot'] . '" />';
	echo '<title>' . $item->title . '</title>';
	echo '<style type="text/css">
	body 
	{
				font-family: Verdana, Arial, sans-serif;
				font-size: 12px;
				padding:10px;
	}
	
	a:hover { background-color:rgb(0,99,220); color:white;}
	
	
	.doi {
	  margin-left: 3px;
	  padding: 2px 2px 2px 19px;
	  background: url("images/doi.png") no-repeat 0 50%;
	}
	

	
	.url {
	  margin-left: 3px;
	  padding: 2px 2px 2px 19px;
	  background: url("images/world_go.png") no-repeat 0 50%;
	}
	

	.link {
	  margin-left: 3px;
	  padding: 2px 2px 2px 19px;
	  background: url("images/link.png") no-repeat 0 50%;
	}

	</style>';
	
	echo '</head>';
	echo '<body>';


	echo '<div style="font-size:18px;font-family:Georgia,Times,serif;font-weight:bold;">' . $item->title . '</div>';
	
	echo '<p>' . $item->organism . '</p>';
	echo '<p>' . $item->locality . '</p>';
	
	if (isset($item->latitude))
	{
		echo '
<!--[if IE]>
<embed width="360" height="180" src="map.php?lat=' . $item->latitude . '&long=' . $item->longitude . '">
</embed>
<![endif]-->
<![if !IE]>
<object id="mysvg" type="image/svg+xml" width="360" height="180" data="map.php?lat=' . $item->latitude . '&long=' . $item->longitude . '">
<p>Error, browser must support "SVG"</p>
</object>
<![endif]>	';
		
	
	
	}
?>
<script type="text/javascript">
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
try {
var pageTracker = _gat._getTracker("UA-4542557-2");
pageTracker._trackPageview();
} catch(err) {}</script>
<?php
	echo '</body>';
	echo '</html>';
}

//--------------------------------------------------------------------------------------------------
function display_specimen_itaxon($item)
{
	global $config;
	

	$itaxon = "{{Specimen\n";
	
	$itaxon .= "|organism = " . $item->organism . "\n";
	$itaxon .= "|locality = " . $item->locality . "\n";
	$itaxon .= "|country = " . $item->country . "\n";
	
	// identifiers
	if (isset($item->institutionCode))
	{
		$itaxon .= "|institutionCode = " . $item->institutionCode . "\n";		
	}
	if (isset($item->collectionCode))
	{
		$itaxon .= "|collectionCode = " . $item->collectionCode . "\n";		
	}
	if (isset($item->catalogNumber))
	{
		$itaxon .= "|catalogNumber = " . $item->catalogNumber . "\n";		
	}
	
	// Type status
	if (isset($item->typeStatus))
	{
		$itaxon .= "|typeStatus = " . $item->typeStatus . "\n";		
	}
	
	// Taxonomy
	if (isset($item->kingdom))
	{
		$itaxon .= "|kingdom = " . $item->kingdom . "\n";		
	}
	if (isset($item->phylum))
	{
		$itaxon .= "|phylum = " . $item->phylum . "\n";		
	}
	if (isset($item->class))
	{
		$itaxon .= "|class = " . $item->class . "\n";		
	}
	if (isset($item->family))
	{
		$itaxon .= "|family = " . $item->family . "\n";		
	}
	if (isset($item->genus))
	{
		$itaxon .= "|genus = " . $item->genus . "\n";		
	}
	if (isset($item->species))
	{
		$itaxon .= "|species = " . $item->species . "\n";		
	}
	if (isset($item->subspecies))
	{
		$itaxon .= "|subspecies = " . $item->subspecies . "\n";		
	}
  
	// Geography
	if (isset($item->island))
	{
		$itaxon .= "|island = " . $item->island . "\n";		
	}
	if (isset($item->stateProvince))
	{
		$itaxon .= "|stateProvince = " . $item->stateProvince . "\n";		
	}

	// Collector details
	if (isset($item->collector))
	{
		$itaxon .= "|collector = " . $item->collector . "\n";		
	}	
	if (isset($item->fieldNumber))
	{
		$itaxon .= "|fieldNumber = " . $item->fieldNumber . "\n";		
	}
	if (isset($item->collectorNumber))
	{
		$itaxon .= "|collectorNumber = " . $item->collectorNumber . "\n";		
	}
	if (isset($item->verbatimCollectingDate))
	{
		$itaxon .= "|verbatimCollectingDate = " . $item->verbatimCollectingDate . "\n";		
	}


	// Georeferencing
	if (isset($item->latitude))
	{
		$itaxon .= "|decimalLatitude = " . $item->latitude . "\n";		
	}
	if (isset($item->longitude))
	{
		$itaxon .= "|decimalLongitude = " . $item->longitude . "\n";		
	}
	if (isset($item->verbatimLatitude))
	{
		$itaxon .= "|verbatimLatitude = " . $item->verbatimLatitude . "\n";		
	}
	if (isset($item->verbatimLongitude))
	{
		$itaxon .= "|verbatimLongitude = " . $item->verbatimLongitude . "\n";		
	}
	
	// Dates	
	if (isset($item->dateCollected))
	{
		$itaxon .= "|dateCollected = " . $item->dateCollected . "\n";		
	}
	if (isset($item->dateModified))
	{
		$itaxon .= "|dateModified = " . $item->dateModified . "\n";		
	}
	
	// BCI
	if (isset($item->bci))
	{
		$itaxon .= "|hostCollection = " . $item->bci . "\n";		
	}
	$itaxon .= '}}';
	
	echo $itaxon;
}



//--------------------------------------------------------------------------------------------------
function display_genbank($item, $display_type)
{
	global $config;
		
	switch ($display_type)
	{
		case DISPLAY_JSON:
			header("Content-type: text/plain; charset=utf-8\n\n");	
			$json = json_format(json_encode($item));
			echo $json;
			break;
			
		case DISPLAY_HTML:
			display_genbank_html($item);
			break;
			
		case DISPLAY_ITAXON:
			header("Content-type: text/plain; charset=utf-8\n\n");	
			echo display_genbank_itaxon($item);
			break;

		case DISPLAY_RDF:
			header("Content-type: application/rdf+xml; charset=utf-8\n\n");	
			echo display_genbank_rdf($item);
			break;	

		case DISPLAY_XML:
			header("Content-type: application/xml; charset=utf-8\n\n");			
			echo display_genbank_rdf($item);
			break;
			
		default:
			break;
	}
}

//--------------------------------------------------------------------------------------------------
function display_genbank_html($item)
{
	global $config;
	
	//print_r($item);
	
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">' 
		. "\n" . '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">' . "\n";
	echo '<head>';
	echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />' . "\n";
	echo '<base href="' . $config['webroot'] . '" />';
	echo '<title>' . $item->accession . '</title>';
	echo '<style type="text/css">
	body 
	{
				font-family: Verdana, Arial, sans-serif;
				font-size: 12px;
				padding:10px;
	}
	
	a:hover { background-color:rgb(0,99,220); color:white;}
	
	
	.doi {
	  margin-left: 3px;
	  padding: 2px 2px 2px 19px;
	  background: url("images/doi.png") no-repeat 0 50%;
	}
	

	
	.url {
	  margin-left: 3px;
	  padding: 2px 2px 2px 19px;
	  background: url("images/world_go.png") no-repeat 0 50%;
	}
	

	.link {
	  margin-left: 3px;
	  padding: 2px 2px 2px 19px;
	  background: url("images/link.png") no-repeat 0 50%;
	}

	</style>';
	
	echo '</head>';
	echo '<body>';


	echo '<div style="font-size:18px;font-family:Georgia,Times,serif;font-weight:bold;">' . $item->accession . '</div>';
	
	echo '<p>' . $item->description . '</p>';
	
	if (isset($item->source->latitude))
	{
		echo '
<!--[if IE]>
<embed width="360" height="180" src="map.php?lat=' . $item->source->latitude . '&long=' . $item->source->longitude . '">
</embed>
<![endif]-->
<![if !IE]>
<object id="mysvg" type="image/svg+xml" width="360" height="180" data="map.php?lat=' . $item->source->latitude . '&long=' . $item->source->longitude . '">
<p>Error, browser must support "SVG"</p>
</object>
<![endif]>	';
		
	
	
	}
?>
<script type="text/javascript">
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
try {
var pageTracker = _gat._getTracker("UA-4542557-2");
pageTracker._trackPageview();
} catch(err) {}</script>
<?php
	echo '</body>';
	echo '</html>';
}

//--------------------------------------------------------------------------------------------------
function display_genbank_rdf($item)
{
	$feed = new DomDocument('1.0');
	$rdf = $feed->createElement('rdf:RDF');
	$rdf->setAttribute('xmlns:rdf', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');
	$rdf->setAttribute('xmlns:rdfs', 'http://www.w3.org/2000/01/rdf-schema#');
	$rdf->setAttribute('xmlns:owl', 'http://www.w3.org/2002/07/owl#');
		
	$rdf->setAttribute('xmlns:dcterms', 'http://purl.org/dc/terms/');
	
	$rdf->setAttribute('xmlns:geo', 'http://www.w3.org/2003/01/geo/wgs84_pos#');
		
	$rdf->setAttribute('xmlns:tcommon', 'http://rs.tdwg.org/ontology/voc/Common#');
	$rdf->setAttribute('xmlns:toccurrence', 'http://rs.tdwg.org/ontology/voc/TaxonOccurrence#');

	$rdf->setAttribute('xmlns:uniprot', 'http://purl.uniprot.org/core/');

//	$genbank = $rdf->appendChild($feed->createElement('rdf:Description'));
	$genbank = $rdf->appendChild($feed->createElement('uniprot:Molecule'));
	$genbank->setAttribute('rdf:about', 'http://bioguid.info/genbank:' . $item->accession);
	
	//----------------------------------------------------------------------------------------------
	// sameAs links
	$sameAs = $genbank->appendChild($feed->createElement('owl:sameAs'));
	$sameAs->setAttribute('rdf:resource', 'http://bio2rdf.org/genbank:' . $item->accession);

	if (isset($item->gi))
	{
		$sameAs = $genbank->appendChild($feed->createElement('owl:sameAs'));
		$sameAs->setAttribute('rdf:resource', 'http://bioguid.info/gi:' . $item->gi);
	}
		
	//----------------------------------------------------------------------------------------------
	// Document metadata
	$created = $genbank->appendChild($feed->createElement('dcterms:created'));
	$created->appendChild($feed->createTextNode($item->created));

	$modified = $genbank->appendChild($feed->createElement('dcterms:modified'));
	$modified->appendChild($feed->createTextNode($item->updated));

	$title = $genbank->appendChild($feed->createElement('dcterms:title'));
	$title->appendChild($feed->createTextNode($item->accession));

	$title = $genbank->appendChild($feed->createElement('dcterms:description'));
	$title->appendChild($feed->createTextNode($item->description));
		
	//----------------------------------------------------------------------------------------------
	// Taxon (link to bioguid URI)
	$db_xref = $genbank->appendChild($feed->createElement('dcterms:subject'));	
	$t = $item->source->db_xref;
	$t = str_replace('taxon:', 'taxonomy:', $t);
	$db_xref->setAttribute('rdf:resource', "http://bioguid.info/" . $t);
	
	//----------------------------------------------------------------------------------------------
	// Reference
	// Do we have a GUID?
	$publication_guid = '';

	// If we have a publication GUID then link to that (make sure there's only one GUID used)
	if ($publication_guid == '')
	{
		if (isset($item->references[0]->doi))
		{
			$reference = $genbank->appendChild($feed->createElement('dcterms:isReferencedBy'));
			$reference->setAttribute('rdf:resource', 'http://bioguid.info/doi:' . $item->references[0]->doi);
			$publication_guid = $item->references[0]->doi;
		}
	}
	if ($publication_guid == '')
	{
		if (isset($item->references[0]->pmid))
		{
			$reference = $genbank->appendChild($feed->createElement('dcterms:isReferencedBy'));
			$reference->setAttribute('rdf:resource', 'http://bioguid.info/pmid:' . $item->references[0]->pmid);
			$publication_guid = $item->references[0]->pmid;
		}
	}	
	if ($publication_guid == '')
	{
		if (isset($item->references[0]->hdl))
		{
			$reference = $genbank->appendChild($feed->createElement('dcterms:isReferencedBy'));
			$reference->setAttribute('rdf:resource', 'http://bioguid.info/hdl:' . $item->references[0]->hdl);
			$publication_guid = $item->references[0]->hdl;
		}
	}

	//----------------------------------------------------------------------------------------------
	// No GUID for publication so make a generic document blank node
	if ($publication_guid == '')
	{
		$reference = $genbank->appendChild($feed->createElement('dcterms:isReferencedBy'));
		$reference->setAttribute('rdf:parseType', 'Resource');
		
		$type = $reference->appendChild($feed->createElement('rdf:type'));
		$type->setAttribute('rdf:resource', 'http://purl.org/ontology/bibo/Document');		

		$citation = $reference->appendChild($feed->createElement('dcterms:bibliographicCitation'));
		$citation->appendChild($feed->createTextNode($item->references[0]->bibliographicCitation));

		$atitle = $reference->appendChild($feed->createElement('dcterms:title'));
		$atitle->appendChild($feed->createTextNode($item->references[0]->atitle));
		
		if (isset($item->references[0]->authors))
		{
			foreach ($item->references[0]->authors as $author)
			{
				$a = $reference->appendChild($feed->createElement('dcterms:creator'));
				
				$astring = '';
				if (isset($author->forename))
				{
					$astring .= $author->forename;
				}
				if (isset($author->lastname))
				{
					$astring .= ' ' . $author->lastname;
				}
				$astring = trim($astring);
				
				$a->appendChild($feed->createTextNode($astring));
			}
		}
		
	}
	
	// handle legacy CASENT in cache
	if (!isset($item->source->specimen))
	{
		if (isset($item->source->specimen_voucher))
		{
			if (preg_match('/^CASENT/', $item->source->specimen_voucher))
			{
				$item->source->specimen = new stdclass;
				$item->source->specimen->guid = 'antweb:' . str_replace(' ', '', strtolower($item->source->specimen_voucher));
			}
		}
	}
		
	//----------------------------------------------------------------------------------------------
	if (isset($item->source->specimen))
	{
		// Specimen exists online
		
		// need URI for it...
		
		
		
		if (isset($item->source->specimen->guid))
		{
			// fix broken CAS
			if (preg_match('/CAS::(?<id>\d+)/', $item->source->specimen->guid, $m))
			{
				$item->source->specimen->guid = 'CAS:Herps:' . $m['id'];
			}
		
			$source = $genbank->appendChild($feed->createElement('dcterms:relation'));
			$source->setAttribute('rdf:resource', 'http://bioguid.info/occurrence:' . $item->source->specimen->guid);
		}
	}
	else
	{
	
	
		// Source is not available online, create blank node
		
		// Source (blank node)
		$source = $genbank->appendChild($feed->createElement('dcterms:relation'));
		$source->setAttribute('rdf:parseType', 'Resource');
		
		$type = $source->appendChild($feed->createElement('rdf:type'));
		$type->setAttribute('rdf:resource', 'http://rs.tdwg.org/ontology/voc/TaxonOccurrence#TaxonOccurrence');

		// Name
		$organism = $source->appendChild($feed->createElement('toccurrence:identifiedToString'));
		$organism->appendChild($feed->createTextNode($item->source->organism));
		
		// Locality information
		if (isset($item->source->latitude))
		{
			$latitude = $source->appendChild($feed->createElement('toccurrence:decimalLatitude'));
			$latitude->appendChild($feed->createTextNode($item->source->latitude));
	
			// geo
			$latitude = $source->appendChild($feed->createElement('geo:lat'));
			$latitude->appendChild($feed->createTextNode($item->source->latitude));
	
		}
		if (isset($item->source->longitude))
		{
			$longitude = $source->appendChild($feed->createElement('toccurrence:decimalLongitude'));
			$longitude->appendChild($feed->createTextNode($item->source->longitude));
	
			// geo
			$longitude = $source->appendChild($feed->createElement('geo:long'));
			$longitude->appendChild($feed->createTextNode($item->source->longitude));
	
		}
		if (isset($item->source->lat_lon))
		{
			$verbatimCoordinates = $source->appendChild($feed->createElement('toccurrence:verbatimCoordinates'));
			$verbatimCoordinates->appendChild($feed->createTextNode($item->source->lat_lon));
		}
		if (isset($item->source->locality))
		{
			$locality = $source->appendChild($feed->createElement('toccurrence:locality'));
			$locality->appendChild($feed->createTextNode($item->source->locality));
		}
		if (isset($item->source->country))
		{
			$country = $source->appendChild($feed->createElement('toccurrence:country'));
			$country->appendChild($feed->createTextNode($item->source->country));
		}
		
		
		
		// Collection
		if (isset($item->source->collected_by))
		{
			$f = $source->appendChild($feed->createElement('toccurrence:collector'));
			$f->appendChild($feed->createTextNode($item->source->collected_by));
		}
		if (isset($item->source->collection_date))
		{
			$f = $source->appendChild($feed->createElement('toccurrence:verbatimCollectingDate'));
			$f->appendChild($feed->createTextNode($item->source->collection_date));
		}
		
		// Voucher code
		// See http://rs.tdwg.org/ontology/voc/TaxonOccurrence#catalogNumber for advice:
		// The identifier used for this TaxonOccurrence within the scope of the collection. 
		// e.g. specimen id. This should be the preferred identifier. Alternative identifiers, 
		// such as additional barcodes, can be given using the dc:identifier property.
		if (isset($item->source->specimen_voucher))
		{
			$f = $source->appendChild($feed->createElement('dcterms:identifier'));
			$f->appendChild($feed->createTextNode($item->source->specimen_voucher));
		}
		
		
		// Other...
		
		
	}
	
	
	
	$rdf = $feed->appendChild($rdf);

	$feed->encoding='utf-8';
	return $feed->saveXML();
}

//--------------------------------------------------------------------------------------------------
function display_genbank_itaxon($item)
{
	global $config;
	
	$itaxon = "{{GenBank\n";
	
	$itaxon .= "|accession=" .  $item->accession . "\n";
	$itaxon .= "|version=" .  $item->version . "\n";
	$itaxon .= "|gi=" .  $item->gi . "\n";
	$itaxon .= "|created=" .  $item->created . "\n";
	$itaxon .= "|modified=" .  $item->updated . "\n";
	$itaxon .= "|description=" .  $item->description . "\n";
	$itaxon .= "|organism=" .  $item->source->organism . "\n";
	
	$s = str_replace('taxon:', '', $item->source->db_xref);
	
	// Source
	$itaxon .= "|source=" .  $s . "\n";
	
	if (isset($item->source->organelle))
	{
		$itaxon .= "|organelle=" .  $item->source->organelle . "\n";
	}
	if (isset($item->source->isolate))
	{
		$itaxon .= "|isolate=" .  $item->source->isolate . "\n";
	}
	if (isset($item->source->specimen_code))
	{
		$itaxon .= "|specimenCode=" .  $item->source->specimen_code . "\n";
	}
	if (isset($item->source->specimen_voucher))
	{
		$itaxon .= "|specimenVoucher=" .  $item->source->specimen_voucher . "\n";
	}
	if (isset($item->source->isolate))
	{
		$itaxon .= "|isolate=" .  $item->source->isolate . "\n";
	}
	if (isset($item->source->note))
	{
		$itaxon .= "|note=" .  $item->source->note . "\n";
	}
	if (isset($item->source->host))
	{
		$itaxon .= "|host=" .  $item->source->host . "\n";
	}
	
	if (isset($item->source->latitude))
	{
		$itaxon .= "|latitude=" .  $item->source->latitude . "\n";
	}
	if (isset($item->source->longitude))
	{
		$itaxon .= "|longitude=" .  $item->source->longitude . "\n";
	}
	
	
	// Publication
	if (isset($item->references[0]->bibliographicCitation))
	{
		$itaxon .= "|bibliographicCitation=" . $item->references[0]->bibliographicCitation . "\n";
	}
	
	$publication_guid = '';
	
	if ($publication_guid == '')
	{
		if (isset($item->references[0]->doi))
		{
			$itaxon .= "|openurl=id%3Ddoi:" . $item->references[0]->doi . "\n";
			
			$wiki_safe_doi = 'doi:' . $item->references[0]->doi;
			$wiki_safe_doi = preg_replace('/^doi:(.*)\[(.*)\](.*)$/i', "Doi:$1-$2-$3", $wiki_safe_doi);
			$wiki_safe_doi = preg_replace('/^doi:(.*)\<(.*)\>(.*)$/i', "Doi:$1-$2-$3", $wiki_safe_doi);
			
			$publication_guid = $wiki_safe_doi;
		}
	}
	if ($publication_guid == '')
	{
		if (isset($item->references[0]->hdl))
		{
			$publication_guid = 'hdl:' . $item->references[0]->hdl;
		}
	}
	if ($publication_guid == '')
	{
		if (isset($item->references[0]->issn)
		&& isset($item->references[0]->volume)
		&& isset($item->references[0]->spage)
		)
		{
			$publication_guid = 'jacc:' 
				. $item->references[0]->issn 
				. ':' . $item->references[0]->volume
				. '@' . $item->references[0]->spage;

			$itaxon .= "|openurl=genre%3Darticle"
				. "%26issn%3D" . $item->references[0]->issn
				. "%26volume%3D" . $item->references[0]->volume
				. "%26spage%3D" . $item->references[0]->spage
				. "\n";
			
		}
	}
	
	if ($publication_guid == '')
	{
		if (isset($item->references[0]->url))
		{
			$publication_guid = $item->references[0]->url;
		}
	}
	if ($publication_guid != '')
	{
		$itaxon .= "|publishedIn=" . $publication_guid . "\n";
	}
	
	// Feature tags
	$feature_list = array();
	foreach ($item->features as $feature)
	{
		$s = $feature->key . ':' . $feature->name;
		
		array_push($feature_list, $s);
	}
	$feature_list = array_unique($feature_list);
	
	$itaxon .= '|features=';
	$count = 0;
	foreach ($feature_list as $f)
	{
		if ($count > 0)
		{
			$itaxon .= ';';
		}
		$itaxon .= $f;
		$count++;
	}
	$itaxon .= "\n";
		
	
	$itaxon .= "}}\n";

	return $itaxon;
	
}



//--------------------------------------------------------------------------------------------------

// Here we go...

$parameters = array();

define('DISPLAY_REDIRECT', 	0);	
define('DISPLAY_JSON', 		1);	
define('DISPLAY_HTML', 		2);	
define('DISPLAY_RDF', 		3);	
define('DISPLAY_CITE', 		4);	
define(DISPLAY_ITAXON,		5);
define(DISPLAY_RDF, 		6);	

$display_type = DISPLAY_HTML; // default
if (isset($_GET['display']))
{
	switch ($_GET['display'])
	{
		case 'redirect':
			$display_type = DISPLAY_REDIRECT;
			break;
		case 'json':
			$display_type = DISPLAY_JSON;
			break;
		case 'html':
			$display_type = DISPLAY_HTML;
			break;
		case 'rdf':
			$display_type = DISPLAY_RDF;
			break;
		case 'cite':
			$display_type = DISPLAY_CITE;
			break;
		case 'itaxon':
			$display_type = DISPLAY_ITAXON;
			break;
		case 'xml':
			$display_type = DISPLAY_XML;
			break;
		default:
			$display_type = DISPLAY_HTML;
			break;
	}
	
	unset($_GET['display']);
}

//--------------------------------------------------------------------------------------------------
// This is where we start processing the OpenURL request
//--------------------------------------------------------------------------------------------------

$error = ERROR_OK;
$parameters = $_GET;
if ($debug)
{
	echo "<h3>Parameters passed to OpenURL resolver</h3>";
	echo '<pre style="text-align: left;border: 1px solid #c7cfd5;background: #f1f5f9;padding:15px;">';
	print_r($parameters);
	echo "</pre>";
}

// What version of OpenURL are we dealing with?
$version = 0.1;

// Above keys are not mandatory, but for version 1.0 the must be at least
// one key with the prefix 'rft_'
if ($version == 0.1)
{
	$rftCount = 0;
	foreach ($parameters as $k => $v)
	{
		if (preg_match('/^rft_/', $k))
		{
			$rftCount++;
		}
	}
	if ($rftCount > 0)
	{
		$version = 1.0;
	}
}


if ($debug)
{
	echo '<div style="border: 1px solid #c7cfd5;background: rgb(255,255,153);padding:15px;">';
	echo "<p>OpenURL version = $version</p>";
	echo '</div>';
}

// Referent is what we want to resolve
$referent = new opReferent();
$referent->version = $version;
$referent->GetParameters($parameters);

if ($debug)
{
	$referent->Dump();
}

// OK, let's do something here...

$item = new stdClass;

// Do we have some identifiers?

$have_identifiers = count($referent->id);

// Why am I doing this ???????
if (isset($_GET['genre']) || isset($_GET['rft_genre']))
{
	$have_identifiers = 0;
}

if ($have_identifiers == 1)
{
	$found_from_id = false;
	//----------Genbank---------------
	if (array_key_exists('genbank', $referent->id) || array_key_exists('gi', $referent->id))
	{
		// genbank
		$found_from_id = find_genbank_from_id($referent, $item);
		if ($found_from_id)
		{
			display_genbank($item, $display_type);
		}
		
	}
	else
	{
		$found_from_id = find_article_from_id($referent, $item);
		if ($found_from_id)
		{
			display($item, $display_type);
		}
	}
	if (!$found_from_id)
	{
/*		if ($error == ERROR_FAILED_TO_RESOLVE_IDENTIFIER)
		{
			// We couldn't resolve the identifier
			if (isset($_GET['genre']) || isset($_GET['rft_genre']))
			{
				// but we will try search using metadata
			}
		
		
		}*/
		display_error($display_type);
	}
}
else
{
	if (isset($_GET['id']) || isset($_GET['rft_id']))
	{
		// User supplied identifiers, but they aren't recognised		
		if (isset($_GET['genre']) || isset($_GET['rft_genre']))
		{
			// Try search using metadata
		}
		else
		{
			// Just identifier, which we don't understand, so bail out
			$error = ERROR_IDENTIFIER_TYPE_UNKNOWN;
			display_error($display_type);
		}
	}
	if ($error == ERROR_OK)
	{
		// Find based on metadata

		//  What kind of referent do we have?
		switch ($referent->values['genre'])
		{
			//--------------------------------------------------------------------------
			case 'book':
				break;

			//--------------------------------------------------------------------------
			case 'bookitem':
				break;
		
			//--------------------------------------------------------------------------
			case 'specimen':
				if ($debug)
				{
					echo '<div style="border: 1px solid #c7cfd5;background: rgb(255,255,153);padding:15px;">';
					echo "<p><b>Referent is a specimen</b></p>";
					echo '</div>';
				}
				
				if (find_specimen_from_metadata($referent->values, $item))
				{
					display_specimen($item, $display_type);
				}
				else
				{
					$error = ERROR_NOT_FOUND_FROM_METADATA;
				}
				
				break;
				

			//--------------------------------------------------------------------------
			case 'article':
				if ($debug)
				{
					echo '<div style="border: 1px solid #c7cfd5;background: rgb(255,255,153);padding:15px;">';
					echo "<p><b>Referent is an article</b></p>";
					echo '</div>';
				}		
				
				if (find_article_from_metadata($referent->values, $item))
				{
					if ($debug)
					{
						echo "<h3>Article</h3>";
						echo '<pre style="text-align: left;border: 1px solid #c7cfd5;background: #f1f5f9;padding:15px;">';
						print_r($item);
						echo "</pre>";
					}
					
					display($item, $display_type);
					
				}
				else
				{
					$error = ERROR_NOT_FOUND_FROM_METADATA;
				}
				break;
				
			default:
				$error = ERROR_UNSUPPORTED_GENRE;
				break;
		}
		
		if ($error != ERROR_OK)
		{
			display_error($display_type);
		}
			
	}
}

}


	
	

?>
