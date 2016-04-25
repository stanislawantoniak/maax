<?php

class ZolagoOs_Rma_Helper_Protected
{
	protected $_fedexCfgKeys = array(
		"fedex_test_mode",
		"fedex_user_key",
		"fedex_user_password",
		"fedex_account_number",
		"fedex_meter_number",
		"fedex_dry_ice_weight",
		"fedex_dropoff_type",
		"fedex_label_stock_type",
		"fedex_pdf_label_width",
		"fedex_pdf_label_height",
		"fedex_signature_option",
		"fedex_notify_on",
		"fedex_notify_email",
		"fedex_itn"
	);
	
	protected $_endiciaCfgKeys = array(
		"endicia_api_url",
		"endicia_test_mode",
		"endicia_requester_id",
		"endicia_account_id",
		"endicia_pass_phrase",
		"endicia_new_pass_phrase",
		"endicia_new_pass_phrase_confirm",
		"endicia_label_type",
		"endicia_mail_class",
		"endicia_mailpiece_shape",
		"endicia_stealth",
		"endicia_delivery_confirmation",
		"endicia_signature_confirmation",
		"endicia_return_receipt",
		"endicia_electronic_return_receipt",
		"endicia_insured_mail",
		"endicia_restricted_delivery",
		"endicia_cod",
		"endicia_balance_threshold",
		"endicia_recredit_amount",
		"endicia_pdf_label_width",
		"endicia_pdf_label_height"
	);
	
	protected $_upsCfgKeys = array(
		"ups_api_url",
		"ups_shipper_number",
		"ups_thirdparty_account_number",
		"ups_thirdparty_country",
		"ups_thirdparty_postcode",
		"ups_insurance",
		"ups_delivery_confirmation",
		"ups_verbal_confirmation",
		"ups_pickup",
		"ups_container",
		"ups_dest_type",
		"ups_pdf_label_width",
		"ups_pdf_label_height"
	);

	public function beforeRmaLabel($vendor, $rma) {
		ZolagoOs_OmniChannel_Helper_Protected::validateLicense("ZolagoOs_Rma");
		$storeId = $rma->getOrder()->getStoreId();
		$method = explode("_", $rma->getUdropshipMethod(), 2);
		$carrierCode = !empty($method[0]) ? $method[0] : $vendor->getCarrierCode();
		if ($carrierCode == "fedex" && $vendor->getData("rma_use_fedex_account") == "global") {
			$rmaFedex = Mage::getStoreConfig("urma/fedex", $storeId);
			foreach ($this->_fedexCfgKeys as $cfgKey) {
				$vendor->setData("__" . $cfgKey, $vendor->getData($cfgKey));
				if ($cfgKey == "fedex_notify_on") {
					if (empty($rmaFedex[$cfgKey])) {
						$rmaFedex[$cfgKey] = array();
					} else {
						if (is_scalar($rmaFedex[$cfgKey])) {
							$rmaFedex[$cfgKey] = array_filter(explode(",", $rmaFedex[$cfgKey]));
						}
						if (!is_array($rmaFedex[$cfgKey])) {
							$rmaFedex[$cfgKey] = array();
						}
					}
				}
				$vendor->setData($cfgKey, $rmaFedex[$cfgKey]);
			}
		} else {
			if ($carrierCode == "usps" && $vendor->getData("rma_use_endicia_account") == "global") {
				$rmaFedex = Mage::getStoreConfig("urma/endicia", $storeId);
				foreach ($this->_endiciaCfgKeys as $cfgKey) {
					$vendor->setData("__" . $cfgKey, $vendor->getData($cfgKey));
					$vendor->setData($cfgKey, $rmaFedex[$cfgKey]);
				}
			} else {
				if ($carrierCode == "ups" && $vendor->getData("rma_use_ups_account") == "global") {
					$rmaFedex = Mage::getStoreConfig("urma/ups", $storeId);
					foreach ($this->_upsCfgKeys as $cfgKey) {
						$vendor->setData("__" . $cfgKey, $vendor->getData($cfgKey));
						$vendor->setData($cfgKey, $rmaFedex[$cfgKey]);
					}
				}
			}
		}
		return $this;
	}

	public function afterRmaLabel($vendor, $rma) {
		ZolagoOs_OmniChannel_Helper_Protected::validateLicense("ZolagoOs_Rma");
		$storeId = $rma->getOrder()->getStoreId();
		$method = explode("_", $rma->getUdropshipMethod(), 2);
		$carrierCode = !empty($method[0]) ? $method[0] : $vendor->getCarrierCode();
		if ($carrierCode == "fedex" && $vendor->getData("rma_use_fedex_account") == "global") {
			foreach ($this->_fedexCfgKeys as $cfgKey) {
				$vendor->setData($cfgKey, $vendor->getData("__" . $cfgKey));
			}
		} else {
			if ($carrierCode == "usps" && $vendor->getData("rma_use_endicia_account") == "global") {
				foreach ($this->_endiciaCfgKeys as $cfgKey) {
					$vendor->setData($cfgKey, $vendor->getData("__" . $cfgKey));
				}
			} else {
				if ($carrierCode == "ups" && $vendor->getData("rma_use_ups_account") == "global") {
					foreach ($this->_upsCfgKeys as $cfgKey) {
						$vendor->setData($cfgKey, $vendor->getData("__" . $cfgKey));
					}
				}
			}
		}
		return $this;
	}
}