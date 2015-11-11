<?php
/*
 * Interface for a table request
 * @author jnorcross
 */
interface iTableRequest {

    public function GetData();

    public function GetFilters();

    public function GetStart();

    public function GetLimit();

    public function GetSortColumn();

    public function GetSortDirection();
    
    public function HasPaging();
}

?>

