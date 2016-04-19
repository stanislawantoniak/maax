<?php
/**
  
 */

class ZolagoOs_OmniChannelPo_Model_Mysql4_Po_Comment_Collection extends Mage_Sales_Model_Mysql4_Collection_Abstract
{
    protected $_eventPrefix = 'udpo_po_comment_collection';
    protected $_eventObject = 'po_comment_collection';

    protected function _construct()
    {
        $this->_init('udpo/po_comment');
    }

    public function setPoFilter($poId)
    {
        $this->addFieldToFilter('parent_id', $poId);
        return $this;
    }

    public function setCreatedAtOrder($direction='desc')
    {
        $this->setOrder('created_at', $direction);
        return $this;
    }
}
