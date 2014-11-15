<?php

	define('INCLUDE_CHECK',true);

	require('../dmbConfig/config.php');
	require 'lib/functions.php';
	require 'lib/db.php';

	initSession();

	setHeader("Etree Sources Missing MD5s");

	$db = new Database($db_config);
	if(!$db->init()) throw new Exception($db->get_error());

	$memberID = isset($_SESSION['id']) ? $_SESSION['id'] : 0;

	// 1. Get shows	with MD5s missing	
	if(isset($_GET["all"]))
	{
		$missingMD5 = 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa';
	}
	else if(isset($_GET["ffp"]))
	{
		$missingMD5 = 'cccccccccccccccccccccccccccccccc';
	}
	else
	{
		$missingMD5 = 'bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb';
	}
	 //isset($_GET["all"]) ? : isset($_GET["ffp"]) ? '' : '';
	$missingMD5s = getMissingMD5s($db,$missingMD5);
		
	$limitSources = "AND `showsources`.`SourceID` in ( ";
	foreach ( $missingMD5s as $key => $value )
	{
		$limitSources .= $key." , ";			
	}		
	$limitSources = substr($limitSources, 0, -2).")";
	
	$ShowDates = getShowDetailsWithMissingMD5s($db,$limitSources);
		
	$tapersAndType = getTapersAndType($db);
	$artistIDs = getArtistIDs($db);
	$artistShortNames = getArtistShortNames($db);		
	$sourcesOnDT = getDTLinks($db);
				
	if(count($ShowDates) > 0)
	{
		if(isset($_GET["all"]))
		{ 
			echo "<h1><center>Etree sources missing all MD5s</center></h1>";
		} 
		else if(isset($_GET["ffp"]))
		{ 
			echo "<h1><center>Etree flac sources missing all FFPs</center></h1>";
		} 
		else 
		{ 
			echo "<h1><center>Etree sources missing some MD5s</center></h1>";
		}
		echo "<br /><table width='100%'><tr>";
		echo "<td align='center'><a href='md5Missing.php?all'>All MD5s missing on Etree</a></td>";
		echo "<td align='center'><a href='md5Missing.php?ffp'>All FFPs missing on Etree</a></td>";
		echo "<td align='center'><a href='md5Missing.php'>Some MD5s missing on Etree</a></td>";
		echo "<td align='center'><a href='shows.php?TNID=9582'>Unclear from source text</a></td>";		
		echo "</tr></table>";
		setShowsTableHeader();
	}
	else
	{
		if(isset($_GET["all"]))
		{ 
			echo "<h1><center>No Etree sources missing all MD5s</center></h1>";
		} 
		else if(isset($_GET["ffp"]))
		{ 
			echo "<h1><center>No Etree sources missing FFPs</center></h1>";
		} 
		else 
		{ 
			echo "<h1><center>No Etree sources missing some MD5s</center></h1>";
		}
	}		
	
	foreach ($ShowDates as $thisShowDetails)
	{
		$showSourcesDetails = "<td>";

		$thisSource = $thisShowDetails["SourceID"];
						
		$taperOutput = outputTaper($tapersAndType,$thisSource);			
		$dtLink = outputDTLink($sourcesOnDT,$thisSource);	
		
		// 
		$showSourcesDetails .= $dtLink;			
		$showSourcesDetails .= "<a href ='sourceDetails.php?sourceID=".$thisSource."&showID'><span class='hasissue'>".str_pad($thisSource, 6, "0", STR_PAD_LEFT)."</span></a>";
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
		$oneRow .="<td>".$thisShowDetails["VenueName"]."</td>\n";
		$oneRow .="<td>".$thisShowDetails["State"]."</td>\n";
		$oneRow .="<td>".$thisShowDetails["City"]."</td>\n";
		$oneRow .="<td>".$thisShowDetails["Country"]."</td>\n";
		
		echo $oneRow;
					
		echo $showSourcesDetails;
		
		echo "</tr>\n";
	}
	echo "</table>\n";
	setFooter();
?>