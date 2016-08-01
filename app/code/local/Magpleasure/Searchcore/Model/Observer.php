<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Searchcore
 */

class Magpleasure_Searchcore_Model_Observer
{
    public function updateSearchTypes()
    {
        $types = $this->_helper()->getSearchConfig()->getTypes();
        asort($types);
        $actualHash = $this->_helper()->getCommon()->getHash()->getFastMd5Hash($types);

        /** @var Magpleasure_Searchcore_Model_Type $typeModel */
        $typeModel = Mage::getModel('searchcore/type');
        $typesHash = $typeModel->getTypesHash();

        if ($actualHash != $typesHash){
            $typeModel->processTypes($types);
        }

        return $this;
    }

    /**
     * Helper
     *
     * @return Magpleasure_Searchcore_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('searchcore');
    }

    public function storeSaveCommitAfter($event)
    {
        /** @var Mage_Core_Model_Store $store */
        $store = $event->getStore();

        if ($store && $store->getId()){

            try {
                /** @var Magpleasure_Searchcore_Model_Resource_Index $indexResource */
                $indexResource = Mage::getResourceModel('searchcore/index');
                $indexResource->setReindexRequiredFlag();

            } catch (Exception $e){
                $this->_helper()->getCommon()->getException()->logException($e);
            }
        }

        return $this;
    }

    public function storeDeleteCommitAfter($event)
    {
        /** @var Mage_Core_Model_Store $store */
        $store = $event->getStore();

        if ($store && $store->getId()){

            try {

                /** @var Magpleasure_Searchcore_Model_Resource_Index $indexResource */
                $indexResource = Mage::getResourceModel('searchcore/index');
                $indexResource->flushForStore($store->getId());

            } catch (Exception $e){
                $this->_helper()->getCommon()->getException()->logException($e);
            }
        }

        return $this;
    }

}