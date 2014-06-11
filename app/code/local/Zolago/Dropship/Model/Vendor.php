<?php

class Zolago_Dropship_Model_Vendor extends Unirgy_Dropship_Model_Vendor
{
	public function getMaxShippingDate($storeId=null)
    {
        $maxShippingDate = $this->getData('max_shipping_date');
        if (is_null($maxShippingDate) || $maxShippingDate=="" || $maxShippingDate==0) {
            $maxShippingDate = Mage::getStoreConfig('udropship/vendor/max_shipping_date', $storeId);
        }
        return (int)$maxShippingDate;
    }
	
	protected function _beforeSave() {
		if($this->getData("max_shipping_date")=="" || $this->getData("max_shipping_date")==0){
			$this->setData("max_shipping_date", null);
		}
		return parent::_beforeSave();
	}

}
