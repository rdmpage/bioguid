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
$config['db_user'] 		= 'root';
$config['db_passwd'] 	= '';
$config['db_name'] 		= 'bioguid';



// Directories--------------------------------------------------------------------------------------
$config['adodb_dir'] 	= 'adodb5/adodb.inc.php'; 


// Proxy settings for connecting to the web---------------------------------------------------------

// Set these if you access the web through a proxy server. This
// is necessary if you are going to use external services such
// as PubMed.
$config['proxy_name'] 	= '';
$config['proxy_port'] 	= '';

//$config['proxy_name'] 	= 'wwwcache.gla.ac.uk';
//$config['proxy_port'] 	= '8080';

// Service keys--------------------------------------------------------------------------------------	

$config['ubio_keyCode']		= '<your ubio key';

	
?>
