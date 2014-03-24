<?php

class Mage_Adminhtml_Block_Widget extends Mage_Adminhtml_Block_Template
{
    public function _getUrlModelClass() {
		if($this->getIsFrontend() || Mage::registry('as_frontend')){
			return 'core/url';
		}
		return parent::_getUrlModel();
	}
}