<?php

	define('INCLUDE_CHECK',true);

	require '../dmbConfig/config.php';
	require 'lib/functions.php';
	require 'lib/db.php';

	initSession();

	setHeader("Members");

	$db = new Database($db_config);
	if(!$db->init()) throw new Exception($db->get_error());

  $members = getMembers($db);

  echo "<br /><br /><h1><center>Members (".count($members).")</center></h1><br /><br />";

  echo "<table align='center' width='50%'>";
  echo "<tr><td><b>Member Name</b></td>";
  echo "<td><b>Date Joined</b></td></tr>";

  foreach ($members as $key => $value)
  {
    echo "<tr><td>$key</td>";
    echo "<td>".$value[0]."</td></tr>";

  }
  echo "</table>";

	setFooter();
?>