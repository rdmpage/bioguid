<?php

// Simple tapir harvester, intendende just for CASENT

require_once(dirname(__FILE__).'/config.inc.php');
require_once(dirname(__FILE__).'/db.php');
require_once(dirname(__FILE__).'/lib.php');

$id = '';
$format = 'html';

if (isset($_GET['id']))
{
        $id = trim($_GET['id']);
}
if (isset($_GET['format']))
{
        switch($format)
        {
                case 'html':
				case 'xml':
                case 'rdf':
                        $format = $_GET['format'];
                        break;
                        
                default:
                        $format = 'html';
                        break;
        }
}

//$id = '0100367';
	
$state = 404; // not found

if ($id != '')
{
	$guid = 'antweb:casent' . $id;
	
	$specimen_id = find_specimen_from_guid($guid);
	if ($specimen_id != 0)
	{
		$json = retrieve_specimen_json($specimen_id);
		$item = json_decode($json);
		$state = 200;
	}
	else
	{
		$url = 'http://www.antweb.org/tapirlink/www/tapir.php/antweb?op=search&start=0&limit=1&template=http://bioguid.info/tapir/dwc_catalog_number.xml&name=casent' . $id;
		
		
		$xml = get($url);
		
		if ($xml != '')
		{
			$xp = new XsltProcessor();
			$xsl = new DomDocument;
			$xsl->load('xsl/tapir2json.xsl');
			$xp->importStylesheet($xsl);
			
			$xml_doc = new DOMDocument;
			$xml_doc->loadXML($xml);
			
			$json = $xp->transformToXML($xml_doc);
			
			//echo $json;
			
			$json = str_replace("\n\"", "\"", $json);
					
			$data = json_decode($json);
						
			//print_r($data);
			
			if (isset($data->status))
			{
				if ($data->status == 'ok')
				{
					$state = 200;
					
					$data->record[0]->bci='urn:lsid:biocol.org:col:35143';
					$data->record[0]->title = 'CASENT' . $id;
				}
			}
			
			if ($state == 200)
			{
				store_specimen($data->record[0]);
				$item = $data->record[0];
			}
		}
	}
}

