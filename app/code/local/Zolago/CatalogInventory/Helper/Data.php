<?php
/**
 * Created by PhpStorm.
 * User: victory
 * Date: 6/18/14
 * Time: 1:11 PM
 */ 
class Zolago_CatalogInventory_Helper_Data extends Mage_Core_Helper_Abstract {
	
	const FLAG_IN_STOCK			= 2;
	const FLAG_LAST_IN_STOCK	= 1;
	const FLAG_OUT_OF_STOCK		= 0;
	const FLAG_NO_STOCK_INFO	=-1;
	
	const MAX_CART_LIST_QTY		= 10;

	/**
	 * @param Mage_Sales_Model_Quote_Item $item
	 * @return boolean
	 */
	public function getCanBuyQuoteItem(Mage_Sales_Model_Quote_Item $item) {
		if($item->getMessages()){
			foreach($item->getMessages() as $message){
				if($message['type']=="error"){
					return false;
				}
			}
		}
		return true;
	}
	
	/**
	 * @param Mage_Sales_Model_Quote_Item $item
	 */
	public function getMaxSaleQty(Mage_Sales_Model_Quote_Item $item) {
		$stockModel = Mage::getModel("cataloginventory/stock_item");
		/* @var $stockModel Mage_CatalogInventory_Model_Stock_Item */
		$product = $item->getProduct();
		
		if($product && $product->getId()){
			if($product->getTypeId()==Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE){
				foreach($item->getChildren() as $childItem){
					$product = $childItem->getProduct();
					break;
				}
			}
		}
		$stockModel->loadByProduct($product);


		if(!!$stockModel->getBackorders()){
			return (int)$stockModel->getMaxSaleQty();
		}
		
		$minimalStock = min(
			$stockModel->getMaxSaleQty(), 
			max($stockModel->getQty(),0)
		);
		
		return max(0, min($minimalStock-$stockModel->getMinQty(), self::MAX_CART_LIST_QTY));
	}
	
	/**
	 * @param Mage_Sales_Model_Quote_Item $item
	 * @return array
	 */
	public function getCartQtyList(Mage_Sales_Model_Quote_Item $item) {
		$maxQty = $this->getMaxSaleQty($item);
		$out = array();
		for($i=1;$i<$maxQty+1;$i++){
			$out[$i] = $i;
		}
		return $out;
	}
	
	/**
	 * @param Mage_Sales_Model_Quote_Item $item
	 * @return int
	 */
	public function getQuoteItemAvailableFlag(Mage_Sales_Model_Quote_Item $item) {
		return  $this->_getQuoteItemAvailableFlag($item);
	}
	
	/**
	 * @param Mage_Sales_Model_Quote_Item $item
	 * @return int
	 */
	protected function _getQuoteItemAvailableFlag(Mage_Sales_Model_Quote_Item $item) {
		// Product didnt exists
		if(!$item->getProduct() && !$item->getProduct()->getId()){
			return self::FLAG_NO_STOCK_INFO;
		}
		
		$productsCheck = array();
		
		$itemThershold = (int)$item->getStore()->getConfig("cataloginventory/options/stock_threshold_qty");
		
		if($item->getChildren() /* && $item->getProduct()->getTypeId()==Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE*/){
			foreach($item->getChildren() as $childItem){
				$productsCheck[] = $childItem->getProduct();
			}
		}else{
			$productsCheck[] = $item->getProduct();
		}
		$stockModel = Mage::getModel("cataloginventory/stock_item");
		/* @var $stockModel Mage_CatalogInventory_Model_Stock_Item */
		if($productsCheck){
			foreach($productsCheck as $product){
				/* @var $product Mage_Catalog_Model_Product */
				$stockModel->loadByProduct($product);
				$flags = array();
				if($stockModel && $stockModel->getId()){
					if($stockModel->getIsInStock () && $stockModel->getQty()>$itemThershold){
						$flag = self::FLAG_IN_STOCK;
					}elseif($stockModel->getIsInStock ()){
						$flag = self::FLAG_LAST_IN_STOCK;
					}else{
						$flag = self::FLAG_OUT_OF_STOCK;
					}
					$flags[] = $flag;
				}else{
					return self::FLAG_NO_STOCK_INFO;
				}
				return min($flags);// return minimal-meain flag
			}
		}
		// If no info
		return self::FLAG_NO_STOCK_INFO;
	}
	
	/**
	 * @param Mage_Sales_Model_Quote_Item $item
	 * @return string
	 */
	public function getQuoteItemAvailableText(Mage_Sales_Model_Quote_Item $item) {
		switch ($this->getQuoteItemAvailableFlag($item)) {
			case self::FLAG_IN_STOCK:
				return Mage::helper('zolagomodago')->__("In stock");
			break;
			case self::FLAG_LAST_IN_STOCK:
				return Mage::helper('zolagomodago')->__("Last in stock");
			break;
			case self::FLAG_OUT_OF_STOCK:
				return Mage::helper('zolagomodago')->__("Out of stock");
			break;
		}
		return Mage::helper('zolagomodago')->__("No stock info");
	}
}