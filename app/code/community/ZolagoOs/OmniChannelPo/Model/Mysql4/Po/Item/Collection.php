<?php
/**
  
 */

class ZolagoOs_OmniChannelPo_Model_Mysql4_Po_Item_Collection extends Mage_Sales_Model_Mysql4_Collection_Abstract
{
    protected $_eventPrefix = 'udpo_po_item_collection';
    protected $_eventObject = 'po_item_collection';

    protected function _construct()
    {
        $this->_init('udpo/po_item');
    }

    public function setPoFilter($poId)
    {
        $this->addFieldToFilter('parent_id', $poId);
        return $this;
    }

    /**
     * @param $fieldName
     * @return Varien_Data_Collection_Db
     */
    public function setRowIdFieldName($fieldName)
    {
        return $this->_setIdFieldName($fieldName);
    }
}
