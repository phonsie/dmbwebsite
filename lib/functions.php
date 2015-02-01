<?php

if(!defined('INCLUDE_CHECK')) die('You are not allowed to execute this file directly');

class ExceptionThrower
{
	static $IGNORE_DEPRECATED = true;
	/**
	 * Start redirecting PHP errors
	 * @param int $level PHP Error level to catch (Default = E_ALL & ~E_DEPRECATED)
	 */
	static function Start($level = null)
	{
		if ($level == null)
		{
			if (defined("E_DEPRECATED"))
			{
				$level = E_ALL & ~E_DEPRECATED ;
			}
			else
			{
				// php 5.2 and earlier don't support E_DEPRECATED
				$level = E_ALL;
				self::$IGNORE_DEPRECATED = true;
			}
		}
		set_error_handler(array("ExceptionThrower", "HandleError"), $level);
	}

	/**
	 * Stop redirecting PHP errors
	 */
	static function Stop()
	{
		restore_error_handler();
	}

	/**
	 * Fired by the PHP error handler function.  Calling this function will
	 * always throw an exception unless error_reporting == 0.  If the
	 * PHP command is called with @ preceeding it, then it will be ignored
	 * here as well.
	 *
	 * @param string $code
	 * @param string $string
	 * @param string $file
	 * @param string $line
	 * @param string $context
	 */
	static function HandleError($code, $string, $file, $line, $context)
	{
		// ignore supressed errors
		if (error_reporting() == 0) return;
		if (self::$IGNORE_DEPRECATED && strpos($string,"deprecated") === true) return true;

		throw new Exception($string,$code);
	}
}

function checkEmail($str)
{
	return preg_match("/^[\.A-z0-9_\-\+]+[@][A-z0-9_\-]+([.][A-z0-9_\-]+)+[A-z]{1,4}$/", $str);
}

function checkIfMD5Exists($db,$id)
{
	$db->select('*')
		->from("md5s")
		->where('MD5', $id)
		->query();

	return($db->fetch_all());
}

function checkIfTrackFileNameExists($db,$id)
{
	$db->select('*')
		->from("trackfilenames")
    ->whereASIS(" WHERE `FileName` = '".$id."'")
    ->query();

  //echo $db->get_query();

  return($db->fetch_all());
}

function checkIfTrackNameExists($db,$id)
{
	$db->select('*')
		->from("tracknames")
		->where('TrackName', $id)
		->query();

	return($db->fetch_all());
}

function initSession()
{
	session_name('phdmbmd5s');
	session_set_cookie_params(2*7*24*60*60);
	session_start();
}

function getArtistIDs($db)
{
	// Get all artists
	$db->select('AID, Artist')
	   ->from(array("artists"))
	   ->query();

	return($db->fetch_assoc());
}

function getArtistShortNames($db)
{
	$db->select('AID, ShortName')
	   ->from(array("artists"))
	   ->query();

	return($db->fetch_assoc());
}

function getBannedSources($db)
{
	$db->select('SourceID,Reason')
	   ->from(array("bannedsources"))
     ->order_by("SourceID", "ASC")
	   ->query();

	$bannedSources = array();
	$data = $db->fetch_all();

	foreach ($data as $bannedSource)
	{
		$bannedSources[$bannedSource["SourceID"]] = $bannedSource;
	}
 	return($bannedSources);
}

function getDTLinks($db)
{
	// Get the details for all sources on DT
	$db->select('SourceID, DTID, Free, Name')
		->from(array("showsources", "dt" ))
		->whereASIS(" WHERE showsources.`SSID` = dt.`ShowSourceID`")
		->order_by(array("SourceID") , array("ASC"))
		->query();

	return($db->fetch_assoc_multi());
}

function getDTLinksForOneSource($db,$sourceID)
{
	$db->select('SourceID, DTID, Free, Name')
		->from(array("showsources", "dt" ))
		->whereASIS(" WHERE showsources.`SSID` = dt.`ShowSourceID` AND showsources.`SourceID` = '".$sourceID."'")
		->order_by(array("DTID") , array("ASC"))
		->query();

	return($db->fetch_all());
}

