<?php
class Zolago_Rma_Block_Courier extends Zolago_Rma_Block_Abstract
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
     * @param Mage_Customer_Model_Address | string | null $newZip
     * @return array
     */
    public function getDateList($newZip = '') {
        if($newZip instanceof Mage_Customer_Model_Address){
            $newZip = $newZip->getPostcode();
        }
        // No selected zip / null - return empty array
        if(empty($newZip)){
            return array();
        }
        return Mage::helper('zolagorma')->
            getDateList($this->getRequest()->getParam('po_id'), $newZip);
    }
    /**
     * @return Mage_Customer_Model_Address | null
     */
    public function getSelectedShippingAddress() {
        return $this->getCustomer()->getAddressItemById(
            $this->getSelectedShipping()
        );
    }
    /**
     * Get customer collecition
     * @return type
     */
    public function getCustomerAddressesJson() {
        $addresses = array();
        $collection = $this->getCustomer()->getAddressesCollection();
        foreach($collection as $address){
            /* @var $address Mage_Customer_Model_Address */
            $arr = $address->getData();
            $arr['street'] = $address->getStreet();
            $addresses[] = $arr;
        }
        return $this->asJson($addresses);
    }

    /**
     * @return int | null
     */
    public function getDefaultShipping() {
        return $this->getCustomer()->getDefaultShipping();
    }

    /**
     * Return customer address
     * @return int | null
     */
    public function getSelectedShipping() {
        if(!$this->hasData("selected_shipping")){
            // Customer address id from last POST
            if($this->getRma()->getCustomerAddressId()){
                $id = $this->getRma()->getCustomerAddressId();
            }else{
                $shippignAddress= $this->
                    getRma()->
                    getShippingAddress();

                if($shippignAddress && $shippignAddress->getCustomerAddressId()){
                    $id = $shippignAddress->getCustomerAddressId();
                }else{
                    $id = $this->getDefaultShipping();
                }
            }
            $this->setData("selected_shipping", $id);
        }
        return $this->getData("selected_shipping");
    }

    /**
     * formatted date (using locale)
     * @param int timestamp
     * @return string
     */
    public function getFormattedDate($timestamp) {
        $list = $this->_getMonthList();
        $date = explode('-',date('j-n-Y',$timestamp));
        $pattern = sprintf('%s %s %s',$date[0],$list[$date[1]],$date[2]);
        return $pattern;
    }
    /**
     * month list in proper language
     * @return array
     */
    protected function _getMonthList() {
        if (!$this->_monthList) {
            $locale = Mage::app()->getLocale();
            $list = $locale->getLocale()->getTranslationList('months',$locale->getLocale());
            $this->_monthList = $list['format']['wide'];
        }
        return $this->_monthList;
    }
    /**
     * @return string
     */
    public function getFormKey() {
        return Mage::getSingleton('core/session')->getFormKey();
    }
    /**
     * @return string
     */
    public function getDefaultCountryId() {
        return Mage::app()->getStore()->getConfig("general/country/default");
    }
    /**
     * @param mixed $data
     * @return string
     */
    public function asJson($data) {
        return Mage::helper('core')->jsonEncode($data);
    }
}
