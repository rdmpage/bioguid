<?php

/**
 * @file reference.php
 *
 * Operations on a reference object
 *
 */
 
require_once(dirname(__FILE__) . '/config.inc.php'); 
require_once(dirname(__FILE__) . '/nameparse.php'); 
 
//--------------------------------------------------------------------------------------------------
/**
 * @brief Add an author to a reference from a string containing the author's name
 *
 * @param reference Reference object
 * @param author Author name as a string
 *
 */
function reference_add_author_from_string(&$reference, $author)
{
	if (!isset($reference->authors))
	{
		$reference->authors = array();
	}
	
	$parts = parse_name($author);
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
	$reference->authors[] = $author;	

}
 
//--------------------------------------------------------------------------------------------------
/**
 * @brief Create a simple text string citation for a reference
 *
 * For an article this is of the form journal volume(issue): spage-epage
 *
 * @param reference Reference object
 *
 * @return Citation string
 */
function reference_to_citation_text_string($reference)
{
	$text = '';
	switch ($reference->genre)
	{
		case 'article':
			$text .= $reference->secondary_title;
			$text .= ' ';
			$text .= $reference->volume ;
			if (isset($reference->issue))
			{
				$text .= '(' . $reference->issue . ')';
			}		
			$text .= ':';
			$text .= ' ';
			$text .= $reference->spage;
			if (isset($reference->epage))
			{
				$text .= '-' . $reference->epage;
			}
			/*if (isset($reference->year))
			{
				$text .= ' [' . $reference->year . ']';
			}*/
			break;
			
		default:
			break;
	}
	
	return $text;
}

//--------------------------------------------------------------------------------------------------
/**
 * @brief Create a simple text string citation for a authors of a reference
 *
 * @param reference Reference object
 *
 * @return Author string 
 */
function reference_authors_to_text_string($reference)
{
	$text = '';
	
	$count = 0;
	$num_authors = count($reference->authors);
	if ($num_authors > 0)
	{
		foreach ($reference->authors as $author)
		{
			$text .= $author->forename . ' ' . $author->lastname;
			if (isset($author->suffix))
			{
				$text .= ' ' . $author->suffix;
			}
			$count++;
			
			if ($count == 2 && $num_authors > 3)
			{
				$text .= ' et al.';
				break;
			}
			if ($count < $num_authors -1)
			{
				$text .= ', ';
			}
			else if ($count < $num_authors)
			{
				$text .= ' and ';
			}			
		}
	}
	
	return $text;
}

//--------------------------------------------------------------------------------------------------
/**
 * @brief Create a COinS (ContextObjects in Spans) for a reference
 *
 * COinS encodes an OpenURL in a <span> tag. See http://ocoins.info/.
 *
 * @param reference Reference object to be encoded
 *
 * @return HTML <span> tag containing a COinS
 */
function reference_to_coins($reference)
{
	global $config;
	
	$coins = '';
	
	switch ($reference->genre)
	{
		case 'article':
			$coins .= '<span class="Z3988"';
			$coins .= ' title="ctx_ver=Z39.88-2004&amp;rft_val_fmt=info:ofi/fmt:kev:mtx:journal';
			if (count($reference->authors) > 0)
			{
				$coins .= '&amp;rft.aulast=' . urlencode($reference->authors[0]->lastname);
				$coins .= '&amp;rft.aufirst=' . urlencode($reference->authors[0]->forename);
			}
			foreach ($reference->authors as $author)
			{
				$coins .= '&amp;rft.au=' . urlencode($author->forename . ' ' . $author->lastname);
			}
			$coins .= '&amp;rft.atitle=' . urlencode($reference->title);
			$coins .= '&amp;rft.jtitle=' . urlencode($reference->secondary_title);
			if (isset($reference->series))
			{
				$coins .= '&amp;rft.series/' . urlencode($reference->series);
			}
			$coins .= '&amp;rft.issn=' . $reference->issn;
			$coins .= '&amp;rft.volume=' . $reference->volume;
			$coins .= '&amp;rft.spage=' . $reference->spage;
			$coins .= '&amp;rft.epage=' . $reference->epage;
			$coins .= '&amp;rft.date=' . $reference->year;
			
			if (isset($reference->sici))
			{
				$coins .= '&amp;rft.sici=' . urlencode($reference->sici);
			}
			
	//		$coins .= '&amp;rft.id=' . $config['web_server'] . $config['web_root'] . 'reference/' . $reference->reference_id;
			
			if (isset($reference->doi))
			{
				$coins .= '&amp;rft_id=info:doi/' . urlencode($reference->doi);
			}
			else if (isset($reference->hdl))
			{
				$coins .= '&amp;rft_id=info:hdl/' . urlencode($reference->hdl);
			}
			else if (isset($reference->url))
			{
				$coins .= '&amp;rft_id='. urlencode($reference->url);
			}
			
			$coins .= '">';
			$coins .= '</span>';
			break;
			
		default:
			break;
	}
	
	return $coins;
}

