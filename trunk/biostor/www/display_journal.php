<?php

/**
 * @file display_journal.php
 *
 * Display journal 
 *
 */

// journal info
require_once (dirname(__FILE__) . '/display_object.php');
require_once ('../reference.php');

//--------------------------------------------------------------------------------------------------
class DisplayJournal extends DisplayObject
{
	public $issn = '';
	
	function __construct()
	{
		$this->GetId();
		$this->GetFormat();
	}
	
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