<?php

/**
 * @file display_reference.php
 *
 * Display reference 
 *
 */

require_once (dirname(__FILE__) . '/display_object.php');
require_once (dirname(__FILE__) . '/form.php');
require_once ('../bhl_names.php');
require_once ('../bhl_text.php');
require_once ('../bhl_viewer.php');
require_once ('../cites.php');
require_once ('../identifier.php');
require_once ('../reference.php');
require_once ('../geocoding.php');
require_once ('../nomenclator.php');

//--------------------------------------------------------------------------------------------------
class DisplayReference extends DisplayObject
{
	public $localities = array();
	public $page = 0;
	public $taxon_names = NULL;
	public $in_bhl = false;
	
	//----------------------------------------------------------------------------------------------
	function GetId()
	{
		if (isset($_GET['id']))
		{
			$this->id = $_GET['id'];
		}
		if (isset($_GET['page']))
		{
			$this->page = $_GET['page'];
		}
	}	
	
	//----------------------------------------------------------------------------------------------
	function GetFormat()
	{
		if (isset($_GET['format']))
		{
			switch ($_GET['format'])
			{

				case 'text':
					$this->format = 'text';
					break;					

				case 'xml':
					$this->format = 'xml';
					break;

				case 'ris':
					$this->format = 'ris';
					break;

				case 'bib':
					$this->format = 'bib';
					break;
		
				default:
					parent::GetFormat();
					break;
			}
		}
	}	
	
	//----------------------------------------------------------------------------------------------
	function DisplayFormattedObject()
	{
		switch ($this->format)
		{
			case 'xml':
				$this->DisplayXml();
				break;

			case 'text':
				$this->DisplayText();
				break;

			case 'ris':
				$this->DisplayRis();
				break;

			case 'bib':
				$this->DisplayBibtex();
				break;

			default:
				parent::DisplayFormattedObject();
				break;
		}
	}		
	
	

