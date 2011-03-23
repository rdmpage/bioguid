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

// Proceedings of the Zoological Society of London
$Items=array(97650);
$Items=array(92048, 92484, 93424, 96158, 96163, 96165, 96442, 96443, 96445, 96679, 96698, 96831, 96832, 96834, 96836, 96871, 96894, 97094, 97095, 97151, 97156, 97157, 97158, 97165, 97224, 97225, 97256, 97409, 97658, 97660, 97667, 97668, 97669, 97670, 97671, 97672, 97764, 97765, 97766, 98459, 98466, 98471, 98473, 98474, 98477, 98493, 98497, 98501, 98503, 98513, 98517, 98524, 98525, 98527, 98528, 98530, 98531, 98540, 98562, 98584, 98587, 98617, 98658, 99080, 99199, 99299, 99351, 99375, 99377, 99378, 99485, 99486, 99487, 99488, 99643, 99645, 99805, 99850);

$Items=array(95638,
93111,
93398,
93403,
95509,
93385,
93412);

$Items=array(94955);

$Items=array(94923);

// Annals of tropical medicine and parasitology
$Items=array(98765,
96750,
96751,
96752,
96604,
96753);

// Proceedings of the Zoological Society of London (extra)
$Items=array(100045,
100198,
100585,
100589,
100598,
100613,
100996);

// BHL fuck ups
// Proceedings of the Zoological Society of Londo

$Items=array(46225);
$Items=array(46212);

// Univ Kansas Bull
$Items=array(91701);

// Journal de conchyliologie.
$Items=array(81370);

// Proc Calif 4th series extra
$Items=array(84615,
85194,
85250);

// 3rd series extra
$Items=array(98467);

$Items=array(98465);

// Proc U S N M
$Items=array(88912,
94484,
99942);

// Proc Ent Soc Wash
$Items=array(54606,54651,54652,54654,54655,54663,54666,54667,54668,54669,54672,54695,54696,54697,54698,54709,54710,54711,54712,54765,54767,54775,54777,54778,54792,54793,54810,54811,54812,54813,54814,54815,54854,54855,54859,54866,54899,54937,54938,54955,54962,54979,54980,54981,54986,55015,55017,55043,55068,55083,55154,55199,55205,55207,55215,55252,81132,81133,81298,84616,84617,84949,89742,95279,95531,97365);

// Proc Ent Soc Wash
$Items=array(100258);

// Ent News
$Items=array(79668);

$Items=array(59708);

// Novon
$Items=array(55396,90519,92595);
$Items=array(55381);

// ann mag
$Items=array(94944);

// Trans Linn Soc TitleID=8257
$Items=array(55279,
55291,
55323,
55329,
55334,
55337,
81420,
81421,
86004,
90111);


$Items=array(102952);

$Items=array(88934); //TvE

// J Lin  Soc Lond Zool
$Items=array(98595,
99085,
98556,
100042,
99813,
98585,
99383,
99814,
98664,
98724,
99211,
98725,
98559,
98582,
98716,
99851,
99469,
98709,
99466,
98586,
98506,
98662,
100259,
98583,
99101,
98560);

// Bull Mus Comp Zool
$Items=array(54619,54620,85041,87759,91578,91579,91580,91581,91592,91593,91594,91600,91607,91608,91609,91610,91652,91653,91654,91655,91656,91657,91658,91659,91660,93764,95201,95202,95203,95211,95212,95213,95214,95215,95216,95217,95218,95357,95369,95375,95436,95437,95438,95439,95440,95443,95444,95611,95612,95613,95622,95623,95624,95816,95817,95818,95819,95862,96039,96065,96066,96181,96182,96183,96674,97351,98449,98450,98451,98596);


$Items=array(100961);

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
