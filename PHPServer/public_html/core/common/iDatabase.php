<?php


interface iDatabase {
    public function lastInsertId();
    public function execute($statement, $parameters = null, $fetchData = true);
    public function rowCount($statement, $parameters = null);
    public function GetColumnSchema($table);
}
