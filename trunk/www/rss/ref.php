<?php

// Reference string parsing...


function parse_ipni_ref($str, &$matches, $debug = 0)
{
	//$debug = 1;
	$matched = false;
	
	// clean dates
	$str = preg_replace('/\[(.*)\]$/', '', $str);
	
	// clean page range
	$str = preg_replace('/\([0-9]+\-?[0-9]+;\s*fig(.*)\)\.?/', '', $str);

	$str = preg_replace('/\(\-?[0-9]+;\s*fig(.*)\)\.?/', '', $str);
	$str = preg_replace('/\(([0-9]+)\-[0-9]+;\s*fig(.*)\)\.?/', "$1", $str);
	$str = preg_replace('/\(\-[0-9]+\)\./', '', $str);
	
/*	echo $str;
	preg_match('/ \(([0-9]+)/', $str, $matches);
	print_r($matches);
*/
	$str = preg_replace('/\(map\)/', '', $str);
	
	// Taxon 55(2): 465 (467) 2006 [22 Jun 2006]
	$str = preg_replace('/\([0-9]+\)\s*([0-9]{4})/', "$1", $str);

	// Clean up
	$str = trim($str);
	
	if ($debug)
	{
		echo "|$str|\n";
	}
	
	// Extract bibliographic details

	if ($debug) echo "Trying " . __LINE__ . "\n";
	if (preg_match('/
		(?<journal>.*)\s+
		(?<volume>[0-9]+)
		\((?<issue>[0-9]+(\-[0-9]+)?)\)
		:\s*
		(?<page>[0-9]+)\s*
		\(?(?<year>[0-9]{4})\)?/x', $str, $matches))
	{
		if ($debug)
		{
			echo __LINE__ . "\n";
			print_r($matches);	
		}
		$matched = true;		
	}
	
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match('/
			(?<journal>.*)\s+
			(?<volume>[0-9]+)
			\((?<issue>[0-9]+(\-[0-9]+)?)\)
			:\s*
			(?<spage>[0-9]+)\-(?<epage>[0-9]+)\s*
			\(?(?<year>[0-9]{4})\)?/x', $str, $matches))
		{
			if ($debug)
			{
				echo __LINE__ . "\n";
				print_r($matches);	
			}
			$matched = true;
		}
	}	

	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match('/
			(?<journal>.*)\s+(?<volume>[0-9]+)
			(\((?<issue>[0-9]+)\))?
			:\s*
			(?<page>[0-9]+)\s*
			\((?<day>[0-9]{1,2})\s+
			(?<month>[A-Z[a-z]+)\.\s+
			(?<year>[0-9]{4})\)/x', $str, $matches))
		{
			if ($debug)
			{
				echo __LINE__ . "\n";
				print_r($matches);	
			}
			$matched = true;
		}	
	}	
	
	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match('/
			(?<journal>.*)\s+(?<volume>[0-9]+)
			(\((?<issue>[0-9]+(\-[0-9]+)?)\)?)?
			:\s*
			(?<page>[0-9]+)\.\s*
			(?<year>[0-9]{4})/x', $str, $matches))
		{
			if ($debug)
			{
				echo __LINE__ . "\n";
				print_r($matches);	
			}
			$matched = true;
		}	
	}	

	if (!$matched)
	{
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match('/
			(?<journal>.*)\s+(?<volume>[0-9]+)
			(\((?<issue>[0-9]+)\))?
			:\s*
			(?<page>[0-9]+)\s*
			\((\-[0-9]|f)(.*)\)\.?\s*
			(?<year>[0-9]{4})/x', $str, $matches))
		{
			if ($debug)
			{
				echo __LINE__ . "\n";
				print_r($matches);	
			}
			$matched = true;
		}	
	}	
	
	
	return $matched;
}
	
// Test cases for IPNI
if (0)
{
	

	$refs = array();
	$failed = array();
	
	array_push($refs, 'Willdenowia 33(1): 61 (2003)');
	array_push($refs, 'Edinburgh J. Bot. 66(1): 110 (-113; fig. 2, map). 2009 [Mar 2009]');
	array_push($refs, 'Taxon 55(2): 467 (466; fig. 1) 2006 [22 Jun 2006]');
	array_push($refs, 'Blumea 50(1): 58 (-60; fig. 9) 2005');
	array_push($refs, 'Pl. Syst. Evol. 246(3-4): 241 2004[27 July 2004]');
	
	array_push($refs, 'Novon 13(4): 384 (5 Dec. 2003)'); 
	array_push($refs, 'Amer J. Bot. 89(4): 702 (699-706; figs. 1-4) 2002');
	array_push($refs, 'Brittonia 54(4): 354 (-356; fig. 2A-D) 2002 [16 Apr 2003]');
	array_push($refs, 'Brittonia 53(4): 559 (2001 publ. 2002)'); // check what is actual publication date...

	array_push($refs, 'Bradleya 26: 92. 2008 [18 Jul 2008]');
	array_push($refs, 'Austral Syst Biol 18(2): 202 (-203, 195; fig. 10d (map)) 2005');
	array_push($refs, 'Bot. J. Linn. Soc. 159(3): 430 (fig. 5, map). 2009 [12 Mar 2009] ');
	array_push($refs, 'Pl. Syst. Evol. 278(1-2): 120. 2009 [Mar 2009]');
	array_push($refs, 'Taxon 58(1): 317. 2009');
	array_push($refs, 'Acta Bot. Hung. 51(1-2): 21 (-23). 2009 [Mar 2009');
	array_push($refs, 'Acta Bot. Hung. 51(1-2): 11 (-14; fig. 1). 2009 [Mar 2009]');
	
	$ok = 0;
	foreach ($refs as $str)
	{
		$matched = parse_ipni_ref($str, $matches, 1);
		
		
		
		if ($matched)
		{
			$ok++;
		}
		else
		{
			array_push($failed, $str);
		}
	}
	
	// report
	
	echo "--------------------------\n";
	echo count($refs) . ' references, ' . (count($refs) - $ok) . ' failed' . "\n";
	print_r($failed);
}


?>