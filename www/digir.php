<?php

require_once(dirname(__FILE__).'/config.inc.php');
require_once(dirname(__FILE__).'/db.php');
require_once(dirname(__FILE__).'/XML/Tree.php');
require_once(dirname(__FILE__).'/ubios.php');
require_once(dirname(__FILE__).'/utils.php');


//------------------------------------------------------------------------------
/**
 * @brief Encapsulate services that I use.
 *
 * We provide some basic instrumentation, such as logging calls to "log/service.log"
 * to provide some information on the performance of the services called.
 *
 */
class Service
{
	var $ch;
	var $serviceName;
	var $result;
	var $status;
	
	function Service ()
	{
		$this->initialiseName();
		$this->ch = curl_init(); 		
	}

	function initialiseName()
	{
		$this->serviceName = 'Service';
	}


	// Call with instrumentation
	function Call()
	{
		global $config;
		
		if ($config['proxy_name'] != '')
		{
			curl_setopt ($this->ch, CURLOPT_PROXY, $config['proxy_name'] . ':' . $config['proxy_port']);
		}
		curl_setopt ($this->ch, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt ($this->ch, CURLOPT_FOLLOWLOCATION, 1); 

		$this->result=curl_exec ($this->ch); 
			
		$info = curl_getinfo($this->ch);
		$http_code = $info['http_code'];
		$total_time = $info['total_time'];
		
		$this->status = $http_code;
		
		$msg = $this->serviceName . ' "' . $info['url'] . '" ' . $info['http_code'] . ' ' . $info['total_time'] . ' ' . $info['size_download'];
//		logToFile('log/' . $this->serviceName . '.log', $msg);		
	
	}
	
	function GetStatusCode()
	{
		return $this->status;
	}
	
	function IsOK()
	{
		// need to check that following on doesn't break this code!!!
		return ($this->status == 200);
	}

}




class DiGIRProvider extends Service
{
	var $serverURL;
	var $resourceCode;
	var $schema;
	
	var $bci;
	
	var $museumIdentifier;
	
