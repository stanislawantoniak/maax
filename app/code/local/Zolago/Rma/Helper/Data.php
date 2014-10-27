<?php

class Zolago_Rma_Helper_Data extends Unirgy_Rma_Helper_Data {

	/**
	 * @param Zolago_Rma_Model_Rma $rma
	 * @param string $status
	 * @param bool | null $notify
	 * @return Zolago_Rma_Model_Rma
	 */
	public function processSaveStatus(Zolago_Rma_Model_Rma $rma, $status, $notify=null) {
		$oldStatus = $rma->getRmaStatus();
		if ($status != $oldStatus) {
			$rma->setRmaStatus($status);
			$rma->getResource()->saveAttribute($rma, 'rma_status');
			// Trigger event
			
			Mage::log("Status logged helper: " . var_export($notify, true));
			Mage::dispatchEvent("zolagorma_rma_status_changed", array(
				"rma" => $rma,
				"notify" => $notify,
				"new_status" => $status,
				"old_status" => $oldStatus
			));
		}
		return $rma;
	}

	/**
	 *
	 * @param type $items
	 * @return type
	 */
	public function getItemList($items) {
		$out = array();
		$child = array();
		foreach ($items as $item) {
			if ($parentId = $item->getParentItemId()) {
				$child[$parentId][] = $item;
			}
		}
		foreach ($items as $item) {
			$max = intval($item->getQty());
			if (!$item->getParentItemId()) {
				for ($a = 0; $a < $max; $a++) {
					$entity_id = $item->getEntityId();
					if (!empty($child[$item->getOrderItemId()])) {
						$name = '';
						foreach ($child[$item->getOrderItemId()] as $ch) {
							$name .= $ch->getName();
						}
					} else {
						$name = $item->getName();
					}
					$out[$entity_id][$a] = array(
						'entityId' => $entity_id,
						'name' => $name,
					);
				}
			}
		}
		return $out;
	}

	/**
	 * tracking rma
	 */
	public function rmaTracking() {
		$statusFilter = array(Unirgy_Dropship_Model_Source::TRACK_STATUS_PENDING, Unirgy_Dropship_Model_Source::TRACK_STATUS_READY, Unirgy_Dropship_Model_Source::TRACK_STATUS_SHIPPED);
		$res = Mage::getSingleton('core/resource');
		$conn = $res->getConnection('sales_read');

		$sIdsSel = $conn->select()->distinct()
				->from($res->getTableName('urma/rma_track'), array('parent_id'))
				->where('udropship_status in (?)', $statusFilter)
				->where('next_check<=?', now())
				->limit(50);
		$sIds = $conn->fetchCol($sIdsSel);
		if (!empty($sIds)) {
			$tracks = Mage::getModel('urma/rma_track')->getCollection()
					->addAttributeToSelect('*')
					->addAttributeToFilter('udropship_status', array('in' => $statusFilter))
					->addAttributeToFilter('parent_id', array('in' => $sIds))
					->addAttributeToSort('parent_id')
			;
			$helper = Mage::helper('udropship');
			$helper->setTrackingHelperPath('zolagorma/tracking');
			$helper->collectTracking($tracks);
		}
	}

	/**
	 * @return array
	 */
	public function getItemConditionTitles() {
		$collection = Mage::getModel('zolagorma/rma_reason')->getCollection();
		return $collection->toOptionHash();
	}

	/**
	 * @param Zolago_Po_Model_Po $po
	 * @param boolean $to_json
	 * 
	 * @return json|array
	 */
	public function getReturnReasons($po, $to_json) {

		$reasons_array = array();

		$vendor = $po->getVendor();

		$vendor_reasons = Mage::getModel('zolagorma/rma_reason_vendor')->getCollection()
				->addFieldToFilter('vendor_id', $vendor->getId());

		if ($vendor_reasons->count() > 0) {

			foreach ($vendor_reasons as $vendor_reason) {

				$return_reason_id = $vendor_reason->getReturnReasonId();

				$days_elapsed = $this->getDaysElapsed($return_reason_id, $po);

				//Get message based on use_default flag
				$message = ($vendor_reason->getUseDefault()) ? $vendor_reason->getReturnReason()->getMessage() : $vendor_reason->getMessage();

				//Get auto_days based on use_default flag
				$auto_days = ($vendor_reason->getUseDefault()) ? $vendor_reason->getReturnReason()->getAutoDays() : $vendor_reason->getAutoDays();

				//Get allowed_days based on use_default flag
				$allowed_days = ($vendor_reason->getUseDefault()) ? $vendor_reason->getReturnReason()->getAllowedDays() : $vendor_reason->getAllowedDays();

				$is_reason_available = ($days_elapsed >= $allowed_days) ? false : true;


				$reasons_array[$return_reason_id] = array(
					'isAvailable' => $is_reason_available,
					'days_elapsed' => $days_elapsed,
					'flow' => $this->getFlow($vendor_reason, $days_elapsed),
					'auto_days' => $auto_days,
					'allowed_days' => $allowed_days,
					'message' => $message
				);
			}
		}

		return ($to_json) ? json_encode($reasons_array) : $reasons_array;
	}

