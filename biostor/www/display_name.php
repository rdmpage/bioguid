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
require_once ('../taxon.php');
require_once (dirname(__FILE__) . '/sparklines.php');

//--------------------------------------------------------------------------------------------------
class DisplayName extends DisplayObject
{
	public $namestring = '';
	public $namebankid = 0;
//	public $identifiers = array();
	
	//----------------------------------------------------------------------------------------------
	function __construct()
	{
		$this->object = new NameString;
		
		$this->GetId();
		$this->GetFormat();
	}
	

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
/*		if (isset($_GET['lsid']))
		{
			$this->identifiers[] = $_GET['lsid'];
		}*/
	}	

	//----------------------------------------------------------------------------------------------
	function DisplayHtmlContent()
	{
		global $config;
		
		echo html_page_header(true, '', 'name');
		
		echo '<h1>' . $this->GetTitle() . '</h1>';
		
		echo '<h2>Identifiers</h2>';
		echo '<ul>';		
		if ($this->object->NameBankID != 0)
		{
			echo '<li>' . 'urn:lsid:ubio.org:namebank:' .  $this->object->NameBankID .'</li>';
		}
		echo '</ul>';
		
		$col = col_accepted_name_for($this->GetTitle());
		if (isset($col->name))
		{
			echo '<h2>Catalogue of Life accepted name</h2>';
			echo '<p>';
			echo '<span><a href="' . $config['web_root'] . 'name/' . $col->name . '">' . $col->name . '</a>' . ' ' . $col->author . '</span>';
			echo '</p>';
		}
		
		echo '<h2>BHL</h2>' . "\n";
		
		// What pages have this name? (BHL timeline)

		echo '<h3>Distribution of name in BHL</h3>';
		$hits = bhl_name_search($this->object->NameBankID);
		if (count($hits) > 0)
		{
			echo '<div>' . "\n";
			echo '   <img src="' . sparkline_bhl_name($hits, 360,100) . '" alt="sparkline" />' . "\n";
			echo '</div>' . "\n";
		}
		else
		{
			echo '<p>Name not found in BHL</p>';
		}
		
		// What articles have this name?
		echo '<h3>References in BHL</h3>';
		$refs = bhl_references_with_name($this->object->NameBankID);
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
			echo bhl_pages_with_name_thumbnails($reference_id,$this->object->NameBankID);	
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
				echo '. ';
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
		return $this->object->namestring;
	}

	//----------------------------------------------------------------------------------------------
	function Retrieve()
	{
		// Called with just an integer, assume it is NameBankID
		if ($this->namebankid != 0)
		{
			$this->object = bhl_retrieve_name_from_namebankid ($this->namebankid);
		}

		// Called with a string
		if ($this->namestring != '')
		{
			$this->object = bhl_retrieve_name_from_namestring ($this->namestring);
			
			if ($this->object == NULL)
			{
				// Do name lookup
				$this->object = db_get_namestring($this->namestring);
				if ($this->object == NULL)
				{
					// Try uBio
					$this->object = ubio_lookup($this->namestring);
				}					
			}	
		}
		
		// CoL?
		
/*		
		if ($this->object != NULL)
		{
			$this->identifiers[] = $this->object->Identifier;
			$this->namebankid = $this->object->NameBankID;
		}
*/		
		return $this->object;
	} 

}

$d = new DisplayName();
$d->Display();


?>