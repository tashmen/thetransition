<?php
/*
 * Abstract controller class for handling basic output operations
 * @author jnorcross
 */
abstract class AbstractController {
    /*
     * Successful data output
     * @data - an array of data to output
     */
    protected function outputSuccess(array $data) {
        $data['success'] = true;
        $this->outputData($data);
    }

    /*
     * Failed data output
     * @message - the error message to output
     */

    protected function outputFailure($message) {
        $results = array();
        $results['success'] = false;
        $results['errortxt'] = $message;
        $this->outputData($results);
    }

    /*
     * Outputs json data back to the requester
     * @data - an array of data to output
     */

    protected function outputData(array $data) {
        $jsonView = new JsonView();
        $jsonView->render($data);
    }
    
    /*
     * Process a resource with the given action
     * @param resource - The resource to invoke the action on
     * @param action - The action to perform on the resource
     * @return array - The data returned from the function
     */
    public function Process($resource, $action)
    {
        try {
            $results = $this->ProcessSpecialAction($resource, $action);
            
            if($results == null)
            {
                Security::VerifySecurity($this->connection);
                if(array_key_exists($resource, $this->resourceToObjectMap)){
                    $className = $this->resourceToObjectMap[$resource];
                    if(array_key_exists($action, $this->actionsToFunctionMap)){
                        $function = $this->actionsToFunctionMap[$action];
                        Logger::LogError("Initiating request for class: " . $className . " and function: " . $function, Logger::debug);
                        $results = $this->ProcessInternal($function, $className, $resource);
                    }
                    else{
                        throw new Exception('Action is not supported: ' . $action);
                    }
                }
                else {
                    throw new Exception('Resource does not exist: ' . $resource);
                }
            }
            $this->outputSuccess($results);
        } catch (Exception $e) {
            Logger::LogError($e->getMessage(), Logger::fatalError);
            $this->outputFailure($e->getMessage());
        }
    }
    
    /*
    * Internal Processing specific to this controller type
    * @param function - The name of the function to call on the provided class
    * @param className - The name of the class to instantiate
    * @param resource - The original resource asked for in the request
    * @return - The results of the processing
    */
    
    protected abstract function ProcessInternal($function, $className, $resource);
    
    /*
     * Processing for special actions
     * @param resource - The name of the resource provided
     * @param action - The name of the action to perform
     */
    protected abstract function ProcessSpecialAction($resource, $action);
    
    
    /*
      Converts the input records from GetRequestData into the proper output format
      @records - The array of input records
      @return - The array of output records in proper format
     */

    protected function ConvertInputRecordsToOutput($resource, $records) {
        $results = array();
        $results[$resource] = $records;
        return $results;
    }
    
}

