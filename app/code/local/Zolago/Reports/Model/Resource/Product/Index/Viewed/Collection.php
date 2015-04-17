<?php

class Zolago_Reports_Model_Resource_Product_Index_Viewed_Collection extends Mage_Reports_Model_Resource_Product_Index_Viewed_Collection
{
    /**
     * Retrieve Product Index table name
     *
     * @return string
     */
    protected function _getTableName()
    {
        return $this->getTable('reports/viewed_product_index');
    }

    /**
     * Retrieve Where Condition to Index table
     *
     * @return array
     */
    protected function _getWhereCondition()
    {
        $condition = array();
        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            $condition['customer_id'] = Mage::getSingleton('customer/session')->getCustomerId();
        } elseif ($this->_customerId) {
            $condition['customer_id'] = $this->_customerId;
        } else {
            $condition['sharing_code'] = Mage::helper('zolagowishlist')->getWishlist()->getData('sharing_code');
        }
        return $condition;
    }
}
