<?php

/**
 * @file connotea.php
 *
 * Access to Connotea
 *
 */

require ('../config.inc.php');

	// From http://thraxil.org/users/anders/posts/2005/12/13/scaling-tag-clouds/
	// we use a power law to assign weights to the terms


$debug = 0;

//---------------------------------------------------------------------------------------------------
function class_from_weight($w,$thresholds)
{
    $i = 0;
    for ($t = 0; $t < count($thresholds); $t++)
    {
    	$i++;
    	if ($w <= $t)
    	{
    		return $i;
    	}
    }
    return $i;
}


//---------------------------------------------------------------------------------------------------
function tags_summary ($user_tags)
{
	$result = new stdclass;
	
	$result->tags = array();
	$tag_count = array();
	$result->tag_list = array();
	
	foreach ($user_tags as $tag)
	{
		array_push($result->tags, $tag);
		
		if (in_array($tag, $tag_count))
		{
			$tag_count[$tag]++;
		}
		else
		{
			$tag_count[$tag]++;
		}
	}
	$result->tags = array_unique($result->tags);
	sort($result->tags);
	
	$result->max_frequency = 0;
	$result->min_frequency = count($user_tags);
	
	foreach ($result->tags as $tag)
	{
		array_push($result->tag_list, 
			array('term' => $tag, 
				'freq' => $tag_count[$tag])
			);
		$result->max_frequency = max($result->max_frequency, $tag_count[$tag]);
		$result->min_frequency = min($result->min_frequency, $tag_count[$tag]);
	}
	return $result;
}

//---------------------------------------------------------------------------------------------------
function tag_cloud($obj)
{
	$html = '';
	
	$levels = 5;
	
	$thresholds = array();
	
	for ($i = 0; $i < $levels; $i++)
	{
		$thresholds[$i] = pow($obj->max_frequency - $obj->min_frequency + 1, (float)$i/(float)$levels);
	}
		
	$html = '';
	foreach ($obj->tag_list as $key => $row) 
	{
		$font_size = 10 + 8 * class_from_weight($row['freq'] - $obj->min_frequency, $thresholds);
		$html .= '<a style="font-size:' . $font_size . 'px;"';
		$html .=  ' href="http://www.connotea.org/tag/' . urlencode($row['term']);
		$html .= '">';
		$html .=  $row['term'];
		$html .= '</a> ';
		$html .= "\n";
	}
	
	return $html;

}

//------------------------------------------------------------------------------
/**
 * @brief Return bookmark in Connotea
 *
 * Given a URI for a reference we compute the MD5 hash of the URI, 
 * which corresponds to the URI of the reference in Connotea if a user has 
 * bookmarked it. 
 *
 * We use the Connotea Web API http://www.connotea.org/wiki/WebAPI to
 * retrieve details about any existing bookmarks.
 *
 * Note that Connotea will return all the bookmarks that correspond to the
 * same URI. However, users may have used a original URIs when bookmarking
 * the reference, such as a DOI prefixed with http://dx.doi.org, or prefixed
 * with doi:, or the URL of the publisher's page for the article. To help
 * get around this problem we generate a range of possible URIs from the source
 * URI, such as trying alternative prefixes for DOI.
 *
 * All tags found are merged into a single array of unique tags.
 *
 * @param uri URI of original reference
 *
 * @return Object describing bookmark
 */
