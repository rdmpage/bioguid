<?php

require_once(dirname(__FILE__) . '/ubio_findit.php');
require_once(dirname(__FILE__) . '/bhl_sparkline.php');

$name1 = '';
if (isset($_GET['name1']))
{
	$name1 = $_GET['name1'];
}

$name2 = '';
if (isset($_GET['name2']))
{
	$name2 = $_GET['name2'];
}

if (($name1 == '') && ($name2 == ''))
{
	// No names
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
"http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
  <title>BHL Sparkline Demo</title> 
  
  
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
<body>

<h1>BHL sparkline example</h1>

<p>Simple demonstration of displaying results of a <a href="http://www.biodiversitylibrary.org/">Biodiversity Heritage Library</a> taxon name search using
a sparkline.</p>

<p>You can enter one taxon name, or two if you want to compare how many times two names appear in BHL (for example, you could compare two synonyms).</p>

<h2>Enter taxon names below</h2>

<div> <form action="compare.php" method="get">
<input style="font-size:18px;background-image:url(images/search.png);background-position-x: 0%;background-position-y: 50%;background-repeat: no-repeat;padding-left: 16px;" type="text" name="name1" id="name1" value="" onkeyup="get_title(this.value)"  />
<input style="font-size:18px;background-image:url(images/search.png);background-position-x: 0%;background-position-y: 50%;background-repeat: no-repeat;padding-left: 16px;" type="text" name="name2" id="name2" value="" onkeyup="get_title(this.value)"  />
<input style="font-size:18px;" type="submit" value="Search" /></form></div>
</body>
</html>
<?	
}
else
{
	// Get uBio namebankid
$id1 = 0;
$names = ubio_findit($name1);

//print_r($names);

foreach($names as $n)
{
	$id1 = $n['namebankID'];
}

$id2 = 0;

if ($name2 != '')
{
	$names = ubio_findit($name2);
	
	//print_r($names);
	
	foreach($names as $n)
	{
		$id2 = $n['namebankID'];
	}
}

if ($id1 == 0)
{
	
	
}
else
{

	// display sparkline
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
  
 <body>

<h1>BHL sparkline example</h1>

<p>Simple demonstration of displaying results of a <a href="http://www.biodiversitylibrary.org/">Biodiversity Heritage Library</a> taxon name search using
a sparkline.</p>

<p>You can enter one taxon name, or two if you want to compare how many times two names appear in BHL (for example, you could compare two synonyms).</p>

<h2>Enter taxon names below</h2>
<div> <form action="compare.php" method="get">
<input style="font-size:18px;background-image:url(images/search.png);background-position-x: 0%;background-position-y: 50%;background-repeat: no-repeat;padding-left: 16px;" type="text" name="name1" id="name1" value="<?php echo $name1; ?>" onkeyup="get_title(this.value)"  />
<input style="font-size:18px;background-image:url(images/search.png);background-position-x: 0%;background-position-y: 50%;background-repeat: no-repeat;padding-left: 16px;" type="text" name="name2" id="name2" value="<?php echo $name2; ?>" onkeyup="get_title(this.value)"  />
<input style="font-size:18px;" type="submit" value="Search" /></form></div>

<p/>
<div style="padding:20px;">
<div style="border-bottom:1px dotted rgb(192,192,192);padding:20px;">
<?php
echo '<h2>' . $name1 . '</h2>';
echo '<p><a href="index.php?name=' . $name1 . '">View timeline</a></p>';
$url = sparkline($id1);
echo '<img src="' . $url . '" />';
?>
</div>

<?php

if ($id2 != 0)
{
	echo '<div style="border-bottom:1px dotted rgb(192,192,192);padding:20px;">';
	echo '<h2>' . $name2 . '</h2>';
	echo '<p><a href="index.php?name=' . $name2 . '">View timeline</a></p>';
	
	$url = sparkline($id2);
	echo '<img src="' . $url . '" />';
	echo '</div>';
}
?>

</div>

</body>
</html>




<?php
}
}
?>