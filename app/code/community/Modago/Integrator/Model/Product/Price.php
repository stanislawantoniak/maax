<?php

/**
 * Class Modago_Integrator_Model_Product_Price
 */
class Modago_Integrator_Model_Product_Price extends Mage_Core_Model_Abstract
{
    const MODAGO_INTEGRATOR_STORE = 1;

    const MODAGO_INTEGRATOR_ORIGINAL_PRICE = "A";
    const MODAGO_INTEGRATOR_SPECIAL_PRICE = "B";

    /**
     * @param $res
     * @return mixed
     */
    public function appendOriginalPricesConfigurableList($res)
    {
        //1. Configurable
        /* @var $r Modago_Integrator_Model_Resource_Product_Price */
        $r = Mage::getModel("modagointegrator/resource_product_price");
        $out = $r->getOptions(self::MODAGO_INTEGRATOR_STORE);

        foreach ($out as $parent) {
            if (isset($parent["children"])) {
                foreach ($parent["children"] as $children) {
                    $res[self::MODAGO_INTEGRATOR_ORIGINAL_PRICE][] = array("sku" => $children["sku"], "price" => $children["price"]);
                }
            }
        }

        return $res;
    }

    /**
     * @param $res
     * @return mixed
     */
    public function appendSpecialPricesConfigurableList($res)
    {
        //1. Configurable
        /** @var Mage_Core_Model_Resource $resource */
        $resource = Mage::getSingleton('core/resource');
        $productRelationTable = $resource->getTableName('catalog_product_relation');
        $productTable = $resource->getTableName('catalog_product_entity');

        $readConnection = $resource->getConnection('core_read');
        $query = "SELECT child_id,parent_id,sku FROM {$productRelationTable} JOIN {$productTable} ON {$productRelationTable}.child_id={$productTable}.entity_id";
        $result = $readConnection->fetchAll($query);


        $parentChildRelation = array();
        if (!empty($result)) {
            foreach ($result as $resultItem) {
                $parentChildRelation[$resultItem["parent_id"]][$resultItem["child_id"]] = $resultItem["sku"];
            }
        }

        $collection = Mage::getModel("catalog/product")->getCollection();
        $collection->setStore(self::MODAGO_INTEGRATOR_STORE);
        $collection->addAttributeToSelect("special_price");
        $collection->addAttributeToFilter('type_id', Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE);

        $prices = array();
        foreach ($collection as $collectionItem) {
            $prices[$collectionItem->getId()] = $collectionItem->getSpecialPrice();
        }

        foreach ($prices as $parentId => $specialPrice) {
            if (isset($parentChildRelation[$parentId])) {
                foreach ($parentChildRelation[$parentId] as $childSku) {
                    $res[self::MODAGO_INTEGRATOR_SPECIAL_PRICE][] = array("sku" => $childSku, "price" => $specialPrice);
                }
            }
        }


        return $res;
    }


    /**
     * @param $res
     */
    public function appendPricesSimpleList($res)
    {
        $collection = Mage::getModel("catalog/product")->getCollection();
        $collection->setStore(self::MODAGO_INTEGRATOR_STORE);
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
