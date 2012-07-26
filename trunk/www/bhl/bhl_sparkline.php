<?php

require_once(dirname(__FILE__) . '/lib.php');
require_once(dirname(__FILE__) . '/bhl_date.php');

/*$id = 0;
if (isset($_GET['id']))
{
	$id = $_GET['id'];
}
else
{
	header ("Content-type: text/plain; charset=utf-8\n\n");
	echo '{}';
	exit();
}*/

function sparkline($id)
{

//$id = 2475959;

//$id = 530114;

//$id = 4799126;

//$id=4799142;

//$id=2707985;
//$id=31333;
//$id=3851985;

//$id = 27222;

//$id=2478573;

$url = 'http://www.biodiversitylibrary.org/services/name/NameService.ashx?op=NameGetDetail&nameBankID=' . $id . '&format=json';

$url = 'http://www.biodiversitylibrary.org/api/httpquery.ashx?op=NameGetDetail&nameBankID=' . $id . '&format=json';

//echo $url;

//exit();


$json = get($url);

$obj = json_decode($json);
//print_r($obj);

$years = array();
$decades = array();


foreach ($obj->Result->Titles as $title)
{
	
	// Try and get date for title
	$title_info = new stdclass;
	if (bhl_date_from_details($title->PublicationDetails, $title_info))
	{
		//print_r($title_info);
	}
	
	foreach ($title->Items as $item)
	{
		
		$info = new stdclass;
		if (parse_bhl_date($item->VolumeInfo, $info))
		{
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
				
			// Years
			echo $event->start . ' ' . $event->end . ' ';
			if (isset($event->end))
			{
				$weight = 1/($event->end - $event->start + 1);
				
				echo $weight . '<br />';
				for ($i = $event->start; $i <= $event->end; $i++)
				{
					
					if (!isset($years[$i]))
					{
						$years[$i] = 0;
					}
					$years[$i] += $weight;
				}
			}
			else
			{
				echo  '1<br />';
				if (!isset($years[$event->start]))
				{
					$years[$event->start] = 0;
				}
				$years[$event->start]++;
			}
			
			/*
			// Decades
			if (isset($event->end))
			{
				for ($i = $event->start; $i <= $event->end; $i++)
				{
					$weight = 1/($event->end - $event->start);
				
					$d = floor($i / 10) * 10;
				
					if (!isset($decades[$d]))
					{
						$decades[$d] = 0;
					}
					$decades[$d]++;
				}
			}
			else
			{
				$d = floor($event->start/ 10) * 10;
				if (!isset($decades[$d]))
				{
					$decades[$d] = 0;
				}
				$decades[$d]++;
			}
			*/
			
		}
		else
		{
			// Didn't get dates	
		}
		
	
	}
}

print_r($years);

/*
$url = 'http://chart.apis.google.com/chart?chs=400x100&cht=ls&chco=0077CC&chm=B,e6f2fa,0,0.0,0.0&chd=t:';
//$chxl = '&chtx=x&chxl=0:';

$max_items = 0;
foreach ($years as $k => $v)
{
	$max_items = max($max_items, $v);
}

$count = 0;
for ($i = 1700; $i < 2000; $i++)
{
	if ($count > 0) { $url .= ','; }
	if (isset($years[$i]))
	{
		$url .= round(($years[$i] * 100.0)/$max_items);
	}
	else
	{
		$url .= '0';
	}
	$count++;
}

echo $url;
*/

//print_r($decades);

$url = 'http://chart.apis.google.com/chart?chs=400x100&cht=ls&chco=0077CC&chm=B,e6f2fa,0,0.0,0.0&chd=t:';
//$chxl = '&chtx=x&chxl=0:';

// Aggregate into decades
$decades = array();
foreach ($years as $k => $v)
{
	$d = floor($k / 10) * 10;

	if (!isset($decades[$d]))
	{
		$decades[$d] = 0;
	}
	$decades[$d] += $v;
}

$max_items = 0;
foreach ($decades as $k => $v)
{
	$max_items = max($max_items, $v);
}

$count = 0;
for ($i = 1750; $i < 2010; $i+= 10)
{
	if ($count > 0) { $url .= ','; }
	if (isset($decades[$i]))
	{
		$url .= round(($decades[$i] * 100.0)/$max_items);
	}
	else
	{
		$url .= '0';
	}
	$count++;
}

$url .= '&chxt=x,y&chxl=0:|1750|1800|1850|1900|1950|2000|1:||' . $max_items;

for ($i = 1750; $i < 2010; $i+= 10)
{
	echo "$i|";
	if (isset($decades[$i]))
	{
		echo  $decades[$i];
	}
	else
	{
		
	}
	echo "<br/>";
}

return $url;

}



//header ("Content-type: text/plain; charset=utf-8\n\n");
//echo json_encode($timeline);


?>