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
$config['web_dir']	= dirname(__FILE__) . '/';


// Database-----------------------------------------------------------------------------------------
$config['adodb_dir'] 	= dirname(__FILE__).'/adodb5/adodb.inc.php'; 
$config['db_user'] 	    = 'root';
$config['db_passwd'] 	= '';
$config['db_name'] 	    = 'c2';


// Directories--------------------------------------------------------------------------------------
$config['tmp_dir'] 	    = $config['web_dir'] . 'tmp'; 
$config['cache_prefix'] = 'cache';
$config['cache_dir'] 	= $config['web_dir'] . $config['cache_prefix']; 



// XSLT installation--------------------------------------------------------------------------------

// If your PHP installation has XSLT support built in leave as ''. 
// If not (such as RedHat 8), set to the path to your copy of sabcmd (e.g.,
// '/usr/local/bin/sabcmd'). To get the path, type 
// 'locate sabcmd' at the system prompt.

$config['sabcmd'] 	= ''; // '/usr/local/bin/sabcmd';


// Proxy settings for connecting to the web---------------------------------------------------------

// Set these if you access the web through a proxy server. This
// is necessary if you are going to use external services such
// as PubMed.
$config['proxy_name'] 	= '';
$config['proxy_port'] 	= '';

$config['proxy_name'] 	= 'wwwcache.gla.ac.uk';
$config['proxy_port'] 	= '8080';

// Keys---------------------------------------------------------------------------------------------
// uBio key
$config['uBio_key'] 		='b751aac2219cf30bcf3190d607d7c9494d87b77c'; 

// Metacarta
$config['metacarta_username']	= 'r.page@bio.gla.ac.uk';
$config['metacarta_password']	= 'peacrab';		

// Yahoo! GeoPlanetª
$config['yahoo_where'] 		= 'v5NLgAjV34HJOvOqjnU6HoLlBx_NHrc0P03uYepNniPsN2ZdrEE9zuWUyiNiuuR.LYA-';




	
?>