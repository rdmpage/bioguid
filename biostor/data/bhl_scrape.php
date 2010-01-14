<?php

require_once('../lib.php');


/*$ItemID = 63777;
$ItemID = 78260;
$ItemID = 63491;
$ItemID = 63426;

$ItemID = 79452;*/

$Items = array(84547);

// BMNH Zool
$Items = array(84546,88480,84548,84549,85288,85291,85292,85287);

foreach ($Items as $ItemID)
{
	// Fetch $html 
	
	$html = get('http://www.biodiversitylibrary.org/item/' . $ItemID);
	
	$Title = 0;
	$VolumeInfo = '';
	$sql = '';
	
	$done_pages = false;
	
	$lines = explode("\n", $html);
	
	// PageID for this item...
	$SequenceOrder = 1;
	foreach ($lines as $line)
	{
		if (preg_match('/<option selected="selected" value="' . $ItemID . '">(?<VolumeInfo>.*)<\/option>/', $line, $matches))
		{
			$VolumeInfo = $matches['VolumeInfo'];
		}
	
		if (preg_match('/<a href="\/bibliography\/(?<TitleID>\d+)"/', $line, $matches))
		{
			$TitleID = $matches['TitleID'];
		}

		if (preg_match('/<td class="volume"/', $line, $matches))
		{
			$done_pages = true;
		}
		
		if (!$done_pages)
		{
			if (preg_match('/<option(\s+selected="selected")?\s+value="(?<PageID>\d+)">(Page (?<PageNumber>\d+)|(?<PageTypeName>\w+(\s+\w+)?))<\/option>/', $line, $matches))
			{
				
				$keys = array();
				$values = array();
				
				// PageID
				$keys[] = 'PageID';
				$values[] = $matches['PageID'];
				
				// ItemID
				$keys[] = 'ItemID';
				$values[] = $ItemID;
		
				// Is page numbered
				if ($matches['PageNumber'] != '')
				{
					$keys[] = 'PagePrefix';
					$values[] = '"Page"';
		
					$keys[] = 'PageNumber';
					$values[] = '"' . $matches['PageNumber'] . '"';
					
					if (isset($matches['PageTypeName']))
					{
						if ($matches['PageTypeName'] == '')
						{
							$keys[] = 'PageTypeName';
							$values[] = '"Text"';				
						}
					}
				}
		
				if (isset($matches['PageTypeName']))
				{
					if ($matches['PageTypeName'] != '')
					{
						$keys[] = 'PageTypeName';
						$values[] = '"' . $matches['PageTypeName'] . '"';			
					}
				}
				
				// fix
				$sql .= 'DELETE FROM bhl_page WHERE PageID=' . $matches['PageID'] . ';' . "\n";
				$sql .= 'INSERT INTO bhl_page (' . implode (",", $keys) . ') VALUES (' . implode (",", $values) . ');' . "\n";
				
				// fix
				$sql .= 'DELETE FROM page WHERE PageID=' . $matches['PageID'] . ';' . "\n";
				$sql .= 'INSERT INTO page (PageID,ItemID,FileNamePrefix,SequenceOrder) VALUES('
				. $matches['PageID']
				. ',' . $ItemID
				. ',' . $matches['PageID'] // Fake this as we need FileNamePrefix to name images in cache
				. ',' . $SequenceOrder++
				.');' . "\n";
			}
		}
	}
	
	
	$sql .= "INSERT INTO bhl_item(ItemID,TitleID,VolumeInfo) VALUES("
	. $ItemID . ',' . $TitleID . ', "' . $VolumeInfo . '");'  . "\n";
	
	$filename = $ItemID . '.sql';
	$file = @fopen($filename, "w") or die("couldn't open file \"$filename\"");
	fwrite($file, $sql);
	fclose($file);
	
}


?>