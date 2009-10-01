<?php

require_once(dirname(__FILE__) . '/ubio_findit.php');

$name = '';
if (isset($_GET['name']))
{
	$name = $_GET['name'];
}

$id = 2475959;

if ($name == '')
{
	// No name
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
"http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
  <title>BHL Timeline Demo</title> 
  
  
<style type="text/css" title="text/css">
<!--
body
{
	font-family: Arial, Verdana, sans-serif;
	font-size:12px;
}
-->
</style> 
</head>
<body onload="onLoad();" onresize="onResize();">

<h1>BHL timeline example</h1>

<p>Simple demonstration of displaying results of a <a href="http://www.biodiversitylibrary.org/">Biodiversity Heritage Library</a> taxon name search using
the <a href="http://www.simile-widgets.org/timeline/">SIMILE Timeline widget</a>.</p>

<h2>Enter taxon name below</h2>

<div><form action="." method="get">
<input style="font-size:18px;background-image:url(images/search.png);background-position-x: 0%;background-position-y: 50%;background-repeat: no-repeat;padding-left: 16px;"  size="40" type="text" name="name" id="name" value="" onkeyup="get_title(this.value)" autocomplete="off" />
<input style="font-size:18px;" type="submit" value="Search" /></form></div>
</body>
</html>
<?	
}
else
{
	// Get uBio namebankid
$id = 0;
$names = ubio_findit($name);

//print_r($names);

foreach($names as $n)
{
	$id = $n['namebankID'];
}


if ($id == 0)
{
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
"http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
  <title>BHL Timeline Demo</title> 
  
  
<style type="text/css" title="text/css">
<!--
body
{
	font-family: Arial, Verdana, sans-serif;
	font-size:12px;
}
-->
</style> 
</head>
<body onload="onLoad();" onresize="onResize();">

<h1>BHL timeline example</h1>

<p>Sorry, the name must be in <a href="http://www.ubio.org">uBio</a> for this tool to work. Please try a new name.</p>

<h2>Enter taxon name below</h2>

<div><form action="." method="get">
<input style="font-size:18px;background-image:url(images/search.png);background-position-x: 0%;background-position-y: 50%;background-repeat: no-repeat;padding-left: 16px;"  size="40" type="text" name="name" id="name" value="" onkeyup="get_title(this.value)" autocomplete="off" />
<input style="font-size:18px;" type="submit" value="Search" /></form></div>
</body>
</html>
<?
}
else
{

	// display timeline
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
"http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
  <title>BHL Timeline Demo</title> 
  
  
<style type="text/css" title="text/css">
<!--
body
{
	font-family: Arial, Verdana, sans-serif;
	font-size:12px;
}
-->
</style>  
  
  
 <script>
      Timeline_ajax_url="timeline_2.3.0/timeline_ajax/simile-ajax-api.js";
     Timeline_urlPrefix='timeline_2.3.0/timeline_js/';       
     Timeline_parameters='bundle=true';
    </script>
    <script src="timeline_2.3.0/timeline_js/timeline-api.js"    
      type="text/javascript">
    </script>
 
 <script type="text/javascript">
        
        var tl;
function onLoad() {
var eventSource1 = new Timeline.DefaultEventSource();


   var bandInfos = [
     Timeline.createBandInfo({
     eventSource:    eventSource1,
         width:          "80%", 
         intervalUnit:   Timeline.DateTime.DECADE, 
         intervalPixels: 100
     }),
     Timeline.createBandInfo({
     	overview:  true,
         width:          "20%", 
         eventSource:    eventSource1,
         intervalUnit:   Timeline.DateTime.CENTURY, 
         intervalPixels: 200
     })
   ];
   
	bandInfos[1].syncWith = 0;
	bandInfos[1].highlight = true;   
   tl = Timeline.create(document.getElementById("my-timeline"), bandInfos);
   
   
	tl.loadJSON("bhl.php?id=<?php echo $id; ?>", function(json, url) {
                eventSource1.loadJSON(json, url);
                });
                
           tl.layout(); // display the Timeline

   
   
 }

 var resizeTimerID = null;
 function onResize() {
     if (resizeTimerID == null) {
         resizeTimerID = window.setTimeout(function() {
             resizeTimerID = null;
             tl.layout();
         }, 500);
     }
 }</script>
</head>
<body onload="onLoad();" onresize="onResize();">

<h1>BHL timeline example</h1>

<p>Simple demonstration of displaying results of a <a href="http://www.biodiversitylibrary.org/">Biodiversity Heritage Library</a> taxon name search using
the <a href="http://www.simile-widgets.org/timeline/">SIMILE Timeline widget</a>.</p>

<h2>Enter taxon name below</h2>
<div><form action="." method="get">
<input style="font-size:18px;background-image:url(images/search.png);background-position-x: 0%;background-position-y: 50%;background-repeat: no-repeat;padding-left: 16px;" size="40" type="text" name="name" id="name" value="<?php echo $name; ?>" onkeyup="get_title(this.value)" autocomplete="off" />
<input style="font-size:18px;" type="submit" value="Search" /></form></div>

<p/>

<div id="my-timeline" style="height: 500px; border: 1px solid #aaa"></div>

<p>This tool only displays references that have a date associated with them. To see the complete set of BHL results you can 
<a href="http://www.biodiversitylibrary.org/name/<?php echo urlencode($name); ?>" target="_new">repeat your search on the BHL site</a>.</p>


<noscript>
This page uses Javascript to show you a Timeline. Please enable Javascript in your browser to see the full page. Thank you.
</noscript></body>
</html>




<?php
}
}
?>