<?php

/**
 * Class for listening for events that are managed by the EventMessenger
 *
 * @author jnorcross
 */
class EventListener {
    private $class;
    private $event;
    private $connection;
    
    public function __construct(iDatabase $connection, $event, $class) {
        $this->connection = $connection;
        $this->event = $event;
        $this->class = $class;
    }
    
    /*
     * Fires the event for this listener
     * @param $parameters - The array of parameters to pass to the event call
     */
    public function FireEvent($parameters)
    {
        
        Logger::LogError('Firing event with class: ' . $this->class . ' and event: ' . $this->event, Logger::debug);
        $function = $this->event;
        $className = $this->class;
        $class = new $className($this->connection);
        $class->$function($parameters);
    }
    
    /*
     * Retrieves the event from this listener
     * @return The event of this listener
     */
    public function GetEvent()
    {
        return $this->event;
    }
}
