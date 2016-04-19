<?php
/**
  
 */

require_once "app/code/community/ZolagoOs/OmniChannel/controllers/VendorController.php";

class ZolagoOs_OmniChannelTierCommission_VendorController extends ZolagoOs_OmniChannel_VendorController
{
    public function ratesAction()
    {
        $this->_renderPage(null, 'tiercom_rates');
    }
    public function ratesPostAction()
    {
        $session = Mage::getSingleton('udropship/session');
        $hlp = Mage::helper('udropship');
        $r = $this->getRequest();
        if ($r->isPost()) {
            $p = $r->getPost();
            try {
                $v = $session->getVendor();
                $v->setTiercomRates($p['tiercom_rates']);
                $v->save();
#echo "<pre>"; print_r($v->debug()); exit;
                $session->addSuccess('Rates has been saved');
            } catch (Exception $e) {
                $session->addError($e->getMessage());
            }
        }
        $this->_redirect('udtiercom/vendor/rates');
    }
}