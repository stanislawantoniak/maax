<?php

class Zolago_Catalog_Model_Resource_Product extends Mage_Catalog_Model_Resource_Product {


    public function savePriceValues($insert, $ids)
    {
        $writeAdapter = $this->_getWriteAdapter();
        $writeAdapter->beginTransaction();
        try {
            $writeAdapter->insertOnDuplicate(
                $writeAdapter->getTableName('catalog_product_entity_decimal'),
                $insert, array('value')
            );

            $this->_getWriteAdapter()->commit();

            Zolago_Catalog_Helper_Configurable::queue($ids);

            //add to solr queue
            Mage::dispatchEvent(
                "catalog_converter_price_update_after",
                array(
                    "product_ids" => $ids
                )
            );

        } catch (Exception $e) {
            $this->_getWriteAdapter()->rollBack();
            $batchFile = Zolago_Catalog_Model_Api2_Restapi_Rest_Admin_V1::CONVERTER_PRICE_UPDATE_LOG;
            Mage::log(microtime() . " rollBack: savePriceValues", 0, $batchFile);

            throw $e;
        }

        return $this;
    }


    /**
     * @param $skuS
     *
     * @return array $assoc
     */
    public function getPriceMarginValues($skuS)
    {
        $assoc = array();

        if (empty($skuS)) {
            return array();
        }
        $readConnection = $this->_getReadAdapter();
        $select = $readConnection->select();
        $select->from(
            array("products" => $this->getTable("catalog/product")),
            array(
                "product_id" => "products.entity_id",
                "sku" => "products.sku"
            )
        );
        $select->join(
            array("text_attributes" => 'catalog_product_entity_text'),
            "products.entity_id=text_attributes.entity_id",
            array(
                'store' => 'text_attributes.store_id',
                'price_margin' => 'text_attributes.value'
            )
        );
        $select->join(
            array("attributes" => $this->getTable("eav/attribute")),
            "attributes.attribute_id=text_attributes.attribute_id",
            array()
        );
        $select->where(
            "products.type_id=?", Mage_Catalog_Model_Product_Type::TYPE_SIMPLE
        );
        $select->where(
            "attributes.attribute_code=?", Zolago_Catalog_Model_Product::ZOLAGO_CATALOG_PRICE_MARGIN_CODE
        );
        $select->where("products.sku IN(?)", $skuS);
        //Mage::log(microtime() . " priceMarginValues: ". $select, 0, 'converter_profilerPriceBatch.log');
        try {
            $assoc = $readConnection->fetchAll($select);
        } catch (Exception $e) {
            Mage::throwException("Error fetching price_margin values");
        }

        return $assoc;
    }

