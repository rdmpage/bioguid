<?php

/**
 * @file form.php
 *
 */
 
require_once ('../config.inc.php');

//--------------------------------------------------------------------------------------------------
// Hidden form used on openurl.php 
function reference_hidden_form($reference)
{
	$html = '<div style="display:none;">';
	
	$html .= '<textarea name="title" rows="5" cols="40">' . $reference->title . '</textarea>';
	// Authors
	$authors = '';
	foreach ($reference->authors as $author)
	{
		$authors .= $author->forename;
		$authors .= ' ' . $author->lastname;
		if (isset($author->suffix))
		{
			$authors .= ' ' .  $author->suffix;
		}
		$authors .= "\n";
	}
	$html .= '<textarea name="authors" rows="5" cols="40">' . trim($authors) . '</textarea>';
	
	foreach ($reference as $k => $v)
	{
		switch ($k)
		{
			case 'secondary_title':
				$html .= '<textarea name="' . $k . '" rows="2" cols="40">' . $v . '</textarea>';
				break;
		
			case 'volume':
			case 'issue':
			case 'spage':
			case 'epage':
			case 'date':
			case 'year':
			case 'issn':
				$html .= '<input type="text" name="' . $k . '" value="' . $v . '"></input>' . "\n";
				break;
				
			default:
				break;
		}
	}
	$html .= '</div>';
	
	return $html;
}

//--------------------------------------------------------------------------------------------------
//form for editing metadata
function reference_form($reference, $recaptcha = true)
{
	global $config;
	
	// field names
	$field_names = array(
		'issn' => 'ISSN',
		'series' => 'Series',
		'volume' => 'Volume',
		'issue' => 'Issue',
		'spage' => 'Starting page',
		'epage' => 'Ending page',
		'date' => 'Date',
		'year' => 'Year',
		);
	
	$html = '';
	
	$html .= '<form id="metadata_form" action="#">';
	$html .= '<table width="100%">';
	
	// Reference type
	// for now
	$html .= '<tr><td></td><td><input type="hidden" name="genre" value="article" ></td></tr>';

/*	$html .= '<tr><td class="field_name">Type:</td><td>';
	$html .= '<select id="genre">';
		
	$html .= '<option value="book"';
	if ($reference->genre == 'book')
	{
		$html .= ' selected="selected"';
	}
	$html .= '>Book</option>';
		$html .= '<option value="chapter"';
	if ($reference->genre == 'chapter')
	{
		$html .= ' selected="selected"';
	}
	$html .= '>Book chapter</option>';
	$html .= '<option value="article"';
	if ($reference->genre == 'article')
	{
		$html .= ' selected="selected"';
	}
	$html .= '>Journal article</option>';
	$html .= '</select>';
	$html .= '</td></tr>'; */
	
	// Title
	$html .= '<tr><td class="field_name">Title</td><td><textarea name="title" rows="5" cols="40">' . $reference->title . '</textarea></td></tr>';
	
	// Authors
	$authors = '';
	foreach ($reference->authors as $author)
	{
		$authors .= $author->forename;
		$authors .= ' ' . $author->lastname;
		if (isset($author->suffix))
		{
			$authors .= ' ' .  $author->suffix;
		}
		$authors .= "\n";
	}
	$html .= '<tr><td class="field_name">Authors</td><td><textarea name="authors" rows="5" cols="40">' . trim($authors) . '</textarea></td></tr>';

	$journal_fields = array('secondary_title', 'issn', 'series', 'volume', 'issue', 'spage', 'epage', 'date', 'year');

	foreach ($journal_fields as $k)
	{
		switch ($k)
		{
			case 'secondary_title':
				$html .= '<tr><td class="field_name">' . 'Journal' . '</td><td><textarea name="' . $k . '" rows="2" cols="40">' . $reference->{$k} . '</textarea></td></tr>';
				break;
		
			default:
				$html .= '<tr><td class="field_name">' . $field_names[$k] . '</td><td><input class="field_value" type="text" name="' . $k . '" value="';
				if (isset($reference->{$k}))
				{
					$html .= $reference->{$k};
				}
				// Vital to suppress enter key in input boxes
				$html .= '" onkeypress="onMyTextKeypress(event);"></td></tr>' . "\n";
				break;
		}
	}
	
	// Recaptcha 
	if ($recaptcha)
	{
		$html .= '<tr><td></td><td>';
		$html .= '<script type="text/javascript">';
		$html .= 'var RecaptchaOptions = ';
		$html .= '{';
		$html .=     'theme: \'white\',';
		$html .=     'tabindex: 2';
		$html .=  '};';
		$html .= '</script>';
		$html .= '<div id="recaptcha_div">';
		$html .= '<script type="text/javascript" src="http://api.recaptcha.net/challenge?k=' . $config['recaptcha_publickey'] . '"></script>';
		$html .= '</div>';
		$html .= '</td></tr>';
	}

	$html .= '<tr><td></td><td><span style="padding:2px;cursor:pointer;background-color:#2D7BB2;color:white;font-size:18px;font-family:Arial;text-align:center;" onclick="store(\'metadata_form\', ' . $reference->PageID . ');">&nbsp;Update&nbsp;</span></td></tr>' . "\n";

	$html .= '</table>
</form>';

	return $html;
}

?>