	function DiGIRProvider($id)
	{
		parent::Service();
		
		// Hard code the mappinging between museum codes and DiGIR providers.
		// If I was clever we'd do this via GBIF's UDDI interface, but that
		// looks like a world of pain. Another illustration of how limited
		// GBIF's current architecture is.
	
		$this->serverURL = array(
			'FMNH-Mammals' 		=> 'digir.fieldmuseum.org/digir/DiGIR.php',
			'ROM' 		=> 'digir.rom.on.ca:80/digir/DiGIR.php',
			'MVZ'		=> '128.32.146.144/digir/allmvz/DiGIR.php',
			'MVZ-Herps'		=> '128.32.146.144/digir/allmvz/DiGIR.php',
			'MVZ-Mammals'		=> '128.32.146.144/digir/allmvz/DiGIR.php',
			'MVZ-Birds'		=> '128.32.146.144/digir/allmvz/DiGIR.php',
			'KU-Herps'		=> 'digir.nhm.ku.edu/digir/DiGIR.php',
			'CASENT' 	=> 'digir.calacademy.org/digir/digir.php',
			'CAS-Birds' 	=> 'digir.calacademy.org/digir/digir.php',
			'CAS-Mammals' 	=> 'digir.calacademy.org/digir/digir.php',
			'CAS-Herps' 	=> 'digir.calacademy.org/digir/digir.php',
			'MOBOT' 	=> 'digir.mobot.org/digir/DiGIR.php',
			'FMNH-Herps' => '66.158.71.154:80/digir/digir.php',
			'FMNH-Birds' => '66.158.71.154:80/digir/digir.php',
			'FMNH-Fish' => '66.158.71.154:80/digir/digir.php',



			'OMNH-Herps' => 'lipan.snomnh.ou.edu/digir/DiGIR.php',
//			'LACM' => '204.140.246.23/DiGIRprov/www/DiGIR.php',

			'LACM-Herps' => 'herpnet.nhm.org:80/digir/DiGIR.php',
			'LACM-Birds' => 'herpnet.nhm.org:80/digir/DiGIR.php',
			'ROM-Herps' => 'digir.rom.on.ca:80/digir/DiGIR.php',
			'ROM-Mammals' => 'digir.rom.on.ca:80/digir/DiGIR.php',
			'ROM-Birds' => 'digir.rom.on.ca:80/digir/DiGIR.php',
			'ROM-Fish' => 'digir.rom.on.ca:80/digir/DiGIR.php',

			'SAMA' => 'digir.austmus.gov.au:80/ozcam/DiGIR.php',
			'AM' => 'digir.austmus.gov.au:80/ozcam/DiGIR.php',
			'ANWC' => 'digir.austmus.gov.au:80/ozcam/DiGIR.php',
			'AMS' => 'digir.austmus.gov.au:80/ozcam/DiGIR.php',
			'WAM' => 'digir.austmus.gov.au:80/ozcam/DiGIR.php',
			'NT' => 'digir.austmus.gov.au:80/ozcam/DiGIR.php',
			
			'USNM-Mammals' => 'nhb-acsmith2.si.edu/emuwebvzmammalsweb/webservices/digir.php',
//			'USNM-Herps' => 'acsmith.si.edu/emuwebvzherpsweb/webservices/digir.php',
			'USNM-Herps' => 'nhb-acsmith2.si.edu/emuwebvzherpsweb/webservices/digir.php',
			'USNM-Fish' => 'nhb-acsmith2.si.edu/emuwebvzfishesweb/webservices/digir.php',
			'USNM-Birds' => 'nhb-acsmith2.si.edu/emuwebvzbirdsweb/webservices/digir.php',
			
			'TNHC-Herps' => '129.116.194.40/digir/DiGIR.php',
			
			// MCZ Harvard (distinguishes between Amphibians and Reptiles)
			'MCZ-Amph' => 'digir.mcz.harvard.edu/digir/DiGIR.php',
			
			// LSUMZ Herpetology
			'LSUMZ-Herps' => '130.39.185.43:80/digir/herps/DiGIR.php',
			
			// UWBM birds
			'UWBM-Birds' => 'biology.burke.washington.edu/digir/DiGIR.php'
		);

		$this->resourceCode = array(
			'FMNH-Mammals' 		=> 'MammalsDwC2',
			'ROM' 		=> 'MammalDwC2',
			'MVZ'		=> 'MVZMaNISDwC2',
			'MVZ-Herps'		=> 'MVZMaNISDwC2',
			'MVZ-Mammals'		=> 'MVZMaNISDwC2',
			'MVZ-Birds'		=> 'MVZMaNISDwC2',
//			'KU-Herps'		=> 'Univ of Kansas Biodiversity Research Center Herp CollectionDwC2',
			'KU-Herps'	=> 'KUH Herpetology',
			'CASENT' 	=> 'CAS Ants',
			'CAS-Birds' 	=> 'CAS Birds',
			'CAS-Mammals' 	=> 'CAS Mammals',
			'CAS-Herps' 	=> 'CAS Herps',
			'MOBOT' 	=> 'MOBOT',
			'FMNH-Herps' => 'HerpsDwC2',
			'FMNH-Birds' => 'BirdsDwC2',
			'FMNH-Fish' => 'FishDwC2',


			'OMNH-Herps' => 'Herps',
			'LACM-Herps' => 'DwC2',    // Nope, now changed... [Wierdly, it uses MammalsDwC2 but serves all verts]
			'LACM-Birds' => 'DwC2',
			'ROM-Herps' => 'HerpDwC2',
			'ROM-Mammals' => 'MammalDwC2',
			'ROM-Birds' => 'BirdDwC2',
			'ROM-Fish' => 'FishDwC2',
			
			'AM' => 'ozcamDwC121',
			'SAMA' => 'ozcamDwC121',
			'ANWC' => 'ozcamDwC121',
			'AMS' => 'ozcamDwC121',
			'WAM' => 'ozcamDwC121',
			'NT' => 'ozcamDwC121',
			
			'USNM-Mammals' => 'NMNH-VZMammals',
			'USNM-Herps' => 'NMNH-VZHerps',	
			'USNM-Fish' => 'NMNH-VZFishes',
			'USNM-Birds' => 'NMNH-VZBirds',
			
			'TNHC-Herps' => 'HerpsDwC2'	,
			
			// MCZ Harvard
			'MCZ-Amph' => 'mczamph',
			
			// LSUMZ Herpetology
			'LSUMZ-Herps' => 'HerpDwC2',
			
			'UWBM-Birds' => 'BirdsDwC2'
			);

		// Schemas vary across DiGIR providers. See The Big Dig (http://bigdig.ecoforge.net/wiki).
		// Here I use the value of <version>$Revision: 1.6 $</version> in the header field of
		// the DiGIR provider response as a flag to use different code to make the query.

		$this->schema = array(
			'FMNH-Mammals' 		=> '1.12',
			'ROM' 		=> '1.12',
			'MVZ'		=> '1.12',
			'MVZ-Herps'		=> '1.12',
			'MVZ-Mammals'		=> '1.12',
			'MVZ-Birds'		=> '1.12',
			'KU-Herps'		=> '1.12',
			'CASENT' 	=> '1.14',
			
			// For fucks sake!!!!!!!!! Can't people use the same freckin schema for the same provider!!!!!!
			'CAS-Birds' 	=> '1.12',
			'CAS-Mammals' 	=> '1.12',
			'CAS-Herps' 	=> '1.12',
			'MOBOT' 	=> '1.14',
			'FMNH-Herps' => '1.12',
			'FMNH-Birds' => '1.12',
			'FMNH-Fish' => '1.12',

			'OMNH-Herps' => '1.12',

			'LACM-Herps' => '1.12',
			'LACM-Birds' => '1.12',

			'ROM-Herps' => '1.12',
			'ROM-Mammals' => '1.12',
			'ROM-Birds' => '1.12',
			'ROM-Fish' => '1.12',
			
			'AM' => '1.12',
			'SAMA' => '1.12',
			'ANWC' => '1.12',
			'AMS' => '1.12',
			'WAM' => '1.12',
			'NT' => '1.12',
			
			'USNM-Mammals' => '1.12',
			'USNM-Herps' => '1.12',		
			'USNM-Fish' => '1.12',
			'USNM-Birds' => '1.12',


			'TNHC-Herps' => '1.12',
			
			// MCZ Harvard
			'MCZ-Amph' => '1.12',
			
			// LSUMZ Herpetology
			'LSUMZ-Herps' => '1.12',
			
			'UWBM-Birds' => '1.12'
			
						);
						
		// LSIDs for collection from http://biocol.org/
		 $this->bci = array(
			 'CAS-Herps' 	=> 'urn:lsid:biocol.org:col:34699',
			 'KU-Herps' => 'urn:lsid:biocol.org:col:34801',
			 
			'FMNH-Herps' => 'urn:lsid:biocol.org:col:34706',
			'FMNH-Birds' => 'urn:lsid:biocol.org:col:34941',
			'FMNH-Mammals' => 'urn:lsid:biocol.org:col:34893',
			
			'MCZ-Amph' => 'urn:lsid:biocol.org:col:34807',
			'MCZ-Herps' => 'urn:lsid:biocol.org:col:34807',

			
			'MVZ-Herps' => 'urn:lsid:biocol.org:col:34818',
			 			 
			 
			 'LACM-Herps' => 'urn:lsid:biocol.org:col:34803',
			 'LACM-Birds' => 'urn:lsid:biocol.org:col:34946',
			 
			'LSUMZ-Herps' => 'urn:lsid:biocol.org:col:34806',
			'LSUMZ-Mammals' => 'urn:lsid:biocol.org:col:34898',
			
			'ROM-Herps' => 'urn:lsid:biocol.org:col:34833',
			'ROM-Mammals' => 'urn:lsid:biocol.org:col:34900',
			'ROM-Birds' => 'urn:lsid:biocol.org:col:34954',
			 
			 
			 'TNHC-Herps' => 'urn:lsid:biocol.org:col:34886',

			 'USNM-Fish' => 'urn:lsid:biocol.org:col:1002',
			 'USNM-Herps' => 'urn:lsid:biocol.org:col:34872',
			 'USNM-Mammals' => 'urn:lsid:biocol.org:col:34905',
			 'USNM-Birds' =>'urn:lsid:biocol.org:col:34965',
			 
			 'UWBM-Birds' => 'urn:lsid:biocol.org:col:34966'

			 );
		 
		
		$this->museumIdentifier = $id;
	}
	
	
	function initialiseName()
	{
		$this->serviceName = 'DiGIR';
	}	
	
	function retrieveSpecimen($institution, $id)
	{
		
	}

