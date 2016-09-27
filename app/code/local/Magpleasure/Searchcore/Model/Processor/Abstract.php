<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Searchcore
 */

abstract class Magpleasure_Searchcore_Model_Processor_Abstract extends Varien_Object
{
    protected $_config;

    /**
     * Get Config
     *
     * @return Magpleasure_Searchcore_Model_Type_Config
     */
    public function getConfig()
    {
        return $this->_config;
    }

    /**
     * Set Config
     *
     * @param Magpleasure_Searchcore_Model_Type_Config $config
     * @return $this
     */
    public function setConfig($config)
    {
        $this->_config = $config;
        return $this;
    }

    abstract public function process(Mage_Core_Model_Abstract $object);

    /**
     * Helper
     *
     * @return Magpleasure_Searchcore_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('searchcore');
    }
}