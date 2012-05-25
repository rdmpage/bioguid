<?php

require_once('../config.inc.php');
require_once('../' . $config['adodb_dir']);

$interval = 3600; // sampling interval in seconds

$db = NewADOConnection('mysql');
$db->Connect("localhost", 
	$config['db_user'], $config['db_passwd'], $config['db_name']);

// Ensure fields are (only) indexed by column name
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;


// from http://uk.php.net/manual/en/function.time.php#85128 

/*
* PHP port of Ruby on Rails famous distance_of_time_in_words method. 
*  See http://api.rubyonrails.com/classes/ActionView/Helpers/DateHelper.html for more details.
*
* Reports the approximate distance in time between two timestamps. Set include_seconds 
* to true if you want more detailed approximations.
*
*/
function distanceOfTimeInWords($from_time, $to_time = 0, $include_seconds = false) {
	$distance_in_minutes = round(abs($to_time - $from_time) / 60);
	$distance_in_seconds = round(abs($to_time - $from_time));

	if ($distance_in_minutes >= 0 and $distance_in_minutes <= 1) {
		if (!$include_seconds) {
			return ($distance_in_minutes == 0) ? 'less than a minute' : '1 minute';
		} else {
			if ($distance_in_seconds >= 0 and $distance_in_seconds <= 4) {
				return 'less than 5 seconds';
			} elseif ($distance_in_seconds >= 5 and $distance_in_seconds <= 9) {
				return 'less than 10 seconds';
			} elseif ($distance_in_seconds >= 10 and $distance_in_seconds <= 19) {
				return 'less than 20 seconds';
			} elseif ($distance_in_seconds >= 20 and $distance_in_seconds <= 39) {
				return 'half a minute';
			} elseif ($distance_in_seconds >= 40 and $distance_in_seconds <= 59) {
				return 'less than a minute';
			} else {
				return '1 minute';
			}
		}
	} elseif ($distance_in_minutes >= 2 and $distance_in_minutes <= 44) {
		return $distance_in_minutes . ' minutes';
	} elseif ($distance_in_minutes >= 45 and $distance_in_minutes <= 89) {
		return 'about 1 hour';
	} elseif ($distance_in_minutes >= 90 and $distance_in_minutes <= 1439) {
		return 'about ' . round(floatval($distance_in_minutes) / 60.0) . ' hours';
	} elseif ($distance_in_minutes >= 1440 and $distance_in_minutes <= 2879) {
		return '1 day';
	} elseif ($distance_in_minutes >= 2880 and $distance_in_minutes <= 43199) {
		return 'about ' . round(floatval($distance_in_minutes) / 1440) . ' days';
	} elseif ($distance_in_minutes >= 43200 and $distance_in_minutes <= 86399) {
		return 'about 1 month';
	} elseif ($distance_in_minutes >= 86400 and $distance_in_minutes <= 525599) {
		return round(floatval($distance_in_minutes) / 43200) . ' months';
	} elseif ($distance_in_minutes >= 525600 and $distance_in_minutes <= 1051199) {
		return 'about 1 year';
	} else {
		return 'over ' . round(floatval($distance_in_minutes) / 525600) . ' years';
	}
}

function sparkline($service_id)
{
	global $db;
	
	$sql = 'SELECT * FROM status
WHERE service_id = ' . $service_id . '
ORDER BY tested DESC
LIMIT 100';

	$result = $db->Execute($sql);
	if ($result == false) die("failed"); 

	$sparks = array();
	$max_time = 20;
	
	while (!$result->EOF) 
	{
		switch ($result->fields['status'])
		{
			case 200:
				if ($result->fields['total_time'] != '')
				{
					$t = 100 - round(($result->fields['total_time'] * 100.0)/$max_time);
					array_push($sparks, $t);
				}
				break;
			default: // time out
				if ($result->fields['total_time'] != '')
				{
					array_push($sparks, 0);
				}
				break;
		}

		$result->MoveNext();
	}

	//print_r($sparks);
	
	$s = 'http://chart.apis.google.com/chart?chs=100x20&amp;cht=ls&amp;chco=0077CC&amp;chm=B,E6F2FA,0,0,0&amp;chls=1,0,0&amp;chd=t:';
	
	$count = 0;
	foreach ($sparks as $spk)
	{
		if ($count != 0)
		{
			$s .= ',';
		}
		$s .= $spk;
		$count++;
	}
	return $s;
	

}

