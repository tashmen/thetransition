<?php
class Security {
    public static $userid;
    public static $creationdt;
    public static $isAdmin;

    public static function VerifySecurity(iDatabase $database) {
        self::$userid = RequestData::GetRequestData('id1');
        self::$creationdt = RequestData::GetRequestData('id2');

        
        $select = "Select count(*) from users ";
        $where = "where id = (?)"; // and creationdt = (?)";
        $parameters[] = self::$userid;
        //$parameters[] = self::$creationdt;

        $statement = $select . $where;
        $count = $database->rowCount($statement, $parameters);
        if ($count == 1){
            $select = "Select adminflg from users " . $where;
            $records = $database->execute($select, $parameters);
            self::$isAdmin = $records[0]['adminflg'];
            return true;
        }
        throw new Exception("Security: User is not allowed access to this system");
    }

    public static function ValidateColumn($columnName, $columnValue) {
        if ($columnName == 'userid') {
            if (self::$userid != $columnValue && !self::IsAdmin())
            {
                throw new Exception("Security: Attempt to set a record to an invalid user: " . $columnValue);
            }
        }
        return true;
    }
    
    public static function IsAdmin()
    {
        $return = false;
        if(self::$isAdmin == "1"){
            $return = true;
        }
        return $return;
    }
}


