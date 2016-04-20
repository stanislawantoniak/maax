<?php

class Zolago_Modago_Block_Sales_Order_Abstract extends Mage_Core_Block_Template
{

	protected $_cache = array();

	/**
	 * @param Mage_Sales_Model_Order $order
	 * @param bool $canceled canceled PO included
	 * @return bool
	 */
	public function hasPo(Mage_Sales_Model_Order $order,$canceled = false) {
		return $this->getPoCollection($order,$canceled)->getSize()>0;
	}
	
	/**
	 * @param Mage_Sales_Model_Order $order
	 * @param bool $canceled canceled PO included
	 * @return float
	 */
	public function getTotal(Mage_Sales_Model_Order $order,$canceled = false) {
		$price = 0;
		foreach($this->getPoCollection($order,$canceled) as $po){
			/* @var $po Zolago_Po_Model_Po */
			$price += $po->getSubtotalInclTax() + $po->getShippingAmountIncl();
		}
		return $price;
		
	}

	/**
	 * @param Mage_Sales_Model_Order $order
	 * @param bool $canceled canceled PO included
	 * @return array - vendor names sorretd vendro objects
	 */
	public function getVendors(Mage_Sales_Model_Order $order,$canceled = false) {
		$vendors = array();
		foreach($this->getPoCollection($order,$canceled) as $po){
			/* @var $po Zolago_Po_Model_Po */
			if(!isset($vendors[$po->getUdropshipVendor()] )){
				$vendors[$po->getUdropshipVendor()] = Mage::helper("udropship")->getVendor($po->getUdropshipVendor());
			}
		}
		usort($vendors, array($this, "_sortByVendor"));
		return $vendors;
	}
	
	/**
	 * @param Mage_Sales_Model_Order $order
	 * @param int $canceled canceled PO included
	 * @return array
	 */
	public function getSortedPoItemsByOrder(Mage_Sales_Model_Order $order,$canceled = false) {

		$items = array();		
		foreach($this->getPoCollection($order,$canceled) as $po){
			/* @var $po Zolago_Po_Model_Po */	
			foreach($po->getAllItems() as $item){
				if($item->getParentItemId()===null){
					$items[] = array(
						"item"		=> $item,
						"options"   => $this->_getOptionsByPoItem($item),
						"vendor"	=> Mage::helper("udropship")->getVendor($po->getUdropshipVendor())
					);
				}
			}
		}
		
		
		usort($items, array($this, "_sortByVendor"));
		
		return $items;
	}
	
	/**
	 * @todo implement by logic
	 * @param Mage_Sales_Model_Order $order
	 * @return string
	 */
	public function getOrderStatus(Mage_Sales_Model_Order $order) {
		return $order->getStatusLabel();
	}
	
	/**
	 * @param array $options
	 */
	public function extractProductOptions(array $options = array()) {
		if(isset($options['attributes_info'])){
			return $options['attributes_info'];
		}
		return array();
	}
	
	/**
	 * @param ZolagoOs_OmniChannelPo_Model_Po_Item $item
	 */
	protected function _getOptionsByPoItem(ZolagoOs_OmniChannelPo_Model_Po_Item $item) {
		return $item->getOrderItem()->getProductOptions();
	}
	
	/**
	 * @param Mage_Sales_Model_Order $order
	 * @param bool $canceled canceled po included
	 * @return ZolagoOs_OmniChannelPo_Model_Mysql4_Po_Collection
	 */
	public function getPoCollection(Mage_Sales_Model_Order $order,$canceled = false) {
		if(!isset($this->_cache[$order->getId()][$canceled])){
			$collection = Mage::getResourceModel("udpo/po_collection");
			/* @var $collection ZolagoOs_OmniChannelPo_Model_Mysql4_Po_Collection */
			$collection->addFieldToFilter("order_id", $order->getId());
			if (!$canceled) {
    			$collection->addFieldToFilter("udropship_status", 
					array("nin"=>Zolago_Po_Model_Po_Status::STATUS_CANCELED)
	    		);
            }
			$this->_cache[$order->getId()][$canceled] =  $collection;
		}
		return $this->_cache[$order->getId()][$canceled];
	}
	
	/**
	 * @param ZolagoOs_OmniChannel_Model_Vendor | array $vendorA
	 * @param ZolagoOs_OmniChannel_Model_Vendor | array $vendorB
	 * @return int
	 */
	protected function _sortByVendor($a, $b) {
		if($a instanceof ZolagoOs_OmniChannel_Model_Vendor && $b instanceof ZolagoOs_OmniChannel_Model_Vendor){ 
			return strcmp($a->getVendorName(), $b->getVendorName());
		}
		return strcmp($a['vendor']->getVendorName(), $b['vendor']->getVendorName());
	}
	
}
