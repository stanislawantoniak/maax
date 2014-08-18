<?php

class Zolago_Catalog_Model_Queue_Item_Configurable extends Zolago_Common_Model_Queue_ItemAbstract
{
    public function _construct() {
        $this->_init('zolagocatalog/queue_configurable');
    }

    public function setItemId($itemId) {
        if (!is_array($itemId)
            || !isset($itemId['product_id'])) {
            Mage::throwException(Mage::helper('zolagocatalog')->__('Error: wrong type of queue element'));
        }
        $this->setProductId($itemId['product_id']);
    }
}