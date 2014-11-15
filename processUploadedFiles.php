<?php

	class source {
		public $MemberID;
		public $MD5ID;
		public $Duration;
		public $Checksum;		
	}
	
	define('INCLUDE_CHECK',true);

	require('../dmbConfig/configPower.php');
	require 'lib/functions.php';
	require 'lib/db.php';

	$db = new Database($db_config);
	if(!$db->init()) throw new Exception($db->get_error());
			
	$d = dir($UploadDirectory) or die($php_errormsg);
	
	while (false !== ($f = $d->read())) 
	{
		if (is_file($d->path.'/'.$f)) 
		{
			if (($fileHandle = fopen($d->path.'/'.$f, "r")) !== FALSE) 
			{
				$row = 0;
				while (($data = fgetcsv($fileHandle, 0, ";")) !== FALSE) 
				{
					$row++;
					$num = count($data);
					if($num === 4)
					{
						$thisSource = new source;
						$thisSource->MemberID 	= $data[0];
						$thisSource->MD5ID 		= $data[1];
						$thisSource->Duration 	= $data[2];
						$thisSource->Checksum 	= $data[3];						
	
						// Only get the Member Secret once
						// Ensure there's no header in the file!
						if ($row < 2 )
						{		
							$MemberSecretArr = getMemberSecret($db, $thisSource->MemberID );
							$MemberSecret = $MemberSecretArr[$thisSource->MemberID][0];	
						}
						
						if ( md5($secretSecret . $MemberSecret . $thisSource->MD5ID) == $thisSource->Checksum)
						{
							//echo "Checksum passed!!\r\n";	
							$db->insertPDO("mytracks", array("MID" => $thisSource->MemberID, "MD5ID" => $thisSource->MD5ID, "AddedTime" => time()));
										
						}
						else
						{
							echo "Failed checksum!!\r\n";
						}										
					}					
					else
					{
						echo "Error in line".$num."\r\n";
					}					
				}
			}
			fclose($fileHandle);
			
			ExceptionThrower::Start();
			try 
			{			
				rename( $UploadDirectory.$f, $UploadDirectory."Processed/".$f);		
			}
			catch (Exception $e) 
			{
				//echo 'Caught exception: ',  $e->getMessage(), "\n";
			}
			ExceptionThrower::Stop();			
		}
	}
	$d->close();
?>