function show_table($title, $result)
{
	echo "<tr><td colspan=\"5\" ><br/><br/><b>$title</b></td></tr>". "\n";
	
	while (!$result->EOF) 
	{
	
		echo '<tr style="background-color:';
		switch ($result->fields['status'])
		{
			case '200':
				echo 'white';
				break;
				
			default:
				echo 'rgb(255,187,187)';
				break;
		}
		echo '">'. "\n";
	
		
		echo '<td style="border-bottom:1px dotted rgb(128,128,128);">';
		switch ($result->fields['status'])
		{
			case '200':
				echo '<img src="../images/accept.png" alt="accept"/>';
				break;
			case '404':
				echo '<img src="../images/delete.png" alt="delete"/>';
				break;
				
			default:
				echo '<img src="../images/error.png" alt="error"/>';
				break;
		}
			
		echo '</td>' . "\n";
		
		echo '<td style="border-bottom:1px dotted rgb(128,128,128);" align="right">' . $result->fields['status'] . '</td>';
		
		$name = $result->fields['name'];
		$name = str_replace("&", "&amp;", $name);
		$name = str_replace("<", "&lt;", $name);
		$name = str_replace(">", "&gt;", $name);
		
		echo '<td style="border-bottom:1px dotted rgb(128,128,128);">' . $name . '</td>'. "\n";
		echo '<td style="border-bottom:1px dotted rgb(128,128,128);"><a href="' . str_replace("&", "&amp;", $result->fields['url']) . '" target="_blank">' . $result->fields['url'] . '</a></td>'. "\n";


		// Sparkline
		//sparkline($result->fields['service_id']);
		
		


		echo '<td style="border-bottom:1px dotted rgb(128,128,128);background-color:white" align="right">' . '<img  src="' . sparkline($result->fields['service_id']) . '" alt="sparkline"/></td>'. "\n";
		echo '<td style="border-bottom:1px dotted rgb(128,128,128);background-color:white" align="right">';
		
		if ($result->fields['total_time'] == 0)
		{
			echo '-';
		}
		else
		{
			echo $result->fields['total_time'];
		}
		
		echo '</td>' . "\n";
	
		echo '</tr>'. "\n";
	
		$result->MoveNext();
	}
}	


		

//header ("Content-type: text/html; charset=utf-8\n\n");
//echo '<html>';
//echo '<head>';
//echo '<title>Biodiversity Services Status</title>';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title>Biodiversity Services Status</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

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
    </style>


<?php
echo '</head>';
echo '<body style="font-family:verdana,Arial,sans-serif;font-size:12px">';
echo '<h1>Status of biodiversity services</h1>';

?>

<div class="blueRect" style="width:100%">
	<div class="top">
		<div class="cn tl"></div>
		<div class="cn tr"></div>
	</div>
	<div class="middle">

<p>This page shows the status of various sources of biodiversity information. Each site is checked every hour. The inspiration was <a href="http://bigdig.ecoforge.net/">The Big Dig</a>, which sadly doesn't seem to have been updated in a while, and <a href="http://www.istwitterdown.com/">Is Twitter Down?</a></p>

<p>For each service there is a sparkline showing how the service has performed over time. The higher the line the quicker the service. Timeouts or errors result in troughs. For example:</p>

<table cellpadding="2" cellspacing="0">
<tbody style="background-color:white;font-family:verdana,Arial,sans-serif;font-size:12px;">
<tr><td>Fast response, always online</td><td><img  src="http://chart.apis.google.com/chart?chs=100x20&amp;cht=ls&amp;chco=0077CC&amp;chm=B,E6F2FA,0,0,0&amp;chls=1,0,0&amp;chd=t:98,98,98,98,98,98,97,98,96,98,97,98,93,98,95,99" alt=""/></td></tr>
<tr><td>Slow response, always online</td><td><img  src="http://chart.apis.google.com/chart?chs=100x20&amp;cht=ls&amp;chco=0077CC&amp;chm=B,E6F2FA,0,0,0&amp;chls=1,0,0&amp;chd=t:35,34,33,17,39,0,33,36,36,32,37,36,36,33,35,35,16" alt=""/></td></tr>

<tr><td>Service that was offline, then online, then off again, then on</td><td><img  src="http://chart.apis.google.com/chart?chs=100x20&amp;cht=ls&amp;chco=0077CC&amp;chm=B,E6F2FA,0,0,0&amp;chls=1,0,0&amp;chd=t:0,0,0,0,0,0,0,0,98,98,98,98,90,98,0,98,96" alt=""/></td></tr>
</tbody>
</table>


<p>Comments are welcome (see <a href="#bottom">bottom of web page</a>), or directly by email to <a href="mailto:r.page@bio.gla.ac.uk">r.page@bio.gla.ac.uk</a>.</p>
	</div>
	<div class="bottom">
		<div class="cn bl"></div>
		<div class="cn br"></div>
	</div>
</div>


