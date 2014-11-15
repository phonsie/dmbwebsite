<?php

	define('INCLUDE_CHECK',true);

	require('../dmbConfig/config.php');
	require 'lib/functions.php';
	require 'lib/db.php';

	initSession();

	setHeader("Shows");

	$db = new Database($db_config);
	if(!$db->init()) throw new Exception($db->get_error());

	$memberID = isset($_SESSION['id']) ? $_SESSION['id'] : 0;
	$searchYear = isset($_GET["year"]) ? "`year` = '".$_GET["year"]."' AND" : "";
	$ArtistIDWhere = isset($_GET["artistID"]) ? "`ArtistID` = '".$_GET["artistID"]."' AND" : "";

	$showYears = true;
	if(isset($_GET["parseError"])) {$showYears = false;}
	if(isset($_GET["TaperID"])) {$showYears = false;}
	if(isset($_GET["TNID"])) {$showYears = false;}
	if(isset($_GET["VenueID"])) {$showYears = false;}

	$showArtists = true;
	if(isset($_GET["parseError"])) {$showArtists = false;}
	if(isset($_GET["TaperID"])) {$showArtists = false;}
	if(isset($_GET['TNID'])) {$showArtists = false;}
	if(isset($_GET['VenueID'])) {$showArtists = false;}

	if(isset($_SESSION['id']))
	{
		$MySources = getMySources($db,$searchYear,$ArtistIDWhere,$memberID);
	}

	// User wants to see all shows where a particular track has been played
	if(isset($_GET["TNID"]))
	{
		// Get all sourceIDs for all the shows for a given track name
		$AllSources = getSourcesGeneric($db,
										array("shows", "showsources", "tracks", "tracknames"),
										"WHERE `shows`.`SID` = `showsources`.`ShowID` AND
										`showsources`.`SSID` = `tracks`.`ShowSourceID` AND
										`tracks`.`NameID` = '".$_GET["TNID"]."' AND
										`tracknames`.`TNID` = '".$_GET["TNID"]."'
										");

		$ArtistIDWhere = "";
	}
	else if	(isset($_GET["TaperID"]))
	{
		// Get all sourceIDs for all the shows for a given taper
		$AllSources = getSourcesGeneric($db,
										array("shows", "showsources", "sourceinfo"),
										"WHERE `shows`.`SID` = `showsources`.`ShowID` AND
										`showsources`.`SSID` = `sourceinfo`.`ShowSourceID` AND
										`sourceinfo`.`TaperID` = '".$_GET["TaperID"]."'
										");

		 $ArtistIDWhere = "";
	}
	else if	(isset($_GET["VenueID"]))
	{
		// Get all sourceIDs for all the shows for a given venue
		$AllSources = getSourcesGeneric($db,
										array("shows", "showsources", "venues" ),
										"WHERE `shows`.`SID` = `showsources`.`ShowID` AND
										`shows`.`VenueMD5` = `venues`.`VenueMD5` AND
										`venues`.`VenueMD5` = '".$_GET["VenueID"]."'
										");
		$ArtistIDWhere = "";
	}
	else
	{
		// Get all sourceIDs for all the shows for a given year and artist unless limited
		$AllSources = getSourcesGeneric($db,
										array("shows", "showsources"),
										"WHERE ".$searchYear." ".$ArtistIDWhere."
										`shows`.`SID` = `showsources`.`ShowID`
										");
	}

	$ShowDates = getShowDetails($db,$searchYear,$ArtistIDWhere,'');

	$SourcesWithTracks = getSourcesWithTracks($db,$searchYear);

	$sourcesOnDT = getDTLinks($db);

	$bannedSources = getBannedSources($db);
	// Get the taperAndType
	$tapersAndType = getTapersAndType($db);

	// Get all artistIDs
	$artistIDs = getArtistIDs($db);

	// Get all artists' short names
	$artistShortNames = getArtistShortNames($db);

	if ($showArtists) {echo(outputArtists($artistIDs)."<br />");}

	// Show all years for chosen artist(s)
	if ($showYears) { $years = getYears($db); echo (outputYears($years));}

	if(isset($_GET["TNID"]))
	{
		$thisTrackName = getThisTrackName($db);
		if(isset($thisTrackName[$_GET["TNID"]][0]))
		{
			echo "<br /><center><h1>".$thisTrackName[$_GET["TNID"]][0]." (".count($AllSources).")</h1></center>";
		}
		else
		{
			echo "<br /><center><h1>No details found for track name with ID ".$_GET["TNID"]."!</h1></center>";
		}
	}

	if(isset($_GET["TaperID"]))
	{
		$thisTaperName = getThisTaperName($db);
		echo "<br /><center><h1>".$thisTaperName[$_GET["TaperID"]][0]." (".count($AllSources).")</h1></center>";
	}

	if(isset($_GET["VenueID"]))
	{
		$thisVenueName = getVenueName($db,$_GET["VenueID"]);
		echo "<br /><center><h1>".$thisVenueName[$_GET["VenueID"]][0]." (".count($AllSources).")</h1></center>";
	}

	if(empty($ShowDates))
	{
		echo "<br /><center><h1>There are no known sources for ".$artistIDs[$_GET["artistID"]][0]." in ".$_GET["year"]."</h1></center><br />";
	}
	else
	{
		setShowsTableHeader();

	foreach ($ShowDates as $thisShowDetails)
	{
		$oneRow = "";
		$showSourcesDetails = "";
		$rowBG = "FFFFFF";
		$showRow = true;
		if(isset($_GET["parseError"]))
		{
			$showRow = false;
		}

		// Does this show have any sources?
		if(isset($AllSources[$thisShowDetails["EtreeID"]]))
		{
			$showSourcesDetails .="<td>";
			foreach ( $AllSources[$thisShowDetails["EtreeID"]] as $thisSource )
			{
				$dtLink =  outputDTLink($sourcesOnDT,$bannedSources,$thisSource);

				if(isset($_GET["NotOnDT"]) && isset($sourcesOnDT[$thisSource]))
				{
					$showRow = false;
				}

				$mySourceCount = -1;
				$sourceCount = 0;
				if(isset($MySources[$thisSource]))
					$mySourceCount = $MySources[$thisSource][0];
				if(isset($SourcesWithTracks[$thisSource]))
					$sourceCount = $SourcesWithTracks[$thisSource][0];

				$showDiff = "";

				if ($sourceCount == 0)
				{
					$showRow = true;
					$class = "parseerror";
				}
				else
				{
					if ($sourceCount == $mySourceCount)
					{
						$class = "have";
						$rowBG = "33AA00";
					}
					else
					{
						if ($mySourceCount > 0)
						{
							$class = "hasissue";
							$rowBG = "FF0000";
							$showDiff = "&diff=1";
						}
						else
						{
							$class = "donthave";
						}
					}
				}
				$thisSourceOutput = "";

				$thisSourceOutput .= outputTaper($tapersAndType,$thisSource);

				if ($class === "parseerror")
				{
					$showSourcesDetails .= "<span class='".$class."'>";
					$showSourcesDetails .= $dtLink." ".str_pad($thisSource, 6, "0", STR_PAD_LEFT)."</span> ".$thisSourceOutput."ParseError";
					$showSourcesDetails .= "<br />\n";
				}
				else
				{
					$showSourcesDetails .= $dtLink." <a href='http://db.etree.org/shn/$thisSource' target='_blank'><img src='images/etree.jpg' alt='et'></a> <a href ='sourceDetails.php?sourceID=".$thisSource.$showDiff."'><span class='".$class."'>".str_pad($thisSource, 6, "0", STR_PAD_LEFT)."</span></a>";
					$showSourcesDetails .= $thisSourceOutput;
					$showSourcesDetails .= "<br />\n";
				}
			}
			$showSourcesDetails .= "</td>\n";
		}
		else
		{
			if(isset($_GET["TNID"]) || isset($_GET["TaperID"]) || isset($_GET["VenueID"]) || isset($_GET["NotOnDT"]) )
			{
				$showRow = false;
			}
			else
 			{
				$rowBG = "BBBBBB";
				$showSourcesDetails .= "<td>&nbsp;";
				$showSourcesDetails .= "</td>\n";
			}
		}

		if ($showRow)
		{
			$dayDate = sprintf('%02d',$thisShowDetails["day"]);
			$monthDate = sprintf('%02d',$thisShowDetails["month"]);
			$yearDate = $thisShowDetails["year"];

			$oneRow .="<tr>\n";
			$oneRow .="<td align='right'><a href='http://db.etree.org/lookup_show.php?shows_key=".$thisShowDetails["EtreeID"]."'>".$artistShortNames[$thisShowDetails["ArtistID"]][0].$yearDate."-".$monthDate."-".$dayDate."</a></td>\n";
			$oneRow .="<td bgcolor=#".$rowBG."><a href='shows.php?VenueID=".$thisShowDetails["VenueMD5"]."'><span class='venue'>".$thisShowDetails["VenueName"]."</span></a></td>\n";
			$oneRow .="<td bgcolor=#".$rowBG.">".$thisShowDetails["State"]."</td>\n";
			$oneRow .="<td bgcolor=#".$rowBG.">".$thisShowDetails["City"]."</td>\n";
			$oneRow .="<td bgcolor=#".$rowBG.">".$thisShowDetails["Country"]."</td>\n";

			echo $oneRow;

			echo $showSourcesDetails;

			echo "</tr>\n";
		}
	}

	echo "</table><br>\n";
	}

	// Show all years for chosen artist(s)
	if ($showYears) { echo ("<br>".outputYears($years));}

	if ($showArtists) {echo(outputArtists($artistIDs));}

	setFooter();
?>

