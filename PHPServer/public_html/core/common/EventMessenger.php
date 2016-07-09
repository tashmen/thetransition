<?php

/**
 * Class for Handling Events
 *
 * @author jnorcross
 */
class EventMessenger {
    /*
     * OnDeleteUser Event
     * Parameter 0 is the Id of the User that is being deleted
     */
    public static $OnDeleteUser = 'OnDeleteUser';
    
    
    private $tableObjectController;
    private $connection;
    private $listeners;
    
    public function __construct(iDatabase $connection) {
        $this->connection = $connection;
        $this->tableObjectController = new TableObjectController($connection);
        
        //Register all listeners from all objects
        foreach($this->tableObjectController->GetResourceToObjectMap() as $className)
        {
            $class = new $className($connection);
            foreach($class->GetEventListeners() as $event)
            {
                $listener = new EventListener($connection, $event, $className);
                $this->RegisterListener($listener);
            }
        }
    }
    
    /*
     * Function to register a listener to this EventMessenger
     * @param $listener - The listener to register
     */
    public function RegisterListener($listener)
    {
        $this->listeners[] = $listener;
    }
    
    /*
     * Function to fire an event
     * @param $event - The Event to fire
     * @param $parameters - The parameters to pass to the event
     */
    public function FireEvent($event, $parameters)
    {
        foreach($this->listeners as $listener)
        {
            if($listener->GetEvent() == $event)
            {
                $listener->FireEvent($parameters);    
            }
        }
    }
}
