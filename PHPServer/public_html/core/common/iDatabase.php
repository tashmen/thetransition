<?php

/**
 * Interface for database operations
 *
 * @author jnorcross
 */
interface iDatabase {
    /*
     * Retrieves the auto incremented id of the last inserted record from the internal PDO object
     * @return id of the last inserted record
     */
    public function lastInsertId();
    /*
     * Execute a sql statement and return an array of objects
     * @statement - The SQL Statement
     * @parameters - An array of parameters for the statment [['type', 'parameter']['type','parameter']...]
     * @fetchData - Determines whether the statement should fetch data from the resultset.  This should be true for select statements and false for insert/update/delete statements.
     * @return - An array of results
     */
    public function execute($statement, $parameters = null, $fetchData = true);
    /*
      Finds the count of the number of records the sql statement would return
      @statement - the SQL statement to execute
      @parameters - an array of parameters to use for prepared queries
     */
    public function rowCount($statement, $parameters = null);
    /*
     * Retrieves column schema for a table
     * @table - the table to retrieve the columns for
     * @return - an array of columnSchema objects
     */
    public function GetColumnSchema($table);
}
