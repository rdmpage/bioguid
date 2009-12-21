<?php

/**
 * @file display_name.php
 *
 * Display taxonomic name string 
 *
 */

require_once (dirname(__FILE__) . '/display_object.php');
require_once ('../bhl_names.php');
require_once ('../col.php');
require_once ('../reference.php');

//--------------------------------------------------------------------------------------------------
class DisplayName extends DisplayObject
{
	public $namestring = '';
	public $namebankid = 0;
	public $identifiers = array();

	//----------------------------------------------------------------------------------------------
	function GetId()
	{
		if (isset($_GET['namebankid']))
		{
			$this->namebankid = $_GET['namebankid'];
		}
		if (isset($_GET['namestring']))
		{
			$this->namestring = $_GET['namestring'];
		}
		if (isset($_GET['lsid']))
		{
			$this->identifiers[] = $_GET['lsid'];
		}
	}	

	//----------------------------------------------------------------------------------------------
	function DisplayHtmlContent()
	{
		global $config;
		
		echo html_page_header(true, '', 'name');
		
		echo '<h1>' . $this->GetTitle() . '</h1>';
		
		echo '<h2>Identifiers</h2>';
		echo '<ul>';
		foreach ($this->identifiers as $identifier)
		{
			if (preg_match('/urn:lsid:ubio.org:namebank:/', $identifier))
			{
				echo '<li>' . '<a href="' . $config['web_root'] . $identifier . '">' . $identifier . '</a></li>';
			}
			else
			{
				echo '<li>' . $identifier . '</li>';			
			}
		}
		echo '</ul>';
		
		$col = col_accepted_name_for($this->GetTitle());
		if (isset($col->name))
		{
			echo '<h2>Catalogue of Life accepted name</h2>';
			echo '<span><a href="' . $config['web_root'] . 'name/' . $col->name . '">' . $col->name . '</a>' . ' ' . $col->author . '</span>';
		}
		
		// What pages have this name? (BHL timeline)
		
		// What articles have this name?
		echo '<h2>BHL Bibliography</h2>';
		$refs = bhl_references_with_name($this->namebankid);
		echo '<ol>';
		foreach($refs as $reference_id)
		{
			$reference = db_retrieve_reference ($reference_id);
			echo '<li style="border-bottom:1px dotted rgb(128,128,128);padding:4px;">';
			echo '<a href="' . $config['web_root'] . 'reference/' . $reference_id . '">' . $reference->title . '</a><br/>';
			echo '<span style="color:green;">' . reference_authors_to_text_string($reference);
			if (isset($reference->year))
			{
				echo ' (' . $reference->year . ')';
			}
			echo ' ' . reference_to_citation_text_string($reference) . '</span>';
			echo ' ' . reference_to_coins($reference);
			echo '<div>';
			echo bhl_pages_with_name_thumbnails($reference_id, $this->namebankid);	
			echo '</div>';
			echo '</li>';
		}
		echo '</ol>';
		
		$refs = col_references_for_name($this->GetTitle());
		if (count($refs) != 0)
		{
			echo '<h2>Catalogue of Life Bibliography</h2>';
			echo '<ol>';
			foreach($refs as $ref)
			{
				echo '<li style="border-bottom:1px dotted rgb(128,128,128);padding:4px;">';
				echo '<span>';
				echo '[' . $ref->record_id . '] ';
				if (isset($ref->reference_type))
				{
					echo '[' . $ref->reference_type . '] ';
				}
				echo $ref->author;
				echo ' ';
				echo $ref->year;
				echo ' ';
				echo $ref->title;
				echo ' ';
				echo $ref->source;
				echo '</span>';
				echo '</li>';
			}
			echo '</ol>';
		}
	}

	//----------------------------------------------------------------------------------------------
	function GetTitle()
	{
		return $this->object->NameString;
	}

	//----------------------------------------------------------------------------------------------
	function Retrieve()
	{
		if ($this->namebankid != 0)
		{
			$this->object = bhl_retrieve_name_from_namebankid ($this->namebankid);
		}
		if ($this->namestring != '')
		{
			$this->object = bhl_retrieve_name_from_namestring ($this->namestring);
		}
		
		// CoL?
		
		
		if ($this->object != NULL)
		{
			$this->identifiers[] = $this->object->Identifier;
			$this->namebankid = $this->object->NameBankID;
		}
		return $this->object;
	} 

}

$d = new DisplayName();
$d->Display();


?>