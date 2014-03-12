<?php
class Zolago_Dropship_Helper_Data extends Unirgy_Dropship_Helper_Data {
	protected $_dhlLogFile = 'dhl_tracking.log';
	protected $_dhlClient;
	protected $_dhlLogin;
	protected $_dhlPassword;
	
	const DHL_STATUS_DELIVERED	= 'DOR';
	const DHL_STATUS_RETURNED	= 'ZWN';
	const DHL_STATUS_WRONG		= 'AN';
	const DHL_HEADER				= 'DHL Tracking Info';

	public function isUdpoMpsAvailable($carrierCode, $vendor = null) {
		if(in_array($carrierCode, array("zolagodhl"))){
			return true;
		}
		return parent::isUdpoMpsAvailable($carrierCode, $vendor);
	}
	
	public function _log($message, $logFile = false) {
		if (!$logFile) {
			$logFile = $this->_dhlLogFile;
		}
		
		Mage::log($message, null, $logFile, true);
	}
	
	public function isDhlActive()
	{
		return Mage::getStoreConfig('carriers/zolagodhl/active');		
	}
	
	public function getDhlLogin()
	{
		return trim(Mage::getStoreConfig('carriers/zolagodhl/id'));		
	}
	
	public function getDhlPassword()
	{
		return trim(Mage::getStoreConfig('carriers/zolagodhl/password'));		
	}
	
	public function getNextDhlCheck($storeId)
	{
		$repeatIn = Mage::getStoreConfig('carriers/zolagodhl/repeat_tracking', $storeId);
		if ($repeatIn <= 0) {
			$repeatIn = 1;
		}
		$repeatIn = $repeatIn*60*60;
		return date('Y-m-d H:i:s', time()+$repeatIn);		
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
	
	public function collectDhlTracking($trackIds)
	{
		if (!$this->isDhlActive()) {
			$this->_log('DHL Service is not Active');
			return false;
		}
		
		if ($this->_dhlLogin === null || $this->_dhlPassword === null || $this->_dhlClient === null) {
			$this->_dhlLogin	= $this->getDhlLogin();
			$this->_dhlPassword	= $this->getDhlPassword();
			
			$dhlClient			= Mage::getModel('zolagodhl/client');
			$dhlClient->setAuth($this->_dhlLogin, $this->_dhlPassword);
			$this->_dhlClient	= $dhlClient;
		}
		
		foreach ($trackIds as $_trackId => $_tracks) {
			foreach ($_tracks as $_track) {			
				$result = $this->_dhlClient->getTrackAndTraceInfo($_track->getTrackNumber());
				$this->_processDhlTrackStatus($_track, $result);				
			}
		}		

		return true;
	}
	
	protected function _processDhlTrackStatus($track, $dhlResult) {
		$saveAction = false;
		$dhlMessage = array();
		$comment = $this->__(self::DHL_HEADER) . PHP_EOL;
		/* @var $shipment Mage_Sales_Model_Order_Shipment */
		$shipment		= $track->getShipment();
		$poId			= $shipment->getUdpoId();
		$dhlComment		= $this->_getPoOrderCommentsHistory($poId);

		if (is_array($dhlResult) && array_key_exists('error', $dhlResult)) {
			$this->_log('DHL Service Error: ' .$dhlResult['error']);
		} elseif (property_exists($dhlResult, 'getTrackAndTraceInfoResult') && property_exists($dhlResult->getTrackAndTraceInfoResult, 'events') && property_exists($dhlResult->getTrackAndTraceInfoResult->events, 'item')) {
			$events = $dhlResult->getTrackAndTraceInfoResult->events;
			foreach ($events->item as $singleEvent) {
				$dhlMessage[$singleEvent->status] = 
						(($singleEvent->receivedBy) ? $this->__('Received By: ') . $singleEvent->receivedBy . PHP_EOL : '')
						. $this->__('Description: ') . $singleEvent->description . PHP_EOL
						. $this->__('Terminal: ') . $singleEvent->terminal . PHP_EOL
						. $this->__('Time: ') . $singleEvent->timestamp . PHP_EOL.PHP_EOL;

				switch ($singleEvent->status) {
					case self::DHL_STATUS_DELIVERED:
						$status = Mage::helper('udpo')->__('Delivered');
						$track->setUdropshipStatus(Unirgy_Dropship_Model_Source::TRACK_STATUS_DELIVERED);
						break;
					case self::DHL_STATUS_RETURNED:
						$status = Mage::helper('udpo')->__('Returned');
						$track->setUdropshipStatus(Unirgy_Dropship_Model_Source::TRACK_STATUS_CANCELED);
					case self::DHL_STATUS_WRONG:
						$status = Mage::helper('udpo')->__('Canceled');
						$track->setUdropshipStatus(Unirgy_Dropship_Model_Source::TRACK_STATUS_CANCELED);
					default:
						$status = Mage::helper('udpo')->__('Ready to Ship');				
						break;
				}
				$saveAction = true;
			}
		} else {
			$this->_log('DHL Service Error: Missing Track and Trace Data');
		}

		if ($saveAction) {
			$dhlMessage = array_unique($dhlMessage);
			foreach ($dhlMessage as $singleMessage) {
				$comment .= $singleMessage;
			}
				
			$dhlComment->setParentId($poId)
					->setComment(trim($comment))
					->setCreatedAt(now())
					->setIsVisibleToVendor(1)
					->setUdropshipStatus($status)
					->setUsername('API');
			
			$track->setNextCheck($this->getNextDhlCheck($shipment->getOrder()->getStoreId()));
//			$dhlComment->save();
//			$track->save();
		}
		return true;
	}
	
	protected function _getPoOrderCommentsHistory($poId, $firstItem = true) {
		$comments = Mage::getModel('udpo/po_comment')
						->getCollection()
						->setPoFilter($poId)
						->addFieldToFilter('comment', array('like' => self::DHL_HEADER));
				
		if ($firstItem) {
			$comments = $comments->getFirstItem();
		}		
		
		return $comments;
	}
}