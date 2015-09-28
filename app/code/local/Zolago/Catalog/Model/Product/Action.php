<?php
/**
 * Class Zolago_Catalog_Model_Product_Action
 */
class Zolago_Catalog_Model_Product_Action extends Mage_Catalog_Model_Product_Action {


    /**
     * @param array $productIds
     * @param array $attrData
     * @param int   $storeId
     *
     * @return Mage_Catalog_Model_Product_Action
     */
    public function updateAttributes($productIds, $attrData, $storeId)
    {
        $ret = parent::updateAttributes($productIds, $attrData, $storeId);
        // Add after event
        Mage::dispatchEvent('catalog_product_attribute_update_after', array(
            'attributes_data' => $attrData,
            'product_ids' => $productIds,
            'store_id' => $storeId
        ));
        return $ret;
    }

    /**
     * @param $productIds
     * @param $attrData
     * @param $storeId
     *
     * @return $this
     */
    public function updateAttributesNoIndex($productIds, $attrData, $storeId)
    {

        Mage::dispatchEvent(
            'catalog_product_attribute_update_before',
            array(
                 'attributes_data' => &$attrData,
                 'product_ids'     => &$productIds,
                 'store_id'        => &$storeId
            )
        );
        $this->_getResource()->updateAttributes($productIds, $attrData, $storeId);
        $this->setData(
            array(
                 'product_ids'     => array_unique($productIds),
                 'attributes_data' => $attrData,
                 'store_id'        => $storeId
            )
        );

        return $this;
    }

    /**
     * @param $productIds
     * @param $attrData
     * @param $storeId
     * @return $this
     * @throws Exception
     */
    public function updateAttributesPure($productIds, $attrData, $storeId)
    {
        $this->_getResource()->updateAttributes($productIds, $attrData, $storeId);
        $this->setData(
            array(
                'product_ids' => array_unique($productIds),
                'attributes_data' => $attrData,
                'store_id' => $storeId
            )
        );

        return $this;
    }

    public function reindexAfterMassAttributeChange(){
        // register mass action indexer event
        Mage::getSingleton('index/indexer')->processEntityAction(
            $this, Mage_Catalog_Model_Product::ENTITY, Mage_Index_Model_Event::TYPE_MASS_ACTION
        );
    }

}
