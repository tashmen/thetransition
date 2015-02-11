<?php
/*
  Handles data interaction for the spaces table
 */

class spaces extends TableObject {
    /*
      Retrieves the primary table for the object
      @return - the primary table as a string
     */

    protected function GetPrimaryTable() {
        return "spaces";
    }

    /*
      Retrieves the view name for the object
      @return - the view of the object
     */

    protected function GetPrimaryTableView() {
        return $this->GetPrimaryTable();
    }

    /*
      Retrieves the column list for the table
      @return - a list of columns for the table
     */

    protected function GetColumns() {
        return new ColumnList($this->GetPrimaryTable(), array('id', 'name', 'icon'), array('id'));
    }

    /*
      Retrieves the column list for the view
      @return a list of columns for the view
     */

    protected function GetColumnsView() {
        return $this->GetColumns();
    }

    /*
      Retrieves the default sort column name
      @return the full name of the sort column as a string to use as the default
     */

    protected function GetDefaultSortColumn() {
        return 'name';
    }
    
    /*
     * Retrieves the security string for accessing the table object
     * @return an array of allowed functions
     */
    public function GetSecurity(){
        return array('read');
    }
}
?>

