<?php


/**
 * Test class for UserPhaseSteps
 */
class UserPhaseStepsTest extends BaseTest{
    public function TestInternal() {
        $this->SetupPlanPhases();
        $this->SetupPhaseSteps();
        $this->TestUserPhaseSteps();
        $this->TeardownPhaseSteps();
        $this->TeardownPlanPhases();
    }
    
    public function TestUserPhaseSteps(){
        $data = '&getcurrentphase=1';
        $user =  $this->GetUser(1);     
        $resource = 'userphasesteps';
         
        //Verify that read will return the first set of steps if the user has no steps completed
        $response = $this->PostToSelf($resource, 'read', $user, $data);
        $this->Assert(true, $response->success, 'Reading current user phasesteps was not successful');
        $userPhaseSteps = $response->userphasesteps[0];
        $this->Assert("1", $userPhaseSteps->userid, 'userid does not match');
        $this->Assert("1", $userPhaseSteps->phasestepid, 'phasestepid does not match');
        $this->Assert("0", $userPhaseSteps->completed, 'completed does not match');
        
        $userPhaseSteps = $response->userphasesteps[1];
        $this->Assert("1", $userPhaseSteps->userid, 'userid does not match');
        $this->Assert("2", $userPhaseSteps->phasestepid, 'phasestepid does not match');
        $this->Assert("0", $userPhaseSteps->completed, 'completed does not match');
        
        $userPhaseSteps = $response->userphasesteps[2];
        $this->Assert("1", $userPhaseSteps->userid, 'userid does not match');
        $this->Assert("3", $userPhaseSteps->phasestepid, 'phasestepid does not match');
        $this->Assert("0", $userPhaseSteps->completed, 'completed does not match');
        
        $this->Assert("3", $response->total, "total does not match");
        
        
        //Complete one step
        $data = '[{\"userid\":\"1\",\"phasestepid\":\"1\",\"completed\":\"1\"}]';
        $response = $this->PostToSelf($resource, 'update', $user, $data);
        $this->Assert(true, $response->success, 'update userphasestep was not successful');
        
        //Validate it comes back completed
        $data = '&getcurrentphase=1';
        $response = $this->PostToSelf($resource, 'read', $user, $data);
        $this->Assert(true, $response->success, 'Reading current user phasesteps was not successful');
        $userPhaseSteps = $response->userphasesteps[0];
        $this->Assert("1", $userPhaseSteps->userid, 'userid does not match');
        $this->Assert("1", $userPhaseSteps->phasestepid, 'phasestepid does not match');
        $this->Assert("1", $userPhaseSteps->completed, 'completed does not match');
        
        $this->Assert("3", $response->total, "total does not match");
        
        //update the rest of the steps
        $data = '[{\"userid\":\"1\",\"phasestepid\":\"2\",\"completed\":\"1\"}]';
        $response = $this->PostToSelf($resource, 'update', $user, $data);
        $this->Assert(true, $response->success, 'update userphasestep was not successful');
        
        $data = '[{\"userid\":\"1\",\"phasestepid\":\"3\",\"completed\":\"1\"}]';
        $response = $this->PostToSelf($resource, 'update', $user, $data);
        $this->Assert(true, $response->success, 'update userphasestep was not successful');
        
        //Validate that the user is brought over to phase 2
        $data = '&getcurrentphase=1';
        $response = $this->PostToSelf($resource, 'read', $user, $data);
        $this->Assert(true, $response->success, 'Reading current user phasesteps was not successful');
        $userPhaseSteps = $response->userphasesteps[0];
        $this->Assert("1", $userPhaseSteps->userid, 'userid does not match');
        $this->Assert("4", $userPhaseSteps->phasestepid, 'phasestepid does not match');
        $this->Assert("0", $userPhaseSteps->completed, 'completed does not match');
        
        //Delete all data that was inserted
        $data = '[{\"userid\":\"1\",\"phasestepid\":\"1\",\"completed\":\"1\"}]';
        $response = $this->PostToSelf($resource, 'delete', $user, $data);
        $this->Assert(true, $response->success, 'delete userphasestep was not successful');
        
        $data = '[{\"userid\":\"1\",\"phasestepid\":\"2\",\"completed\":\"1\"}]';
        $response = $this->PostToSelf($resource, 'delete', $user, $data);
        $this->Assert(true, $response->success, 'delete userphasestep was not successful');
        
        $data = '[{\"userid\":\"1\",\"phasestepid\":\"3\",\"completed\":\"1\"}]';
        $response = $this->PostToSelf($resource, 'delete', $user, $data);
        $this->Assert(true, $response->success, 'delete userphasestep was not successful');
    }
    
