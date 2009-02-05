<?php

// $Id: $

/* Extract identifiers from article URLs (e.g., those returned by Google Scholar) */

require ('config.inc.php');
require_once('crossref.php');
require_once('jstor.php');
require_once('lib.php');
require_once('scraping.php');



//--------------------------------------------------------------------------------------------------
function fetchCambridge($url, &$item)
{
	$success = false;
	
	//echo 'fetchCambridge';
	
	//echo $url;
	
	$html = get($url);
	
	//echo $html;
	if ($html != '')
	{
		$item->status = 'ok';
	
		// Get meta tags (may have useful info, such as issn)
		preg_match_all('|<meta[^>]+name=\"([^\"]*)\"\s*(scheme=\"([^\"]*)\"\s*)*content=\"([^\"]*)\"[^>]+>|',  $html, $out, PREG_PATTERN_ORDER);
		$r = print_r ($out, true);
	
	/*	echo "<pre>";
		echo htmlentities($r);
		echo "</pre>";*/ 
		
		parseCoins($out, $item);
		
		// Extract journal info
		//echo 'boo';
		
		$start = strpos($html, '<h5>');
		$end = strpos($html, '</h5>');
		
		if (($start != false) && ($end != false))
		{
		
			//echo "Start=$start - End=$end\n";
			
			$j = substr($html, $start, ($end - $start));
			//echo $j;
			
			$j = str_replace("Cambridge University Press", "", $j);
			$j = str_replace($item->title, "", $j);
			
			$match = array();
			if (preg_match('/\([0-9]{4}\),\s*([0-9]+):\s*([0-9]+)[-|\-]([0-9]+)/', $j, $match))
			{
				//print_r($match);
				
				$item->volume = $match[1];
				$item->spage = $match[2];
				$item->epage = $match[3];
			}
		}		
	}
}

//--------------------------------------------------------------------------------------------------
/**
 *
 *
 * Allen Press URLs may have DOIs but these aren't functional. Instead we have to unpack the URL 
 * and construct a new URL to extract the RIS file.
 *
 */
function parseAllenUrl($url, &$item)
{
	$item->status = 'failed';
	
	// Encode doi part of URL, otherwise Allen Press won't recognise it
	if (preg_match('/doi=(.*)/', $url, $matches))
	{
		//print_r($matches);
		$escaped_doi = urlencode($matches[1]);
		$url = preg_replace("/doi=(.*)/", "doi=$escaped_doi", $url);
	}

	
	$item->url = $url;

	$html = get($url);
	
	//echo "html=" . $html;
	//echo "url=" . $url;
	
	if ($html != '')
	{
		$matches = array();
		if (preg_match('/<a href="(\?request=cite-builder&doi=(.*))">/', $html, $matches))
		{
			$doi = urldecode($matches[2]);
			
			$p = explode("/", $doi);
			
			// Get the pseudo SICI
			$sici = $p[1]; 
			
			// Fix format
			$sici = str_replace("[", "<", $sici);
			$sici = str_replace("]", ">", $sici);
			
			// Get details
			$x = unpack_sici($sici);
			
			$u = 'http://apt.allenpress.com/perlserv/?request=download-citation&t=refman&f='				
			. $x['issn']
			. '_' . $x['volume']
			. '_' . $x['site']
			. '&doi=' . urlencode($doi);
			
			$item->issn = $x['issn'];
			
						
			$ris = get($u);
			
			if ($ris == '')
			{
				
			}
			else
			{
				$item->status = 'ok';
				parseRIS($ris, $item);
				
				// We might be able to get a real DOI at this point
				$doi = search_for_doi($item->issn, $item->volume, $item->spage, 'article', $item);		
				if ($doi == '')
				{
					unset($item->doi);
				}
				else
				{
					$item->doi = $doi;
				}
			}
			
		}
	}
}


