<?php

class Zolago_Po_Block_Vendor_Po_Info extends Unirgy_DropshipPo_Block_Vendor_Po_Info
{
        
    protected $_allowedKeys;
    //{{{ 
    /**
     * 
     * @param string $key
     * @return 
     */

    //}}}
    protected function _reduceArray($key) {
            $idx = array_search($key,$this->_allowedKeys);
            if ($idx !== false) {
                unset($this->_allowedKeys[$idx]);
            }
        
    }
    public function getCarriers()
    {
        $po = $this->getPo();
        $posId = $po->getDefaultPosId();
        $pos = Mage::getModel('zolagopos/pos')->load($posId);
        $this->_allowedKeys = $this->getAllowedKeys();
        if (!$pos->getUseDhl()) {
            $this->_reduceArray('orbadhl');
        }
        if (!$pos->getUseOrbaups()) {
            $this->_reduceArray('orbaups');
        }
        $out = array_intersect_key(parent::getCarriers(), array_flip($this->_allowedKeys));
        return $out;
    }

    protected function getAllowedKeys() {
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
