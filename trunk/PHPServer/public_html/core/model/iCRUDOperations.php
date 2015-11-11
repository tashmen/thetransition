<?php

/**
 * Interface for CRUD operations
 * @author jnorcross
 */
interface iCRUDOperations {
     /*
     * Creates a new record
     */
    public function create();
    /*
     * Reads the records
     */
    public function read();
    /*
     * Updates an existing record
     */
    public function update();
    /*
     * Deletes an existing record
     */
    public function delete();
}
