<?php

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
		if (preg_match("/(?<yearstart>[0-9]{4})\-(?<yearend>[0-9]{4})\.?$/", $str, $m))
		{
			if ($debug) { print_r($m); }
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
			if ($debug) { print_r($m); }
			$info->start = $m['year'];
			$matched = true;
		}
		
		
	}
	
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/(?<yearstart>[0-9]{4})\-(?<yearend>[0-9]{2})\.?$/", $str, $m))
		{
			if ($debug) { print_r($m); }
			$info->start = $m['yearstart'];
			$info->end = substr ($m['yearstart'], 0, 2) . $m['yearend'];
			$matched = true;
		}
		
	}	
	
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/(?<yearstart>[0-9]{4})\-(?<yearend>[0-9]{2})\.?$/", $str, $m))
		{
			if ($debug) { print_r($m); }
			$info->start = $m['yearstart'];
			$info->end = substr ($m['yearstart'], 0, 2) . $m['yearend'];
			$matched = true;
		}
		
	}	
	
	
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/(?<yearstart>[0-9]{4})\-\[(?<yearend>[0-9]{4})\]\.?$/", $str, $m))
		{
			if ($debug) { print_r($m); }
			$info->start = $m['yearstart'];
			$info->end = $m['yearend'];
			$matched = true;
		}
		
	}	
	
	
	
	return $matched;
}

function parse_bhl_date($str, &$info)
{
	$debug = false;
	$matched = false;
	
	// Clean up
	
	$str = preg_replace('/^new ser./', '', $str);
	$str = preg_replace('/text$/', '', $str);
	$str = trim($str);
	
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^(?<year>[0-9]{4})$/", $str, $m))
		{
			if ($debug) { print_r($m); }
			$info->start = $m['year'];
			$matched = true;
		}
		
	}
	
	
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^(no|v|t)\.\s*(?<volumefrom>[0-9]+)(.*)\((?<yearstart>[0-9]{4})(?<yearend>[0-9]{4})\)$/", $str, $m))
		{
			if ($debug) { print_r($m); }
			$info->volume_from = $m['volumefrom'];
			$info->volume_to = $m['volumeto'];
			$info->start = $m['yearstart'];
			$info->end = $m['yearend'];
			$matched = true;
		}
		
	}
	
	
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^(v|t)\.\s*(?<volume>[0-9]+)(.*)\((?<yearstart>[0-9]{4})\-(?<yearend>[0-9]{4})\)$/", $str, $m))
		{
			if ($debug) { print_r($m); }
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
		if (preg_match("/^(v|t|bd)\.\s*(?<volume>[0-9]+)(.*)\(?(?<year>[0-9]{4})\)?$/", $str, $m))
		{
			if ($debug) { print_r($m); }
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
		if (preg_match("/^no\.\s*(?<volume>[0-9]+)(.*)\(?(?<year>[0-9]{4})\)?$/", $str, $m))
		{
			if ($debug) { print_r($m); }
			$info->volume = $m['volume'];
			$info->start = $m['year'];
			$matched = true;
		}
		
	}
	
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match("/^(no|v|t)\.\s*((?<volumefrom>[0-9]+)\-(?<volumeto>[0-9]+))(.*)\((?<yearstart>[0-9]{4})\-(?<yearend>[0-9]{4})\)$/", $str, $m))
		{
			if ($debug) { print_r($m); }
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
			if ($debug) { print_r($m); }
			$info->volume_from = $m['volumefrom'];
			$info->volume_to = $m['volumeto'];
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
			if ($debug) { print_r($m); }
			$info->volume_from = $m['volumefrom'];
			$info->volume_to = $m['volumeto'];
			$info->start = $m['yearstart'];
			$info->end = substr ($m['yearstart'], 0, 2) . $m['yearend'];
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

	
	
if (0)
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

	
	
?>