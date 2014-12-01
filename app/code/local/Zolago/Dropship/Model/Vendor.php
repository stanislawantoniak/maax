<?php
class Zolago_Dropship_Model_Vendor extends Unirgy_Dropship_Model_Vendor
{


    protected $_preferences;


    protected function _getPreferences()
    {
        if (is_null($this->_preferences)) {
            $vendorPreferences = Mage::getModel('zolagodropship/preferences');
            $vendorPreferences->load($this->getVendorId(), 'vendor_id');

            $this->_preferences = $vendorPreferences;
        }

        return $this->_preferences;
    }

    public function getData($key = '', $index = null)
    {
        if (in_array($key, array(
                //contact
                'company_name', 'tax_no', 'www',
                'contact_email', 'contact_telephone', 'executive_firstname', 'executive_lastname', 'executive_telephone', 'executive_telephone_mobile',
                //
                'administrator_firstname', 'administrator_lastname', 'administrator_telephone', 'administrator_telephone_mobile',
                //rma
                'rma_email', 'rma_telephone', 'rma_executive_telephone', 'rma_executive_telephone_mobile', 'rma_executive_email'
            )
        )
        ) {

            $preferences = $this->_getPreferences();
	        if(!is_null($preferences)) {
		        $this->addData($preferences->getData());
	        }
        }
        return parent::getData($key, $index);
    }

	/**
	 * Sets root category to registry and then return
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
}
