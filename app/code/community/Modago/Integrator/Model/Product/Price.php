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


        if (empty($result))
            return $res;

        $parentChildRelation = array();
        foreach ($result as $resultItem) {
            //if product has more then one parent, then use first order by SKU ASC
            if (!isset($parentChildRelation[$resultItem["parent_id"]][$resultItem["child_id"]])) {
                $parentChildRelation[$resultItem["parent_id"]][$resultItem["child_id"]] = $resultItem["sku"];
            }
        }

        if (empty($parentChildRelation))
            return $res;

        $this->_getHelper()->saveOldStore();
        $collection = Mage::getResourceModel('catalog/product_collection');
        $collection->setStore($this->_integrationStore);
        $collection->addFinalPrice();
        $collection->addAttributeToFilter('type_id', Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE);
        $collection->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);

        $prices = array();
        $finalPrices = array();
        foreach ($collection as $collectionItem) {
            $parentProductId = $collectionItem->getId();
            $price = $collectionItem->getPrice();
            if (!empty($price)) {
                $prices[$parentProductId] = $price;
            }
            $finalPrice = $collectionItem->getFinalPrice();
            if (!empty($finalPrice)) {
                $finalPrices[$parentProductId] = $finalPrice;
            }
            unset($price, $finalPrice);
        }
        unset($parentProductId);


        foreach ($parentChildRelation as $parentId => $children) {
            foreach ($children as $childId => $childSku) {
                if (isset($finalPrices[$parentId])) {
                    $res[self::MODAGO_INTEGRATOR_FINAL_PRICE][$childSku] = array("sku" => $childSku, "price" => $finalPrices[$parentId]);
                }
                if (isset($prices[$parentId])) {
                    $res[self::MODAGO_INTEGRATOR_ORIGINAL_PRICE][$childSku] = array("sku" => $childSku, "price" => $prices[$parentId]);
                    $res[self::MODAGO_INTEGRATOR_PRICE_SALE_BEFORE][$childSku] = array("sku" => $childSku, "price" => $prices[$parentId]);
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
            //do not override price if already got from configurable
            if (!isset($res[self::MODAGO_INTEGRATOR_FINAL_PRICE][$sku])) {
                $finalPrice = $collectionItem->getFinalPrice();
                if (!empty($finalPrice)) {
                    $res[self::MODAGO_INTEGRATOR_FINAL_PRICE][$sku] = array("sku" => $sku, "price" => $finalPrice);
                }
            }
            if (!isset($res[self::MODAGO_INTEGRATOR_ORIGINAL_PRICE][$sku])) {
                $price = $collectionItem->getPrice();
                if (!empty($price)) {
                    $res[self::MODAGO_INTEGRATOR_ORIGINAL_PRICE][$sku] = array("sku" => $sku, "price" => $price);
                    $res[self::MODAGO_INTEGRATOR_PRICE_SALE_BEFORE][$sku] = array("sku" => $sku, "price" => $price);
                }
            }

        }
        $this->_getHelper()->restoreOldStore();

        return $res;
    }

}
