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
        
        $this->_getHelper()->saveOldStore();
        $collection = Mage::getResourceModel('catalog/product_collection');
        $collection->setStore($this->_integrationStore);
        $collection->addFinalPrice();
        $collection->addAttributeToFilter('type_id', Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE);
        $collection->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
        
        //Collect prices for used children
        $collectionUsedSimple = Mage::getResourceModel('catalog/product_collection');
        $collectionUsedSimple->setStore($this->_integrationStore);
        $collectionUsedSimple->addFinalPrice();
        $collectionUsedSimple->addAttributeToFilter('type_id', Mage_Catalog_Model_Product_Type::TYPE_SIMPLE);
        $collectionUsedSimple->addAttributeToFilter('entity_id', array('in' => $childrenIds));
        
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
                        "price" => $finalPrices[$parentId],
                        "type" => self::MODAGO_INTEGRATOR_FINAL_PRICE
                    );
                }

                if (isset($finalPricesUsedSimple[$childId])) {
                    //Append "C" price
                    $res[self::MODAGO_INTEGRATOR_FINAL_PRICE_SIMPLE][$childSku] = array(
                        "sku" => $childSku,
                        "price" => $finalPricesUsedSimple[$childId],
                        "type" => self::MODAGO_INTEGRATOR_FINAL_PRICE_SIMPLE
                    );
                }


                if (isset($prices[$parentId])) {
                    //Append "B" price
                    $res[self::MODAGO_INTEGRATOR_ORIGINAL_PRICE][$childSku] = array(
                        "sku" => $childSku,
                        "price" => $prices[$parentId],
                        "type" => self::MODAGO_INTEGRATOR_ORIGINAL_PRICE
                    );

                    //Append "salePriceBefore" price
                    $res[self::MODAGO_INTEGRATOR_PRICE_SALE_BEFORE][$childSku] = array(
                        "sku" => $childSku,
                        "price" => $prices[$parentId],
                        "type" => self::MODAGO_INTEGRATOR_PRICE_SALE_BEFORE
                    );
                }

                if (isset($pricesUsedSimple[$childId])) {
                    //Append "Z" price
                    $res[self::MODAGO_INTEGRATOR_ORIGINAL_PRICE_SIMPLE][$childSku] = array(
                        "sku" => $childSku,
                        "price" => $pricesUsedSimple[$childId],
                        "type" => self::MODAGO_INTEGRATOR_ORIGINAL_PRICE_SIMPLE
                    );
                }
            }
        }
        
        $this->_getHelper()->restoreOldStore();

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
                    "price" => $finalPrice,
                    "type" => self::MODAGO_INTEGRATOR_FINAL_PRICE
                );
            }

            //2. Append "B" prices
            if (!isset($res[self::MODAGO_INTEGRATOR_ORIGINAL_PRICE][$sku]) && !empty($price)) {
                $res[self::MODAGO_INTEGRATOR_ORIGINAL_PRICE][$sku] = array(
                    "sku" => $sku,
                    "price" => $price,
                    "type" => self::MODAGO_INTEGRATOR_ORIGINAL_PRICE
                );
            }

            //3. Append "salePriceBefore" prices
            if (!isset($res[self::MODAGO_INTEGRATOR_PRICE_SALE_BEFORE][$sku]) && !empty( $price)) {
                $res[self::MODAGO_INTEGRATOR_PRICE_SALE_BEFORE][$sku] = array(
                    "sku" => $sku,
                    "price" => $price,
                    "type" => self::MODAGO_INTEGRATOR_PRICE_SALE_BEFORE
                );
            }

            //4. Append "C" prices
            if (!isset($res[self::MODAGO_INTEGRATOR_FINAL_PRICE_SIMPLE][$sku]) && !empty($finalPrice)) {
                $res[self::MODAGO_INTEGRATOR_FINAL_PRICE_SIMPLE][$sku] = array(
                    "sku" => $sku,
                    "price" => $finalPrice,
                    "type" => self::MODAGO_INTEGRATOR_FINAL_PRICE_SIMPLE
                );
            }

            //5. Append "Z" prices
            if (!isset($res[self::MODAGO_INTEGRATOR_ORIGINAL_PRICE_SIMPLE][$sku]) && !empty($price)) {
                $res[self::MODAGO_INTEGRATOR_ORIGINAL_PRICE_SIMPLE][$sku] = array(
                    "sku" => $sku,
                    "price" => $price,
                    "type" => self::MODAGO_INTEGRATOR_ORIGINAL_PRICE_SIMPLE
                );
            }
            unset($sku, $finalPrice, $price);
        }
        
        $this->_getHelper()->restoreOldStore();

        return $res;
    }

}
