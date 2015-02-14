<?php

class SkillsTest extends BaseTest {
    public function TestInternal() {
        $this->TestSkills();
    }
    
    private function TestSkills()
    {
        $data = '[{\"id\":\"4\",\"name\":\"test\"}]';
        $user =  $this->GetUser(1);     
        $resource = 'skills';
         
        $response = $this->PostToSelf($resource, 'create', $user, $data);
        $this->Assert('true', $response->success, 'create was not successful');
        $skills = $response->skills[0];
        $this->Assert('4', $skills->id, 'Skill id does not match');
        $this->Assert('test', $skills->name, 'Name does not match');
        
        $data1 = '[{\"id\":\"5\",\"name\":\"ttest\"}]';
        $response = $this->PostToSelf($resource, 'create', $user, $data1);
        $data2 = '[{\"id\":\"6\",\"name\":\"tttest\"}]';
        $response = $this->PostToSelf($resource, 'create', $user, $data2);
        
        $data = '[{\"id\":\"4\",\"name\":\"test2\"}]';
        $response = $this->PostToSelf($resource, 'update', $user, $data);
        $this->Assert('true', $response->success, 'update was not successful');
        $skills = $response->skills[0];
        $this->Assert('4', $skills->id, 'Skill id does not match');
        $this->Assert('test2', $skills->name, 'Name does not match');
           
        $response = $this->PostToSelf($resource, 'read', $user, '', '&limit=1&start=0&page=1');
        $this->Assert('true', $response->success, 'read was not successful');
        $this->Assert('3', $response->total, 'total does not match');
        $this->Assert('1', count($response->skills), 'count does not match');
        $skills = $response->skills[0];
        $this->Assert('4', $skills->id, 'Skill id does not match');
        $this->Assert('test2', $skills->name, 'Name does not match');
        
        $this->PostToSelf($resource, 'delete', $user, $data);
        $this->Assert('true', $response->success, 'delete was not successful');
        
        $this->PostToSelf($resource, 'delete', $user, $data1);
        $this->PostToSelf($resource, 'delete', $user, $data2);
    }
}
