<?php

// Taxon-specific Zootaxa feeds. Assumes we have harvested the Zootaxa papers already
// (I guess this means I need to consume my own RSS feed), so that all we need to extract
// from HTML is the link to the abstract, and we use that as the identifier to look up
// reference in bioGUID OpenURL resolver.

require_once (dirname(__FILE__) . '/feed_maker.php');

$taxon = '';
if (isset($_GET['taxon']))
{
	$taxon = $_GET['taxon'];
}

$debug = 0;

//--------------------------------------------------------------------------------------------------
class ZootaxaByTaxonFeed extends FeedMaker
{

	function FeedId ()
	{
		$this->id = md5($this->url . $this->title);
	}

	//----------------------------------------------------------------------------------------------
	function Harvest()
	{
		global $debug;
		
		$base_url = 'http://www.mapress.com/zootaxa';
		$page = $this->url . $this->title . '.html';		
		$html = get($page);
		
		// Find links to abstracts. We use these as publication GUIDs, under assumtpion
		// that we have alreay harvested the Zootaxa papers and hence we can resolve the URLs
		if (preg_match_all('/<a href="(?<url>(.*))">Abstract &amp; excerpt<\/a>/',  $html, $matches, PREG_PATTERN_ORDER))
		{			
			foreach ($matches['url'] as $abstract_url)
			{
				$abstract_url = str_replace('..', '', $abstract_url);
				$abstract_url = $base_url . $abstract_url;
				
				// we parsed it OK, now find guid...
				$url = 'http://bioguid.info/openurl/?id=' . $abstract_url . '&display=json';
				
				$j = get($url);
				
				$item = json_decode($j);
				
				$item->tags = array();
				array_push($item->tags, $this->title);
				
				if ($item->status == 'ok')
				{
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
					$feed_item->updated = $item->date;
					$feed_item->payload = $item;
					
					//print_r($feed_item);
					
					$this->StoreFeedItem($feed_item);
				}
				
			}
		}		
	}

}

$url = 'http://www.mapress.com/zootaxa/taxa/';
$f = new ZootaxaByTaxonFeed($url, $taxon, 7, RSS1);
$f->WriteFeed();

?>