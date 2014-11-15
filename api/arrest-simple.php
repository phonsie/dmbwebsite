<?php
/**
 * Arrest MySQL
 * A "plug-n-play" RESTful API for your MySQL database.
 *
 * <code>
 * $arrest = new ArrestMySQL($db_config);
 * $arrest->rest();
 * </code>
 *
 * Author: Gilbert Pellegrom
 * Website: http://dev7studios.com
 * Date: Jan 2013
 * Version 1.1
 * Modified by Phonsie Hevey
 */
define('INCLUDE_CHECK',true); 
require('../lib/db.php');
require('../lib/functions.php');

class ArrestMySQL {

    /**
     * The instance of Database
     *
     * @var Database
     */
    private $db;
    /**
     * The structure of the database
     *
     * @var array
     */
    private $db_structure;
    /**
     * The URI segments
     *
     * @var array
     */
    private $segments;
    /**
     * Array of custom table indexes
     *
     * @var array
     */
    private $table_index;

    /**
     * Create an instance, optionally setting a base URI
     *
     * @param array $db_config An array of database config options. Format:
     * <code>
     * $db_config = array(
     *    'server'   => 'localhost',
     *    'database' => '',
     *    'username' => '',
     *    'password' => '',
     *    'verbose' => false
     * );
     *</code>
     * @param string $base_uri Optional base URI if not in root folder
     * @access public
     */
    public function __construct($db_config, $base_uri = '') 
    {
        $this->db = new Database($db_config);
        if(!$this->db->init()) throw new Exception($this->db->get_error());
        $this->db_structure = $this->map_db($db_config['database']);
        $this->segments = $this->get_uri_segments($base_uri);
        $this->table_index = array();
	}
    
    /**
     * Handle the REST calls and map them to corresponding CRUD
     *
     * @access public
     */
    public function rest()
    {
        header('Content-type: application/json');
        /*
        create > POST   /table
        read   > GET    /table[/id]
        update > PUT    /table/id
        delete > DELETE /table/id
        */
        switch ($_SERVER['REQUEST_METHOD']) 
		{
            case 'POST':
                break;
            case 'GET':
                $this->read();
                break;
            case 'PUT':
                break;
            case 'DELETE':
                break;
        }
    }
    
    /**
     * Add a custom index (usually primary key) for a table
     *
     * @param string $table Name of the table
     * @param string $field Name of the index field
     * @access public
     */
    public function set_table_index($table, $field)
    {
        $this->table_index[$table] = $field;
    }
    
    /**
     * Map the stucture of the MySQL db to an array
     *
     * @param string $database Name of the database
     * @return array Returns array of db structure
     * @access private
     */
    private function map_db($database)
    {
		// Map db structure to array
        $tables_arr = array();
        $this->db->query('SHOW TABLES FROM '. $database);
        while($table = $this->db->fetch_array())
		{           	        
        	if(isset($table['Tables_in_'. $database]))
			{        		
        	    $table_name = $table['Tables_in_'. $database];
        	    $tables_arr[$table_name] = array();
            }
        }
        	
        foreach($tables_arr as $table_name=>$val)
		{
    	    $this->db->query('SHOW COLUMNS FROM '. $table_name);
    	    $fields = $this->db->fetch_all();
    	    $tables_arr[$table_name] = $fields;
	    }	
	 	return $tables_arr;
	}
    
    /**
     * Get the URI segments from the URL
     *
     * @param string $base_uri Optional base URI if not in root folder
     * @return array Returns array of URI segments
     * @access private
     */
    private function get_uri_segments($base_uri)
    {
        // Fix REQUEST_URI if required
		if(!isset($_SERVER['REQUEST_URI']))
		{
            $_SERVER['REQUEST_URI'] = substr($_SERVER['PHP_SELF'], 1);
            if(isset($_SERVER['QUERY_STRING'])) $_SERVER['REQUEST_URI'] .= '?'. $_SERVER['QUERY_STRING'];
        }
        
    	$url = '';
    	$request_url = $_SERVER['REQUEST_URI'];
    	$script_url  = $_SERVER['PHP_SELF'];
    	$request_url = str_replace($base_uri, '', $request_url);
    	if($request_url != $script_url) $url = trim(preg_replace('/'. str_replace('/', '\/', str_replace('index.php', '', $script_url)) .'/', '', $request_url, 1), '/');
        $url = rtrim(preg_replace('/\?.*/', '', $url), '/');
        
        return explode('/', $url);
    }
    
    /**
     * Get a URI segment
     *
     * @param int $index Index of the URI segment
     * @return mixed Returns URI segment or false if none exists
     * @access private
     */
    private function segment($index)
    {
        if(isset($this->segments[$index])) return $this->segments[$index];
        return false;
    }
      
