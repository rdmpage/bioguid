<?php

// fetch names if we haven't got them...
require_once (dirname(__FILE__) . '/db.php');
require_once (dirname(__FILE__) . '/lib.php');
require_once (dirname(__FILE__) . '/bhl_names.php');

$ids = array(126311);

$ids=array(105416,105402,125890,59343,107177);

$ids=array(110878);

$ids=array(99906,108144,100200,100656,107955,107963,107976,83078,66142,87244,87255,105412,87185,87103,65960,66503,87120,105406,86522,105403,126315,107177,95592,97207,105415,87635,97899,65958,48993,59343,14562,125890,103534,109586,891,97484,889,105414,1312,104732,84533,113895,105402,85290,114597,126311,102399);

$ids = array(54668,14790,54677,99677,127560,60044,127575,127574,99512,107049,106157,105730,125900,127561,105962,61688,58301,105714,58630,14789,98326,14787,106156,97776,99962,98307,14781,97871,4435,844,97482,832,97490,101826,106132);

$ids=array(127609);
$ids=array(120824);

$ids=array(116847);

$ids=array(127997);

foreach ($ids as $reference_id)

/*$start = 1;
$end = 126555;
$start = 51457;
$start = 97870;

$start = 114301;
$end = 114302;
for ($reference_id = $start; $reference_id < $end; $reference_id++)*/

{
	
	
	echo $reference_id . "\n";
	
	if (db_retrieve_reference($reference_id) != null)
	{
		$nm = bhl_names_in_reference_by_page($reference_id);
		
		if (isset($nm->names) && (count($nm->names) == 0))
		{
			// fetch names
			$pages = bhl_retrieve_reference_pages($reference_id);
			$page_ids = array();
			foreach ($pages as $p)
			{
				echo $p->PageID;
				
				$parameters = array(
				'op' 		=> 'GetPageMetadata',
				'pageid' 	=> $p->PageID,
				'ocr' 		=> 'f',
				'names' 	=> 't',
				'apikey' 	=> '0d4f0303-712e-49e0-92c5-2113a5959159',
				'format' 	=> 'json'
				);
			
				$url = 'http://www.biodiversitylibrary.org/api2/httpquery.ashx?' . http_build_query($parameters);
				
				$json = get($url);
				
				//echo $json;
				if ($json != '')
				{
					$response = json_decode($json);
					
					foreach ($response->Result->Names as $Name)
					{
						echo '.';
						//print_r($Name);
						if (isset($Name->NameBankID) 
							&& ($Name->NameBankID != '')
							&& isset($Name->NameConfirmed))
						{
							$sql = 'INSERT INTO bhl_page_name(NameBankID,NameConfirmed,PageID) VALUES(' . $Name->NameBankID . ',' . $db->qstr($Name->NameConfirmed) . ',' . $p->PageID . ');';
							//echo $sql . "\n";
							$result = $db->Execute($sql);
							if ($result == false) die("failed [" . __FILE__ . ":" . __LINE__ . "]: " . $sql);
						}
					}
				}
				echo "\n";
			}	
		}
		else
		{
			echo " done!";
		}
		
	}
	echo "\n";
}

?>