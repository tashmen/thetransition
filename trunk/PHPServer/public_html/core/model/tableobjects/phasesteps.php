<?php

/**
 * Handles database interaction for the phasesteps table
 *
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
