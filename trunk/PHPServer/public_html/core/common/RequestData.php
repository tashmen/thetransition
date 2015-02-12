<?php

/*
* Handles all input data formatting.  All access should be done through this class instead of global variables.
*/
Class RequestData
{
    public static $filterJson = 'json';
    public static function GetRequestData($varName, $filterType = NULL)
    {   
        $value = "";
        if(isset($_REQUEST[$varName]))
        {
            if($filterType == RequestData::$filterJson)
            {
                $json = stripslashes($_REQUEST[$varName]);
                $value = json_decode($json);
            }
            else{
                $value = $_REQUEST[$varName];
            }
        }
        return $value;
    }
}

?>
