<?php

	define('INCLUDE_CHECK',true);

	require('../dmbConfig/config.php');
	require 'lib/functions.php';
	require 'lib/db.php';

	initSession();	

	if(isset($_GET['logoff']))
	{
		$_SESSION = array();
		session_destroy();
		
		header("Location: index.php");
		exit;
	}
	
	setHeader("Welcome");
	
	echo "<h1><center>Welcome</center></h1>";
?>	
	<h2>1. Do you want to easily keep track of all the DMB-related sources you have?</h2>
	<p>You can do this in four steps: 
	<ul>
		<li>Register on this site if you haven't already done so.</li>
		<li>Install the program, as explained in the <a href="how-to.php">How-To</a>.</li>
		<li>Scan your (external) hard drive where the shows are stored.</li>
		<li>Upload the log file which the program creates <a href="inport.php">here</a>.
	</ul>
	</p><p>
	After about 5 minutes your sources will show up as green when you click on <a href="shows.php?artistID=6&year=2014">Shows</a> in the menu above.
	You can also use the <a href="overview.php">Overview</a> to see how many shows you have imported here for a given year. 
	</p><p>
	<h2>2. Are you looking for a particular show or source?</h2>
	<p>
	Click on <a href="shows.php?artistID=6&year=2014">Shows</a> in the menu above and in the sub menus choose the artist and year of 
	the show you're looking for. If it's available on <a href="http://www.dreamingtree.org">dreamingtree.org</a> you will see a familiar download 
	image in front of the given <a href="http://db.etree.org">etree</a> shnid.	
	</p>
	<hr>
	Other uses not yet developed include:
	<ul>
		<li>an alphabetic list of songs, venues, cities, tapers etc ( for the moment you can click on each of these when browsing the site)</li>
		<li>using the data to automatically name flac files in a format you like</li>
		<li>make the data available via an API so that anyone can use it as they wish
	</ul>
	<hr>
	<i>Please note the data in the database is not 100% cleaned up yet, so for some shows the taper might be listed as unknown or missing. 
	A venue may have a slightly different name in the files being imported so sometimes all shows for a particular venue won't show up when 
	you click on it.</i>
	<!--
	<h3>2. Are you looking for a particular song or venue?</h3>
	If you click on the venue name you will get a list of all known concerts played there. If you click on the taper name you will 
	get a list of all sources from this taper. If you click on the source details you can get a track listing. Click on a track name 
	and you will get a list of sources where that track was played. 
	-->	
<?php	
	setFooter();
?>