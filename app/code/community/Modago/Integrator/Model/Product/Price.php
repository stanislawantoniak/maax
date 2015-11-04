<?php

/**
 * Class Modago_Integrator_Model_Product_Price
 */
class Modago_Integrator_Model_Product_Price extends Mage_Core_Model_Abstract
{
    protected $_integrationStore;

    const MODAGO_INTEGRATOR_ORIGINAL_PRICE = "A";
    const MODAGO_INTEGRATOR_SPECIAL_PRICE = "B";

    /**
     * Modago_Integrator_Model_Product_Price constructor.
     * @param $_integrationStore
     */
    public function __construct()
    {
        $this->_integrationStore = $this->getIntegrationStore();
    }


    protected function getIntegrationStore()
    {
        /** @var Modago_Integrator_Helper_Data $helper */
        $helper = Mage::helper('modagointegrator');
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
        $result = $readConnection->fetchAll($query);


        $parentChildRelation = array();
        if (!empty($result)) {
            foreach ($result as $resultItem) {
                $parentChildRelation[$resultItem["parent_id"]][$resultItem["child_id"]] = $resultItem["sku"];
            }
        }

        $collection = Mage::getModel("catalog/product")->getCollection();
        $collection->setStore($this->_integrationStore);
        $collection->addAttributeToSelect("price");
        $collection->addAttributeToSelect("special_price");
        $collection->addAttributeToFilter('type_id', Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE);

        $prices = array();
        $specialPrices = array();
        foreach ($collection as $collectionItem) {
            $prices[$collectionItem->getId()] = $collectionItem->getPrice();
            $specialPrices[$collectionItem->getId()] = $collectionItem->getSpecialPrice();
        }


        foreach ($parentChildRelation as $parentId => $children) {

            foreach ($children as $childId => $childSku) {
                if (isset($prices[$parentId])) {
                    $res[self::MODAGO_INTEGRATOR_ORIGINAL_PRICE][] = array("sku" => $childSku, "price" => $prices[$parentId]);
                }
                if (isset($specialPrices[$parentId])) {
                    $res[self::MODAGO_INTEGRATOR_SPECIAL_PRICE][] = array("sku" => $childSku, "price" => $specialPrices[$parentId]);
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
        $collection = Mage::getModel("catalog/product")->getCollection();
        $collection->setStore($this->_integrationStore);
        $collection->addAttributeToSelect("price");
        $collection->addAttributeToSelect("special_price");
        $collection->addAttributeToFilter('type_id', Mage_Catalog_Model_Product_Type::TYPE_SIMPLE);
        $collection->addAttributeToFilter('visibility', array("neq" => Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE));
        foreach ($collection as $collectionItem) {
            $res[self::MODAGO_INTEGRATOR_ORIGINAL_PRICE][] = array("sku" => $collectionItem->getSku(), "price" => $collectionItem->getPrice());
            $res[self::MODAGO_INTEGRATOR_SPECIAL_PRICE][] = array("sku" => $collectionItem->getSku(), "price" => $collectionItem->getSpecialPrice());
        }
        return $res;
    }

}
