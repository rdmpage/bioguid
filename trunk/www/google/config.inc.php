<?php

// $Id: //

/**
 * @file config.php
 *
 * Global configuration variables (may be added to by other modules).
 *
 */

global $config;


// Proxy settings for connecting to the web----------------

// Set these if you access the web through a proxy server. This
// is necessary if you are going to use external services such
// as PubMed.
$config['proxy_name'] 	= '';
$config['proxy_port'] 	= '';

$config['proxy_name'] 	= 'wwwcache.gla.ac.uk';
$config['proxy_port'] 	= '8080';

$config['secret_key'] 		= '<GOGGLE KEY>';
$config['twitter_username']	= '<TWITTER USERNAME>';
$config['twitter_password']	= '<TWITTER PASSWORD>';

?>
