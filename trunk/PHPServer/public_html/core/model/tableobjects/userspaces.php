<?php
/*
  Handles data interaction for the userspaces table
 */

class userspaces extends TableObject {
    /*
      Retrieves the primary table for the object
      @return - the primary table as a string
     */

    protected function GetPrimaryTable() {
        return "userspaces";
    }

    /*
      Retrieves the view name for the object
      @return - the view of the object
     */

    protected function GetPrimaryTableView() {
        return $this->GetPrimaryTable() . 'view';
    }

    /*
      Retrieves the column list for the table
      @return - a list of columns for the table
     */

    protected function GetColumns() {
        return new ColumnList($this->GetPrimaryTable(), array('id', 'userid', 'spaceid', 'privacy', 'name', 'description', 'location', 'latitude', 'longitude'), array('id'));
    }

    /*
      Retrieves the column list for the view
      @return a list of columns for the view
     */

    protected function GetColumnsView() {
        return new ColumnList($this->GetPrimaryTable(), array('id', 'userid', 'spaceid', 'privacy', 'name', 'description', 'location', 'latitude', 'longitude', 'fullname', 'icon'), array('id'));
    }

    /*
      Retrieves the default sort column name
      @return the full name of the sort column as a string to use as the default
     */

    protected function GetDefaultSortColumn() {
        return 'name';
    }

}
?>

