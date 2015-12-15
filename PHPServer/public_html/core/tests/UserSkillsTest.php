<?php

class UserSkillsTest extends BaseTest{
    public function TestInternal() {
        $this->TestUserSkills();
    }
    
    private function TestUserSkills()
    {
        $data = '[{\"userid\":\"1\",\"skillid\":\"2\",\"userrating\":\"3\"}]';
        $user =  $this->GetUser(1);     
        $resource = 'userskills';
         
        $response = $this->PostToSelf($resource, 'create', $user, $data);
        $this->Assert('true', $response->success, 'create was not successful');
        $userskills = $response->userskills[0];
        $this->Assert('1', $userskills->userid, 'userid does not match');
        $this->Assert('2', $userskills->skillid, 'skillid does not match');
        $this->Assert('3', $userskills->userrating, 'userrating does not match');
        
        $data = '[{\"userid\":\"1\",\"skillid\":\"2\",\"userrating\":\"4\"}]';
        $response = $this->PostToSelf($resource, 'update', $user, $data);
        $this->Assert('true', $response->success, 'update was not successful');
        $userskills = $response->userskills[0];
        $this->Assert('1', $userskills->userid, 'userid does not match');
        $this->Assert('2', $userskills->skillid, 'skillid does not match');
        $this->Assert('4', $userskills->userrating, 'userrating does not match');
           
        $additionalData = '&page=1&start=0&limit=25&filter=[{\"property\":\"userid\",\"value\":\"1\",\"operator\":\"ne\"}]';
        $response = $this->PostToSelf($resource, 'read', $user, '', $additionalData);
        $this->Assert('true', $response->success, 'read was not successful');
        $userskills = $response->userskills[0];
        $this->Assert(0, count($userskills), 'Expected no results');
        
        $this->PostToSelf($resource, 'delete', $user, $data);
        $this->Assert('true', $response->success, 'delete was not successful');
    }
}
