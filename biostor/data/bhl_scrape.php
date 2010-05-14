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

$Items = array(54586); // Ecuadorian frogs of the genus Colostethus (Anura:Dendrobatidae)

$Items = array(63495);

$Items=array(61725);

$Items=array(81069); // Malacologia vol 25

$Items=array(54629);

$Items=array(54208);

$Items=array(63419);

$Items=array(54586);

$Items=array(55145);

$Items=array(81045);

$Items=array(81210);

$Items=array(71907);

// Spixiana
$Items=array(89584,89727,89743,89565,89750,89812,89706,89747,89768,89811,89701,89575,89560,89588,
89573,89558,89815,89582,89816,89716,89571,89773,89557,89567,89589,89562);

// Extra Mis Pub Kansas
$Items=array(54987,54802,54835,54794,54817,55047,55069,54922,55046,54701,55041,54586);

$Items=array(54555);
$Items=array(54295);
$Items=array(81231);
$Items=array(81200);
$Items=array(54284);

$Items=array(53859); // Proc Calif Acad Sci
$Items=array(54291);

$Items=array(91152,90953,90417,90412,90954,90421,91104,91102,90543,90542,90880,90422,90419,90418,90509,90436,90449,90451,90438,90453,90455,90456,91167,91169,91191,91206,91212,91192,91210,91455,90541,90454,91359);

$Items=array(87816,87842,87830,87819,87809,87818,87807,87817,89680,89091,89430,89795,89045,89808,89090,89697,89039,89796,89814,89745,90122,89769,89185,89046,89428,89707,89660,89749,90121,89813,89708,89059,89774,89665,89583,89790,89663,89792,89662,89791,89658,89432,89667,89561,89587,90120,89586,89568,89772,89559,89585,89666,89715,89572,89570,89699,89704,89703,89569,89705,89700,90033,89574,89566,89590,89556,89591);

// Ann Mag Nat Hist
$Items=array(54081,54281,54284,54285,54286,54289,54290,54294,54295,54309,54359,54464,54554,54555,54556,54557,54587,54605,54611,55143,55145,55148,55172,55190,61654,61719,61720,61721,61725,61726,61727,61788,61789,61790,61791,61792,61794,61795,61796,61797,61798,61799,61855,61856,61857,61858,61860,61862,61863,61864,61866,61919,61920,61921,61922,61923,61924,61929,63336,63338,63340,63341,63342,63346,63347,63348,63417,63419,63421,63422,63423,63424,63425,63426,63491,63492,63493,63494,63495,63496,63588,63591,63677,63679,63684,63688,63770,63772,63777,65735,68510,68511,71826,71831,71832,71833,71834,71835,71836,71837,71838,71907,71908,71909,71910,71915,71919,71988,71990,71991,72068,72071,72074,72075,72076,72149,72150,72151,72153,72154,72155,72224,72225,72226,72229,72231,72232,72233,72234,72302,72303,72304,72305,72307,72310,72312,78257,78259,78260,78261,78262,78268,78380,78381,78384,78387,78391,78502,78506,78508,78509,78510,78511,79589,79672,81026,81041,81045,81046,81048,81071,81199,81200,81210,81219,81231,81237,84520,84521,84522,84523,85040,85173,85252,85286,86916,87333,87729,88001,88062,88260,88261,88262,88431,88432,88433,88434,88449,92742,93156);


// Wissenschaftliche Ergebnisse der Schwedischen Expedition nach de
$Items=array(54337);


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
	
	// Avoid duplicates
	$sql .= 'DELETE FROM bhl_item WHERE ItemID=' . $ItemID . ';' . "\n";
	
	$sql .= "INSERT INTO bhl_item(ItemID,TitleID,VolumeInfo) VALUES("
	. $ItemID . ',' . $TitleID . ', "' . $VolumeInfo . '");'  . "\n";
	
	$filename = $ItemID . '.sql';
	$file = @fopen($filename, "w") or die("couldn't open file \"$filename\"");
	fwrite($file, $sql);
	fclose($file);
	
}


?>