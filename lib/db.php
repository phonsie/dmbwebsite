<?php

/**
 * MySQL Database Wrapper
 * Author: Caleb Mingle (Modified by Gilbert Pellegrom)
 * Website: http://forrst.com/posts/Simple_PHP_MySQL_Wrapper-LZ1
 * Date:  4/22/2011
 * Date: 12/14/2013 Further modified by Phonsie Hevey
 */

class Database
{
 	private $_config;       // Stores the configuration provided by user.
	private $_query;        // Stores the current query.
	private $_error;        // Stores an error based on $_verbose.
	private $_verbose;      // Stores a boolean determining output / storage of errors.
  private $_buildQuery;   // Stores the current "in progress" query build.

  public function __construct($config)
  {
		$this->_config = $config;
	}

	/*
	 * Initializes the database.  Checks the configuration, connects, selects database.
     */

    public function init()
    {
		if(!$this->__check_config())
		{
			return false;
		}

		if(!$this->__connect())
		{
			return false;
		}
		return true;
	}

    /*
     * Checks the configuration for blanks.
     */
    private function __check_config()
	{
        $config = $this->_config;

        if(empty($config["server"]) || empty($config["username"]) || empty($config["database"]))
		{
            $this->_error = "Configuration details were blank.";
            return false;
        }

        $this->_verbose = ($config["verbose"]) ? true : false;
        return true;
    }

    /*
     * Connects to the database.
     */
    private function __connect()
	{
    	global $pdo;
    	$pdo = new PDO("mysql:host=".$this->_config["server"].";dbname=".$this->_config["database"],$this->_config["username"],$this->_config["password"]);
		return true;
    }

    /*
     * SELECT starter.  $fields can be either a string or an array of strings to select.
     */
	public function select($fields, $add = false)
	{
        $query = "SELECT";
        if(!empty($fields) && !is_array($fields))
		{
			$query .= " {$fields}";
        }
		else if(is_array($fields))
		{
            $query .= " `";
            $query .= implode("`,`", $fields);
            $query .= "`";
        }
		else
		{
            $query .= " *";
        }

		if($add)
		{
			$this->_buildQuery .= $query;
		}
		else
		{
			$this->_buildQuery = $query;
		}
		return $this;
    }

    /*
     * Adds where the SELECT is going to be coming from (table wise).
     * select("*")
     * select("username")
     * select(array("username", "password"))
     */

	public function from($table)
	{
    	$query = "";

    	if(!empty($table) && !is_array($table))
		{
    		$query .= "{$table}";
    	}
		else if(is_array($table))
		{
    		$query .= implode("`,`", $table);
    	}
		else
		{
    		$query .= "*";
    	}
        $this->_buildQuery .= " FROM `{$query}`";
        return $this;
    }

    public function from_open_bracket()
	{
        $this->_buildQuery .= " FROM (";
        return $this;
    }

	/*
	public function from($table) {
        $this->_buildQuery .= " FROM `{$table}`";
        return $this;
    }
	*/

    /*
     * UPDATE starter.
     * update("users")
     */
    public function update($table)
	{
        $this->_buildQuery = "UPDATE `{$table}`";
        return $this;
    }

    /*
     * DELETE starter.
     * delete("users")
     */
    public function delete($table)
	{
	    $this->_buildQuery = "DELETE FROM `{$table}`";
	        //echo $this->_buildQuery;
		return $this;
    }

	public function deleteASISPDO($queryString)
    {
    	global $pdo;
    	try
        {
			//echo "In deleteASISPDO\r\n".$queryString."\r\nIn deleteASISPDO\r\n";
			$query = $pdo->prepare($queryString);
			$query->execute();
		}
       	catch( PDOException $Exception )
       	{
               echo $Exception;
       	}
	}

    /*
     * INSERT starter.  $data is an array matched columns to values:
     * $data = array("username" => "Caleb", "email" => "caleb@mingle-graphics.com");
     * insert("users", array("username" => "Caleb", "password" => "hash"))
     */
    public function insert($table, $data)
	{
        $query = "INSERT INTO `{$table}` (";
        $keys   = array_keys($data);
        $values = array_values($data);

        $query .= implode(", ", $keys);
        $query .= ") VALUES (";

        $array  = array();

        foreach($values as $value) {
            $array[] = "'{$value}'";
        }

        $query .= implode(", ", $array) . ")";
        $this->_buildQuery = $query;
        return $this;
    }

    /*
     * INSERT starter.  $data is an array matched columns to values:
     * $data = array("username" => "Caleb", "email" => "caleb@mingle-graphics.com");
     * insert("users", array("username" => "Caleb", "password" => "hash"))
     */

    public function insertPDO($table, $data)
    {
    	global $pdo;
    	try
        {
			$columnString = implode(',', array_keys($data));
			$valueString = implode(',', array_fill(0, count($data), '?'));

			$query = $pdo->prepare("INSERT INTO `{$table}` ({$columnString}) VALUES ({$valueString})");
			$query->execute(array_values($data));
			$id = $pdo->lastInsertId();
		}
       	catch( PDOException $Exception )
       	{
               echo $Exception;
       	}
	return $id;
    }

    /*
     * SET.  $data is an array matched key => value.
     * set(array("username" => "Caleb"))
     */
    public function set($data)
	{
        if(!is_array($data)) return $this;

        $query =  "SET ";
        $array = array();

        foreach($data as $key => $value)
		{
            $array[] = "`{$key}`='{$value}'";
        }

        $query .= implode(", ", $array);

        $this->_buildQuery .= " " . $query;
        return $this;
    }

