<?php

// Reference string parsing...

// Wikispecies reference string parsing...
function parse_wikispecies_ref($str, &$obj, $debug = 0)
{
	$debug = 0;
	$matched = false;
	
	// Clean up
	$str = trim($str);
	
	if ($debug)
	{
		echo "|$str|\n";
	}
	
	// Extract bibliographic details
	
	// ''Zootaxa'', '''441''': 1-8
	
	if (!$matched)
	{
		$m = array();
		
		if ($debug) echo "Trying " . __LINE__ . "\n";
		if (preg_match_all("/
			([^']''([^'']+|(?R))*'')
			/x", $str, $m))
		{
			if ($debug)
			{
				echo __LINE__ . "\n";
				print_r($m);					
			}
			$obj->journal = $m[2][count($m[2]) - 1];
			
			// get volume and pagination
			if (preg_match("/
				'''(?<volume>[0-9]+)'''
				(\([0-9]+\))?
				:
				\s+(?<spage>[0-9]+)
				[-|Ð]
				/x", $str, $m))
			{
				if ($debug)
				{
					echo __LINE__ . "\n";
					print_r($m);	
				}
				
				$obj->volume = $m['volume'];
				$obj->spage = $m['spage'];
				//$matches['epage'] = $m['epage'];
				$matched = true;	
			}
			
			// Authors
			if (preg_match_all("/
				(\{\{aut\|([^\}\}]+|(?R))*\}\})
				/x", $str, $a))
			{
				//print_r($a);
				
				$obj->authors = array();
				$obj->authors = $a[2];
			}
			
			if (preg_match("/(?<year>[0-9]{4})(: )?(?<atitle>.*)''" . $obj->journal . "/", $str, $m))
			{
				//print_r($m);
				$obj->year = $m['year'];
				$obj->atitle = trim($m['atitle']);
				$obj->atitle = str_replace("'", "", $obj->atitle);
			}
			
			// Title
			
				
			
				
		}
	}

	
	return $matched;
}


function parse_ion_ref($str, &$matches, $debug = 0)
{
	$matched = false;
	
	if (preg_match('/(?<journal>Zootaxa), (?<volume>\d+), (?<date>\s*((\d+ \w+)|(\w+ \d+)|(\w+))?(\s*(?<year>[0-9]{4})))(\((?<actualyear>[0-9]{4})\))?:\s*(?<spage>\d+)-(?<epage>\d+).(\s*http:\/\/(?<url>.*) \[)?/', $str, $matches))
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
		if (preg_match('/(?<journal>(.*)), (No. )?(?<volume>\d+),?\s*(\((?<issue>\d+\-\d+)\)),? (?<date>\s*(\d+ \w+)?(\s*(?<year>[0-9]{4}))):\s*(?<spage>\d+)-(?<epage>\d+)./', $str, $matches))
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
		if (preg_match('/(?<journal>(.*)), (No. )?(?<volume>\d+),?\s*(\((?<issue>\d+\-\d+)\)), (?<months>[A-Z][a-z]+\-[A-Z][a-z]+)\s*(?<year>[0-9]{4}):\s*(?<spage>\d+)-(?<epage>\d+)./', $str, $matches))
		{
			if ($debug)
			{
				echo __LINE__ . "\n";
				print_r($matches);	
			}
			$matched = true;
		}	
	}
	
 	//Memoirs of the Queensland Museum, 50(2), 10 January 2005: 133-194.
	
	if (!$matched)
	{
		if (preg_match('/(?<journal>(.*)), (?<volume>\d+)(\((?<issue>\d+)\)),?((.*)(?<year>[0-9]{4}))?:\s*(?<spage>\d+)-(?<epage>\d+)./', $str, $matches))
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
		if (preg_match('/(?<journal>(.*)), (No. )?(?<volume>\d+),?\s*(\((?<issue>\d+)\))?,?(?<date>\s*((\d+ \w+)|(\w+ \d+)|(\w+))?(\s*(?<year>[0-9]{4})))(\((?<actualyear>[0-9]{4})\))?:\s*(?<spage>\d+)-(?<epage>\d+).(\s*http:\/\/(?<url>.*) \[)?/', $str, $matches))
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
		if (preg_match('/(?<journal>(.*)), (?<volume>\d+)\s*(?<year>[0-9]{4}):\s*pp.\s*(?<spage>\d+)-(?<epage>\d+)./', $str, $matches))
		{
			if ($debug)
			{
				echo __LINE__ . "\n";
				print_r($matches);	
			}
			$matched = true;
		}
	}

	// Venus (Tokyo), 63(3-4), January 2005: 109-119.
	if (!$matched)
	{
		if (preg_match('/(?<journal>(.*)), (?<volume>\d+)(\((?<issue>\d+\-\d+)\)), (.*)\s*(?<year>[0-9]{4}):\s*(?<spage>\d+)-(?<epage>\d+)./', $str, $matches))
		{
			if ($debug)
			{
				echo __LINE__ . "\n";
				print_r($matches);	
			}
			$matched = true;
		}
	}
	


//A review of the Telorchiinae, a group of Distomid Trematodes. Parasitology Cambridge, 20 1928: pp. 336-356.
	return $matched;	
}

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

// Test cases for Wikispecies
if (0)
{
	$refs = array();
	$failed = array();
	
	array_push($refs, '* {{aut|Stoev, P.}} 2004: The first troglomorphic species of the millipede genus \'\'Paracortina\'\' Wang &amp;amp; Zhang, 1993 from south Yunnan, China (Diplopoda: Callipodida: Paracortinidae). \'\'Zootaxa\'\', \'\'\'441\'\'\': 1-8. [http://mapress.com/zootaxa/2004f/z00441f.pdf Abstract &amp;amp; excerpt]&lt;br /&gt;');
	array_push($refs, '* {{aut|Aguiar, A.J.C.}}; {{aut|Melo, G.A.R.}} 2007: Taxonomic revision, phylogenetic analysis, and biogeography of the bee genus \'\'Tropidopedia\'\' (Hymenoptera, Apidae, Tapinotaspidini). \'\'Zoological journal of Linnean Society\'\', \'\'\'151\'\'\': 511Ð554.&lt;/div&gt;</description>');
	array_push($refs, '* {{aut|Shelley, R.M.}} 2003: A revised, annotated, family-level classification of the Diplopoda. \'\'Arthropoda selecta\'\', \'\'\'11\'\'\'(3): 187-207.&lt;/div&gt;</description>');
	array_push($refs, '* {{aut|Mesibov, R.}}; {{aut|Ruhberg, H.}} 1991: Ecology and conservation of \'\'Tasmanipatus barretti\'\' and \'\'T. anophthalmus\'\', parapatric onychophorans (Onychophora: Peripatopsidae) from northeastern Tasmania. \'\'Papers and proceedings of the Royal Society of Tasmania\'\', \'\'\'125\'\'\': 11-16.&lt;br /&gt;');
	array_push($refs, '* {{aut|Franz, N.M.}}; {{aut|Skelley, P.E.}} 2008: \'\'Pharaxonotha portophylla\'\' (Coleoptera: Erotylidae), new species and pollinator of \'\'Zamia\'\' (Zamiaceae) in Puerto Rico. \'\'Caribbean journal of science\'\', \'\'\'44\'\'\': 321-333. [http://academic.uprm.edu/~franz/publications/Pharaxonotha.pdf PDF]&lt;/div&gt;</description>');
	array_push($refs, '* {{aut|Wang, W.L.}} 1993: On Liaoximordellidae fam. nov. (Coleoptera, Insecta) from the Jurassic of western Liaoning Province, China. \'\'Dizhi Xuebao\'\' [=\'\'Acta geologica Sinica\'\'], \'\'\'67\'\'\'(1): 86-94. [in Chinese with English abstract]');

	echo "--------------------------\n";	
	$ok = 0;
	foreach ($refs as $str)
	{
		$obj = new stdclass;
		$matched = parse_wikispecies_ref($str, $obj, 1);
		
		if ($matched)
		{
			$ok++;
			
			print_r($obj);
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


// Test cases for ION
if (0)
{
	

	$refs = array();
	$failed = array();
	
/*	array_push($refs, 'A new species of glassfrog from the elfin forests of the Cordillera del Condor, southeastern Ecuador. (Anura: Centrolenidae). Herpetozoa, 21(1-2), 30 Juni 2008: 49-56.');
	array_push($refs, 'A new species of mermithid nematode parasite Romanomermis narayani n. sp. from Culex sp. Mosquito larvae in the rice fields of A.P. Current Nematology, 17(1-2), June-December 2006: 7-15.');
	array_push($refs, 'A review of the Telorchiinae, a group of Distomid Trematodes. Parasitology Cambridge, 20 1928: pp. 336-356.');
	array_push($refs, 'Mas datos para el conocimiento de las esponjas de las costas espanolas. Boletin de Pescas Madrid, 7 1922: pp. 247-272.');
	array_push($refs,	'Caruncle in Megalomma Johansson, 1925 (Polychaeta: Sabellidae) and the description of a new species from the eastern Tropical Pacific. Journal of Natural History, 42(29-30) 2008: 1951-1973.');
*/
array_push($refs, '. Memoirs of the Queensland Museum, 50(2), 10 January 2005: 133-194.');
array_push($refs, '. Venus (Tokyo), 63(3-4), January 2005: 109-119.');
	$ok = 0;
	foreach ($refs as $str)
	{
		$matched = parse_ion_ref($str, $matches, 1);
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
	array_push($refs, 'Madro–o  55(3):188. 2008 [Jul 208]');
	
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