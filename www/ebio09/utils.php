﻿<?php

/**
 * @file utils.php
 *
 */
 
//--------------------------------------------------------------------------------------------------
// from http://www.ajaxray.com/blog/2008/02/06/php-uuid-generator-function/
/**
  * Generates an UUID
  * 
  * @author     Anis uddin Ahmad <admin@ajaxray.com>
  * @param      string  an optional prefix
  * @return     string  the formatted uuid
  */
function uuid($prefix = '')
{
	$chars = md5(uniqid(mt_rand(), true));
	$uuid  = substr($chars,0,8) . '-';
	$uuid .= substr($chars,8,4) . '-';
	$uuid .= substr($chars,12,4) . '-';
	$uuid .= substr($chars,16,4) . '-';
	$uuid .= substr($chars,20,12);
	
	return $prefix . $uuid;
}

//--------------------------------------------------------------------------------------------------
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
	$result = $degrees . '°' . $minutes . '´' . $seconds . '˝';
	return $result;
}

//--------------------------------------------------------------------------------------------------
function format_decimal_latlon($latitude, $longitude)
{
	$html = decimal_to_degrees($latitude);
	$html .= ($latitude < 0.0 ? 'S' : 'N');
	$html .= ', ';
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
function trim_text($str, $words=10)
{
	$s = word_split($str, $words);
	if (strlen($s) < strlen($str))
	{
		$s .= '...';
	}
	return $s;
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

// from http://uk.php.net/manual/en/function.time.php#85128 

/*
* PHP port of Ruby on Rails famous distance_of_time_in_words method. 
*  See http://api.rubyonrails.com/classes/ActionView/Helpers/DateHelper.html for more details.
*
* Reports the approximate distance in time between two timestamps. Set include_seconds 
* to true if you want more detailed approximations.
*
*/
function distanceOfTimeInWords($from_time, $to_time = 0, $include_seconds = false) {
	$distance_in_minutes = round(abs($to_time - $from_time) / 60);
	$distance_in_seconds = round(abs($to_time - $from_time));

	if ($distance_in_minutes >= 0 and $distance_in_minutes <= 1) {
		if (!$include_seconds) {
			return ($distance_in_minutes == 0) ? 'less than a minute' : '1 minute';
		} else {
			if ($distance_in_seconds >= 0 and $distance_in_seconds <= 4) {
				return 'less than 5 seconds';
			} elseif ($distance_in_seconds >= 5 and $distance_in_seconds <= 9) {
				return 'less than 10 seconds';
			} elseif ($distance_in_seconds >= 10 and $distance_in_seconds <= 19) {
				return 'less than 20 seconds';
			} elseif ($distance_in_seconds >= 20 and $distance_in_seconds <= 39) {
				return 'half a minute';
			} elseif ($distance_in_seconds >= 40 and $distance_in_seconds <= 59) {
				return 'less than a minute';
			} else {
				return '1 minute';
			}
		}
	} elseif ($distance_in_minutes >= 2 and $distance_in_minutes <= 44) {
		return $distance_in_minutes . ' minutes';
	} elseif ($distance_in_minutes >= 45 and $distance_in_minutes <= 89) {
		return 'about 1 hour';
	} elseif ($distance_in_minutes >= 90 and $distance_in_minutes <= 1439) {
		return 'about ' . round(floatval($distance_in_minutes) / 60.0) . ' hours';
	} elseif ($distance_in_minutes >= 1440 and $distance_in_minutes <= 2879) {
		return '1 day';
	} elseif ($distance_in_minutes >= 2880 and $distance_in_minutes <= 43199) {
		return 'about ' . round(floatval($distance_in_minutes) / 1440) . ' days';
	} elseif ($distance_in_minutes >= 43200 and $distance_in_minutes <= 86399) {
		return 'about 1 month';
	} elseif ($distance_in_minutes >= 86400 and $distance_in_minutes <= 525599) {
		return round(floatval($distance_in_minutes) / 43200) . ' months';
	} elseif ($distance_in_minutes >= 525600 and $distance_in_minutes <= 1051199) {
		return 'about 1 year';
	} else {
		return 'over ' . round(floatval($distance_in_minutes) / 525600) . ' years';
	}
}




?>