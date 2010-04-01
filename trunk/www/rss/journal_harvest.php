<?php

/**
 * @file harvest.php
 * 
 * Harvest journal feeds to populate bioGUID
 *
 */

require_once (dirname(__FILE__) . '/config.inc.php');
require_once (dirname(__FILE__) . '/lib.php');
require_once (dirname(__FILE__) . '/rss.php');
require_once ('../nuytsia.php');


function main()
{
	$feeds = array(
		// BioOne ----------------------------------------------------------------------------------
		'http://www.bioone.org/action/showFeed?type=etoc&feed=rss&jc=harv',
		'http://www.bioone.org/action/showFeed?type=etoc&feed=rss&jc=novi',
		'http://www.bioone.org/action/showFeed?type=etoc&feed=rss&jc=afzo',
		'http://www.bioone.org/action/showFeed?type=etoc&feed=rss&jc=mobt',
		'http://www.bioone.org/action/showFeed?type=etoc&feed=rss&jc=cara',
		'http://www.bioone.org/action/showFeed?type=etoc&feed=rss&jc=esaa',
		'http://www.bioone.org/action/showFeed?type=etoc&feed=rss&jc=brvo',
		'http://www.bioone.org/action/showFeed?type=etoc&feed=rss&jc=brit',
		'http://www.bioone.org/action/showFeed?type=etoc&feed=rss&jc=amnb',
		'http://www.bioone.org/action/showFeed?type=etoc&feed=rss&jc=acta',
		'http://www.bioone.org/action/showFeed?type=etoc&feed=rss&jc=jzoo', // Zoological Science
		
		// Ingenta ---------------------------------------------------------------------------------
		'http://api.ingentaconnect.com/content/rbsb/bjb/latest?format=rss',
		'http://api.ingentaconnect.com/content/iapt/tax/latest?format=rss',
		'http://api.ingentaconnect.com/content/schweiz/novh/latest?format=rss',
		'http://api.ingentaconnect.com/content/nhn/pimj/latest?format=rss',
		'http://api.ingentaconnect.com/content/rssa/trssa/latest?format=rss',
		
		// Entomological Society of America
		'http://api.ingentaconnect.com/content/esa/jme/latest?format=rss',
		'http://api.ingentaconnect.com/content/esa/aesa/latest?format=rss',
		
		// Scielo ----------------------------------------------------------------------------------
		'http://www.scielo.br/rss.php?pid=0085-562620090001&lang=en',
		'http://www.scielo.br/rss.php?pid=0100-8404&lang=en',
		'http://www.scielo.br/rss.php?pid=0074-0276&lang=en',
		'http://www.scielo.br/rss.php?pid=0031-1049&lang=en',
		'http://www.scielo.br/rss.php?pid=1519-566X&lang=en',
		'http://www.scielo.br/rss.php?pid=0101-817520080004&lang=en', 	// Revista Brasileira de Zoologia
		'http://www.scielo.br/rss.php?pid=1679-6225&lang=en', 			// Neotropical Ichthyology
		'http://www.scielo.br/rss.php?pid=0102-330620080004&lang=en',
				
		// Wiley
		'http://www3.interscience.wiley.com/rss/journal/118902517',
		'http://www3.interscience.wiley.com/rss/journal/118902517',
		'http://www3.interscience.wiley.com/rss/journal/118506135',
		'http://www3.interscience.wiley.com/rss/journal/117964631', 	// Systematic Entomology
		
		// CSIRO
		'http://www.publish.csiro.au/RSS_Feed/CSIRO_Publishing_Recent_SB.xml',
		'http://www.publish.csiro.au/RSS_Feed/CSIRO_Publishing_Recent_IS.xml',
		
		// Elsevier
		'http://rss.sciencedirect.com/publication/science/6963',
		
		// Springer
		'http://www.springerlink.com/content/0165-5752?sortorder=asc&export=rss',
		
		// other
		'http://www.akademiai.com/content/jw080595p305/?sortorder=asc&export=rss',
		'http://www.hindawi.com/journals/psyche/rss.xml', 												// Psyche
		'http://science.dec.wa.gov.au/nuytsia/nuytsia.rss.xml', 										// Nuytsia
		'http://pensoftonline.net/zookeys/index.php/journal/gateway/plugin/WebFeedGatewayPlugin/rss', 	// Zookeys

	);
	
	foreach ($feeds as $url)
	{
	
		$result = GetRSS ($url, $rss, true);
		
		echo $result . "\n";
		
		//exit();
		
		if ($result == 0)
		{
			echo $rss;
			
			// Process
			$rss = str_replace("\n", '', $rss);
			$rss = str_replace("\r", '', $rss);
			
			// Clean up Zookeys ATOM feed
			if (preg_match('/<\/feed><br(.*)$/', $rss))
			{
				$rss = preg_replace('/<\/feed><br(.*)$/', '</feed>', $rss);
			}
			// Clean up Zookeys RSS1 feed
			if (preg_match('/<\/rdf:RDF><br(.*)$/', $rss))
			{
				$rss = preg_replace('/<\/rdf:RDF><br(.*)$/', '</rdf:RDF>', $rss);
			}

			// Clean up Zotero ATOM feed
			if (preg_match('/^Content-Type: application\/atom\+xml/', $rss))
			{
				$rss = preg_replace('/^Content-Type: application\/atom\+xml/', '', $rss);
			}
			
			
			// Extract links (feed-type specific)
			$links = array();
			
			$dom= new DOMDocument;
			$dom->loadXML($rss);
			$xpath = new DOMXPath($dom);
			// Add namespaces to XPath to ensure our queries work
			
			$xpath->registerNamespace("rdf", "http://www.w3.org/1999/02/22-rdf-syntax-ns#");
			
			$xpath->registerNamespace("annotate", "http://purl.org/rss/1.0/modules/annotate/");
			$xpath->registerNamespace("content", "http://purl.org/rss/1.0/modules/content/");
			$xpath->registerNamespace("rss", "http://purl.org/rss/1.0/");
			$xpath->registerNamespace("slash", "http://purl.org/rss/1.0/modules/slash/");
			
			$xpath->registerNamespace("dcterms", "http://purl.org/dc/terms/");
			$xpath->registerNamespace("dc", "http://purl.org/dc/elements/1.1/");
			
			$xpath->registerNamespace("atom", "http://www.w3.org/2005/Atom");

			// Is it RSS 2.0?
			$xpath_query = "//rss/channel/item/link";
			$nodeCollection = $xpath->query ($xpath_query);
			foreach($nodeCollection as $node)
			{
				array_push($links, $node->firstChild->nodeValue);
			}
			
			// Is it RSS 1.0?
			$xpath_query = "//rdf:RDF/rss:item/rss:link";
			$nodeCollection = $xpath->query ($xpath_query);
			foreach($nodeCollection as $node)
			{
				array_push($links, $node->firstChild->nodeValue);
			}
			
			
			// Add to bioguid via OpenURL
			
			print_r($links);
			
			foreach ($links as $link)
			{
				$done = false;
				
				echo $link . "\n";
				
				// Journal-specific handling
				
				// Nuytsia is complicated as link is a database query that may return
				// multiple papers (e.g., same author may have > 1 paper in a volume
				if (preg_match('/http:\/\/science.dec.wa.gov.au\/nuytsia\//', $link))
				{
					parse_nuytsia($link);
					$done = true;
				}
				
				if (!$done)
				{
					// Default, link is URL of a single article...
					$url = "http://bioguid.info/openurl?id=" . urlencode($link) . "&display=json";
					
					$json = get($url);
					$obj = json_decode($json);
					
					print_r($obj);
				}
			}
			
			
			
			
/*			// Nope, is it RSS 1?
			if ($feed_title == '')
			{			
				// RSS 1.0
				$xpath_query = "//rss:channel/rss:title";
				$nodeCollection = $xpath->query ($xpath_query);
				foreach($nodeCollection as $node)
				{
					$feed_title = $node->firstChild->nodeValue;
					$feed_type = 'rss';
					$feed_version = 'RSS1';
					
					$nc = $xpath->query ('//rss:channel/rss:description');
					foreach($nc as $n)
					{
						$feed_description = $n->firstChild->nodeValue;
					}
	
					$nc = $xpath->query ('//rss:channel/rss:link');
					foreach($nc as $n)
					{
						$feed_link = $n->firstChild->nodeValue;
					}
				}
			}
			
			// Nope, is it ATOM?
			if ($feed_title == '')
			{
				// Atom
				$xpath_query = "//atom:feed/atom:title";
				$nodeCollection = $xpath->query ($xpath_query);
				foreach($nodeCollection as $node)
				{
					$feed_title = $node->firstChild->nodeValue;
					$feed_type = 'atom';
				}

				$xpath_query = "//atom:feed/atom:subtitle";
				$nodeCollection = $xpath->query ($xpath_query);
				foreach($nodeCollection as $node)
				{
					$feed_description = $node->firstChild->nodeValue;
				}

				// Link with rel="self" attribute 
				// <link rel="self" href="http://api.flickr.com/services/feeds/groups_pool.gne?id=806927@N20&amp;lang=en-us&amp;format=atom" />
				$xpath_query = "//atom:feed/atom:link[@rel='self']/@href";
				$nodeCollection = $xpath->query ($xpath_query);
				foreach($nodeCollection as $node)
				{
					$feed_link = $node->firstChild->nodeValue;
				}
				
				// Link with no 'rel' attribute  
				if ($feed_link == '')
				{
					$xpath_query = "//atom:feed/atom:link/@href";
					$nodeCollection = $xpath->query ($xpath_query);
					foreach($nodeCollection as $node)
					{
						$feed_link = $node->firstChild->nodeValue;
					}
				}	
			}
			
*/			
		
			

		}
	}

}

main();

?>
