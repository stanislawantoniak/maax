<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Searchcore
 */
class Magpleasure_Searchcore_Model_Type_Config extends Varien_Object
{
    /**
     * @var bool
     */
    protected $_isLoaded = false;
    /**
     * @var
     */
    protected $_typeId;

    /**
     * Type Id
     *
     * @return integer
     */
    public function getTypeId()
    {
        return $this->_typeId;
    }

    /**
     * Type Id
     *
     * @param $typeId
     *
     * @return bool
     */
    public function setTypeId($typeId)
    {
        $this->_typeId = $typeId;

        return false;
    }

    /**
     * @return bool
     */
    public function getId()
    {
        return !!$this->_isLoaded;
    }

    /**
     * @param $typeCode
     *
     * @return $this
     */
    public function loadByCode($typeCode)
    {
        if ($typeCode) {
            $data = $this->_helper()->getSearchConfig()->getTypeConfig($typeCode);
            $this->setData($data);
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

    /**
     * @return array|mixed
     */
    public function getFields()
    {
        $fields = $this->getData('fields');
        if (is_array($fields)) {
            return $fields;
        }

        return array();
    }

    /**
     * @return array|mixed
     */
    public function getGetters()
    {
        $getters = $this->getData('getters');
        if (is_array($getters)) {
            return $getters;
        }

        return array();
    }

    /**
     * Target Resource
     *
     * @return bool|Mage_Core_Model_Abstract
     */
    public function getTargetResource()
    {
        if ($modelName = $this->getModel()) {
            $collection = Mage::getResourceModel($modelName);

            return $collection;
        }

        return false;
    }

    /**
     * Model Name
     *
     * @return string
     */
    public function getModel()
    {
        return $this->getData('model');
    }

    /**
     * Model Data Processor
     *
     * @return Magpleasure_Searchcore_Model_Processor_Abstract
     */
    public function getProcessor()
    {
        $processorName = $this->getData('processor');

        /** @var Magpleasure_Searchcore_Model_Processor_Abstract $processor */
        $processor = Mage::getSingleton("searchcore/processor_{$processorName}");
        $processor->setConfig($this);

        return $processor;
    }

    /**
     * Getting value of updated at field for gradation of search results.
     *
     * @param Mage_Core_Model_Abstract $object
     *
     * @return string
     */
    public function getUpdatedAtField(Mage_Core_Model_Abstract $object)
    {
        $field = $this->getData('updated_at_field');
        $value = $object->getData($field);

        return $value;
    }
}