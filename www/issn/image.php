<?php

require_once ('../config.inc.php');

$issn = '';
if (isset($_GET['issn']))
{
	$issn = $_GET['issn'];
}

$image_filename = $config['web_dir'] . 'issn/images/unknown.png';
$ext = 'png';

if ($issn != '')
{
	// Do we have an image of the journal?
	$extensions = array('gif', 'jpg', 'png', 'jpeg');
	
	// Where we look for images
	$dir = $config['web_dir'] . 'issn/images';

	$base_name = str_replace('-', '', $issn);
	$found = false;
	
	foreach ($extensions as $extension)
	{
		$filename = $dir . '/' . $base_name . '.' . $extension;
		
		
		if (file_exists($filename))
		{
			$found = true;
			$ext = $extension;
			$image_filename = $config['webroot'] . 'issn/images/' . $base_name . '.' . $extension;
			break;
		}
	}
}

header("Content-Type: image/" . $ext);
@readfile($image_filename);

?>
	
