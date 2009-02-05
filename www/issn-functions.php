<?php

/**
 *
 * @file issn.php
 *
 * Handle ISSN lookups
 
 
 Needs ISSN database (based on Entrez, JSTOR, CrossRef, JournalSeek, etc
 
INSERT INTO issn(title, issn)
SELECT crossref.title, crossref.issn FROM crossref
LEFT JOIN issn ON crossref.title = issn.title
WHERE issn.title IS NULL;
 
 
 *
 */

require_once('db.php');
require_once('ISBN-ISSN.php');

//--------------------------------------------------------------------------------------------------

// from http://en.wikipedia.org/wiki/Longest_common_subsequence_problem

function  LCSLength($X, $Y)
{
	$C = array();

	$m = strlen($X);
	$n = strlen($Y);

	for ($i = 0; $i <= $m; $i++)
	{
		$C[$i][0] = 0;
	}
	for ($j = 0; $j <= $n; $j++)
	{
		$C[0][$j] = 0;
	}

	for ($i = 1; $i <= $m; $i++)
	{
		for ($j = 1; $j <= $n; $j++)
		{
			if ($X{$i-1} == $Y{$j-1})
			{
				$C[$i][$j] = $C[$i-1][$j-1]+1;
			}
			else
			{
				$C[$i][$j] = max($C[$i][$j-1], $C[$i-1][$j]);
			}
		}
	}

	return $C;
}


$left = '';
$right = '';

//--------------------------------------------------------------------------------------------------
function printDiff($C, $X, $Y, $i, $j)
{
	global $left;
	global $right;
	if (($i > 0) and ($j > 0) and ($X{$i-1} == $Y{$j-1}))
	{
		printDiff($C, $X, $Y, $i-1, $j-1);
		//echo "  " , $X{$i-1};

		$left .= "<span style=\"background:rgb(100,255,100);color:black;\">" . $X{$i-1} . "</span>";
		$right .= "<span style=\"background:rgb(100,255,100);color:black;\">" . $X{$i-1} . "</span>";
	   }
	else
	{
		if (($j > 0) and ($i == 0 or $C[$i][$j-1] >= $C[$i-1][$j]))
		{
			printDiff($C, $X, $Y, $i, $j-1);
			//echo "+ " , $Y{$j-1};

			$right .= $Y{$j-1};
		}
		else 
		{
			if (($i > 0) and ($j == 0 or $C[$i][$j-1] < $C[$i-1][$j]))
			{
			printDiff($C, $X, $Y, $i-1, $j);
			//echo "- " , $X{$i-1};

			$left .= $X{$i-1};
			}
		}
	}
}

//--------------------------------------------------------------------------------------------------
/**
 * @brief Obtain ISSN for a journal
 *
 * @param journal Journal name
 * @param threshold Threshold for matching name (default = 0.75)
 *
 * If exact match not found we use approximate string matching to find the best match. The
 * journal name is stripped of short words ("of", "the") and punctuation, then a MySQL LIKE
 * query finds a candidate list. From this list we take title with the best Dice score.
 * 
 * @return ISSN, if it exists, otherwise an empty string
 *
 */
