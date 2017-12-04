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
        $this->GetConnection()->execute("Update " . $this->GetPrimaryTable(). " set userid = 1 where userid = ?", $parameters, false);
    }
    
    public function GetUserForBud($budid)
    {
        $parameters = array();
        $parameters[] = $budid;
        $results = $this->GetConnection()->execute("Select userid from " . $this->GetPrimaryTable() . " where id = (?)", $parameters, true);
        return $results[0]['userid'];
    }
    
    /*
     * Gather bud names based on bud ids.
     * @param ids - The array of ids to retrieve the names for
     * @return an array of skill names
     */
    public function GetBudNames($ids) {
        $names = array();
        if (count($ids) > 0) {
            $statement = "SELECT * FROM userbuds where id in (";
            $values = "";
            $parameters = array();
            foreach ($ids as $id) {
                $parameters[] = $id;
                if ($values != "") {
                    $values = $values . ", ";
                }
                $values = $values . "?";
            }
            $statement = $statement . $values . ")";
            $budnames = $this->GetConnection()->execute($statement, $parameters);
            
            foreach ($budnames as $budname) {
                $names[] = $budname['name'];
            }
        }
        return $names;
    }
    
}
