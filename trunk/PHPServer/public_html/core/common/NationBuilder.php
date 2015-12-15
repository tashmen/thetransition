<?php
/*
 * Handles sending information to NationBuilder
 * @author jnorcross
 */

class NationBuilder {

    private $clientId;
    private $clientSecret;
    private $token;
    private $baseApiUrl;
    private $client;
    private $enabled;

    public function __construct() {
        $this->clientId = Settings::$nb_clientId;
        $this->clientSecret = Settings::$nb_clientSecret;
        $this->token = Settings::$nb_token;
        $this->baseApiUrl = Settings::$nb_baseApiUrl;
        if($this->clientId != "" && $this->clientSecret != "" && $this->token != "" && $this->baseApiUrl != "")
        {
            $this->client = new Client($this->clientId, $this->clientSecret);
            $this->client->setAccessToken($this->token);
            $this->enabled = true;
        }
        else
        {
            $this->enabled = false;
            Logger::LogError('NationBuilder is disabled.', Logger::debug);
        }
        //$this->client->setAccessTokenType(Client::ACCESS_TOKEN_BEARER);
    }
    
    public function IsEnabled()
    {
        return $this->enabled;
    }

    /*
      Initiates a request to nationbuilder
      @statement - the api call to make; the base api url is added to the given statement
      @parameters - an array of parameters to pass to the request
      @method - the method type to use (Get, Post, Put, Delete)
      @header - an array of header keys to add to the header
     */

    public function Execute($statement, $parameters = array(), $method = Client::HTTP_METHOD_GET, $header = array()) {
        if($this->IsEnabled())
        {
            Logger::LogData("nationbuilderresponse.log", $statement);
            $result = $this->client->fetch($this->baseApiUrl . $statement, $parameters, $method, $header);
            if ($result['code'] != 200) {
                throw new Exception("Nationbuilder response not successful: " . $result['code']);
            }
            Logger::LogData("nationbuilderresponse.log", print_r($result, true));
            return $result;
        }
    }

    /*
      Synchronizes data from Nationbuilder to our database
      @database - PDO connection to our MySQL database
     */

    public function SynchronizeUsers($database) {
        if($this->IsEnabled())
        {
            $user = new users($database);
            $statement = '/api/v1/people';
            $parameters = array();
            $next = '';
            do
            {
                $response = $this->Execute($statement, $parameters);
                $results = $response['result']['results'];
                foreach ($results as $result) {
                    $user->createupdate($result);
                }
                $next = $response['result']['next'];
                Logger::LogError("next value: " . $next, Logger::debug);
                $statement = explode('?', $next);
                $statement = $statement[0];
                Logger::LogError("Statement: " . $statement, Logger::debug);
                $parameters = $this->FindParametersInURL($next);
                Logger::LogError(print_r($parameters, true), Logger::debug);
            } while (next != '' && $statement != '' && count($parameters)>0);
        }
    }
    
    /*
     * Helper function for converting parameters in a url to an array
     */
    private function FindParametersInURL($url){
        $array = array();
        $split = explode('?', $url);
        if(count($split) == 2)
        {
            $params = $split[1];
            $params = explode('&', $params);
            foreach($params as $param){
                $values = explode('=', $param);
                $array[$values[0]] = $values[1];
            }
        }
        return $array;
    }
    
    /*
     * Synchronize a single user base on the id
     * @database - PDO connection to our MySQL DB
     * @id - Id of the user to obtain
     */
    
    public function SynchronizeUser($database, $id)
    {
        if($this->IsEnabled())
        {
            $user = new users($database);
            Logger::LogError("Attempting to sync user with id: " . $id, Logger::debug);
            if($id == '')
                throw new Exception ("Invalid ID");
            $response = $this->Execute('/api/v1/people/' . $id);
            Logger::LogError("Sync User: " . print_r($response, true), Logger::debug);
            $person = $response['result']['person'];
            $user->createupdate($person);
        }
    }

    /*
      Add some person webhooks so that we can keep data in sync
      @database - PDO connection to our MySQL database
     */

    public function AddWebHooks($database) {
        if(!$this->IsEnabled())
        {
            return;
        }
        $statement = '/api/v1/webhooks';
        //$this->postDelete($statement . '/54334275dbf3ec30ac008223');


        $data = array(
            'webhook' => array(
                'version' => 4,
                'url' => Settings::$serverLocation . '?resource=users&action=personcreation',
                'event' => 'person_creation'
            )
        );
        $response = $this->postRequest($statement, $data);
        print_r($response);
        echo '<br/>';

        $data = array(
            'webhook' => array(
                'version' => 4,
                'url' => Settings::$serverLocation . '?resource=users&action=personupdate',
                'event' => 'person_update'
            )
        );
        $response = $this->postRequest($statement, $data);


        print_r($response);
        echo '<br/>';

        print_r($this->Execute($statement));
    }
    
    /*
     * Create the named membership in nationbuilder
     * @param id - the id of the user
     * @param expiration - the date the membership expires
     * @param name - the name of the membership
     * @param reason - the reason for creation
     */
    public function CreateMembership($id, $expiration, $name, $reason)
    {
        if(!$this->IsEnabled())
        {
            return;
        }
        $statement = '/api/v1/people/' . $id . '/memberships';
        $user = array(
            "membership" => array(
                "name" => $name,
                "status" => "active",
                "status_reason" => $reason,
                "expires_on" => $expiration
            )
        );
        $this->postRequest($statement, $user, 'POST');
    }
    