//--------------------------------------------------------------------------------------------------
function fetchInformaworld($url, &$item)
{
	$success = false;
	
	$html = get($url);
	
	//echo 'boo';
	
	echo $html;
	
	
	if ($html != '')
	{
		$item->status = 'ok';
	
		// Get meta tags (may have useful info, such as issn)
		preg_match_all('|<meta[^>]+name=\"([^\"]*)\"\s*(scheme=\"([^\"]*)\"\s*)*content=\"([^\"]*)\"[^>]+>|',  $html, $out, PREG_PATTERN_ORDER);
	
		$r = print_r ($out, true);
	
		/*echo "<pre>";
		echo htmlentities($r);
		echo "</pre>"; */
	
		parseDcMeta($out, $item);
	}
}

//--------------------------------------------------------------------------------------------------
function fetchIngenta($url, &$item)
{
	$success = false;
	
	$html = get($url);
	
	//echo $html;
	
	if ($html != '')
	{
		$item->status = 'ok';
	
		// Get meta tags (may have useful info, such as issn)
		preg_match_all('|<meta[^>]+name=\"([^\"]*)\"\s*(scheme=\"([^\"]*)\"\s*)*content=\"([^\"]*)\"[^>]+>|',  $html, $out, PREG_PATTERN_ORDER);
	
		$r = print_r ($out, true);

	/*	echo "<pre>";
		echo htmlentities($r);
		echo "</pre>";*/
	
	
	
		// build attributes
	
	
		//$attributes 
	
	
		foreach($out[1] as $k => $v)
		{
	//		echo $v, "<br/>";
			
			switch ($v)
			{
				case 'DCTERMS.isPartOf':
					$issn = $out[4][$k];			
					$issn = str_replace('urn:ISSN:','',$issn);
					$item->issn = $issn;
					break;
		
		/*		case 'DC.title':
					$attributes['title'] = $out[4][$k];
					break;
					*/
				default:
					break;
			}
		}
		
		
		
		// RIS link
		
		$url = str_replace('www.ingenta', 'api.ingenta', $url);
		$url .= "?format=ris";
		
		$ris = get($url);
		
		if ($ris == '')
		{
			$item->status = 'failed';
		}
		else
		{
			parseRIS($ris, $item);
		}
		
	}	
}





