<?php

/**
 * @file utils.php
 *
 */

//------------------------------------------------------------------------------
/**
 * @brief Convert a decimal latitude or longitude to deg min sec format
 *
 * @param decimal Latitude or longitude as a decimal number
 *
 * @return Degree format
 */
function decimal_to_degrees($decimal)
{
	$decimal = abs($decimal);
	$degrees = floor($decimal);
	$minutes = floor(60 * ($decimal - $degrees));
	$seconds = round(60 * (60 * ($decimal - $degrees) - $minutes));
	
	// &#176;
	$result = $degrees . '&deg;' . $minutes . '&rsquo;' . $seconds . '&rdquo;';
	return $result;
}

//------------------------------------------------------------------------------
function format_decimal_latlon($latitude, $longitude)
{
	$html = decimal_to_degrees($latitude);
	$html .= ($latitude < 0.0 ? 'S' : 'N');
	$html .= '&nbsp;';
	$html .= decimal_to_degrees($longitude);
	$html .= ($latitude < 0.0 ? 'W' : 'E');
	return $html;
}

//------------------------------------------------------------------------------
// from http://phpsense.com/php/php-word-splitter.html
function word_split($str,$words=15) {
	$arr = preg_split("/[\s]+/", $str,$words+1);
	$arr = array_slice($arr,0,$words);
	return join(' ',$arr);
}	

 
//------------------------------------------------------------------------------
/**
 * @brief Format an arbitrary date as YYYY-MM-DD
 *
 * @param date A string representation of a date
 *
 * @return Date in YYYY-MM-DD format
 */
function format_date($date)
{
	$formatted_date = '';
	
	// Dates like 2006-8-7T15:47:36.000Z break PHP strtotime, so
	// replace the T with a space.
	$date = preg_replace('/-([0-9]{1,2})T([0-9]{1,2}):/', '-$1 $2:', $date);
	
	if (PHP_VERSION < 5.0)
	{
		if (-1 != strtotime($date))
		{
			$formatted_date = date("Y-m-d", strtotime($date));
		}		
	}
	else
	{
		if (false != strtotime($date))
		{
			$formatted_date = date("Y-m-d", strtotime($date));
		}
	}
	return $formatted_date;
}


//------------------------------------------------------------------------------
/**
 * @brief Extract the year from a date
 *
 * @param date A string representation of a date in YYYY-MM-DD format
 * @return Year in YYYY format
 */
function year_from_date($date)
{
	$year = 'YYYY';
	$matches = array();
	if (preg_match("/([0-9]{4})(\-[0-9]{1,2})?(\-[0-9]{1,2})?/", $date, $matches))
	{
		$year = $matches[1];
	}
	return $year;
}



//------------------------------------------------------------------------------
/**
 * @brief Clone function for PHP 4
 *
 * Clone function for PHP4. because PHP5 handles objects differently from
 * PHP 4 it is possible to badly mangle objects. PHP4 passes a copy, PHP 5
 * passes a reference. php4_clone makes it possibel to ensure a copy is being 
 * passed in both versions.
 *
 * Borrowed from http://www.hat.net/geeky/php_tricks_-_php_5_clone_in_php4
 *
 */
function php4_clone($object) 
{
	if (version_compare(phpversion(), '5.0') < 0) 
	{
		return $object;
	} 
	else 
	{
		return @clone($object);
	}
}

//------------------------------------------------------------------------------
/**
 * @brief Log service calls to disk
 *
 * @param filename Log file name
 * @param msg Message to write to log file
 */
function logToFile($filename, $msg)
{
	$fd = fopen($filename, "a");
	$str = '[' . date("Y/m/d h:i:s", mktime()) . '] ' . $msg;
	fwrite($fd, $str . "\n");
	fclose($fd);
}

//------------------------------------------------------------------------------
/**
 * @brief Store metadata in disk cache
 *
 * Store original metadata in disk cache, mainly for debugging. Typically the metadata is
 * in XML format, such as the response from a DiGIR provider, OAI interface, or CrossRef OpenURL
 * service. The metadata is stored in a file <b>namespace/id.extension</b>. Characters such
 * as '/' and ':' in the identifier are replaced by '-' to make the identifier safe to
 * use as a file name.
 *
 * @param namespace Namespace part of GUID
 * @param id Identifier part GUID
 * @param data Metadata
 * @param extension Extension of file (default 'xml')
 *
 */ 
function storeInCache ($namespace, $id, $data, $extension = 'xml')
{
	global $config;
	$cache_namespace = $config['cache_dir']. "/" . $namespace;
	
	// make id safe as a filename
	$id = preg_replace('/\//', '-', $id);
	$id = preg_replace('/:/', '-', $id);
	
	$cache_filename = $cache_namespace . "/" . $id . '.' . $extension;
			
	// Ensure cache subfolder exists for this authority
	if (!file_exists($cache_namespace))
	{
		$oldumask = umask(0); 
		mkdir($cache_namespace, 0777);
		umask($oldumask);
	}
	
	// Store data in cache
	$cache_file = @fopen($cache_filename, "w+") or die("could't open file --\"$cache_filename\"");
	@fwrite($cache_file, $data);
	fclose($cache_file);
}



?>