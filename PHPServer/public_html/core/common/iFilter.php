<?php

/*
 * Interface for Filter operations
 * @author jnorcross
 */
interface iFilter {

    public function GetColumn();

    public function GetValue();

    public function GetOperator();

    /*
     * Determines whether the filter is valid.  A valid filter has to have a column, value and operation.
     * @return true if the vilter is valid
     */
    public function IsValid();
}
