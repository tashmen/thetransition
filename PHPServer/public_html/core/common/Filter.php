<?php

/*
 * Stores filtering information for SQL where clauses
 */
class Filter implements iFilter {

    private $column;
    private $value;
    private $operator;

    public function __construct(array $filter) {
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

    private function ConvertOperator($operator) {
        switch ($operator) {
            case "like":
                $this->value = "%" . $this->value . "%";
                break;
            case "eq":
                $operator = "=";
                break;
            case "ne":
                $operator = "!=";
                break;
            case "gt":
                $operator = ">";
                break;
            case "lt":
                $operator = "<";
                break;
            default:
                throw new Exception("Invalid operator given: " . $operator);
        }
        return $operator;
    }

}

