<?php

require_once(dirname(__FILE__).'/ubios.php');

$name = '';
$namebankID = array();

if (isset($_GET['name']))
{
	$name = $_GET['name'];
	
}

if ($name != '')
{
	$names = ubio_namebank_search_rest(trim($name), false, true); // just take simple exact match
	
	//print_r($names);
	
	if (count($names) > 0)
	{
		foreach ($names as $n)
		{
			array_push($namebankID, $n);
		}
	}
}

echo json_encode($namebankID);


?>