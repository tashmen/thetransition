<?php

/*
 * Stores all of the request information needed for TableObjects
 * Limit is defaulted to 25 if it doesn't exist
 * @author jnorcross
 */
class TableRequest implements iTableRequest {

    private $data;
    private $filters;
    private $start;
    private $limit;
    private $sortColumn;
    private $sortDirection;
    private $DEFAULT_LIMIT = 25;

    public function __construct($initFromRequest = true) {
        if($initFromRequest){
            $this->data = RequestData::GetRequestData('data', RequestData::$filterJson);

            $filters = RequestData::GetRequestData('filter', RequestData::$filterJson);
            $this->filters = array();
            if(is_array($filters))
            {
                foreach ($filters as $filter) {
                    $this->filters[] = new Filter($filter);
                }
            }

            $start = RequestData::GetRequestData('start');
            $limit = RequestData::GetRequestData('limit');
            if ($limit != "" && $start != "") {
                $start = intval($start);
                $limit = intval($limit);
                if ($start < 0) {
                    $start = 0;
                }
                if ($limit <= 0) {
                    $limit = $this->DEFAULT_LIMIT;
                }
            }
            $this->limit = $limit;
            $this->start = $start;

            $sort = RequestData::GetRequestData('sort', RequestData::$filterJson);
            $this->SetSort($sort);
        }
        else{
            $this->filters = array();
            $this->limit = '';
            $this->start = '';
            $this->sortColumn = '';
            $this->sortDirection = 'ASC';
        }
    }
    
    public function SetData($data){
        $this->data = $data;
    }

    public function GetData() {
        return $this->data;
    }
    
    /*
     * Sets an array of filters on the request
     */
    public function SetFilters(array $filters){
        $this->filters = $filters;
    }

    public function GetFilters() {
        return $this->filters;
    }

    public function GetStart() {
        return $this->start;
    }

    public function GetLimit() {
        return $this->limit;
    }

    public function GetSortColumn() {
        return $this->sortColumn;
    }

    public function GetSortDirection() {
        return $this->sortDirection;
    }
    
    public function HasPaging(){
        $return = false;
        if($this->limit !== '' && $this->start !== ''){
            $return = true;
        }
        return $return;
    }

    private function SetSort($sort) {
        $this->sortColumn = $sort[0]->property;
        $this->SetSortDirection($sort[0]->direction);
    }
    
    public function SetSortColumn($column)
    {
        $this->sortColumn = $column;
    }

    public function SetSortDirection($direction)
    {
        $orderDirection = $direction;
        if ($orderDirection == "DESC") {
            $orderDirection = "DESC";
        } else {
            $orderDirection = "ASC";
        }
        $this->sortDirection = $orderDirection;
    }
}


