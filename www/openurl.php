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

<!-- <img src="images/bioGUID24.png" align="absmiddle"/><br/> -->

<h1>OpenURL Resolver</h1>

<div id="details" ></div>
<div>

<!-- <fieldset>
<legend>Quick search</legend> -->

<h2>Simple lookup using JACC</h2>

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
<td></td>
<td>issn</td><td></td>
<td>volume</td><td></td>
<td>start&nbsp;page</td><td></td>
</tr>

<tr>
<td width="200" align="right"><b>bioguid.info/openurl/jacc/</b></td>
<td><input id="issn" name="issn" value=""/></td>
<td><b>/</b></td>
<td><input name="volume" value="" size="6"/></td>
<td><b>/</b></td>
<td><input name="spage" value="" size="6"/></td>
<td>&nbsp;<input type="Submit" name="submit" value="Go" /></td></tr>

<tr>
<td></td>
<td><input id="title" name="title" value="" onkeyup="journalsuggest(this.value)" autocomplete="off" /></td>
<td></td>
<td></td>
<td></td>
<td></td>
<td></td>
</tr>

<tr>
<td></td>
<td>journal</td>
<td></td>
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
		
		$cache_id = find_in_cache_from_guid('url', $referent->id['url']);
		
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
						doi_metadata ($item->doi, $item);
					}
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
			store_in_cache($item);
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
			
			$max_tries = 40;
			$doi = '';
			
			$page = $values['pages'];
			$upper_bound = $page; // save the original starting page
				
			$count = 0;
			while (!$found && ($count < $max_tries))
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
				echo '<p>Trying JSTOR</p>';
			}
		
			
			if (enough_for_jstor_lookup($values) && in_jstor($values['issn'], $values['date']) )
			{
				
				
				$max_tries = 20;
				
				
				$page = $values['pages'];
				$upper_bound = $page; // save the original starting page
				
				$temp_values = $values;				
					
				$count = 0;
				while (!$found && ($count < $max_tries))
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
function write_coins($item)
{
	$html = '';
	$html .= '<span class="Z3988"';
	$html .= ' title="ctx_ver=Z39.88-2004&amp;rft_val_fmt=info:ofi/fmt:kev:mtx:journal';
	$html .= '&amp;rft.issn=' . $item->issn;
	$html .= '&amp;rft.volume=' . $item->volume;
	$html .= '&amp;rft.spage=' . $item->spage;
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
			
			echo '</body>';
			echo '</html>';
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
	$item->status = 'ok';

	header("Content-type: application/rdf+xml; charset=utf-8\n\n");	
	$rdf = '<?xml version="1.0" encoding="UTF-8"?>';
	
	$rdf .= '<rdf:RDF';
	$rdf .= ' xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" ';
	$rdf .= ' xmlns:rdfs="http://www.w3.org/2000/01/rdf-schema#" ';
	$rdf .= ' xmlns:dc="http://purl.org/dc/elements/1.1/" ';
	$rdf .= ' xmlns:dcterms="http://purl.org/dc/terms/" ';
	$rdf .= ' xmlns:prism="http://prismstandard.org/namespaces/2.0/basic/" ';
	$rdf .= '>';
	
	// Primary identifier
	$primaryId = '';
	$id = '';
	if (isset($item->doi))
	{
		$id = 'http://bioguid.info/doi:' . $item->doi;
		$primaryId = 'doi';
	}
	if ($id == '')
	{
		if (isset($item->pmid))
		{
			$id = 'http://bioguid.info/pmid:' . $item->pmid;
			$primaryId = 'pmid';
		}
	}
	if ($id == '')
	{
		if (isset($item->hdl))
		{
			$id = 'http://bioguid.info/hdl:' . $item->hdl;
			$primaryId = 'hdl';
		}
	}
	
	$rdf .= '<rdf:Description rdf:about="' . $id . '">';
	
	// Identifiers
	
	if (isset($item->doi))
	{
		$rdf .= '<prism:doi>' . $item->doi . '</prism:doi>';
		$rdf .= '<dc:identifier>' . 'doi:' . htmlspecialchars($item->doi, ENT_NOQUOTES) . '</dc:identifier>';
	}
	
	if (isset($item->pmid))
	{
		if ($primaryId != 'pmid')
		{
			$rdf .= '<dc:identifier rdf:resource="http://bioguid.info/pmid:' . $item->pmid . '" />';
		}
	}
			
	if (isset($item->hdl))
	{
		if ($primaryId != 'hdl')
		{
			$rdf .= '<dc:identifier rdf:resource="http://bioguid.info/hdl:' . $item->hdl . '" />';
		}
	}
		
	// Authors
	$num_authors = count($item->authors);
	if ($num_authors > 0)
	{
		foreach ($item->authors as $author)
		{
			$rdf .= '<dc:creator>' . $author->forename . ' ' . $author->lastname;
			if (isset($author->suffix))
			{
				$rdf .= ' ' . $author->suffix;
			}
			$rdf .= '</dc:creator>';
		}
	}
	
	
	// Bibliographic details
	if (isset($item->atitle))
	{
		$rdf .= '<dc:title>' . htmlspecialchars($item->atitle, ENT_NOQUOTES) . '</dc:title>';
	}
	if (isset($item->title))
	{
		$rdf .= '<prism:publicationName>' . $item->title . '</prism:publicationName>';
	}
	if (isset($item->issn))
	{
		$rdf .= '<prism:issn>' . $item->issn . '</prism:issn>';
	}
	if (isset($item->volume))
	{
		$rdf .= '<prism:volume>' . $item->volume . '</prism:volume>';
	}
	if (isset($item->issue))
	{
		$rdf .= '<prism:number>' . $item->issue . '</prism:number>';
	}
	if (isset($item->spage))
	{
		$rdf .= '<prism:startingPage>' . $item->spage . '</prism:startingPage>';
	}
	if (isset($item->epage))
	{
		$rdf .= '<prism:endingPage>' . $item->epage . '</prism:endingPage>';
	}
	if (isset($item->year))
	{
		$rdf .= '<dc:date>' . $item->year . '</dc:date>';
	}
	if (isset($item->abstract))
	{
		$rdf .= '<dcterms:abstract>' . htmlspecialchars($item->abstract, ENT_NOQUOTES) . '</dcterms:abstract>';
	}
	$rdf .= '</rdf:Description>';
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
		$jacc = $item->issn . ':' . $item->volume . '@' . $item->spage;
		echo '<li style="padding-bottom:4px;">';
		echo '<a class="link" href="jacc/' . $jacc . '" target="_blank">[JACC]' . $jacc . '</a>';
		echo '</li>';
	}
	
	if (
		isset($item->title)
		&& isset($item->year)
		&& isset($item->volume)
		&& isset($item->spage)
		)
	{	
		$openref = $item->title . '/' . $item->year . '/' .  $item->volume . '/' . $item->spage;
		echo '<li style="padding-bottom:4px;">';
		echo '<a class="link" href="openref/' . $openref . '" target="_blank">openref://' . $openref . '</a>';
		echo '</li>';
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
	
	
	echo '</body>';
	echo '</html>';

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
		default:
			break;
	}
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

	echo '</body>';
	echo '</html>';
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

	echo '</body>';
	echo '</html>';
}


//--------------------------------------------------------------------------------------------------

// Here we go...

$parameters = array();

define('DISPLAY_REDIRECT', 	0);	
define('DISPLAY_JSON', 		1);	
define('DISPLAY_HTML', 		2);	
define('DISPLAY_RDF', 		3);	


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
