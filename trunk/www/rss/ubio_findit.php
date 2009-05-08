<?php

// $Id: $

require_once(dirname(__FILE__).'/config.inc.php');
require_once('../lib/nusoap.php');

//--------------------------------------------------------------------------------------------------
/**
 * @brief Call uBio's SOAP service to find all names in text
 *
 * @param Text The text to search.
 */
function ubio_findit($text)
{
	global $config;
	$names = array();
	
	$client = new nusoap_client('http://names.ubio.org/soap/', 'wsdl',
				$config['proxy_name'], $config['proxy_port'], '', '');
	
	
	$err = $client->getError();
	if ($err) 
	{
		return $names;
	}
	// This is vital to get through Glasgow's proxy server
	$client->setUseCurl(true);
	
	$param = array(
		'url' => '',
		'freeText' => base64_encode($text),
		'strict' => 0,
		'threshold' => 0.5
		);			

	$proxy = $client->getProxy();				
	$result = $proxy->findIT(
		$param['url'], 
		$param['freeText'], 
		$param['strict'], 
		$param['threshold']
		);
		
	
	// Check for a fault
	if ($proxy->fault) 
	{
		print_r($result);
	} 
	else 
	{
		// Check for errors
		$err = $proxy->getError();
		if ($err) 
		{
		}
		else 
		{
			// Display the result
			//print_r($result);
			
			// Extract names
			$xml = $result['returnXML'];
			if ($xml != '')
			{
				if (PHP_VERSION >= 5.0)
				{
					$dom= new DOMDocument;
					$dom->loadXML($xml);
					$xpath = new DOMXPath($dom);
					$xpath_query = "//allNames/entity";
					$nodeCollection = $xpath->query ($xpath_query);
					$nameString = '';
					
					foreach($nodeCollection as $node)
					{
						foreach ($node->childNodes as $v) 
						{
							$name = $v->nodeName;
							if ($name == "nameString")
							{
								$nameString = $v->firstChild->nodeValue;
								$names[$nameString]['nameString'] = $v->firstChild->nodeValue;
							}
							if ($name == "score")
							{
								$names[$nameString]['score'] = $v->firstChild->nodeValue;
							}
							if ($name == "namebankID")
							{
								$names[$nameString]['namebankID'] = $v->firstChild->nodeValue;
							}
							if ($name == "parsedName")
							{
								// Much grief, we need to attribute of this node
								$n = $v->attributes->getNamedItem('canonical');
								$names[$nameString]['canonical'] = $n->nodeValue;
							}
							
						}
					}
				}
			}
			//print_r($names);
			//echo '</pre>';
		}
	}
//		echo '<h2>Request</h2><pre>' . htmlspecialchars($client->request, ENT_QUOTES) . '</pre>';
//		echo '<h2>Response</h2><pre>' . htmlspecialchars($client->response, ENT_QUOTES) . '</pre>';
//		echo '<h2>Debug</h2><pre>' . htmlspecialchars($client->debug_str, ENT_QUOTES) . '</pre>';

	return $names;
}


function tag_names($names, $text, $binomial = false)
{
	foreach ($names as $n)
	{
		$nameString = $n['nameString'];
		$canonical = $n['canonical'];
		$namebankID = 0;
		if (isset($n['namebankID']))
		{
			$namebankID = $n['namebankID'];
		}
		$nameString = preg_replace('/([A-Z])\[(.*)\] (.*)/', "$1. $3", $nameString);
		
		$is_uninomial = (strpos($canonical, ' ') === false);
		$go = false;
		if ($is_uninomial && !$binomial)
		{
			$go = true;
		}
		if (!$is_uninomial && $binomial)
		{
			$go = true;
		}
		if ($go)
		{
			
			$pattern = "/([^\[][^\[][^\|])$nameString/";
			
			if ($nameString != $canonical)
			{
				$text = preg_replace($pattern, "$1[[$canonical|$nameString]]", $text);
			}
			else
			{
				$text = preg_replace($pattern, "$1[[$nameString]]", $text);
			}		
			
		/*	if ($namebankID != 0)
			{
				$text = preg_replace($pattern, "$1[[namebankID:$namebankID|$nameString]]", $text);
			}
			else
			{
				$text = preg_replace($pattern, "$1[[$nameString]]", $text);
			}
		*/
		}
	}

	return $text;
}

function tag_all_names($names, $text)
{
	$text = '   ' . $text;
	// do binonials first
	$text = tag_names($names, $text, true);
	// do uninomials
	$text = tag_names($names, $text, false);
	
	$text = trim($text);
	return $text;
}

