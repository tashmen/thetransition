<?php

/*
 * Class for handling all aspects of security
 * @author jnorcross
 */
class Security {
    private static $userid;
    private static $secretKey;
    private static $isAdmin;
    private static $pointPersonId;

    /*
     * Verifies whether the id's provided match to a valid user in the system.
     * @param database - The database connection
     * @return true if the user is valid
     */
    public static function VerifySecurity(iDatabase $database) {
        self::$userid = RequestData::GetRequestData('id1');
        self::$secretKey = RequestData::GetRequestData('id2');

        $select = "Select count(*) from users ";
        $where = "where id = (?) ";
        $parameters[] = self::$userid;
        if(version_compare(PHP_VERSION, '5.3.0') >= 0)
        {
            $where = $where . "and secretkey = (?)";
            $parameters[] = self::$secretKey;
        }
        
        $statement = $select . $where;
        $count = $database->rowCount($statement, $parameters);
        if ($count == 1){
            $select = "Select adminflg, pointpersonid from users " . $where;
            $records = $database->execute($select, $parameters);
            self::$isAdmin = $records[0]['adminflg'];
            self::$pointPersonId = $records[0]['pointpersonid'];
            return true;
        }
        //User could not be found so try to sync the user from nationbuilder and then check again
        $nb = new NationBuilder();
        $nb->SynchronizeUser($database, self::$userid);
        $count = $database->rowCount($statement, $parameters);
        if ($count == 1){
            $select = "Select adminflg from users " . $where;
            $records = $database->execute($select, $parameters);
            self::$isAdmin = $records[0]['adminflg'];
            return true;
        }
        
        if(self::$secretKey == '')
        {
            throw new Exception("Security key was not provided. Please refresh the page and try again.");
        }
        
        throw new Exception("Security: User is not allowed access to this system.  Please refresh the page and try again.");
    }

    /*
     * Validates that users can only set records with their id.  Requires the column name to be "userid".
     * @param columnName - The name of the column to validate
     * @param columnValue - The value that is trying to be saved
     * @return true if the user is the logged in user accessing the system or the user is an administrator
     */
    public static function ValidateColumn($columnName, $columnValue, iDatabase $database) {
        if ($columnName == 'userid') {
            if (self::$userid != $columnValue && !self::IsAdmin() && !self::IsPointPerson($columnValue, $database))
            {
                throw new Exception("Security: Attempt to set a record to an invalid user: " . $columnValue);
            }
        }
        return true;
    }
    
    /*
     * Checks if the user is an administrator
     * @return true if the user as the isAdmin flag set to 1.
     */
    public static function IsAdmin()
    {
        $return = false;
        if(self::$isAdmin == "1"){
            $return = true;
        }
        return $return;
    }
    
    /*
     * Retrieves the currently logged in user's point person id
     * @return the id of the currently logged in user's point person
     */
    public static function GetLoggedInUserPointPersonId()
    {
        return self::$pointPersonId;
    }
    
    /*
     * Retrieves the currently logged in user's id
     * @return logged in user's id
     */
    public static function GetLoggedInUser()
    {
        return self::$userid;
    }
    
    public static function IsPointPerson($userid, iDatabase $database)
    {
        $sql = "Select count(*) from users where id = (?) and pointpersonid = (?)";
        $parameters[] = $userid;
        $parameters[] = self::$userid;
        return $database->rowCount($sql, $parameters) == 1;
    }
}


