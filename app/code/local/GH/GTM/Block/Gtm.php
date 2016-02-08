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
		$data = $data + $this->_getVisitorData();

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

	protected function _getVisitorData()
	{
		$data = array();
		/** @var Zolago_Customer_Model_Session $customerSession */
		$customerSession = Mage::getSingleton('customer/session');

		// visitorHasSubscribed - by default 'no'
		$data['visitorHasSubscribed'] = 'no';

		// visitorId, visitorHasAccount
		$customerId = $customerSession->getCustomerId();
		if($customerId) {
			$data['visitorId'] = (string)$customerId;
			$data['visitorHasAccount'] = 'yes';

			//visitorHasSubscribed
			$resource = Mage::getSingleton('core/resource');
			$readConnection = $resource->getConnection('core_read');

			$query =
				'SELECT `subscriber_status` FROM `' .
				$resource->getTableName('newsletter/subscriber') .
				'` WHERE `customer_id` = ' . $customerId;

			$result = $readConnection->fetchAll($query);
			if(count($result)) {
				$newsletterStatus = current(current($result));
				switch($newsletterStatus) {
					case Zolago_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED:
						$data['visitorHasSubscribed'] = 'yes';
						break;
					case Zolago_Newsletter_Model_Subscriber::STATUS_UNSUBSCRIBED:
						$data['visitorHasSubscribed'] = 'unsubscribed';
						break;
				}
			}
		} else {
			$data['visitorHasAccount'] = 'no';
		}

		//visitorLogged
		$data['visitorLogged'] = $customerSession->isLoggedIn() ? 'yes' : 'no';

		return $data;
	}
}