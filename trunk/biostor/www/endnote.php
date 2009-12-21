<?php

/**
 * @file endnote.php
 *
 * Explain how to use with EndNote page
 *
 */
 
require_once ('../config.inc.php');
require_once (dirname(__FILE__) . '/html.php');

global $config;

header("Content-type: text/html; charset=utf-8\n\n");
echo html_html_open();
echo html_head_open();

echo html_title('EndNote - ' . $config['site_name']);
echo html_head_open();
echo html_head_close();
echo html_body_open();
echo html_page_header(false);

?>
<div style="float:right;padding:10px;"><img src="static/endnote.png" width="48" height="48"/></div><h1>Using BioStor with EndNote</h1>

<p>You can link to BioStor references from within <a href="http://www.endnote.com/">EndNote&reg;</a>.</p>

<h2>Set up OpenURL linking</h2>
<p>To enable OpenURL linking go to <b>EndNote Preferences</b> and click 
on <b>OpenURL</b>. You should see a dialog box similar to this one (the example is from EndNote 9 on Mac OS X):</p>

<div style="text-align:center;"><img src="static/endnote_openurl.png" width="400"/></div>

<p>Do the following:
<ol>
<li>Check <b>Enable OpenURL</b></li>
<li>Set the <b>OpenURL Path</b> to http://biostor.org/openurl.php</li>
<li>Click on <b>Save</b></li>
</ol>
</p>

<h2>Use OpenURL linking</h2>

<p>To use OpenURL linking, simply open an individual reference in EndNote and click on the <b>Open URL Link</b> command in the <b>References</b> menu. For example, here is a reference from the <a href="http://decapoda.nhm.org/references/library.html">Decapoda Tree of Life EndNote library</a>:</p>

<div style="text-align:center;"><img src="static/endnote_reference.png" width="400"/></div>

<p>If we click on the <b>References</b> menu we see the <b>Open URL Link</b> command (not to be confused with the <b>Open URL</b> command):</p>

<div style="text-align:center;"><img src="static/endnote_menu.png" width="300"/></div>

<p>If you click on <b>Open URL Link</b> EndNote will take you to your default web browser and load the OpenURL, in this case</p>

<a href="http://biostor.org/openurl.php?sid=ISI:WoS&aufirst=M.J.&aulast=Rathbun&atitle=Descriptions%20of%20new%20decapod%20crustaceans%20from%20the%20west%20coast%20of%20North%20America&title=Proceedings%20of%20the%20United%20States%20National%20Museum&volume=24&issue=1272&date=1902&spage=885&epage=-905">http://biostor.org/openurl.php?sid=ISI:WoS<br/>&aufirst=M.J.<br/>&aulast=Rathbun&atitle=Descriptions%20of%20new%20decapod%20crustaceans%20from%20the%20west%20coast%20of%20North%20America<br/>&title=Proceedings%20of%20the%20United%20States%20National%20Museum<br/>&volume=24<br/>&issue=1272<br/>&date=1902<br/>&spage=885<br/>&epage=-905</a>


<?php
echo html_body_close();
echo html_html_close();	
?>