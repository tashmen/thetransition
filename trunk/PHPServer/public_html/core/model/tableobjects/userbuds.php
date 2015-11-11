<?php

/*
 * Handles all of the model logic for Buds
 * @author jnorcross
 */
class userbuds extends TableObject {
    /*
      Retrieves the primary table for the object
      @return - the primary table as a string
     */

    public function GetPrimaryTable(){
        return 'userbuds';
    }
    
    /*
      Retrieves the view name for the object
      @return - the view of the object
     */
    
    protected function GetPrimaryTableView() {
        $view = 'userbudsview';
        if(RequestData::GetRequestData('includestatus') == "1")
        {
            $view = 'userbudsmembershipstatusview';
        }
        return $view;
    }

    /*
      Retrieves the default sort column name
      @return the full name of the sort column as a string to use as the default
     */

    protected function GetDefaultSortColumn(){
        return 'name';
    }
    
    /*
     * Retrieves the security string for accessing the table object
     * @return an array of allowed functions
     */
    public function GetSecurity(){
        return array('read', 'create', 'update', 'delete');
    }
    
    /*
      Retrieves whether or not the table has an auto-increment id.  These id's need to be handled and returned back to the user as they are generated so that update works properly.
      Note: We assume that if this is true then the column name is 'id'
      @return true if the table has this field otherwise false
     */

    protected function HasAutoIncrementId() {
        return true;
    }
}
