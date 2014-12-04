<?php
class Zolago_Modago_Block_Po_Email_Summary extends Mage_Core_Block_Template
{
	/**
	 * @return Zolago_Po_Model_Po | null
	 */
	public function getPo() {
		return Mage::registry("current_po");
	}
	
	/**
	 * @return type
	 */
	public function getItems() {
		if(!$this->hasData("items")){
			$items = array();
			if($this->getPo()){
				// Filter only top visible items
				foreach($this->getPo()->getItemsCollection() as $item){
					/* @var $item Zolago_Po_Model_Po_Item */
					if(!$item->getParentItemId()){
						$items[] = $item;
					}
				}
			}
			$this->setData("items", $items);
		}
		return $this->getData("items");
	}
	
	/**
	 * @param Zolago_Po_Model_Po_Item $item
	 * @return string
	 */
	public function renderItem(Zolago_Po_Model_Po_Item $item) {
		return $this->getLayout()->createBlock($this->_getRenderer($item))->
			setTemplate("zolagopo/email/summary/item/renderer/default.phtml")->
			setItem($item)->
			toHtml();
	}
	
	/**
	 * @return string
	 */
	protected function _getRenderer(Zolago_Po_Model_Po_Item $item) {
		return 'zolagomodago/sales_order_item_renderer_default';
	}
} 