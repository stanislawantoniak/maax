<?php
/**
  
 */
 
class ZolagoOs_OmniChannelPayout_Model_Method_Offline implements ZolagoOs_OmniChannelPayout_Model_Method_Interface
{
    protected $_hasExtraInfo=false;
    public function hasExtraInfo($payout)
    {
        return $this->_hasExtraInfo;
    }
    protected $_isOnline=false;
    public function isOnline()
    {
        return $this->_isOnline;
    }
    public function pay($payout)
    {
        if ($payout instanceof ZolagoOs_OmniChannel_Model_Vendor_Statement_Interface) {
            $payout = array($payout);
        }
        foreach ($payout as $pt) {
            $pt->afterPay();
        }
        return true;
    }
}