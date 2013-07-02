<?php

/**
 * @file config.php
 *
 * Global configuration variables (may be added to by other modules).
 *
 */

global $config;

// Date timezone
date_default_timezone_set('Europe/London');

// Default encoding
mb_internal_encoding("UTF-8");

// Server-------------------------------------------------------------------------------------------
$config['web_server']	= 'http://biostor.org';
$config['site_name']	= 'BioStor';

// Files--------------------------------------------------------------------------------------------
$config['web_dir']		= dirname(__FILE__) . '/www';
$config['web_root']		= 'http://biostor.org/';


// Database-----------------------------------------------------------------------------------------
$config['adodb_dir'] 	= dirname(__FILE__).'/adodb5/adodb.inc.php'; 
$config['db_user'] 	    = 'biostor';
$config['db_passwd'] 	= '';
$config['db_name'] 	    = 'biostor';


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
$config['mogrify'] 	= '/usr/local/bin/mogrify';

// Keys---------------------------------------------------------------------------------------------

// GMap 
// biostor.org
$config['gmap'] = "ABQIAAAAk2P0FJHPZEvUQyY4pt_aIRQWxjAv2aC2DlfYqj-s4AIYom3A9RQrzF4CLCZbuxUmclW7GSTDMt1N3w";

// Recaptcha

// biostor.org
$config['recaptcha_publickey'] 	= "6LcaDQoAAAAAAFV-boLJD9NT-tZNbm_jIL4SGYwx";
$config['recaptcha_privatekey']	= "6LcaDQoAAAAAAGfDVXOhC9nu0G7sl6ZeXtfpaPmj";

// Mendeley api
define('CONSUMER_KEY', 'cd1634437de8f30a429210b45678647b04c62a4d4');
define('CONSUMER_SECRET', '0514d79a665a64bb2b382df1db362250');
define('OAUTH_CALLBACK', 'http://biostor.org/callback.php');

// Twitter------------------------------------------------------------------------------------------

// Twitter API @biostor_org
$config['twitter']						= true;
$config['twitter_oauth_token'] 			= '97419527-AAMvDUeW5J9SAU3eCqvk2vFrZlwTeA4LHdaPbWML0';
$config['twitter_oauth_token_secret'] 	= 'fLBJsKc9j2ge6UMYdN8fixuAYIG82dWJKrIifLhg';

$config['twitter_consumer_key'] 		= 'Z7F1ES5WrcNjoeRXI3ZVjg';
$config['twitter_consumer_secret'] 		= 'Qnc7q48f0bzVJmqe3Ec1t1wPPDJInYMRwY6aRn1af8';

// BHL API
$config['bhl_api_key']  				= '9bbd2d7a-74db-4f3a-a438-a0e30db9d001';


// Flags--------------------------------------------------------------------------------------------

$config['use_mendeley_oauth'] 	= true;
$config['use_disqus'] 			= true;
$config['use_uservoice'] 		= true;
$config['use_gbif'] 			= true;

$config['fetch_images'] 		= true; // false to suppress fetching of images, useful when adding lots of references

?>
