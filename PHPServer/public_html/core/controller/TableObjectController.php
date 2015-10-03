<?php

class TableObjectController extends AbstractController{
    protected $connection;
    //Stores the mapping from resource object to resource classes
    protected $resourceToObjectMap;
    //Stores the mapping from actions to function names
    protected $actionsToFunctionMap;
    
    public function __construct(iDatabase $connection)
    {
        $this->connection = $connection;
        
        $this->MapObjects();
        $this->MapActions();
    }
    
    /*
     * Adds the mapping for the actions available in this controller
     */
    protected function MapActions()
    {
        //crud operations available
        $this->actionsToFunctionMap['create'] = 'create';
        $this->actionsToFunctionMap['read'] = 'read';
        $this->actionsToFunctionMap['update'] = 'update';
        $this->actionsToFunctionMap['delete'] = 'delete';
        
        //Ext operations available
        $this->actionsToFunctionMap['extdata'] = 'GetExtData';
    }
    
    /*
     * Adds the mapping for the objects available in this controller
     */
    protected function MapObjects()
    {
        //The key is the value given from the user; changing the key will require changing the frontend logic 
        //The value is the class name that is mapped to the given key
        $this->resourceToObjectMap['locations'] = 'locations';
        $this->resourceToObjectMap['objectcategory'] = 'objectcategory';
        $this->resourceToObjectMap['objectpermanence'] = 'objectpermanence';
        $this->resourceToObjectMap['phasesteps'] = 'phasesteps';
        $this->resourceToObjectMap['planphases'] = 'planphases';
        $this->resourceToObjectMap['skills'] = 'skills';
        $this->resourceToObjectMap['spaces'] = 'spaces';
        $this->resourceToObjectMap['tasks'] = 'tasks';
        $this->resourceToObjectMap['taskcomments'] = 'taskcomments';
        $this->resourceToObjectMap['userbuds'] = 'userbuds';
        $this->resourceToObjectMap['userbudsmembership'] = 'userbudsmembership';
        $this->resourceToObjectMap['userlocations'] = 'userlocations';
        $this->resourceToObjectMap['userobjects'] = 'userobjects';
        $this->resourceToObjectMap['userphasesteps'] = 'userphasesteps';
        $this->resourceToObjectMap['userreviews'] = 'userreviews';
        $this->resourceToObjectMap['userskills'] = 'userskills';
        $this->resourceToObjectMap['userspaces'] = 'userspaces';
        $this->resourceToObjectMap['users'] = 'users';
    }

    /*
     * Internal Processing specific to this controller type
     * @param function - The name of the function to call on the provided class
     * @param className - The name of the class to instantiate
     * @param resource - The original resource asked for in the request
     * @return - The results of the processing
     */
    
    protected function ProcessInternal($function, $className, $resource)
    {
        $inputData = new TableRequest();
        $table = new $className($this->connection, $inputData);
        if(!(in_array($action, $table->GetSecurity()) || Security::IsAdmin()))
        {
            throw new Exception('Action is not allowed: ' . $action);
        }
        $table->$function();
        $this->ProcessImageFile($className, $function, $table->GetResults());
        return $this->GetOutput($resource, $table);
    }
    
    /*
     * Processing for special actions
     * @param resource - The name of the resource provided
     * @param action - The name of the action to perform
     */
    protected function ProcessSpecialAction($resource, $action)
    {
        $results = null;
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
        return $results;
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
}