	function search($institution, $queryType, $queryTerm, $start=0, $limit=10)
	{
		
		//echo $queryTerm, ' ', $queryType, "\n";
		
		if (strcasecmp($institution,'CASENT') == 0) { $institution = 'CASENT'; }
		if (strcasecmp($institution,'inbiocri') == 0) { $institution = 'CASENT'; }
		if (strcasecmp($institution,'lacment') == 0) { $institution = 'CASENT'; }	
		if (strcasecmp($institution,'jtlc') == 0) { $institution = 'CASENT'; }	
		
		
		$server = $this->serverURL[$institution];
		$resource = $this->resourceCode[$institution] ;

		// Build request message
		$tree = new XML_Tree();

		$root = & $tree->addRoot(
			"request",
			'',
			array(
					"xmlns" => 'http://digir.net/schema/protocol/2003/1.0',
					"xmlns:xsd" => 'http://www.w3.org/2001/XMLSchema',
					"xmlns:xsi" => 'http://www.w3.org/2001/XMLSchema-instance',
					"xmlns:digir" => 'http://digir.net/schema/protocol/2003/1.0',
					"xmlns:dwc" => 'http://digir.net/schema/conceptual/darwin/2003/1.0',
					"xmlns:darwin" => 'http://digir.net/schema/conceptual/darwin/2003/1.0',
					"xsi:schemaLocation" => 'http://digir.net/schema/protocol/2003/1.0',
					"xsi:schemaLocation" => 'http://digir.net/schema/protocol/2003/1.0 http://digir.sourceforge.net/schema/protocol/2003/1.0/digir.xsd http://digir.net/schema/conceptual/darwin/2003/1.0 http://digir.sourceforge.net/schema/conceptual/darwin/2003/1.0/darwin2.xsd',
					)
			);

		$header = & $root->addChild("header");
		$header->addChild("version", "1.0.0");
		$header->addChild("sendTime", date("Ymd \TG:i:s") ); 
		$header->addChild("source", $_SERVER['SERVER_ADDR']); 
		$header->addChild("destination", 
			$server,
			array(
				"resource" => $resource
				)
			); 
		$header->addChild("type", "search");

		$search = & $root->addChild("search");
		$filter = & $search->addChild("filter");
		$equals = & $filter->addChild("equals");
		
		switch($queryType)
		{
			case 'genus':
				$equals->addChild("darwin:Genus", $queryTerm);
				break;
			case 'family':
				$equals->addChild("darwin:Family", $queryTerm);
				break;
			case 'class':
				$equals->addChild("darwin:Class", $queryTerm);
				break;
			case 'country':
				$equals->addChild("darwin:Country", $queryTerm);
				break;
			case 'specimen':
				switch ($this->schema[$institution])
				{
					case "1.14":
						$equals->addChild("darwin:CatalogNumber", $queryTerm);
						break;

					default:
						$equals->addChild("darwin:CatalogNumberText", $queryTerm);
						break;
				}
				break;
				
			default:
				break;
		}


		$records = & $search->addChild("records",
			"",
			array(
				"limit" => $limit,
				"start" => $start
				)
			);
				if ($this->schema[$institution] == '1.12')
				{
				$records->addChild("structure",
					"",
					array(
	//					"schemaLocation" => "http://bnhm.berkeley.museum/manis/DwC/darwin2resultfull.xsd",

						"schemaLocation" => "http://digir.sourceforge.net/schema/conceptual/darwin/result/full/2003/darwin2resultfull.xsd",
						)
					);
				}
				else
				{

					$structure = & $records->addChild("structure", "");

					$element = & $structure->addChild("xsd:element", "", 
							array(
							"name" => "record"
							));

					$complexType =  & $element->addChild("xsd:complexType", "");
					$sequence =  & $complexType->addChild("xsd:sequence", "");

					$sequence->addChild("xsd:element", "",
							array(
							"ref" => "darwin:InstitutionCode"
							));
					$sequence->addChild("xsd:element", "",
							array(
							"ref" => "darwin:CollectionCode"
							));

					$sequence->addChild("xsd:element", "",
							array(
							"ref" => "darwin:CatalogNumber"
							));
					$sequence->addChild("xsd:element", "",
							array(
							"ref" => "darwin:ScientificName"
							));
					$sequence->addChild("xsd:element", "",
							array(
							"ref" => "darwin:VerbatimCollectingDate"
							));
					$sequence->addChild("xsd:element", "",
							array(
							"ref" => "darwin:DateLastModified"
							));
					$sequence->addChild("xsd:element", "",
							array(
							"ref" => "darwin:YearCollected"
							));
					$sequence->addChild("xsd:element", "",
							array(
							"ref" => "darwin:MonthCollected"
							));
					$sequence->addChild("xsd:element", "",
							array(
							"ref" => "darwin:DayCollected"
							));
					$sequence->addChild("xsd:element", "",
							array(
							"ref" => "darwin:TimeCollected"
							));
					$sequence->addChild("xsd:element", "",
							array(
							"ref" => "darwin:Kingdom"
							));
					$sequence->addChild("xsd:element", "",
							array(
							"ref" => "darwin:Phylum"
							));
					$sequence->addChild("xsd:element", "",
							array(
							"ref" => "darwin:Class"
							));
					$sequence->addChild("xsd:element", "",
							array(
							"ref" => "darwin:Order"
							));
					$sequence->addChild("xsd:element", "",
							array(
							"ref" => "darwin:Family"
							));
					$sequence->addChild("xsd:element", "",
							array(
							"ref" => "darwin:Genus"
							));
					$sequence->addChild("xsd:element", "",
							array(
							"ref" => "darwin:Species"
							));
					$sequence->addChild("xsd:element", "",
							array(
							"ref" => "darwin:Subspecies"
							));
					$sequence->addChild("xsd:element", "",
							array(
							"ref" => "darwin:Country"
							));
					$sequence->addChild("xsd:element", "",
							array(
							"ref" => "darwin:StateProvince"
							));
					$sequence->addChild("xsd:element", "",
							array(
							"ref" => "darwin:County"
							));
					$sequence->addChild("xsd:element", "",
							array(
							"ref" => "darwin:Island"
							));
					$sequence->addChild("xsd:element", "",
							array(
							"ref" => "darwin:IslandGroup"
							));
					$sequence->addChild("xsd:element", "",
							array(
							"ref" => "darwin:ContinentOcean"
							));
					$sequence->addChild("xsd:element", "",
							array(
							"ref" => "darwin:Locality"
							));
					$sequence->addChild("xsd:element", "",
							array(
							"ref" => "darwin:HorizontalDatum"
							));
					$sequence->addChild("xsd:element", "",
							array(
							"ref" => "darwin:Collector"
							));
					$sequence->addChild("xsd:element", "",
							array(
							"ref" => "darwin:Remarks"
							));
					$sequence->addChild("xsd:element", "",
							array(
							"ref" => "darwin:TypeStatus"
							));
					$sequence->addChild("xsd:element", "",
							array(
							"ref" => "darwin:OtherCatalogNumbers"
							));
					$sequence->addChild("xsd:element", "",
							array(
							"ref" => "darwin:CollectorNumber"
							));
					$sequence->addChild("xsd:element", "",
							array(
							"ref" => "darwin:FieldNumber"
							));
					$sequence->addChild("xsd:element", "",
							array(
							"ref" => "darwin:GenBankNum"
							));
					$sequence->addChild("xsd:element", "",
							array(
							"ref" => "darwin:Latitude"
							));
					$sequence->addChild("xsd:element", "",
							array(
							"ref" => "darwin:Longitude"
							));
					$sequence->addChild("xsd:element", "",
							array(
							"ref" => "darwin:Sex"
							));
					$sequence->addChild("xsd:element", "",
							array(
							"ref" => "darwin:Notes"
							));
					$sequence->addChild("xsd:element", "",
							array(
							"ref" => "darwin:IdentifiedBy"
							));
					$sequence->addChild("xsd:element", "",
							array(
							"ref" => "darwin:YearIdentified"
							));
					$sequence->addChild("xsd:element", "",
							array(
							"ref" => "darwin:MonthIdentified"
							));
					$sequence->addChild("xsd:element", "",
							array(
							"ref" => "darwin:DayIdentified"
							));




				}
		$xml = 	$tree->get();

		//echo $xml;

		// Strip XML header
		$xml = str_replace (
			'<?xml version="1.0"?>', 
			'', 
			$xml);

		// Remove line breaks
		$xml = str_replace (
			"\n", 
			'', 
			$xml);

		// Replace spaces with HEX code
		$xml = str_replace (
			" ", 
			'%20', 
			$xml);

			//echo $xml;

			$url = "http://$server?doc=";
			$url .= $xml;

		//echo $url;

		return $url;



		
	}
	
	
	function inventory($institution, $queryType, $queryTerm)
	{		
		if (strcasecmp($institution,'CASENT') == 0) { $institution = 'CASENT'; }
		if (strcasecmp($institution,'inbiocri') == 0) { $institution = 'CASENT'; }
		if (strcasecmp($institution,'lacment') == 0) { $institution = 'CASENT'; }	
		if (strcasecmp($institution,'jtlc') == 0) { $institution = 'CASENT'; }	
		
		
		$server = $this->serverURL[$institution];
		$resource = $this->resourceCode[$institution] ;

		// Build request message
		$tree = new XML_Tree();

		$root = & $tree->addRoot(
			"request",
			'',
			array(
					"xmlns" => 'http://digir.net/schema/protocol/2003/1.0',
					"xmlns:xsd" => 'http://www.w3.org/2001/XMLSchema',
					"xmlns:xsi" => 'http://www.w3.org/2001/XMLSchema-instance',
					"xmlns:digir" => 'http://digir.net/schema/protocol/2003/1.0',
					"xmlns:dwc" => 'http://digir.net/schema/conceptual/darwin/2003/1.0',
					"xmlns:darwin" => 'http://digir.net/schema/conceptual/darwin/2003/1.0',
					"xsi:schemaLocation" => 'http://digir.net/schema/protocol/2003/1.0',
					"xsi:schemaLocation" => 'http://digir.net/schema/protocol/2003/1.0 http://digir.sourceforge.net/schema/protocol/2003/1.0/digir.xsd http://digir.net/schema/conceptual/darwin/2003/1.0 http://digir.sourceforge.net/schema/conceptual/darwin/2003/1.0/darwin2.xsd',
					)
			);

		$header = & $root->addChild("header");
		$header->addChild("version", "1.0.0");
		$header->addChild("sendTime", date("Ymd \TG:i:s") ); 
		$header->addChild("source", $_SERVER['SERVER_ADDR']); 
		$header->addChild("destination", 
			$server,
			array(
				"resource" => $resource
				)
			); 
		$header->addChild("type", "inventory");

		$inventory = & $root->addChild("inventory");
		$filter = & $inventory->addChild("filter");
		$equals = & $filter->addChild("equals");
		
		switch($queryType)
		{
			case 'genus':
				$equals->addChild("darwin:Genus", $queryTerm);
				$inventory->addChild("darwin:Genus");		
				break;
			case 'family':
				$equals->addChild("darwin:Family", $queryTerm);
				$inventory->addChild("darwin:Family");		
				break;
			case 'country':
				$equals->addChild("darwin:Country", $queryTerm);
				$inventory->addChild("darwin:Country");		
				break;
				
			default:
				break;
		}
	//	$inventory->addChild("darwin:ScientificName");		
		$inventory->addChild("count", "true");
		$xml = 	$tree->get();

		//echo $xml;

		// Strip XML header
		$xml = str_replace (
			'<?xml version="1.0"?>', 
			'', 
			$xml);

		// Remove line breaks
		$xml = str_replace (
			"\n", 
			'', 
			$xml);

		// Replace spaces with HEX code
		$xml = str_replace (
			" ", 
			'%20', 
			$xml);

		// KEMU doesn't like %20/
		$xml = str_replace (
			"%20/", 
			'/', 
			$xml);

			//echo $xml;

			$url = "http://$server?doc=";
			$url .= $xml;

			echo $url;

		return $url;



		
	}	
	
	
	function Harvest($institution, $queryType, $queryTerm, &$start, $page_size, &$done)
	{		
		$url = $this->search($institution, $queryType, $queryTerm, $start, $page_size);
		
		echo $url;
		
		curl_setopt($this->ch, CURLOPT_URL, $url);
		$this->Call();

		$xml = $this->result;
		
		//echo "\n\n---------------\n\n";
		//echo $xml;
		
		$xml = str_replace("xmlns='http://digir.net/schema/protocol/2003/1.0'", "", $xml);
		
		if (PHP_VERSION >= 5.0)
		{	
			$dom= new DOMDocument;
			$dom->loadXML($xml);
			$xpath = new DOMXPath($dom);
			$xpath_query = "//diagnostic[@code='END_OF_RECORDS']";
			$nodeCollection = $xpath->query ($xpath_query);
			
			foreach($nodeCollection as $node)
			{
				$done = ($node->firstChild->nodeValue != 'false');
			}
		}
		return $xml;
		
	}
	
	
	
	
	function GetInventoryCount($institution, $queryType, $queryTerm)
	{
		echo "\n\n---------------\n\n";
		$url = $this->inventory($institution, $queryType, $queryTerm);
		
		//echo $url;
		curl_setopt($this->ch, CURLOPT_URL, $url);
		
		$this->Call();
		
		$xml = $this->result;
		
		echo "\n\n---------------\n\n";
		echo $xml;
		
		$count = 0;
		$ok = false;
		
		if (preg_match('/<\?xml version/', $xml))
		{
			// we have XML
			
			$xml = preg_replace ("/<response xmlns='http:\/\/digir.net\/schema\/protocol\/2003\/1.0'/", '<response ', $xml);
			$xml = preg_replace ("/darwin:ScientificName/", 'ScientificName', $xml);
			
			//echo $xml;
			
			//echo "hello";
				
			if (PHP_VERSION >= 5.0)
			{	
				$dom= new DOMDocument;
				$dom->loadXML($xml);
				$xpath = new DOMXPath($dom);
				$xpath_query = "//ScientificName/@count";
				$nodeCollection = $xpath->query ($xpath_query);
				foreach($nodeCollection as $node)
				{
					echo  $node->firstChild->nodeValue, "<br />";
					$count += $node->firstChild->nodeValue;
				}
			}
		} 
		return $count;				
		
	}

	
	
	
	
	
	//------------------------------------------------------------------------------
	/**
	 * @brief Build a DiGIR search query.
	 *
	 * Given an institutional code (such as a museum abbreviation) and a specimen 
	 * number we construct a DiGIR query to retrieve the corresponding record.
	 * 
	 * This is a bit of an undertaking as there is not an easy means to discover the
	 * DiGIR provider corresponding to a given specimen code, and the DiGIR providers
	 * themselves vary in what version of the DarwinCore schema they support. 
	 * See The Big Dig (http://bigdig.ecoforge.net/wiki) for details.
	 * Here I use the value of  <b>Revision</b> in the &lt;version&gt; tag in the header field of
	 * the DiGIR provider response as a flag to use different code to make the query.
	 *
	 * By default I assume we have an original specimen code such as <b>FMNH 14734</b>,
	 * and this has been parsed into the institution code (FMNH), a namespace (where needed),
	 * and the specimen number
	 * (14734). Namespaces are needed for two reasons:
	 *    - Different taxon collections may have different providers (e.g., FMNH)
	 *    - The same institution and specimen code may occur in different collections
	 *      served by the same provider (e.g., MVZ)
	 *
	 * An example of an identifier is <b>FMNH:Mammals:14734</b>. 
	 * In some cases, we need some further fussing. For example, the KU and Calacademy DiGIR
	 * providers expects specimen numbers to include the institutional or collection prefix.
	 *
	 * I hard code the mapping between museum codes and DiGIR providers. 
	 * If I was clever we'd do this via GBIF's UDDI interface, but that 
	 * looks like a world of pain. Typically the DiGIR provider's URL is found by browsing
	 * GBIF's web site. 
	 *
	 * The DiGIR query is a XML tree, which is then encoded as a URL. The tree varies depending
	 * on whether the provider is serving schema 1.2 or 1.4.
	 *
	 * @param institution Institutional code
	 * @param id Specimen number
	 */
	function buildDIGIRQuery($institution, $id)
	{
		//echo $institution, "<br/>";
		
		// Kansas expects specimen codes to have KU at the start
//		if (strcasecmp($institution,'KU-Herps') == 0) { $id = 'KU' . $id; }

		// Calacademy is similar to KU 
		if (strcasecmp($institution,'CASENT') == 0) { $id = strtolower($institution) . $id; $institution = 'CASENT'; }
		if (strcasecmp($institution,'inbiocri') == 0) { $id = strtolower($institution) . $id; $institution = 'CASENT'; }
		if (strcasecmp($institution,'lacment') == 0) { $id = 'lacm ent ' . $id; $institution = 'CASENT'; }	
		if (strcasecmp($institution,'jtlc') == 0) { $id = strtolower($institution) . $id; $institution = 'CASENT'; }	


		$server = $this->serverURL[$institution];
		$resource = $this->resourceCode[$institution] ;

		// Build request message
		$tree = new XML_Tree();

		$root = & $tree->addRoot(
			"request",
			'',
			array(
					"xmlns" => 'http://digir.net/schema/protocol/2003/1.0',
					"xmlns:xsd" => 'http://www.w3.org/2001/XMLSchema',
					"xmlns:xsi" => 'http://www.w3.org/2001/XMLSchema-instance',
					"xmlns:digir" => 'http://digir.net/schema/protocol/2003/1.0',
					"xmlns:dwc" => 'http://digir.net/schema/conceptual/darwin/2003/1.0',
					"xmlns:darwin" => 'http://digir.net/schema/conceptual/darwin/2003/1.0',
					"xsi:schemaLocation" => 'http://digir.net/schema/protocol/2003/1.0',
					"xsi:schemaLocation" => 'http://digir.net/schema/protocol/2003/1.0 http://digir.sourceforge.net/schema/protocol/2003/1.0/digir.xsd http://digir.net/schema/conceptual/darwin/2003/1.0 http://digir.sourceforge.net/schema/conceptual/darwin/2003/1.0/darwin2.xsd',
					)
			);
	
		$header = & $root->addChild("header");
		$header->addChild("version", "1.0.0");
		$header->addChild("sendTime", date("Ymd \TG:i:s") ); 
		$header->addChild("source", $_SERVER['SERVER_ADDR']); 
		$header->addChild("destination", 
			$server,
			array(
				"resource" => $resource
				)
			); 
		$header->addChild("type", "search");

		$search = & $root->addChild("search");
		$filter = & $search->addChild("filter");
		
				
		
		// Australian stuff served by Ozcam may have more than one provider
		switch ($institution)
		{
			case 'SAMA':
			case 'ANWC':
			case 'AMS':
			case 'WAM':
			case 'NT':
				$and = & $filter->addChild("and");
				$equals = & $and->addChild("equals");
				$equals->addChild("darwin:CatalogNumberText", $id);
				$equals2 = & $and->addChild("equals");
				$equals2->addChild("darwin:InstitutionCode", $institution);
				break;
				
			default:
				if (preg_match('/^MVZ/', $institution))
				{				
					//echo $institution;
					list($inst, $coll) = explode("-", $institution);
					//echo $coll;
					
					$and = & $filter->addChild("and");
					$equals = & $and->addChild("equals");
					$equals->addChild("darwin:CatalogNumberText", $id);
					$equals2 = & $and->addChild("equals");
					
					switch ($coll)
					{
						case 'Herps':
							$equals2->addChild("darwin:CollectionCode", 'Herp');
							break;
						case 'Mammals':
							$equals2->addChild("darwin:CollectionCode", 'Mamm');
							break;
						case 'Birds':
							$equals2->addChild("darwin:CollectionCode", 'Bird');
							break;
							
						default:
							$equals2->addChild("darwin:CollectionCode", $coll);
							break;
							
					}
				}
				else
				{
					$equals = & $filter->addChild("equals");
					switch ($this->schema[$institution])
					{
						case "1.14":
							$equals->addChild("darwin:CatalogNumber", $id);
							break;

						default:
							$equals->addChild("darwin:CatalogNumberText", $id);
							break;
					}
				}
				break;
		}
		
		


		$records = & $search->addChild("records",
			"",
			array(
				"limit" => 10,
				"start" => 0
				)
			);
				if ($this->schema[$institution] == '1.12')
				{
				$records->addChild("structure",
					"",
					array(
	//					"schemaLocation" => "http://bnhm.berkeley.museum/manis/DwC/darwin2resultfull.xsd",
		
						"schemaLocation" => "http://digir.sourceforge.net/schema/conceptual/darwin/result/full/2003/darwin2resultfull.xsd",
						)
					);
				}
				else
				{
		
					$structure = & $records->addChild("structure", "");
			
					$element = & $structure->addChild("xsd:element", "", 
							array(
							"name" => "record"
							));
					
					$complexType =  & $element->addChild("xsd:complexType", "");
					$sequence =  & $complexType->addChild("xsd:sequence", "");
			
					$sequence->addChild("xsd:element", "",
							array(
							"ref" => "darwin:InstitutionCode"
							));
					$sequence->addChild("xsd:element", "",
							array(
							"ref" => "darwin:CollectionCode"
							));

					$sequence->addChild("xsd:element", "",
							array(
							"ref" => "darwin:CatalogNumber"
							));
					$sequence->addChild("xsd:element", "",
							array(
							"ref" => "darwin:ScientificName"
							));
					$sequence->addChild("xsd:element", "",
							array(
							"ref" => "darwin:VerbatimCollectingDate"
							));
					$sequence->addChild("xsd:element", "",
							array(
							"ref" => "darwin:DateLastModified"
							));
					$sequence->addChild("xsd:element", "",
							array(
							"ref" => "darwin:YearCollected"
							));
					$sequence->addChild("xsd:element", "",
							array(
							"ref" => "darwin:MonthCollected"
							));
					$sequence->addChild("xsd:element", "",
							array(
							"ref" => "darwin:DayCollected"
							));
					$sequence->addChild("xsd:element", "",
							array(
							"ref" => "darwin:TimeCollected"
							));
					$sequence->addChild("xsd:element", "",
							array(
							"ref" => "darwin:Kingdom"
							));
					$sequence->addChild("xsd:element", "",
							array(
							"ref" => "darwin:Phylum"
							));
					$sequence->addChild("xsd:element", "",
							array(
							"ref" => "darwin:Class"
							));
					$sequence->addChild("xsd:element", "",
							array(
							"ref" => "darwin:Order"
							));
					$sequence->addChild("xsd:element", "",
							array(
							"ref" => "darwin:Family"
							));
					$sequence->addChild("xsd:element", "",
							array(
							"ref" => "darwin:Genus"
							));
					$sequence->addChild("xsd:element", "",
							array(
							"ref" => "darwin:Species"
							));
					$sequence->addChild("xsd:element", "",
							array(
							"ref" => "darwin:Subspecies"
							));
					$sequence->addChild("xsd:element", "",
							array(
							"ref" => "darwin:Country"
							));
					$sequence->addChild("xsd:element", "",
							array(
							"ref" => "darwin:StateProvince"
							));
					$sequence->addChild("xsd:element", "",
							array(
							"ref" => "darwin:County"
							));
					$sequence->addChild("xsd:element", "",
							array(
							"ref" => "darwin:Island"
							));
					$sequence->addChild("xsd:element", "",
							array(
							"ref" => "darwin:IslandGroup"
							));
					$sequence->addChild("xsd:element", "",
							array(
							"ref" => "darwin:ContinentOcean"
							));
					$sequence->addChild("xsd:element", "",
							array(
							"ref" => "darwin:Locality"
							));
					$sequence->addChild("xsd:element", "",
							array(
							"ref" => "darwin:HorizontalDatum"
							));
					$sequence->addChild("xsd:element", "",
							array(
							"ref" => "darwin:Collector"
							));
					$sequence->addChild("xsd:element", "",
							array(
							"ref" => "darwin:Remarks"
							));
					$sequence->addChild("xsd:element", "",
							array(
							"ref" => "darwin:TypeStatus"
							));
					$sequence->addChild("xsd:element", "",
							array(
							"ref" => "darwin:OtherCatalogNumbers"
							));
					$sequence->addChild("xsd:element", "",
							array(
							"ref" => "darwin:CollectorNumber"
							));
					$sequence->addChild("xsd:element", "",
							array(
							"ref" => "darwin:FieldNumber"
							));
					$sequence->addChild("xsd:element", "",
							array(
							"ref" => "darwin:GenBankNum"
							));
					$sequence->addChild("xsd:element", "",
							array(
							"ref" => "darwin:Latitude"
							));
					$sequence->addChild("xsd:element", "",
							array(
							"ref" => "darwin:Longitude"
							));
					$sequence->addChild("xsd:element", "",
							array(
							"ref" => "darwin:Sex"
							));
					$sequence->addChild("xsd:element", "",
							array(
							"ref" => "darwin:Notes"
							));
					$sequence->addChild("xsd:element", "",
							array(
							"ref" => "darwin:IdentifiedBy"
							));
					$sequence->addChild("xsd:element", "",
							array(
							"ref" => "darwin:YearIdentified"
							));
					$sequence->addChild("xsd:element", "",
							array(
							"ref" => "darwin:MonthIdentified"
							));
					$sequence->addChild("xsd:element", "",
							array(
							"ref" => "darwin:DayIdentified"
							));
			



				}
		$xml = 	$tree->get();

		//echo $xml;

		// Strip XML header
		$xml = str_replace (
			'<?xml version="1.0"?>', 
			'', 
			$xml);
			
		// Remove line breaks
		$xml = str_replace (
			"\n", 
			'', 
			$xml);

		// Replace spaces with HEX code
		$xml = str_replace (
			" ", 
			'%20', 
			$xml);

			//echo $xml;
	
			$url = "http://$server?doc=";
			$url .= $xml;

			//echo $url;
		/*	$url = "http://$server?doc=<request%20xmlns='http://digir.net/schema/protocol/2003/1.0'%20xmlns:xsd='http://www.w3.org/2001/XMLSchema'%20xmlns:xsi='http://www.w3.org/2001/XMLSchema-instance'%20xmlns:digir='http://digir.net/schema/protocol/2003/1.0'%20xmlns:dwc='http://digir.net/schema/conceptual/darwin/2003/1.0'%20xmlns:darwin='http://digir.net/schema/conceptual/darwin/2003/1.0'%20xsi:schemaLocation='http://digir.net/schema/protocol/2003/1.0%20http://digir.sourceforge.net/schema/protocol/2003/1.0/digir.xsd%20http://digir.net/schema/conceptual/darwin/2003/1.0%20http://digir.sourceforge.net/schema/conceptual/darwin/2003/1.0/darwin2.xsd'><header><version>1.0.0</version><sendTime>20040324T094003+0100</sendTime><source>192.168.1.101</source><destination%20resource='$resource'>$server</destination><type>search</type></header><search><filter><equals><darwin:CatalogNumberText>$id</darwin:CatalogNumberText></equals></filter><records%20limit=\"10\"%20start=\"0\"><structure%20schemaLocation=\"http://bnhm.berkeley.museum/manis/DwC/darwin2resultfull.xsd\"%20/></records></search></request>";
	
		*/		

		return $url;
	}
	
	
	function GetXML($url)
	{
		curl_setopt($this->ch, CURLOPT_URL, $url);
		
		$this->Call();		
		$xml = $this->result;
		
		return $xml;
	}	
	

