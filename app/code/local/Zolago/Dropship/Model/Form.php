<?php

class Zolago_Dropship_Model_Form extends Varien_Data_Form {
	public function _construct() {
		parent::_construct();
		/** @var Zolago_Dropship_Block_Form_Renderer_Fieldset $block */
		$block = Mage::getSingleton('core/layout')->
			createBlock('zolagodropship/form_renderer_fieldset');
		self::setFieldsetRenderer($block);
		/** @var Zolago_Dropship_Block_Form_Renderer_Fieldset_Element $block */
		$block = Mage::getSingleton('core/layout')->
			createBlock('zolagodropship/form_renderer_fieldset_element');
		self::setFieldsetElementRenderer($block);
	}
}
