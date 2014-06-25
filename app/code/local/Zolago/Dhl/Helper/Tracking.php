<?php
class Zolago_Dhl_Helper_Tracking extends Mage_Core_Helper_Abstract {

	/**
	 * Collect Dhl Tracking Data from Web Api Calls
	 * 
	 * @param array $trackIds
	 * 
	 * @return boolean
	 */
	public function collectDhlTracking($trackIds)
	{
		if (!Mage::helper('zolagodhl')->isDhlActive()) {
			Mage::helper('zolagodhl')->_log('DHL Service is not Active');
			return false;
		}
		
		$dhlClient = Mage::helper('zolagodhl')->startDhlClient();
		$processTracks = array();
		foreach ($trackIds as $_trackId => $_tracks) {
			foreach ($_tracks as $_track) {
				$result = $dhlClient->getTrackAndTraceInfo($_track->getTrackNumber());
				//Process Single Track and Trace Object
				$this->_processDhlTrackStatus($_track, $result);
				//Collect all Track and Trace Objects related to one PO
				if (empty($processTracks[$_track->getParentId()])) {
					$processTracks[$_track->getParentId()] = array();
				}
				$processTracks[$_track->getParentId()][] = $_track;
			}
		}
		//Process all Collected Shipments and update Shipment Status
		$result = $this->_processShipmentTracks($processTracks);
		return true;
	}
	
    /**
     * @param Mage_Sales_Model_Order_Shipment_Track $track
     * @param string $shipmentIdMessage
     * @param array $dhlMessage
     * @param string $status
     * @return boolean
     */
     protected function _addComment($track,$shipmentIdMessage,$dhlMessage,$status) {
		$comment = $this->__(Zolago_Dhl_Helper_Data::DHL_HEADER) . PHP_EOL;
		/* @var $shipment Mage_Sales_Model_Order_Shipment */
		$shipment		= $track->getShipment();
		$poId			= $shipment->getUdpoId();

		$comment .= $shipmentIdMessage;
		$dhlComment	= $this->_getPoOrderCommentsHistory($poId, $comment);
		$dhlMessage = array_reverse(array_unique($dhlMessage));
		foreach ($dhlMessage as $singleMessage) {
			$comment .= $singleMessage;
		}
		
		$comment = trim($comment);
		//Add Dhl T&T Comment to PO
		$dhlComment->setParentId($poId)
				->setComment($comment)
				->setCreatedAt(now())
				->setIsVisibleToVendor(true)
				->setUsername('API');
		
		//Add Dhl T&T Comment to related Shipment History
		$this->_addDhlShipmentComment(
			$track->getShipment(),
			$comment,
			$status
		);
		try {
			$dhlComment->save();
		} catch (Exception $e) {
			Mage::logException($e);
			return false;
		}
		
     }
	/**
	 * Process Single Dhl Track and Trace Record
	 * 
	 * @param type $track
	 * @param type $dhlResult
	 * 
	 * @return boolean
	 */
	protected function _processDhlTrackStatus($track, $dhlResult) {
		$dhlMessage = array();		$status			= $this->__('Ready to Ship');
		$shipmentIdMessage = '';
		$shipment		= $track->getShipment();
		$oldStatus = $track->getUdropshipStatus();
		if (is_array($dhlResult) && array_key_exists('error', $dhlResult)) {
			//Dhl Error Scenario
			Mage::helper('zolagodhl')->_log('DHL Service Error: ' .$dhlResult['error']);
			$dhlMessage[] = 'DHL Service Error: ' .$dhlResult['error'];
		} elseif (property_exists($dhlResult, 'getTrackAndTraceInfoResult') && property_exists($dhlResult->getTrackAndTraceInfoResult, 'events') && property_exists($dhlResult->getTrackAndTraceInfoResult->events, 'item')) {
			$shipmentIdMessage = $this->__('Tracking ID') . ': '. $dhlResult->getTrackAndTraceInfoResult->shipmentId . PHP_EOL;
			$events = $dhlResult->getTrackAndTraceInfoResult->events;
			//DHL: Concatenate T&T Message History
			$shipped = false;
			foreach ($events->item as $singleEvent) {
				$dhlMessage[$singleEvent->status] = 
						(!empty($singleEvent->receivedBy) ? $this->__('Received By: ') . $singleEvent->receivedBy . PHP_EOL : '')
						. $this->__('Description: ') . $singleEvent->description . PHP_EOL
						. $this->__('Terminal: ') . $singleEvent->terminal . PHP_EOL
						. $this->__('Time: ') . $singleEvent->timestamp . PHP_EOL.PHP_EOL;
				switch ($singleEvent->status) {
					case Zolago_Dhl_Helper_Data::DHL_STATUS_DELIVERED:
						$status = $this->__('Delivered');
						$track->setUdropshipStatus(Unirgy_Dropship_Model_Source::TRACK_STATUS_DELIVERED);
						$track->setShippedDate(Varien_Date::now());
						$track->getShipment()->setUdropshipStatus(Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_DELIVERED);
						$shipped = false;
						break;
					case Zolago_Dhl_Helper_Data::DHL_STATUS_RETURNED:
						$status = $this->__('Returned');
						$track->setUdropshipStatus(Unirgy_Dropship_Model_Source::TRACK_STATUS_CANCELED);
						$track->setShippedDate(null);
						$track->getShipment()->setUdropshipStatus(Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_RETURNED);
						$shipped = false;
						break;
					case Zolago_Dhl_Helper_Data::DHL_STATUS_WRONG:
						$status = $this->__('Canceled');
						$track->setUdropshipStatus(Unirgy_Dropship_Model_Source::TRACK_STATUS_CANCELED);
						$track->setShippedDate(null);
						$track->getShipment()->setUdropshipStatus(Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_RETURNED);
						$shipped = false;
						break;
					case Zolago_Dhl_Helper_Data::DHL_STATUS_SHIPPED:
					case Zolago_Dhl_Helper_Data::DHL_STATUS_SORT:
					case Zolago_Dhl_Helper_Data::DHL_STATUS_LP:
					case Zolago_Dhl_Helper_Data::DHL_STATUS_LK:
					case Zolago_Dhl_Helper_Data::DHL_STATUS_AWI:
					case Zolago_Dhl_Helper_Data::DHL_STATUS_BGR:
					case Zolago_Dhl_Helper_Data::DHL_STATUS_OP:
					    if (!$shipped) {
    					    $status = $this->__('Shipped');
	    					$track->setUdropshipStatus(Unirgy_Dropship_Model_Source::TRACK_STATUS_SHIPPED);
		    				$track->setShippedDate(Varien_Date::now());
			    			$track->getShipment()->setUdropshipStatus(Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_SHIPPED);
				    		$shipped = true;
						}
						break;
					default:					    
						break;
				}
			}
		} else {
			//DHL Scenario: No T&T Data Recieved
			Mage::helper('zolagodhl')->_log('DHL Service Error: Missing Track and Trace Data');
			$dhlMessage[] = $this->__('DHL Service Error: Missing Track and Trace Data');
		}
		if ($oldStatus != $track->getUdropshipStatus()) {
    		$this->_addComment($track,$shipmentIdMessage,$dhlMessage,$status);
        }
		if (!in_array($status, array($this->__('Delivered'), $this->__('Returned'), $this->__('Canceled')))) {
			$track->setNextCheck(Mage::helper('zolagodhl')->getNextDhlCheck($shipment->getOrder()->getStoreId()));
		}

		try {
			$track->setWebApi(true);
			$track->save();
			$track->getShipment()->save();			
		} catch (Exception $e) {
			Mage::logException($e);
			return false;
		}

		return true;
	}

