<?php

/*
 * Stores filtering information for SQL where clauses
 * @author jnorcross
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
    
    public static function CreateFilter($column, $value, $operator)
    {
        $filter = new stdClass();
        $filter->property = $column;
        $filter->value = $value;
        $filter->operator = $operator;
        $filter = new Filter($filter);
        return $filter;
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

    /*
     * Determines whether the filter is valid.  A valid filter has to have a column, value and operation.
     * @return true if the vilter is valid
     */
    public function IsValid() {
        $return = false;
        if ($this->column != "" && $this->value != "" && $this->operator != "") {
            $return = true;
        }
        return $return;
    }
    
    /*
     * Builds the query for the filter
     * @return a string containing a filter to be added to a where clause
     */
    public function BuildQuery(){
        if($this->GetOperator() == "in")
        {
            $array = null;
            if(is_array($this->value))
            {
                $array = $this->value;
            }
            else
            {
                $array = explode(",", $this->value);
            }
            $count = count($array);
            $criteria = sprintf("?%s", str_repeat(",?", ($count ? $count-1 : 0)));
            return $this->GetColumn() . " " . $this->GetOperator() . " (" . $criteria . ") ";
        }
        else if($this->GetOperator() == "notnull")
        {
            return $this->GetColumn() . " is not null ";
        }
        else if($this->GetOperator() == "null")
        {
            return $this->GetColumn() . " is null ";
        }
        return $this->GetColumn() . " " . $this->GetOperator() . " (?) ";
    }
    
    /*
     * Sets the parameters for a prepared statement to fill in the column values for this filters where clause.
     * @param parameters - The array of parameters to store the values.
     * @return The list of parameters with the values set.
     */
    public function SetParameters(&$parameters){
        if ($this->GetOperator() == "in") {
            $array = null;
            if (is_array($this->value)) {
                $array = $this->value;
            } else {
                $array = explode(",", $this->value);
            }
            foreach ($array as $value) {
                $parameters[] = $value;
            }
        } 
        else if($this->GetOperator() == "notnull" || $this->GetOperator() == "null"){
            //Do nothing here; there are no parameters for these functions
        }
        else {
            $parameters[] = $this->GetValue();
        }
    }

    /*
     * Transforms an operator into the corresponding SQL operation
     * @param operator - The operator to convert
     * @return The corresponding SQL operation
     */
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
            case "notnull":
            case "null":
                $this->value = "null";
                break;
            default:
                throw new Exception("Invalid operator given: " . $operator);
        }
        return $operator;
    }

}

