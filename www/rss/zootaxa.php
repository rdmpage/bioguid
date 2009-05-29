<?php

require_once (dirname(__FILE__) . '/feed_maker.php');
require_once (dirname(__FILE__) . '/nameparse.php');
require_once (dirname(__FILE__) . '/ubio_findit.php');

$debug = 0;


//--------------------------------------------------------------------------------------------------
class ZootaxaFeed extends FeedMaker
{
	//----------------------------------------------------------------------------------------------
	function Harvest()
	{
	
		global $debug;
		
		//echo "|" . $this->url . "|";
		$html = get($this->url);
		
		$html = utf8_encode($html);
		
//		echo $html;

		
		$html = str_replace("\n", "", $html);
		$html = str_replace("\r", "", $html);
		$html = str_replace("<p align=\"left\">", "\n<p align=\"left\">", $html);
		  
		if (preg_match_all('/
		<p\s+align="left">(.*)<\/p>
		/x',  $html, $matches, PREG_PATTERN_ORDER))
		{
			if ($debug)
			{
				print_r($matches);
			}
			
			foreach ($matches[1] as $paragraph)
			{
				$m = array();
				
				$item = new stdclass;
				$item->authors = array();
				$item->title = 'Zootaxa';
				$item->issn = '1175-5326';
			
				// <b>2095</b>: 37-46 (<i>
				if (preg_match('/<b>(?<volume>[0-9]+)<\/b>:\s*(?<spage>[0-9]+)\-(?<epage>[0-9]+)/', $paragraph, $m))
				{
					//print_r($m);
					
					$item->volume = $m['volume'];
					$item->spage = $m['spage'];
					$item->epage = $m['epage'];
				}
		
		
				// authors
				if (preg_match('/<br>\s*(?<authors>[A-Z]+(.*))<\/font><br>/', $paragraph, $m))
				{
					//print_r($m);
					
					$item->authorString = $m['authors'];
					
					// clean			
					$a = trim($item->authorString);
					
					// remove countries
					$a = preg_replace('/\([A-Za-z \.]+\)/', '', $a);
					$a = preg_replace('/\(Nouvelle\-Caledonie\)/', '', $a);
					
					// protect suffix
					$a = preg_replace('/, J[R|r]/', ' Jr', $a);
					
					// remove punctuation
					$a = str_replace (",", "|", $a);
					$a = str_replace ("&amp;", "|", $a);
					
					//echo "a=$a\n";
					
					$authors = explode("|", $a);
					//print_r($authors);
					
					foreach ($authors as $value)
					{
						//array_push($item->authors, trim($auth));
						
						$value = trim($value);
						
						// Make nice
						$value = mb_convert_case($value, 
							MB_CASE_TITLE, mb_detect_encoding($value));
									
						// Get parts of name
						$parts = parse_name($value);
						
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
		
				// abstract
				if (preg_match('/<a href="(?<url>(.*))">Abstract/', $paragraph, $m))
				{
					//print_r($m);
					
					$item->url = 'http://www.mapress.com/zootaxa/' . $m['url'];
					
				}
		
				// pdf
				if (preg_match('/<\/font><a href="(?<url>(.*))">Full/', $paragraph, $m))
				{
					//print_r($m);
					
					$item->pdf = 'http://www.mapress.com/zootaxa/' . $m['url'];
					
				}
				
				// access
				if (preg_match('/subscription\s+required/', $paragraph, $m))
				{
					//print_r($m);
				}
				else
				{
					$item->availability = 'open access';
				}
				
				
				
				// date
				if (preg_match('/<i>(?<date>[0-9]+\s+[A-Z][a-z]+\s+[0-9]{4})<\/i>/', $paragraph, $m))
				{
					//print_r($m);
					
					$item->date =  date("Y-m-d", strtotime($m['date']));
					$item->year =  date("Y", strtotime($m['date']));
				}
				
				// (11 <i>May 2009</i>)
				if (preg_match('/(?<date>[0-9]+\s+<i>[A-Z][a-z]+\s+[0-9]{4})<\/i>/', $paragraph, $m))
				{
					$date = strip_tags($m['date']);
					
					$item->date =  date("Y-m-d", strtotime($date));
					$item->year =  date("Y", strtotime($date));
				}
				
				
				// title
				if (preg_match('/<font FACE="Times New Roman">(?<title>.*)<\/b><br>/', $paragraph, $m))
				{
					//print_r($m);
					
					$atitle = $m['title'];
					
					// Some Zootaxa HTML replies on implict space between >< for spacing,
					// which results in word being run together when tags are stripped.
					
					$atitle = str_replace('><', '> <', $atitle);
					
					$atitle = strip_tags($atitle);
					$atitle = preg_replace('/\s\s*/', ' ', $atitle);
					$item->atitle = $atitle;
					
				}
				
				//print_r($item);
		
				// Store
				if (isset($item->atitle))
				{			
					// ubio tags to extract taxonomic names and LSIDs
					$names = ubio_findit($item->atitle);
				
					$item->tags = array();
					$item->tagids = array();
					foreach ($names as $n)
					{
						foreach ($n as $k => $v)
						{
							switch ($k)
							{
								case 'canonical':
									array_push($item->tags, $v);
									break;
								case 'namebankID':
									array_push($item->tagids, 'urn:lsid:ubio.org:namebank:' . $v);
									break;
								default:
									break;
							}
						}
					}
					
				
					// Store feed item
					$feed_item = new stdclass;
					$feed_item->title = $item->atitle;
					$feed_item->link = $item->url;
					
					$description = '';
					$count = 0;
					$num_authors = count($item->authors);
					if ($num_authors > 0)
					{
						foreach ($item->authors as $author)
						{
							$description .= $author->forename . ' ' . $author->lastname;
							if (isset($author->suffix))
							{
								$description .= ' ' . $author->suffix;
							}
							$count++;
							if ($count < $num_authors-1)
							{
								$description .= ', ';
							}
							else if ($count < $num_authors)
							{
								$description .= ' and ';
							}
							
						}
					}
					$description .= '<br/>';
					$description .= '<i>Zootaxa</i>' . ' <b>' . $item->volume . '</b> ' . $item->spage . '-' . $item->epage . ' [' . $item->date . ']' . '<br/>';
					
					// tags
					foreach ( $item->tags as $tag )
					{
					    $description .= '<b>' . $tag . '</b><br/>';
					}
					
					
					
					$feed_item->description = $description;
					$feed_item->id = $item->url;
					$feed_item->created = $item->date;
					$feed_item->payload = $item;
					
					
					
					
					
					
					
					$this->StoreFeedItem($feed_item);
				}	
				
				
				// to RDF 1.
				
			}
			
		}
		

		
	
	}

}


$url = 'http://www.mapress.com/zootaxa/content.html';
$f = new ZootaxaFeed($url, 'Zootaxa Current Issues',1, RSS1);
$f->WriteFeed();

?>