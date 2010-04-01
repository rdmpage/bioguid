<?php

require_once ('../../config.inc.php');
require_once ($config['adodb_dir']);
require_once ('../../utils.php');


// generate JSON...

global $config;
global $ADODB_FETCH_MODE;

$locs = array();

$db = NewADOConnection('mysql');
$db->Connect("localhost", 
	$config['db_user'] , $config['db_passwd'] , $config['db_name']);

// Ensure fields are (only) indexed by column name
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

// get list of localities
$sql = 'SELECT latitude, longitude, name, thumbnail, title, description,pubDate, kind, link, taxon_image_url FROM item
INNER JOIN item_locality_joiner USING (guid)
INNER JOIN locality ON locality.id =  item_locality_joiner.locality_id '
. 'WHERE pubDate > ' . $db->qstr('2009-05-01')
//. 'AND link LIKE "%mapress%"'
//. 'AND link LIKE "%flickr%"'
//. 'AND link LIKE "%bioone%"'
//. 'AND link LIKE "%ncbi%"'
. ' GROUP BY latitude, longitude, pubDate'
. ' ORDER BY pubDate;
';

$result = $db->Execute($sql);
if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);

$items = array();

while (!$result->EOF) 
{
	$item = array();
	
	array_push($item, $result->fields['latitude']);
	array_push($item, $result->fields['longitude']);
	
	// Locality name
	
	$locality =  $result->fields['name'];
	if ($locality == '')
	{
		$locality = format_decimal_latlon($result->fields['latitude'], $result->fields['longitude']);
	}
	array_push($item, $locality);
	
	// image URL and description
	switch ( $result->fields['kind'])
	{
		// paper
		case 0:
			if ($result->fields['thumbnail'] != '')
			{
				array_push($item, $result->fields['thumbnail']);
			}
			else
			{
				array_push($item, 'http://bioguid.info/ebio09/www/images/issn/unknown.png');
			}
			// twitter user name
			array_push($item, '');
			array_push($item, trim_text($result->fields['title']));
			break;
		// image
		case 1:
			array_push($item, $result->fields['thumbnail']);
			// twitter user name
			array_push($item, '');
			array_push($item, $result->fields['title']);
			break;
		// sequences
		case 2:
			array_push($item, 'http://bioguid.info/ebio09/www/images/70px-DNA_icon.svg.png');
			// twitter user name
			array_push($item, '');
			array_push($item, $result->fields['title'] . ' '. trim_text($result->fields['description']));
			break;
			
		default:
			break;
	}
	
	array_push($item, distanceOfTimeInWords(strtotime($result->fields['pubDate']) ,time(),true));
	
	array_push($item, $result->fields['link']);
	array_push($item, $result->fields['taxon_image_url']);
	array_push($items, $item);
	

	$result->MoveNext();
}

//print_r($items);

echo json_encode($items);

?>


	
	
	
