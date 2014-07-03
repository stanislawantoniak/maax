<?php

class Zolago_Po_Block_Vendor_Po_Info extends Unirgy_DropshipPo_Block_Vendor_Po_Info
{
    
    public function getCarriers()
    {
        return array_intersect_key(parent::getCarriers(), array_flip($this->getAllowedKeys()));
	}
	
	protected function getAllowedKeys(){
		return Mage::helper('zolagodropship')->getAllowedCarriers();
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
	
    public function getRemainingShippingAmount()
    {
		$hlp = Mage::helper('udropship');
		$_shipmentStatuses = Mage::getSingleton('udropship/source')->setPath('shipment_statuses')->toOptionHash();
        $sa = 0;
        $po = $this->getPo();
        foreach ($po->getShipmentsCollection() as $_s) {
			$status = $hlp->__(isset($_shipmentStatuses[$_s->getUdropshipStatus()]) ? $_shipmentStatuses[$_s->getUdropshipStatus()] : 'Unknown');
			if (!in_array($status, array($hlp->__('Canceled')))) {
				$sa += $_s->getBaseShippingAmount();
			}
        }
		
		return max(0,$po->getBaseShippingAmount() + $po->getBaseShippingTax() -$sa);
    }	
}