function issn_from_journal_title($journal, $threshold = 0.75)
{
	global $db; 
	global $left;
	global $right;
	global $debug;

	$issn = '';
	
	$journal = trim($journal);

	// First try and exact match
	$sql = 'SELECT * FROM issn WHERE (title = ' . $db->Quote($journal) . ')';
	

	$result = $db->Execute($sql);
	if ($result == false) die("failed: " . $sql);

	if ($result->NumRows() == 1)
	{
		$issn = $result->fields['issn'];
	}
	else
	{
		// No exact match, try an approximate match

		// Clean up
		$query = $journal;
		
		// short pronouns are likely to cause problems as people may get them wrong (ie., "of" and "for")
		
		$query = str_replace(' of ', ' ', $query);
		$query = str_replace(' for ', ' ', $query);
		$query = preg_replace('/^The /', '', $query);
		
		$query = str_replace(',', '', $query);
		$query = str_replace('\'', '', $query);
		$query = str_replace('.', '', $query);
		$query = str_replace(' ', '% ', $query);
		$query = '%' . $query;
		$query .= '%';
		

		$sql = 'SELECT * FROM issn WHERE (title LIKE ' . $db->Quote($query) . ')';
		
		//echo $sql;


		$result = $db->Execute($sql);
		if ($result == false) die("failed: " . $sql);

		// Build results list
		$hits = array();


		while (!$result->EOF) 
		{			
			$left = $right = '';

			$qStr = $journal;
			$qStr = str_replace('.', '', $qStr);
			$hStr = $result->fields['title'];
			$hStr = str_replace('.', '', $hStr);

			$C = LCSLength($qStr, $hStr);
			printDiff($C, $qStr, $hStr, strlen($qStr), strlen($hStr));

			$score = $C[strlen($qStr)][strlen($hStr)];

			$score = 1.0 - (float)(strlen($qStr) + strlen($hStr) - 2 * $score)/(float)(strlen($qStr) + strlen($hStr));
			//$score *= 100;

			$hit = array(
				'hit' => $result->fields['title'],
				'hitDisplay' => $right,
				'score' => $score,
				'issn' => $result->fields['issn']
				);

			array_push($hits, $hit);
			$result->MoveNext();
		}		
		// sort
		$scores = array();
		foreach ($hits as $key => $row) 
		{
				$scores[$key]  = $row['score'];
		}
		array_multisort($scores, SORT_NUMERIC, SORT_DESC, $hits);

		if ($debug)
		{
			echo '<table border="1" cellpadding="2">';
			echo '<tr style="font-family:Arial;font-size:12px;"><th>Journal</th><th>Score</th><th>ISSN</th></tr>';
			foreach ($hits as $hit)
			{
				echo '<tr style="font-family:Arial;font-size:12px;">';
				echo '<td>';
				echo "<span style=\"background:white;color:black;\">" , $hit['hitDisplay'], "</span>";			
				echo '</td>';
				echo '<td>';
				echo $hit['score'];
				echo '</td>';
				echo '<td>';
				echo '<a href="http://journalseek.net/cgi-bin/journalseek/journalsearch.cgi?field=issn&query=' . $hit['issn'] . '" target="_blank">' . $hit['issn'] . '</a>';
				echo '</td>';
				echo '</tr>';
			}		
			echo '</table>';
		}

		if (count($hits) > 0)
		{
			// Do we have a hit (above some threshhold)
			if ($hits[0]['score'] >= $threshold)
			{
				$issn = $hits[0]['issn'];
			}
		}


	}

	return $issn;
}
	
//--------------------------------------------------------------------------------------------------
// Check if metadata includes an ISSN, if not get it
function check_for_missing_issn(&$metadata)
{
	if (!array_key_exists('issn', $metadata))
	{
		if (array_key_exists('title', $metadata))
		{
			$issn = issn_from_journal_title($metadata['title']);
			if ($issn != '')
			{
				$metadata['issn'] = $issn;
			}
		}
	}
}

//--------------------------------------------------------------------------------------------------
// Find a journal title from ISSN
// return longest name as best guess
function journal_title_from_issn($issn, $language_code = 'en')
{
	global $db;
	
	$title = '';
	
	//	Format ISSN	
	$clean = ISN_clean($issn);
	$class = ISSN_classifier($clean);
	if ($class == "checksumOK")
	{
		$issn = canonical_ISSN($issn);
	
		$sql = 'SELECT * FROM issn WHERE (issn = ' . $db->Quote($issn) . ') 
		AND (language_code=' . $db->Quote($language_code) . ') ORDER BY LENGTH(title) DESC LIMIT 1';
	

		$result = $db->Execute($sql);
		if ($result == false) die("failed: " . $sql);
	
		if ($result->NumRows() == 1)
		{
			$title = $result->fields['title'];
		}
	}
	
	return $title;
}
	
	
// test	

//echo issn_from_journal_title('Proc R Soc Lond B Biol Sci');
	
?>