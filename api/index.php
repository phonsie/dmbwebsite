<?php

require('../../dmbConfig/config.php');
require('arrest-simple.php');

try 
{
	$headers = apache_request_headers();
	/**
	 * Note: You will need to provide a base_uri as the second param if this file 
	 * resides in a subfolder e.g. if the URL to this file is http://example.com/some/sub/folder/index.php
	 * then the base_uri should be "some/sub/folder"
	 */
	$arrest = new ArrestMySQL($db_config, $api_path);
	
	/**
	 * By default it is assumed that the primary key of a table is "id". If this
	 * is not the case then you can set a custom index by using the
	 * set_table_index($table, $field) method
	 */
	$arrest->set_table_index('artists', 'AID');
	$arrest->set_table_index('bannedsources', 'BSID');
	$arrest->set_table_index('cities', 'CIID');
	$arrest->set_table_index('countries', 'COID');	   
	$arrest->set_table_index('dt', 'DTID');	   
	$arrest->set_table_index('members', 'MID');	   
	$arrest->set_table_index('md5s', 'MD5ID');
	$arrest->set_table_index('mytracks', 'MID');
	/* Don't change these again ;) */
	$arrest->set_table_index('shows', 'SID');
	$arrest->set_table_index('showsources', 'SourceID');
	/* Don't change these again ;) */
	$arrest->set_table_index('sourceinfo', 'SIID');	    
	$arrest->set_table_index('taper', 'TPID');	    
	$arrest->set_table_index('trackfilenames', 'FNID');	     
	$arrest->set_table_index('tracknames', 'TNID');
	$arrest->set_table_index('tracks', 'TID');	     
	$arrest->set_table_index('venues', 'VenueMD5');
	$arrest->rest();		
} 
catch (Exception $e) 
{
    echo $e;
}

function IsAuthenticated() 
{
    if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
        $httpd_username = filter_var($_SERVER['PHP_AUTH_USER'], FILTER_SANITIZE_STRING, FILTER_FLAG_ENCODE_HIGH|FILTER_FLAG_ENCODE_LOW);
        $httpd_password = filter_var($_SERVER['PHP_AUTH_PW'], FILTER_SANITIZE_STRING, FILTER_FLAG_ENCODE_HIGH|FILTER_FLAG_ENCODE_LOW);
        if ($httpd_username == "api" && $httpd_password == "tester") {
            return TRUE;
        } else {
            return FALSE;
        }
    }
    return FALSE;
}
?>