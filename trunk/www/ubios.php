<?php

require_once(dirname(__FILE__).'/config.inc.php');
require_once(dirname(__FILE__).'/db.php');
require_once(dirname(__FILE__).'/lib.php');
require_once(dirname(__FILE__).'/lib/nusoap.php');



function ubio_namebank_search($text, $has_authority=false)
{
	global $config;
	global $db;
	
	// Maybe we have this already?
	$namebankID = find_ubio_name_in_cache($text, $has_authority);
	
	if (count($namebankID) > 0)
	{
		return $namebankID;
	}
	
	// Not found, so make SOAP call
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
		'searchName' => base64_encode($text),
		'searchAuth' => '',
		'searchYear' => '',
		'order' => 'name',
		'rank' => '',
		'sci' => 1,
		'linkedVern' => 1,
		'vern' => 1,
		'keyCode' => $config['ubio_keyCode']
		);			
	
	$proxy = $client->getProxy();				
	$result = $proxy->namebank_search(
		$param['searchName'], 
		$param['searchAuth'], 
		$param['searchYear'], 
		$param['order'],
		$param['rank'],
		$param['sci'],
		$param['linkedVern'],
		$param['vern'],
		$param['keyCode']
		);
	
	
	// Check for a fault
	if ($proxy->fault) 
	{
//		print_r($result);
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
			print_r($result);
			
			// get the relevant matches
			foreach ($result['scientificNames'] as $r)
			{
				if ($has_authority)
				{
					$n = strtolower(base64_decode($r['fullNameString']));
					
					// Strip punctuation 
					$n = str_replace(",", "", $n);
					$n = str_replace("(", "", $n);
					$n = str_replace(")", "", $n);
					$text = str_replace(",", "", $text);
					$text = str_replace("(", "", $text);
					$text = str_replace(")", "", $text);
				}
				else
				{
					$n = strtolower(base64_decode($r['nameString']));										
				}
				
				//echo $n;
				
				if ($n == strtolower($text))
				{
					// Store this name
//					echo base64_decode($r['nameString']) . "\n";
					
					array_push($namebankID, $r['namebankID']);
					
					
				}
				
				// Store name in cache
				store_ubio_name($r);
			}
			
			
			
		}
	}
	
	return $namebankID;
}

function ubio_namebank_search_rest($text, $has_authority=false, $exact=false)
{
	global $config;
	global $db;
	
	// Maybe we have this already?
	$namebankID = find_ubio_name_in_cache($text, $has_authority, $exact);
//	$namebankID = array();
	if (count($namebankID) > 0)
	{
		return $namebankID;
	}
	
	// Not found, so make HTTP call
	$queryTerm = str_replace (' ', '%20', $text);
	$url = "http://names.ubio.org/webservices/service.php?function=namebank_search&searchQualifier=exact&searchName=";
	$url .= $queryTerm;
	$url .= '&sci=1&vern=0';
	$url .= '&keyCode=' . $config['ubio_keyCode'];
	
	//echo $url . "\n";

	$xml = get($url);
	
	//echo $xml;
	

	if ($xml != '')
	{
		if (PHP_VERSION >= 5.0)
		{
				$dom= new DOMDocument;
				$dom->loadXML($xml);
				$xpath = new DOMXPath($dom);
				$xpath_query = "//scientificNames/value";
				$nodeCollection = $xpath->query ($xpath_query);
				foreach($nodeCollection as $node)
				{
/*					$namebankID = '';
					$nameString = '';
					$fullNameString = '';
					$packageID = '';
					$packageName = '';
					$basionymUnit = '';
					$rankID;
					$rankName;*/
					
					$r = array();
					
					foreach ($node->childNodes as $v) 
					{
						$name = $v->nodeName; 
						if ($name == "namebankID")
						{
							$r['namebankID'] = $v->firstChild->nodeValue;
						}
						if ($name == "nameString")
						{
							$r['nameString'] = $v->firstChild->nodeValue;
						}
						if ($name == "fullNameString")
						{
							$r['fullNameString'] = $v->firstChild->nodeValue;
						}
						if ($name == "packageID")
						{
							$r['packageID'] = $v->firstChild->nodeValue;
						}
						if ($name == "packageName")
						{
							$r['packageName'] = $v->firstChild->nodeValue;
						}
						if ($name == "basionymUnit")
						{
							$r['basionymUnit'] = $v->firstChild->nodeValue;
						}
						if ($name == "rankID")
						{
							$r['rankID'] = $v->firstChild->nodeValue;
						}
						if ($name == "rankName")
						{
							$r['rankName'] = $v->firstChild->nodeValue;
						}
					}
					
					// Process
					if ($has_authority)
					{
						$n = strtolower(base64_decode($r['fullNameString']));
						
						// Strip punctuation 
						$n = str_replace(",", "", $n);
						$n = str_replace("(", "", $n);
						$n = str_replace(")", "", $n);
						$text = str_replace(",", "", $text);
						$text = str_replace("(", "", $text);
						$text = str_replace(")", "", $text);
					}
					else
					{
						$n = strtolower(base64_decode($r['nameString']));										
					}
					
					//echo $n;
					
					if ($n == strtolower($text))
					{
						// Store this name
	//					echo base64_decode($r['nameString']) . "\n";
						
						if ($exact)
						{
							$n = strtolower(base64_decode($r['fullNameString']));
							if ($n == strtolower($text))
							{
								array_push($namebankID, $r['namebankID']);
							}
						}
						else
						{
							array_push($namebankID, $r['namebankID']);
						}
						
						
					}
					
					//print_r($r);
					// Store name in cache
					store_ubio_name($r);
					
				}

		}
	}

	
	return $namebankID;
}



//print_r(ubio_namebank_search_rest('Apus apus', false, true));



?>