	function Get()
	{
		// 
		if (preg_match('/^(MVZ:|FMNH:|ROM:|KU:|USNM:|CAS:|TNHC:|OMNH:|LSUMZ:)/', $this->museumIdentifier))
		{
			list ($institution, $collection, $id) = explode (':', $this->museumIdentifier);
			$institution = $institution . "-" . $collection;
		}
		else
		{
			list ($institution, $id) = explode (':', $this->museumIdentifier);
		}

		$url = $this->buildDIGIRQuery($institution, $id);
		
		//echo $url;
		curl_setopt($this->ch, CURLOPT_URL, $url);
		
		$this->Call();
		storeInCache($institution, $id, $this->result, 'xml');
		
		$xml = $this->result;
		
		$data = '';
		$ok = false;
		
		if (preg_match('/<\?xml version/', $xml))
		{
			// we have XML
			
			$xml = preg_replace ("/<response xmlns='http:\/\/digir.net\/schema\/protocol\/2003\/1.0'/", '<response', $xml);
				
			// We got XML, but did we get a hit?
			$record_count = 0;
			if (PHP_VERSION >= 5.0)
			{	
				$dom= new DOMDocument;
				$dom->loadXML($xml);
				$xpath = new DOMXPath($dom);
				$xpath_query = "//diagnostic[@code='RECORD_COUNT']";
				$nodeCollection = $xpath->query ($xpath_query);
				foreach($nodeCollection as $node)
				{
					$record_count = $node->firstChild->nodeValue;
				}
			}
			else
			{
				$xpath = new XPath();
				$xpath->importFromString($xml);
				$xpath_query = "//diagnostic[@code='RECORD_COUNT']";
				$nodeCollection = $xpath->match($xpath_query);
				foreach($nodeCollection as $node)
				{
					$record_count = $xpath->getData($node);
				}
			}
					
			//echo $record_count;
			

			if ($record_count != 0)
			{
			
				//print $xml;
			
				$xp = new XsltProcessor();
				$xsl = new DomDocument;
				$xsl->load('xsl/digir2JSON.xsl');
				$xp->importStylesheet($xsl);
				
				$xml_doc = new DOMDocument;
				$xml_doc->loadXML($xml);
				
				$json = $xp->transformToXML($xml_doc);
				
				//echo $json;
				
				$json = str_replace("\n\"", "\"", $json);
						
				$data = json_decode($json);
							
				//print_r($data);
				
				//echo $data->status, "\n";
				
				if (isset($data->status))
				{
					if ($data->status == 'ok')
					{
						$ok = true;
					}
				}
				
			}
			else
			{
				$data->status = 'no record found';				
			}
		}
		else
		{
			$data->status = 'no XML returned';
		}
		return $data;				
		
	}
	
