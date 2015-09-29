<?php

class Zolago_Catalog_Model_Product_Type_Configurable extends Mage_Catalog_Model_Product_Type_Configurable
{

    /**
     * Light version of Mage_Catalog_Model_Product_Type_Configurable::getUsedProducts
     * for multiple products
     * @param $attributeId
     * @param $productIds
     */
    public function getUsedProductsByAttribute($attributeId, $productIds)
    {
        $usedProducts = array();
        $collection = Mage::getResourceModel('zolagocatalog/product_collection');


        $collection->getSelect()
            ->joinInner(
                array("link_table" => 'catalog_product_super_link'),
                "e.entity_id = link_table.product_id",
                array("link_table.parent_id")
            )->joinInner(
                array("at_size" => 'catalog_product_entity_int'),
                "e.entity_id = at_size.entity_id",
                array("size" => "at_size.value")
            )
            ->joinInner(
                array("sku_vendor" => 'catalog_product_entity_varchar'),
                "e.entity_id = sku_vendor.entity_id",
                array("skuv" => "sku_vendor.value")
            )
            ->where("at_size.attribute_id=?", $attributeId)
            ->where("sku_vendor.attribute_id=?", 316)
            ->where("at_size.store_id=?", 0)
            ->where("link_table.parent_id IN(?)", $productIds)
            ->where("(`e`.`required_options` != '1') OR (`e`.`required_options` IS NULL)")
            ->where("at_size.value IS NOT NULL");

        foreach ($collection as $product) {
            $usedProducts[$product->getParentId()][$product->getId()] = array(
                "sku" => $product->getSku(),
                "skuv" => $product->getSkuv(),
                "size" => $product->getSize(),
            );
        }

        return $usedProducts;

    }

}