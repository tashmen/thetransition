<?php
/*
 * Handles data interaction for the usersuggestionscomments table
 * @author jnorcross
 */

class usersuggestionscomments extends TableObject {
    /*
      Retrieves the primary table for the object
      @return - the primary table as a string
     */

    public function GetPrimaryTable() {
        return "usersuggestionscomments";
    }
    
    /*
      Retrieves the view name for the object
      @return - the view of the object
     */

    protected function GetPrimaryTableView() {
        return $this->GetPrimaryTable() . 'view';
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
        return array('read', 'create', 'update');
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
     * Deletes suggestions comments for the deleted user
     * @param $parameters - Array of parameters
     *  First parameter is the id of the user
     */
    public function OnDeleteUser($parameters)
    {
        Logger::LogError('Deleting user suggestions comments for deleting user.', Logger::debug);
        Logger::LogError(print_r($parameters,true), Logger::debug);
        $this->GetConnection()->execute("Delete from " . $this->GetPrimaryTable(). " where userid = ?", $parameters, false);
    }
    
    /*
     * Deletes the comments associated with the suggestions that are to be deleted
     * $parameters - id of the user being delete
     */
    public function BeforeDeleteSuggestionsForUser($parameters)
    {
        Logger::LogError('Deleting user suggestions comments for deleted user suggestions.', Logger::debug);
        Logger::LogError(print_r($parameters,true), Logger::debug);
        $this->GetConnection()->execute("Delete from " . $this->GetPrimaryTable(). " where usersuggestionid in (select id from usersuggestions where userid = ?)", $parameters, false);
    }
}
?>

