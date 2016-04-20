<?php

class Zolago_Po_Block_Adminhtml_SalesOrderViewTab_Udpos
    extends ZolagoOs_OmniChannelPo_Block_Adminhtml_SalesOrderViewTab_Udpos
{
	
	protected $_joined = false;


	public function getCollection()
    {
		$coll = parent::getCollection();
		if(!$this->_joined){
			$coll->addFieldToSelect("default_pos_name");
			$coll->addFieldToSelect("default_pos_id");
			$this->_joined = true;
		}
		return $coll;
    }
	
    protected function _prepareColumns()
    {
        $this->addColumnAfter('default_pos_name', array(
            'header'    => Mage::helper('zolagopos')->__('POS'),
            'index'     => 'default_pos_name',
            'type'      => 'text',
        ), "increment_id");

        return parent::_prepareColumns();
    }
}
