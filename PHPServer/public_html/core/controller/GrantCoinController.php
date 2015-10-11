<?php

/**
 * Controller to handle interaction with GrantCoin classes
 *
 * @author jnorcross
 */
class GrantCoinController extends TableObjectController {
    private $grantCoinMembershipName = "GrantCoin Contributor";
    /*
     * Processing for special actions
     * @param resource - The name of the resource provided
     * @param action - The name of the action to perform
     */
    protected function ProcessSpecialAction($resource, $action)
    {
        $results = array();
        if($resource == "bittrex" && $action == "getbtcgrtmarketsummary")
        {
            $results[] = Bittrex::GetBTCGRTMarketSummary();
        }
        else if($resource == "bitstamp" && $action == "getusdbtcmarketsummary")
        {
            $results[] = Bitstamp::GetUSDBTCMarketSummary();
        }
        else if($resource == "grantcoinuser" && $action == "getaccount")
        {
            Security::VerifySecurity($this->connection);
            $nb = new NationBuilder();
            $membership = $nb->GetMembership(Security::$userid, $this->grantCoinMembershipName);
            Logger::LogError(print_r($membership, true), Logger::debug);
            
            $status = $membership['status'];
            $expiration = explode("T", $membership['expires_on']);
            $expiration = $expiration[0];
            
            $grantcoin = new GrantCoin();
            $grantcoinuser = new GrantCoinUser($this->connection, $grantcoin, Security::$userid);
            $account = array(
                address => $grantcoinuser->GetAddress(),
                balancegrt => $grantcoinuser->GetBalanceGRT(),
                balanceusd => $grantcoinuser->GetValueUSD(),
                status => $status,
                expirationdt => $expiration,
                monthlysubgrt => $grantcoin->ConvertBuyUSDToGRT(5),
                yearlysubgrt => $grantcoin->ConvertBuyUSDToGRT(5*12)
            );
            $results[] = $account;
        }
        $results = $this->ConvertInputRecordsToOutput($resource, $results);
        return $results;
    }
}
