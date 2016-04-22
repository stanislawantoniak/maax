<?php

class Zolago_Po_Block_Vendor_Po_Info extends ZolagoOs_OmniChannelPo_Block_Vendor_Po_Info
{
        
    public function getCarriers()
    {
        $po = $this->getPo();
        $posId = $po->getDefaultPosId();
        $vendor = $po->getVendor();
        $pos = Mage::getModel('zolagopos/pos')->load($posId);
        $myCarriers = array_merge(
            array_flip(Mage::helper('zolagodropship')->getAllowedCarriersForPos($pos)),
            array_flip(Mage::helper('zolagodropship')->getAllowedCarriersForVendor($vendor))
        );
        $out = array_intersect_key(
            parent::getCarriers(), 
            $myCarriers
        );
        return $out;
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
