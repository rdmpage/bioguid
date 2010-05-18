<?php

// $Id: //

/**
 * @file config.php
 *
 * Global configuration variables (may be added to by other modules).
 *
 */

global $config;

// Server-------------------------------------------------------------------------------------------
$config['web_dir']	= dirname(__FILE__) . '/www/';

$config['web_root']	= 'http://localhost/~rpage/browser/www/';
//$config['web_root']	= 'http://iphylo.org/~rpage/browser/www/';

$config['site_name'] = 'Browser';

// Database-----------------------------------------------------------------------------------------
$config['adodb_dir'] 	= dirname(__FILE__).'/adodb5/adodb.inc.php'; 
$config['db_user'] 	    = 'root';
$config['db_passwd'] 	= '';
$config['db_name'] 	    = 'plos';

// Proxy settings for connecting to the web---------------------------------------------------------

// Set these if you access the web through a proxy server. This
// is necessary if you are going to use external services such
// as PubMed.
$config['proxy_name'] 	= '';
$config['proxy_port'] 	= '';

//$config['proxy_name'] 	= 'wwwcache.gla.ac.uk';
//$config['proxy_port'] 	= '8080';

// Keys---------------------------------------------------------------------------------------------
$config['gmap'] = 'ABQIAAAAk2P0FJHPZEvUQyY4pt_aIRSvftEOPUiKz5aBpUmr8CSVBljEWxSYgNtBN2k3SX-RkJCsOKiTGZtRkg';



	
?>