<?php

/**
 * Class Modago_Integrator_Model_Product_Price
 */
class Modago_Integrator_Model_Product_Price extends Mage_Core_Model_Abstract
{
    protected $_integrationStore;

    const MODAGO_INTEGRATOR_FINAL_PRICE = "A";
    const MODAGO_INTEGRATOR_ORIGINAL_PRICE = "B";
    const MODAGO_INTEGRATOR_PRICE_SALE_BEFORE = "salePriceBefore";

    const MODAGO_INTEGRATOR_FINAL_PRICE_SIMPLE = "C";
    const MODAGO_INTEGRATOR_ORIGINAL_PRICE_SIMPLE = "Z";

    protected $_helper;
    /**
     * Modago_Integrator_Model_Product_Price constructor.
     * @param $_integrationStore
     */
    public function __construct()
    {
        $this->_integrationStore = $this->getIntegrationStore();
    }

    protected function _getHelper() {
        if (empty($this->_helper)) {
            $this->_helper = Mage::helper('modagointegrator');
        }
        return $this->_helper;
    }

    protected function getIntegrationStore()
    {
        /** @var Modago_Integrator_Helper_Data $helper */
        $helper = $this->_getHelper();
        return $helper->getIntegrationStore();
    }

    /**
     * Get product collection by store id
     * @param int $store_id
     * @return Mage_Catalog_Model_Resource_Product_Collection|Object
     */
    function getProductCollectionByStoreId($store_id, $type = null, $ids = array())
    {
        $oldStore = Mage::app()->getStore();
        Mage::app()->setCurrentStore($store_id);

        $collection = Mage::getResourceModel('catalog/product_collection');
        $collection
            ->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
        ;
        $collection->addAttributeToFilter('type_id', $type);
        if (!empty($ids)) {
            $collection->addAttributeToFilter('entity_id', $ids);
        }
        Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);

        Mage::app()->setCurrentStore($oldStore);

