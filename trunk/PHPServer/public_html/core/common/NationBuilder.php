<?php
/*
  Handles sending information to NationBuilder
 */

class NationBuilder {

    private $clientId;
    private $clientSecret;
    private $token;
    private $baseApiUrl;
    private $client;

    public function __construct() {
        $this->clientId = Settings::$nb_clientId;
        $this->clientSecret = Settings::$nb_clientSecret;
        $this->token = Settings::$nb_token;
        $this->baseApiUrl = Settings::$nb_baseApiUrl;
        $this->client = new Client($this->clientId, $this->clientSecret);
        $this->client->setAccessToken($this->token);
        //$this->client->setAccessTokenType(Client::ACCESS_TOKEN_BEARER);
    }

    /*
      Initiates a request to nationbuilder
      @statement - the api call to make; the base api url is added to the given statement
      @parameters - an array of parameters to pass to the request
      @method - the method type to use (Get, Post, Put, Delete)
      @header - an array of header keys to add to the header
     */

    public function Execute($statement, $parameters = array(), $method = Client::HTTP_METHOD_GET, $header = array()) {
        return $this->client->fetch($this->baseApiUrl . $statement, $parameters, $method, $header);
    }

    /*
      Synchronizes data from Nationbuilder to our database
      @database - PDO connection to our MySQL database
     */

    public function SynchronizeUsers($database) {
        $user = new users($database);
        $response = $this->Execute('/api/v1/people');
        $results = $response['result']['results'];
        foreach ($results as $result) {
            $user->createupdate($result);
        }
    }

    /*
      Add some person webhooks so that we can keep data in sync
      @database - PDO connection to our MySQL database
     */

    public function AddWebHooks($database) {
        $statement = '/api/v1/webhooks';
        //$this->postDelete($statement . '/54334275dbf3ec30ac008223');


        $data = array(
            'webhook' => array(
                'version' => 4,
                'url' => 'http://thetransition.comeze.com/api.php?resource=users&action=personcreation',
                'event' => 'person_creation'
            )
        );
        $response = $this->postRequest($statement, $data);
        print_r($response);
        echo '<br/>';

        $data = array(
            'webhook' => array(
                'version' => 4,
                'url' => 'http://thetransition.comeze.com/api.php?resource=users&action=personupdate',
                'event' => 'person_update'
            )
        );
        $response = $this->postRequest($statement, $data);


        print_r($response);
        echo '<br/>';

        print_r($this->Execute($statement));
    }

    /*
      Pushes tags to nationbuilder; This function will not delete tags which are missing.
      @id - the id of the user
      @tags - the tags in array to add
     */

    public function PushTags($id, $tags) {
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
        //DELETE /api/v1/people/:id/taggings/:tag
        $statement = '/api/v1/people/' . $id . '/taggings/' . $tag;
        $this->postDelete($statement);
    }

    /*
      Views all of the nation's webhooks
     */

    public function ViewWebHooks() {
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

        return json_decode($json_response, true);
    }

    /*
      Uses curl to post a delete message to Nationbuilder
      @$statement - the endpoint url to use
     */

    private function postDelete($statement) {
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

        $clientId = '35f177ad5138dd4eaa35f68b7db2561779c514e45a32da55369c89412ff1a170';
        $clientSecret = 'dd72c5ac9e2f9d6b181b67782f4fd60dc48fb022449f2678d4eb7ffce806c745';
        $client = new Client($clientId, $clientSecret);


        $redirectUrl = 'http://thetransition.comeze.com/api.php';
        $authorizeUrl = 'https://thetransition.nationbuilder.com/oauth/authorize';
        //$authUrl = $client->getAuthenticationUrl($authorizeUrl, $redirectUrl); 
        //echo $authUrl; 
        //Generate access token
        echo 'Code value given: ' . $code;
        echo '<br/>';
        //$code = '24a015cf8d03da15baea07b0a23a185cdafc08046939c3fca69ca968761b4407'; 
        // generate a token response

        $accessTokenUrl = 'https://thetransition.nationbuilder.com/oauth/token';
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
        $baseApiUrl = 'https://thetransition.nationbuilder.com';
        $client->setAccessToken($token);
        $response = $client->fetch($baseApiUrl . '/api/v1/people');
        print_r($response);
    }

}
?>

