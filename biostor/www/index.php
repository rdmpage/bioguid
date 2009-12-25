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

//
echo html_include_link('application/atom+xml', 'ATOM', 'rss.php?format=atom', 'alternate');

echo html_title($config['site_name']);
echo html_head_close();
echo html_body_open();
echo html_page_header(false);


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

// How many journals?
$sql = 'SELECT COUNT(DISTINCT(issn)) AS c FROM rdmp_reference';

$result = $db->Execute($sql);
if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

$num_journals = $result->fields['c'];

// How many editors (IP)?
$sql = 'SELECT COUNT(DISTINCT(INET_NTOA(ip))) as c FROM rdmp_reference_version';

$result = $db->Execute($sql);
if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);

$num_editors = $result->fields['c'];

echo '<div style="float:right;padding:10px;">';

echo '<table cellpadding="4">';
echo '<tr><td style="font-size:20px;color:rgb(128,128,128);">References</td><td style="font-size:32px;text-align:right;">' . $num_references . '</td></tr>';
echo '<tr><td style="font-size:20px;color:rgb(128,128,128);">Authors</td><td style="font-size:32px;text-align:right;">' . $num_authors . '</td></tr>';
echo '<tr><td style="font-size:20px;color:rgb(128,128,128);">Journals</td><td style="font-size:32px;text-align:right;">' . $num_journals . '</td></tr>';
echo '<tr><td style="font-size:20px;color:rgb(128,128,128);">Participants</td><td style="font-size:32px;text-align:right;">' . $num_editors . '</td></tr>';
echo '</table>';

echo '</div>';

echo '<h1>What is BioStor?</h1>';


echo '<p>BioStor provides tools for extracting, annotating, and visualising literature from the <a href="http://www.biodiversitylibrary.org/">Biodiversity Heritage Library</a> (and other sources). It builds on ideas developed for <a href="http://bioguid.info">bioGUID</a> (see <a href="http://dx.doi.org/10.1186/1471-2105-10-S14-S5">doi:10.1186/1471-2105-10-S14-S5</a>).</p>';

echo '<ul>';
echo '<li>Find references using <a href="openurl.php">Reference finder</a></li>';
echo '<li>Start browsing <a href="reference/1">references</a>, <a href="author/1">authors</a>, or <a href="name/2706186">taxon names</a></li>';
echo '</ul>';

echo '<h1>Progress</h1>';

echo '<h2>Articles</h2>';

echo '<p>Number of articles per journal (<a href="journals.php">more...</a>). Get most recently added articles as a <a href="http://biostor.org/rss.php?format=atom">RSS feed</a>.</p>';

	
$sql = 'SELECT secondary_title, issn, COUNT(reference_id) AS c
FROM rdmp_reference
GROUP BY issn
ORDER BY c DESC
LIMIT 5';

echo '<table border="0">';
echo '<tr>';

$result = $db->Execute($sql);
if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);

while (!$result->EOF) 
{
	echo '<td valign="top" align="center">';
	echo '<div><a href="' . $config['web_root'] . 'issn/' . $result->fields['issn'] .'"><img src="http://bioguid.info/issn/image.php?issn=' . $result->fields['issn']  . '" alt="cover" style="border:1px solid rgb(228,228,228);height:100px;" /></a></div>';
	echo '<div><a href="' . $config['web_root'] . 'issn/' . $result->fields['issn'] .'">' . $result->fields['secondary_title'] . '</a></div>';
	echo $result->fields['c'] . '&nbsp;articles';
	echo '</td>';
	
	$result->MoveNext();		
}
echo '</tr>';
echo '</table>';

echo '<h2>Authors</h2>';

// Most prolific authors...

$sql = 'SELECT COUNT(rdmp_reference.reference_id) AS c, rdmp_author.author_id, rdmp_author.forename, rdmp_author.lastname 
FROM rdmp_reference
INNER JOIN rdmp_author_reference_joiner USING (reference_id)
INNER JOIN rdmp_author USING (author_id)
GROUP BY (rdmp_author.author_cluster_id)
ORDER BY c DESC
LIMIT 10';

$result = $db->Execute($sql);
if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);

echo '<ol>';

while (!$result->EOF) 
{
	echo '<li>';
	echo '<a href="' . $config['web_root'] . 'author/' . $result->fields['author_id'] . '">' 
	. $result->fields['forename']  . ' ' .  $result->fields['lastname'] . '</a> ' 
	. $result->fields['c'] . ' articles';
	echo '</li>';
	
	$result->MoveNext();		
}

echo '</ol>';


echo '<h1>Finding references using BioStor</h1>

<p>The main purpose of BioStor is to find articles in the <a href="http://www.biodiversitylibrary.org/">Biodiversity Heritage Library</a>. To get started you can read the <a href="guide.php">guide to using BioStor</a>, or go directly to the <a href="openurl.php">Reference Finder</a>. You can also use BioStor to find references from within <a href="endnote.php">EndNote</a> and <a href="zotero.php">Zotero</a>. If you use the Firefox web browser you could install the <a href="referrer.php">OpenURL Referrer add on</a>, which will add the same functionality to sites that support support COinS, such as <a href="mendeley.php">Mendeley</a>.</p>
';


echo '<h1>About</h1>
<p>BioStor is a project by <a href="http://iphylo.blogspot.com">Rod Page</a>. For data sources see <a href="credits.php">Credits</a>.</p>
';


echo html_body_close();
echo html_html_close();	


?>