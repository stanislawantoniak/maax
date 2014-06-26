<?php
/**
 * Class Zolago_Catalog_Model_Queue_Item_Pricetype
 *
 * @category    Zolago
 * @package     Zolago_Catalog
 *
 */
class Zolago_Catalog_Model_Queue_Item_Pricetype extends Zolago_Common_Model_Queue_ItemAbstract
{
    public function _construct() {
        $this->_init('zolagocatalog/queue_pricetype');
    }

    public function setItemId($itemId) {
        if (!is_array($itemId)
            || !isset($itemId['product_id'])) {
            Mage::throwException(Mage::helper('zolagocatalog')->__('Error: wrong type of queue element'));
        }
        $this->setProductId($itemId['product_id']);
    }
}