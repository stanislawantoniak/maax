<?php

class Zolago_Po_Block_Rma extends Mage_Core_Block_Template
{
    protected $_poList;
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('zolagorma/po/list.phtml');
    }
    
    public function setPoList($collection) {
        $this->_poList = $collection;
    }
    public function getPoList() {
        if (empty($this->_poList)) {
            $customerId = Mage::getSingleton('customer/session')->getCustomerId();
            if (!$customerId) {
                return array();
            }
            $collection = Mage::getResourceModel('zolagopo/po_collection');
            $collection->addFieldToFilter('main_table.customer_id',$customerId);
            $collection->addFieldToFilter('udropship_status',Unirgy_DropshipPo_Model_Source::UDPO_STATUS_DELIVERED);
			
			
			
            // remove po without shipment
            $out = array();
            foreach ($collection as $po) {
            	
                if ($po->getLastNotCanceledShipment() && $po->canBeReturned()) {
                    $this->_poList[] = $po;
                }
            }
        }
        return $this->_poList;
        
    }
    public function getItemList($po) {
        if (!$po) {
            return array();
        }
        $items = $po->getItemsCollection();
        $out = Mage::helper('zolagorma')->getItemList($items);
        return $out;
    }
}
