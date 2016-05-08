<?php
/*
 * Handles data interaction for the skills table
 * @author jnorcross
 */

class skills extends TableObject {
    
    /*
      Retrieves whether or not the table has an auto-increment id.  These id's need to be handled and returned back to the user as they are generated so that update works properly.
      Note: We assume that if this is true then the column name is 'id'
      @return true if the table has this field otherwise false
     */

    protected function HasAutoIncrementId() {
        return true;
    }
    
    /*
      Retrieves the primary table for the object
      @return - the primary table as a string
     */

    public function GetPrimaryTable() {
        return "skills";
    }

    /*
      Retrieves the default sort column name
      @return the full name of the sort column as a string to use as the default
     */

    protected function GetDefaultSortColumn() {
        return 'name';
    }
    
    /*
     * Retrieves the security string for accessing the table object
     * @return an array of allowed functions
     */
    public function GetSecurity(){
        return array('read', 'create');
    }

    /*
     * Gather skill names based on skill ids.
     * @param ids - The array of ids to retrieve the names for
     * @return an array of skill names
     */
    public function GetSkillNames($ids) {
        $names = array();
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
            $skillnames = $this->GetConnection()->execute($statement, $parameters);
            
            foreach ($skillnames as $skillname) {
                $names[] = $skillname['name'];
            }
        }
        return $names;
    }

}
