<?php
class Zolago_Catalog_Helper_Data extends Mage_Core_Helper_Abstract {
	const ADDITIONAL_ATTRIBUTES_GROUP	= 'Additional columns';
	const SPECIAL_LABELS_OLD_DELIMITER	= ':';
	const SPECIAL_LABELS_NEW_DELIMITER	= ' | ';

    /**
     * get id-sku associated array
     * @return array
     */
    public static function getIdSkuAssoc()
    {
        $readConnection = Mage::getSingleton('core/resource')
            ->getConnection('core_read');

        $select = $readConnection->select();
        $select
            ->from('catalog_product_entity AS products',
                array(
                    'product_id' => 'entity_id',
                    'sku',
                )
            )
            ->where("products.type_id=?", Mage_Catalog_Model_Product_Type::TYPE_SIMPLE);

        $skuAssoc = $readConnection->fetchPairs($select);
        return $skuAssoc;
    }
    /**
     * get sku-id associated array
     * @return array
     */
    public static function getSkuAssoc($skus = array())
    {
        $readConnection = Mage::getSingleton('core/resource')
            ->getConnection('core_read');

        $select = $readConnection->select();
        $select
            ->from('catalog_product_entity AS products',
                array(
                    'sku',
                    'product_id' => 'entity_id'
                )
            )
            ->where("products.type_id=?", Mage_Catalog_Model_Product_Type::TYPE_SIMPLE);


        if (!empty($skus)) {
            $select->where("sku IN (?)", $skus);
        }

        $skuAssoc = $readConnection->fetchPairs($select);
        return $skuAssoc;
    }

    public static  function getSkuAssocId($sku)
    {
        $readConnection = Mage::getSingleton('core/resource')
            ->getConnection('core_read');
        $query = "SELECT entity_id AS product_id FROM `catalog_product_entity` WHERE type_id='simple' AND sku='{$sku}';";
        $productId = $readConnection->fetchOne($query);
        return $productId;
    }
}