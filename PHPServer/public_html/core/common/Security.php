<?php
class Security {
    public static $userid;
    public static $creationdt;

    public static function VerifySecurity($database) {
        self::$userid = RequestData::GetRequestData('id1');
        self::$creationdt = RequestData::GetRequestData('id2');

        $statement = "Select count(*) from users where id = (?)"; // and creationdt = (?)";
        $parameters[] = self::$userid;
        //$parameters[] = self::$creationdt;

        $count = $database->rowCount($statement, $parameters);
        if ($count == 1){
            return true;
        }
        throw new Exception("User is not allowed access to this system");
    }

    public static function ValidateColumn($columnName, $columnValue) {
        if ($columnName == 'userid') {
            if (self::$userid != $columnValue)
            {
                throw new Exception("Attempt to set a record to an invalid user");
            }
        }
        return true;
    }
    
    public static function IsAdmin()
    {
        return true;
    }
}
?>

