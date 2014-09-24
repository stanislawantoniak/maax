<?php

class Zolago_Modago_Block_Sales_Order_Abstract extends Mage_Core_Block_Template
{

	protected $_cache = array();
	
	/**
	 * @param Mage_Sales_Model_Order $order
	 * @return array - vendor names sorretd vendro objects
	 */
	public function getVendors(Mage_Sales_Model_Order $order) {
		$vendors = array();
		foreach($this->getPoCollection($order) as $po){
			/* @var $po Zolago_Po_Model_Po */
			$vendors[] = Mage::helper("udropship")->getVendor($po->getUdropshipVendor());
		}
		usort($vendors, array($this, "_sortByVendor"));
		return $vendors;
	}
	
	/**
	 * @param Mage_Sales_Model_Order $order
	 * @return array
	 */
	public function getSortedPoItemsByOrder(Mage_Sales_Model_Order $order) {
		$vendors = $this->getVendors($order);
		$items = array();
		
		foreach($this->getPoCollection($order) as $po){
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
	 * @param Unirgy_DropshipPo_Model_Po_Item $item
	 */
	protected function _getOptionsByPoItem(Unirgy_DropshipPo_Model_Po_Item $item) {
		return $item->getOrderItem()->getProductOptions();
	}
	
	/**
	 * @param Mage_Sales_Model_Order $order
	 * @return Unirgy_DropshipPo_Model_Mysql4_Po_Collection
	 */
	public function getPoCollection(Mage_Sales_Model_Order $order) {
		if(!isset($this->_cache[$order->getId()])){
			$collection = Mage::getResourceModel("udpo/po_collection");
			/* @var $collection Unirgy_DropshipPo_Model_Mysql4_Po_Collection */
			$collection->addFieldToFilter("order_id", $order->getId());
			$collection->addFieldToFilter("udropship_status", 
					array("nin"=>Zolago_Po_Model_Po_Status::STATUS_CANCELED)
			);
			$this->_cache[$order->getId()] =  $collection;
		}
		return $this->_cache[$order->getId()];
	}
	
	/**
	 * @param Unirgy_Dropship_Model_Vendor | array $vendorA
	 * @param Unirgy_Dropship_Model_Vendor | array $vendorB
	 * @return int
	 */
	protected function _sortByVendor($a, $b) {
		if($a instanceof Unirgy_Dropship_Model_Vendor && $b instanceof Unirgy_Dropship_Model_Vendor){ 
			return strcmp($a->getVendorName(), $b->getVendorName());
		}
		return strcmp($a['vendor']->getVendorName(), $b['vendor']->getVendorName());
	}
	
}