function reference_to_bibtex($reference)
{
	global $config;
	
	$bibtex = '';
	
	switch ($reference->genre)
	{
		case 'article':
			$bibtex .= "@article{";
			
			// Citekey
			$citekey = '';
			if (isset($reference->authors[0]->lastname))
			{
				$citekey = $reference->authors[0]->lastname;
				if (isset($reference->year))
				{
					$citekey .= $reference->year;
				}
				else
				{
					$citekey = ':' . $reference->reference_id;
				}
			}
			else
			{
				$citekey = 'biostor:' . $reference->reference_id;
			}
			$bibtex .= $citekey . ",\n";	
			
			
			$num_authors = count($reference->authors);
			if (count($num_authors) > 0)
			{
				$bibtex .= "   author = {" . latex_safe($reference->authors[0]->forename) . ' ' . latex_safe($reference->authors[0]->lastname);
				
				for ($i = 1; $i < $num_authors; $i++)
				{
					$bibtex .= " and " . latex_safe($reference->authors[$i]->forename) . ' ' . latex_safe($reference->authors[$i]->lastname);
				}
				$bibtex .= "},\n";
			}
			$bibtex .= "   title = {" . latex_safe($reference->title) . "},\n";
			$bibtex .= "   journal = {" . $reference->secondary_title . "},\n";
			$bibtex .= "   volume = {" . $reference->volume . "},\n";
			if (isset($reference->issue) && ($reference->issue != ''))
			{
				$bibtex .= "   number = {" . $reference->issue . "},\n";
			}
			$bibtex .= "   pages = {" . $reference->spage; 
			if (isset($reference->epage))
			{
				$bibtex .= "--" . $reference->epage;
			}
			$bibtex .=  "},\n";
			$bibtex .= "   year = {" . $reference->year . "},\n";

			$bibtex .= "   url = {" .  $config['web_root'] . "reference/" . $reference->reference_id . "}\n";
			$bibtex .=  "}\n";
			break;
			
		default:
			break;
	}
	
	return $bibtex;
}



//--------------------------------------------------------------------------------------------------
/**
 * @brief Export reference in RIS format
 *
 * @param reference Reference object to be encoded
 *
 * @return RIS format text
 */
function reference_to_ris($reference)
{
	global $config;
	
	$ris = '';
	
	switch ($reference->genre)
	{
		case 'article':
			$ris .= "TY  - JOUR\n";
			foreach ($reference->authors as $author)
			{
				$ris .= "AU  - " . $author->lastname . ", " . $author->forename . "\n";
			}
			$ris .= "ID  - " . $reference->reference_id . "\n";
			$ris .= "TI  - " . $reference->title . "\n";
			$ris .= "JF  - " . $reference->secondary_title . "\n";
			$ris .= "VL  - " . $reference->volume . "\n";
			if (isset($reference->issue))
			{
				$ris .= "IS  - " . $reference->issue . "\n";
			}	
			$ris .= "SP  - " . $reference->spage . "\n";
			if (isset($reference->epage))
			{
				$ris .= "EP  - " . $reference->epage . "\n";
			}	
			$ris .= "Y1  - " . $reference->year . "\n";
			$ris .= "UR  - " .  $config['web_root'] . "reference/" . $reference->reference_id . "\n";
			$ris .= "ER  - \n\n";
			
			break;
			
		default:
			break;
	}
	
	return $ris;
}

