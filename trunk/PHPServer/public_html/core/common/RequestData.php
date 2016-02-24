<?php

/*
 * Handles all input data formatting.  All access should be done through this class instead of global variables.
 * @author jnorcross
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
                //In php5.5 strip slashes no longer needed
                if (version_compare(PHP_VERSION, '5.3.0') < 0)
                {
                    $json = stripslashes($_REQUEST[$varName]);
                }
                else $json = $_REQUEST[$varName];
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
