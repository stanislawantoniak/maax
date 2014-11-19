<?php


class Zolago_Catalog_Model_Resource_Product_Configurable
    extends Mage_Core_Model_Resource_Db_Abstract
{
    const PRICE_ATTRIBUTE_CODE = 'price';

    protected function _construct()
    {
        $this->_init('zolagocatalog/pricessizes', null);
    }


    /**
     * @param Mage_Core_Model_Abstract $object
     * @param array                    $storeId
     * @param array                    $configurableProductsIds
     *
     * @return array
     */
    public function getConfigurableMinPrice($configurableProductsIds, $storeId = 0)
    {
//        Zend_Debug::dump($configurableProductsIds);
        if (empty($configurableProductsIds)) {
            return array();
        }
        $adapter = $this->getReadConnection();
        $select = $adapter->select();

        $select
            ->from(
                'catalog_product_entity_decimal AS prices',
                array(
                    'configurable_product' => 'product_relation.parent_id',
                    'min_price' => 'MIN(prices.value)')
            )
            ->join(
                array('products' => 'catalog_product_entity'),
                'products.entity_id = prices.entity_id',
                array()
            )
            ->join(
                array('attribute' => 'eav_attribute'),
                'attribute.attribute_id=prices.attribute_id',
                array()
            )
            ->join(
                array('product_relation' => 'catalog_product_relation'),
                'product_relation.child_id = prices.entity_id',
                array()
            )
            ->where('products.type_id=?', Mage_Catalog_Model_Product_Type::TYPE_SIMPLE)//choose from simple products
            ->where('attribute.attribute_code=?', self::PRICE_ATTRIBUTE_CODE);


        $select->where("prices.store_id=?", $storeId);


        $configurableProductsIds = implode(',', $configurableProductsIds);
        $select->where("product_relation.parent_id IN({$configurableProductsIds})");

        //$select->order('products.entity_id');

        $select->group('product_relation.parent_id');
        //echo $select;
        $result = $adapter->fetchAssoc($select);
//        Zend_Debug::dump($result);
        return $result;
    }



    public function getConfigurableSimpleRelation($listUpdatedProducts)
    {

        if (empty($listUpdatedProducts)) {
            return array();
        }
        $listUpdatedProducts = implode(',', $listUpdatedProducts);
        $adapter = $this->getReadConnection();
        $select = $adapter->select();
        $select
            ->from(
                'catalog_product_relation AS product_relation',
                array(
                    'configurable_product' => 'product_relation.parent_id',
                    'simple_product' => 'product_relation.child_id'
                )
            )
            ->where("product_relation.child_id IN({$listUpdatedProducts})");
        //echo $select;
        $result = $adapter->fetchAssoc($select);


        return $result;
    }

    /**
     * @param $ids
     * @return array
     */
    public function getConfigurableSimpleRelationJoin($ids)
    {
        if(empty($ids)){
            return array();
        }
        $adapter = $this->getReadConnection();
        $select = $adapter->select();
        $select
            ->from(
                'zolago_catalog_queue_configurable AS queue_configurable',
                array()
            )
            ->join(
                'catalog_product_relation AS product_relation',
                'product_relation.child_id=queue_configurable.product_id',
                array(
                    'configurable_product' => 'product_relation.parent_id',
                    'simple_product' => 'product_relation.child_id'
                )
            )
            ->where('queue_configurable.status','0')
            ->where('product_relation.child_id IN (?)', implode(",",$ids))
        ;

        $result = $adapter->fetchAssoc($select);

        return $result;
    }

    /**
     * Get configurable prices
     *
     * @param array $storeId
     *
     * @return array
     */
    public function getConfigurablePrices($storeId, $limit = 0)
    {

        $adapter = $this->getReadConnection();
        $select = $adapter->select();
        $select
            ->from(
                'catalog_product_entity AS products',
                array(
                     'product' => 'products.entity_id',
                     'sku'     => 'products.sku'
                )
            )
            ->join(
                array('prices' => 'catalog_product_entity_decimal'),
                'prices.entity_id=products.entity_id',
                array(
                     'price' => 'prices.value'
                )
            )
            ->where("prices.attribute_id=?", self::PRICE_ATTRIBUTE_CODE)
            ->where("products.type_id=?", Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE)
//            ->where("prices.store_id IN (".implode(',',$storeId).")")
//            ->order('products.entity_id')
        ;
        if ($limit > 0) {
            $select->limit($limit);
        }

        $result = $adapter->fetchAssoc($select);
        return $result;
    }


    public function getConfigurablePricesMinPriceRelation($storeId, $limit = 0)
    {

        $adapter = $this->getReadConnection();
        $select = $adapter->select();
        $select
            ->from(
                'catalog_product_relation AS product_relation',
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
                     'price'     => 'product_entity_decimal.value',
                     'min_price' => 'MIN(product_entity_decimal.value)'
                )
            )
            ->where("product_entity_decimal.attribute_id=?", self::PRICE_ATTRIBUTE_CODE)
//            ->where("product_entity_decimal.store_id IN (".implode(',',$storeId).")" )

            ->group('product_relation.parent_id');
        if ($limit > 0) {
            $select->limit($limit);
        }

        $result = $adapter->fetchAssoc($select);
        return $result;
    }


    /**
     * @param $productConfigurableId
     * @param $superAttributeId
     * @param $productMinPrice
     * @param $storeId
     * @param $websiteId
     */
    public function insertProductSuperAttributePricing(
        $productConfigurableId, $superAttributeId, $productMinPrice, $store
    ) {

        $productRelations = $this->_getProductRelationPricesSizes($productConfigurableId, $store);

        if (!empty($productRelations)) {
            $insert = array();
            foreach ($productRelations as $productRelation) {

                $size = $productRelation['child_size'];
                $price = $productRelation['child_price'];
                $website = $productRelation['website'];

                $priceIncrement = (float)$price - $productMinPrice;

                $insert[] = "({$superAttributeId},{$size},{$priceIncrement},{$website})";
            }

            if (!empty($insert)) {
                $lineQuery = implode(",", $insert);

                $catalogProductSuperAttributePricingTable = 'catalog_product_super_attribute_pricing';

                $insertQuery = sprintf(
                    "
                    INSERT INTO  %s (product_super_attribute_id,value_index,pricing_value,website_id)
                    VALUES %s
                    ON DUPLICATE KEY UPDATE catalog_product_super_attribute_pricing.pricing_value=VALUES(catalog_product_super_attribute_pricing.pricing_value)
                    ", $catalogProductSuperAttributePricingTable, $lineQuery
                );

                $this->_getWriteAdapter()->query($insertQuery);

            }
        }
    }

    public function insertProductSuperAttributePricingApp($productConfigurableId, $superAttributeId, $store)
    {

        $productRelations = $this->_getProductRelationPricesSizes($productConfigurableId, $store);
        Zend_Debug::dump($productRelations);

        if (!empty($productRelations)) {
            $insert = array();
            $productMinPrice = array();
            foreach ($productRelations as $i) {
                $productMinPrice[] = $i['child_price'];
            }
            //Zend_Debug::dump($productMinPrice);
            $productMinimalPrice = min($productMinPrice);
            Mage::getSingleton('catalog/product_action')->updateAttributesNoIndex(
                array($productConfigurableId), array('price' => $productMinimalPrice), $store
            );

            foreach ($productRelations as $productRelation) {
                $size = $productRelation['child_size'];
                $price = $productRelation['child_price'];
                $website = $productRelation['website'];
                $productMinPrice[] = $price;

                $priceIncrement = (float)$price - $productMinimalPrice;

                $insert[] = "({$superAttributeId},{$size},{$priceIncrement},{$website})";
            }

            if (!empty($insert)) {
                $lineQuery = implode(",", $insert);

                $catalogProductSuperAttributePricingTable = 'catalog_product_super_attribute_pricing';

                $insertQuery = sprintf(
                    "
                    INSERT INTO  %s (product_super_attribute_id,value_index,pricing_value,website_id)
                    VALUES %s
                    ON DUPLICATE KEY UPDATE catalog_product_super_attribute_pricing.pricing_value=VALUES(catalog_product_super_attribute_pricing.pricing_value)
                    ", $catalogProductSuperAttributePricingTable, $lineQuery
                );

                $this->_getWriteAdapter()->query($insertQuery);

            }
        }
    }
    /**
     * get super attribute ids
     *
     * @return array
     */
    public function getSuperAttributes()
    {
        $readConnection = $this->_getReadAdapter();
        $select = $readConnection->select()
            ->from(
                'catalog_product_super_attribute',
                array('configurable_product' => 'product_id',
                      'super_attribute'      => 'product_super_attribute_id'
                )
            );
        $superAttributes = $readConnection->fetchAssoc($select);

        return $superAttributes;
    }


    /**
     * @param $productConfigurableId
     * @param $storeId
     *
     * @return array
     */
    private function _getProductRelationPricesSizes($productConfigurableId, $store)
    {
        $readConnection = $this->_getReadAdapter();
        $productRelations = array();

        $select = $readConnection->select()
            ->from('vw_product_relation_prices_sizes_relation')
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns(
                array('DISTINCT(child)', 'parent', 'child_size', 'child_price', 'website')
            )
            ->where('parent=?', $productConfigurableId)
            ->where('store=?', $store);
//echo $select;

        try
        {
            $productRelations = $readConnection->fetchAll($select);
        }
        catch(Exception $e)
        {
            Mage::log($e->getMessage(), Zend_Log::ERR);
        }

        return $productRelations;
    }

}