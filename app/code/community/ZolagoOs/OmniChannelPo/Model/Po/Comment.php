<?php
/**
  
 */

class ZolagoOs_OmniChannelPo_Model_Po_Comment extends Mage_Sales_Model_Abstract
{
    protected $_eventPrefix = 'udpo_po_comment';
    protected $_eventObject = 'po_comment';
    
    protected $_po;

    protected function _construct()
    {
        $this->_init('udpo/po_comment');
    }

    public function setPo(ZolagoOs_OmniChannelPo_Model_Po $po)
    {
        $this->_po = $po;
        return $this;
    }

    public function getPo()
    {
        return $this->_po;
    }

    public function getStore()
    {
        if ($this->getPo()) {
            return $this->getPo()->getStore();
        }
        return Mage::app()->getStore();
    }

    protected function _beforeSave()
    {
        parent::_beforeSave();

        if (!$this->getParentId() && $this->getPo()) {
            $this->setParentId($this->getPo()->getId());
        }

        return $this;
    }
}
