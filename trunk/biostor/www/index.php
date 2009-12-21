<?php

/**
 * @file index.php
 *
 * Home page
 *
 */
 
require_once ('../config.inc.php');
require_once ('../db.php');
require_once (dirname(__FILE__) . '/html.php');


global $config;

header("Content-type: text/html; charset=utf-8\n\n");
echo html_html_open();
echo html_head_open();

//echo html_include_link('application/rdf+xml', 'RSS 1.0', 'rss.php?format=rss1', 'alternate');
echo html_include_link('application/atom+xml', 'ATOM', 'rss.php?format=atom', 'alternate');

echo html_title($config['site_name']);
echo html_head_open();
echo html_head_close();
echo html_body_open();
echo html_page_header(false);


echo '<p>BioStor provides tools for extracting, annotating, and visualising literature
from the <a href="http://www.biodiversitylibrary.org/">Biodiversity Heritage Library</a>.</p>';

// How many articles?
$sql = 'SELECT COUNT(reference_id) AS c FROM rdmp_reference';
$result = $db->Execute($sql);
if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

$num_references = $result->fields['c'];

// How many authors?
$sql = 'SELECT COUNT(DISTINCT(author_id))	 AS c
FROM rdmp_author
INNER JOIN rdmp_author_reference_joiner USING(author_id)
WHERE (lastname <> "") AND (forename <> "")';

$result = $db->Execute($sql);
if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

$num_authors = $result->fields['c'];

echo '<table>';
echo '<tr><td style="font-size:32px">References</td><td <td style="font-size:32px">' . $num_references . '</td></tr>';
echo '<tr><td style="font-size:32px">Authors</td><td <td style="font-size:32px">' . $num_authors . '</td></tr>';

echo '</table>';
echo '<h3>Getting started</h3>

<p>You can get started by going directly to the <a href="openurl.php">Reference Finder</a>, or you can read the <a href="guide.php">guide to using BioStor</a>.
</p>

<h3>Using BioStor with EndNote, Zotero, and Firefox</h3>
<p>You can use BioStor to find references from within <a href="endnote.php">EndNote</a> and <a href="zotero.php">Zotero</a>. If you use the Firefox web browser you could install the <a href="referrer.php">OpenURL Referrer add on</a>, which will add the same functionality to sites that support support COinS, such as <a href="mendeley.php">Mendeley</a>.</p>
';


echo '<h3>About</h3>
<p>BioStor is a project by <a href="http://iphylo.blogspot.com">Rod Page</a>. For data sources see <a href="credits.php">Credits</a>.</p>
';


echo html_body_close();
echo html_html_close();	


?>