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
            /** @var Modago_Integrator_Helper_Data $helper */
            $helper = Mage::helper("modagointegrator");

            $this->_options = array(array("value" => 0, "label" => $helper->__("Default Config")));
            $this->_options = array_merge($this->_options, Mage::getResourceModel('core/store_collection')->load()->toOptionArray());
        }

        return $this->_options;
    }
}
