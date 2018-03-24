<?php

/**
 * Handles all database interactions for the planphases table
 * @author jnorcross
 */
class planphases extends TableObject{
    /*
      Retrieves the primary table for the object
      @return - the primary table as a string
     */

    public function GetPrimaryTable(){
        return 'planphases';
    }

    /*
      Retrieves the default sort column name
      @return the full name of the sort column as a string to use as the default
     */

    protected function GetDefaultSortColumn(){
        return 'number';
    }
    
    /*
     * Retrieves the security string for accessing the table object
     * @return an array of allowed functions
     */
    public function GetSecurity(){
        return array('read');
    }
    
    /*
     * Retrieves the phase number given a phaseid
     * @param $phaseid - The id of the phase to return the number of
     * @return The phase number
     */
    public function GetNumber($phaseid)
    {
        $sql = "Select number from " . $this->GetPrimaryTable() . " where id = (?)";
        $parameters[] = $phaseid;
        $resultSet = $this->GetConnection()->execute($sql, $parameters);
        Logger::LogError(print_r($resultSet, true), Logger::debug);
        return $resultSet[0]['number'];
    }
    
    /*
     * Retrieves the phase id given a phase number
     * @param $phasenumber - The number of the phase to return the id of
     * @return The phase id
     */
    public function GetId($phasenumber)
    {
        $sql = "Select id from " . $this->GetPrimaryTable() . " where number = (?)";
        $parameters[] = $phasenumber;
        $resultSet = $this->GetConnection()->execute($sql, $parameters);
        Logger::LogError(print_r($resultSet, true), Logger::debug);
        return $resultSet[0]['id'];
    }
}
