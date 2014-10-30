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