<?php

/**
 * Class Modago_Integrator_Model_Generator_Price_Product
 */
class Modago_Integrator_Model_Generator_Price_Product extends Mage_Catalog_Model_Product
{


    /**
     * @param $res
     * @param $type
     * @return mixed
     */
    public function appendOriginalPricesList($res, $type)
    {
        $collection = Mage::getModel("catalog/product")->getCollection();
        $collection->setStore(1);
        $collection->addAttributeToSelect("price");
        //$collection->setPageSize(10);
        //$collection->addAttributeToFilter('visibility', array('neq' => Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE));
        //$collection->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);


        foreach ($collection as $collectionItem) {
            $res[$type][] = array("sku" => $collectionItem->getSku(), "price" => $collectionItem->getPrice());
        }

        return $res;
    }

    /**
     * @param $res
     * @param $type
     * @return mixed
     */
    public function appendSpecialPricesList($res, $type)
    {
        $collection = Mage::getModel("catalog/product")->getCollection();
        $collection->setStore(1);
        $collection->addAttributeToSelect("price");
        $collection->addAttributeToSelect("special_price");
        //$collection->setPageSize(10);
        //$collection->addAttributeToFilter('visibility', array('neq' => Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE));
        //$collection->addAttributeToFilter('status', Mage_Catalog_Model_Product_Status::STATUS_ENABLED);
        $collection->addAttributeToFilter('type_id', Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE);


        foreach ($collection as $collectionItem) {
            $res[$type][] = array("sku" => $collectionItem->getSku(), "price" => $collectionItem->getSpecialPrice());
        }

        return $res;
    }

}
