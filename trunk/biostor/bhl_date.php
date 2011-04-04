<?php

/**
 * @file bhl_date.php
 *
 * Extract dates, volume, and series information from BHL database fields
 *
 */
 
require_once(dirname(__FILE__) . '/utilities.php');
 

//--------------------------------------------------------------------------------------------------
function bhl_date_from_details($str, &$info)
{
	$debug = false;
	$matched = false;
	
	// Clean up
	$str = trim($str);
	
	//echo "$str\n";
	

	
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/(?<yearstart>[0-9]{4})\s*\-\s*(?<yearend>[0-9]{4})\.?$/", $str, $m))
		{
			if ($debug) { echo "$str"; print_r($m); }
			$info->start = $m['yearstart'];
			$info->end = $m['yearend'];
			$matched = true;
		}
		
	}	
	
	
	
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/(?<year>[0-9]{4})\.?$/", $str, $m))
		{
			if ($debug) { echo "$str"; print_r($m); }
			$info->start = $m['year'];
			$matched = true;
		}
		
		
	}
	
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/(?<yearstart>[0-9]{4})\s*\-\s*(?<yearend>[0-9]{2})\.?$/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->start = $m['yearstart'];
			$info->end = substr ($m['yearstart'], 0, 2) . $m['yearend'];
			$matched = true;
		}
		
	}	
	
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/(?<yearstart>[0-9]{4})\s*\-\s*(?<yearend>[0-9]{2})\.?$/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->start = $m['yearstart'];
			$info->end = substr ($m['yearstart'], 0, 2) . $m['yearend'];
			$matched = true;
		}
		
	}	
	
	
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/(?<yearstart>[0-9]{4})\s*\-\s*\[(?<yearend>[0-9]{4})\]\.?$/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->start = $m['yearstart'];
			$info->end = $m['yearend'];
			$matched = true;
		}
		
	}	
	
	
	
	return $matched;
}

