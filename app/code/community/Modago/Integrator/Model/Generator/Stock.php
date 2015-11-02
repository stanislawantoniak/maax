<?php
/**
 * generating stock file
 */
class Modago_Integrator_Model_Generator_Stock
    extends Modago_Integrator_Model_Generator {
    
    protected $_getList;
	protected $_header;
	protected $_footer;

    /**
     * file path
     *
     * @return string
     */
     protected function _getPath() {
	     return Mage::getBaseDir('var'). DS . parent::DIRECTORY . DS . $this->getExternalId()."_STOCKS_".Mage::getModel('core/date')->date('Y-m-d_H:i:s').".xml";
     }

	public function _getHeader() {
		if(!$this->_header) {
			$this->_header = "<mall><merchant>".$this->getExternalId()."</merchant><stocksPerPOS><pos id=\"MAGAZYN\">";
		}
		return $this->_header;
	}

	public function _getFooter() {
		if(!$this->_footer) {
			$this->_footer = "</pos></stocksPerPOS></mall>";
		}
		return $this->_footer;
	}
     
    /**
     * prepare content
     *
     * @return array
     */
     protected function _prepareList() {
	     if($this->_getList) {
		     return false;
	     }
	     /** @var Mage_Core_Model_Resource $resource */
	     $resource = Mage::getSingleton('core/resource');
	     $productTable = $resource->getTableName('catalog_product_entity');
	     $stockTable = $resource->getTableName('cataloginventory_stock_item');

	     $readConnection = $resource->getConnection('core_read');
	     $query =
		     "SELECT $productTable.sku AS sku, $stockTable.qty AS qty
			  FROM $productTable, $stockTable WHERE $stockTable.product_id = $productTable.entity_id;";

	     $this->_getList = true;
	     return $readConnection->fetchAll($query);
     }
     
    /**
     *	prepare xml block 
     * @var array $item
     * @return string
     */
     protected function _prepareXmlBlock($item) {
	     $qty = intval($item['qty']);
         return "<product><sku>{$item['sku']}</sku><stock>$qty</stock></product>";
     }
}
