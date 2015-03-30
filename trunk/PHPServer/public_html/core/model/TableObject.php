<?php

/*
  Generic Table Object for handling the base functions (create, read, update, delete)
  Read - Should be able to handle sorting and filtering
  Create - Add new record
  Update - Update existing record
  Delete - Delete existing record
 */

abstract class TableObject implements iExtOperations, iCRUDOperations {

    //Note Private variables can't be accessed from sub-classes; use the protected/public methods instead
    private $connection;
    private $request;
    private $properties;
    private $results;
    private $columnSchema;
    private $columnList;
    private $columnSchemaView;
    private $columnListView;

    /*
      Retrieves the primary table for the object
      @return - the primary table as a string
     */

    abstract public function GetPrimaryTable();

    /*
      Retrieves the default sort column name
      @return the full name of the sort column as a string to use as the default
     */

    abstract protected function GetDefaultSortColumn();

    /*
     * Retrieves the security string for accessing the table object
     * @return an array of allowed functions
     */

    abstract public function GetSecurity();

    public function __construct(iDatabase $con, iTableRequest $request = null) {
        $this->connection = $con;
        $this->request = $request;
        $this->properties = array();
        $this->columnSchema = $this->connection->GetColumnSchema($this->GetPrimaryTable());
        $columnSchema = $this->GetColumnSchema();
        $columns = array();
        $keys = array();
        foreach ($columnSchema as $column) {
            array_push($columns, $column->GetColumnName());
            if ($column->IsPrimaryKey()) {
                array_push($keys, $column->GetColumnName());
            }
        }
        $this->columnList = new ColumnList($this->GetPrimaryTable(), $columns, $keys);
        
        if($this->GetPrimaryTable() != $this->GetPrimaryTableView())
        {
            $this->columnSchemaView = $this->connection->GetColumnSchema($this->GetPrimaryTableView());
            $columnSchema = $this->columnSchemaView;
            $columns = array();
            $keys = array();
            foreach ($columnSchema as $column) {
                array_push($columns, $column->GetColumnName());
                if ($column->IsPrimaryKey()) {
                    array_push($keys, $column->GetColumnName());
                }
            }
            $this->columnListView = new ColumnList($this->GetPrimaryTableView(), $columns, $keys);
        }
    }

    /*
      Retrieves the column list for the table
      @return - a list of columns for the table
     */

    protected function GetColumns() {
        return $this->columnList;
    }

    /*
      Retrieves the column list for the view
      @return a list of columns for the view
     */

    protected function GetColumnsView() {
        $columns = $this->GetColumns();
        if($this->GetPrimaryTable() != $this->GetPrimaryTableView())
        {
            $columns = $this->columnListView;
        }
        return $columns;
    }

    /*
      Retrieves the view name for the object
      @return - the view of the object
     */

    protected function GetPrimaryTableView() {
        return $this->GetPrimaryTable();
    }

    protected function GetColumnSchema() {
        return $this->columnSchema;
    }

    protected function GetConnection() {
        return $this->connection;
    }

    protected function GetRequest() {
        return $this->request;
    }

    protected function GetData() {
        return $this->request->GetData();
    }

    protected function AddProperty($name, $value) {
        $this->properties[$name] = $value;
    }

    public function GetProperties() {
        return $this->properties;
    }

    public function GetResults() {
        return $this->results;
    }

    protected function SetResults(array $results) {
        $this->results = $results;
    }

    /*
      Retrieves whether or not the table has an auto-increment id.  These id's need to be handled and returned back to the user as they are generated so that update works properly.
      Note: We assume that if this is true then the column name is 'id'
      @return true if the table has this field otherwise false
     */

    protected function HasAutoIncrementId() {
        return false;
    }

    /*
      Verifies the field is valid based on whether or not the column exists as a column in our view
     */

    protected function ValidateColumnView($columnName) {
        $columns = $this->GetColumnsView();
        $columnNames = $columns->GetNames();
        $return = false;
        if (in_array($columnName, $columnNames)) {//See if the column is a valid column for the view
            $return = true;
        }
        else    
        {
            Logger::LogError("Invalid column attempted to be used: " . $columnName, Logger::debug);
        }
        return $return;
    }

