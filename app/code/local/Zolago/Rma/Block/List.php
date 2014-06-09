<?php

class Zolago_Rma_Block_List extends Mage_Core_Block_Template
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('zolagorma/list.phtml');
    }

    public function getRmaList() {
        $customerId = Mage::getSingleton('customer/session')->getCustomerId();
        if (!$customerId) {
            return array();
        }

        $collection = Mage::getModel('zolagorma/rma')->getCollection();
        $collection->addFieldToFilter('customer_id',$customerId);        
        return $collection; 
    }
}
