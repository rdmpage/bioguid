<?php

/**
 * @file journals.php
 *
 * Search 
 *
 */


require_once ('../config.inc.php');
require_once ('../db.php');
require_once (dirname(__FILE__) . '/html.php');

echo html_html_open();
echo html_head_open();
echo html_title ('Journals - BioStor');
echo html_head_close();
echo html_body_open();
echo html_page_header(false);	
echo '<h1>Journals</h1>';

	global $db;
	
	$sql = 'SELECT secondary_title, issn, COUNT(reference_id) AS c
FROM rdmp_reference
GROUP BY issn
ORDER BY secondary_title';

	$char = 'A';
	
	echo '<ul>';
	echo '<li>' . 'A';
	echo '<ul>';
	
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
	
	while (!$result->EOF) 
	{
		$title = $result->fields['secondary_title'];
		
		if ($title{0} != $char)
		{
			echo '</ul>';
			echo '</li>';
			
			$char = $title{0};
			echo '<li>' . $char;
			echo '<ul>';
		}
		echo '<li style="border-top:1px dotted rgb(128,128,128);">';
		echo '<a href="' . $config['web_root'] . 'issn/' . $result->fields['issn'] .'">' . $result->fields['secondary_title'] . '</a> [' . $result->fields['c'] . ']<br/>';
		echo '<span>' . $result->fields['issn'] . '</span><br/>';
		echo '<a href="' . $config['web_root'] . 'issn/' . $result->fields['issn'] .'"><img src="http://bioguid.info/issn/image.php?issn=' . $result->fields['issn']  . '" alt="cover" style="border:1px solid rgb(228,228,228);height:100px;" /></a>';
		echo '</li>';
		
		$result->MoveNext();		
	}
	echo '</li>';
	echo '</ul>';

echo html_body_close();
echo html_html_close();


?>