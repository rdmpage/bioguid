<?php

require_once(dirname(__FILE__) . '/config.inc.php');
require_once($config['adodb_dir']);
require_once (dirname(__FILE__) . '/lib.php');

//--------------------------------------------------------------------------------------------------
// from http://www.ajaxray.com/blog/2008/02/06/php-uuid-generator-function/
/**
  * Generates an UUID
  * 
  * @author     Anis uddin Ahmad <admin@ajaxray.com>
  * @param      string  an optional prefix
  * @return     string  the formatted uuid
  */
function uuid($prefix = '')
{
	$chars = md5(uniqid(mt_rand(), true));
	$uuid  = substr($chars,0,8) . '-';
	$uuid .= substr($chars,8,4) . '-';
	$uuid .= substr($chars,12,4) . '-';
	$uuid .= substr($chars,16,4) . '-';
	$uuid .= substr($chars,20,12);
	
	return $prefix . $uuid;
}

$db = NewADOConnection('mysql');
$db->Connect("localhost", 
	$config['db_user'] , $config['db_passwd'] , $config['db_name']);

// Ensure fields are (only) indexed by column name
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;


//--------------------------------------------------------------------------------------------------
class FeedMaker
{
	var $id;
	var $url;
	var $title;
	var $harvest_interval;
	var $items;
	
	//----------------------------------------------------------------------------------------------
	function __construct($url, $title, $harvest_interval = 1)
	{
		$this->url 				= $url;
		$this->title 			= $title;
		$this->harvest_interval = $harvest_interval;
		$this->StoreFeedSource();
	}
	
	//----------------------------------------------------------------------------------------------
	// For moany feeds the URL will be unique, but for others (especially services) this may not be
	// the case, so make this a method we can override.
	function FeedId ()
	{
		$this->id = md5($this->url);
	}
	
	//----------------------------------------------------------------------------------------------
	function StoreFeedSource()
	{
		global $db;
		
		$this->FeedId();
		
		$sql = 'SELECT * FROM `feed_source` WHERE (id = ' . $db->qstr($this->id) . ') LIMIT  1';
		
		$result = $db->Execute($sql);
		if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
	
		if ($result->NumRows() == 0)
		{
			$sql = 'INSERT INTO `feed_source`(`id`, `url`, `last_accessed`, `harvest_interval`) VALUES ('
			 . $db->qstr($this->id)
			 . ', ' . $db->qstr($this->url)
			 . ', ' . $db->qstr('2000-01-01 00:00:00')
			 . ', ' . $this->harvest_interval
			 . ')';
			 
			 
			 
			$result = $db->Execute($sql);
			if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
			
		}
	}	
	
	//----------------------------------------------------------------------------------------------
	function StoreFeedHarvestTime()
	{
		global $db;
			
		$sql = 'SELECT * FROM `feed_source` WHERE (id = ' . $db->qstr($this->id) . ') LIMIT  1';
		
		$result = $db->Execute($sql);
		if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
	
		if ($result->NumRows() == 1)
		{
			$sql = 'UPDATE feed_source SET last_accessed=NOW()';
			$result = $db->Execute($sql);
			if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
			
		}
	}		
	
	//----------------------------------------------------------------------------------------------
	// Has feed expired (i.e., is the last time we harvested the feed older than the harvest interval?
	function SourceExpired($id)
	{
		global $db;
		
		$expired = 0;
		
		$sql = 'SELECT * FROM `feed_source` WHERE (id = ' . $db->qstr($this->id) . ') LIMIT  1';
				
		$result = $db->Execute($sql);
		if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
	
		if ($result->NumRows() == 1)
		{
			$sql = 'SELECT (DATE_ADD(' . $db->qstr($result->fields['last_accessed']) 
				. ', INTERVAL ' . $db->qstr($result->fields['harvest_interval']) . ' DAY)) < NOW() AS expired';
				
			$result = $db->Execute($sql);
			if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
			
			$expired = $result->fields['expired'];
		}
		
		return $expired;
	}	

	//----------------------------------------------------------------------------------------------
	function RetrieveFeedItems ($num_items = 100)
	{
		global $db;
		
		$this->items = array();
	
		$sql = 'SELECT * FROM feed_item 
		WHERE (feed_id = ' . $db->qstr($this->id) . ')
		ORDER BY updated DESC
		LIMIT ' . $num_items;
		
		//echo $sql;
		
		$result = $db->Execute($sql);
		if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
	
		while (!$result->EOF) 
		{
			$item = new stdclass;
			
			$item->link = $result->fields['link'];
			$item->title = $result->fields['title'];
			$item->description = $result->fields['description'];

			// dates
			$item->updated = $result->fields['updated'];
			if ($result->fields['created'] != '')
			{
				$item->created = $result->fields['created'];
			}
			
			if ($result->fields['latitude'] != '')
			{
				$item->latitude = $result->fields['latitude'];
			}
			if ($result->fields['longitude'] != '')
			{
				$item->longitude = $result->fields['longitude'];
			}
			if ($result->fields['links'] != '')
			{
				$item->links = json_decode($result->fields['links']);
			}
			else
			{
				$item->links = array();
			}
			
			array_push($this->items, $item);
			$result->MoveNext();				
		}
	}	

