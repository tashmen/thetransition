<?php

/*
 * Class for interacting with the userreviews table
 * @author jnorcross
 */
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
    
    /*
     * The list of events this object listens for
     * @return - An array of events to listen for
     */
    public function GetEventListeners()
    {
        return array(EventMessenger::$OnDeleteUser);
    }
    
    /*
     * Deletes suggestions for the deleted user
     * @param $parameters - Array of parameters
     *  First parameter is the id of the user
     */
    public function OnDeleteUser($parameters)
    {
        $this->GetConnection()->execute("Delete from " . $this->GetPrimaryTable(). " where reviewerid = ?", $parameters, false);
        $this->GetConnection()->execute("Delete from " . $this->GetPrimaryTable(). " where revieweeid = ?", $parameters, false);
    }
}
?>
