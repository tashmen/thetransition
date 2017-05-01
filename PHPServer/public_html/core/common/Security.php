<?php

/*
 * Class for handling all aspects of security and caching logged in user data
 * @author jnorcross
 */
class Security {
    private static $userid;
    private static $secretKey;
    private static $isAdmin;
    private static $pointPersonId;
    private static $latitude;
    private static $longitude;

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
        $counter = 0;
        do
        {
            $count = $database->rowCount($statement, $parameters);
            if ($count == 1){
                $select = "Select adminflg, pointpersonid, latitude, longitude from users " . $where;
                $records = $database->execute($select, $parameters);
                self::$isAdmin = $records[0]['adminflg'];
                self::$pointPersonId = $records[0]['pointpersonid'];
                self::$latitude = $records[0]['latitude'];
                self::$longitude = $records[0]['longitude'];
                return true;
            }
            //User could not be found so try to sync the user from nationbuilder and then check again
            $nb = new NationBuilder();
            $nb->SynchronizeUser($database, self::$userid);
            $counter++;
        }
        while($counter<2);

        
        if(self::$secretKey == '')
        {
            throw new Exception("Security key was not provided. Please refresh the page and try again.");
        }
        
        throw new Exception("Security: User is not allowed access to this system.  Please refresh the page and try again.");
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
    
    /*
     * Retrieves the currently logged in user's latitude
     * @return logged in user's latitude
     */
    public static function GetLoggedInUserLatitude()
    {
        return self::$latitude;
    }
    
    /*
     * Retrieves the currently logged in user's longitude
     * @return logged in user's longitude
     */
    public static function GetLoggedInUserLongitude()
    {
        return self::$longitude;
    }
    
    /*
     * Determine if the user has a location
     * @return true if the user has both a latitude and longitude coordinates
     */
    public static function HasLocation()
    {
        if (Security::GetLoggedInUserLatitude() == '' || Security::GetLoggedInUserLongitude() == '') {
            return false;
        }
        return true;
    }
    
    public static function IsPointPerson($userid, iDatabase $database)
    {
        $sql = "Select count(*) from users where id = (?) and pointpersonid = (?)";
        $parameters[] = $userid;
        $parameters[] = self::$userid;
        return $database->rowCount($sql, $parameters) == 1;
    }
}


