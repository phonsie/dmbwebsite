<?php

	define('INCLUDE_CHECK',true);

	require('../dmbConfig/config.php');
	require 'lib/functions.php';
	require 'lib/db.php';

	initSession();

	setHeader("Source Details");
    
	if (isset($_GET["sourceID"]))
	{
		$sourceID = (int)$_GET["sourceID"];
		
		$db = new Database($db_config);
		if(!$db->init()) throw new Exception($db->get_error());

		$TrackDetails = getSourceDetails($db,$sourceID);
			
		if (empty($TrackDetails))
		{
			echo "Source ".$sourceID." not found<br />";
		}
					
		if(substr($sourceID, 0, 3 ) != "362")
		{
			$EtreeLink = "<br />Etree link:<a href ='http://db.etree.org/shn/".substr($sourceID, -6)."' target='_blank'><span class='EtreeLink'>";
			$EtreeLink .= "http://db.etree.org/shn/".substr($sourceID, -6);
			$EtreeLink .= "</span></a>\n";									
	
			echo $EtreeLink ;
		}
				
		$sourcesOnDT = getDTLinksForOneSource($db,$sourceID);							
		
		$DTLink = "";
		
		foreach ($sourcesOnDT as $thisSourceOnDT )
		{
			$DTLink .= "<br />Dreaming Tree link:<a href ='http://www.dreamingtree.org/details.php?id=".$thisSourceOnDT["DTID"]."' target='_blank'><span class='DTLink'>";
			$DTLink .= $thisSourceOnDT["Name"];
			$DTLink .= "</span></a>\n";											
		}
		
		echo $DTLink;
		
		if (isset($_GET["diff"]))
		{			
			$oneMD5 = "";
			foreach ($TrackDetails as $thisTrackDetail)
			{				
				$oneMD5 .= ",\"".$thisTrackDetail["MD5ID"]."\"";
			}
			
			$MyMatchingMD5IDs = getMatchingMD5ID($db, $oneMD5);				
		}
		
		?>
			<br />
			<br />
			<table border="1" cellpadding="5" cellspacing="1" align="center">
		<?php
						
			foreach ($TrackDetails as $thisTrackDetail)
			{			
				$trackNameLink = "<a href ='shows.php?TNID=".$thisTrackDetail["TNID"]."' ><span class='TrackName'>";
				$trackNameLink .= $thisTrackDetail["TrackName"];
				$trackNameLink .= "</span></a>\n";	
				
				$oneRow ="<tr>\n";
				$oneRow .="<td>".$thisTrackDetail["Number"]."</td>\n";
				$oneRow .="<td>".$trackNameLink."</td>\n";
				$parts = explode(':', $thisTrackDetail["Length"]);
				$seconds = ($parts[0] * 3600) + ($parts[1] * 60) + $parts[2];
				//echo $seconds;
				if($seconds != 0)
				{
					$oneRow .="<td>".substr($thisTrackDetail["Length"],3)."</td>\n";
				}
				else
				{
					$oneRow .="<td>--:--</td>\n";
				}
				$oneRow .="<td>".$thisTrackDetail["FileName"]."</td>\n";
				
				if(substr($thisTrackDetail["MD5"],0,30) == 'bbbbbbbbbbbbbbbbbbbbbbbbbbbbbb')
				{
					$oneRow .="<td><span class='OneMD5Missing'>".$thisTrackDetail["MD5"]."</span></td>\n";
				}					
				else if(!isset($_GET["diff"]) || isset($MyMatchingMD5IDs[$thisTrackDetail["MD5ID"]]) )
				{
					$oneRow .="<td><span class='MD5'>".$thisTrackDetail["MD5"]."</span></td>\n";
				}
				else
				{
					$oneRow .="<td><span class='MD5NotMatched'>".$thisTrackDetail["MD5"]."</span></td>\n";					
				}
				if (isset($_GET["showID"]))
				{
					$oneRow .="<td>".$thisTrackDetail["TID"]."</td>\n";
				}
				$oneRow .="</tr>\n";
				echo $oneRow;	
			}						
		?>
			</table>					
		<?php
	}
	else
	{
		echo "No source chosen";
	}
	setFooter();	
?>    
    