//--------------------------------------------------------------------------------------------------
function parse_bhl_date($str, &$info)
{
	$debug = false;
	$matched = false;
	
	// Clean up
	
//	$str = preg_replace('/^new ser./', '', $str);
	$str = preg_replace('/text$/', '', $str);
	$str = preg_replace('/:plates$/', '', $str);
	$str = trim($str);
	
	if ($debug)
	{
		echo $str . '<br/>';
	}
	
	// no. 86 (June 1999)
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^no. (?<volume>\d+) \(\w+ (?<year>[0-9]{4})\)$/", $str, $m))
		{
			$info->volume = $m['volume'];
			$info->start = $m['year'];
			$matched = true;
		}	
	}
	
	// arg. 59 (1902)
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^arg. (?<volume>\d+) \((?<year>[0-9]{4})\)$/", $str, $m))
		{
			$info->volume = $m['volume'];
			$info->start = $m['year'];
			$matched = true;
		}	
	}
	
	
	// n.s., v. 4 1856-58
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/n.s., v. (?<volume>\d+) (?<yearstart>[0-9]{4})\-(?<yearend>[0-9]{2})/", $str, $m))
		{
			$info->volume = $m['volume'];
			$info->start = $m['yearstart'];
			$info->end = substr ($m['yearstart'], 0, 2) . $m['yearend'];
			$matched = true;
		}	
	}
	
	// 3rd ser., v. 3 1864-69
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/(?<series>\d+)rd ser., v. (?<volume>\d+) (?<yearstart>[0-9]{4})\-(?<yearend>[0-9]{2})/", $str, $m))
		{
			$info->series = $m['series'];
			$info->volume = $m['volume'];
			$info->start = $m['yearstart'];
			$info->end = substr ($m['yearstart'], 0, 2) . $m['yearend'];
			$matched = true;
		}	
	}
	
	
	// ser. 2 v. 6 (1894-1897)
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/ser. (?<series>\d+) v. (?<volume>\d+) \((?<yearstart>[0-9]{4})\-(?<yearend>[0-9]{4})\)/", $str, $m))
		{
			$info->series = $m['series'];
			$info->volume = $m['volume'];
			$info->start = $m['yearstart'];
			$info->end = $m['yearend'];
			$matched = true;
		}	
	}
	
	
	// 35-36, 1918-1920
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^(?<volume_from>\d+)\-(?<volume_to>\d+),\s+(?<yearstart>[0-9]{4})\-(?<yearend>[0-9]{4})$/", $str, $m))
		{
			$info->volume_from = $m['volume_from'];
			$info->volume_to = $m['volume_to'];
			$info->start = $m['yearstart'];
			$info->end = $m['yearend'];
			$matched = true;
		}	
	}

	// 34, 1917-1918
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^(?<volume>\d+),\s+(?<yearstart>[0-9]{4})\-(?<yearend>[0-9]{4})$/", $str, $m))
		{
			$info->volume = $m['volume'];
			$info->start = $m['yearstart'];
			$info->end = $m['yearend'];
			$matched = true;
		}	
	}
	
	
	// v. 99- 100 1956-57
	if (!$matched)
	{
		if ($str == 'v. 99- 100 1956-57')
		{
			$info->volume_from = 99;
			$info->volume_to = 100;
			$info->start = 1956;
			$info->end = 1957;
			$matched = true;
			
		}	
	}
	
	// bd. 17-18 (1899-1900)
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^bd\.\s+(?<volume_from>\d+)\-(?<volume_to>\d+)\s+\((?<yearstart>[0-9]{4})\-(?<yearend>[0-9]{4})\)$/", $str, $m))
		{
			$info->volume_from = $m['volume_from'];
			$info->volume_to = $m['volume_to'];
			$info->start = $m['yearstart'];
			$info->end = $m['yearend'];
			$matched = true;
		}
	}
	
	
	// bd. 19-20 (1901-02)
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^bd\.\s+(?<volume_from>\d+)\-(?<volume_to>\d+)\s+\((?<yearstart>[0-9]{4})\-(?<yearend>[0-9]{2})\)$/", $str, $m))
		{
			$info->volume_from = $m['volume_from'];
			$info->volume_to = $m['volume_to'];
			$info->start = $m['yearstart'];
			$info->end = substr ($m['yearstart'], 0, 2) . $m['yearend'];
			$matched = true;
		}
	}
	
	// v. 88/89 1977/78
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^v\.\s+(?<volume_from>\d+)\/(?<volume_to>\d+)\s+(?<yearstart>[0-9]{4})\/(?<yearend>[0-9]{2})/", $str, $m))
		{
			$info->volume_from = $m['volume_from'];
			$info->volume_to = $m['volume_to'];
			$info->start = $m['yearstart'];
			$info->end = substr ($m['yearstart'], 0, 2) . $m['yearend'];
			$matched = true;
		}
	}
	
	// 1901, v. 1 (Jan.-Apr.)
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^(?<volume>[0-9]{4}),\s+v.\s+\d+\s+\($/", $str, $m))
		{
			$info->volume = $m['volume'];
			$matched = true;
		}
	}
	
	// 1867 (incomplete)
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^(?<volume>[0-9]{4})\s+\(incomplete\)$/", $str, $m))
		{
			$info->volume = $m['volume'];
			$matched = true;
		}
	}
	
	// 1921 v. 1-2
	if (!$matched)
	{
		if ($str == '1921 v. 1-2')
		{
			$info->volume = 1921;
			$matched = true;
		}
	}
	
	// Part 19  - Part 20 (1851-52)
	if (!$matched)
	{
		if ($str == 'Part 19  - Part 20 (1851-52)')
		{
			$info->volume_from = 1851;
			$info->volume_to = 1852;
			$matched = true;
		}
	}
	
	// [1908-1944]
	if (!$matched)
	{
		if ($str == '[1908-1944]')
		{
			$info->start = 1908;
			$info->end = 1944;
			$matched = true;
		}
	}
	
	
	// Vol 10 - Vol 10
	if (!$matched)
	{
		if ($str == 'Vol 10 - Vol 10')
		{
			$info->volume = 10;
			$matched = true;
		}
	}
	if (!$matched)
	{
		if ($str == 'Vol 20 - Vol 20')
		{
			$info->volume = 10;
			$matched = true;
		}
	}
	
	// v 11 (1914-15)
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^v (?<volume>\d+) \((?<yearstart>[0-9]{4})-(?<yearend>[0-9]{2})\)$/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->volume = $m['volume'];
			$info->start = $m['yearstart'];
			$info->end = substr ($m['yearstart'], 0, 2) . $m['yearend'];
			$matched = true;
		}
		
	}	
	
	
	// 1923, pt. 3-4 (pp. 483-1097)
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^(?<volume>[0-9]{4}),\s*p[p|t]\./", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->volume = $m['volume'];
			$matched = true;
		}
		
	}		
	
	// Vol 10 (3)
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^Vol (?<volume>[0-9]+) \((?<issue>[0-9]+)\)$/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->volume_from = $m['volume'];
			$info->issue = $m['issue'];
			$info->issue = $info->issue;
			$matched = true;
		}
		
	}	
	
	//
	
	
	// Band I - Band II
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^Band (?<volumefrom>[XVI]+) - Band (?<volumeto>[XVI]+)/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->volume_from = arabic($m['volumefrom']);
			$info->volume_to = arabic($m['volumeto']);
			$matched = true;
		}		
	}		
	
	// Band V
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^Band (?<volume>[XVI]+)/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->volume = arabic($m['volume']);
			$matched = true;
		}		
	}		
	
	
	// Bd.2.E.b
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^Bd\.(?<volume>[0-9]+)\./", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->volume = $m['volume'];
			$matched = true;
		}		
	}		
	
	
	// Jahrg. 74:bd. 2:heft 2 (1908)
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^Jahrg\.\s+(?<volume>[0-9]+):(.*)\((?<year>[0-9]{4})\)$/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->volume = $m['volume'];
			$info->start = $m['year'];
			$matched = true;
		}		
	}
	
	// Jahrg. 73:bd. 2:heft 2: Lief. 3 - Jahrg. 73:bd. 2:
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^Jahrg\.\s+(?<volume>[0-9]+):/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->volume = $m['volume'];
			$matched = true;
		}		
	}		
	
	// 88.d. (1945)
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^(?<volume>\d+)\.d\.\s+\((?<year>[0-9]{4})\)$/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->volume = $m['volume'];
			$info->start = $m['year'];
			$matched = true;
		}
	}		
	// 65./66. d. 1922/23
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^(?<volumefrom>\d+)\.\/(?<volumeto>\d+)\.\s*d\.\s*(?<yearstart>[0-9]{4})\/(?<yearend>[0-9]{2})$/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->volume_from = $m['volumefrom'];
			$info->volume_to = $m['volumeto'];
			$info->start = $m['yearstart'];
			$info->end = substr ($m['yearstart'], 0, 2) . $m['yearend'];
			$matched = true;
		}
		
	}		
	
	
	// bd. 2 (1901-04)
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^bd.\s*(?<volume>\d+)\s*\((?<yearstart>[0-9]{4})-(?<yearend>[0-9]{2})\)$/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->volume = $m['volume'];
			$info->start = $m['yearstart'];
			$info->end = substr ($m['yearstart'], 0, 2) . $m['yearend'];
			$matched = true;
		}
		
	}		
	
	
	
	// nuova ser.:v.1 (1901-1905)
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^nuova ser.:v.(?<volume>[0-9]+)\s*\((?<yearstart>[0-9]{4})-(?<yearend>[0-9]{4})\)$/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->volume = $m['volume'];
			$info->start = $m['yearstart'];
			$info->end = $m['yearend'];
			$matched = true;
		}
		
	}	
	
	
	// 8 (Series 2)
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^(?<volume>\d+)\s+\(Series\s+(?<series>\d+)\)$/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->volume = $m['volume'];
			$info->series = $m['series'];
			$matched = true;
		}
	}		
	
	// 1912 v. 59
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^(?<year>1[8|9][0-9]{2}) v.\s*(?<volume>[0-9]{1,2})$/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->volume = $m['volume'];
			$info->start = $m['year'];
			$matched = true;
		}
	}	
	
	// 1916-18 v. 63-65
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^(?<yearstart>1[8|9][0-9]{2})-(?<yearend>[0-9]{2}) v. 
		(?<volumefrom>[0-9]{1,2})-(?<volumeto>[0-9]{1,2})$/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->volume_from = $m['volumefrom'];
			$info->volume_to = $m['volumeto'];
			$info->start = $m['yearstart'];
			$info->end = substr ($m['yearstart'], 0, 2) . $m['yearend'];
			$matched = true;
		}
		
	}		
	
	
	// 3. d. 1859
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^(?<volume>[0-9]+).\s*(d\.|jaarg\.)\s*(?<year>[0-9]{4})$/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->volume = $m['volume'];
			$info->start = $m['year'];
			$matched = true;
		}
	}	
	

	
	// 29. d. 1885/86
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^(?<volume>[0-9]+).\s*(d\.|jaarg\.)\s*(?<yearstart>[0-9]{4})\/(?<yearend>[0-9]{2})$/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->volume = $m['volume'];
			$info->start = $m['yearstart'];
			$info->end = substr ($m['yearstart'], 0, 2) . $m['yearend'];
			$matched = true;
		}
		
	}	
	
	// 46./47. d. 1903/04
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^v\.\s*(?<volumefrom>[0-9]+).\s*(d\.|jaarg\.)\s*(?<yearstart>[0-9]{4})\/(?<yearend>[0-9]{2})$/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->volume_from = $m['volumefrom'];
			$info->volume_to = $m['volumeto'];
			$info->start = $m['yearstart'];
			$info->end = substr ($m['yearstart'], 0, 2) . $m['yearend'];
			$matched = true;
		}
	}			
	
	// Volume XI
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^Volume\s+(?<volume>[XVI]+)$/", $str, $m))
		{
			if ($debug) { echo "$str"; print_r($m); }
			$info->volume = arabic($m['volume']);
			$matched = true;
		}
	}		
	
	// 14, 1891
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^(?<volume>[0-9]+),\s*(?<year>[0-9]{4})$/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->volume = $m['volume'];
			$info->start = $m['year'];
			$matched = true;
		}
		
	}	
	
	// jahrg.5
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^jahr[g]?\.(?<volume>[0-9]+)$/u", $str, $m))
		{
			if ($debug) { echo "$str"; print_r($m); }
			$info->volume = $m['volume'];
			$matched = true;
		}
		
	}	
	
	// jahr.21-25
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^jahr[g]?\.(?<volumefrom>[0-9]+)([-|—](?<volumeto>[0-9]+))?$/u", $str, $m))
		{
			if ($debug) { echo "$str"; print_r($m); }
			$info->volume_from = $m['volumefrom'];
			$info->volume_to = $m['volumeto'];
			$matched = true;
		}
		
	}		
	
	//v.58-59
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^v\.\s*(?<volumefrom>[0-9]+)-(?<volumeto>[0-9]+)$/u", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->volume_from = $m['volumefrom'];
			$info->volume_to = $m['volumeto'];
			$matched = true;
		}
		
	}	
	
	// 31 n.01
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^(?<volumefrom>[0-9]+)\s+n\.(?<issue>[0-9]+)$/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->volume_from = $m['volumefrom'];
			$info->issue = $m['issue'];
			$info->issue = preg_replace('/^0/', '', $info->issue);
			$matched = true;
		}
		
	}	


	
	if (!$matched)
	{
		$m = array();
		
		if ($debug) { echo "Trying " . __LINE__ . "\n"; }
		if (preg_match("/^(?<year>[0-9]{4})$/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->start = $m['year'];
			$matched = true;
		}
		
	}
	
	// Volume 46
	// vol 4
	if (!$matched)
	{
		$m = array();
		
		if ($debug) { echo "Trying " . __LINE__ . "\n"; }
		if (preg_match("/^(Volume|vol) (?<volume>[0-9]+)$/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->volume = $m['volume'];			
			$matched = true;
		}
		
	}
	
	// no. 224 pt. 1 1962
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^no\.\s*(?<volume>[0-9]+)\s*pt\.\s*(?<issue>[0-9]+)\s*\(?(?<year>[0-9]{4})\)?$/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->volume = $m['volume'];
			$info->issue = $m['issue'];
			$info->start = $m['year'];
			$matched = true;
		}
		
	}
	
	// Bd.4
	if (!$matched)
	{
		$m = array();
		
		if ($debug) { echo "Trying " . __LINE__ . "\n"; }
		if (preg_match("/^Bd\.(?<volume>[0-9]+)$/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->volume = $m['volume'];			
			$matched = true;
		}
		
	}
	
	
	// Jahrg. 6, Bd. 1 (1840)
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^Jahrg\.\s*(?<volume>[0-9]+),\s*[B|b]d\.\s*(?<issue>[0-9]+)\s*\(?(?<year>[0-9]{4})\)?$/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->volume = $m['volume'];
			$info->issue = $m['issue'];
			$info->start = $m['year'];
			$matched = true;
		}		
	}
	
	
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^(?<yearstart>[0-9]{4})\-(?<yearend>[0-9]{4})$/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->start = $m['yearstart'];
			$info->end = $m['yearend'];
			$matched = true;
		}
		
	}

	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^(?<yearstart>[0-9]{4})\-(?<yearend>[0-9]{2})$/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->start = $m['yearstart'];
			$info->end = substr ($m['yearstart'], 0, 2) . $m['yearend'];
			$matched = true;
		}
		
	}
	
	// Fieldiana Zoology v.24, no.16
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^(?<title>.*)\s+v.(?<volume>[0-9]+), no.(?<issue>[0-9]+)$/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->title = $m['title'];
			$info->volume = $m['volume'];
			$info->issue = $m['issue'];
			$matched = true;
		}
		
	}
	
	
	
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^(no|v|t)\.\s*(?<volume>[0-9]+)(.*)\((?<yearstart>[0-9]{4})(?<yearend>[0-9]{4})\)$/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->volume = $m['volume'];
			$info->start = $m['yearstart'];
			$info->end = $m['yearend'];
			$matched = true;
		}
		
	}
	
	
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^(bd|v|t|ser|Haft)\.\s*(?<volume>[0-9]+)(.*)\((?<yearstart>[0-9]{4})\-(?<yearend>[0-9]{4})\)$/i", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->volume = $m['volume'];
			$info->start = $m['yearstart'];
			$info->end = $m['yearend'];
			$matched = true;
		}
	}	
	
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^(v|t|bd|Bd|anno|Haft)\.?\s*(?<volume>[0-9]+)\s*\(?(?<year>[0-9]{4})\)?$/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->volume = $m['volume'];
			$info->start = $m['year'];
			$matched = true;
		}
		
		
	}


	
	// no.180 (1996)
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^no\.\s*(?<volume>[0-9]+)\s*\(?(?<year>[0-9]{4})\)?$/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->volume = $m['volume'];
			$info->start = $m['year'];
			$matched = true;
		}
		
	}
	
	// bd.52, 1921
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^bd\.\s*(?<volume>[0-9]+),\s*(?<year>[0-9]{4})?$/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->volume = $m['volume'];
			$info->start = $m['year'];
			$matched = true;
		}
		
	}
	
	// e.g. Ann. mag. nat. hist., Proc Calif Acad Sci
	// 3rd ser. v. 8 (1861) 
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^(?<series>[0-9]+)(rd|th|nd)\.?\s+ser.\s*v.\s*(?<volume>[0-9]+)\s*\(?((?<yearstart>[0-9]{4})(\-(?<yearend>[0-9]{4}))?)?\)?$/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->volume = $m['volume'];
			if (isset($m['yearstart']))
			{
				$info->start = $m['yearstart'];
			}
			if (isset($m['yearend']))
			{
				$info->end = $m['yearend'];
			}
			$info->series = $m['series'];
			$matched = true;
		}
		
	}
	
	// *** WARNING *** Line:369 Not matched "4th ser. v. 41 1977-79"<
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^(?<series>[0-9]+)(rd|th|nd)\.?\s+ser.\s*v.\s*(?<volume>[0-9]+)\s*\(?((?<yearstart>[0-9]{4})(\-(?<yearend>[0-9]{2})))\)?$/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->volume = $m['volume'];
			$info->start = $m['yearstart'];
			$info->end = substr ($m['yearstart'], 0, 2) . $m['yearend'];
			$info->series = $m['series'];
			$matched = true;
		}
		
	}	
	
	// this one is v dangerous as has (*.) in middle, use only as last resort...!!!!!!