    public function SetupPhaseSteps(){
        $data = '[{\"id\":\"1\",\"planphaseid\":\"1\",\"name\":\"Phase0Step1\",\"number\":\"0\"}]';
        $user =  $this->GetUser(1);     
        $resource = 'phasesteps';
         
        $response = $this->PostToSelf($resource, 'create', $user, $data);
        $this->Assert(true, $response->success, 'create phasesteps was not successful');
        
        $data = '[{\"id\":\"2\",\"planphaseid\":\"1\",\"name\":\"Phase0Step2\",\"number\":\"1\"}]';
        $response = $this->PostToSelf($resource, 'create', $user, $data);
        $this->Assert(true, $response->success, 'create phasesteps was not successful');
        
        $data = '[{\"id\":\"3\",\"planphaseid\":\"1\",\"name\":\"Phase0Step3\",\"number\":\"2\"}]';
        $response = $this->PostToSelf($resource, 'create', $user, $data);
        $this->Assert(true, $response->success, 'create phasesteps was not successful');
        
        $data = '[{\"id\":\"4\",\"planphaseid\":\"2\",\"name\":\"Phase1Step1\",\"number\":\"0\"}]';
        $response = $this->PostToSelf($resource, 'create', $user, $data);
        $this->Assert(true, $response->success, 'create phasesteps was not successful');
    }
    
    public function TeardownPhaseSteps(){
        $data = '[{\"id\":\"1\",\"planphaseid\":\"1\",\"name\":\"Phase0Step1\",\"number\":\"0\"}]';
        $user =  $this->GetUser(1);     
        $resource = 'phasesteps';
         
        $response = $this->PostToSelf($resource, 'delete', $user, $data);
        
        $data = '[{\"id\":\"2\",\"planphaseid\":\"1\",\"name\":\"Phase0Step2\",\"number\":\"1\"}]';
        $response = $this->PostToSelf($resource, 'delete', $user, $data);
        
        $data = '[{\"id\":\"3\",\"planphaseid\":\"1\",\"name\":\"Phase0Step3\",\"number\":\"2\"}]';
        $response = $this->PostToSelf($resource, 'delete', $user, $data);
        
        $data = '[{\"id\":\"4\",\"planphaseid\":\"2\",\"name\":\"Phase1Step1\",\"number\":\"0\"}]';
        $response = $this->PostToSelf($resource, 'delete', $user, $data);
    }
    
    public function SetupPlanPhases(){
        $data = '[{\"id\":\"1\",\"name\":\"Phase0\",\"number\":\"0\"}]';
        $user =  $this->GetUser(1);     
        $resource = 'planphases';
         
        $response = $this->PostToSelf($resource, 'create', $user, $data);
        $this->Assert(true, $response->success, 'create planphases was not successful');
        
        $data = '[{\"id\":\"2\",\"name\":\"Phase1\",\"number\":\"1\"}]';
        $response = $this->PostToSelf($resource, 'create', $user, $data);
        $this->Assert(true, $response->success, 'create planphases was not successful');
    }
    
    public function TeardownPlanPhases(){
        $data = '[{\"id\":\"1\",\"name\":\"Phase0\",\"number\":\"0\"}]';
        $user =  $this->GetUser(1);     
        $resource = 'planphases';
         
        $response = $this->PostToSelf($resource, 'delete', $user, $data);
        
        $data = '[{\"id\":\"2\",\"name\":\"Phase1\",\"number\":\"1\"}]';
        $response = $this->PostToSelf($resource, 'delete', $user, $data);
    }
    
}
