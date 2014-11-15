<?php

	define('INCLUDE_CHECK',true);

	require('../dmbConfig/config.php');
	require 'lib/functions.php';
	require 'lib/db.php';

	initSession();

	setHeader("Overview");
	
	$memberID = isset($_SESSION['id']) ? $_SESSION['id'] : 0;
	
	$db = new Database($db_config);
	if(!$db->init()) throw new Exception($db->get_error());
	
	// Get all artistIDs
	$artistIDs = getArtistIDs($db);		
	
	// Get all artists' short names
	$artistShortNames = getArtistShortNames($db);		
	
	$ShowsCountByArtistAndYear = getShowsCountByArtistAndYear($db);				
		
	foreach ($ShowsCountByArtistAndYear as $countForThisYear)
	{
		$thisKey = $countForThisYear['Year']."_".$countForThisYear['ArtistID'];
		$CountForYearArtist[$thisKey] = $countForThisYear['Count'];		
	}
	
	if(isset($_SESSION['id']))
	{
		$MyShowsCountByArtistAndYear = getMyShowsCountByArtistAndYear($db,$memberID);
		
		foreach ($MyShowsCountByArtistAndYear as $myCountForThisYear)
		{
			$thisKey = $myCountForThisYear['Year']."_".$myCountForThisYear['ArtistID'];
			$MyCountForYearArtist[$thisKey] = $myCountForThisYear['MyCount'];		
		}
	}	

	$outputNewLine = "\r\n";
	
	echo "<center><table border='1' cellpadding='5' cellspacing='1' >".$outputNewLine;
	echo "<tr>".$outputNewLine;			
	echo "<th>Year</th>".$outputNewLine;
	foreach ($artistIDs as $oneArtistID => $artistName)
	{
		echo "<th>".$artistName[0]."</th>".$outputNewLine;
	}
	if(isset($_SESSION['id']))
	{
		echo "<th>%</th>".$outputNewLine;
	}
	echo "</tr>".$outputNewLine;
	
	$years = getYears($db);
	
	foreach ($years as $oneYear)
	{	
		if ($oneYear["year"] != 1939)
		{		
			echo "<tr align='center'>".$outputNewLine;
			echo "<td><b><a href='shows.php?year=".$oneYear["year"]."'>".$oneYear["year"]."</a></b></td>".$outputNewLine;
			
			$totalCount = 0;
			$myTotalCount = 0;
		
			foreach ($artistIDs as $oneArtistID => $artistName)				
			{
				if(isset($CountForYearArtist[$oneYear["year"]."_".$oneArtistID]))
				{
					$myCount = 0;
					$actualCount = $CountForYearArtist[$oneYear["year"]."_".$oneArtistID];
					$totalCount += $actualCount;
					if(isset($MyCountForYearArtist[$oneYear["year"]."_".$oneArtistID]))
					{
						$myCount = $MyCountForYearArtist[$oneYear["year"]."_".$oneArtistID];
						$myTotalCount += $myCount;
					}
		
					if($myCount == $actualCount)
					{
						echo "<td bgcolor='#33AA00'>";
					}
					else	
					{
						echo "<td>";				
					}
					
					if(isset($_SESSION['id']))
					{
						echo $myCount."/";
					}
					echo $actualCount;
					
					if(isset($_SESSION['id']) && $myCount != $actualCount)
					{	
						echo " (".($actualCount - $myCount).")";
					}
					
					echo "</td>".$outputNewLine;
				}
				else
				{
					if(isset($_SESSION['id']))
					{
						echo "<td bgcolor='#33AA00'>&nbsp;</td>".$outputNewLine;
					}
					else
					{
						echo "<td>&nbsp;</td>".$outputNewLine;				
					}
				}				
			}
				
			if(isset($_SESSION['id']))
			{
				if($myTotalCount == $totalCount)
				{
					echo "<td bgcolor='#33AA00'>";
				}
				else	
				{
					echo "<td>";				
				}
					
				echo sprintf("%.2f%%", $myTotalCount / $totalCount * 100);								
				echo "</td>".$outputNewLine;
			}				
			echo "</tr>".$outputNewLine;
		}
	}
	echo "</table></center>".$outputNewLine;
	
	setFooter();	
?>