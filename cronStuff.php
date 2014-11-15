<?php

	define('INCLUDE_CHECK',true);

	require '../dmbConfig/configPower.php';
	require 'lib/functions.php';
	require 'lib/db.php';

	initSession();

	setHeader("CRON");
	
	$db = new Database($db_config);
	if(!$db->init()) throw new Exception($db->get_error());

	$IDs = array();
	
	for ($i=0; $i<=2; $i++) 
	{
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, 'http://www.dreamingtree.org/log.php?page='.$i);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_COOKIE, 'uid='.$UID.'; pass='.$Passkey);
		
		$result = curl_exec($ch);
		
		$html = new DOMDocument();
		@$html->loadHtml($result);
		$xpath = new DOMXPath( $html );
		$logLines = $xpath->query( "//tr[@class='tableb']//td[3]" );
		foreach ($logLines as $oneLogLine)
		{
			if ($oneLogLine->hasChildNodes())
			{		
				foreach($oneLogLine->childNodes as $oneLogLineComment)
				{
					$DeleteLogLine = null;
					if (strpos($oneLogLineComment->nodeValue,'was deleted by') !== false) 
					{
						//echo "Deleted<br>";
						//echo $oneLogLineComment->nodeValue."<br>";	
						$DeleteLogLine = $oneLogLineComment->nodeValue;
					}
					else if (strpos($oneLogLineComment->nodeValue,'was uploaded by') !== false) 
					{
						//echo "Uploaded<br>";
					}
					else if (strpos($oneLogLineComment->nodeValue,'was edited by') !== false) 
					{
						//echo "Edited<br>";
					}
					else if (strpos($oneLogLineComment->nodeValue,'No Seeders For 30 days!') !== false) 
					{
						//echo "Deleted<br>";
						//echo $oneLogLineComment->nodeValue."<br>";
						$DeleteLogLine = $oneLogLineComment->nodeValue;						
					}
					else
					{
						echo "Unknown";
					}								
					
					if(!empty($DeleteLogLine))
					{
						$IDs[] =  strstr(str_replace("Torrent ", "", $DeleteLogLine), " ", TRUE);
					}
				}	
			}				
		}	
	}
	print_r ($IDs);
	
	setFooter();
?>