    /*
      Function to allow us to set values for special fields like creation dates.
     */

    protected function SetValueForCreateUpdate($record, $columnName) {
        $return = $record->$columnName;
        if ($columnName == 'lastupdated') {
            $return = date('Y-m-d');
        }
        return $return;
    }

    public function create() {
        $columns = $this->GetColumns();
        $arrayInsertFields = array_fill(0, $columns->GetCount(), "?");
        $statement = "INSERT INTO " . $this->GetPrimaryTable() . 
                " (" . implode(",", $columns->GetNames()) . ")" . 
                " VALUES (" . implode(",", $arrayInsertFields) . ")" .
                " ON DUPLICATE KEY UPDATE ";
        
        $duplicateUpdate = "";
        foreach ($columns->GetColumns() as $column) {
            if (!($column->IsKey())) {
                if($duplicateUpdate != "")
                {
                    $duplicateUpdate = $duplicateUpdate . ", ";
                }
                $duplicateUpdate = $duplicateUpdate . $column->GetName() . " = " . "VALUES(" . $column->GetName() . ")";
            }
        }
        $statement = $statement . $duplicateUpdate;
        Logger::LogError($statement, Logger::debug);
        $records = $this->GetData();
        foreach ($records as $record) {
            $parameters = array();
            foreach ($columns->GetNames() as $column) {
                Security::ValidateColumn($column, $record->$column);
                $parameters[] = $this->SetValueForCreateUpdate($record, $column);
            }
            $this->connection->execute($statement, $parameters, false);
            if ($this->HasAutoIncrementId()) {
                $record->id = $this->connection->lastInsertId();
            }
        }
        $this->SetResults($records);
    }

    public function update() {
        $columns = $this->GetColumns();
        $statement = "Update " . $this->GetPrimaryTable() . " set ";
        $where = " where ";

        $set = "";
        $criteria = "";
        foreach ($columns->GetColumns() as $column) {
            //If the column is not a key then set the column
            if (!($column->IsKey())) {
                if ($set != "") {
                    $set = $set . ", ";
                }
                $set = $set . $column->GetName() . " = (?)";
            } else { //Otherwise use the key to find the record
                if ($criteria != "") {
                    $criteria = $criteria . " and ";
                }
                $criteria = $criteria . $column->GetName() . " = (?)";
            }
        }
        if ($set == "") {
            throw new Exception("No values set.  At least one column must be set in an update.");
        }
        if ($criteria == "") {
            throw new Exception("No keys set.  At least one key is required in an update");
        }
        $statement = $statement . $set . $where . $criteria;

        $records = $this->GetData();
        foreach ($records as $record) {
            $parameters = array();
            foreach ($columns->GetColumns() as $column) {
                if (!$column->IsKey()) {//All set columns must come before all key columns
                    $columnName = $column->GetName();
                    Security::ValidateColumn($columnName, $record->$columnName);
                    $parameters[] = $this->SetValueForCreateUpdate($record, $columnName);
                }
            }
            foreach ($columns->GetKeys() as $key) {
                Security::ValidateColumn($key, $record->$key);
                $parameters[] = $record->$key;
            }
            $this->connection->execute($statement, $parameters, false);
        }
        $this->SetResults($records);
    }

    public function delete() {
        $columns = $this->GetColumns();
        $statement = "Delete from " . $this->GetPrimaryTable() . " where ";
        $where = "";
        foreach ($columns->GetKeys() as $key) {
            if ($where != "") {
                $where = $where . " and ";
            }
            $where = $where . $key . " = (?)";
        }
        if ($where == "") {
            throw new Exception("No keys set.  At least one key is required to delete.");
        }
        $statement = $statement . $where;

        $records = $this->GetData();
        foreach ($records as $record) {
            $parameters = array();
            foreach ($columns->GetKeys() as $key) {
                Security::ValidateColumn($key, $record->$key);
                $parameters[] = $record->$key;
            }
            $this->connection->execute($statement, $parameters, false);
        }
        $this->SetResults($records);
    }

