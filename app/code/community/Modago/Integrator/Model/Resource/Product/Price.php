<?php

class Modago_Integrator_Model_Resource_Product_Price
    extends Mage_Core_Model_Resource_Abstract
{

    protected function _construct()
    {
    }

    protected function _getResource()
    {
        return Mage::getSingleton('core/resource');
    }

    /**
     * Retrieve connection for read data
     *
     * @return Varien_Db_Adapter_Interface
     */
    protected function _getReadAdapter()
    {

        return Mage::getSingleton('core/resource')->getConnection('core_read');
    }

    /**
     * Retrieve connection for write data
     *
     * @return Varien_Db_Adapter_Interface
     */
    protected function _getWriteAdapter()
    {
        return Mage::getSingleton('core/resource')->getConnection('core_write');
    }

    /*
     *
     */
    public function getOptions($storeId)
    {

        $out = array();

        $adapter = $this->_getReadAdapter();

        $productSuperAttributeTable = $this->_getResource()->getTableName('catalog/product_super_attribute');
        $query = "SELECT DISTINCT product_id FROM `{$productSuperAttributeTable}` LIMIT";
        $ids = $adapter->fetchCol($query);

        $baseSelect = $adapter->select();
        $baseSelect->from(array("product" => $this->_getResource()->getTableName('catalog/product')));
        $baseSelect->reset("columns");
        $baseSelect->columns(array("entity_id", "sku", "type_id"));
        $baseSelect->where("product.entity_id IN (?)", $ids);


        foreach ($adapter->fetchAll($baseSelect) as $row) {
            $out[$row['entity_id']] = array_merge($row);
        }

        // Child data
        foreach ($this->getChildren($ids, $storeId) as $child) {
            // Group products by option
            if (!isset($out[$child['parent_id']]['children'][$child['value_id']])) {
                $out[$child['parent_id']]['children'][$child['value_id']] = array(
                    'sku' => $child['sku'],
                    'price' => ($child['price']+$child["simple_price"])
                );
            }

        }

        // Make flat arrays
        foreach ($out as &$item) {
            if (isset($item['children'])) {
                $item['children'] = array_values($item['children']);
            }
        }
        return array_values($out);
    }


    /**
     * @param array $ids
     * @param int $storeId
     * @return type
     */
    public function getChildren(array $ids, $storeId)
    {
        $websiteId = Mage::app()->getStore($storeId)->getWebsiteId();

        $resource = $this->_getResource();
        $read = $this->_getReadAdapter();

        $select = $read->select();
        $select->from(
            array("link" => $resource->getTableName("catalog/product_super_link")),
            array("parent_id", "product_id")
        );

        // Add configurable attribute
        $select->join(
            array("sa" => $resource->getTableName("catalog/product_super_attribute")),
            "sa.product_id=link.parent_id",
            array("attribute_id", "product_super_attribute_id")
        );

        $select->join(
            array("product" => $resource->getTableName("catalog/product")),
            "product.entity_id=link.product_id",
            array("sku")
        );
        $select->join(
            array("product_price" => $resource->getTableName("catalog_product_entity_decimal")),
            "product_price.entity_id=link.product_id AND store_id={$storeId}",
            array("value AS simple_price")
        );

        // Add values of attributes
        $select->join(
            array("product_int" => $resource->getTableName("catalog_product_entity_int")),
            "product_int.entity_id=link.product_id AND product_int.attribute_id=sa.attribute_id",
            array("value", "value_id")
        );

        // Add optional pricing
        $conds = array(
            "sa_price.product_super_attribute_id=sa.product_super_attribute_id",
            "sa_price.value_index=product_int.value",
            $this->_getReadAdapter()->quoteInto("sa_price.website_id=?", $websiteId)
        );

        $select->joinLeft(
            array("sa_price" => $resource->getTableName("catalog/product_super_attribute_pricing")),
            implode(" AND ", $conds),
            array()
        );

        // Optional price
        $select->columns(array("price" => new Zend_Db_Expr("IF(sa_price.value_id>0, sa_price.pricing_value, 0)")));

        $select->where("link.parent_id IN (?)", $ids);
        $select->order("sa.position");

        return $read->fetchAll($select);
    }
}