        return $collection;
    }
    /**
     * @param $res
     * @return mixed
     */
    public function appendPricesForConfigurable($res)
    {
        //1. Configurable
        /** @var Mage_Core_Model_Resource $resource */
        $resource = Mage::getSingleton('core/resource');
        $productRelationTable = $resource->getTableName('catalog_product_relation');
        $productTable = $resource->getTableName('catalog_product_entity');

        $readConnection = $resource->getConnection('core_read');
        $query = "SELECT child_id,parent_id,children.sku AS sku FROM {$productRelationTable}
                 JOIN {$productTable} AS children ON {$productRelationTable}.child_id=children.entity_id
                 JOIN {$productTable} AS parents ON {$productRelationTable}.parent_id=parents.entity_id
                 ORDER BY parents.sku ASC
                 ";
        try {
            $result = $readConnection->fetchAll($query);
        } catch (Modago_Integrator_Exception $e) {
            Mage::logException($e);
        }


        if (empty($result)) {
            return $res;
        }

        $parentChildRelation = array();
        
        $childrenIds = array();
        foreach ($result as $resultItem) {
            //if product has more then one parent, then use first order by SKU ASC
            if (!isset($parentChildRelation[$resultItem["parent_id"]][$resultItem["child_id"]])) {                
                $parentChildRelation[$resultItem["parent_id"]][$resultItem["child_id"]] = $resultItem["sku"];
                $childrenIds[] = $resultItem["child_id"];
            }
        }
        
        if (empty($parentChildRelation) || empty($childrenIds)) {
            return $res;
        }

        $collection = $this->getProductCollectionByStoreId($this->_integrationStore, Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE);
        $collectionUsedSimple = $this->getProductCollectionByStoreId($this->_integrationStore, Mage_Catalog_Model_Product_Type::TYPE_SIMPLE, $childrenIds);

        $pricesUsedSimple = array();
        $finalPricesUsedSimple = array();
        foreach ($collectionUsedSimple as $collectionUsedSimpleItem) {
            $childProductId = $collectionUsedSimpleItem->getId();
            $priceUsedSimple = (float)$collectionUsedSimpleItem->getPrice();
            $finalPriceUsedSimple = (float)$collectionUsedSimpleItem->getFinalPrice();
            if (!empty($priceUsedSimple)) {
                $pricesUsedSimple[$childProductId] = $priceUsedSimple;
            }
            if (!empty($finalPriceUsedSimple)) {
                $finalPricesUsedSimple[$childProductId] = $finalPriceUsedSimple;
            }
            unset($priceUsedSimple, $finalPriceUsedSimple, $childProductId);
        }
        
        //--Collect prices for used children

        
        //Collect prices for parents
        $prices = array();
        $finalPrices = array();
        foreach ($collection as $collectionItem) {
            $parentProductId = $collectionItem->getId();

            $price = (float) $collectionItem->getPrice();
            $finalPrice = (float) $collectionItem->getFinalPrice();

            if (!empty($price)) {
                $prices[$parentProductId] = $price;
            }

            if (!empty($finalPrice)) {
                $finalPrices[$parentProductId] = $finalPrice;
            }

            unset($price, $finalPrice, $parentProductId);
        }
        //--Collect prices for parents


        foreach ($parentChildRelation as $parentId => $children) {
            foreach ($children as $childId => $childSku) {
                if (isset($finalPrices[$parentId])) {
                    //Append "A" price
                    $res[self::MODAGO_INTEGRATOR_FINAL_PRICE][$childSku] = array(
                        "sku" => $childSku,
                        "price" => $finalPrices[$parentId]
                    );
                }

                if (isset($finalPricesUsedSimple[$childId])) {
                    //Append "C" price
                    $res[self::MODAGO_INTEGRATOR_FINAL_PRICE_SIMPLE][$childSku] = array(
                        "sku" => $childSku,
                        "price" => $finalPricesUsedSimple[$childId]
                    );
                }


                if (isset($prices[$parentId])) {
                    //Append "B" price
                    $res[self::MODAGO_INTEGRATOR_ORIGINAL_PRICE][$childSku] = array(
                        "sku" => $childSku,
                        "price" => $prices[$parentId]
                    );

                    //Append "salePriceBefore" price
                    $res[self::MODAGO_INTEGRATOR_PRICE_SALE_BEFORE][$childSku] = array(
                        "sku" => $childSku,
                        "price" => $prices[$parentId]
                    );
                }

                if (isset($pricesUsedSimple[$childId])) {
                    //Append "Z" price
                    $res[self::MODAGO_INTEGRATOR_ORIGINAL_PRICE_SIMPLE][$childSku] = array(
                        "sku" => $childSku,
                        "price" => $pricesUsedSimple[$childId]
                    );
                }
            }
        }
        


        return $res;
    }

    /**
     * @param $res
     */
    public function appendPricesForSimple($res)
    {
        $this->_getHelper()->saveOldStore();
        $collection = Mage::getResourceModel('catalog/product_collection');
        $collection->setStore($this->_integrationStore);
        $collection->addAttributeToFilter('type_id', Mage_Catalog_Model_Product_Type::TYPE_SIMPLE);
        $collection->addAttributeToFilter('visibility', array("neq" => Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE));
        $collection->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);

        $collection->addFinalPrice();

        foreach ($collection as $collectionItem) {
            $sku = $collectionItem->getSku();
            $finalPrice = (float)$collectionItem->getFinalPrice();
            $price = (float)$collectionItem->getPrice();

            //do not override price if already got from configurable
            //1. Append "A" prices
            if (!isset($res[self::MODAGO_INTEGRATOR_FINAL_PRICE][$sku]) && !empty($finalPrice)) {
                $res[self::MODAGO_INTEGRATOR_FINAL_PRICE][$sku] = array(
                    "sku" => $sku,
                    "price" => $finalPrice
                );
            }

            //2. Append "B" prices
            if (!isset($res[self::MODAGO_INTEGRATOR_ORIGINAL_PRICE][$sku]) && !empty($price)) {
                $res[self::MODAGO_INTEGRATOR_ORIGINAL_PRICE][$sku] = array(
                    "sku" => $sku,
                    "price" => $price
                );
            }

            //3. Append "salePriceBefore" prices
            if (!isset($res[self::MODAGO_INTEGRATOR_PRICE_SALE_BEFORE][$sku]) && !empty( $price)) {
                $res[self::MODAGO_INTEGRATOR_PRICE_SALE_BEFORE][$sku] = array(
                    "sku" => $sku,
                    "price" => $price
                );
            }

            //4. Append "C" prices
            if (!isset($res[self::MODAGO_INTEGRATOR_FINAL_PRICE_SIMPLE][$sku]) && !empty($finalPrice)) {
                $res[self::MODAGO_INTEGRATOR_FINAL_PRICE_SIMPLE][$sku] = array(
                    "sku" => $sku,
                    "price" => $finalPrice
                );
            }

            //5. Append "Z" prices
            if (!isset($res[self::MODAGO_INTEGRATOR_ORIGINAL_PRICE_SIMPLE][$sku]) && !empty($price)) {
                $res[self::MODAGO_INTEGRATOR_ORIGINAL_PRICE_SIMPLE][$sku] = array(
                    "sku" => $sku,
                    "price" => $price
                );
            }
            unset($sku, $finalPrice, $price);
        }
        
        $this->_getHelper()->restoreOldStore();

        return $res;
    }

}
