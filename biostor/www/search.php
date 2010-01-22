<?php

/**
 * @file (new) search.php
 *
 * Search 
 *
 */


require_once ('../config.inc.php');
require_once ('../db.php');
require_once (dirname(__FILE__) . '/html.php');
require_once ('../lib.php');


function search($query)
{
	global $db;
	
	$sql = 'SELECT *, MATCH(object_text) AGAINST(' . $db->qstr($query) . ')
AS score FROM rdmp_text_index
WHERE MATCH(object_text) AGAINST(' . $db->qstr($query) . ')
ORDER BY score DESC';

	$hits = array();
	
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);	
	while (!$result->EOF) 
	{
		$hit = new stdclass;
		
		$type = $result->fields['object_type'];
		
		if (!isset($hits[$type]))
		{
			$hits[$type] = array();
		}
		$hit = new stdclass;
		$hit->uri = $result->fields['object_uri'];
		$hit->snippet = $result->fields['object_text'];
		
		$hits[$type][] = $hit;
		$result->MoveNext();
	}
	return $hits;
}


$query = '';

if (isset($_GET['q']))
{
	$query = $_GET['q'];
	
	echo html_html_open();
	echo html_head_open();
	echo html_title ($query . ' - ' . $config['site_name']);
	echo html_head_close();
	echo html_body_open();
	echo html_page_header(true, $query);	
	echo '<h1>Search &quot;' .  $query . '&quot;</h1>';
	
	if ($query == '')
	{
		echo '<p>Please enter a query</p>';
		echo html_body_close();
		echo html_html_close();		
		exit();
	}


	$hits = search($query);	
	
	/*	
	echo '<pre>';
	print_r($hits);
	echo '</pre>';
	*/
	
	foreach ($hits as $k => $v)
	{
		echo '<h2>';
		switch ($k)
		{
			case 'author':
				echo "Authors";
				break;
			case 'title':
				echo "Reference";
				break;
			default:
				echo "[Unknown]";
				break;
		}
		echo '</h2>';
		echo '<ol>';
		foreach ($v as $hit)
		{
			echo '<li style="padding:4px;"><a href="' . $hit->uri . '">' . $hit->snippet . '</a></li>';
			//print_r($hit);
		}
		echo '</ol>';
		echo '</li>';
	}

	echo html_body_close();
	echo html_html_close();



}
else
{
	echo html_html_open();
	echo html_head_open();
	echo html_title ('Search');
	echo html_head_close();
	echo html_body_open();
	echo html_page_header(true);	
	echo '<h1>Search</h1>';
	echo html_body_close();
	echo html_html_close();
}



?>