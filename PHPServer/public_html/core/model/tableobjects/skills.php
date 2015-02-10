<?php
/*
  Handles data interaction for the skills table
 */

class skills extends TableObject {
    /*
      Retrieves the primary table for the object
      @return - the primary table as a string
     */

    protected function GetPrimaryTable() {
        return "skills";
    }

    /*
      Retrieves the view name for the object
      @return - the view of the object
     */

    protected function GetPrimaryTableView() {
        return $this->GetPrimaryTable();
    }

    /*
      Retrieves the column list for the table
      @return - a list of columns for the table
     */

    protected function GetColumns() {
        return new ColumnList($this->GetPrimaryTable(), array('id', 'name'), array('id'));
    }

    /*
      Retrieves the column list for the view
      @return a list of columns for the view
     */

    protected function GetColumnsView() {
        return $this->GetColumns();
    }

    /*
      Retrieves the default sort column name
      @return the full name of the sort column as a string to use as the default
     */

    protected function GetDefaultSortColumn() {
        return 'name';
    }

    protected function create() {
        throw new Exception("Function not allowed");
    }

    protected function update() {
        throw new Exception("Function not allowed");
    }

    protected function delete() {
        throw new Exception("Function not allowed");
    }

    //Gather skill names based on skill ids.
    public function GetSkillNames($ids) {
        if (count($ids) > 0) {
            $statement = "SELECT * FROM skills where id in (";
            $values = "";
            $parameters = array();
            foreach ($ids as $id) {
                $parameters[] = $id;
                if ($values != "") {
                    $values = $values . ", ";
                }
                $values = $values . "?";
            }
            $statement = $statement . $values . ")";
            $skillnames = $this->connection->execute($statement, $parameters);
            $names = array();
            foreach ($skillnames as $skillname) {
                $names[] = $skillname['name'];
            }
            return $names;
        }
        return array();
    }

}
?>

