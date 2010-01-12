<?php

// Sparklines

/**
 * @file sparklines.php
 *
 */

require_once ('../db.php');

define(START_DATE, '2009-12-20');

// get number of days since project started
function days_since_start()
{
	$date_diff = time() - strtotime(START_DATE);
	$time_span = floor($date_diff/(60*60*24));
	return $time_span;
}

// Generate a sparkline (normalises values)
function make_sparkline($values,$max_value,$width=200,$height=50)
{
	// Normalise
	$n = count($values);
	for ($i = 0; $i < $n; $i++)
	{
		$values[$i] = round(($values[$i] * 100.0)/$max_value);
	}

	$url = 'http://chart.apis.google.com/chart?chs=' . $width . 'x' . $height . '&cht=ls&chco=0077CC&chm=B,e6f2fa,0,0.0,0.0&chd=t:';

	$url .= join(",", $values);
	
	return $url;
}

// Sparkline of articles added to a journal, units are days
function sparkline_articles_added_for_issn($issn)
{
	global $db;
	
	// Get daily counts of articles created since start of project
	$sql = 'SELECT count(reference_id) AS c, year(created), month(created), day(created), 
	datediff(created, ' . $db->qstr(START_DATE) . ') AS days, created FROM rdmp_reference 
	WHERE issn=' . $db->qstr($issn) . '
	GROUP BY day(created)
	ORDER BY created';
	
	$time_span = days_since_start();
	
	// Initialise array
	$count = array();
	for ($i = 0; $i < $time_span; $i++)
	{
		$count[$i] = 0;
	}
	
	$max_count = 0;
	
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
	
	while (!$result->EOF) 
	{
		$count[$result->fields['days']] = $result->fields['c'];
		$max_count = max($max_count, $result->fields['c']);
		$result->MoveNext();
	}
	
	return make_sparkline($count, $max_count, 100, 50);
}

// Sparkline of articles added to database
function sparkline_cummulative_articles_added()
{
	global $db;
	
	// Get daily counts of articles created since start of project
	$sql = 'SELECT count(reference_id) AS c, year(created), month(created), day(created), 
	datediff(created, ' . $db->qstr(START_DATE) . ') AS days, created FROM rdmp_reference 
	GROUP BY day(created)
	ORDER BY created';
	
	$time_span = days_since_start();
	
	// Initialise array
	$count = array();
	for ($i = 0; $i < $time_span; $i++)
	{
		$count[$i] = 0;
	}
	
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
	
	$running_total = 0;
	
	while (!$result->EOF) 
	{
		$running_total += $result->fields['c'];
		$count[$result->fields['days']] = $running_total;
		$result->MoveNext();
	}
	
	for ($i = 1; $i < $time_span; $i++)
	{
		if ($count[$i] == 0)
		{
			$count[$i] = $count[$i-1];
		}
	}
	
	
	return make_sparkline($count, $running_total, 80, 30);
}


// test
if (0)
{
$issn = '0006-9698';
sparkline_articles_added_for_issn($issn);
}


?>
