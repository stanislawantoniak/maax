<?php

class Zolago_Catalog_Block_Vendor_Price_Stock extends Zolago_Catalog_Block_Vendor_Price_Abstract
{
	/**
	 * @return Zolago_Pos_Model_Resource_Pos_Collection
	 */
	public function getPosCollection() {
		$collection = Mage::getResourceModel('zolagopos/pos_collection');
		/* @var $collection Zolago_Pos_Model_Resource_Pos_Collection */
		$collection->addVendorFilter($this->_getVendor());
		$collection->addActiveFilter();
		return $collection;
	}
	
	/**
	 * @return Mage_CatalogInventory_Model_Stock_Item
	 */
	public function getStockItem() {
			if(!$this->getData("stock_item")){
			$stockItem = Mage::getModel("cataloginventory/stock_item");
			/* @var $stockItem Mage_CatalogInventory_Model_Stock_Item */
			$stockItem->loadByProduct($this->getProduct());
			$this->setData("stock_item", $stockItem);
		}
		return $this->getData("stock_item");
	}
	
	/**
	 * @return array
	 */
	public function getItems() {
		$items = array();
		$data = $this->_getConverterStock();
		
		foreach($this->getPosCollection() as $pos){
			/* @var $pos Zolago_Pos_Model_Pos */
			$item = new Varien_Object;
			$item->setPos($pos);
			$item->setImportedStock($this->getStockItem()->getQty());
			$item->setConverterStock($this->_extractConverterStock($data, $pos));
			
			$finalStock = null;
			if(is_numeric($item->getConverterStock())){
				$finalStock = $item->getConverterStock();
				if(is_numeric($pos->getMinimalStock())){
					$finalStock-=$pos->getMinimalStock();
				}
			}
			$item->setFinalStock(((int)$finalStock>0)? $finalStock:0);
			$items[] = $item;
		}
		return $items;
	}
	
	/**
	 * @param array $data
	 * @param Zolago_Pos_Model_Pos $pos
	 * @return null|nit
	 */
	protected function _extractConverterStock($data, Zolago_Pos_Model_Pos $pos) {
		if(is_array($data)){
			foreach($data as $row){
				if($row['pos']==$pos->getExternalId()){
					return $row['stock'];
				}
			}
		}
		return null;
	}
	
	/**
	 * @return null | int
	 */
	protected function _getConverterStock() {
		$convertert = Mage::getSingleton('zolagoconverter/client');
		/* @var $convertert Zolago_Converter_Model_Client */
		$product = $this->getProduct();
		
		return $convertert->getQtys($this->_getVendor()->getExternalId(), $product->getSkuv());
	}
	
	/**
	 * @param mixed $number
	 * @return strning
	 */
	public function formatNumber($number) {
		return is_null($number) ? $this->__("N/A")  : round($number,2);
	}

}