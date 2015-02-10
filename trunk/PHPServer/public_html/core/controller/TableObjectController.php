<?php

class TableObjectController extends AbstractController{
    private $connection;
    //Stores the mapping from resource object to resource classes
    private $resourceToObjectMap;
    //Stores the mapping from actions to function names
    private $actionsToFunctionMap;
    
    public function __construct(iDatabase $connection)
    {
        $this->connection = $connection;
        
        //The key is the value given from the user; changing the key will require changing the frontend logic 
        //The value is the class name that is mapped to the given key
        $this->resourceToObjectMap['objectcategory'] = 'objectcategory';
        $this->resourceToObjectMap['objectpermenance'] = 'objectpermenance';
        $this->resourceToObjectMap['skills'] = 'skills';
        $this->resourceToObjectMap['spaces'] = 'spaces';
        $this->resourceToObjectMap['userobjects'] = 'userobjects';
        $this->resourceToObjectMap['userreviews'] = 'userreviews';
        $this->resourceToObjectMap['userskills'] = 'userskills';
        $this->resourceToObjectMap['userspaces'] = 'userspaces';
        
        //crud operations available
        $this->actionsToFunctionMap['create'] = 'create';
        $this->actionsToFunctionMap['read'] = 'read';
        $this->actionsToFunctionMap['update'] = 'update';
        $this->actionsToFunctionMap['delete'] = 'delete';
        
        //Ext operations available
        $this->actionsToFunctionMap['extdata'] = 'GetExtData';
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
            if($resource == "tableobject" && $action == "gettables")
            {
                $results = array();
                $inputData = new TableRequest();
                foreach($this->resourceToObjectMap as $className)
                {
                    $object = new $className($this->connection, $inputData);
                    $results[] = array('id' => $object->GetPrimaryTable());
                }
                $results = $this->ConvertInputRecordsToOutput($resource, $results);
            }
            else 
            {
                Security::VerifySecurity($this->connection);

                if(array_key_exists($resource, $this->resourceToObjectMap)){
                    $className = $this->resourceToObjectMap[$resource];
                    if(array_key_exists($action, $this->actionsToFunctionMap)){
                        $function = $this->actionsToFunctionMap[$action];
                        $inputData = new TableRequest();
                        $table = new $className($this->connection, $inputData);
                        Logger::LogError("Initiating request for class: " . $className . " and function: " . $function, Logger::debug);
                        $table->$function($inputData);

                        $this->ProcessImageFile($className, $function, $table->GetResults());

                        $results = $this->GetOutput($resource, $table);
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
    
    protected function GetOutput($resource, TableObject $tableObject) {
        $output = $this->ConvertInputRecordsToOutput($resource, $tableObject->GetResults());
        foreach ($tableObject->GetProperties() as $key => $value) {
            $output[$key] = $value;
        }
        return $output;
    }

    /*
      This function assumes that the records have id and imageFile attributes
     */

    protected function ProcessImageFile($table, $action, array $results) {
        if ($action == 'create' || $action == 'update') {
            foreach ($results as $record) {
                $imageFile = $record->imageFile;
                if ($imageFile == "")
                {
                    continue;
                }
                $id = $record->id;
                if ($id == ""){
                    continue;
                }
                //$encodedData = str_replace(' ', '+', $imageFile);
                //$decodedData = base64_decode($encodedData);

                $uriPhp = 'data://' . substr($imageFile, 5);
                $binary = file_get_contents($uriPhp);

                $fileName = Constants::$uploadImages . $table . "/" . $id . ".png"; //Is it safe to just always use .png?
                file_put_contents($fileName, $binary);
            }
        }
        else if ($action == 'delete') {
            foreach ($results as $record) {
                $id = $record->id;
                if ($id == ""){
                    continue;
                }
                $fileName = Constants::$uploadImages . $table . "/" . $id . ".png";
                if (file_exists($fileName)){
                    unlink($fileName);
                }
            }
        }
    }
    
    /*
      Converts the input records from GetRequestData into the proper output format
      @records - The array of input records
      @return - The array of output records in proper format
     */

    private function ConvertInputRecordsToOutput($resource, $records) {
        $results = array();
        $results[$resource] = $records;
        return $results;
    }
}

?>