<?php

/*
 * Class for handling all aspects of security
 * @author jnorcross
 */
class Security {
    public static $userid;
    public static $secretKey;
    public static $isAdmin;

    /*
     * Verifies whether the id's provided match to a valid user in the system.
     * @param database - The database connection
     * @return true if the user is valid
     */
    public static function VerifySecurity(iDatabase $database) {
        self::$userid = RequestData::GetRequestData('id1');
        self::$secretKey = RequestData::GetRequestData('id2');
        
        if(self::$secretKey == '')
        {
            throw new Exception("Security key was not provided.");
        }

        $select = "Select count(*) from users ";
        $where = "where id = (?) and secretKey = (?)";
        $parameters[] = self::$userid;
        $parameters[] = self::$secretKey;

        $statement = $select . $where;
        $count = $database->rowCount($statement, $parameters);
        if ($count == 1){
            $select = "Select adminflg from users " . $where;
            $records = $database->execute($select, $parameters);
            self::$isAdmin = $records[0]['adminflg'];
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
        throw new Exception("Security: User is not allowed access to this system.  Please refresh the page and try again.");
    }

    /*
     * Validates that users can only set records with their id.  Requires the column name to be "userid".
     * @param columnName - The name of the column to validate
     * @param columnValue - The value that is trying to be saved
     * @return true if the user is the logged in user accessing the system or the user is an administrator
     */
    public static function ValidateColumn($columnName, $columnValue) {
        if ($columnName == 'userid') {
            if (self::$userid != $columnValue && !self::IsAdmin())
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
}