	function GetBCI($institutionCode, $collectionCode, $catalogNumber)
	{
		if ($collectionCode != '')
		{
			$institution = $institutionCode . "-" . $collectionCode;
		}
		else
		{
			$institution = $institutionCode;
		}
		
		$bci = '';
		// Collection GUID
		
		if (isset($this->bci[$institution]))
		{
			$bci = $this->bci[$institution];
		}
		return $bci;
		
	}
	
	
	function Get2($institutionCode, $collectionCode, $catalogNumber)
	{
		if ($collectionCode != '')
		{
			$institution = $institutionCode . "-" . $collectionCode;
		}
		else
		{
			$institution = $institutionCode;
		}

		$url = $this->buildDIGIRQuery($institution, $catalogNumber);
		
		//echo $url;
		curl_setopt($this->ch, CURLOPT_URL, $url);
		
		$this->Call();
		storeInCache($institution, $catalogNumber, $this->result, 'xml');
		
		$xml = $this->result;
		
		$data = '';
		$ok = false;
		
		if (preg_match('/<\?xml version/', $xml))
		{
			// we have XML
			
			$xml = preg_replace ("/<response xmlns='http:\/\/digir.net\/schema\/protocol\/2003\/1.0'/", '<response', $xml);
				
			// We got XML, but did we get a hit?
			$record_count = 0;
			if (PHP_VERSION >= 5.0)
			{	
				$dom= new DOMDocument;
				$dom->loadXML($xml);
				$xpath = new DOMXPath($dom);
				$xpath_query = "//diagnostic[@code='RECORD_COUNT']";
				$nodeCollection = $xpath->query ($xpath_query);
				foreach($nodeCollection as $node)
				{
					$record_count = $node->firstChild->nodeValue;
				}
			}
			else
			{
				$xpath = new XPath();
				$xpath->importFromString($xml);
				$xpath_query = "//diagnostic[@code='RECORD_COUNT']";
				$nodeCollection = $xpath->match($xpath_query);
				foreach($nodeCollection as $node)
				{
					$record_count = $xpath->getData($node);
				}
			}
					
			//echo "Record count=$record_count\n";
			

			if ($record_count != 0)
			{
			
				//echo $xml;
			
				$xp = new XsltProcessor();
				$xsl = new DomDocument;
				$xsl->load('xsl/digir2JSON.xsl');
				$xp->importStylesheet($xsl);
				
				$xml_doc = new DOMDocument;
				$xml_doc->loadXML($xml);
				
				$json = $xp->transformToXML($xml_doc);
				
				//echo $json;
				
				$json = str_replace("\n", "", $json);
						
				$data = json_decode($json);
							
				//print_r($data);
				
				//echo $data->status, "\n";
				
				// Clean weird KU stuff where they have lat=long=0
				if (isset($data->record[0]->latitude) && isset($data->record[0]->longitude))
				{
					if (($data->record[0]->latitude == 0) && ($data->record[0]->longitude == 0))
					{
						unset ($data->record[0]->latitude);
						unset ($data->record[0]->longitude);
					}
				}
				//print_r($data);
				
				
				if (isset($data->status))
				{
					if ($data->status == 'ok')
					{
						$ok = true;
					}
				}
				
			}
			else
			{
				$data->status = 'no record found';				
			}
		}
		else
		{
			$data->status = 'no XML returned';
		}
		return $data;				
		
	}
	
	
	function post_process($d)
	{
		// guid
		
		$d->guid = $d->institutionCode . ':' . $d->collectionCode . ':' . $d->catalogNumber;
		
	
		// Fix dates
		if (isset($d->verbatimCollectingDate))
		{
			$date = format_date($d->verbatimCollectingDate);
			if ('' != $date)
			{
				$d->dateCollected = $date;
			}
		}
		if (isset($d->dateLastModified))
		{
			$date = format_date($d->dateLastModified);
			if ('' != $date)
			{
				$d->dateModified = $date;
			}
		}
		
		// Coordinates
		// FUCK! What are people doing!!!!!!!
		// KU 289791 occurs three times in the same XML document, but worse the
		// decimalLatitude and decimalLongtitude fields contain the degree symbol and hemisphere.
		
		if (isset($d->latitude))
		{
			if (preg_match("/°N/", $d->latitude))
			{
				$d->latitude = str_replace("°N", "", $d->latitude);
			}
			if (preg_match("/°S/", $d->latitude))
			{
				$d->latitude = str_replace("°S", "", $d->latitude);
				if ($d->latitude > 0)
				{
					$d->latitude *= -1.0;
				}
			}
		}
		
		if (isset($d->longitude))
		{
			if (preg_match("/°E/", $d->longitude))
			{
				$d->longitude = str_replace("°E", "", $d->longitude);
			}
			if (preg_match("/°W/", $d->longitude))
			{
				$d->longitude = str_replace("°W", "", $d->longitude);
				if ($d->longitude > 0)
				{
					$d->longitude *= -1.0;
				}
			}
		}
		
		// Can't do this as loc can't be NULL
	/*			if (isset($d->longitude) && isset($d->latitude))
		{
			$d->loc = "GeomFromText('POINT(" . $d->longitude . " " . $d->latitude . ")')";
		}
	*/			
	
	
		// uBio name lookup
		$name = '';
		if (isset($d->organism))
		{
			$d->organism = trim($d->organism);
			$name = $d->organism;
		}
		else
		{
			if (isset($d->genus))
			{
				$name = $d->genus;
				if (isset($d->species))
				{
					$name .= ' ' . $d->species;
				}
				$d->organism = $name;
			}
		}
		if ($name != '')
		{
			$names = ubio_namebank_search_rest(trim($name), false, true); // just take simple exact match
			
			if (count($names) > 0)
			{
				$d->namebankID = array();
				foreach ($names as $n)
				{
					array_push($d->namebankID, $n);
				}
			}
		}
	
	
	
		return $d;
	}	
	
}




function get_specimen($institutionCode, $collectionCode, $catalogNumber, &$item)
{
	
	$id = find_specimen($institutionCode, $collectionCode, $catalogNumber);
	
	if ($id == 0)
	{
		$s = new DiGIRProvider('');
		
		//echo "$institutionCode, $collectionCode, $catalogNumber\n";
		
		// Some collections don't have a collection code,
		// or need other fixes
		switch ($institutionCode)
		{
			case 'SAMA':
				$collectionCode = '';
				break;
			case 'KUNHM':
				$institutionCode = 'KU';
				break;
				
			default:
				break;
		}
		
		$data = $s->Get2($institutionCode, $collectionCode, $catalogNumber);
		
		//print_r($data);
		
		if (isset($data->record))
		{
			foreach ($data->record as $d)
			{
		
				$d = $s->post_process($d);
				
				// We may have to reverse extract collection code
/*				$d->institutionCode = $institutionCode;
				$d->collectionCode  = $collectionCode;
				$d->catalogNumber   = $catalogNumber; */
				
				$cCode = $d->collectionCode;
		
				if ($cCode == '')
				{
				
				
				
					if (preg_match('/(.*):(?<collectionCode>.*):(.*)/', $d->guid, $matches))
					{
						$d->collectionCode = $matches['collectionCode'];
					}


					switch ($institutionCode)
					{
						case 'CAS':
							if ($collectionCode == 'Herps')
							{
								$d->collectionCode = $collectionCode;
								if (preg_match('/CAS::(?<id>\d+)/', $d->guid, $m))
								{
									$d->guid = 'CAS:Herps:' . $m['id'];
								}
							}
							break;
							
						default:
							break;
					}
					
				}
				else
				{
					// We have a collection code, but we might need to fix it...
					switch ($institutionCode)
					{
						case 'CAS':
							if ($d->collectionCode == 'ORN')
							{
								$d->collectionCode = 'Birds';
							}
							break;

						case 'KU':
							if ($d->collectionCode == 'KUH')
							{
								$d->collectionCode = 'Herps';
							}
							break;


							
						default:
							break;
					}
				}
				
				
				$bci = $s->GetBci($d->institutionCode, $d->collectionCode, $d->catalogNumber);
				if ($bci != '')
				{
					$d->bci=$bci;
				}
				
				//print_r($d);
				
				
				$id = store_specimen($d);
				
				$item = $d;
			}
		}
	}
	else
	{
		$json = retrieve_specimen_json($id);
		$item = json_decode($json);
	}
	
	return $id;
}


if (0)
{
//openurl style


$institutionCode='ROM';
$collectionCode='Herps';
$catalogNumber = 39639;

/*$institutionCode='USNM';
$collectionCode='Fish';
$catalogNumber = 376608;
*/





$institutionCode='USNM';
$collectionCode='Fish';
$catalogNumber = 327557;

$institutionCode='USNM';
$collectionCode='Herps';
$catalogNumber = 314515;

$institutionCode='USNM';
$collectionCode='Herps';
$catalogNumber = 534008;

$institutionCode='USNM';
$collectionCode='Herps';
$catalogNumber = 230000;

/*
$institutionCode='LACM';
$collectionCode='';
$catalogNumber = 128210;


$institutionCode='CAS';
$collectionCode='Herps';
$catalogNumber = '226163';


$institutionCode='SAMA';
$collectionCode='';
$catalogNumber = 'R20583';

$institutionCode='TNHC';
$collectionCode='Herps';
$catalogNumber = '63518';

*/

$institutionCode='USNM';
$collectionCode='Fish';
$catalogNumber = 376608;

$institutionCode='LACM';
$collectionCode='';
$catalogNumber = 26764;

$institutionCode='CAS';
$collectionCode='Herps';
$catalogNumber = '226163';

$institutionCode='TNHC';
$collectionCode='Herps';
$catalogNumber = '63518';

$institutionCode='KU';
$collectionCode='Herps';
$catalogNumber = 222069;

$institutionCode='CAS';
$collectionCode='Herps';
$catalogNumber = '223934';

$institutionCode='TNHC';
$collectionCode='Herps';
$catalogNumber = '63583';

$institutionCode='UWBM';
$collectionCode='Birds';
$catalogNumber = '57004';

$institutionCode='KU';
$collectionCode='Herps';
$catalogNumber = '290425';

//KUHE 24141

/*$institutionCode='KUHE';
$collectionCode='Herps';
$catalogNumber = '24141';*/

//ROM110927
$institutionCode='FMNH';
$collectionCode='Mammals';
$catalogNumber = '168879';

//USNM 111753
$institutionCode='USNM';
$collectionCode='Mammals';
$catalogNumber = '111753';


	if (find_specimen($institutionCode, $collectionCode, $catalogNumber) == 0)
	{
		$s = new DiGIRProvider('');
		$data = $s->Get2($institutionCode, $collectionCode, $catalogNumber);
		
		//print_r($data);
		
		foreach ($data->record as $d)
		{
	
			$d = $s->post_process($d);
			
			// We may have to reverse extract collection code
/*			$d->institutionCode = $institutionCode;
			$d->collectionCode  = $collectionCode;
			$d->catalogNumber   = $catalogNumber;*/
	
	
			if ($collectionCode == '')
			{
				if (preg_match('/(.*):(?<collectionCode>.*):(.*)/', $d->guid, $matches))
				{
					$d->collectionCode = $matches['collectionCode'];
				}
			}
			
			
			$bci = $s->GetBci($d->institutionCode, $d->collectionCode, $d->catalogNumber);
			if ($bci != '')
			{
				$d->bci=$bci;
			}
			
			//print_r($d);
			
			
			store_specimen($d);
			
			
			
			
			
		}
	}	

}



if (0)
{
	// One specimen
	$id = 'MCZ-Amph:A-119850';
	
	$id = 'LACM:42409';
	$id = "ROM-Herps:39639";
	$id = "KU-Herps:291678";
	
	$id = 'CAS-Herps:201776';
	
	$id = 'MVZ-Mammals:171518';
	
	$id="CASENT:9018897";
	
	$id = 'LSUMZ:Herps:17489';
	
	$id = 'USNM:Herps:343260';
	
	$id='KU:Herps:222069';
	
	$id = 'USNM:Fish:376608';

	$id = 'FMNH:Birds:396051';


	$id = 'SAMA:R62753'; // not found...
	
	$id = 'MCZ-Amph:A-88434';
	
	$id = "LACM:26764";
	
//	$id='TNHC:Herps:63518';

	$s = new DiGIRProvider($id);
	$data = $s->Get();
	
	print_r($data);
	
	foreach ($data->record as $d)
	{
		$d = $s->post_process($d);
		print_r($data);
	}
	
}

if (0)
{
	// DiGIR harvester...
	

	$s = new DiGIRProvider('');
	$done = false;
	$start = 0;
	$page_size = 10;
	
	// CAS-HERPS
	$collection = 'CAS-Herps';
	$type = 'class';
	$query = 'Reptilia';
	
	
	// 
/*	$collection = 'USNM-Mammals';
	$type = 'class';
	$query = 'Mammalia';
	
*/	

	// SAMA reptiles
	$collection = 'SAMA';
	$type = 'class';
	$query = 'Reptilia';

	$sqlfile = @fopen("$collection.sql", "w+") or die("could't open file --\"$collection.sql\"");


	
	while (!$done)
	{
		$xml = $s->Harvest($collection, $type, $query, $start, $page_size, $done);
		
		//echo $xml;
		$xp = new XsltProcessor();
		$xsl = new DomDocument;
		$xsl->load('xsl/digir2JSON.xsl');
		$xp->importStylesheet($xsl);
		
		$xml_doc = new DOMDocument;
		$xml_doc->loadXML($xml);
		
		$json = $xp->transformToXML($xml_doc);
				
		$data = json_decode($json);
				
		print_r($data);
		
		print "\n-----------\n";
		
		// process and write SQL
		
		foreach ($data->record as $d)
		{
			$d = $s->post_process($d);

			print_r($d);
			
			$count = 0;
			$sql = 'INSERT INTO darwin_core(';
			$fields = '';
			$values = '';
			
			// Generate SQL
			foreach ($d as $k => $v)
			{
				if ($count > 0)
				{
					$fields .= ',';
					$values .= ',';
				}
				$fields .= "`" . $k . "`";
				
				if ($k == 'loc')
				{
					$values .= $v;
				}
				else
				{
					$values .= $db->qstr($v);
				}
				$count++;
			}
			$sql .= $fields . ') VALUES (' . $values . ');';
			
			@fwrite($sqlfile, $sql . "\n");
		}
		
		
		
		
		
		
		
		$start += $page_size;
		
		//$done = true;
	}
	
	fclose($sqlfile);
}





?>