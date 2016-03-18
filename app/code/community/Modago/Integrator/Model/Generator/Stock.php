<?php
/**
 * generating stock file
 */
class Modago_Integrator_Model_Generator_Stock
    extends Modago_Integrator_Model_Generator {

    protected $_getList;

    protected function _construct() {
        $this->setFileNamePrefix('stocks');
    }

    public function getHeader() {
        return "<mall><version>".$this->getHelper()->getModuleVersion().
               "</version><merchant>".$this->getExternalId()."</merchant><stocksPerPOS><pos id=\"MAGAZYN\">";
    }

    public function getFooter() {
        return "</pos></stocksPerPOS></mall>";
    }

    public function getStockManage() {
        return $this->getHelper()->getStockManage();
    }
    /**
     * prepare content
     *
     * @return array
     */
    public function prepareList() {
        if($this->_getList) {
            return false;
        }
        /** @var Mage_Core_Model_Resource $resource */
        $resource = Mage::getSingleton('core/resource');
        $productTable = $resource->getTableName('catalog_product_entity');
        $stockTable = $resource->getTableName('cataloginventory_stock_item');
        $eavAttributeTable = $resource->getTableName('eav_attribute');
        $catalogProductEntityIntTable = $resource->getTableName('catalog_product_entity_int');
        $defaultStockManage = $this->getStockManage();

        $readConnection = $resource->getConnection('core_read');
        $query =
            "SELECT DISTINCT $productTable.sku AS sku, $stockTable.qty AS qty ".
            " FROM $productTable INNER JOIN $stockTable ON $stockTable.product_id = $productTable.entity_id ".
            " INNER JOIN $catalogProductEntityIntTable ON $catalogProductEntityIntTable.entity_id = $productTable.entity_id ".
            " INNER JOIN $eavAttributeTable ON $eavAttributeTable.attribute_id = $catalogProductEntityIntTable.attribute_id".
            " WHERE  $eavAttributeTable.attribute_code = 'status' AND $catalogProductEntityIntTable.value = '".Mage_Catalog_Model_Product_Status::STATUS_ENABLED."'". // only status enabled
            " AND ";
        if ($defaultStockManage) { // only with stock manage
            $query .= "($stockTable.manage_stock = 1 OR $stockTable.use_config_manage_stock = 1)";
        } else {
            $query .= "($stockTable.manage_stock = 1 AND $stockTable.use_config_manage_stock = 0)";
        }
        $query .= " AND ($productTable.type_id = 'simple')"; // only simple products


        $this->_getList = true;
        return $readConnection->fetchAll($query);
    }

    /**
     *	prepare xml block
     * @var array $item
     * @return string
     */
    public function prepareXmlBlock($key,$item) {
        $qty = intval($item['qty']);
        return "<product><sku>{$item['sku']}</sku><stock>".($qty >= 0 ? $qty : 0)."</stock></product>";
    }
}
