<?php
/*
  Handles data interaction for the users table
 */

class users {
    private $connection;
    
    public function __construct($con) {
        $this->connection = $con;
    }
    
    /*
      This is a nationbuilder webhook for creating/updating users.
      @person - person object as defined by the returned json object from NationBuilder
     */

    public function createupdate($person) {
        Logger::LogRequest('createupdate.log', Logger::nationBuilder);
        $id = $person['id'];
        $name = $person['first_name'] . " " . $person['last_name'];
        $creationdt = $person['created_at'];

        $parameters[] = $id;
        $total = $this->connection->rowCount("SELECT COUNT(*) FROM users where id = (?)", $parameters);
        if ($total > 0) {//if user exists update
            $param[] = $name;
            $param[] = $creationdt;
            $param[] = $id;
            $this->connection->execute("UPDATE users set fullname = (?), creationdt = (?) where id = (?)", $param, false);
        } else {//Else create new user
            $parameters[] = $name;
            $parameters[] = $creationdt;
            $this->connection->execute("Insert into users (id, fullname, creationdt) values (?, ?, ?)", $parameters, false);
        }
    }

}
?>