    /**
     * @param $skuS
     *
     * @return array $assoc
     */
    public function getPriceMarginValuesConfigurable($skuS)
    {
        $margins = array();

        if (empty($skuS)) {
            return array();
        }
        $entityTypeID = Mage::getModel('catalog/product')->getResource()->getTypeId();

        $readConnection = $this->_getReadAdapter();
        $select = $readConnection->select();
        $select->from(
            array("product_relation" => $this->getTable("catalog/product_relation")),
            array(
                "parent_id" => "product_relation.parent_id",
                "product_id" => "product_relation.child_id"
            )
        );
        $select->join(
            array("products" =>  $this->getTable("catalog/product")),
            "products.entity_id=product_relation.child_id",
            array(
                'products.sku'
            )
        );
        $select->join(
            array("margins" =>  'catalog_product_entity_text'),
            'product_relation.parent_id=margins.entity_id',
            array(
                'price_margin' => 'margins.value',
                'store' => 'margins.store_id'
            )
        );
        $select->join(
            array("attributes" => $this->getTable("eav/attribute")),
            "attributes.attribute_id=margins.attribute_id",
            array()
        );
        $select->where(
            "attributes.entity_type_id=?", $entityTypeID
        );
        $select->where(
            "attributes.attribute_code=?", Zolago_Catalog_Model_Product::ZOLAGO_CATALOG_PRICE_MARGIN_CODE
        );
        $select->where("products.sku IN(?)", $skuS);

        try {
            $margins = $readConnection->fetchAll($select);
        } catch (Exception $e) {
            Mage::throwException("Error fetching price_margin values");
        }

        return $margins;
    }
    /**
     * Get converter price type
     * @param $sku
     *
     * @return array
     */
    public function getConverterPriceTypeBySku($sku)
    {
        $priceType = array();
        if (empty($sku)) {
            Mage::throwException('Empty sku');
            return $priceType;
        }

        $readConnection = $this->getReadConnection();

        $select = $readConnection->select();
        $select
            ->from(
                'catalog_product_entity AS e',
                array(
                    'sku'        => 'e.sku',
                    'product_id' => 'e.entity_id'
                )
            )
            ->join(
                array('eav' => 'eav_attribute'),
                'e.entity_type_id = eav.entity_type_id',
                array()
            )
            ->join(
                array('integ' => 'catalog_product_entity_int'),
                'eav.attribute_id = integ.attribute_id',
                array(
                    'converter_price_type_value' => 'integ.value',
                    //'store'                      => 'integ.store_id'
                )
            )
            ->join(
                array('option_value' => 'eav_attribute_option_value'),
                'integ.value=option_value.option_id',
                array(
                    'price_type' => 'option_value.value',
                )
            )
            ->where("e.type_id=?", Mage_Catalog_Model_Product_Type::TYPE_SIMPLE)
            ->where("e.sku=?", $sku)
            ->where("integ.entity_id = e.entity_id")
            ->where("eav.attribute_code=?", Zolago_Catalog_Model_Product::ZOLAGO_CATALOG_CONVERTER_PRICE_TYPE_CODE)
            ->where("integ.store_id=?", (int)Mage::getSingleton('adminhtml/config_data')->getStore())
        ;


        try {
            $priceType = $readConnection->fetchRow($select);
        } catch (Exception $e) {
            Mage::throwException(Mage::helper('catalog')->__('Fetch converter price type: ' .$e->getMessage()));
        }
        return $priceType;
    }
    /**
     * Get converter price type
     * @param $sku
     *
     * @return array
     */
    public function getConverterPriceType($skus = array())
    {
        $priceType = array();
        $readConnection = $this->getReadConnection();
        $select = $readConnection->select();
        $select
            ->from(
                'catalog_product_entity AS e',
                array(
                    'sku'        => 'e.sku',
                    'product_id' => 'e.entity_id'
                )
            )
            ->join(
                array('eav' => 'eav_attribute'),
                'e.entity_type_id = eav.entity_type_id',
                array()
            )
            ->join(
                array('integ' => 'catalog_product_entity_int'),
                'eav.attribute_id = integ.attribute_id',
                array(
                    'converter_price_type_value' => 'integ.value',
                    'store'                      => 'integ.store_id'
                )
            )
            ->join(
                array('option_value' => 'eav_attribute_option_value'),
                'integ.value=option_value.option_id',
                array(
                    'price_type' => 'option_value.value',
                )
            )
            ->where("e.type_id=?", Mage_Catalog_Model_Product_Type::TYPE_SIMPLE)
            ->where("integ.entity_id = e.entity_id")
            ->where("eav.attribute_code=?", Zolago_Catalog_Model_Product::ZOLAGO_CATALOG_CONVERTER_PRICE_TYPE_CODE);

        if(!empty($skus)){
            $select->where("e.sku IN(?)", $skus);
        }

        try {
            $priceType = $readConnection->fetchAll($select);
        } catch (Exception $e) {
            Mage::throwException(Mage::helper('catalog')->__('Fetch converter price type: ' .$e->getMessage()));
        }
        return $priceType;
    }
}