	/**
	 * @param int $return_reason_id
	 * @param Zolago_Po_Model_Po $po
	 * 
	 * @return float | boolean
	 */
	public function getDaysElapsed($return_reason_id, $po) {

		$vendor = $po->getVendor();
		$order = $po->getOrder();

		$reason_vendor = Mage::getModel('zolagorma/rma_reason_vendor')->getCollection()
				->addFieldToFilter('return_reason_id', $return_reason_id)
				->addFieldToFilter('vendor_id', $vendor->getId())
				->getFirstItem();

		if ($reason_vendor) {

			//now
			$time_now = new Zend_Date();
			$track = Mage::getModel('sales/order_shipment_track')->getCollection()
					->addFieldToFilter('order_id', $order->getId())
					->getFirstItem();
			if (!$track->getId()) {
				return false;
			}

			$shipped_date = $track->getShippedDate();

			// Get default value as a date of creation of tracking
			if (!$shipped_date)
				$shipped_date = $track->getCreatedAt();

			$time_then = new Zend_Date($shipped_date);
			$difference = $time_now->sub($time_then);

			$measure = new Zend_Measure_Time($difference->toValue(), Zend_Measure_Time::SECOND);
			$measure->convertTo(Zend_Measure_Time::DAY);

			return (float) $measure->getValue();
		}

		return NULL;
	}

	/**
	 * Get flow number based on days elapsed
	 * 
	 * @param Zolago_Rma_Model_Resource_Rma_Reason_Vendor $vendor_reason
	 * @param int $days_elasped
	 * 
	 * @return int | false
	 */
	public function getFlow($vendor_reason, $days_elapsed) {

		$auto_days = $vendor_reason->getAutoDays();
		$allowed_days = $vendor_reason->getAllowedDays();

		if ($days_elapsed < $auto_days) {
			return Zolago_Rma_Model_Rma::FLOW_INSTANT;
		} else if ($days_elapsed <= $allowed_days) {
			return Zolago_Rma_Model_Rma::FLOW_ACKNOWLEDGED;
		} else {
			return false;
		}
	}

	public function sendNewRmaNotificationEmail($rma, $comment = '') {
		$order = $rma->getOrder();
		$store = $order->getStore();

		$vendor = $rma->getVendor();

		$hlp = Mage::helper('udropship');
		$data = array();

		$hlp->setDesignStore($store);
		$shippingAddress = $order->getShippingAddress();
		if (!$shippingAddress) {
			$shippingAddress = $order->getBillingAddress();
		}
		$data += array(
			'rma' => $rma,
			'order' => $order,
			'vendor' => $vendor,
			'comment' => $comment,
			'is_admin_comment' => $comment && $rma->getIsAdmin(),
			'is_customer_comment' => $comment && $rma->getIsCustomer(),
			'store_name' => $store->getName(),
			'vendor_name' => $vendor->getVendorName(),
			'rma_id' => $rma->getIncrementId(),
			'order_id' => $order->getIncrementId(),
			'customer_info' => Mage::helper('udropship')->formatCustomerAddress($shippingAddress, 'html', $vendor),
			'rma_url' => Mage::getUrl('urma/vendor/', array('_query' => 'filter_rma_id_from=' . $rma->getIncrementId() . '&filter_rma_id_to=' . $rma->getIncrementId())),
		);

		$template = $store->getConfig('urma/general/new_rma_vendor_email_template');
		$identity = $store->getConfig('udropship/vendor/vendor_email_identity');


		$emailM = Mage::getModel('udropship/email');
		$data['_BCC'] = $vendor->getNewOrderCcEmails();
		if (($emailField = $store->getConfig('udropship/vendor/vendor_notification_field'))) {
			$email = $vendor->getData($emailField) ? $vendor->getData($emailField) : $vendor->getEmail();
			$data['recepient'] = $vendor->getVendorName();
			$emailM->sendTransactional($template, $identity, $email, $vendor->getVendorName(), $data);
		} else {
//            $email = $vendor->getEmail();
			//Send Email to vendor agents of super vendor
			$vendorM = Mage::getResourceModel('udropship/vendor');
			$superVendorAgents = $vendorM->getSuperVendorAgentEmails($vendor->getId());
			if (!empty($superVendorAgents)) {
				foreach ($superVendorAgents as $email => $_) {
					$data['recepient'] = implode(' ', array($_['firstname'], $_['lastname']));
					$emailM
							->sendTransactional($template, $identity, $email, $vendor->getVendorName(), $data);
				}
			}
			//Send Email to vendor agents of vendor
			$vendorAgents = $vendorM->getVendorAgentEmails($vendor->getId());
			if (!empty($vendorAgents)) {
				foreach ($vendorAgents as $email => $_) {
					$data['recepient'] = implode(' ', array($_['firstname'], $_['lastname']));
					$emailM
							->sendTransactional($template, $identity, $email, $vendor->getVendorName(), $data);
				}
			}
		}



		$hlp->setDesignStore();
	}

}