    /**
     * Handles a GET and reads from the database
     *
     * @access private
     */
    private function read()
    {    	
        $table = $this->segment(0);
		//$id = intval($this->segment(1));
		$id = $this->segment(1);
							
		switch ($table) 
		{
			// We don't want to show the member details in public!
			case "members":													 
				$error = array('0' => array(
					'MemberID' => '0',
					'Name' => '0'						
				));				
				die(json_encode($error));
				break;	
		
			case "dtNotOnEtree":													 
				if($result = getDTNotOnEtree($this->db))
				{
					die(json_encode($result));
				}
				else 
				{
					$error = array('0' => array(
						'SourceID' => '0',
						'DTID' => '0',
						'Free' => '0',
						'Name' => '0'						
					));				
					die(json_encode($error));
				}
				break;	
					
			case "extraMD5s":				
				if($result = getExtraMD5s($this->db))
				{
					die(json_encode($result));
				} 
				else 
				{
					$error = array('0' => array(
						'MD5ID' => '0',
						'MD5' => '0'						
					));
					die(json_encode($error));
				}			
				break;
			
			case "extraTrackFileNames":							
				if($result = getExtraTrackFileNames($this->db))
				{
					die(json_encode($result));
				} 
				else 
				{
					$error = array('0' => array(
						'FNID' => '0',
						'FileName' => '0'						
					));
					die(json_encode($error));
				}			
				break;

			case "getRelatedSources":	
				if($result = getRelatedSources($this->db,$id))
				{
					die(json_encode($result));
				} 
				else
				{
					$error = array( '0' => array(
						'SourceID' => '0'				
					));
					die(json_encode($error));
				}
				break;	
				
			case "getShowsCountByArtistAndYear":			
				if($result = getShowsCountByArtistAndYear($this->db))
				{
					die(json_encode($result));
				} 
				else
				{
					$error = array( '1900' => array(
						'Count' => '0',	
						'ArtistID' => '0'				
					));
					die(json_encode($error));
				}
				break;	
				
			case "getSourceDetails":			
				if($result = getSourceDetails($this->db,$id))
				{
					die(json_encode($result));
				} 
				else
				{
					$error = array( '0' => array(
						'TID' => '0',	
						'Number' => '0',	
						'TrackName' => '0',	
						'Length' => '0',	
						'TNID' => '0',	
						'FileName' => '0',	
						'MD5' => '0',	
						'MD5ID' => '0'				
					));
					die(json_encode($error));
				}
				break;				
				
			case "lastDT":
				if($result = getLastDTID($this->db))
				{
					die(json_encode($result));
				} 
				else
				{
					$error = array('0' => array(
						'LastDT' => '0'						
					));
					die(json_encode($error));
				}
				break;		
	
			case "memberIDfromSecret":				
				if($result = getMemberIDFromSecret($this->db,$id))
				{
					die(json_encode($result));
				}
				else 
				{
					$error = array(
						'MID' => '0'						
					);
					die(json_encode($error));
				}
				break;
	
			case "md5exists":				
				if($result = checkIfMD5Exists($this->db,$id))
				{
					die(json_encode($result));
				}
				else 
				{
					$error = array('0' => array(
						'MD5ID' => '0',
						'MD5' => "-"
					));
					die(json_encode($error));
				}
				break;
		
			case "MyDTSources":													 
				if($result = getMyDTSources($this->db,$id))
				{
					die(json_encode($result));
				}
				else 
				{
					$error = array('0' => array(
						'SourceID' => '0',
						'DTID' => '0'						
					));				
					die(json_encode($error));
				}
				break;				
				
			case "parseError":								
				if($result = getSourcesWhichFailedToParse($this->db))
				{
					die(json_encode($result));
				} 
				else 
				{
					$error = array('0' => array(
						'SourceID' => '0'						
					));
					die(json_encode($error));
				}			
				break;
								
			case "sourcesMissingTapers":							
				if($result = getSourcesWithNoTapers($this->db))
				{
					die(json_encode($result));
				} 
				else 
				{
					$error = array('0' => array(
						'SourceID' => '0'						
					));
					die(json_encode($error));
				}			
				break;
						
			case "trackfilenameexists":
				$id = urldecode($id);
				if($result = checkIfTrackFileNameExists($this->db,$id))
				{
					die(json_encode($result));
				}
				else 
				{
					$error = array('0' => array(
						'FNID' => '0',
						'FileName' => "-"
					));
					die(json_encode($error));
				}
				break;

			case "trackIDsFromFNID":
				if($result = getTrackIDsFromFNID($this->db,$id))
				{
					die(json_encode($result));
				}
				else 
				{
					$error = array('0' => array(
						'TID' => '0',						
					));
					die(json_encode($error));
				}
				break;
				
			case "trackIDsFromNameID":
				if($result = getTrackIDsFromNameID($this->db,$id))
				{
					die(json_encode($result));
				}
				else 
				{
					$error = array('0' => array(
						'TID' => '0',						
					));
					die(json_encode($error));
				}
				break;	
							
			case "trackIDsFromShowSourceID":
				if($result = getTrackIDsFromShowSourceID($this->db,$id))
				{
					die(json_encode($result));
				}
				else 
				{
					$error = array('0' => array(
						'TID' => '0',
						'ShowSourceID' => '0',
						'Number' => '0',
						'FileNameID' => '0',
						'NameID' => '0',
						'MD5ID' => '0',
						'Length' => "00:00:00"
					));				
					die(json_encode($error));
				}
				break;	
			
			case "tracknameexists":
				if($result = checkIfTrackNameExists($this->db,$id))
				{
					die(json_encode($result));
				}
				else 
				{
					$error = array('0' => array(
						'TNID' => '0',
						'TrackName' => "-"
					));
					die(json_encode($error));
				}
				break;
			
			case "SourceInfoByShowSourceID":
				$this->db->select('*')
						 ->from("sourceinfo")
						 ->where('ShowSourceID', $id)
						 ->query();
				
				if($result = $this->db->fetch_all())
				{
					die(json_encode($result));
				}
				else 
				{
					$error = array('0' => array(
						'SIID' => '0',
						'ShowSourceID' => '0',
						'TaperID' => '0',
						'Type' => "0"
					));
					die(json_encode($error));
				}
				break;					
	
			case "ShowIDbyEtreeID":
				$id = urldecode($id);
				$this->db->select('SID')
						 ->from("shows")
						 ->where('EtreeID', $id)
						 ->query();
				
				if($result = $this->db->fetch_all())
				{
					die(json_encode($result));
				}
				else 
				{
					$error = array('0' => array(
						'SID' => '0',						
					));
					die(json_encode($error));
				}
				break;	
	
			case "SSIDbySourceID":
				$id = urldecode($id);
				$this->db->select('*')
						 ->from("showsources")
						 ->where('SourceID', $id)
						 ->query();
				if($result = $this->db->fetch_all())
				{
					die(json_encode($result));
				}
				else 
				{
					$error = array('0' => array(
						'SSID' => '0',
						'ShowID' => '0',
						'ShowSource' => "0"
					));
					die(json_encode($error));
				}
				break;					
				
			case "taperByName":
				$id = urldecode($id);
				if($result = getTaperByName($this->db,$id))
				{
					die(json_encode($result));
				}
				else 
				{
					$error = array('0' => array(
						'TPID' => '-1',
						'Name' => '-',						
					));
					die(json_encode($error));
				}
				break;				
			
			case "sourceIDsFromMD5":
				$id = urldecode($id);
				$this->db->select('SourceID')
						 ->from(array("md5s", "showsources", "tracks" ))
						 ->whereASIS("WHERE tracks.`MD5ID` = md5s.`MD5ID` AND tracks.`ShowSourceID` =  showsources.`SSID` AND md5s.`MD5` = '".$id."'")
						 ->query();
				
				if($result = $this->db->fetch_all())
				{
					die(json_encode($result));
				}
				else 
				{
					$error = array('0' => array(
						'SourceID' => '0',						
					));
					die(json_encode($error));
				}
				break;	
			
			case "mytracksbymemberid":
				$this->db->select('MD5')
					 ->from(array("md5s", "mytracks" ))
					 ->whereASIS("WHERE mytracks.`MD5ID` = md5s.`MD5ID` AND mytracks.`MID` = ".intval($this->_get('mid')))
					 ->limit(intval($this->_get('limit')), intval($this->_get('offset')))							 
					 ->query();		
				
				if($result = $this->db->fetch_all())
				{
					die(json_encode($result));
				}
				else 
				{
					$error = array('0' => array(
						'MD5' => '0',						
					));
					die(json_encode($error));
				}
				break;
		
			default:
					
				if(!$table || !isset($this->db_structure[$table]))
				{
					$error = array('result' => array(
						'message' => 'Not Found',
						'code' => 404,
						'id' => 0
					));
					die(json_encode($error));
				}

				//if($id && is_int($id)) {
				if($id) 
				{
					$index = 'id';
					if(isset($this->table_index[$table])) $index = $this->table_index[$table];
					$this->db->select('*')
							 ->from($table)
							 ->where($index, $id)
							 ->query();
					//if($result = $this->db->fetch_array()){						
					
					if($result = $this->db->fetch_all())
					{
						die(json_encode($result));
					}
					else 
					{
						$error = array('result' => array(
							'message' => 'No Content',
							'code' => 204,
							'id' => 0
						));
						die(json_encode($error));
					}				
				} 
				else 
				{
					$this->db->select('*')
							 ->from($table)
							 //->order_by_old($this->_get('order_by'), $this->_get('order'))
							 ->limit(intval($this->_get('limit')), intval($this->_get('offset')))
							 ->query();

					//echo $this->db->get_query();
					
					if($result = $this->db->fetch_all())
					{
						die(json_encode($result));
					}
					else
					{
						$error = array('result' => array(
							'message' => 'No Content',
							'code' => 204,
							'id' => 0
						));
						die(json_encode($error));
					}
				}
		}		
    }
    
    /**
     * Helper function to retrieve $_GET variables
     *
     * @param string $index Optional $_GET index
     * @return mixed Returns the $_GET var at the specified index,
     *               the whole $_GET array or false
     * @access private
     */
    private function _get($index = '')
    {
        if($index)
		{
            if(isset($_GET[$index]) && $_GET[$index]) return strip_tags($_GET[$index]);
        } 
		else 
		{
            if(isset($_GET) && !empty($_GET)) return $_GET;
        }
        return false;
    }    
}
?>