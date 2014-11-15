<?php

	define('INCLUDE_CHECK',true);

	require('../dmbConfig/config.php');
	require 'lib/functions.php';
	require 'lib/db.php';

	$db = new Database($db_config);
	if(!$db->init()) throw new Exception($db->get_error());

	initSession();

	$memberID = isset($_SESSION['id']) ? $_SESSION['id'] : 0;

	$AddedTime = isset($_GET['tid']) ? $_GET['tid'] : (time() - (1 * 24 * 60 * 60));
	
	$rows = getMySourcesForEtreeImport($db,$memberID,$AddedTime);
	/*
	echo "ArtistName,ShowDate,ShnKey,MyTaperName<br/>";
	//print_r ($rows);
	foreach ($rows as $thisRow)
	{
		echo $thisRow["Artist"].",".$thisRow["Year"]."-".str_pad($thisRow["Month"], 2, "0", STR_PAD_LEFT)."-".str_pad($thisRow["Day"], 2, "0", STR_PAD_LEFT).",".$thisRow["SourceID"].",".$thisRow["Taper"]."<br/>";
	}
	*/
	
	// output headers so that the file is downloaded rather than displayed
	header('Content-Type: text/csv; charset=utf-8');
	header('Content-Disposition: attachment; filename=etree.Import.'.date("Y-m-d_H-i-s").'.csv');

	// create a file pointer connected to the output stream
	$output = fopen('php://output', 'w');

	
	// output the column headings
	fputcsv($output, array('ArtistName', 'ShowDate', 'ShnKey', 'MyTaperName'));
	
	foreach ($rows as $thisRow)
	{
		$thisOutputArray = array($thisRow["Artist"],$thisRow["Year"]."-".str_pad($thisRow["Month"], 2, "0", STR_PAD_LEFT)."-".str_pad($thisRow["Day"], 2, "0", STR_PAD_LEFT),$thisRow["SourceID"],$thisRow["Taper"]);
		fputcsv($output,$thisOutputArray);
	}	
?>