<?php

require_once(dirname(__FILE__) . '/lib.php');
require_once(dirname(__FILE__) . '/bhl_date.php');

$id = 0;
if (isset($_GET['id']))
{
	$id = $_GET['id'];
}
else
{
	header ("Content-type: text/plain; charset=utf-8\n\n");
	echo '{}';
	exit();
}

//$id = 2475959;

/*$id = 530114;

$id = 4799126;

$id=4799142;

$id=2707985;
$id=31333;
$id=3851985;*/

$url = 'http://www.biodiversitylibrary.org/services/name/NameService.ashx?op=NameGetDetail&nameBankID=' . $id . '&format=json';

$json = get($url);

$obj = json_decode($json);
//print_r($obj);

/*
var timeline_data = {  // save as a global variable
'dateTimeFormat': 'iso8601',
'wikiURL': "http://simile.mit.edu/shelf/",
'wikiSection': "Simile Cubism Timeline",

'events' : [
        {'start': '1974',
        'title': 'Miscellaneous publication - University of Kansas, Museum of Natural History.',
        'description':'pages 60, 64',
        'image': 'http://images.allposters.com/images/AWI/NR096_b.jpg',
        'link': 'http://www.biodiversitylibrary.org/item/25843'
        },
        
        {'start': '1921',
        'title': 'Proceedings of the Biological Society of Washington.',
        'description':'pages <a href="http://www.biodiversitylibrary.org/page/3332443" target="_new">161</a>, 195',
        'image': 'http://images.allposters.com/images/AWI/NR096_b.jpg',
        'link': 'http://www.biodiversitylibrary.org/item/22866'
        },

]
}
*/

$timeline = new stdclass;

$timeline->dateTimeFormat = "iso8601";
$timeline->events = array();

foreach ($obj->NameResult->Titles as $title)
{
	//echo $title->TitleID . ' ' . $title->PublicationTitle . ' ' . count($title->Items) . "\n";
	
	// Try and get date for title
	$title_info = new stdclass;
	if (bhl_date_from_details($title->PublicationDetails, $title_info))
	{
		//print_r($title_info);
	}
	
	foreach ($title->Items as $item)
	{
		//echo '  ' . $item->VolumeInfo . "\n";
		
		$info = new stdclass;
		if (parse_bhl_date($item->VolumeInfo, $info))
		{
			//print_r($info);
		}
		
		if (isset($title_info->start) || isset($info->start))
		{			
			$event = new stdclass;
					
			if (isset($info->start))
			{
				$event->start = $info->start;
				if (isset($info->end))
				{
					$event->end = $info->end;
				}
			}
			else
			{
				$event->start = $title_info->start;
				if (isset($title_info->end))
				{
					$event->end = $title_info->end;
				}
			}
			
			// Link is to item
			$event->link = 'http://www.biodiversitylibrary.org/item/' . $item->ItemID;
			
			// Title is item
			$event->title = $title->PublicationTitle;
			$event->title .= ' [' . count($item->Pages) . ']';
		
			// Description includes pages
			$event->description = $title->PublicationDetails . '<br/>' . $item->VolumeInfo;
			$event->description .= '<ul>';
			foreach ($item->Pages as $page)
			{
				$event->description .= '<li><a href="http://www.biodiversitylibrary.org/page/' . $page->PageID . '" target="_new">' . $page->Prefix . ' ' . $page->Number . '</a></li>';
			}
			$event->description .= '</ul>';
			
			array_push($timeline->events, $event);
		}
		else
		{
			
			//echo "\n*** FAIL ***\n\n";
		}
		
	
	}
}

//print_r($timeline);

header ("Content-type: text/plain; charset=utf-8\n\n");
echo json_encode($timeline);


?>