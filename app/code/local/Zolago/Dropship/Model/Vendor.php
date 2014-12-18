<?php
class Zolago_Dropship_Model_Vendor extends Unirgy_Dropship_Model_Vendor
{

    const VENDOR_TYPE_BRANDSHOP = 2;
    const VENDOR_TYPE_STANDARD = 1;
	
	/**
	 * Overide fuction to add additional email address bases od vendor operators
	 * @return array
	 */
	public function getNewOrderCcEmails() {
		$po = $this->getData("po");
		/* @var $po Zolago_Po_Model_Po */
		$new = array();
		if($po && $po->getId()){
			foreach($po->getAllowedOperators() as $operator){
				$new[] = $operator->getEmail();
			}
		}
		$old = parent::getNewOrderCcEmails();
		return array_unique(array_merge(is_array($old) ? $old : array(), $new));
	}
	
	/**
	 * @todo add params
	 * @param array $params
	 * @return string
	 */
	public function getVendorUrl($params=array()) {
		return Mage::helper("zolagodropshipmicrosite")->getVendorUrl($this);
	}
	
	/**
	 * @return bool
	 */
	public function isBrandshop() {
		return $this->getVendorType()==Zolago_Dropship_Model_Source::VENDOR_TYPE_BRANDSHOP;
	}

    public function isStandard() {
        return $this->getVendorType()==Zolago_Dropship_Model_Source::VENDOR_TYPE_STANDARD;
    }
	
	/**
	 * Sets root category to registry and then return
     * [DEPRECATED] ! DO NOT USE !
	 */
	public function rootCategory($websiteId = NULL){

		if($category = Mage::registry('vendor_current_category')){
            return $category;
		}
		
		$websiteId		= ($websiteId) ? $websiteId : Mage::app()->getWebsite()->getId();
		$rootCategoryId = Mage::helper('zolagodropshipmicrosite')
				->getVendorRootCategory($this, $websiteId);

        $category = Mage::getModel("catalog/category")->load($rootCategoryId);
		
		if(!$category->getId()){
			$category->load(Mage::app()->getStore()->getRootCategoryId());
		}
		
		Mage::register('vendor_current_category', $category);
		
		return $category;		
	}	
	
	/**
	 * @return array
	 */
	public function getChildVendorIds() {
		if(!$this->hasData('child_vendor_ids')){
			$this->setData('child_vendor_ids', $this->getResource()->getChildVendorIds($this));
		}
		return $this->getData('child_vendor_ids');
	}
	
	/**
	 * @return array
	 */
	public function getAllowedPos() {
		if(!$this->hasData("allowed_pos")){
			$allowedPos = array();
			if($this->getId()){
				$allowedPos = $this->getResource()->getAllowedPos($this);
			}
			$this->setData("allowed_pos", $allowedPos);
		}
		return $this->getData("allowed_pos");
	}
	
	/**
	 * @return Zolago_Rma_Model_Resource_Rma_Reason_Collection
	 */
	public function getRmaReasonVendorCollection(){
    	//$vendor_id = $this->getVendorId();
		$collection = Mage::getResourceModel('zolagorma/rma_reason_vendor_collection');
		/* @var $collection Zolago_Rma_Model_Resource_Rma_Reason_Collection */
		if($this->getId()){
			$collection->addFieldToFilter('vendor_id', $this->getId());
		}else{
			$collection->addFieldToFilter('vendor_id', -1);
		}
		return $collection;
    }

	public function getMaxShippingDays($storeId=null)
    {
        $maxShippingDays = $this->getData('max_shipping_days');
        if (is_null($maxShippingDays) || $maxShippingDays=="" || $maxShippingDays < 0) {
            $maxShippingDays = Mage::getStoreConfig('udropship/vendor/max_shipping_days', $storeId);
        }
        return (int)$maxShippingDays;
    }
	
	public function getMaxShippingTime($storeId=null)
    {
        $maxShippingTime = $this->getData('max_shipping_time');
        if (is_null($maxShippingTime) || $maxShippingTime=="" || $maxShippingTime==0) {
            $maxShippingTime = Mage::getStoreConfig('udropship/vendor/max_shipping_time', $storeId);
        }
        return $maxShippingTime;
    }
	
	protected function _beforeSave() {
		if($this->getData("max_shipping_days")=="" || $this->getData("max_shipping_days") < 0){
			$this->setData("max_shipping_days", null);
		}
		
		if($this->getData("max_shipping_time")=="" || $this->getData("max_shipping_time")==0){
			$this->setData("max_shipping_time", null);
		}
		else{
			if($this->getData('max_shipping_time') && is_array($this->getData('max_shipping_time'))){
				$this->setData('max_shipping_time', implode(',', $this->getData('max_shipping_time')));
			}
		}
		
		return parent::_beforeSave();
	}

	public function getFormatedAddress($type='text')
	{
		switch ($type) {
			case 'text':
				return $this->getStreet(-1)."\n".$this->getCity().', '.$this->getRegionCode().' '.$this->getZip();
		}
		$format = Mage::getSingleton('customer/address_config')->getFormatByCode($type);
		if (!$format) {
			return null;
		}
		$renderer = $format->getRenderer();
		//die(var_dump($renderer));
		if (!$renderer) {
			return null;
		}
		$address = $this->getAddressObj();
		$address->unsVendorAttn();
		$address->unsFirstname();
		$address->unsLastname();
		$address->setCompany($this->getCompanyName());

		return $renderer->render($address);
	}

    public function getVendorLogoUrl() {
        return Mage::getBaseUrl(Mage_core_model_store::URL_TYPE_MEDIA) . $this->getData('logo');
    }

}
