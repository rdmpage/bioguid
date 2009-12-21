<?php

// cluster authors, and repopulate rdmp_text_index to help searching for author names

require_once (dirname(__FILE__) . '/db.php');

global $config;
global $db;

//--------------------------------------------------------------------------------------------------

$authors = array();
$sql = 'SELECT DISTINCT(author_id), forename, lastname 
FROM rdmp_author
INNER JOIN rdmp_author_reference_joiner USING(author_id)
WHERE (lastname <> "") AND (forename <> "")';

echo $sql . "\n";

$result = $db->Execute($sql);
if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);

while (!$result->EOF) 
{
	
	$author = new stdclass;
	$author->id = $result->fields['author_id'];
	$author->name = trim($result->fields['forename']) . ' ' . $result->fields['lastname'];
	
	$authors[] = $author;
	
	$result->MoveNext();				
}

print_r($authors);

foreach ($authors as $author)
{
	// 
	$sql = 'DELETE FROM rdmp_text_index WHERE (object_id=' . $author->id . ') AND (object_type=' . $db->qstr('author') . ')';
		
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);

	$sql = 'INSERT INTO rdmp_text_index(object_type, object_id, object_text)
	VALUES ("author", ' . $author->id . ', ' . $db->qstr($author->name) . ')';
	
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
}	


//--------------------------------------------------------------------------------------------------
// Get distinct last names that are linked to references
$last_names = array();

$sql = 'SELECT DISTINCT(lastname) AS last
FROM rdmp_author
INNER JOIN rdmp_author_reference_joiner USING(author_id)
WHERE (lastname <> "") AND (forename <> "")';

echo $sql . "\n";

$result = $db->Execute($sql);
if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);

while (!$result->EOF) 
{
	$last_names[] = $result->fields['last'];
	$result->MoveNext();				
}

print_r($last_names);



// Get forenames
foreach ($last_names as $last)
{
	// Reset clusters to be same as author_id
	$sql = 'UPDATE rdmp_author SET author_cluster_id = author_id WHERE lastname=' . $db->qstr($last);
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);

	// Get distinct forenames with this last name (restricted to author names actually linked
	// to references
	$sql = 'SELECT DISTINCT(forename) 
	FROM rdmp_author 
	INNER JOIN rdmp_author_reference_joiner USING(author_id)
	WHERE (lastname = ' . $db->qstr($last) . ') AND (forename <> "")';
	
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
	
	$names = '';
	$count = 0;
	
	while (!$result->EOF) 
	{
		$names .=  $result->fields['forename'] . "\n";
		$count++;
		$result->MoveNext();				
	}	

	//echo $names;
	
	if ($count > 1)
	{
		// More than one person has this last name

		// Call equivalent names service
		$url = 'http://bioguid.info/services/equivalent.php';
		
		$ch = curl_init(); 
		curl_setopt ($ch, CURLOPT_URL, $url); 
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt ($ch, CURLOPT_POST,	1); 
		
		if ($config['proxy_name'] != '')
		{
			curl_setopt ($ch, CURLOPT_PROXY, $config['proxy_name'] . ':' . $config['proxy_port']);
		}
		
		$vars = "names=" . trim($names) . "&format=json";
		
		curl_setopt ($ch, CURLOPT_POSTFIELDS, $vars); 
		
				
		$j = curl_exec ($ch); 
		
		if (curl_errno ($ch) != 0 )
		{
			echo "CURL error: ", curl_errno ($ch), " ", curl_error($ch);
		}
		else
		{
			$info = curl_getinfo($ch);
			//print_r($info);	
		}
		
		if ($j != '')
		{
			// Cluster names
			$obj = json_decode($j);
			
			print_r($obj);
			
			foreach ($obj->clusters as $c)
			{
				// Link these authors together
				$ids = array();
								
				foreach ($c as $k)
				{
					$k = trim($k);

					$sql = 'SELECT DISTINCT(author_id)
						FROM rdmp_author 
						INNER JOIN rdmp_author_reference_joiner USING(author_id)
						WHERE (lastname = ' . $db->qstr($last) . ') AND (forename = ' . $db->qstr($k) . ')';

					$result = $db->Execute($sql);
					if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);

					while (!$result->EOF) 
					{
						$ids[] = $result->fields['author_id'];
						$result->MoveNext();				
					}	
				}
			}
			sort($ids);
			
			$author_cluster_id = $ids[0];
			foreach ($ids as $author_id)
			{
				$sql = 'UPDATE rdmp_author SET author_cluster_id=' . $author_cluster_id
				. ' WHERE author_id=' . $author_id;
				
				$result = $db->Execute($sql);
				if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
			}
		}
	}
}

?>