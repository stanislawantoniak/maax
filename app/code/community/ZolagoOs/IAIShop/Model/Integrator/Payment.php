<?php
/**
 * orders integrator for one vendor
 */
class ZolagoOs_IAIShop_Model_Integrator_Payment extends Varien_Object {
	protected $_vendor;
	protected $_token;
	protected $_connector;
	protected $_confirmOrderList;
	protected $_helper;

	const IAISHOP_SYNC_ORDERS_BATCH = 100;

	public function getHelper() {
		if (!$this->_helper) {
			$this->_helper = Mage::helper('zosiaishop');
		}
		return $this->_helper;
	}

	public function setVendor($vendor) {
		$this->_vendor = $vendor;
	}

	public function setConnector($connector) {
		$this->_connector = $connector;
	}

	public function getVendor() {
		return $this->_vendor;
	}
	public function getConnector() {
		return $this->_connector;
	}
	/**
	 * prepare session for vendor
	 *
	 * @return string
	 */

	protected function _getToken() {
		$vendor = $this->getVendor();
		$id = $vendor->getId();
		if (empty($this->_token)) {
			$session = Mage::getModel('ghapi/session');
			$token = $session->generateToken($id);
			$ghapiUser = Mage::getModel('ghapi/user')->loadByVendorId($id);
			if (!$ghapiUser->getId()) {
				Mage::throwException(Mage::helper('zosiaishop')->__('GH Api user for vendor %s does not exists',$vendor->getName()));
			}
			// set session for api
			$session->setUserId($ghapiUser->getId())
				->setToken($token)
				->setCreatedAt(Mage::helper('ghapi')->getDate())
				->save();
			$this->_token = $token;
		}
		return $this->_token;
	}

	/**
	 * prepare new orders list
	 *
	 * @return array
	 */

	public function getGhApiVendorPayments()
	{
		$token = $this->_getToken();
		$connector = $this->getConnector();
		$params = new StdClass();
		$params->sessionToken = $token;
		$params->messageBatchSize = self::IAISHOP_SYNC_ORDERS_BATCH ;
		$params->messageType = GH_Api_Model_System_Source_Message_Type::GH_API_MESSAGE_PAYMENT_DATA_CHANGED;
		return $connector->getChangeOrderMessage($params);
	}
	/**
	 * process response from iaishop
	 */
	public function processResponse($response, $orderId) {
		if (!$response->errors->faultCode) {
			if (!empty($response->result->payment_id)) {
				$po = Mage::getModel('udpo/po')->loadByIncrementId($orderId);
				if ($po) {
					$this->addOrderToConfirmMessage($orderId);
					$this->log($this->getHelper()->__('Payment for order %s was imported to IAI Shop with number %s',$orderId,$response->result->payment_id));
				} else {
					$this->log($this->getHelper()->__('Order %s does not exists',$orderId));
				}
			} else {
				$this->getHelper()->fileLog($response->result);
				$this->log($this->getHelper()->__('IAI Api payment has not serial number for order %s',$orderId));
			}
		} else {
			$this->getHelper()->fileLog($response->errors);
			$this->log($this->getHelper()->__('IAI Api Error %d at order %s: %s',$response->errors->faultCode,$orderId,$response->errors->faultString));
		}

	}
	/**
	 * sync orders
	 */
	public function sync() {

		$payments = $this->getGhApiVendorPayments();
		$iaiConnector = Mage::getModel("zosiaishop/client_connector");
		$iaiConnector->setVendorId($this->getVendor()->getId());

		$paymentForms = $iaiConnector->getPaymentForms()->result->payment_forms;

		if ($payments->status) {
			foreach ($this->prepareOrderList($payments->list) as $item) {
				if (!empty($item->external_order_id) && floatval($item->order_due_amount) == 0) {

					$item->payment_method_external_id = array_values(array_filter(
						$paymentForms,
						function ($e) use (&$item) {
							return $e->name == $this->getHelper()->getMappedPaymentForPayments($item->payment_method);
    					}
					))[0]->id;

					$response = $iaiConnector->addPayment($item);

					Zend_Debug::dump($response);

					if (!empty($response->result->payment_id)) {
						$this->processResponse($response,$item->order_id);
					}
				}
			}
			$this->confirmMessages($payments->list);
		}

	}

	/**
	 * confirm messages from list
	 */
	public function confirmMessages($list) {
		$toConfirm = array();
		foreach ($list as $msg) {
			if (($msg->orderID) && isset($this->_confirmOrderList[$msg->orderID])) {
				$toConfirm[] = $msg->messageID;
			}
		}
		if (count($toConfirm)) {
			$connector = $this->getConnector();
			$token = $this->_getToken();
			$params = new StdClass();
			$params->sessionToken = $token;
			$params->messageID = new StdClass();
			$params->messageID->ID = $toConfirm;
			$connector->setChangeOrderMessageConfirmation($params);
		}
	}
	/**
	 * complete message list to confirm
	 */
	public function addOrderToConfirmMessage($orderId) {
		$this->_confirmOrderList[$orderId] = $orderId;
	}

	/**
	 * prepare orders details
	 */
	public function prepareOrderList($list) {
		$ids = array();
		foreach ($list as $item) {
			$ids[] = $item->orderID;
		}
		if (!count($ids)) {
			return array();
		}
		$token = $this->_getToken();
		$params = new StdClass();
		$params->sessionToken = $token;
		$params->orderID = new StdClass();
		$params->orderID->ID = array_unique($ids);
		$connector = $this->getConnector();
		$list = $connector->getOrdersById($params);
		if ($list->status) {
			return $list->orderList;
		}
		return array();
	}

	/**
	 * logs
	 *
	 * @param string
	 */

	public function log($mess) {
		$vendorId = $this->getVendor()->getId();
		$this->getHelper()->log($vendorId,$mess);
	}
}