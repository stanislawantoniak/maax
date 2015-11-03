<?php

class Modago_Integrator_Model_Resource_Product_Price
    extends Mage_Core_Model_Resource_Db_Abstract
{

    protected function _construct()
    {
        $this->_init("catalog/product", null);
    }

    /*
     *
     */
    public function getOptions($storeId)
    {

        $out = array();

        $adapter = $this->getReadConnection();

        $productSuperAttributeTable = $this->getTable('product_super_attribute');
        $query = "SELECT DISTINCT product_id FROM `{$productSuperAttributeTable}`";
        $ids = $adapter->fetchCol($query);

        $baseSelect = $adapter->select();
        $baseSelect->from(array("product" => $this->getMainTable()));
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
                    'price' => $child['price']
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

        $select = $this->getReadConnection()->select();
        $select->from(
            array("link" => $this->getTable("catalog/product_super_link")),
            array("parent_id", "product_id")
        );

        // Add configurable attribute
        $select->join(
            array("sa" => $this->getTable("catalog/product_super_attribute")),
            "sa.product_id=link.parent_id",
            array("attribute_id", "product_super_attribute_id")
        );

        $select->join(
            array("product" => $this->getTable("catalog/product")),
            "product.entity_id=link.product_id",
            array("sku")
        );

        // Add values of attributes
        $select->join(
            array("product_int" => $this->getValueTable("catalog/product", "int")),
            "product_int.entity_id=link.product_id AND product_int.attribute_id=sa.attribute_id",
            array("value", "value_id")
        );

        // Add optional pricing
        $conds = array(
            "sa_price.product_super_attribute_id=sa.product_super_attribute_id",
            "sa_price.value_index=product_int.value",
            $this->getReadConnection()->quoteInto("sa_price.website_id=?", $websiteId)
        );

        $select->joinLeft(
            array("sa_price" => $this->getTable("catalog/product_super_attribute_pricing")),
            implode(" AND ", $conds),
            array()
        );

        // Optional price
        $select->columns(array("price" => new Zend_Db_Expr("IF(sa_price.value_id>0, sa_price.pricing_value, 0)")));

        $select->where("link.parent_id IN (?)", $ids);
        $select->order("sa.position");

        return $this->getReadConnection()->fetchAll($select);
    }
}