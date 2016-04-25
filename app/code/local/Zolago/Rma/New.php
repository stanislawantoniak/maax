<?php
class Zolago_Rma_Block_New extends Mage_Core_Block_Template
{
    protected $_returnRenderer;
	protected $_attributeCache;
	
	/**
	 * @return Zolago_Po_Model_Po
	 */
    public function getPo() {
        return Mage::registry('current_po');
    }
	/**
	 * @return Zolago_Rma_Model_Rma
	 */
	public function getRma() {
		return Mage::registry('current_rma');
	}
	/**
	 * @return ZolagoOs_Rma_Model_Rma_Track
	 */
	public function getTrack() {
		return Mage::registry('current_track');
	}
	
	/**
	 * @todo Impelemnt with logic
	 * @return boolean
	 */
	public function getIsCompleint() {
		return false;
	}
	
	/**
	 * @return Zolago_Dropship_Model_Vendor
	 */
	public function getVendor() {
		return $this->getPo()->getVendor();
	}
	
	/**
	 * @return string
	 */
	public function getVendorName() {
		return $this->getVendor()->getVendorName();
	}
	
	/**
	 * @param int $item
	 * @param int|null $width
	 * @param int|null $height
	 * @return string
	 */
	public function getPoItemThumb($item, $width=60, $height=null) {
		return $this->_getPoItem($item)->getProductThumbHelper()->
			resize($width, $height)->
			keepFrame(false);
	}
	
	/**
	 * @param @param Zolago_Po_Model_Po_Item | int $item $item
	 * @return Zolago_Po_Model_Po_Item
	 */
	protected function _getPoItem($item) {
		if($item instanceof Zolago_Po_Model_Po_Item){
			return $item;
		}
		if(!($item instanceof Zolago_Po_Model_Po_Item)){
			$item = $this->getPo()->getItemById($item);
		}
		if(!($item instanceof Zolago_Po_Model_Po_Item)){
			$item = Mage::getModel("udpo/po_item")->load($item);
		}
		return $item;
	}
	
	/**
	 * @param Zolago_Po_Model_Po_Item | int $item
	 * @return array()
	 */
	public function getConfigurableAttributesByItem($item) {
		
		$item = $this->_getPoItem($item);
		
		if(!$item->getId()){
			return array();
		}
		
		if(!isset($this->_attributeCache[$item->getId()])){
			// No parent or attibutes
			$orderItem = $item->getOrderItem();
			$options = array();
			if($orderItem->getId()){
				$_options = $orderItem->getProductOptions();
				if(isset($_options['attributes_info'])){
					$options = $_options['attributes_info'];
				}
			}
			$this->_attributeCache[$item->getId()] = $options;
		}
		return $this->_attributeCache[$item->getId()];
	}
	
	/**
	 * @return Zolago_Po_Model_Resource_Po_Item_Collection | null
	 */
	public function getItemList() {
		if(!$this->getData("item_list")){
			$po = $this->getPo();
			if (!$po) {
				return null;
			}
			$items = $po->getItemsCollection();
			$out = Mage::helper('zolagorma')->getItemList($items);
			$this->setData("item_list", $out);
		}
        return $this->getData("item_list");
    }
	
	
	public function getHours() {
		$opts = array();
		for($i=6*2;$i<16*2-1;$i++){
			$opts[$i] = sprintf("%02d:%02d", floor($i/2), ($i%2)*15);
		}
		return $opts;
	}
}
