<?php

	define('INCLUDE_CHECK',true);

	require '../dmbConfig/config.php';
	require 'lib/functions.php';
	require 'lib/db.php';

	initSession();

	setHeader("Sources not to be uploaded");

	$db = new Database($db_config);
	if(!$db->init()) throw new Exception($db->get_error());

  $bannedSources = getBannedSources($db);

  echo "<br /><br /><h1><center>Sources not to be uploaded (".count($bannedSources).")</center></h1><br /><br />";

  echo "<table align='center' width='50%'>";
  echo "<tr><td><b>Source ID</b></td>";
  echo "<td><b>Reason</b></td></tr>";

  foreach ($bannedSources as $key => $value)
  {
    echo "<tr><td><a href='http://db.etree.org/shn/$key'>$key</a></td>";
    echo "<td>".$value["Reason"]."</td></tr>";

  }
  echo "</table>";

	setFooter();
?>