    protected function _addDhlShipmentComment($shipment, $comment, $status = false, $visibleToVendor=true, $isVendorNotified=false, $isCustomerNotified=false)
    {		
		$commentModel = Mage::getResourceModel('sales/order_shipment_comment_collection')
			->setShipmentFilter($shipment->getId())
			->addFieldToFilter('comment', array('like' => '%'. Zolago_Dhl_Helper_Data::DHL_HEADER . '%'))
			->getFirstItem();
		$commentModel->setParentId($shipment->getId())
			->setComment($comment)
			->setIsCustomerNotified($isCustomerNotified)
			->setIsVendorNotified($isVendorNotified)
			->setIsVisibleToVendor($visibleToVendor)
			->setUdropshipStatus($status)
			->setCreatedAt(now());
        $commentModel->save();
        return $commentModel;
    }

	protected function _processShipmentTracks($shipmentTracks)
	{
		foreach ($shipmentTracks as $_sTracksId => $_sTracks) {
			$tracksCount = count($shipmentTracks[$_sTracksId]);
			foreach ($_sTracks as $_track) {
				switch ($tracksCount) {
					case Zolago_Dropship_Helper_Data::TRACK_SINGLE:
						$this->_processOrder($_track);
						break;
					default:
						$this->_processOrder($_sTracks, true);
						break;
				}
			}
		}		
	}
	
	protected function _processOrder($_sTracks, $multiple = false)
	{
		$completeOrder = true;
		
		if ($multiple) {
			//Check if all Shipments are Delivered and Update Order Status
			foreach ($_sTracks as $sTrack) {
				if ($sTrack->getUdropshipStatus() !== Unirgy_Dropship_Model_Source::TRACK_STATUS_DELIVERED) {
					$completeOrder = false;
				}
			}
		} else {
			$sTrack = $_sTracks;
			$shipmentStatus = $sTrack->getUdropshipStatus();
			$shipment = $sTrack->getShipment();
			switch ($shipmentStatus) {
				case Unirgy_Dropship_Model_Source::TRACK_STATUS_DELIVERED:
					$this->_setOrderCompleteState($shipment);
					break;
				default:
					$completeOrder = false;
					break;
			}
		}
		
		if ($completeOrder) {
			//Set Order Status based on all related shipments
			$this->_setOrderCompleteState($sTrack->getShipment());
		}
		
		return $this;
	}
	
	protected function _setOrderCompleteState($shipment)
	{
		$order = $shipment->getOrder();
		$order->setStatus(Mage_Sales_Model_Order::STATE_COMPLETE)
			->setUdropshipStatus(Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_DELIVERED);					
		$poOrderId = $shipment->getUdpoId();
		$poOrder = Mage::getModel('udpo/po')->load($poOrderId);
		$poOrder->setStatus(Mage_Sales_Model_Order::STATE_COMPLETE)
			->setUdropshipStatus(Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_DELIVERED);
		
		try {
			$order->save();
		} catch (Exception $e) {
			Mage::logException($e);
			return false;
		}

		try {
			$poOrder->save();
		} catch (Exception $e) {
			Mage::logException($e);
			return false;
		}
	}
	
	protected function _getPoOrderCommentsHistory($poId, $commentFilter = false, $firstItem = true) {
		($commentFilter) ? $commentFilter : Zolago_Dhl_Helper_Data::DHL_HEADER;
		$comments = Mage::getModel('udpo/po_comment')
						->getCollection()
						->setPoFilter($poId)
						->addFieldToFilter('comment', array('like' => '%'. $commentFilter . '%'));
				
		if ($firstItem) {
			$comments = $comments->getFirstItem();
		}		
		
		return $comments;
	}

}

