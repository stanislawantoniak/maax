<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Common
 */
class Magpleasure_Common_Helper_Store extends Mage_Core_Helper_Abstract
{
    protected $_actualStoreIds;

    /**
     * Retrieves Actual Store Ids
     *
     * @return array
     */
    public function getFrontendStoreIds()
    {
        if (!$this->_actualStoreIds){
            $storeIds = array();

            /** @var Mage_Core_Model_Store $stores */
            $stores = Mage::getModel('core/store')->getCollection();
            foreach ($stores as $store){
                if ($store->getId()){
                    $storeIds[] = $store->getId();
                }
            }

            $this->_actualStoreIds = $storeIds;
        }
        return $this->_actualStoreIds;
    }

    /**
     * Retrieves all available store Ids
     *
     * @return array
     */
    public function getAllStores()
    {
        $frontendStoreIds = $this->getFrontendStoreIds();
        $frontendStoreIds[] = '0';
        return $frontendStoreIds;
    }
}