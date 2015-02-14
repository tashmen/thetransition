<?php
/*
  Handles data interaction for the userskills table
 */

class userskills extends TableObject {
    /*
      Retrieves the primary table for the object
      @return - the primary table as a string
     */

    public function GetPrimaryTable() {
        return "userskills";
    }

    /*
      Retrieves the view name for the object
      @return - the view of the object
     */

    protected function GetPrimaryTableView() {
        return 'userskillsview';
    }

    /*
      Retrieves the column list for the table
      @return - a list of columns for the table
     */

    protected function GetColumns() {
        return new ColumnList($this->GetPrimaryTable(), array('userid', 'skillid', 'userrating'), array('userid', 'skillid'));
    }

    /*
      Retrieves the column list for the view
      @return a list of columns for the view
     */

    protected function GetColumnsView() {
        return new ColumnList($this->GetPrimaryTableView(), array('userid', 'skillid', 'userrating', 'fullname', 'name'), array('userid', 'skillid'));
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
        return array('read', 'create', 'update', 'delete');
    }

    public function create() {
        parent::create();
        $records = $this->GetData();
        $userid = "";

        $skillids;
        foreach ($records as $record) {
            $skillids[] = $record->skillid;
            $userid = $record->userid;
        }

        if ($userid != "") {
            $skills = new skills($this->GetConnection(), $this->GetRequest());
            $skillnames = $skills->GetSkillNames($skillids);
            $nb = new NationBuilder();
            $nb->PushTags($userid, $skillnames);
        }
    }

    public function delete() {
        parent::delete();
        $records = $this->GetData();
        $userid = "";
        $skillids;
        foreach ($records as $record) {
            $skillids[] = $record->skillid;
            $userid = $record->userid;
        }

        if ($userid != "") {
            $skills = new skills($this->GetConnection(), $this->GetRequest());
            $skillnames = $skills->GetSkillNames($skillids);
            $nb = new NationBuilder();
            foreach ($skillnames as $skillname) {
                $nb->DeleteTag($userid, $skillname);
            }
        }
    }
}
