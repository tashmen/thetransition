<?php

/*
 * Stores filtering information for SQL where clauses
 */
class Filter implements iFilter {

    private $column;
    private $value;
    private $operator;

    public function __construct($filter) {
        $this->column = $filter->property;
        $this->value = $filter->value;
        $this->operator = $this->ConvertOperator($filter->operator);
    }

    public function GetColumn() {
        return $this->column;
    }

    public function GetValue() {
        return $this->value;
    }

    public function GetOperator() {
        return $this->operator;
    }

    public function IsValid() {
        $return = false;
        if ($this->column != "" && $this->value != "" && $this->operator != "") {
            $return = true;
        }
        return $return;
    }
    
    public function BuildQuery(){
        if($this->GetOperator() == "in")
        {
            $array = explode(",", $this->value);
            $count = count($array);
            $criteria = sprintf("?%s", str_repeat(",?", ($count ? $count-1 : 0)));
            return $this->GetColumn() . " " . $this->GetOperator() . " (" . $criteria . ") ";  
        }
        return $this->GetColumn() . " " . $this->GetOperator() . " (?) ";
    }
    
    public function SetParameters(&$parameters){
        if($this->GetOperator() == "in")
        {
            $array = explode(",", $this->value);
            foreach($array as $value)
            {
                $parameters[] = $value;
            }
            
        }
        else $parameters[] = $this->GetValue();
    }

    private function ConvertOperator($operator) {
        switch ($operator) {
            case "like":
                $this->value = "%" . $this->value . "%";
                break;
            case "eq":
            case "=":
                $operator = "=";
                break;
            case "ne":
            case "!=":
                $operator = "!=";
                break;
            case "gt":
            case ">":
                $operator = ">";
                break;
            case "lt":
            case "<":
                $operator = "<";
                break;
            case "lte":
            case "<=":
                $operator = "<=";
                break;
            case "in":
		
                break;
            default:
                throw new Exception("Invalid operator given: " . $operator);
        }
        return $operator;
    }

}

