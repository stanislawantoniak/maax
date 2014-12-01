<?php
/**
 * @method Mage_Sales_Model_Resource_Order_Collection getOrders() 
 */
class Zolago_Modago_Block_Sales_Order_History_Text extends Mage_Core_Block_Template
{
    const ORDER_HISTORY_EMPTY_TEXT = 'account-order-history-empty-text';
    protected $_opened_orders;
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('sales/order/history/text.phtml');
    }
    
    //{{{ 
    /**
     * count of opened orders
     * @return int 
     */

    //}}}
    public function getOpenedOrders() {
        if ($this->_opened_orders === null) {
            $openedOrders = Mage::helper('zolagosales')->getOpenedOrders();
            $this->_opened_orders = count($openedOrders);
        }
        return $this->_opened_orders;
    }
    
    //{{{ 
    /**
     * if no opened orders return static text
     * @return string
     */	
    public function getEmptyText() {
        return $this->getLayout()->createBlock('cms/block')->setBlockId(self::ORDER_HISTORY_EMPTY_TEXT)->toHtml();
    }
    //}}}
}
