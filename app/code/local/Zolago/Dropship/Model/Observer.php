<?php

class Zolago_Dropship_Model_Observer extends Unirgy_Dropship_Model_Observer{
	
	/**
	 * Clear afetr laod quote better performance
	 * @todo possible bugs not compatibile with unirgy?
	 * @param type $observer
	 * @return Zolago_Dropship_Model_Observer
	 */
	public function sales_quote_load_after($observer){
		if(Mage::helper("zolagocommon")->isUserDataRequest()){
			return;
		}
		parent::sales_quote_load_after($observer);
	}
	
	/**
	 * @param type $observer
	 * @return \Zolago_Dropship_Model_Observer
	 */
	public function bindLocale($observer)
    {
		if(!Mage::registry("dropship_switch_lang")){
			return;
		}
		
		// Handle locale
		$session = Mage::getSingleton('udropship/session');
		
		if ($locale=$observer->getEvent()->getLocale()) {
            if ($choosedLocale = $session->getLocale()) {
                $locale->setLocaleCode($choosedLocale);
            }
        }
        return $this;
    }

    public function cronCollectTracking()
    {
        $statusFilter = array(Unirgy_Dropship_Model_Source::TRACK_STATUS_PENDING,Unirgy_Dropship_Model_Source::TRACK_STATUS_READY,Unirgy_Dropship_Model_Source::TRACK_STATUS_SHIPPED);
        $time = Mage::getModel('core/date')->timestamp();
        $now = date('Y-m-d H:i:s', $time);

        if (Mage::helper('udropship')->isSalesFlat()) {

            $res  = Mage::getSingleton('core/resource');
            $conn = $res->getConnection('sales_read');

            $sIdsSel = $conn->select()->distinct()
                ->from($res->getTableName('sales/shipment_track'), array('parent_id'))
                ->where('udropship_status in (?)', $statusFilter)
                ->where('next_check<=?', $now)
                ->limit(50);
            $sIds = $conn->fetchCol($sIdsSel);

        } else {
            $res  = Mage::getSingleton('core/resource');
            $conn = $res->getConnection('sales_read');

            $eav = Mage::getSingleton('eav/config');
            $trackEt = $eav->getEntityType('shipment_track');
            $udStatusAttr = $eav->getAttribute('shipment_track', 'udropship_status');
            $nextCheckAttr = $eav->getAttribute('shipment_track', 'next_check');

            $sIdsSel = $conn->select()->distinct()
                ->from(array('e' => $trackEt->getValueTablePrefix()), array('parent_id'))
                ->join(
                    array('us' => $udStatusAttr->getBackendTable()),
                    $conn->quoteInto('e.entity_id=us.entity_id and us.entity_type_id=?', $trackEt->getId())
                    .$conn->quoteInto(' and us.attribute_id=?', $udStatusAttr->getId()),
                    array())
                ->join(
                    array('nc' => $nextCheckAttr->getBackendTable()),
                    $conn->quoteInto('e.entity_id=nc.entity_id and nc.entity_type_id=?', $trackEt->getId())
                    .$conn->quoteInto(' and nc.attribute_id=?', $nextCheckAttr->getId()),
                    array())
                ->where('us.value in (?)', $statusFilter)
                ->where('nc.value<=?', $now)
                ->limit(50);
            $sIds = $conn->fetchCol($sIdsSel);
        }

        if (!empty($sIds)) {
            $tracks = Mage::getModel('sales/order_shipment_track')->getCollection()
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('udropship_status', array('in'=>$statusFilter))
                ->addAttributeToFilter('parent_id', array('in'=>$sIds))
                ->addAttributeToSort('parent_id')
            ;

            try {
                Mage::helper('udropship')->collectTracking($tracks);
            } catch (Exception $e) {
                $tracksByStore = array();
                foreach ($tracks as $track) {
                    $tracksByStore[$track->getShipment()->getOrder()->getStoreId()][] = $track;
                }
                foreach ($tracksByStore as $sId => $_tracks) {
                    Mage::helper('udropship/error')->sendPollTrackingFailedNotification($_tracks, "$e", $sId);
                }
            }
        }

        if (0<Mage::getStoreConfig('udropship/error_notifications/poll_tracking_limit')) {
            $limit = date('Y-m-d H:i:s', $time-24*60*60*Mage::getStoreConfig('udropship/error_notifications/poll_tracking_limit'));

            $tracks = Mage::getModel('sales/order_shipment_track')->getCollection()
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('udropship_status', 'P')
                ->addAttributeToFilter('created_at', array('datetime'=>true, 'to'=>$limit))
                ->setPageSize(50)
            ;
            $tracksByStore = array();
            foreach ($tracks as $track) {
                $cCode = $track->getCarrierCode();
                if (!$cCode) {
                    continue;
                }
                $vId = $track->getShipment()->getUdropshipVendor();
                $v = Mage::helper('udropship')->getVendor($vId);
                if (!$v->getTrackApi($cCode)) {
                    continue;
                }
                $tracksByStore[$track->getShipment()->getOrder()->getStoreId()][] = $track;
            }
            foreach ($tracksByStore as $sId => $_tracks) {
                Mage::helper('udropship/error')->sendPollTrackingLimitExceededNotification($_tracks, $sId);
            }
        }
    }



    /**
     * Add Data to Save Action
     *
     * @param $observer Varien_Event_Observer
	 * 
     * @return Zolago_Dropship_Model_Observer
     */	
	public function addOrbaShippingData(Varien_Event_Observer $observer)
	{
        $time = Mage::getModel('core/date')->timestamp();
        $track = $observer->getEvent()->getTrack();
		$carrierCode = $track->getCarrierCode();
		
		if (in_array($carrierCode,array(Orba_Shipping_Model_Carrier_Dhl::CODE,Orba_Shipping_Model_Carrier_Ups::CODE))
			&& Mage::getSingleton('shipping/config')->getCarrierInstance($carrierCode)->isTrackingAvailable()
			&& !$track->getWebApi()) {
				$track->setNextCheck(date('Y-m-d H:i:s', $time));
				$track->setUdropshipStatus(Unirgy_Dropship_Model_Source::TRACK_STATUS_PENDING);
		}

		return $this;
	}
}
