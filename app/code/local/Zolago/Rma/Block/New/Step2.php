<?php
class Zolago_Rma_Block_New_Step2 extends  Zolago_Rma_Block_New_Abstract{
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

		// Customer address id from last POST
		if($this->getRma()->getCustomerAddressId()){
			return $this->getRma()->getCustomerAddressId();
		}

		$shippignAddress= $this->
			getRma()->
			getShippingAddress();

		if($shippignAddress && $shippignAddress->getCustomerAddressId()){
			return $shippignAddress->getCustomerAddressId();
		}
		return $this->getDefaultShipping();
	}
	
    /**
     * list of possible pickup data
     * @return array
     */         
     public function getDateList($newZip = '') {
         return Mage::helper('zolagorma')->getDateList($newZip);
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

    public function getCurrentPostcode() {
        return "01-318";
    }
}