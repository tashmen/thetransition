<?php
/*
  Singleton logger class for logging information to files
 */

class Logger {

    public static $logLevel;

    //Log Level constants
    const debug = 1; //Smaller numbers mean that the event is less likely to be logged
    const nationBuilder = 8;
    const info = 10;
    const fatalError = 100; //The larger the number the more likely the event is to be logged

    /*
      Sets the log level for all logger objects
      @level - the level to set the log to; preferably this should be one of the constants defined in this class
     */

    public static function SetLogLevel($level) {
        self::$logLevel = $level;
    }

    /*
      Checks if the given log level is allowed based on the current log level set
      @level - the level to check
     */

    private static function CheckLogLevel($level) {
        $return = false;
        if (self::$logLevel <= $level){
            $return = true;
        }
        return $return;
    }

    /*
      Logs error to the error.log file
      @error - The error message to log
      @level - the log level for the message
     */

    public static function LogError($error, $level) {
        if (self::CheckLogLevel($level)) {
            $req_dump = " Error: " . $error . "\r\n";
            $filename = 'error.log';
            self::LogData($filename, $req_dump);
        }
    }

    /*
      Logs request data to the specified file name
      @filename - the name of the file to log request data to
     */

    public static function LogRequest($filename, $level) {
        if (self::CheckLogLevel($level)) {
            $req_dump = print_r($_REQUEST, TRUE);

            try {
                $json = file_get_contents('php://input');
                $data = json_decode($json, true);
            } catch (Exception $e) {
                $req_dump = $req_dump . " Error: " . $e->getMessage();
            }

            $req_dump = $req_dump . "  Extra Data: " . print_r($data, true) . "\r\n";
            self::LogData($filename, $req_dump);
        }
    }

    /*
      Logs the data to a file
      @filename - the name of the file to log to
      @req_dump - the dumpped request data to log
     */

    public static function LogData($filename, $req_dump) {
        $file = Constants::$mysite . $filename;
        $fp = fopen($file, 'a');
        $filesize = filesize($file);
        if ($filesize > 4000000){
            rename(Constants::$mysite . $filename, Constants::$mysite . $filename . date('m-d-Y-h-i-s', time()));
        }
        fwrite($fp, date('m/d/Y h:i:s ', time()) . $req_dump . "\r\n");
        fclose($fp);
    }

}
?>

