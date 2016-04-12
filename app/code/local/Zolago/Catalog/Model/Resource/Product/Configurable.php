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

        $result = $adapter->fetchAssoc($select);
        return $result;
    }


    /**
     * @param $listUpdatedProducts
     * @return array
     */
    public function getConfigurableSimpleRelation($listUpdatedProducts)
    {

        if (empty($listUpdatedProducts))
            return array();

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
            ->where("product_relation.child_id IN(?)", $listUpdatedProducts);

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

    public static $_vendorAutomaticStrikeoutPricePercent;



    /**
     * @param $storeId
     * @param $ids
     */
    public function updateSalePromoFlagForStore($storeId, $ids)
    {
        /** @var Zolago_Catalog_Model_Resource_Product_Collection $coll */
        $coll = Mage::getResourceModel('zolagocatalog/product_collection');
        $coll->setStore($storeId);

        $coll->addAttributeToSelect("price", "left");                   //WEBSITE
        $coll->addAttributeToSelect("msrp", "left");                    //WEBSITE
        $coll->addAttributeToSelect("campaign_regular_id", "left");     //WEBSITE
        $coll->addAttributeToSelect("udropship_vendor", "left");        //GLOBALNY
        $coll->addAttributeToSelect("is_new", "left");                  //GLOBALNY

        $coll->addFieldToFilter('entity_id', array('in' => $ids));


        $setFlagEmpty = array();
        $setFlagPromo = array();
        $setFlagSale = array();

        $data = $coll->getData();

        if (empty($data))
            return;

        foreach ($data as $_product) {

            if (isset(self::$_vendorAutomaticStrikeoutPricePercent[$_product["udropship_vendor"]])) {
                $percent = self::$_vendorAutomaticStrikeoutPricePercent[$_product["udropship_vendor"]];
            } else {
                $_vendor = Mage::getModel("udropship/vendor")->load($_product["udropship_vendor"]);

                $percent = Mage::helper("zolagocatalog")->getAutomaticStrikeoutPricePercent($_vendor);
                self::$_vendorAutomaticStrikeoutPricePercent[$_product["udropship_vendor"]] = $percent;

            }

            if (!empty($_product["campaign_regular_id"])) {
                // przypadek dotyczy tylko sytuacji gdy produkt nie jest w kampanii promo/sale
                continue;
            }
            if (
                (float)$_product["price"] > 0
                &&
                (float)$_product["msrp"]
                &&
                (float)$_product["msrp"] - (float)$_product["price"] >= ((float)$_product["price"] * (float)($percent / 100))
            ) {

                if (empty((int)$_product["is_new"])) {
                    $setFlagSale[$storeId][] = $_product["entity_id"];
                } else {
                    $setFlagPromo[$storeId][] = $_product["entity_id"];
                }

            } else {
                $setFlagEmpty[$storeId][] = $_product["entity_id"];
            }

        }


        /* @var $aM Zolago_Catalog_Model_Product_Action */
        $aM = Mage::getSingleton('catalog/product_action');

        if (!empty($setFlagSale)) {
            foreach ($setFlagSale as $storeId => $productIds) {
                $aM->updateAttributesPure($productIds,
                    array("product_flag" => Zolago_Catalog_Model_Product_Source_Flag::FLAG_SALE),
                    $storeId
                );
            }
        }

        unset($storeId, $productIds);
        if (!empty($setFlagPromo)) {
            foreach ($setFlagPromo as $storeId => $productIds) {
                $aM->updateAttributesPure($productIds,
                    array("product_flag" => Zolago_Catalog_Model_Product_Source_Flag::FLAG_PROMOTION),
                    $storeId
                );
            }
        }

        unset($storeId, $productIds);
        if (!empty($setFlagEmpty)) {
            foreach ($setFlagEmpty as $storeId => $productIds) {
                $aM->updateAttributesPure($productIds,
                    array("product_flag" => null),
                    $storeId
                );
            }
        }
    }


    public function updateSalePromoFlag($ids)
    {
        $websites = Mage::app()->getWebsites();
        foreach ($websites as $website) {

            $defaultStoreId = Mage::app()
                ->getWebsite($website->getId())
                ->getDefaultGroup()
                ->getDefaultStoreId();

            $this->updateSalePromoFlagForStore($defaultStoreId, $ids);
        }
    }


    /**
     * Set parent_price=min(child1_price,child2_price,...)
     * and options
     *
     * @param $recoverOptionsProducts
     */
    public function recoverProductPriceAndOptionsBasedOnSimples($recoverOptionsProducts)
    {

        if (empty($recoverOptionsProducts)) {
            return;
        }

        $websiteIdsToUpdate = array_keys($recoverOptionsProducts);
        /* @var $zolagocatalogHelper Zolago_Catalog_Helper_Data */
        $zolagocatalogHelper = Mage::helper('zolagocatalog');
        $stores = $zolagocatalogHelper->getStoresForWebsites($websiteIdsToUpdate);

        $parentIds = array();
        /* @var $configResourceModel   Zolago_Catalog_Model_Resource_Product_Configurable */
        $configResourceModel = Mage::getResourceModel('zolagocatalog/product_configurable');
        foreach ($recoverOptionsProducts as $websiteId => $parentIdsPerWebsite) {
            $parentIds = array_merge($parentIdsPerWebsite,$parentIdsPerWebsite);
        }
        $superAttributes = $configResourceModel->getSuperAttributes($parentIds);

        /* @var $configModel  Zolago_Catalog_Model_Product_Type_Configurable */
        $configModel = Mage::getModel('zolagocatalog/product_type_configurable');

        $dataToUpdate = array();

        //1. Collect data before update
        foreach ($recoverOptionsProducts as $websiteId => $parentIds) {
            if (empty($parentIds)) {
                //Nothing to recover
                continue;
            }
            if(!isset($dataToUpdate[$websiteId])){
                $dataToUpdate[$websiteId] = array();
            }

            try {
                $firstStore = array_values($stores[$websiteId])[0];
                $childProductsByAttribute = $configModel->getUsedSizePriceRelations($firstStore, $parentIds);
                foreach ($parentIds as $parentId) {

                    $superAttributeId = isset($superAttributes[$parentId]) ? $superAttributes[$parentId]['super_attribute'] : false;
                    $priceSizeRelation = isset($childProductsByAttribute[$parentId]) ? $childProductsByAttribute[$parentId] : false;

                    if ($superAttributeId && $priceSizeRelation) {
                        $dataToUpdate[$websiteId] = array_merge_recursive($dataToUpdate[$websiteId], $this->collectConfigurableProductOptions($parentId, $superAttributeId, $priceSizeRelation, $websiteId));
                    }
                }
            } catch (Mage_Core_Exception $e) {
                Mage::logException($e);
            }
        }

        //2. Update

        $options = array();
        if (!empty($dataToUpdate)) {
            /* @var $aM Zolago_Catalog_Model_Product_Action */
            $aM = Mage::getSingleton('catalog/product_action');

            foreach ($dataToUpdate as $website => $data) {
                $price = $data["price"];
                $options = array_merge($options, $data["options"]);

                if (!isset($stores[$website]))
                    continue;

                foreach ($price as $value => $productIds) {
                    foreach ($stores[$website] as $store) {
                        $aM->updateAttributesPure($productIds, array("price" => (string)$value), $store);

                        $col = Zolago_Turpentine_Model_Observer_Ban::collectProductsBeforeBan($productIds, $store);
                        Mage::dispatchEvent("zolagocatalog_converter_stock_complete", array("products" => $col));
                    }
                }
            }
        }

        if (!empty($options))
            $this->insertProductOptions($options);

    }


    /**
     * Set parent_msrp=min(child1_msrp,child2_msrp,...)
     *
     * @param $recoverMSRP array(website_id1 => array(parent_product_id1, parent_product_id_2, ...), website_id2 => array(...))
     */
    public function recoverProductMSRPBasedOnSimples($recoverMSRP)
    {
        if (empty($recoverMSRP)) {
            return;
        }

        $websiteIdsToUpdate = array_keys($recoverMSRP);
        /* @var $zolagocatalogHelper Zolago_Catalog_Helper_Data */
        $zolagocatalogHelper = Mage::helper('zolagocatalog');
        $stores = $zolagocatalogHelper->getStoresForWebsites($websiteIdsToUpdate);


        $parentIds = array();
        /* @var $configResourceModel   Zolago_Catalog_Model_Resource_Product_Configurable */
        $configResourceModel = Mage::getResourceModel('zolagocatalog/product_configurable');
        foreach ($recoverMSRP as $websiteId => $parentIdsPerWebsite) {
            $parentIds = array_merge($parentIds, $parentIdsPerWebsite);
        }

        /* @var $configModel  Zolago_Catalog_Model_Product_Type_Configurable */
        $configModel = Mage::getModel('zolagocatalog/product_type_configurable');

        $dataToUpdate = array();

        //1. Collect data before update
        foreach ($recoverMSRP as $websiteId => $parentIds) {
            if (empty($parentIds)) {
                //Nothing to recover
                continue;
            }
            if (!isset($dataToUpdate[$websiteId])) {
                $dataToUpdate[$websiteId] = array();
            }

            try {
                $firstStore = array_values($stores[$websiteId])[0];
                $childProductsMSRP = $configModel->getMSRPForChildren($firstStore, $parentIds);

                foreach ($parentIds as $parentId) {
                    $msrpRelation = isset($childProductsMSRP[$parentId]) ? $childProductsMSRP[$parentId] : false;

                    if ($msrpRelation) {
                        $dataToUpdate[$websiteId] = array_merge_recursive($dataToUpdate[$websiteId], $this->collectConfigurableProductMSRP($parentId, $msrpRelation, $websiteId));
                    }
                }
            } catch (Mage_Core_Exception $e) {
                Mage::logException($e);
            }
        }

        //2. Update
        $options = array();
        if (!empty($dataToUpdate)) {
            /* @var $aM Zolago_Catalog_Model_Product_Action */
            $aM = Mage::getSingleton('catalog/product_action');

            foreach ($dataToUpdate as $website => $data) {
                foreach ($data as $value => $productIds) {
                    if (!isset($stores[$website]))
                        continue;

                    foreach ($stores[$website] as $store) {
                        $aM->updateAttributesPure($productIds, array("msrp" => (string)$value), $store);
                    }
                }
            }
        }
    }


    /**
     * Collect min price for configurable and options
     *
     * @param $productConfigurableId
     * @param $superAttributeId
     * @param $priceSizeRelation
     * @param $websiteId
     * @return array
     */
    public function collectConfigurableProductOptions($productConfigurableId, $superAttributeId, $priceSizeRelation, $websiteId)
    {
        $dataToUpdate = array();

        if (empty($productConfigurableId)) {
            return $dataToUpdate; //Nothing to update
        }

        $insert = array();

        //1. Collect price
        $productMinPrice = array();
        foreach ($priceSizeRelation as $item) {
            $productMinPrice[] = $item['price'];
        }

        $productMinimalPrice = min($productMinPrice);
        $dataToUpdate["price"][number_format(Mage::app()->getLocale()->getNumber($productMinimalPrice), 2)][$websiteId] = $productConfigurableId;

        //2. Collect options
        foreach ($priceSizeRelation as $productRelation) {
            $size = $productRelation['size'];
            $price = $productRelation['price'];

            $priceIncrement = Mage::app()->getLocale()->getNumber($price - $productMinimalPrice);
            $insert[] = "({$superAttributeId},{$size},{$priceIncrement},{$websiteId})";
        }


        $dataToUpdate["options"] = array_unique($insert);

        return $dataToUpdate;
    }

    /**
     * Collect min msrp for configurable
     *
     * @param $productConfigurableId
     * @param $msrpRelation
     * @param $websiteId
     * @return array
     */
    public function collectConfigurableProductMSRP($productConfigurableId, $msrpRelation, $websiteId)
    {
        $dataToUpdate = array();

        if (empty($productConfigurableId)) {
            return $dataToUpdate; //Nothing to update
        }

        $insert = array();

        //1. Collect price
        $productMinPrice = array();
        foreach ($msrpRelation as $item) {
            $productMinPrice[] = $item['msrp'];
        }

        $productMinimalPrice = min($productMinPrice);
        $productMinimalPrice = number_format(Mage::app()->getLocale()->getNumber($productMinimalPrice), 2);
        if(!empty($productMinimalPrice)){
            $dataToUpdate[$productMinimalPrice][$websiteId] = $productConfigurableId;
        }

        return $dataToUpdate;
    }

    /*
     * Inset product options
     */
    public function insertProductOptions(array $insert)
    {
        $this->_getWriteAdapter()->beginTransaction();
        try {
            $this->_getWriteAdapter()->commit();

            $limitRowsToInsert = 100;
            // save collected data every 1000 rows
            if (count($insert) >= $limitRowsToInsert) {
                $insertBatch = array_chunk($insert, $limitRowsToInsert);
                foreach ($insertBatch as $insertBatchItem) {
                    $this->_insertProductOptions($insertBatchItem);
                }
            } else {
                $this->_insertProductOptions($insert);
            }
            $this->_getWriteAdapter()->commit();

        } catch (Exception $e) {
            $this->_getWriteAdapter()->rollBack();
            Mage::logException($e);
        }

        return $this;
    }

    /**
     * @param array $insert
     */
    public function _insertProductOptions(array $insert)
    {
        //Mage::log($insert, null, "_insertProductOptions.log");
        if (empty($insert)) {
            return;
        }
        $lineQuery = implode(",", $insert);

        $catalogProductSuperAttributePricingTable = $this->getTable('catalog/product_super_attribute_pricing');

        $insertQuery = sprintf("
                    INSERT INTO  %s (product_super_attribute_id,value_index,pricing_value,website_id)
                    VALUES %s
                    ON DUPLICATE KEY UPDATE catalog_product_super_attribute_pricing.pricing_value=VALUES(catalog_product_super_attribute_pricing.pricing_value)
                    ", $catalogProductSuperAttributePricingTable, $lineQuery
        );

        try {
            $this->_getWriteAdapter()->query($insertQuery);

        } catch (Exception $e) {
            Mage::logException($e);
            //Mage::log($e->getMessage(), null, "_insertProductOptions.log");
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


    /**
     *
     * Update configurable products values
     * 1. price=min(child1_price,child2_price,...) (if product not in valid SALE or PROMOTION campaign)
     * 2. msrp=min(child1_msrp,child2_msrp,...)
     * 3. configurable product options
     * for website
     *
     * @param $parentIds
     * @return array
     */
    public function updateConfigurableProductsValues($parentIds){
        $productsIdsPullToSolr = array();
        Mage::log($parentIds, null, "qqq.log");
        //1. Filter valid campaign products

        //1.1. Get collection of configurable products NOT IN SALE or PROMOTION
        /* @var $collection Mage_Catalog_Model_Resource_Product_Collection */
        $collection = Mage::getResourceModel('zolagocatalog/product_collection');
        $collection->addAttributeToSelect('skuv');
        $collection->addAttributeToSelect('campaign_regular_id');
        $collection->addAttributeToSelect('price');

        $collection->addAttributeToSelect(Zolago_Catalog_Model_Product::ZOLAGO_CATALOG_MSRP_CODE);
        $collection->addAttributeToSelect(Zolago_Catalog_Model_Product::ZOLAGO_CATALOG_CONVERTER_MSRP_TYPE_CODE);

        $collection->addAttributeToSelect("udropship_vendor");
        $collection->addAttributeToFilter('type_id', Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE);
        $collection->addFieldToFilter('entity_id', array('in' => $parentIds));
        //$collection->addFieldToFilter('product_flag', array('null' => true));

        $parentProductIds = $collection->getAllIds();

        $parentProductIds = array_combine($parentProductIds, $parentProductIds);

        if($collection->getSize() <= 0){
            return $productsIdsPullToSolr; //Nothing to update
        }

        //Do not update campaign products
        foreach ($collection as $_product) {
            if ((int)$_product->getCampaignRegularId() > 0)
                unset($parentProductIds[$_product->getId()]);
        }
        $parentProductIds = array_values($parentProductIds);

        if(empty($parentProductIds)){
            return $productsIdsPullToSolr; //Nothing to update
        }
        $productsIdsPullToSolr = $parentProductIds;

        //2. Find out products, which msrp should be recovered
        $idsToSetMSRP = array();
        foreach ($collection as $_product) {
            //if Converter Msrp Type = From file
            if ($_product->getData(Zolago_Catalog_Model_Product::ZOLAGO_CATALOG_CONVERTER_MSRP_TYPE_CODE) == Zolago_Catalog_Model_Product_Source_Convertermsrptype::FLAG_AUTO) {
                $idsToSetMSRP[] = $_product->getId();
            }
        }

        //3. Prepare products per website array before recover
        $recoverOptionsProducts = array();
        $recoverMSRP = array();
        $websites = Mage::app()->getWebsites();

        foreach ($websites as $website) {
            if (!isset($recoverOptionsProducts[$website->getId()]))
                $recoverOptionsProducts[$website->getId()] = array();

            if (!isset($recoverMSRP[$website->getId()]))
                $recoverMSRP[$website->getId()] = array();

            $recoverOptionsProducts[$website->getId()] = $parentProductIds;
            $recoverMSRP[$website->getId()] = $parentProductIds;
        }


        //4. Recover options for configurable products
        if (!empty($recoverOptionsProducts)) {
            //recover options
            /* @var $configurableRModel Zolago_Catalog_Model_Resource_Product_Configurable */
            $configurableRModel = Mage::getResourceModel('zolagocatalog/product_configurable');
            $configurableRModel->recoverProductPriceAndOptionsBasedOnSimples($recoverOptionsProducts);
            $configurableRModel->recoverProductMSRPBasedOnSimples($recoverMSRP);
        }

        return $productsIdsPullToSolr;
    }

}