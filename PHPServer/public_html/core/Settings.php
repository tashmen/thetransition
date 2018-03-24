<?php

/**
 * Holds all of the settings needed for the application
 * @author jnorcross
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
    public static $nb_secretToken = '';
    
    //This is the nation slug that nationbuilder will send to us when making a user request.
    public static $nb_nationslug = '';
    
    //Is this server a test server or production server? false = test true = production
    public static $isProdServer = false;
    //If isProdServer if false then specify the location of your server for executing tests
    public static $serverLocation = 'http://127.0.0.1:8888/api.php';
    
    //Host Name of the server
    public static $hostName = '';
    
    public static $loglevel = Logger::debug;
    
    //The cost for a membership in the Transition in US dollars
    public static $membershipCostInUSD = 5;
    //Enable performance statistics such as time it took to execute
    public static $logPerformanceStatistics = false;
}
