<?php
class userreviews extends TableObject {
    /*
      Retrieves the primary table for the object
      @return - the primary table as a string
     */

    public function GetPrimaryTable() {
        return "userreviews";
    }

    /*
      Retrieves the view name for the object
      @return - the view of the object
     */

    protected function GetPrimaryTableView() {
        return "userreviewsview";
    }

    /*
      Retrieves the column list for the table
      @return - a list of columns for the table
     */

    protected function GetColumns() {
        return new ColumnList($this->GetPrimaryTable(), array('reviewerid', 'revieweeid', 'name', 'review', 'lastupdated'), array('reviewerid', 'revieweeid'));
    }

    /*
      Retrieves the column list for the view
      @return a list of columns for the view
     */

    protected function GetColumnsView() {
        return new ColumnList($this->GetPrimaryTable(), array('reviewerid', 'revieweeid', 'name', 'review', 'lastupdated', 'reviewerfullname'), array('reviewerid', 'revieweeid'));
    }

    /*
      Retrieves the default sort column name
      @return the full name of the sort column as a string to use as the default
     */

    protected function GetDefaultSortColumn() {
        return 'lastupdated';
    }

    /*
     * Retrieves the security string for accessing the table object
     * @return an array of allowed functions
     */
    public function GetSecurity(){
        return array('read', 'create', 'update', 'delete');
    }
}
?>
