<?php

/**
 * Class Modago_Integrator_Model_System_Config_Source_Store
 */
class Modago_Integrator_Model_System_Config_Source_Store
{
    protected $_options;

    public function toOptionArray()
    {
        if (!$this->_options) {
            $this->_options = array(array("value" => "", "label" => " "));
            $this->_options = array_merge($this->_options, Mage::getResourceModel('core/store_collection')->load()->toOptionArray());
        }

        return $this->_options;
    }
}
