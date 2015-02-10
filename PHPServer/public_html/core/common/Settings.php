<?php

/**
 * Holds all of the settings needed for the application
 */
class Settings {
    //MySQL settings for connecting to the database
    public static $mysql_host = "127.0.0.1";
    public static $mysql_database = "thetransition";
    public static $mysql_user = "root";
    public static $mysql_password = "root";
    
    //Nationbuilder settings for connecting with Nationbuilder
    public static $nb_clientId = ''; 
    public static $nb_clientSecret = ''; 
    public static $nb_token = '';
    public static $nb_baseApiUrl = '';
    
    //Is this server a test server or production server? false = test true = production
    public static $isProdServer = false;
    //If isProdServer if false then specify the location of your server for executing tests
    public static $serverLocation = 'http://127.0.0.1:8888/api.php';
    
    public static $loglevel = Logger::debug;
}
?>