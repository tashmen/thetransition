<?php

/**
 * Handles database interaction for the taskcomments table
 *
 */
class taskcomments extends TableObject{
    /*
      Retrieves the primary table for the object
      @return - the primary table as a string
     */

    public function GetPrimaryTable(){
        return 'taskcomments';
    }
    
    /*
      Retrieves the view name for the object
      @return - the view of the object
     */

    protected function GetPrimaryTableView() {
        return 'taskcomments';
    }

    /*
      Retrieves the default sort column name
      @return the full name of the sort column as a string to use as the default
     */

    protected function GetDefaultSortColumn(){
        return 'createddate';
    }
    
    /*
     * Retrieves the security string for accessing the table object
     * @return an array of allowed functions
     */
    public function GetSecurity(){
        return array('read', 'update', 'delete');
    }
}