<?php
/*
 * Handles sending requests to the MySQL Database
 * 
 * @author jnorcross
 */

class MySqlDB implements iDatabase{

    private $mysql_host;
    private $mysql_database;
    private $mysql_user;
    private $mysql_password;
    public $connection;
    private $result;

    public function __construct() {
        $this->mysql_host = Settings::$mysql_host;
        $this->mysql_database = Settings::$mysql_database;
        $this->mysql_user = Settings::$mysql_user;
        $this->mysql_password = Settings::$mysql_password;
        $waitForConnection = true;
        do
        {
            try
            {
                $this->connection = new PDO('mysql:host=' . $this->mysql_host . ';dbname=' . $this->mysql_database, $this->mysql_user, $this->mysql_password);
                $waitForConnection = false;
            }
            catch(Exception $e)//If we can't connect to sql then just delay 1 second and try again
            {
                sleep(1);
            }
        }
        while($waitForConnection);

        $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /*
     * Retrieves the auto incremented id of the last inserted record from the internal PDO object
     * @return id of the last inserted record
     */
    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }

    /*
     * Execute a sql statement and return an array of objects
     * @statement - The SQL Statement
     * @parameters - An array of parameters for the statment [['type', 'parameter']['type','parameter']...]
     * @fetchData - Determines whether the statement should fetch data from the resultset.  This should be true for select statements and false for insert/update/delete statements.
     * @return - An array of results
     */

    public function execute($statement, $parameters = null, $fetchData = true) {
        Logger::LogData('database.log', $statement);
        Logger::LogData('database.log', print_r($parameters, true));
        $return = array();
        if (count($parameters) > 0) {//prepared statements
            $prepare = $this->connection->prepare($statement);
            $array = $prepare->errorInfo();
            if (array_key_exists(2, $array) && $array[2] != "") {
                throw new Exception("Execute failed: " . $array[2]);
            }
            $prepare->execute($parameters);
            $array = $prepare->errorInfo();
            if (array_key_exists(2, $array) && $array[2] != "") {
                throw new Exception("Execute failed: " . $array[2]);
            }
            if ($fetchData) {
                $return = $prepare->fetchAll();
            }
        } else {//normal statment
            $result = $this->connection->query($statement);
            if ($fetchData) {
                $return = $result->fetchAll();
            }
        }
        return $return;
    }

    /*
      Finds the count of the number of records the sql statement would return
      @statement - the SQL statement to execute
      @parameters - an array of parameters to use for prepared queries
     */

    public function rowCount($statement, $parameters = null) {
        $result = NULL;
        Logger::LogData('database.log', $statement);
        Logger::LogData('database.log', print_r($parameters, true));
        if (count($parameters) > 0) {//prepared statements
            $result = $this->connection->prepare($statement);
            $array = $result->errorInfo();
            if (array_key_exists(2, $array) && $array[2] != ""){
                throw new Exception("Execute failed: " . $array[2]);
            }
            $result->execute($parameters);
            $array = $result->errorInfo();
            if (array_key_exists(2, $array) && $array[2] != ""){
                throw new Exception("Execute failed: " . $array[2]);
            }
        }
        else {//normal statment
            $result = $this->connection->query($statement);
        }
        return $result->fetchColumn();
    }
    
    /*
     * Retrieves column schema for a table
     * @table - the table to retrieve the columns for
     * @return - an array of columnSchema objects
     */
    public function GetColumnSchema($table) {
        $columnSchema = $this->execute("Select COLUMN_NAME, IS_NULLABLE, DATA_TYPE, CHARACTER_MAXIMUM_LENGTH, "
                . "COLUMN_KEY from information_schema.columns where table_name = (?) and table_schema = (?)", array($table, Settings::$mysql_database));
        
        $columns = array();
        foreach($columnSchema as $column)
        {
            $name = $column['COLUMN_NAME'];
            $null = $column['IS_NULLABLE'] == "NO" ? false : true;
            $type = $column['DATA_TYPE'];
            $maxLength = $column['CHARACTER_MAXIMUM_LENGTH'];
            $isPrimaryKey = $column['COLUMN_KEY'] == "PRI" ? true : false;
            $columns[] = new ColumnSchema($name, $null,  $type, $maxLength, $isPrimaryKey, $table);
        }
        return $columns;
    }

}