//--------------------------------------------------------------------------------------------------
function url2meta($url)
{
	global $config;
	
	$url = urldecode($url);

	$item = new stdClass;
	$item->status = 'failed';
	$item->authors = array();


	$match=array();

	//------------------------------------------------------------------------------
	// DSpace
	if (preg_match('/\/dspace\//', $url))
	{
	
		// AMNH
		if (preg_match('/http:\/\/digitallibrary.amnh.org\/dspace\//', $url))
		{
			// rewrite URL
			$url = str_replace('http://digitallibrary.amnh.org/dspace/', '', $url);
			$url = str_replace('handle/', '', $url);
			$url = str_replace('bitstream/', '', $url);
			$url = preg_replace('/\/1\/N.*/', '', $url);
			
			$item->status = 'ok';
			$item->comment = 'url';
			$item->hdl = $url;
		}
		
		if (preg_match('/https:\/\/qir.kyushu-u.ac.jp\/dspace\//', $url))
		{
			// rewrite URL
			$url = str_replace('https://qir.kyushu-u.ac.jp/dspace/', '', $url);
			$url = str_replace('handle/', '', $url);
			$url = str_replace('bitstream/', '', $url);
			$url = preg_replace('/\/1\/.*/', '', $url);
			
			$item->status = 'ok';
			$item->comment = 'url';
			$item->hdl = $url;
		}
	}
	
	//------------------------------------------------------------------------------
	// Blackwells
	if (preg_match('/http:\/\/www.blackwell-synergy.com\//', $url))
	{
		list($prefix, $suffix) = split ('/doi/', $url);
	
		$suffix = str_replace("abs/", "", $suffix);
		$suffix = str_replace("full/", "", $suffix);
		$doi = preg_replace('/\?.*/', '', $suffix);
		
		$item->status = 'ok';
		$item->doi = $doi;
	}
	
	//------------------------------------------------------------------------------
	// Cambridge
	if (preg_match('/http:\/\/journals.cambridge.org\//', $url))
	{
		fetchCambridge($url, $item);
	}
	
	//------------------------------------------------------------------------------
	// Ingenta
	if (preg_match('/http:\/\/www.ingentaconnect.com\//', $url))
	{
		fetchIngenta($url, $item);
	}

	//------------------------------------------------------------------------------
	// Informa
	if (preg_match('/http:\/\/www.informaworld.com\//', $url))
	{
		if ('' == $config['proxy_name'])
		{
			fetchInformaworld($url, $item);
		}
		else
		{
			if (preg_match('/http:\/\/www.informaworld.com\/index\/([0-9]+).pdf/', $url, $match))
			{
				//echo $url;
				$url = 'http://www.informaworld.com/smpp/content~db=all~content=a' . $match[1];
				fetchInformaworld($url, $item);
			}
		}
	}
	
	//------------------------------------------------------------------------------
	// T&F
	if (preg_match('/http:\/\/taylorandfrancis.metapress.com\//', $url))
	{
		// We get bounced to Informaworld, which has DC meta
		if ('' == $config['proxy_name'])
		{
			fetchInformaworld($url, $item);
		}
		else
		{
			if (preg_match('/http:\/\/www.informaworld.com\/index\/([0-9]+).pdf/', $url, $match))
			{
				//echo $url;
				$url = 'http://www.informaworld.com/smpp/content~db=all~content=a' . $match[1];
				fetchInformaworld($url, $item);
			}
		}
	}	
	
	//------------------------------------------------------------------------------
	// Wiley
	if (preg_match('/http:\/\/doi.wiley.com\//', $url))
	{
			$url = str_replace('http://doi.wiley.com/', '', $url);
			
			$item->status = 'ok';
			$item->comment = 'url';
			$item->doi = $url;
	}

	//------------------------------------------------------------------------------
	if (preg_match('/http:\/\/apsjournals.apsnet.org\/doi\/abs\//', $url))
	{
			$url = str_replace('http://apsjournals.apsnet.org/doi/abs/', '', $url);
			
			$item->status = 'ok';
			$item->comment = 'url';
			$item->doi = $url;
	}
	//------------------------------------------------------------------------------
	if (preg_match('/http:\/\/biology.plosjournals.org\/perlserv(\/)?\?request=get\-document&doi=(.*)/', $url, $match))
	{			
			$item->status = 'ok';
			$item->comment = 'url';
			$item->doi = $match[2];
	}
	
	//------------------------------------------------------------------------------
	if (preg_match('/http:\/\/www.journals.uchicago.edu\/doi\/(abs|full)\/(.*)/', $url, $match))
	{
			$item->status = 'ok';
			$item->comment = 'url';
			$item->doi = $match[2];
	}
	//------------------------------------------------------------------------------
	if (preg_match('/http:\/\/arjournals.annualreviews.org\/doi\/(abs|full)\/(.*)/', $url, $match))
	{
			$item->status = 'ok';
			$item->comment = 'url';
			$item->doi = $match[2];
	}
	//------------------------------------------------------------------------------
	if (preg_match('/http:\/\/www.journals.uchicago.edu\/cgi-bin\/resolve\?id=doi:(.*)/', $url, $match))
	{
			$item->status = 'ok';
			$item->comment = 'url';
			$item->doi = $match[1];
			
			
	}
	
	
	
	
	//------------------------------------------------------------------------------
	if (preg_match('/http:\/\/www.bioone.org\/perlserv\/\?request=get-(abstract|document)&doi=(.*)/', $url, $match))
	{
		$item->status = 'ok';
		$item->comment = 'url';
		$item->doi = $match[2];
		
		//echo $match[1];
		
		$cite_url = 'http://www.bioone.org/perlserv/?request=cite-builder&doi=' . urlencode($match[1]);
		
		//echo $cite_url;
		
		// Harvest as DOI may be broken...
		$html = get($cite_url);
		if ($html != '')
		{
			//echo $html;
			
			
			preg_match('/<a href="(\?request=download-citation&#38;t=refman&#38;f=([0-9]{4}\-[0-9]{3}([0-9]|X))(.*))">Reference Manager/', $html, $match);
			
			//print_r($match);
			
			if (isset($match[1]))
			{
				$issn = $match[2];
				
				$item->issn = $match[2];
			
				$ris_url = 'http://www.bioone.org/perlserv/' . $match[1];
				
				$ris_url = str_replace('&#38;', '&', $ris_url);
				
				//echo '<b>', $ris_url, '</b><br/>';
				
				$ris = get($ris_url);
				
				parseRIS($ris, $item);
				
				// Make DOI-safe URL
				
				$item->url = 
				
				sprintf('http://www.bioone.org/perlserv/?request=get-abstract'
					. '&issn=%s' 
					. '&volume=%03d'  
					. '&issue=%02d' 
					. '&page=%04d',
					 $item->issn,
					 $item->volume,
					 $item->issue,
					 $item->spage);
					 
			}
		}
	}
	
	//------------------------------------------------------------------------------

	// Non-DOI version of BioOne URL
	if (preg_match('/http:\/\/www.bioone.org\/perlserv\/\?request=get-abstract&issn=(.*)/', $url, $match))
	{
		$item->status = 'ok';
		$item->comment = 'url';
		
		$html = get($url);
		if ($html != '')
		{

			if (preg_match('/<a href="\?request=cite-builder&doi=(.*)">Create Reference/', $html, $match))
			{					
				$cite_url = 'http://www.bioone.org/perlserv/?request=cite-builder&doi=' . $match[1];
				
				// Harvest as DOI may be broken...
				$html = get($cite_url);
				if ($html != '')
				{
					//echo $html;
					
					
					preg_match('/<a href="(\?request=download-citation&#38;t=refman&#38;f=([0-9]{4}\-[0-9]{3}([0-9]|X))(.*))">Reference Manager/', $html, $match);
					
					//print_r($match);
					
					if (isset($match[1]))
					{
						$issn = $match[2];
						
						$item->issn = $match[2];
					
						$ris_url = 'http://www.bioone.org/perlserv/' . $match[1];
						
						$ris_url = str_replace('&#38;', '&', $ris_url);
						
						//echo '<b>', $ris_url, '</b><br/>';
						
						$ris = get($ris_url);
						
						parseRIS($ris, $item);
						
						// Make DOI-safe URL
						
						$item->url = 
						
						sprintf('http://www.bioone.org/perlserv/?request=get-abstract'
							. '&issn=%s' 
							. '&volume=%03d'  
							. '&issue=%02d' 
							. '&page=%04d',
							 $item->issn,
							 $item->volume,
							 $item->issue,
							 $item->spage);
							 
					}
				}
			}
		}
	}
	


	//------------------------------------------------------------------------------
	// Springer (metapress)
	if (preg_match('/http:\/\/www.springerlink.com\//', $url))
	{
		$url = str_replace('http://www.springerlink.com/index/', '', $url);
		$url = str_replace('.pdf', '', $url);
		$url = 'http://www.springerlink.com/content/' . $url;
		
		//print $url;
		
		$html = get($url);
		
		//echo $html;
		
		if ($html != '')
		{
			$item->status='ok';
			$match = array();
			preg_match('/<td class="labelName">DOI<\/td><td class="labelValue">(.*)<\/td>/', $html, $match);
			if (isset($match[1]))
			{
				$item->doi = $match[1];
			}	
		}
	}	
	
	//------------------------------------------------------------------------------
	// Royal Society (Metapress)
	if (preg_match('/http:\/\/www.journals.royalsoc.ac.uk\//', $url))
	{
		// rewrite URL	
		$url = str_replace('http://www.journals.royalsoc.ac.uk/index/', '', $url);
		$url = str_replace('.pdf', '', $url);
		$url = 'http://journals.royalsociety.org/content/' . $url;
		
		// look for DOI
		$html = get($url);
		if ($html != '')
		{
			$item->status='ok';
			$match = array();
			preg_match('/DOI<\/td><td class="labelValue">(.*)<\/td>/', $html, $match);
		
			if (isset($match[1]))
			{
				$item->doi = $match[1];
			}
		
		}
	}	
	
	//------------------------------------------------------------------------------
	// NCBI
	if (preg_match('/http:\/\/www.ncbi.nlm.nih.gov/', $url))
	{
		$match = array();
		preg_match('/list_uids=([0-9]+)/', $url, $match);
	
		if (isset($match[1]))
		{
			$item->status='ok';
			$item->status='url';
			$item->pmid = $match[1];
		}
		
		if (preg_match('/http:\/\/www.ncbi.nlm.nih.gov\/pubmed\/([0-9]+)/', $url, $match))
		{
				$item->status='ok';
				$item->status='url';
				$item->pmid = $match[1];
		}
	}
	
	#http://www.ncbi.nlm.nih.gov/pubmed/11029000
	
	
	
	//------------------------------------------------------------------------------
	// Allen Press (another DOI-buggering organisation
	if (preg_match('/http:\/\/apt.allenpress.com\//', $url))
	{
		// Unpack URL
		
		//echo 'allen';
		parseAllenUrl($url, $item);
	}	
	
	//------------------------------------------------------------------------------
	// Raffles
	if (preg_match('/http:\/\/rmbr.nus.edu.sg\/rbz\//', $url))
	{
		// extract details from URL
		
		$match = array();
		preg_match('/http:\/\/rmbr.nus.edu.sg\/rbz\/biblio\/([0-9]+)\/([0-9]+)rbz([0-9]+)(\-([0-9]+))?.pdf/', $url, $match);
	/*	echo '<pre>';
		print_r($match);
		echo '</pre>';*/
		
		if (6 == count($match))
		{
			$item->status='ok';
			$spage = $match[3];
			$spage = preg_replace('/^0*/', '', $spage);
			$epage = $match[5];
			$epage = preg_replace('/^0*/', '', $epage);
			$item->spage = $spage;
			$item->epage = $epage;
			$item->volume = $match[1];
			$item->issn = '0217-2445';
			$item->url = $url;
			
		
		}
	}	
	
	//------------------------------------------------------------------------------
	// CSIRO
	
	// http://www.publish.csiro.au/nid/150/paper/display/citation/paper/SB9910229.htm
	
	//echo $url;
	
	if (preg_match('/(http:\/\/www.publish.csiro.au\/\?paper=(.*)|http:\/\/www.publish.csiro.au\/nid\/[0-9]+\/display\/citation\/paper\/(.*).htm|http:\/\/www.publish.csiro.au\/\?act=view_file&file_id=(.*).pdf|http:\/\/www.publish.csiro.au\/nid\/[0-9]+\/paper\/(.*).htm)/', $url, $match))
	{
		//print_r($match);
		//echo '<br/>';
		
		$id = '';
		
		if (isset($match[2]))
		{
			$id = $match[2];
		}
		if (isset($match[3]))
		{
			$id = $match[3];
		}
		if (isset($match[5]))
		{
			$id = $match[5];
		}
		
		$url = 'http://www.publish.csiro.au/?paper=' . $id;
		
		$html = get($url);
		if ($html != '')
		{
			$item->status='ok';
			$match = array();
			
			$ms = '/doi:((.*)\/' . $id . ')/';
			//echo $ms, '<br/>';
			
			preg_match($ms, $html, $match);
			
			//print_r($match);
		
			if (isset($match[1]))
			{
				$item->doi = $match[1];
			}
		
		}

	
/*		print_r($match);
		
		$ris_url = 'http://www.publish.csiro.au/view/journals/dsp_journal_retrieve_citation.cfm?ct='
		 . $match[2] . '.ris';
		 
		$ris = get($ris_url);
		
		if ($ris == '')
		{
			$item->status = 'failed';
		}
		else
		{
			$item->status = 'ok';
			parseRIS($ris, $item);
		}*/
		 
		 
	}
	
	
	//------------------------------------------------------------------------------
	// Highwire Press
	if (preg_match('/http:\/\/(www.)*(.*).org\/cgi\/content\/abstract\/([0-9]+\/[0-9]+\/[0-9]+)/', $url, $match))
	{
		//print_r($match);
		
		
		$ris_url = 'http://' . $match[1] 
			. $match[2] . '.org/cgi/citmgr?type=refman&gca=';
			
			
		//echo $match[2], '<br/>';
		
		switch($match[2])
		{
			case 'aob.oxfordjournals':
				$ris_url .= 'annbot';				
				$item->issn = '0305-7364';				
				break;
			case 'biolbull':
				$ris_url .= 'biolbull';
				$item->issn = '0006-3185';
				break;
			case 'sciencemag':
				$ris_url .= 'sci';
				$item->issn = '0036-8075';
				break;
			case 'nar.oxfordjournals':
				$ris_url .= 'nar';
				$item->issn = '0305-1048';
				break;
			case 'aem.asm':
				$ris_url .= 'aem';
				$item->issn = '0099-2240';
				break;
			case 'ijs.sgmjournals':
				$ris_url .= 'ijs';
				$item->issn = '1466-5026';
				break;
			case 'bioinformatics.oxfordjournals':
				$ris_url .= 'bioinfo';
				$item->issn = '1367-4803';
				break;
			case 'icb.oxfordjournals':
				$ris_url .= 'icbiol';
				$item->issn = '1540-7063';
				break;
			case 'jvi.asm':
				$ris_url .= 'jvi';
				$item->issn = '0022-538X';
				break;
			case 'mbe.oxfordjournals':
				$ris_url .= 'molbiolevol';
				$item->issn = '0737-4038';
				break;
			case 'mollus.oxfordjournals':
				$ris_url .= 'mollus';
				$item->issn = '0260-1230';
				break;
			case 'studiesinmycology':
				$ris_url .= 'simycol';
				$item->issn = '0166-0616';
				break;
			case 'beheco.oxfordjournals':
				$ris_url .= 'beheco';
				$item->issn = '1045-2249';
				break;
			default:
				$ris_url .= $match[2];
				break;
		}
		$ris_url .= ';' . $match[3];

		
		//echo $ris_url;
		
		$ris = get($ris_url);
		
		if ($ris == '')
		{
			$item->status = 'failed';
		}
		else
		{
			$item->status = 'ok';
			parseRIS($ris, $item);
		}
	}
	
	// JSTOR stable
	if (preg_match('/http:\/\/www.jstor.org\/pss\/[0-9]+/', $url, $match))
	{
		$id = $match[2];
		
		$html = get($url);
		// Add line feeds so regular expresison works
		$html = str_replace('<meta', "\n<meta", $html);

		// Pull out the meta tags
		preg_match_all("|<meta\s*name=\"(dc.[A-Za-z]*)\"\s*(scheme=\"(.*)\")?\s*(content=\"(.*)\")><\/meta>|",  $html, $out, PREG_PATTERN_ORDER);
	
		$r = print_r ($out, true);
	
		/*echo "<pre>";
		echo htmlentities($r);
		echo "</pre>";*/
	
		parseDcMeta($out, $item);
		
		// Kill DOI on assumption we wouldn't be harvesting stable URL if DOI worked
		unset($item->doi);
		
		
		$item->status = 'ok';
		$item->url = $url;
	}
	
	
	

	//------------------------------------------------------------------------------
	// JSTOR
	if (preg_match('/http:\/\/(www|links).jstor.org\/sici\?sici=(.*)/', $url, $match))
	{
		$sici = $match[2];
		
		$html = get($url);
				
		//echo $html;
		
		if ('' == $config['proxy_name'])
		{
			// Outside Glasgow so we get metadata directly
		}
		else
		{
			// Inside Glasgow, we are licensed, so we need one more step
			
			// Extract stable indentifier
			preg_match('/&amp;suffix=([0-9]+)/', $html, $match);
			
			//print_r($match);
		
			if (isset($match[1]))
			{
				$item->stable = $match[1];
				$item->url = 'http://www.jstor.org/stable/' . $match[1];
				
				// ok, harvest
							
				$html = get('http://www.jstor.org/stable/info/' . $match[1]);
			}	
			
		}
		
			
			// Add line feeds so regular expresison works
			$html = str_replace('<meta', "\n<meta", $html);
	
			// Pull out the meta tags
			preg_match_all("|<meta\s*name=\"(dc.[A-Za-z]*)\"\s*(scheme=\"(.*)\")?\s*(content=\"(.*)\")><\/meta>|",  $html, $out, PREG_PATTERN_ORDER);
		
			$r = print_r ($out, true);
		
			/*echo "<pre>";
			echo htmlentities($r);
			echo "</pre>";*/
		
			parseDcMeta($out, $item);
			
			// Can we get anything more out of SICI?
			$out = unpack_sici($sici);
			
			if (isset($out['issn'])) $item->issn = $out['issn'];
			if (isset($out['year'])) $item->year = $out['year'];
			if (isset($out['volume'])) $item->volume = $out['volume'];
			if (isset($out['issue'])) $item->issue = $out['issue'];
			if (isset($out['site'])) $item->spage = $out['site'];
		
		
		
		
		$item->status = 'ok';
		$item->sici = $sici;
	}


	//------------------------------------------------------------------------------
	// Elsevier
	// http://linkinghub.elsevier.com/retrieve/pii/S0885576501903558
	if (preg_match('/http:\/\/linkinghub.elsevier.com/', $url))
	{
		
		$html = get($url);
	
		//echo $html;		
			
		preg_match('/doi:(.*)<\/a>&nbsp;/', $html, $match);
		if (isset($match[1]))
		{
			$item->status = 'ok';
			$item->doi = $match[1];
		}
	}	
	
	//------------------------------------------------------------------------------
	//http://www.sciencedirect.com/science?_ob=GatewayURL&_origin=IRSSSEARCH&_method=citationSearch&_piikey=S072320200800043X&_version=1&md5=952f39fe9bd26f49f29dd4282cc2f224
	if (preg_match('/http:\/\/www.sciencedirect.com/', $url))
	{
		$html = get($url);
	
		//echo $html;		
			
		preg_match('/doi:(.*)<\/a>&nbsp;/', $html, $match);
		if (isset($match[1]))
		{
			$item->status = 'ok';
			$item->doi = $match[1];
		}
	}	
	
	
	// http://rparticle.web-p.cisti.nrc.ca/rparticle/AbstractTemplateServlet?calyLang=eng&journal=cjz&volume=56&year=0&issue=3&msno=z78-059
	if (preg_match('/http:\/\/rparticle.web\-p.cisti.nrc.ca/', $url))
	{
		$html = get($url);
		
		$item->url = $url;
		
		// NRC does things a little differently...
		preg_match_all("|<meta\s*name=\"(?<key>((?<namespace>[A-Za-z]+)\.)?(?<value>[A-Za-z]+))\"\s*(content=\"(?<content>.*)\")\s*\/>|",  $html, $out, PREG_PATTERN_ORDER);
	
//		$r = print_r ($out, true);
		
		//print_r($out);

		$authors = array();
		
		foreach($out['key'] as $k => $v)
		{
			//echo $k . ' ' . $v . " " . $out['content'][$k] . "\n";
			
			switch($v)
			{
				case 'dc.title':
					$atitle = $out['content'][$k];
					$atitle = html_entity_decode($atitle, ENT_QUOTES, "utf-8" ); 			
					$atitle = strip_tags($atitle);
					$item->atitle = $atitle;
					break;
					
				case 'title.journal':
					if (!isset($item->title))
					{
						$item->title = $out['content'][$k];
						switch($item->title)
						{
							case 'Can. J. Zool.':
								$item->issn = '0008-4301';
								break;
								
							default:
								break;
						}
					}
					break;
					
				case 'author':
					if (count($authors) == 0)
					{
						$a = $out['content'][$k];
						// split string
						// Protect Jr
						$a  = str_replace(", JR", " Jr", $a);
						$authors  = str_replace(", Jr", " Jr", $a);
						
						$a = preg_replace("/,? and /", "|", $a);
						$a  = str_replace(", ", "|", $a);
						
						$authors = explode("|", $a);
					}
					break;
					
				case 'identifier.doi':
					$item->doi = $out['content'][$k];
					break;
				case 'identifier.volume':
					$item->volume = $out['content'][$k];
					break;
				case 'identifier.issue':
					$item->issue = $out['content'][$k];
					break;
				case 'identifier.startpage':
					$item->spage = $out['content'][$k];
					break;
				case 'date.published':
					$item->year = $out['content'][$k];
					break;

					
				default:
					$item->status = 'ok';
					break;
			}
			
			
		}
		
		// Abstract
		$html = str_replace("\n", " ", $html);
		$html = str_replace("\r", " ", $html);
		$match = array();	
		if (preg_match('/<a name="abs_english" id="abs_english"><!--abs_english--><\/a>(.*)<a name="abs_french" id="abs_french">/', $html, $match))
		{
			print_r($match);
			
			$abstract = $match[1];
			$abstract = str_replace("<b>Abstract: </b>", "", $abstract);
			$abstract = html_entity_decode($abstract, ENT_QUOTES, "utf-8" ); 			
			$abstract = strip_tags($abstract);
			$abstract = trim($abstract);
			$item->abstract= $abstract;
		}
		
		
		
		
		// Clean up authors
		$item->authors = array();
		$authors = array_unique($authors);
		
		foreach ($authors as $a)
		{
		
			$a = mb_convert_case($a, 
				MB_CASE_TITLE, mb_detect_encoding($a));
		
			// Get parts of name
			$parts = parse_name($a);
			
			$author = new stdClass();
			
			if (isset($parts['last']))
			{
				$author->lastname = $parts['last'];
			}
			if (isset($parts['suffix']))
			{
				$author->suffix = $parts['suffix'];
			}
			if (isset($parts['first']))
			{
				$author->forename = $parts['first'];
				
				if (array_key_exists('middle', $parts))
				{
					$author->forename .= ' ' . $parts['middle'];
				}
			}
		
			array_push($item->authors, $author);
		
		}
	
		
		
		
		
		
		
	}
	
	// Get some metadata from Naturalis (because their OAI service is fucked
	if (preg_match('/http:\/\/www.repository.naturalis.nl\/record/', $url))
	{
		$html = get($url);
		
		//echo $html;
		
		$item->url = $url;
		
		$match = array();
		
		if (preg_match("/Pages<\/td><td class=\"value\">(?<spage>[0-9]+)(\-(?<epage>[0-9]+))?/",  $html, $match))
		{
			//print_r($match);
			
			$item->spage = $match['spage'];
			$item->epage = $match['epage'];
		}
		
	}
	
	return $item;
	
	
	
/*	echo '<pre>';
	print_r($item);
	echo '</pre>'; */
}

