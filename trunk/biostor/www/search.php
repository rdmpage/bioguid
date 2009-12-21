<?php

/**
 * @file search.php
 *
 * Search 
 *
 */


require_once ('../config.inc.php');
require_once ('../db.php');
require_once (dirname(__FILE__) . '/html.php');
require_once ('../lib.php');


function search_name($query, &$search_results)
{
	global $db;
	
	$sql = 'SELECT DISTINCT(NameBankID), NameConfirmed 
	FROM bhl_page_name
	WHERE (NameConfirmed LIKE ' . $db->qstr($query) . ') LIMIT 10';
	
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);

	$search_results->max_score = 0;
	$search_results->min_score = 1000;
	
	while (!$result->EOF) 
	{
		$hit = new stdclass;
		$hit->snippet = $result->fields['NameConfirmed'];
		$hit->object_id = $result->fields['NameBankID'];
		$hit->object_type = 'name';
		
		$hit->score = 1;

		$search_results->hits[] = $hit;
		$search_results->max_score = max($search_results->max_score, $hit->score);
		$search_results->min_score = min($search_results->min_score, $hit->score);

		$result->MoveNext();
	}
	
/*	echo '<pre>';
	print_r($search_results);
	echo '</pre>';*/
	return $search_results;
}

function search_author ($query, &$search_results)
{
	global $db;	

	$sql = 'SELECT *, MATCH(object_text) AGAINST(' . $db->qstr($query) . ')
AS score FROM rdmp_text_index
WHERE MATCH(object_text) AGAINST(' . $db->qstr($query) . ') 
AND (object_type="author")
LIMIT 20';
		
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);

	$search_results->max_score = 0;
	$search_results->min_score = 1000;
	
	while (!$result->EOF) 
	{
		$hit = new stdclass;
		$hit->snippet = $result->fields['object_text'];
		$hit->object_id = $result->fields['object_id'];
		$hit->object_type = $result->fields['object_type'];
		$hit->score = $result->fields['score'];

		$search_results->hits[] = $hit;
		$search_results->max_score = max($search_results->max_score, $hit->score);
		$search_results->min_score = min($search_results->min_score, $hit->score);

		$result->MoveNext();
	}
	
	/*echo '<pre>';
	print_r($search_results);
	echo '</pre>';*/
	
}
	



$query = '';
$category = 'all';

if (isset($_GET['q']))
{
	$query = $_GET['q'];
	
	if (isset($_GET['category']))
	{
		$category = $_GET['category'];
		switch ($category)
		{
			case 'author':
			case 'name':
			case 'all':
				// Accept category
				break;
				
			default:
				// Not a recognised category, so fall through to 'all'
				$category = 'all';
				break;
		}
	
	}
	
	echo html_html_open();
	echo html_head_open();
	echo html_title ($query . ' - ' . $config['site_name']);
	echo html_head_close();
	echo html_body_open();
	echo html_page_header(true, $query, $category);	
	echo '<h1>Search</h1>';
	
	if ($query == '')
	{
		echo '<p>Please enter a query</p>';
		echo html_body_close();
		echo html_html_close();		
		exit();
	}


	$search_results	= new stdclass;
	$search_results->hits = array();
	
	switch ($category)
	{
		case 'name':
			search_name($query, $search_results);
			break;

		case 'author':
			search_author($query, $search_results);
			break;
			
		default:
			echo '<p>Not a recognised category</p>';
			break;
	}
	
	
	
	// Style using ideas from http://www.alistapart.com/articles/accessibledatavisualization/
	// Display percentage score from min to max so we have visual sense of what the best hits are
	echo '<ol class="chartlist">';
	foreach ($search_results->hits as $hit)
	{

		$snippet = $hit->snippet;
		
		/*
		foreach ($q_array as $q)
		{
			$snippet = str_replace($q, "<<<" . $q . ">>>", $snippet);
		}
		$snippet = str_replace("<<<", "<span style=\"color:black\">", $snippet);
		$snippet = str_replace(">>>", "</span>", $snippet);
		*/
		
		if ($search_results->min_score == $search_results->max_score)
		{
			$percentage = 0;
		}
		else
		{
			$percentage = round(100 * ($hit->score - $search_results->min_score)/($search_results->max_score - $search_results->min_score), 2);
			$percentage = $percentage / 2.0; // halve so we still see score
		}
		
//		echo '<li>' . '<a href="title/' . $hit->id .'">' .  $snippet . ' [' . $hit->id . ']' . '</a>';
		echo '<li>' . '<a href="' . $config['web_root'] . $hit->object_type . '/' . $hit->object_id .'">' .  $snippet . '</a>';
		echo '<span class="count">' . round($hit->score, 2) . '</span>';
		echo '<span class="index" style="width: ' . $percentage . '%">(' . $percentage . '%)</span>';
		echo '</li>';
	}
	echo '</ol>';
		
	

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