    /*
     * Updates the named membership for a user to the specified expiration date
     * @param id - The id of the user
     * @param expiration - the expiration date for the membership
     * @param name - the name of the membership
     * @param reason - the reason for the update
     */
    public function UpdateMembership($id, $expiration, $name, $reason)
    {
        if(!$this->IsEnabled())
        {
            return;
        }
        $statement = '/api/v1/people/' . $id . '/memberships';
        $user = array(
            "membership" => array(
                "name" => $name,
                "status" => "active",
                "status_reason" => $reason,
                "expires_on" => $expiration
            )
        );
        $this->postRequest($statement, $user, 'PUT');
    }
    
    /*
     * Retrieves the current named Membership for the user
     * @param id - the id of the user
     * @param name - the name of the membership
     * @return The GrantCoin Membership
     * 
     * {
            "name": "premier",
            "person_id": 2,
            "expires_on": null,
            "started_at": "2014-07-21T13:48:51-07:00",
            "created_at": "2014-07-21T13:48:51-07:00",
            "updated_at": "2014-07-21T13:48:51-07:00",
            "status": "active",
            "status_reason": null
        }
     */
    public function GetMembership($id, $name)
    {
        if(!$this->IsEnabled())
        {
            return;
        }
        $statement = '/api/v1/people/' . $id . '/memberships';
        $results = $this->Execute($statement);
        $results = $results['result']['results'];
        foreach($results as $result){
            if ($result['name'] == $name) {
                return $result;
            }
        }
        return null;
    }
    /*
      Pushes tags to nationbuilder; This function will not delete tags which are missing.
      @id - the id of the user
      @tags - the tags in array to add
     */

    public function PushTags($id, $tags) {
        if(!$this->IsEnabled())
        {
            return;
        }
        $statement = '/api/v1/people/' . $id;
        $user = array(
            "person" => array(
                "tags" => $tags
            )
        );
        $this->postRequest($statement, $user, 'PUT');
    }

    /*
      Deletes a tag from a user
      @id - The id of the user
      @tag - the tag to remove
     */

    public function DeleteTag($id, $tag) {
        if(!$this->IsEnabled())
        {
            return;
        }
        //DELETE /api/v1/people/:id/taggings/:tag
        $statement = '/api/v1/people/' . $id . '/taggings/' . $tag;
        $this->postDelete($statement);
    }

    /*
      Views all of the nation's webhooks
     */

    public function ViewWebHooks() {
        if(!$this->IsEnabled())
        {
            return;
        }
        $statement = '/api/v1/webhooks';
        print_r($this->Execute($statement));
    }

    /*
      The client doesn't seem to work with posting requests so created another function for using curl to post json requests to nationbuilder.
      @$statement - The api endpoint to use
      @$data - the data to send to Nationbuilder (non-json encoded)
      @return - the response from the request already decoded into an array
     */

    private function postRequest($statement, $data, $type = 'POST') {
        if(!$this->IsEnabled())
        {
            return;
        }
        $ch = curl_init($this->baseApiUrl . $statement . "?access_token=" . $this->token);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $type);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 
        curl_setopt($ch, CURLOPT_TIMEOUT, '10');
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Accept: application/json"));

        $json_data = json_encode($data);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);

        $json_response = curl_exec($ch);
        if ($curl_error = curl_error($ch)) {
            throw new Exception($curl_error, oauthException::CURL_ERROR);
        }
        curl_close($ch);
        
        Logger::LogData('nationbuilderresponse.log', print_r(json_decode($json_response, true), true));
        return json_decode($json_response, true);
    }

    /*
      Uses curl to post a delete message to Nationbuilder
      @$statement - the endpoint url to use
     */

    private function postDelete($statement) {
        if(!$this->IsEnabled())
        {
            return;
        }
        $ch = curl_init($this->baseApiUrl . $statement . "?access_token=" . $this->token);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); 
        curl_setopt($ch, CURLOPT_TIMEOUT, '10');

        $json_response = curl_exec($ch);
        curl_close($ch);
    }

    /*
      Used to setup the nationbuilder token information which is only needed during intial setup.  This function will print the token and code information.
      @code - the code provided by nationbuilder
     */

    function ClientSetup($code) {
        /* Setup Clients */

        $clientId = Settings::$nb_clientId;
        $clientSecret = Settings::$nb_clientSecret;
        $client = new Client($clientId, $clientSecret);
        $redirectUrl = Settings::$serverLocation;

        if($code=='')
        {
            $authorizeUrl = Settings::$nb_baseApiUrl . '/oauth/authorize';
            $authUrl = $client->getAuthenticationUrl($authorizeUrl, $redirectUrl); 
            echo $authUrl;
            return;
        }
        //Generate access token
        echo 'Code value given: ' . $code;
        echo '<br/>';
        //$code = ''; 
        // generate a token response

        $accessTokenUrl = Settings::$nb_baseApiUrl . '/oauth/token';
        $params = array('code' => $code, 'redirect_uri' => $redirectUrl);
        $response = $client->getAccessToken($accessTokenUrl, 'authorization_code', $params);
        echo '<br/>';
        print_r($response);
        echo '<br/>';
        // set the client token 
        $token = $response['result']['access_token'];
        $client->setAccessToken($token);

        //Test NationBuilder API call
        echo '<br/>';
        print_r('Was given the following token: ' . $token);
        echo '<br/>';
        $baseApiUrl = Settings::$nb_baseApiUrl;
        $client->setAccessToken($token);
        $response = $client->fetch($baseApiUrl . '/api/v1/people');
        print_r($response);
    }

}
