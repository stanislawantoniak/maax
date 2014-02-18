<?php

class Zolago_Dropship_Block_Form extends Varien_Data_Form{
	public function _construct() {
		parent::_construct();
		$block = Mage::getSingleton('core/layout')->
			createBlock('zolagodropship/form_renderer_fieldset');
		self::setFieldsetRenderer($block);
	}
}
