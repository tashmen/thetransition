<?php
/*
 * Defines a list of columns
 * @author jnorcross
 */

class ColumnList {

    private $columns;
    private $count;
    private $keyCount;

    public function __construct($table, $names, $keys) {
        $this->count = 0;
        $this->columns = array();
        foreach ($names as $name) {
            $isKey = in_array($name, $keys);
            $this->columns[] = new Column($table, $name, $isKey);
            $this->count++;
            if ($isKey)
                $this->keyCount++;
        }
    }

    /*
      Retrieves the array of columns
     */

    public function GetColumns() {
        return $this->columns;
    }

    /*
      Constructs an array of column names
     */

    public function GetNames() {
        $names = array();
        foreach ($this->columns as $column) {
            $names[] = $column->GetName();
        }
        return $names;
    }

    /*
      Constructs an array of column full names
     */

    public function GetFullNames() {
        $fullnames = array();
        foreach ($this->columns as $column) {
            $names[] = $column->GetFullName();
        }
        return $fullnames;
    }

    /*
      Constructs an array of all the key columns with their full names
     */

    public function GetFullNameKeys() {
        $keys = array();
        foreach ($this->columns as $column) {
            if ($column->IsKey())
                $keys[] = $column->GetFullName();
        }
        return $keys;
    }

    /*
      Constructs an array of all the key columns with just the name; no table
     */

    public function GetKeys() {
        $keys = array();
        foreach ($this->columns as $column) {
            if ($column->IsKey())
                $keys[] = $column->GetName();
        }
        return $keys;
    }

    /*
      Retrieves the number of columns in the list
     */

    public function GetCount() {
        return $this->count;
    }

    /*
      Retrieves the number of keys in the list
     */

    public function GetKeyCount() {
        return $this->keyCount;
    }

}
?>