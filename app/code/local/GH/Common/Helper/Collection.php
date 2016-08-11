<?php

/**
 * Class GH_Common_Helper_Collection
 */
class GH_Common_Helper_Collection extends Mage_Core_Helper_Abstract {

    /**
     * Make smart clone of collection from $source to $destination
     * Ex:
     * $newCollection = $helper->cloneByParts(
     *      Mage::getResourceModel('catalog/product_collection'),
     *      $source);
     *
     * By 'smart' means `clone` whole SELECT
     *
     * @param Varien_Data_Collection_Db $destination
     * @param Varien_Data_Collection_Db $source
     * @return mixed
     */
    public function cloneByParts($destination, $source) {
        foreach ($this->_getSelectParts() as $part) {
            $destination->getSelect()->setPart($part, $source->getSelect()->getPart($part));
        }
        return $destination;
    }

    /**
     * Get all parts of select
     * For more info:
     * @see Zend_Db_Select
     *
     * @return array
     */
    protected function _getSelectParts() {
        return array(
            Varien_Db_Select::DISTINCT,
            Varien_Db_Select::COLUMNS,
            Varien_Db_Select::UNION,
            Varien_Db_Select::FROM,
            Varien_Db_Select::WHERE,
            Varien_Db_Select::GROUP,
            Varien_Db_Select::HAVING,
            Varien_Db_Select::ORDER,
            Varien_Db_Select::LIMIT_COUNT,
            Varien_Db_Select::LIMIT_OFFSET,
            Varien_Db_Select::FOR_UPDATE
        );
    }
}