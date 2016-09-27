<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Searchcore
 */
class Magpleasure_Searchcore_Model_Processor_Fields extends Magpleasure_Searchcore_Model_Processor_Abstract
{
    public function process(Mage_Core_Model_Abstract $object)
    {
        $data = array();

        # 1. Collect data from fields
        foreach ($this->getConfig()->getFields() as $field) {

            /** @var $field Magpleasure_Searchcore_Model_Field_Default */
            if ($value = $field->getProcessableValue($object)) {
                $data[] = $value;
            }
        }

        foreach ($this->getConfig()->getGetters() as $getter) {
            if ($value = $object->$getter()) {
                try {
                    $data[] = $this
                        ->_helper()
                        ->getSearchConfig()
                        ->getDefaultFieldProcessor()
                        ->getProcessableValue((string)$value);

                } catch (Exception $e) {
                    $this->_helper()->getCommon()->getException($e);
                }
            }
        }

        return implode(" ", $data);
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
}