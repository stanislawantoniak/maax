<?php
class Zolago_Dropship_Helper_Data extends Unirgy_Dropship_Helper_Data {
	const TRACK_SINGLE			= 1;

	public function isUdpoMpsAvailable($carrierCode, $vendor = null) {
		if(in_array($carrierCode, array("zolagodhl"))){
			return true;
		}
		return parent::isUdpoMpsAvailable($carrierCode, $vendor);
	}
	
    /**
     * Poll carriers tracking API
     *
     * @param mixed $tracks
     */
    public function collectTracking($tracks)
    {
        $requests = array();
        foreach ($tracks as $track) {
            $cCode = $track->getCarrierCode();
            if (!$cCode) {
                continue;
            }
            $vId = $track->getShipment()->getUdropshipVendor();
            $v = Mage::helper('udropship')->getVendor($vId);
			if ($cCode !== 'zolagodhl') {
				if (!$v->getTrackApi($cCode) || !$v->getId()) {
					continue;
				}
			}
			
			if (!$vId) {
				continue;
			}
			
			$requests[$cCode][$vId][$track->getNumber()][] = $track;
        }

        foreach ($requests as $cCode => $vendors) {
            foreach ($vendors as $vId => $trackIds) {
                $_track = null;
                foreach ($trackIds as $_trackId=>$_tracks) {
                    foreach ($_tracks as $_track) break 2;
                }
                try {
                    if ($_track) Mage::helper('udropship/label')->beforeShipmentLabel($v, $_track);
					if ($cCode !== 'zolagodhl') {
						$result = $v->getTrackApi($cCode)->collectTracking($v, array_keys($trackIds));
					} else {
						$result = $this->collectDhlTracking($trackIds);
					}
                    if ($_track) Mage::helper('udropship/label')->afterShipmentLabel($v, $_track);
                } catch (Exception $e) {
                    if ($_track) Mage::helper('udropship/label')->afterShipmentLabel($v, $_track);
                    $this->_processPollTrackingFailed($trackIds, $e);
                    continue;
                }

                $processTracks = array();
                foreach ($result as $trackId=>$status) {
                    foreach ($trackIds[$trackId] as $track) {

                        if (in_array($status, array(Unirgy_Dropship_Model_Source::TRACK_STATUS_PENDING,Unirgy_Dropship_Model_Source::TRACK_STATUS_READY,Unirgy_Dropship_Model_Source::TRACK_STATUS_SHIPPED))) {
                            $repeatIn = Mage::getStoreConfig('udropship/customer/repeat_poll_tracking', $track->getShipment()->getOrder()->getStoreId());
                            if ($repeatIn<=0) {
                                $repeatIn = 12;
                            }
                            $repeatIn = $repeatIn*60*60;
                            $track->setNextCheck(date('Y-m-d H:i:s', time()+$repeatIn))->save();
                            if ($status==Unirgy_Dropship_Model_Source::TRACK_STATUS_PENDING) continue;
                        }

                        $track->setUdropshipStatus($status);
                        if ($track->dataHasChangedFor('udropship_status')) {
                            switch ($status) {
                            case Unirgy_Dropship_Model_Source::TRACK_STATUS_READY:
                                Mage::helper('udropship')->addShipmentComment(
                                    $track->getShipment(),
                                    $this->__('Tracking ID %s was picked up from %s', $trackId, $v->getVendorName())
                                );
                                $track->getShipment()->save();
                                break;

                            case Unirgy_Dropship_Model_Source::TRACK_STATUS_DELIVERED:
                                Mage::helper('udropship')->addShipmentComment(
                                    $track->getShipment(),
                                    $this->__('Tracking ID %s was delivered to customer', $trackId)
                                );
                                $track->getShipment()->save();
                                break;
                            }
                            if (empty($processTracks[$track->getParentId()])) {
                                $processTracks[$track->getParentId()] = array();
                            }
                            $processTracks[$track->getParentId()][] = $track;
                        }
                    }
                }
                foreach ($processTracks as $_pTracks) {
                    try {
                        $this->processTrackStatus($_pTracks, true);
                    } catch (Exception $e) {
                        $this->_processPollTrackingFailed($_pTracks, $e);
                        continue;
                    }
                }
            }
        }
    }
	
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
				$processTracks[$_track->getOrderId()][] = $_track;
			}
		}
		
		//Process all Collected Shipments and update Shipment Status
		$this->_processShipmentTracks($processTracks);
		return true;
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
		$dhlMessage = array();
		$comment = $this->__(Zolago_Dhl_Helper_Data::DHL_HEADER) . PHP_EOL;
		/* @var $shipment Mage_Sales_Model_Order_Shipment */
		$shipment		= $track->getShipment();
		$poId			= $shipment->getUdpoId();
		$status			= $this->__('Ready to Ship');

		if (is_array($dhlResult) && array_key_exists('error', $dhlResult)) {
			Mage::helper('zolagodhl')->_log('DHL Service Error: ' .$dhlResult['error']);
			$dhlMessage[] = 'DHL Service Error: ' .$dhlResult['error'];
		} elseif (property_exists($dhlResult, 'getTrackAndTraceInfoResult') && property_exists($dhlResult->getTrackAndTraceInfoResult, 'events') && property_exists($dhlResult->getTrackAndTraceInfoResult->events, 'item')) {
			$shipmentIdMessage = $this->__('Tracking ID') . ': '. $dhlResult->getTrackAndTraceInfoResult->shipmentId . PHP_EOL;
			$events = $dhlResult->getTrackAndTraceInfoResult->events;
			foreach ($events->item as $singleEvent) {
				$dhlMessage[$singleEvent->status] = 
						(($singleEvent->receivedBy) ? $this->__('Received By: ') . $singleEvent->receivedBy . PHP_EOL : '')
						. $this->__('Description: ') . $singleEvent->description . PHP_EOL
						. $this->__('Terminal: ') . $singleEvent->terminal . PHP_EOL
						. $this->__('Time: ') . $singleEvent->timestamp . PHP_EOL.PHP_EOL;

				switch ($singleEvent->status) {
					case Zolago_Dhl_Helper_Data::DHL_STATUS_DELIVERED:
						$status = $this->__('Delivered');
						$track->setUdropshipStatus(Unirgy_Dropship_Model_Source::TRACK_STATUS_DELIVERED);
						$track->getShipment()->setUdropshipStatus(Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_DELIVERED);
						break;
					case Zolago_Dhl_Helper_Data::DHL_STATUS_RETURNED:
						$status = $this->__('Returned');
						$track->setUdropshipStatus(Unirgy_Dropship_Model_Source::TRACK_STATUS_CANCELED);
						$track->getShipment()->setUdropshipStatus(Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_RETURNED);
						break;
					case Zolago_Dhl_Helper_Data::DHL_STATUS_WRONG:
						$status = $this->__('Canceled');
						$track->setUdropshipStatus(Unirgy_Dropship_Model_Source::TRACK_STATUS_CANCELED);
						$track->getShipment()->setUdropshipStatus(Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_RETURNED);
						break;
					default:
						break;
				}
			}
		} else {
			Mage::helper('zolagodhl')->_log('DHL Service Error: Missing Track and Trace Data');
			$dhlMessage[] = $this->__('DHL Service Error: Missing Track and Trace Data');
		}

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

		try {
			$dhlComment->save();
		} catch (Exception $e) {
			Mage::logException($e);
			return false;
		}
		return true;
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
					case self::TRACK_SINGLE:
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
}