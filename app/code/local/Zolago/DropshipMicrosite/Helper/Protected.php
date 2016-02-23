<?php
class Zolago_DropshipMicrosite_Helper_Protected extends Unirgy_DropshipMicrosite_Helper_Protected {

	protected function _getFrontendVendor($useUrl = false) {

		if($useUrl === false) {
			$useUrl = Mage::registry('ajax_referrer_url');
		}

		return parent::_getFrontendVendor($useUrl);
	}
}