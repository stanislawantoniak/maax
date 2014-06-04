<?php
class Zolago_Catalog_Helper_Data extends Mage_Core_Helper_Abstract {
	const ADDITIONAL_ATTRIBUTES_GROUP	= 'Additional columns';
	const SPECIAL_LABELS_OLD_DELIMITER	= ':';
	const SPECIAL_LABELS_NEW_DELIMITER	= ' | ';


    /**
     * get sku-id associated array
     * @return array
     */
    public static  function getSkuAssoc()
    {
        $readConnection = Mage::getSingleton('core/resource')
            ->getConnection('core_read');
        $query = "SELECT sku,entity_id AS product_id FROM `catalog_product_entity` WHERE type_id='simple';"; //Total time 0.00285 (on 1000 row(s))


        $skuAssoc = $readConnection->fetchPairs($query);
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