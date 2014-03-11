<?php

class Zolago_Po_Block_Vendor_Po_Info extends Unirgy_DropshipPo_Block_Vendor_Po_Info
{
    
    public function getCarriers()
    {
        return array_intersect_key(parent::getCarriers(), array_flip($this->getAllowedKeys()));
	}
	
	protected function getAllowedKeys(){
		return Mage::helper('zolagocommon')->getCarriersForVendor();
	}
}
