<?php
/*
 * Defines a column for a table
 * @author jnorcross
 */

class Column {

    private $table;
    private $name;
    private $isKey;

    const spacer = ".";

    public function __construct($table, $name, $isKey) {
        $this->table = $table;
        $this->name = $name;
        $this->isKey = $isKey;
    }

    /*
      Retrieves the full name of the field
     */

    public function GetFullName() {
        return $this->table . "_" . $this->name;
    }

    /*
      Retrieves just the name of the field without the table
     */

    public function GetName() {
        return $this->name;
    }

    /*
      Retrieves whether or not the field is a key
     */

    public function IsKey() {
        return $this->isKey;
    }

    /*
      Splits a full name into it's parts and returns an array
     */

    public static function SplitName($name) {
        return explode($this->spacer, $name);
    }

}