/*	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^(?<volume>[0-9]+)(.*)\(?(?<year>[0-9]{4})\)?$/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->volume = $m['volume'];
			$info->start = $m['year'];
			$matched = true;
		}
		
	}
*/	
	
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^(no|v|t)\.\s*((?<volumefrom>[0-9]+)\-(?<volumeto>[0-9]+))(.*)\((?<yearstart>[0-9]{4})\-(?<yearend>[0-9]{4})\)$/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->volume_from = $m['volumefrom'];
			$info->volume_to = $m['volumeto'];
			$info->start = $m['yearstart'];
			$info->end = $m['yearend'];
			$matched = true;
		}
		
	}
	
	// no. 85-93 1991-92
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^(no|v|t)\.\s*((?<volumefrom>[0-9]+)\-(?<volumeto>[0-9]+))(.*)\(?(?<yearstart>[0-9]{4})\-(?<yearend>[0-9]{2})\)?$/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->volume_from = $m['volumefrom'];
			$info->volume_to = $m['volumeto'];
			$info->start = $m['yearstart'];
			$info->end = substr ($m['yearstart'], 0, 2) . $m['yearend'];
			$matched = true;
		}
		
	}	
	
	// no. 85-93 1991-92
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^(Bd|no|v|t)\.\s*(?<volume>[0-9]+)(.*)\(?(?<yearstart>[0-9]{4})\-(?<yearend>[0-9]{2})\)?$/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->volume = $m['volume'];
			$info->start = $m['yearstart'];
			$info->end = substr ($m['yearstart'], 0, 2) . $m['yearend'];
			$matched = true;
		}
		
	}	
	
	
	// v. 1-2 (1814-1826)
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^(no|v|t)\.\s*((?<volumefrom>[0-9]+)\-(?<volumeto>[0-9]+))(.*)\(?(?<yearstart>[0-9]{4})\-(?<yearend>[0-9]{4})\)?$/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->volume_from = $m['volumefrom'];
			$info->volume_to = $m['volumeto'];
			$info->start = $m['yearstart'];
			$info->end = $m['yearend'];
			$matched = true;
		}
		
	}	
	
	// ser.2 t.1-2 1895-1897
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^(ser)\.\s*(?<series>[0-9]+)\s*t\.((?<volumefrom>[0-9]+)\-(?<volumeto>[0-9]+))\s*\(?(?<yearstart>[0-9]{4})\-(?<yearend>[0-9]{4})\)?$/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->series = $m['series'];
			$info->volume_from = $m['volumefrom'];
			$info->volume_to = $m['volumeto'];
			$info->start = $m['yearstart'];
			$info->end = $m['yearend'];
			$matched = true;
		}
		
	}	
	
	// ser.3, v. 6, 1914
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^ser\.\s*(?<series>[0-9]+),?\s*[v|t]\.\s*(?<volume>[0-9]+),?\s*(.*)\(?(?<year>[0-9]{4})\)?$/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->series = $m['series'];
			$info->volume = $m['volume'];
			$info->start = $m['year'];
			$matched = true;
		}
		
	}
	
	// new ser.:v.44 (1900-1901):plates
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^(new )ser\.\s*[,|:]?\s*[v|t]\.\s*(?<volume>[0-9]+)\s*\(?((?<yearstart>[0-9]{4})(\-(?<yearend>[0-9]{4}))?)?\)?$/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->series ='new series';
			$info->volume = $m['volume'];
			if (isset($m['yearstart']))
			{
				$info->start = $m['yearstart'];
			}
			if (isset($m['yearend']))
			{
				$info->end = $m['yearend'];
			}
			$matched = true;
		}
		
	}
	
	
	// No date, just volume (e.g. Bull brit Mus Nat Hist)
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^((Vol|tome\.?)\s*)?(?<volume>[0-9]+[A-Z]?)$/", $str, $m))
		{
			if ($debug) { echo "$str"; print_r($m); }
			$info->volume = $m['volume'];
			$matched = true;
		}
		
	}	
	
	// Tome 20
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^Tome\s+(?<volume>[0-9]+)$/", $str, $m))
		{
			if ($debug) { echo "$str"; print_r($m); }
			$info->volume = $m['volume'];
			$matched = true;
		}
		
	}	
	

	// 18 and index to v.1-17
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^(?<volume>[0-9]+) and index/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->volume = $m['volume'];
			$matched = true;
		}
		
	}	
	
	// 22-24
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^(?<volumefrom>[0-9]+)\-(?<volumeto>[0-9]+)$/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->volume_from = $m['volumefrom'];
			$info->volume_to = $m['volumeto'];
			$matched = true;
		}
		
	}	

	// 20:pt.1
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^(?<volume>[0-9]+):/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->volume = $m['volume'];
			$matched = true;
		}
		
	}	
	
	// v.33, no.1
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^v\.\s*(?<volume>[0-9]+)(, (no|pt).\s*(?<issue>[0-9]+))?/", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->volume = $m['volume'];
			if (isset($m['issue']))
			{
				$info->issue = $m['issue'];
			}
			$matched = true;
		}
		
	}	
	
	
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/new series,? no.\s*(?<volume>[0-9]+)?/i", $str, $m))
		{
			if ($debug) { echo "$str
"; print_r($m); }
			$info->volume = $m['volume'];
			$info->series = 'new series';
			$matched = true;
		}
		
	}	
	
	return $matched;
}
	