function connoteaTagsForURI ($uri)
{
	global $config;
	global $debug;

	$result =  new stdClass();
	$result->status = 'error';
	
	$tags = array();
	$bookmark = array();
	$users = array();
	$tag_count = array();
	

	$ch = curl_init(); 
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1); 
	curl_setopt($ch, CURLOPT_USERPWD, $config['connotea_user'] . ':' . $config['connotea_pass']);
	
	if ($config['proxy_name'] != '')
	{
		curl_setopt ($ch, CURLOPT_PROXY, $config['proxy_name'] . ':' . $config['proxy_port']);
	}
	
	// Cycle through alternative representations of URI
	$uriList = array();
	
	// A DOI may have been bookmarked as http://dx.doi.org/ or doi:
	if (preg_match('/^http:\/\/dx.doi.org\//', $uri))
	{
		// Identifier
		$uri = str_replace ('http://dx.doi.org/', '', $uri);
		
		// Replace <> with entities
		$uri = str_replace( '<', '%3C', $uri);
		$uri = str_replace( '>', '%3E', $uri);
				
		array_push ($uriList, 'http://dx.doi.org/' . $uri);
		array_push ($uriList, 'doi:' . $uri);
		
		// Connotea rendered the DOI lowercase, but it may have been bookmarked in uppercase
		$uri = strtoupper($uri);
		array_push ($uriList, 'http://dx.doi.org/' . $uri);
		array_push ($uriList, 'doi:' . $uri);
	}
	
	// A DOI may have been bookmarked as http://dx.doi.org/ or doi:
	if (preg_match('/^doi:/', $uri))
	{
		// Identifier
		$uri = str_replace ('doi:', '', $uri);
		
		// Replace <> with entities
		$uri = str_replace( '<', '%3C', $uri);
		$uri = str_replace( '>', '%3E', $uri);
				
		array_push ($uriList, 'http://dx.doi.org/' . $uri);
		array_push ($uriList, 'doi:' . $uri);
		
		// Connotea rendered the DOI lowercase, but it may have been bookmarked in uppercase
		$uri = strtoupper($uri);
		array_push ($uriList, 'http://dx.doi.org/' . $uri);
		array_push ($uriList, 'doi:' . $uri);
	}
	
	
	// PMID
	if (preg_match('/^http:\/\/www.ncbi.nlm.nih.gov\/pubmed\//', $uri))
	{
		// Extract PubMed identifier
		$uri = str_replace ('http://www.ncbi.nlm.nih.gov/pubmed/', '', $uri);
		
		// Try URL or pmid versions				
		array_push ($uriList, 'http://www.ncbi.nlm.nih.gov/pubmed/' . $uri);
		array_push ($uriList, 'pmid:' . $uri);
		
	}

	if (preg_match('/^pmid:/', $uri))
	{
		// Extract PubMed identifier
		$uri = str_replace ('pmid:', '', $uri);
		
		// Try URL or pmid versions				
		array_push ($uriList, 'http://www.ncbi.nlm.nih.gov/pubmed/' . $uri);
		array_push ($uriList, 'pmid:' . $uri);
		
	}
	
	
	if ($debug)
	{
		echo '<pre style="text-align: left;border: 1px solid #c7cfd5;background: #f1f5f9;padding:15px;">';
		print_r($uriList);
		echo "</pre>";	
	}
	
	
	foreach ($uriList as $u)
	{
		$url = 'http://www.connotea.org/data/uri/' . md5($u);
				
		if ($debug)
		{
			echo '<pre style="text-align: left;border: 1px solid #c7cfd5;background: #f1f5f9;padding:15px;">';
			echo $url;
			echo "</pre>";	
		}

		curl_setopt($ch, CURLOPT_URL, $url); 
		$curl_result = curl_exec ($ch); 
			
		if (curl_errno ($ch) != 0 )
		{
			echo "CURL error: ", curl_errno ($ch), " ", curl_error($ch);
		}
		else
		{
			$info = curl_getinfo($ch);
			$http_code = $info['http_code'];

			$bookmarkFound = false;
			
			if ($http_code == 200)
			{
				$result->status = 'ok';
				
				$xml = $curl_result;
				
				if ($debug)
				{
					echo '<pre style="text-align: left;border: 1px solid #c7cfd5;background: #f1f5f9;padding:15px;">';
					echo htmlentities($xml);
					echo "</pre>";	
				}
				
				$dom= new DOMDocument;
				$dom->loadXML($xml);
				$xpath = new DOMXPath($dom);
				
				$nodeCollection = $xpath->query ("//dc:subject");
				foreach($nodeCollection as $node)
				{
					$bookmarkFound = true;
					$tag = $node->firstChild->nodeValue;
					
					array_push($tags, $tag);
					
					if (in_array($tag, $tag_count))
					{
						$tag_count[$tag]++;
					}
					else
					{
						$tag_count[$tag]++;
					}
	
				}
				$nodeCollection = $xpath->query ("//dc:creator");
				foreach($nodeCollection as $node)
				{
					array_push($users, $node->firstChild->nodeValue);
	
				}
			}
			if ($bookmarkFound)
			{
				array_push($bookmark, 'http://www.connotea.org/uri/' . md5($u));
			}
		}
	}	
	
	
	$result->tags = tags_summary($tags);
		
	$bookmark = array_unique($bookmark);
	
	if (count($bookmark) > 0)
	{
		$result->bookmark = $bookmark[0];
	}
	else
	{
		$result->bookmark = '';
	}
	
	
	// Who posted this?
	$result->posts = count($users);
	$result->users = array();
	if ($result->posts > 0)
	{
		foreach ($users as $u)
		{
			array_push($result->users, $u);
		}
	}
		
	//$result->tags = $tags;
		
	if ($debug)
	{
		echo '<pre style="text-align: left;border: 1px solid #c7cfd5;background: #f1f5f9;padding:15px;">';
		print_r($bookmark);
		print_r($users);
		print_r($result);
		echo "</pre>";
	}
	
	return $result;
}

