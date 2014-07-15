<?php
class Zolago_Dropship_Helper_Data extends Unirgy_Dropship_Helper_Data {
	const TRACK_SINGLE			= 1;
	protected $trackingHelperPath = 'zolagodhl/tracking';
	
    /**
     * @param string
     */
	public function setTrackingHelperPath($path) {
		$this->trackingHelperPath  = $path;
		
	}

	/**
	 * @param Unirgy_Rma_Model_Rma_Track | string $tracking 
	 */
	public function getTrackingStatusName($tracking) {
		if($tracking instanceof Unirgy_Rma_Model_Rma_Track){
			$tracking = $tracking->getUdropshipStatus();
		}
		switch ($tracking) {
			case Unirgy_Dropship_Model_Source::TRACK_STATUS_CANCELED:
				return $this->__("Canceled");
			break;
			case Unirgy_Dropship_Model_Source::TRACK_STATUS_DELIVERED:
				return $this->__("Delivered");
			break;
			case Unirgy_Dropship_Model_Source::TRACK_STATUS_PENDING:
				return $this->__("Pending");
			break;
			case Unirgy_Dropship_Model_Source::TRACK_STATUS_READY:
				return $this->__("Ready");
			break;
			case Unirgy_Dropship_Model_Source::TRACK_STATUS_SHIPPED:
				return $this->__("Shipped");
			break;
		}
		return $tracking;
	}
	
	/**
	 * @param Mage_Core_Model_Store|int|null $store
	 * @return Mage_Catalog_Model_Entity_Attribute
	 */
	public function getVendorSkuAttribute($store=null) {
		if($store instanceof Mage_Core_Model_Store){
			$store=$store->getId();
		}
		$attrCode = Mage::getStoreConfig('udropship/vendor/vendor_sku_attribute', $store);
		if(!empty($attrCode)){
			$attr = Mage::getSingleton('eav/config')->getAttribute(Mage_Catalog_Model_Product::ENTITY, $attrCode);
			if($attr->getId()){
				return $attr;
			}
		}
		return Mage::getSingleton('eav/config')->getAttribute(Mage_Catalog_Model_Product::ENTITY, "sku");
	}
	
	public function getAllowedCarriers() {
		return array(/*"", "custom", */"zolagodhl","ups");
	}
	
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
			if ($cCode !== Zolago_Dhl_Model_Carrier::CODE) {
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
					if ($cCode !== Zolago_Dhl_Model_Carrier::CODE) {
						$result = $v->getTrackApi($cCode)->collectTracking($v, array_keys($trackIds));
					} else {
						$result = Mage::helper($this->trackingHelperPath)->collectDhlTracking($trackIds);
					}
                    if ($_track) Mage::helper('udropship/label')->afterShipmentLabel($v, $_track);
                } catch (Exception $e) {
                    if ($_track) Mage::helper('udropship/label')->afterShipmentLabel($v, $_track);
                    $this->_processPollTrackingFailed($trackIds, $e);
                    continue;
                }
				
				if(!result) continue;
				
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
                                $track->setShippedDate();
                                $track->save();
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
                        $this->processTrackStatus($_pTracks, true); } catch
                    (Exception $e) {
                        $this->_processPollTrackingFailed($_pTracks, $e);
                        continue;
                    }
                }
            }
        }
    }
	
	
	
	public function getProductStatusForVendor(Unirgy_Dropship_Model_Vendor $vendor) {
		$status = Mage_Catalog_Model_Product_Status::STATUS_DISABLED;

		if ($vendor->getReviewStatus()) {
			$status = (int)$vendor->getReviewStatus();
		}
		
		return $status;
	}

    public function getVendorLogoResizedUrl($vendor, $width, $height)
    {
		return Mage::helper('udropship')->getResizedVendorLogoUrl($vendor, $width, $height);
    }
}