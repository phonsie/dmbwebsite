<?php

	define('INCLUDE_CHECK',true);

	require('../dmbConfig/config.php');
	require 'lib/functions.php';
	require 'lib/db.php';

	initSession();
	
	setHeader("Import your shows");

if(isset($_SESSION['id']))
{
	
	$db = new Database($db_config);
	if(!$db->init()) throw new Exception($db->get_error());
	
	$memberID = isset($_SESSION['id']) ? $_SESSION['id'] : 0;

	$AddedTime = isset($_GET['tid']) ? $_GET['tid'] : (time() - (1 * 24 * 60 * 60));
	
	$myRecentSources = getMyRecentSources($db,$memberID,$AddedTime);
		
	$limitSources = "AND `showsources`.`SourceID` in ( ";
	foreach ( $myRecentSources as $key => $value )
	{
		$limitSources .= $key." , ";			
	}		
	$limitSources = substr($limitSources, 0, -2).")";
	
	// 2. Get the details about the recent shows
	$RecentlyAddedShows = getShowDetailsForRecentlyAddedShows($db,$limitSources);
			
	$myRecentSourcesCounts = getMyRecentSourcesCounts($db,$memberID,$limitSources);
		
	$tapersAndType = getTapersAndType($db);
	$artistIDs = getArtistIDs($db);
	$artistShortNames = getArtistShortNames($db);		
	$sourcesOnDT = getDTLinks($db);
	
	//print_r($sourcesOnDT);
	
?>
	<h1><center>Import your shows</center></h1><br />
	If this is your first time follow the instruction <a href="how-to.php">here</a>
	<hr>	
	<form action="uploadFileResult.php" method="post" enctype="multipart/form-data">
		<input type="file" name="file" id="file">
		<input type="submit" name="submit" value="Submit">
	</form>
	<hr>
<?php
	
	echo "<br /><br /><h1><center>Recently Imported (".count($RecentlyAddedShows).")</center></h1><br /><br />";
	
	echo "<table width='100%'><tr>";
	echo "<td align='center'><a href='import.php?tid=".(time() - (1 *  1 * 15 * 60))."'>Within the last 15 minutes</a></td>";
	echo "<td align='center'><a href='import.php?tid=".(time() - (1 * 24 * 60 * 60))."'>Within the last 24 hours</a></td>";
	echo "<td align='center'><a href='import.php?tid=".(time() - (7 * 24 * 60 * 60))."'>Within the last 7 days</a></td>";
	echo "<td align='center'><a href='import.php?tid=".(time() - (30 * 24 * 60 * 60))."'>Within the last 30 days</a></td> ";
	echo "</tr></table>";
	
	if(count($RecentlyAddedShows) > 0)
	{
		echo "<hr>Click <a href='export.php?tid=".$AddedTime."'>here </a>";	
		echo "to download the list of shows below in .csv file which you can upload to db.etree.org.";
		echo "<br><b>Please note this will result in duplicates in your etree list if the shows are already listed there.</b><hr>";
		
		setRecentImportsTableHeader();
	
		foreach ($RecentlyAddedShows as $thisShowDetails)
		{
			$class = "none";		
			$showDiff = "";
			$showSourcesDetails = "<td>";

			$thisSource = $thisShowDetails["SourceID"];
			$myCount = ($myRecentSourcesCounts[$thisSource][0]["MyCount"]);
			$count = ($myRecentSourcesCounts[$thisShowDetails["SourceID"]][0]["Count"]);;
			
			if ($myCount == $count)
			{
				$rowBG = "33AA00";
				$textClass = "";
				$class = "have";
				$showDiff = "";
			}
			else
			{			
				$class = "hasissue";
				$showDiff = "&diff";
				$rowBG = "AA1100";			
				$textClass = "hasissueTD";						
				//$thisSourceLink = "<a href ='sourceDetails.php?sourceID=".$thisSource."&diff'><span class='".$class."'>".str_pad($thisSource, 6, "0", STR_PAD_LEFT)."</span></a>";
			}
							
			$taperOutput = outputTaper($tapersAndType,$thisSource);			
			$dtLink = outputDTLink($sourcesOnDT,$thisSource);	
			
			// 
			$showSourcesDetails .= $dtLink;			
			$showSourcesDetails .= "<a href ='sourceDetails.php?sourceID=".$thisSource.$showDiff."'><span class='".$class."'>".str_pad($thisSource, 6, "0", STR_PAD_LEFT)."</span></a>";
			$showSourcesDetails .= " ".$taperOutput;
			$showSourcesDetails .= "<br />\n";																	
				
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
			if(isset($sourcesOnDT[$thisSource]))
			{
				$oneRow .="<td bgcolor=#".$rowBG."><span class='".$textClass."'>".$sourcesOnDT[$thisSource][0]["Name"]."</span></td>\n";
			}		
			else
			{
				$oneRow .="<td bgcolor=#".$rowBG."><span class='".$textClass."'>-</span></td>\n";
			}
			echo $oneRow;
						
			echo $showSourcesDetails;
			
			echo "</tr>\n";
		}		
	}
	else
	{
		echo "<b><center><br>No etree sources added in that time!</center></b>";
	}			
}
else
{
?>
	Please <a href="login.php">log in</a>
<?php
}
	setFooter();
?>