<?php
class Zolago_DropshipMicrosite_Helper_Protected extends Unirgy_DropshipMicrosite_Helper_Protected {

	protected $_getFrontedVendorCache = null;

	protected function _getFrontendVendor($useUrl = false) {

		// Fix for ajax for customer
		$request = Mage::app()->getRequest()->getRequestString();
		$ajaxRefererUrl = Mage::registry('ajax_referer_url');
		if ($ajaxRefererUrl || strpos($request, 'orbacommon/ajax_customer/get_account_information') !== false) {
			$useUrl = $ajaxRefererUrl ? $ajaxRefererUrl : Mage::app()->getRequest()->getServer('HTTP_REFERER');
		}
		// Add simple cache
		if (!empty($this->_getFrontedVendorCache[(string)$useUrl])) {
			return $this->_getFrontedVendorCache[(string)$useUrl];
		}
		return $this->_getFrontedVendorCache[(string)$useUrl] = parent::_getFrontendVendor($useUrl);
	}
}