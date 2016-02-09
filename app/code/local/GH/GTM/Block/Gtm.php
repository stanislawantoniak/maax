<?php

/**
 * Google Tag Manager Block
 *
 * Class GH_GTM_Block_Gtm
 *
 * @method $this setCustomData() add js to extend dataLayer object
 */
class GH_GTM_Block_Gtm extends Shopgo_GTM_Block_Gtm {
	/**
	 * GH_GTM_Block_Gtm constructor.
	 */
	public function _construct() {
		parent::_construct();
		$template = $this->getTemplate();
		if (empty($template)) {
			$this->setTemplate('ghgtm/default.phtml');
		}
	}


	/**
	 * Generate JavaScript for the data layer.
	 *
	 * @return string|null
	 */
	protected function _getDataLayer() {
		// Initialise our data source.
		$data = array();
		$dataScript = '';

		// Get transaction and visitor data.
		$data = $data + $this->_getTransactionData();
		$data = $data + $this->_getContextData();

		// Get transaction and visitor data, if desired.
		if (Mage::helper('gtm')->isDataLayerEnabled() && !empty($data)) {
			// Generate the data layer JavaScript.
			$dataScript .= "<script>dataLayer = [" . json_encode($data) . "];</script>\n\n";
		}
		// removed Spying part
		return $dataScript;
	}

	/**
	 * @return array
	 */
	public function getRawDataLayer() {
		if (!Mage::helper('gtm')->isGTMAvailable()) {
			return '';
		}
		$data = array();
		$data += $this->_getTransactionData() + $this->_getVisitorData();
		if (Mage::helper('gtm')->isDataLayerEnabled() && !empty($data)) {
			return json_encode($data);
		} else {
			return '';
		}
	}


	protected function _getContextData()
	{
		$data = array();

		//skip own stores
		/** @var Zolago_Common_Helper_Data $commonHlp */
		$commonHlp = Mage::helper('zolagocommon');
		if ($commonHlp->isOwnStore()) {
			return $data;
		}

		/** @var GH_GTM_Helper_Data $gtmHlp */
		$gtmHlp = Mage::helper("gh_gtm");
		$path = $gtmHlp->getContextPath();
		$allowedPaths = $gtmHlp->getAllowedContextPaths();

		if(in_array($path,$allowedPaths)) {
			/** @var Mage_Core_Helper_Url $urlHlp */
			$urlHlp = Mage::helper('core/url');
			$urlData = parse_url($urlHlp->getCurrentUrl());
			$urlPath = explode("/",$urlData['path']);

			if(isset($urlPath[1]) && $urlPath[1]) {
				$vendorKey = $urlPath[1];
				$vendorData = $gtmHlp->getVendorDataByUrlKey($vendorKey);

				if(count($vendorData) && isset($vendorData['vendor_name']) && isset($vendorData['vendor_type'])) {
					switch($vendorData['vendor_type']) {
						case Zolago_Dropship_Model_Vendor::VENDOR_TYPE_BRANDSHOP:
							$data['contextType'] = 'brandshop';
							break;
						case Zolago_Dropship_Model_Vendor::VENDOR_TYPE_STANDARD:
							$data['contextType'] = 'vendor';
							break;
					}
					$data['contextDetails'] = $vendorData['vendor_name'];
				}
			}

			if(!isset($data['contextType'])) {
				$data['contextType'] = 'general';
			}
		}
		return $data;
	}
}