<?php

//--------------------------------------------------------------------------------------------------
// MySQL
require_once(dirname(__FILE__).'/adodb5/adodb.inc.php');
require_once(dirname(__FILE__).'/config.inc.php');


$name = '';
if (isset($_GET['name']))
{
	$name = $_GET['name'];
	
	global $config;
	global $ADODB_FETCH_MODE;
		
	$obj = new stdclass;
	$obj->results = array();
	$obj->num_results = 0;		
		
	$db = NewADOConnection('mysql');
	$db->Connect("localhost", 
		$config['db_user'], $config['db_passwd'], $config['db_name']);
	
	// Ensure fields are (only) indexed by column name
	$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
	
	
	$sql = 'SELECT * FROM ion_rss
		WHERE (name = ' .  $db->Quote($name) . ') AND (full_publication IS NOT NULL) LIMIT 1';
		
	$result = $db->Execute($sql);
	if ($result == false) die("failed"); 


	echo '<h1>' . $result->fields['name'] . '</h1>';
	echo '<p>';
	
	echo $result->fields['full_publication'];
	
	if ($result->fields['doi'] != '')
	{
		echo '<br/>doi:' . '<a href="http://dx.doi.org/' . $result->fields['doi'] . '">' . $result->fields['doi'] . '</a>';
	}
	if ($result->fields['url'] != '')
	{
		echo '<br/>url:' . '<a href="' . $result->fields['url'] . '">' . $result->fields['url'] . '</a>';
	}
	if ($result->fields['journal'] != '')
	{
		echo '<br/>OpenURL:' . '<a href="http://biostor.org/openurl.php?genre=article'
		. '&atitle=' . urlencode($result->fields['publicationTitle']) 
		. '&title=' . urlencode($result->fields['journal']) 
			. '&volume=' . $result->fields['volume'] . '&spage=' . $result->fields['spage'] . '" target="_new">OpenURL</a>';
	}
	
	echo '</p>';
	
	echo '<p>urn:lsid:organismnames.com:name:' . $result->fields['guid'] . '</p>';
	echo '<p><a href="http://www.organismnames.com/details.htm?lsid=' . $result->fields['guid'] . '">web link</a></p>';
}
else
{
?>

<html>
<head>
<title>ION Lookup</title>


    <style type="text/css">

	body {
		font-family: Arial;
		font-size:14px;
	}
    
	#details
	{
		display: none;
		position:absolute;
		background-color:white;
		border-left: 1px solid rgb(192,192,192);
		border-top: 1px solid rgb(192,192,192);
		border-bottom: 1px solid black;
		border-right: 1px solid black;
	}
	
	.taxon_preview
	{
		background-color:white;
		border-bottom:1px dotted rgb(192,192,192);
		padding:2px;
	}
	.taxon_preview:hover
	{
		background-color:rgb(192,192,192);
	}
	
	
    </style>
    
  <!-- JSONscriptRequest -->
  <script type="text/javascript" src="scripts/jsr_class.js"></script>
  
  <!-- Dynamic web form -->
<script type="text/javascript">

 
//--------------------------------------------------------------------------------------------------
function ws_title(jData) 
{
  if (jData == null) 
  {
    // There was a problem parsing search results
    return;
  }

   //ShowDiv("details");
   
   var details = document.getElementById("details");
  
  	list = '';
    for (i=0; i< jData.results.length; i++) 
    {
 		list += '<div class="taxon_preview"  onclick="setText(\'name\', \'' +  jData.results[i].name + '\');">'
 			+ jData.results[i].name + ' ' + jData.results[i].authorship
 			+ '</div>'
 			;
  	}
  	if (jData.results.length < jData.num_results)
  	{
  		list += '<div>...</div>';
  	}

  	
  	details.innerHTML = list;
  	
  	ShowDiv("details");
}

//--------------------------------------------------------------------------------------------------
function get_title(title) 
{
	var str = title;
	if (str.length == '')
	{
		HideDiv("details");
	}
	else
	{
		if (str.length > 2)
			{
			request = 'lookup.php?name=' + title + '&callback=ws_title';
		
			// Create a new script object
			aObj = new JSONscriptRequest(request);
			// Build the script tag
			aObj.buildScriptTag();
			// Execute (add) the script tag
			aObj.addScriptTag();
		}
		else
		{
			HideDiv("details");
		}
	}
}

//--------------------------------------------------------------------------------------------------
function positionDiv(divid)
{
	var el = document.getElementById('journal');
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


//--------------------------------------------------------------------------------------------------
function ShowDiv(divid)
{
	
   document.getElementById(divid).style.display="block";
   positionDiv(divid);
}

//--------------------------------------------------------------------------------------------------
function HideDiv(divid)
{
   document.getElementById(divid).style.display="none";
}

//--------------------------------------------------------------------------------------------------
function setText(id, str)
{
	document.getElementById(id).value = str;
	HideDiv('details');

}

</script> 

</head>
</body>



<div><form action="." method="get">
<input style="font-size:24px;background-image:url(images/search.png);background-position-x: 0%;background-position-y: 50%;background-repeat: no-repeat;padding-left: 16px;" type="text" name="name" id="name" value="" onkeyup="get_title(this.value)" autocomplete="off" />
<div id="details" ></div>
<input style="font-size:24px;" type="submit" value="Search" /></form></div>

</body>
</html>
<?php
}
?>