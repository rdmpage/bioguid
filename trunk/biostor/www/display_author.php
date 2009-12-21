<?php

/**
 * @file display_author.php
 *
 * Display information about a person
 */

require_once (dirname(__FILE__) . '/display_object.php');

//--------------------------------------------------------------------------------------------------
class DisplayAuthor extends DisplayObject
{
	public $name = '';
	
	//----------------------------------------------------------------------------------------------
	function GetId()
	{
		if (isset($_GET['id']))
		{
			$this->id = $_GET['id'];
		}
		if (isset($_GET['name']))
		{
			$this->name = $_GET['name'];
		}
	}	

	// Extra <HEAD> items
	function DisplayHtmlHead()
	{
		echo html_include_script('js/coauthors.js');
	}

	//----------------------------------------------------------------------------------------------
	function DisplayHtmlContent()
	{
		global $config;
		
		echo html_page_header(true, '', 'author');
		
		echo '<h1>' . $this->GetTitle() . '</h1>';
		
		// Name variations
		$names = db_get_all_author_names($this->id);
		if (count($names) > 1)
		{
			echo '<h2>Name variants</h2>'; 
			echo '<ul>';
			foreach ($names as $name)
			{
				echo '<li><a href="' . $config['web_root'] . 'author/' . $name['author_id'] . '">' . $name['name'] . '</a></li>';
			}
			echo '</ul>';
		}

		// List of papers authored
		echo '<h2>Publications</h2>';
		$refs = db_retrieve_authored_references($this->id);
		echo '<ul>';
		foreach($refs as $reference_id)
		{
			$reference = db_retrieve_reference ($reference_id);
			echo '<li><a href="' . $config['web_root'] . 'reference/' . $reference_id . '">' . $reference->title . '</a></li>';
		}
		echo '</ul>';

		// Timeline 
		echo '<h2>Publication Timeline</h2>';
		echo '<div>';
		$timeline = db_retrieve_author_timeline($this->id);
		
		$max_count = 0;
		foreach ($timeline as $k => $v)
		{
			$max_count = max ($max_count, $v);
		}
		
		// CSS display from http://www.alistapart.com/articles/accessibledatavisualization/
		echo '<ul class="timeline">';
		foreach ($timeline as $k => $v)
		{
			echo '<li>';
			echo '<a>';
			echo '<span class="label">' . $k . '</span>';
			
			$percentage = round(100 * $v/$max_count, 2);
			
			echo '<span class="count" style="height: ' . $percentage . '%">(' . $v . ')</span>';
			echo '</a>';
			echo '</li>';
		}
		echo '</ul>';
		echo '</div>';
		//print_r($timeline);
		
		// Coauthorships (LinkedIn-style)
		$coauthors = db_retrieve_coauthors($this->id);
		//print_r($coauthors);
		echo '<h2>Coauthors</h2>';
		echo '<div>';
		echo '<div id="contact_index" style="float:right;padding:6px;text-align:center;"></div>';
		echo '<div id="contact_list" style="overflow:auto;height:400px;border:1px solid rgb(190,190,190);"></div>';
		echo '</div>';
		
		echo '<script type="text/javascript">' . "\n" . 'display_coauthors(\'' .  json_encode($coauthors) . '\');</script>' . "\n";



	}
	
	//----------------------------------------------------------------------------------------------
	// hCard 
	function DisplayMicroformat()
	{
		echo '<div style="visibility:hidden;height:0px;">';	
		echo '<div class="vcard">';
		echo '<span class="fn n">';
		echo '<span class="given-name">' . $this->object->forename . '</span>';
		echo '&nbsp;';
		echo '<span class="family-name">' . $this->object->lastname . '</span>';
		echo '</span>';
		echo '</div>';
		echo '</div>';
	}

	//----------------------------------------------------------------------------------------------
	function GetTitle()
	{
		return $this->object->forename . ' ' . $this->object->lastname;
	}

	//----------------------------------------------------------------------------------------------
	function Retrieve()
	{
		$this->object = db_retrieve_author ($this->id);
		return $this->object;
	} 

}

$d = new DisplayAuthor();
$d->Display();


?>