// test

/*
$url = 'http://www.repository.naturalis.nl/record/227182';
echo $url;

$item = url2meta($url);

print_r($item);*/


/*
$url = 'http://rparticle.web-p.cisti.nrc.ca/rparticle/AbstractTemplateServlet?calyLang=eng&journal=cjz&volume=34&year=0&issue=5&msno=z56-049';

$item = url2meta($url);
$item->source = $url;

print_r($item);

*/

// test

/*$url = 'http://www.amjbot.org/cgi/content/abstract/91/5/760';
$url = 'http://www.springerlink.com/index/WQ53G717850U04LU.pdf';
$url = 'http://www.bioone.org/perlserv/?request=get-abstract&doi=10.1645%2FGE-3254';
$url = 'http://linkinghub.elsevier.com/retrieve/pii/S0044523104701059';
$url = 'http://www.blackwell-synergy.com/doi/abs/10.1111/j.1550-7408.2006.00115.x ';

*/

/*
$url ='http://www.sciencedirect.com/science?_ob=GatewayURL&_origin=IRSSSEARCH&_method=citationSearch&_piikey=S072320200800043X&_version=1&md5=952f39fe9bd26f49f29dd4282cc2f224';
$item = url2meta($url);
$item->source = $url;

print_r($item);

*/

?>
