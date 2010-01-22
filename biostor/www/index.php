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
require_once (dirname(__FILE__) . '/sparklines.php');


global $config;

header("Content-type: text/html; charset=utf-8\n\n");
echo html_html_open();
echo html_head_open();

//echo html_include_link('application/rdf+xml', 'RSS 1.0', 'rss.php?format=rss1', 'alternate');
echo html_include_link('application/atom+xml', 'ATOM', 'rss.php?format=atom', 'alternate');

echo html_title($config['site_name']);
echo html_head_close();
echo html_body_open();
echo html_page_header(true);

// How many pages?

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

echo '<div style="float:right;padding:10px;">' . "\n";

echo '<table cellpadding="4">' . "\n";
echo '<tr><td style="font-size:20px;color:rgb(128,128,128);">References</td><td style="font-size:20px;text-align:right;">';
//<img src="' . sparkline_cummulative_articles_added() . '" alt="sparkline" />'
echo $num_references .'</td></tr>' . "\n";
echo '<tr><td style="font-size:20px;color:rgb(128,128,128);">Authors</td><td style="font-size:20px;text-align:right;">' . $num_authors . '</td></tr>';
echo '<tr><td style="font-size:20px;color:rgb(128,128,128);">Journals</td><td style="font-size:20px;text-align:right;">' . $num_journals . '</td></tr>' . "\n";
echo '<tr><td style="font-size:20px;color:rgb(128,128,128);">Participants</td><td style="font-size:20px;text-align:right;">' . $num_editors . '</td></tr>' . "\n";

echo '<tr><td colspan="2"><img src="' . sparkline_references('', 360,100) . '" alt="sparkline" align="top"/></td></tr>' . "\n";


echo '</table>' . "\n";

echo '</div>' . "\n";

echo '<h1>What is BioStor?</h1>' . "\n";


echo '<p>BioStor provides tools for extracting, annotating, and visualising literature from the <a href="http://www.biodiversitylibrary.org/">Biodiversity Heritage Library</a> (and other sources). It builds on ideas developed for <a href="http://bioguid.info">bioGUID</a> (see <a href="http://dx.doi.org/10.1186/1471-2105-10-S14-S5">doi:10.1186/1471-2105-10-S14-S5</a>).</p>' . "\n";

/*
echo '<ul>';
echo '<li>Find references using <a href="openurl.php">Reference finder</a></li>';
echo '<li>Start browsing <a href="reference/1">references</a>, <a href="author/1">authors</a>, or <a href="name/2706186">taxon names</a></li>';
echo '</ul>';

echo '<h2>Finding references using BioStor</h2>
*/

echo '<p>The main purpose of BioStor is to find articles in the <a href="http://www.biodiversitylibrary.org/">Biodiversity Heritage Library</a>. To get started you can read the <a href="guide.php">guide to using BioStor</a>, or go directly to the <a href="openurl.php">Reference Finder</a>. You can also use BioStor to find references from within <a href="endnote.php">EndNote</a> and <a href="zotero.php">Zotero</a>. If you use the Firefox web browser you could install the <a href="referrer.php">OpenURL Referrer add on</a>, which will add the same functionality to sites that support support COinS, such as <a href="mendeley.php">Mendeley</a>.</p>
' . "\n";

echo '<p>BioStor is a project by <a href="http://iphylo.blogspot.com">Rod Page</a>. For data sources see <a href="credits.php">Credits</a>.</p>
' . "\n";

echo '<h1>Progress</h1>';

echo '<p>Numbers of articles per year</p>' . "\n";


echo '<h2>Articles</h2>' . "\n";

echo '<p>Number of articles per journal (<a href="journals.php">more...</a>). Get most recently added articles as a <a href="http://biostor.org/rss.php?format=atom">RSS feed</a>.</p>' . "\n";

	
$sql = 'SELECT secondary_title, issn, COUNT(reference_id) AS c
FROM rdmp_reference
GROUP BY issn
ORDER BY c DESC
LIMIT 5';

echo '<table border="0">' . "\n";
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

$sql = 'SELECT secondary_title, issn, COUNT(reference_id) AS c
FROM rdmp_reference
GROUP BY issn
ORDER BY c DESC
LIMIT 5,5';

echo '<tr>' . "\n";

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
echo '</tr>' . "\n";
echo '</table>' . "\n";

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

echo '<p>Most prolific authors:</p>' . "\n";
echo '<ol>' . "\n";

