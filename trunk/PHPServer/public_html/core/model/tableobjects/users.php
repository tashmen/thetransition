<?php
/*
  Handles data interaction for the users table
 */

class users extends TableObject {
    /*
      Retrieves the primary table for the object
      @return - the primary table as a string
     */

    public function GetPrimaryTable(){
        return 'users';
    }

    /*
      Retrieves the default sort column name
      @return the full name of the sort column as a string to use as the default
     */

    protected function GetDefaultSortColumn(){
        return 'fullname';
    }
    
    /*
     * Retrieves the security string for accessing the table object
     * @return an array of allowed functions
     */
    public function GetSecurity(){
        return array();
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
        $total = $this->GetConnection()->rowCount("SELECT COUNT(*) FROM users where id = (?)", $parameters);
        if ($total > 0) {//if user exists update
            $param[] = $name;
            $param[] = $creationdt;
            $param[] = $id;
            $this->GetConnection()->execute("UPDATE users set fullname = (?), creationdt = (?) where id = (?)", $param, false);
        } else {//Else create new user
            $parameters[] = $name;
            $parameters[] = $creationdt;
            $this->GetConnection()->execute("Insert into users (id, fullname, creationdt) values (?, ?, ?)", $parameters, false);
        }
    }
}
?>
