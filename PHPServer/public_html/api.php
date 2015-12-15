<?php

date_default_timezone_set('Etc/UTC');

register_shutdown_function( "fatal_handler" );

function fatal_handler()
{
    $error = error_get_last();

    if( $error !== NULL) {
        $errno   = $error["type"];
        $errfile = $error["file"];
        $errline = $error["line"];
        $errstr  = $error["message"];
        if($errno != 8)//Do not log 'notice' errors
        {
            Logger::LogError("Error # " . $errno . " \n File: " . $errfile . " \n Line: " . $errline . " \n Message: " . $errstr, Logger::fatalError);
        }
    }
}

/* auto load all classes */

function __autoload($class_name) {
    $autoloadLocations = array('common', 'model', 'view', 'tableobjects', 'controller', 'tests', 'grantcoin');
    $filename = $class_name . '.php';
    foreach ($autoloadLocations as $location) {
        $file = Constants::$$location . $filename;
        if (file_exists($file)) {
            include_once($file);
        }
    }
    return false;
}

/*
 * Constants
 * This class must be declared within api.php otherwise autoload may not work properly
 */

class Constants {
    public static $mysite;
    public static $uploadImages;
    public static $coreLocation;
    public static $thirdPartyUtils;
    public static $common;
    public static $model;
    public static $view;
    public static $tableobjects;
    public static $controller;
    public static $tests;
    public static $grantcoin;

    public static function InitConstants() {
        Constants::$mysite = dirname(__FILE__) . '/';
        Constants::$coreLocation = Constants::$mysite . 'core/';
        Constants::$uploadImages = Constants::$mysite . 'upload/images/';
        Constants::$thirdPartyUtils = Constants::$coreLocation . 'thirdpartyutils/';
        Constants::$common = Constants::$coreLocation . 'common/';
        Constants::$model = Constants::$coreLocation . 'model/';
        Constants::$view = Constants::$coreLocation . 'view/';
        Constants::$controller = Constants::$coreLocation . 'controller/';
        Constants::$tableobjects = Constants::$model . 'tableobjects/';
        Constants::$tests = Constants::$coreLocation . 'tests/';
        Constants::$grantcoin = Constants::$coreLocation . 'grantcoin/';
    }
}

Constants::InitConstants();

require(Constants::$thirdPartyUtils . 'Oauth2/Client.php');
require(Constants::$thirdPartyUtils . 'Oauth2/GrantType/IGrantType.php');
require(Constants::$thirdPartyUtils . 'Oauth2/GrantType/AuthorizationCode.php');


Logger::SetLogLevel(Settings::$loglevel);
try
{
    $handler = new BaseObjectHandler();
    $handler->handleRequest();
} catch (Exception $e) {
    Logger::LogError($e->getMessage(), Logger::fatalError);
    echo 'Fatal Error Occurred. Terminating.';
}

//Objects and Functions below here
//********************************

/*
  Handler for doing whatever is needed for every php request
 */
class BaseObjectHandler {

    private $con;

    public function __construct() {
        // Create connection
        $this->con = new MySqlDB();
    }

    /*
      Determines how to handle a request
     */