while (!$result->EOF) 
{
	echo '<li>';
	echo '<a href="' . $config['web_root'] . 'author/' . $result->fields['author_id'] . '">' 
	. $result->fields['forename']  . ' ' .  $result->fields['lastname'] . '</a> ' 
	. $result->fields['c'] . ' articles';
	echo '</li>' . "\n";
	
	$result->MoveNext();		
}

echo '</ol>' . "\n";

echo '<p>Some notable authors (images from Wikipedia)</p>';
echo '<table>';
echo '<tr>';

echo '<!-- Nathan Banks -->';
echo '<td>';
echo '<table>';
echo '<tr><td><a href="author/2234">Nathan Banks</a></td></tr>';
echo '<tr><td><img src="images/people/150px-Banks_NathanUSDA-SEL-AcariB.jpg" height="128" /></td></tr>';
echo '<tr><td><a href="http://en.wikipedia.org/wiki/Nathan_Banks">Wikipedia</a></td></tr>';
echo '</table>';
echo '</td>';

echo '<!-- Lipke_Holthuis -->';
echo '<td>';
echo '<table>';
echo '<tr><td><a href="author/489">Lipke Holthuis</a></td></tr>';
echo '<tr><td><div style="height:128px;width:100px;border:1px solid rgb(228,228,228);"><p/></div></td></tr>';
echo '<tr><td><a href="http://en.wikipedia.org/wiki/Lipke_Holthuis">Wikipedia</a></td></tr>';
echo '</table>';
echo '</td>';

echo '<!-- David_Starr_Jordan -->';
echo '<td>';
echo '<table>';
echo '<tr><td><a href="author/10203">David Starr Jordan</a></td></tr>';
echo '<tr><td><img src="images/people/File-Dsjordan.jpeg" height="128" /></td></tr>';
echo '<tr><td><a href="http://en.wikipedia.org/wiki/David_Starr_Jordan">Wikipedia</a></td></tr>';
echo '</table>';	
echo '</td>';



echo '<!-- Mary Rathbun -->';
echo '<td>';
echo '<table>';
echo '<tr><td><a href="author/1">Mary Rathbun</a></td></tr>';
echo '<tr><td><div style="height:128px;width:100px;border:1px solid rgb(228,228,228);"><p/></div></td></tr>';
echo '<tr><td><a href="http://en.wikipedia.org/wiki/Mary_Rathbun">Wikipedia</a></td></tr>';
echo '</table>';
echo '</td>';

echo '<!-- Hobart Smith -->';
echo '<td>';
echo '<table>';
echo '<tr><td><a href="author/830">Hobart M Smith</a></td></tr>';
echo '<tr><td><div style="height:128px;width:100px;border:1px solid rgb(228,228,228);"><p/></div></td></tr>';
echo '<tr><td><a href="http://en.wikipedia.org/wiki/Hobart_Muir_Smith">Wikipedia</a></td></tr>';
echo '</table>';
echo '</td>';

echo '<!-- James_Edward_Smith -->';
echo '<td>';
echo '<table>';
echo '<tr><td><a href="author/2701">James Edward Smith</a></td></tr>';
echo '<tr><td><img src="images/people/180px-James_Edward_Smith.jpg" height="128" /></td></tr>';
echo '<tr><td><a href="http://en.wikipedia.org/wiki/James_Edward_Smith">Wikipedia</a></td></tr>';
echo '</table>';
echo '</td>';



echo '</tr>';
echo '</table>';

echo '<h1>Localities</h1>';
echo '<p>Localities extracted from articles (<a href="kml.php">click here</a> for Google Earth KML file).</p>';

echo '
<!--[if IE]>
<embed width="360" height="180" src="map_references.php">
</embed>
<![endif]-->
<![if !IE]>
<object id="mysvg" type="image/svg+xml" width="360" height="180" data="map_references.php">
<p>Error, browser must support "SVG"</p>
</object>
<![endif]>';





echo '<hr />' . "\n";

echo '<div id="recentcomments" class="dsq-widget"><h2 class="dsq-widget-title">Recent Comments</h2><script type="text/javascript" src="http://disqus.com/forums/biostor/recent_comments_widget.js?num_items=5&hide_avatars=0&avatar_size=32&excerpt_length=200"></script></div><a href="http://disqus.com/">Powered by Disqus</a>';


echo html_body_close();
echo html_html_close();	


?>