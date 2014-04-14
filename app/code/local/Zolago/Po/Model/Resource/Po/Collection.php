<?php
class Zolago_Po_Model_Resource_Po_Collection 
	extends Unirgy_DropshipPo_Model_Mysql4_Po_Collection
{
    public function addOrderData() {
		return $this->_joinOrderTable();
	}
	
	protected function _joinOrderTable()
    {
        if (!$this->_orderJoined) {
            $this->getSelect()->join(
                array('order_table'=>$this->getTable('sales/order')),
                'order_table.entity_id=main_table.order_id',
                array('base_currency_code')
            );
            $this->_orderJoined = true;
        }
        return $this;
    }
}
