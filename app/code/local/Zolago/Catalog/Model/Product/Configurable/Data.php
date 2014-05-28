<?php


class Zolago_Catalog_Model_Product_Configurable_Data extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('zolagocatalog/pricessizes');
    }


    /**
     * @param Mage_Core_Model_Abstract $object
     * @param int $storeId
     * @param array $configurableProductsIds
     * @return array
     */
    public function getConfigurableMinPrice($storeId, $configurableProductsIds = array())
    {
        $result = array();

        $adapter = $this->getReadConnection();
        $select = $adapter->select();

        $select
            ->from('catalog_product_entity_decimal AS prices',
                array(
                    'configurable_product' => 'product_relation.parent_id',
                    'min_price' => 'MIN(NULLIF(prices.value, 0))')
            )

            ->join(
                array('products' => 'catalog_product_entity'),
                'products.entity_id = prices.entity_id',
                array()
            )
            ->join(
                array('product_relation' => 'catalog_product_relation'),
                'product_relation.child_id = prices.entity_id',
                array()
            )
            ->where('products.type_id=?', 'simple') //choose from simple products
            ->where('prices.attribute_id=?', 75)
            ->where('prices.store_id=?', (int)$storeId);
        if (!empty($configurableProductsIds)) {
            $select->where("product_relation.parent_id IN({$configurableProductsIds})");
        }
        $select->order('products.entity_id');

        $select->group('product_relation.parent_id');

        $result = $adapter->fetchAssoc($select);


        return $result;
    }


    /**
     * @param $listUpdatedProducts
     * @return array
     */
    public function getConfigurableSimpleRelation($listUpdatedProducts, $limit = 0)
    {
        $result = array();
        if (!empty($listUpdatedProducts)) {
            $adapter = $this->getReadConnection();
            $select = $adapter->select();
            $select
                ->from('catalog_product_relation AS product_relation',
                    array(
                        'configurable_product' => 'product_relation.parent_id',
                        'simple_product' => 'product_relation.child_id'
                    )
                )
                ->join(
                    array('products' => 'catalog_product_entity'),
                    'product_relation.child_id=products.entity_id',
                    array(
                        'simple_sku' => 'products.sku'
                    )
                )
                ->where("products.sku IN ({$listUpdatedProducts})");
            $result = $adapter->fetchAssoc($select);
        }

        return $result;
    }


    /**
     * Get configurable prices
     * @param int $storeId
     * @return array
     */
    public function getConfigurablePrices($storeId = 0, $limit = 0)
    {

        $adapter = $this->getReadConnection();
        $select = $adapter->select();
        $select
            ->from('catalog_product_entity AS products',
                array(
                    'product' => 'products.entity_id',
                    'sku' => 'products.sku'
                )
            )
            ->join(
                array('prices' => 'catalog_product_entity_decimal'),
                'prices.entity_id=products.entity_id',
                array(
                    'price' => 'prices.value'
                )
            )
            ->where("prices.attribute_id=?", 75)
            ->where("products.type_id=?", 'configurable')
            ->where("prices.store_id=?", (int)$storeId)
            ->order('products.entity_id');
        if ($limit > 0) {
            $select->limit($limit);
        }

        $result = $adapter->fetchAssoc($select);
        return $result;
    }



    public function getConfigurablePricesMinPriceRelation($storeId = 0, $limit = 0)
    {

        $adapter = $this->getReadConnection();
        $select = $adapter->select();
        $select
            ->from('catalog_product_relation AS product_relation',
                array(
                    'product' => 'parent_id'
                )
            )
            ->join(
                array('product_super_attribute' => 'catalog_product_super_attribute'),
                'product_relation.parent_id=product_super_attribute.product_id',
                array()
            )
            ->join(
                array('product_super_attribute_pricing' => 'catalog_product_super_attribute_pricing'),
                'product_super_attribute_pricing.product_super_attribute_id=product_super_attribute.product_super_attribute_id',
                array(
                    'diff' => 'product_super_attribute_pricing.pricing_value'
                )
            )
            ->join(
                array('product_entity_decimal' => 'catalog_product_entity_decimal'),
                'product_relation.child_id=product_entity_decimal.entity_id',
                array(
                    'price' => 'product_entity_decimal.value',
                    'min_price' => 'MIN(product_entity_decimal.value)'
                )
            )
            ->where("product_entity_decimal.attribute_id=?", 75)
            ->where("product_entity_decimal.store_id", (int)$storeId)

            ->group('product_relation.parent_id');
        if ($limit > 0) {
            $select->limit($limit);
        }

        $result = $adapter->fetchAssoc($select);
        return $result;
    }
}