    public function handleRequest() {
        Logger::LogRequest('incoming.log', Logger::debug);

        $resource = RequestData::GetRequestData('resource');
        $action = RequestData::GetRequestData('action');
        $command = RequestData::GetRequestData('command');

        if($command == 'grantcoin')
        {
            $controller = new GrantCoinController($this->con);
            $controller->Process($resource, $action);
        }
        //Check if this is a nationbuilder webhook:
        else if($resource == "users" && (action == "personupdate" || action == "personcreation"))
        {
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);
            if ($data['nation_slug'] == 'thetransition') {
                $person = $data['payload']['person'];
                $user = new users($this->con);
                $user->createupdate($person);
            }
        }
        else if ($resource == "" || $action == "") 
        {
            $this->processAdministrationHandlers();
        } 
        else 
        {
            $controller = new TableObjectController($this->con);
            $controller->Process($resource, $action);
        }
    }

    private function processAdministrationHandlers() {
        //Check if this is a cron job execution
        /*
          if(RequestData::GetRequestData('cronjob') == '1')
          {

          $argv = array();
          $argv[0] = 'test';
          $argv[1] = 'automatedemail';
          Logger::LogError("processing cron job from request", Logger::debug);
          }
          if (isset($argv)) {
          Logger::LogError((extension_loaded('openssl')?'SSL loaded':'SSL not loaded')."\n", Logger::debug);
          $cronjob = new CronJob($argv);
          $cronjob->ProcessJob();
          Logger::LogError("processed cron job", Logger::debug);
          }
         */
        $nb = new NationBuilder();
        $code = RequestData::GetRequestData('code');
        if ($code != "" || RequestData::GetRequestData('setupnbclient') == 1) {
            $nb->ClientSetup($code);
        }
		
		if(RequestData::GetRequestData('addwebhooks')==1)
		{
			$nb->AddWebHooks($this->con);
		}
		if(RequestData::GetRequestData('viewwebhooks')==1)
		{
			$nb->ViewWebHooks();
		}
        
        if(RequestData::GetRequestData('pushMembership')== 1){
            $nb->PushMembership('18', 'Contributor', '10/19/2015');
        }
        
        if (RequestData::GetRequestData('sync') == 1) {
            if(RequestData::GetRequestData('userid') != '')
                $nb->SynchronizeUser ($this->con, RequestData::GetRequestData('userid'));
            else $nb->SynchronizeUsers($this->con);
        }
       
        $runTests = RequestData::GetRequestData('runtests');
        if($runTests == "1")
        {
            $tests = new SystemTests();
            $tests->run($this->con);
        }
        
        $usdValue = RequestData::GetRequestData('usdvalue');
        $grtValue = RequestData::GetRequestData('grtvalue');
        $grantCoin = new GrantCoin();
        if($usdValue != '')
        {
            echo '<br> Buying ' . $usdValue . ' USD would cost ' . $grantCoin->ConvertBuyUSDToGRT($usdValue) . ' GRT';
            echo '<br> Selling ' . $usdValue . ' USD would provide ' . $grantCoin->ConvertSellUSDToGRT($usdValue) . ' GRT';
        }
        if($grtValue != '')
        {
            echo '<br> Buying ' . $grtValue . ' GRT would cost ' . $grantCoin->ConvertBuyGRTToUSD($grtValue) . ' USD';
            echo '<br> Selling ' . $grtValue . ' GRT would provide ' . $grantCoin->ConvertSellGRTToUSD($grtValue) .  ' USD';
        }
        
    }
}

