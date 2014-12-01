<?php
class Zolago_Rma_Block_Courier extends Zolago_Rma_Block_New_Step2
{
    protected $_monthList = array();

    public function getPo()
    {
        $poId = $this->getRma()->getUdpoId();
        return Mage::getModel('zolagopo/po')->load($poId);
    }

    /**
     * @return Mage_Customer_Model_Customer
     */
    public function getCustomer() {
        return Mage::getSingleton('customer/session')->getCustomer();
    }

    /**
     * is dhl enabled for rma
     * @return bool
     */
    public function isDhlEnabled() {
        $vendor = $this->getPo()->getVendor();
        $helper = Mage::helper('orbashipping/carrier_dhl');
        return $helper->isEnabledForRma($vendor) || $helper->isEnabledForVendor($vendor);
    }

    /**
     * @return string
     */
    public function getFormKey() {
        return Mage::getSingleton('core/session')->getFormKey();
    }

    /**
     * @return bool
     */
    public function showCustomerAccount(){
        return false;
    }

    public function getLegend(){
        return Mage::helper("zolagorma")->__("Book a courier");
    }

}
