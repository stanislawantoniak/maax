<?php
/**
 * resource model for product queue
 *
 * @category    Zolago
 * @package     Zolago_Catalog
 *
 */
class Zolago_Catalog_Model_Resource_Queue_Pricetype extends Zolago_Common_Model_Resource_Queue_Abstract {
    /**
     * @var int
     */
    protected $_buffer = 500;
    /**
     * @var array
     */
    protected $_dataToSave = array();


    /**
     * Init main table
     */
    public  function _construct() {

        $this->_init('zolagocatalog/queue_pricetype','queue_id');
    }

    /**
     * Add item to queue
     * @param $ids
     *
     * @return mixed
     */
    public function addToQueue($ids)
    {
        if (!empty($ids)) {
            foreach ($ids as $productId) {
                $this->_prepareData($productId);
            }

            $this->_saveData();
        }
        return $ids;
    }

    /**
     * @param $id
     *
     * @return mixed
     */
    public function addToQueueProduct($id)
    {
        if (!empty($id)) {

            $table = $this->getTable('zolagocatalog/queue_pricetype');
            $data = array(
                        "insert_date"          => Varien_Date::now(),
                        "status"               => 0,
                        "product_id"           => $id
                    );
            $fields = array('insert_date', 'status', 'product_id');
            $this->getReadConnection()

            ->insertOnDuplicate($table, $data, $fields);
        }
        return $id;
    }

    /**
     * Reset data after save
     */
    protected function _resetData()
    {
        $this->_dataToSave = array();
    }

    /**
     * Prepare data to save
     * @param $productId
     */
    protected function _prepareData($productId)
    {
        $key = $this->_buildIndexKey($productId);
        $this->_dataToSave[$key] = array(
                                       "insert_date" => Varien_Date::now(),
                                       "product_id" => $productId,
                                       "process_date" => null,
                                       "status" => 0,
                                   );
    }

    /**
     * @param $productId
     *
     * @return string
     */
    protected function _buildIndexKey($productId)
    {
        return "$productId";
    }

    /**
     * @return insert products id values
     */
    protected function _saveData()
    {
        $i = $this->_buffer;
        $all = 0;
        $insert = array();
        $this->_getWriteAdapter()->beginTransaction();

        foreach ($this->_dataToSave as $item) {
            $insert[] = $item;
            $i--;
            // Insert via buffer
            if ($i == 0) {
                $i = $this->_buffer;
                $all += $this->_buffer;
                $this->_getWriteAdapter()->insertOnDuplicate($this->getMainTable(), $insert, array());
                $insert = array();
            }
        }

        // Insert out of buffer values
        if (count($insert)) {
            $all += count($insert);
            $this->_getWriteAdapter()->insertOnDuplicate($this->getMainTable(), $insert, array());
        }

        // Commit transaction
        $this->_getWriteAdapter()->commit();
        $this->_resetData();
        return $all;
    }

    /**
     * Clear precessed queue
     */
    public function clearQueue()
    {
        $condition = $this->_getWriteAdapter()->quoteInto('status = ?', 1);
        $this->_getWriteAdapter()->delete($this->getTable('zolagocatalog/queue_pricetype'), $condition);

    }

    /**
     * @return array
     * @throws Mage_Core_Exception
     */
    public function getConverterPriceTypeOptions()
    {
        $converterPriceTypeOptions = array();
        $attribute = Mage::getSingleton('eav/config')->getAttribute(
                         'catalog_product', Zolago_Catalog_Model_Product::ZOLAGO_CATALOG_CONVERTER_PRICE_TYPE_CODE
                     );
        if ($attribute->usesSource()) {
            $options = $attribute->getSource()->getAllOptions(false);
            if (!empty($options)) {
                foreach ($options as $option) {
                    $converterPriceTypeOptions[$option['value']] = $option['label'];
                }
            }
        }
        return $converterPriceTypeOptions;
    }


