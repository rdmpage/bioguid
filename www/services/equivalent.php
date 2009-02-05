<?php

$gmlfile = '';
$name_map = array();

//--------------------------------------------------------------------------------------------------
function clean($str)
{
	$str = str_replace (".", " ", $str);
	$str = str_replace ("-", " ", $str);  // think about this...
	$str = preg_replace('/\s+/', ' ', $str);
	$str = trim($str);
	return $str;
}

//--------------------------------------------------------------------------------------------------
function compare($str1, $str2)
{
	global $gmlfile;
	global $node_map;
	
	$short = $str1;
	$long = $str2;
	
	$parts = explode(' ', $short);
	$lparts = explode(' ', $long);
	
	//print_r($parts);
	//print_r($lparts);
	
	// Swap if one string is longer than the other
	if (count($parts) > count($lparts))
	{
		$tmp = $parts;
		$parts = $lparts;
		$lparts = $tmp;
		
		$long = $str1;
		$short = $str2;
	}
	
	$pattern = '/';
	foreach ($parts as $p)
	{
		$pat = $p;
		if (strlen($p) > 1)
		{
			$pat = $p[0];
		}		
		$pattern .= '(' . $pat . '\w*)? ';
	}
	$pattern = trim($pattern);
	$pattern .= '/';
	
	//echo "Pattern=$pattern\n";
	
	$match = array();
	
	if (preg_match($pattern, $long . ' ', $match))
	{
//		print_r($match);
	}
	
	// Score for this comparison
	$score = 0;
	
	// We need to match all the parts of the shorter name
	$threshold = count($parts);
	
	// How many matches did we get?
	$count = 0;
	foreach ($match as $k => $v)
	{
		if ($v != '')
		{
			$count++;
		}
	}
	$count--; // ignore match[0], which we always have
	
	//echo "threshold=$threshold, count=$count\n";
	
	$ok = true;
	
	if ($count >= $threshold)
	{
		for ($i = 0; $i < $count; $i++)
		{
			//echo $parts[$i] . " - " .  $lparts[$i] . "\n";
			
			if ((strpos($parts[$i], $lparts[$i]) === false) and (strpos($lparts[$i], $parts[$i]) === false))
			{
				// Matches are different names
				$ok = false;
			}
			else
			{
				// any kind of match scores 1
				$score++;
			}
	
			// if the two names are identical, and longer than one
			// character regard them as being full name matches, and
			// the score is 1.1
			if (strcasecmp($parts[$i], $lparts[$i]) == 0)
			{
				if (strlen($parts[$i]) > 1)
				{
					$score += 0.1;
				}
			}
		}

	}
	
//	echo "score=$score\n";
	
	if ($score != 0 and $ok)
	{
		
		fwrite($gmlfile, "edge [ source " . $node_map[$str1] . " target " . $node_map[$str2] . " label \"" . $score . "\"]\n");
		
	}
	
	
}


if (count($_POST) == 0)
{
	header("Content-type: text/html; charset=utf-8");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<title>Author Name Matching</title>
	<meta name="generator" content="BBEdit 9.0" />
</head>
<body>
<form action="equivalent.php" method="post">
<textarea name="names" rows="30" cols="80"></textarea><br/>
<select name="format">
	<option value="html">HTML</option>
	<option value="json">JSON</option>
</select>
<input type="submit" value="Go">
</form>
</body>
</html>
<?php
}
else
{
	//echo "<pre>";
	
	//print_r($_POST);
	
	$names = explode ("\n", $_POST['names']);
	
	//print_r($names);
	
	$format = 'json';
	if (isset($_POST['format']))
	{
		$format = $_POST['format'];
	}
	//echo $format;
	
	$gmfilename = 'tmp/' . uniqid('') . '.gml';
	
	$gmlfile = @fopen($gmfilename, "w+") or die("could't open file --\"" . $gmfilename . "\"");
	fwrite($gmlfile, "graph [  
    version 1
    directed 0\n");	
	
	
	// Clean names
	for ($i = 0; $i < count($names); $i++)
	{
		$names[$i] = clean($names[$i]);
		if ($names[$i] == '')
		{
			unset($names[$i]);
		}
	}
	
	$tmp = array_unique($names);
	$names = array();
	foreach ($tmp as $n)
	{
		array_push($names, $n);
	}
	
	//print_r($names);
	
	// GML nodes
	for ($i = 0; $i < count($names); $i++)
	{
		fwrite($gmlfile, "node [id " . $i . " label \"" . $names[$i] . "\"]\n");
		$node_map[$names[$i]] = $i;
	}
	
	
	for ($i = 0; $i < count($names) -1; $i++)
	{
		for ($j = $i + 1; $j < count($names); $j++)
		{
			compare($names[$i], $names[$j]);
		}
	}
	
	
	fwrite($gmlfile, "]\n");
	fclose($gmlfile);

	//echo "</pre>";
	
	
	// process
//	$cmd = dirname(__FILE__)  . '/equivalent ' . $gmfilename;
	$cmd = '/Library/WebServer/bioguid/equivalent/equivalent ' . $gmfilename;
	
	switch ($format)
	{
		case 'json':
			header("Content-type: text/plain; charset=utf-8");
			system($cmd);
			break;
			
		case 'html':
			$output = array();
			exec ($cmd, $output);
			$json = join("\n", $output);
			
			header("Content-type: text/html; charset=utf-8");
			$obj = json_decode($json);

			echo '<p>Name clusters</p)';	
			echo '<ol>';
			foreach ($obj->clusters as $c)
			{
				$longest = '';
				foreach ($c as $k)
				{
					if (strlen($k) > strlen($longest))
					{
						$longest = $k;
					}
				}
				
				echo '<li><b>' . $longest . '</b><ul>';
				foreach ($c as $k)
				{
					echo '<li>' . $k . '</li>';
				}
				echo '</ul></li>';
			}
			echo '</ol>';
			break;
	}
	
}


?>