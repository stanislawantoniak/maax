<?php

class Zolago_Catalog_Model_Product_Type_Configurable extends Mage_Catalog_Model_Product_Type_Configurable
{

    /**
     * Light version of Mage_Catalog_Model_Product_Type_Configurable::getUsedProducts
     * for multiple products
     *
     * @param $productIds
     * @return array
     */
    public function getUsedProductsByAttribute($productIds)
    {

//        $usedProducts = array(
//            'parent_id1' => array(
//                'child_1' => array("id" => 'child_1_id', "sku" => 'child_1_sku', "skuv" => 'child_1_skuv', "size" => 'child_1_size', "price" => 'child_1_price'),
//                'child_2' => array("id" => 'child_2_id', "sku" => 'child_2_sku', "skuv" => 'child_2_skuv', "size" => 'child_2_size', "price" => 'child_2_price'),
//
//            ),
//            'parent_id2' => array(
//            ...
//
//        )
//    );
        $usedProducts = array();
        $collection = Mage::getResourceModel('zolagocatalog/product_collection');

        $collection->joinAttribute(
            'status',
            'catalog_product/status',
            'entity_id',
            null,
            'inner',
            Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID
        );
        
        $attributeSize = Mage::getResourceModel('catalog/product')
            ->getAttribute('size');
        $attributeSizeId = $attributeSize->getAttributeId();


        $attributeVendorSku = Mage::getResourceModel('catalog/product')
            ->getAttribute('skuv');
        $attributeVendorSkuId = $attributeVendorSku->getAttributeId();
        $collection->getSelect()
            ->columns('concat(e.entity_id,"_",link_table.parent_id) as unique_id')
            ->joinInner(
                array("link_table" => 'catalog_product_super_link'),
                "e.entity_id = link_table.product_id",
                array("link_table.parent_id")
            )
            ->joinInner(
                array("at_size" => 'catalog_product_entity_int'),
                "e.entity_id = at_size.entity_id",
                array("size" => "at_size.value")
            )
            ->joinInner(
                array("at_sku_vendor" => 'catalog_product_entity_varchar'),
                "e.entity_id = at_sku_vendor.entity_id",
                array("skuv" => "at_sku_vendor.value")
            )
            ->where("at_size.attribute_id=?", $attributeSizeId)
            ->where("at_sku_vendor.attribute_id=?", $attributeVendorSkuId)

            ->where("at_size.store_id=?", Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID)
            ->where("at_sku_vendor.store_id=?", Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID)

            ->where("link_table.parent_id IN(?)", $productIds)
            ->where("(`e`.`required_options` != '1') OR (`e`.`required_options` IS NULL)")

            ->where("at_size.value IS NOT NULL")
            ->where("at_status.value <> ?",Zolago_DropshipVendorProduct_Model_ProductStatus::STATUS_INVALID)
            
            ;
        $collection->setRowIdFieldName('unique_id');

        foreach ($collection as $product) {
            $usedProducts[$product->getParentId()][$product->getId()] = array(
                "id" => $product->getId(),      //Simple product id
                "sku" => $product->getSku(),    //Simple product sku
                "skuv" => $product->getSkuv(),  //Simple product skuv
                "size" => $product->getSize(),  //Simple product size
                "price" => $product->getPrice() //Simple product price
            );
        }

        return $usedProducts;

    }


    /**
     * Get relation size-price for store
     * @param $storeId
     * @param $productIds
     * @return array
     */
    public function getUsedSizePriceRelations($storeId, $productIds)
    {

        $origStore = Mage::app()->getStore();

        $store = Mage::getModel("core/store")->load($storeId);
        Mage::app()->setCurrentStore($store);

        $sizePriceRelations = array();
        $collection = Mage::getResourceModel('zolagocatalog/product_collection');
        $collection->joinAttribute(
            'price',
            'catalog_product/price',
            'entity_id',
            null,
            'left',
            $storeId
        );
        $collection->joinAttribute(
            'size',
            'catalog_product/size',
            'entity_id',
            null,
            'left',
            $storeId
        );
        $collection->joinAttribute(
            'skuv',
            'catalog_product/skuv',
            'entity_id',
            null,
            'inner',
            Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID
        );
        $collection->joinAttribute(
            'status',
            'catalog_product/status',
            'entity_id',
            null,
            'inner',
            Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID
        );
        $collection->getSelect()
            ->columns('concat(e.entity_id,"_",link_table.parent_id) as unique_id')
            ->joinInner(
                array("link_table" => 'catalog_product_super_link'),
                "e.entity_id = link_table.product_id",
                array("link_table.parent_id")
            )
            ->joinInner(
                array("stock" => 'cataloginventory_stock_item'),
                "e.entity_id = stock.product_id AND stock_id = 1",
                array("stock.is_in_stock")
            )                
            ->where("link_table.parent_id IN(?)", $productIds)
            ->where("(`e`.`required_options` != '1') OR (`e`.`required_options` IS NULL)")
            ->where("at_size.value IS NOT NULL")
            ->where("at_status.value <> ?",Zolago_DropshipVendorProduct_Model_ProductStatus::STATUS_INVALID)
        ;
        $collection->setRowIdFieldName('unique_id');        

        foreach ($collection as $product) {
            $sizePriceRelations[$product->getParentId()][$product->getId()] = array(
                "id" => $product->getId(),      //Simple product id
                "sku" => $product->getSku(),    //Simple product sku
                "skuv" => $product->getSkuv(),  //Simple product skuv
                "size" => $product->getSize(),  //Simple product size
                "price" => $product->getPrice(), //Simple product price
                "is_in_stock" => $product->getIsInStock()
            );
        }
        Mage::app()->setCurrentStore($origStore);

        return $sizePriceRelations;

    }


    /**
     * Get children MSRP for store
     * @param $store
     * @param $productIds
     * @return array
     */
    public function getMSRPForChildren($store, $productIds)
    {

        $mSRPForChildren = array();
        $collection = Mage::getResourceModel('zolagocatalog/product_collection');


        $attributeMSRP = Mage::getResourceModel('catalog/product')
            ->getAttribute('msrp');
        $attributeMSRPId = $attributeMSRP->getAttributeId();


        $collection->getSelect()
            ->columns('concat(e.entity_id,"_",link_table.parent_id) as unique_id')
            ->joinInner(
                array("link_table" => 'catalog_product_super_link'),
                "e.entity_id = link_table.product_id",
                array("link_table.parent_id")
            )
            ->joinInner(
                array("at_msrp" => 'catalog_product_entity_decimal'),
                "e.entity_id = at_msrp.entity_id",
                array("msrp" => "at_msrp.value")
            )
            ->joinInner(
                array("stock" => 'cataloginventory_stock_item'),
                "e.entity_id = stock.product_id AND stock_id = 1",
                array("stock.is_in_stock")
            )                

            ->where("at_msrp.attribute_id=?", $attributeMSRPId)
            ->where("at_msrp.store_id=?", $store)

            ->where("link_table.parent_id IN(?)", $productIds);
        $collection->setRowIdFieldName('unique_id');        

        foreach ($collection as $product) {
            $mSRPForChildren[$product->getParentId()][$product->getId()] = array(
                "id" => $product->getId(),      //Simple product id
                "sku" => $product->getSku(),    //Simple product sku
                "msrp" => $product->getMsrp(), //Simple product msrp
                "is_in_stock" => $product->getIsInStock(),
            );
        }

        return $mSRPForChildren;

    }


}