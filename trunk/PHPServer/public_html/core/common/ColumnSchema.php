<?php


/**
 * Description of ColumnSchema
 * Wrapper class for handling schema information from a database
 * @author jnorcross
 */
class ColumnSchema {
    private $columnName;
    private $isNullable;
    private $dataType;
    private $maxLength;
    private $isPrimaryKey;
    private $table;
    
    public function __construct($columnName, $isNullable, $dataType, $maxLength, $isPrimaryKey, $table)
    {
        $this->columnName = $columnName;
        $this->isNullable = $isNullable;
        $this->dataType = $dataType;
        $this->maxLength = $maxLength;
        $this->isPrimaryKey = $isPrimaryKey;
        $this->table = $table;
    }
    
    public function GetColumnName()
    {
        return $this->columnName;
    }
    
    public function IsNullable()
    {
        return $this->isNullable;
    }
    
    public function GetDataType()
    {
        return $this->dataType;
    }
    
    public function GetMaxLength()
    {
        return $this->maxLength;
    }
    
    public function IsPrimaryKey()
    {
        return $this->isPrimaryKey;
    }
}
