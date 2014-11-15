<?php

	define('INCLUDE_CHECK',true);

	require('../dmbConfig/config.php');
	require 'lib/functions.php';
	require 'lib/db.php';

	initSession();

	setHeader("Result of File Upload");
		
	$allowedExts = array("csv");
	$temp = explode(".", $_FILES["file"]["name"]);
	$extension = end($temp);
		
	if ( $_FILES["file"]["size"] < 2048000 && in_array($extension, $allowedExts) )
	{
		if ($_FILES["file"]["error"] > 0)
		{
			echo "Return Code: " . $_FILES["file"]["error"] . "<br>";
		}
		else
		{
			if (file_exists($UploadDirectory . $_FILES["file"]["name"]))
			{
				echo $_FILES["file"]["name"] . " already exists.";
			}
			else
			{
				move_uploaded_file($_FILES["file"]["tmp_name"],
				$UploadDirectory."/".$_FILES["file"]["name"]);
				echo "Your file " . $_FILES["file"]["name"] . " has been uploaded successfully. Your sources will be added shortly. Please check back in 5 minutes";
			}
		}
	}
	else
	{
		echo "Invalid file. Is it too big?";
		echo "Size: " . ($_FILES["file"]["size"] / 1024) . " kB<br>";		
	}
	setFooter();	
?>