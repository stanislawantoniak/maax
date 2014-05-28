<?php


class Zolago_Catalog_Model_Product_Configurable_Data extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {

    }


    /**
     * @param Mage_Core_Model_Abstract $object
     * @param int $storeId
     * @param array $configurableProductsIds
     * @return array
     */
    public function getConfigurableMinPrice(int $storeId, array $configurableProductsIds)
    {
        $result = array();

        if (!empty($configurableProductsIds)) {
            $adapter = $this->getReadConnection();
            $select = $adapter->select();

            $select
                ->from('catalog_product_entity_decimal AS prices',
                    array(
                        'configurable_product' => 'product_relation.parent_id',
                        'min_price' => 'MIN(prices.value)')
                )
                ->join(
                    array('sizes' => 'catalog_product_entity_int'),
                    'prices.entity_id = sizes.entity_id',
                    array()
                )
                ->join(
                    array('product_relation' => 'catalog_product_relation'),
                    'product_relation.child_id = sizes.entity_id',
                    array()
                )
                ->where('prices.store_id=?', (int)$storeId)
                ->where("product_relation.parent_id IN({$configurableProductsIds})")
                ->group('product_relation.parent_id');
            $result = $adapter->fetchAssoc($select);
        }

        return $result;
    }


    /**
     * @param $listUpdatedProducts
     * @return array
     */
    public function getConfigurableSimpleRelation($listUpdatedProducts)
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
}