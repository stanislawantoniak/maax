<?php
class Zolago_Rma_Block_New_Step2 extends  Zolago_Rma_Block_New_Abstract {
    protected $_monthList = array();

    /**
     * @return bool
     */
    public function getHasDefaultPayment() {
        return is_array($this->getQuote()->getCustomer()->getLastUsedPayment());
    }

    /**
     * @return string
     */
    public function getDefaultCountryId() {
        return Mage::app()->getStore()->getConfig("general/country/default");
    }

    /**
     * Get customer collecition
     * @return type
     */
    public function getCustomerAddressesJson() {
        $addresses = array();
        $collection = $this->getCustomer()->getAddressesCollection();
        foreach($collection as $address) {
            /* @var $address Mage_Customer_Model_Address */
            $arr = $address->getData();
            $arr['street'] = $address->getStreet();
            $addresses[] = $arr;
        }
        return $this->asJson($addresses);
    }

    /**
     * @return Mage_Customer_Model_Address | false
     */
    public function getDefaultShipping() {
        return $this->getCustomer()->getDefaultShippingAddress();
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
     * Return customer address
     * @return int | null
     */
    public function getSelectedShipping() {
        if(!$this->hasData("selected_shipping")) {
            // Customer address id from last POST
            $id = null;
            if($this->getRma()->getCustomerAddressId()) {
                $id = $this->getRma()->getCustomerAddressId();
            } else {
                $shippignAddress= $this->
                                  getRma()->
                                  getShippingAddress();

                if($shippignAddress && $shippignAddress->getCustomerAddressId()) {
                    $id = $shippignAddress->getCustomerAddressId();
                }
                elseif($this->getDefaultShipping()) {
                    $id = $this->getDefaultShipping()->getId();
                }
                // No deault address, but som address in addressbok
                if(is_null($id)) {
                    $firstAddress = $this->getCustomer()->
                                    getAddressesCollection()->
                                    getFirstItem();
                    if($firstAddress instanceof Mage_Customer_Model_Address
                            && $firstAddress->getId()) {
                        $id = $firstAddress->getId();
                    }
                }
            }
            $this->setData("selected_shipping", $id);
        }
        return $this->getData("selected_shipping");
    }

    /**
     * @param Mage_Customer_Model_Address | null $newZip
     * @return array
     */
    public function getDateList(Mage_Customer_Model_Address $address = null) {
        if (empty($address)) {
            return array();
        }
        $country = $address->getCountryId();
        $newZip = $newZip->getPostcode();
        // No selected zip / null - return empty array
        if (empty($country) || empty($newZip)) {
            return array();
        }
        return Mage::helper('zolagorma')->
               getDateList($country, $newZip);
    }

    /**
     * is dhl enabled for rma
     * @return bool
     */
    public function isDhlEnabled() {
        $vendor = $this->getParentBlock()->getVendor();
        $helper = Mage::helper('orbashipping/carrier_dhl');
        return $helper->isEnabledForRma($vendor) || $helper->isEnabledForVendor($vendor);
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

    public function showCustomerAccount() {
        return true;
    }

    public function getLegend() {
        return Mage::helper("zolagorma")->__("Report a return or claim");
    }
    public function getAvailableCountryJson() {
        $countries = Mage::helper('zolagocommon')->getAvailableCountry();
        return $this->asJson($countries);
    }
}