    /*
     * Returns a string for the Order by clause.  This function will default to using the Default sort order and ascending if none is specified.
     */

    private function GetOrderBy() {
        //Grab sorting
        $orderColumn = $this->request->GetSortColumn();
        if (!$this->ValidateColumnView($orderColumn)) {
            $orderColumn = $this->GetDefaultSortColumn();
        }
        $orderDirection = $this->request->GetSortDirection();

        $orderby = " Order By " . $orderColumn . " " . $orderDirection;
        return $orderby;
    }

    /*
     * Returns a string containing the limit if one is required for paging.
     */

    private function GetLimit() {
        //Add paging parameters if they exist
        $strLimit = "";
        if ($this->request->HasPaging()) {
            $start = $this->request->GetStart();
            $limit = $this->request->GetLimit();
            $strLimit = " LIMIT " . $start . ", " . $limit;
        }
        return $strLimit;
    }

    /*
     * @param parameters - An array of parameters to be returned for the where clause
     * @return Returns the string container the parameterized where clause
     */

    private function GetWhere(&$parameters) {
        $where = "";
        //Implement filtering
        $filters = $this->request->GetFilters();

        $criteria = "";
        foreach ($filters as $filter) {
            $property = $filter->GetColumn();
            if (!$this->ValidateColumnView($property)) {
                continue;
            }
            $value = $filter->GetValue();
            $operator = $filter->GetOperator();

            if ($filter->IsValid()) {
                if ($criteria != "") {
                    $criteria = $criteria . "and ";
                }
                $criteria = $criteria . $property . " " . $operator . " (?) ";
                $parameters[] = $value;
            }
        }
        if ($criteria != "") {
            $where = " where " . $criteria;
        }

        return $where;
    }

    public function read() {
        $orderby = $this->GetOrderBy();
        $strLimit = $this->GetLimit();

        $select = "SELECT * FROM " . $this->GetPrimaryTableView();

        $parameters = array();
        $where = $this->GetWhere($parameters);

        //Use count to find the actual total results since we might be paging
        $countSelect = "SELECT COUNT(*) FROM " . $this->GetPrimaryTableView();

        $statement = $select . $where . $orderby . $strLimit;
        $countStatement = $countSelect . $where;

        //Execute the statements and return the results
        $results = array();
        $this->AddProperty("total", $this->connection->rowCount($countStatement, $parameters));

        $resultSet = $this->connection->execute($statement, $parameters);
        foreach ($resultSet as $row) {
            $arrayResult = array();
            foreach ($this->GetColumnsView()->GetNames() as $column) {
                $arrayResult[$column] = $row[$column];
            }
            $results[] = $arrayResult;
        }

        $this->SetResults($results);
    }

    //array of form items
    public function GetExtForm() {
        $columnSchema = $this->GetColumnSchema();
        return Ext::BuildFormObject($columnSchema);
    }

    //array of columns
    public function GetExtColumns() {
        $columnSchema = $this->GetColumnSchema();
        $itemArray = array();
        foreach ($columnSchema as $column) {
            $itemArray[] = Ext::BuildColumnObject($column);
        }
        return $itemArray;
    }

    //array defining the model
    public function GetExtModel() {
        $columnSchema = $this->GetColumnSchema();
        $itemArray = array();
        foreach ($columnSchema as $column) {
            $itemArray[] = Ext::BuildModelObject($column);
        }
        return $itemArray;
    }

    //array defining the store
    public function GetExtStore() {
        //$columnSchema = $this->GetColumnSchema();
        $itemArray = array();


        return $itemArray;
    }

    //array to put together all of the arrays from the above functions
    public function GetExtData() {
        $extview = array();
        $extview['form'] = $this->GetExtForm();
        $extview['columns'] = $this->GetExtColumns();
        $extview['model'] = $this->GetExtModel();
        $extview['store'] = $this->GetExtStore();

        $this->SetResults($extview);
    }

}
