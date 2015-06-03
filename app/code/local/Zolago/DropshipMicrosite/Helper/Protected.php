<?php
class Zolago_DropshipMicrosite_Helper_Protected extends Unirgy_DropshipMicrosite_Helper_Protected {

	protected function _getFrontendVendor($useUrl = false) {
		Mage::log('got_in');

		if($useUrl === false) {
			$useUrl = Mage::registry('ajax_referer_url');
		}

		return parent::_getFrontendVendor($useUrl);
	}
}