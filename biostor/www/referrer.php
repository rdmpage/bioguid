<?php

/**
 * @file referrer.php
 *
 * Explain how to use with Firefox
 *
 */
 
require_once ('../config.inc.php');
require_once (dirname(__FILE__) . '/html.php');

global $config;

header("Content-type: text/html; charset=utf-8\n\n");
echo html_html_open();
echo html_head_open();

echo html_title('OpenURL Referrer - ' . $config['site_name']);
echo html_head_open();
echo html_head_close();
echo html_body_open();
echo html_page_header(false);

?>
<div style="float:right;padding:10px;"><img src="static/extensionItem.png" /></div>
<h1>Using BioStor with Firefox OpenURL Referrer Add-on</h1>

<p><a href="https://addons.mozilla.org/en-US/firefox/addon/4150">OpenURL Referrer</a> is a Firefox extension that converts bibliographic citations in the form of <a href="http://ocoins.info/">COinS</a> into URLs.</p>

<h2>Set up OpenURL linking</h2>
<p>Once you have installed OpenURL Referrer in Firefox, go to the <b>Tools</b> menu and choose the <b>Add-ons</b> command. This will display the list of installed Add-ons:</p>

<div style="text-align:center;"><img src="static/addons.png" width="400"/></div>

<p>Select <b>OpenURL Referrer</b> and click on the <b>Preferences</b> button to display the Preferences dialog box:</p>

<div style="text-align:center;"><img src="static/referrer_preferences.png" width="400"/></div>

<p>Do the following:
<ol>
<li>Click on <b>New Profile</b> and in the dialog box that appears set the profile name to "BioStor".</li>
<li>Set the <b>Link Server Base URL</b> to http://biostor.org/openurl.php</li>
<li>In the section <b>Display link as</b> enter some text in the <b>Text</b> field, for example "BioStor".</li>
</ol>
</p>

<h2>Use OpenURL linking</h2>

<p>When you visit a web page that contains COinS the OpenURL referrer will insert a link (labelled "BioStor") into the web page. Clicking on that link will take you to BioStor's OpenURL resolver.</p>



<?php
echo html_body_close();
echo html_html_close();	
?>