	//----------------------------------------------------------------------------------------------
	function StoreFeedItem ($item)
	{
		global $db;
	
		// Don't overwite
		
		$sql = 'SELECT * FROM `feed_item` WHERE (id = ' . $db->qstr($item->id) . ') LIMIT  1';
		
		$result = $db->Execute($sql);
		if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
	
		if ($result->NumRows() == 0)
		{
			// we don't have this one
			$sql = 'INSERT INTO feed_item(';
			$columns = '';
			$values = ') VALUES (';
			
			$columns .= 'id';
			$values .= $db->qstr($item->id);
	
			$columns .= ',feed_id';
			$values .= ',' . $db->qstr($this->id);
			
			$columns .= ',title';
			$values .= ',' . $db->qstr($item->title);
	
			$columns .= ',link';
			$values .= ',' . $db->qstr($item->link);
	
			$columns .= ',description';
			$values .= ',' . $db->qstr($item->description);
			
			// dates
			if (isset($item->updated))
			{
				$columns .= ',updated';
				$values .= ',' . $db->qstr($item->updated);
			}
			else
			{
				$columns .= ',updated';
				$values .= ', NOW()';
			}

			if (isset($item->created))
			{
				$columns .= ',created';
				$values .= ',' . $db->qstr($item->created);
			}
			else
			{
				$columns .= ',created';
				$values .= ', NOW()';
			}
			
	
			if (isset($item->latitude))
			{
				$columns .= ',latitude';
				$values .= ',' . $item->latitude;
			}
			if (isset($item->longitude))
			{
				$columns .= ',longitude';
				$values .= ',' . $item->longitude;
			}
			
			if (isset($item->links))
			{
				$j = json_encode($item->links);
				$columns .= ',links';
				$values .= ',' . $db->qstr($j);
			}
				
			
			
			$sql .= $columns . $values . ');';
			
			//echo $sql;
			
			// Store
			$result = $db->Execute($sql);
			if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
		}
	}
		
	//----------------------------------------------------------------------------------------------
	function GetRss()
	{
		if ($this->SourceExpired($this->id))
		{
			$this->Harvest();
			$this->StoreFeedHarvestTime();
		}
		// Get cached content
		$this->RetrieveFeedItems(10);
		
		$rss = $this->ItemsToFeed();
		return $rss;
	}
	
