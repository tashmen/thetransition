<?php
/*
 * Handles data interaction for the user objects table
 * @author jnorcross
 */

class userobjects extends TableObject {
    /*
      Retrieves the primary table for the object
      @return - the primary table as a string
     */

    public function GetPrimaryTable() {
        return "userobjects";
    }

    /*
      Retrieves the view name for the object
      @return - the view of the object
     */

    protected function GetPrimaryTableView() {
        return "userobjectsview";
    }

    /*
      Retrieves the default sort column name
      @return the full name of the sort column as a string to use as the default
     */

    protected function GetDefaultSortColumn() {
        return 'name';
    }

    /*
      Retrieves whether or not the table has an auto-increment id.  These id's need to be handled and returned back to the user as they are generated so that update works properly.
      Note: We assume that if this is true then the column name is 'id'
      @return true if the table has this field otherwise false
     */

    protected function HasAutoIncrementId() {
        return true;
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