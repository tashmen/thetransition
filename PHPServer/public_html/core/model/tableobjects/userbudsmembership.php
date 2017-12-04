<?php

/**
 * Handles the logic for interacting with the userbudsmembership table
 *
 * @author jnorcross
 */
class userbudsmembership extends TableObject{
    /*
      Retrieves the primary table for the object
      @return - the primary table as a string
     */

    public function GetPrimaryTable(){
        return 'userbudsmembership';
    }
    
    /*
      Retrieves the view name for the object
      @return - the view of the object
     */
    
    protected function GetPrimaryTableView() {
        return 'userbudsmembershipview';
    }
    

    /*
      Retrieves the default sort column name
      @return the full name of the sort column as a string to use as the default
     */

    protected function GetDefaultSortColumn(){
        return 'userbudname';
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
        $this->GetConnection()->execute("Delete from " . $this->GetPrimaryTable(). " where userid = ?", $parameters, false);
    }
    
    /*
     * Validates that users can modify the record
     * @param record - The record to validate
     * @return true if the user has access to modify record otherwise throw error
     */
    public function ValidateRecord($record) {
        $bIsValid = true;
        try
        {
            parent::ValidateRecord($record);
        } catch (Exception $ex) {
            $bIsValid = false;
        }
        if(!bIsValid)
        {
            $userBud = new userbuds($this->GetConnection());
            $userid = $userBud->GetUserForBud($record->userbudid);
            if ($userid != Security::GetLoggedInUser())
            {
                throw new Exception("Security: The record you are modifying does not belong to you or you are not the owner of the bud.");
            }
        }
        return true;
    }
    
    public function create() {
        parent::create();
        $records = $this->GetData();
        $this->UpdateTags($records);
    }
    
    public function update() {
        parent::update();
        $records = $this->GetData();
        $this->UpdateTags($records);
    }

    public function delete() {
        parent::delete();
        $records = $this->GetData();
        $this->UpdateTags($records, true);
    }
    
    /**
     * Updates the tags based on membership in a bud
     * @param records - The records to push updates for
     */
    public function UpdateTags($records, $isFullDelete = false){
        $userid = "";

        $budidsbyuser;
        $budidsbyuserdelete;
        foreach ($records as $record) {
            $userid = $record->userid;
            if($record->status == 2 && !$isFullDelete)
            {
                $budidsbyuser[$userid][] = $record->userbudid;
            }
            else{
                $budidsbyuserdelete[$userid][] = $record->userbudid;
            }
        }

        $userbuds = new userbuds($this->GetConnection(), $this->GetRequest());
        $nb = new NationBuilder();
        
        foreach ($budidsbyuser as $userid => $budids){
            $budnames = $userbuds->GetBudNames($budids);
            $nb->PushTags($userid, $budnames);
        }
        
        foreach ($budidsbyuserdelete as $userid => $budids){
            $budnames = $userbuds->GetBudNames($budids);
            $nb->DeleteTags($userid, $budnames);
        }
    }
    
}