	//----------------------------------------------------------------------------------------------
	// Extra <HEAD> items
	function DisplayHtmlHead()
	{
		global $config;
		
		echo reference_to_meta_tags($this->object);
		
		echo html_include_css('css/viewer.css');
		echo html_include_script('js/fadeup.js');
		echo html_include_script('js/prototype.js');
		echo html_include_script('js/lazierLoad.js'); // not working for some reason...
		echo html_include_script('js/viewer.js');
		
		// Recaptcha
		echo html_include_script('http://api.recaptcha.net/js/recaptcha_ajax.js');

		// Tag tree for names
		$this->taxon_names = bhl_names_in_reference($this->id);
		if ($this->taxon_names != NULL)
		{
			$tags = '';
			foreach ($this->taxon_names->names as $name) 
			{
				$tags .= $name['namestring'] . '|' . $name['NameBankID'] . "\\n";
			}
		
			echo  '<script type="text/javascript">' . "\n";
			echo "function make_tag_tree()
			{
			var success	= function(t){tagtreeComplete(t);}
			var failure	= function(t){tagtreeFailed(t);}
		
			var url = '" . $config['web_root'] . "tagtree/tags2tree.php';
			var pars = 'tags='+ '" . $tags . "';
//			pars += '&url=display_name.php?id%3D'
			pars += '&url=name/'
			var myAjax = new Ajax.Request(url, {method:'post', postBody:pars, onSuccess:success, onFailure:failure});
			}
			
function tagtreeComplete(t)
{
	var s = t.responseText;
	
	$('taxon_names').innerHTML = s;
}

function tagtreeFailed(t)
{
}			
			";
			echo  '</script>' . "\n";
		}
			
		// Form validation
		echo  '<script type="text/javascript">' . "\n";
		echo 'var check_issn = /^[0-9]{4}\-[0-9]{3}([0-9]|X)$/;' . "\n";
		echo 'var check_date = /^([0-9]{4})\-([0-9]{2})\-([0-9]{2})$/;' . "\n";
		echo 'var check_year = /^[0-9]{4}$/;' . "\n";
		
		echo  '</script>' . "\n";
		
			
		// Form editing
		echo  '<script type="text/javascript">' . "\n";
		echo '
function reportErrors(errors)
{
 var msg = "The form contains errors...\n";
 for (var i = 0; i<errors.length; i++) {
 var numError = i + 1;
  msg += "\n" + numError + ". " + errors[i];
}
 alert(msg);
}		
		
function store(form_id, page_id)
{
	var form = $(form_id);
	
	// validate
	var issn = form.issn.value;
	var date = form.date.value;
	var year = form.year.value;

	var secondary_title = form.secondary_title.value;

	var errors = [];
 
 	if (secondary_title == "")  
 	{
  		errors[errors.length] = "Please supply a journal name";
 	}	


 	if ((issn != "") && !check_issn.test(issn)) 
 	{
  		errors[errors.length] = "ISSN " + "\"" + issn + "\" is not valid";
 	}	
 	if ((date != "") && !check_date.test(date)) 
 	{
  		errors[errors.length] = "Date must be in form \"YYYY-MM-DD\"";
 	}	
 	if ((year != "") && !check_year.test(year)) 
 	{
  		errors[errors.length] = "Year \"" + year + "\" is not valid";
 	}	

	if (errors.length > 0)
	{
		reportErrors(errors);
		Recaptcha.create("' . $config['recaptcha_publickey'] . '",
			"recaptcha_div", {
			theme: "clean",
			callback: Recaptcha.focus_response_field
		});
		return false;
	}
		
	//alert($(form).serialize());
	
	// Update database
	var success	= function(t){updateSuccess(t);}
	var failure	= function(t){updateFailure(t);}
	
	var url = "' . $config['web_root'] . 'update.php";
	var pars = $(form).serialize() + "&PageID=" + page_id + "&update=true";
	var myAjax = new Ajax.Request(url, {method:"post", postBody:pars, onSuccess:success, onFailure:failure});
}

function updateSuccess (t)
{
	var s = t.responseText.evalJSON();
	//alert(t.responseText);
	if (s.is_valid)
	{
		// we\'ve updated metadata, so reload page (or do ajax calls, but reload is easier)
		window.location.reload(true);
	}
	else
	{
		// User did not pass recaptcha so refresh it
		Recaptcha.create("' . $config['recaptcha_publickey'] . '",
			"recaptcha_div", {
			theme: "clean",
			callback: Recaptcha.focus_response_field
		});
		//fadeUp($(metadata_form),255,255,153);
	}
}
function updateFailure (t)
{
	alert("Failed: " + t);
}

';

// Based on http://ne0phyte.com/blog/2008/09/02/javascript-keypress-event/
// and http://blog.evandavey.com/2008/02/how-to-capture-return-key-from-field.html
// I want to cpature enter key press in recaptcha to avoid submitting the form (user must click
// on button for that). We listen for keypress and eat it. Note that we attach the listener after
// the window has loaded.
echo 'function onMyTextKeypress(event)
{
	if (Event.KEY_RETURN == event.keyCode) 
	{
		// do something usefull
		//alert(\'Enter key was pressed.\');
		
		Event.stop(event);
	}
	return;
}
Event.observe(window, \'load\', function() {
	Event.observe(\'recaptcha_response_field\', \'keypress\', onMyTextKeypress);
});';

		echo  '</script>';
		
		// If we have point localities then we need a map
		if (count($this->localities) != 0)
		{
			echo html_include_script('http://maps.google.com/maps?file=api&amp;v=2&amp;key=' . $config['gmap']);
			echo html_include_script('js/gmap.js');
			echo  '<script type="text/javascript">' . "\n";
			
			echo '
				function initialize() 
				{
				  if (GBrowserIsCompatible()) 
				  {
						var map = new GMap2(document.getElementById("map_canvas"));
						map.setCenter(new GLatLng(0, -0), 1);
						map.addControl(new GSmallMapControl());
						map.addControl(new GOverviewMapControl());
						map.addControl(new GMapTypeControl());        
						map.addMapType(G_PHYSICAL_MAP);
						//map.setMapType(G_PHYSICAL_MAP);
						';
			
			
			foreach ($this->localities as $loc)
			{
				echo 'map.addOverlay(createMarker(new GLatLng(';
				echo $loc->latitude;
				echo ',';
				echo $loc->longitude;
				echo '),\'\',';
				if ($loc->name != '')
				{
					echo "'" . $loc->name . "'";
				}
				else
				{
					echo "'" . format_decimal_latlon($loc->latitude, $loc->longitude) . "'";
				}
				echo '));' . "\n";
			}
			echo ' 	}
			}' . "\n";
			echo  '</script>' . "\n";
		}
	}
	
	//----------------------------------------------------------------------------------------------
	function DisplayBodyOpen()
	{
		if (count($this->localities) != 0)
		{
			// Load Google Maps
			echo html_body_open(
				array(
					'onload' => 'initialize()',
					'onunload' => 'GUnload()'
					)
				);
		}
		else
		{
			echo html_body_open();
		}
	}	
	
	//----------------------------------------------------------------------------------------------
	function DisplayEditForm()
	{
		$html = reference_form($this->object);
		return $html;
	}

	//----------------------------------------------------------------------------------------------
	function DisplayHtmlContent()
	{
		global $config;

		echo html_page_header(true, '', 'name');
		
		echo '<div style="float:right;background-color:rgb(230,242,250);padding:6px">' . "\n";
		echo '<h2>Identifiers</h2>' . "\n";
		echo '<ul class="guid-list">' . "\n";
			
		echo '<li class="permalink"><a href="' . $config['web_root'] . 'reference/' . $this->id . '" title="Permalink">' . $config['web_root'] . 'reference/' . $this->id . '</a></li>' . "\n";	
		if ($this->in_bhl)
		{
			echo '<li class="bhl"><a href="http://www.biodiversitylibrary.org/page/' . $this->object->PageID . '" target="_new" title="BHL page">' .  $this->object->PageID . '</a></li>' . "\n";
		}
		
		if (isset($this->object->doi))
		{
			echo '<li class="doi"><a href="http://dx.doi.org/' . $this->object->doi . '" target="_new" title="DOI">' .  $this->object->doi . '</a></li>' . "\n";
		}
		if (isset($this->object->url))
		{
			echo '<li class="url"><a href="' . $this->object->url . '" target="_new" title="URL">' .  trim_string($this->object->url, 30) . '</a></li>' . "\n";
		}
		if (isset($this->object->pdf))
		{
			echo '<li class="pdf"><a href="' . $this->object->pdf . '" target="_new" title="PDF">' .  trim_string($this->object->pdf, 30) . '</a></li>' . "\n";
		}
		if (isset($this->object->hdl))
		{
			echo '<li class="handle"><a href="http://hdl.handle.net/' . $this->object->hdl . '" target="_new" title="Handle">' .  $this->object->hdl . '</a></li>' . "\n";
		}
		if (isset($this->object->lsid))
		{
			echo '<li class="lsid"><a href="' . $config['web_root'] . $this->object->lsid . '" title="LSID">' . $this->object->lsid . '</a></li>' . "\n";
		}
		if (isset($this->object->pmid))
		{
			echo '<li class="pmid"><a href="http://www.ncbi.nlm.nih.gov/pubmed/' . $this->object->pmid . '" target="_new" title="PMID" >' . $this->object->pmid . '</a></li>' . "\n";
		}
		echo '</ul>' . "\n";

		echo '<h2>Export</h2>' . "\n";
		echo '<ul class="export-list">' . "\n";
		echo '<li class="xml"><a href="' . $config['web_root'] . 'reference/' . $this->id . '.xml" title="Endnote XML">Endnote XML</a></li>';
		echo '<li class="ris"><a href="' . $config['web_root'] . 'reference/' . $this->id . '.ris" title="RIS">Reference manager</a></li>';		
		echo '<li class="bibtex"><a href="' . $config['web_root'] . 'reference/' . $this->id . '.bib" title="BibTex">BibTex</a></li>';	
		
		if ($this->in_bhl)
		{
			echo '<li class="text"><a href="' . $config['web_root'] . 'reference/' . $this->id . '.text" title="Text">Text</a></li>';
		}
		echo '</ul>' . "\n";


		echo '</div>' . "\n";
		
		
		echo '<h1>' . $this->GetTitle() . '</h1>' . "\n";
		
		//------------------------------------------------------------------------------------------
		// Authors
		echo '<div>' . "\n";
		$count = 0;
		$num_authors = count($this->object->authors);
		if ($num_authors > 0)
		{
			foreach ($this->object->authors as $author)
			{
				echo '<a href="' . $config['web_root'] . 'author/' . $author->id . '">';
				echo $author->forename . ' ' . $author->lastname;
				if (isset($author->suffix))
				{
					echo ' ' . $author->suffix;
				}
				echo '</a>';
				$count++;
				if ($count < $num_authors -1)
				{
					echo ', ';
				}
				else if ($count < $num_authors)
				{
					echo ' and ';
				}
				
			}
		}
		echo "\n" . '</div>' . "\n";
		
		
		//------------------------------------------------------------------------------------------
		// Metadata and COinS
		echo '<div>' . "\n";
		echo '<span class="journal">';
		
		// Various options for linking journal.
		if (isset($this->object->issn))
		{
			echo '<a href="' . $config['web_root'] . 'issn/' . $this->object->issn . '">';
			echo $this->object->secondary_title;
			echo '</a>';
		}
		elseif (isset($this->object->oclc))
		{
			echo '<a href="' . $config['web_root'] . 'oclc/' . $this->object->oclc . '">';
			echo $this->object->secondary_title;
			echo '</a>';
		}
		else		
		{
			echo $this->object->secondary_title;
		}
		echo '</span>';
		echo ' ';
		if (isset($this->object->series))
		{
			echo ' <span class="volume">(' . $this->object->series . ') </span>';
		}
		echo '<span class="volume">' . $this->object->volume . '</span>';
		if (isset($this->object->issue))
		{
			echo '<span class="issue">' . '(' . $this->object->issue . ')' . '</span>';
		}		
		echo ':';
		echo ' ';
		echo '<span class="pages">' . $this->object->spage . '</span>';
		if (isset($this->object->epage))
		{
			echo '<span class="pages">' . '-' . $this->object->epage . '</span>';
		}
		if (isset($this->object->year))
		{
			echo ' ';
			echo '<span class="year">' . '(' . $this->object->year . ')' . '</span>';
		}
		echo reference_to_coins($this->object);
		echo '</div>' . "\n";
		
		//------------------------------------------------------------------------------------------
		// When record added and updated
		echo '<p class="explanation">Reference added ';
		echo distanceOfTimeInWords(strtotime($this->object->created) ,time(),true);
		echo ' ago';		
		echo '</p>' . "\n";
		
		//------------------------------------------------------------------------------------------
		// Export options
/*		echo '<h2>Export</h2>' . "\n";
		echo '<div>' . "\n";
		echo '<span><a href="' . $config['web_root'] . 'reference/' . $this->id . '.xml" title="Endnote XML">Endnote XML</a></span>';
		echo ' | ';
		echo '<span><a href="' . $config['web_root'] . 'reference/' . $this->id . '.ris" title="RIS">Reference manager</a></span>';		
		echo ' | ';
		echo '<span><a href="' . $config['web_root'] . 'reference/' . $this->id . '.bib" title="BibTex">BibTex</a></span>';	
		
		if ($this->in_bhl)
		{
			echo ' | ';
			echo '<span><a href="' . $config['web_root'] . 'reference/' . $this->id . '.text" title="Text">Text</a></span>';
		}
		echo '</div>' . "\n";
*/		
		
		//------------------------------------------------------------------------------------------
		// Identifiers
/*		echo '<h2>Identifiers</h2>' . "\n";
		echo '<ul>' . "\n";
		if ($this->in_bhl)
		{
			// BHL reference
			echo '<li><a href="http://www.biodiversitylibrary.org/page/' . $this->object->PageID . '" target="_new">BHL PageID:' . $this->object->PageID . '</a></li>' . "\n";
		}
		
		if (isset($this->object->sici))
		{
			echo '<li><a href="' . $config['web_root'] . 'sici/' . $this->object->sici . '">' .  $this->object->sici . '</a></li>' . "\n";
		}
		if (isset($this->object->url))
		{
			echo '<li><a href="' . $this->object->url . '" target="_new">' .  $this->object->url . '</a></li>' . "\n";
		}
		if (isset($this->object->pdf))
		{
			echo '<li><a href="' . $this->object->pdf . '" target="_new">' .  $this->object->pdf . '</a></li>' . "\n";
		}
		if (isset($this->object->doi))
		{
			echo '<li><a href="http://dx.doi.org/' . $this->object->doi . '" target="_new">' .  $this->object->doi . '</a></li>' . "\n";
		}
		if (isset($this->object->hdl))
		{
			echo '<li><a href="http://hdl.handle.net/' . $this->object->hdl . '" target="_new">' .  $this->object->hdl . '</a></li>' . "\n";
		}
		if (isset($this->object->lsid))
		{
			echo '<li><a href="' . $config['web_root'] . $this->object->lsid . '">' . $this->object->lsid . '</a></li>' . "\n";
		}
		if (isset($this->object->pmid))
		{
			echo '<li><a href="http://www.ncbi.nlm.nih.gov/pubmed/' . $this->object->pmid . '" target="_new">' . $this->object->pmid . '</a></li>' . "\n";
		}
		echo '</ul>' . "\n";*/
		
		//------------------------------------------------------------------------------------------
		// Linking
		echo '<div>' . "\n";
		echo '<span><a href="' . $config['web_root'] . 'reference/' . $this->id . '/backlinks" title="References">Cites (' . num_cites($this->id) . ')</a></span>';
		echo ' | ';
		echo '<span><a href="' . $config['web_root'] . 'reference/' . $this->id . '/forwardlinks" title="Forward links">Cited by (' . num_cited_by($this->id) . ')</a></span>';
		echo '</div>' . "\n";
		
		//------------------------------------------------------------------------------------------
		// Nomenclature
		$acts = array();
		if (isset($this->object->lsid))
		{
			$acts = array_merge($acts, acts_in_publication($this->object->lsid));
		}
		if (count($acts) > 0)
		{
			echo '<h2>Names published</h2>';
			echo '<ul>';
			foreach ($acts as $tn)
			{
				echo '<li><a href="' . $config['web_root'] . 'name/' . urlencode($tn->ToHTML()) . '">' . $tn->ToHTML() . '</a></li>';
			}
			echo '</ul>';
		}
		
		
		//------------------------------------------------------------------------------------------
		if ($this->in_bhl)
		{
			//--------------------------------------------------------------------------------------
			echo '<h2>Viewer</h2>';
			echo '<p id="viewer_status"></p>' . "\n";
			echo '<table width="100%" >';
			echo '<tr  valign="top"><td>';
			echo bhl_reference_viewer($this->id, $this->page);
			echo '</td>';
			echo '<td>';
			
			echo $this->DisplayEditForm();
			
			echo '</td></tr>';
			echo '</table>';
			
			
			//--------------------------------------------------------------------------------------
			$tag_cloud = name_tag_cloud($this->taxon_names);
			if ($tag_cloud != '')
			{
				echo '<h2>Taxon name tag cloud</h2>';
				echo '<p class="explanation">Taxonomic names extracted from OCR text for document using uBio tools.</p>';
				echo $tag_cloud;

				echo '<h2>Taxonomic classification</h2>';
				echo '<p class="explanation">Catalogue of Life classification for taxonomic names in document</p>';
				echo '<div id="taxon_names"></div>';
				
				echo  '<script type="text/javascript">make_tag_tree();</script>';

			}
			
			//--------------------------------------------------------------------------------------
			if (count($this->localities) != 0)
			{
				echo '<h2>Localities</h2>';
				echo '<p class="explanation">Localities extracted from OCR text.</p>';
				echo '<div id="map_canvas" style="width: 600px; height: 300px"></div>';
			}
			
			
		}
		else
		{
			echo '<table width="100%" >';
			echo '<tr><td valign="top" width="600">';
			
			$have_content = false;
			
			// PDF displayed using Google Docs
			if (!$have_content)
			{
				// If we have a PDF display it using Google Docs Viewer http://docs.google.com/viewer
				if ($this->object->url)
				{
					if (preg_match('/\.pdf$/', $this->object->url))
					{
						echo '<iframe src="http://docs.google.com/viewer?url=';
						echo urlencode($this->object->url) . '&embedded=true" width="600" height="700" style="border: none;">' . "\n";
						echo '</iframe>' . "\n";
						
						$have_content = true;
					}
				}
			}
			
			if (!$have_content)
			{
				if (isset($this->object->abstract))
				{
					echo '<h3>Abstract</h3>' . "\n";
					echo '<div>' . $this->object->abstract . '</div>' . "\n";
					$have_content = true;
				}
			}
			
			if (!$have_content)
			{
				echo '<span>[No text or abstract to display]</span>';
			}
			
			
			echo '</td>';
			echo '<td>';
			
			echo $this->DisplayEditForm();
			
			echo '</td></tr>';
			echo '</table>';
		
		}
	}
	
	//----------------------------------------------------------------------------------------------
	// JSON format
	function DisplayJson()
	{
		header("Content-type: text/plain; charset=utf-8\n\n");
		echo json_format(json_encode($this->object));
	}
	
	//----------------------------------------------------------------------------------------------
	function DisplayRis()
	{
		header("Content-type: text/plain; charset=utf-8\n\n");
		echo reference_to_ris($this->object);
	}
	
	//----------------------------------------------------------------------------------------------
	function DisplayBibtex()
	{
		header("Content-type: application/x-bibtex; charset=utf-8\n\n");
		echo reference_to_bibtex($this->object);
	}	

	//----------------------------------------------------------------------------------------------
	// Endnote XML export format
	function DisplayXml()
	{
		// Create XML document
		$doc = new DomDocument('1.0', 'UTF-8');
		$xml = $doc->appendChild($doc->createElement('xml'));

		// root element is <records>
		$records = $xml->appendChild($doc->createElement('records'));

		// add record for this reference
		reference_to_endnote_xml($this->object, $doc, $records);
		
		// Dump XML
		header("Content-type: text/xml; charset=utf-8\n\n");
		echo $doc->saveXML();
	}
	
	
	//----------------------------------------------------------------------------------------------
	function DisplayText()
	{
		$text = '';
		if (db_reference_from_bhl($this->id))
		{
			$pages = bhl_retrieve_reference_pages($this->id);
			$page_ids = array();
			foreach ($pages as $p)
			{
				$page_ids[] = $p->PageID;
			}
			
	
			$text = bhl_fetch_text_for_pages($page_ids);
			
			$text = str_replace ('\n', "\n" , $text);
			$text = str_replace ("\n ", "\n" , $text);
			
			// wiki experiments
			/*
			foreach ($page_ids as $page)
			{
				$names = names_in_page($page);
				print_r($names);
			}
			*/
		}
		
		header("Content-type: text/plain; charset=utf-8\n\n");
		echo $text;
	}

	//----------------------------------------------------------------------------------------------
	function GetTitle()
	{
		return $this->object->title;
	}


	//----------------------------------------------------------------------------------------------
	function Retrieve()
	{
		if ($this->id != 0)
		{
			$this->object = db_retrieve_reference ($this->id);
			$this->in_bhl = db_reference_from_bhl($this->id);
		}
								
		// Geocoding?
		if ($this->in_bhl)
		{
			if (!bhl_has_been_geocoded($this->id))
			{
				bhl_geocode_reference($this->id);
			}
			$this->localities = bhl_localities_for_reference($this->id);
		}
		
		return $this->object;
	} 

}

$d = new DisplayReference();
$d->Display();


?>