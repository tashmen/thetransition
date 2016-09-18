<?php

/**
 * Handles database interaction for the phasesteps table
 *
 * @author jnorcross
 */
class phasesteps extends TableObject{
    /*
      Retrieves the primary table for the object
      @return - the primary table as a string
     */

    public function GetPrimaryTable(){
        return 'phasesteps';
    }
    
    /*
      Retrieves the view name for the object
      @return - the view of the object
     */

    protected function GetPrimaryTableView() {
        return 'phasestepsview';
    }

    /*
      Retrieves the default sort column name
      @return the full name of the sort column as a string to use as the default
     */

    protected function GetDefaultSortColumn(){
        return 'plannumber, number';
    }
    
    /*
     * Retrieves the security string for accessing the table object
     * @return an array of allowed functions
     */
    public function GetSecurity(){
        return array('read');
    }
    
    /*
     * Given a list of phase step id's, determine if any of them are required to be completed by the point person
     * returns true if any of the id's need to be completed by the point person
     */
    public function ContainsPointPersonTask($ids)
    {
        $param = "";
        $arrayIds = explode(',', $ids);
        $parameters = array();
        foreach($arrayIds as $id)
        {
            if($param == "")
            {
                $param = "?";
            }
            else 
            {
                $param = $param . ",?";
            }
            $parameters[] = $id;
        }
        $sql =  "Select count(*) from phasesteps where id in (" . $param . ") and pointpersontask = true";
        Logger::LogError($sql, Logger::debug);
        Logger::LogError(print_r($parameters, true), Logger::debug);
        $count = $this->GetConnection()->rowCount($sql, $parameters);
        Logger::LogError($count, Logger::debug);
        return $count > 0;
    }
    
    /*
     * Override to provide html editor for the name
     */
    public function GetExtForm() {
        $form = parent::GetExtForm();
        $items = &$form[0]['items'];
        foreach($items as &$item)
        {
            if($item['name'] == 'name')
            {
                $item['xtype'] = 'htmleditor';
            }
        }
        return $form;
    }
}
