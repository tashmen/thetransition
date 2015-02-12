<?php

/**
 * Holds common functions for all tests
 *
 */
abstract class BaseTest {
    private $users;
    private $con;
    
    abstract public function TestInternal();
    public function Test()
    {
        $this->CreateUsers();
        $this->TestInternal();
        $this->DeleteUsers();
    }
    
   
    
    public function __construct(iDatabase $connection) {
        $this->con = $connection;
    }


    protected function CreateUsers()
    {
        $this->users = array();
        $this->users[] = array('id' => '1', 'first_name' => 'test', 'last_name' => 'user', 'created_at' => date('y-m-d', time()));
        $this->users[] = array('id' => '2', 'first_name' => 'test1', 'last_name' => 'user1', 'created_at' => date('y-m-d', time()));
        foreach($this->users as $user)
        {
            $usersModel = new users($this->con);
            $usersModel->createupdate($user);
        }
    }
    
    protected function DeleteUsers()
    {
        foreach($this->users as $user)
        {
            $this->con->execute('Delete from Users where id = ' . $user['id'], null, false);
        }
    }
    
    protected function GetUser($id)
    {
        foreach($this->users as $user)
        {
            if($user['id'] == $id){
                return $user;
            }
        }
    }
    
    protected function Assert($expected, $actual, $message = '')
    {
        if($expected != $actual){
                echo ('<br> Expected: ' . $expected . '<br> Actual: ' . $actual . '<br> Message: ' . get_class($this) . ' ' . $message . '<br><br>');
                debug_print_backtrace();
        }
    }

    
    
    protected function PostToSelf($resource, $action, $user, $data, $additionalParams = '')
    {
        $ch = curl_init(Settings::$serverLocation . "?resource=" . $resource . '&action=' . $action . 
                '&id1=' . $user['id'] . '&id2=' . $user['created_at'] . '&data=' . $data . $additionalParams);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 
        curl_setopt($ch, CURLOPT_TIMEOUT, '10');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/x-json", "Accept: application/x-json"));

        //$json_data = $data;//json_encode($data);
        //curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);

        $json_response = curl_exec($ch);
        Logger::LogError(print_r($json_response, true), Logger::debug);
        if ($curl_error = curl_error($ch)) {
            throw new Exception($curl_error, oauthException::CURL_ERROR);
        }
        curl_close($ch);

        return json_decode($json_response);
    }
}
