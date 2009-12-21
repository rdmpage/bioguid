<?php

/**
 * @file credits.php
 *
 * Credits page
 *
 */
 
require_once ('../config.inc.php');
require_once (dirname(__FILE__) . '/html.php');


global $config;

header("Content-type: text/html; charset=utf-8\n\n");
echo html_html_open();
echo html_head_open();
echo html_title('Credits - ' . $config['site_name']);
echo html_head_open();
echo html_head_close();
echo html_body_open();
echo html_page_header(false);

echo '<h1>Credits</h1>
<h2>Data</h2>
<h3>Biodiversity Heritage Library</h3>
<p>The core data for BioStor comes from the <a href="http://www.biodiversitylibrary.org">Biodiversity Heritage Library</a> (BHL). <a href="http://twitter.com/chrisfreeland">Chris Freeland</a>, <a href="http://twitter.com/fak3r">Phil Cryer</a>, and Mike Lichtenberg provided data dumps and other assistance.</p>
<p>Page images supplied by BHL are available under a <a href= http://creativecommons.org/licenses/by-nc/2.5/">Creative Commons Attribution-Noncommercial 2.5 Generic License</a></p>
<p>
<img src="static/share.png" />
<img src="static/remix.png" />
</p>
<h3>Catalogue of Life</h3>
<p>A database dump from the 2008 edition of the <a href="http://www.catalogueoflife.org/">Catalogue of Life</a>, available as an <a href="http://documents.sp2000.org/AC/ISOImages/AnnualChecklist2008.iso">ISO image</a>.</p>
<h3>JSTOR</h3>
<p>A list of <a href="http://www.jstor.org">JSTOR</a> publications was obtained from JSTOR.</p>
';

echo '<h2>Code</h2>
<p>In developing BioStor I made use of a number of code libraries and snippets. These include:
<ul>
<li><a href="http://adodb.sourceforge.net/">ADOdb Database Abstraction Library for PHP (and Python)</a></li>
<li><a href="http://www.bram.us/projects/js_bramus/lazierload/">lazierLoad</a> by Bram Van Damme.</li>
<li><a href="http://www.lokeshdhakar.com">Lightbox 2</a> by Lokesh Dhakar, as modified by Fabian Lange to provide <a href="http://blog.hma-info.de/2008/04/09/latest-lightbox-v2-with-automatic-resizing/">automatic resizing</a></li>
<li><a href="http://recaptcha.net/plugins/php/">PHP CAPTCHA library for reCAPTCHA</a></li>
<li><a href="http://www.prototypejs.org/">Prototype Javascript framework</a></li>
</ul>
';

echo html_body_close();
echo html_html_close();	


?>