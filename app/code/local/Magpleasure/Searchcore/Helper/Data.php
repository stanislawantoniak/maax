<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Searchcore
 */

class Magpleasure_Searchcore_Helper_Data extends Mage_Core_Helper_Abstract
{
    const CACHE_KEY_SEARCHABLE_TYPE_PREFIX = 'mp_searchcore_search_type_';

    /**
     * Search Config
     *
     * @return Magpleasure_Searchcore_Helper_Config
     */
    public function getSearchConfig()
    {
        return Mage::helper('searchcore/config');
    }

    public function getTypeList()
    {
        $typeList = array();
        foreach ($this->_getTypeCollection() as $item){
            $typeList[] = $item->getTypeCode();
        }
        return $typeList;
    }

    protected function _getTypeCollection()
    {
        return Mage::getModel('searchcore/type')->getCollection();
    }

    /**
     * Type by Code
     *
     * @param $typeCode
     * @return bool|Magpleasure_Searchcore_Model_Type
     */
    public function getTypeByCode($typeCode)
    {
        /** @var Magpleasure_Searchcore_Model_Type $type */
        $type = Mage::getModel('searchcore/type');
        $type->load($typeCode, 'type_code');
        return $type->getId() ? $type : false;
    }

    public function getSearchableType($modelName)
    {
        if ($typeId = $this->_getSearchableTypeId($modelName)){
            /** @var Magpleasure_Searchcore_Model_Type $type */
            $type = Mage::getModel('searchcore/type')->load($typeId);
            return $type;
        }

        return false;
    }

    protected function _getSearchableTypeId($modelName)
    {
        $cacheKey = self::CACHE_KEY_SEARCHABLE_TYPE_PREFIX.md5($modelName);
        $typeId = $this->getCommon()->getCache()->getPreparedValue($cacheKey);

        if ($typeId === null){

            $typeId = false;

            $collection = $this->_getTypeCollection();
            foreach ($collection as $type){
                /** @var $type Magpleasure_Searchcore_Model_Type */

                /** @var Magpleasure_Searchcore_Model_Type_Config $typeConfig */
                $typeConfig = $type->getConfig();

                if ($typeConfig->getModel() == $modelName){
                    $typeId = $type->getId();
                    $this->getCommon()->getCache()->savePreparedValue($cacheKey, $typeId);
                    return $typeId;
                }
            }

            $this->getCommon()->getCache()->savePreparedValue($cacheKey, false);
        }

        return $typeId ? $typeId : false;
    }

    /**
     * Common Helper
     *
     * @return Magpleasure_Common_Helper_Data
     */
    public function getCommon()
    {
        return Mage::helper('magpleasure');
    }

    /**
     * Stemmer
     *
     * @return Magpleasure_Searchcore_Helper_Tools_Stemmer
     */
    public function getStemmer()
    {
        return Mage::helper("searchcore/tools_stemmer");
    }

    /**
     * Ignore Helper
     *
     * @return Magpleasure_Searchcore_Helper_Tools_Ignore
     */
    public function getIgnoreHelper()
    {
        return Mage::helper("searchcore/tools_ignore");
    }

    /**
     * Range Helper
     *
     * @return Magpleasure_Searchcore_Helper_Tools_Range
     */
    public function getRangeHelper()
    {
        return Mage::helper("searchcore/tools_range");
    }

    public function getTextTransformer()
    {
        return Mage::helper("searchcore/tools_text");
    }
}