<?php

class Zolago_Catalog_Model_Resource_Product_Gallery extends Mage_Catalog_Model_Resource_Product
{

    public function getProductImageData($imageValueId)
    {
        $result = array();

        $readConnection = $this->_getReadAdapter();

        $productMediaValueTable = $this->getTable("catalog/product_attribute_media_gallery_value");

        $defaultStoreId = (int)Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID;

        $select = $readConnection->select();
        $select->from(
            array("gallery_value" => $productMediaValueTable),
            array("*")
        );
        $select->where("gallery_value.value_id=?", $imageValueId);
        $select->where("gallery_value.store_id=?", $defaultStoreId);

        try {
            $result = $readConnection->fetchRow($select);

        } catch (GH_Common_Exception $e) {
            Mage::logException($e);
        } catch (Exception $e) {
            Mage::logException($e);
        }

        return $result;
    }
    /**
     * Get product enabled images
     * @param $productId
     * @return array
     */
    public function getEnabledProductImages($productId)
    {
        $result = array();

        $readConnection = $this->_getReadAdapter();

        $productMediaTable = $this->getTable("catalog/product_attribute_media_gallery");
        $productMediaValueTable = $this->getTable("catalog/product_attribute_media_gallery_value");

        $defaultStoreId = (int)Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID;

        $select = $readConnection->select();
        $select->from(
            array("gallery" => $productMediaTable),
            array("value")
        );
        $select->join(
            array("gallery_value" => $productMediaValueTable),
            "gallery.value_id=gallery_value.value_id",
            array()
        );
        $select->where("gallery.entity_id=?", (int)$productId);
        $select->where("gallery_value.store_id=?", $defaultStoreId);
        $select->where("gallery_value.disabled=?", 0);
        $select->order("position ASC");

        try {
            $result = $readConnection->fetchCol($select);

        } catch (GH_Common_Exception $e) {
            Mage::logException($e);
        } catch (Exception $e) {
            Mage::logException($e);
        }

        return $result;
    }
}