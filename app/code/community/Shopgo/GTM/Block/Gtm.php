<?php
/**
 * Google Tag Manager Block
 *
 * @category    ShopGo
 * @package     Shopgo_GTM
 * @author      Ali Halabyah <ali@shopgo.me>
 * @copyright   Copyright (c) 2014 ShopGo
 * @license     http://opensource.org/licenses/osl-3.0.php Open Software License 3.0 (OSL-3.0)
 */
class Shopgo_GTM_Block_Gtm extends Mage_Core_Block_Template
{
	/**
	 * Return The GTM container id.
	 *
	 * @return string
	 */
	protected function _getContainerId()
	{
		// Get the container ID.
		$containerId = Mage::helper('gtm')->getContainerId();

		// Return the container id.
		return $containerId;
	}

	/**
	 * Generate JavaScript for the data layer.
	 *
	 * @return string|null
	 */
	protected function _getDataLayer()
	{
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

		// Generate shopgoStoresDataLayer JavaScript.
		if (!empty($data)) {
			$dataScript .= "<script>shopgoStoresDataLayer = [" . json_encode($data) . "];</script>\n\n";
		}
		return $dataScript;
	}

	/**
	 * Get transaction data for use in the data layer.
	 *
	 * @link https://developers.google.com/tag-manager/reference
	 * @return array
	 */
	protected function _getTransactionData()
	{
		$data = array();

		$orderIds = $this->getOrderIds();
		if (empty($orderIds) || !is_array($orderIds)) return array();

		$collection = Mage::getResourceModel('sales/order_collection')->addFieldToFilter('entity_id', array('in' => $orderIds));

		$i = 0;
		$products = array();

		foreach ($collection as $order) {
			if ($i == 0) {
				// Build all fields for first order.
				$data = array(
					'transactionId' => $order->getIncrementId(),
					'transactionDate' => date("Y-m-d"),
					'transactionType' => Mage::helper('gtm')->getTransactionType(),
					'transactionAffiliation' => Mage::helper('gtm')->getTransactionAffiliation(),
					'transactionTotal' => round($order->getBaseGrandTotal(),2),
					'transactionShipping' => round($order->getBaseShippingAmount(),2),
					'transactionTax' => round($order->getBaseTaxAmount(),2),
					'transactionPaymentType' => $order->getPayment()->getMethodInstance()->getTitle(),
					'transactionCurrency' => Mage::app()->getStore()->getBaseCurrencyCode(),
					'transactionShippingMethod' => $order->getShippingCarrier() ? $order->getShippingCarrier()->getCarrierCode() : 'No Shipping Method',
					'transactionPromoCode' => $order->getCouponCode(),
					'transactionProducts' => array()
				);
			} else {
				// For subsequent orders, append to order ID, totals and shipping method.
				$data['transactionId'] .= '|' . $order->getIncrementId();
				$data['transactionTotal'] += $order->getBaseGrandTotal();
				$data['transactionShipping'] += $order->getBaseShippingAmount();
				$data['transactionTax'] += $order->getBaseTaxAmount();
				$data['transactionShippingMethod'] .= '|' . $order->getShippingCarrier() ? $order->getShippingCarrier()->getCarrierCode() : 'No Shipping Method';
			}

			// Build products array.
			foreach ($order->getAllVisibleItems() as $item) {
				$product = $item->getProduct();
				$product_categories = array();
				try {
					$product_categories = $product->getCategoryIds();
				} catch (Mage_Exception $e) {
					// todo
				}
				$categories = array();
				foreach ($product_categories as $category) {
					$categories[] = Mage::getModel('catalog/category')->load($category)->getName();
				}
				if (empty($products[$item->getSku()])) {
					// Build all fields the first time we encounter this item.
					$products[$item->getSku()] = array(
						'name' => $this->jsQuoteEscape(Mage::helper('core')->escapeHtml($item->getName())),
						'sku' => $this->jsQuoteEscape(Mage::helper('core')->escapeHtml($item->getSku())),
						'category' => implode('|',$categories),
						'price' => (double)number_format($item->getBasePrice(),2,'.',''),
						'quantity' => (int)$item->getQtyOrdered()
					);
				} else {
					// If we already have the item, update quantity.
					$products[$item->getSku()]['quantity'] += (int)$item->getQtyOrdered();
				}
			}

			$i++;
		}

		// Push products into main data array.
		foreach ($products as $product) {
			$data['transactionProducts'][] = $product;
		}

		// Trim empty fields from the final output.
		foreach ($data as $key => $value) {
			if (!is_numeric($value) && empty($value)) unset($data[$key]);
		}

		return $data;
	}

	/**
	 * Get visitor data for use in the data layer.
	 *
	 * @link https://developers.google.com/tag-manager/reference
	 * @return array
	 */
	protected function _getVisitorData()
	{
		$data = array();
		/** @var Mage_Customer_Model_Session $customerSession */
		$customerSession = Mage::getSingleton('customer/session');

		/** @var Mage_Persistent_Helper_Session $persistentSession */
		$persistentSession = Mage::helper("persistent/session");

		// visitorId
		/** @var Zolago_Customer_Model_Customer $customer */
		if ($customerSession->getCustomerId()) {
			$customer = $customerSession->getCustomer();
		} elseif($persistentSession->isPersistent()) {
			$customer = $persistentSession->getCustomer();
		}

		if(isset($customer) && $customer->getId()) {
			$data['visitorId'] = (string)$customer->getId();
			$data['visitorHasAccount'] = 'yes';

			/** @var Zolago_Newsletter_Model_Subscriber $subscriber */
			$subscriber = Mage::getModel('newsletter/subscriber');
			$subscriber->loadByCustomer($customer);
			if($subscriber->getId()) {
				switch($subscriber->getStatus()) {
					case Zolago_Newsletter_Model_Subscriber::STATUS_SUBSCRIBED:
						$data['visitorHasSubscribed'] = 'yes';
						break;
					case Zolago_Newsletter_Model_Subscriber::STATUS_UNSUBSCRIBED:
						$data['visitorHasSubscribed'] = 'unsubscribed';
						break;

					//case Zolago_Newsletter_Model_Subscriber::STATUS_NOT_ACTIVE:
					//case Zolago_Newsletter_Model_Subscriber::STATUS_UNCONFIRMED:
					default:
						$data['visitorHasSubscribed'] = 'no';
						break;
				}
			}
		} else {
			$data['visitorHasAccount'] = 'no';
		}

		if(!isset($data['visitorHasSubscribed'])) {
			$data['visitorHasSubscribed'] = 'no';
		}

		//visitorLogged
		$data['visitorLogged'] = ($customerSession->isLoggedIn()) ? 'yes' : 'no';

		// visitorType
		/*$data['visitorType'] = (string)Mage::getModel('customer/group')->load($customer->getCustomerGroupId())->getCode();*/

		// visitorExistingCustomer / visitorLifetimeValue
/*		$orders = Mage::getResourceModel('sales/order_collection')->addFieldToSelect('*')->addFieldToFilter('customer_id',$customer->getId());
		$ordersTotal = 0;
		foreach ($orders as $order) {
			$ordersTotal += $order->getGrandTotal();
		}
		$data['visitorLifetimeValue'] = round($ordersTotal,2);
		$data['visitorExistingCustomer'] = ($ordersTotal > 0) ? 'Yes' : 'No';*/

		return $data;
	}

	/**
	 * Render Google Tag Manager code
	 *
	 * @return string
	 */
	protected function _toHtml()
	{
		// if (!Mage::helper('gtm')->isGTMAvailable()) return '';
		return parent::_toHtml();
	}
}