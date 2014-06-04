<?php

class Zolago_Po_Block_List extends Mage_Core_Block_Template
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
            $this->_poList = $collection;
        }
        return $this->_poList;
        
    }
}
