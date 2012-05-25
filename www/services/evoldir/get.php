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
	
	$output = $msg->body;
	
	// From http://jmrware.com/articles/2010/linkifyurl/linkify.php
	// via http://stackoverflow.com/questions/5461702/regex-to-find-url-in-a-text/5463604#5463604
	
	$url_pattern = '/# Rev:20100913_0900 github.com\/jmrware\/LinkifyURL
# Match http & ftp URL that is not already linkified.
  # Alternative 1: URL delimited by (parentheses).
  (\()                     # $1  "(" start delimiter.
  ((?:ht|f)tps?:\/\/[a-z0-9\-._~!$&\'()*+,;=:\/?#[\]@%]+)  # $2: URL.
  (\))                     # $3: ")" end delimiter.
| # Alternative 2: URL delimited by [square brackets].
  (\[)                     # $4: "[" start delimiter.
  ((?:ht|f)tps?:\/\/[a-z0-9\-._~!$&\'()*+,;=:\/?#[\]@%]+)  # $5: URL.
  (\])                     # $6: "]" end delimiter.
| # Alternative 3: URL delimited by {curly braces}.
  (\{)                     # $7: "{" start delimiter.
  ((?:ht|f)tps?:\/\/[a-z0-9\-._~!$&\'()*+,;=:\/?#[\]@%]+)  # $8: URL.
  (\})                     # $9: "}" end delimiter.
| # Alternative 4: URL delimited by <angle brackets>.
  (<|&(?:lt|\#60|\#x3c);)  # $10: "<" start delimiter (or HTML entity).
  ((?:ht|f)tps?:\/\/[a-z0-9\-._~!$&\'()*+,;=:\/?#[\]@%]+)  # $11: URL.
  (>|&(?:gt|\#62|\#x3e);)  # $12: ">" end delimiter (or HTML entity).
| # Alternative 5: URL not delimited by (), [], {} or <>.
  (                        # $13: Prefix proving URL not already linked.
    (?: ^                  # Can be a beginning of line or string, or
    | [^=\s\'"\]]          # a non-"=", non-quote, non-"]", followed by
    ) \s*[\'"]?            # optional whitespace and optional quote;
  | [^=\s]\s+              # or... a non-equals sign followed by whitespace.
  )                        # End $13. Non-prelinkified-proof prefix.
  ( \b                     # $14: Other non-delimited URL.
    (?:ht|f)tps?:\/\/      # Required literal http, https, ftp or ftps prefix.
    [a-z0-9\-._~!$\'()*+,;=:\/?#[\]@%]+ # All URI chars except "&" (normal*).
    (?:                    # Either on a "&" or at the end of URI.
      (?!                  # Allow a "&" char only if not start of an...
        &(?:gt|\#0*62|\#x0*3e);                  # HTML ">" entity, or
      | &(?:amp|apos|quot|\#0*3[49]|\#x0*2[27]); # a [&\'"] entity if
        [.!&\',:?;]?        # followed by optional punctuation then
        (?:[^a-z0-9\-._~!$&\'()*+,;=:\/?#[\]@%]|$)  # a non-URI char or EOS.
      ) &                  # If neg-assertion true, match "&" (special).
      [a-z0-9\-._~!$\'()*+,;=:\/?#[\]@%]* # More non-& URI chars (normal*).
    )*                     # Unroll-the-loop (special normal*)*.
    [a-z0-9\-_~$()*+=\/#[\]@%]  # Last char can\'t be [.!&\',;:?]
  )                        # End $14. Other non-delimited URL.
/imx';
$url_replace = '$1$4$7$10$13<a href="$2$5$8$11$14">$2$5$8$11$14</a>$3$6$9$12';

	$output = preg_replace($url_pattern, $url_replace, $output);

	
	// From http://daringfireball.net/2009/11/liberal_regex_for_matching_urls
	//$output = preg_replace('/\b(([\w-]+:\/\/?|www[.])[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|\/)))/', '<a href="$0">$0</a>', $output);
	//$output = preg_replace('/(https?):\/\/(.*)(\b|\))/', '<a href="$0">$0</a>', $output);
	//$output = preg_replace('/\b(.*)@(.*)\b/', '$1[at]$2', $output);
	
	//$output = preg_replace('/<a href="(.*)">/U', '<a href="http://$1">', $output);
	
	
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
<hr />
<p><a href="http://bioguid.info/services/evoldir/">About</a></p>
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