	//----------------------------------------------------------------------------------------------
	function ItemsToFeed()
	{
		// header
		$feed = new DomDocument('1.0');
		$rss = $feed->createElement('feed');
		$rss->setAttribute('xmlns', 'http://www.w3.org/2005/Atom');
		$rss->setAttribute('xmlns:geo', 'http://www.w3.org/2003/01/geo/wgs84_pos#');
		$rss->setAttribute('xmlns:georss', 'http://www.georss.org/georss');
		$rss = $feed->appendChild($rss);
		
		// feed
		
		// title
		$title = $feed->createElement('title');
		$title = $rss->appendChild($title);
		$value = $feed->createTextNode($this->title);
		$value = $title->appendChild($value);
		
		// link
		$link = $feed->createElement('link');
		$link->setAttribute('href', $this->url);
		$link = $rss->appendChild($link);
		
		// updated
		$updated = $feed->createElement('updated');
		$updated = $rss->appendChild($updated);
		$value = $feed->createTextNode(date(DATE_ATOM));
		$value = $updated->appendChild($value);
		
		// id
		$id = $feed->createElement('id');
		$id = $rss->appendChild($id);
		$value = $feed->createTextNode('urn:uuid:' . uuid());
		$value = $id->appendChild($value);
		
		// author
		$author = $feed->createElement('author');
		$author = $rss->appendChild($author);
		
		$name = $feed->createElement('name');
		$name = $author->appendChild($name);
		
		$value = $feed->createTextNode('Rod Page');
		$value = $name->appendChild($value);
		
		foreach ($this->items as $item)
		{
			$entry = $feed->createElement('entry');
			$entry = $rss->appendChild($entry);
			
			// title
			$title = $entry->appendChild($feed->createElement('title'));
			$title->appendChild($feed->createTextNode($item->title));
		
			// link
			$link = $entry->appendChild($feed->createElement('link'));
			$link->setAttribute('rel', 'alternate');
			$link->setAttribute('type', 'text/html');
			$link->setAttribute('href', $item->link);
		
			// dates
			$updated = $entry->appendChild($feed->createElement('updated'));
			$updated->appendChild($feed->createTextNode(date(DATE_ATOM, strtotime($item->updated))));
			
			if (isset($item->created))
			{
				$created = $entry->appendChild($feed->createElement('published'));
				$created->appendChild($feed->createTextNode(date(DATE_ATOM, strtotime($item->created))));
			}		
			
			
			// id
			$id = $entry->appendChild($feed->createElement('id'));
			$id->appendChild($feed->createTextNode('urn:uuid:' . uuid()));
		
			// content
			$content = $entry->appendChild($feed->createElement('content'));
			$content->setAttribute('type', 'html');
			$content->appendChild($feed->createTextNode($item->description));
		
			// summary
			$summary = $entry->appendChild($feed->createElement('summary'));
			$summary->setAttribute('type', 'html');
			$summary->appendChild($feed->createTextNode($item->description));
		
			// georss
			if (isset($item->latitude))
			{
				$geo = $entry->appendChild($feed->createElement('georss:point'));
				$geo->appendChild($feed->createTextNode($item->latitude . ' ' . $item->longitude));
		
				$geo = $entry->appendChild($feed->createElement('geo:lat'));
				$geo->appendChild($feed->createTextNode($item->latitude));
		
				$geo = $entry->appendChild($feed->createElement('geo:long'));
				$geo->appendChild($feed->createTextNode($item->longitude));
			}
			
						
			// links
			foreach ($item->links as $link)
			{
				//print_r($link);
			
				foreach ($link as $k => $v)
				{
					switch ($k)
					{
						case 'taxon':
							$link = $entry->appendChild($feed->createElement('link'));
							$link->setAttribute('rel', 'related');
							$link->setAttribute('type', 'text/html');
							$link->setAttribute('href', 'http://www.ncbi.nlm.nih.gov/Taxonomy/Browser/wwwtax.cgi?id=' . $v);
							$link->setAttribute('title', 'taxon:' . $v);
							break;
		
						case 'doi':
							$link = $entry->appendChild($feed->createElement('link'));
							$link->setAttribute('rel', 'related');
							$link->setAttribute('type', 'text/html');
							$link->setAttribute('href', 'http://dx.doi.org/' . $v);
							$link->setAttribute('title', 'doi:' . $v);
							break;

						case 'hdl':
							$link = $entry->appendChild($feed->createElement('link'));
							$link->setAttribute('rel', 'related');
							$link->setAttribute('type', 'text/html');
							$link->setAttribute('href', 'http://hdl.handle.net/' . $v);
							$link->setAttribute('title', 'hdl:' . $v);
							break;
		
						case 'pmid':
							$link = $entry->appendChild($feed->createElement('link'));
							$link->setAttribute('rel', 'related');
							$link->setAttribute('type', 'text/html');
							$link->setAttribute('href', 'http://www.ncbi.nlm.nih.gov/pubmed/' . $v);
							$link->setAttribute('title', 'pmid:' . $v);
							break;

						case 'lsid':
							$link = $entry->appendChild($feed->createElement('link'));
							$link->setAttribute('rel', 'related');
							$link->setAttribute('type', 'text/html');
							$link->setAttribute('href', 'http://bioguid.info/' . $v);
							$link->setAttribute('title', $v);
							break;

						case 'url':
							$link = $entry->appendChild($feed->createElement('link'));
							$link->setAttribute('rel', 'related');
							$link->setAttribute('type', 'text/html');
							$link->setAttribute('href', $v);
							$link->setAttribute('title', $v);
							break;

						case 'pdf':
							$link = $entry->appendChild($feed->createElement('link'));
							$link->setAttribute('rel', 'related');
							$link->setAttribute('type', 'application/pdf');
							$link->setAttribute('href', $v);
							$link->setAttribute('title', 'PDF');
							break;
							
						default:
							break;
					}
				}
			}
		}

		return $feed->saveXML();
	
	
	}
	
	//----------------------------------------------------------------------------------------------
	function Harvest()
	{
	}
	
	
}



/*$url = 'http://eutils.ncbi.nlm.nih.gov/entrez/eutils/esearch.fcgi?db=nucleotide&term=barcode[keyword]';
$f = new FeedMaker($url, 'Barcode Sequences');
echo $f->GetRss();*/
	
//		$this->source_html = get($this->source_url);
//		$this->ExtractItems();
		


?>