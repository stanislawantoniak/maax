<?php
class Zolago_Dropship_Block_Vendor_Shipment_Info extends Unirgy_Dropship_Block_Vendor_Shipment_Info
{
	public function getCarriers(){
        return array_intersect_key(parent::getCarriers(), array_flip($this->getAllowedKeys()));
	}
	
	protected function getAllowedKeys(){
		return Mage::helper('zolagocommon')->getCarriersForVendor();
	}

}
