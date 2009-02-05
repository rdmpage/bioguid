<?php

// $Id: //

/**
 * @file config.php
 *
 * Global configuration variables (may be added to by other modules).
 *
 */

global $config;


// Server
$config['server']   	= 'http://bioguid.info/'; //http://localhost';
//$config['webroot'] 	= $config['server']  . '/~rpage/op/';
$config['webroot'] 		= $config['server']; 
$config['web_dir']		= dirname(__FILE__) . '/';


// Database
$config['db_user'] 		= '<databse username>';
$config['db_passwd'] 	= '<database password>';
$config['db_name'] 		= 'bioguid';



// Directories------------------------------------------------------------------
$config['tmp_dir'] 		= '/tmp'; 
$config['adodb_dir'] 	= 'adodb5/adodb.inc.php'; 
$config['cache_prefix'] = 'cache';
$config['cache_dir'] 	= $config['web_dir'] . $config['cache_prefix']; 
$config['cache_time'] 	= 86400; 

// PDF
$config['pdf_dir'] 		= $config['web_dir'] . 'pdf'; 
$config['web_pdf_dir'] 	= $config['webroot'] . 'pdf'; 

// Thumbnails
$config['thumb_dir'] 		= $config['web_dir'] . 'thumbnails'; 
$config['web_thumb_dir'] 	= $config['webroot'] . 'thumbnails'; 


// XSLT installation------------------------------------------------------------

// If your PHP installation has XSLT support built in leave as ''. 
// If not (such as RedHat 8), set to the path to your copy of sabcmd (e.g.,
// '/usr/local/bin/sabcmd'). To get the path, type 
// 'locate sabcmd' at the system prompt.

$config['sabcmd'] 		= '/usr/local/bin/sabcmd';
//$config['sabcmd'] 	= '';

// Imagemagik------------------------------------------------------------------
$config['convert']		= '/usr/local/bin/convert';




// Proxy settings for connecting to the web----------------

// Set these if you access the web through a proxy server. This
// is necessary if you are going to use external services such
// as PubMed.
$config['proxy_name'] 	= '';
$config['proxy_port'] 	= '';

$config['proxy_name'] 	= 'wwwcache.gla.ac.uk';
$config['proxy_port'] 	= '8080';


// Service accounts
$config['crossref_user']	= 'ourl_rdmpage';
$config['crossref_pass']	= 'peacrab';

$config['ubio_keyCode']		= 'b751aac2219cf30bcf3190d607d7c9494d87b77c'

	
?>