if (0)
{
	// test
	
	$text = 'Philorhizus marggii n. sp. is described from Greece (southern Peloponnese). Type locality: Taygetos Massif, Profitis Illias, N 36°58’/E 022°21’, 2000-2400 m asl. Members of this micropterous species are distinguished from the other Philorhizus species occurring on the Balkans by habitus, the special colouration pattern of the elytra and the special construction of the internal sac of the median lobe. Illustrations of the habitus, the median lobe and its internal sac and a description of the habitat of the new species are presented. A key to all Philorhizus species known from Greece is given. Biogeographic notes on the distribution of micropterous Philorhizus species in the western Palaearctic realm are given. Philorhizus paulo Wrase, 1995 is recorded from France for the first time (East Pyrenees)';
	
	$text = 'The first comprehensive combined molecular and morphological phylogenetic analysis of the major groups of termites is presented. This was based on the analysis of three genes (cytochrome oxidase II, 12S and 28S) and worker characters for approximately 250 species of termites. Parsimony analysis of the aligned dataset showed that the monophyly of Hodotermitidae, Kalotermitidae and Termitidae were well supported, while Termopsidae and Rhinotermitidae were both paraphyletic on the estimated cladogram. Within Termitidae, the most diverse and ecologically most important family, the monophyly of Macrotermitinae, Foraminitermitinae, Apicotermitinae, Syntermitinae and Nasutitermitinae were all broadly supported, but Termitinae was paraphyletic. The pantropical genera Termes, Amitermes and Nasutitermes were all paraphyletic on the estimated cladogram, with at least 17 genera nested within Nasutitermes, given the presently accepted generic limits. Key biological features were mapped onto the cladogram. It was not possible to reconstruct the evolution of true workers unambiguously, as it was as parsimonious to assume a basal evolution of true workers and subsequent evolution of pseudergates, as to assume a basal condition of pseudergates and subsequent evolution of true workers. However, true workers were only found in species with either separate- or intermediate-type nests, so that the mapping of nest habit and worker type onto the cladogram were perfectly correlated. Feeding group evolution, however, showed a much more complex pattern, particularly within the Termitidae, where it proved impossible to estimate unambiguously the ancestral state within the family (which is associated with the loss of worker gut flagellates). However, one biologically plausible optimization implies an initial evolution from wood-feeding to fungus-growing, proposed as the ancestral condition within the Termitidae, followed by the very early evolution of soil-feeding and subsequent re-evolution of wood-feeding in numerous lineages.';
	
	$text = 'The family Kalotermitidae is redescribed. The subfamily names \'Electrotermitinae\' and \'Kalotermitinae\' are placed in synonymy. The fossil genus Eotermes is removed from the family Kalotermitidae and placed in the family Hodotermitidae. 2. Three hundred and fifty-three species, fossil and living, are classified into 24 genera. Of these 24 genera, the following eight are new: Postelectrotermes, Ceratokalotermes, Comatermes, Incisitermes, Marginitermes, Tauritermes, Bifiditermes, and Bicornitermes. The genera Pterotermes, Proneotermes, Allotermes, and Epicalotermes are resurrected. The genus name \'Proglyptotermes\' is relegated to synonymy. All the genera are described, and the generitype species are illustrated. 3. The generic classification is based on a constellation of conservative, adaptive, and regressed characters of both the imago and the soldier castes. 4. The phylogeny of the genera is discussed. The imago-nymph mandible indicates two main evolutionary lines. The first line is represented by the Proelectrotermes-Calcaritermes complex, and the second line by the Incisitermes-Cryptotermes complex. 5. Several cases of convergence are illustrated. In both the main lines of the family Kalotermitidae, the phragmotic head, the enlarged third antennal segment, and the slightly sclerotized median vein have all evolved independently many times. Also, the arolium has been convergently lost in many genera. 6. A discussion on conservative and regressed characters is included. Characters that show phylogenetic advancement or regression are also listed. 7. It is evident from the data on the hosts and Protozoa that the evolution of the genera of the Protozoa did not occur in conjunction with the evolution of the host genera and that the differentiation of the Protozoa genera took place before the differentiation of the host genera.';
	
	$text = 'Etheostoma erythrozonum, a new species of darter (Teleostei: Percidae) from the Meramec River drainage, Missouri';
	
	$names = ubio_findit($text);
	print_r($names);
}
//echo tag_all_names($names, $text);



?>