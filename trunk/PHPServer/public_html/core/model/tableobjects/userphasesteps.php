<?php

/**
 * Handles database interaction for the userphasesteps table
 *
 */
class userphasesteps extends TableObject{
    /*
      Retrieves the primary table for the object
      @return - the primary table as a string
     */

    public function GetPrimaryTable(){
        return 'userphasesteps';
    }
    
     /*
      Retrieves the view name for the object
      @return - the view of the object
     */

    protected function GetPrimaryTableView() {
        return 'userphasestepsview';
    }

    /*
      Retrieves the default sort column name
      @return the full name of the sort column as a string to use as the default
     */

    protected function GetDefaultSortColumn(){
        return 'phasestepnumber';
    }
    
    /*
     * Retrieves the security string for accessing the table object
     * @return an array of allowed functions
     */
    public function GetSecurity(){
        return array('read');
    }
    
    public function read(){
        parent::read();
        $getCurrentPhase = RequestData::GetRequestData('getcurrentphase');
        if($getCurrentPhase == "1")
        {
            $aryUserPhaseSteps = $this->GetResults();
            $request = new TableRequest(false);
            $phaseSteps = new phasesteps($this->GetConnection(), $request);
            $phaseSteps->read();
            $aryPhaseSteps = $phaseSteps->GetResults();
            $currentPhase = $this->GetCurrentPhaseId($aryUserPhaseSteps, $aryPhaseSteps);
            
            $aryCurrentPhaseSteps = array();
            foreach($aryPhaseSteps as $phaseStep)
            {
                if($phaseStep['planphaseid'] == $currentPhase)
                {
                    $aryCurrentPhaseSteps[] = $phaseStep;
                }
            }
            $aryCurrentUserPhaseSteps = array();
            $aryNewCurrentUserPhaseSteps = array();
            foreach($aryCurrentPhaseSteps as $currentPhaseStep)
            {
                $isFound = false;
                foreach($aryUserPhaseSteps as $userPhaseStep){
                    if($userPhaseStep['phasestepid'] == $currentPhaseStep['id']){
                        $isFound = true;
                        $aryCurrentUserPhaseSteps[] = $userPhaseStep;
                    }
                }
                if(!$isFound){
                    $obj = array('userid' => Security::$userid, 'phasestepid' => $currentPhaseStep['id'], 'completed' => '0');
                    $aryCurrentUserPhaseSteps[] = $obj;
                    $aryNewCurrentUserPhaseSteps[] = (object)$obj;
                }
            }
            $request = new TableRequest(false);
            $request->SetData($aryNewCurrentUserPhaseSteps);
            $newUserPhaseSteps = new userphasesteps($this->GetConnection(), $request);
            $newUserPhaseSteps->create();
            $this->AddProperty("total", '' . count($aryCurrentUserPhaseSteps));
            $this->SetResults($aryCurrentUserPhaseSteps);
        }    
    }
    
    private function GetCurrentPhaseId(array $aryUserPhaseSteps, array $aryPhaseSteps){
        $currentPhase = '';
        $planPhase = '';
        foreach($aryPhaseSteps as $phaseStep){
            $stepId = $phaseStep['id'];
            $isFound = false;
            foreach($aryUserPhaseSteps as $userPhaseSteps){
                if($userPhaseSteps['phasestepid'] == $stepId){
                    $isFound = true;
                    $planPhase = $phaseStep['planphaseid'];
                    if($userPhaseSteps['completed'] != "1"){
                        $currentPhase = $phaseStep['planphaseid'];
                        break;
                    }
                }
            }
            if(!$isFound)//User hasn't started this phase since we can't find it
            {
                $currentPhase = $phaseStep['planphaseid'];
                break;
            }
            if($currentPhase != ''){
                break;
            }
        }
        if($currentPhase == ""){//If we couldn't find any incomplete phase
            if($planPhase == "")//If there weren't any phases found 
            {
                $currentPhase = $aryPhaseSteps[0]['planphaseid'];//start from the beginning
            }
            else
            {
                $currentPhase = $planPhase;//Otherwise return the last completed phase available
            }
        }
        return $currentPhase;
    }
}