/*
  class CronJob
  {
  private $jobtype;
  public function __construct($arguments)
  {
  $this->jobtype = $arguments[1];//arguments[0] point to this script
  }

  /*
  Processes the job based on the job type
  Note job types are passed in via the cron job execution script which should look like this:
  {php -f /home/a1466265/} public_html/api.php automatedemail
  The above item in brackets is required and set automatically by the cron job UI that is available.
 *//*
  public function ProcessJob()
  {
  if($this->jobtype == "automatedemail")
  {
  $this->CheckAutomatedEmail();
  }
  }

  private function CheckAutomatedEmail()
  {
  //Query the autoresponderemails table using read()
  //For each object see if any users fit the rule
  //If rule is true
  //Send email to user

  echo phpinfo();

  $fp = fsockopen('smtp.gmail.com', 587, $errno, $errstr, 5);
  if (!$fp) {
  echo "port is blocked";
  } else {
  // port is open and available
  echo "Port is open";
  fclose($fp);
  }

  $fp = stream_socket_client("tcp://smtp.gmail.com:587", $errno, $errstr, 5);
  if (!$fp) {
  echo "$errstr ($errno)<br />\n";
  } else {
  while (!feof($fp)) {
  echo fgets($fp, 1024);
  }
  fclose($fp);
  }
  /*
  //Create a new SMTP instance
  $smtp = new SMTP;


  //Enable connection-level debug output
  $smtp->do_debug = SMTP::DEBUG_CONNECTION;


  try {
  //Connect to an SMTP server
  if ($smtp->connect('smtp.gmail.com', 25)) {
  //Say hello
  if ($smtp->hello(Settings::$hostName)) { //Put your host name in here
  //Authenticate
  if ($smtp->authenticate('jonmnorcross@gmail.com', 'spanish2')) {
  echo "Connected ok!";
  } else {
  throw new Exception('Authentication failed: ' . $smtp->getLastReply());
  }
  } else {
  throw new Exception('HELO failed: '. $smtp->getLastReply());
  }
  }
  else {
  throw new Exception('Connect failed');
  }
  } catch (Exception $e) {
  echo 'SMTP error: '. $e->getMessage(), "\n";
  }
  //Whatever happened, close the connection.
  $smtp->quit(true);

  /*
  try
  {
  $email = new Email();
  $email->CreateMessage("jonmnorcross@gmail.com", "Jon", "jonmnorcross@gmail.com", "Jon", "Test Email", "Test message, am I <b>bold</b>?<br><br>NO!");
  $email->SendMessage();
  }
  catch (phpmailerException $e)
  {
  Logger::LogError($e->errorMessage(), Logger::fatalError);
  }
 *//*
  }
  }

  class Email
  {
  private $phpmailer;
  public function __construct()
  {
  //Create a new PHPMailer instance
  $this->phpmailer = new PHPMailer();

  //Tell PHPMailer to use SMTP
  $this->phpmailer->isSMTP();

  //Enable SMTP debugging
  // 0 = off (for production use)
  // 1 = client messages
  // 2 = client and server messages
  $this->phpmailer->SMTPDebug = 4;

  //Ask for HTML-friendly debug output
  $this->phpmailer->Debugoutput = 'html';

  //Set the hostname of the mail server
  $this->phpmailer->Host = 'smtp.gmail.com';

  //Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
  $this->phpmailer->Port = 587;

  //Set the encryption system to use - ssl (deprecated) or tls
  $this->phpmailer->SMTPSecure = 'tls';

  //Whether to use SMTP authentication
  $this->phpmailer->SMTPAuth = true;

  //Username to use for SMTP authentication - use full email address for gmail
  $this->phpmailer->Username = "jonmnorcross@gmail.com";

  //Password to use for SMTP authentication
  $this->phpmailer->Password = "spanish2";
  }

  /*
  Add the message attributes to the email
  @param $fromAddress - the email address from where it originated
  @param $fromNam - the name of the user where the email originates
  @param $toAddress - the email address of the recipient
  @param $toAddressName - the name of the recipient
  @param $subject - the subject line of the email
  @param $htmlMsg - the message to send in html format
 *//*
  public function CreateMessage($fromAddress, $fromName, $toAddress, $toAddressName, $subject, $htmlMsg)
  {
  //Set who the message is to be sent from
  $this->phpmailer->setFrom($fromAddress, $fromName);

  //Set who the message is to be sent to
  $this->phpmailer->addAddress($toAddress, $toAddressName);

  //Set the subject line
  $this->phpmailer->Subject = $subject;

  //Read an HTML message body from an external file, convert referenced images to embedded,
  //convert HTML into a basic plain-text alternative body
  $this->phpmailer->msgHTML($htmlMsg);


  /* If needed at later point adding our own alt text or attachments is posssible
  //Replace the plain text body with one created manually
  $mail->AltBody = 'This is a plain-text message body';

  //Attach an image file
  $mail->addAttachment('images/phpmailer_mini.png');
 *//*
  }

  public function SendMessage()
  {
  //send the message, check for errors
  if (!$this->phpmailer->send()) {
  throw new Exception($this->phpmailer->ErrorInfo);
  }
  }
  } */
?>