<?php
/*

$sql = 'SELECT * FROM status
ORDER BY tested DESC
LIMIT 1';

$result = $db->Execute($sql);
if ($result == false) die("failed"); 


//echo strtotime($result->fields['tested']) . ' ' . time();

echo '<p>Services queried ' . distanceOfTimeInWords(strtotime($result->fields['tested']) ,time(),true) . ' ago.</p>'. "\n";
*/

echo "<table cellpadding=\"3\" cellspacing=\"0\" width=\"100%\">". "\n";
echo '<tbody style="font-family:verdana,Arial,sans-serif;font-size:12px">'. "\n";
echo '<tr><th></th><th>Service</th><th>URL</th><th>Status</th><th>History</th><th>Latest response time&nbsp;(s)</th></tr>'. "\n";


// Main web sites
$sql = 'SELECT * FROM services
INNER JOIN status ON services.id = status.service_id
WHERE (kind=\'Web site\')
AND (tested > NOW() - INTERVAL ' . $interval . ' SECOND)
ORDER BY name';

$result = $db->Execute($sql);
if ($result == false) die("failed"); 

show_table('Web sites', $result);

if (1)
{

	// LSIDs
	$sql = 'SELECT * FROM services
	INNER JOIN status ON services.id = status.service_id
	WHERE (kind=\'LSID\')
	AND (tested > NOW() - INTERVAL ' . $interval . ' SECOND)
	ORDER BY name';
	
	$result = $db->Execute($sql);
	if ($result == false) die("failed"); 
	
	show_table('LSID authorities', $result);


	// Bibliographic databases
	$sql = 'SELECT * FROM services
	INNER JOIN status ON services.id = status.service_id
	WHERE (kind=\'Bibliographic database\')
	AND (tested > NOW() - INTERVAL ' . $interval . ' SECOND)
	ORDER BY name';
	
	$result = $db->Execute($sql);
	if ($result == false) die("failed"); 
	
	show_table('Bibliographic databases', $result);
	
	// OAI
	$sql = 'SELECT * FROM services
	INNER JOIN status ON services.id = status.service_id
	WHERE (kind=\'OAI\')
	AND (tested > NOW() - INTERVAL ' . $interval . ' SECOND)
	ORDER BY name';
	
	$result = $db->Execute($sql);
	if ($result == false) die("failed"); 
	
	show_table('OAI', $result);
	
	
	// DIGIR
	$sql = 'SELECT * FROM services
	INNER JOIN status ON services.id = status.service_id
	WHERE (kind=\'DIGIR\')
	AND (tested > NOW() - INTERVAL ' . $interval . ' SECOND)
	ORDER BY name';
	
	$result = $db->Execute($sql);
	if ($result == false) die("failed"); 
	
	show_table('DiGIR providers', $result);
}
echo "</tbody>". "\n";
echo "</table>". "\n";
echo '<hr/>'. "\n";

if (1)
{
	echo '<h3>Key to symbols and colours</h3>';
	echo "<table cellpadding=\"3\" cellspacing=\"0\">";
	echo '<tbody style="font-family:verdana,Arial,sans-serif;font-size:12px">';
	echo '<tr>';
	echo '<td><img src="../images/accept.png" alt="accept"/></td>';
	echo '<td>200</td>';
	echo '<td>Everything seems to be OK</td>';
	echo '</tr>';
	echo '<tr style="background-color:rgb(255,187,187)">';
	echo '<td><img src="../images/delete.png" alt="error"/></td>';
	echo '<td>404</td>';
	echo '<td>Service has gone, vanished, just like that</td>';
	echo '</tr>';
	
	echo '<tr style="background-color:rgb(255,187,187)">';
	echo '<td><img src="../images/error.png" alt="error"/></td>';
	echo '<td>500</td>';
	echo '<td>Internal server error (something is seriously wrong with the service)</td>';
	echo '</tr>';
	
	
	
	echo '<tr style="background-color:rgb(255,187,187)">';
	echo '<td><img src="../images/error.png" alt="error"/></td>';
	echo '<td><i>nn</i></td>';
	echo '<td>Service timed out, either service is offline, or slow to respond (number is CURL error number)</td>';
	echo '</tr>';
	echo "</tbody>";
	echo "</table>";
	echo '<hr/>';
}
?>
<!-- this must be a <a name=""></a> tag, simply closing <a name="" /> breaks disqus for IE -->
<p><a name="bottom"></a></p>
<div id="disqus_thread"></div>
<script type="text/javascript" src="http://disqus.com/forums/bioguid/embed.js">
</script>
<p><a href="http://disqus.com" class="dsq-brlink">blog comments powered by <span class="logo-disqus">Disqus</span></a></p>
<?php

echo '</body>';
echo '</html>';

	

?>