    /**
     * @param $ids
     *
     * @return array $assoc
     */
    public function getVendorSkuAssoc($ids)
    {
        $assoc = array();

        if (!empty($ids)) {
            $readConnection = $this->_getReadAdapter();
            $attribute = Mage::getSingleton('eav/config')->getAttribute(
                             Mage_Catalog_Model_Product::ENTITY,
                             Mage::getStoreConfig('zolagoos/vendor/vendor_sku_attribute')
                         );
            $vendor = Mage::getSingleton('eav/config')->getAttribute(
                          Mage_Catalog_Model_Product::ENTITY,
                          'udropship_vendor'
                      );
            /* @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
            $tVarchar = $attribute->getBackendTable();


            $tVarchar = $readConnection->getTableName('catalog_product_entity_varchar');
            $tInt 	  = $readConnection->getTableName('catalog_product_entity_int');
            // not simple products
            $select = $readConnection->select();
            $select->from(
                array("product_varchar" => $tVarchar),
                array(
                    "product_id" => "product_varchar.entity_id",
                    "skuv"       => "product_varchar.value",
                    "store"      => "product_varchar.store_id",
                    "vendor" 	  => "product_int.value",
                    "parent"	  => "product_relation.parent_id",
                )
            );
//            $select->join(
//                array("attribute" => $this->getTable("eav/attribute")),
//                "attribute.attribute_id = product_varchar.attribute_id",
//                array()
//            );
            $select->join(
                array("product_int" => $tInt),
                "product_int.entity_id = product_varchar.entity_id",
                array()
            );
            $select->join(
                array("product_relation" => $this->getTable("catalog/product_relation")),
                "product_varchar.entity_id = product_relation.child_id",
                array()
            );
            /*
                        $select->join(
                            array("parent_product" => $this->getTable("catalog/product")),
                            "parent_product.entity_id = product_relation.parent_id",
                            array("type" => "parent_product.type_id")
                        );
            */
            //$select->where("attribute.attribute_code=?", Mage::getStoreConfig('zolagoos/vendor/vendor_sku_attribute'));
            $select->where("product_varchar.attribute_id=?",$attribute->getId());
            $select->where("product_int.attribute_id=?",$vendor->getId());
            $select->where("product_relation.parent_id IN(?)", $ids);

            try {
                $assoc1 = $readConnection->fetchAll($select);
            } catch (Exception $e) {
                Mage::throwException("Error skuv");
            }
            // remove existing ids
            $out = array();
            foreach ($assoc1 as $prod) {
                $out[] = $prod['product_id'];
                $out[] = $prod['parent'];
            }
            $fin = array_diff($ids,$out);

            // simple products

            if (count($fin)) {
                $select2 = $readConnection->select();
                $select2->from(
                    array("product_varchar" => $tVarchar),
                    array(
                        "product_id" => "product_varchar.entity_id",
                        "skuv"       => "product_varchar.value",
                        "store"      => "product_varchar.store_id",
                        "vendor" => "product_int.value",
                        "parent" => "product_varchar.entity_id",   // parent same as product
                    )
                );
                $select2->join(
                    array("product_int" => $tInt),
                    "product_int.entity_id = product_varchar.entity_id",
                    array()
                );
                $select2->where("product_varchar.attribute_id=?",$attribute->getId());
                $select2->where("product_int.attribute_id=?",$vendor->getId());
                $select2->where("product_varchar.entity_id IN(?)", $fin);
                try {
                    $assoc2 = $readConnection->fetchAll($select2);
                } catch (Exception $e) {
                    Mage::throwException("Error skuv 2");
                }
            } else {
                $assoc2 = array();
            }

            $assoc = array_merge($assoc1,$assoc2);
        }
        return $assoc;
    }

    /**
     * @param $ids
     *
     * @return array $assoc
     */
    public function getPriceTypeValues($ids)
    {
        $assoc = array();

        if (!empty($ids)) {
            $readConnection = $this->_getReadAdapter();
            $select = $readConnection->select();
            $select->from(
                array("product_int" => 'catalog_product_entity_int'),
                array(
                    "product_id"           => "product_int.entity_id",
                    "converter_price_type" => "product_int.value",
                    "store"                => "product_int.store_id"
                )
            );
            $select->join(
                array("attribute" => $this->getTable("eav/attribute")),
                "attribute.attribute_id = product_int.attribute_id",
                array()
            );
            $select->join(
                array("attribute_option_value" => $this->getTable("eav/attribute_option_value")),
                "attribute_option_value.option_id=product_int.value",
                array("converter_price_type_label" => "attribute_option_value.value")
            );
            $select->where(
                "attribute.attribute_code=?", Zolago_Catalog_Model_Product::ZOLAGO_CATALOG_CONVERTER_PRICE_TYPE_CODE
            );
            $select->where("product_int.entity_id IN(?)", $ids);


            try {
                $assoc = $readConnection->fetchAll($select);
            } catch (Exception $e) {
                Mage::throwException("Error fetching converter_price_type values");
            }

        }
        return $assoc;
    }


    /**
     * @param $ids
     *
     * @return array $assoc
     */
    public function getPriceMarginValues($ids)
    {
        $assoc = array();

        if (!empty($ids)) {
            $readConnection = $this->_getReadAdapter();
            $select = $readConnection->select();
            $select->from(
                array("product_text" => 'catalog_product_entity_text'),
                array(
                    "product_id"   => "product_text.entity_id",
                    "price_margin" => "product_text.value",
                    "store"        => "product_text.store_id"
                )
            );
            $select->join(
                array("attribute" => $this->getTable("eav/attribute")),
                "attribute.attribute_id = product_text.attribute_id",
                array()
            );
            $select->where(
                "attribute.attribute_code=?", Zolago_Catalog_Model_Product::ZOLAGO_CATALOG_PRICE_MARGIN_CODE
            );
            $select->where("product_text.entity_id IN(?)", $ids);

            try {
                $assoc = $readConnection->fetchAll($select);
            } catch (Exception $e) {
                Mage::throwException("Error fetching price_margin values");
            }
        }
        return $assoc;
    }
    /**
     * @param $ids
     *
     * @return array $assoc
     */
    public function getMsrpValues($ids)
    {
        $assoc = array();

        if (!empty($ids)) {
            $readConnection = $this->_getReadAdapter();
            $select = $readConnection->select();
            $select->from(
                array("product_int" => 'catalog_product_entity_int'),
                array(
                    "product_id"   => "product_int.entity_id",
                    "price_msrp" => "product_int.value",
                    "store"        => "product_int.store_id"
                )
            );
            $select->join(
                array("attribute" => $this->getTable("eav/attribute")),
                "attribute.attribute_id = product_int.attribute_id",
                array()
            );
            $select->where(
                "attribute.attribute_code=?", Zolago_Catalog_Model_Product::ZOLAGO_CATALOG_CONVERTER_MSRP_TYPE_CODE
            );
            $select->where("product_int.entity_id IN(?)", $ids);

            try {
                $assoc = $readConnection->fetchAll($select);
            } catch (Exception $e) {
                Mage::throwException("Error fetching price_margin values");
            }
        }
        return $assoc;
    }

}

