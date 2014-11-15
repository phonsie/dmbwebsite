<?php

	define('INCLUDE_CHECK',true);

	require('../dmbConfig/config.php');
	require 'lib/functions.php';
	require 'lib/db.php';

	initSession();

	setHeader("Shows With Errors");

	$db = new Database($db_config);
	if(!$db->init()) throw new Exception($db->get_error());

	$memberID = isset($_SESSION['id']) ? $_SESSION['id'] : 0;

	// 1. Get shows which have errors
	$mySourcesWithErrors = getMySourcesWithErrors($db,$memberID);
		
	$limitSources = "AND `shows`.`SID` in ( ";
	foreach ( $mySourcesWithErrors as $key => $value )
	{
				$limitSources .= $key." , ";		
				$mySourceIDsWithErrors[$value[0]["SourceID"]] = 1;
	}		
	$limitSources = substr($limitSources, 0, -2).")";
		
	$ShowDates = getShowDetails($db,'','',$limitSources);	
	
	$AllSourcesOfShowsWithErrors = getSourcesGeneric($db, array("shows", "showsources"), "WHERE `shows`.`SID` = `showsources`.`ShowID` ".$limitSources);
		
	$extraSources = "AND `showsources`.`SourceID` in ( ";
	foreach ( $AllSourcesOfShowsWithErrors as $key => $value )
	{
		foreach ($value as $SourceID)
		{
			if(!isset($mySourceIDsWithErrors[$SourceID]))
			{
				$extraSources .= $SourceID." , ";
			}
		}		
	}		
	$extraSources = substr($extraSources, 0, -2).")";
		
	$myRelatedSources = getRelatedSourcesOfSourcesWithErrors($db,$extraSources,$memberID);		
		
	$tapersAndType = getTapersAndType($db);
	$artistIDs = getArtistIDs($db);
	$artistShortNames = getArtistShortNames($db);		
	$sourcesOnDT = getDTLinks($db);
	
	echo "<h1><center>Shows With Errors</center></h1>";
	
	if(count($ShowDates) > 0)
	{
		setShowsTableHeader();
	}
	else
	{
		echo "<b><center><br>Great! You've no shows which partially match on <a href='http://db.etree.org'>etree!</a></center></b>";
	}	
		
	foreach ($ShowDates as $thisShowDetails)
	{
		$class = "none";		
		$haveOneValidShow = false;
		$showDiff = "";
		$showSourcesDetails = "<td>";
		foreach ( $AllSourcesOfShowsWithErrors[$thisShowDetails["EtreeID"]] as $thisSource )
		{		
			if (isset($myRelatedSources[$thisSource]))
			{
				$haveOneValidShow = true;
				$class = "have";
				$showDiff = "";
				//$thisSourceLink = "<a href ='sourceDetails.php?sourceID=".$thisSource."'><span class='".$class."'>".str_pad($thisSource, 6, "0", STR_PAD_LEFT)."</span></a>";
			}
			else
			{
				if (isset($mySourceIDsWithErrors[$thisSource]))
				{
					$class = "hasissue";
					$showDiff = "&diff&showID";
					//$thisSourceLink = "<a href ='sourceDetails.php?sourceID=".$thisSource."&diff'><span class='".$class."'>".str_pad($thisSource, 6, "0", STR_PAD_LEFT)."</span></a>";
				}
				else
				{
					$class = "donthave";
					$showDiff = "";
					//$thisSourceLink = "<a href ='sourceDetails.php?sourceID=".$thisSource."'><span class='".$class."'>".str_pad($thisSource, 6, "0", STR_PAD_LEFT)."</span></a>";
				}
			}
						
			$taperOutput = outputTaper($tapersAndType,$thisSource);			
			$dtLink = outputDTLink($sourcesOnDT,$thisSource);	
			
			// 
			$showSourcesDetails .= $dtLink;			
			$showSourcesDetails .= "<a href ='sourceDetails.php?sourceID=".$thisSource.$showDiff."'><span class='".$class."'>".str_pad($thisSource, 6, "0", STR_PAD_LEFT)."</span></a>";
			$showSourcesDetails .= " ".$taperOutput;
			$showSourcesDetails .= "<br />\n";																	
		}	
		
		if ($haveOneValidShow)
		{
			$rowBG = "33AA00";
			$textClass = "";
		}
		else
		{
			$rowBG = "AA1100";			
			$textClass = "hasissueTD";					
		}
		
		$showSourcesDetails .= "</td>\n";
		
		$oneRow = "";
		
		$dayDate = sprintf('%02d',$thisShowDetails["day"]);
		$monthDate = sprintf('%02d',$thisShowDetails["month"]);
		$yearDate = $thisShowDetails["year"];			
		
		$oneRow .="<tr>\n";
		$oneRow .="<td align='right'><a href='http://db.etree.org/lookup_show.php?shows_key=".$thisShowDetails["EtreeID"]."'>";
		$oneRow .= $artistShortNames[$thisShowDetails["ArtistID"]][0].$yearDate."-".$monthDate."-".$dayDate;
		$oneRow .= "</a></td>\n";
		$oneRow .="<td bgcolor=#".$rowBG."><span class='".$textClass."'>".$thisShowDetails["VenueName"]."</span></td>\n";
		$oneRow .="<td bgcolor=#".$rowBG."><span class='".$textClass."'>".$thisShowDetails["State"]."</span></td>\n";
		$oneRow .="<td bgcolor=#".$rowBG."><span class='".$textClass."'>".$thisShowDetails["City"]."</span></td>\n";
		$oneRow .="<td bgcolor=#".$rowBG."><span class='".$textClass."'>".$thisShowDetails["Country"]."</span></td>\n";
		
		echo $oneRow;
					
		echo $showSourcesDetails;
		
		echo "</tr>\n";
	}
	setFooter();
?>