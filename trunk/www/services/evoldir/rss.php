<?php
// rss feed

require_once('config.inc.php');
require_once('db.php');


$sql = 'SELECT * FROM `message` ORDER BY created DESC LIMIT 50';
$result = $db->Execute($sql);
if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);

$feed = new DomDocument('1.0');
$rss = $feed->createElement('rss');
$rss->setAttribute('version', '2.0');

	$rss->setAttribute('xmlns:geo', 'http://www.w3.org/2003/01/geo/wgs84_pos#');
	$rss->setAttribute('xmlns:georss', 'http://www.georss.org/georss');


$rss = $feed->appendChild($rss);

// Channel
$channel = $feed->createElement('channel');
$channel = $rss->appendChild($channel);


$title = $feed->createElement('title');
$title = $channel->appendChild($title);
$value = $feed->createTextNode('EVOLDIR');
$value = $title->appendChild($value);

// Link
$link = $feed->createElement('link');
$link = $channel->appendChild($link);
$value = $feed->createTextNode('http://evol.mcmaster.ca/evoldir.html');
$value = $link->appendChild($value);

// description
$description = $feed->createElement('description');
$description = $channel->appendChild($description);
$value = $feed->createTextNode('Recent posts to the EVOLDIR mailing list');
$value = $description->appendChild($value);


while (!$result->EOF) 
{
	$item = $feed->createElement('item');
	$item = $channel->appendChild($item);

	
	
	// Title
	$title = $feed->createElement('title');
	$title = $item->appendChild($title);
	$value = $feed->createTextNode(htmlspecialchars($result->fields['subject']));
	$value = $title->appendChild($value);
	
	// Link
	$link = $feed->createElement('link');
	$link = $item->appendChild($link);
	$value = $feed->createTextNode('http://bioguid.info/services/evoldir/get.php?id=' . $result->fields['id'] );
	$value = $link->appendChild($value);

	// GUID
	$guid = $feed->createElement('guid');
	$guid = $item->appendChild($guid);
	$value = $feed->createTextNode('http://bioguid.info/services/evoldir/get.php?id=' . $result->fields['id'] );
	$value = $guid->appendChild($value);

	// Date
	$pubDate = $feed->createElement('pubDate');
	$pubDate = $item->appendChild($pubDate);
	$value = $feed->createTextNode(gmdate(DATE_RFC822, strtotime($result->fields['created'])));
	$value = $pubDate->appendChild($value);
	
	
	$text = $result->fields['body'];
	$output = preg_replace('/(https?):\/\/(.*)(\b|\))/', '<a href="$0">$0</a>', $text);
	$output = preg_replace('/\b(.*)@(.*)\b/', '$1[at]$2', $output);	
	
	//$output = utf8_encode($output);
	
	//$output = substr($output, 0, 100);
	//$output = '<a href="http://bioguid.info/services/evoldir/get.php?id=' . $result->fields['id'] . '>View more</a><br/>' . $output;
	
	
	// Description
	$description = $feed->createElement('description');
	$description = $item->appendChild($description);
	$value = $feed->createTextNode($output);
	$value = $description->appendChild($value);
	
	// geo
	if ($result->fields['latitude'] != null)
	{
		$point = $feed->createElement('georss:point');
		$point = $item->appendChild($point);
		$text = $result->fields['latitude'] . ' ' . $result->fields['longitude'];
		$value = $feed->createTextNode($text);
		$value = $point->appendChild($value);	
	}	
	
	$result->MoveNext();	
}

header("Content-type: text/xml");
echo  $feed->saveXML();

?>