$debug = 0;
$uri = '';
$format = 'json';
$callback = '';

if (isset($_GET['uri']))
{
	$uri = $_GET['uri'];
	$result = connoteaTagsForURI($uri);
	$result->status = 'ok';
}
else
{
	$result =  new stdClass();
	$result->status = 'no uri supplied';
}

if (isset($_GET['format']))
{
	$format = $_GET['format'];
	switch ($format)
	{
		case 'json':
			$format = json;
			break;
		case 'html':
			$format= 'html';
			break;
		default:
			$format = 'json';
			break;
	}
}

if (isset($_GET['callback']))
{
	$callback = $_GET['callback'];
}


// generate html snippet
$html = '';
switch ($result->status)
{
	case 'ok':
		if ($result->posts == 0)
		{
			// not found
			$html .= '[not found in Connotea]';
		}
		else
		{
			$html .= '<a href="' . $result->bookmark . '">Bookmarked</a> by ';
			switch (count($result->users))
			{
				case 1: 
					$html .= '<a href="http://www.connotea.org/user/' . $result->users[0] . '">' . $result->users[0] . '</a>';
					break;
				case 2: 
					$html .= '<a href="http://www.connotea.org/user/' . $result->users[0] . '">' . $result->users[0] . '</a>';
					$html .= ' and <a href="http://www.connotea.org/user/' . $result->users[1] . '">' . $result->users[1] . '</a>';
					break;
				case 3: 
					$html .= '<a href="http://www.connotea.org/user/' . $result->users[0] . '">' . $result->users[0] . '</a>';
					$html .= ', <a href="http://www.connotea.org/user/' . $result->users[1] . '">' . $result->users[1] . '</a>';
					$html .= ' and <a href="http://www.connotea.org/user/' . $result->users[2] . '">' . $result->users[2] . '</a>';
					break;
					
				default:
					$html .= '<a href="http://www.connotea.org/user/' . $result->users[0] . '">' . $result->users[0] . '</a>';
					$html .= ', <a href="http://www.connotea.org/user/' . $result->users[1] . '">' . $result->users[1] . '</a>';
					$html .= ', <a href="http://www.connotea.org/user/' . $result->users[2] . '">' . $result->users[2] . '</a>';
					$html .= ' and ' . (count($result->users) - 3) . ' other(s) ';
					break;
			}
		}
		$html .= '<br/>' . tag_cloud($result->tags);
		break;
	
	default:
		// badness
		$html .= '';
		break;
}
$html = '<div>' . $html . '</div>';
$result->html = $html;

switch ($format)
{
	case 'json':
		$output = json_encode($result);
		header("Content-type: text/plain; charset=utf-8\n\n");
		$json = '';
		if ($callback != '')
		{
			$json .= $callback . '(';
		}
		$json .= $output;
		if ($callback != '')
		{
			$json .= ')';
		}
		echo $json;
		break;
		
	case 'html':
		echo $html;
		break;
		
	default:
		break;
}


?>