//--------------------------------------------------------------------------------------------------
function reference_to_atom($reference, &$feed, &$rss)
{
	global $config;
	
	$entry = $feed->createElement('entry');
	$entry = $rss->appendChild($entry);
	
	// title
	$title = $entry->appendChild($feed->createElement('title'));
	$title->appendChild($feed->createTextNode($reference->title));

	// link
	$link = $entry->appendChild($feed->createElement('link'));
	$link->setAttribute('rel', 'alternate');
	$link->setAttribute('type', 'text/html');
	$link->setAttribute('href', $config['web_root'] . 'reference/' . $reference->reference_id);

	// dates
	$updated = $entry->appendChild($feed->createElement('updated'));
	$updated->appendChild($feed->createTextNode(date(DATE_ATOM, strtotime($reference->updated))));
	
	$created = $entry->appendChild($feed->createElement('published'));
	$created->appendChild($feed->createTextNode(date(DATE_ATOM, strtotime($reference->created))));

	// id
	$id = $entry->appendChild($feed->createElement('id'));
	$id->appendChild($feed->createTextNode('urn:uuid:' . uuid()));
	
	$description = reference_authors_to_text_string($reference) . '<br/>';
	$description .= reference_to_citation_text_string($reference);
	
	// content
	$content = $entry->appendChild($feed->createElement('content'));
	$content->setAttribute('type', 'html');
	$content->appendChild($feed->createTextNode($description));

	// summary
	$summary = $entry->appendChild($feed->createElement('summary'));
	$summary->setAttribute('type', 'html');
	$summary->appendChild($feed->createTextNode($description));

}


//--------------------------------------------------------------------------------------------------
/**
 * @brief Write reference in Endnote XML
 *
 * @param reference Reference object
 *
 * @return XML 
 */
function reference_to_endnote_xml($reference)
{
	$doc = new DomDocument('1.0', 'UTF-8');
	
	// xml
	$xml = $doc->appendChild($doc->createElement('xml'));

	// records
	$records = $xml->appendChild($doc->createElement('records'));

	// record
	$record = $records->appendChild($doc->createElement('record'));
	
	// ref-type
	$ref_type = $record->appendChild($doc->createElement('ref-type'));
	
	switch ($reference->genre)
	{
		case 'article':
			$ref_type->setAttribute('name', 'Journal Article');
			break;
	}
	// contributors
	$contributors = $record->appendChild($doc->createElement('contributors'));
	$authors = $contributors->appendChild($doc->createElement('authors'));
	foreach ($reference->authors as $a)
	{
		$author = $authors->appendChild($doc->createElement('authors'));
		$author->setAttribute('first-name', $a->forename);
		$author->setAttribute('last-name', $a->lastname);
		if (isset($a->suffix))
		{			
			$author->setAttribute('suffix', $a->suffix);
		}
		$author->appendChild($doc->createTextNode($a->forename . ' ' . $a->lastname));
	}
	
	// titles		
	$titles = $record->appendChild($doc->createElement('titles'));

	$title = $titles->appendChild($doc->createElement('title'));
	$title->appendChild($doc->createTextNode($reference->title));

	$secondary_title = $titles->appendChild($doc->createElement('secondary-title'));
	$secondary_title->appendChild($doc->createTextNode($reference->secondary_title));
	
	if ($reference->genre == 'article')
	{
		$periodical = $record->appendChild($doc->createElement('periodical'));
	
		$full_title = $periodical->appendChild($doc->createElement('full-title'));
		$full_title->appendChild($doc->createTextNode($reference->secondary_title));
	
		$volume = $record->appendChild($doc->createElement('volume'));
		$volume->appendChild($doc->createTextNode($reference->volume));
		
		if (isset($reference->issue))
		{
			$number = $record->appendChild($doc->createElement('number'));
			$number->appendChild($doc->createTextNode($reference->issue));			
		}

		$pages = $record->appendChild($doc->createElement('pages'));
		$pages->setAttribute('start', $reference->spage);
		
		$page_string = $reference->spage;
		if (isset($reference->epage))
		{
			$page_string .= '-' . $reference->epage;
			$pages->setAttribute('end', $reference->epage);
		}
		$pages->appendChild($doc->createTextNode($page_string));

	}
	// Dates
	$dates = $record->appendChild($doc->createElement('dates'));
	$year = $dates->appendChild($doc->createElement('year'));
	$year->appendChild($doc->createTextNode($reference->year));

	return $doc->saveXML();
}

?>