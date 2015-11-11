<?php
/*
 * Interface for all views
 * 
 * @author jnorcross
 */
interface iView{
    /*
     * Render data for the view using the supplied array of data.
     * 
     * Data should be echo'ed out to the browser
     */
    public function render(array $data);
}
