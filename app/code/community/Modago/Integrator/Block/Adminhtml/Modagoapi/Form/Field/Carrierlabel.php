<?php

/**
 * Field 'renderer' for admin config in section with carriers
 *
 * Class Modago_Integrator_Block_Adminhtml_Modagoapi_Form_Field_Carrierlabel
 */
class Modago_Integrator_Block_Adminhtml_Modagoapi_Form_Field_Carrierlabel extends Mage_Adminhtml_Block_System_Config_Form_Field {

	protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element) {
		/** @var Modago_Integrator_Helper_Api $helper */
		$helper = Mage::helper('modagointegrator/api');
		return $helper->__("Modago Courier");
	}

	public function render(Varien_Data_Form_Element_Abstract $element) {
		$element->setScope(null); // don't show scope ex: [GLOBAL]
		return parent::render($element);
	}
}
