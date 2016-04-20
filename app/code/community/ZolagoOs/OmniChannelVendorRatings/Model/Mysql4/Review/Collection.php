<?php

class ZolagoOs_OmniChannelVendorRatings_Model_Mysql4_Review_Collection extends Mage_Review_Model_Mysql4_Review_Collection
{
    protected function _beforeLoad()
    {
        return $this;
    }
    protected function _afterLoad()
    {
        parent::_afterLoad();
        if ($this->getFlag('AddRateVotes')) {
            $this->addRateVotes();
        }
        if ($this->getFlag('AddAddressData')) {
            $this->addAddressData();
        }
        return $this;
    }
    public function addAddressData()
    {
        $sIds = array();
        foreach ($this->getItems() as $item) {
            $sId = $item->getData('rel_entity_pk_value');
            $sIds[] = $sId;
            $rIdsBySid[$sId] = $item->getId();
        }
        $rHlp = Mage::getResourceSingleton('udropship/helper');
        $saIdsData = $rHlp->loadDbColumns(Mage::getModel('sales/order_shipment'), $sIds, array('shipping_address_id'));
        foreach ($saIdsData as $sId=>$saIdData) {
            $saId = $saIdData['shipping_address_id'];
            $saIds[$sId] = $saId;
        }
        $addrCol = Mage::getModel('sales/order_address')->getCollection();
        $addrCol->addFieldToFilter('entity_id', array('in'=>$saIds));
        foreach ($rIdsBySid as $sId=>$rId) {
            $saId = @$saIds[$sId];
            if ($saId && ($sa = $addrCol->getItemById($saId)) && ($r = $this->getItemById($rId))) {
                $r->setShippingAddress($sa);
            }
        }
    }
}