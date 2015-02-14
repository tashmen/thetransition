<?php


/**
 * Handles all of the conversions to Ext objects
 *
 */
class Ext {
    
    /*
     * Builds an Ext form object from an array of ColumnSchema objects
     * @param columnSchema - array of ColumnSchema objects
     * @return array of form items
     */
    public static function BuildFormObject(array $columnSchema)
    {
        $fieldSet = array(
            xtype => 'fieldset',
            collapsible => false,
            border => false,
            margin => '5',
            defaults => array(labelAlign => 'right', width => '100%')
        );
        
        $itemObject = array();
        foreach($columnSchema as $column)
        {
            $properties = self::GetExtFormFieldPropertiesByType($column);                          
            $formObject = array();
            foreach($properties as $key => $value)
            {
                $formObject[$key] = $value;
            }
            $formObject['fieldLabel'] = $column->GetColumnName();
            $formObject['name'] = $column->GetColumnName();
            $formObject['allowBlank'] = $column->IsNullable();
            $itemObject[] = $formObject;
        }
        
        $fieldSet[items] = $itemObject;
        return array($fieldSet);
    }
    
    public static function BuildColumnObject(ColumnSchema $columnSchema)
    {
        $columns = array();
        $columns['text'] = $columnSchema->GetColumnName();
        $columns['dataIndex'] = $columnSchema->GetColumnName();
        $columns['hideable'] = false;
        $columns['sortable'] = false;
        return $columns;
    }
    
    public static function BuildModelObject(ColumnSchema $columnSchema)
    {
        $model = array();
        $model['name'] = $columnSchema->GetColumnName();
        return $model;
    }
    
    private static function GetExtFormFieldPropertiesByType(ColumnSchema $columnSchema)
    {
        $return = array();
        switch ($columnSchema->GetDataType())
        {
            case 'double':
            case 'int':
                if($columnSchema->GetColumnName() == "id")
                {
                    $return['xtype'] = 'hiddenfield';
                }
                else {
                    $return['xtype'] = 'numberfield';
                }
                break;
            case 'tinyint':
                $return['xtype'] = 'checkboxfield';
                break;  
            case 'varchar':
                $return['maxLength'] = $columnSchema->GetMaxLength();
                $return['xtype'] = $return['maxLength'] == 100 ? 'textfield' : 'textareafield';
                break;
            case 'date':
            case 'datetime':
                if($columnSchema->GetColumnName() == "creationdt" || $columnSchema->GetColumnName() == "lastupdated"){
                    $return['xtype'] = 'hiddenfield';
                }
                else {
                    $return['xtype'] = 'datefield';
                }
                break;
        }
        return $return;
    }
}
