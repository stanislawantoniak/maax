<?php
/**
 * @method Mage_Sales_Model_Resource_Order_Collection getOrders() 
 */
class Zolago_Modago_Block_Sales_Order_View extends Mage_Sales_Block_Order_View {
    
    protected $_collection;
    protected $_block;
    
	/**
	 * @return float
	 */
	public function getGrandTotal() {
		if(!$this->getHasAnyPo()){
			return $this->getOrder()->getGrandTotal();
		}
		$total = 0;
		foreach ($this->getItems() as $_item){
			$total += /*$_item->getData('shipping_amount_incl') +*/ $_item->getData('grand_total_incl_tax'); 
		}
		return $total;
	}
	

    //{{{ 
    /**
     * @param ZolagoOs_OmniChannelPo_Model_SalesOrder
     * @return 
     */
    public function getPoListByOrder($order) {
        if (!$this->_collection) {
            $collection = Mage::getResourceModel('udpo/po_collection');
            $collection->setOrderFilter($order);
            $this->_collection = $collection;
        }
        return $this->_collection;
    }
    //}}}	

    //{{{ 
    /**
     * po items
     * @return 
     */
    public function getItems() {
        $order = $this->getOrder();
        $collection = $this->getPoListByOrder($order);
        return $collection;
    }
    //}}}

    //{{{ 
    /**
     * formatted payment method
     * @return 
     */
    public function getPaymentHtml() {
         return  $this->escapeHtml(
             $this->getOrder()
                  ->getPayment()
                  ->getMethodInstance()
                  ->getTitle()
            );   
    }
    //}}}    
    //{{{ 
    /**
     * create block for rendering
     * @param 
     * @return 
     */
    protected function _getItemBlock() {
        if (!$this->_block) {
            $block = $this->getLayout()->createBlock('zolagomodago/sales_order_item');
            $block->addItemRender('default','zolagomodago/sales_order_item_renderer_default','sales/order/item/renderer/default.phtml');
            $this->_block = $block;
        }
        return $this->_block;
    }
    //}}}
    //{{{ 
    /**
     * render po item
     * @param Zolago_Po_Model_Po_Item $item
     * @return 
     */
    public function getItemHtml(Zolago_Po_Model_Po_Item $item) {
        $block = $this->_getItemBlock();
        return $block->getItemHtml($item);
    }
    //}}}    
    
    //{{{ 
    /**
     * prepare shipping info block
     * @param Zolago_Po_Model_Po $item
     * @return 
     */
    public function getShipmentBlock($item) {
        $block = $this->getLayout()
                    ->createBlock('zolagomodago/sales_order_shipment');
        return $block->setItem($item)->toHtml();
    }
    //}}}
    //{{{ 
    /**
     * prepare billing info block
     * @param Zolago_Po_Model_Po $item
     * @return 
     */
    public function getInfoBlock($item) {
        return $this->getLayout()
                    ->createBlock('zolagomodago/sales_order_info')
                    ->setItem($item)
                    ->toHtml();
    }
    //}}}
}
