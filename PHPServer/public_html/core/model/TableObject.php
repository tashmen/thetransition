<?php

/*
 * Generic Table Object for handling the base functions (create, read, update, delete)
 * Read - Should be able to handle sorting and filtering
 * Create - Add new record
 * Update - Update existing record
 * Delete - Delete existing record
 * 
 * @author jnorcross
 */

abstract class TableObject implements iExtOperations, iCRUDOperations {

    //Note Private variables can't be accessed from sub-classes; use the protected/public methods instead
    private $connection;
    private $request;
    private $data;
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
        if(!is_null($request)){
            $this->data = $request->GetData();
        }
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
        return $this->data;
    }
    
    protected function SetData($data){
        $this->data = $data;
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
        //If we are checking if the view has a distance and distance filtering is enabled then we will override the view with a distance component hence the view will always have a distance column in this case
        if($columnName == 'distance' && $this->HasDistanceFilter())
            return true;
            
        $columns = $this->GetColumnsView();
        $columnNames = $columns->GetNames();
        $return = false;
        if (in_array($columnName, $columnNames)) {//See if the column is a valid column for the view
            $return = true;
        }
        else    
        {
            if($columnName != "")
                Logger::LogError("Invalid column attempted to be used: " . $columnName, Logger::debug);
        }
        return $return;
    }

    /*
      Function to allow us to set values for special fields like creation dates.
     */

    protected function SetValueForCreateUpdate($record, $columnName, $actionType) {
        $return = $record->$columnName;
        if ($columnName == 'lastupdated') {
            $return = date('Y-m-d');
        }
        if($columnName == 'creationdt' && $actionType == 'create')
        {
            $return = date('Y-m-d');
        }
        return $return;
    }

    /*
     * Creates a new record for the table object
     */
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
            $this->ValidateRecord($record);
            foreach ($columns->GetNames() as $column) {
                $parameters[] = $this->SetValueForCreateUpdate($record, $column, 'create');
            }
            $this->connection->execute($statement, $parameters, false);
            if ($this->HasAutoIncrementId()) {
                $record->id = $this->connection->lastInsertId();
            }
        }
        $this->SetResults($records);
    }

    /*
     * Updates an existing record for a table object
     */
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
            $this->ValidateRecord($record);
            foreach ($columns->GetColumns() as $column) {
                if (!$column->IsKey()) {//All set columns must come before all key columns
                    $columnName = $column->GetName();
                    $parameters[] = $this->SetValueForCreateUpdate($record, $columnName, 'update');
                }
            }
            foreach ($columns->GetKeys() as $key) {
                $parameters[] = $record->$key;
            }
            $this->connection->execute($statement, $parameters, false);
        }
        $this->SetResults($records);
    }

    /*
     * Deletes an existing record for a table object
     */
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
            $this->ValidateRecord($record);
            foreach ($columns->GetKeys() as $key) {
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

            if ($filter->IsValid()) {
                if ($criteria != "") {
                    $criteria = $criteria . "and ";
                }
                $criteria = $criteria . $filter->BuildQuery();
                $filter->SetParameters($parameters);
            }
        }
        if ($criteria != "") {
            $where = " where " . $criteria;
        }

        return $where;
    }

    /*
     * Reads records from the table object
     */
    public function read() {
        $orderby = $this->GetOrderBy();
        $strLimit = $this->GetLimit();

        $table = $this->GetPrimaryTableView();
        if($this->HasDistanceFilter())
        {
            $table = $this->BuildDistanceTable($table);
        }
        $select = "SELECT * FROM " . $table;

        $parameters = array();
        $where = $this->GetWhere($parameters);

        //Use count to find the actual total results since we might be paging
        $countSelect = "SELECT COUNT(*) FROM " . $table;

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

    /*
     * Determines if the Table object has a request to filter by distance
     * @returns true if distance filtering is enabled
     */
    protected function HasDistanceFilter()
    {
        //If the currently logged in user doesn't have a latitude/longitude to calculate distance from then we can't filter
        if(Security::HasLocation())
        {    
            $filters = $this->GetRequest()->GetFilters();
            foreach($filters as $filter)
            {
                if($filter->GetColumn() == "distance")
                {
                    return true;
                }
            }
        }
        return false;
    }
    
    /* 
     * Retrieves the distance filter from the list of requested filters
     * @retun The distance filter
     */
    protected function GetDistanceFilter()
    {
        $filters = $this->GetRequest()->GetFilters();
        foreach($filters as $filter)
        {
            if($filter->GetColumn() == "distance")
            {
                return $filter;
            }
        }
        return null;
    }
    
    /*
     * For Distance filtering to work we need to build a table with the distance values available
     * Distances are based on the currently logged in users latitude and longitude so if the user doesn't have a lat/long then set the distance as the filter value + 1 to include everything
     * @param table - The table name to compute distances for
     * @return - a sql string that can fit into a from clause ex: (select *, distance from {table} where latitude between x1 and x2 and longitude between y1 and y2)
     */
    protected function BuildDistanceTable($table)
    {
        $distanceFilter = $this->GetDistanceFilter();
        $distance = $distanceFilter->GetValue();
        
        $myLat = Security::GetLoggedInUserLatitude();
        $myLong = Security::GetLoggedInUserLongitude();
        
        $lat1 = $myLat-($distance / 69);
        $lat2 = $myLat+($distance / 69);
        $long1 = $myLong-$distance / abs(cos(deg2rad($myLat))*69);
        $long2 = $myLong+$distance / abs(cos(deg2rad($myLat))*69);
        
        $sql = '(select *, ( 3959 * acos( cos( radians( ' . $myLat . ') ) 
              * cos( radians( latitude ) ) 
              * cos( radians( longitude ) - radians( ' . $myLong . ') ) 
              + sin( radians( ' . $myLat . ') ) 
              * sin( radians( latitude ) ) ) ) AS distance from ' . $table . 
                ' where latitude between ' . $lat1 . ' and ' . $lat2 . ' and longitude between ' . $long1 . ' and ' . $long2 . ') as temp ';
        
        return $sql;
    }
    
    /*
     * Function for returning a list of events that a table object is listening for
     * Subclasses should override the function if they want to listen for specific events.  A function with the same name as the event must be implemented.
     * @return - An array of events to listen for
     */
    public function GetEventListeners()
    {
        return array();
    }
    
    /*
     * Validates that users can only set records with their id.  Requires the column name to be "userid".
     * @param record - The record to validate
     * @return true if the user has access to modify the record otherwise throw error
     */
    public function ValidateRecord($record) {
        $columns = $this->GetColumns();
        foreach ($columns->GetColumns() as $column) {
            $columnName = $column->GetName();
            if ($columnName == 'userid') {
                if (Security::GetLoggedInUser() != $record->$columnName && !Security::IsAdmin())
                {
                    throw new Exception("Security: The record you are modifying does not belong to you and cannot be changed.");
                }
            }
        }
        return true;
    }
}
