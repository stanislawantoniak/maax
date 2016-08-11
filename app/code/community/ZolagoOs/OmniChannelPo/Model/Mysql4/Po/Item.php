<?php
/**
  
 */

class ZolagoOs_OmniChannelPo_Model_Mysql4_Po_Item extends Mage_Sales_Model_Mysql4_Order_Abstract
{
    protected $_eventPrefix = 'udpo_po_item_resource';

    protected function _construct()
    {
        $this->_init('udpo/po_item', 'entity_id');
    }
}
