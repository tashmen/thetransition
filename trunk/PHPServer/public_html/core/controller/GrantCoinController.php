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
        Bittrex::SetConnection($this->connection);
        Bitstamp::SetConnection($this->connection);
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
            $results[] = $this->OnGetAccount();
        }
        else if($resource == "grantcoin" && $action == "validate"){
            $results[] = $this->OnValidate();
        }
        else if($resource == "grantcoin" && $action == "processtransaction"){
            $results[] = $this->OnProcessTransaction();
        }
        $results = $this->ConvertInputRecordsToOutput($resource, $results);
        return $results;
    }
    
    /*
     * Internal method for processing a transaction that has been confirmed by the user
     */
    private function OnProcessTransaction(){
        $transactionID = RequestData::GetRequestData("confirmationid");
        Security::VerifySecurity($this->connection);
        $grantcoin = new GrantCoin();
        $grantcoinuser = new GrantCoinUser($this->connection, $grantcoin, Security::GetLoggedInUser());
        $grantCoinUserTransaction = new GrantCoinUserTransaction($this->connection, $grantcoinuser, $grantcoin);
        
        $grantCoinUserTransaction->ApplyTransaction($transactionID);
        
        $expiration = null;
        
        try{
            if($grantCoinUserTransaction->GetType() == $grantCoinUserTransaction->types['MEMBERSHIP'])
            {
                $expiration = $grantCoinUserTransaction->GetMembershipExpiration();

                $nb = new NationBuilder();
                $membership = $nb->GetMembership(Security::GetLoggedInUser(), $this->grantCoinMembershipName);
                if($membership == null)
                {
                    $nb->CreateMembership(Security::GetLoggedInUser(), $expiration, $this->grantCoinMembershipName, "Purchased via GrantCoin");
                }
                else{
                    $nb->UpdateMembership(Security::GetLoggedInUser(), $expiration, $this->grantCoinMembershipName, "Purchased via GrantCoin");
                }
            }
        } catch (Exception $ex) {
            //Oh no NB couldn't update the record so now we took their money, but have not provided a membership; for now let's handle these by hand; maybe later we can automate this
            Logger::LogData('GrantCoinTransactionFailure.log', "Transaction id of " . $transactionID . " was processed, but the membership was not applied in Nationbuilder.  The expiration date should be " . $expiration . ". Error was " . $ex);
            throw Exception("An error occurred while updating your membership.  Please contact The Transition team to have your membership reviewed.");
        }
        
        return array(
            successtxt => "Transaction completed successfully."
        );
    }
    
    /*
     * Internal method for validating transactions that the user wants to make
     */
    private function OnValidate(){
        Security::VerifySecurity($this->connection);
        $submitType = RequestData::GetRequestData('submittype');
        $grantcoin = new GrantCoin();
        $grantcoinuser = new GrantCoinUser($this->connection, $grantcoin, Security::GetLoggedInUser());
        $grantCoinUserTransaction = new GrantCoinUserTransaction($this->connection, $grantcoinuser, $grantcoin);
        switch($submitType){
            case $grantCoinUserTransaction->types['DONATE']://Donate
                $donationAmount = RequestData::GetRequestData('donate');
                if ($donationAmount == '' || $donationAmount <= 0) {
                    throw new Exception("Donation must be a positive value.");
                } 
                else if($donationAmount > $grantcoinuser->GetBalanceGRT())
                {
                    throw new Exception("The donation amount of " . $donationAmount . " is more than your current balance of " . $grantcoinuser->GetBalanceGRT() . ".  Please donate an amount that is less than your current balance.");
                }
                else{
                    $transactionID = $grantCoinUserTransaction->CreatePendingTransaction($grantCoinUserTransaction->types['DONATE'], $donationAmount);
                    return array(
                        confirmationtxt => "Do you want to donate " . $donationAmount . " GRT?",
                        confirmationid => $transactionID
                    );
                }
                break;
            case $grantCoinUserTransaction->types['MEMBERSHIP']: //Purchase membership
                $months = RequestData::GetRequestData("months");
                $monthlysub = RequestData::GetRequestData("monthlysub");
                $currentMonthlySub = $grantcoin->ConvertBuyUSDToGRT(Settings::$membershipCostInUSD);
                $currentTotalCost = $currentMonthlySub * $months;
                if($currentMonthlySub . '' != $monthlysub . '')
                {
                    throw new Exception("Current monthly sub of " . $monthlysub . " is no longer valid.  The new rate is " . $currentMonthlySub . ".  This new rate will be valid for about 30 minutes.");
                }
                else if($months <= 0){
                    throw new Exception("Number of months must be greater than 0.");
                }
                else if($currentTotalCost > $grantcoinuser->GetBalanceGRT()){
                    throw new Exception("The total cost of this membership is " . $currentTotalCost . " which is more than your balance of " . $grantcoinuser->GetBalanceGRT());
                }
                else {
                    $nb = new NationBuilder();
                    $membership = $nb->GetMembership(Security::GetLoggedInUser(), $this->grantCoinMembershipName);

                    $expiration = null;
                    if($membership == null)
                    {
                        $expiration = new DateTime();
                    }
                    else{
                        $status = $membership['status'];
                        if ($status == 'expired') {
                            $expiration = new DateTime();
                        } 
                        else {
                            $expiration = explode("T", $membership['expires_on']);
                            $expiration = new DateTime($expiration[0]);
                        }
                    }
                    $expiration->modify("+" . $months . " months");
                    $strExpiration = $expiration->format('Y-m-d');
                    
                    $transactionID = $grantCoinUserTransaction->CreatePendingTransaction($grantCoinUserTransaction->types['MEMBERSHIP'], $currentTotalCost, $strExpiration);
                    return array(
                        confirmationtxt => "Do you want to purchase a membership for " . $currentTotalCost . " GRT?  Your membership would expire on " . $strExpiration . " if this membership is purchased.",
                        confirmationid => $transactionID
                    );
                }
                break;
            case $grantCoinUserTransaction->types['WITHDRAW']://Withdraw
                $withdrawAmount = RequestData::GetRequestData("withdrawamount");
                $address = RequestData::GetRequestData("address");
                
                if($withdrawAmount > $grantcoinuser->GetBalanceGRT())
                {
                    throw new Exception("The donation amount of " . $withdrawAmount . " is more than your current balance of " . $grantcoinuser->GetBalanceGRT() . ".  Please withdraw an amount that is less than your current balance.");
                }
                else{
                    $transactionID = $grantCoinUserTransaction->CreatePendingTransaction($grantCoinUserTransaction->types['WITHDRAW'], $withdrawAmount, null, $address);
                    return array(
                        confirmationtxt => "Do you want to withdraw " . $withdrawAmount . " GRT to the following address: " . $address . "?",
                        confirmationid => $transactionID
                    );
                }
                
                break;
        }
    }
    
    /*
     * Internal method for retrieving a user's GrantCoin account information
     * @return - The account information
     */
    private function OnGetAccount(){
        Security::VerifySecurity($this->connection);
        $nb = new NationBuilder();
        $membership = $nb->GetMembership(Security::GetLoggedInUser(), $this->grantCoinMembershipName);
        Logger::LogError(print_r($membership, true), Logger::debug);

        $status = $membership['status'];
        $expiration = explode("T", $membership['expires_on']);
        $expiration = $expiration[0];

        $grantcoin = new GrantCoin();
        $grantcoinuser = new GrantCoinUser($this->connection, $grantcoin, Security::GetLoggedInUser());
        $account = array(
            address => $grantcoinuser->GetAddress(),
            balancegrt => $grantcoinuser->GetBalanceGRT(),
            balanceusd => $grantcoinuser->GetValueUSD(),
            status => $status,
            expirationdt => $expiration,
            monthlysubgrt => $grantcoin->ConvertBuyUSDToGRT(Settings::$membershipCostInUSD),
            yearlysubgrt => $grantcoin->ConvertBuyUSDToGRT(Settings::$membershipCostInUSD*12)
        );
        return $account;
    }
}
