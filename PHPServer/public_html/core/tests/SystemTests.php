<?php

/*
 * Tests for all system functionality to validate that everything is working as expected
 * Tests should not interfere with any preexisting data and should not leave any artifacts.  
 * Any data that is created must be subsequently deleted.
 */
class SystemTests {
    private $testMap;
    
    public function __construct() {
        $this->testMap[] = 'UserReviewsTest';
        $this->testMap[] = 'SkillsTest';
        $this->testMap[] = 'UserSkillsTest';
        $this->testMap[] = 'UserPhaseStepsTest';
    }
    
    public function run($connection)
    {
        //Do not allow running of tests on production server
       if(Settings::$isProdServer){
           return true;
       }
       
       
       try{
           foreach($this->testMap as $test)
           {
               $tester = new $test($connection);
               $tester->Test();
           }
           echo 'If this is the only message then all tests passed';
       }
       catch(Exception $e){
           echo $e->getMessage();
       }
       
    }

   
}
