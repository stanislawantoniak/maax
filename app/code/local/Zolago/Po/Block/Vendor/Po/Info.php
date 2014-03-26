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
	
    public function getPo()
    {
        if (!$this->hasData('po')) {
            $id = (int)$this->getRequest()->getParam('id');
            $po = Mage::getModel('udpo/po')->load($id);
            $this->setData('po', $po);
            Mage::helper('udropship')->assignVendorSkus($po);
            Mage::helper('udropship/item')->hideVendorIdOption($po);
            if ($this->isShowTotals()) {
                Mage::helper('udropship/item')->initPoTotals($po, true);
            }
        }
        return $this->getData('po');
    }	
}