if (0)
{
	

	$dates = array();
	$failed = array();
	
	array_push($dates, '[Monaco]Impr. de Monaco,1889-19<14>');
	array_push($dates, 'Lille :Le Bigot,1888-[1895]');
	array_push($dates, 'London,Longmans, Green and Co.,1922.');
	array_push($dates, 'Copenhagen,[G. & C. Gads Forlag],1910.');
	array_push($dates, 'Boston,S.E. Cassino and company,1884-85.');

	
	$ok = 0;
	foreach ($dates as $str)
	{
		$info = new stdclass;
		$matched = bhl_date_from_details($str, $info);
		
		if ($matched)
		{
			$ok++;
			
			print_r($info);
		}
		else
		{
			array_push($failed, $str);
		}
	}
	
	// report
	
	echo "--------------------------\n";
	echo count($refs) . ' dates, ' . (count($dates) - $ok) . ' failed' . "\n";
	print_r($failed);
}

	
	
//--------------------------------------------------------------------------------------------------
/**
 * @brief Test parse_bhl_date function using a range of test cases
 *
 */
function test_parse_bhl_date()
{
	

	$dates = array();
	$failed = array();
	
	array_push($dates, 'v.35:pt.1 (1952)');
	array_push($dates, 'v.15 (1961-1966)');
	array_push($dates, 'v. 34 (1921)');
	array_push($dates, 'no.180 (1996)');
	array_push($dates, 'no.296-325 (1968-1969)');
	array_push($dates, 'no. 85-93 1991-92');
	array_push($dates, 'v. 1-2 1991-24');
	array_push($dates, 'v. 1-2 (1814-1826)');
	array_push($dates, 'v. 39, no. 2 (1996)');
	array_push($dates, 'v. 85, no. 1-4 (1986)');

	array_push($dates, 't. 4 (1891-1892)');
	array_push($dates, 't. 17-18 1882-85');
	array_push($dates, 't. 17; (ser. 2, t.7) (1889)');

	array_push($dates, 't. 3 no. 3-4 marzo-abr 1920');

	array_push($dates, 'new ser. v. 1 (1883-1886)');
	array_push($dates, 'v. 5 (18501851)');
	array_push($dates, 'no. 138 1926');
	
	array_push($dates, '3rd ser. v. 8 (1861) ');
	
	array_push($dates, 'new ser.:v.5');
	array_push($dates, 'new ser.:v.45 (1901-1902)');
	array_push($dates, 'new ser. v. 19 (1906-1907)');
	
	$dates[] = 'v. 58-59';

	
	$ok = 0;
	foreach ($dates as $str)
	{
		$info = new stdclass;
		$matched = parse_bhl_date($str, $info);
		
		if ($matched)
		{
			$ok++;
			
			print_r($info);
		}
		else
		{
			array_push($failed, $str);
		}
	}
	
	// report
	
	echo "--------------------------\n";
	echo count($refs) . ' dates, ' . (count($dates) - $ok) . ' failed' . "\n";
	print_r($failed);
}

if (0)
{
	test_parse_bhl_date();
}

	
	
?>