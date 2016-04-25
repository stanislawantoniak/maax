<?php
/**
  
 */

class ZolagoOs_OmniChannelPayout_PaypalipnController extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {
        Mage::getModel('udpayout/method_paypal')->processIpnPost();
    }
}