function getDTNotOnEtree($db)
{
	$db->select('SourceID, DTID, Free, Name')
		->from(array("showsources", "dt" ))
		->whereASIS(" WHERE showsources.`SSID` = dt.`ShowSourceID` AND showsources.`SourceID` = '999999999'")
		->order_by(array("DTID") , array("ASC"))
		->query();

	return($db->fetch_all());
}

function getExtraMD5s($db)
{
	$db->select('md5s.MD5ID,md5s.MD5')
		->from('md5s')
		->left_join_table_name("tracks")
		->on('tracks.MD5ID = md5s.MD5ID')
		->whereASIS(' WHERE Number IS NULL')
		->query();

	return($db->fetch_all());
}

function getExtraTrackFileNames($db)
{
	$db->select('trackfilenames.FNID,trackfilenames.FileName')
		->from('trackfilenames')
		->left_join_table_name("tracks")
		->on('trackfilenames.FNID = tracks.FileNameID')
		->whereASIS(' WHERE Number IS NULL')
		->query();

	return($db->fetch_all());
}

function getFNID($db, $FileName)
{
	$db->select('FNID,FileName')
		->from(array("trackfilenames"))
		->whereASIS(' WHERE `trackfilenames`.`FileName` = "'.$FileName."\"")
		->query();

	return($db->fetch_assoc());
}

function getLastDTID($db)
{
	$db->select('MAX(DTID) AS LastDT')
		->from("dt")
		->query();

	return($db->fetch_all());
}

function getMatchingMD5ID($db, $oneMD5)
{
	// Get the MD5ID which match the ones in myTracks
	$db->select('MD5ID, MID')
		->from(array("mytracks"))
		->whereASIS("WHERE `mytracks`.`MD5ID` in (".substr($oneMD5,1).")")
		->query();

	return($db->fetch_assoc());
}

function getMD5ID($db, $MD5String)
{
	$db->select('MD5ID,MD5')
		->from(array("md5s"))
		->whereASIS(' WHERE `md5s`.`MD5` = "'.$MD5String."\"")
		->query();

	return($db->fetch_assoc());
}

function getMemberID($db,$username,$pass)
{
	$db->select('MID,usr,secret')
		->from('members')
		->whereASIS(" WHERE `usr` = '".$username."' AND `pass` = '".$pass."'")
		->query();

	return($db->fetch_array());
}

function getMemberIDFromSecret($db,$secret)
{
	$db->select('MID')
		->from('members')
		->whereASIS(" WHERE `secret` = '".$secret."'")
		->query();

	//echo $db->get_query();

	return($db->fetch_array());
}

function getMembers($db)
{
  $db->select('usr, dt')
    ->from('members')
    ->order_by("usr", "ASC")
    ->query();

    return($db->fetch_assoc());
}

function getMemberSecret($db, $memberID)
{
	$db->select('MID, secret')
		->from(array("members"))
		->whereASIS(' WHERE `members`.`MID` = '.$memberID)
		->query();

	return($db->fetch_assoc());
}

function getMissingMD5s($db,$MD5)
{
		$MySourcesWhere = "WHERE `shows`.`SID` = `showsources`.`ShowID` AND
							`tracks`.`ShowSourceID` = `showsources`.`SSID` AND
							`tracks`.`MD5ID` = `md5s`.`MD5ID` AND
							`md5s`.`MD5` = '".$MD5."'";

		$db->select('SourceID, Count(ShowSourceID) AS Count')
		->from(array("shows", "showsources","tracks", "md5s" ))
		->whereASIS($MySourcesWhere)
		->group_by("ShowSourceID")
		->order_by("SourceID", "ASC")
		->query();

		return($db->fetch_assoc());
}

