<?php
/**
 *	Empty history block
 */
class Zolago_Modago_Block_Sales_Order_History_Empty extends Mage_Core_Block_Template
{
    const ORDER_HISTORY_EMPTY = 'account-order-history-empty';

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('sales/order/history/empty.phtml');
    }
    
    //{{{ 
    /**
     * static block
     * @return string
     */	
    public function getBlock() {
        return $this->getLayout()->createBlock('cms/block')->setBlockId(self::ORDER_HISTORY_EMPTY)->toHtml();
    }
    //}}}
}
