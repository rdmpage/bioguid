<?php

/**
 * @file html.php
 *
 * Wrap HTML output
 *
 */

require_once('../config.inc.php');


//--------------------------------------------------------------------------------------------------
function html_html_open()
{
	return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">' 
		. "\n" . '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">' . "\n";
}

//--------------------------------------------------------------------------------------------------
function html_html_close()
{
	return '</html>';
}

//--------------------------------------------------------------------------------------------------
function html_head_open()
{
	global $config;
	
	$html = '<head>' . "\n"
		. '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />' . "\n";
				
	// Base URL for all links on page
	// This is very useful because we use Apache mod_rewrite extensively, and this ensures
	// image URLs can still be written as relative addresses
	$html .= '<base href="' . $config['web_root'] . '" />' . "\n";
	
	// Base URL as Javascript variable (Firefox needs this for some images to load correctly)
	// Plus we can use it in scripts
	$html .= '<!--Webs site root as global Javascript variable so Firefox can find images. -->' . "\n";
	$html .= '<script type="text/javascript">' . "\n";
	$html .= 'var gWebRoot = \'' .  $config['web_root'] . '\';' . "\n";
	$html .= '</script>' . "\n";
		
	// CSS
	$html .= html_include_css ('css/main.css');
			
	return $html;
}

//--------------------------------------------------------------------------------------------------
function html_head_close()
{
	return '</head>' . "\n";
}

//--------------------------------------------------------------------------------------------------
function html_body_open($params = '')
{
	global $config;
	
	$html = '';
	if ($params != '')
	{
		$html = '<body';
		foreach ($params as $k => $v)
		{
			$html .= ' '  . $k . '=' . '"' . $v . '"';
		}
		
		$html .= '>' . "\n";
	
	}
	else
	{
		$html = '<body>' . "\n";
	}
	
	
	
	return $html;
	
}

//--------------------------------------------------------------------------------------------------
function html_page_header($has_browse = true, $uri = '')
{
	global $config;
	
	$html = '';
	if ($has_browse)
	{
		$html .= html_browse_box($uri);
	}
	return $html;
}

//--------------------------------------------------------------------------------------------------
function html_body_close()
{
	$html = '';
	$html .= '</body>' . "\n";
	return $html;
}


//--------------------------------------------------------------------------------------------------
function html_title($str)
{
	return '<title>' . $str . '</title>' . "\n";
}

//--------------------------------------------------------------------------------------------------
// Absolutely vital to write this in the form <script></script>, otherwise
// Firefox breaks badly
function html_include_script($script_path)
{
	global $config;
	
	if (preg_match('/^http:/', $script_path))
	{
		// Externally hosted
		return '<script type="text/javascript" src="' . $script_path . '"></script>' . "\n";
	}
	else
	{
		return '<script type="text/javascript" src="' . $config['web_root'] . $script_path . '"></script>' . "\n";
	}
}

//--------------------------------------------------------------------------------------------------
function html_include_css($css_path)
{
	global $config;
	return '<link type="text/css" href="' . $config['web_root'] . $css_path . '" rel="stylesheet" />' . "\n";
}

//--------------------------------------------------------------------------------------------------
function html_include_link($type, $title, $path, $rel)
{
	global $config;
	return '<link type="' . $type . '" title="' . $title . '" href="' . $config['web_root'] . $path . '" rel="' . $rel . '" />' . "\n";
}


//--------------------------------------------------------------------------------------------------
function html_image($image_path, $class = '')
{
	global $config;
	$html = '<img ';
	if ($class != '')
	{
		$html .= ' class="' . $class . '"';
	}
	$html .=  'src="' . $image_path . '" alt="" />';
	return $html;
}

//--------------------------------------------------------------------------------------------------
function html_browse_box($uri = '')
{
	global $config;
	
	$html = '';
	$html .='<div style="padding:20px;float:right;">' . "\n";	
	$html .= '<input  style="font-size:16px;" id="browse" name="browse" type="text" size="50" value="' . $uri . '" onkeypress="handleKeyPress(event,browse);"/>' . "\n";
	$html .= '<button style="font-size:16px;" type="button" onclick="browseUri(browse);">Browse</button>' . "\n";
	$html .= '</div>' . "\n";	
	
	return $html;
}



?>