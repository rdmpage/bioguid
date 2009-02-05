<?php


class LongestCommonSequence
{
	var $left;
	var $right;
	var $C = array();
	var $X;
	var $Y;
	
	function LongestCommonSequence ($X, $Y)
	{
		$this->left = '';
		$this->right = '';
		$this->X = $X;
		$this->Y = $Y;
		
	
	}
	
	// from http://en.wikipedia.org/wiki/Longest_common_subsequence_problem
	function compare()
	{
		$m = strlen($this->X);
		$n = strlen($this->Y);
	
		for ($i = 0; $i <= $m; $i++)
		{
			$this->C[$i][0] = 0;
		}
		for ($j = 0; $j <= $n; $j++)
		{
			$this->C[0][$j] = 0;
		}
	
		for ($i = 1; $i <= $m; $i++)
		{
			for ($j = 1; $j <= $n; $j++)
			{
				if ($this->X{$i-1} == $this->Y{$j-1})
				{
					$this->C[$i][$j] = $this->C[$i-1][$j-1]+1;
				}
				else
				{
					$this->C[$i][$j] = max($this->C[$i][$j-1], $this->C[$i-1][$j]);
				}
			}
		}
	}	
	
	function score()
	{
		$this->compare();
		$this->printDiff($this->C, $this->X, $this->Y, strlen($this->X), strlen($this->Y));
		return $this->C[strlen($this->X)][strlen($this->Y)];
	}
	
	function display()
	{
		$html = '<div>' . $this->left . '<br/>' . $this->right . '</div>';
		return $html;
	}
	
		
	function printDiff($C, $X, $Y, $i, $j)
	{
		if (($i > 0) and ($j > 0) and ($X{$i-1} == $Y{$j-1}))
		{
			$this->printDiff($C, $X, $Y, $i-1, $j-1);
			//echo "  " , $X{$i-1};
	
			$this->left .= "<span style=\"background:rgb(100,255,100);color:black;\">" . $X{$i-1} . "</span>";
			$this->right .= "<span style=\"background:rgb(100,255,100);color:black;\">" . $X{$i-1} . "</span>";
		   }
		else
		{
			if (($j > 0) and ($i == 0 or $C[$i][$j-1] >= $C[$i-1][$j]))
			{
				$this->printDiff($C, $X, $Y, $i, $j-1);
				//echo "+ " , $Y{$j-1};
	
				$this->right .= $Y{$j-1};
			}
			else 
			{
				if (($i > 0) and ($j == 0 or $C[$i][$j-1] < $C[$i-1][$j]))
				{
					$this->printDiff($C, $X, $Y, $i-1, $j);
					//echo "- " , $X{$i-1};
		
					$this->left .= $X{$i-1};
				}
			}
		}
	}
}


// test

$s1 = 'hello blue marine';
$s2 = 'yellow blue submarine';

$lcs = new LongestCommonSequence($s1,$s2);

echo $lcs->score();

echo $lcs->display();


?>