function getMyDTSources($db,$memberID)
{
		$MySourcesWhere = "WHERE `shows`.`SID` = `showsources`.`ShowID` AND
							`tracks`.`ShowSourceID` = `showsources`.`SSID` AND
							`dt`.`ShowSourceID` = `showsources`.`SSID` AND
							`tracks`.`MD5ID` = `mytracks`.`MD5ID` AND
							`mytracks`.`MID` = '".$memberID."'";

		// Get the ShowSourceID and count for all the sources a member has
		$db->select('SourceID, DTID')
			->from(array("shows", "showsources","tracks", "mytracks", "dt" ))
			->whereASIS($MySourcesWhere)
			->group_by("`tracks`.`ShowSourceID`")
			->order_by("SourceID", "ASC")
			->query();

		return($db->fetch_all());
}

function getMyRecentSources($db,$memberID,$AddedTime)
{
		$MySourcesWhere = "WHERE `shows`.`SID` = `showsources`.`ShowID` AND
							`tracks`.`ShowSourceID` = `showsources`.`SSID` AND
							`tracks`.`MD5ID` = `mytracks`.`MD5ID` AND
							`mytracks`.`MID` = '".$memberID."' AND
							`mytracks`.`AddedTime` > '".$AddedTime."'";

		$db->select('SourceID, Count(ShowSourceID) AS Count')
		->from(array("shows", "showsources","tracks", "mytracks" ))
		->whereASIS($MySourcesWhere)
		->group_by("ShowSourceID")
		->order_by("SourceID", "ASC")
		->query();

		//echo $db->get_query();

		return($db->fetch_assoc());
}

function getMyRecentSourcesCounts($db,$memberID,$limitSources)
{
		$MySourcesWhere = " WHERE `shows`.`SID` = `showsources`.`ShowID` AND
							`tracks`.`ShowSourceID` = `showsources`.`SSID` AND
							`tracks`.`MD5ID` = `mytracks`.`MD5ID` AND
							`mytracks`.`MID` = '".$memberID."' ".$limitSources;


		$db->select('MySources.SourceID, AllSources.Count, MySources.MyCount ')
		->from_open_bracket()
		->select('SID, SourceID, Count(ShowSourceID) AS MyCount',true)
		->from(array("shows", "showsources","tracks", "mytracks" ))
		->whereASIS($MySourcesWhere)
		->group_by("ShowSourceID")
		->order_by("SourceID", "ASC")
		->mysql_as('MySources')
		->join()
		->select('SourceID, Count(ShowSourceID) AS Count',true)
		->from(array("shows", "showsources","tracks"))
		->whereASIS(' WHERE `shows`.`SID` = `showsources`.`ShowID` AND `tracks`.`ShowSourceID` = `showsources`.`SSID` '.$limitSources)
		->group_by("ShowSourceID")
		->order_by("SourceID", "ASC")
		->mysql_as('AllSources')
		->on('MySources.SourceID = AllSources.SourceID')
		->query();

		return($db->fetch_assoc_multi());
}

