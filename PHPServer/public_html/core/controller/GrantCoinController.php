<?php

/**
 * Controller to handle interaction with GrantCoin classes
 *
 * @author jnorcross
 */
class GrantCoinController extends TableObjectController {
        
    /*
     * Processing for special actions
     * @param resource - The name of the resource provided
     * @param action - The name of the action to perform
     */
    protected function ProcessSpecialAction($resource, $action)
    {
        $results = null;
        if($resource == "bittrex" && $action == "getbtcgrtmarketsummary")
        {
            $results = array();
            $results[] = Bittrex::GetBTCGRTMarketSummary();
            
        }
        else if($resource == "bitstamp" && $action == "getusdbtcmarketsummary")
        {
            $results = array();
            $results[] = Bitstamp::GetUSDBTCMarketSummary();
        }
        $results = $this->ConvertInputRecordsToOutput($resource, $results);
        return $results;
    }
}
