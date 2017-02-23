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
                'catalog_product_super_link AS catalog_product_super_link',
                array(
                    'configurable_product' => 'catalog_product_super_link.parent_id',
                    'simple_product' => 'catalog_product_super_link.product_id'
                )
            )
            ->where("catalog_product_super_link.product_id IN(?)", $listUpdatedProducts);

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


    public static $_vendorAutomaticStrikeoutPricePercent;
    public static $_vendorAutomaticFlagPricePercent;



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
        $setMsrpEmpty = array();
        
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

            if (isset(self::$_vendorAutomaticFlagPricePercent[$_product["udropship_vendor"]])) {
                $percentFlag = self::$_vendorAutomaticFlagPricePercent[$_product["udropship_vendor"]];
            } else {
                $_vendor = Mage::getModel("udropship/vendor")->load($_product["udropship_vendor"]);

                $percentFlag = Mage::helper("zolagocatalog")->getAutomaticFlagPricePercent($_vendor);
                self::$_vendorAutomaticFlagPricePercent[$_product["udropship_vendor"]] = $percentFlag;

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
                ((float)$_product["msrp"] - (float)$_product["price"] < ((float)$_product["msrp"] * (float)($percent / 100))
                )
            ) {
                $setMsrpEmpty[$storeId][] = $_product["entity_id"];                
            }
            if (
                (float)$_product["price"] > 0
                &&
                (float)$_product["msrp"]
                &&
                ((float)$_product["msrp"] - (float)$_product["price"] >= ((float)$_product["msrp"] * (float)($percentFlag / 100))
                )
            ) {
                $isNew = (bool)$_product["is_new"];
                if (!$isNew) {
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
        if (!empty($setMsrpEmpty)) {
            foreach ($setMsrpEmpty as $storeId => $productIds) {
                $aM->updateAttributesPure($productIds,
                    array("msrp" => 0),
                    $storeId
                );
            }
        }
    }


    /**
     *
     *
     * jako sprzedawca chcę żeby cena, cena przekreślona i flaga była ustawiana automatycznie
     * 1. przypadek dotyczy tylko sytuacji gdy produkt nie jest w kampanii promo/sale
     * 2. jeśli ustawiona cena jest mniejsza co najmniej o procent z konfiguracji (np. 10%) to ustawiamy cena przekreślona = cena srp z produktu, flaga = wyprzedaż
     * 3. w zasadzie to powinno ustawić poprawnie cenę przekreśloną i flagę
     *
     * @param $ids
     * @throws Mage_Core_Exception
     */
    public function updateSalePromoFlag($ids)
    {
        $websites = Mage::app()->getWebsites();
        foreach ($websites as $website) {

            $updateIds = array();
            $defaultStore = Mage::app()
                ->getWebsite($website->getId())
                ->getDefaultGroup()
                ->getDefaultStore();

            $defaultStoreId = $defaultStore->getId();

            $productsInCampaign = $this->productsInCampaign($defaultStore, $ids);

            foreach ($ids as $parentProductId) {
                if (!in_array($parentProductId, $productsInCampaign)) {
                    $updateIds[] = $parentProductId;
                }
            }
            if (empty($updateIds))
                continue;

            //Set product_flag SALE/PROMO for products not in campaign ONLY!!!
            $this->updateSalePromoFlagForStore($defaultStoreId, $updateIds);
        }
    }


    /**
     * Get collection of configurable products NOT IN SALE or PROMOTION
     *
     * @param $store
     * @param $ids
     * @return array
     */
    public function productsInCampaign($store, $ids){
        //1.1. Get collection of configurable products NOT IN SALE or PROMOTION
        /* @var $collection Mage_Catalog_Model_Resource_Product_Collection */
        $collection = Mage::getResourceModel('zolagocatalog/product_collection');
        $collection->setStore($store);
        $collection->addAttributeToSelect('skuv');
        $collection->addAttributeToSelect('campaign_regular_id', 'left');
        //$collection->addAttributeToSelect("udropship_vendor");
        $collection->addFieldToFilter('entity_id', array('in' => $ids));
        $collection->getSelect()->where("at_campaign_regular_id.value IS NOT NULL");

        $productsInCampaign = $collection->getAllIds();

        return $productsInCampaign;
    }

    /**
     * @param $store
     * @param $ids
     * @return array
     */
    public function productsMSRPManual($store, $ids)
    {
        /* @var $collection Mage_Catalog_Model_Resource_Product_Collection */
        $collection = Mage::getResourceModel('zolagocatalog/product_collection');
        $collection->setStore($store);
        $collection->addAttributeToSelect('skuv');
        $collection->addAttributeToSelect(Zolago_Catalog_Model_Product::ZOLAGO_CATALOG_CONVERTER_MSRP_TYPE_CODE, 'left');
        //$collection->addAttributeToSelect("udropship_vendor");
        $collection->addFieldToFilter('entity_id', array('in' => $ids));
        $collection->getSelect()->where("at_converter_msrp_type.value=?", Zolago_Catalog_Model_Product_Source_Convertermsrptype::FLAG_MANUAL);

        $productsMSRPManual = $collection->getAllIds();

        return $productsMSRPManual;
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

        $parentIds = array();
        /* @var $configResourceModel   Zolago_Catalog_Model_Resource_Product_Configurable */
        $configResourceModel = Mage::getResourceModel('zolagocatalog/product_configurable');
        foreach ($recoverOptionsProducts as $websiteId => $parentIdsPerWebsite) {
            $parentIds = array_merge($parentIds,$parentIdsPerWebsite);
        }
        $superAttributes = $configResourceModel->getSuperAttributes($parentIds);

        /* @var $configModel  Zolago_Catalog_Model_Product_Type_Configurable */
        $configModel = Mage::getModel('zolagocatalog/product_type_configurable');

        $dataToUpdate = array();

        //1. Collect data before update
        unset($parentIds);
        foreach ($recoverOptionsProducts as $websiteId => $parentIds) {
            if (empty($parentIds)) {
                //Nothing to recover
                continue;
            }
            if(!isset($dataToUpdate[$websiteId])){
                $dataToUpdate[$websiteId] = array();
            }

            try {
                $defaultStore = Mage::app()
                    ->getWebsite($websiteId)
                    ->getDefaultGroup()
                    ->getDefaultStore();

                $defaultStoreId = $defaultStore->getId();

                $childProductsByAttribute = $configModel->getUsedSizePriceRelations($defaultStoreId, $parentIds);
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

        unset($websiteId, $defaultStoreId);
        $options = array();
        if (!empty($dataToUpdate)) {
            /* @var $aM Zolago_Catalog_Model_Product_Action */
            $aM = Mage::getSingleton('catalog/product_action');

            foreach ($dataToUpdate as $websiteId => $data) {
                $price = $data["price"];
                $options = array_merge($options, $data["options"]);

                $defaultWebsiteStoreId = Mage::app()
                    ->getWebsite($websiteId)
                    ->getDefaultGroup()
                    ->getDefaultStoreId();

                foreach ($price as $value => $productIds) {
                    $aM->updateAttributesPure($productIds, array("price" => (string)$value), $defaultWebsiteStoreId);
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
        if (empty($recoverMSRP))
            return;

        $parentIds = array();

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
                $defaultStoreId = Mage::app()
                    ->getWebsite($websiteId)
                    ->getDefaultGroup()
                    ->getDefaultStoreId();

                $childProductsMSRP = $configModel->getMSRPForChildren($defaultStoreId, $parentIds, true);

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
        unset($websiteId, $defaultStoreId);
        $options = array();

        $ids = array();
        if (!empty($dataToUpdate)) {
            /* @var $aM Zolago_Catalog_Model_Product_Action */
            $aM = Mage::getSingleton('catalog/product_action');

            foreach ($dataToUpdate as $websiteId => $data) {
                $defaultStoreId = Mage::app()
                    ->getWebsite($websiteId)
                    ->getDefaultGroup()
                    ->getDefaultStoreId();

                foreach ($data as $value => $productIds) {
                    $aM->updateAttributesPure($productIds, array("msrp" => (string)$value), $defaultStoreId);
                    $ids = array_merge($ids, $productIds);
                }
            }

            //set null to attribute for default store id (required for good quote calculation)
            $aM->updateAttributesPure(
                $ids,
                array(
                    'product_flag' => null,
                    'msrp' => null
                ),
                Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID
            );
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
        $productMinAllPrice = array();
        foreach ($priceSizeRelation as $item) {
            $productMinAllPrice[] = $item['price'];
            if ($item['is_in_stock']) {
                $productMinPrice[] = $item['price'];
            }
        }
        if (empty($productMinPrice)) {
            $productMinPrice = $productMinAllPrice;
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


        //1. Collect price
        $productMinPrice = array();
        $productMinAllPrice = array();
        foreach ($msrpRelation as $item) {            
            if ($item['is_in_stock']) {
                $productMinPrice[] = $item['msrp'];
            } 
            $productMinAllPrice[] = $item['msrp'];
        }
        if (empty($productMinPrice)) {
            $productMinPrice = $productMinAllPrice;
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
            Mage::throwException("Error removeUpdatedRows");
            throw $e;
        }

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
    public function updateConfigurableProductsValues($parentIds)
    {
        $productsIdsPullToSolr = array();

        $parentProductIds = $parentIds;

        if (empty($parentProductIds)) {
            return $productsIdsPullToSolr; //Nothing to update
        }
        $productsIdsPullToSolr = $parentProductIds;

        //3. Prepare products per website array before recover
        $recoverOptionsProducts = array();
        $recoverMSRP = array();
        $websites = Mage::app()->getWebsites();

        foreach ($websites as $website) {
            if (!isset($recoverOptionsProducts[$website->getId()]))
                $recoverOptionsProducts[$website->getId()] = array();

            if (!isset($recoverMSRP[$website->getId()]))
                $recoverMSRP[$website->getId()] = array();

            $defaultStore = Mage::app()
                ->getWebsite($website->getId())
                ->getDefaultGroup()
                ->getDefaultStore();

            $productsInCampaign = $this->productsInCampaign($defaultStore, $parentProductIds);
            $productsMSRPManual = $this->productsMSRPManual($defaultStore, $parentProductIds);

            foreach ($parentProductIds as $parentProductId) {
                if (!in_array($parentProductId, $productsInCampaign)) {
                    $recoverOptionsProducts[$website->getId()][] = $parentProductId;
                }
                if (!in_array($parentProductId, $productsMSRPManual)) {
                    $recoverMSRP[$website->getId()][] = $parentProductId;
                }
            }
        }


        if (empty($recoverOptionsProducts) || empty($recoverMSRP)) {
            return $productsIdsPullToSolr;
        }

        //4. Recover options for configurable products
        //recover options
        /* @var $configurableRModel Zolago_Catalog_Model_Resource_Product_Configurable */
        $configurableRModel = Mage::getResourceModel('zolagocatalog/product_configurable');
        $configurableRModel->recoverProductPriceAndOptionsBasedOnSimples($recoverOptionsProducts);
        $configurableRModel->recoverProductMSRPBasedOnSimples($recoverMSRP);


        return $productsIdsPullToSolr;
    }

}