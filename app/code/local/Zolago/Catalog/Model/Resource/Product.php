<?php

class Zolago_Catalog_Model_Resource_Product extends Mage_Catalog_Model_Resource_Product {


    public function savePriceValues($insert, $ids)
    {
        $writeAdapter = $this->_getWriteAdapter();
        $writeAdapter->beginTransaction();
        try {
            //1. update simple product price
            $writeAdapter->insertOnDuplicate(
                $writeAdapter->getTableName('catalog_product_entity_decimal'),
                $insert, array('value')
            );

            $this->_getWriteAdapter()->commit();

            //2. put simple products to configurable queue
            //@todo test without configurable processing
            //Zolago_Catalog_Helper_Configurable::queue($ids);

        } catch (Exception $e) {
            $this->_getWriteAdapter()->rollBack();

            Mage::throwException("Error savePriceValues");

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
     * Fetch products that should be updated by converter
     * Converter Msrp Type = From file
     * @param $skuS
     * @return array
     * @throws Mage_Core_Exception
     */
    public function getMSRPSourceValuesManualConverterConfigurable($skuS)
    {
        $msrp = array();

        if (empty($skuS)) {
            return array();
        }
        $entityTypeID = Mage::getModel('catalog/product')->getResource()->getTypeId();

        $readConnection = $this->_getReadAdapter();
        $select = $readConnection->select();
        $select->from(
            array("msrp_source" => "catalog_product_entity_int"),
            array(
                "store" => "msrp_source.store_id",
                "msrp_source_type" => "msrp_source.value"
            )
        );
        $select->join(
            array("product_relation" => "catalog_product_relation"),
            "product_relation.parent_id=msrp_source.entity_id",
            array()
        );
        $select->join(
            array("products" => $this->getTable("catalog/product")),
            "products.entity_id=product_relation.child_id",
            array(
                'products.sku'
            )
        );
        $select->join(
            array("attributes" => $this->getTable("eav/attribute")),
            "attributes.attribute_id=msrp_source.attribute_id",
            array()
        );
        $select->where(
            "attributes.entity_type_id=?", $entityTypeID
        );
        $select->where(
            "attributes.attribute_code=?", Zolago_Catalog_Model_Product::ZOLAGO_CATALOG_CONVERTER_MSRP_TYPE_CODE
        );
        $select->where("msrp_source.value=?", Zolago_Catalog_Model_Product_Source_Convertermsrptype::FLAG_MANUAL);
        $select->where("products.sku IN(?)", $skuS);
        Mage::log($select->__toString(), 0, 'priceMSRPSource.log');
        try {
            $msrp = $readConnection->fetchAll($select);
        } catch (Exception $e) {
            Mage::throwException("Error fetching converter_msrp_type values");
        }

        return $msrp;
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
                    'sku' => 'e.sku',
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
                    'store' => 'integ.store_id'
                )
            )
            ->join(
                array('option_value' => 'eav_attribute_option_value'),
                'integ.value=option_value.option_id',
                array(
                    'price_type' => 'option_value.value',
                )
            )
            //->where("e.type_id=?", Mage_Catalog_Model_Product_Type::TYPE_SIMPLE)
            ->where("integ.entity_id = e.entity_id")
            ->where("eav.attribute_code=?", Zolago_Catalog_Model_Product::ZOLAGO_CATALOG_CONVERTER_PRICE_TYPE_CODE);

        if (!empty($skus)) {
            $select->where("e.sku IN(?)", $skus);
        }

        try {
            $priceType = $readConnection->fetchAll($select);
        } catch (Exception $e) {
            Mage::throwException(Mage::helper('catalog')->__('Fetch converter price type: ' . $e->getMessage()));
        }
        return $priceType;
    }

    /**
     * @param $skus
     * @return array
     * @throws Mage_Core_Exception
     */
    public function getConverterPriceTypeConfigurable($skus)
    {
        if (empty($skus)) {
            return array();
        }
        $priceType = array();
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
            array("products" => $this->getTable("catalog/product")),
            "products.entity_id=product_relation.child_id",
            array(
                'products.sku'
            )
        );

        $select->join(
            array("integ" =>  'catalog_product_entity_int'),
            'product_relation.parent_id=integ.entity_id',
            array(
                'price_type' => 'integ.value',
                'store' => 'integ.store_id'
            )
        );
        $select->join(
            array("attributes" => $this->getTable("eav/attribute")),
            "attributes.attribute_id=integ.attribute_id",
            array()
        );
        $select->join(
            array('option_value' => 'eav_attribute_option_value'),
            'integ.value=option_value.option_id',
            array(
                'price_type' => 'option_value.value',
            )
        );

        $select->where(
            "attributes.entity_type_id=?", $entityTypeID
        );
        $select->where(
            "attributes.attribute_code=?", Zolago_Catalog_Model_Product::ZOLAGO_CATALOG_CONVERTER_PRICE_TYPE_CODE
        );
        $select->where("products.sku IN(?)", $skus);

        try {
            $priceType = $readConnection->fetchAll($select);
        } catch (Exception $e) {
            Mage::throwException("Error fetching converter_price_type values");
        }
        return $priceType;
    }
}