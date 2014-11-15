<?php

	define('INCLUDE_CHECK',true);

	require '../dmbConfig/config.php';
	require 'lib/functions.php';
	require 'lib/db.php';

	initSession();

	setHeader("BLANK_TEMPLATE");

	$db = new Database($db_config);
	if(!$db->init()) throw new Exception($db->get_error());

	
	setFooter();
?>