    /*
     * WHERE.  $fields and $values can either be strings or arrays based on how many you need.
     * $operators can be an array to add in <, >, etc.  Must match the index for $fields and $values.
     * where("username", "Caleb")
     * where(array("username", "password"), array("Caleb", "testing"))
     * where(array("username", "level"), array("Caleb", "10"), array("=", "<"))
     */
    public function where($fields, $values, $operators = '')
	{
        if(!is_array($fields) && !is_array($values))
		{
            $operator = (empty($operators)) ? '=' : $operators[0];
            $query = " WHERE `{$fields}` {$operator} '{$values}'";
        }
		else
		{
            $array = array_combine($fields, $values);
            $query = " WHERE ";

            $data  = array();
            $counter = 0;

            foreach($array as $key => $value)
			{
                $operator = (!empty($operators) && !empty($operators[$counter])) ? $operators[$counter] : '=';
                $data[] = "`{$key}` {$operator} '{$value}'";
                $counter++;
            }
            $query .= implode(" AND ", $data);
        }

        $this->_buildQuery .= $query;
        return $this;
    }

	public function whereASIS($query)
	{
    	$this->_buildQuery .= $query;
    	return $this;
    }

    /*
     * Order By:
     * order_by("username", "asc")
     */
    // OLD Version
    public function order_by_old($field, $direction = 'asc') {
        if($field) $this->_buildQuery .= " ORDER BY `{$field}` " . strtoupper($direction);
        return $this;
    }

    public function order_by($fields)
    {
    	$query = " ORDER BY ";

        if(!empty($fields) && !is_array($fields))
        {
			$query .= " {$fields}";
		}
        else if(is_array($fields))
        {
			$query .= " `";
			$query .= implode("`,`", $fields);
			$query .= "`";
		}
        else
        {
			$query .= " *";
		}
		$this->_buildQuery .= $query;
        return $this;
    }

    /*
     * Limit:
     * limit(1)
     * limit(1, 0)
     */
    public function limit($max, $min = '0')
	{
        if($max) $this->_buildQuery .= " LIMIT {$min},{$max}";
        return $this;
    }

    public function group_by($fields)
	{
    	$query = " GROUP BY ";
        if(!empty($fields) && !is_array($fields))
        {
			$query .= " {$fields}";
		}
        else if(is_array($fields))
        {
			$query .= implode(",", $fields);
		}
        else
        {
			$query .= " *";
		}
		$this->_buildQuery .= $query;
		return $this;
    }

	/*
	public function group_by($field)
	{
    	if($field) $this->_buildQuery .= " GROUP BY {$field}";
    	return $this;
    }
	*/

	public function mysql_as($table)
	{
        $this->_buildQuery .= ") AS `{$table}`";
        return $this;
    }

    public function join()
	{
        $this->_buildQuery .= "JOIN (";
        return $this;
    }

	public function inner_join()
	{
        $this->_buildQuery .= " INNER JOIN (";
        return $this;
    }

	public function left_join()
	{
        $this->_buildQuery .= " LEFT JOIN (";
        return $this;
    }

	public function left_join_table_name($table_name)
	{
        $this->_buildQuery .= " LEFT JOIN ".$table_name;
        return $this;
    }

    public function on($match)
	{
        $this->_buildQuery .= " ON ".$match;
        return $this;
    }

    public function fetch_assoc()
	{
    	$array = $this->_query->fetchAll(PDO::FETCH_COLUMN|PDO::FETCH_GROUP);
    	return $array;
    }

	public function fetch_assoc_multi()
	{
    	$array = $this->_query->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_ASSOC);
    	return $array;
    }

    /*
     * Will return the array of data from the query.
     */
    public function fetch_array()
	{
    	$array = $this->_query->fetch();
    	if($array)
		{
            foreach($array as $key=>$val)
			{
            	if(is_numeric($key))
				{
                    unset($array[$key]);
                }
            }
        }

        if(!$array && $this->_verbose)
		{
            $this->_error = mysql_error();
        }
        return $array;
    }

    public function fetch_all()
	{
    	$results = array();
        while($array = $this->_query->fetch())
		{
        	foreach($array as $key=>$val)
			{
                if(is_numeric($key))
				{
                    unset($array[$key]);
                }
            }
            $results[] = $array;
        }
        if(!$array && $this->_verbose)
		{
            $this->_error = mysql_error();
        }
        return $results;
    }

    /*
     * Will return the number or rows affected from the query.
     */
    public function num_rows()
	{
        $num = @mysql_num_rows($this->_query);

        if(!$num && $this->_verbose)
		{
            $this->_error = mysql_error();
        }
        return $num;
    }

    /*
     * If $query_text is blank, query will be performed on the built query stored.
     */
    public function query($query_text = '')
	{
    	$query_text = ($query_text == '') ? $this->_buildQuery : $query_text;

		//echo $query_text."END\r\n";

    	global $pdo;
    	$query = $pdo->prepare($query_text);
    	$query->execute();
    	$this->_query = $query;
    	return $this;
    }

    /*
     * If $query_text is blank, query will be performed on the built query stored.
    */
    /*
    public function queryPDO($query_text = '', $data) {
    	$query_text = ($query_text == '') ? $this->_buildQuery : $query_text;
    	$keys   = array_keys($data);
    	$values = array_values($data);

    	global $pdo;
    	$query = $pdo->prepare($query_text);
    	$query->execute($executeArray);
    	$this->_query = $query;
    	return $this;
    }
    */

    /*
     * Will return the current built query story in $this->_buildQuery;
     */
    public function get_query()
	{
        return $this->_buildQuery;
    }

    /*
     * Will return the current stored error.
     */
    public function get_error()
	{
        return $this->_error;
    }
}
?>