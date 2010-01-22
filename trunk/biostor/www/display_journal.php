<?php

/**
 * @file display_journal.php
 *
 * Display journal 
 *
 */

// journal info
require_once (dirname(__FILE__) . '/display_object.php');
require_once ('../bhl_journal.php');
require_once ('../reference.php');
require_once (dirname(__FILE__) . '/sparklines.php');

//--------------------------------------------------------------------------------------------------
class DisplayJournal extends DisplayObject
{
	public $issn = '';
	
	
	//----------------------------------------------------------------------------------------------
	function GetId()
	{
		if (isset($_GET['id']))
		{
			$this->id = $_GET['id'];
		}
		if (isset($_GET['issn']))
		{
			$this->issn = $_GET['issn'];
		}
	}	


	//----------------------------------------------------------------------------------------------
	function DisplayHtmlContent()
	{
		global $config;
		
		echo html_page_header(true, '', 'name');
		
		echo '<h1>' . $this->GetTitle() . '</h1>';
		
		// Image
		if (isset($this->issn))
		{
			echo '<div>';
			echo '<img src="http://bioguid.info/issn/image.php?issn=' . $this->issn . '" alt="cover" style="border:1px solid rgb(228,228,228);height:100px;" />';
			echo '</div>';
		}
		
		// Stats
/*		echo '<div>';
		echo '<img src="' . sparkline_articles_added_for_issn($this->issn) . '" alt="sparkline" />';
		echo '</div>';*/
		
		
		echo '<h2>Coverage</h2>' . "\n";

		echo '<p>' . bhl_articles_for_issn($this->issn) . ' articles identified.</p>' . "\n";

		echo '<h3>Distribution of identified articles over time</h3>' . "\n";

		echo '<div>' . "\n";
		echo '   <img src="' . sparkline_references($this->issn, 360,100) . '" alt="sparkline" />' . "\n";
		echo '</div>' . "\n";
		
		echo '<h3>Distribution of identified articles across BHL items</h3>' . "\n";
		
		echo '<div>';
		echo '<div style="display:inline;background-color:rgb(230,242,250);width:20px;height:20px;">&nbsp;&nbsp;&nbsp;&nbsp;</div>';
		echo '&nbsp;Scanned pages&nbsp;';
		echo '<div style="display:inline;background-color:rgb(0,119,204);width:10px;height:10px;">&nbsp;&nbsp;&nbsp;&nbsp;</div>';
		echo '&nbsp;Articles&nbsp;';
		echo '</div>';
		echo '<p />';
		
		$titles = bhl_titles_for_issn($this->issn);
		$institutions = institutions_from_titles($titles);

		$items = array();
		$volumes = array();		
		items_from_titles($titles, $items, $volumes);
		$html = '<div style="height:400px;border:1px solid rgb(192,192,192);overflow:auto;">' . "\n";
		$html .= '<table>' . "\n";
		$html .= '<tbody style="font-size:10px;">' . "\n";
		
		foreach ($volumes as $volume)
		{
			$item = $items[$volume];
			
			// How many pages in this item?
			$num_pages = bhl_num_pages_in_item($item->ItemID);
			
			// Coverage
			$coverage = bhl_item_page_coverage($item->ItemID);	
			
			$row_height = 10;
			
			// Draw as DIV
			
			$html .= '<tr>' . "\n";
			$html .= '<td>';
			$html .= '<a href="http://www.biodiversitylibrary.org/item/' . $item->ItemID .'" target="_new">';
			$html .= $item->VolumeInfo;
			$html .= '</a>';
			$html .= '</td>' . "\n";
			$html .= '<td>' . "\n";
			$html .= '<div style="position:relative">' . "\n";
			$html .= '   <div style="background-color:rgb(230,242,250);border-bottom:1px solid rgb(192,192,192);border-right:1px solid rgb(192,192,192);position:absolute;left:0px;top:0px;width:' . $num_pages . 'px;height:' . $row_height . 'px;">' . "\n";
			
			foreach ($coverage as $c)
			{   
				$html .= '      <div style="background-color:rgb(0,119,204);position:absolute;left:' . $c->start . 'px;top:0px;width:' . ($c->end - $c->start) . 'px;height:' . $row_height . 'px;">' . "\n";
				$html .= '      </div>' . "\n";
			}   
			
			
			$html .= '   </div>' . "\n";
			$html .= '</div>' . "\n";
			$html .= '</td>' . "\n";
			$html .= '</tr>' . "\n";
		
		}
		
		$html .= '</tbody>' . "\n";
		$html .= '</table>' . "\n";
		$html .= '</div>' . "\n";
		echo $html;
		
		echo '<h3>BHL source(s)</h3>' . "\n";
		echo '<table>' . "\n";
		foreach ($institutions as $k => $v)
		{
			echo '<tr>' . "\n";
			echo '<td>' . "\n";
			switch ($k)
			{
				case 'American Museum of Natural History Library':
					echo '<img src="' . $config['web_root'] . 'images/institutions/' . 'AMNH_logo_--_blue_rectangle.jpg' . '" width="48" />';
					break;

				case 'Harvard University, MCZ, Ernst Mayr Library':
					echo '<br /><img src="' . $config['web_root'] . 'images/institutions/' . 'Mod_Color_Harvard_Shield_small_bigger.jpg' . '" width="48" />';
					break;
					
				case 'Missouri Botanical Garden':
					echo '<br /><img src="' . $config['web_root'] . 'images/institutions/' . 'twitter_icon_MBG.jpg' . '" width="48" />';
					break;
					
				case 'New York Botanical Garden':
					echo '<br /><img src="' . $config['web_root'] . 'images/institutions/' . 'NYBGDOMEHEADERWEB.jpg' . '" />';
					break;

				case 'Smithsonian Institution Libraries':
					echo '<br /><img src="' . $config['web_root'] . 'images/institutions/' . 'SILCatesbyMagnolia.jpg' . '"  width="48" />';
					break;
				
				case 'The Field Museum':
					echo '<br /><img src="' . $config['web_root'] . 'images/institutions/' . 'field.jpg' . '" width="48" />';
					break;
				
				case 'BHL-Europe':
					echo '<br /><div style="background-color:green;width:120px;text-align:center"><img src="' . $config['web_root'] . 'images/institutions/' . 'BHL_logo_wg.png' . '" height="48" /></div>';			
					break;
					
				case 'Boston Public Library':
					echo '<br /><img src="' . $config['web_root'] . 'images/institutions/' . 'BPLcards.jpg' . '" width="48" />';			
					break;
					
				case 'Harvard University Herbarium':
					echo '<br /><img src="' . $config['web_root'] . 'images/institutions/' . 'huh_logo_bw_100.png' . '" width="48" />';
					break;
				
				case 'MBLWHOI Library':
					echo '<br /><img src="' . $config['web_root'] . 'images/institutions/' . 'library_logo2_bigger.jpg' . '" width="48" />';
					break;
					
				case 'Natural History Museum, London':
					echo '<br /><img src="' . $config['web_root'] . 'images/institutions/' . 'natural_history_museum-01.jpg' . '" width="48" />';
					break;
				
				case 'University of Illinois Urbana Champaign':
					echo '<br /><img src="' . $config['web_root'] . 'images/institutions/' . 'ilogo_horz_bold.gif' . '" height="48" />';
					break;
							
				default:
					break;
			}
			echo '</td>' . "\n";	
			echo '<td>' . "\n";	
			echo $k . '<br />' . $v . ' items';	
			echo '</td>' . "\n";	
			echo '</tr>' . "\n";	

		}
		echo '</table>' . "\n";	
		
		
		// How does journal relate to BHL Titles and Items?
		
		$titles = db_retrieve_journal_names_from_issn($this->issn);
		if (count($titles) > 1)
		{	
			echo '<h2>Alternative titles</h2>';
			echo '<ul>';
			foreach ($titles as $title)
			{
				echo '<li>' . $title . '</li>';
			}
			echo '</ul>';
		}
		
		echo '<h2>Articles</h2>';
		
		$articles = db_retrieve_articles_from_journal($this->issn);
		echo '<ul>';
		foreach ($articles as $k => $v)
		{
			echo '<li style="display:block;border-top:1px solid #EEE; ">' . $k;
			echo '<ul>';
			foreach ($v as $ref)
			{
				if (0)
				{
					// fast
					echo '<li><a href="' . $config['web_root'] . 'reference/' . $ref->id . '">' . $ref->title . '</a></li>';
				}
				else
				{
					// slower, but useful for debugging
					$reference = db_retrieve_reference ($ref->id);
					echo '<li style="border-bottom:1px dotted rgb(128,128,128);padding:4px;">';
					echo '<a href="' . $config['web_root'] . 'reference/' . $ref->id . '">' . $reference->title . '</a><br/>';
					echo '<span style="color:green;">' . reference_authors_to_text_string($reference);
					if (isset($reference->year))
					{
						echo ' (' . $reference->year . ')';
					}
					echo ' ' . reference_to_citation_text_string($reference) . '</span>';
					echo ' ' . reference_to_coins($reference);

					// Thumbail, useful for debugging
					if (0)
					{
						echo '<div>';					
						$pages = bhl_retrieve_reference_pages($ref->id);
						$image = bhl_fetch_page_image($pages[0]->PageID);
						echo '<a href="' . $config['web_root'] . 'reference/' . $ref->id . '">';
						echo '<img style="padding:2px;border:1px solid blue;margin:2px;" id="thumbnail_image_' . $page->PageID . '" src="' . $image->thumbnail->url . '" width="' . $image->thumbnail->width . '" height="' . $image->thumbnail->height . '" alt="thumbnail"/>';	
						echo '</a>';
						echo '</div>'; 
					}
					echo '</li>';
				}
			}
			echo '</ul>';
			echo '</li>';
		}
		echo '</ul>';

	}

	//----------------------------------------------------------------------------------------------
	function GetTitle()
	{
		return $this->object->title;
	}


	//----------------------------------------------------------------------------------------------
	function Retrieve()
	{
		if ($this->issn != '')
		{
			$this->object = db_retrieve_journal_from_issn ($this->issn);
		}
		
		return $this->object;
	} 

}

$d = new DisplayJournal();
$d->Display();


?>