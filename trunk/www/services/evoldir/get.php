<?php
// include class

require_once('config.inc.php');
require_once('db.php');


$id = 'aa71e3be9d6f09382256049e46533af2';
if (isset($_GET['id']))
{
	$id = $_GET['id'];


}
if ($id != '')


$msg = get_message($id);

if (isset($msg->messageid))
{
	//print_r($msg);
	
	$text = $msg->body;
	
	// From http://daringfireball.net/2009/11/liberal_regex_for_matching_urls
	$output = preg_replace('/\b(([\w-]+:\/\/?|www[.])[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|\/)))/', '<a href="$0">$0</a>', $text);
	//$output = preg_replace('/(https?):\/\/(.*)(\b|\))/', '<a href="$0">$0</a>', $text);
	$output = preg_replace('/\b(.*)@(.*)\b/', '$1[at]$2', $output);
	
	
?>
<html>
<head>
<link rel="icon" href="favicon.ico">
<title><?php echo $msg->subject; ?> - EvolDir</title>
<style type="text/css">
body {
	font-family: Arial, Verdana, sans-serif;
	background-color:rgb(128,128,128);
}
</style>
</head>
<body>
<div style="background-color:white;border:2px solid black;margin:20px;padding:20px;-webkit-border-bottom-left-radius: 9px
9px;-webkit-border-bottom-right-radius: 9px
9px;-webkit-border-top-left-radius: 9px
9px;-webkit-border-top-right-radius: 9px 9px;">
<h1><img src="images/d_bigger.png" align="right"/><?php echo $msg->subject; ?></h1>
<p>From the Evolution Directory (<a href="http://evol.mcmaster.ca/evoldir.html">EvolDir</a>) via <a href="http://twitter.com/evoldir">Twitter</a>.</p>
<hr />
<p><?php echo nl2br($output); ?> </p>
</div>
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
<?

}
else
{
?>
<html>
<head>
<link rel="icon" href="favicon.ico">
<title>Error - EvolDir</title>
</head>
<body>
<h1><?php echo $id; ?></h1>
<p>No message corresponds to id &quot;<?php echo $id; ?>&quot;</p>
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
<?	
	
}






?>
