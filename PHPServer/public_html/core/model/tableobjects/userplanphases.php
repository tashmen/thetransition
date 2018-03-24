<?php

/**
 * Handles database interaction for the userplanphases table
 *
 * @author jnorcross
 */
class userplanphases extends TableObject{
    /*
      Retrieves the primary table for the object
      @return - the primary table as a string
     */

    public function GetPrimaryTable(){
        return 'userplanphases';
    }
    
     /*
      Retrieves the view name for the object
      @return - the view of the object
     */

    protected function GetPrimaryTableView() {
        return 'userplanphasesview';
    }

    /*
      Retrieves the default sort column name
      @return the full name of the sort column as a string to use as the default
     */

    protected function GetDefaultSortColumn(){
        return 'planphasenumber';
    }
    
    /*
     * Retrieves the security string for accessing the table object
     * @return an array of allowed functions
     */
    public function GetSecurity(){
        return array('read','update');
    }
    
    /*
     * Validates that the user can modify the record. 
     * @param record - The record to validate
     * @return true if the user has access to modify record otherwiser throw error
     */
    public function ValidateRecord($record) {
        return true;
    }
    
    /*
     * User has started a phase.  Ensure that the phase cannot start multiple times
     * @param phasenumber - The planphase id that is being started
     * @param userid - The id of the user to start the phase for
     */
    public function StartPhase($phaseid, $userid)
    {
        Logger::LogError('Starting Plan Phase', Logger::debug);
        $result = $this->GetUserPlanPhase($phaseid, $userid);
        
        Logger::LogError('Started date is: ' . $result->starteddate, Logger::debug);
        
        if($result->starteddate == ''){
            $result->starteddate = date('Y-m-d');
            $this->SetData(array($result));
            $this->create();
        }
    }
    
    
    
    /*
     * User has completed the phase.  Ensure that the phase cannot end multiple times.
     * @param phasenumber - The planphase number that is being completed
     * @param userid - The id of the user to end the phase for
     */
    public function EndPhase($phasenumber, $userid)
    {
        Logger::LogError('Ending Plan Phase', Logger::debug);
        $planphase = new planphases($this->GetConnection());
        $phaseid = $planphase->GetId($phasenumber);
        
        $result = $this->GetUserPlanPhase($phaseid, $userid);
        
        Logger::LogError('Completion date is: ' . $result->completiondate, Logger::debug);
        
        if($result->completiondate == ''){
            $result->completiondate = date('Y-m-d');
            $this->SetData(array($result));
            $this->create();
        }
    }
    
    /*
     * Retrieves a user plan phase based on a given phaseid and userid or creates a new record if one doesn't exist
     * @returns the user plan phase.
     */
    private function GetUserPlanPhase($phaseid, $userid)
    {
        Logger::LogError('Retrieving User Plan Phase', Logger::debug);
        $tableRequest = new TableRequest(false);
        $filter = Filter::CreateFilter('userid', $userid, Operators::EQUALTO);
        $filters[] = $filter;
        $filter = Filter::CreateFilter('planphaseid', $phaseid, Operators::EQUALTO);
        $filters[] = $filter;
        $tableRequest->SetFilters($filters);
        Logger::LogError('Request is: ' . print_r($tableRequest, true), Logger::debug);
        $userplanphase = new userplanphases($this->GetConnection(), $tableRequest);
        $userplanphase->read();
        $results = $userplanphase->GetResults();
        Logger::LogError(print_r($results, true), Logger::debug);
        
        if(count($results) == 1)
        {
            $result = (object) ($results[0]);
        }
        else
        {
            $result = new stdClass();
            $result->userid = $userid;
            $result->planphaseid = $phaseid;
            $result->starteddate = '';
        }
        Logger::LogError('Result is: ' . print_r($result, true), Logger::debug);
        
        return $result;
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
}
