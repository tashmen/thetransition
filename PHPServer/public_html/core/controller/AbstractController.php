<?php

class AbstractController {
    /*
      Successful json data output
      @data - an array of data to output
     */
    protected function outputSuccess(array $data) {
        $data['success'] = true;
        $this->outputData($data);
    }

    /*
      Failed json data output
      @message - the error message to output
     */

    protected function outputFailure($message) {
        $results = array();
        $results['success'] = false;
        $results['errortxt'] = $message;
        $this->outputData($results);
    }

    /*
      Outputs json data back to the requester
      @data - an array of data to output
     */

    protected function outputData(array $data) {
        $jsonView = new JsonView();
        $jsonView->render($data);
    }
}

?>