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
$config['web_server']	= 'http://localhost';
$config['site_name']	= 'BioStor';

// Files--------------------------------------------------------------------------------------------
$config['web_dir']		= dirname(__FILE__) . '/www';
$config['web_root']		= '/~rpage/biostor/www/';


// Database-----------------------------------------------------------------------------------------
$config['adodb_dir'] 	= dirname(__FILE__).'/adodb5/adodb.inc.php'; 
$config['db_user'] 	    = '';
$config['db_passwd'] 	= '';
$config['db_name'] 	    = '';


// Directories--------------------------------------------------------------------------------------
$config['tmp_dir'] 	    = dirname(__FILE__) . '/tmp'; 
$config['cache_prefix'] = 'cache';
$config['cache_dir'] 	= $config['web_dir'] . '/' . $config['cache_prefix']; 

// Proxy settings for connecting to the web---------------------------------------------------------

// Set these if you access the web through a proxy server. This
// is necessary if you are going to use external services such
// as PubMed.
$config['proxy_name'] 	= '';
$config['proxy_port'] 	= '';

$config['proxy_name'] 	= 'wwwcache.gla.ac.uk';
$config['proxy_port'] 	= '8080';

// Tools--------------------------------------------------------------------------------------------

$config['convert'] 	= '/usr/local/bin/convert';

// Keys---------------------------------------------------------------------------------------------

// GMap 
$config['gmap'] 				= "";

// Recaptcha

// biostor.org
$config['recaptcha_publickey'] 	= "";
$config['recaptcha_privatekey']	= "";

?>