switch ($state)
{
	case 404:
		ob_start();
		header('HTTP/1.1 404 Not Found');
		header('Status: 404 Not Found');
		$_SERVER['REDIRECT_STATUS'] = 404;
		break;
		
	case 400:
		ob_start();
		header('HTTP/1.0 400');
		header('Status: 400');
		$_SERVER['REDIRECT_STATUS'] = 400;
		break;
		
	case 200:
		switch ($format)
		{
			case 'rdf':
			
				$feed = new DomDocument('1.0');
				$rdf = $feed->createElement('rdf:RDF');
				$rdf->setAttribute('xmlns:rdf', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');
				$rdf->setAttribute('xmlns:rdfs', 'http://www.w3.org/2000/01/rdf-schema#');
				$rdf->setAttribute('xmlns:dcterms', 'http://purl.org/dc/terms/');
				
				$rdf->setAttribute('xmlns:geo', 'http://www.w3.org/2003/01/geo/wgs84_pos#');
					
				$rdf->setAttribute('xmlns:toccurrence', 'http://rs.tdwg.org/ontology/voc/TaxonOccurrence#');
			
			
				// Specimen
				$specimen = $rdf->appendChild($feed->createElement('toccurrence:TaxonOccurrence'));
				$specimen->setAttribute('rdf:about', 'http://bioguid.info/occurrence:' . $item->guid);
				
				// Document metadata
				$modified = $specimen->appendChild($feed->createElement('dcterms:modified'));
				$modified->appendChild($feed->createTextNode($item->dateModified));
				
				
				// Specimen codes
				$institutionCode = $specimen->appendChild($feed->createElement('toccurrence:institutionCode'));
				$institutionCode->appendChild($feed->createTextNode($item->institutionCode));
			
				$collectionCode = $specimen->appendChild($feed->createElement('toccurrence:collectionCode'));
				$collectionCode->appendChild($feed->createTextNode($item->collectionCode));
			
				$catalogNumber = $specimen->appendChild($feed->createElement('toccurrence:catalogNumber'));
				$catalogNumber->appendChild($feed->createTextNode($item->catalogNumber));
			
				// Taxon
				$identifiedToString = $specimen->appendChild($feed->createElement('toccurrence:identifiedToString'));
				$identifiedToString->appendChild($feed->createTextNode($item->organism));
				
				
				// Type status
				if (isset($item->typeStatus))
				{
					$typeStatusString = $specimen->appendChild($feed->createElement('toccurrence:typeStatusString'));
					$typeStatusString->appendChild($feed->createTextNode($item->typeStatus));
				}
				
				// Locality information
				if (isset($item->latitude))
				{
					$latitude = $specimen->appendChild($feed->createElement('toccurrence:decimalLatitude'));
					$latitude->appendChild($feed->createTextNode($item->latitude));
			
					// geo
					$latitude = $specimen->appendChild($feed->createElement('geo:lat'));
					$latitude->appendChild($feed->createTextNode($item->latitude));
			
				}
				if (isset($item->longitude))
				{
					$longitude = $specimen->appendChild($feed->createElement('toccurrence:decimalLongitude'));
					$longitude->appendChild($feed->createTextNode($item->longitude));
			
					// geo
					$longitude = $specimen->appendChild($feed->createElement('geo:long'));
					$longitude->appendChild($feed->createTextNode($item->longitude));
				}
				
				if (isset($item->locality))
				{
					$locality = $specimen->appendChild($feed->createElement('toccurrence:locality'));
					$locality->appendChild($feed->createTextNode($item->locality));
				}
				if (isset($item->county))
				{
					$county = $specimen->appendChild($feed->createElement('toccurrence:county'));
					$county->appendChild($feed->createTextNode($item->county));
				}
				if (isset($item->island))
				{
					$island = $specimen->appendChild($feed->createElement('toccurrence:island'));
					$island->appendChild($feed->createTextNode($item->island));
				}
				if (isset($item->country))
				{
					$country = $specimen->appendChild($feed->createElement('toccurrence:country'));
					$country->appendChild($feed->createTextNode($item->country));
				}
				if (isset($item->stateProvince))
				{
					$stateProvince = $specimen->appendChild($feed->createElement('toccurrence:stateProvince'));
					$stateProvince->appendChild($feed->createTextNode($item->stateProvince));
				}
				if (isset($item->continentOcean))
				{
					$continentOcean = $specimen->appendChild($feed->createElement('toccurrence:continentOcean'));
					$continentOcean->appendChild($feed->createTextNode($item->continentOcean));
				}
					
			
				// Collector details
				if (isset($item->collector))
				{
					$collector = $specimen->appendChild($feed->createElement('toccurrence:collector'));
					$collector->appendChild($feed->createTextNode($item->collector));
				}
				if (isset($item->collectorNumber))
				{
					$collectorsFieldNumber = $specimen->appendChild($feed->createElement('toccurrence:collectorsFieldNumber'));
					$collectorsFieldNumber->appendChild($feed->createTextNode($item->collectorNumber));
				}
				if (isset($item->fieldNumber))
				{
					$collectorsBatchNumber = $specimen->appendChild($feed->createElement('toccurrence:collectorsBatchNumber'));
					$collectorsBatchNumber->appendChild($feed->createTextNode($item->fieldNumber));
				}
				if (isset($item->verbatimCollectingDate))
				{
					$verbatimCollectingDate = $specimen->appendChild($feed->createElement('toccurrence:verbatimCollectingDate'));
					$verbatimCollectingDate->appendChild($feed->createTextNode($item->verbatimCollectingDate));
				}
				if (isset($item->dateCollected))
				{
					$earliestDateCollected = $specimen->appendChild($feed->createElement('toccurrence:earliestDateCollected'));
					$earliestDateCollected->appendChild($feed->createTextNode($item->dateCollected));
			
					$latestDateCollected = $specimen->appendChild($feed->createElement('toccurrence:latestDateCollected'));
					$latestDateCollected->appendChild($feed->createTextNode($item->dateCollected));
				}
				
				// BCI
				if (isset($item->bci))
				{
					$type = $specimen->appendChild($feed->createElement('toccurrence:hostCollection'));
					$type->setAttribute('rdf:resource', 'http://bioguid.info/' . $item->bci);		
				}
			
				
				$rdf = $feed->appendChild($rdf);
			
				$feed->encoding='utf-8';
				header("Content-type: application/rdf+xml; charset=utf-8\n\n");	
				echo $feed->saveXML();
				break;

			case 'html':
			default:
				header("Content-type: text/html; charset=utf-8\n\n");	
				echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">' 
					. "\n" . '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">' . "\n";
				echo '<head>';
				echo '<title>' . $item->title . '</title>';
				echo '<style type="text/css">
				body 
				{
					font-family: Verdana, Arial, sans-serif;
					font-size: 12px;
					padding:30px;
				
				}
				</style>';
				
				echo '</head>';
				echo '<body>';
				
				echo '<div style="font-size:18px;font-family:Georgia,Times,serif;font-weight:bold;">' . $item->title . '</div>';
				
				echo '<p>' . $item->organism . '</p>';
				echo '<p>' . $item->locality . '</p>';
				
				if (isset($item->latitude))
				{
					echo '
			<!--[if IE]>
			<embed width="360" height="180" src="map.php?lat=' . $item->latitude . '&long=' . $item->longitude . '">
			</embed>
			<![endif]-->
			<![if !IE]>
			<object id="mysvg" type="image/svg+xml" width="360" height="180" data="map.php?lat=' . $item->latitude . '&long=' . $item->longitude . '">
			<p>Error, browser must support "SVG"</p>
			</object>
			<![endif]>	';
					
				
				
				}				

				echo '</body>';
				echo '</html>';
				break;
		}
	

}
	


?>