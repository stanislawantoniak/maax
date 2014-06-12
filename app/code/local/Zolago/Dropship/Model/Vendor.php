<?php

class Zolago_Dropship_Model_Vendor extends Unirgy_Dropship_Model_Vendor
{
	public function getMaxShippingDays($storeId=null)
    {
        $maxShippingDays = $this->getData('max_shipping_date');
        if (is_null($maxShippingDays) || $maxShippingDays=="" || $maxShippingDays==0) {
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
		if($this->getData("max_shipping_days")=="" || $this->getData("max_shipping_days")==0){
			$this->setData("max_shipping_days", null);
		}
		
		if($this->getData("max_shipping_time")=="" || $this->getData("max_shipping_time")==0){
			$this->setData("max_shipping_time", null);
		}
		return parent::_beforeSave();
	}

}
