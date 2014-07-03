<?php
class Orba_Shipping_Helper_Carrier_Tracking_Dhl extends Orba_Shipping_Helper_Carrier_Tracking {
    
    //{{{ 
    /**
     * Collect tracking for DHL
     * @param Zolago_Carrier_Model_Client $client
     * @param type $track
     * @return 
     */

    protected function _process($client,$_track) {
        $result = $client->getTrackAndTraceInfo($_track->getTrackNumber());
				//Process Single Track and Trace Object
        $this->_processDhlTrackStatus($_track, $result);

    }
    //}}}
	
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
			Mage::helper('orbashipping/carrier_dhl')->_log('DHL Service Error: ' .$dhlResult['error']);
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
					case Zolago_Carrier_Helper_Dhl::DHL_STATUS_DELIVERED:
						$status = $this->__('Delivered');
						$track->setUdropshipStatus(Unirgy_Dropship_Model_Source::TRACK_STATUS_DELIVERED);
						$track->setShippedDate(Varien_Date::now());
						$track->getShipment()->setUdropshipStatus(Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_DELIVERED);
						$shipped = false;
						break;
					case Zolago_Carrier_Helper_Dhl::DHL_STATUS_RETURNED:
						$status = $this->__('Returned');
						$track->setUdropshipStatus(Unirgy_Dropship_Model_Source::TRACK_STATUS_CANCELED);
						$track->setShippedDate(null);
						$track->getShipment()->setUdropshipStatus(Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_RETURNED);
						$shipped = false;
						break;
					case Zolago_Carrier_Helper_Dhl::DHL_STATUS_WRONG:
						$status = $this->__('Canceled');
						$track->setUdropshipStatus(Unirgy_Dropship_Model_Source::TRACK_STATUS_CANCELED);
						$track->setShippedDate(null);
						$track->getShipment()->setUdropshipStatus(Unirgy_Dropship_Model_Source::SHIPMENT_STATUS_RETURNED);
						$shipped = false;
						break;
					case Zolago_Carrier_Helper_Dhl::DHL_STATUS_SHIPPED:
					case Zolago_Carrier_Helper_Dhl::DHL_STATUS_SORT:
					case Zolago_Carrier_Helper_Dhl::DHL_STATUS_LP:
					case Zolago_Carrier_Helper_Dhl::DHL_STATUS_LK:
					case Zolago_Carrier_Helper_Dhl::DHL_STATUS_AWI:
					case Zolago_Carrier_Helper_Dhl::DHL_STATUS_BGR:
					case Zolago_Carrier_Helper_Dhl::DHL_STATUS_OP:
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
			Mage::helper('orbashipping/carrier_dhl')->_log('DHL Service Error: Missing Track and Trace Data');
			$dhlMessage[] = $this->__('DHL Service Error: Missing Track and Trace Data');
		}
		if ($oldStatus != $track->getUdropshipStatus()) {
    		$this->_addComment($track,$shipmentIdMessage,$dhlMessage,$status);
        }
		if (!in_array($status, array($this->__('Delivered'), $this->__('Returned'), $this->__('Canceled')))) {
			$track->setNextCheck(Mage::helper('orbashipping/carrier_dhl')->getNextDhlCheck($shipment->getOrder()->getStoreId()));
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

}