function getMyShowsCountByArtistAndYear($db,$memberID)
{
	// Get a count of the number of shows by each artist each year.
	$db->select('Year, ArtistID, Count(a.ShowID) As MyCount')
		->from_open_bracket()
		->select('ShowID, Year, ArtistID',true)
		->from(array("shows","showsources","tracks","mytracks"))
		->whereASIS(" WHERE `shows`.`SID` = `showsources`.`ShowID` AND
					`tracks`.`ShowSourceID` = `showsources`.`SSID` AND
					`tracks`.`MD5ID` = `mytracks`.`MD5ID` AND
					`mytracks`.`MID` = '".$memberID."'")
		->group_by("ShowID")
		->mysql_as('a')
		->inner_join()
		->select('ShowID, SSID',true)
		->from(array("showsources"))
		->group_by("ShowID")
		->mysql_as('UniqueShowID')
		->on("a.ShowID = UniqueShowID.ShowID")
		->group_by(array("a.Year, a.ArtistID"))
		->query();

	return($db->fetch_all());
}

function getMySources($db,$searchYear,$ArtistIDWhere,$memberID)
{
		$MySourcesWhere = "WHERE ".$searchYear."
							".$ArtistIDWhere."
							`shows`.`SID` = `showsources`.`ShowID` AND
							`tracks`.`ShowSourceID` = `showsources`.`SSID` AND
							`tracks`.`MD5ID` = `mytracks`.`MD5ID` AND
							`mytracks`.`MID` = '".$memberID."'";

		// Get the ShowSourceID and count for all the sources a member has
		$db->select('SourceID, Count(ShowSourceID) AS Count')
			->from(array("shows", "showsources","tracks", "mytracks" ))
			->whereASIS($MySourcesWhere)
			->group_by("ShowSourceID")
			->order_by("SourceID", "ASC")
			->query();

		return($db->fetch_assoc());
}

function getMySourcesForEtreeImport($db,$memberID,$AddedTime)
{
		//`sourceinfo`.`TaperID` = `taper`.`TPID` AND
		// `sourceinfo`.`ShowSourceID` = `showsources`.`SSID` AND

		$MySourcesWhere = "WHERE `shows`.`SID` = `showsources`.`ShowID` AND
							`sourceinfo`.`ShowSourceID` = `showsources`.`SSID` AND
							`tracks`.`ShowSourceID` = `showsources`.`SSID` AND
							`tracks`.`MD5ID` = `mytracks`.`MD5ID` AND
							`shows`.`ArtistID` = `artists`.`AID` AND
							`sourceinfo`.`TaperID` = `taper`.`TPID` AND
							`mytracks`.`AddedTime` > '".$AddedTime."' AND
							`mytracks`.`MID` = '".$memberID."'";

		// Get the ShowSourceID and count for all the sources a member has
		//, "sourceinfo" ))
		$db->select('Artist, Year, Month, Day, taper.Name As Taper, SourceID')
			->from(array("shows", "showsources", "tracks", "mytracks", "artists" , "sourceinfo", "taper"))
			->whereASIS($MySourcesWhere)
			->group_by("sourceinfo.ShowSourceID")
			->order_by("SourceID", "ASC")
			->query();

		//echo $db->get_query();

		return($db->fetch_all());
}

function getMySourcesWithErrors($db,$memberID)
{
		$MySourcesWhere = " WHERE `shows`.`SID` = `showsources`.`ShowID` AND
							`tracks`.`ShowSourceID` = `showsources`.`SSID` AND
							`tracks`.`MD5ID` = `mytracks`.`MD5ID` AND
							`mytracks`.`MID` = '".$memberID."'";


		$db->select('MySources.SID, MySources.SourceID, AllSources.Count, MySources.MyCount ')
		->from_open_bracket()
		->select('SID, SourceID, Count(ShowSourceID) AS MyCount',true)
		->from(array("shows", "showsources","tracks", "mytracks" ))
		->whereASIS($MySourcesWhere)
		->group_by("ShowSourceID")
		->order_by("SourceID", "ASC")
		->mysql_as('MySources')
		->join()
		->select('SourceID, Count(ShowSourceID) AS Count',true)
		->from(array("shows", "showsources","tracks"))
		->whereASIS(' WHERE `shows`.`SID` = `showsources`.`ShowID` AND `tracks`.`ShowSourceID` = `showsources`.`SSID`')
		->group_by("ShowSourceID")
		->order_by("SourceID", "ASC")
		->mysql_as('AllSources')
		->on('MySources.SourceID = AllSources.SourceID')
		->whereASIS(' WHERE MySources.MyCount != AllSources.Count')
		->query();

		return($db->fetch_assoc_multi());
}

function getRelatedSources($db,$SourceID)
{
	// Get the SourceID
	$db->select('ShowID')
	->from(array("showsources"))
	->whereASIS("WHERE `showsources`.`SourceID` = ".$SourceID)
	->query();

	$ShowID = $db->fetch_all();

	if(isset($ShowID[0]["ShowID"]))
	{
		$db->select('SourceID')
		->from(array("showsources"))
		->whereASIS("WHERE `showsources`.`ShowID` = ".$ShowID[0]["ShowID"])
		->query();
		return($db->fetch_all());
	}
	else
	{
		return(null);
	}
}

function getRelatedSourcesOfSourcesWithErrors($db,$extraSources,$memberID)
{
	// Get the SourceID and count of ShowSourceID for all sources
	$db->select('SourceID, Count(ShowSourceID) AS Count')
	->from(array("shows","showsources","tracks","mytracks"))
	->whereASIS("WHERE `shows`.`SID` = `showsources`.`ShowID` AND
						`tracks`.`ShowSourceID` = `showsources`.`SSID` AND
						`tracks`.`MD5ID` = `mytracks`.`MD5ID` AND
						`mytracks`.`MID` = '".$memberID."' ".$extraSources )
	->group_by("ShowSourceID")
	->query();
	return($db->fetch_assoc_multi());
}

function getShowDetails($db,$searchYear,$ArtistIDWhere,$limitSources)
{
	$db->select('month, day, year, VenueName, venues.`VenueMD5`, State, City, Country, EtreeID, ArtistID')
		->from(array("shows", "venues","cities","countries"))
		->whereASIS(" WHERE ".$searchYear."
					".$ArtistIDWhere."
					venues.`VenueMD5` = shows.`VenueMD5` AND
					venues.`CityID` = cities.`CIID` AND
					venues.`CountryID` = countries.`COID`  ".$limitSources)
		->order_by(array("shows`.`year","shows`.`Month", "shows`.`Day") , array("ASC","ASC","ASC"))
		->query();

	return($db->fetch_all());
}

function getShowDetailsForRecentlyAddedShows($db,$limitSources)
{
	$db->select('month, day, year, VenueName, State, City, Country, EtreeID, ArtistID,SourceID')
		->from(array("shows", "venues","cities","countries","showsources" ))
		->whereASIS(" WHERE venues.`VenueMD5` = shows.`VenueMD5` AND
						venues.`CityID` = cities.`CIID` AND
						venues.`CountryID` = countries.`COID` AND
						`shows`.`SID` = `showsources`.`ShowID` ".$limitSources)
		->order_by(array("shows`.`year","shows`.`Month", "shows`.`Day") , array("ASC","ASC","ASC"))
		->query();

	return($db->fetch_all());
}

function getShowDetailsWithMissingMD5s($db,$limitSources)
{
	$db->select('month, day, year, VenueName, State, City, Country, EtreeID, ArtistID,SourceID')
		->from(array("shows", "venues","cities","countries","showsources" ))
		->whereASIS(" WHERE venues.`VenueMD5` = shows.`VenueMD5` AND
						venues.`CityID` = cities.`CIID` AND
						venues.`CountryID` = countries.`COID` AND
						`shows`.`SID` = `showsources`.`ShowID` ".$limitSources)
		->order_by(array("shows`.`year","shows`.`Month", "shows`.`Day") , array("ASC","ASC","ASC"))
		->query();
	return($db->fetch_all());
}

function getShowsCountByArtistAndYear($db)
{
	// Get a count of the number of shows by each artist each year.
	$db->select('shows.Year, Count(EtreeID) As Count, ArtistID')
	->from(array("shows"))
	->join()
	->select('ShowID, SSID',true)
	->from(array("showsources"))
	->group_by("ShowID")
	->mysql_as('UniqueShowID')
	->whereASIS(" WHERE shows.`SID` = UniqueShowID.`ShowID`")
	->group_by(array("shows.Year, ArtistID"))
	->query();

	return($db->fetch_all());
}

function getSourceDetails($db,$sourceID)
{
	$whereStatement = "WHERE ";
	$whereStatement .= "showsources.`SourceID` = '".$sourceID."' AND ";
	$whereStatement .= "showsources.`SSID` = `tracks`.`ShowSourceID` AND ";
	$whereStatement .= "tracks.`NameID` = `tracknames`.`TNID` AND ";
	$whereStatement .= "tracks.`FileNameID` = `trackfilenames`.`FNID` AND ";
	$whereStatement .= "tracks.`MD5ID` = `md5s`.`MD5ID`";

	// Get all details for a certain source
	$db->select('TID, Number, TrackName, Length, TNID, FileName, MD5, tracks.MD5ID')
		->from(array("showsources", "tracks", "tracknames", "trackfilenames" , "md5s"))
		->whereASIS($whereStatement)
		->order_by("Number","ASC")
		->query();

	return($db->fetch_all());
}

function getSourcesGeneric($db,$tables,$where)
{
	$db->select('EtreeID, SourceID')
		->from($tables)
		->whereASIS($where)
		->order_by("EtreeID","ASC")
		->query();

	return($db->fetch_assoc());
}

function getSourcesWhichFailedToParse($db)
{
	$db->select('AllSources.SourceID')
		->from_open_bracket()
		->select('SourceID',true)
		->from(array("showsources"))
		->order_by("SourceID", "ASC")
		->mysql_as('AllSources')
		->left_join()
		->select('SourceID, Count(ShowSourceID) AS Count',true)
		->from(array("showsources","tracks"))
		->whereASIS(' WHERE `tracks`.`ShowSourceID` = `showsources`.`SSID`')
		->group_by("SourceID")
		->order_by("SourceID", "ASC")
		->mysql_as('SourcesWithMD5s')
		->on('AllSources.SourceID = SourcesWithMD5s.SourceID')
		->whereASIS(' WHERE Count IS NULL')
		->query();

	return($db->fetch_all());
}

function getSourcesWithNoMD5s($db)
{
	$db->select('AllSources.SourceID, SourcesWithMD5s.Count')
		->from_open_bracket()
		->select('SourceID',true)
		->from(array("showsources"))
		->order_by("SourceID", "ASC")
		->mysql_as('AllSources')
		->left_join()
		->select('SourceID, Count(ShowSourceID) AS Count',true)
		->from(array("showsources","tracks"))
		->whereASIS(' WHERE `tracks`.`ShowSourceID` = `showsources`.`SSID`')
		->group_by("SourceID")
		->order_by("SourceID", "ASC")
		->mysql_as('SourcesWithMD5s')
		->on('AllSources.SourceID = SourcesWithMD5s.SourceID')
		->whereASIS(' WHERE Count IS NULL')
		->query();

		return($db->fetch_assoc_multi());
}

function getSourcesWithNoTapers($db)
{
	$db->select('TaperMissing.SourceID')
		->from_open_bracket()
		->select('SourceID, SIID',true)
		->from('showsources')
		->left_join_table_name("sourceinfo")
		->on('showSources.SSID = sourceinfo.ShowSourceID')
		->order_by("SourceID", "DESC")
		->mysql_as('TaperMissing')
		->whereASIS(' WHERE SIID IS NULL')
		->query();

	return($db->fetch_all());
}

function getSourcesWithTracks($db,$searchYear)
{
	$db->select('SourceID, Count(ShowSourceID) AS Count')
		->from(array("shows", "showsources", "tracks"))
		->whereASIS("WHERE ".$searchYear."
					`shows`.`SID` = `showsources`.`ShowID` AND
					`tracks`.`ShowSourceID` = `showsources`.`SSID`")
		->group_by("ShowSourceID")
		->query();

	return($db->fetch_assoc());
}

function getTaperByName($db,$id)
{
	$db->select('*')
		->from("taper")
		->where('Name', $id)
		->query();

	return($db->fetch_all());
}

function getTapersAndType($db)
{
	// Get the tapers name
	$db->select('SourceID, Name, TPID, Type')
		->from(array("showsources", "sourceinfo", "taper" ))
		->whereASIS(" WHERE showsources.`SSID` = sourceinfo.`ShowSourceID` AND
							sourceinfo.`TaperID` = taper.`TPID`")
		->order_by(array("SourceID") , array("ASC"))
		->query();
	return($db->fetch_assoc_multi());
}

function getThisTaperName($db)
{
	$db->select('TPID, Name')
		->from(array("taper" ))
		->whereASIS("WHERE `taper`.`TPID` = '".$_GET["TaperID"]."'")
		->query();

	return($db->fetch_assoc());
}

function getThisTrackName($db)
{
	$db->select('TNID, TrackName')
		->from(array("tracknames" ))
		->whereASIS("WHERE `tracknames`.`TNID` = '".$_GET["TNID"]."'")
		->query();

	return($db->fetch_assoc());
}

function getTrackIDsFromFNID($db,$id)
{
	$db->select('TID')
		->from(array("tracks", "trackfilenames"))
		->whereASIS("WHERE `tracks`.`FileNameID` = `trackfilenames`.`FNID` AND `trackfilenames`.`FNID` = '".$id."'")
		->query();

	return($db->fetch_all());
}

function getTrackIDsFromNameID($db,$id)
{
	$db->select('TID')
		->from(array("tracks", "tracknames"))
		->whereASIS("WHERE `tracks`.`NameID` = `tracknames`.`TNID` AND `tracknames`.`TNID` = '".$id."'")
		->query();

		return($db->fetch_all());
}

function getTrackIDsFromShowSourceID($db,$id)
{
	$db->select('*')
		->from("tracks")
		->whereASIS("WHERE `tracks`.`ShowSourceID` = ".$id)
		->query();

		return($db->fetch_all());
}

function getVenueName($db,$VenueID)
{
	$db->select('VenueMD5, VenueName')
		->from(array("venues"))
		->whereASIS(" WHERE venues.`VenueMD5` = '".$VenueID."'")
		->query();

	return($db->fetch_assoc());
}

function getYears($db)
{
	$yearWhere = isset($_GET["artistID"]) ? "WHERE `ArtistID` = '".$_GET["artistID"]."'" : "";

	$db->select('DISTINCT year')
		->from(array("shows"))
		->whereASIS($yearWhere)
		->order_by("year", "ASC")
		->query();

	return($db->fetch_all());
}

function outputArtists($artistIDs)
{
	$selectedArtistID = isset($_GET["artistID"]) ? $_GET["artistID"] : "";
	$AllArtistClass = isset($_GET["artistID"]) ? "UnSelectedArtist" : "SelectedArtist";

	$Year = isset($_GET["year"]) ? $_GET["year"] : "2014";

	$artistOutput = "<center><b><a href ='shows.php?year=".$Year."'><span class='".$AllArtistClass."'>All</span></a> | ";
	foreach ($artistIDs as $oneArtistID => $artistName)
	{
		$artistClass = $selectedArtistID == $oneArtistID ? "SelectedArtist" : "UnSelectedArtist";
		$artistOutput .= "<a href ='shows.php?artistID=".$oneArtistID."&year=".$Year."'><span class='".$artistClass."'>".$artistName[0]."</span></a> | ";
	}
	return($artistOutput .="</b></center>");
}

function outputDTLink($sourcesOnDT,$bannedSources,$thisSource)
{
	$dtLink = "";
	if(isset($sourcesOnDT[$thisSource]))
	{
		if($sourcesOnDT[$thisSource][0]["Free"] == 0)
		{
			$dtLink .= "<a target='_blank' href='http://www.dreamingtree.org/details.php?id=".$sourcesOnDT[$thisSource][0]["DTID"]."'><img src='images/dl.gif' alt='Download'></a>";
		}
		else
		{
			$dtLink .= "<a target='_blank' href='http://www.dreamingtree.org/details.php?id=".$sourcesOnDT[$thisSource][0]["DTID"]."'><img height='14' width='14' src='images/freedownload.png' alt='Free'></a>";
		}
	}
	else if (array_key_exists($thisSource, $bannedSources))
	{
		$dtLink .= "<a href='bannedSources.php'><img height='14' width='14' src='images/red_x.jpg' alt='Do not upload' title='".$bannedSources[$thisSource]["Reason"]."'></a>";
	}
	else
	{
		$dtLink .= "<img height='14' width='14' src='images/blank.jpg' alt='blank' >";
	}
  $dtLink .= $thisSource > 999999 ? "<img height='14' width='14' src='images/blank.jpg' alt='blank' >" : "<a href='http://db.etree.org/shn/$thisSource' target='_blank'><img src='images/etree.jpg' alt='et'></a>";
	return $dtLink;
}

function outputTaper($tapersAndType,$thisSource)
{
	$thisTaperOutput = "";
	if(isset($tapersAndType[$thisSource]))
	{
		if($tapersAndType[$thisSource][0]["Type"] == 2)
		{
			$thisTaperOutput = " 24-bit ";
		}
		else
		{
			$thisTaperOutput = " ";
		}
		$thisTaperOutput .= "<a href='shows.php?TaperID=".$tapersAndType[$thisSource][0]["TPID"]."'>";
		$thisTaperOutput .= "<span class='Taper'>".$tapersAndType[$thisSource][0]["Name"]."</span></a>";
	}
	return ($thisTaperOutput);
}

function outputYears($years)
{
	$selectedYear = isset($_GET["year"]) ? $_GET["year"] : "";

	$yearWhereURL = isset($_GET["artistID"]) ? "artistID=".$_GET["artistID"]."&" : "";
	$yearsOutput = "<center><b>";
	$yearsOutput .= isset($_GET["artistID"]) ? "<a href ='shows.php?artistID=".$_GET["artistID"]."'>All</a> | " : "";
	foreach ($years as $oneYear)
	{
		$yearClass = $selectedYear == $oneYear["year"] ? "SelectedYear" : "UnSelectedYear";
		$yearsOutput .= "<a href ='shows.php?".$yearWhereURL."year=".$oneYear["year"]."'><span class='".$yearClass."'>".$oneYear["year"]."</span></a> | ";
	}
	echo $yearsOutput .="</b></center>";
}

function setRecentImportsTableHeader()
{
?>
<br />
<table border="1" cellpadding="2" cellspacing="1" width='100%'>
	<tr>
		<th width="50">Date</th>
		<th width="200">VenueName</th>
		<th width="225">Torrent Name</th>
		<th width="200">Links</th>
	<tr>
<?php
}

function setShowsTableHeader()
{
?>
<br />
<table border="1" cellpadding="2" cellspacing="1" width='100%'>
	<tr>
		<th width="50">Date</th>
		<th width="200">VenueName</th>
		<th width="25">State</th>
		<th width="100">City</th>
		<th width="100">Country</th>
		<th width="200">Links</th>
	<tr>
<?php
}

function setHeader($pageTitle)
{
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title><?php echo $pageTitle; ?></title>
	<link rel="stylesheet" type="text/css" href="demo.css" media="screen" />
	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js"></script>
</head>

<body>
	<div class="pageContent">
		<div id="main">
			<!-- Menu -->
			<div class="container">
			Hello <?php echo isset($_SESSION['usr']) ? $_SESSION['usr'].", <a href='index.php?logoff'>Log off </a>" : 'Guest | <a href="login.php">Log in</a> | <a href="register.php">Register</a> ';?>
			| <a href="index.php">Home</a>
			| <a href="overview.php">Overview</a>
			| <a href="shows.php?artistID=6&year=2014">Shows</a>
			| <a href="md5Missing.php">Missing MD5s</a>
			<?php if(isset($_SESSION['usr'])) { echo "| <a href='import.php'>Import</a> ";} ?>
			<?php if(isset($_SESSION['usr'])) { echo "| <a href='errors.php'>My Shows With Errors</a> ";} ?>
			<?php if(isset($_SESSION['secret'])) { echo "| Secret: <b>".$_SESSION['secret']."</b>";} ?>
			<div class="clear"></div>
			</div>
			<!-- /Menu -->

			<div class="container">

	<?php
}

function setFooter()
{
	?>
			</div>
		</div>
	</div>
</body>
</html>
<?php
}
?>
