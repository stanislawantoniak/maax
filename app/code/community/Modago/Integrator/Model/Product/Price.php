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
    public function appendOriginalPricesList($res)
    {
        /* @var $r Modago_Integrator_Model_Resource_Product_Price */
        $r = Mage::getModel("modagointegrator/resource_product_price");
        $out = $r->getOptions(self::MODAGO_INTEGRATOR_STORE);

        foreach ($out as $parent) {
            if(isset($parent["children"])){
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
    public function appendSpecialPricesList($res)
    {
        $collection = Mage::getModel("catalog/product")->getCollection();
        $collection->setStore(self::MODAGO_INTEGRATOR_STORE);
        $collection->addAttributeToSelect("price");
        $collection->addAttributeToSelect("special_price");
        $collection->addAttributeToFilter('type_id', Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE);


        foreach ($collection as $collectionItem) {
            $res[self::MODAGO_INTEGRATOR_SPECIAL_PRICE][] = array("sku" => $collectionItem->getSku(), "price" => $collectionItem->getSpecialPrice());
        }

        return $res;
    }

}
