<?php

class Zolago_Catalog_Model_Resource_Product extends Mage_Catalog_Model_Resource_Product {


    public function savePriceValues($insert)
    {
        $writeAdapter = $this->_getWriteAdapter();
        $writeAdapter->beginTransaction();
        try {
            $writeAdapter->insertOnDuplicate(
                $writeAdapter->getTableName('catalog_product_entity_decimal'),
                $insert, array('value')
            );

            $this->_getWriteAdapter()->commit();
        } catch (Exception $e) {
            $this->_getWriteAdapter()->rollBack();
            throw $e;
        }

        return $this;
    }

    /**
     * @param $ids
     *
     * @return array $assoc
     */
    public function getPriceMarginValues($skuS)
    {
        $assoc = array();

        if (!empty($skuS)) {
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
            $select->join(
                array("product" => $this->getTable("catalog/product")),
                "product_text.entity_id = product.entity_id",
                array()
            );
            $select->where(
                "attribute.attribute_code=?", Zolago_Catalog_Model_Product::ZOLAGO_CATALOG_PRICE_MARGIN_CODE
            );
            $select->where("product.sku IN(?)", $skuS);

            try {
                $assoc = $readConnection->fetchAll($select);
            } catch (Exception $e) {
                Mage::throwException("Error fetching price_margin values");
            }
        }
        return $assoc;
    }
}