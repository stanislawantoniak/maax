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
     * Used in UNIT TEST
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
     * TODO remove: no usages found
     * Return array like family tree
     * roots is parents
     * nodes are children
     *
     * example:
     * for input array
     * (
     *    [0] => 25733
     *    [1] => 2
     *    [2] => 25734
     *    [3] => 25735
     * )
     * return array could be
     * [32339<parent_id>] => Array
     *    (
     *    [0] => 25733<child_id>
     *    [1] => 25734<child_id>
     *    [2] => 25735<child_id>
     *    )
     *
     * @param $listUpdatedProducts
     * @return array
     */
    public function getConfigurableSimpleRelationArray($listUpdatedProducts)
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

        $result = $adapter->fetchAll($select);
        $arr = array();
        foreach ($result as $row) {
            $arr[$row['configurable_product']][] = $row['simple_product'];
        }

        return $arr;
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

                $priceIncrement = number_format(Mage::app()->getLocale()->getNumber($price - $productMinPrice), 2);

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

    public function insertProductSuperAttributePricingApp($productConfigurableId, $superAttributeId, $stores)
    {
        /* @var $aM Zolago_Catalog_Model_Product_Action */
        $aM = Mage::getSingleton('catalog/product_action');
        $msrpPricesSource = array(); $productRelations = array(); $msrpPrices = array();
        foreach ($stores as $store) {
            $priceSizeRelation = $this->_getProductRelationPricesSizes($productConfigurableId, $store);
            if (!empty($priceSizeRelation)) {
                $productRelations[$store] = $priceSizeRelation;
            }

            $msrpSource = $this->getIsMSRPManual($productConfigurableId, $store);
            if (!empty($msrpSource)) {
                $msrpPricesSource[$store] = $this->getIsMSRPManual($productConfigurableId, $store);
            }

            $msrpPriceValues = $this->getMSRPPrices($productConfigurableId, $store);
            if (!empty($msrpPriceValues)) {
                $msrpPrices[$store] = $msrpPriceValues;
            }

        }
        unset($store);
        unset($msrpSource);
        unset($priceSizeRelation);
        unset($msrpPriceValues);


        $insert = array();
        foreach ($stores as $store) {
            if (
                (isset($productRelations[$store]) && !empty($productRelations[$store]))
                ||
                isset($msrpPrices[$store]) && !empty($msrpPrices[$store])
            ) {

                if (isset($productRelations[$store]) && !empty($productRelations[$store])) {
                    //1. update price
                    $productMinPrice = array();
                    foreach ($productRelations[$store] as $i) {
                        $productMinPrice[] = $i['child_price'];
                    }
                    unset($i);
                    $productMinimalPrice = min($productMinPrice);
                    $aM->updateAttributesPure(
                        array($productConfigurableId), array('price' => $productMinimalPrice), $store
                    );

                    //2. update options
                    foreach ($productRelations[$store] as $productRelation) {
                        $size = $productRelation['child_size'];
                        $price = $productRelation['child_price'];
                        $website = $productRelation['website'];


                        $priceIncrement = (float)$price - $productMinimalPrice;

                        $insert[] = "({$superAttributeId},{$size},{$priceIncrement},{$website})";
                    }
                }


                $msrpSource = Zolago_Catalog_Model_Product_Source_Convertermsrptype::FLAG_AUTO;
                if (isset($msrpPricesSource[$store])) {
                    $msrpPricesSourceValue = array_pop($msrpPricesSource[$store]);
                    $msrpSource = (int)$msrpPricesSourceValue['msrp_source_type'];
                }

                //if Converter Msrp Type = From file
                if ($msrpSource == Zolago_Catalog_Model_Product_Source_Convertermsrptype::FLAG_AUTO) {
                    if (isset($msrpPrices[$store]) && !empty($msrpPrices[$store])) {
                        //3. update msrp
                        $productMSRPMinPrice = array();
                        foreach ($msrpPrices[$store] as $i) {
                            $productMSRPMinPrice[] = $i['msrp_price'];
                        }
                        unset($i);

                        if (!empty($productMSRPMinPrice)) {
                            $productMSRPMinimalPrice = min($productMSRPMinPrice);

                            $aM->updateAttributesPure(
                                array($productConfigurableId), array('msrp' => $productMSRPMinimalPrice), $store
                            );
                        }
                    }
                }
            }
        }

        if (!empty($insert)) {
            $insert = array_unique($insert);
            $this->_insertProductOptions($insert);

        }
    }


    public function setProductOptionsBasedOnSimples($recoverOptionsProducts)
    {

        if (empty($recoverOptionsProducts)) {
            return;
        }

        $websiteIdsToUpdate = array_keys($recoverOptionsProducts);
        /* @var $zolagocatalogHelper Zolago_Catalog_Helper_Data */
        $zolagocatalogHelper = Mage::helper('zolagocatalog');
        $stores = $zolagocatalogHelper->getStoresForWebsites($websiteIdsToUpdate);

        foreach ($recoverOptionsProducts as $websiteId => $parentIds) {
            if (empty($parentIds)) {
                continue;
            }

            $superAttributes = $this->getSuperAttributes($parentIds);
            $storesToUpdate = isset($stores[$websiteId]) ? $stores[$websiteId] : false; //array of stores

            $priceSizeRelation = array();
            foreach ($storesToUpdate as $store) {
                $priceSizeRelationByStore = $this->_getProductRelationPricesSizes($parentIds, $store);
                foreach ($priceSizeRelationByStore as $priceSizeRelationByStoreI) {
                    $priceSizeRelation[$priceSizeRelationByStoreI['parent']][$store][] = $priceSizeRelationByStoreI;
                }
            }

            foreach ($parentIds as $parentId) {
                $superAttributeId = isset($superAttributes[$parentId]) ? $superAttributes[$parentId]['super_attribute'] : false;
                $priceSizeRelationForStores = isset($priceSizeRelation[$parentId]) ? $priceSizeRelation[$parentId] : false;

                if ($superAttributeId && $priceSizeRelationForStores) {
                    $this->setConfigurableProductOptions($parentId, $superAttributeId, $priceSizeRelationForStores, $websiteId, $storesToUpdate);
                }
            }
        }
        unset($parentIds);
    }


    public function setConfigurableProductOptions($productConfigurableId, $superAttributeId, $priceSizeRelation, $website, $stores)
    {
        if (empty($productConfigurableId)) {
            return;
        }
        /* @var $aM Zolago_Catalog_Model_Product_Action */
        $aM = Mage::getSingleton('catalog/product_action');
        $insert = array();

        foreach ($stores as $store) {
            $productRelationsByStore = isset($priceSizeRelation[$store]) ? $priceSizeRelation[$store] : false;

            if (!$productRelationsByStore) {
                continue;
            }

            //1. update price
            $productMinPrice = array();
            foreach ($productRelationsByStore as $i) {
                $productMinPrice[] = $i['child_price'];
            }
            unset($i);
            $productMinimalPrice = min($productMinPrice);
            $aM->updateAttributesPure(
                array($productConfigurableId), array('price' => $productMinimalPrice), $store
            );

            //2. update options
            foreach ($productRelationsByStore as $productRelation) {
                $size = $productRelation['child_size'];
                $price = $productRelation['child_price'];
                $website = $productRelation['website'];

                $priceIncrement = number_format(Mage::app()->getLocale()->getNumber($price - $productMinimalPrice), 2);

                $insert[] = "({$superAttributeId},{$size},{$priceIncrement},{$website})";
            }

        }


        if (!empty($insert)) {
            $insert = array_unique($insert);
            $this->_insertProductOptions($insert);
        }
    }

    public function _insertProductOptions(array$insert)
    {
        if (empty($insert)) {
            return;
        }
        $lineQuery = implode(",", $insert);

        $catalogProductSuperAttributePricingTable = 'catalog_product_super_attribute_pricing';

        $insertQuery = sprintf(
            "
                    INSERT INTO  %s (product_super_attribute_id,value_index,pricing_value,website_id)
                    VALUES %s
                    ON DUPLICATE KEY UPDATE catalog_product_super_attribute_pricing.pricing_value=VALUES(catalog_product_super_attribute_pricing.pricing_value)
                    ", $catalogProductSuperAttributePricingTable, $lineQuery
        );

        try {
            $this->_getWriteAdapter()->query($insertQuery);

        } catch (Exception $e) {
            Mage::log($e->getMessage(), 0, 'configurable_update.log');
            Mage::throwException("Error insertProductSuperAttributePricingApp");

            throw $e;
        }
    }

    /**
     * get super attribute ids
     *
     * @return array
     */
    public function getSuperAttributes($configurableProductsIds)
    {
        if (empty($configurableProductsIds)) {
            return array();
        }

        $readConnection = $this->_getReadAdapter();
        $select = $readConnection->select()
            ->from(
                'catalog_product_super_attribute',
                array('configurable_product' => 'product_id',
                    'super_attribute' => 'product_super_attribute_id'
                )
            );
        $select->where("catalog_product_super_attribute.product_id IN(?)", $configurableProductsIds);
        $superAttributes = $readConnection->fetchAssoc($select);
        return $superAttributes;
    }

    /**
     * @param $ids
     */
    public  function removeUpdatedRows($ids)
    {
        $table = 'zolago_catalog_queue_configurable';
        $lineQuery = implode(',', $ids);
        $delete = sprintf("DELETE FROM  %s WHERE queue_id IN (%s);", $table, $lineQuery);
        try {
            $this->_getWriteAdapter()->query($delete);

        } catch (Exception $e) {
            Mage::throwException("Error insertProductSuperAttributePricingApp");

            throw $e;
        }

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

        $table = $this->getMainTable();
        $select = $readConnection->select()
            ->from($table)
            ->reset(Zend_Db_Select::COLUMNS)
            ->columns(
                array('DISTINCT(child)', 'parent', 'child_size', 'child_price', 'website')
            );
        if (is_array($productConfigurableId)) {
            $select->where('parent IN(?)', $productConfigurableId);
        } else {
            $select->where('parent=?', $productConfigurableId);
        }
        $select->where('store=?', $store);

        try {
            $productRelations = $readConnection->fetchAll($select);
        } catch (Exception $e) {
            Mage::log($e->getMessage(), Zend_Log::ERR);
        }

        return $productRelations;
    }


    public function getMSRPPrices($configurableId,$store)
    {
        $msrp = array();

        if (empty($configurableId)) {
            return array();
        }
        $entityTypeID = Mage::getModel('catalog/product')->getResource()->getTypeId();

        $readConnection = $this->_getReadAdapter();
        $select = $readConnection->select();
        $select->from(
            array("product_relation" => "catalog_product_relation"),
            array(
                "parent_id" => "product_relation.parent_id",
                "child_id" => "product_relation.child_id"
            )
        );
        $select->join(
            array("msrp" => "catalog_product_entity_decimal"),
            "msrp.entity_id=product_relation.child_id",
            array(
                "msrp_price" => "msrp.value",
                "store_id" => "msrp.store_id",
            )
        );
        $select->join(
            array("products" => $this->getTable("catalog/product")),
            "products.entity_id=product_relation.child_id",
            array(
                'products.sku'
            )
        );
        $select->join(
            array("attributes" => $this->getTable("eav/attribute")),
            "attributes.attribute_id=msrp.attribute_id",
            array()
        );
        $select->where(
            "attributes.entity_type_id=?", $entityTypeID
        );
        $select->where(
            "attributes.attribute_code=?", Zolago_Catalog_Model_Product::ZOLAGO_CATALOG_MSRP_CODE
        );

        $select->where("product_relation.parent_id=?", $configurableId);
        $select->where(
            "msrp.store_id=?", $store
        );

        //Mage::log($select->__toString(), 0, 'priceMSRPSource.log');
        try {
            $msrp = $readConnection->fetchAll($select);
        } catch (Exception $e) {
            Mage::throwException("Error fetching msrp values");
        }

        return $msrp;
    }

    public function getIsMSRPManual($configurableId, $store)
    {
        $msrp = array();

        if (empty($configurableId)) {
            return array();
        }
        $entityTypeID = Mage::getModel('catalog/product')->getResource()->getTypeId();

        $readConnection = $this->_getReadAdapter();
        $select = $readConnection->select();
        $select->from(
            array("msrp_source" => "catalog_product_entity_int"),
            array()
        );
        $select->join(
            array("product_relation" => "catalog_product_relation"),
            "product_relation.parent_id=msrp_source.entity_id",
            array(
                "parent_id" => "DISTINCT(product_relation.parent_id)",
                "msrp_source_type" => "msrp_source.value",
                "store" => "msrp_source.store_id"
            )
        );
        $select->join(
            array("products" => $this->getTable("catalog/product")),
            "products.entity_id=product_relation.child_id",
            array()
        );
        $select->join(
            array("attributes" => $this->getTable("eav/attribute")),
            "attributes.attribute_id=msrp_source.attribute_id",
            array()
        );
        $select->where(
            "attributes.entity_type_id=?", $entityTypeID
        );
        $select->where(
            "attributes.attribute_code=?", Zolago_Catalog_Model_Product::ZOLAGO_CATALOG_CONVERTER_MSRP_TYPE_CODE
        );
        $select->where("msrp_source.value=?", Zolago_Catalog_Model_Product_Source_Convertermsrptype::FLAG_MANUAL);
        $select->where("product_relation.parent_id=?", $configurableId);
        $select->where(
            "msrp_source.store_id=?", $store
        );

        //Mage::log($select->__toString(), 0, 'priceMSRPSource.log');
        try {
            $msrp = $readConnection->fetchAll($select);
        } catch (Exception $e) {
            Mage::throwException("Error fetching converter_msrp_type values");
        }

        return $msrp;
    }

}